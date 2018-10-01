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
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'e.reply_date'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }	
	//if (isset($_REQUEST['q'])) { $q = mysqli_real_escape_string($_REQUEST['q']); } else { $q = NULL; }	
	if (isset($_REQUEST['q'])) { $q = $_REQUEST['q']; } else { $q = NULL; }	
	
    // if we have a quick search
	if ($q) {
		$sql_search_ext_count= ", cf_users u_from, cf_users u_to
	          WHERE  e.from_user_id = u_from.user_id 
 			      AND    e.to_user_id   = u_to.user_id  
						AND (u_from.first_name like '%".$q."%' || u_from.surname like '%".$q."%' 
						|| u_to.first_name like '%".$q."%' || u_to.surname like '%".$q."%'  
						|| u_to.email_address like '%".$q."%' || u_from.email_address like '%".$q."%' 
						|| e.message like '%".$q."%') ";
		$sql_search_ext = "AND (u_from.first_name like '%".$q."%' || u_from.surname like '%".$q."%' 
						|| u_to.first_name like '%".$q."%' || u_to.surname like '%".$q."%'  || u_to.email_address like '%".$q."%' || u_from.email_address like '%".$q."%' || e.message like '%".$q."%' ) ";

	}

	
	// Count query
	$query = "SELECT COUNT(*) FROM cf_email_replies e".$sql_search_ext_count;
	
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$row = mysqli_fetch_row($result);
	$count = $row[0];
	$debug .= debugEvent("Count query",$query);	
	

	
	// Create a pager for the data
	$pagerLink = $_SERVER['PHP_SELF']."?orderBy=".$orderBy."&direction=".$direction;
    if ($q) {
		$pagerLink .= '&q='.$q;
	}
	$pager = new Pager($count,$start,$sortNum,$pagerLink);
	

	// Create the header links for the table columns
	$headerMapping = array (
		"Stopped" => "u_from.suppressed_replies",
		"From User" => "from_first_name",
		"To User" => "to_first_name",
		"To Ad Title" => "e.to_ad_id",
		"To Ad Type" => "e.to_post_type",
		"Message" => "e.message",
		"Reply Date" => "e.reply_date"
	);
	
	$link = "&user_id=".$user_id."&start=".$start."&sortNum=".$sortNum;
	
	$headerLinks = createHeaderLinks($headerMapping,$link,$orderBy,$direction);		
	
	// Data query
    $query = "
  		   SELECT  e.suppressed_replies as was_stopped,
			       u_from.first_name as from_first_name, 
			       u_from.surname as from_surname, 				   
			       u_from.user_id as from_user_id,
                               u_from.facebook_id as from_facebook_id,
 			       u_from.suppressed_replies as u_from_suppressed_replies,
			       u_to.first_name as to_first_name, 
			       u_to.surname as to_surname, 		
                               u_to.facebook_id as to_facebook_id,		   
                               u_to.suppressed_replies as u_to_suppressed_replies,
				   u_to.user_id as to_user_id,
				   e.to_ad_id, 
				   e.to_post_type, 
				   e.message, 
				   e.reply_date,
		  	 	 e.reply_id
			FROM   cf_users u_from, 
 				   cf_users u_to, 
			       cf_email_replies e
			WHERE  e.from_user_id = u_from.user_id 
			AND    e.to_user_id   = u_to.user_id ".$sql_search_ext."
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
			$u .= '<td>'.$row['was_stopped'].'</td>'."\n";
                        if ($row['u_from_suppressed_replies'] == 1) {$scammer = 'class="obligatory"'; } else { $scammer = NULL; };
		 	$u .= '<td '.$scammer.'><a '.$scammer.' href="edit-member.php?user_id='.$row['from_user_id'].'">'.$row['from_first_name'].' '.$row['from_surname'].'</a><br />'.($row['from_facebook_id']==''?'':' <strong>*FB*</strong> ').checkForOfferedAd(TRUE,$row['from_user_id']).','.checkForWantedAd(TRUE,$row['from_user_id']).'</td>'."\n";
                        if ($row['u_to_suppressed_replies'] == 1) {$scammer = 'class="obligatory"'; } else { $scammer = NULL; };
			$u .= '<td><a href="edit-member.php?user_id='.$row['to_user_id'].'">'.$row['to_first_name'].' '.$row['to_surname'].'</a><br />'.($row['to_facebook_id']==''?'':' <strong>*FB*</strong> ').checkForOfferedAd(TRUE,$row['to_user_id']).','.checkForWantedAd(TRUE,$row['to_user_id']).'</td>'."\n";
				$u .= '<td><a href="../details.php?id='.$row['to_ad_id'].'&post_type='.$row['to_post_type'].'">'.strip_tags(getAdTitleByID($row['to_ad_id'],$row['to_post_type'])).'</a></td>'."\n";
				$u .= '<td>'.$row['to_post_type'].'</td>'."\n";
				$u .= '<td>'.$row['message'].'</td>'."\n";			
				$u .= '<td><a href="edit-email.php?reply_id='.$row['reply_id'].'">'.$row['reply_date'].'</a></td>';
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
	     <h1 class="mt0">Email replies</h1>
<p><strong>The table below shows all advert replies.</strong><br />All dark column headers can be clicked to sort.</p>
      </td>
      <td width="300" align="right" valign="top">
		<div id="admin_quick_search">
			<form name="quick_search_form" method="get" action="<?php print $_SERVER['PHP_SELF']?>">
			<div style="margin-bottom:4px;">Sender/receiver quick search: <span class="grey">(to user, from user, email)</span></div>
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
		<th><a href="<?php print $headerLinks['Stopped']['href']?>">Stopped<?php print $headerLinks['Stopped']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['From User']['href']?>">From User<?php print $headerLinks['From User']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['To User']['href']?>">To User<?php print $headerLinks['To User']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['To Ad Title']['href']?>">To Ad Title<?php print $headerLinks['To Ad Title']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['To Ad Type']['href']?>">To Ad Type<?php print $headerLinks['To Ad Type']['icon']?></a></th>		
		<th><a href="<?php print $headerLinks['Message']['href']?>">Message<?php print $headerLinks['Message']['icon']?></a></th>
		<th><a href="<?php print $headerLinks['Reply Date']['href']?>">Reply Date<?php print $headerLinks['Reply Date']['icon']?></a></th>
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
