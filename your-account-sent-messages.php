<?php
session_start();

// Autoloader
require_once 'web/global.php';


require('includes/class.pager.php');		// Pager class

connectToDB();

	$remove_msg = "";
	
	
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }	
	
	if (isset($_REQUEST['start'])) { 
		$start = $_REQUEST['start']; 
	} elseif (isset($_GET['start'])) { 
		$start = $_GET['start']; 
	} else { 
		$start = 0; 
	}
	if (isset($_REQUEST['sortNum'])) { $sortNum = $_REQUEST['sortNum']; } else { $sortNum = 10; }
	if (isset($_REQUEST['orderBy'])) { $orderBy = $_REQUEST['orderBy']; } else { $orderBy = 'reply_date'; }
	if (isset($_REQUEST['direction'])) { $direction = $_REQUEST['direction']; } else { $direction = 'desc'; }
	
	if (isset($_GET['reply_id'])) { $reply_id = $_GET['reply_id']; } else { $reply_id = NULL; }
	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }	

	
	// Count query
	$query = "SELECT e.reply_id
						FROM cf_email_replies as `e`, 
						     cf_users as `u`,
						     cf_offered o
						WHERE u.user_id = e.from_user_id
						 AND e.from_user_id = '".$_SESSION['u_id']."'
						 AND o.offered_id = e.to_ad_id		
						 AND e.to_post_type = 'offered'						
						 AND e.sender_deleted = 0
						UNION ALL
						SELECT e.reply_id
						FROM cf_email_replies as `e`, 
				 	       cf_users as `u`,
				         cf_wanted w
		        WHERE u.user_id = e.from_user_id
						 AND e.from_user_id = '".$_SESSION['u_id']."'
						 AND w.wanted_id = e.to_ad_id
						 AND e.to_post_type = 'wanted'						
						 AND e.sender_deleted = 0		
						UNION ALL
						SELECT e.reply_id
						FROM cf_email_replies as `e`, 
						     cf_users as `u`,
						     cf_offered_archive o
						WHERE u.user_id = e.from_user_id
						 AND e.from_user_id = '".$_SESSION['u_id']."'
						 AND o.offered_id = e.to_ad_id		
						 AND e.to_post_type = 'offered'						
						 AND e.sender_deleted = 0
						UNION ALL
						SELECT e.reply_id
						FROM cf_email_replies as `e`, 
				 	       cf_users as `u`,
				         cf_wanted_archive w
		        WHERe u.user_id = e.from_user_id
						 AND e.from_user_id = '".$_SESSION['u_id']."'
						 AND w.wanted_id = e.to_ad_id
						 AND e.to_post_type = 'wanted'						
						 AND e.sender_deleted = 0								
						";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$debug .= $query;		
	$count = mysqli_num_rows($result);
	 	 
	// Create a pager for the data
	$pagerLink = $_SERVER['PHP_SELF']."?orderBy=".$orderBy."&direction=".$direction;
	$pager = new Pager($count,$start,$sortNum,$pagerLink);			
		
	// If we're temporarily suspending, unsuspending an ad, or to republish 
	switch($action) {
		case "delete":
			$remove_msg = '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:280px;">
			<p class="mb0 mt0 style5">Please confirm message deletion</p>
			Delete message?<br /><br /><a href="'.$_SERVER['PHP_SELF'].'?action=delete_confirmed&reply_id='.$reply_id.'&start='.$start.'">Delete</a>&nbsp;&nbsp;or&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?start='.$start.'">Cancel</a></div><br />';
			$start = 0;
			break;
	
		case "delete_confirmed":
			$query = "update cf_email_replies 
								set sender_deleted = 1 
								where reply_id = ".$reply_id." 
								and from_user_id = ".$_SESSION['u_id'];
			$debug .= $query;
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				header('Location:../your-account-sent-messages.php?start='.$start.'&action=deleted'); exit;
			} else {
				$remove_msg = '<p class="error"><strong>There was a problem deleting this sent message.</strong></p>';
			}
			break;
			
		case "deleted":
				$remove_msg = '<p class="green"><strong>The message was deleted from your sent messages.</strong></p>';
			break;
	}
	
	// Load all the "Saved ads" for this user
	$s = "";
	
	// 1. Create array of replies
	$query = "
		SELECT e.reply_id,
					 e.to_user_id,
					 e.message,
					 e.reply_date reply_date,					 
					 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
           'LIVE_AD' as `status`,
					 to_ad_id as `ad_id`,
					 to_post_type as `post_type`,
					 concat_ws(' ',u_to.first_name, u_to.surname) as `to_name`,					 
					 suspended,
					 u_to.suppressed_replies as `scam_reply`					 
		FROM cf_email_replies as `e`, 
				 cf_users as `u`,
				 cf_users as `u_to`,				 
				 cf_offered o
		WHERE u.user_id = e.from_user_id
	  AND u_to.user_id = e.to_user_id		
		AND e.from_user_id = '".$_SESSION['u_id']."'
		AND o.offered_id = e.to_ad_id		
		AND e.to_post_type = 'offered'		
		AND e.sender_deleted = 0 
		";
		if ($action == "delete") { $query .= " and reply_id = ".$reply_id." "; }
	$query .= "	
	  UNION ALL		
		SELECT e.reply_id,
					 e.to_user_id,
					 e.message,
					 e.reply_date reply_date,					 
					 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
           'DELETED_AD' as `status`,
					 to_ad_id as `ad_id`,
					 to_post_type as `post_type`,
					 concat_ws(' ',u_to.first_name, u_to.surname) as `to_name`,					 
					 suspended,
					 u_to.suppressed_replies as `scam_reply`					 
		FROM cf_email_replies as `e`, 
				 cf_users as `u`,
				 cf_users as `u_to`,				 
				 cf_offered_archive o
		WHERE u.user_id = e.from_user_id
	  AND u_to.user_id = e.to_user_id		
		AND e.from_user_id = '".$_SESSION['u_id']."'
		AND o.offered_id = e.to_ad_id		
		AND e.to_post_type = 'offered'		
		AND e.sender_deleted = 0 
			";
  if ($action == "delete") { $query .= " and reply_id = ".$reply_id." "; }
	$query .= "	
		UNION ALL 
		SELECT e.reply_id,
					 e.to_user_id,		
		       e.message,
					 e.reply_date reply_date,
					 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
        	 'LIVE_AD' as `status`,
					 to_ad_id as `ad_id`,
					 to_post_type as `post_type`,
					 concat_ws(' ',u_to.first_name, u_to.surname) as `to_name`,
					 w.suspended,
					 u_to.suppressed_replies as `scam_reply`
		FROM cf_email_replies as `e`, 
				 cf_users as `u`,
				 cf_users as `u_to`,				 
				 cf_wanted w
		WHERE u.user_id = e.from_user_id
	  AND u_to.user_id = e.to_user_id				
		AND e.from_user_id = '".$_SESSION['u_id']."'
		AND w.wanted_id = e.to_ad_id
		AND e.to_post_type = 'wanted'
		AND e.sender_deleted = 0 ";
	if ($action == "delete") { $query .= " and reply_id = ".$reply_id." "; }		
		$query .= "	
		UNION ALL 
		SELECT e.reply_id,
					 e.to_user_id,		
		       e.message,
					 e.reply_date reply_date,
					 DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date_formatted`,
        	 'DELETED_AD' as `status`,
					 to_ad_id as `ad_id`,
					 to_post_type as `post_type`,
					 concat_ws(' ',u_to.first_name, u_to.surname) as `to_name`,
					 w.suspended,
					 u_to.suppressed_replies as `scam_reply`
		FROM cf_email_replies as `e`, 
				 cf_users as `u`,
				 cf_users as `u_to`,				 
				 cf_wanted_archive w
		WHERE u.user_id = e.from_user_id
	  AND u_to.user_id = e.to_user_id				
		AND e.from_user_id = '".$_SESSION['u_id']."'
		AND w.wanted_id = e.to_ad_id
		AND e.to_post_type = 'wanted'
		AND e.sender_deleted = 0 ";
	if ($action == "delete") { $query .= " and reply_id = ".$reply_id." "; }		
	$query .= "order by ".$orderBy." ".$direction." limit ".$start.", ".$sortNum;
		
    $class = "odd";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$debug .= $query;
		if (mysqli_num_rows($result)) {
		while($reply = mysqli_fetch_assoc($result)) {
			
			// Determine if this sent message has been replied to: if members are corresponding then 
			// the real name (cf_users name) can be revealed, otherwise "Ad owner" is shown.
    /*  $query = "SELECT reply_id  
			 					FROM cf_email_replies
								WHERE to_user_id = ".$_SESSION['u_id']."
								AND   from_user_id = ".$reply['to_user_id'];
			$name_result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (mysqli_num_rows($name_result)) {
				$from_name = $reply['name'];
			} else {
				$from_name = 'Ad owner';			
			}  */
		
		$s .= '<table cellpadding="5" cellspacing="0" width="100%">';		
			$s .= '<tr class="'.$class.'">'."\n";
				$s .= '<td style="padding-top:7px;padding-left:10px;padding-right:10px;padding-bottom:0px;">';
				$s .= '<strong>Sent: </strong>'.$reply['reply_date_formatted'].'<br />';						
				
				if ($reply['to_user_id'] == "570") { $row_class_start = '<span class="red">'; $row_class_end = '</span>'; } else { $row_class_start = ''; $row_class_end = ''; }			
				$s .= '<strong>To: </strong>'.$row_class_start.$reply['to_name'].$row_class_end.'<br />';				
				
				$s .= '</td>';
				if ($action != "delete") {
					$s .= '<td align="right" valign="top" style="padding-top:7px;padding-left:10px;padding-right:10px">';				
//			$s .= '<a href="your-account-message-reply.php?action=sent_thread&reply_id='.$reply['reply_id'].'">Thread</a> | ';									
/*					if ($reply['to_user_id'] == $_SESSION['u_id']) {
						$s .= '<span class="grey">To you</span> | ';					
					} else {
						$s .= '<a href="your-account-message-reply.php?action=thread&reply_id='.$reply['reply_id'].'">Thread</a> | ';						
					} */
					$s .= '<a href="'.$_SERVER['PHP_SELF'].'?action=delete&reply_id='.$reply['reply_id'].'&start='.$start.'">Delete</a></td>';				
				}
			$s .= '</tr>';			
			$s .= '</table>';					
				
			$s .= '<table cellspacing="0" width="100%">';		
			$s .= '<tr class="'.$class.'">'."\n";			
			$s .= '<td style="padding-top:0px;padding-left:10px;padding-right:10px;padding-bottom:5px;">';
					// Open Title externally
				$s .= '<strong>Advert: '.getAdTitleByID($reply['ad_id'],$reply['post_type'],TRUE, FALSE, TRUE).'</strong>';
				if ($reply['status'] == 'DELETED_AD') {
					$s .= '<br /><span class="red">(this advert has been deleted)</span>';
 			   } else if ($reply['scam_reply'] == 1) {
				  $s .= '<br /></span><span class="red">(spam/scam advert)</span>';
				} else if ($reply['suspended']) {
					$s .= '<br /></span><span style="color:#FF9900;">(this ad is suspended)</span>';
				}					
			$s .= '</td>';
			$s .= '</tr>';
			$s .= '</table>';					

			$s .= '<table cellpadding="5" cellspacing="0" width="100%">';		
			$s .= '<tr class="'.$class.'">'."\n";
			$s .= '<td style="padding-left:10px;padding-right:25px;padding-bottom:25px;">'."\n";			
				$s .= '<strong>Your message: </strong><br />'; 
				$message = str_replace("#\\#", "#\#", $reply['message']);
				$message = str_replace("#\\\#", "#\#", $message);				
				$message = str_replace("#\\#", "#\#", $message);								
				$s .= nl2br(makeClickableLinks(stripslashes($message)));
			$s .= '</td></tr>'."\n";			
			$s .= '</table>';								
			$s .= '<br />';												
//			$class = ($class == "even")? "odd":"even";				
						
		}
	}

	// Set the flag for the weclome message to TRUE
	if ($count == 0) {
	  $welcome_msg = TRUE;		
		$s .= '<p>You have no sent messages.</p>';
	} elseif (!$s && $count > 0) {
		$welcome_msg = FALSE;		
	  $start = $start - 10;
		header('Location:../your-account-sent-messages.php?start='.$start.'&action=deleted'); exit;
	} else {
		$welcome_msg = FALSE;	
		$temp .= '<table cellpadding="5" cellspacing="0"  width="100%">';
		$temp .= $s;
		$temp .= '</table>';
		$s = $temp;
		unset($temp);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your sent messages - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" -->
<style type="text/css">
<!--
.style1 {color: #000000}
.style5 {
	font-size: 14px;
	font-weight: bold;
}
-->
</style>
<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<div class="cl" id="cl">
			<h1 class="mt0">Your sent messages</h1>
			<div class="clear"><!----></div>
			<?php print $msg?>
			<?php print $remove_msg?>
			<?php if (!$welcome_msg && $action != "delete" ) { ?>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
   		<tr>
        <td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?> </strong> messages.</td>
   			<td align="right"><?php print $pager->createLinks()?></td>
    	</tr>
			</table>	
			<?php } ?>			
			<?php print $s?>		
			<?php if (!$welcome_msg && $count > 10 && $action != "delete") { ?>			
				<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:1em auto;">
   		<tr>
        <td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?> </strong> messages.</td>
   			<td align="right"><?php print $pager->createLinks()?></td>
    	</tr>
			</table>				
			<form name="updateForm" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
			</form>
			<?php } ?>						
			<?php if ($welcome_msg) { ?>
		  <div class="mt10" style="width:290px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
				<h2 class="mb0 mt0">Your sent messages</h2>
	      <p class="mb0 mt0" align="left">Your replies to adverts and messages are shown here.</p>
		  </div>
		    <?php } ?>				
		  </p>
		</div>
		<div class="cs" style="width:20px; height:270px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
			
			<?php if (rand(1,100)> 65) { ?>
				<?php print sharingCFS()?>			
			<?php } else { ?>				
					<script type="text/javascript"><!--
					google_ad_client = "pub-3776682804513044";
					/* 250x250 image only, created 12/04/09 */
					google_ad_slot = "9681363594";
					google_ad_width = 250;
					google_ad_height = 250;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>			
			<?php } ?>
			<p class="mt0 mb0">&nbsp;</p>
					<script type="text/javascript"><!--
					google_ad_client = "pub-3776682804513044";
					/* 250x250 image only, created 12/04/09 */
					google_ad_slot = "9681363594";
					google_ad_width = 250;
					google_ad_height = 250;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>			
										
		</div>
		<div class="cc0"><!----></div>
		<!-- InstanceEndEditable -->
		</div>
		<div class="redMenu">
			<ul>
				<!--<li><a href="../flat-finding-tips.php">flat finding tips</a></li>-->
				<li><a href="advertising.php">advertising</a></li>			
				<li><a href="where-does-all-the-money-go.php">where does the money go?</a></li>
				<li><a href="glossary.php">glossary</a></li>
				<li><a href="terms-and-conditions.php">terms &amp; conditions</a></li>
				<li><a href="privacy-policy.php">privacy policy</a></li>
				<li><a href="contact-us.php">contact us</a></li>
				<li class="noSeparator"><a href="resources.php">links &amp; resources</a></li>
			</ul>
		</div>
		<div id="footerText">
			<p class="m0"><strong>Christian Flatshare... helping accommodation seekers connect with the local church community<br />
			Finding homes, growing churches and building communities </strong>&copy; ChristianFlatShare.org 2007-<?php print date("Y")?></p>
	  </div>
	</div>
	<div id="footer"><img src="images/spacer.gif" alt="*" width="1" height="12"/></div>
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
<?php print getTrackingCode();?>
</body>
<!-- InstanceEnd --></html>
