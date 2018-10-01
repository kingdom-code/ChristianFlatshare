<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	
	// Dissallow access if needed parameters have not been supplied
	if (!isset($_REQUEST['post_type'])) { header("Location:index.php"); exit; } else { $post_type = $_REQUEST['post_type']; }
	if (!isset($_REQUEST['id'])) { header("Location:".$post_type."_ads.php"); exit; } else { $id = $_REQUEST['id']; }
	
	if (isset($_POST['delete'])) { 
	
		// Move the ad into the appropriate archive table
		$query = "
		insert into cf_".$post_type."_archive  (
			select *,now() from cf_".$post_type." where ".$post_type."_id = '".$id."'
		);";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		// Admin has clicked on the delete button
		$query = "delete from cf_".$post_type." where ".$post_type."_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if ($result) {
			header("Location: ".$post_type."-ads.php?report=deletionSuccess"); exit;
		} else {
			header("Location: ".$post_type."-ads.php?report=deletionFailure"); exit;
		}
		
	} else if (isset($_POST['cancel'])) {
	
		header("Location: ".$post_type."-ads.php"); exit;	
	
	} else {
	
		// Find out all the offered ads placed by this user
		$query = "select * from cf_".$post_type." where ".$post_type."_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		$summary = createSummaryV2($ad,$post_type,"",true);
		
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
<h1 class="mt0">Ad deletion </h1>
<p class="error">Important note: This action cannot be undone.</p>
<table width="100%" border="0" cellpadding="8" cellspacing="0" id="adListings">
	<?php print $summary;?>
</table>
<form name="deletion" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<input type="hidden" name="post_type" value="<?php print $post_type?>" />
<input type="hidden" name="id" value="<?php print $id?>" />
<p>Are you sure you want to delete this advertisement?</p>
<p>
	<input type="submit" name="delete" value="Yes" style="width:50px;" />
	&nbsp;
	<input type="submit" name="cancel" value="No" style="width:50px;" />
</p>
</form>
<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
