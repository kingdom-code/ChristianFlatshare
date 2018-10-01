<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

require('includes/class.pager.php');		// Pager class

connectToDB();

	$remove_msg = "";
	
		
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	if (isset($_REQUEST['start'])) { 
		$start = $_REQUEST['start']; 
	} elseif (isset($_GET['start'])) { 
		$start = $_GET['start']; 
	} else { 
		$start = 0; 
	}
	if (isset($_REQUEST['sortNum'])) { $sortNum = $_REQUEST['sortNum']; } else { $sortNum = 10; }
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'reply_date'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }
	
	if (isset($_GET['reply_id'])) { $reply_id = $_GET['reply_id']; } else { $reply_id = NULL; }
	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }	
	if (isset($_GET['post_type'])) { $post_type = $_GET['post_type']; } else { $post_type = NULL; }	
	if (isset($_GET['ad_id'])) { $ad_id = $_GET['ad_id']; } else { $ad_id = NULL; }			
	
	switch($action) {
		case "delete":
			$remove_msg = '<div  class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:280px;">
			<p class="mb0 mt0 style5">Please confirm message deletion</p>
			Delete message?<br /><br /><a href="'.$_SERVER['PHP_SELF'].'?action=delete_confirmed&reply_id='.$reply_id.'&start='.$start.'">Delete</a>&nbsp;&nbsp;or&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?start='.$start.'">Cancel</a></div><br />';
					
/*			$remove_msg = '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:280px;">
			<p class="mb0 mt0 style5">Please confirm</p>
			<p class="mb0 mt0">Delete message?&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?action=delete_confirmed&reply_id='.$reply_id.'&start='.$start.'">Delete</a>&nbsp;or&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?start='.$start.'">Cancel</a></p></div><br />'; */
			$start = 0;			
			break;
	
		case "delete_confirmed":
			$query = "update cf_email_replies 
								set recipient_deleted = 1 
								where reply_id = ".$reply_id." 
								and to_user_id = ".$_SESSION['u_id'];
			$debug .= debugEvent("case delete_confirmed:",$query);	
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				header('Location:../your-account-received-messages.php?start='.$start.'&action=deleted'); exit;
			} else {
				$remove_msg = '<p class="error"><strong>There was a problem deleting this message.</strong></p>';
			}
			break;
			
		case "deleted":
				$remove_msg = '<p class="green"><strong>The message was deleted.</strong></p>';
			break;
		case "forward":
			$query = "select email_address 
								from cf_users
			          where user_id = ".$_SESSION['u_id'];
      $debug .= debugEvent("case forward:",$query);	
			$results = mysqli_query($GLOBALS['mysql_conn'], $query);
			$row = mysqli_fetch_assoc($results);
			
			$remove_msg = '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:300px;">
			<p class="mb0 mt0 style5">Please confirm message forward</p>
			<p class="mb0 mt0">Forward this message to '.$row['email_address'].'?</p>
			<p class="mb0 mt0" align="left">
			<br /> 
			<a href="'.$_SERVER['PHP_SELF'].'?action=forward_confirmed&reply_id='.$reply_id.'&start='.$start.'">Forward</a>&nbsp;or&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?start='.$start.'">Cancel</a>
			</p>
			</div>
			<br />';
			$start = 0;			
			break;
		case "forward_confirmed":
			$query = "select u_to.email_address as email_address, 
											 concat_ws(' ', u_from.first_name, u_from.surname) as name,
											 e.to_post_type as post_type, 
											 e.to_ad_id as ad_id, 
											 e.message,
											 e.from_user_id as from_user_id
								from cf_users u_to, 
									 	 cf_users u_from, 
										 cf_email_replies e
								where u_from.user_id = e.from_user_id
								and	  u_to.user_id = e.to_user_id
								and   e.reply_id = ".$reply_id."
								";
      $debug .= debugEvent("case formward confirmed:",$query);	
			$results = mysqli_query($GLOBALS['mysql_conn'], $query);
			$row = mysqli_fetch_assoc($results);
			
			$query = "select email_address, 
											 concat_ws(' ', first_name, surname) as name
								from cf_users 
								where user_id = ".$_SESSION['u_id'];
      $debug .= debugEvent("Basic select:",$query);	
			$results = mysqli_query($GLOBALS['mysql_conn'], $query);
			$reply_owner = mysqli_fetch_assoc($results);			
		    
            
            $ad_title = getAdTitleByID($row['ad_id'], $row['post_type'], FALSE);
            
            // Send Email
            $CFSMailer = new CFSMailer();
        
            // Get Body
            $body = $twig->render('emails/forward.html.twig', array(
                'name' => $row['name'],
                'advert' => array('title' => $ad_title, 'url' => 'http://' . SITE . 'details.php?id=' . $row['ad_id'] . '&post_type=' . $row['post_type']),
                'message' => $row['message'],
                'from_ads' => createSummaryForAllAds($row['from_user_id'], FALSE, TRUE),
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Forward: ' . $ad_title;
            $to = $reply_owner['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to);
            $sent = $CFSMailer->sendMessage($msg);
            
			if ($sent > 0) {
				$showForm = FALSE; // don't show form
				$msg = '<p class="success">The message has been forwarded to ' . $reply_owner['email_address'] . '.</p>'."\n";
			}
            else {
                $msg = '<p class="error">There was a problem forwarding your reply. Please report to Christian Flatshare.</p>'."\n";
			} 
	
			break;			
	}
 
	// Get account suspended status, TRUE or FALSE
	$supended_account = accountSuspended();
	// Count query
	$query = "SELECT e.reply_id
						FROM cf_email_replies as `e`, 
						     cf_users as `u_from`,						 
						     cf_offered o
						WHERE e.to_user_id = '".$_SESSION['u_id']."'
						AND	 	o.offered_id = e.to_ad_id
				    AND   u_from.user_id = e.from_user_id			
						AND   e.to_post_type = 'offered'
						AND	 	e.recipient_deleted = 0	";
  if ($action == "ad_replies") { $query .= " and o.offered_id = ".$ad_id." "; }						
		$query .= "	UNION ALL						
						SELECT e.reply_id
						FROM cf_email_replies as `e`, 
						     cf_users as `u_from`,						 
						     cf_offered_archive o
						WHERE e.to_user_id = '".$_SESSION['u_id']."'
						AND	 	o.offered_id = e.to_ad_id
				    AND   u_from.user_id = e.from_user_id			
						AND   e.to_post_type = 'offered'
						AND	 	e.recipient_deleted = 0							
						";						
//						and     (e.suppressed_replies = 0 
//			 			        and u_from.suppressed_replies = 0
//			          or  (u_from.suppressed_replies = 1
//      			         and e.from_user_id =  '".$_SESSION['u_id']."'))

  if ($action == "ad_replies") { $query .= " and o.offered_id = ".$ad_id." "; }
			// e - email sent not blocked, 
			// u - user sent to is a scammer
	  $query .= " UNION ALL
						SELECT e.reply_id
						FROM cf_email_replies as `e`, 
				 	  		 cf_users as `u_from`,						
				    	   cf_wanted w
						WHERE e.to_user_id = '".$_SESSION['u_id']."'
						AND 	w.wanted_id = e.to_ad_id
				    AND   u_from.user_id = e.from_user_id		
						AND   e.to_post_type = 'wanted'													
						AND	 	e.recipient_deleted = 0	";
  if ($action == "ad_replies") { $query .= " and w.wanted_id = ".$ad_id." "; }							
  $query .= " UNION ALL
						SELECT e.reply_id
						FROM cf_email_replies as `e`, 
				 	  		 cf_users as `u_from`,						
				    	   cf_wanted_archive w
						WHERE e.to_user_id = '".$_SESSION['u_id']."'
						AND 	w.wanted_id = e.to_ad_id
				    AND   u_from.user_id = e.from_user_id		
						AND   e.to_post_type = 'wanted'													
						AND	 	e.recipient_deleted = 0							
												";
//						and     (e.suppressed_replies = 0 
//			  			       and u_from.suppressed_replies = 0
//			          or  (u_from.suppressed_replies = 1
//     			         and e.from_user_id =  '".$_SESSION['u_id']."'))

  if ($action == "ad_replies") { $query .= " and w.wanted_id = ".$ad_id." "; }						
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
  $debug .= debugEvent("Account status SQL:",$query);	
	$count = mysqli_num_rows($result);
	 	 
	// Create a pager for the data
	$pagerLink = $_SERVER['PHP_SELF']."?orderBy=".$orderBy."&direction=".$direction;
	$pager = new Pager($count,$start,$sortNum,$pagerLink);			
	
	// Load all the "Saved ads" for this user
	$s = "";
	if ($action == "ad_replies") {
		$msg .= 'Showing replies to your advert:<br /><p class="mt0 mb10"><strong>'.getAdTitleByID($ad_id,$post_type,TRUE, FALSE, TRUE).'</strong></p>';
	}
	
	// Create array of replies
	$query = "
			SELECT e.reply_id,
						 e.message,
						 e.reply_date reply_date,					 
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
						 'LIVE_AD' as `status`,
						 to_ad_id as `ad_id`,
						 to_post_type as `post_type`,
						 suspended,
						 concat_ws(' ',u.first_name, u.surname) name, 
						 u.first_name,
						 u.email_address,
						 from_user_id,
						 u2.suppressed_replies as `scam_reply`
			FROM cf_email_replies e, 
					 cf_users u,
					 cf_users u2,							 
					 cf_offered o
			WHERE e.to_user_id = '".$_SESSION['u_id']."'
			AND   o.offered_id = e.to_ad_id					
			AND   u.user_id = e.from_user_id
		  AND   u2.user_id = e.from_user_id			
			AND   e.to_post_type = 'offered'
			AND   e.recipient_deleted = 0 " ;
		if ($action == "delete" || $action == "forward") { $query .= " and reply_id = ".$reply_id." "; }			
	  if ($action == "ad_replies") { $query .= " and o.offered_id = ".$ad_id." "; }									
			$query .= " UNION ALL
			SELECT e.reply_id,
						 e.message,
						 e.reply_date reply_date,					 
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
						 'DELETED_AD' as `status`,
						 to_ad_id as `ad_id`,
						 to_post_type as `post_type`,
						 suspended,
						 concat_ws(' ',u.first_name, u.surname) name, 
						 u.first_name,
						 u.email_address,
						 from_user_id,
						 u2.suppressed_replies as `scam_reply`
			FROM cf_email_replies e, 
					 cf_users u,
					 cf_users u2,							 
					 cf_offered_archive o
			WHERE e.to_user_id = '".$_SESSION['u_id']."'
			AND   o.offered_id = e.to_ad_id					
			AND   u.user_id = e.from_user_id
		  AND   u2.user_id = e.from_user_id			
			AND   e.to_post_type = 'offered'
			AND   e.recipient_deleted = 0			
			";
//			and     (e.suppressed_replies = 0 
//			         and u.suppressed_replies = 0
//          or  (u.suppressed_replies = 1
//               and e.from_user_id =  '".$_SESSION['u_id']."'))
	
		if ($action == "delete" || $action == "forward") { $query .= " and reply_id = ".$reply_id." "; }					
	  if ($action == "ad_replies") { $query .= " and o.offered_id = ".$ad_id." "; }								
			$query .= " UNION ALL
			SELECT e.reply_id,
						 e.message,
						 e.reply_date reply_date,
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
 						 'LIVE_AD' as `status`,
						 to_ad_id as `ad_id`,
						 to_post_type as `post_type`,
						 suspended,
						 concat_ws(' ',u.first_name, u.surname) name,
						 u.first_name,
						 u.email_address,
						 from_user_id,
						 u2.suppressed_replies as `scam_reply`
			FROM cf_email_replies e, 
					 cf_users u,
					 cf_users u2,					 
					 cf_wanted w
			WHERE e.to_user_id = '".$_SESSION['u_id']."'
			AND   w.wanted_id = e.to_ad_id					
			AND   u.user_id = e.from_user_id
		  AND   u2.user_id = e.from_user_id			
			AND   e.to_post_type = 'wanted'			
			AND   e.recipient_deleted = 0 ";
	  if ($action == "delete" || $action == "forward") { $query .= " and reply_id = ".$reply_id." "; }					
	  if ($action == "ad_replies") { $query .= " and w.wanted_id = ".$ad_id." "; }			
		 $query .= "  UNION ALL
			SELECT e.reply_id,
						 e.message,
						 e.reply_date reply_date,
						 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
             'DELETED_AD' as `status`,
						 to_ad_id as `ad_id`,
						 to_post_type as `post_type`,
						 suspended,
						 concat_ws(' ',u.first_name, u.surname) name,
						 u.first_name,
						 u.email_address,
						 from_user_id,
						 u2.suppressed_replies as `scam_reply`
			FROM cf_email_replies e, 
					 cf_users u,
					 cf_users u2,					 
					 cf_wanted_archive w
			WHERE e.to_user_id = '".$_SESSION['u_id']."'
			AND   w.wanted_id = e.to_ad_id					
			AND   u.user_id = e.from_user_id
		  AND   u2.user_id = e.from_user_id			
			AND   e.to_post_type = 'wanted'			
			AND   e.recipient_deleted = 0			
				";			
//			and     (e.suppressed_replies = 0 
//			         and u.suppressed_replies = 0
//          or  (u.suppressed_replies = 1
//               and e.from_user_id =  '".$_SESSION['u_id']."'))

			if ($action == "delete" || $action == "forward") { $query .= " and reply_id = ".$reply_id." "; }					
		  if ($action == "ad_replies") { $query .= " and w.wanted_id = ".$ad_id." "; }									
			$query .= "	order by ".$orderBy." ".$direction." limit ".$start.", ".$sortNum;
			
    $class = "odd";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    $debug .= debugEvent("Main content SQL:",$query);	
		if (mysqli_num_rows($result)) {
		while($reply = mysqli_fetch_assoc($result)) {
			$s .= '<table cellpadding="5" cellspacing="0" width="100%">';			
			$s .= '<tr class="'.$class.'">'."\n";
			$s .= '<td style="padding-top:7px;padding-left:10px;padding-right:10px;padding-bottom:5px;">';
					
			// Open Title externally
			$s .= '<strong>Date: </strong>'.$reply['reply_date_formatted'].'<br />';				
			if ($reply['from_user_id'] == $_SESSION['u_id']) {
				$s .= '<strong>From: </strong>You<br />';								
			} else {
//				$s .= '<strong>From: </strong>'.$reply['name'].'<br />';								
//				$s .= '<strong>From: </strong><a href="mailto:'.$reply['email_address'].'?subject=Re: '.getAdTitleByID($reply['ad_id'],$reply['post_type'], FALSE).'">'.$reply['name'].'</a><br />'."\n";	
					if ($reply['from_user_id'] == "570") { $row_class_start = '<span class="red">'; $row_class_end = '</span>'; } else { $row_class_start = ''; $row_class_end = ''; }			
					$s .= '<strong>From: </strong>'.$row_class_start.$reply['name'].$row_class_end.'<br />';													
		//		$s .= '<strong>From: </strong>'.$reply['name'].'</strong><br />'."\n";					
//				$s .= '<strong>From: </strong>'.$reply['name'].'<br />'."\n";					
			}								
			$s .= '<strong>To: </strong> You<br />';															
			$s .= '</td>';
			
			
			$s .= '<td align="right" valign="top" style="padding-top:7px;padding-left:10px;padding-right:10px">';
			if ($action != "delete" && $action != "forward") {
				$s .= '<a href="your-account-received-messages.php?action=forward&reply_id='.$reply['reply_id'].'&start='.$start.'">Forward</a> | <a href="your-account-received-messages.php?action=delete&reply_id='.$reply['reply_id'].'&start='.$start.'">Delete</a> | <a href="your-account-message-reply.php?action=thread&reply_id='.$reply['reply_id'].'">Thread</a> | ';
				if ($reply['from_user_id'] == $_SESSION['u_id']) {
					$s .= '<span class="grey">From you</span>'; 
				} elseif ($supended_account) {
					$s .= '<span class="grey">Account suspended</span>'; 				
				} else {
					$s .= '<a href="your-account-message-reply.php?reply_id='.$reply['reply_id'].'">Reply</a>';
				}
				$s .= '<br />';				
			} // If action delete / forward
			$s .= '</td>';			
			
			$s .= '</tr>';			
			$s .= '</table>';			
			
			$s .= '<table cellpadding="5" cellspacing="0" width="100%">';					
			$s .= '<tr class="'.$class.'">'."\n";			
			$s .= '<td style="padding-top:0px;padding-left:10px;padding-right:10px;padding-bottom:5px;">'."\n";			
			$s .= '<strong>Subject:</strong> <strong>'.getAdTitleByID($reply['ad_id'],$reply['post_type'],TRUE, FALSE, TRUE).'</strong><br />';
			if ($reply['status'] == 'DELETED_AD') {
				$s .= '<span class="red">(this advert has been deleted)</span>';
			} else if ($reply['scam_reply'] == 1 && $reply['post_type'] == 'offered') {
				$s .= '</span><span class="red"></span>';
			} else if ($reply['suspended']) {
				$s .= '</span><span style="color:#FF9900;">(this ad is suspended)</span>';
			}						
			$s .= '</td>';
			$s .= '</tr>';				
				
			$s .= '<tr class="'.$class.'">'."\n";			
			$s .= '<td style="padding-top:3px;padding-left:10px;padding-right:25px;padding-bottom:0px;">'."\n";							 

                       if ($reply['scam_reply'] == 1)
			{
 			 // If FROM_USER_ID is scammer inlucde message
			 if ($reply['post_type'] == 'wanted') {
			  $s .= '<span class="red"><b>Scam correspondant</b>: this person has been associated with sending scam advert details, please ignore.</span><br /><br />'."\n";			 
			  } else {
			  $s .= '<span class="red"><b>Spam/scam warning</b><br /> "'.$reply['name'].'" has been reported by others. We recommend ignoring or caution.</span><br /><br />'."\n";
			 }
			} // if scam reply

			$message = str_replace("#\\#", "#\#", $reply['message']);
			$s .= nl2br(makeClickableLinks(stripslashes($message)));						
	//		$s .= nl2br(makeClickableLinks(stripslashes($reply['message'])));
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

                        // Get mututal fb friends
                  //      $mutualFriends = NULL;
                  //      $mutualFriends = $CFSFacebook->getMutualFriends($_SESSION['u_id'], $reply['from_user_id']);
                  //      $s .= $twig->render('mutualFriendsMsg.html.twig', array('friends' => $mutualFriends));

			$s .= '</td>'."\n";						
			$s .= '</tr>'."\n";			
			$s .= '</table>';							
			$s .= '<br />';							 			
		}
	} 

	// Set the flag for the weclome message to TRUE
	if (!$s && $count == 0) {
		$welcome_msg = TRUE;
		if ($action == "ad_replies") {
			$s .= '<p>You have no messages in reply to this advert.</p>';
		} else {
			$s .= '<p>You have no messages.</p>';
		}		
	} elseif (!$s && $count > 0) {
	  $start = $start - 10;
		header('Location:../your-account-received-messages.php?start='.$start.'&action=deleted'); exit;		
	} else {
		$welcome_msg = FALSE;	
		$temp .= '<table cellpadding="5" cellspacing="0"  width="100%">';
		$temp .= $s;
		$temp .= '</table>';
		$s = $temp;
		unset($temp);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your messages - Christian Flatshare</title>
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
<style type="text/css">
<!--
.style1 {color: #000000}
.style5 {
	font-size: 14px;
	font-weight: bold;
}
-->
</style>
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
		<div class="cl" id="cl">
		<h1 class="mt0">Your messages</h1>
			<!--<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr><td>
					 
					</td>
					<td align="right">
						<span class="grey">Please <a href="contact-us.php"  target="_blank">report any problems</a> with CFS so<br /> 
					  that we can fix them promptly. Thank you. 
						</td>
			</tr>
			</table> -->
			
			<div class="clear"><!----></div>
			<?php print $msg?>
			<?php print $remove_msg?>
			<?php if (!$welcome_msg && $action != "delete" && $action != "forward") { ?>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
   		<tr>
        <td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?> </strong> messages.</td>
		    <td align="right"><?php print $pager->createLinks()?></td>
    	</tr>
			</table>	
			<?php } ?>
			<?php print $s?>		
			<?php if (!$welcome_msg && $count > 10 && $action != "delete" && $action != "forward") { ?>			
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
   		<tr>
        <td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?> </strong> messages.</td>
    		<td align="right"><?php print $pager->createLinks()?></td>  
    	</tr>
			</table>
			<?php } ?>						
			<form name="updateForm" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
			</form>
			<?php if ($welcome_msg && $action != "ad_replies") { ?>
		  <div class="mt10" style="width:330px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
				<h2 class="mt0 mb5">Your messages</h2>
	      <p class="mt0 mb0" align="left">Messages you have received from other members are shown here. </p>
		  </div>
		    <?php } ?>				
		  </p>
		</div>
		<div class="cs" style="width:20px; height:270px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
			
			<?php if (rand(1,100) > 65) { ?>
					<?php print  sharingCFS() ?>
			<?php } else { ?>

					<!-- 200x200 image-->			
					<script type="text/javascript"><!--
					google_ad_client = "pub-3776682804513044";
					/* 250x250 image only, created 12/04/09 */
					google_ad_slot = "9681363594";
					google_ad_width = 250;
					google_ad_height = 250;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>
			<?php } ?>
					
					<p class="mt0 mb0">&nbsp;</p>
					<!-- 200x200 image-->			
					<script type="text/javascript"><!--
					google_ad_client = "pub-3776682804513044";
					/* 250x250 image only, created 12/04/09 */
					google_ad_slot = "9681363594";
					google_ad_width = 250;
					google_ad_height = 250;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>

					<p class="mt0 mb0">&nbsp;</p>
					<!-- 200x200 image-->			
					<script type="text/javascript"><!--
					google_ad_client = "pub-3776682804513044";
					/* 250x250 image only, created 12/04/09 */
					google_ad_slot = "9681363594";
					google_ad_width = 250;
					google_ad_height = 250;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>
						
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
