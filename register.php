<?php

use CFS\Mailer\CFSMailer;
use CFS\Database\CFSDatabase;

// Autoloader
require_once 'web/global.php';

// If user logged in redirect them
if (isset($currentUser) && !empty($currentUser)) {
    header('Location: your-account-manage-posts.php');
    exit;
}

$database = new CFSDatabase();
$connection = $database->getConnection();

// Get number of churches using CFS
$sql = "SELECT count(*) AS churches FROM cf_church_directory";
$numChurches = $connection->prepare($sql);
$numChurches->execute();
$stats['churches'] = $numChurches->fetchColumn(0);

// Process any variables

$email                  = (isset($_POST['email'])) ? trim($_POST['email']) : NULL;
$email_verification     = (isset($_POST['email_verification'])) ? trim($_POST['email_verification']) : NULL;
$first_name             = (isset($_POST['first_name'])) ? trim($_POST['first_name']) : NULL;
$surname                = (isset($_POST['surname'])) ? trim($_POST['surname']) : NULL;
$password               = (isset($_POST['password'])) ? trim($_POST['password']) : NULL;
$church_attended        = (isset($_POST['church_attended'])) ? trim($_POST['church_attended']) : NULL;
$hear_about             = (isset($_POST['hear_about'])) ? trim($_POST['hear_about']) : NULL;
$agree                  = (isset($_POST['agree'])) ? trim($_POST['agree']) : NULL;
$news_opt_in            = (isset($_POST['news_opt_in'])) ? trim($_POST['news_opt_in']) : 0;
$comments               = (isset($_POST['comments'])) ? trim($_POST['comments']) : NULL;
if (!empty($_POST))
 {
   $advertising            = (isset($_POST['advertising'])) ? trim($_POST['advertising']) : NULL;
 } else { 
   $advertising            = (isset($_GET['advertising'])) ? trim($_GET['advertising']) : NULL;
 } 
$showForm               = TRUE; // By default, show the registration form

