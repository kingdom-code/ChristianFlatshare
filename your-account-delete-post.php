<?php
session_start();

// Autoloader
require_once 'web/global.php';

connectToDB();
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	if (isset($_REQUEST['post_type'])) { $post_type = $_REQUEST['post_type']; }
	if (isset($_REQUEST['ad_type'])) { $post_type = $_REQUEST['ad_type']; } 	// backward compatibility; change of ad_type to post_Type	
	
	if (!isset($_REQUEST['id'])) { header("Location:your-account-manage-posts.php"); exit; } else { $id = $_REQUEST['id']; }
	if (isset($_POST['cfs_feedback'])) { $cfs_feedback = $_POST['cfs_feedback']; } else { $cfs_feedback = NULL; }
	if (isset($_POST['cfs_feedback2'])) { $cfs_feedback2 = $_POST['cfs_feedback2']; } else { $cfs_feedback2 = NULL; }	
	if (isset($_POST['helpful'])) { $helpful = $_POST['helpful']; } else { $helpful = NULL; }		
	if (isset($_POST['photos_added'])) { $photos_added = $_POST['photos_added']; } else { $photos_added = "PHOTOS=".photoCount($id,$post_type); }			
	
	// First, find out if we're dealing with a valid id.
	$query = "select ".$post_type."_id, suspended from cf_".$post_type." 
				where user_id = '".$_SESSION['u_id']."' 
				and ".$post_type."_id = '".$id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!mysqli_num_rows($result)) { header("Location: your-account-manage-posts.php"); exit; }			
	$row = mysqli_fetch_row($result);	
	$suspended = $row['1'];
	
	if (isset($_POST['delete'])) { 
	
		// User has clicked on the delete button: 
		//Insert comments
		
		if ($cfs_feedback != '' || $cfs_feedback2 != '') {
			$cfs_feedback = "Problems and suggestions - ".$cfs_feedback.". I liked - ".$cfs_feedback2;
		}
	
		$query = "
		INSERT INTO cf_feedback (ad_id, post_type, feedback,  helpful, feedback_date, user_id)
		  VALUES (".$id.",'".$post_type."', '".$cfs_feedback."', '".$helpful.' '.$photos_added."', now(), ".$_SESSION['u_id'].");";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		//Move the ad on the appropriate table
		$query = "
		insert into cf_".$post_type."_archive  (
			select *,now() from cf_".$post_type." where ".$post_type."_id = '".$id."'
		);";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$query = "delete from cf_".$post_type." where ".$post_type."_id = '".$id."'";
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
	
		// Show details for the ad we're about to delete
		$query = "select * from cf_".$post_type." where ".$post_type."_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		$summary = createSummaryV2($ad,$post_type,"odd",true);
		
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your ads - Ad deletion - Christian Flatshare</title>
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
		<h1 class="mt0">Please confirm ad deletion</h1>
		<?php print $summary;?>
		<form name="deletion" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
		<input type="hidden" name="post_type" value="<?php print $post_type?>" />
		<input type="hidden" name="id" value="<?php print $id?>" />
		
	
	
			<table border="0" cellpadding="0" id="replyToAdOwner">
				<tr>
					<td width="400" align="left" valign="top"><br />
					<strong>Christian Flatshare feeback</strong><br />
					Please share any feedback you have about Christian Flatshare.<br />
					<br />
					<strong>Problems and suggestions</strong><br />
					<textarea name="cfs_feedback" rows="3" id="cfs_feedback" style="width:100%; padding:2px; font-size:12px;"><?php print stripslashes($cfs_feedback)?></textarea>
					<br />
					<strong>Things you liked</strong><br />
					<textarea name="cfs_feedback2" rows="3" id="cfs_feedback2" style="width:100%; padding:2px; font-size:12px;"><?php print stripslashes($cfs_feedback2)?></textarea>					</td>
					<td width="10"></td>
					<td width="370" valign="top" class="grey"><p class="mt0"><br /><br /><br /><br /><br />
					  <span class="grey">Encouraging comments, stories,  things we should <br />change, improvements you would like to see. <br /><br />                
					  All feedback is read by CFS's administrators. </span></td>
				</tr>
			</table>
			
	<br />
			
<table border="0" cellpadding="0" cellspacing="10">
<tr valign="top">
<td valign="top">	
			<strong>Was Christian Flatshare helpful for you?</strong>	
			<table border="0" cellpadding="0" cellspacing="10">
			<tr>
				<td valign="top"><input name="helpful" type="radio" value="helpful" id="helpful" /></td>
				<td><span class="green"><strong><label for="helpful">Yes, it was helpful</label></strong></span></td>
				</tr>
		
			<tr>
				<td valign="top"><input name="helpful" type="radio" value="helped a little" id="helped a little"/></td>
				<td><span class="pending_payment"><label for="helped a little">It helped a little</label></span></td>
			</tr>
			
			<tr>
				<td valign="top"><input name="helpful" type="radio" value="Not helpful" id="Not helpful"/></td>
				<td><span class="grey"><strong><label for="Not helpful">No, not helpful this time</label></strong></span></td>
			</tr>
			</table>			
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td valign="top">			
		
	 <?php if (photoCount($id,$post_type)	== 0) { ?>
		<strong>Did you add photos to your advert?</strong>			
			<table border="0" cellpadding="0" cellspacing="10">
		
			<tr>
				<td valign="top"><input name="photos_added" type="radio" value="yes photos" id="yes photos" /></td>
				<td><span class="green"><strong><label for="yes photos">Yes</label></strong></span></td>
				</tr>
		
			<tr>
				<td valign="top"><input name="photos_added" type="radio" value="no photos" id="no photos"/></td>
				<td><span class="grey"><strong><label for="no photos">No</label></strong></span></td>
			</tr>
		</table>			
		<?php } ?>
</td>
</tr>
</table>		
<!--
<br />
<strong>Was it easy to use?</strong>	
<table border="0" cellpadding="0" cellspacing="10">
	<tr>
		<td valign="top"><input name="enjoyed_using" type="radio" value="helpful" /></td>
		<td><span class="green"><strong>Yes, a doddle</strong></span></td>
		</tr>
	<tr>
		<td valign="top"><input name="enjoyed_using" type="radio" value="OK" /></td>
		<td><span class="pending_payment">It was okay</span></td>
	</tr>
	
	<tr>
		<td valign="top"><input name="enjoyed_using" type="radio" value="Not helpful" /></td>
		<td><span class="pending_payment_and_approval">Pending payment &amp; approval</span></td>
	</tr>
</table>
-->
<br />

		<p class="mb0 mt0">
		<?php if ($suspended != 1) { ?>
		If you prefer to you can "<a href="your-account-manage-posts.php?action=suspend&post_type=<?php print $post_type?>&id=<?php print $id?>">suspend</a>" your ad to temporarily remove it.<br />
		<br />
		<?php } ?>
		Permanently delete this advert?</p>
		<p >
			<input type="submit" name="delete" value="Delete advert" style="width:95px;" />
			&nbsp;
			<input type="submit" name="cancel" value="Cancel" style="width:50px;" />
		</p>
		</form>
		
		
		<p class="mb0"><a href="your-account-manage-posts.php">Back to Your Ads</a></p>
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
