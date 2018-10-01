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
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'u.created_date'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }	
	//if (isset($_REQUEST['q'])) { $q = mysqli_real_escape_string($_REQUEST['q']); } else { $q= NULL ; }
	if (isset($_REQUEST['q'])) { $q = $_REQUEST['q']; } else { $q= NULL ; }

	
	// If $_POST the user has click on an 'Edit', 'Delete' or 'Comments' button.
	if ($_POST) {
	
		$post = each($_POST);
		/* 
			If for example a "Comments" button was pressed, 
			the $post array will now contain:
			
			Array
			(
				[1] => Comments
				[value] => Comments
				[0] => comments_1
				[key] => comments_1
			)
			
		*/
		preg_match('/^(delete|edit|comments)_(\d+)$/',$post['key'],$matches);
		$action = $matches[1]; // e.g. comments
		$id = $matches[2]; // e.g. 1
		
		switch($action) {
		
			case "edit":
				header("Location:edit-member.php?user_id=".$id);
				break;
				
			case "delete":
				$msg = "";
				// Check if user has any offered ads in the system
				$query = "select * from cf_offered where user_id = '".$id."';";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if (mysqli_num_rows($result)) {
					$msg .= '<p class="error">Cannot delete user. They have one or more OFFERED ads on the system.</p>';
				}
				// Check if user has any wanted ads in the system
				$query = "select * from cf_wanted where user_id = '".$id."';";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if (mysqli_num_rows($result)) {
					$msg .= '<p class="error">Cannot delete user. They have one or more WANTED ads on the system.</p>';
				}			
				if (!$msg) {
					$query = "delete from cf_users where user_id = '".$id."';";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						$msg .= '<p class="success">User was deleted successfully.</p>';
					} else {
						$msg .= '<p class="error">There was an error deleting this user.</p>';
					}
				}
				break;
					
		}
				
	}
	
	// if we have a quick search
	if ($q) {
		 if (is_numeric($q)) {
		$sql_search_ext = " and (u.user_id = ".$q." ) ";			 
	 } else {
		$sql_search_ext = " and (first_name like '%".$q."%' || surname like '%".$q."%' || email_address like '%".$q."%' ) ";
	 }
		//$sql_search_ext = " and (first_name like '%".$q."%' || surname like '%".$q."%' || email_address like '%".$q."%' ) ";
	}

	// Count query
	$query = "select count(*) from cf_users as `u`
	where 1=1 ".$sql_search_ext;
	$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die($query);
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
		"User ID" => "u.user_id",
		"Name" => "u.name",
		"Email" => "u.email_address",
		"Member since" => "u.created_date",
		"Last login" => "u.last_login",
		"Number of ads" => "offered",
		"Saved ads" => "saved",		
    "Replies received" => "replies_received",				
    "Emails sent" => "emails_sent"					
		//"Email verified" => "u.email_verified",
	);
	$link = "&user_id=".$user_id."&start=".$start."&sortNum=".$sortNum;
	$headerLinks = createHeaderLinks($headerMapping,$link,$orderaBy,$direction);		
	
	// Data query
	$query = "
		
    SELECT u.user_id, 
				DATE_FORMAT(u.created_date,'%d/%m/%Y - %k:%i') AS `created_date`, 
				DATE_FORMAT(u.last_login,'%d/%m/%Y - %k:%i') AS `last_login`, 
				u.first_name, u.surname, facebook_id,
				CONCAT_WS(' ',u.first_name,u.surname) AS `name`, u.email_address,
				u.hear_about, u.church_attended, suppressed_replies, account_suspended,
				IFNULL((SELECT COUNT(offered_id) 
								FROM cf_offered t1
								WHERE published != 2 
				        AND t1.user_id = u.user_id),0) AS `offered`,
				IFNULL((SELECT COUNT(wanted_id) AS `count` 
								FROM cf_wanted t1
								WHERE published != 2
								AND t1.user_id = u.user_id),0) AS `wanted`,
				IFNULL((SELECT COUNT(ad_id) AS `count` 	
								FROM cf_saved_ads sa
								WHERE sa.user_id = u.user_id
								GROUP BY user_id),0) AS `saved`, 
				IFNULL((SELECT COUNT(to_user_id) AS `count` 
								FROM cf_email_replies et
								WHERE et.to_user_id = u.user_id
								GROUP BY to_user_id),0) AS `replies_received`,
				IFNULL((SELECT COUNT(from_user_id) AS `count` 
								FROM cf_email_replies er
								WHERE er.from_user_id = u.user_id
								GROUP BY from_user_id),0) AS `emails_sent`
				FROM cf_users AS `u`

		WHERE 1=1 ".$sql_search_ext."
		order by ".$orderBy." ".$direction." limit ".$start.", ".$sortNum."
		
	";
	//$debug .= debugEvent("Query",$query);
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	if (!mysqli_num_rows($result)) {
		
	} else {
		
		$class = "trOdd";
		while($row = mysqli_fetch_assoc($result)) {
		
		    if ($row['suppressed_replies'] == 1) {$scammer = 'class="obligatory"'; } else { $scammer = 'HHHH'; };
		    if ($row['account_suspended'] == 1) {$suspended = 'class="grey"'; } else { $suspended = ''; };				
			$u .= '<tr class="'.$class.'" ';
			$u .= 'onmouseover="this.className=\'trOver\';" onmouseout="this.className=\''.$class.'\';"';			
			$u .= '>'."\n";
				$u .= '<td align="center" '.$suspended.'>'.$row['user_id'].'</td>'."\n";
			$u .= '<td><a href="edit-member.php?user_id='.$row['user_id'].'">'.$row['name'].'</a></td>';				
			$u .= '<td '.$scammer.'>'.$row['email_address'].($row['facebook_id']==''?'':' <strong>*FB*</strong>'.'</td>')."\n";				
			$u .= '<td align="center">'.$row['created_date'].'</td>'."\n";
			$u .= '<td align="center">'.$row['last_login'].'</td>'."\n";
			$u .= '<td align="left">'.$row['hear_about'].'</td>'."\n";				
			$u .= '<td align="left">'.$row['church_attended'].'&nbsp;</td>'."\n";								
			$u .= '<td align="centre">'.$row['saved'].'&nbsp;</td>'."\n";														
			
			$u .= '<td><a href="emails.php?q='.$row['email_address'].'">'.$row['replies_received'].'</a></td>';				
			$u .= '<td><a href="emails.php?q='.$row['email_address'].'">'.$row['emails_sent'].'</a></td>';							
			
			// Enter the number of ads for this users
			$u .= '<td align="center">';
				if ($row['offered']) {
					$u .= '<a href="offered-ads.php?user_id='.$row['user_id'].'">'.$row['offered'].' offered</a>';
				} else {
					$u .= '<span class="grey">0 offered<span>';
				}
				$u .= ' - '; // the separator
				if ($row['wanted']) {
					$u .= '<a href="wanted-ads.php?user_id='.$row['user_id'].'">'.$row['wanted'].' wanted</a>';
				} else {
					$u .= '<span class="grey">0 wanted<span>';
				}			
				$u .= '</td>'."\n";	
				//$u .= '<td align="center">'.$row['email_verified'].'</td>'."\n";
				//$u .= '<td width="55" align="center"><input name="edit_'.$row['user_id'].'" type="submit" value="Edit" class="adminButton"/></td>'."\n";
				//$u .= '<td width="55" align="center"><input name="delete_'.$row['user_id'].'" type="submit" value="Delete" class="adminButton" onClick="return confirm(\'Are you sure you want to delete '.$row['name'].'?\');"/></td>'."\n";
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
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td>
			<h1 class="mt0">Members List</h1>
			<strong>The table below shows all members of ChristianFlatShare.org.</strong><br />For all other information on each user (such as date of birth, gender etc.) please click on the &quot;Edit&quot; button. <br />All dark column headers can be clicked to sort.		</td>
		<td width="300" align="right" valign="top">
			<div id="admin_quick_search">
				<form name="quick_search_form" method="get" action="<?php print $_SERVER['PHP_SELF']?>">
				<div style="margin-bottom:4px;">Member quick search: <span class="grey">(firstname, surname, email, IP)</span></div>
				<div>
					<input name="q" id="quick_search" type="text" value="<?php print $q?>"/>
					<input id="quick_search_button" type="submit" value="Search" /></div>
				</form>
			</div>		</td>
	</tr>
</table>
<?php print $msg?>
<form name="form1" id="form1" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
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
	
				
				
		<th><a href="<?php print $headerLinks['User ID']['href']?>">ID<?php print $headerLinks['User ID']['icon']?></a></th>
<!--		<th><a href="<?php print $headerLinks['Name']['href']?>">Name<?php print $headerLinks['Name']['icon']?></a></th> -->
		<th><a href="<?php print $headerLinks['Surname']['href']?>">Name<?php print $headerLinks['Surname']['icon']?></a></th> 
		<th><a href="<?php print $headerLinks['Email']['href']?>">Email<?php print $headerLinks['Email']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['Member since']['href']?>">Member since<?php print $headerLinks['Member since']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Last login']['href']?>">Last login<?php print $headerLinks['Last login']['icon']?></a></th>
		<th>Hear about</th>
		<th>Church attended</th>			
		<th><a href="<?php print $headerLinks['Saved ads']['href']?>">Saved ads<?php print $headerLinks['Saved ads']['icon']?></a></th>		
	  <th><a href="<?php print $headerLinks['Replies received']['href']?>">Replies received<?php print $headerLinks['Replies received']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Emails sent']['href']?>">Emails sent<?php print $headerLinks['Emails sent']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Number of ads']['href']?>">Number of ads<?php print $headerLinks['Number of ads']['icon']?></a></th>				
		<!--<th><a href="<?php print $headerLinks['Email verified']['href']?>">Email verified<?php print $headerLinks['Email verified']['icon']?></a></th> -->
		<!--<th colspan="2">Actions</th>-->
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
