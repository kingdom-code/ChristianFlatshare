<?php

// Autoloader
require_once 'web/global.php';

	$msg = NULL;
	

	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }
	// Get the reply_id, this is for message
	if (isset($_GET['reply_id'])) { $reply_id = $_GET['reply_id']; } else { $reply_id = NULL; }	
	if (isset($_GET['hash'])) { $hash = $_GET['hash']; } else { $hash = NULL; }		
	
	
	// If action == "keep_ad" or action == "remove_ad" we're dealing with a link from an email
	// and we will be affecting a user login
	if ($action == "message" ) {
		// Check hash
		if ($hash != md5($reply_id.'cfsmessage')) {
		   header("Location:index.php"); 			
		}

		
		
		$query = "SELECT to_user_id, to_post_type, to_ad_id, from_user_id
		          FROM cf_email_replies where reply_id = '".$reply_id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
  	$to_user_id = cfs_mysqli_result($result,0,0);			
  	$type = cfs_mysqli_result($result,0,1);			
  	$ad_id = cfs_mysqli_result($result,0,2);			
  	$from_user_id = cfs_mysqli_result($result,0,3);			
		
		$query = "SELECT suppressed_replies, concat_ws(' ',first_name, surname) name 
              from cf_users
 							where user_id = ".$from_user_id;
    $result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
    $scam_reply = cfs_mysqli_result($result,0,0);
    $scam_name = cfs_mysqli_result($result,0,1);
    
		
		// Get ad details for summary
		$query = "SELECT * 
		          FROM cf_".$type." WHERE ".$type."_id = ".$ad_id.";";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);						

	  //	Simply change the "last_login" of the user					
		$query = "update cf_users set last_login = now(), last_updated_date = NOW() where user_id = '".$to_user_id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
		
			 
	   // show threaded reply
	   $query = "
			select e.reply_id,
						 e.message,
						 e.reply_date reply_date,					 
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
						 'LIVE_AD' as `status`,
						 e.to_ad_id as `ad_id`,
						 e.to_post_type as `post_type`,
						 suspended,
						 (CASE u_from.user_id
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ',u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ', u_to.first_name, u_to.surname)
							END) to_name,							
							u_from.first_name,
						  u_from.email_address,
						 e.from_user_id, 
						 e.to_user_id			 					 
			from cf_email_replies as `e`, 
				   cf_email_replies as `e2`, 			
					 cf_users as `u_to`,
					 cf_users as `u_from`,					 
					 cf_offered o
			where 
			    (
						(e.to_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.sender_deleted = 0
				     and e.to_user_id = e2.from_user_id 
						)
					)		
			and e2.reply_id = '".$reply_id."'
			and e.to_ad_id = e2.to_ad_id 								
			and o.offered_id = e.to_ad_id
			and e.to_post_type = 'offered'
			UNION ALL
				select e.reply_id,
						 e.message,
						 e.reply_date reply_date,					 
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
						 'DELETED_AD' as `status`,
						 e.to_ad_id as `ad_id`,
						 e.to_post_type as `post_type`,
						 suspended,
						 (CASE u_from.user_id
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ',u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ', u_to.first_name, u_to.surname)
							END) to_name,							
							u_from.first_name,
						  u_from.email_address,
						 e.from_user_id, 
						 e.to_user_id			 					 
			from cf_email_replies as `e`, 
				   cf_email_replies as `e2`, 			
					 cf_users as `u_to`,
					 cf_users as `u_from`,					 
					 cf_offered_archive o
			where 
			    (
						(e.to_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.sender_deleted = 0
				     and e.to_user_id = e2.from_user_id 
						)
					)		
			and e2.reply_id = '".$reply_id."'
			and e.to_ad_id = e2.to_ad_id 								
			and o.offered_id = e.to_ad_id
			and e.to_post_type = 'offered'			
			UNION ALL
			select e.reply_id,
						 e.message,
						 e.reply_date reply_date,
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
						 'LIVE_AD' as `status`,
						 e.to_ad_id as `ad_id`,
						 e.to_post_type as `post_type`,
						 suspended,
						 (CASE u_from.user_id 
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ', u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ', u_to.first_name, u_to.surname)
							END) to_name,							
						 u_from.first_name,
						 u_from.email_address,
						 e.from_user_id,
						 e.to_user_id
			from cf_email_replies as `e`, 
				   cf_email_replies as `e2`, 						
					 cf_users as `u_to`,
					 cf_users as `u_from`,					 
					 cf_wanted w
			where 
			    (
						(e.to_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 						
						 and e.sender_deleted = 0
				     and e.to_user_id = e2.from_user_id 
						)
					)		
			and e2.reply_id = '".$reply_id."'
			and e.to_ad_id = e2.to_ad_id 
			and w.wanted_id = e.to_ad_id
			and e.to_post_type = 'wanted'
			UNION ALL
						select e.reply_id,
						 e.message,
						 e.reply_date reply_date,
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
						 'DELETED_AD' as `status`,
						 e.to_ad_id as `ad_id`,
						 e.to_post_type as `post_type`,
						 suspended,
						 (CASE u_from.user_id 
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ', u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$to_user_id." THEN 'You'
							ELSE concat_ws(' ', u_to.first_name, u_to.surname)
							END) to_name,							
						 u_from.first_name,
						 u_from.email_address,
						 e.from_user_id,
						 e.to_user_id
			from cf_email_replies as `e`, 
				   cf_email_replies as `e2`, 						
					 cf_users as `u_to`,
					 cf_users as `u_from`,					 
					 cf_wanted_archive w
			where 
			    (
						(e.to_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$to_user_id."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 						
						 and e.sender_deleted = 0
				     and e.to_user_id = e2.from_user_id 
						)
					)		
			and e2.reply_id = '".$reply_id."'
			and e.to_ad_id = e2.to_ad_id 
			and w.wanted_id = e.to_ad_id
			and e.to_post_type = 'wanted'
			order by reply_id desc";
			
    $class = "odd";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$debug .= $query;

		if (mysqli_num_rows($result)) {
			while($reply = mysqli_fetch_assoc($result)) {
				$s .= '<table cellpadding="5" cellspacing="0" width="100%">';		
				$s .= '<tr class="'.$class.'">'."\n";
					$s .= '<td style="padding-top:7px;padding-left:10px;padding-right:0px;padding-bottom:5px;">';
					// Open Title externally
					$s .= '<table cellpadding="0" cellspacing="0" width="100%" style="padding-top:0px;padding-top:0px;"><tr>';
					$s .= '<td><strong>Date: </strong>'.$reply['reply_date_formatted'].'</td>';
					$s .= '</tr></table>';				

				// 	$s .= '<a href="your-account-message-reply.php?reply_id='.$reply['reply_id'].'">Reply</a>';	
					if ($reply['from_user_id'] == "570") { $row_class_start = '<span class="red">'; $row_class_end = '</span>'; } else { $row_class_start = ''; $row_class_end = ''; }			
					$s .= '<strong>From: </strong>'.$row_class_start.$reply['from_name'].$row_class_end.'<br />';													
					
					if ($reply['to_user_id'] == "570") { $row_class_start = '<span class="red">'; $row_class_end = '</span>'; } else { $row_class_start = ''; $row_class_end = ''; }			
					$s .= '<strong>To: </strong>'.$row_class_start.$reply['to_name'].$row_class_end.'<br />';									
					$s .= '</td>';

				$s .= '</tr>';
				$s .= '</table>';
				$s .= '<table cellpadding="5" cellspacing="0" width="100%">';					
				$s .= '<tr class="'.$class.'">'."\n";			
					$s .= '<td style="padding-top:3px;padding-left:10px;padding-right:25px;padding-bottom:0px;">'."\n";							
					
					// To avoid the situtation where a long piece of text such as a url will
					// not wrap, you need to wrap it with a div which has overflow:auto and
					// must specify at least the height or width. In our case, we set a fixed
					// width in order to get the horizontal scroller.
				//	$s .= '<div style="width:560px; overflow:auto;">';		
					$s .= nl2br(makeClickableLinks(stripslashes($reply['message'])));
				//	$s .= '</div>';
					
					
					
					$s .= '</td>'."\n";			
				$s .= '</tr>';							
				
				$s .= '<tr class="'.$class.'">'."\n";
					$s .= '<td style="padding-top:25px;padding-left:10px;padding-right:15px;padding-bottom:9px;">'."\n";
													
					$s .= '<strong>Adverts by '.stripslashes(trim($reply['first_name'])).' currently showing on Christian Flatshare:</strong><br />'."\n";	
					$adsSummary = createSummaryForAllAds($reply['from_user_id'], FALSE);
					if (!$adsSummary) { 
						$s .= 'No adverts showing.'.'<br />'."\n";
					} else {
						$s .= $adsSummary; 
					}
					
					$s .= '</td>'."\n";						
				$s .= '</tr>'."\n";			
				$s .= '</table>';							
				$s .= '<br />';		
								
			} // close while
		} // close if results
	} // END $action == "message"
		
		
		
		
	
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
				
				<p class="error">Sorry, we are unable to process your request<br />It appears that since the link was sent, you have either:</p>
				<ul>
					<li>Changed your email address</li>
					<li>Changed your password</li>
			  </ul>
					<p> To continue managing your advert, please login and go to &quot;Your ads&quot;</p>
					<br /><br />
				
				<?php } else if ($msg == "error") { ?>
				
					<h1 class="mt0">An error occured when updating the database</h1>
					<p class="error">Please contact <?php print TECH_EMAIL?>.<br/>We apologise for the invonvenience.</p>
					
				<?php } else { ?>
     	<h1 class="mt0">Message thread</h1>		
			<strong>Advert: <?php print getAdTitle($ad,$type,TRUE,TRUE)?></strong>
			<br /><br />
      <?php if ($scam_reply == 1) { ?>
       <span class="red"><b>Spam/scam warning</b><br /> "<?= $scam_name?>" has been reported by others. We recommend ignoring or proceeding with caution.</span><br /><br />
      <?php } ?>

			<?php print $s?>		
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
