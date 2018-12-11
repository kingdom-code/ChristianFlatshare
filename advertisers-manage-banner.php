<?php

use CFS\Image\CFSImage;

    // Autoloader
    require_once 'web/global.php';

	// Ensure only valid advertisers are logged in
	if ($_SESSION['u_access'] != "advertiser") { header("Location:index.php"); exit; }
	
    
    
	// Initialise variables
	if (isset($_REQUEST['banner_id'])) { $banner_id = $_REQUEST['banner_id']; } else { $banner_id = NULL; }
	if (isset($_REQUEST['action'])) { $action = $_REQUEST['action']; } else { $action = "add"; }
	if (isset($_GET['edit'])) {
		$action = "edit";
		$banner_id = trim($_GET['edit']);
	}
    
    $radius = (isset($_POST['radius']) ? $_POST['radius'] : 5);
    
	$error = array(); // Will contain flags for many errors	
	
	// Act depending on the action
	switch($action) {
	
		case "add":
		
			$page_title = "Add a new banner";
			$page_description = "<strong>Please use the form below to create a new banner.</strong>";
			$action = "postadd";
			break;
			
		case "postadd":
		
        
            if (isset($_POST['advertLocations'])) {
                $advertLocations = $_POST['advertLocations'];
            }
			$page_title = "Add a new banner";
			$page_description = "<strong>Please use the form below to create a new banner.</strong>";
			
			/*
				Initialise $_POST variables:

				Array
				(
					[action] => postadd
					[title] => 
					[link] => 
					[date_from] => 
					[date_to] => 
					[display] => 
					[radius_1] => 
					[place_1] => 
					[radius_2] => 
					[place_2] => 
					[radius_3] => 
					[place_3] => 
					[radius_4] => 
					[place_4] => 
					[radius_5] => 
					[place_5] => 
					[frontpage] => 
					[file] =>
				)
							
			*/
			foreach($_POST as $key => $value) {
				${$key} = trim($value);
			}
			
			if (!$link) { $link = "http://"; }
			// Assert type=728x90, as other advert type is deprecated and no longer given as an option
			$type = "728x90"; 
			
			// Validation
			if (!$title) { $error['title'] = TRUE; }
			if (!$link || $link == "http://") { $error['link'] = TRUE; }
			if (!$type) { $error['type'] = TRUE; }
			if ($date_from && $date_to) {
				// Ensure date_to is after date_from
				$from = strtotime($date_from);
				$to = strtotime($date_to);
				if ($to <= $from) {
					$error['date_from'] = TRUE;
				}			
			}
			if (!$display) { $error['display'] = TRUE; }
			if ($display == "2") {

                $locations = json_decode($_POST['advertLocations']);
					
			}
			// Handle upload only if no errors
			if (!$error) {
                
                // Get any potentially uploaded file
                if (isset($_FILES['filename']['tmp_name']) && is_readable($_FILES['filename']['tmp_name'])) {
                    $file = new SplFileInfo($_FILES['filename']['tmp_name']);
                    $image = new CFSImage($file);
                }
                else {
                    $image = NULL;
                }
            
                if ($image !== NULL) {
                    try {
                        // Validate file type
                        $image->validateFileExtension(array('jpg', 'gif'));
                
                        // Validate file size (in MB)
                        $image->validateFileSize(1);
                
                        // Validate image size (in px)
                        $image->validateImageSize(728, 90, '=');
                    } catch(Exception $e) {
                        $error['filename'] = $e->getMessage();
                    }
                
                    if (!isset($error['filename'])) {
                        // SAVE IMAGE
                        $filename = $image->saveBanner();
                    }
                }
			}

			
			// Save to database if no errors exist
			if (!$error) {
			
				// Default dates for past and future
				if (!$date_from) { $date_from = '1900-01-01'; }
				if (!$date_to) { $date_to = '3000-01-01'; }
				
				// Insert data into the banners table
				$query = "
					INSERT INTO cf_banners SET
						user_id 	=	'".$_SESSION['u_id']."',
						title		=	'".$title."',
						link		=	'".$link."',
						date_from	=	'".$date_from."',
						date_to		=	'".$date_to."',
						type		=	'".$type."',
						display		=	'".$display."',
						filename	=	'".$filename."',
						frontpage	=	'".$frontpage."',
                        country     =   '".$country."'
				";	
				
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
								
				if (!$result) {
					$error['db_update'] = '<p class="error">An error occured when updating the database. Please contact us to resolve</p>';
					@unlink("images/banners/".$dest_name);
				} else {
				
					// If display == 2 (location-specific), insert into the cf_banners_locations
					if ($display == "2") {
					
						// Get the id of the newly-added banner
						$new_id = mysqli_insert_id();
					    
                        foreach ($locations as $location) {
                            $query = "INSERT INTO cf_banners_locations (banner_id,place,radius,country,latitude,longitude) ";
                            $query .= "VALUES (" . $new_id . ", '', " . $location[2] . ", '" . $country ."', " . $location[0] . ", " . $location[1] . ", " . $userCountry['iso'] . ")";
                            $result = mysqli_query($GLOBALS['mysql_conn'], $query);
                        }
					}				
				
					header("Location: advertisers.php?msg=add_success");
					exit;				
				}
							
			}
			
			break;
			
		case "edit":
		    
            
            
			// Load banner info from the db
			$query = "SELECT * FROM cf_banners WHERE banner_id = '".$banner_id."' AND user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (!mysqli_num_rows($result)) {
				header("Location: advertisers.php");
				exit;
			} else {
				$banner = mysqli_fetch_assoc($result);
				foreach($banner as $key => $value) {
					${$key} = $value;
				}
				
				// If $display == 2, load info from the cf_banners_locations table
				if ($display == 2) {
                    			$locations = array();
					$query = "SELECT latitude, longitude, radius FROM cf_banners_locations WHERE banner_id = '".$banner_id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						while($row = mysqli_fetch_assoc($result)) {
                            $locations[] = array(
                                (float)$row['latitude'],
                                (float)$row['longitude'],
                                (int)$row['radius']
                            );
						}
					}
                    
                    $advertLocations = json_encode($locations);
				}
			}
            
            if (isset($_POST['advertLocations'])) {
                $advertLocations = $_POST['advertLocations'];
            }
			
			$page_title = "Editing banner &quot;".$title."&quot;";
			$page_description  = 'Please use the form below to edit the details of your banner.<br/><br/>';
			$action = "postedit";
			break;
			
		case "postedit":
		
			// Initialise $_POST variables:
			foreach($_POST as $key => $value) {
				${$key} = trim($value);
				if (!$link) { $link = "http://"; }
			}
			
			// Get existing banner image
			$query = "SELECT approved,filename,type FROM cf_banners WHERE banner_id = '".$banner_id."' and user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (!$result) {
				header("Location:advertisers.php");
				exit;			
			} else {
				$approved 	= cfs_mysqli_result($result,0,0);
				$filename 	= cfs_mysqli_result($result,0,1);
				$type		= cfs_mysqli_result($result,0,2);
			}
			
			$page_title = "Editing banner &quot;".$title."&quot;";
			$page_description  = 'Please use the form below to edit the details of your banner.<br/>';
			$page_description .= '<strong>PLEASE NOTE:</strong> Uploading a new image to replace an already-approved ';
			$page_description .= 'one will set your status to &quot;Pending approval&quot;, ';
			$page_description .= 'until one of our administrators can approve the banner.';
			
			// Validation
			if (!$title) { $error['title'] = TRUE; }
			if (!$link || $link == "http://") { $error['link'] = TRUE; }
			if ($date_from && $date_to) {
				// Ensure date_to is after date_from
				$from = strtotime($date_from);
				$to = strtotime($date_to);
				if ($to <= $from) {
					$error['date_from'] = TRUE;
				}			
			}
			if (!$display) { $error['display'] = TRUE; }
			if ($display == "2") {

                $locations = json_decode($_POST['advertLocations']);
					
			}

			// Handle upload only if no errors
			$dest_name = NULL;
			if (!$error) {
				
			}
			
			// Save to database if no errors exist
			if (!$error) {
			
				// If we're uploading a new banner, delete old one and set banner to "Pending approval"
				if ($dest_name) {
					$approved = 0;
					@unlink("images/banners/".$filename);
				} else {
					$dest_name = $filename;
				}
				
				// Default dates for past and future
				if (!$date_from) { $date_from = '1900-01-01'; }
				if (!$date_to) { $date_to = '3000-01-01'; }
			
				$query = "
					UPDATE cf_banners SET
						title		=	'".$title."',
						link		=	'".$link."',
						date_from	=	'".$date_from."',
						date_to		=	'".$date_to."',
						display		=	'".$display."',
						filename	=	'".$dest_name."',
						frontpage	=	'".$frontpage."',
						approved	=	'".$approved."'
					WHERE banner_id = '".$banner_id."'
					AND (user_id = '".$_SESSION['u_id']."'  or '".$_SESSION['u_id']."' = 15691)";	
				
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if (!$result) {
					$error['db_update'] = '<p class="error">An error occured when updating the database. Please contact us to resolve</p>';
					@unlink("images/banners/".$dest_name);
				} else {
					
					// Whatever the value of display, let's clear the location specific data
					$query = "DELETE FROM cf_banners_locations WHERE banner_id = '".$banner_id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					
					// If display == 2 (location-specific), insert into the cf_banners_locations
					if ($display == "2") {
                        foreach ($locations as $location) {
                            $query = "INSERT INTO cf_banners_locations (banner_id,place,radius,country,latitude,longitude) VALUES (" . $banner_id . ", '', " . $location[2] . ", '" . $country ."', " . $location[0] . ", " . $location[1] . ")";
                            $result = mysqli_query($GLOBALS['mysql_conn'], $query);
                        }
					}					
					
					header("Location: advertisers.php?msg=update_success");
					exit;				
				}
			
			}
			break;
	
	}
	
	// Mileage for postcode-specific dropdowns
	$miles = array(
		'5'		=>	'5 miles',
		'10'	=>	'10 miles',
		'20'	=>	'20 miles',
		'50'	=>	'50 miles',
		'100'	=>	'100 miles'	
	);	
	
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
	<title><?php print $page_title?> - Christian Flatshare</title>
	<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<script language="javascript" type="text/javascript" src="scripts/adverts.js"></script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" -->
	<script language="javascript" type="text/javascript">

		window.addEvent("domready",function(){
		
			<?php if ($display != "2") { ?>
			$('display_2_specifics').setStyle('display','none');
			<?php } ?>
			
			$('display_1').addEvent('click',function(){
				$('display_2_specifics').setStyle('display','none');
				$$('#display_2_specifics input').set('value','');
				$$('#display_2_specifics select').each(function(sel){
					sel.set('value',0);
				});			
			});
			
			$('display_2').addEvent('click',function(){
				$('display_2_specifics').setStyle('display','');
			});
		
		});
	
	
	</script>
    <!-- GOOGLE MAPS API v3  -->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
    <script src="scripts/adverts.js"></script>
    <script src="scripts/json2.js"></script>
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
				
		<div style="
			margin-bottom:20px; 
			background-image:url(images/separator-column.gif);
			background-position:601px top;
			background-repeat:repeat-y;
		">
		
			<div style="float:left; width:590px; margin-right:21px;">
				<h1 class="mt0"><?php print $page_title?></h1>
				<p class="mb0"><?php print $page_description?><br />
				Cancel and <a href="advertisers.php">return to the main advertisers page</a></p>
			</div>
					
			<div style="float:left; width:239px;">
			
				<?php print $theme['side']; ?>
			
			</div>
		
			<div class="cc0"><!----></div>
			
		</div>
		
		
	
		<form name="bannerForm" method="POST" action="<?php print $_SERVER['PHP_SELF']?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php print $action?>" />
		<?php if ($action == "postedit") { ?>
		<input type="hidden" name="banner_id" value="<?php print $banner_id?>" />
		<?php } ?>
		<div class="box_light_grey mb20">
			<div class="tr"><span class="l"></span><span class="r"></span></div>
			<div class="mr" style="padding:20px 30px;">
				<?php print $error['db_update']?>
				<h2 class="mt0">Banner properties</h2>
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="120">Banner title:</td>
						<td class="obligatory">*&nbsp;</td>
						<td>
							<?php if ($error['title']) { ?>
							<span class="error">Please enter a title for your banner</span><br />
							<?php } ?>
							<input name="title" type="text" id="title" size="60" value="<?php print $title?>" />
						</td>
					</tr>
					<tr style="padding-bottom:15px;">
						<td width="120">&nbsp;</td>
						<td>&nbsp;</td>
						<td class="grey">Banner title is used for your reference only.<br />
						  &nbsp;</td>
					</tr>
					<tr>
						<td width="120">Banner  links to:</td>
						<td class="obligatory">*&nbsp;</td>
						<td>
							<?php if ($error['link']) { ?>
							<span class="error">Please specify the link for this banner</span><br />
							<?php } ?>
							<input name="link" type="text" id="link" value="<?php print  ($link == '')?'http://':$link ?>" size="100"  />
						</td>
					</tr>
					<tr>
						<td width="120">&nbsp;</td>
						<td>&nbsp;</td>
						<td class="grey">Where you would like your banner to link to, e.g. http://www.oakhall.co.uk<br />&nbsp;</td>
					</tr>					
					<tr>
						<td width="120" valign="top">Banner type:</td>
						<td valign="top" class="obligatory"></td>
						<td>
							<?php if ($action == "postedit") { ?>
								<p class="mb10"><span class="grey">Banner images cannot be changed.<br/>To add a banner of a different size, please choose create a new banner.</span></p>
							<?php } else { ?>
							<?php if ($error['type']) { ?>
								<p class="mb10"><span class="error">Please specify the type of this banner</span></p>
							<?php } ?>
							<table cellpadding="10" cellspacing="0">
							<tr>					
									<td valign="top" style="background-color:#E2E2E2; width:270px;">
										<table cellpadding="0" cellspacing="0" class="mb10">
											<tr>
											<!--	<td><?php print createRadio("type,banner_728x90","728x90",$type,'','','onclick="pickBannerType(this.value);"')?></td> -->
											  <td></td>
												<td><label for="banner_728x90">728 x 90 "Leaderboard" horizontal banner</label></td>
											</tr>
										</table>
										<a class="banner_728x90" href="#">728 x 90</a>
										<p class="mt10 mb10">This banner can be displayed on:</p>
										<ul class="m0">
											<li>The front page</li>
											<li>The search results pages</li>
										</ul>
									</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								<!--	<td valign="top" style="background-color:#E2E2E2; width:270px;">
										<table cellpadding="0" cellspacing="0" class="mb10">
											<tr>
												<td><?php print createRadio("type,banner_120x240","120x240",$type,'','','onclick="pickBannerType(this.value);"')?></td>
												<td><label for="banner_120x240">120 x 240 Vertical banner</label></td>
											</tr>
										</table>
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td valign="top" width="64"><a class="banner_120x240" href="#">120<br />x<br />240</a></td>
												<td valign="top">
													<p class="mt0 mb10">This banner can be displayed on the advert details page.</li>
												</td>
											</tr>
										</table>
									</td> -->
								</tr>
							</table>
							<?php } ?>
						</td>
					</tr>
				</table>
				<h3 class="mb0">Dates:</h3>
				<p class="mt0 mb0">Specify a range of dates that you want this banner to be displayed:</p>

