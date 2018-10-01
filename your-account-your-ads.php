<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

	$remove_msg = "";
	
	if (isset($_GET['new_ad'])) { $new_ad = $_GET['new_ad']; } else { $new_ad = NULL; }
	if (isset($_GET['id'])) { $id = $_GET['id']; } else { $id = NULL; }
	if (isset($_GET['post_type'])) { $post_type = $_GET['post_type']; } else { $post_type = NULL; }
	if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }
	
	// If action == "keep_ad" or action == "remove_ad" we're dealing with a link from an email
	// and we will be affecting a user login
	if ($action == "keep_ad" || $action == "remove_ad") {
		// Ensure we have a valid ad, get user_id & email and do the hash check.
		$query = "select user_id from cf_".$post_type." where ".$post_type."_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		// Continue, only if we have a valid return
		if (mysqli_num_rows($result)) {
			die($query);			
		}
	
	}
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }

	
	
	// If we're changing the availability time for an ad	
	if ($_POST) {
		/*
			$_POST data looks like:
			[available_date_offered_1616] => 2007-03-10
			[available_date_offered_1620] => 2007-03-10
			[available_date_offered_1622] => 2007-03-10
			[available_date_wanted_13] => 2007-03-10
			[available_date_wanted_14] => 2007-03-10
		*/
		foreach($_POST as $key => $value) {
		
			if ($value) {
				preg_match('/^available_date_(offered|wanted)_(\d*)$/',$key,$matches);
				$query = "
					update 
						cf_".$matches[1]." 
					set 
						available_date = '".$value."',
						expiry_date = date_add('".$value."',interval 10 day)
					where
						".$matches[1]."_id = '".$matches[2]."';
				";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die($query);
			}
		
		}
	}
	
	// If this page has been called after the addition of a new ad in the database
	if ($new_ad && $id && $post_type) {
	
		// Step 1: Notify the user
		$result = notifyUser($id,$post_type, $twig);
	
	}
	
	$offered_ads = "";
	$wanted_ads = "";
	

	
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
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$debug .= $query;
	if (mysqli_num_rows($result)) {
		while($ad = mysqli_fetch_assoc($result)) {
			$s .= '<tr>';
			$s .= '<td>';
			$s .= '<strong>'.getAdTitle($ad,"offered").'</strong><br/>';
			$s .= '<span class="grey f10">Saved '.$ad['date_saved'].'</span>';
			if ($ad['status'] == 'DELETED_AD') {
				$s .= '<span class="red"> advert now deleted</span>';
			} else if ($ad['suspended']) {
				$s .= '<span class="suspended"> advert suspended</span>';
			}			
      /*    $reply_query = "SELECT DATE_FORMAT(r.reply_date,'%d %b, %Y at %H:%i') as `reply_date` 
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
	  */
			$s .= '</td>';
			$s .= '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=remove&post_type=offered&id='.$ad['ad_id'].'">Remove</a></td>';
			$s .= '</tr>';
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
			$s .= '<tr>';
			$s .= '<td>';
			$s .= '<strong>'.getAdTitle($ad,"wanted").'</strong><br/>';
			$s .= '<span class="grey f10">Saved on '.$ad['date_saved'].'</span>';
			if ($ad['status'] == 'DELETED_AD') {
				$s .= '<strong class="red"> advert deleted</strong><br/>';
			} else if ($ad['suspended']) {
				$s .= '<span class="suspended"> advert suspended</span><br/>';
			}			
        /*  $reply_query = "SELECT DATE_FORMAT(r.reply_date,'%d %b, %Y at %H:%i') as `reply_date` 
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
		*/
			$s .= '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=remove&post_type=wanted&id='.$ad['wanted_id'].'">Remove</a></td>';
			$s .= '</tr>';
		}
	}

		
	if (!$s) {
		$s .= '<p>You have no saved adverts</p>';

	} else {
		$welcome_msg = FALSE;	
		$temp .= '<table cellpadding="0" cellspacing="0" class="defaultTable" width="100%">';
		$temp .= '<tr>';
		$temp .= '<th>Saved adverts</th>';
		$temp .= '<th>Remove</th>';
		$temp .= '</tr>';
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
<title>Your ads - Manage your ads - Christian Flatshare</title>
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
			<h1 class="mt0">Your ads and replies</h1>
			<div class="clear"><!----></div>
			<?php print $msg?>
			<?php if ($new_ad) { ?>
			<script language="javascript" type="text/javascript">
			
				window.addEvent('load', function(){
				
					if (MOOdalBox) {
						MOOdalBox.open("details-inline.php?id=<?php print $id?>&post_type=<?php print $post_type?>",'','moodalbox 920');
					}
				
				});
			
			</script>			
			<?php /*
			<div id="new_ad_close_button"><a href="#" onclick="return hideMessage();"><img src="images/title-ad-created-succesfully-close-button.gif" width="16" height="16" border="0" /></a></div>
		  <div id="new_ad">				
				<p class="mt0"><img src="images/title-ad-created-succesfully.gif" alt="Ad was created successfully" width="227" height="29" /></p>
			<p><span class="style3">
			    Your 
				  <?php print $post_type?> 
  accommodation advert has been added to the Christian Flatshare database,<br />
			  and an email with a link to your ad has been sent to you for confirmation. </span></p>
				<p class="style4">See below to <strong>add photos</strong>, and <strong>view, edit </strong>or <strong>delete </strong>your ads</p>
				<p class="style4"><span class="style6">Facebook</span>- see below to &quot;Facebook your ad&quot;				</p>
				<p class="style3"><strong>Sharing CFS with your friends and your church leadership<br />
			    will help CFS, your advert and other people's</strong></p>
			  <p align="center" class="mb0 style3">			  CFS is a non-profit  organisation dedicated to finding homes, growing churches and building communities.<br />
<!--			      <strong>Thank you for using Christian Flatshare</strong><br /> -->
			    <br />
			  </p>
		  </div>			
		  */ ?>
		  
		  			
			<?php } ?>
			<?php print $remove_msg?>
			<?php print $s?>			
			<form name="updateForm" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
			
						
			<?php if ($offered_ads) { 
				$welcome_msg = FALSE;				
			?>
			<h2>Offered adverts</h2>
				<?php
				
					$class = "odd";
					foreach($offered_ads as $id => $ad) {
						echo createLargeSummary($ad,"offered",$class);
						echo createDisplayEmails($ad,"offered",$class);
						$class = ($class == "even")? "odd":"even";
					}
				
				?>
		<!-- </table> -->
			<?php } else { ?>
			<p>You have no offered accommodation adverts</p>
			<?php } ?>
			
			
			<?php if ($wanted_ads) { 
			  $welcome_msg = FALSE;				
			?>
			<h2>Wanted adverts</h2>
				<?php
				
					$class = "odd";
					foreach($wanted_ads as $id => $ad) {
						echo createLargeSummary($ad,"wanted",$class);
						echo createDisplayEmails($ad,"wanted",$class);						
						$class = ($class == "even")? "odd":"even";
					}
				
				?>
			</table>
			<?php } else { ?>
			<p>You have no wanted accommodation adverts<br />
			  <br /></p>
			<?php } ?>
			
			</form>
			<?php if ($welcome_msg) { ?>
		  <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
				<h2 class="mt0">Welcome to Christian Flatshare... </h2>
	    <p>Christian Flatshare is a non-profit organisation, helping accommodation seekers to connect with the local church. <br />
				  <br />
	        Taking care to create helpful and informative adverts will help you to get the best response from Christian Flatshare, and so will will sharing Christian Flatshare with your friends and with your church leadership. </p>
	    <p>Sharing Christian Flatshare will help others to connect with the local church. On behalf on the thousands who have used Christian Flatshare, we are grateful to the many that have taken initiatives and help communicate Christian Flatshare to their own church fellowship. </p>
	    Please enjoy Christian Flatshare.
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
