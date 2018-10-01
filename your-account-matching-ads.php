<?php

// Autoloader
require_once 'web/global.php';

connectToDB();

	$remove_msg = "";

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }

	// If we're temporarily suspending, unsuspending an ad, or to republish 
	switch($action) {
	
		case "remove":
			$query = "update cf_saved_ads set active = '0' where post_type = '".$post_type."' and user_id = '".$_SESSION['u_id']."' and ad_id = '".$id."'";

			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				$remove_msg = '<p class="green"><strong>The advert was removed from your saved ads</strong></p>';
			}
			break;
	
	}

	
	
	function createDisplayEmails($ad,$post_type,$class) {
    // Create advert table header
	$o = '<table width="100%" border="0" cellpadding="4" cellspacing="0" class="greyTable">'."\n";
	$o .= '<tr>'."\n";
 	$o .= '<th align="left">Replies to this advert</th>'."\n";
    $o .= '</tr>'."\n";

	// EMAIL REPLIES
	$query = "
			select DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
			       e.message,
				   CONCAT_WS(' ', u_from.first_name,u_from.surname) as `name`,
				   u_from.first_name as `first_name`,				   
				   u_from.email_address as `email_address`,
				   reply_id, 
				   u_from.user_id as `from_user_id`
			from cf_email_replies `e`, 
				 cf_users as `u_from`
			where e.to_user_id = '".$_SESSION['u_id']."'
			and   e.to_post_type = '".$post_type."'
			and   e.to_ad_id   = '".$ad[$post_type.'_id']."'
			and   u_from.user_id = e.from_user_id
			and   e.deleted = 0
			and   u_from.suppressed_replies = 0
			order by e.reply_date desc;	
		";
 	  
	  $class = "odd";
		$debug .= debugEvent("Email replies query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
  		  while($reply = mysqli_fetch_assoc($result)) {
		    $hash = md5($reply['reply_id'].$reply['from_user_id']);
  		    $o .= '<tr class="'.$class.'">'."\n";
	        $o .= '<td style="padding-left:10px;padding-right:5px;padding-top:10px;padding-bottom:15px;">'."\n";
			
			
// 	  $o .= '<a href="your-account-email-control.php?reply_id='.$reply['reply_id'].'&reply_hash='.$hash.'&class='.$class.'&action=delete" style="float:right;">Delete this messege</a>';							
			$o .= '<strong>From </strong>'.$reply['name'].'<br />'."\n";	

			$o .= '<strong>Email </strong><a href="mailto:'.$reply['email_address'].'?subject=Re: '.getAdTitle($ad,$post_type, FALSE).'">'.$reply['email_address'].'</a><br />'."\n";					
			$o .= '<strong>Sent </strong>'.$reply['reply_date'].'<br />'."\n";					
			
            $o .= '<br />'."\n";					
            $o .= '<strong>Their message to you:</strong>'.'<br />'."\n";		
            $o .= nl2br(clickable_link(stripslashes($reply['message']))).'<br />'.'<br />'."\n";	
							
            $o .= '<strong>Adverts by '.$reply['first_name'].' currently showing on Christian Flatshare:</strong><br />'."\n";	
			$adsSummary = createSummaryForAllAds($reply['from_user_id'], FALSE);
			if (!$adsSummary) { 
			  $o .= 'No adverts showing.'.'<br />'."\n";
			  } else {
              $o .= $adsSummary; }
	        $o .= '</td>'."\n";						
		    $o .= '</tr>'."\n";
			
		    $class = ($class == "even")? "odd":"even";
		} // WHILE email loop end
	} else {
		  $class = "even";
  		    $o .= '<tr class="'.$class.'">'."\n";
	        $o .= '<td style="padding-left:10px;padding-right:5px;">'."\n";
			$o .= 'Replies to your ads are sent directly to your registered email account.'.'<br />'."\n";					 
			$o .= 'Ad replies are also displayed here for your convenience.'.'<br /><br />'."\n";					
			$o .= 'There are no replies to this ad yet.'."\n";					
		    $o .= '</td>'."\n";
		    $o .= '</tr>'."\n";
		    $class = ($class == "even")? "odd":"even";
	} // IF results



  	   $o .= '</table> '."\n"; // email reply table
	   $o .= '<p></p>';				
		
		return $o;
				
	}  // createDispalyEmails
	
	
	
	// If another page has called this page and we need to report on the result of an action:
	if (isset($_REQUEST['report'])) {
		switch($_REQUEST['report']) {
			case "deletionSuccess":
				$msg = '<p class="success">Your ad has been deleted successfully</p>';
				break;
			case "deletionSuccessThankyou":
				$msg = '<p class="success">Your ad has been deleted successfully<br />';
				$msg .= 'Thank you for giving feedback about Christian Flatshare</p>';
				break;				
			case "deletionFailure":
				$msg = '<p class="error">An error occured when deleting ad. Please contact '.TECH_EMAIL.'</p>';
				break;	
			case "updateSuccess":
				$msg = '<p class="success">Your ad has been updated successfully</p>';
				break;
			case "updateFailure":
				$msg = '<p class="error">An error occured when updating your ad. Please contact '.TECH_EMAIL.'</p>';
				break;				
		}
	}
	
	// Load all the "Saved ads" for this user
	$s = "";
	
	// 1. Offered ads
	$query = "
		select
			o.offered_id,
			o.bedrooms_available,
			o.bedrooms_total,
			o.accommodation_type,
			o.room_share,
			o.building_type,
			o.street_name,
			o.town_chosen,
			o.suspended,
			j.town,
			o.postcode,
			DATE_FORMAT(s.date_saved,'%d %b, %Y at %H:%i') as `date_saved`,
			'LIVE_AD' as `status`,
			s.ad_id as `ad_id`
		from cf_saved_ads as `s`, cf_offered as `o`, cf_jibble_postcodes as `j`
		where s.post_type = 'offered'
		and o.offered_id = s.ad_id
		and j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
		and s.user_id = '".$_SESSION['u_id']."'
		and s.active = '1'
		UNION
		select
			o.offered_id,
			o.bedrooms_available,
			o.bedrooms_total,
			o.accommodation_type,
			o.room_share,
			o.building_type,
			o.street_name,
			o.town_chosen,
			o.suspended,
			j.town,
			o.postcode,
			DATE_FORMAT(s.date_saved,'%d %b, %Y at %H:%i') as `date_saved`,
			'DELETED_AD' as `status`,
			s.ad_id as `ad_id`
		from cf_saved_ads as `s`, cf_offered_archive as `o`, cf_jibble_postcodes as `j`
		where s.post_type = 'offered'
		and o.offered_id = s.ad_id
		and j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
		and s.user_id = '".$_SESSION['u_id']."'
		and s.active = '1'		
	";
    $class = "odd";		
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$debug .= $query;
	if (mysqli_num_rows($result)) {
		while($ad = mysqli_fetch_assoc($result)) {
			$s .= '<tr class="'.$class.'">'."\n";
			$s .= '<td>';
			$s .= '<strong>'.getAdTitle($ad,"offered").'</strong><br />';

			$s .= '<span class="grey f10">Saved '.$ad['date_saved'].'</span>';
						
            $reply_query = "SELECT DATE_FORMAT(r.reply_date,'%d %b, %Y at %H:%i') as `reply_date` 
			         FROM cf_email_replies r
					 WHERE r.from_user_id = ".$_SESSION['u_id']."
					 AND   r.to_ad_id = ".$ad['offered_id']."
					 AND   r.to_post_type = 'offered'
					 ";
            $reply_result = mysqli_query($GLOBALS['mysql_conn'], $reply_query);					 
			if (mysqli_num_rows($result)) {
  		      while($reply = mysqli_fetch_assoc($reply_result)) {
			  $s .= '<br /><span class="grey f10">You replied on '.$reply['reply_date'].'</span>';
			  }
			}
			
			if ($ad['status'] == 'DELETED_AD') {
				$s .= '<br /><span class="red"><strong>this advert has been deleted</strong></span>';
			} else if ($ad['suspended']) {
				$s .= '<br /></span><span class="suspended">this ad has been suspended</span>';
			}			
			
						
			$s .= '</td>';
			$s .= '<td align="right" valign="top"><a href="'.$_SERVER['PHP_SELF'].'?action=remove&post_type=offered&id='.$ad['offered_id'].'">Remove</a></td>';				
			$s .= '</tr>';

			$class = ($class == "even")? "odd":"even";				
						
		}
	}
	
	// 2. Wanted ads
	$query = "
		select
			s.ad_id,
			w.wanted_id,
			w.bedrooms_required,
			w.distance_from_postcode,
			w.location,
			w.postcode,
			w.suspended,
			DATE_FORMAT(s.date_saved,'%d %b, %Y at %H:%i') as `date_saved`,
			'LIVE_AD' as `status`
		from cf_saved_ads as `s`,cf_wanted as `w` 
		where s.post_type = 'wanted' 
		and w.wanted_id = s.ad_id
		and s.user_id = '".$_SESSION['u_id']."' 
		and s.active = '1'
		
		UNION
		
		select
			s.ad_id,
			w.wanted_id,
			w.bedrooms_required,
			w.distance_from_postcode,
			w.location,
			w.postcode,
			w.suspended,
			DATE_FORMAT(s.date_saved,'%d %b, %Y at %H:%i') as `date_saved`,
			'DELETED_AD' as `status`
		from cf_saved_ads as `s`,cf_wanted_archive as `w` 
		where s.post_type = 'wanted'
		and w.wanted_id = s.ad_id 
		and s.user_id = '".$_SESSION['u_id']."'
		and s.active = '1'		
	";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (mysqli_num_rows($result)) {
		while($ad = mysqli_fetch_assoc($result)) {
			$s .= '<tr class="'.$class.'">'."\n";
			$s .= '<td>';
			$s .= '<strong>'.getAdTitle($ad,"wanted").'</strong><br />';
							
		  $s .= '<span class="grey f10">Saved '.$ad['date_saved'].'</span>';	
						
          $reply_query = "SELECT DATE_FORMAT(r.reply_date,'%d %b, %Y at %H:%i') as `reply_date` 
			         FROM cf_email_replies r
					 WHERE r.from_user_id = ".$_SESSION['u_id']."
					 AND   r.to_ad_id = ".$ad['wanted_id']."
					 AND   r.to_post_type = 'wanted'
					 ";
            $reply_result = mysqli_query($GLOBALS['mysql_conn'], $reply_query);					 
			if (mysqli_num_rows($result)) {
  		      while($reply = mysqli_fetch_assoc($reply_result)) {
			  $s .= '<br /><span class="grey f10">You replied on '.$reply['reply_date'].'</span>';
			  }
			}		
			
			if ($ad['status'] == 'DELETED_AD') {
				$s .= '<br /></span><span class="red"><strong>this advert has been deleted</strong></span>';
			} else if ($ad['suspended']) {
			    $s .= '<br /></span><span class="suspended">this advert has been suspended</span>';
			}			
				
			$s .= '</td>';
			$s .= '<td align="right" valign="top"><a href="'.$_SERVER['PHP_SELF'].'?action=remove&post_type=wanted&id='.$ad['wanted_id'].'">Remove</a></td>';				
			$s .= '</tr>';
							
			$class = ($class == "even")? "odd":"even";				
		}
	}

	// Set the flag for the weclome message to TRUE
	if (!$s) {
	    $welcome_msg = TRUE;		
		$s .= '<p>You have no saved adverts.</p>';
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
<title>Your matching ads - Christian Flatshare</title>
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
<script language="javascript" type="text/javascript" src="includes/mootools-release-1.11.js"></script>
<script language="javascript" type="text/javascript" src="includes/moodalbox/moodalbox.js"></script>
<link href="includes/moodalbox/moodalbox.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript">

	function hideMessage() {
	
		$('new_ad').style.display = "none";
		$('new_ad_close_button').style.display = "none"; 
	
	}

</script>
<style type="text/css">
<!--
.style1 {color: #000000}
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
			<h1 class="mt0">Your matching ads</h1>
			<div class="clear"><!----></div>
			<?php print $msg?>
			<?php print $remove_msg?>
			<?php print $s?>			
			<form name="updateForm" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
			</form>
			<?php if ($welcome_msg) { ?>
		  <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
				<h2 class="mt0">Your saved ads</h2>
	    <p class="mb0" align="justify">When browsing adverts on Christian Flatshare you can choose to mark the ads of interest to you using the &quot;<strong>Hide/save ads</strong>&quot; button, shown in the top-right corner of ads. Clicking this button once will mark the ad as &quot;hidden&quot;, which prevents it appearing again in your search results. Clicking the button a second time marks the advert as &quot;saved&quot; and it wil be shown on this page.
	    </p>
	    
		  </div>
		    <?php } ?>				
		  </p>
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
