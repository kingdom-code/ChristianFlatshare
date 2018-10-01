<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

require('includes/class.randompass.php');	// Random password generator class

connectToDB();

	$showForm = true;
	$msg = NULL;
	
	if (isset($_COOKIE['CF_LOGIN'])) {
		$temp = preg_split("/\|/",$_COOKIE['CF_LOGIN']);
		$email = $temp[0];
		$password = $temp[1];
		$remember = true;
	}
	
	if ($_POST) {
		
		$email = trim($_POST['email']);
		$passInstance = new rndPass(10);
		$password = substr(md5($passInstance->PassGen()), 0, 10);
		$error = NULL;
	  if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[_0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {
			$error['email'] = 'Please enter a valid email address'; 
		} else {
			$query = "select * from cf_users where email_address = '".$email."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (mysqli_num_rows($result) == 0) {
				$error['email'] = "We don't have an account with this email address on our database.";
			} else {
				// Send an email with the new password
                $user = mysqli_fetch_assoc($result);

                // Send Email
                $CFSMailer = new CFSMailer();
            
                // Get Body
                $body = $twig->render('emails/forgotten_password.html.twig', array(
                    'first_name' => $user['first_name'],
                    'email' => trim($email),
                    'base_url' => 'http://' . SITE,
                    'password' => $password,
                ));

                // Set variables
                $subject = 'Christian Flatshare - Password Reset';
                $to = $email;

                $msg = $CFSMailer->createMessage($subject, $body, $to);
                $sent = $CFSMailer->sendMessage($msg, true);

				if ($sent > 0) {
					$showForm = false;
					$msg = '<p><span class="success">A login link has been sent to your email address - </span><strong>'.$email.'</strong></p>'."\n";
					$msg .= '<p class="mt0">Please check that the password email is not sent to your <span class="obligatory" style="font-size:14px;"><strong>JUNK MAIL</strong></span> folder.<br /><br /><br /></p>';
					$query = "update cf_users set password = '".md5($password)."' where email_address = '".trim($email)."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) { 
						$msg = '<p class="error">There was an error modifying the ChristianFlatShare.org database. Please contact '.TECH_EMAIL.'</p>'."\n";
					}
				} else {
					$showForm = true;
					$msg = '<p class="error">There was an error sending you an email. Please contact '.TECH_EMAIL.'</p>';
				}
			}
		}
		
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Forgotten Password - Christian Flatshare</title>
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
.style1 {
	font-size: 13px;
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
		<div id="header_forgotten_password" class="header_no_h2">
			<h1 class="m0">Forgotten Password</h1>
		</div>		
		<?php print $msg?>	
		<?php if ($showForm) { ?>
		<p class="mt0">Enter your email address below and click  &quot;Send me a new password&quot;.</p>
		<p class="mt0">		  A new  computer-generated password will be  sent to the registered email address.		</p>
		<form name="reminder" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
		  <table border="0" cellpadding="0" cellspacing="6" class="sidePadding">
			<tr>
				<td align="right">Email: </td>
				<td><input name="email" type="text" class="inputElement" id="email" value="<?php print $email?>" style="width:200px;" /></td>
				<td class="error"><?php print $error['email']?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="Submit" value="Send me a new password" /></td>
				<td class="error">&nbsp;</td>
			</tr>
                  
		</table>
		</form>
		<?php } ?>
		<p class="mb0"><a href="index.php">Return to the welcome page</a></p>
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
