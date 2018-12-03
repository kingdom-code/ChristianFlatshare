<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

	$showForm = TRUE;
	
	// First time this page is called, an offered_id or a wanted_id is supplied.
	if (isset($_GET['offered_id'])) { $type = "offered"; $id = $_GET['offered_id']; }
	if (isset($_GET['wanted_id'])) { $type = "wanted"; $id = $_GET['wanted_id']; }
	if (isset($_POST['type'])) { $type = $_POST['type']; }
	if (!$type) { header("Location: index.php"); exit; }
	// Whereas when this page calls itself (after a post submission), the type and id parameters
	// are directly sent (rather than assigned as above).
	if (isset($_POST['type'])) { $type = $_POST['type']; }
	if (isset($_POST['id'])) { $id = $_POST['id']; }
	
	if (isset($_POST['name'])) {
		$name = trim($_POST['name']);
        $_SESSION['v_name'] = $name;
	} elseif (isset($_SESSION['u_name'])) {
		$name = $_SESSION['u_name'];
	} elseif (isset($_SESSION['v_name'])) { 
        $name = $_SESSION['v_name'];
	} else {
		$name = NULL;
	}
	
	if (isset($_POST['your_email'])) {
		$your_email = trim($_POST['your_email']);
        $_SESSION['visitor_your_email'] = $your_email;		
	} elseif (isset($_SESSION['u_email'])) {
		$your_email = $_SESSION['u_email'];
	} elseif (isset($_SESSION['visitor_your_email'])) {
		$your_email = $_SESSION['visitor_your_email'];
	} else {	
		$your_email = NULL;
	}
	
	if (isset($_POST['email'])) {
	   $email = strtolower(trim($_POST['email'])); 
       $_SESSION['visitor_email'] = $email; 
	} elseif (isset($_SESSION['visitor_email'])) {
		$email = $_SESSION['visitor_email'];
	} else {	
		$email = NULL;
	}
	
	
	
	if (isset($_POST['comments'])) { $comments = $_POST['comments']; } else { $comments = NULL; }
	
	$query  = "select a.*,j.town,DATEDIFF(curdate(),created_date) as `ad_age` ";
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
	$summary = createSummaryV2($ad,$type,"odd",FALSE,FALSE,$_GET['t']);	
	
	if ($_POST) {
	
		// Validate name & email
		if (!trim($name)) {
			$error['name'] = '<span class="error">Please enter your full name</span><br/>';
		}
		if (!preg_match(REGEXP_EMAIL,$your_email)) {
			$error['your_email'] = '<span class="error">Please enter a valid email address</span><br/>';
		}		
		if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {
			$error['email'] = '<span class="error">Please enter a valid email address</span><br/>';
		}
		
		if ($error) {
		
			$msg = '<p class="error">Errors were found in your form. Please amend</p>'."\n";
			
		}
        else {
		
            // Send Email
            $CFSMailer = new CFSMailer();
            
			$comments = $comments;
			$title = getAdTitle($ad, $type, FALSE);
			$subject = "Christian Flatshare regarding: " . $title;
            $link = 'http://'.SITE.'details.php?id='.$id.'&post_type='.$type;
            
            // Get Body
            $body = $twig->render('emails/send_to_a_friend.html.twig', array(
                'name' => $name,
                'email' => $your_email,
                'message' => $comments,
                'advert' => $title,
                'link' => $link,
            ));
            
            // Set variables
            $to = $email;
            
			// BCC if advertiser
		    $bcc = ($advertising == TRUE) ? 'ryanwdavies@hotmail.com' : NULL;
            
            var_dump($to);
            
            $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
            
			// Test latch to avoid sending twice
 			if (!$_SESSION['sent_to_friend']) {
                
                $sent = $CFSMailer->sendMessage($msg);
                
				if ($sent > 0) {	
					$showForm = FALSE; // don't show form
					$_SESSION['sent_to_friend'] = TRUE;
					$msg = '<p><h2 class="success">Your message has been sent</h2><p>';
					//$msg .= '<p>Here is the message you sent:<br/></p>'."\n";
					$msg .= '<table cellpadding="0" cellspacing="0" width="100%">'."\n";
				    $msg .= '<tr><td>'."\n";
				    $msg .= '<div class="box_light_grey mb10" style="float:left; width:750px;" style="padding-left:5px;padding-right:10px;padding-top:5px;padding-bottom:10px;">'."\n";
				    $msg .= '<div class="tr"><span class="l"></span><span class="r"></span></div>'."\n";
				    $msg .= '<div class="mr">'."\n";
				    $msg .= '<h2 class="mt0 mb5">Your message...</h2><br />'."\n";
				    $msg .= '<table cellpadding="0" cellspacing="0" width="100%">'."\n";
  		            $msg .= '<tr class="even">'."\n";
	  			    $msg .= '<td style="padding-left:5px;padding-right:5px;padding-top:10px;padding-bottom:5px;">'."\n";
					$msg .= '<strong>From: </strong>send-to-a-friend@christianflatshare.org<br />'."\n";					
					$msg .= '<strong>Reply-to: </strong>'.$your_email.'<br />'."\n";										
					$msg .= '<strong>Subject: </strong>'.$subject.'<br /><br />'."\n";															
					$msg .= $body;
			        $msg .= '</td>'."\n";
		  		    $msg .= '<tr class="odd">'."\n";									
			        $msg .= '<td><br /></td>'."\n";			
				    $msg .= '</tr>'."\n";
					$msg .= '</table>
							 </div>
							 <div class="br"><span class="l"></span><span class="r"></span></div>
							 </div>
							</td></tr>
							<tr><td style="padding-top:5px;">
							<span class="grey mt10"></table>'."\n";
				} else {
					$msg = '<p class="error">'.TELL_A_FRIEND_ERROR.'</p><br /><br />'."\n";
				}
		    } // sent_to_friend test
		}
	
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
	<title>Send to a friend - Christian Flatshare</title>
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
	<script language="javascript" type="text/javascript">
		
		function updateEmail(value) {
			var e = $('dynamicEmailBody');
			value = value.replace(/\n/g, "<br/>");
			e.innerHTML = value;
		}
		
		function updateName(value) {
			var n = $('dynamicName');
			n.innerHTML = value;
		}
		
		function updateEmailAddress(value) {
			var n = $('dynamicEmailAddress');
			n.innerHTML = value;
		}	
		
	</script>
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
				<td><h1 class="m0">Send to a friend</h1></td>
					<td align="right">
						<?php if ($showForm): ?>
						  <!--return one page-->
						  <p class="mb0"><a href="javascript:history.go(-1)">Return to the previous page</a></p>
						<?php else: ?>
						  <!--return two pages-->
						  <p class="mb0"><a href="javascript:history.go(-2)">Return to the previous page</a></p>
					    <?php endif; ?>
					</td>
			</tr>
		</table>
		<?php print $summary; ?>
		<?php print $msg; ?>
		<?php if ($showForm):
		   // Set the latch to prevent multiple replies using page refresh
  			$_SESSION['sent_to_friend'] = FALSE;
		 ?>


  <p><h2 class="mb5 mt10">This feature has been temporarily suspended</h2>
  Send-to-a-friend has been temporarily suspended and will be re-enabled soon. If you<br /> would like to send details of this advert to someone, the link and title are:<br /><br />
 
   <?php
     $title = getAdTitle($ad, $type, FALSE);
     $link = 'http://'.SITE.'details.php?id='.$id.'&post_type='.$type; 
     echo "<b>".$title."</b>";
     echo "<br />";
     print nl2br(clickable_link(($link))); 
     ?>
   <br /><br />
   <br /><br />


</p>
<!-- COMMENT
				<p>Use the form below to send this advert to a friend.</p>
		        <form name="tellAFriend" method="post" action="<?php print $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="type" value="<?php print $type; ?>" />
		<input type="hidden" name="id" value="<?php print $id; ?>" />
		<table border="0" cellpadding="0" cellspacing="10" id="replyToAdOwner">
			<tr>
				<td width="160">Your name: <span class="obligatory">*</span></td>
	<td><?php print $error['name']; ?><input name="name" type="text" id="name" onkeyup="updateName(this.value);" value="<?php print $name; ?>"/></td>
			</tr>
			<tr>
				<td width="160">Your email address: <span class="obligatory">*</span></td>
				<td><?php print $error['your_email']; ?>
					<input name="your_email" type="text" id="your_email" onkeyup="updateEmailAddress(this.value);" value="<?php print $your_email; ?>" /></td>
			</tr>			
			<tr>
				<td width="160">Your friend's email address: <span class="obligatory">*</span></td>
				<td><?php print $error['email']; ?><input name="email" type="text" id="email" value="<?php print $email; ?>"/></td>
			</tr>
			<tr>
				<td width="160" valign="top">Message for your friend:</td>
				<td width="500"><textarea name="comments" rows="5" id="comments"  style="padding:2px; font-size:12px;" onkeyup="updateEmail(this.value);" ><?php print $comments; ?></textarea></td>
				
			</tr>
			<tr>
				<td width="160" valign="top">Preview of email to be sent:</td>
				<td width="500">
					<div id="emailPreview">
					    <span class="mb0" style="font-size:11px;font-family:Tahoma;">
							A message from your friend <span id="dynamicName"><?php print $name; ?></span> (<span id="dynamicEmailAddress"><?php print $your_email; ?></span>):
						</span><br />
						<p class="mb0" id="dynamicEmailBody"><span class="grey">Start typing your message...</span></p>
						<br />
					    <span class="mb0" style="font-size:11px;font-family:Tahoma;">
						They would like to show you this advert:<br	/><br	/>
						<strong><?php print getAdTitle($ad,$type,FALSE); ?></strong> 
 					    (http://<?php print SITE; ?>details.php?<?php print $type; ?>_id=<?php print $id; ?>)
						</span>
					</div>
				</td>
			</tr>
	<!--		<tr>
				<td width="160" valign="top">&nbsp;</td>
				<td><input type="submit" name="Submit" value="Send to your friend" /></td>
			</tr>
		</table>
		</form>
-->
    <?php endif; ?>
		
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
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); } ?>
<?php print getTrackingCode(); ?>
</body>
</html>
