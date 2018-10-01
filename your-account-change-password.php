<?php

// Autoloader
require_once 'web/global.php';

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
//	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	
	
	if (isset($_POST['new_pass_1'])) { $new_pass_1 = trim($_POST['new_pass_1']); } else { $new_pass_1 = NULL; }
	if (isset($_POST['new_pass_2'])) { $new_pass_2 = trim($_POST['new_pass_2']); } else { $new_pass_2 = NULL; }
	if (isset($_POST['cancel'])) { header("Location:index.php"); exit; } // Redirect if user pressed the cancel button
	$showForm = TRUE; // By default, show the change password form
	
	if ($_POST) {
	    if (!preg_match('/^(?=.*[a-zA-Z]).{2,16}$/',$new_pass_1)) { // New password entered?
			$error['new_pass_1'] = '<span class="error">Please enter a valid password</span><br/>';
		} else if (!$new_pass_2) { // Verification of new password entered?
			$error['new_pass_2'] = '<span class="error">Please verify your password</span><br/>';
		} else if ($new_pass_1 != $new_pass_2) { // Verification matches new password?
			$error['new_pass_2'] = '<span class="error">Passwords do not match</span><br/>';
		}
		// If errors occured
		if (!$error) {
			// Update database with new password
			$query = "update cf_users set password = '".md5($new_pass_1)."', last_updated_date = now() where user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$debug .= debugEvent("Update password query",$query);
			$showForm = FALSE; // don't show form
			if ($result) {
				$msg  = '<p class="success">Your password has been successfully changed</p><br /><br />'."\n";
				$msg .= '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';				
				$msg .= '<p><a href="index.php">Home page</a>&nbsp;|&nbsp;<a href="your-account-manage-posts.php">Your ads page</a></p>'."\n";
			} else {
				$msg = '<p class="error">An error occured when updating your password. Please contact '.TECH_EMAIL.'</p>'."\n";
			}
		}	
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your Account - Change Password</title>
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
			<h1 class="mt0">Your account - change password </h1>
			<?php print $msg?>
			<?php if ($showForm) { ?>
			<p>Use the form below to change your password.<br /><?php if ($currentUser['facebookEnabled']) { ?><br /><b>Note</b> - you are currently logged in using your Facebook account.<br /><br /><?php } ?>
			</p>
			<form name="changePass" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
			<div class="fieldSet">
			<div class="fieldSetTitle">Password details</div>
				<table border="0" cellpadding="0" cellspacing="10">
					<tr>
						<td align="right"><span class="obligatory">*&nbsp;</span>New password :</td>
						<td><?php print $error['new_pass_1']?><input name="new_pass_1" type="password" id="new_pass_1" size="32" maxlength="32" /></td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span>&nbsp;Repeat new password :</td>
						<td><?php print $error['new_pass_2']?><input name="new_pass_2" type="password" id="new_pass_2" size="32" maxlength="32" /></td>
					</tr>
				</table>
				<p class="mb0"><input type="submit" name="submit" value="Change password" />
				&nbsp;<input type="submit" name="cancel" value="Cancel"/></p>
			</div>
			</form>			
			<?php } ?>
		</div>
		<div class="cs" style="width:20px; height:270px;"><!----></div>
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
