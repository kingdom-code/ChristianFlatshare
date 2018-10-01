<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

	$remove_msg = "";
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:your-account-manage-posts.php"); exit; }
        if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }

	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }	
	
	// Ensure we have a valid ad, get user_id & email and do the hash check.
	$query = "select account_suspended from cf_users where user_id = ".$_SESSION['u_id'];
	$result= mysqli_query($GLOBALS['mysql_conn'], $query);
	// Continue, only if we have a valid return
	if (!mysqli_num_rows($result)) {
		header("Location:your-account-manage-posts.php"); exit; 
	} else {
		$user_record = mysqli_fetch_assoc($result);	
	}

	
	// If we're temporarily suspending, unsuspending an ad, or to republish 
	switch($action) {
			case "suspend":
			$query = "update cf_users set account_suspended = '1' where user_id = '".$_SESSION['u_id']."' ";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				$query = "update cf_offered set suspended = '1' where user_id = '".$_SESSION['u_id']."' ";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);						
				
				$query = "update cf_wanted set suspended = '1' where user_id = '".$_SESSION['u_id']."' ";						
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);			
				
				header("Location:your-account-manage-posts.php"); exit; 
			} else {
				$msg = '<p class="error"><strong>There was a problem suspendeding your account. Please contact Christian Flatshare.</strong></p>';
			}
			break;
			
			case "unsuspend":
			$query = "update cf_users set account_suspended = '0' where user_id = '".$_SESSION['u_id']."' ";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				$account_unsuspended = TRUE;		
			}
			break;			
	}
	
	if ($user_record['account_suspended'] == 1) {
//						header("Location:your-account-manage-posts.php"); exit; 
	}

	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Suspend your account - Christian Flatshare</title>
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
			<?php if ($account_unsuspended) { ?>
			<h1 class="mt0 mb2">Account un-suspended</h1>
			<h2 class="mt0 mb0">your account had been un-suspended</h2>
			<p class="success"><strong>You account has been un-suspended.</strong></p>
			
			
			<?php } else { ?>
			<h1 class="mt0 mb2">Suspend your account</h1>
			<h2 class="mt0 mb0">suspend your adverts and account, so others know...</h2>
			<div class="clear"><!----></div>
			
			<br />
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-left:15px">
				If you have finished using Christian Flatshare for the time being, we recommend you suspend your account.<br />
				You can un-suspend it at any time.
				</td>
			</tr>
			<tr>
				<td style="padding-left:15px;padding-bottom:15px;padding-top:10px">
				 <div class="mt10" style="width:400px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
			    <p class="mt0 mb0"><a href="<?php print $_SERVER['PHP_SELF']?>?action=suspend">Click here to suspend your account</a> &nbsp;<img src="images/photo-loader.gif" width="16" height="16" id="photo_loader" style="display:none;"/>    		</p>
				</div>
				</td>
			</tr>				
			
			<tr>
				<td style="padding-left:15px;">
				<p class="mb10 mt5">Suspending your account does three things:</p>
				<li>Suspends any adverts you have published</li>
				<li>Stops you from being alerted if someone replies to a message you have sent them</li>
				<li>Informs anyone replying to messages you have sent them that your account is suspended and that you will not be alerted to the arrival of their new message. Their message will still be sent to you, shown in Your messages.</li>
				</td>
			</tr>
			
			<tr>
				<td style="padding-left:15px;padding-bottom:5px;padding-top:15px">
				While your account is suspended you cannot:
				</td>
			</tr>			
			<tr>
				<td style="padding-left:15px">
				<li>Reply to messages or adverts</li>
				<li>Post new adverts</li>
				</td>
			</tr>		
			<tr>
				<td style="padding-left:15px;padding-bottom:5px;padding-top:25px">
				<b>However</b>, while your account is suspended you can still:
				</td>
			</tr>			
			<tr>
				<td style="padding-left:15px">
				<li>Share Christian Flatshare with your friends and church leadership</li>
				<li>Print this <a href="http://<?php print SITE?>A4%20CFS%20Poster.pdf"  target="_blank">lovely poster</a> for your church notice board</li>
				<li>Pray for the ministry Christian Flatshare and those other trying to connect with the local church through it</li><br />Thank you.
				</td>
			</tr>						
			</table>
			
			</p>
		    <?php } ?>				
		  </p>
		</div>
		<div class="cs" style="width:20px; height:270px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
			<?php print sharingCFS()?>					
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
