<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	// In order for this page to work, the user_id must be provided
	if (isset($_REQUEST['user_id'])) { $user_id = $_REQUEST['user_id']; } else { header("Location:index.php"); exit; }
	
	// Initialise all needed variables
        if (isset($_POST['access'])) { $access = trim($_POST['access']); } else { $access = 'member'; }
	if (isset($_POST['email'])) { $email = trim($_POST['email']); } else { $email = NULL; }
	if (isset($_POST['new_pass_1'])) { $new_pass_1 = trim($_POST['new_pass_1']); } else { $new_pass_1 = NULL; }
	if (isset($_POST['new_pass_2'])) { $new_pass_2 = trim($_POST['new_pass_2']); } else { $new_pass_2 = NULL; }	
	if (isset($_POST['first_name'])) { $first_name = addslashes(trim($_POST['first_name'])); } else { $first_name = NULL; }
	if (isset($_POST['surname'])) { $surname = addslashes(trim($_POST['surname'])); } else { $surname = NULL; }
	if (isset($_POST['suppressed_replies'])) { $suppressed_replies = $_POST['suppressed_replies']; } else { $suppressed_replies = NULL; }
	if (isset($_POST['email_verified'])) { $email_verified = $_POST['email_verified']; } else { $email_verified = NULL; }	
	if (isset($_POST['hear_about'])) { $hear_about = addslashes(trim($_POST['hear_about'])); } else { $hear_about = NULL; }
	if (isset($_POST['church_attended'])) { $church_attended = addslashes(trim($_POST['church_attended'])); } else { $church_attended = NULL; }	
	if (isset($_POST['agree'])) { $agree = $_POST['agree']; } else { $agree = NULL; }
	if (isset($_POST['news_opt_in'])) { $news_opt_in = $_POST['news_opt_in']; } else { $news_opt_in = 0; }
	if (isset($_POST['account_suspended'])) { $account_suspended = $_POST['account_suspended']; } else { $account_suspended = 0; }	
	if (isset($_POST['created_date'])) { $created_date = $_POST['created_date']; } else { $created_date = NULL; }
	if (isset($_POST['last_updated_date'])) { $last_updated_date = $_POST['last_updated_date']; } else { $last_updated_date = NULL; }
	if (isset($_POST['last_login'])) { $last_login = $_POST['last_login']; } else { $last_login = NULL; }
	if (isset($_POST['facebook_id'])) { $facebook_id = $_POST['facebook_id']; } else { $facebook_id = NULL; }
	if (isset($_POST['cancel'])) { header("Location: members.php"); exit; } // Redirect if user pressed the cancel button
	$showForm = TRUE; // By default, show the registration form
	$now = new DateTime(); // Store current date / time into $now
	
	if ($_POST) {

		// Validate, then handle registration
		if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[_0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {		
  		$error['email'] = '<span class="error">Please enter a valid email address</span><br/>';
		} 
		
		// else {
			// validating email address entered by user if already registered.
		//	$query = "select * from cf_users where email_address = '".$email."' and user_id != '".$user_id."'";
		//	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		//	if (mysqli_num_rows($result)) {
		//		$error['email'] = '<span class="error">A user is already registered with this email address</span><br/>';
		//	}
		//}
		if ($new_pass_1) {
			if (!preg_match('/^(?=.*\d)(?=.*[a-zA-Z]).{6,16}$/',$new_pass_1)) { // New password entered?
				$error['new_pass_1'] = '<span class="error">Please enter a valid password</span><br/>';
			} else if (!$new_pass_2) { // Verification of new password entered?
				$error['new_pass_2'] = '<span class="error">Please verify your password</span><br/>';
			} else if ($new_pass_1 != $new_pass_2) { // Verification matches new password?
				$error['new_pass_2'] = '<span class="error">Passwords do not match</span><br/>';
			}		
		}		
		if (trim($first_name) == "") { $error['first_name'] = '<span class="error">Please enter the member\'s first name</span><br/>'; }
		if (trim($surname) == "") { $error['surname'] = '<span class="error">Please enter the member\'s surname</span><br/>'; }
		// If DOB was specified, check for validity
		if ($dob) {
			// Make sure date follows the DD/MM/YYYY pattern
			if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/',$dob,$matches)) {
				$error['dob'] = '<span class="error">Please enter a valid date of birth (DD/MM/YYYY) or leave blank</span><br/>';
			} 
			// Make sure date is a VALID date
			if (!checkdate($matches[2],$matches[1],$matches[3])) {
				$error['dob'] = '<span class="error">Date of birth is not a valid gregorian date.</span><br/>';
			}
			// Make sure the date is in the past
			$dobDate = new DateTime();
			$dobDate->setDate($matches[3]."-".$matches[2]."-".$matches[1]);
			if ($dobDate > $now) {
				$error['dob'] = '<span class="error">Time paradox error. Please make sure member is born in the past.</span><br/>'."\n";
			}
			// If we have not encountered a date error yet
			if (!$error['dob']) {
				$dobSQL = $matches[3]."-".$matches[2]."-".$matches[1];
			}
		}
	
		// If errors occured
		if ($error) {
			
			$msg = '<p class="error">Errors were found in your form. Please amend</p>'."\n";
		
		} else {

			// Update database
			$query = "update cf_users set ";
		//	$query .= "last_updated_date = '".$now->getDate()."',"; // last_updated_date
			$query .= "email_address = '".$email."',";
			$query .= "first_name = '".$first_name."',";
			$query .= "surname = '".$surname."',";
			$query .= "access = '".$access."',";			
			$query .= "suppressed_replies = '".$suppressed_replies."',";
			$query .= "email_verified = '".$email_verified."',";			
			$query .= "hear_about = '".$hear_about."',";
			$query .= "church_attended = '".$church_attended."',";			
			if ($new_pass_1) { $query .= "password = '".md5($new_pass_1)."',"; }
			$query .= "news_opt_in = '".$news_opt_in."', ";
			$query .= "account_suspended = '".$account_suspended."', ";			
                        if (!is_numeric($facebook_id) && $facebook_id != 0) {  
			  $query .= "facebook_id = '".$facebook_id."' ";			
                        } else { 
		  	  $query .= "facebook_id = null ";			
                        }
			$query .= "where user_id = ".$user_id;
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$debug .= debugEvent("Update query",$query);
			$showForm = FALSE; // don't show form
			if ($result) {
				$msg  = '<p class="success">Member\'s profile has been successfully updated.</p>'."\n";
			} else {
				die(mysqli_error());
				$msg = '<p class="error">An error occured when updating member\'s profile. Please contact '.TECH_EMAIL.'</p>'."\n";
			}
			
    	// Update database
			if ($suppressed_replies)
			 // Suppress Offered Ads
			 {
			  $query = "update cf_offered set suspended = 1 where user_id = '".$user_id."' and suspended = 0;";
 		  	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$result_count = mysqli_affected_rows();
			  $debug .= debugEvent("Scam update query 1",$query);
				
			  if ($result) {
			 	 $msg .= '<p class="success">'.$result_count.' offered scam adverts where suspended</p>'."\n";
			  } else {
				die(mysqli_error());
				$msg = '<p class="error">An error occured when updating member\'s profile. Please contact '.TECH_EMAIL.'</p>'."\n";				
			  }
				
			 // Suppress Wanted Ads				
				$query = "update cf_wanted set suspended = 1 where user_id = '".$user_id."' and suspended = 0;";
 		  	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$result_count = mysqli_affected_rows();
			  $debug .= debugEvent("Scam update query 2",$query);
			  if ($result) {
			 	 $msg .= '<p class="success">'.$result_count.' wanted scam adverts where suspended</p>'."\n";
			  } else {
				die(mysqli_error());
				$msg = '<p class="error">An error occured when updating member\'s profile. Please contact '.TECH_EMAIL.'</p>'."\n";				
			  }
			 } 
                       /*   else {
			 // Ususpended adverts
                         // Update, 29-JUN-2014 - we don't want to do this... this is un-suepending all adverts... and could be done for any updates to the user record
 			  $query = "update cf_offered set suspended = 0 where user_id = '".$user_id."' and suspended = 1;";
 		    $result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$result_count = mysqli_affected_rows();
			  $debug .= debugEvent("Scam update query 3",$query);
				
			 if ($result) {
			 	 $msg .= '<p class="success">'.$result_count.' offered adverts where unsuspended</p>'."\n";
			  } else {
 				 die(mysqli_error());
				 $msg = '<p class="error">An error occured when updating member\'s profile. Please contact '.TECH_EMAIL.'</p>'."\n";				
			}
				
			  // Suppress Wanted Ads				
				$query = "update cf_wanted set suspended = 0 where user_id = '".$user_id."' and suspended = 1;";
 		  	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$result_count = mysqli_affected_rows();
			  $debug .= debugEvent("Scam update query 4",$query);
			  if ($result) {
			 	 $msg .= '<p class="success">'.$result_count.' wanted adverts where unsuspended</p>'."\n";
			  } else {
				 die(mysqli_error());
 				 $msg = '<p class="error">An error occured when updating member\'s profile. Please contact '.TECH_EMAIL.'</p>'."\n";				
			}
		       } */			
				$msg .= '<p><a href="edit-member.php?user_id='.$user_id.'">View changed profile</a>&nbsp;|&nbsp;<a href="members.php">Return to the members list</a></p>'."\n";
	              } // end IF error	
        // If POST, else
	} else {
	
		$query = "
			select 
				user_id,
				date_format(created_date,'%d/%m/%Y') as `created_date`,
				date_format(last_updated_date,'%d/%m/%Y - %k:%i') as `last_updated_date`,
				date_format(last_login,'%d/%m/%Y - %k:%i') as `last_login`,
				first_name,
				surname,
				email_address,
				suppressed_replies,
				email_verified,				
				news_opt_in,
				account_suspended,				
				hear_about,
				church_attended,				
				active,
				password, 
				access,
                                facebook_id
			from cf_users where user_id = '".$user_id."'	
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		if (!$result) {
			$showForm = FALSE;
			$msg = '<p class="error">Could not retrieve your profile details. Please contact '.TECH_EMAIL.'</p>'."\n";
		} else {
			$udata = mysqli_fetch_assoc($result);
			$email = $udata['email_address'];
			$first_name = $udata['first_name'];
			$surname = $udata['surname'];
			$suppressed_replies = $udata['suppressed_replies'];			
			$email_verified = $udata['email_verified'];									
			$hear_about = $udata['hear_about'];
			$church_attended = $udata['church_attended'];			
			$news_opt_in = $udata['news_opt_in'];
			$account_suspended = $udata['account_suspended'];			
			$created_date = $udata['created_date'];
			$last_updated_date = $udata['last_updated_date'];
			$last_login = $udata['last_login'];
			$password = $udata['password'];						
			$access = $udata['access'];									
			$facebook_id = $udata['facebook_id'];									
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
<!-- InstanceBeginEditable name="head" --><!-- TemplateParam name="class" type="text" value="current" --><!-- InstanceEndEditable -->
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
		<!-- <li><a href="stats.php" class="">Statistics</> -->
		<!-- <li><a href="#" class="">View payment history</a></li> -->
	</ul>
</div>
<div><img src="../images/spacer.gif" width="100%" height="1"></div>
<div id="MAIN_CONTENT">
<!-- InstanceBeginEditable name="mainContent" -->
<h1 class="mt0">Editing profile for <?php print $first_name?> <?php print $surname?>.</h1>
<p>Please use the form below to edit the profile of this member:</p>
<?php print $msg?>
<?php if ($showForm) { ?>
<form name="editMember" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="user_id" value="<?php print $user_id?>" />
<input type="hidden" name="created_date" value="<?php print $created_date?>" />
<input type="hidden" name="last_updated_date" value="<?php print $last_updated_date?>" />
<input type="hidden" name="last_login" value="<?php print $last_login?>" />
<div class="fieldSet">
<div class="fieldSetTitle">Login information:</div>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td align="right"><span class="obligatory">*</span>&nbsp;Email Address:</td>
			<td><?php print $error['email']?><input name="email" value="<?php print $email?>" type="text" id="email" size="50" maxlength="100" /></td>
		</tr>
		<tr>
			<td align="right">New password :</td>
			<td><?php print $error['new_pass_1']?><input name="new_pass_1" type="password" id="new_pass_1" size="32" maxlength="16" /></td>
		</tr>
		<tr>
			<td width="130" align="right">&nbsp;Repeat new password :</td>
			<td><?php print $error['new_pass_2']?><input name="new_pass_2" type="password" id="new_pass_2" size="32" maxlength="16" /></td>
		</tr>
	</table>
	</p>
</div>
<div class="fieldSet">
<div class="fieldSetTitle">Members details:</div>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td align="right"><span class="obligatory">*&nbsp;</span>First Name:</td>
			<td><?php print $error['first_name']?><input name="first_name" value="<?php print stripslashes($first_name)?>" type="text" id="first_name" size="50" maxlength="50" /></td>
		</tr>
		<tr>
			<td align="right"><span class="obligatory">*</span>&nbsp;Surname:</td>
			<td><?php print $error['surname']?><input name="surname" value="<?php print stripslashes($surname)?>" type="text" id="surname" size="50" maxlength="50" /></td>
		</tr>
		<tr>
			<td align="right">Account Type:</td>
			<td><?php 
				$tempArray = array("member"=>"member","advertiser"=>"advertiser");
				echo createDropDown("access",$tempArray,$access);?>
			</td>
		</tr>		
		<tr>
			<td align="right">Suppressed Replies:</td>
			<td><?php 
				$tempArray = array(""=>"NO","1"=>"YES");
				echo createDropDown("suppressed_replies",$tempArray,$suppressed_replies);?>
			</td>
		</tr>
        <tr>					
			<td align="right">Email verified:</td>		
			<td><?php 
				$tempArray = array("0"=>"NO","1"=>"YES");
				echo createDropDown("email_verified",$tempArray,$email_verified);
			?></td>			
		</tr>
		<tr class="formNoPadding">
			<td width="130" align="right">Password hash</td>
			<td><strong><?php print $password?></strong></td>
		</tr>		
		<tr class="formNoPadding">
			<td width="130" align="right">Where did you<br />hear about us?</td>
	  	    <td><input name="hear_about" value="<?php print stripslashes($hear_about)?>" type="text" id="hear_about" size="75" maxlength="255" /></td>
		</tr>

		<tr class="formNoPadding">
			<td width="130" align="right">Church</td>
			<td><input name="church_attended" value="<?php print stripslashes($church_attended)?>" type="text" id="church_attended" size="75" maxlength="255" /></td>
		</tr>		
		<tr class="formNoPadding">
			<td width="130" align="right">Facebook ID</td>
			<td><input name="facebook_id" value="<?php print $facebook_id ?>" type="text" id="facebook_id" size="40" maxlength="255" /></td>
		</tr>		
	</table>

	Posted ads:
    <p><?php print createSummaryForAllAdsAll($_REQUEST['user_id']);?></p>
	Saved ads:
		<p><?php print createSummaryofSavedAds($_REQUEST['user_id']);?></p>
	Logins:
		<p><?php print createSummaryForLogins($_REQUEST['user_id']);?></p>
	Messages:
		<p><?php print createSummaryForEmails($_REQUEST['user_id']);?></p>
	</div>	

<div class="fieldSet">
	<div class="fieldSetTitle">Member statistics:</div>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td>Date created: </td>
			<td><strong><?php print $created_date?></strong></td>
		</tr>
		<tr>
			<td>Last updated:</td>
			<td><strong><?php print $last_updated_date?></strong></td>
		</tr>
		<tr>
			<td>Last login:</td>
			<td><strong><?php print $last_login?></strong></td>
		</tr>		
	</table>
</div>
<div class="fieldSet">
	<div class="fieldSetTitle">CFS News subscription </div>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td><input name="news_opt_in" type="checkbox" id="news_opt_in" value="1" <?php if ($news_opt_in) { ?>checked="checked"<?php } ?>/></td>
			<td>Member receives emails from ChristianFlatShare.org   <span class="grey"></span></td>
		</tr>
		
		<tr>
			<td><input name="account_suspended" type="checkbox" id="account_suspended" value="1" <?php if ($account_suspended) { ?>checked="checked"<?php } ?>/></td>
			<td>Account Suspended (you will need to suspend adverts manually)<span class="grey"></span></td>
		</tr>		
	</table>
	<p class="mb0"><input type="submit" name="submit" value="Save changes" />&nbsp;<input type="submit" name="cancel" value="Cancel"/></p>
</div>
</form>
<?php } ?>
<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
