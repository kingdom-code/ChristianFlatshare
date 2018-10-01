<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';

	require('../includes/class.pager.php');		// Pager class

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }

	// Initialise variables
	$a = "";
	if (isset($_REQUEST['start'])) { $start = $_REQUEST['start']; } else { $start = 0; }
	if (isset($_REQUEST['sortNum'])) { $sortNum = $_REQUEST['sortNum']; } else { $sortNum = ADMIN_ITEMS_PER_PAGE; }
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'w.wanted_id'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }	
	if (isset($_GET['user_id'])) { $user_id = $_GET['user_id']; } else { $user_id = NULL; }
//	if (isset($_REQUEST['q'])) { $q = mysqli_real_escape_string($_REQUEST['q']); } else { $q = NULL; }
        if (isset($_REQUEST['q'])) { $q = $_REQUEST['q']; } else { $q = NULL; }
		
    // if we have a quick search
	if ($q) {
		$sql_search_ext = " and (u.email_address like '%".$q."%' || u.first_name like '%".$q."%' || u.surname like '%".$q."%' || w.wanted_id = '".$q."') ";
	}		
				
	
	// If $_POST the user has click on an 'Edit' or 'Delete' button.
	if ($_POST) {
	
		$post = each($_POST);
		/* 
			If for example an "Edit" button was pressed, 
			the $_Post array will now contain:
			
			Array
			(
				[value] => Edit
				[key] => edit_1
			)
			
		*/
		preg_match('/^(delete|edit)_(\d+)$/',$post['key'],$matches);
		$action = $matches[1]; // e.g. edit
		$id = $matches[2]; // e.g. 1
		switch($action) {
			case "edit": header("Location:edit-wanted.php?id=".$id); exit; break;
			case "delete": header("Location:delete-ad.php?post_type=wanted&id=".$id); exit; break;
		}
				
	}	
	
	// Count query
	$query = "select count(*) 
	          from cf_wanted w, cf_users u 
	          where w.user_id = u.user_id ".$sql_search_ext." "
			  .($user_id? "and w.user_id = '".$user_id."'":"")." order by w.created_date desc";		
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$row = mysqli_fetch_row($result);
	$count = $row[0];

	// Create a pager for count query
	$pagerLink = $_SERVER['PHP_SELF'].'?orderBy='.$orderBy.'&direction='.$direction;
    if ($q) {
		$pagerLink .= '&q='.$q;
	}	
	$pager = new Pager($count,$start,$sortNum,$pagerLink);

	// Create the header links for the table columns
	$headerMapping = array (
		"ID" => "w.wanted_id",
		"Member name" => "u.first_name",
		"Created" => "w.created_date",
		"Last Updated" => "w.last_updated_date",
		"Views" => "w.times_viewed",
		"Status" => "w.published"
	);
	$link = "&user_id=".$user_id."&start=".$start."&sortNum=".$sortNum;
	$headerLinks = createHeaderLinks($headerMapping,$link,$orderBy,$direction);	
	
	// Data query
	$query = "
		select
			w.wanted_id,
			u.first_name,
			u.user_id,
			u.surname,
 			u.suppressed_replies as suppressed_replies,
                        u.facebook_id,
			date_format(w.created_date,'%d/%m/%Y - %k:%i') as `created_date`,
			date_format(w.last_updated_date,'%d/%m/%Y - %k:%i') as `last_updated_date`,
				w.location,
				w.postcode,
				w.bedrooms_required,
				w.building_type_flat,
				w.building_type_house,
			w.times_viewed,
			w.paid_for,
			w.approved,
			w.suspended,
			w.published,
                        w.country
		from cf_wanted as `w`, cf_users as `u` where u.user_id = w.user_id 
		".($user_id? "and u.user_id = '".$user_id."'":"")."
		".$sql_search_ext."		
		order by ".$orderBy." ".$direction." limit ".$start.", ".$sortNum."
	";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);	
	$debug .= debugEvent("Offered ads data query",$query);

	if (!mysqli_num_rows($result)) {
		
		// If no wanted ads exist on the system
		$a = '<tr class="trEmpty"><td colspan="8">No wanted ads have been placed yet</td></tr>';
		
	} else {
		
		$class = "trOdd";
		while($row = mysqli_fetch_assoc($result)) {
                        if ($row['suppressed_replies'] == 1) {$scammer = 'class="obligatory"'; } else { $scammer = NULL; };
			$a .= '<tr class="'.$class.'" ';
			$a .= 'onmouseover="this.className=\'trOver\';" onmouseout="this.className=\''.$class.'\';"';			
			$a .= '>'."\n";
			$a .= '<td align="center">'.$row['wanted_id'].'</td>'."\n";
			$a .= '<td '.$scammer.' ><a '.$scammer.' href="edit-member.php?user_id='.$row['user_id'].'">'.$row['first_name'].' '.$row['surname'].'</a>'.'</a>'.($row['facebook_id']==''?'':' <strong>*FB*</strong>').'</td>'."\n";
				
				if ($row['published']=="2") {$row_class='class="error"';} else {$row_class = '';}
				$a .= '<td>'.$row['country'].' <a href="../details.php?id='.$row['wanted_id'].'&post_type=wanted"><span '.$row_class.'>'.strip_tags(getAdTitle($row,"wanted")).'</span></a></td>'."\n";
				
				$a .= '<td align="center">'.$row['created_date'].'</td>'."\n";
				$a .= '<td align="center">'.$row['last_updated_date'].'</td>'."\n";
				$a .= '<td align="center">'.$row['times_viewed'].' - ('.photoCount($row['wanted_id'], "wanted").')</td>'."\n";
				$status = getStatus($row['paid_for'],$row['approved'],$row['suspended'],$row['published']);
				$a .= '<td align="center"><a href="change-status.php?post_type=wanted&id='.$row['wanted_id'].'" class="'.preg_replace('/\s/','_',strtolower($status)).'">'.$status.'</a></td>'."\n";
				$a .= '<td width="55"><input name="edit_'.$row['wanted_id'].'" type="submit" class="adminButton" value="Edit"/></td>'."\n";
				$a .= '<td width="55"><input name="delete_'.$row['wanted_id'].'" type="submit" class="adminButton" value="Delete" onClick="return confirm(\'Are you sure you want to delete this ad?\nYou cannot undo this action.\');"/></td>'."\n";
			$a .= '</tr>'."\n";
			$class = ($class == "trOdd")? "trEven":"trOdd";
			// Store the user_name
			if ($user_id) {
				$user_name = $row['first_name']." ".$row['surname'];
			}	
		}
	
	}
	
	// If another page has called this page and we need to report on the result of an action:
	if (isset($_REQUEST['report'])) {
		switch($_REQUEST['report']) {
			case "deletionSuccess":
				$msg = '<p class="success">Ad was deleted successfully</p>';
				break;
			case "deletionFailure":
				$msg = '<p class="error">An error occured when deleting ad. Please contact '.TECH_EMAIL.'</p>';
				break;	
			case "updateSuccess":
				$msg = '<p class="success">Ad has been updated successfully</p>';
				break;
			case "updateFailure":
				$msg = '<p class="error">An error occured when updating ad. Please contact '.TECH_EMAIL.'</p>';
				break;	
			case "statusChangeSuccess":
				$msg = '<p class="success">Ad status has been changed successfully.</p>';
				break;
			case "statusChangeFailure":
				$msg = '<p class="error">An error occured when updating ad status. Please contact '.TECH_EMAIL.'</p>';
				break;	
			case "statusChangeSuccessEmailSuccess":
				$msg = '<p class="success">Ad status has been changed successfully. Email was sent successfully.</p>';
				break;
			case "statusChangeSuccessEmailFailure":
				$msg = '<p class="success">Ad status has been changed successfully. <span class="error">Email was NOT sent. Please contact '.TECH_EMAIL.'</span></p>';
				break;											
					
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
<!-- InstanceParam name="highlightPage" type="text" value="4" -->
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
		<li><a href="wanted-ads.php" class="current">View wanted ads</a></li>
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


<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
    <tr>
	  <td> 
	  <h1 class="mt0">Wanted ads</h1>
<p><strong>The table below shows all Wanted ads at ChristianFlatShare.org<?php print ($user_id? " for user ".$user_name:"")?>.</strong><br />You can change the status of an advertisement by clicking on it.<br />All dark column headers can be clicked to sort.</p>
      <td width="300" align="right" valign="top">
		<div id="admin_quick_search">
			<form name="quick_search_form" method="get" action="<?php print $_SERVER['PHP_SELF']?>">
			<div style="margin-bottom:4px;">Member firstname, surname, email, ad ID: <span class="grey"></span></div>
			<div>
				<input name="q" id="quick_search" type="text" value="<?php print $q?>"/>
				<input id="quick_search_button" type="submit" value="Search" /></div>
			</form>
		</div>		
	  </td>
    </tr>
</table>


<?php print $msg?>
<form name="form1" id="form1" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:1em;">
    <tr>
        <td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?> </strong> offered ads.</td>
   		<td align="right"><?php print $pager->createLinks()?></td>
    </tr>
</table>
<table width="100%" border="0" cellpadding="4" cellspacing="0" class="greyTable">
	<tr>
		<th nowrap="nowrap"><a href="<?php print $headerLinks['ID']['href']?>">ID<?php print $headerLinks['ID']['icon']?></a></th>
		<th nowrap="nowrap"><a href="<?php print $headerLinks['Member name']['href']?>">Member name<?php print $headerLinks['Member name']['icon']?></a></th>
		<th nowrap="nowrap">Ad summary</a></th>
		<th nowrap="nowrap"><a href="<?php print $headerLinks['Created']['href']?>">Created<?php print $headerLinks['Created']['icon']?></a></th>
		<th nowrap="nowrap"><a href="<?php print $headerLinks['Last Updated']['href']?>">Last Updated<?php print $headerLinks['Last Updated']['icon']?></a></th>
		<th nowrap="nowrap"><a href="<?php print $headerLinks['Views']['href']?>">Views<?php print $headerLinks['Views']['icon']?></a></th>
		<th nowrap="nowrap"><a href="<?php print $headerLinks['Status']['href']?>">Status<?php print $headerLinks['Status']['icon']?></a></th>
		<th colspan="2" nowrap="nowrap">Actions</th>
	</tr>
	<?php print $a?>
</table>
</form>
<p><a href="index.php">Return to the main administration page</a></p>
<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
