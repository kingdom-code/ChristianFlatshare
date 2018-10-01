<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

   // Dissallow access if user not logged in
   if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
   
   	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
   
   if (!isset($_REQUEST['reply_id'])) { header("Location:your-account-manage-posts.php"); exit; } else { $reply_id = $_REQUEST['reply_id']; }
   if (!isset($_REQUEST['reply_hash'])) { header("Location:your-account-manage-posts.php"); exit; } 
   		else { $reply_hash = $_REQUEST['reply_hash']; }   
   if (!isset($_REQUEST['class'])) { header("Location:your-account-manage-posts.php"); exit; } else { $class = $_REQUEST['class']; }		
	
	// First, find out if we're dealing with a valid id.
	$query = "select reply_id, from_user_id 
	          from cf_email_replies 
			  where reply_id = '".$reply_id."';";
			  
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!mysqli_num_rows($result)) { header("Location: your-account-manage-posts.php"); exit; }			
	$hash_test = mysqli_fetch_assoc($result); 
	
	// Exit if hash does not validates
  	if ($reply_hash != md5($hash_test['reply_id'].$hash_test['from_user_id'])) {
      header("Location: your-account-manage-posts.php"); exit;
    }
	  
	if (isset($_POST['delete'])) { 
	
		// User has clicked on the delete button: 
		//Insert comments
		$query = "
		INSERT INTO cf_feedback (ad_id, post_type, feedback, feedback_date, user_id)
		  VALUES (".$id.",'".$post_type."','".$cfs_feedback.$cfs_feedback2."', now(), ".$_SESSION['u_id'].");";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		if ($result) {
		    if ($cfs_feedback != "" || $cfs_feedback2 != "" ) {
			header("Location: your-account-manage-posts.php?report=deletionSuccessThankyou"); exit;	 
			} else {
			header("Location: your-account-manage-posts.php?report=deletionSuccess"); exit;
			}
		} else {
			header("Location: your-account-manage-posts.php?report=deletionFailure"); exit;
		}
		
	} else if (isset($_POST['cancel'])) {
	
		header("Location:your-account-manage-posts.php");
		exit;	
	
	} else {
	
		// Show details for the email we're about to delete
		$query = "select * from cf_email_replies where reply_id = '".$reply_id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$email_reply = mysqli_fetch_assoc($result);
		$summary = createSummaryV2($ad,$post_type,"odd",true);
		
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your ads - Email deletion - Christian Flatshare</title>
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
		<h1 class="mt0">Please confirm email deletion</h1>
		<?php print $summary;?>
		<form name="deletion" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
		<input type="hidden" name="post_type" value="<?php print $post_type?>" />
		<input type="hidden" name="id" value="<?php print $id?>" />
			<table border="0" cellpadding="0" id="replyToAdOwner">
				<tr>
				  <td width="585" align="left" valign="top"><br />
					<strong>Christian Flatshare feeback</strong><br />
					Please share any feedback you have about Christian Flatshare.<br />
					<br />
					<strong>Problems and suggestions...</strong><br />
					<textarea name="cfs_feedback" rows="5" id="cfs_feedback" style="width:100%"><?php print stripslashes($cfs_feedback)?></textarea>
					<br />
					<strong>Things you liked</strong><br />
					<textarea name="cfs_feedback2" rows="5" id="cfs_feedback2" style="width:100%"><?php print stripslashes($cfs_feedback2)?>
					</textarea></td>
					<td width="10"></td>
				</tr>
			</table>
		<p class="mb0 mt0">Advert deletion cannot be undone<br />
		  Delete your advertisment and send your feedback? </p>
		<p >
			<input type="submit" name="delete" value="Delete ad" style="width:70px;" />
			&nbsp;
			<input type="submit" name="cancel" value="Cancel" style="width:50px;" />
		</p>
		</form>
		<p class="mb0"><a href="your-account.php">Back to Your Ads</a></p>
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
