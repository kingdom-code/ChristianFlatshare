<?php
    session_start();
    
    // Autoloader
    require_once 'web/global.php';
    
    connectToDB();
	
	// Ensure only valid advertisers are logged in
	if ($_SESSION['u_access'] != "advertiser") { header("Location:index.php"); exit; }
	
	// Delete action
	if (isset($_GET['delete'])) { 
		// Load filename
		$query = "SELECT filename FROM cf_banners WHERE banner_id = '".$_GET['delete']."' and user_id = '".$_SESSION['u_id']."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
			// Get the filename for current banner
			$filename = cfs_mysqli_result($result,0,0);
			// Delete entry from db
			$query = "UPDATE cf_banners SET deleted = 1 WHERE banner_id = '".$_GET['delete']."' and user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			//if ($result) {
			//	// Delete was succesful, remove the old image as well
			//	@unlink("images/banners/".$filename);
			//}		
		}
	}
	
	// Suspend action
	if (isset($_GET['suspend'])) { 
		// Load filename
		$query = "SELECT filename FROM cf_banners WHERE banner_id = '".$_GET['suspend']."' and user_id = '".$_SESSION['u_id']."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
			// Toggle the suspend on or of
			$query = "
				UPDATE cf_banners SET suspended = ABS(suspended - 1) 
				WHERE banner_id = '".$_GET['suspend']."' and user_id = '".$_SESSION['u_id']."'
			";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);					
		}
	}
		
	// Load all banners for this advertiser or advertising@christianflatshare.org 11691
	$query = "
		SELECT 
			*,
	    FORMAT(times_viewed,0) AS `banner_clicks`,
			DATE_FORMAT(date_from,'%a, %D %M %Y') as `date_from_formatted`,
			DATE_FORMAT(date_to,'%a, %D %M %Y') as `date_to_formatted`,
			IF (date_from<CURDATE(),IF(date_to>CURDATE(),'Running Now','Campaign Completed'),'Approved, scheduled to run') as `banner_status`
		FROM cf_banners 
    WHERE deleted != 1
	  AND (user_id = ".$_SESSION['u_id']." or ".$_SESSION['u_id']." = 15691)
		ORDER BY banner_id DESC;";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$banner_count = mysqli_num_rows($result);

	if ($banner_count) {
	
		while($banner = mysqli_fetch_assoc($result)) {
		
		  $query = "SELECT 'x' FROM cf_banners_clicks WHERE banner_id = ".$banner['banner_id'].";";
			$click_result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($click_result) { 
				$banner_clicks = mysqli_num_rows($click_result); 
			}
			
			
			/*
			
			<div class="box_light_grey mb20">
			<div class="tr"><span class="l"></span><span class="r"></span></div>
			<div class="mr">
				<h2 class="mt0 mb10">{custom title given by advertiser} - <a href="#">edit this banner</a></h2>
				<div><img src="images/banners/temp_banner_728x90.jpg" width="728" height="90" /></div>
				<table border="0" cellspacing="10" cellpadding="0">
	
					<tr>
						<td align="right">Number of page views:</td>
						<td><strong>4423</strong></td>
					</tr>
					<tr>
						<td align="right">Number of clicks to date:</td>
						<td><strong>35</strong></td>
					</tr>
					<tr>
						<td align="right">Remaining CFS points:</td>
						<td><strong>65</strong></td>
					</tr>
					<tr>
						<td align="right" valign="top">Location-based display:</td>
						<td><strong>Show when ad is inside 15 miles radius of NW3<br />
						Show location #2<br />
						Show location #3</strong></td>
					</tr>
					<tr>
						<td align="right">Show on the front page:</td>
						<td><strong class="error">No</strong></td>
					</tr>
					<tr>
						<td align="right">Campaign dates:</td>
						<td><strong>15/02/2009</strong> to <strong>15/09/2009 </strong></td>
					</tr>
					<tr>
						<td align="right">Status:</td>
						<td><strong class="green">Active</strong></td>
					</tr>
					<tr>
						<td align="right">Suspend:</td>
						<td><a href="#">Not suspended</a></td>
					</tr>
				</table>				
			</div>
			<div class="br"><span class="l"></span><span class="r"></span></div>
			</div>			
			
			*/
			
			$o .= '<div class="box_light_grey mb20">'."\n";
			$o .= '<div class="tr"><span class="l"></span><span class="r"></span></div>'."\n";
			$o .= '<div class="mr">'."\n";
			
				// Title
				$o .= '<div style="float:right;">';
					
					// Edit action
					$o .= '<a href="advertisers-manage-banner.php?edit='.$banner['banner_id'].'">';
					$o .= 'Edit ad';
					$o .= '</a>';
					$o .= '&nbsp;|&nbsp;';
					
					// Suspend action
					$o .= '<a href="'.$_SERVER['PHP_SELF'].'?suspend='.$banner['banner_id'].'">';
					$o .= ($banner['suspended']? "Un-suspend" : "Suspend");
					$o .= '</a>';
					$o .= '&nbsp;|&nbsp;';
					
					// Delete action
					$o .= '<a href="'.$_SERVER['PHP_SELF'].'?delete='.$banner['banner_id'].'" ';
					$o .= ' onclick="return confirm(\'Are you sure you want to delete this banner?\');"';
					$o .= '>Delete</a>';
					
				$o .= '</div>';			
				$o .= '<h2 class="mt0 mb10">'.$banner['title'].'</h2>'."\n";
				
				// Image
				if ($banner['type'] == "728x90") {
					$o .= '<div><img src="images/banners/'.$banner['filename'].'" width="728" height="90" /></div>'."\n";
				} else {
					$o .= '<div style="float:right; margin-left:10px;"><img src="images/banners/'.$banner['filename'].'" width="120" height="240" /></div>';
				}
				
				$o .= '<table border="0" cellspacing="10" cellpadding="0">'."\n";
				
					/*
					
					// Page views
					$o .= '<tr>'."\n";
					$o .= '<td align="right">Number of page views:</td>'."\n";
					$o .= '<td><strong>???</strong></td>'."\n";
					$o .= '</tr>'."\n";
					
					// Number of clicks to date
					$o .= '<tr>'."\n";
					$o .= '<td align="right">Number of clicks to date:</td>'."\n";
					$o .= '<td><strong>????</strong></td>'."\n";
					$o .= '</tr>'."\n";
					
					*/
					
					// Display
					$o .= '<tr>'."\n";
					$o .= '<td align="right" valign="top">Display:</td>'."\n";	
					$o .= '<td>';

					if ($banner['display'] == 1) {
						$o .= '<strong>Nationwide</strong>';
					} else if ($banner['display'] == 2) {
						$o .= '<strong>Location-specific display</strong>';
						// Load the location-specific display info for this banner
						$query = "SELECT * FROM cf_banners_locations WHERE banner_id = '".$banner['banner_id']."'";
						$loc_result = mysqli_query($GLOBALS['mysql_conn'], $query);
                        $locations = array();
						if (mysqli_num_rows($result)) {
							while ($row = mysqli_fetch_assoc($loc_result)) {
                                $locations[] = array(
                                    (float)$row['latitude'],
                                    (float)$row['longitude'],
                                    (int)$row['radius']
                                );
							}
                            $advertLocations = json_encode($locations);
                            
                            $o .= '<input type="hidden" name="advertLocations" id="advertLocations" value="' . $advertLocations . '"/><div id="locationMap" style="width: 600px; height: 300px; margin: 20px 0;"></div>';
						}
						mysqli_free_result($loc_result);
					}
					$o .= '</td>'."\n";
					$o .= '</tr>'."\n";
						
					// Frontpage?
					$o .= '<tr>'."\n";
					$o .= '<td align="right">Show on the front page:</td>'."\n";
					$o .= '<td><strong>'.($banner['frontpage']? 'Yes':'No').'</strong></td>'."\n";
					$o .= '</tr>'."\n";
					
					// Campaign dates
					$dates = "";
					if ($banner['date_from'] && $banner['date_from'] != "1900-01-01" ) {
						$dates .= 'Start date: <strong>'.$banner['date_from_formatted'].'</strong>';
					}
					if ($banner['date_to'] && $banner['date_to'] != "3000-01-01") {
						if ($dates) { $dates .= '<br/>'; }
						$dates .= 'End date: <strong>'.$banner['date_to_formatted'].'</strong>';	
					}
					if (!$dates) {
						$dates .= '<strong>Contiuous</strong>';
					}
					$o .= '<tr>'."\n";
					$o .= '<td align="right" valign="top">Campaign dates:</td>'."\n";
					$o .= '<td>'.$dates.'</td>'."\n";
					$o .= '</tr>'."\n";
					
					// Banner status
					// Combination of suspended and approved
					$status = "";
					if ($banner['suspended']) {
						// Banner has been suspended by the user
						$status = '<span class="orange"><strong>Suspended</strong></span>';
					} else if (!$banner['approved']) {
						// Banner is "Pending approval"
						$status = '<span class="orange"><strong>Pending Approval</strong></span>';
					} else {
						// Banner is approved and not suspended, i.e. running
						if ($banner['banner_status'] == "Campaign Completed") {
							$status = '<span class="grey"><strong>'.$banner['banner_status'].'</strong></span>';						
						} else {
							$status = '<span class="green"><strong>'.$banner['banner_status'].'</strong></span>';												
						}
					}
					$o .= '<tr>'."\n";
					$o .= '<td align="right">Banner status:</td>'."\n";
					$o .= '<td>'.$status.'</td>'."\n";
					$o .= '</tr>'."\n";

					$o .= '<tr>'."\n";
					$o .= '<td align="right">Page impressions:</td>'."\n";
					$o .= '<td><strong>'.$banner['banner_clicks'].'</strong></td>'."\n";
					$o .= '</tr>'."\n";
					
					$o .= '<tr>'."\n";
					$o .= '<td align="right">Number of clicks:</td>'."\n";
					$o .= '<td><strong>'.$banner_clicks.'</strong></td>'."\n";
					$o .= '</tr>'."\n";										
				
				$o .= '</table>'."\n";	
				
				// For vertical banners, clear the float:right of the banner canvas
				if ($banner['type'] == "120x240") {
					$o .= '<div class="cc0"><!----></div>';
				}
			
			$o .= '</div>'."\n";
			$o .= '<div class="br"><span class="l"></span><span class="r"></span></div>'."\n";
			$o .= '</div>'."\n";
		
		}
	
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Advertisers - Christian Flatshare</title>
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
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" -->
	<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
    <!-- GOOGLE MAPS API v3  -->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
    <script src="scripts/adverts.js"></script>
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
				<h1 class="mt0">Advertisers page</h1>
				<h2 class="mb0">Welcome <span class="green"><?php print $_SESSION['u_name']?></span></h2>
				<p>You currently have <?php print $banner_count?> banners adverts on CFS.<br />New banner adverts are usually approved within 12hrs.</p>
				<?php if ($banner_count) { ?>
				<p class="m0">Use the controls below to view details of or modify the properties of your banners.</p>
				<?php } ?>
			</div>
					
			<div style="float:left; width:239px;">
			
				<?php print $theme['side']; ?>	
			
			</div>
		
			<div class="cc0"><!----></div>
			
		</div>
		
		<?php if ($banner_count == 0) { ?>		
		
		<div style="background-color:#E2E2E2; padding:40px; text-align:center; font-size:16px; font-weight:bold;">
			<a href="advertisers-manage-banner.php"> Click to upload a new banner</a></div>
		
		<?php } else { ?>
		
		<?php print $o?>
		
		<?php } ?>
		
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
