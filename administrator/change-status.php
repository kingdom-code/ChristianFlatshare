<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	// Dissallow access if needed parameters have not been supplied
	if (!isset($_REQUEST['post_type'])) { header("Location:index.php"); exit; } else { $post_type = $_REQUEST['post_type']; }
	if (!isset($_REQUEST['id'])) { header("Location:".$post_type."-ads.php"); exit; } else { $id = $_REQUEST['id']; }
	
	if ($_POST) {
	
		// If the user has cancelled, return to the previous page
		if (isset($_POST['cancel'])) { header("Location: ".$post_type."-ads.php"); exit; }
		
		// Update the status of the selected ad
		$query = "update cf_".$post_type." set paid_for='".$_POST['status'][0]."', approved='".$_POST['status'][1]."', published='".$_POST['status'][2]."', suspended='".$_POST['suspended']."' where ".$post_type."_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if ($result) {
			if ($_POST['email_user']) {
				// Notify the user that the ad was published
				if (notifyUser($id,$post_type, $twig, TRUE)) {
					header("Location: ".$post_type."-ads.php?report=statusChangeSuccessEmailSuccess"); exit;
				} else {
					header("Location: ".$post_type."-ads.php?report=statusChangeSuccessEmailFailure"); exit;
				}
			} else {
				header("Location: ".$post_type."-ads.php?report=statusChangeSuccess"); exit;
			}
		} else {
			header("Location: ".$post_type."-ads.php?report=statusChangeFailure"); exit;
		}		
		
	} else {
	
		// Find out the status of the current ad (concatenate the three columns:
		// paid_for, approveda and published
		$query = "select CONCAT(paid_for,CONCAT(approved,published)) as `status`, suspended from cf_offered where offered_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad_record = mysqli_fetch_assoc($result);
		$status = $ad_record['status'];		
		$suspended = $ad_record['suspended'];				
		
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/admin.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>ChristianFlatShare.org administration</title>
<!-- InstanceEndEditable -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="head" --><!-- TemplateParam name="class" type="text" value="current" --><!-- InstanceEndEditable -->
<link href="../styles/admin.css" rel="stylesheet" type="text/css" />
<!-- InstanceParam name="highlightPage" type="text" value="0" -->
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="HEADER">
    <tr>
        <td width="370"><img src="../images/admin-header.gif" width="355" height="40" /></td>
        <td align="right" style="padding-right:15px;" nowrap="nowrap"><a href="../logout.php">Log out administrator</a></td>
    </tr>
</table>
<div><img src="../images/admin-header-blue-back.gif" width="100%" height="6"></div>
<div id="MENU">
	<ul>
		<li><a href="index.php" class="">Main menu</a></li>
		<li><a href="members.php" class="">View members</a></li>
		<li><a href="offered-ads.php" class="">View offered ads</a></li>
		<li><a href="wanted-ads.php" class="">View wanted ads</a></li>
		<li><a href="emails.php" class="">Email Replies</a></li>	
		<li><a href="cfs_feedback.php" class="">Feedback</a></li>				
		<li><a href="logins.php" class="">Logins</a></li>				
		<li><a href="stats.php" class="">Statistics</a></li>						
		<!--<li><a href="#" class="">View payment history</a></li>-->
	</ul>
</div>
<div><img src="../images/spacer.gif" width="100%" height="1"></div>
<div id="MAIN_CONTENT">
<!-- InstanceBeginEditable name="mainContent" -->
<h1 class="mt0">Change status for ad <?php print $id?></h1>
<p>Use the radio buttons below to change the status of this ad:</p>
<form name="statusChange" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<input type="hidden" name="post_type" value="<?php print $post_type?>" />
<input type="hidden" name="id" value="<?php print $id?>" />
<table border="0" cellpadding="0" cellspacing="10">
	<tr>
		<td valign="top"><input name="status" type="radio" value="000" <?php print ($status == "000"? 'checked="checked"':'')?>/></td>
		<td><span class="pending_payment_and_approval">Pending payment &amp; approval</span><br />The member has placed an ad but not paid for it.<br />The ad has not been approved by an administration. </td>
		</tr>
	<tr>
		<td valign="top"><input name="status" type="radio" value="010" <?php print ($status == "010"? 'checked="checked"':'')?>/></td>
		<td><span class="pending_payment">Pending payment </span><br />The ad was approved by an administrator but a donation has not been made yet. </td>
		</tr>
	<tr>
		<td valign="top"><input name="status" type="radio" value="100" <?php print ($status == "100"? 'checked="checked"':'')?>/></td>
		<td><span class="pending_approval">Pending approval </span><br />Ad needs to be approved by ad administrator. </td>
		</tr>
	<tr>
		<td valign="top"><input name="status" type="radio" value="111" <?php print ($status == "111"? 'checked="checked"':'')?>/></td>
		<td><span class="published">Published</span><br />A donation was made and the ad was approved by an administrator.<br />
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input name="email_user" type="checkbox" id="email_user" value="1" /></td>
					<td style="padding-left:4px;">Email user? </td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td valign="top"><input name="status" type="radio" value="110" <?php print ($status=="110"? 'checked="checked"':'')?>/></td>
		<td><span class="unpublished">Unpublished</span><br />A donation was made and the ad was approved however an administrator has chosen to hide it. </td>
		</tr>
		
	<tr>
	</tr>
			
	<tr>	
		<td><input name="suspended" type="checkbox" id="suspended" value="1" <?php if ($suspended=="1") { ?>checked="checked"<?php } ?>/></td>
		<td>Advert suspended (you will need to suspend adverts manually)<span class="grey"></span></td>
	</tr>				
	
	<tr>
		<td valign="top">&nbsp;</td>
		<td><input type="submit" name="Submit" value="Apply status change" />&nbsp;<input type="submit" name="cancel" value="Cancel" /></td>
	</tr>
	
</table>
</form>
<p><a href="index.php">Return to the main administration page</a></p>
<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