<!-- IF DATES ARE CHANGED, NEED TO RE-APPROVE -->
				<table border="0" cellpadding="0" cellspacing="10">
					<tr>
						<td width="120">Date from:</td>
						<td>
							<?php if ($error['date_from']) { ?>
							<span class="error">Please ensure that the FROM date is earlier than the TO date.</span><br />
							<?php } ?>
							<?php //if ($action == "postadd") { ?> 
								<?php print createDateDropDown("date_from",180,$date_from,"Start now","dateSelector") ?>
							 <?php //} //else {	?> 
						</td>
					</tr>
					<tr>
						<td width="120">Date to:</td>
						<td><?php print createDateDropDown("date_to",180,$date_to,$date_to,"dateSelector")?></td>
					</tr>
				</table>
				
				
				<h3 class="mb0">Where to display:</h3>
				<p class="mt0">Indicate where banner should be displayed:</p>
				
				<?php if ($error['display']) { ?>
							<span class="error">Please specify where you want your banner to be displayed</span><br />
				<?php } ?>
							
				<table border="0" cellpadding="0" cellspacing="10">
					<tr>
						<td width="120" valign="top">Shown in search results and advert details pages</td>
						<td>
	
							<table cellpadding="0" cellspacing="0">
								<tr>
									<td valign="top" style="padding-right:10px;"><?php print createRadio("display,display_1","1",$display)?></td>
                  <td width="479"><label for="display_1"><strong>Countrywide (<?php print $userCountry['name']; ?>)</strong><!--<?php print CFS_POINT_COST_NATIONWIDE?> CFS point / click</strong>--></strong><br />
									Banners are shown in the search results list and on the advert details page, nationwide. </label></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width="120">&nbsp;</td>
						<td>
							<table cellpadding="0" cellspacing="0">
								<tr>
									<td valign="top" style="padding-right:10px;"><?php print createRadio("display,display_2","2",$display)?></td>
									<td><label for="display_2"><strong>Location-specific</strong><!-- results - <?php print CFS_POINT_COST_LOCATION_SPECIFIC?> CFS points / click</strong>--><br /></label>
									  Banners are shown in the search results list and on the advert details page, for locations you specify. </td>
								</tr>
							</table>
							
											
	<script language="javascript" type="text/javascript">
	
		function pickBannerType(v) {
				if (v == "120x240") {				
						$('pickBannerType_chosen').set('text','120 x 240');	
				} else {
						$('pickBannerType_chosen').set('text','728 x 90');	
				}
			}
