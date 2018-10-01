<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';

	require('../includes/class.pager.php');		// Pager class
	
	$u = "";
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	
	// Initialise variables
	if (isset($_REQUEST['start'])) { $start = $_REQUEST['start']; } else { $start = 0; }
	if (isset($_REQUEST['sortNum'])) { $sortNum = $_REQUEST['sortNum']; } else { $sortNum = ADMIN_ITEMS_PER_PAGE; }
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'feedback_date'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }	
        //if (isset($_REQUEST['q'])) { $q = mysqli_real_escape_string($_REQUEST['q']); } else { $q = NULL; }	
	if (isset($_REQUEST['q'])) { $q = $_REQUEST['q']; } else { $q = NULL; }	
	
 	// Count query
	$query = "SELECT COUNT(*) FROM cf_feedback";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$row = mysqli_fetch_row($result);
	$count = $row[0];
	
	// Create a pager for the data
	$pagerLink = $_SERVER['PHP_SELF']."?orderBy=".$orderBy."&direction=".$direction;
    if ($q) {
		$pagerLink .= '&q='.$q;
	}
	$pager = new Pager($count,$start,$sortNum,$pagerLink);
	
	// Create the header links for the table columns
	$headerMapping = array (
		"First name"    => "first_name",
		"Surname"       => "surname",
		"Email Address" => "email_address",
		"Feedback"      => "feedback",
		"Helpful"      => "helpful",		
		"Date"          => "feedback_date"
	);
	
	$link = "&user_id=".$user_id."&start=".$start."&sortNum=".$sortNum;
	
	$headerLinks = createHeaderLinks($headerMapping,$link,$orderBy,$direction);		
	
	// Data query
    $query = "
  		   SELECT  u.first_name first_name,
			       u.surname surname, 
			       u.email_address email_address, 	
		               u.user_id,
                               u.facebook_id,
				   f.feedback,
			       f.feedback_date, 
						 f.helpful
			FROM   cf_users u, 
 				   cf_feedback f 
			WHERE  f.user_id = u.user_id 
   	  	    ORDER BY ".$orderBy." ".$direction." limit ".$start.", ".$sortNum;
	$debug .= debugEvent("Query",$query);
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	if (!mysqli_num_rows($result)) {
		
	} else {
		
		$class = "trOdd";
		while($row = mysqli_fetch_assoc($result)) {
			$u .= '<tr class="'.$class.'" ';
			$u .= 'onmouseover="this.className=\'trOver\';" onmouseout="this.className=\''.$class.'\';"';			
			$u .= '>'."\n";
		$u .= '<td width="80"><a href="edit-member.php?user_id='.$row['user_id'].'">'.$row['first_name'].'</a></td>'."\n";
		$u .= '<td width="80"><a href="edit-member.php?user_id='.$row['user_id'].'">'.$row['surname'].'</a>'.($row['facebook_id']==''?'':' <strong>*FB*</strong>').'</td>'."\n";
				$u .= '<td width="180">'.$row['email_address'].'</td>'."\n";
				$u .= '<td>'.$row['feedback'].'</td>'."\n";								
				$u .= '<td>'.$row['helpful'].'</td>'."\n";												
				$u .= '<td width="140">'.$row['feedback_date'].'</td>'."\n";												
  		   	    $u .= '</tr>'."\n";
	  		$class = ($class == "trOdd")? "trEven":"trOdd";
		}
	
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/admin.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>ChristianFlatShare.org administration</title>
<!-- InstanceEndEditable -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<link href="../styles/admin.css" rel="stylesheet" type="text/css" />
<!-- InstanceParam name="highlightPage" type="text" value="2" -->
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
		<li><a href="members.php" class="current">View members</a></li>
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

<form name="form1" id="form1" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
    <tr>
	  <td> 
	     <h1 class="mt0">Member Feedback </h1>
         <p><strong>The table below show user feedback.</strong><br />
           All dark column headers can be clicked to sort.</p>
      </td>
    </tr>
</table>
<?php print $msg?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
    <tr>
        <td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?> </strong> users.<?php
			if ($q) {
				echo ' - <a href="members.php">Clear search</a>';
			}
		?></td>
   		<td align="right"><?php print $pager->createLinks()?></td>
    </tr>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="0" class="greyTable extendInputFields">
	<tr>
		<th><a href="<?php print $headerLinks['First Name']['href']?>">First Name<?php print $headerLinks['First name']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Surname']['href']?>">Surname<?php print $headerLinks['Surname']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Email Address']['href']?>">Email Address<?php print $headerLinks['Email Address']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Feedback']['href']?>">Feedback<?php print $headerLinks['Feedback']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['Helpful']['href']?>">Helpful?<?php print $headerLinks['helpful']['icon']?></a></th>				
		<th><a href="<?php print $headerLinks['Feedback Date']['href']?>">Feedback Date<?php print $headerLinks['Feedback Date']['icon']?></a></th>		
	</tr>
	<!--<tr class="trOdd">
		<td align="center">&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td align="center">&nbsp;</td>
		<td width="70" align="center"><input name="comments_*" type="submit" value="Comments" style="width:70px;"/></td>
		<td width="50" align="center"><input name="edit_*" type="submit" value="Edit" style="width:50px;"/></td>
		<td width="50" align="center"><input name="delete_*" type="submit" value="Delete" style="width:50px;"/></td>
	</tr>-->
	<?php print $u?>
</table>
</form>
<p><a href="index.php">Return to the main administration page</a></p>
<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
