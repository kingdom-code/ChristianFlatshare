<?php
session_start();

// Autoloader
require_once 'web/global.php';

require('includes/class.phpmailer.php'); // Email class

connectToDB();
	
	// Initialise variables
	$error = NULL;
	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }
	if (isset($_GET['user_id'])) { $user_id = $_GET['user_id']; } else { header("Location:index.php"); exit; }
	if (isset($_GET['control'])) { $control = $_GET['control']; } else { $control = NULL; }
	
	// Ensure user_id is a valid, non-verified user
	$query = "select * from cf_users where user_id = '".$user_id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!mysqli_num_rows($result)) {
		// User does not exist.
		header("Location: index.php");
		exit;
	} else {
		$user = mysqli_fetch_assoc($result);
	}
	
	// If the control variable is set, the user is following the link from the email he / she received
	if ($control) {	
		// Check to see whether the control parameter is the same as the password
		if ($control == $user['password']) {
			// VERIFY the user
			$query = "update cf_users set email_verified = '1' where user_id = '".$user_id."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				$msg = "success";
			} else {
				$msg = "failure";
			}
		} else {
			// ERRONEOUS CONTROL (hacked query string perhaps?)
$msg = "failure";
//			die("Your verification has failed");
		}
	}
	
	// If action == "resend", we'll need to resend the verification email
	if ($action == "resend") {
		$link = 'http://'.SITE.'verify.php?user_id='.$user['user_id'].'&control='.$user['password'];
		$link = '<a href="'.$link.'">'.$link.'</a>';
 		$mail    = new PHPMailer();
		$body    = $mail->getFile('emails/register.html');
		$body    = eregi_replace("[\]",'',$body);
		
		$mail->From     = "welcome@christianflatshare.org";
		$mail->FromName = "welcome@christianflatshare.org";
		$mail->Subject  = "[Christian Flatshare] Email address verification";
		$mail->MsgHTML(sprintf($body,$user['first_name'],$link));
		$mail->AddAddress($user['email_address'], $user['email_address']);		
		if ($mail->Send()) {
			$msg = 'resend_success';
		} else {
			$msg = 'resend_failure';
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Verify your email address - Christian Flatshare</title>
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
				
			<h1 class="mt0">Email Verification</h1>
		<?php if ($msg == "success") { ?>
		<h2 class="success">Your email address has been successfully verified</h2>
		<p>You can now login to Christian Flatshare.</p><br /><br /><br />
		<?php } ?>
		<?php if ($msg == "failure") { ?>
		<p class="error">An error occured when trying to verify your email address</p>
		<p>Please <a href="contact-us.php">contact us</a> with for assistance</p><br /><br /><br />
		<?php } ?>
		<?php if ($msg == "resend_success") { ?>		
		<h2 class="success mb0">Verification email resent to <strong><?php print $user['email_address']?></strong></h2>
		<p class="mb0">Click the link in this email to verify your email address.</p>	
		<p class="mt0">Please check your <strong class="obligatory" style="font-size:14px">SPAM MAIL</strong> folder for your verification email.</p>
		<br />
		<br />
	
		<?php } ?>
		<?php if ($msg == "resend_failure") { ?>
		<p class="error">An error occured when trying to send the verification email</p>
		<p>Please <a href="contact-us.php">contact us</a> explaining the problem</p><br />
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