if (!empty($_POST)) {

	// Validate, then handle registration
	if (!preg_match(REGEXP_EMAIL,$email)) {
		$error['email'] = '<span class="error">Please enter a valid email</span><br/>';
	}
    else {
		// Ensure both passwords are identical
		if ($email != $email_verification) {
			$error['email'] = '<span class="error">Email addresses don\'t match</span><br/>';
		}
        else {
			// Validating email address entered by user if already registered.
            $sql = "SELECT count(*) AS users FROM cf_users WHERE email_address = :email";
            $numUsers = $connection->prepare($sql);
            $numUsers->bindValue("email", $email);
            $numUsers->execute();
            $result = $numUsers->fetchColumn(0);
            
			if ($result > 0) {
				$error['email'] = '<span class="error">A member is already registered with this email address</span><br/>';
			}
		}
	}
    if (trim($comments) == "" && $advertising == TRUE) { $error['comments'] = '<span class="error">Please enter your company or organisation name</span><br/>'; }
    if (trim($first_name) == "") { $error['first_name'] = '<span class="error">Please enter your first name</span><br/>'; }
    if (trim($surname) == "") { $error['surname'] = '<span class="error">Please enter your surname</span><br/>'; }
	if ($password == "") {
		$error['password'] = '<span class="error">Please enter a password</span><br/>';
	} else if (strlen($password) < 5) {
		$error['password'] = '<span class="error">Password must be 5 characters minimum<span><br/>';
	}		
    if (!$agree) { $error['agree'] = '<span class="error">Please read the terms and conditions before proceeding</span><br/>'; }

	// If not errors occured
	if (!$error) {
        // Insert new user into DB
        $sql = "INSERT INTO cf_users SET
            created_date = now(),
            last_updated_date = now(),
            email_address = :email,
            first_name = :first_name,
            surname = :last_name,
            password = md5(:password),
            church_attended = :church_attended,
            hear_about = :hear_about,
            news_opt_in = :news_opt_in,
            email_verified = 1";
        $newUser = $connection->prepare($sql);
        $newUser->bindValue("email", $email);
        $newUser->bindValue("first_name", $first_name);
        $newUser->bindValue("last_name", $surname);
        $newUser->bindValue("password", $password);
        $newUser->bindValue("church_attended", $church_attended);
        $newUser->bindValue("hear_about", $hear_about);
        $newUser->bindValue("news_opt_in", $news_opt_in);
        $result = $newUser->execute();
	    
        if ($advertising == TRUE) {
            $sql = "UPDATE cf_users SET
                access = 'advertiser',
                comments = :comments
            WHERE email_address = :email";
            $updateAdvertiser = $connection->prepare($sql);
            $updateAdvertiser->bindValue("comments", $comments);
            $updateAdvertiser->bindValue("email", $email);
            $updateAdvertiser->execute();				
        }
        
        // If insertion is successful them email user
        if ($result) {
            $ip_address = trim($_SERVER['REMOTE_ADDR']);
            recordLogin($email, getIPNumber($ip_address));		
            
            // If user is joining from a scam ip address, set to suppress
            $sql = "UPDATE cf_users SET
                suppressed_replies = 1
            WHERE email_address = :email AND
                :ip_address IN (SELECT DISTINCT ip FROM cf_scam_ips WHERE ip IS NOT NULL)";
            $updateScammer = $connection->prepare($sql);
            $updateScammer->bindValue("email", $email);
            $updateScammer->bindValue("ip_address", $ip_address);
            $updateScammer->execute();		
            
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/register_confirmation.html.twig', array(
                'first_name' => $first_name,
                'email' => $email,
                'password' => $password,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare registration confirmation';
            $to = $email;
            
			// BCC if advertiser
		    $bcc = ($advertising == TRUE) ? array('ryanwdavies@hotmail.com' => 'Ryan Davies') : NULL;
            
            $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
            $sent = $CFSMailer->sendMessage($msg);
            
			if ($sent > 0) {	
                $showForm = FALSE;
            }
            else {
                $msg = '<p class="error">There was an error sending you the activation email. Please contact '.TECH_EMAIL.'</p>'."\n";
			}
		}
        else {
			$msg = '<p class="error">An error occured adding you to the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>'."\n";
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Join - Christian Flatshare</title>
        <!-- Styles -->
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
    </head>
<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<div class="cc0"><!----></div>
	

		<?php if ($_GET['msg'] == "save_ads") { ?>
		<h2 class="red">To use the &quot;Save Ads&quot; feature, you must be <a href="login.php">logged in</a>.<br />
		If you're not a member, please use the form below to join Christian Flatshare. </h2>
		<?php } ?>
		<?php if ($showForm) { ?>
		<?php if ($advertising) { ?>
			<h1 class="mt0">Christian Flatshare Advertising Account</h1>		
		<?php } else { ?>
			<h1 class="mt0">Joining Christian Flatshare</h1>				
		<?php } ?>
        <p>Join Christian Flatshare to post an advert or <a href="/">login</a>.</p>
		
		<?php if ($advertising) { ?>		
		<p class="mb20">Christian Flatshare advertising accounts are available for those that wish to advertisie on CFS.<br /><br /><strong><span class="obligatory">NOTE:</span></strong> This is <strong>not for accommodation adverts</strong> - <a href="register.php">for accommodation ads register here</a>.
		<!-- <br /><br />Please complete the form below and click login to get started.</p> -->
		<?php } else { ?>		
<!--		<p>If you enjoy Christian Flatshare, please share it with others.<br />
		Christian Flatshare (CFS) has been supported by the leadership of <?php print $stats['churches']?> churches since January 2007 (see <a href="churches-using-cfs.php" target="_blank">churches using CFS</a>).
		<br /> 
-->

		<?php } ?>						

		</p>
		<form name="register" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
		<?php print $msg?>
		<div class="fieldSet">
			<table border="0" cellpadding="0" cellspacing="10">
                           <?php if (! $advertising) { ?>
				<tr>
					<td align="right"></td>
					<td width="373"><a href="<?php print $CFSFacebook->getLoginURL(); ?>" class="fb-login">Join using Facebook</a><p class="fb-info">- We DO NOT publish anything in your News Feed<br /> - Joining using Facebook <a href="facebook-your-ad.php" target="_blank">allows you to see mutual friends you have with others</a>&nbsp;<br /></p></td>
				</tr>	
				<tr>
                           <?php } ?>
					<td align="right"><span class="obligatory">*</span>&nbsp;First name:</td>
					<td width="373"><?php print $error['first_name']?>
				  <input name="first_name" value="<?php print $first_name?>" type="text" id="first_name" size="25" maxlength="50" style="padding:2px; font-size: 12px;"/></td>
				</tr>				
				<tr>
					<td align="right"><span class="obligatory">*</span>&nbsp;Surname:</td>
					<td><?php print $error['surname']?><input name="surname" value="<?php print $surname?>" type="text" id="surname" size="25" maxlength="50" style="padding:2px; font-size: 12px;"/></td>
				</tr>		
				<?php if ($advertising) { ?>
				<tr>
					<td align="right"><span class="obligatory">*</span>&nbsp;Company or Organisation:</td>
					<td><?php print $error['comments']?><input name="comments" value="<?php print $comments?>" type="text" id="comments" size="25" maxlength="50" style="padding:2px; font-size: 12px;"/></td>
				</tr>		
		  	<?php } ?>


				<tr>
					<td align="right"><span class="obligatory">*</span>&nbsp;Email address:</td>
				  <td><?php print $error['email']?><input name="email" value="<?php print $email?>" type="text" id="email" size="50" maxlength="100" style="padding:2px; font-size: 12px;"/></td>
				</tr>
				<tr>
				  <td align="right"><span class="obligatory">*</span>&nbsp;Verify email:</td>
				  <td><?php print $error['email_verification']?><input name="email_verification" value="<?php print $email_verification?>" type="text" id="email_verification" size="50" maxlength="100" style="padding:2px; font-size: 12px;"/></td>
				</tr>
				<tr>
					<td align="right"><span class="obligatory">*</span> Password:</td>
				  <td><?php print $error['password']?><input name="password" value="<?php print $password?>" type="password" id="password" size="14" maxlength="100" style="padding:2px; font-size: 12px;"/> 
					  <span class=grey>CFS password (min five characters long)</span></td>
			  </tr>		
					<tr>
					</tr>		
					<tr>
			 <tr class="formNoPadding">
			   <td align="right">Church connection:<br/></td>
	   <td><input name="church_attended" value="<?php print $church_attended?>" type="text" id="church_attended" size="45" maxlength="255" style="padding:2px; font-size: 12px;"/> 
			     <span class="grey">church attended/connection (if one)</span></td>
			  </tr>
				<tr class="formNoPadding">
					<td align="right" valign="top">Where did you<br /> 
						hear about CFS:<br/></td>
				  <td><input name="hear_about" value="<?php print $hear_about?>" type="text" id="hear_about" size="72" maxlength="255" style="padding:2px; font-size: 12px;"/><br/>
				  	  <span class="grey">e.g.  friend at St. John's Nottingham, Google, a postcard in St. Luke's Sheffield</span></td>
				</tr>
					
			</table>
		
			<table border="0" cellpadding="0" cellspacing="10">
				<tr>
				  <td align="right" style="padding-left:130px"><span class="obligatory">*</span></td>
					<td ><input name="agree" type="checkbox" id="agree" value="checkbox" /></td>
					<td><label for="agree">I have read the <a href="terms-and-conditions.php" target="_blank">terms and conditions</a> and agree with them </label><?php print $error['agree']?></td>
				<tr>
				  <td ></td>
					<td><input name="news_opt_in" type="checkbox" id="news_opt_in" value="1"  /></td>
					<td><label for="news_opt_in">May we occasionally send you news about Christian Flatshare?</label></td>
				</tr>
			</table>
			<br />
 		<p class="m0 mb20" style="padding-left:160px"><input type="submit" name="submit" value=" Register " />		  
		<br />
			</p>
			
		</div>
			<input type="hidden" name="advertising" value="<?php print $advertising?>" />
		</form>
		<?php } else { ?>
		<div id="columnLeft">		
			<div id="cancel_box" class="dialog_box">
			<div class="dialog_canvas">
				<h1 class="m0">A confirmation email was sent to you</h1>
				<div class="dialog_text">
					<p class="mt0 mb0">We have sent a membership confirmation email to:</p>
					<p class="mt10 mb0" align="center"><strong><?php print  $email  ?></strong></p>
					    <br />If your <b>junk mail settings</b> or <b>company firewall</b> have blocked this email, other Christian Flatshare notifications (such as new message alerts) are likely to be blocked in the same way.</p>
					<p class="mb0" align="center"><strong>Please check that you have received this email.</strong></p>
				</div>
				<div class="dialog_buttons">
				<table cellpadding="0" cellspacing="0" align="center">
					<tr>
						<td style="padding-right:10px;">
							<input type="button" value="Okay, I'll check" onclick="javascript:$('cancel_box').setStyle('display','none');"  style="width:120px;"/>
						</td>
					</tr>
				</table>
				</div>
			</div>
			</div>
		
		<h2 class="success mb0">Thank you for joining Christian Flatshare</h2>
		<p class="mt15">You can now login using the email address and password provided, (just click Login).</p>
			<!--	<p class="mt5">To prevent Christian Flatshare emails being treated as JUNK mail, you should add &quot;<strong>ChristianFlatShare.org</strong>&quot;<br /> 
		to your safe senders list. </p> -->

   <!--		<p class="mb0"><a href="index.php">Return the welcome page</a></p>	 -->
    <div style="line-height:0px;
    height:410px;
    width:0px;
    float:left;
    padding:0px;"><!----></div>
				
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
			
		<?php } ?>
<script language="javascript" type="text/javascript" src="includes/slimbox-new/slimbox.js"></script>
<link href="includes/slimbox-new/slimbox.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
.style1 {font-weight: bold}
-->
</style>
<link href="styles/dialog_box.css" rel="stylesheet" type="text/css" />

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
