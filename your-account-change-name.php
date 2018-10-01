<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	


	if (isset($_POST['email'])) { $email = trim($_POST['email']); } else { $email = NULL; }
	if (isset($_POST['first_name'])) { $first_name = trim($_POST['first_name']); } else { $first_name = NULL; }
	if (isset($_POST['surname'])) { $surname = trim($_POST['surname']); } else { $surname = NULL; }
	if (isset($_POST['agree'])) { $agree = $_POST['agree']; } else { $agree = NULL; }
	if (isset($_POST['news_opt_in'])) { $news_opt_in = intval($_POST['news_opt_in']); } else { $news_opt_in = 0; }
	if (isset($_POST['cancel'])) { header("Location:index.php"); exit; } // Redirect if user pressed the cancel button
	$showForm = TRUE; // By default, show the registration form
	
	if ($_POST) {
	
		// Validate, then handle registration
		if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {
			$error['email'] = '<span class="error">Please enter a valid email</span><br/>';
		} else {
			// validating email address entered by user if already registered.
			if ($email != $_SESSION['u_email']) {
				$query = "SELECT * FROM cf_users 
				          WHERE email_address = '".$email."'
									AND email_address NOT IN (SELECT email_address 
									                      FROM cf_users 
																				WHERE user_id = '".$_SESSION['u_id']."')";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if (mysqli_num_rows($result)) {
					$error['email'] = '<span class="error">A user is already registered with this email address</span><br/>';
				}
			}
		}
		
		if (trim($first_name) == "") { $error['first_name'] = '<span class="error">Please enter your first name</span><br/>'; }
		if (trim($surname) == "") { $error['surname'] = '<span class="error">Please enter your surname</span><br/>'; }


		// If errors occured
		if ($error) {
			$msg = '<p class="error">Errors were found in your entries. Please review.</p>'."\n";
		} else {
			// Update database
			$query  = "update cf_users set ";
			$query .= "last_updated_date = now(),"; // last_updated_date
			$query .= "email_address = '".$email."',";
			$query .= "first_name = '".trim($first_name)."',";
			$query .= "surname = '".trim($surname)."',";
			$query .= "news_opt_in = '".intval($news_opt_in)."' ";
			$query .= "where user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$debug .= debugEvent("Update query",$query);
			$showForm = FALSE; // don't show form
			if ($result) {
                if ($_SESSION['u_email'] != $email) {
                    // Send Email
                    $CFSMailer = new CFSMailer();
                    
                    // Get Body
                    $body = $twig->render('emails/change_email.html.twig', array(
                        'first_name' => $first_name,
                        'new_email' => $email,
                        'old_email' => $_SESSION['u_email'],
                        'password' => $password,
                    ));
                    
                    // Set variables
                    $subject = 'Christian Flatshare email address changed';
                    $to = array($email, $_SESSION['u_email']);
                    
                    $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
                    $sent = $CFSMailer->sendMessage($msg);
            		
                    if ($sent > 0) {	
                        $msg = '<p class="success">Your email address was changed. We have sent you a notification email.</p>'."\n";
                    } else{
                        $msg = '<p class="error">There was an error sending you the activation email. Please contact '.TECH_EMAIL.'</p>'."\n";					
                    }
			  } // End if email address changed 
				
                // Change the user_email session variable (in case the user has updated his/her email addrtess
                $_SESSION['u_name'] = trim($first_name).' '.trim($surname);
                $_SESSION['u_email'] = $email;
                $msg .= '<p class="success">Your account has been successfully updated</p>'."\n";
			}
            else {
				die(mysqli_error());
				$msg = '<p class="error">An error occured when updating your account. Please contact '.TECH_EMAIL.'</p>'."\n";
			}
		}	
	}		
// } else { // No $_POST[
	
	 if (!$_POST) {
		$query = "select email_address, first_name, surname, date_format(dob,'%d/%m/%Y') as `dob`, gender, hear_about, news_opt_in from cf_users where user_id = '".$_SESSION['u_id']."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
		
			$udata = mysqli_fetch_assoc($result);
			$email = $udata['email_address'];
			$first_name = $udata['first_name'];
			$surname = $udata['surname'];
			$news_opt_in = intval($udata['news_opt_in']);
		}	
	
//	}	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your Account - Name Change</title>
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
		<div class="cl">
			<h1 class="mt0">Your account -  name change </h1>
			<?php print $msg?>
			<?php //if ($showForm) { ?>
			<p>Use the form below to change your acount details.</p>			
			<form name="register" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
			<div class="fieldSet">
			<div class="fieldSetTitle">Your personal details</div>
				<table border="0" cellpadding="0" cellspacing="10">
					<tr>
						<td align="right"><span class="obligatory">*&nbsp;</span>First Name:</td>
						<td><?php print $error['first_name']?><input name="first_name" value="<?php print stripslashes($first_name)?>" type="text" id="first_name" size="40" maxlength="40" /></td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span>&nbsp;Surname:</td>
						<td><?php print $error['surname']?><input name="surname" value="<?php print stripslashes($surname)?>" type="text" id="surname" size="40" maxlength="40" /></td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span>&nbsp;Email Address:</td>
						<td><?php print $error['email']?><input name="email" value="<?php print $email?>" type="text" id="email" size="50" maxlength="100" /></td>
					</tr>					
				</table>
			</div>
			<div class="fieldSet">
				<div class="fieldSetTitle">CFS News subscription </div>
				<table border="0" cellpadding="0" cellspacing="10">
					<tr>
						<td><input name="news_opt_in" type="checkbox" id="news_opt_in" value="1" <?php if ($news_opt_in) { ?>checked="checked"<?php } ?>/></td>
						<td>May we send you CFS news? - <span class="grey">we will never pass your details to any third parties</span></td>
					</tr>
				</table>
				<p class="mb0"><input type="submit" name="submit" value="Save changes" />&nbsp;<input type="submit" name="cancel" value="Cancel"/></p>
			</div>
			</form>
			<?php // } ?> <!-- End show form -->
		</div>
		<div class="cs" style="width:20px; height:10px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
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
