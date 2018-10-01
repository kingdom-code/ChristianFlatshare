<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

   // Dissallow access if user not logged in
   if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
   
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
   
   if (!isset($_REQUEST['reply_id'])) { header("Location:your-account-manage-posts.php"); exit; } else { $reply_id = $_REQUEST['reply_id']; }
   if (!isset($_REQUEST['reply_hash'])) { header("Location:your-account-manage-posts.php"); exit; } 
   		else { $reply_hash = $_REQUEST['reply_hash']; }   
   if (!isset($_REQUEST['class'])) { header("Location:your-account-manage-posts.php"); exit; } else { $class = $_REQUEST['class']; }		
   if (!isset($_REQUEST['action'])) { header("Location:your-account-manage-posts.php"); exit; } else { $action = $_REQUEST['action']; }	
   
   	if (isset($_POST['reply_id'])) { $delete_reply_id = $_POST['reply_id']; } else { $delete_reply_id = NULL; }
if ($_POST) (
	if (isset($_POST['delete'])) { 
	
		// User has clicked on the delete button: 
		//Insert comments
		$query = "update cf_email_replies set deleted = 1 where reply_id = ".$delete_reply_id;
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		if ($result) {
			header("Location: your-account-manage-posts.php?report=emailDeletionSuccess"); exit;
		} else {
			header("Location: your-account-manage-posts.php?report=emailDeletionFailure"); exit;
		}
		
	} else if (isset($_POST['cancel'])) {
	
		header("Location:your-account-manage-posts.php");
		exit;	
	
 	}
	
}	
	
	// First, find out if we're dealing with a valid id.
	// Show details for the email we're about to delete			  	
	$query = "select DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
			         e.message,
				     CONCAT_WS(' ', u_from.first_name,u_from.surname) as `name`,
				     u_from.first_name as `first_name`,				   
				     u_from.email_address as `email_address`,
				     reply_id, 
				     u_from.user_id as `from_user_id`,
					 e.to_ad_id,
					 e.to_post_type					 
	          from cf_email_replies e,
				 cf_users as `u_from`
			  where e.reply_id = ".$reply_id."
			  and   u_from.user_id = e.from_user_id
			  and   e.deleted = 0
			  and   u_from.suppressed_replies = 0";
	$debug .= debugEvent("cf_email_replies based on passed in ID",$query);			  
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!mysqli_num_rows($result)) { header("Location: your-account-manage-posts.php"); exit; }			
	$reply = mysqli_fetch_assoc($result); 
	
	// Exit if hash does not validates
  	if ($reply_hash != md5($reply['reply_id'].$reply['from_user_id'])) {
      header("Location: your-account-manage-posts.php"); exit;
    }
	
	// Get ad details for ad which eamil to be deleted pertains to
	$query = "select *
	          from cf_".$reply['to_post_type']."
			  where ".$reply['to_post_type']."_id = ".$reply['to_ad_id'].";";
	$debug .= debugEvent("Ad details based on reply_to ID",$query);		
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$ad = mysqli_fetch_assoc($result); 
		
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your ads - Email deletion - Christian Flatshare</title>
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
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
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
	<?php print 
		// Create advert table header
		$o = '<p></p>';
        $o .= '<h1 class="mt0">Please confirm email deletion</h1>'."\n";				
		$o .= '<p class="m0" style="font-size:13px;">';
        $o .= '<strong>'.getAdTitle($ad,$reply['to_post_type'], TRUE, FALSE, TRUE).'</strong>'."\n";			
				$o .= '<p>'."\n";
	    $o .= '<table width="570" border="0" cellpadding="4" cellspacing="0" class="greyTable">'."\n";
	    $o .= '<tr>'."\n";
 	    $o .= '<th align="left">Reply to this advert</th>'."\n";
        $o .= '</tr>'."\n";
  		    $o .= '<tr class="'.$class.'">'."\n";
	        $o .= '<td style="padding-left:10px;padding-right:5px;">'."\n";
			$o .= '<strong>From </strong>'.$reply['name'].'<br />'."\n";	
			$o .= '<strong>Email </strong><a href="mailto:'.$reply['email_address'].'?subject=Re: '.getAdTitle($ad,$reply['to_post_type'], FALSE).'">'.$reply['email_address'].'</a><br />'."\n";					
			$o .= '<strong>Sent </strong>'.$reply['reply_date'].'<br />'."\n";					
            $o .= '<br />'."\n";					
            $o .= '<strong>Their message to you:</strong>'.'<br />'."\n";		
            $o .= stripslashes($reply['message']).'<br />'.'<br />'."\n";	
            $o .= '<strong>Adverts by '.$reply['first_name'].' currently showing on Christian Flatshare:</strong><br />'."\n";	
			$adsSummary = createSummaryForAllAds($reply['from_user_id'], FALSE);
			if (!$adsSummary) { 
			  $o .= 'No adverts showing.'.'<br />'."\n";
			  } else {
              $o .= $adsSummary; }
	        $o .= '</td>'."\n";						
		    $o .= '</tr>'."\n";
		    $o .= '</table>'."\n";			
		?>
		<form name="deletion" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
		<input type="hidden" name="reply_id" value="<?php print $reply_id?>" />
		
		<?php print $o?>
		
		<br /><br />
		<p class="mb0 mt0">
		  Permanently delete this email?
		<p >
			<input type="submit" name="delete" value="Delete email" style="width:80px;" />
			&nbsp;
			<input type="submit" name="cancel" value="Cancel" style="width:50px;" />
		</p>
		</form>
		<p class="mb0"><a href="your-account.php">Back to Your Ads</a></p>
		</div>
				<div class="cs" style="width:20px; height:270px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
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
