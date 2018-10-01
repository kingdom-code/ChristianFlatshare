<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

	$remove_msg = "";
    $showForm = TRUE;	
	$replied = "replied";						
	
		
	
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
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'reply_id'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }
	
	if (isset($_GET['reply_id'])) { $reply_id = $_GET['reply_id']; } else { $reply_id = NULL; }
	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }	
	
	if (isset($_POST['reply_id'])) { $reply_id = $_POST['reply_id']; }
	if (isset($_POST['message'])) { $message = addslashes($_POST['message']); }	


 	// Check that the message reply_id was sent or from the SESSION id
	$query = "select reply_id from cf_email_replies 
						where (to_user_id = ".$_SESSION['u_id']." 
						       or 
									 from_user_id = ".$_SESSION['u_id'].")
						and reply_id = ".$reply_id;
	$debug .= $query;
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$num_rows = mysqli_num_rows($result);
	if ($num_rows != 1) {
    // reply_id, to user id and SESSION u_id do not match
	  header("Location:../your-account-received-messages.php"); exit; 
	}

			
	if ($_POST) {
		if (!$_SESSION['reply_sent']) {
			$proceed = TRUE;
			// Switch polarity: TO_USER becomes FROM_USER
			// This SQL gets information from the original reply
			// Suppressed replies is set if the TO USER is a scammer, as this will now be the replying user who's replies we suppress
			// email_address is take from the FROM USER, as this is now the TO USER and destination email_address
			$query = "select 
								to_ad_id as ad_id, 
								from_user_id, 
								to_user_id, 
								to_post_type as post_type,
								u_to.suppressed_replies as suppressed_replies,
								u_from.email_address as email_address,
								u_from.account_suspended as account_suspended
			          from cf_email_replies e,
								     cf_users u_to,
										 cf_users u_from
								where reply_id = ".$reply_id."
								and u_to.user_id = to_user_id
								and u_from.user_id = from_user_id	";
			$debug .= $query;
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
      $ad_reply = mysqli_fetch_assoc($result);
			
			// Determine if the current message sender is the advert owner
			// This is used to decide if the "Send me links to remove me ad" in the email alert
			$query = "select user_id 
			          from cf_".$ad_reply['post_type']." 
								where ".$ad_reply['post_type']."_id = ".$ad_reply['ad_id'];
			$debug .= $query;
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
      $owner = mysqli_fetch_assoc($result);
			// see if the owner of this ad is the one who will get the reply,
			// and therefore we should send the delete links.
			$include_links = FALSE;
			if ($owner['user_id'] == $ad_reply['from_user_id']) { $include_links = TRUE; }
			
			// scammer mods
			if ($ad_reply['suppressed_replies'] == 1) {$proceed = FALSE; sleep(20); }
			
			// There have been duplicate messages inserted in to the messages table 
			// check message does already exists:
                        // RD 28-APR-2013 - this would be better as a NOT EXISTS in the INSERT statement
			$query = "SELECT 'x' FROM cf_email_replies
								WHERE from_user_id = '".$_SESSION['u_id']."'
								AND   to_user_id   = '".$ad_reply['from_user_id']."'
								AND   to_ad_id     = '".$ad_reply['ad_id']."'
								AND   to_post_type   = '".$ad_reply['post_type']."'
								AND   message      = '".$message."'
								AND   reply_date > date_sub(now(), interval 10 MINUTE)
								";
			$debug .= debugEvent("duplicate check query",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$num_rows = mysqli_num_rows($result);
			$debug .= debugEvent("number of duplicates found",$num_rows);			
			if ($num_rows == 0) { // Message already found, do not insert or send email
			
				$query = "insert into cf_email_replies 
								(from_user_id,
								 to_user_id,
								 to_ad_id,
								 to_post_type,
								 message,
								 reply_date,
								 suppressed_replies) 
				 values ('".$_SESSION['u_id']."',
								 '".$ad_reply['from_user_id']."',
								 '".$ad_reply['ad_id']."',
								 '".$ad_reply['post_type']."',							 
								 '".$message."',							 
								 now(), 
								 '".$ad_reply['suppressed_replies']."');";
					$debug .= debugEvent("insert query",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
                    
					if ($result) {
						$msg = '<p class="green"><strong>Your message was sent and the recipent has been notified by email.</strong></p>';					
						
						// Get reply_id for message hash
						$query = "
							SELECT MAX(reply_id)
							FROM cf_email_replies 	
							WHERE from_user_id =  '".$_SESSION['u_id']."'
							AND   to_user_id   =  '".$ad_reply['from_user_id']."'
							AND   to_ad_id     =  '".$ad_reply['ad_id']."'
							;		
						";
						$debug .= debugEvent("select reply_id query",$query);
						$result = mysqli_query($GLOBALS['mysql_conn'], $query);
						$msg_reply_id = cfs_mysqli_result($result,0,0);		
						$msghash = md5($msg_reply_id.'cfsmessage');
					} else {
						$msg = '<p class="error"><strong>There was a problem sending your message..</strong></p>';
                        $proceed = FALSE;				
					}
					
					// checks for proceed and that the destination member's account is not suspended
					if ($proceed && $ad_reply['account_suspended'] != 1) {
                        
                        // Get information for hash
						$query = "SELECT * FROM cf_users WHERE user_id = (SELECT user_id FROM cf_".$ad_reply['post_type']." where ".$ad_reply['post_type']."_id = '".$ad_reply['ad_id']."')";
			 			$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
						// Ok, we have a valid user
						$row = mysqli_fetch_assoc($result);
						$hash = md5($row['password'].$row['email_address']);
                        
                        // Send Email
                        $CFSMailer = new CFSMailer();
            
                        $advert_title = getAdTitleByID($ad_reply['ad_id'],$ad_reply['post_type'],FALSE);
                        $advert_url = 'http://' . SITE . 'details.php?id=' . $ad_reply['ad_id'] . '&post_type=' . $ad_reply['post_type'];
                        
                        // Get Body
                        $body = $twig->render('emails/reply.html.twig', array(
                            'advert' => array('title' => $advert_title, 'url' => $advert_url),
                            'name' => $_SESSION['u_name'],
                            'advert_owner' => ($include_links) ? TRUE : FALSE,
                            'suspend_url' => 'http://' . SITE . 'email-actions.php?action=suspend_ad&post_type='.$ad_reply['post_type'].'&id='.$ad_reply['ad_id'].'&hash='.$hash,
                            'from_ads' => createSummaryForAllAds($_SESSION['u_id'], FALSE, TRUE),
                            'msg_url' => 'http://'.SITE.'email-view.php?action=message&reply_id='.$msg_reply_id.'&hash='.$msghash,
                        ));
                        
                        // Set variables
                        $subject = 'Christian Flatshare - New Message Regarding: ' . $advert_title;
                        $to = $ad_reply['email_address'];
                        
                        $msg = $CFSMailer->createMessage($subject, $body, $to);
                        $sent = $CFSMailer->sendMessage($msg);
                        
                        if ($sent > 0) {
                            header('Location:../your-account-message-reply.php?reply_id='.$reply_id.'&action='.$replied); exit;
                        }
                        else {
                            $msg = '<p class="error"><strong>There was a problem sending your message..</strong></p>';
                        }
					} // if PROCEED & account suspended
					
					if ($ad_reply['account_suspended'] != 1) {
						$replied = "replied";
                        		
					} else {
						// account replied to was suspended, not email alert sent
						$replied = "replied_no_alert";										
					}
				} else {
					$replied = "replied2";
				} // if DUPLICATE CHECK - email already found
		} // if !SESSION REPLY
	} // if POST
	
	switch($action) {
	    case "replied":
				$_SESSION['reply_sent'] = TRUE;
				$showForm = FALSE;
				$msg = '<p class="green"><strong>Your message was sent and its owner has been notified.</strong></p>';
			break; 
			
	    case "replied2":
				$_SESSION['reply_sent'] = TRUE;
				$showForm = FALSE;
				$msg = '<p class="orange"><strong>You have sent identical message to this member within the last 10 minutes.</strong></p>';
			break; 
			
	    case "replied_no_alert":
				$_SESSION['reply_sent'] = TRUE;
				$showForm = FALSE;
				$msg = '<p class="green"><strong>Your message was sent.</strong></p>';
			break; 			
						
	    case "thread":
				$showForm = FALSE;
			break; 			
		
  }
		

	$s = "";
	$query = "select concat_ws(' ',u.first_name, u.surname) as `name`, 
						to_ad_id as `ad_id`, to_post_type  as `post_type`, 
						u.account_suspended as `account_suspended`,	
						u.suppressed_replies as `suppressed_replies`,  		                 
						u2.suppressed_replies as `to_scammer`
	          from cf_users u, 
						     cf_email_replies e,
								 cf_users u2			 
						where u.user_id != ".$_SESSION['u_id']."
						and   e.reply_id = ".$reply_id."
						and   from_user_id = u2.user_id						
						and  (u.user_id = to_user_id 
							 or u.user_id = from_user_id) ";
  		$debug .= debugEvent('Q1 ',$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
      $row = mysqli_fetch_row($result);
      $to_name = $row[0];
      $ad_id = $row[1];		
      $post_type = $row[2];	
			$account_suspended = $row[3];
			$suppressed_replies = $row[4];			
			$to_scammer = $row[5];							
  
	if ($ad_id) {
	 $s .= '<p style="padding-left:1px;padding-top:5px" class="mt10 mb0"><strong>Regarding: '.getAdTitleByID($ad_id,$post_type,TRUE, FALSE, TRUE).'</strong></p>';
  } else {
	 // The case where the user has responded to themselves - the ad_id is not set
		$query = "select concat_ws(' ',first_name, surname) as `name`, 
		                 to_ad_id as `ad_id`, to_post_type  as `post_type`, 
										 u.suppressed_replies as `suppressed_replies`
							from cf_users u, 
							     cf_email_replies e
							where u.user_id = ".$_SESSION['u_id']."
							and   e.reply_id = ".$reply_id."
							and  (u.user_id = to_user_id 
								 or u.user_id = from_user_id) ";
				$debug .= $query;
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$row = mysqli_fetch_row($result);
				$to_name = 'You';
				$ad_id = $row[1];		
				$post_type = $row[2];			
				$suppressed_replies = $row[3];
	 $s .= '<p style="padding-left:1px" class="mt0 mb0"><strong>Regarding: '.getAdTitleByID($ad_id,$post_type,TRUE, FALSE, TRUE).'</strong></p>';				
	}
	 	 $query = "select deleted, suspended from cf_".$post_type."_all where ".$post_type."_id = ".$ad_id;
	 	 $debug .= 'deleted / suspended '.$query;	 
		 $result = mysqli_query($GLOBALS['mysql_conn'], $query);
         if ($result) {
      	   $row = mysqli_fetch_row($result);
      	   $status = $row[0];
      	   $suspended = $row[1];	
         }
         else {
             $status = 0;
             $suspended = 0;
         }
		 
		if ($status == 1) {
	 	  $s .= '<p style="padding-left:0px" class="mb0 mt0 red">(this advert has been deleted)</p>';
		} elseif ($suppressed_replies == 1 && $result['post_type'] == 'offered') {
				$s .= '</span><span class="red">(spam/scam advert)</span>';
		} elseif ($to_scammer == 1 ) {
				$s .= '<span class="mt5 red"><b>Scam correspondant</b>: this person has been associated with sending scam advert details, please ignore.</span>';		
		} elseif ($suspended == 1) {
		  $s .= '<p style="padding-left:0px;color:#FF9900;" class="mb0 mt0">(this ad is suspended)</p>';
		}						

		
// Show the reply box if showForm
  if ($showForm) {
			$_SESSION['reply_sent'] = FALSE;
			
		if ($account_suspended == 1) {
		 $s .= '<div class="mt10" style="width:400px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">';
		 $s .= '<p class="mt0 mb0"><strong>'.$to_name.' has suspended their account.</strong></p>';
		 $s .= '<p class="mt0 mb0">You can send them a message but they will not be sent a new message notification email. They will be able to read your message when they next login.</strong></p>';		 
		 $s .= '</div>';
		}			
			
			$s .= '<form name="replyToAdOwner" method="post" action="'.$_SERVER["PHP_SELF"].'">
						 <input type="hidden" name="reply_id" value="'.$reply_id.'" />
						<table width="570">
						<tr>
							<td style="padding-left:0px;padding-top:5px;padding-right:5px" valign="top" >Reply to <strong>'.$to_name.'</strong>: </td>
						</tr>
						<tr>
						<td style="padding-left:0px" width="560">
							<textarea style="width:100%; padding:3px; font-size:12px;" name="message" rows="10" id="message">'.$message.'</textarea>
						</td>
						</tr>
						<tr>
						<td style="padding-right:0px" align="right">
							<input type="submit" name="Submit" value="Send reply" />
						</td>
						</tr>							
						</table>
						</form>';
	} 	 
  // $s .= '<p style="padding-left:5px" class="mt5 mb10">Messages between <strong>You</strong> and <strong>'.$to_name.'</strong>: 	 <td style="padding-right:10px;" align="right"><a href="your-account-message-reply.php?reply_id='.$reply_id.'">Reply</a></td></p>';
	
	 $s .= '<p class="mt5 mb10"><table style="padding-left:5px" cellpadding="0" cellspacing="0" border="0" width="100%"><tr>';
	 $s .= '<td>Messages between you and '.$to_name.':</td>'; 
	 if (!$showForm) {
	   $s .= '<td align="right"><a href="your-account-message-reply.php?reply_id='.$reply_id.'">Reply</a></td>';
	 }
	 $s .= '</tr></table></p>';
	 
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
						  WHEN ".$_SESSION['u_id']." THEN 'You'
							ELSE concat_ws(' ',u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$_SESSION['u_id']." THEN 'You'
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
						(e.to_user_id = ".$_SESSION['u_id']."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$_SESSION['u_id']."
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
						  WHEN ".$_SESSION['u_id']." THEN 'You'
							ELSE concat_ws(' ',u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$_SESSION['u_id']." THEN 'You'
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
						(e.to_user_id = ".$_SESSION['u_id']."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$_SESSION['u_id']."
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
						  WHEN ".$_SESSION['u_id']." THEN 'You'
							ELSE concat_ws(' ', u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$_SESSION['u_id']." THEN 'You'
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
						(e.to_user_id = ".$_SESSION['u_id']."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$_SESSION['u_id']."
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
						  WHEN ".$_SESSION['u_id']." THEN 'You'
							ELSE concat_ws(' ', u_from.first_name, u_from.surname)
							END) from_name,
						 (CASE u_to.user_id
						  WHEN ".$_SESSION['u_id']." THEN 'You'
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
						(e.to_user_id = ".$_SESSION['u_id']."
					 	 and e.from_user_id = u_from.user_id 
					 	 and e.to_user_id = u_to.user_id 						 
						 and e.recipient_deleted = 0
				     and e.from_user_id = e2.from_user_id 
						)
					or  		 
					  (e.from_user_id = ".$_SESSION['u_id']."
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
			order by ".$orderBy." ".$direction;
			
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
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Reply to message - Christian Flatshare</title>
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
		  <?PHP if ($action == "thread") { ?>
			<h1 class="mt0">Message thread</h1>
			<?PHP } else { ?>
			<h1 class="mt0">Reply to message</h1>			
			<?PHP } ?>
			<div class="clear"><!----></div>
			<?php print $msg?>
			<?php print $remove_msg?>
			<?php print $s?>		
		  </p>
		</div>
		<div class="cs" style="width:20px; height:270px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
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
