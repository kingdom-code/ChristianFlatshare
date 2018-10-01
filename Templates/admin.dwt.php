<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- TemplateBeginEditable name="doctitle" -->
<title>ChristianFlatShare.org administration</title>
<!-- TemplateEndEditable -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- TemplateBeginEditable name="head" --><!-- TemplateEndEditable -->
<link href="../styles/admin.css" rel="stylesheet" type="text/css" />
<!-- TemplateParam name="highlightPage" type="text" value="1" -->
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
		<li><a href="../administrator/index.php" class="@@((highlightPage=='1') ? 'current' : '')@@">Main menu</a></li>
		<li><a href="../administrator/members.php" class="@@((highlightPage=='2') ? 'current' : '')@@">View members</a></li>
		<li><a href="../administrator/offered-ads.php" class="@@((highlightPage=='3') ? 'current' : '')@@">View offered ads</a></li>
		<li><a href="../administrator/wanted-ads.php" class="@@((highlightPage=='4') ? 'current' : '')@@">View wanted ads</a></li>
		<li><a href="../administrator/emails.php" class="@@((highlightPage=='5') ? 'current' : '')@@">Email Replies</a></li>	
		<li><a href="../administrator/cfs_feedback.php" class="@@((highlightPage=='6') ? 'current' : '')@@">Feedback</a></li>				
		<li><a href="../administrator/logins.php" class="@@((highlightPage=='7') ? 'current' : '')@@">Logins</a></li>				
		<li><a href="../administrator/stats.php" class="@@((highlightPage=='8') ? 'current' : '')@@">Statistics</a></li>						
		<!--<li><a href="#" class="@@((highlightPage=='5') ? 'current' : '')@@">View payment history</a></li>-->
	</ul>
</div>
<div><img src="../images/spacer.gif" width="100%" height="1"></div>
<div id="MAIN_CONTENT">
<!-- TemplateBeginEditable name="mainContent" -->
<h1 class="mt0">Welcome {Administrator name}.</h1>
<p>Please choose one of the options below:</p>
<ul>
	<li><a href="#">View users</a> (*)</li>
	<li class="grey">View offered ads</li>
	<li class="grey">View wanted ads</li>
	<li class="grey">View payment history </li>
</ul>
<!-- TemplateEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
</html>
