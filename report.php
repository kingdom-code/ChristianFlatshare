<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

	$showForm = TRUE;
	
	// First time this page is called, an offered_id or a wanted_id is supplied.
	if (isset($_GET['offered_id'])) { $type = "offered"; $id = $_GET['offered_id']; }
	if (isset($_GET['wanted_id'])) { $type = "wanted"; $id = $_GET['wanted_id']; }
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
	if (isset($_POST['email'])) {
		$email = strtolower(trim($_POST['email']));
	} else if (isset($_SESSION['u_email'])) {
		$email = $_SESSION['u_email'];
	} else {
		$email = NULL;
	}
	if (isset($_POST['comments'])) { $comments = $_POST['comments']; } else { $comments = NULL; }
	
	$query  = "select a.*,j.town,DATEDIFF(curdate(),created_date) as `ad_age` from cf_".$type." as `a` ";
	$query .= "left join cf_jibble_postcodes as `j` on ";
	if ($type == "offered") {
		$query .= "SUBSTRING_INDEX(a.postcode,' ',1) = j.postcode ";
	} else {
		$query .= "a.postcode = j.postcode ";
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
		if (!preg_match(REGEXP_EMAIL,$email)) {
			$error['email'] = '<span class="error">Please enter a valid email address</span><br/>';
		}
		
		if ($error) {
		
			$msg = '<p class="error">Errors were found in your form. Please amend</p>'."\n";
			
		} else {
		    $advert_title   = getAdTitle($ad, $type, FALSE);
            $advert_url     = getAdURL($ad, $type);
            
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/report.html.twig', array(
                'name' => $name,
                'advert' => array('title' => $advert_title, 'url' => $advert_url),
                'email' => $email,
                'message' => $comments,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Reported advert: ' . $advert_title;
            $to = array('info@christianflatshare.org', 'ryanwdavies@gmail.com');

            if (! stristr("$comments","jewel")  && ! stristr("$comments","watches")  && ! stristr("$comments","a href") && ! stristr("$comments","hand") && ! stristr("$comments","Replica") && ! stristr("$comments","discount") && ! stristr("$comments",'[url=') )
 	    {
             $msg = $CFSMailer->createMessage($subject, $body, $to, NULL, $email);
             $sent = $CFSMailer->sendMessage($msg);
	    } else {
             $sent=1;
  	    }
            
			if ($sent > 0) {	
				$showForm = FALSE; // don't show form
				$msg = '<p class="success">Thank you for your message.<br />An administrator will attend to it shortly.</p><br />'."\n";
			} else {
				$msg = '<p class="error">'.REPORT_AD_FAILURE.'</p>'."\n";
			}
		
		}
	
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Report ad to CFS admin - Christian Flatshare</title>
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
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
			<tr>
				<td><h1 class="m0">Report ad to CFS admin</h1></td>
				<td align="right"><a href="#" onclick="history.go(-1);">Return to the previous page</a></td>
			</tr>
		</table>
		<?php print $summary?>
		<?php print $msg?>
		<?php if ($showForm) { ?>
		<table width="650px"><tr class="mt10"><td>
		<p align="justify">Please use the form below to report inappropriate adverts to Christian Flatshare.<br />
		Your comments are sent only to CFS administrators.<br /><br />
		<strong>Note: </strong>If you are have seen inaccuracies with an advert (such a weekly price put instead of monthly, or a wrong phone number), please use <a href="reply.php?<?php print $type?>_id=<?php print $id?>">Reply to the advert</a>, and take the friendly initiative to inform the owner of the advert know directly.</p>
		</td></tr></table>
		<p>
		<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">		  
		  <input type="hidden" name="type" value="<?php print $type?>" />
	      <input type="hidden" name="id" value="<?php print $id?>" />
		</p>
		<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
		  <table border="0" cellpadding="0" cellspacing="10" id="replyToAdOwner">
			<tr>
			<?php if (isset($_SESSION['u_id'])) { ?>
				<td>Your name: </td>			
				<td  width="400"><strong><?php print stripslashes($name)?></strong></td>			
			<?php } else { ?>	
				<td>Your name: <span class="obligatory">*</span></td>			
				<td><?php print $error['name']?><input name="name" type="text" id="name" value="<?php print $name?>"/></td>
			<?php } ?>
			</tr>
			<tr>
			<?php if (isset($_SESSION['u_id'])) { ?>			
				<td>Your email address: </td>			
				<td width="400"><strong><?php print $_SESSION['u_email']?></strong></td>
			<?php } else { ?>					
				<td>Your email address: <span class="obligatory">*</span> </td>			
				<td><?php print $error['email']?><input name="email" type="text" id="email" value="<?php print $email?>"/></td>
			<?php } ?>				
			</tr>
			<tr>
				<td valign="top">Comments / Observations : </td>
				<td><textarea name="comments" rows="10" style="padding:2px; font-size:12px;" id="comments"><?php print $comments?></textarea></td>
			</tr>
			<tr>
				<td valign="top">&nbsp;</td>
				<td><input type="submit" name="Submit" value="Report to CFS admin" /></td>
			</tr>
		</table>
		</form>
		<?php } ?>
		<p class="mb0"><a href="index.php">Back to welcome page</a></p>
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
