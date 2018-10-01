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
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'login_date'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }	
	if (isset($_REQUEST['uk'])) { $uk = ($_REQUEST['uk']); $sql_search_ext = " and ip_country != 'UNITED KINGDOM' "; 
															} else { $uk = 'yes'; }	
	
    // if we have a quick search
	if ($q) {
		$sql_search_ext = " and (CONCAT_WS(u.first_name, u.surname, ' ') like '%".$q."%' || l.email_address like '%".$q."%' || l.ip_city like '%".$q."%' || l.ip_region like '%".$q."%'  || l.ip_country like '%".$q."%' || l.ip_name like '%".$q."%' || l.user_id like '%".$q."%') ";
	}

	// Count query
	$query = "SELECT COUNT(*) 
				FROM   cf_user_logins l, cf_users u
				WHERE  u.user_id = l.user_id
 			    ".$sql_search_ext;
	$debug .= debugEvent("Pager query",$query);			  
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
		"Name" => "name",
		"Email" => "email_address",
		"User ID" => "user_id",		
		"Suppressed" => "suppressed_replies",
		"Login Date" => "login_date",
		"City" => "ip_city",
		"Region"    => "ip_region",
		"Country" => "ip_country",
		"IP Name" => "ip_name"		
	);
	
	$link = "&user_id=".$user_id."&start=".$start."&sortNum=".$sortNum;
	
	$headerLinks = createHeaderLinks($headerMapping,$link,$orderBy,$direction);		
	
	// Data query
    $query = "
  		   SELECT  CONCAT_WS(u.first_name, u.surname, ' ') name,
		           l.email_address email_address,
                           u.facebook_id facebook_id,
				   l.suppressed_replies suppressed_replies,
				   l.login_date login_date,
				   l.ip_city ip_city,
				   l.ip_region ip_region,
				   l.ip_country ip_country,
				   l.ip_name ip_name,
				   l.user_id user_id 
			FROM   cf_user_logins l, cf_users u
			WHERE  u.user_id = l.user_id
			    ".$sql_search_ext."
   	  	    ORDER BY ".$orderBy." ".$direction." limit ".$start.", ".$sortNum;
	$debug .= debugEvent("Query",$query);
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);

		$class = "trOdd";
		while($row = mysqli_fetch_assoc($result)) {
			$u .= '<tr class="'.$class.'" ';
			$u .= 'onmouseover="this.className=\'trOver\';" onmouseout="this.className=\''.$class.'\';"';			
			$u .= '>'."\n";
				$u .= '<td><a href="edit-member.php?user_id='.$row['user_id'].'">'.$row['name'].'</a></td>'."\n";			
				$u .= '<td>'.$row['email_address'].($row['facebook_id']==''?'':' <strong>*FB*</strong>').'</td>'."\n";
				$u .= '<td>'.$row['user_id'].'</td>'."\n";				
				$u .= '<td>'.$row['suppressed_replies'].'</td>'."\n";				
				$u .= '<td>'.$row['login_date'].'</td>'."\n";								
				$u .= '<td>'.$row['ip_city'].'</td>'."\n";								
				$u .= '<td>'.$row['ip_region'].'</td>'."\n";	
				$u .= '<td>'.$row['ip_country'].'</td>'."\n";	
				$u .= '<td>'.$row['ip_name'].'</td>'."\n";	
  		   	    $u .= '</tr>'."\n";
	  		$class = ($class == "trOdd")? "trEven":"trOdd";
		
	
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
	     <h1 class="mt0">Logins</h1>
 				 <?php if ($uk == 'yes') { ?>
         <p><a href=<?php $_SERVER['PHP_SELF'] ?>?uk=no>Show non-UK logins</a>
				 <?php } else { ?>
         <p><a href=<?php $_SERVER['PHP_SELF'] ?>?uk=yes>Show all logins</a>				 
				 <?php } ?>

         <p><strong>The table below show user logins.</strong><br />
         All dark column headers can be clicked to sort.</p></td>
      <td width="300" align="right" valign="top">
		<div id="admin_quick_search">
			<form name="quick_search_form" method="get" action="<?php print $_SERVER['PHP_SELF']?>">
			<div style="margin-bottom:4px;">Name, Email address: </div>
			<div>
				<input name="q" id="quick_search" type="text" value="<?php print $q?>"/>
				<input id="quick_search_button" type="submit" value="Search" />
			</div>
			</form>
		</div>		
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
		<th><a href="<?php print $headerLinks['Name']['href']?>">Name<?php print $headerLinks['Name']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Email']['href']?>">Email<?php print $headerLinks['Email']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['User ID']['href']?>">User ID<?php print $headerLinks['User ID']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['Suppressed']['href']?>">Suppressed<?php print $headerLinks['Suppressed']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Login Date']['href']?>">Login Date<?php print $headerLinks['Login Date']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['City']['href']?>">City<?php print $headerLinks['City']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['Region']['href']?>">Region<?php print $headerLinks['Region']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Country']['href']?>">Country<?php print $headerLinks['Country']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['IP Name']['href']?>">IP Name<?php print $headerLinks['IP Name']['icon']?></a></th>		
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
