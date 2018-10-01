<?php

use CFS\International\CFSInternational;

// Autoloader
require_once 'web/global.php';
require_once 'includes/urlLinker.php';

require('includes/class.randompass.php');	// Random password generator class

	// In order for this page to work, the id & type must be provided
	if (isset($_REQUEST['post_type'])) { $post_type = $_REQUEST['post_type']; } 
	if (isset($_REQUEST['ad_type'])) { $post_type = $_REQUEST['ad_type']; } 	// backward compatibility; change of ad_type to post_Type
	if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; } //else { header("Location:index.php"); exit; }
	if (isset($_REQUEST['wid'])) { $wid = $_REQUEST['wid']; } //else { header("Location:index.php"); exit; }
	if (isset($_REQUEST['action'])) { $action = $_REQUEST['action']; } //else { header("Location:index.php"); exit; }

	if (isset($_POST['cancel'])) { header("Location:your-account-manage-posts.php"); exit; }	
	if (isset($_POST['accommodation_description'])) { $accommodation_description = $_POST['accommodation_description']; } else { $accommodation_description = NULL; }	
	if (isset($_POST['household_description'])) { $household_description = $_POST['household_description']; } else { $household_description = NULL; }		
	if (isset($_POST['accommodation_situation'])) { $accommodation_situation = $_POST['accommodation_situation']; } else { $accommodation_situation = NULL; }			
	
	if (isset($_POST['id'])) { $id = $_POST['id']; } 
	if (isset($_POST['post_type'])) { $post_type = $_POST['post_type']; } 
	
	$open_photo_after_load = NULL;
						
	// If we're removing the palup choice
	switch($action) {
			case "remove":
			$query = "delete from cf_palups where offered_id = '".$id."' and wanted_id = '".$wid."' and user_id = '".$_SESSION['u_id']."'";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0) {
				$remove_msg = '<p class="mt0 green"><strong>Your wanted ad has been remvoed from the pal-up list.</strong></p>';
			}
		break;			
			case "add_palup":
			if ($_SESSION['u_id']) {
 			 $query = "insert into cf_palups (user_id, offered_id, wanted_id, last_updated_date, palup_date, active)
			           select user_id,".$id.",wanted_id, curdate(), curdate(), 1
								 from cf_wanted
								 where user_id = ".$_SESSION['u_id']."
								 and published = 1 
								 and suspended = 0
								 and expiry_date >= now()
								 and wanted_id not in (select wanted_id from cf_palups 
							                               where user_id = ".$_SESSION['u_id']." 
							                               and offered_id = ".$id.")
                                                                 and  (country = '" . $userCountry['iso'] . "' or country = '')";
  		  $debug = debugEvent("Palup insert:",$query);								 
      	mysqli_query($GLOBALS['mysql_conn'], $query);	
				if (mysqli_affected_rows($GLOBALS['mysql_conn']) > 1) {
				 $remove_msg = '<p class="mt0 green"><strong>Your wanted ads have been added to the pal-up list.</strong></p>';
				} elseif (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0) {
				 $remove_msg = '<p class="mt0 green"><strong>Your wanted ad has been added to the pal-up list.</strong></p>';				 
          	  	}
		break;
			}
		}
	
/*			
	if ($_POST)	{
	 	if ($post_type == "wanted") {
			$query = "UPDATE cf_wanted SET accommodation_situation = '".$accommodation_situation."'
				 				WHERE wanted_id = '".$id."'
								AND   user_id = '".$_SESSION['u_id']."'
								";
		} else {
			$query = "UPDATE cf_offered 
								SET accommodation_description = '".$accommodation_description."',
										household_description = '".$household_description."'
						 		WHERE offered_id = '".$id."'
								AND   user_id = '".$_SESSION['u_id']."'
								";
		}			
		
		$debug .= debugEvent("UPDATE query",$query);		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if ($result) {
				header("Location: your-account-manage-posts.php?report=updateSuccess");
			} else {
				header("Location: your-account-manage-posts.php?report=updateFailure");
			}			
	} // if $_POST
*/	
	
