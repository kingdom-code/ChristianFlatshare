<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

	$msg = NULL;
	
	if (isset($_GET['id'])) { $id = $_GET['id']; } else { $id = NULL; }
	if (isset($_GET['post_type'])) { $post_type = $_GET['post_type']; } else { $post_type = NULL; }
	if (isset($_GET['ad_type'])) { $post_type = $_GET['ad_type']; } 	// backward compatibility; change of ad_type to post_Type	
	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }
	
	// If there action we will be affecting a user login
	if ($action == "keep_ad" || $action == "suspend_ad" || $action == "unsuspend_ad" || $action == "update_expiry" || $action == "send_controls" ) {
		// Ensure we have a valid ad, get user_id & email and do the hash check.
                if ($post_type != "offered" && $post_type != "wanted") { $post_type = "offered"; } // if someone toys with the GET string post_type, set to offered
		$query = "select user_id from cf_".$post_type." where ".$post_type."_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
		// Ok, we have a valid user
		if (mysqli_num_rows($result)) {
			// HASH is the combination of the last_login and email_address
			// If HASH is different it means that either the user did login
			// or that they have changed their email address. In both cases,
			// we do not continue.
			$user_id = cfs_mysqli_result($result,0,0);
			$query = "select * from cf_users where user_id = '".$user_id."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$row = mysqli_fetch_assoc($result);
			$hash = md5($row['password'].$row['email_address']);
			$hash_send_controls = md5($row['password'].$row['email_address'].'send_controls');			
		
			if ($action == "send_controls" ) {			
			 	if ($hash_send_controls == $_GET['hash']) {
					$msg = "PASS";
				} else {$msg = "";}
			}
			else {
				if ($hash == $_GET['hash']) {
					$msg = "PASS";
				} else {$msg = "";}
			}
			
			
			if ($msg != "PASS" ) {
				$msg = "invalid_hash";
			} else {
				// Hash is correct.
				if ($action == "keep_ad") {
					// Unsuspend advert
					$query = "UPDATE cf_".$post_type." 
							  SET suspended = 0, 
							  last_updated_date = NOW() 
							  WHERE ".$post_type."_id = '".$id."'";					
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					
					// Set wanted from / available from to todays date
					$query = "UPDATE cf_".$post_type." 
							  SET available_date = DATE_ADD(NOW(),INTERVAL 2 day),
							  	  expiry_date = DATE_ADD(CURDATE(),INTERVAL 12 day)							  
							  WHERE ".$post_type."_id = '".$id."'
							  AND  available_date < CURDATE()";					
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);					
					// Simply change the "last_login" of the user					
					$query = "update cf_users set last_login = now(), last_updated_date = NOW() where user_id = '".$user_id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						$msg = "last_login_updated";
					} else {
						$msg = "error";
					}				
					
				}					
					
				if ($action == "suspend_ad") {
				    // Update last login date
					$query = "update cf_users set last_login = now(), last_updated_date = NOW() where user_id = '".$user_id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);				
									
					// Simply change the "last_login" of the user
					$query = "update cf_".$post_type." set suspended = 1, last_updated_date = NOW()  where ".$post_type."_id = '".$id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						$msg = "suspend_ad";
					} else {
						$msg = "error";
					}									
				}
				
				if ($action == "unsuspend_ad") {
				    // Update last login date
					$query = "update cf_users set 
							  last_login = NOW(),
							  last_updated_date = NOW()
							  where user_id = '".$user_id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);	
					
					// Set wanted from / available from to todays date
					$query = "UPDATE cf_".$post_type." 
							  SET available_date = DATE_ADD(CURDATE(),INTERVAL 2 day),
							  	  expiry_date = DATE_ADD(CURDATE(),INTERVAL 12 day)
							  WHERE ".$post_type."_id = '".$id."'
							  AND  available_date < CURDATE()";					
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);	
													
					// Unsuspend ad
					$query = "update cf_".$post_type." set suspended = 0, last_updated_date = NOW() where ".$post_type."_id = '".$id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						$msg = "unsuspend_ad";
					} else {
						$msg = "error";
					}									
				}
				
				if ($action == "update_expiry") {
				    // Update last login date
					$query = "update cf_users set last_login = now() where user_id = '".$user_id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);				
					
					// Simply change the "last_login" of the user
					// RD 16-April-2008 changed available_date to SYSDATE+2, so ad look fresh
					$query = "UPDATE cf_".$post_type." SET 
					  		  available_date = DATE_ADD(NOW(),INTERVAL 2 day), 
							  last_updated_date = NOW(), 
							  expiry_date = DATE_ADD(NOW(),INTERVAL 12 day),
							  suspended = 0
							  WHERE ".$post_type."_id = '".$id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						$msg = "update_expiry";
					} else {
						$msg = "error";
					}									
				} // End action == update_expiry
					
				if ($action == "send_controls") {
				// Send email with control actions
				// Send an email to the user about the ad
					if ($post_type == "offered") {
						$query = "
							select 
							date_format(o.expiry_date,'%M %D') as `expiry_date`,
							o.offered_id,
							o.postcode,
							o.bedrooms_available,
							o.bedrooms_total,
							o.accommodation_type,
							o.building_type,
							o.street_name,
							o.published,
							(CASE IFNULL(o.town_chosen,'NULL')
						     WHEN 'NULL' THEN j.town 
						     ELSE o.town_chosen
		  			    	  END) as town,
							u.first_name,
							u.surname,
							u.password,
							u.email_address
							from cf_offered as `o`
							left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 
							left join cf_users as `u` on u.user_id = o.user_id 
							where o.offered_id = '".$id."';	
						";
					} else {
						$query = "
							select 
							date_format(w.expiry_date,'%M %D') as `expiry_date`,
							w.wanted_id,
							w.postcode,
							w.bedrooms_required,
							w.distance_from_postcode,
							w.location,	
							w.published,		
							u.first_name,
							u.surname,
							u.password,
							u.email_address
							from cf_wanted as `w`
							left join cf_users as `u` on u.user_id = w.user_id 
							where w.wanted_id = '".$id."';	
						";		
					} // End post type
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					$ad = mysqli_fetch_assoc($result);
			
					$ad_title   = getAdTitle($ad, $post_type, FALSE);
                                        $ad_url     = getAdURL($ad, $post_type);
                    
					// Create the hash of last_login and email_address
					$hash = md5($ad['password'].$ad['email_address']);
		            
                    // Send Email
                    $CFSMailer = new CFSMailer();
                    
                    // Get Body
                    $body = $twig->render('emails/send_controls.html.twig', array(
                        'first_name' => $ad['first_name'],
                        'advert' => array('title' => $ad_title, 'url' => $ad_url),
                        'keep_ad' => 'http://'.SITE.'email-actions.php?action=keep_ad&post_type='.$post_type.'&id='.$ad[$post_type."_id"].'&hash='.$hash,
                        'suspend_ad' => 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type='.$post_type.'&id='.$ad[$post_type."_id"].'&hash='.$hash,
                    ));
                    
                    // Set variables
                    $subject = 'Christian Flatshare - Advert control links';
                    $to = $ad['email_address'];
                    $bcc = 'info@christianflatshare.org';
                    
                    $msg = $CFSMailer->createMessage($subject, $body, $to);
                    $sent = $CFSMailer->sendMessage($msg);
                    
					if ($sent > 0) {
						$msg = "advert_controls";
					} else {
						$msg = "error";
					}									
				} // End action == send_controls
																
			} // End msg != "PASS"
		} // End if mysqli_num_rows($result)) 
	} // End $action == "keep_ad" || $action == "suspend_ad" || $action == "unsuspend_ad" || $action == "update_expiry" || $action == "send_controls"
	
	
	if (!$msg) {
		header("Location:index.php"); exit;
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Christian Flatshare</title>
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
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			<div id="columnLeft">
				
				<?php if ($msg == "invalid_hash") { ?>
			        <h1 class="mt0 sucess">A problem occurred...</h1>
				<p class="error"><br />We are unable to process this request</p>
				<p>This incident has been automatically reported to CFS.</p>
		          	<br /><br />
 
                   <?php
                       $CFSMailer = new CFSMailer();

                        $body = $twig->render('emails/send_controls.html.twig', array(
                         'first_name' => 'AN ERROR OCCURED - ID='.$id.',  POST_TYPE='.$post_type.',   AD_TYPE='.$post_type.',  ACTION='.$action,
                         'advert' => array('title' => $ad_title, 'url' => $ad_url),
                         'keep_ad' => 'http://'.SITE.'email-actions.php?action=keep_ad&post_type='.$post_type.'&id='.$ad[$post_type."_id"].'&hash='.$hash,
                         'suspend_ad' => 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type='.$post_type.'&id='.$ad[$post_type."_id"].'&hash='.$hash,
                        ));

                       // Set variables
                       $subject = 'Christian Flatshare - ERROR';
                       $to = 'ryanwdavies@gmail.com';

                       $msg = $CFSMailer->createMessage($subject, $body, $to);
                       $sent = $CFSMailer->sendMessage($msg);

                      ?>

	
				<?php } else if ($msg == "last_login_updated") { ?>
				
                                    <h1 class="mt0">Your advert will be kept live</h1>
                                        <p><strong class="success">Your &quot;Last logged in&quot; date has been change to today</strong> </p>
                                        <p><br /><b><?php echo getAdTitleByID($id, $post_type);  ?></b></p>

                                        <p>Logging into Christian Flatshare periodically helps to indicate to others that you are still using Christian  Flatshare, as the number of days since you last logged in is shown on your advert.</p>
                                        <p>Adverts are automatically suspended and assumed no longer needed when a member does not login for over 30 days.<br /><br /></p>
                                <!--    <p>You will receive a further &quot;keep or suspend&quot; email if you do not login for 20 days (if you have live adverts then).<br /></p> -->

					
				    <?php } else if ($msg == "suspend_ad") { ?>
				
					<h1 class="mt0 sucess">Your advert has been suspended</h1>
                                        <p><br /><b><?php echo getAdTitleByID($id, $post_type);  ?></b></p>
					<p>You can un-suspend, edit or delete your advert at anytime.</p><br /><br /><br />				
				
	
		
				    <?php } else if ($msg == "advert_controls") { ?>
				
					<h1 class="mt0 sucess">An email has been sent</h1>
					<p class="success"><strong>An email has been sent with a link to suspend your advert</strong></p>
					<p>You can un-suspend, edit or delete you advert by logging in and going to the "Your ads" page</p><br /><br /><br />				

				    <?php } else if ($msg == "unsuspend_ad") { ?>
				
					<h1 class="mt0 sucess">Your advert has been unsuspended</h1>
                                        <p><br /><b><?php echo getAdTitleByID($id, $post_type);  ?></b></p>
					<p>You can suspend, edit or delete you advert at anytime when logged in.</p><br /><br /><br />
					
				    <?php } else if ($msg == "update_expiry") { 
					if ($post_type = "offered" ) { ?>
				        <h1 class="mt0 sucess">Your advert has been updated</h1>
                                       <p><br /><b><?php echo getAdTitleByID($id, $post_type);  ?></b></p>
				       <p class="success"><strong>Your advert's available from date is now today</strong></p>
					<p>This helps to indicate that your accommodation is still available. You can edit or delete your advert anytime when logged in.</p><br /><br />						
					<?php } else if ($post_type = "wanted" ) { ?>
				        <h1 class="mt0 sucess">Your advert has been updated</h1>
					    <p class="success"><strong>Your advert's wanted from date is now today</strong></p>
					<p>This help to indicate that you are still looking for accommodation. You can edit or delete your advert anytime whtn logged in.</p><br /><br />											
						<?php } ?>			
				<?php } else if ($msg == "error") { ?>
				
					<h1 class="mt0">An error occured when updating the database</h1>
					<p class="error">Please contact <?php print TECH_EMAIL?>.<br/>We apologise for the invonvenience.</p>
					
				<?php } ?>
		<p class="mb0"><a href="index.php">Return to the welcome page</a></p>

			</div>
			<div id="columnRight">
				
				<?php if (!$_SESSION['u_id']) { ?>
					<div class="box_grey mb10">
						<div class="tr"><span class="l"></span><span class="r"></span></div>
						<div class="mr">
						<h2 class="m0">Member Login</h2>
						<?php print createLoginForm($email,$password,$remember)?>
						</div>
						<div class="br"><span class="l"></span><span class="r"></span></div>
					</div>				
				<?php } else { ?>
					<?php print $theme['side']; ?>
				<?php } ?>

			</div>
			<div class="cc0"><!----></div>
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
