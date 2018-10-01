<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

	$showForm = TRUE;
	 
	// First time this page is called, an offered_id or a wanted_id is supplied.
	if (isset($_GET['offered_id'])) { $type = "offered"; $id = $_GET['offered_id']; }
	if (isset($_GET['wanted_id'])) { $type = "wanted"; $id = $_GET['wanted_id']; }
	
	// If $ad_posted_or advert posted override passed in, show reply form
	if ($_GET['ad_posted_or'] == 1 ) { $ad_posted_or = TRUE; $show_reply_form = TRUE; }	else { $ad_posted_or = FALSE; $show_reply_form = FALSE; } 
	if ($_GET['t'] == 1) { $t = '&t=1'; } else { $t = ''; } 
	
	
	// if reply is called recursively, 
	if (isset($_REQUEST['replied']) && isset($_REQUEST['type']) && isset($_REQUEST['id'])) { 
		$type = $_REQUEST['type']; 
		$id = $_REQUEST['id']; 
		$replied = $_REQUEST['replied']; 				
		$showForm = FALSE;
		if (TrustedUser($_SESSION['u_id']) == 'trusted') {
		$msg = '<p class="success" style="margin-bottom:10px">Your message has been sent to the advert\'s owner</p><br /><br />'."\n";
		} else {
		$msg = '<p class="success" style="margin-bottom:10px;font-size:15px;">Correct answer!</p>Your message has been sent to the advert\'s owner.<br /><br />'."\n";		
		}
	}	
	
	// Whereas when this page calls itself (after a post submission), the type and id parameters
	// are directly sent (rather than assigned as above).
	if (isset($_POST['type'])) { $type = $_POST['type']; }
	if (isset($_POST['id'])) { $id = $_POST['id']; }
	if (isset($_POST['name'])) {
		$name = trim($_POST['name']);
	} else if (isset($_SESSION['u_name'])) {
		$name = $_SESSION['u_name'];
	} else {
		$name = NULL;
	}
	if (isset($_POST['comments'])) { $comments = $_POST['comments']; } else { $comments = NULL; }
	if (isset($_POST['question_id'])) { $question_id = $_POST['question_id']; $ad_posted_or = TRUE; 
	                                      // ad_posted_or is set if there is a question_id, this implies that the captchar was used previously
	                                      // and therefore there is no need to show the "Are you looking for accommodation?" question
	                                   } else { 
																		  $question_id = NULL; 
																		 }
	if (isset($_POST['answer'])) { $answer = $_POST['answer']; } else { $answer = NULL; }
	
	$query  = "select a.*,
				j.town,
				DATEDIFF(curdate(),a.created_date) as `ad_age`, 
				u.email_address, 
				u.password, 				
				DATEDIFF(curdate(),u.last_login) as `last_login_days` ";
	// if user is logged on, get ad "saved" status
	if (isset($_SESSION['u_id'])) {
		$query .= ", s.active as `active` ";
	}
	$query .= "from cf_".$type." as `a` ";
	$query .= "left join cf_jibble_postcodes as `j` on ";
	if ($type == "offered") {
		$query .= "SUBSTRING_INDEX(a.postcode,' ',1) = j.postcode ";
	} else {
		$query .= "a.postcode = j.postcode ";
	}
	$query .= "left join cf_users as `u` on u.user_id = a.user_id ";
	// If user is logged on, get ad "saved" status
	if (isset($_SESSION['u_id'])) {
		$query .= "
			left join cf_saved_ads as `s` 
			on s.ad_id = a.".$type."_id and 
			s.post_type = '".$type."' and 
			s.user_id = '".$_SESSION['u_id']."' 		
		";
	}
	$query .= "where a.".$type."_id = '".$id."'";
	$debug .= debugEvent("Selection query",$query);
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$ad = mysqli_fetch_assoc($result);
	$summary = createSummaryV2($ad,$type,"odd mb10",FALSE,FALSE,$_GET['t']);
	$summaryForAllUserAds = createSummaryForAllAds($_SESSION['u_id']);
	
  	
	function createDisplayEmails($ad,$type) {	
    // Create ad reply summaries	
	$query = "
			SELECT DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
			       e.message,
				     reply_id, 
				     from_user_id,
				     u_from.first_name,
				     CONCAT_WS(' ',u_from.first_name,u_from.surname) as `name`
			FROM  cf_email_replies `e`, 
				    cf_users as `u_from`,
				    cf_".$type." as `ad`
			WHERE ((e.from_user_id = '".$_SESSION['u_id']."'
					    and e.from_user_id != ad.user_id						
						 )
						or
						 (e.from_user_id = '".$_SESSION['u_id']."'
					 	  and e.to_user_id = '".$_SESSION['u_id']."'
						  and e.from_user_id = ad.user_id
						))
			AND   ad.".$type."_id = e.to_ad_id
			AND   e.to_post_type = '".$type."'
			AND   e.to_ad_id   = '".$ad[$type.'_id']."'
			AND   u_from.user_id = e.from_user_id
			AND   e.sender_deleted = 0
			UNION ALL
			SELECT DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
			       e.message,
				   reply_id, 
				   from_user_id,
				   u_from.first_name,
				   CONCAT_WS(' ',u_from.first_name,u_from.surname) as `name`
			FROM cf_email_replies `e`, 
				 cf_users as `u_from`,
				 cf_".$type."_archive as `ad`
			WHERE ((e.from_user_id = '".$_SESSION['u_id']."'
					    and e.from_user_id != ad.user_id						
						 )
						or
						 (e.from_user_id = '".$_SESSION['u_id']."'
					 	  and e.to_user_id = '".$_SESSION['u_id']."'
						  and e.from_user_id = ad.user_id
						))
			AND   ad.".$type."_id = e.to_ad_id
			AND   e.to_post_type = '".$type."'
			AND   e.to_ad_id   = '".$ad[$type.'_id']."'
			AND   u_from.user_id = e.from_user_id
			AND   e.sender_deleted = 0
			ORDER BY reply_date DESC			
		";
 
		$debug .= debugEvent("Email replies query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
			$o = '
			<br />
			<table cellpadding="0" cellspacing="0" width="100%">
			  <tr><td>	
			  <!-- Advert responses -->
			  <div class="box_light_grey mb10" style="float:left; width:550px;">
			  <div class="tr"><span class="l"></span><span class="r"></span></div>
			  <div class="mr">
			  
			  <h2 class="mt0 mb5">Your replies to this ad</h2>
			  <br />
			  <table cellpadding="0" cellspacing="0" width="100%">';
  		  while($reply = mysqli_fetch_assoc($result)) {
  		    $o .= '<tr class="even">'."\n";
	        $o .= '<td style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:10px;">'."\n";
			$o .= '<strong>Sent: </strong>'.$reply['reply_date'].'<br />'."\n";					
            $o .= '<strong>Your message to '.trim(stripslashes($ad['contact_name'])).':</strong>'.'<br />'."\n";		
        //    $o .= '<p class="mt5">'.clickable_link(nl2br(stripslashes($reply['message']))).'</p>'."\n";	
            $o .= '<p class="mt5">'.makeClickableLinks(nl2br(stripslashes($reply['message']))).'</p>'."\n";							
			// Include advert links
            $o .= '<strong>Adverts by '.trim($reply['first_name']).' currently showing on Christian Flatshare:</strong><br />'."\n";	
			$adsSummary = createSummaryForAllAds($reply['from_user_id'], FALSE);
			if (!$adsSummary) { 
			  $o .= 'No adverts showing.'.'<br />'."\n";
			  } else {
              $o .= '<strong>'.$adsSummary.'</strong>'; 
			}
	        $o .= '</td>'."\n";						
		    $o .= '</tr>'."\n";						
	        $o .= '</td>'."\n";
  		    $o .= '<tr class="odd">'."\n";									
	        $o .= '<td><br /></td>'."\n";			
		    $o .= '</tr>'."\n";
		  } // WHILE email loop end
		  $o .= '</table>
				 </div>
				 <div class="br"><span class="l"></span><span class="r"></span></div>
				 </div>
				</td></tr>
				<tr><td style="padding-top:5px;">
				<span class="grey mt10"></table>';
 	    } // IF results
 	    return $o;
      } // createDisplayEmails
	
	
	
	
	if ($_POST) {
		if (TrustedUser($_SESSION['u_id']) == 'trusted') {
			// no CAPTCHA validation 
		} else {
			// CAPTCHA validation
			if (empty($answer)) {
				$error['captcha'] = '<p class="error">Please choose an answer</p>';
			} else {
				$query = "select answers from cf_captcha_questions where question_id = '".$question_id."';";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$correctAnswers = explode(",",cfs_mysqli_result($result,0,0));
				$check = in_array($answer,$correctAnswers);
				if ($check === FALSE) {
					$error['captcha'] = '<p class="error">Your answer was incorrect, please try again.</p>';
				}		
			}
		} // if trusted
		
		if ($error) {
			//$msg = '<p class="error">Errors were found in your form. Please amend</p>'."\n";
		} else {
			// Check if user has suppressed_replies
			$proceed = TRUE;			
			$query = "select suppressed_replies from cf_users where user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$suppress = cfs_mysqli_result($result,0,0);
			
			if ($suppress == "1") { $proceed = FALSE; sleep(30); }		
		
			// Whether it's a scam or not, we need to update the db table
			// Don't update if replies already sent
			if (!$_SESSION['ad_replied']) {
                
    			// If scammer is trying to send to himself to test, allow
    			if ($ad['user_id'] == $_SESSION['u_id']) {
    				$proceed = TRUE;
    			}	
                
    			$query = "
    				insert into cf_email_replies 	
    					(from_user_id,from_name,to_user_id,to_ad_id,
                                          to_post_type, message,reply_date,suppressed_replies) 
                                 select '".$_SESSION['u_id']."','".addslashes($name)."','".$ad['user_id']."',
    					'".$ad[$type."_id"]."','".$type."','".addslashes($comments)."',now(), ".$suppress."
                                  from cf_email_replies
                                  where not exists (select null from cf_email_replies 
                                                    where from_user_id = '".$_SESSION['u_id']."'
                                                    and to_user_id = '".$ad['user_id']."'
                                                    and to_ad_id =  '".$ad[$type."_id"]."'
                                                    and message = '".addslashes($comments)."')
                                  limit 1 ";
    			$debug .= debugEvent("insert query",$query);
    			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			    
                if ($result){
                
        			$query = "
        				SELECT MAX(reply_id)
        				FROM cf_email_replies 	
        				WHERE from_user_id = '".$_SESSION['u_id']."'
        				AND   to_user_id   = '".$ad['user_id']."'
        				AND   to_ad_id     = '".$ad[$type."_id"]."'
        				;		
        			";
        			$debug .= debugEvent("select reply_id query",$query);
        			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
        			$reply_id = cfs_mysqli_result($result,0,0);
                
                    $proceed = TRUE;
                }
                else {
                    $proceed = FALSE;
                }		
            
        		if ($proceed) {
        			// Create the hash password and email_address
        			$hash = md5($ad['password'].$ad['email_address']);		
        			$msghash = md5($reply_id.'cfsmessage');
	    
        
                    // Send Email
                    $CFSMailer = new CFSMailer();

                 //   $advert_title = getAdTitleByID($ad['ad_id'],$ad['post_type'],FALSE);
                 //   $advert_url = 'http://' . SITE . 'details.php?id=' . $ad['ad_id'] . '&post_type=' . $ad['post_type'];
                    $advert_title = getAdTitleByID($ad[$type."_id"],$type,FALSE);
                    $advert_url = 'http://' . SITE . 'details.php?id=' . $ad[$type."_id"] . '&post_type=' . $type;

        
                    // Get Body
                    $body = $twig->render('emails/reply.html.twig', array(
                        'advert' => array('title' => $advert_title, 'url' => $advert_url),
                        'name' => $_SESSION['u_name'],
                        'advert_owner' => FALSE,
                        'suspend_url' => 'http://' . SITE . 'email-actions.php?action=suspend_ad&post_type='.$ad['post_type'].'&id='.$ad['ad_id'].'&hash='.$hash,
                        'from_ads' => createSummaryForAllAds($_SESSION['u_id'], FALSE, TRUE),
                        'msg_url' => 'http://'.SITE.'email-view.php?action=message&reply_id='.$reply_id.'&hash='.$msghash,
                    ));
        
                    // Set variables
                    $subject = 'Christian Flatshare - New Message Regarding: ' . $advert_title;
                    $to = $ad['email_address'];
        
                    $msg = $CFSMailer->createMessage($subject, $body, $to);
                    $sent = $CFSMailer->sendMessage($msg);
                    
                    
                    if ($sent > 0) {	
                        $showForm = FALSE;
                        header('Location:../reply.php?replied=yes&type='.$type.'&id='.$id); exit;
                    }
        		}
                else {
                    $msg = '<p class="error">There was a problem sending your reply. Please report to Christian Flatshare.</p>'."\n";
        		}
            }
        }
	}
	
	if ($showForm) {
		// Get a random captcha question
		$query = "select * from cf_captcha_questions order by rand() limit 0,1";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$question = mysqli_fetch_assoc($result);
		// Load the corrent answers of the question plus a few incorrect ones to bring the total to 9
		$correctAnswers = "";
		$temp = explode(",",$question['answers']);
		$count = 9 - count($temp);
		foreach($temp as $value) {
			$correctAnswers .= "'".$value."',";
		}
		$correctAnswers = substr($correctAnswers,0,-1);
		$query = "
			select * from (
				select * from (select * from cf_captcha_answers where answer_id not in (".$correctAnswers.") order by rand() limit 0,8) as `wrong_answers`
			union
				select * from (select * from cf_captcha_answers where answer_id in (".$correctAnswers.") order by rand() limit 0,1) as `right_answer`
			) as `answers` order by rand();	
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or examine($query);
		// Tabulate the answers in a three by three grid
		$counter = 0;
		$captcha = '<tr>';
		while ($row = mysqli_fetch_assoc($result)) {
			if ($counter % 3 == 0) {
				$captcha .= '</tr>';
				$captcha .= '<tr>';
			}
			$captcha .= '<td width="100px">';
			// Create the checkbox
			$captcha .= '<input type="radio" name="answer" value="'.$row['answer_id'].'" id="answer_'.$row['answer_id'].'" />';
			// Create the answer text
			$captcha .= '<label for="answer_'.$row['answer_id'].'">'.$row['text'].'</label>';
			$captcha .= '</td>';
			
			$counter++;
		}
		$captcha .= '</tr>';
				
	}	// Show form
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Reply to ad owner - Christian Flatshare</title>
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
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
			<tr>
				<td><h1 class="m0">Reply to ad owner</h1></td>
					<td align="right">
						<?php if ($showForm) { ?>
						  <!--return one page-->
						  <p class="mb0"><a href="javascript:history.go(-1)">Return to the previous page</a></p>
						<?php } else { ?>
						  <!--return two pages-->
						  <p class="mb0"><a href="javascript:history.go(-2)">Return to the previous page</a></p>
					    <?php } ?>
					</td>
			</tr>
		</table>
		<?php print $summary?>
		<?php print $msg?>
		<?php if ($showForm) { ?>
		<?php /*if ($ad['contact_name']) { ?>
		<h2 class="mt0">Contact: <a href="reply.php?<?php print $type?>_id=<?php print $id?>"><?php print $ad['contact_name']?></a><?php print ($ad['contact_phone']? ", ".$ad['contact_phone']:"")?></h2>
		<?php }*/ ?>
		
		<?php if (!$_SESSION['u_id']) { ?>
		
			<div id="dhtmltooltip"></div>
			<script type="text/javascript">
			
				// Code based on the excellent DynamicDrive DHTML Code Library (www.dynamicdrive.com)
				// For a more powerful and old browser compatible example, visit http://www.dynamicdrive.com/
			
				var offsetxpoint=20 //Customize x offset of tooltip
				var offsetypoint=0 //Customize y offset of tooltip
				var ie=document.all
				var ns6=document.getElementById && !document.all
				var enabletip=false;
				var tipobj= document.getElementById("dhtmltooltip");
			
				function show_tooltip(tooltipContent){
					if (ns6||ie){
						tipobj.innerHTML = tooltipContent;
						enabletip = true;
						return false;
					}
				}
				
				function hide_tooltip(){
					if (ns6||ie){
						enabletip = false;
						tipobj.style.visibility = "hidden";
						tipobj.style.left = "-1000px";
						tipobj.style.backgroundColor = '';
						tipobj.style.width = '';
					}
				}
				
				function position_tooltip(e){
					if (enabletip) {
						var curX=(ns6)?e.pageX : event.x+document.body.scrollLeft;
						var curY=(ns6)?e.pageY : event.y+document.body.scrollTop;
						tipobj.style.left=curX+offsetxpoint+"px";
						tipobj.style.top=curY+offsetypoint+"px";
						tipobj.style.visibility="visible";
					}
				}
				
				document.onmousemove=position_tooltip
				
			</script>
		  <div id="columnLeft">		
		  <div style="float:left; width:580px;">			
			<h2 class="mb5 mt10">Login or join to reply to ads</h2>
			<p class="mt0"><strong><a href="register.php" class="f12">Join Christian Flatshare here</a></strong>   - it's free and takes just seconds!</p>
<!--
	      <?php if ($type == "offered")  { ?>
	        <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:460px;">
			 	<span class="style5">Looking for accommodation??</span><br /> 
		Posting a Wanted Accommodation advert will help those offering accommodation to find you.
		<br />
		<br />
			<strong>Posting an ad helps you to get the best from Christian Flatshare.</strong>
			<br />
			Links to your ads are included automatically when you reply to other ads. </div>
             <?php } else { ?>		
		     <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:475px;">
			 	<span class="style5">Accommodation to offer??</span><br /> 
		Posting an Offerd Accommodation advert will help those looking for accommodation to find yours.
		<br />
		<br />
			
			<strong>Posting an ad helps you to get the best from Christian Flatshare.</strong> <br />
			Links to your ads are included automatically when you reply to other ads.
          </div>
             <?php } ?>		 
--> 
		  <br /><br /><br /><br /><br /><br /><p class="mb0 grey">Christian Flatshare <strong>does  not </strong> disclose member email addresses to third  parties, see <a href="privacy-policy.php">privacy policy</a><br />
		Christian Flatshare <strong>does</strong> help to protect its members from any spam and scam type emails</p>
		    </div>
		</div>
		<div id="columnRight" style="padding-top:15px;">
			<div class="box_grey mb10">
			  <div class="tr"><span class="l"></span><span class="r"></span></div>
						<div class="mr">
						<h2 class="m0">Member Login</h2>
						<?php print createLoginForm($email,$password,$remember,$type.":".$ad[$type."_id"])?>
						</div>
						<div class="br"><span class="l"></span><span class="r"></span></div>
					</div>				
 </div>
			<div class="cc0"><!----></div>
 <?php  } else { 
       if ($ad['last_login_days']==0) {
	     $last_logged_in = 'today';
		 } else if ($ad['last_login_days']==1) {
		 $last_logged_in = 'yesterday';
		 } else {
		 $last_logged_in = $ad['last_login_days'].' days ago';	
		 }	
		 
		 // Reset session variable so that ad reply logic will allow replies
		 $_SESSION['ad_replied'] = FALSE;
		 
	   if ($ad['contact_name']) { ?>
	   <table border="0" cellpadding="0">
	   <tr>
	     <td width=50% class="mt0 mb0" valign="top">
		 <span class="style5">Contact: <?php print trim(stripslashes($ad['contact_name']))?><?php print ($ad['contact_phone']? ", ".$ad['contact_phone']:" ")?></span><br />		 
		 </td>
		  <td width=353 align="right" valign="top">
		 		 <span class="grey">(<?php print stripslashes($ad['contact_name'])?> logged in <?php print $last_logged_in?>)</strong></span>
		 </td>
     <td width=199 align="right" valign="top">
		 </td>
		   </tr>
 	  </table>
  	  <?php } ?>
	
	
 	  <?php if ($type == "wanted" && !checkForOfferedAd() && !$ad_posted_or) { 
						$show_reply_form = FALSE; ?>
	 <table border="0" cellpadding="0">
   <tr>
     <td width="410" class="mt0 mb0" valign="top" style="padding-left:40px;padding-top:10px;padding-bottom:40px">
	     <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:500px;">
			<span class="style5">Do you have accommodation to offer??</span><br />
			<br />
			<strong>Posting an Offered Accommodation ad helps <u>YOU</u> get the best from Christian Flatshare!</strong><br /><br />
			<strong>Why??</strong><br />
			- CFS shows all wanted ads that match the accommodation you offer<br />			
			- Links to your Offerd ads are included automatically when you reply to ads<br />
			- You can add photos to show how very nice your accommodation really is!<br /><br  />
		 <table border="0" cellpadding="0" width="100%">
		 <tr>
     	 <td><a href="post-choice.php">Let me post an advert</a></td>		
    	 <td align="right"><a href="reply.php?wanted_id=<?php print $id?><?php print $t?>&ad_posted_or=1">Just let me reply</a></td>
		 </tr>
			</table>
      </div>
		</td>
		</tr>
		</table>
      <?php  } elseif ($type == "offered" && !checkForWantedAd() && !$ad_posted_or) { 
				$show_reply_form = FALSE; ?>
	 <table border="0" cellpadding="0">
   <tr>
     <td width="490" class="mt0 mb0" valign="top" style="padding-left:40px;padding-top:10px;padding-bottom:40px">				
	     <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:510px;">
			 <span class="style5">Are you looking for accommodation??</span><br />
			<br />
			<strong>Posting a Wanted Accommodation advert helps <u>YOU</u> get the best from Christian Flatshare!</strong><br />
			<br />
			<b>Why??</b><br />
			- CFS shows all offered accommodation ads matching your requirements <br />			
			- People with rooms to offer sometimes only browse Wanted ads and don't post Offered ads<br />			
			- Links to your Wanted ad is included automatically when you reply to offered accommodation ads<br />
			- You can add fun photos to help introduce yourself!<br  />
			<br  />
		 <table border="0" cellpadding="0" width="100%">
		 <tr>
    	 <td><a href="post-choice.php">Click here to post a Wanted advert</a></td>			 
    	 <td align="right"><a href="reply.php?offered_id=<?php print $id?><?php print $t?>&ad_posted_or=1">Just let me reply</a></td>		

		 </tr>
		 </table>
     		
    </div>
		</td>
		</tr>
		</table>					
      <?php } else { 
				$show_reply_form = TRUE; 
		 } ?>
		 <?php if ($show_reply_form) { ?>
			<p class="mb0">Reply to <?php print trim(stripslashes($ad['contact_name']))?> here:</p>
			<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
			<input type="hidden" name="type" value="<?php print $type?>" />
			<input type="hidden" name="id" value="<?php print $id?>" />
			<input type="hidden" name="question_id" value="<?php print $question['question_id']?>" />
			<table border="0" cellpadding="0" cellspacing="5" id="replyToAdOwner">
			  <tr style="padding-bottom:2px;">
					<td nowrap="nowrap">Your name:</td>
					<td  width="460"><strong><?php print stripslashes($name)?></strong></td>
					<td width="270">&nbsp;</td>
				</tr>
			<!--	<tr style="padding-bottom:4px;">
					<td width="100" nowrap="nowrap">Your email address: </td>
					<td width="460"><strong><?php print $_SESSION['u_email']?></strong></td>
					<td>&nbsp;</td>
				</tr> -->
				<tr>
					<td valign="top" nowrap="nowrap">Your message: </td>
				  <td width="530" style="padding-top:3px;" valign="top"><textarea style="width:100%;  padding:3px; font-size:12px;" name="comments" rows="12" id="comments"><?php print $comments?></textarea></td>
					<td valign="top" class="grey" style="padding-left:10px;padding-top:3px;"><p class="mt0">
					<?php if ($type == "offered")  { ?><strong>Introduce yourself</strong><br />
					Write a warm and informative message in reply to the ad and to introduce yourself.<br />
			          <br />
				      Why not take the friendly initiative and include: your name, age, occupation, a church connection and the date you need accommodation from. </p>
					  <?php } else { ?><strong>Introduce yourself and your accommodation</strong><br />
					  Write a warm and informative message in reply to the ad and to introduce yourself and your accommodation.<br /><br />Why not take the friendly initiative  to include: your name, describe the household members, a church connection, and occupation(s). 
					  </p>
					  <?php } ?>				  </td>
				</tr>

					<?php if (TrustedUser($_SESSION['u_id']) != 'trusted') { ?>
							<tr style="padding-bottom:15px;padding-top:5px;">
								<td valign="top">Human test: </td>
								<td width="500">
									<p class="mt0">To help protect against automatic spam messages, we'll need to detect whether you're  a real person<br />or not (no offence) by asking you a simple question...</p>
									<?php print $error['captcha']?>
									<p class="mb0"><strong><?php print $question['question_text']?></strong></p>
									<table border="0" cellspacing="4" cellpadding="0">
										<?php print $captcha?>
								</table>			<br />&nbsp; <br />		</td>
								<td>&nbsp;</td>
							</tr>
					<?php } ?> <!-- END TrustedUser -->							
							<tr>
								<td valign="top">&nbsp;</td>
								<td align="right" valign="top"><span class="grey"><div>(Links to your own published adverts are automatically included in your reply)</div></span>&nbsp;&nbsp;<input type="submit" name="Submit" value="Send your message" />&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
				</table>
			</form>
						<br />
 		<?php } ?>
			
		<?php } ?> <!-- End IF SHOW REPLY FORM -->
			
		<?php } ?>
				<?php print createDisplayEmails($ad,$type)?>
				
		<?php if ($showForm) { ?>
			  <!--return one page-->
			  <p class="mb0"><a href="javascript:history.go(-1)">Return to the previous page</a></p>
		<?php } else { ?>
			  <!--return two pages-->
			  <p class="mb0"><a href="javascript:history.go(-2)">Return to the previous page</a></p>
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
