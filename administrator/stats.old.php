<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';

	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	
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
<!-- InstanceParam name="highlightPage" type="text" value="1" -->
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
		<li><a href="index.php" class="current">Main menu</a></li>
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
<h1 class="mt0">Welcome <?php print $_SESSION['u_name']?>.</h1>
<p>Please choose one of the options below:</p>
<ul>
	<li><a href="members.php">View members </a> (<?php print getNumberOfMembers()?>)</li>
	<li><a href="offered-ads.php">View offered ads </a> (<?php print getNumberOfOffered()?>)</li>
	<li><a href="wanted-ads.php">View wanted ads</a> (<?php print getNumberOfWanted()?>)</li>
    <li><a href="emails.php">emails</a> (<?php print getNumberEmails()?>)</li>
    <li><a href="logins.php">Logins</a> (<?php print getNumberLogins()?>)</li>					
    <li><a href="cfs_feedback.php">CFS feedback</a> (<?php print getNumberFeedback()?>)</li>	
    <li><a href="stats.php">Statistics</a></li>	
  <li>Saved Ads (<?php print getNumberSavedAdsAdmin()?>)</li>			
	<li>Total offered (<?php print getTotalNumberOfAds()?>)</li>
	<li>Banner Clicks (<?php print getBannerClickCount()?>)</li>		
	<li>Palups active (<?php print getTotalNumberOfPalups()?>)</li>
	<li>Offered views (<?php print getTotalNumberOfOfferedViews()?>)</li>
	<li>Wanted views (<?php print getTotalNumberOfWantedViews()?>)</li>	
	<li>Banner ads (<?php print getNumberofBannerAds()?>)</li>		
	<li>Facebook signups (<?php print getNumberofFacebook()?>)</li>		
  <li><a href="scam_ips.php">Scam IPs</a></li>			
  </ul>

<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
