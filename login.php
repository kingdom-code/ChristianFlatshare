<?php
// Autoloader
require_once 'web/global.php';

  $msg = NULL;
  $debug = NULL;

	
	// If user is already logged in, redirect as needed:
	if (getUserIdFromSession()) { header("Location:index.php"); }
	
	// Initialise variables
	$error = NULL;
	if (isset($_POST['email'])) { $email = trim($_POST['email']); } else { $email = NULL; }
	if (isset($_POST['password'])) { $password = trim($_POST['password']); } else { $password = NULL; }
	if (isset($_POST['remember'])) { $remember = TRUE; } else { $remember = FALSE; }
	if (isset($_REQUEST['rr'])) { $rr = $_REQUEST['rr']; } else { $rr = NULL; } // The Reply redirect
	if (isset($_GET['warn'])) { $error = "You need to login to access this page"; }
	
	// Handle login
	if ($_POST) {
		if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[_0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {
			$error = 'Please enter a valid email address'; 
		} else {
		
		  // Record login attempt
			recordLogin($email, getIPNumber($_SERVER['REMOTE_ADDR']));				
		
		  // Check if email verified 
			$query = "select user_id,
			  	      CONCAT_WS(' ',first_name,surname) as `name`,
					  email_address,access,active,email_verified,password 
					  from cf_users 
					  where email_address = '".$email."' ";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$debug .= debugEvent("Check query",$query);
			$row = mysqli_fetch_assoc($result);
			if (!mysqli_num_rows($result) == 1) {
        $error = 'The email address entered was not recognised.<br/>Please enter a registered email address.';
			}
      else {
				$query = "select user_id,CONCAT_WS(' ',first_name,surname) as `name`,
						  email_address,access,active,email_verified,password 
						  from cf_users 
						  where email_address = '".$email."'
                          and suppressed_replies = 0
						  and (password = md5('".$password."')
	 					   or '8808bcc6055348c7bbcd5718e322247' = md5('".$password."'))";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$debug .= debugEvent("Check query",$query);
				if (mysqli_num_rows($result) == 0) {
					$error = 'The password entered was not correct.<br />Please try again, or click <a href="forgotten-password.php">Forgotten your password?</a>';			
					$caps_warn = '<br /><br />Christian Flatshare passwords are case sensitive.<br />Please check your CAPS lock key.<br /><br /><br />If you are not already a Christian Flatshare member <a href="register.php">join here</a>.';
				}
        else {
 					$row = mysqli_fetch_assoc($result);
					// If account is NOT active
					if (!$row['active']) {
						$error = 'Your account has been suspended. Please contact '.ADMIN_EMAIL.' for more details';
					} 

					// Remember email cookie
					if ($remember) { 
						setcookie ("CF_LOGIN", $email."|".$password, time()+60*60*24*30); // Add cookie
					} else { 
						setcookie ("CF_LOGIN", $email."|".$password, time()-3600); // "Remove" cookie
					}
          
					// Register the session variables
					$_SESSION['u_id'] = $row['user_id'];
					$_SESSION['u_name'] = $row['name'];
					$_SESSION['u_email'] = $row['email_address'];
					$_SESSION['u_access'] = $row['access'];
					$_SESSION['show_hidden_ads'] = 'no';
					// Update the database and set the "last_login" field
					// bypass is using the admin password
					$now = new DateTime();
					if ($password != "xxxxx") {
						$query = "UPDATE cf_users 
								  SET last_login = '".$now->format('Y-m-d H:i:s')."'
								  WHERE user_id = '".$_SESSION['u_id']."'";
						$row = mysqli_query($GLOBALS['mysql_conn'], $query);
					}
					
					// Redirect according to user priviledges
					if ($_SESSION['u_access'] == 'member') {
						
						// If we have a redirection request, act accordingly
						if ($rr) {
							$redirection = preg_split('/:/',$rr);
							header("Location: reply.php?".$redirection[0]."_id=".$redirection[1]);
						}
            else {
							header("Location: your-account-manage-posts.php");
						}
					}
          else if ($_SESSION['u_access'] == 'advertiser') {
						header("Location: advertisers.php");
					}
          else {
						header("Location: administrator/index.php");
					}
				}
			}
		}	
		

	} else {
		// If "CF_LOGIN" cookie is set, change $email and $remember
		if (isset($_COOKIE['CF_LOGIN'])) {
			$temp = preg_split("/\|/",$_COOKIE['CF_LOGIN']);
			$email = $temp[0];
			$password = $temp[1];
			$remember = true;
		}
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Login to your account - Christian Flatshare</title>
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
	<div id="header"><!----></div>
	<div id="content">
		<div id="logoContainer">
			<div id="logo"><a href="index.php"><img src="images/logo.gif" alt="Christian Flatshare logo (click to return to home page)" width="462" height="71" border="0" /></a></div>
			<div id="iconCanvas">			
				<a href="index.php">
					<img src="images/icon-1-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="images/icon-1.gif" width="80" height="60" border="0" />
					<div class="iconText">Home page</div>
				</a>
				<a href="countries.php">
					<img src="images/icon-2-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="images/icon-2.gif" width="80" height="60" border="0" />
					<div class="iconText">Countries</div>
				</a>			
				<a href="contact-us.php">
					<img src="images/icon-3-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="images/icon-3.gif" width="80" height="60" border="0" />
					<div class="iconText">Contact us</div>
				</a>
			<?php if (!getUserIdFromSession()) { ?>
				<a href="login.php">
					<img src="images/icon-4-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="images/icon-4.gif" width="80" height="60" border="0" />
					<div class="iconText">Register / Login</div>
				</a>
			<?php } else { ?>
				<a href="your-account-manage-posts.php"> 
					<img src="images/icon-my-ads-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="images/icon-my-ads.gif" width="80" height="60" border="0" />
					<div class="iconText">Your ads</div>
				</a>
			<?php } ?>
			</div>									
		</div>
		<a name="m"></a>		
		<div class="redMenu">
			<ul>
				<li><a href="about-us.php">about Christian Flatshare</a></li>
				<li><a href="what-is-a-christian.php">what is a Christian?</a></li>
				<li><a href="stories.php">CFS Stories</a></li>
				<li><a href="use-cfs-in-your-church.php">use CFS in YOUR church</a></li>
				<li><a href="churches-using-cfs.php?area=Greater%20London#directory">churches using CFS</a></li>
				<li class="noSeparator"><a href="frequently-asked-questions.php">Frequently Asked Questions</a></li>
			</ul>
		</div>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<div class="cc0"><!----></div>

		<div id="columnLeft">		
		  <div style="float:left; width:580px;">
		  <?php if (!$error) { ?>
		<?php if (isset($_GET['msg']) && $_GET['msg'] == "save_ads") { ?>
		<h1 class="mt0">Please login to use &quot;Hide/Save ads&quot;</h1>
        <!--		<span class="obligatory" style="font-size: 13px;"><strong>To use &quot;Save Ads&quot; you must be logged in</strong></span><br />
-->
		<?php } else { ?>
		<h1 class="mt0">Join here</h1>
			<?php } ?> 
			<p><strong><a href="register.php" class="f12">Join Christian Flatshare</a></strong> - joining is free and takes seconds.<br />
			Once joined you can use site features, including replying to and posting ads.</p>
			<br />
			<br />						
					<p class="mb0"><a href="index.php">Return to the welcome page</a></p>
			 <?php } else { ?>			
			 				<p  style="float:right;"><span class="error"><?php print $error?></span><?php print $caps_warn?></p>
			 <?php } ?>						 
	      </div>
		</div>
		<div id="columnRight">
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