/*	// First of all, check for ownership of the ad
	if ($edit_mode == "edit") {
		$query = "select count(*) from cf_".$post_type." where user_id = '".$_SESSION['u_id']."' and ".$post_type."_id = '".$id."';";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$check = cfs_mysqli_result($result,0,0);
		if (!$check) {
			header("Location: your-account-manage-posts.php"); exit;
		}					
  } // ownership check	
*/
	
	if ($post_type == "offered") {
		$query  = "
			select 
			  o.*,
			 (CASE IFNULL(o.town_chosen,'')
				WHEN '' THEN j.town 
				ELSE o.town_chosen
				END) as town,			 
			(CASE available_date > SYSDATE()
			   WHEN true THEN DATE_FORMAT(o.available_date,'%d %M %Y')
			   ELSE 'Today'
			 END) as `available_date_formatted`,
			DATEDIFF(curdate(),created_date) as `ad_age`, 
			(SELECT DATEDIFF(curdate(),last_login)  from cf_users 
		 	 WHERE cf_users.user_id = o.user_id) as `last_login_days`,
			(SELECT suppressed_replies from cf_users  WHERE cf_users.user_id = o.user_id) as `scam_ad`,
			 	(SELECT CONCAT(first_name,' ', surname) from cf_users WHERE cf_users.user_id = o.user_id) as `scam_name`
		";
		if (isset($_SESSION['u_id'])) { $query .= ", s.active as `active` "; } // Saved ad status
		$query .= "
			from cf_offered as `o` 
			left join cf_jibble_postcodes as `j` on SUBSTRING_INDEX(o.postcode,' ',1) = j.postcode 
		";
		if (isset($_SESSION['u_id'])) {
			$query .= "
				left join cf_saved_ads as `s` 
				on s.ad_id = o.offered_id and 
				s.post_type = 'offered' and 
				s.user_id = '".$_SESSION['u_id']."' 
			";
		}
		$query .= "
			where o.offered_id = '".$id."'
		";
		$debug = debugEvent("Details for offered ad query:",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		$propertyDescription = getPropertyDescription($ad['accommodation_type'],$ad['building_type']);
	}
	
	if ($post_type == "wanted") {
		$query = "
			select *, 
			DATE_FORMAT(available_date,'%d %b %Y') as `available_date_formatted`, 
			(CASE available_date > SYSDATE()
			   WHEN true THEN DATE_FORMAT(available_date,'%d %M %Y')
			   ELSE 'Today'
			 END) as `available_date_formatted`,
			DATEDIFF(curdate(),created_date) as `ad_age`,
			(select DATEDIFF(curdate(),last_login)  from cf_users where cf_users.user_id = w.user_id) as `last_login_days`,
  		(SELECT suppressed_replies from cf_users WHERE cf_users.user_id = w.user_id) as `scam_ad`,
 			(SELECT CONCAT(first_name,' ', surname) from cf_users WHERE cf_users.user_id = w.user_id) as `scam_name`
		";
		if (isset($_SESSION['u_id'])) { $query .= ", s.active as `active` "; } // Saved ad status
		$query .= "		
			from cf_wanted  as `w`
		";
		if (isset($_SESSION['u_id'])) {
			$query .= "
				left join cf_saved_ads as `s` 
				on s.ad_id = wanted_id and 
				s.post_type = 'wanted' and 
				s.user_id = '".$_SESSION['u_id']."' 
			";
		}
		$query .= "where wanted_id = '".$id."'";
		$debug = debugEvent("Details for wanted ad query:",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
	}
    
    // Setup app country
    $CFSIntl->setAppCountry($ad['country']);
    $appCountry = $CFSIntl->getAppCountry();
    $fb_photo = NULL;
	$query = "select * from cf_photos where ad_id = '".$id."' and post_type = '".$post_type."' order by photo_sort asc;";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$num_photos = mysqli_num_rows($result); // This is used to determine the height of the Google ad strip
	if (!mysqli_num_rows($result)) {
		$photos .= '<p align="center" class="mt10 mb0"><img src="images/icon-polaroids.gif" alt="Photos for this ad" /></p>'."\n";
		$photos .= '<p class="grey" align="center">'.(isset($_SESSION['u_id'])?stripslashes($ad['contact_name']):"the advert's owner").' has not uploaded photos yet</p>';
	} else {
		//$photos = '<p>Click on any thumbnail for a larger image:</p>';
		$photos = '';
		while($row = mysqli_fetch_assoc($result)) {
			// The image must have a max height of 90px and must fit on a 120 * 90 area
			list($w,$h) = getImgRatio("images/photos/".$row['photo_filename'],"",90,120,90);
			$photos .= '<div class="photoContainer mb10">';
			// AUto-open a lightbox photo if user clicked on a small thumbnail on display.php
			if ($_GET['photo_id'] == $row['photo_id']) {
				$open_photo_after_load = '	Slimbox.open("images/photos/'.$row['photo_filename'].'","'.($row['caption']? $row['caption']:' ').'");';
			}
			$photos .= '<a href="/images/photos/'.$row['photo_filename'].'" rel="lightbox[gallery]" title="'.($row['caption']? $row['caption']:' ').'">';
            if ($fb_photo === NULL) {
                //$fb_photo = 'http://www.christianflatshare.org/images/photos/image-2118959753513678128b085.JPG';
                $fb_photo = 'http://' . $_SERVER['SERVER_NAME'] . '/images/photos/' . $row['photo_filename'];
            }
            
			$photos .= '<img src="thumbnailer.php?img=images/photos/'.$row['photo_filename'].'&w='.$w.'&h='.$h.'" border="0"/>';
			$photos .= '</a>';
			// The caption
			if (trim($row['caption'])) {
				$photos .= '<div>'.$row['caption'].'</div>';
			}
			$photos .= '</div>'."\n";
		}
	}	

   	function createDisplayEmails($ad,$post_type) {	
    // Create ad reply summaries	
	$query = "
			SELECT DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
                               e.reply_date as  `reply_date_for_order`,
			       e.message,
				     reply_id,
				     first_name,
				     from_user_id
			FROM cf_email_replies as `e`, 
				   cf_users as `u_from`,
				   cf_".$post_type." as `ad`
			WHERE ((e.from_user_id = '".$_SESSION['u_id']."'
					    and e.from_user_id != ad.user_id						
						 )
						or
						 (e.from_user_id = '".$_SESSION['u_id']."'
					 	  and e.to_user_id = '".$_SESSION['u_id']."'
						  and e.from_user_id = ad.user_id
						))
			AND   ad.".$post_type."_id = e.to_ad_id
			AND   e.to_post_type = '".$post_type."'
			AND   e.to_ad_id   = '".$ad[$post_type.'_id']."'
			AND   u_from.user_id = e.from_user_id
			AND   e.sender_deleted = 0			
      UNION ALL
      SELECT DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
                               e.reply_date as  `reply_date_for_order`,
			       e.message,
				     reply_id,
				     first_name,
				     from_user_id
			FROM  cf_email_replies as `e`, 
				    cf_users as `u_from`,
				    cf_".$post_type."_archive as `ad`
			WHERE ((e.from_user_id = '".$_SESSION['u_id']."'
					    and e.from_user_id != ad.user_id						
						 )
						or
						 (e.from_user_id = '".$_SESSION['u_id']."'
					 	  and e.to_user_id = '".$_SESSION['u_id']."'
						  and e.from_user_id = ad.user_id
						))
			AND   ad.".$post_type."_id = e.to_ad_id
			AND   e.to_post_type = '".$post_type."'
			AND   e.to_ad_id   = '".$ad[$post_type.'_id']."'
			AND   u_from.user_id = e.from_user_id
			AND   e.sender_deleted = 0			
      ORDER BY reply_date_for_order DESC 
		";
		
		$debug .= debugEvent("Email replies query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
 		  $o .= 'Your replies are visible only to you and are shown to help you track the adverts you reply to.<br /><br />'."\n";										
											

  		  while($reply = mysqli_fetch_assoc($result)) {
  		    $o .= '<tr class="even">'."\n";
	        $o .= '<td style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:10px;">'."\n";
					$o .= '<strong>Sent: </strong>'.$reply['reply_date'].'<br />'."\n";					
					
					
          $o .= '<strong>Your message to '.stripslashes(trim($ad['contact_name'])).':</strong>'.'<br />'."\n";		
          $o .= '<p class="mt5">'.nl2br(htmlEscapeAndLinkUrls($reply['message'])).'</p>'."\n";								

			
          $o .= '<strong>Adverts by '.trim($reply['first_name']).' currently showing on Christian Flatshare:</strong><br />'."\n";	
			$adsSummary = createSummaryForAllAds($reply['from_user_id'], FALSE);
			if (!$adsSummary) {  
			  $o .= 'No adverts showing.'.'<br />'."\n";
		    } else {
              $o .= '<strong>'.$adsSummary.'</strong>'; 
			}		
	        $o .= '</td>'."\n";
  		    $o .= '<tr class="odd" style="height:10px;">'."\n";									
	        $o .= '<td></td>'."\n";			
		    $o .= '</tr>'."\n";
		} // WHILE email loop end
	} else { 
	    if (!$_SESSION['u_id']) {
		  // Not logged in 
		  $class = "odd";
  		  $o .= '<tr class="'.$class.'">'."\n";
	      $o .= '<td style="padding-left:0px;padding-right:5px;padding-top:0px;padding-bottom:5px;">'."\n";
		  	$o .= 'Login to see your replies to this ad shown here.<br />'."\n";					
 		    $o .= 'Your replies are visible only to you and are shown here to help you track the adverts you respond to.'.'<br />'."\n";			
		    $o .= '</td>'."\n";
		    $o .= '</tr>'."\n";
		    $class = ($class == "even")? "odd":"even";		  
		  
		  } else {
		  $class = "odd";
  		  $o .= '<tr class="'.$class.'">'."\n";
	      $o .= '<td style="padding-left:0px;padding-right:5px;padding-top:0px;padding-bottom:5px;">'."\n";
			  $o .= 'You have not replied to this ad.'.'<br />'."\n";					
 	      $o .= 'Your replies are visible only to you and are shown here to help you track the adverts you respond to.'.'<br />'."\n";						
		    $o .= '</td>'."\n";
		    $o .= '</tr>'."\n";
		    $class = ($class == "even")? "odd":"even";
		  }
	} // IF results
	
	return $o;
    } // createDisplayEmails
	
	
	 function getMaxReplyId($post_type, $id) {
			// sent_thread is used on the Details page, to show the message thread from the SENT MESSAGES list
	    $reply_id_query = 'SELECT MAX(reply_id)
											 	 FROM cf_email_replies 
												 WHERE to_post_type = "'.$post_type.'"
												 AND   to_ad_id   = "'.$id.'"													 
												 AND   to_user_id = "'.$_SESSION['u_id'].'" ';
			$reply_id_reult = mysqli_query($GLOBALS['mysql_conn'], $reply_id_query);
      $reply_id_row = mysqli_fetch_row($reply_id_reult);
      $max_reply_id = $reply_id_row[0];
			return $max_reply_id;
		}

  function createDisplayPalups($offered_id) {	
    // Create createDisplayPalups
		
   $query = "
			select
			w.wanted_id,
			w.bedrooms_required,
			w.distance_from_postcode,
			w.location,
			w.postcode,
			w.suspended,
			DATE_FORMAT(p.palup_date,'%d %b, %Y') as `palup_date`,
			u.first_name, u.surname,
			p.user_id as palup_user_id
		from cf_palups as `p`,
		     cf_wanted as `w`,
				 cf_users as `u`
		where w.wanted_id = p.wanted_id
    and p.offered_id = ".$offered_id."
		and p.active = '1'
		and p.user_id = u.user_id
		and w.suspended = 0
	  and w.published = 1 
	  and w.expiry_date >= now()
		order by p.palup_date desc
	";
    $class = "odd";		
	  $result = mysqli_query($GLOBALS['mysql_conn'], $query);
	  $debug .= $query;
	  if (mysqli_num_rows($result)) {
		 while($ad = mysqli_fetch_assoc($result)) {
			$s .= '<tr class="'.$class.'">'."\n";
			$s .= '<td>';
			$s .= '<p class="mt0 mb5">'.$ad['first_name'].' '.$ad['surname'];

			if ($_SESSION['u_id']) {
			  if ($ad['palup_user_id'] == $_SESSION['u_id']) { 
				 $s .= '&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?&id='.$offered_id.'&wid='.$ad['wanted_id'].'&post_type=offered&action=remove">Remove</a>'; 
				}
			 }
		  $s .= '<br />';			 
			$s .= '<strong>'.getAdTitle($ad,"wanted",TRUE,FALSE,TRUE).'</strong>';
			$s .= '<span class="grey">&nbsp;&nbsp;'.$ad['palup_date'].'</span>';			 
		  $s .= '</p>';
		  $s .= '</td>';			
			
		/*	if ($ad['suspended']) {
				$s .= '<span style="color:#FF9900;">(this ad is suspended)</span><br />';
			}			
		*/	


	   	$s .= '</td>';
			$s .= '</tr>';
		//	$class = ($class == "even")? "odd":"even";									
	  }
	}
	return $s;
 } // createDisplayPalups
		
		
 function createPalup_Option($offered_id) {	
    // Create createPalup_Option

	if ($_SESSION['u_id']) {
   $query = "
			select wanted_id 
 	 	  from cf_wanted as `w`
		  where w.user_id = ".$_SESSION['u_id']."
		  and w.suspended = 0
                  and w.published = 1 
 	          and w.expiry_date >= now()
		  and w.wanted_id not in (select wanted_id from cf_palups where offered_id = ".$offered_id.")";
	  $result = mysqli_query($GLOBALS['mysql_conn'], $query);
	  $debug .= $query;
	  $wanted_ads_to_add = mysqli_num_rows($result);
		
   $query = "
			select wanted_id 
 	 	  from cf_wanted as `w`
		  where w.user_id = ".$_SESSION['u_id']."
		  and w.suspended = 0
      and w.published = 1 
 	    and w.expiry_date >= now()
 	  ";
	  $result = mysqli_query($GLOBALS['mysql_conn'], $query);
	  $debug .= $query;
	  $no_wanted_ads = mysqli_num_rows($result);		
	}
	
	if (!$_SESSION['u_id']) {
	 $s = '<span class="grey">(Login to add an advert here)</span>';
	} elseif ($no_wanted_ads == 1 && $wanted_ads_to_add == 0) {
	 // User posted their ad already
	 $s = '<span class="grey">(Your advert is listed)</span>';
	} elseif ($no_wanted_ads > 1 && $wanted_ads_to_add == 0) {
	 // User posted their ad already
	 $s = '<span class="grey">(Your adverts are listed)</span>';
	 	} elseif ($no_wanted_ads == 0) {
	 // User has no wanted ads live
	 $s = '<span class="grey">(Post a Wanted accommodation advert to add yours here)</span>';
	} elseif ($no_wanted_ads > 0) {
	 // User has wanted add to post
	 $s = '<a href="'.$_SERVER['PHP_SELF'].'?&id='.$offered_id.'&post_type=offered&action=add_palup">Add your wanted ad&nbsp;</a></td>'; 
	}

	return $s;
 } // createPalup_Option
 		
		
		
			
	
	// Update the time_viewed field
	$query = "update cf_".$post_type." set times_viewed = (times_viewed + 1) where ".$post_type."_id = '".$id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	$terms = getTermsArray("minimum");
		
	
	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php if ($ad) { echo strip_tags(getAdTitle($ad,$post_type))." - "; } ?>Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<script>
jQuery(document).ready(function($) {
    jQuery(".FBFriends img").tooltip({ position: { my: "center top", at: "center bottom+5" } });
});
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" -->
<script language="javascript" type="text/javascript" src="includes/save-ad-1.2.js"></script>
<script language="javascript" type="text/javascript" src="includes/slimbox-new/slimbox.js"></script>
<script language="javascript" type="text/javascript" src="scripts/share.js"></script>
<link href="includes/slimbox-new/slimbox.css" rel="stylesheet" type="text/css" />
<?php if ($open_photo_after_load) { ?>
<script language="javascript" type="text/javascript">

	window.addEvent("load",function(){
		<?php print $open_photo_after_load?>
	});

</script>
<?php } ?>


<script language="javascript" type="text/javascript">
	window.addEvent('domready',function() {
		
		//tip_pricing
		//<p><strong>Pricing</strong></p>
		var myTips = new Tips('.tooltip');
		
		if ($('what_is_palup')) {
			$('what_is_palup').store('tip:title', 'Whole Place Palup');
			$('what_is_palup').store('tip:text', 'If you might be interested in sharing this <?php echo $ad['building_type']?> you can place a Wanted accommodation<br /> advert (which will have details about you), and choose to list it here. Others who<br />could be interested to share the <?php echo $ad['building_type']?> can then contact you to explore the idea.<br /><br />You can remove your advert at any time by clicking the "<strong>remove</strong>" link which will<br /> appear next to your advert, or from the &quot;<strong>Your whole place pal-ups page</strong>&quot; <br />option on the member&#39;s menu (on the right when you login).');
		}
		if ($('contact_details')) {
			$('contact_details').store('tip:title', 'Contact Details');
			$('contact_details').store('tip:text', 'Member contact details are only shown once you are logged in.<br /><br />Logging in first is required to prevent member contact details from<br />being stored by search engines when they visit Christian Flatshare.<br /><br />Login or join (free of charge) to see all details.');
		}
		
	});
</script>

<style type="text/css">
<!--
.style1 {
	font-size: 14px;
	font-weight: bold;
}
-->
</style>
<?php
if ($fb_photo === NULL) {
    $fb_photo = 'http://'.SITE.'images/pictures/'.$ad['picture'];
}
?>

    <!-- FACEBOOK OPEN GRAPH -->
    <?php if ($post_type == 'offered'): ?>
    <meta property="fb:app_id" content="241207662692677" />
    <meta property="og:title" content="<?php print getAdTitle($ad,$post_type, FALSE); ?>" />
    <meta property="og:description" content="<?php print trim(substr($ad['accommodation_description'], 0, 160)); ?>..." />
    <meta property="og:image" content="<?php print $fb_photo; ?>" />
    
    <meta property="og:type" content="christian-flat-share:property" />
    <meta property="place:location:latitude" content="<?php print $ad['latitude']; ?>" /> 
    <meta property="place:location:longitude" content="<?php print $ad['longitude']; ?>" />
    <?php else: ?>
        <meta property="fb:app_id" content="241207662692677" />
        <meta property="og:title" content="<?php print getAdTitle($ad,$post_type, FALSE); ?>" />
        <meta property="og:description" content="<?php print trim(substr($ad['accommodation_situation'], 0, 160)); ?>..." />
        <meta property="og:image" content="<?php print $fb_photo; ?>" />
    <?php endif;?>
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
            <?php if (isset($_GET['new_ad'])):
                // Send email
                notifyUser($id,$post_type, $twig);
            ?>
            <div class="new-ad-message">
                <p>Success! Here is your new advert, need to make changes? <a href="post-<?php print $post_type; ?>.php?id=<?php print $id; ?>">Change advert</a> or <a href="your-account-manage-posts.php">All OK</a> </p>
            </div>
            <?php endif; ?>
		<!-- InstanceBeginEditable name="mainContent" -->
	 <?php if ($ad['scam_ad'] == 1) { ?>
	 				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
					<tr>
						<td>
						 <h1 class="m0"><a name="detail"></a>Offered ad details</h1></td>
						<td align="right">
							<?php 			
							// Next and previous functionality
							$debug .= debugEvent("$_SESSION variable 'result_set':",print_r($_SESSION['result_set'],true));
							echo nextPreviousAd($post_type, $id);		
							$debug .= debugEvent("Referer:",print_r($_SERVER,true)); 
							?>
						</td>
					</tr>
				</table>
	 				<?php print createSummaryV2($ad,"offered","odd mb10",FALSE,FALSE,TRUE)?>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-left:15px;padding-bottom:15px;padding-top:10px">
				 <div class="mt10" style="width:625px;background-color:#FFFFCC;padding-left:10px;padding-right:10px;padding-bottom:10px;padding-top:00px;border:1px solid #FFCC00;">
				 	<h1>This advert has been removed by Christian Flatshare</h1>
			    <p class="mt0 mb0">This advert was identified as a spam/scam advert. Its owner, "<?php print $ad['scam_name']?>", has been blocked.</p>
 		      <p class="mt10 mb10">Scam adverts are adverts posted for property that may not exist and are likely to have these characteristics:</p>
				<table>
 				 <tr>
				  <td>&nbsp;&nbsp;</td>
				  <td>
				  <li>The landlord will <b>unable to show you the flat</b> (away from town with a convoluted excuse)</li>
				   <li>The landlord will ask you to <b>send a handsome deposit</b> to hold the flat (which you will not have seen - bad idea)</li>
				  <li>The landlord will request  <b>payment by Western Union</b> (an <u>untraceable money transfer</u> service - also a bad idea)</li>
				
				  <li>The landlord will will avoid using the CFS messaging for correspondence, as they know this can be monitored </li>
				  <li>The landlord will often (not always) avoid telephone contact; they are often in a different country so will sound distant, and often us an &quot;070...&quot; phone number (which is a low-cost international internet phone number) </li></td>
				 </tr>
				</table>
			  <p class="mt10 mb20">Someone placing such an advert will gently pressure/tempt you to send a deposit - advertising great accommodation, in a great location, and saying there are many others waiting to see it - and saying they will hold it for you if you are the first to send a deposit. </p>
				<p 	class="mt0 mb10" style="font-size: 14px;font-weight: bold;padding-left:0px;">Good Landlords...</p>
			    <p class="mt0 mb10">Conversely, you should expect of landlords (and especially within the church community), that they:</p>
				  <table  style="width:100%">
 				 <tr>
				  <td>&nbsp;&nbsp;</td>
				  <td>

				  <li><b><i>Want</i> to show you the property</b>, so that you can view it and be informed before paying a deposit</li>
				  <li>To use a <b>normal bank account</b>, which provides traceability</li>
				  <li>To want to <b>produce documents and agree in writing</b> details of your arrangement</li>
          <p class="mt10 mb10">As such, the process should be transparent and straight forwards. Common sense prevails.</p>
					</td>
				 </tr>
				</table>
				</div>
				</td>
			</tr>		
			</table>
	<p class="mt10 mb20">&nbsp;&nbsp; </p>
	
	  <?php  } else if ($ad['suspended']) { ?>
             <?php print createSummaryV2($ad,$post_type,"odd mb10",TRUE,FALSE,TRUE)?>

		<h1>This advert has been suspended</h1>
		<p>This advert has been suspended by its owner.</p>
		<br /><br />
		
		<?php } else if (!$ad || $ad['published'] == 2) { ?>
		<div style="padding:60px;">
		<h1 align="center">Thank you for visiting Christian Flatshare</h1>
		<h2 align="center">Unfortunately this advert is no longer available... </h2>
		<p align="center"><a href="index.php">Return to the home page</a></p>
		</div>		
		<?php } else { ?>		
 
			<?php if ($post_type == "offered") { ?>

				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
					<tr>
						<td><?php print $remove_msg?> 
						    <h1 class="m0"><a name="detail"></a>Offered ad details</h1></td>
						<td align="right">
							<?php 			
							// Next and previous functionality
							$debug .= debugEvent("$_SESSION variable 'result_set':",print_r($_SESSION['result_set'],true));
							echo nextPreviousAd($post_type, $id);		
							$debug .= debugEvent("Referer:",print_r($_SERVER,true)); 
							?>
						</td>
					</tr>
				</table>
	
				<?php print createSummaryV2($ad,"offered","odd mb10",FALSE,FALSE,TRUE)?>
        <?php
        // Get mututal fb friends
        $mutualFriends = NULL;
        $mutualFriends = $CFSFacebook->getMutualFriends($currentUser['user_id'], $ad['user_id']);
        print $twig->render('mutualFriends.html.twig', array('friends' => $mutualFriends));
        ?>
        <br/>
				<!-- Contact name,phone number, member login and ad age-->
				<?php	
					if ($ad['last_login_days']==0) {
					$last_logged_in = 'logged in today';
					} else if ($ad['last_login_days']==1) {
					$last_logged_in = 'logged in yesterday';
					} else {
					$last_logged_in = 'last logged in '.$ad['last_login_days'].' days ago';	
					}	?>
								
				<div style="float:left; width:700px;">				

				
				<?php print $res?>
				
	<table> 
            <tr><td width=60%>
	             <p class="mt0">
		       <span class="style1">Contact: </span>
				<?php if (isset($_SESSION['u_id']))
				          { ?><span class="style1"><?php print trim(stripslashes($ad['contact_name']))?><?php print ($ad['contact_phone']?", ".$ad['contact_phone']:" ")?></span><?php }
								else 
								  { echo '</span><span class="grey">&nbsp;<a href="login.php">login</a> to see contact details </span><a href="#" class="tooltip" id="contact_details">(?)</a>'; } 
								?>
								<br /><a href="reply.php?offered_id=<?php print $id?>">Send <?php print (isset($_SESSION['u_id'])?stripslashes($ad['contact_name']):"the advert's owner")?> a message</a>
</td>
<td width=400 align="right" valign="bottom">
   <span class="grey">(<?php print (isset($_SESSION['u_id'])?stripslashes($ad['contact_name']):"advert owner")?> <?php print $last_logged_in?>)</span>

  <a title="Share property on Facebook"
        href="http://www.facebook.com/sharer.php?u=<?php print urlencode($_SERVER["SERVER_NAME"] . '/details.php?id=' . $_GET['id'] . '&post_type=' . $_GET['post_type']); ?>" target="_blank" class="fb-share">Share on Facebook</a>
</p>
</td></tr></table>
        
				<!--
				<div class="fieldSet">
					<div class="fieldSetTitle">THE ACCOMMODATION</div>
				-->
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">

					<h2 class="mt0">The Accommodation</h2>
                    <?php if ($ad['country'] == 'GB' || $ad['country'] == ''): ?> 
	                <p><strong><?php print stripslashes($ad['street_name'])?>, <?php print $ad['town']?> (<?php print getUKPostcodeFirstPart((trim($ad['$postal_code'])==""?$ad['postcode']:$ad['postal_code'])) ?>)</strong></p>
                    <?php else: ?>
                        <p><strong><?php print implode(', ', array($ad['street'], $ad['area'], $ad['region']))?></strong></p>
                    <?php endif;?>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="140">Bedrooms available:</td>
							<td width="180"><strong><?php print createBedroomSummary($ad)?></strong></td>
							<td width="140">Monthy price:</td>
							<td>
								<?php
								if ($ad['accommodation_type'] == "whole place" && $ad['room_letting'] == 1) {
									echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['price_pcm'], 'app') . ' Whole '.ucwords($ad['building_type']).' </strong>'."\n";
								} elseif ($ad['accommodation_type'] == "whole place" && $ad['room_letting'] == 0) {									
									echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['price_pcm'], 'app') . ' Whole '.ucwords($ad['building_type']).'</strong>'."\n";
								} else {
									echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['price_pcm'], 'app') . ' per bedroom</strong>'."\n";
								}	
							?>
							</td>
						</tr>
						<tr>
							<td width="140">Total number of bedrooms:</td>
							<td width="180"><strong><?php print $ad['bedrooms_total']?></strong></td>
							<td width="140">Deposit required:</td>
							<td><strong><?php print ($ad['deposit_required']) ? $CFSIntl->formatCountryCurrency($ad['deposit_required'], 'app') : 'None'; ?></strong></td>
						</tr>
						<tr>
							<td width="140">Building type:</td>
							<td width="180"><strong>
								<?php print ucwords($ad['building_type'])?>
							</strong></td>
							<td width="140">Price includes:</td>
							<td>
								
								<table border="0" cellpadding="0" cellspacing="5">
									<tr>
                                        <?php if ($ad['country'] == 'GB' || $ad['country'] == ''): ?>
										<td><?php print ($ad['incl_council_tax']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
										<td <?php print ($ad['incl_council_tax']? '':' class="grey"')?>>Council Tax</td>
                                        <?php endif; ?>
										<td><?php print ($ad['incl_utilities']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
										<td <?php print ($ad['incl_utilities']? '':' class="grey"')?>>Bills</td>
                                        <?php if ($ad['country'] != 'GB'): ?>
                                            <td></td>
                                            <td></td>
                                        <?php endif; ?>
									</tr>
								</table>									
								<!--<strong>
								<?php
	
								if (!$ad['incl_utilities'] && !$ad['incl_council_tax']) {
									echo "-";
								} else {
									if ($ad['incl_utilities']) { $t .= "utilities, "; }
									if ($ad['incl_council_tax']) { $t .= "council tax, "; }
									if ($t) { $t = substr($t,0,-2); } // Snip last space and comma
									echo $t;
								}								
								?>
								</strong>-->						</td>
						</tr>
						
						<tr>
							<td width="140">Furnishings:</td>
							<td width="180"><strong><?php print ($ad['furnished']? 'Furnished' : 'Unfurnished')?></strong></td>
			<!--				<td width="140"><?php print ($ad['accommodation_type'] == "whole place"? 'Indicative monthly bills:' : 'Indicative share of bills:')?></td> 	Bills are no longer included for Whole Places -->
							<td width="140">
<?php 

if ($ad['country'] == 'GB' || $ad['country'] == '') {
    if ($ad['offered_id'] > 5416) {	
    	// from ad 5416 ave bills is CT for whole place ads
        if ($ad['accommodation_type'] == "whole place") { 
            echo 'Council tax:';
        }
        elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 1) {
            echo 'Bills and council tax share:';
        }
        elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 0) {
        	echo 'Indicative share of bills:';									
        }
        elseif ($ad['incl_council_tax'] == 0 && $ad['incl_utilities'] == 1) {
        	echo 'Share of council tax:';																	
        }
        else {
        	echo 'Bills and council tax share:';																								
        }
    }
    else {
    	if ($ad['accommodation_type'] == "whole place") { 
    	    echo 'Indicative monthly bills:';
    	}
        elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 1) {
    		echo 'Bills and council tax share:';
    	}
        elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 0) {
    		echo 'Indicative share of bills:';									
    	}
        elseif ($ad['incl_council_tax'] == 0 && $ad['incl_utilities'] == 1) {
    		echo 'Share of council tax:';																	
    	}
        else {
    		echo 'Bills and council tax share:';																								
    	}
     }
 }
 else {
    echo 'Bills:';
 }
 ?>
							</td>		
							<td>
								<?php
								if ($ad['accommodation_type'] != "whole place") {
									if ($ad['incl_utilities'] && $ad['incl_council_tax'] ) {
										echo '<strong>Bills and CT included</strong>';								
									}	elseif ($ad['average_bills'] > 0) {
										if ($ad['bedrooms_available'] > 1) { 
											echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['average_bills'], 'app') . ' a month, per bedroom</strong>';										
										} else {
											echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['average_bills'], 'app') . ' a month</strong>';										
										}
									}	else {
									  echo '<span class="grey">No amount given for bills</span>';
									}
								} elseif ($ad['accommodation_type'] == "whole place") {
									// figure represents CT
									if ($ad['offered_id'] > 5416) {	
										if ($ad['average_bills'] == 0 && !$ad['incl_council_tax']) {
											echo '<span class="grey">No amount given for council tax</span>';
										} elseif ($ad['incl_council_tax'] && $ad['incl_utilities']) {
											echo '<strong>CT and bills included</strong>';
										} elseif ($ad['incl_council_tax']) {										
											echo '<strong>Council tax included</strong>';
										} else {
											echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['average_bills'], 'app') . '</strong>';
										}
									} else { // figure represnts bills
										if ($ad['average_bills'] == 0) {
											echo '<span class="grey">No amount given for bills</span>';
										} else {
											echo '<strong>' . $CFSIntl->formatCountryCurrency($ad['average_bills'], 'app') . '</strong>';										
										}
									}
								}
								?>
							</td>
						</tr>
						
					</table>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="140">Date available:</td>
							<td width="180">
								<strong>
								<?php print $ad['available_date_formatted']?><br />
								</strong>
							</td>
							<td width="140">Parking available:</td>
							<td><strong>
								<?php print $ad['parking']?>
							</strong></td>
						</tr>
						<tr>
							<td width="140">Minimum term::</td>
							<td width="180">
							<?php
								if ($ad['min_term']) {
									echo '<strong>'.$terms[$ad['min_term']].'</strong>';
								} else {
									echo '<span class="grey">None</span>';
								}						
							?>
							</td>
							<td width="140">
								<?php if ($ad['accommodation_type'] == "whole place") { ?>
								Room letting:
								<?php } ?>
							</td>
							<td>
								<?php if ($ad['accommodation_type'] == "whole place") { ?>							
									<?php if ($ad['room_letting'] == 1) { ?>
										<strong>Yes.</strong> Bedrooms may be let
									<?php } else { ?>
										<span class="grey">No, not for individual bedroom letting</span>
									<?php } ?>		
								<?php } ?>														
							</td>
						</tr>
						<tr>
							<td width="140">Maximum term:</td>
							<td width="180">
							<?php
								if ($ad['max_term'] == "999") {
									echo '<span class="grey">None</span>';
								} else {
									echo '<strong>'.$terms[$ad['max_term']].'</strong>';
									if ($ad['max_term'] <= 12) {
									echo ' <strong>(short-term)</strong>';
									}					
								}												
							?>
							</td>
							<td width="140">&nbsp;</td>
							<td>
								<?php if ($ad['room_letting'] == 1) { ?>
								individually, please enquire.
								<?php } ?>
							</td>
						</tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
						<tr>
							<td><?php print ($ad['shared_lounge_area']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['shared_lounge_area']? '':' class="grey"')?>>Shared lounge area</td>
							
							<td><?php print ($ad['bicycle_store']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['bicycle_store']? '':' class="grey"')?>>Bicycle store </td>
							
							
							<td><?php print ($ad['washing_machine']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['washing_machine']? '':' class="grey"')?>>Washing machine </td>
						</tr>
						<tr>
							<td><?php print ($ad['garden_or_terrace']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['garden_or_terrace']? '':' class="grey"')?>>A garden / roof terrace </td>

							<td><?php print ($ad['shared_broadband']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['shared_broadband']? '':' class="grey"')?>>Shared broadband </td>													
							
							<td><?php print ($ad['dish_washer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['dish_washer']? '':' class="grey"')?>>Dish washer </td>
						</tr>
						<tr>
							<td><?php print ($ad['ensuite_bathroom']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['ensuite_bathroom']? '':' class="grey"')?>>Ensuite bathroom </td>
							
							<td><?php print ($ad['cleaner']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['cleaner']? '':' class="grey"')?>>A cleaner that visits </td>	
							
							<td><?php print ($ad['tumble_dryer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125" <?php print ($ad['tumble_dryer']? '':' class="grey"')?>>Tumble dryer </td>
						</tr>
			<!-- <tr>
							<td><?php print ($ad['central_heating']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125"><?php print ($ad['central_heating']? '':' class="grey"')?>Central heating </td>

							<td>&nbsp;</td>
							<td width="125">&nbsp;</td>
							<td>&nbsp;</td>
							<td width="125">&nbsp;</td>
						</tr> -->
					</table>

					<?php if ($ad['accommodation_description']) { ?>
						<div id="displayAccommodationDetails">
						<p class="mt0"><strong>More about the accommodation:</strong></p>
					<!--	<p class="mb0"><?php print nl2br(clickable_link(stripslashes($ad['accommodation_description'])))?></p> -->
						<p class="mb0"><?php print nl2br(htmlEscapeAndLinkUrls($ad['accommodation_description'])); ?></p>
						</div>	
					<?php } ?>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
				<?php if (strpos($propertyDescription,"Whole") === FALSE) {	// Only show the CURRENT HOUSEHOLD tab if the ad is NOT about a whole flat or whole house ?>
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">
					<h2 class="mt0">The Household</h2>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Members:</td>
							<td><strong><?php
							$t = "";
							if ($ad['current_num_males']) {
								$t = $ad['current_num_males'].' male';
								if ($ad['current_num_males'] > 1) { $t .= 's'; }
								$t .= ' and ';
							}
							if ($ad['current_num_females']) {
								$t .= $ad['current_num_females'].' female';
								if ($ad['current_num_females'] > 1) { $t .= 's'; }
							} else {
								$t = substr($t,0,-5); // Snip the ' and ';
							}
							echo $t;
							?></strong></td>
						</tr>
						<tr>
							<td>Age range:</td>
							<td><strong><?php print cleanAge($ad['current_min_age'], $ad['current_max_age']) . ' years old'; ?></strong></td>
						</tr>
						<tr>
							<td>Occupation:</td>
							<td><strong>
								<?php if ($ad['current_num_males'] + $ad['current_num_females'] > 1) {
											$current_occupation = $ad['current_occupation'];
										} else {
											// Remove the trailing S, for the singular
										  $current_occupation = substr($ad['current_occupation'], 0, -1);
											if ($ad['current_occupation'] == "Students (<22yrs)") { $current_occupation = "Student (<22yrs)"; }											
										}
								?>
								<?php print ucwords($current_occupation)?>
							</strong></td>
						</tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
						<tr>
							<td><?php print ($ad['owner_lives_in']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['owner_lives_in']? '':' class="grey"')?>>The owner is a household member</td>
						</tr>
						<tr>
							<td><?php print ($ad['current_is_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['current_is_couple']? '':' class="grey"')?>>The household has a married couple</td>
						</tr>

                                                <?php if ($ad['accommodation_type'] != 'flat share') { ?>
						<tr>
							<td><?php print ($ad['current_is_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['current_is_family']? '':' class="grey"')?>>The household has children</td>						
						</tr>
                                                <?php } ?>

					</table>
					
					<table border="0" cellpadding="0" cellspacing="0" class="mb10">
						<tr>
							<td width="160">Church attended:</td>
							<td><strong><?php print stripslashes($ad['church_attended'])?></strong></td>
						</tr>
						<?php if ($ad['church_url']) { ?>
						<tr>
							<td width="160">Our church website:</td>
							<td><strong><?php print htmlEscapeAndLinkUrls($ad['church_url']); ?></strong></td>							
						</tr>
						<?php } ?>
					</table>
					
			
					<?php if ($ad['household_description']) { ?>
						<div id="displayHouseholdDetails">
						<p class="mt0"><strong>More about the household:</strong></p>
						<p class="mb0"><?php print nl2br(htmlEscapeAndLinkUrls($ad['household_description'])); ?></p>
					<!--	<p class="mb0"><?php print nl2br(clickable_link(stripslashes($ad['household_description'])))?></p>		-->
						</div>
					<?php } ?>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				<?php } ?>
				
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">

					<h2 class="mt0">The Accommodation Would Suit</h2>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Sex:</td>
							<td><strong><?php
							$isPlural = ($ad['bedrooms_available'] > 1)? TRUE:FALSE;
							switch($ad['suit_gender']) {
								case "Male(s)": $toEcho .= ($isPlural)? "Males":"Male"; break;
								case "Female(s)": $toEcho .= ($isPlural)? "Females":"Female"; break;
								case "Mixed": $toEcho .= ($isPlural)? "Males or females":"Male or female"; break;
							}
							echo $toEcho;						
							?></strong></td>
						</tr>
						<tr>
							<td>Age range:</td>
							<td><strong><?php print cleanAge($ad['suit_min_age'], $ad['suit_max_age'], 'suit'); ?></strong></td>
						</tr>
						<tr>
							<td>Occupation:</td>
							<td><strong><?php
							$t = "";
							$flag = FALSE;
							if ($ad['suit_student']) { // If ad suits students
								$t .= "Student";
								$t .= ($isPlural)? "s, ":", ";
								$flag = TRUE;
							}
							if ($ad['suit_mature_student']) { // If ad suits mature students
								$t .= "Mature student";
								$t .= ($isPlural)? "s, ":", ";
								$flag = TRUE;
							}
							if ($ad['suit_professional']) { // If ad suits professionals
								$t .= "Professional";
								$t .= ($isPlural)? "s, ":", ";
								$flag = TRUE;			
							}
							// If flag is still false then no occupation was selected
							if (!$flag) { $t .= "Any occupation"; }
							if (substr($t,-2,2) == ", ") { $t = substr($t,0,-2); } // Snip last comma and space (if there) 
							echo $t;						
							?></strong></td>
						</tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
						<tr>
							<td><?php print ($ad['suit_married_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['suit_married_couple']? '':' class="grey"')?>>Would suit a married couple</td>
						</tr>
						<tr>
							<td><?php print ($ad['suit_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['suit_family']? '':' class="grey"')?>>Would suit a family with children</td>
						</tr>
						<?php if($ad['church_reference'] > 0){?>
						<tr>
							<td><?php print ($ad['church_reference']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['church_reference']? '':' class="grey"')?>>Would suit someone who, if asked, could provide a recommendation from a current or previous church</td>
						</tr>
						<?php } ?>						
					</table>
					
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
				
				
				
				<table cellpadding="0" cellspacing="0" width="100%">
				<tr><td>					
				<?php if ($ad['accommodation_type'] == 'whole place' )
				 { ?>
				  <!-- Advert responses -->
				  <div class="box_light_grey mb10" style="float:left; width:550px;">
				  <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
           <table border="0" cellpadding="0" cellspacing="0" width="100%">
					 <tr> 
					  <td ><h2 class="mt0 mb5">Whole Place Palups</h2></td>
						
						<td align="right"><?php echo createPalup_Option($ad['offered_id'])?></td>
					 </tr>
					 </table>
 				   <p class="mt0 mb5">The wanted adverts of those who have expressed they may be willing to explore sharing this <?php print $ad['building_type']?>: &nbsp;<strong><a href="#" class="tooltip" id="what_is_palup">(?)</a></strong></p>					 					
           <table border="0" cellpadding="0" cellspacing="0" width="100%">
				   <tr><td>
					 <?php
					   if (getNumberOfAdPalups($ad['offered_id']) == 0) 
						   { ?>
						<p class="mt0 mb5">No one has chosen to pal-up for this <?php print $ad['building_type']?> yet.</p>
					   <?php
					  } else {
					    echo createDisplayPalups($ad['offered_id']);					
						}
					 ?>
				   </td></tr>
				   </table>					
					<?php } ?>
        </div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>

          <div class="box_light_grey mb10" style="float:left; width:550px;">
				  <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
						
				  <p class="mt0 mb0">
					 <table style="padding-right:4px" cellpadding="0" cellspacing="0" border="0" width="100%">
					  <tr>
					   <td><h2 class="mt0 mb5">Your replies to this ad</h2></td>
					   <td align="right"><?php if (numberOfReplies($post_type, $id)>0) { ?>
						 				<a href="your-account-message-reply.php?reply_id=<?php print getMaxReplyId($post_type,$id)?>">Message thread</a>
									<?php } ?>
						</td>				
					  </tr>
					 </table>

				  <table border="0" cellpadding="0" cellspacing="0" width="100%">
				  <tr><td>
					<?php
					   echo createDisplayEmails($ad,$post_type);					
					?>
				  </td></tr>
				  </table>
										 </p>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>					
				
								
				<td align="right" valign="top">
					<!-- AddThis Bookmark Button BEGIN -->
					<script type="text/javascript">
					addthis_url    = location.href;   
					addthis_title  = document.title;  
					addthis_pub    = 'Christian Flatshare';     
					</script><script type="text/javascript" src="http://s7.addthis.com/js/addthis_widget.php?v=12" ></script>
					<!-- AddThis Bookmark Button END -->
				</td></tr>

				<tr><td style="padding-top:5px;">
				<!-- Advert age -->
				<span class="grey mt10">
				<?php
					if ($ad['ad_age']==0) {
						$ad_age .= '(advert: created today, ';
					} else if ($ad['ad_age']==1) {
						$ad_age .= '(advert: created yesteday, ';
					} elseif ($ad['ad_age']>15) {
					      $ad_age .= '(advert: more than 15 days old, ';
						} else {
						  $ad_age .= '(advert: '.$ad['ad_age'].' days old, ';	
					} 
					echo $ad_age;
				?>
				<!-- Times views  -->
				<?php 
					if ($ad['times_viewed']==0) {
						$times_viewed  .= 'viewed once)';
					} else if ($ad['times_viewed']==1) {
						$times_viewed  .= ' viewed once)';
					} else {
						$times_viewed .= ' viewed '.$ad['times_viewed'].' times)';	
					}
					echo $times_viewed;
				?>	
				<br /><br />
				Offered adverts are suspended when owners do not login for 30 days, or when <br />
				adverts are 10 days older than their  available date. Owners are notified prior. <br />
				<br />   
				</span>						
				</td>
				</tr>
				</table>
				
				</div>		
				<div class="cs" style="width:21px; height: 600px;"><!----></div>
				<div style="float:left; width:129px;">
					<h2 class="mt0">Photos:</h2>
					<?php print $photos?>
					
					<div class="clear"><!----></div>
					<?php print loadBanner("120",$ad['postcode'],FALSE, $num_photos)?>
					
				</div>
				<div class="clear" style="height:0px;"><!----></div>			
	
			<?php } else if ($post_type == "wanted") { ?> 
			
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
					<tr>
						<td><h1 class="m0"><a name="detail"></a>Wanted ad details</h1></td>
						<td align="right">
							<?php 			
							// Next and previous functionality
							$debug .= debugEvent("$_SESSION variable 'result_set':",print_r($_SESSION['result_set'],true));
							echo nextPreviousAd($post_type, $id);		
							$debug .= debugEvent("REferer:",print_r($_SERVER,true)); 
							?>
						</td>
					</tr>
				</table>
				
				<?php print createSummaryV2($ad,"wanted","odd mb10",FALSE,FALSE,TRUE)?>
				
				<!-- Contact name,phone number and member login -->
				<?php	
					if ($ad['last_login_days']==0) {
					$last_logged_in = 'logged in today';
					} else if ($ad['last_login_days']==1) {
					$last_logged_in = 'logged in yesterday';
					} else {
					$last_logged_in =  'last logged in '.$ad['last_login_days'].' days ago';	
					}	?>	
				
				<div class="cl" style="width:700px;">
				<?php print $res?>
 		 	  <table><tr><td width=60%>
				<p class="mt0">
				
				<span class="style1">Contact: </span>
			<?php if (isset($_SESSION['u_id']))
				          { ?><span class="style1"><?php print trim(stripslashes($ad['contact_name']))?><?php print ($ad['contact_phone']?", ".$ad['contact_phone']:" ")?></span><?php }
								else 
								  { echo '</span><span class="grey">&nbsp;<a href="login.php">login</a> to see contact details </span><a href="#" class="tooltip" id="contact_details">(?)</a>'; } 
								?>
								<br /><a href="reply.php?wanted_id=<?php print $id?>">Send <?php print (isset($_SESSION['u_id'])?stripslashes($ad['contact_name']):"the advert's owner")?> a message</a>
								
			
         </td>
				 <td width=400 align="right" valign="top"><span class="grey">(<?php print (isset($_SESSION['u_id']) ? stripslashes($ad['contact_name']) : "advert owner"); ?> <?php print $last_logged_in; ?>)<br/>
    <a title="Share property on Facebook" 
        href="http://www.facebook.com/sharer.php?u=<?php print urlencode($_SERVER["SERVER_NAME"] . '/details.php?id=' . $_GET['id'] . '&post_type=' . $_GET['post_type']); ?>" target="_blank" class="fb-share">Share on Facebook</a></span></p>
				</td></tr></table>

			
				
				
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">

					<h2 class="mt0">The Accommodation Wanted</h2>
 				  <table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Location</td>
							<td><strong><?php
							echo 'Within '.$ad['distance_from_postcode'].' mile';
							if ($ad['distance_from_postcode'] > 1) { echo 's'; }
                            
                            if ($ad['country'] == 'GB' || $ad['country'] == '') {
                                print ' of '.stripslashes($ad['location']).' ('.getUKPostcodeFirstPart($ad['postcode']).')';
                            }
                            else {
                                $address = array($ad['street'], $ad['area'], $ad['region']);
                                print ' of ' . implode(', ', $address);
                            }
							?></strong></td>
						</tr>
						<tr>
							<td>Bedrooms required:</td>
							<td><strong><?php print $ad['bedrooms_required']?></strong></td>
						</tr>
						<tr>
							<td>Price (approx monthly max):</td>
							<td><strong><?php print $CFSIntl->formatCountryCurrency($ad['price_pcm'], 'app'); ?> per bedroom</strong></td>
						</tr>
					</table>
					
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Required from:</td>
							<td><strong>
							<?php print $ad['available_date_formatted'];?>
							</strong></td>
						</tr>
						<tr>
							<td>Minimum term:</td>
							<td><?php
							if ($ad['min_term']) {
								echo '<strong>';
								echo $terms[$ad['min_term']];
								echo '</strong>';
							} else {
								echo '<span class="grey">None</span>';
							}
							?></td>
						</tr>
						<tr>
							<td>Maximum term:</td>
							<td><?php
							if ($ad['max_term'] == "999") {
								echo '<span class="grey">None</span>';
							} else {
								echo '<strong>';
								echo $terms[$ad['max_term']];
								echo '</strong>';
								if ($ad['max_term'] <= 12) {
									echo ' <strong>(short-term)</strong>';
								}
							}						
							?></td>
						</tr>
						<tr>
							<td>Accommodation type:</td>
							<td><strong><?php
							  $t = '';
								if ($ad['accommodation_type_flat_share']) { $t = 'flatshare, '; }
								if ($ad['accommodation_type_room_share']) { $t .= 'room share, '; }								
								if ($ad['accommodation_type_family_share']) { $t .= 'family share, '; }
								if ($ad['accommodation_type_whole_place']) { $t .= 'whole place, '; }
								if ($ad['palup']) { $t .= 'pal-up, '; }
								$t = substr($t,0,-2); // Snip last space & comma
								$t = ucfirst($t);
								echo $t;
							?></strong></td>
						</tr>
						<tr>
							<td>Building type:</td>
							<td><strong><?php
							if ($ad['building_type_flat'] && $ad['building_type_house']) {
								echo "House or flat";
							} else if ($ad['building_type_flat']) {
								echo "Flat";
							} else if ($ad['building_type_house']) {
								echo "House";
							}
							?></strong></td>
						</tr>
					</table>
					
					<p>The accommodation must have:</p>
					<table border="0" cellpadding="0" cellspacing="5">
						<tr>
							<td><?php print ($ad['shared_lounge_area']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['shared_lounge_area']? '':' class="grey"')?>>Shared lounge area</td>
							
							<td><?php print ($ad['washing_machine']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['washing_machine']? '':' class="grey"')?>>Washing machine </td>
							
							<td><?php print ($ad['garden_or_terrace']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['garden_or_terrace']? '':' class="grey"')?>>A garden / roof terrace </td>

						</tr>
						<tr>
							<td><?php print ($ad['bicycle_store']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['bicycle_store']? '':' class="grey"')?>>Bicycle store </td>
							
							<td><?php print ($ad['dish_washer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['dish_washer']? '':' class="grey"')?>>Dish washer </td>
							
							<td><?php print ($ad['parking']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['parking']? '':' class="grey"')?>>Somewhere to park a car</td>
						</tr>
						<tr>
							<td><?php print ($ad['ensuite_bathroom']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['ensuite_bathroom']? '':' class="grey"')?>>Ensuite bathroom </td>
							
							<td><?php print ($ad['tumble_dryer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['tumble_dryer']? '':' class="grey"')?>>Tumble dryer </td>
							
							<td></td>
							<!--
							<td><?php print ($ad['central_heating']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['central_heating']? '':' class="grey"')?>>Central heating </td> 
							-->									
						</tr>
					</table>								
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
		

				
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">
				
					<?php if ($ad['current_num_females'] + $ad['current_num_males'] > 1) { $plural = "s"; } ?>
					<h2 class="mt0">The Accommodation Seeker<?php echo $plural; ?></h2>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Sex:</td>
							<td><strong><?php
							$t = "";
							if ($ad['current_num_males']) {
								$t = $ad['current_num_males'].' male';
								if ($ad['current_num_males'] > 1) { $t .= 's'; }
								$t .= ' and ';
							}
							if ($ad['current_num_females']) {
								$t .= $ad['current_num_females'].' female';
								if ($ad['current_num_females'] > 1) { $t .= 's'; }
							} else {
								$t = substr($t,0,-5); // Snip the ' and ';
							}
							echo $t;
							?></strong></td>
						</tr>
						<tr>
							<td>Age range:</td>
							<td><strong><?php
							if (!$ad['current_min_age'] && !$ad['current_max_age']) {
								echo '-';
							} else if (!$ad['current_min_age']) {
								echo "Under ".$ad['current_max_age']." years old";
							} else if (!$ad['current_max_age']) {
								echo "Over ".$ad['current_min_age']." years old";
							} else {
								if ($ad['current_min_age'] == $ad['current_max_age']) {
									echo $ad['current_min_age']." years old";
								} else {
								echo $ad['current_min_age']." to ".$ad['current_max_age']." years old";
							}
							}
							?></strong></td>
						</tr>
						<tr>
							<td>Occupation:</td>
							<?php if ($ad['current_num_males'] + $ad['current_num_females'] > 1) {
											$current_occupation = $ad['current_occupation'];
										} else {
											// Remove the trailing S, for the singular
										  $current_occupation = substr($ad['current_occupation'], 0, -1);
											if ($ad['current_occupation'] == "Students (<22yrs)") { $current_occupation = "Student (<22yrs)"; }
										}
							?>
							<td><strong><?php print ucwords($current_occupation)?></strong></td>
						</tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
						<tr>
							<td><?php print ($ad['current_is_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['current_is_couple']? '':' class="grey"')?>>Are a married couple</td>
						</tr>
						<tr>
							<td><?php print ($ad['current_is_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['current_is_family']? '':' class="grey"')?>>Are a family with children</td>						
						</tr>
						<?php if($ad['church_reference'] > 0){?>
						<tr>
							<td><?php print ($ad['church_reference']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['church_reference']? '':' class="grey"')?>>Could provide a recommendation from a previous church if asked to</td>
						</tr>					
						<?php } ?>
					</table>
					
					<?php if ($ad['church_attended']) { ?>
					<table border="0" cellpadding="0" cellspacing="0" class="mb10">
						<tr>
							<td width="160">Church attended:</td>
							<td><strong><?php print stripslashes($ad['church_attended'])?></strong></td>
						</tr>
						<?php if ($ad['church_url']) { ?>
						<tr>
							<td width="160">Our church website:</td>
						<!-- <td><strong><?php print nl2br(clickable_link((" ".stripslashes($ad['church_url']))))?></strong></td> -->
							<td><strong><?php print htmlEscapeAndLinkUrls($ad['church_url']); ?></strong></td>							
						</tr>
						<?php } ?>
					</table>
					<?php } ?>
				
					<?php if ($ad['accommodation_situation']) { ?>
						<div id="displayHouseholdDetails">
						<p class="mt0"><strong>More about the accommodation seeker<?php echo $plural; ?>:</strong></p>
				<!--		<p class="mb0"><?php print nl2br(clickable_link(stripslashes($ad['accommodation_situation'])))?></p> -->
						<p class="mb0"><?php print htmlEscapeAndLinkUrls($ad['accommodation_situation']); ?></p>						
					</div>
					<?php } ?>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
						<?php if ($ad['accommodation_type_flat_share'] || $ad['accommodation_type_family_share'] || $ad['accommodation_type_room_share']) { ?>
				
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">

					<h2 class="mt0">The Preferred Household</h2>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Max number of members:</td>
							<td><strong><?php print ($ad['shared_adult_members']==4? "Any number":$ad['shared_adult_members'])?></strong></td>
						</tr>
						<tr>
							<td>Sex:</td>
							<td><strong><?php
							$toEcho = "";
							if ($ad['shared_males']) { $toEcho .= "Males, "; }
							if ($ad['shared_females']) { $toEcho .= "Females, "; }
							if ($ad['shared_mixed']) { $toEcho .= "Mixed household, "; }
							// Snip last comma and space
							$toEcho = substr($toEcho,0,-2);
							echo $toEcho;
							?></strong></td>
						</tr>
						<tr>
							<td>Age range:</td>
							<td><?php
								if (!$ad['shared_min_age'] && !$ad['shared_max_age']) {
									echo '<strong>';
									echo "Any age";
									echo '</strong>';
								} else {
									echo '<strong>';
									if (!$ad['shared_min_age']) {
										echo "Under ".$ad['shared_max_age']." years old";
									} else if (!$ad['shared_max_age']) {
										echo "Over ".$ad['shared_min_age']." years old";
									} else {
										echo $ad['shared_min_age']." to ".$ad['shared_max_age']." years old";
									}
									echo '</strong>';
								}
							?></td>
						</tr>
						<tr>
							<td>Occupation:</td>
							<td><?php
								$isPlural = ($ad['shared_adult_members'] > 1)? TRUE : FALSE;
								if ((!$ad['shared_student'] && !$ad['shared_mature_student'] && !$ad['shared_professional']) ||
								    ($ad['shared_student'] && $ad['shared_mature_student'] && $ad['shared_professional'])) {
									echo '<strong>Any occupation</strong>';
								} else {
									echo '<strong>';
									$t = null;
									if ($ad['shared_student']) {
										$t .= 'Student';
										if ($isPlural) { $t .= 's'; }
										$t .= ' (<22yrs), ';
									}
									if ($ad['shared_mature_student']) {
										$t .= 'Mature student';
										if ($isPlural) { $t .= 's'; }
										$t .= ', ';
									}
									if ($ad['shared_professional']) {
										$t .= 'Professional';
										if ($isPlural) { $t .= 's'; }
										$t .= ', ';
									}
									$t = substr($t,0,-2);
									echo $t;
									echo '</strong>';
								}
							?></td>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="5">
						<tr>
							<td><?php print ($ad['shared_owner_lives_in']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['shared_owner_lives_in']? '':' class="grey"')?>>The owner could be a member</td>
						</tr>
						<tr>
							<td><?php print ($ad['shared_married_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['shared_married_couple']? '':' class="grey"')?>>It could have a married couple</td>
						</tr>
						<tr>
							<td><?php print ($ad['shared_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['shared_family']? '':' class="grey"')?>>It could be a family with children</td>
						</tr>
					</table>
					
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
				<?php } ?>
				
				<table cellpadding="0" cellspacing="0" width="100%">
				<tr><td>						
				  <!-- Advert responses -->
				  <div class="box_light_grey mb10" style="float:left; width:550px;">
				  <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
				  <h2 class="mt0 mb5">Your replies to this ad</h2>
				   <table border="0" cellpadding="0" cellspacing="0" width="100%">
				  <tr><td>
					<?php
					   echo createDisplayEmails($ad,$post_type);					
					?>
				  </td></tr>
				  </table>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>					
				
								
				<td align="right" valign="top">
					<!-- AddThis Bookmark Button BEGIN -->
					<script type="text/javascript">
					addthis_url    = location.href;   
					addthis_title  = document.title;  
					addthis_pub    = 'Christian Flatshare';     
					</script><script type="text/javascript" src="http://s7.addthis.com/js/addthis_widget.php?v=12" ></script>
					<!-- AddThis Bookmark Button END -->
				</td></tr>

				<tr><td style="padding-top:5px;">
				<!-- Advert age -->
				<span class="grey mt10">
				<?php
					if ($ad['ad_age']==0) {
						$ad_age .= '(advert: created today, ';
					} else if ($ad['ad_age']==1) {
						$ad_age .= '(advert: created yesteday, ';
					} elseif ($ad['ad_age']>15) {
					      $ad_age .= '(advert: more than 15 days old, ';
						} else {
						  $ad_age .= '(advert: '.$ad['ad_age'].' days old, ';	
					} 
					echo $ad_age;
				?>
				<!-- Times views  -->
				<?php 
					if ($ad['times_viewed']==0) {
						$times_viewed  .= 'viewed once)';
					} else if ($ad['times_viewed']==1) {
						$times_viewed  .= ' viewed once)';
					} else {
						$times_viewed .= ' viewed '.$ad['times_viewed'].' times)';	
					}
					echo $times_viewed;
				?>	
				<br /><br />
				Wanted adverts are suspended when owners do not login for 30 days, or when <br />
				adverts are 10 days older than their &quot;wanted from&quot; date. Owners are notified prior. <br /><br />   
				</span>						
				</td></tr>
				</table>		
				
				</div>
				<div class="cs" style="width:21px; height: 600px;"><!----></div>
				<div class="cr" style="width:129px;">
				<h2 class="mt0">Photos:</h2>
					<?php print $photos?>
					<div class="clear"><!----></div>
					<?php print loadBanner("120",$ad['postcode'],FALSE, $num_photos)?>
										
				</div>
				<div class="clear" style="height:0px;"><!----></div>			
			<?php } ?>
	
			
		<?php } ?>
		
        <?php
            
        if (!empty($ad['latitude'])) {
            print loadBanner("728", array($ad['latitude'], $ad['longitude']), FALSE, 0, $userCountry['iso']);
        }
        else {
            print loadBanner("728",$ad['postcode'],FALSE, 0, $userCountry['iso']);
        }
            
            
        ?>
				
		<?php if ($ad) { ?>
		<table width="100%" class="mb0">
		<tr><td>
		<a href="#" onclick="history.go(-1);">Return to the previous page</a>
		<td>
		<?php if ($ad['suspended'] == 0) { ?>
		<td align="right">
		Please <a href="use-cfs-in-your-church.php" target="_blank">share</a> Christian Flatshare with your church.
		</td>
		<?php } ?>		
		</tr></table>
		
		<?php } ?>
	
		
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