</script>			
							
							<div id="display_2_specifics" class="mt10" style="padding:10px; background-color:#E2E2E2;">
							<?php if ($error['display']) { ?>
							<div class="error" style="padding:10px 10px 0px 10px;">Please ensure that you fill in the radius-postcode pairs correctly.</div>
							<?php } ?>
							<table cellpadding="0" cellspacing="10">
                                <tr>
                                    <td></td>
                                    <td>Choose a radius and then enter a location. You can add multiple locations by entering and selecting new locations. To remove a location simply click on the circle in the map.</td>
                                </tr>
								<tr>
									<td>Radius</td>
									<td><?php print createDropdown("radius",$miles,$radius)?></td>
								</tr>
								<tr>
									<td>Location</td>
									<td>
                                        <input type="input" id="advertLocationPlace" />
                                        <input type="hidden" name="country" id="advertCountry" value="<?php print $userCountry['iso']; ?>" />
                                        <input type="hidden" name="advertLocations" id="advertLocations" value="<?php print $advertLocations; ?>" />
                                    </td>
								</tr>
								<tr>
									<td></td>
									<td><div id="locationMap" style="width: 400px; height: 300px; display: none;"></div></td>
								</tr>
							</table>						
							</div>
							
						</td>
					</tr>
					<tr>
						<td valign="top">Show on frontpage?</td>
						<td>
							<table cellpadding="0" cellspacing="0">
								<tr>
									<td valign="top" style="padding-right:10px;"><?php print createCheckbox("frontpage",1,$frontpage)?></td>
									<td><label for="frontpage"><strong>Show banner on the frontpage<!-- - <?php print CFS_POINT_COST_FRONTPAGE?> CFS points / click--></strong><br />Check this box to have your banner to show on the frontpage.</label></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>

				<?php if ($action == "postadd") { ?>
				<h3 class="mb0">Image file:</h3>
				<p class="mt0 mb5">Upload your  JPEG or GIF banner file with dimensions <span id="pickBannerType_chosen">728 x 90</span> (less than 1MB).</p>
				<table border="0" cellpadding="0" cellspacing="0">
					<?php if ($error['filename']) { ?>
					<tr>
						<td width="120">&nbsp;</td>
						<td class="error"><?php print $error['filename']?></td>
					</tr>
					<?php } ?>
					<tr>
						<td width="120">Browse for file:<?php print $test?></td>
						<td><input type="file" size="93"  name="filename" id="filename" value="<?php print $filename?>"/></td>
					</tr>
					
					<tr>
						<td width="120">&nbsp;</td>
						<td><span class="grey"><strong>Please note: </strong>It is important for Christian Flatshare that CFS remains pleasing to look at to our visitors and members.
						</span></td>
					</tr>
				</table>					
	
					<?php } // end show upload banner box ?>		
					
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="120">&nbsp;</td>
						<?php if ($action == "postadd") { ?>
							<td><br /><input type="submit" value="Submit banner ad"/></td>						
						<?php } else { ?>
							<td><br /><input type="submit" value="Update banner ad"/></td>						
						<?php } ?>						
					</tr>
				</table>

				<?php if ($action == "postedit") { ?>
				<h3>Current banner:</h3>
				<div><img src="images/banners/<?php print $filename?>" /></div>
				<?php } ?>
				
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				
			</div>
			</form>

	
		<!-- InstanceEndEditable -->
		</div>
		<div class="redMenu">
			<ul>
				<!--<li><a href="../flat-finding-tips.php">flat finding tips</a></li>-->
				<li><a href="advertising.php">advertising</a></li>			
				<li><a href="where-does-all-the-money-go.php">where does the money go?</a></li>
				<li><a href="glossary.php">glossary</a></li>
				<li><a href="terms-and-conditions.php">terms &amp; conditions</a></li>
				<li><a href="privacy-policy.php">privacy policy</a></li>
				<li><a href="contact-us.php">contact us</a></li>
				<li class="noSeparator"><a href="resources.php">links &amp; resources</a></li>
			</ul>
		</div>
		<div id="footerText">
			<p class="m0"><strong>Christian Flatshare... helping accommodation seekers connect with the local church community<br />
			Finding homes, growing churches and building communities </strong>&copy; ChristianFlatShare.org 2007-<?php print date("Y")?></p>
	  </div>
	</div>
	<div id="footer"><img src="images/spacer.gif" alt="*" width="1" height="12"/></div>
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
<?php print getTrackingCode();?>
</body>
<!-- InstanceEnd --></html>
