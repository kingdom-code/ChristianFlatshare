<?php
ini_set("session.gc_maxlifetime","3600");
session_start();

// Autoloader
require_once 'web/global.php';

connectToDB();

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	//	Initialise needed vars
    $debug  = NULL;
	$msg									= "";
	$post_type 							= init_var("post_type","GET");
	$delete 							= init_var("delete","GET");
	$delete_confirm 			= init_var("delete_confirm","GET");
	$accommodation_type 	= init_var("accommodation_type","GET");
	
	// Various flags, set from if / else statemets to choose which screens to show
	// by default, all of them are set to FALSE.
	$show_accommodation_types = FALSE;
	$show_previous_ads = FALSE;
	$show_delete_confirmation = FALSE;
	
	// First step, if $post_type is defined, user has picked a type
	// Check if an existing ad is in place
	if ($post_type) {
	
		$query = "
		
			SELECT
			*,
			DATE_FORMAT(created_date,'%d %M %Y at %k:%i') as `formatted_date`
			FROM cf_".$post_type." 
			WHERE user_id = '".$_SESSION['u_id']."' 
			AND published = '2';
			
		";
			
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
		
			// Get details for the current unfinished ad 
			$ad = mysqli_fetch_assoc($result);

			if ($delete == $ad[$post_type."_id"]) {
			
				// Show a confirmation box about deleting this ad
				$show_delete_confirmation = TRUE;
				
			} else if ($delete_confirm == $ad[$post_type."_id"]) {

				// User has confirmed that he / she wants to delete this ad
				$query = "
					DELETE from cf_".$post_type." 
					WHERE published = '2'
					AND   user_id = ".$_SESSION['u_id'];
				$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die($query);
				if (!$result) {
					header("Location: ".$_SERVER['PHP_SELF']."?post_type=".$post_type."&msg=delete_error");
					exit;					
				} else {
					header("Location: ".$_SERVER['PHP_SELF']."?post_type=".$post_type."&msg=delete_ok");
					exit;				
				}						
			
			} else {
			
				// Show the previous ad information and ask the user how he / she wants to proceed
				$show_previous_ads = TRUE;	
				
			}			
			
		} else {

			// NO PREVIOUS ADS EXIST
			// For wanted create new ad and redirec to the ad-wanted.php age to start editing
			if ($post_type == "wanted") {
			
				// Create a new entry on the database
				$query = "
					INSERT INTO cf_wanted
						(user_id,published,created_date) 
					VALUES
						('".$_SESSION['u_id']."','2',now());
				";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if ($result) {
					
					// Redirect to the post-wanted.php page to start editing this ad.
					header("Location: post-wanted.php?id=".mysqli_insert_id($GLOBALS['mysql_conn']));
					exit;				
				
				} else {
				
					$msg  = '<p class="error">An error occured when creating a new ad.</p>';
					$msg .= '<p>We apologise for the inconvience. Please <a href="contact-us.php">contact Christian Flatshare</a>.</p>';
								
				}
				
			}
			
			// For offered we need to show the accommodation type
			if (empty($accommodation_type)) {
			
				$show_accommodation_types = TRUE;
			
			}
            else if ( $accommodation_type == "flat share" || $accommodation_type == "room share" || $accommodation_type == "family share" || $accommodation_type == "whole place") {
			
				// Create a new entry on the database
				if ( $accommodation_type == "room share" ) {
					$query = "
						INSERT INTO cf_offered
							(user_id,published,created_date,accommodation_type, building_type, room_share, bedrooms_available, bedrooms_double ) 
						VALUES
							('".$_SESSION['u_id']."','2',now(),'flat share', 'house', '1', '1', '1');
					";
          } else if ( $accommodation_type == "whole place" ) {
          // whole place
          $query = "
            INSERT INTO cf_offered
              (user_id,published,created_date,accommodation_type, building_type, paid_for, approved)
            VALUES
              ('".$_SESSION['u_id']."','2',now(),'$accommodation_type', 'house', 1, 0);
          ";
					} else {
					// flat share, family share
					$query = "
						INSERT INTO cf_offered
							(user_id,published,created_date,accommodation_type, building_type) 
						VALUES
							('".$_SESSION['u_id']."','2',now(),'$accommodation_type', 'house');
					";			
					}		
                                $debug .= debugEvent('Insert query',$query);
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if ($result) {
					// Redirect to the post-offered.php page to start editing this ad.
					header("Location: post-offered.php?id=".mysqli_insert_id($GLOBALS['mysql_conn']));
					exit;
		
				} else {
		
					$msg  = '<p class="error">An error occured when creating a new ad.</p>';
					$msg .= '<p>We apologise for the inconvience. Please <a href="contact-us.php">contact Christian Flatshare</a>.</p>';
						
				}
				
			}	
			
		} 
		
	}


	$debug .= debugEvent('Session vars:',print_r($_SESSION,true));
	
	// If we're showing a message (i.e. deletion ok)
	if (isset($_GET['msg'])) {
		if ($_GET['msg'] == "delete_ok") {
			$msg = '<p class="success">Old advert deleted succesfully</p>';	
		} else if ($_GET['msg'] == "delete_error") {
			$msg = '<p class="error">An error occured when deleting your incomplete ad. Please contact us to resolve</p>';
		}
	}
	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Choose ad type - Christian Flatshare</title>
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
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			<?php if ($show_delete_confirmation || $show_previous_ads) { ?>
				<h2 class="mb0 orange"><strong>You have a partially completed advert</strong></h2>			
			<?php } else { ?> 
				<h1 class="mb0">Post a new advert</h1>
		  <?php } ?> 		
		
			
			<?php print $msg?>
			
			<?php if (!$post_type) { ?>
			<h2 class="m0 mt20 mb10" align="center">Which type of advert would you like to post?</h2>
			
			<table align="center" border="0" cellspacing="0" cellpadding="15" id="post_type">
				<tr>
					<td width="250px" align="center" ><h2 class="m0"><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=offered">Offered Accommodation ad </a></h2>
					  <p class="m0">You have accommodation to offer</p></td>
	
					<td width="250px" align="center"><h2 class="m0"><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=wanted">Wanted Accommodation ad</a></h2>
					  <p class="m0">You are looking for accommodation</p></td>
			
				</tr>
			</table>
			<br />
			<br />
			
			<?php } ?>

			<?php if ($show_accommodation_types) { ?>
			
			<p class="mb20"><strong>Posting an Offered Accommodation ad. </strong><br/>
			Please choose one of the following  Offered Accommodation advert types: </p>
			<ul>
			
				<li><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=offered&amp;accommodation_type=whole place"><strong>Whole Place</strong></a><br/>
				An <b>unoccupied</b> house or flat. <br />
				Note: If you have an unoccupied property which you would like to let, and would consider <br />
				letting to individuals separately, (as in house/flat sharers), you should chose &quot;Whole Place&quot; and<br />
				later tick the option &quot;<i>let bedrooms individually</i>&quot;.</li>			
				
				<li class="mb10 mt20"><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=offered&amp;accommodation_type=flat share"><strong>House or Flatshare</strong></a><br/>
				A house or a flat shared with others.</li>
				
				<li class="mb10"><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=offered&amp;accommodation_type=room share"><strong>Room Share</strong></a><br/>
				A room shared with someone of the same sex.</li>
									
				<li class="mb10"><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=offered&amp;accommodation_type=family share"><strong>Family Share</strong></a><br/>
				Accommodation shared with a family with children, or lodging with a married couple. </li>
				
		
			</ul>
			<br />
			
			<?php } ?>
		
			<?php if ($show_previous_ads) { ?>
			

			<?php if ($post_type != "wanted") { ?>
				<p class="mt5 mb20">You started to create a &quot;<strong><?php
							  	if ($ad['accommodation_type']=="whole place") { echo "Whole Place"; }
							elseif ($ad['accommodation_type']=="flat share" && $ad['room_share'] != "1" ) { echo "House or Flatshare"; }
							elseif ($ad['accommodation_type']=="flat share" && $ad['room_share'] == "1" ) { echo "Room Share"; }						
							elseif ($ad['accommodation_type']=="family share") { echo "Family Share"; } ?>&quot;</strong> advert on <strong><?php print $ad['formatted_date']?></strong>.</p>
							
			<div class="message_box" style="width:280px;">
				<p><strong>What would you like to do?</strong></p>
			        <p><br /><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=<?php print $post_type ?>&delete=<?php print $ad[$post_type.'_id']?>">Delete and start again</a>
					&nbsp;or&nbsp;
					<a href="post-<?php print $post_type?>.php?id=<?php print $ad[$post_type.'_id']?>">Continue with this Ad</a>
                                </p>
			</div>							
							<br /><br /><br /><br />
			<?php } else { ?>
				<p class="mt5 mb20">You started to create an advert on <strong><?php print $ad['formatted_date']?></strong>.</p>
			<div class="message_box" style="width:280px;">
				<p><strong>What would you like to do?</strong></p>
				<p><br /><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=<?php print $post_type?>&delete=<?php print $ad[$post_type.'_id']?>">Delete and start again</a>
					&nbsp;or&nbsp;
					<a href="post-<?php print $post_type?>.php?id=<?php print $ad[$post_type.'_id']?>">Continue with this Ad</a></p>
			</div>			
							<br /><br /><br /><br />
			<?php } ?>

			<?php } ?>	
			
			<?php if ($show_delete_confirmation) { ?>
                          <?php if ($post_type != "wanted") { ?>
                                <p class="mt5 mb20">You started to create a &quot;<strong><?php
                                                                if ($ad['accommodation_type']=="whole place") { echo "Whole Place"; }
                                                        elseif ($ad['accommodation_type']=="flat share" && $ad['room_share'] != "1" ) { echo "House or Flatshare"; }
                                                        elseif ($ad['accommodation_type']=="flat share" && $ad['room_share'] == "1" ) { echo "Room Share"; }
                                                        elseif ($ad['accommodation_type']=="family share") { echo "Family Share"; } ?>&quot;</strong> advert on <strong><?php print $ad['formatted_date']?></strong>.</p>
                            <?php } else { ?>
                           <p class="mt5 mb20">You started to create an advert on <strong><?php print $ad['formatted_date']?></strong>.</p>
                                   <?php } ?>
			    <div class="message_box" style="width:280px;">
					<p class="mb0"><strong>Please confirm, delete ad?</strong></p>
				<p><br /><a href="<?php print $_SERVER['PHP_SELF']?>?post_type=<?php print $post_type?>&delete_confirm=<?php print $ad[$post_type.'_id']?>">Delete partially completed</a>&nbsp;&nbsp;or&nbsp;&nbsp;<a href="<?php print $_SERVER['PHP_SELF']?>?post_type=<?php print $post_type?>">Cancel</a></p>
			</div>
			<br /><br /><br /><br />
			
			<?php } ?>		

			<p><a href="your-account-manage-posts.php">Cancel and return to Your ads</a></p>
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
