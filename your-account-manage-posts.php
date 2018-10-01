<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

    $debug = NULL;
	$remove_msg = "";
	$hide_photo_warning = TRUE;
	
	// If advertiser: redirect
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	// Set the flag for the welcome message to TRUE
 	$welcome_msg = TRUE;	
	
	
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

  // Determine if system message has been acknowleged
	$query = "select warning_ack from cf_users where user_id = '".$_SESSION['u_id']."' ";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);		
	$user_record = mysqli_fetch_assoc($result);	
	($user_record['warning_ack'] == 1)?$warning_ack=TRUE:$warming_ack=FALSE;;
	if (!$warning_ack) { header("Location:login-message.php"); exit; }
  
  // Determine if member has suspended their account or not
	$query = "select account_suspended from cf_users where user_id = '".$_SESSION['u_id']."' ";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);		
	$user_record = mysqli_fetch_assoc($result);	
	($user_record['account_suspended']==1)?$suspended=TRUE:$suspended=FALSE;
	// If we're temporarily suspending, unsuspending an ad, republish, new ad
	if ($new_ad) { $action="newad"; }
	switch($action) {
	    case "newad":
			$remove_msg = '<p class="green"><strong>Your ad has been published successfully.</strong></p>';
			break; 
		case "suspend":
			$query = "update cf_".$post_type." set suspended = '1' 
					  where ".$post_type."_id = '".$id."'
					  and user_id = ".$_SESSION['u_id'];
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result && (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0)) {
				$remove_msg = '<p class="green"><strong>Your ad has been suspended.</strong></p><p><strong>Note: </strong>If you have finished using CFS for the time being you can <a href="your-suspend-account.php">suspend your account</a>.</p>
                <div class="fb-like-container-long"><p>Was your advert successful? Recommend CFS on Facebook.</p><div class="fb-like" data-href="http://www.christianflatshare.org" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-action="recommend"></div></div><br/>';
			} else {
		//	$remove_msg = '<p class="red"><strong>There was a problem suspending your advert.</strong></p>';
			}					
			break;
			
		case "unsuspend":
			$query = "update cf_".$post_type." 
					  set suspended = '0' where ".$post_type."_id = '".$id."'
					  and user_id = ".$_SESSION['u_id'];
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result && (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0)) {
		//	$remove_msg = '<p class="green"><strong>Your advert has been successfully un-suspended.</strong></p>';
			} else {
		//	$remove_msg = '<p class="red"><strong>There was a problem un-suspending your advert.</strong></p>';
			}					
			break;
			
		case "republish":
		$query = "update cf_".$post_type." set created_date = now(), times_viewed = 0 
				  where ".$post_type."_id = '".$id."' and DATEDIFF(now(),created_date) > 15
				  and user_id = ".$_SESSION['u_id'];
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result && (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0)) {
			  if (photoCount($id, $post_type) > 0) {
					$remove_msg = '<p class="green"><strong>Your advert has been successfully republished</strong></p>';
				} else {
					// No photos are added - display warning
					$hide_photo_warning = FALSE;
				  $welcome_msg = FALSE;								
					$remove_msg = '
					  <p class="green"><strong>Your advert has been republished.</strong></p>
						<table border="0" cellpadding="0">
						 <tr>
							 <td width="410" class="mt10 mb0" valign="top" style="padding-left:10px;padding-top:0px;padding-bottom:40px">
								<div class="mt0" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:475px;">
								<p class="mb5 mt0"><span class="style5"><strong>Add photos to get the best response!</strong></span></p><br />';
					if ($post_type == "wanted") {
						$remove_msg .= '<a href="post-'.$post_type.'.php?id='.$id.'&step=6">Adding photos</a> helps introduce the accommodation seeker(s) and get a <u>much</u> better response.';
					} else { 
						$remove_msg .= '<a href="post-'.$post_type.'.php?id='.$id.'&step=6">Adding photos</a> helps attract a <u>much</u> better advert response. <br />Recommended photos include:<br />
						- Bedrooms<br />
						- Bathrooms<br />
						- Kitchen<br />
						- Living areas<br />
						- Of the outside of the house or flat';					
					}
					$remove_msg .= '<br /><br />
						<a href="your-account-manage-posts.php">Okay, got it!</a>
						</div>
					</td>
					</tr>
					</table>';
				} // END If Photos
			} else {
	   	//	$remove_msg = '<p class="red"><strong>There was a problem republishing your advert.</strong></p>';
			}			
			break;			
			
		case "familyshare":
		$query = "update cf_".$post_type." set accommodation_type = 'family share'
				  where ".$post_type."_id = '".$id."' 
				  and user_id = ".$_SESSION['u_id'];
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result && (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0)) {
			$remove_msg = '<p class="green"><strong>Your advert has been successfully changed to "family '.$ad['accommodation_type'].' share"</strong></p>';
			} else {
		//	$remove_msg = '<p class="red"><strong>There was a problem changing your advert.</strong></p>';
			}			
			break;			
		
		case "extend":
		$query = "update cf_".$post_type." 
							set available_date = now(), 
									expiry_date = date_add(now(),interval 10 day)
				  where ".$post_type."_id = '".$id."' 
				  and user_id = ".$_SESSION['u_id'];
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			
			if ($result && (mysqli_affected_rows($GLOBALS['mysql_conn']) > 0)) {
				$remove_msg = '<p class="green"><strong>Your advert has been extended for 10 days.</strong></p>';
			} else {
	   		$remove_msg = '<p class="red"><strong>There was a problem extending your advert.</strong></p>';
			}			
			break;						
	}
	
	
	
	
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
	
	$offered_ads = array();
	$wanted_ads = array();
	
	// OFFERED ADS
	/*$query = "
	select 
	offered_id, bedrooms_available, bedrooms_total,
	accommodation_type, building_type, street_name, postcode,
	paid_for, approved, suspended, published,
	DATE_FORMAT(expiry_date,'%W, %D of %M') as `expiry_date`,
	available_date,
	DATE_FORMAT(available_date,'%W, %D of %M') as `available_date_formatted`,
	DATE_FORMAT(created_date,'%d %M %Y at %k:%i') as `created_date`,
	DATE_FORMAT(last_updated_date,'%d %M %Y at %k:%i') as `last_updated_date`,
	if(expiry_date < now(),1,0) as `expired`,
	times_viewed, picture, count(cf_photos.ad_id) as `num_of_photos`
	from cf_offered 
	left join cf_photos on cf_photos.ad_id = cf_offered.offered_id
	where user_id = '".$_SESSION['u_id']."'
	group by cf_offered.offered_id";*/
   
    // RD 30-JUN-13 ... I don't think this needs to be UNION really... 
    //if ($userCountry['iso'] == 'GB') {    
    	$query = "
    		select 
    			o.offered_id, o.bedrooms_available, o.bedrooms_total, o.accommodation_type, o.room_share, o.building_type, o.street_name, o.postcode,
                          o.country, o.area, o.region, o.street,

    			(CASE WHEN LENGTH(town_chosen)>0
    			      THEN o.town_chosen
    	 	           ELSE (SELECT j2.town 
    				      	 FROM   cf_jibble_postcodes j2 
    						 WHERE  SUBSTRING_INDEX(o.postcode,' ',1) = j2.postcode )
    		            END) as town,
    			o.paid_for, o.approved, o.suspended,
    			(CASE IFNULL(u.suppressed_replies,0)
    	 	       WHEN 0 THEN o.published
    			   ELSE 1
    		     END) as `published`,
    			DATE_FORMAT(DATE_ADD(o.available_date,INTERVAL 10 DAY),'%W, %M %D') as `expiry_date`,
    			DATEDIFF(o.expiry_date, now()) as `days_to_expiry`,
    			o.available_date, current_num_males, current_num_females, current_min_age, current_max_age,
    			current_is_family, current_is_couple, household_description, accommodation_description,
    			recommendations,
    			DATE_FORMAT(o.available_date,'%W, %M %D') as `available_date_formatted`,
    			DATE_FORMAT(o.available_date,'%D %M %Y') as `available_date_formatted_2`,			
    			DATE_FORMAT(o.created_date,'%d %b, %Y at %H:%i') as `created_date`,
    			DATE_FORMAT(o.last_updated_date,'%d %b, %Y at %H:%i') as `last_updated_date`,
    			if(o.expiry_date < now(),1,0) as `expired`,
    			o.times_viewed, o.picture, 
      	         DATEDIFF(now(),o.created_date) as `days_old`,			
    			(SELECT COUNT(*) from cf_photos p WHERE p.ad_id = o.offered_id and p.post_type = 'offered' ) as `num_of_photos`,
    			u.first_name, u.surname
    		from cf_offered as `o`, 
    		     cf_users as `u`
    		where o.user_id = '".$_SESSION['u_id']."'
    		and   o.user_id = u.user_id
    		and   o.published != 2
                and   (o.country = 'GB' or o.country = '')
            UNION 
    		select 
    			o.offered_id, o.bedrooms_available, o.bedrooms_total, o.accommodation_type, o.room_share, o.building_type, o.street_name, o.postcode,
                        o.country, o.area, o.region, o.street,
    			(CASE WHEN LENGTH(town_chosen)>0
    			      THEN o.town_chosen
    	 	           ELSE (SELECT j2.town 
    				      	 FROM   cf_jibble_postcodes j2 
    						 WHERE  SUBSTRING_INDEX(o.postcode,' ',1) = j2.postcode )
    		         END) as town,
    			o.paid_for, o.approved, o.suspended,
    			(CASE IFNULL(u.suppressed_replies,0)
    	 	       WHEN 0 THEN o.published
    			   ELSE 1
    		     END) as `published`,
    			DATE_FORMAT(DATE_ADD(o.available_date,INTERVAL 10 DAY),'%W, %M %D') as `expiry_date`,
    			DATEDIFF(o.expiry_date, now()) as `days_to_expiry`,
    			o.available_date, current_num_males, current_num_females, current_min_age, current_max_age,
    			current_is_family, current_is_couple, household_description, accommodation_description,
    			recommendations,
    			DATE_FORMAT(o.available_date,'%W, %M %D') as `available_date_formatted`,
    			DATE_FORMAT(o.available_date,'%D %M %Y') as `available_date_formatted_2`,			
    			DATE_FORMAT(o.created_date,'%d %b, %Y at %H:%i') as `created_date`,
    			DATE_FORMAT(o.last_updated_date,'%d %b, %Y at %H:%i') as `last_updated_date`,
    			if(o.expiry_date < now(),1,0) as `expired`,
    			o.times_viewed, o.picture, 
      	         DATEDIFF(now(),o.created_date) as `days_old`,			
    			(SELECT COUNT(*) from cf_photos p WHERE p.ad_id = o.offered_id and p.post_type = 'offered' ) as `num_of_photos`,
    			u.first_name, u.surname
    		from cf_offered as `o`, 
    		     cf_users as `u`,
    		     cf_jibble_postcodes as `j`
    		where o.user_id = '".$_SESSION['u_id']."'
    		and   o.user_id = u.user_id
    		and   o.published != 2
                and   (o.country != 'GB' AND o.country != '')";
    //}
  // echo $query; 
	$debug .= debugEvent("Offered ads query",$query);
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	while($ad = mysqli_fetch_assoc($result)) {
                $debug .= debugEvent("ad['offered_id']",$ad['offered_id']);
		$offered_ads[$ad['offered_id']] = $ad;
	}
	
	// WANTED ADS
	$query = "
	select 
	w.wanted_id, w.bedrooms_required, w.distance_from_postcode, w.location, w.postcode,
	w.paid_for, w.approved, w.suspended, w.country, w.street, w.area, w.region,
	(CASE IFNULL(u.suppressed_replies,0)
       WHEN 0 THEN w.published
	   ELSE 1
     END) as `published`,
	DATE_FORMAT(DATE_ADD(w.available_date,INTERVAL 10 DAY),'%W, %M %D') as `expiry_date`,	 
	DATEDIFF(w.expiry_date, now()) as `days_to_expiry`,	
	w.available_date,
	DATE_FORMAT(w.available_date,'%W, %M %D') as `available_date_formatted`,
	DATE_FORMAT(w.available_date,'%D %M %Y') as `available_date_formatted_2`,				
	DATE_FORMAT(w.created_date,'%d %b, %Y at %H:%i') as `created_date`,
	DATE_FORMAT(w.last_updated_date,'%d %b, %Y at %H:%i') as `last_updated_date`,
	if(w.expiry_date < now(),1,0) as `expired`,	
	w.times_viewed, w.picture, current_num_males, current_num_females, current_min_age, current_max_age,
	recommendations, accommodation_situation,
	palup,
	DATEDIFF(now(),w.created_date) as `days_old`,
	(SELECT COUNT(*) FROM cf_photos p WHERE p.ad_id = w.wanted_id and p.post_type = 'wanted' ) as `num_of_photos`,
	u.first_name, u.surname
	from cf_wanted as `w`,
	     cf_users as `u`
	where w.user_id = '".$_SESSION['u_id']."'
	and   w.user_id = u.user_id
	and   w.published != 2";

   // if ($userCountry['iso'] == 'GB' || $userCountry['iso'] == '') {
   //     $query .= " and   (w.country = '" . $userCountry['iso'] . "' or w.country = '')";
   // }
   // else {
   //     $query .= " and   w.country = '" . $userCountry['iso'] . "'";
   // }

	$query .= " group by w.wanted_id";
    
	$debug .= debugEvent("Wanted ads query",$query);
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	while($ad = mysqli_fetch_assoc($result)) {
		$wanted_ads[$ad['wanted_id']] = $ad;
	}
	function createLargeSummary($ad,$post_type,$class) {
	$o.= '<div class="box_light_grey mb10">
		  <div class="tr"><span class="l"></span><span class="r"></span></div>';
			
		// Create advert table header
		$o .= '<table style="padding-bottom:10px" width="100%" border="0" cellpadding="0" cellspacing="0">'."\n"; 
		// DESCRIPTION CELL
		$o .= '<tr class="box_light_grey">';
		$o .= '<td style="padding-left:10px;padding-right:10px">'."\n";
		$o .= '<span style="font-size:15px;"><strong>'.ucfirst($post_type).' ad</strong></span>&nbsp;&nbsp;<span style="font-size:11px;"><a href="details.php?id='.$ad[$post_type.'_id'].'&post_type='.$post_type.'" target="_blank">View your ad</a></span>'."\n";					
		$o .= '</td>';		
		
		$o .= '<td style="font-size:11px;padding-right:10px;" align="right">';
		$o .= '<a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=6">Add photos</a> | ';				
		$o .= '<a href="your-account-delete-post.php?post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Delete ad</a> | ';					
		if (!$ad['suspended']) {
			$o .= '<a href="'.$_SERVER['PHP_SELF'].'?action=suspend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Suspend ad</a> | ';
		} else {
			$o .= '<a href="'.$_SERVER['PHP_SELF'].'?action=unsuspend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Un-suspend ad</a> | ';
		}
   	 	$o .= '<a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'">Edit ad</a>';
		
		$o .= '</td></tr>';				
		$o .= '<table>';		
		$o .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">'."\n"; 
		// Ad title		
		$o .= '<tr><td style="padding-top:2px;padding-left:10px;padding-right:5px;padding-bottom:0px;">'."\n";
		$o .= '<span class="m0" style="font-size:13px;"><strong>'.getAdTitle($ad,$post_type, TRUE, FALSE, TRUE).'</strong></span><br />';
   	        $o .= '</td></tr>'."\n";	
		// Status			
		$o .= '<tr class="odd">'."\n";
		$o .= '<td style="padding-left:10px;padding-right:10px;padding-top:5px;">'."\n";
		if ($ad['expired'] && !$ad['suspended']) {
			$o .= '<span style="font-size:13px;" class="grey">Status: </span><strong class="error" style="font-size:13px;">ADVERT EXPIRED </strong><br/>';
		} else {
			$status = getStatus($ad['paid_for'],$ad['approved'],$ad['suspended'],$ad['published']);
      // A temporary hack - Whole Place ads are defaulted to Pending Approval - we hide that here by defaulting the status to Published
      if ($status == 'Pending approval') { $status = 'Published'; }
			$o .= '<span style="font-size:13px;" class="grey">Status: </span><span style="font-size:13px;" class="'.preg_replace('/\s/','_',strtolower($status)).'">'.$status.'</span><br/>';
		}
	    	$o .= '</td></tr>'."\n";	
		
		$o .= '<tr class="odd">'."\n";
		$o .= '<td style="padding-left:10px;padding-right:10px;padding-top:0px;">'."\n";		
	
	// Advert WILL expire in 1-9 days...	
	if ($ad['days_to_expiry'] < 8 && $ad['days_to_expiry'] > 0 && !$ad['suspended']) {
	    $o .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:400px;" >';
		// Information
		 if ($ad['days_to_expiry'] > 1 ) { $days_plural = 's'; } else { $days_plural = ''; }
		 $o .= '<table width="100%" border="0" cellpadding="0">
		 				<tr>
		 				<td><span style="font-size:14px;"><strong>Expiry notice</strong></strong></span></td><td align="right">Your advert will expire in <span class="obligatory"><strong>'.$ad['days_to_expiry'].' day'.$days_plural.'</strong></span></td>
						</tr></table>'."\n";					
		//$o .= '<h3>Expired advert?</h3>'."\n";
 	  if ($post_type == "offered") {
			$o .= 'Your accommodation is advertised as being available from '.$ad['available_date_formatted_2'].'.<br />Adverts expire 10 days after their "Available From" date.</p>'."\n";
		  $o .= '<p class="mb0"><a href="'.$_SERVER['PHP_SELF'].'?action=extend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Click here</a> to update your ad and keep it published for up to 10 days more.</p>'."\n";								
		} else {
			$o .= 'Your accommodation is advertsied as wanted from '.$ad['available_date_formatted_2'].'.<br />Adverts expire 10 days after their "Wanted From" date.</p>'."\n";
		  $o .= '<p class="mb0"><a href="'.$_SERVER['PHP_SELF'].'?action=extend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Click here</a> to update your ad and keep it published for up to 10 days more.</p>'."\n";								
		}
		
		$o .= '</div>';
 	} // end if advert expired
	
	// Advert has expired in 1-9 days...	
	if ($ad['days_to_expiry'] <= 0 && !$ad['suspended']) {
	    $o .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:430px;" >';
		// Information
		 $o .= '<span style="font-size:14px;"><strong>Advert expired</strong></strong></span><br />'."\n";					
		//$o .= '<h3>Expired advert?</h3>'."\n";
		if ($post_type == "offered") {
			$o .= 'Your accommodation offered is advertised as being available from '.$ad['available_date_formatted_2'].'.<br />Adverts expire 10 days after their "Available From" date. Your advert has now expired.</p>'."\n";
			$o .= '<p class="mb0"><a href="'.$_SERVER['PHP_SELF'].'?action=extend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Click here</a> to un-expire your advert.</p>'."\n";								
		} else {
			$o .= 'Your wanted accommodation is advertised as being wanted from '.$ad['available_date_formatted_2'].'.<br />Adverts expire 10 days after their "Wanted From" date. Your advert has now expired.</p>'."\n";		
			$o .= '<p class="mb0"><a href="'.$_SERVER['PHP_SELF'].'?action=extend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Click here</a> to automatically update and un-expire your advert.</p>'."\n";					
		}			
			
				
		$o .= '</div>';
 	} // end if advert expired
		
	
	
	if ($ad['suspended']) {
	    $o .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:380px;">';
		// Information
		$o .= '<span class="mb0" style="font-size:14px;"><strong>Advert suspended</strong></strong></span><br />'."\n";					
		$o .= 'You have suspended this advert.<br /><br />'."\n";
		$o .= '<a href="'.$_SERVER['PHP_SELF'].'?action=unsuspend&post_type='.$post_type.'&id='.$ad[$post_type.'_id'].'">Un-suspend your ad</a> to make it visible again to Christian Flatshare visitors.'."\n";
		$o .= '</div>';
	} // end if advert suspended	
	
	if (!$ad['suspended']) {
		// Conditionally allow member to change their days old date > 15 days
		if (!$ad['suspended'] && !$ad['expired'] && ($ad['days_old'] > 15)) {
		
//				$o .= '<div class="mt10" style="background-color:#84E36C;padding:10px;border:1px solid #009900; width:360px;">';
				$o .= '<div class="mt10" style="background-color:#CCFF99;padding:10px;border:1px solid #33CC00; width:360px;">';				
			// Information
			$o .= '<span class="mb0" style="font-size:14px;"><strong>Republish your ad</strong></strong></span><br />'."\n";					
			$o .= 'We notice that you are still using this advert, which is now '.$ad['days_old'].' days old.<br /><br />'."\n";
			$o .= 'Republishing your ad sets its published date to today and the number of times viewed to zero. <br /><br /><a href="'.$_SERVER['PHP_SELF'].'?action=republish&post_type='.	$post_type.'&id='.$ad[$post_type.'_id'].'">Republish your ad with one click!</a>'."\n";
			$o .= '</div>';
//			$o .= '<br />';	
			}    	
		
		// Analyse advert
		// Photos
		$advert_recommendations = 0;
		if ($ad['num_of_photos'] == 0) { 
			$advert_recommendations = $advert_recommendations + 1;
			$recommendations .= '<strong>Add photos:</strong> <a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=6">Click to add photos</a>';
			if ($post_type == "wanted") {
				if (($ad['current_num_males'] + $ad['current_num_females'])==1) { $plural = ''; } else { $plural = 's'; }		
					$recommendations .= '<br />Photos can be a helpful way to introduce the accommodation seeker(s)'.$plural.', and can be fun!<br />'; 
			} else { 
					if ($ad['accommodation_type'] <> "whole place" ) { 
				// Flatshare / family share
					$recommendations .= '<br />Adverts with <b>8 good</b> photos receive a <strong><u>MUCH</u></strong> better response. Photos are essential for showing what your '.$ad['building_type'].' is like, and can help introduce the household too.<br />'; 	
				} else {
				// whole place		
					$recommendations .= '<br />Adverts with <b>8 good</b> photos receive a <strong><u>MUCH</u></strong> better response.<br />Photos are essential for show what your '.$ad['building_type'].' is like. Adding photos is key!<br />'; 		  
			}
			}
		} // end of photos
	
		// Descriptions
			if ($post_type == "offered") {	
				if (str_word_count($ad['accommodation_description'],0)<25) {		
					if ($advert_recommendations != 0) {$recommendations .= '<br />';}
					$advert_recommendations = $advert_recommendations + 1;
					$also = "also ";					
					$recommendations .= '<strong>Accommodation description: </strong><a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=2">Add more details</a><br />Your accommodation description is a quite brief. Providing a fuller description of the '.$ad['building_type'].' (room size, furniture, location, bathrooms) may help. <br />'; 		
				}
				
				if ($ad['accommodation_type']<>"whole place" && str_word_count($ad['household_description'],0)<25) {
					if ($advert_recommendations != 0) {$recommendations .= '<br />';}		
					$advert_recommendations = $advert_recommendations + 1;
						$recommendations .= '<strong>Household description: </strong>
						<a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=3"><style="font-size:11px;">Add more details</a><br />
						Your household description is '.$also.' quite brief. Adding details of your household (jobs/studies, dates, church life, hobbies...) will help better inform others. </strong><br />'; 		
				}			
				
			} else {
			// Wanted 
				if (str_word_count($ad['accommodation_situation'],0)<25) {
					if ($advert_recommendations != 0) {$recommendations .= '<br />';}		
					$advert_recommendations = $advert_recommendations + 1;
						if (($ad['current_num_males'] + $ad['current_num_females'])==1) 
							{ $plural = ''; 
							$plural2 = 'yourself'; 
						} else { 
							$plural = 's'; 
							$plural2 = 'those looking for accommdation'; 
						}
						$recommendations .= '<strong>Accommodation seeker'.$plural.': </strong><a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=3"><style="font-size:11px;">Add more details</a><br />Your have given quite a brief description of '.$plural2.'. Giving a fuller description (interests, occupation/studies, date to move...) will help to better inform those seeing your ad.<br />'; 		
					}
					

			}
		
		// Conditionally show advert recommendations
		if (!$ad['suspended'] && !$ad['expired'] && $advert_recommendations>0) {
				if ($advert_recommendations>1) { $plural = 's'; } else { $plural = ''; }
	//	    $o .= '<div class="mt10" style="background-color:#ACEBA5;padding:10px;border:1px solid #339900;width:400px;">';
			$o .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;width:460px;">';			
			$o .= '<table width="100%" border="0" cellpadding="0">';
			$o .= '<tr>'."\n";		
			$o .= '<td class="mb0" align="left" style="font-size:14px;"><strong></strong></td><td align="right" class="obligatory" style="font-size:14px;"><strong>Recommendation'.$plural.'!</strong></td>'."\n";					
			$o .= '</tr></table>'."\n";
			$o .= '<table width="100%" border="0" cellpadding="0">';
			$o .= '<tr><td>'."\n";		
			$o .= $recommendations;
			$o .= '</td></tr></table>'."\n";
			$o .= '</div>';
			}    		
			$o .= '<br />';		
			$o .= '</table>'; // close table for title, status and messages
			
	
				$o .= '<table  style="padding-left:0px;padding-right:0px;font-size:12px;margin-top:0px" width="100%" border="0" cellpadding="0" cellspacing="0">'."\n"; 
			$o .= '<tr style="height:10px;" class="box_grey">';
			$o .= '<td class="box_light_grey" ></td>'."\n";			
			$o .= '<td><div class="tr"><span class="lgl"></span></div></td>'."\n";					
			$o .= '<td><div class="tr"><span class="lgr"></span></div></td>'."\n";					
			$o .= '<td class="box_light_grey" ></td>'."\n";			
			$o .= '</tr>';				
			
			
			$o .= '<tr >';
			$o .= '<td width="80px"></td>'."\n";			
			$o .= '<td style="padding-left:10px;padding-top:0px;padding-bottom:0px;" class="greyBox" style="width:280px;">';
			
			$o .= '<a title="Share property on Facebook" 
                href="http://www.facebook.com/sharer.php?u='.  urlencode($_SERVER["SERVER_NAME"] . '/details.php?id=' . $ad[$post_type.'_id'] . '&post_type=' . $post_type) .'" target="_blank" class="fb-share">Share on Facebook</a>';
			
			$o .='</td>'."\n";			
			$o .= '<td style="padding-right:12px;padding-top:0px;"  class="greyBox" style="width:180px;" align="right"><span style="font-size:12px;"><strong>Retro Advert Picture</strong></span></td>'."\n";			
			$o .= '<td width="90px"></td>'."\n";			
			$o .= '</tr>';		
			
			// sub-table  Ad controls
			$o .= '<tr>';
			$o .= '<td width="0px"></td>'."\n";			
			$o .= '<td style="padding-left:10px;padding-top:10px;padding-bottom:0px;" width="340px" class="greyBox" valign="top">';
			if ($ad['num_of_photos'] == 0) {
				$o .= 'For best effect <b>add photos first</b>! - <a href="facebook-your-ad.php">more...</a><br /><br />'."\n";			
			} else {
				$o .= '<span align="justify">Share with your Facebook friends.<br /><a href="facebook-your-ad.php">More...</a><br />'."\n";
			}
			$o .= '<br /><br />';		
			// Advert stats
		//	$o .= '<span style="font-size:12px"><strong>Advert Statistics</strong></span><br />'."\n";				
			if ($ad['times_viewed'] == 1) { $times_viewed = 'once'; } else { $times_viewed = $ad['times_viewed'].' times'; }
			$o .= '<p class="mt5 mb10 grey">Advert viewed '.$times_viewed.' in detail<br/>';
			$o .= '<span class="grey">Published on '.$ad['created_date'].'</span></p>'."\n";
			$o .= '</td>';	
			
			
			// THUMBNAIL CELL
			$o .= '<td style="padding-right:12px;padding-top:0px;padding-bottom:4px;"  class="greyBox" valign="top" align="right" width="180px">'."\n";
			$o .= '<p class="mb5 mt0">';
	//	$o .= '<span class="grey" align="right"><strong>this one boring??</strong><span><br />';		
					$o .= '<a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=5"><style="font-size:11px;">Change advert picture</a></p>';		
			$o .= '<div align="right" class="thumbnailContainer">';
			$o .= '<a href="post-'.$post_type.'.php?id='.$ad[$post_type.'_id'].'&step=5"><img src="http://'.SITE.'images/pictures/'.$ad['picture'].'" alt="Change retro advert picture"  border="0"></a><br />';			
			$o .= '</div>';		
			$o .= '</td>'."\n";	
			
			$o .= '<td width="50px">'."\n"; // right spacer columns
			$o .= '</td>'."\n";	
			
			
				$o .= '<tr style="height:10px;" class="box_grey br">';
			$o .= '<td class="box_light_grey"></td>'."\n";			
			$o .= '<td><div class="br"><span class="lgl"></span></div></td>'."\n";					
			$o .= '<td><div class="br"><span class="lgr"></span></div></td>'."\n";					
			$o .= '<td class="box_light_grey" ></td>'."\n";			
			$o .= '</tr>';
				
			// end sub-table
			$o .= '</table>';		
			$o .= '<br />';		
	
			// Matching ads
		 // compute the matching ads
			$adMatches = matchingAds($ad[$post_type.'_id'], $post_type);	
			if ($post_type == "wanted") {	
					$palupMatches = matchingPalups($ad[$post_type.'_id']);	
			}
		 $o .= '<table style="padding-left:10px;padding-right:30px;padding-bottom:0px;" cellpadding="0" cellspacing="0" class="noBorder" width="100%">';			
		 $o .= '<tr>';
		 $o .= '<td style="padding-right:10px;padding-bottom:10px;">';
			if ($post_type == "offered") {	 
		 $o .= '<span style="font-size:12px;"><strong>Adverts matching yours</strong></span><span style="font-size:10px;"></span><br />To help find someone for your '.$ad['building_type'].' you should reply to the wanted accommodation ads. Wanted accommodation adverts detected as matching the accommodation you offer are shown here.'."\n";	
			} else {
		 $o .= '<span style="font-size:12px;"><strong>Adverts matching yours</strong></span><span style="font-size:10px;"></span><br />To help find accommondation you should reply to the offered accommodation ads. Offered accommodation adverts detected as matching your requirements are shown here.'."\n";	
			}
		 $o .= '</td></tr>';		
	
		 $o .= '<tr><td style="padding-right:10px;padding-bottom:0px;">';		
			if ($adMatches > 9) {
				if ($post_type == "wanted") {
					$o .= '<div class="mt0 mb10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;width:470px;">';				
				} else {
					$o .= '<div class="mt0 mb10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;width:480px;">';				
				}
			} elseif ($adMatches > 0 || $palupMatches > 0) {
				if ($post_type == "wanted") {
					$o .= '<div class="mt0 mb10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;width:465px;">';				
				} else {
					$o .= '<div class="mt0 mb10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;width:475px;">';				
				}
			}			
			
			if ($post_type == "wanted") {
				// Matching ads
				if ($adMatches > 0) {		
					if ($adMatches > 1) {
						$o .= 'There are <strong>'.$adMatches.' offered accommodation ads</strong> that might suit you. <a href="display.php?search_type=ad_matches&match_post_type='.$post_type.'&match_ad_id='.$ad[$post_type.'_id'].'" target="_blank">View matching offered ads</a>.<br />'."\n";	
					} else {	
						$o .= 'There is <strong>1 ad offered accommodation ad</strong> that might suit you. <a href="display.php?search_type=ad_matches&match_post_type='.$post_type.'&match_ad_id='.$ad[$post_type.'_id'].'" target="_blank">View matching offered ad</a>.<br />'."\n";	
					}
				}
				// Matching palups
				if ($palupMatches > 0) {			
					if ($palupMatches > 1) {
				 //   $no_palup = ($ad['palup']=="0")?" (although you have not chosen to pal-up yourself)":"";
						$o .= 'There are <strong>'.$palupMatches.' pal-up ads</strong> you might like to consider contacting. <a href="display.php?search_type=palup&match_post_type='.$post_type.'&match_ad_id='.$ad[$post_type.'_id'].'" target="_blank">View pal-up ads</a>'.$no_palup.'.<br />'."\n";	
					} else {		
						$o .= 'There is <strong>1 pal-up ad</strong> you might like to consider contacting. <a href="display.php?search_type=palup&match_post_type='.$post_type.'&match_ad_id='.$ad[$post_type.'_id'].'" target="_blank">View pal-up ad</a>.<br />'."\n";	
					}
				}				
			} else { // Offered
				if ($adMatches > 0) {			
					if ($adMatches > 1) {
						$o .= 'There are <strong>'.$adMatches.' wanted accommodation ads</strong> you might like reply to. <a href="display.php?search_type=ad_matches&match_post_type='.$post_type.'&match_ad_id='.$ad[$post_type.'_id'].'" target="_blank">View matching wanted ads</a>.<br />'."\n";	
					} else {	
						$o .= 'There is <strong>1 wanted accommodation ad</strong> you might like reply to. <a href="display.php?search_type=ad_matches&match_post_type='.$post_type.'&match_ad_id='.$ad[$post_type.'_id'].' " target="_blank">View matching wanted ad</a>.<br />'."\n";	
					}
				}
			}  // if wanted / offerd
			if ($adMatches > 0 || $palupMatches > 0 ) {
					$o .= '<strong>TIP*</strong> use "Hide/Save ad" to hide unsuitable ads shown in the results list.'; 
			} else {
				if ($post_type == "wanted") {
					$o .= '<span class="grey mb0">Currently no matching offered accomodation or pal-up ads are found.<br />Being flexible (and accurate) with the requirements in your ad helps with matching.</span><br />'; 
					} else {
					$o .= '<span class="grey mb10">Currently no matching wanted accommodation ads are found.<br />Being flexible (and accurate) with the requirements in your ad helps with matching.</span><br />'; 
				}
			}		
			if ($adMatches > 0 || $palupMatches > 0) {
				$o .= '</div>';			
			} else {
				$o .= '<br />';				
			}	
		 $o .= '</td></tr>';		
		 $o .= '<tr>';
			 // sub table to allow two colmns
			 $o .= '<table class="mb0 mt0" style="padding-left:10px;padding-right:0px;padding-bottom:0px;" cellpadding="0" cellspacing="0" class="noBorder" width="100%"><tr>';
			if ($post_type == "offered") {	 
				$o .= '<td>Matches are based around location, accommodation type, date, term, sex, and price.<br />It is important you <a href="details.php?id='.$ad[$post_type.'_id'].'&post_type='.$post_type.'" target="_blank">read your ad</a> to check that it is correct.</td>'."\n";	
			} else {
				$o .= '<td>Matches are based around location, accommodation type, date, sex, term and price.<br />It is important you <a href="details.php?id='.$ad[$post_type.'_id'].'&post_type='.$post_type.'" target="_blank">read your ad</a> to check that it is correct.</td>'."\n";	
			}		
			
			$o .= '<td class="mt0 mb0" style="padding-left:10px;padding-right:10px;" align="right" valign="bottom">';			
	//		$o .= '<script type="text/javascript">
        //							addthis_url    = "'.SITE.'details.php?id='.$ad[$post_type.'_id'].'%26post_type='.$post_type.'";
        //						addthis_title  = document.title;  
        //							addthis_pub    = "Christian Flatshare";     
        //					</script><script type="text/javascript" src="http://s7.addthis.com/js/addthis_widget.php?v=12" ></script>';
			$o .= '</td>';							 
			 $o .= '</tr></table>';	  
			// close of sub table
		} // End of "if advert not suspended"
		
	  $o .= '</tr>';	
	  $o .= '</table>';
	  $o .= '<div class="br"><span class="l"></span><span class="r"></span></div>';
  	$o .= '</div>';	// Div to close light grey box
				
		// Set SESSION variable so that results can be passed to the next function
		if ($advert_recommendations>0) {
			$_SESSION['advert_recommendations'] = $advert_recommendations;
		} else { 
			$_SESSION['advert_recommendations'] = null; 
		} 
		
	return $o;
	} // Main ad section
	
	function createDisplayEmails($ad,$post_type,$class,$advert_recommendations) {
    // Create advert table header
	$o = '<table width="100%" border="0" cellpadding="4" cellspacing="0" class="noBorder">'."\n";
		  $class = "even";
  	  $o .= '<tr class="'.$class.'">'."\n";
	$o .= '<td style="padding-left:10px;padding-right:10px;padding-top:5px;padding-bottom:15px;"">'."\n";
			$o .= 'Replies to this advert are shown in <a href="your-account-received-messages.php?action=ad_replies&ad_id='.$ad[$post_type.'_id'].'&post_type='.$post_type.'">Your messages</a>. New message alerts are sent to your registered email address.'."\n";					
		/*	$o .= 'To help get response to your advert:<br />'."\n";		
			$o .= '<span style="float:left;padding-left:10px;padding-bottom:0px;padding-top:0px;">';			
			if ($advert_recommendations>0) {					
				if ($advert_recommendations==1) { $plural = ''; } else { $plural = 's'; }
				$o .= '<li>Follow your advert recommendations. You have <span class="obligatory">'.$advert_recommendations.' recommendation'.$plural.'</span>, see above.</li>'."\n";											
			}
			$o .= '<li>Reply to any matching adverts, shown above.</li>'."\n";										
			$o .= '<li><a href="details.php?id='.$ad[$post_type.'_id'].'&post_type='.$post_type.'" target="_blank">Read your ad</a> to check it is correct.</li>'."\n";									
			$o .= '<li>Share Christian Flatshare with your church and friends!</li>'."\n";	
			$o .= '</span>';									
		*/
		    $o .= '</td>'."\n";
		    $o .= '</tr>'."\n";
		 //   $class = ($class == "even")? "odd":"even";

	// EMAIL REPLIES
	$query = "
			select reply_id
			from cf_email_replies `e`, 
					 cf_users as `u_from`
			where e.to_user_id = '".$_SESSION['u_id']."'
			and   e.from_user_id = u_from.user_id
			and   e.to_post_type = '".$post_type."'
			and   e.to_ad_id   = '".$ad[$post_type.'_id']."'
			and   e.recipient_deleted = 0
			# Recipiant is a scammer, or the message itself is not suppressed
			and     (e.suppressed_replies = 0 
  			       and u_from.suppressed_replies = 0
                               or  (u_from.suppressed_replies = 1
 			         and e.from_user_id =  '".$_SESSION['u_id']."'))
		";
 	  
	  $class = "odd";
		$debug .= debugEvent("Email replies query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$num_rows = mysqli_num_rows($result);
		if ($num_rows > 0) {
  	  $o .= '<tr class="'.$class.'">'."\n";
	  $o .= '<td style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:15px">'."\n";
			if ($num_rows == 1) { $plural = "reply"; } else { $plural = "replies"; }
			$o .= 'You have <a href="your-account-received-messages.php?action=ad_replies&ad_id='.$ad[$post_type.'_id'].'&post_type='.$post_type.'">'.$num_rows.' '.$plural.'</a> to this advert.<br />'."\n";		
	    $o .= '</td>'."\n";
	    $o .= '</tr>'."\n";  		 
		} else {
		  $class = "odd";
  	  $o .= '<tr class="'.$class.'">'."\n";
	    $o .= '<td style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:15px;">'."\n";
			$o .= 'There are no responses to this advert yet.<br />'."\n";		
	    $o .= '</td>'."\n";
	    $o .= '</tr>'."\n";
		} // IF results

  	 $o .= '</table> '."\n"; // email reply table
	   $o .= '<br /><br />';				
		
		return $o;
				
	}  // createDispalyEmails
	
	
	
	// If another page has called this page and we need to report on the result of an action:
	if (isset($_REQUEST['report'])) {
		switch($_REQUEST['report']) {
			case "deletionSuccess":
				$msg = '<p class="success">Your ad has been deleted successfully.</p>
				<p><strong>Note: </strong>If you have finished using CFS for the time being you can <a href="your-suspend-account.php">suspend your account</a>.</p><div class="fb-like-container-long"><p>Was you\'re advert successful? Recommend us on Facebook.</p><div class="fb-like" data-href="http://www.christianflatshare.org" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-action="recommend"></div></div><br />';
				break;
			case "deletionSuccessThankyou":
				$msg = '<p class="success">Your ad has been deleted successfully<br />';
				$msg .= 'Thank you for giving feedback about Christian Flatshare</p><div class="fb-like-container-long"><p>Was you\'re advert successful? Recommend us on Facebook.</p><div class="fb-like" data-href="http://www.christianflatshare.org" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-action="recommend"></div></div><br/>';
				break;				
			case "deletionFailure":
				$msg = '<p class="error">An error occured when deleting ad.</p>';
				break;	
			case "updateSuccess":
				$msg = '<p class="success">Your ad has been updated successfully</p>';
				break;
			case "updateFailure":
				$msg = '<p class="error">An error occured when updating your ad.</p>';
				break;				
			case "emailDeletionSuccess":
				$msg = '<p class="success">The email has been deleted</p>';
				break;
			case "emailDeletionFailure":
				$msg = '<p class="error">An error occured when deleting your email.</p>';
				break;					
      case "warning_ack":
        $msg = '<p class="success">Scam advice acknowleged</p>';
        break;
					
		}
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
    <script language="javascript" type="text/javascript" src="scripts/share.js"></script>
<!-- InstanceBeginEditable name="head" -->
	<script language="javascript" type="text/javascript" src="includes/moodalbox/moodalbox.js"></script>
	<script language="javascript" type="text/javascript">

		function hideMessage() {
		
			$('new_ad').style.display = "none";
			$('new_ad_close_button').style.display = "none"; 
		
		}
		
	</script>
	<link href="includes/moodalbox/moodalbox.css" rel="stylesheet" type="text/css" />
	<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
    <!-- FACEBOOK JS SDK -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=241207662692677";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<div class="cl" id="cl">
		<?php if ($suspended) { ?>
			<h1 class="mt0 mb5">Your account is suspended</h1>		
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-left:15px;padding-bottom:15px;padding-top:10px">
				 <div class="mt10" style="width:400px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
			   <p class="mt0 mb0"><a href="your-suspend-account.php?action=unsuspend">Click here to un-suspend your account</a> &nbsp;<img src="images/photo-loader.gif" width="16" height="16" id="photo_loader" style="display:none;"/>    		</p>
				</div>
				</td>
			</tr>				
			
			<tr>
				<td style="padding-left:15px;">
				<p class="mb10 mt5">Suspending your account does three things:</p>
				<li>Suspends any adverts you have published</li>
				<li>Stops you from being alerted if someone replies to a message you have sent them</li>
				<li>Informs anyone replying to messages you have sent them that your account is suspended and that you will not be alerted to the arrival of their new message. Their message will still be sent to you, shown in Your messages.</li>
				</td>
			</tr>
			<tr>
				<td style="padding-left:15px;padding-bottom:5px;padding-top:15px">
				While your account is suspended you cannot:
				</td>
			</tr>			
			<tr>
				<td style="padding-left:15px">
				<li>Reply to messages or adverts </li>
				<li>Post new adverts</li>
				</td>
			</tr>		
			
			<tr>
				<td style="padding-left:15px;padding-bottom:5px;padding-top:25px">
				<b>However</b>, while your account is suspended you can still:
				</td>
			</tr>			
			<tr>
				<td style="padding-left:15px">
				<li>Share Christian Flatshare with your friends and church leadership</li>
				<li>Print this <a href="http://<?php print SITE?>A4%20CFS%20Poster.pdf"  target="_blank">lovely poster</a> for your church notice board</li>
				<li>Pray for the ministry Christian Flatshare and those other trying to connect with the local church through it</li><br />Thank you.
				</td>
			</tr>						
						
			</table>		
		
		<?php } else { ?>
			<!-- <h1 class="mt0">Your ads in <?php print (in_array(strtolower(substr($userCountry['name'], 0, 1)), array('u'))) ? 'the ' : ''; print $userCountry['name']; ?></h1> -->
			<h1 class="mt0">Your adverts</h1>
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
			<?php } ?>
			
			<?php print $remove_msg?>
			<?php print $s?>			
			
			<!-- advert summary -->
			<?php 
			$query = "SELECT count(*) 
			 	        FROM   cf_wanted
								WHERE  user_id = ".$_SESSION['u_id']."
								AND    expiry_date >= NOW()
								AND    published = 1
								AND    suspended = 0
                                AND    country = '". $userCountry['iso'] ."'";
			$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
			$num_wanted_published = $row[0];
			if ($num_wanted_published > 0) {
				if ($num_wanted_published > 1) {
					// Plural case
					$num_wanted_published = $num_wanted_published.' published wanted ads';
				} else { 
					$num_wanted_published = $num_wanted_published.' published wanted ad';
				}
			} else { 
				$num_wanted_published = FALSE;
			}
			
			$query = "SELECT count(*) 
			 	        FROM   cf_wanted
						WHERE  user_id = ".$_SESSION['u_id']."								
				 	        AND    expiry_date < NOW()
						AND    published = 1								
						AND    suspended = 0";
                                              //  AND    (country = '". $userCountry['iso'] ."' or country = '')";

			$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
			$num_wanted_expired = $row[0];
			if ($num_wanted_expired > 0) {
				if ($num_wanted_expired > 1) {
					// Plural case
					$num_wanted_expired = $num_wanted_expired.' expired wanted ads';
				} else { 
					$num_wanted_expired = $num_wanted_expired.' expired wanted ad';
				}
			} else { 
				$num_wanted_expired = FALSE;
			}
			
			$query = "SELECT count(*) 
			 	        FROM   cf_wanted
								WHERE  user_id = ".$_SESSION['u_id']."		
								AND    published = 1														
								AND    suspended = 1
                                AND    country = '". $userCountry['iso'] ."'";
			$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
			$num_wanted_suspended = $row[0];
			if ($num_wanted_suspended > 0) {
				if ($num_wanted_suspended > 1) {
					// Plural case
					$num_wanted_suspended = $num_wanted_suspended.' suspended wanted ads';
				} else { 
					$num_wanted_suspended = $num_wanted_suspended.' suspended wanted ad';
				}
			} else { 
				$num_wanted_suspended = FALSE;
			}			
			
			$query = "SELECT count(*) 
			 	        FROM   cf_offered
								WHERE  user_id = ".$_SESSION['u_id']."
								AND    expiry_date >= NOW()
								AND    published = 1								
								AND    suspended = 0
                                AND    country = '". $userCountry['iso'] ."'";
			$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
			$num_offered_published = $row[0];
			if ($num_offered_published > 0) {
				if ($num_offered_published > 1) {
					// Plural case
					$num_offered_published = $num_offered_published.' published offered ads';
				} else { 
					$num_offered_published = $num_offered_published.' published offered ad';
				}
			} else { 
				$num_offered_published = FALSE;
			}	
			
			$query = "SELECT count(*) 
			 	        FROM   cf_offered
								WHERE  user_id = ".$_SESSION['u_id']."								
								AND    expiry_date < NOW()
								AND    published = 1								
								AND    suspended = 0
                                AND    country = '". $userCountry['iso'] ."'";						
			$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
			$num_offered_expired = $row[0];
			if ($num_offered_expired > 0) {
				if ($num_offered_expired > 1) {
					// Plural case
					$num_offered_expired = $num_offered_expired.' expired offered ads';
				} else { 
					$num_offered_expired = $num_offered_expired.' expired offered ad';
				}
			} else { 
				$num_offered_expired = FALSE;
			}			
			
			$query = "SELECT count(*) 
			 	        FROM   cf_offered
								WHERE  user_id = ".$_SESSION['u_id']."								
								AND    published = 1								
								AND    suspended = 1
                                AND    country = '". $userCountry['iso'] ."'";
			$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
			$num_offered_suspended = $row[0];
			if ($num_offered_suspended > 0) {
				if ($num_offered_suspended > 1) {
					// Plural case
					$num_offered_suspended = $num_offered_suspended.' suspended offered ads';
				} else { 
					$num_offered_suspended = $num_offered_suspended.' suspended offered ad';
				}
			} else { 
				$num_offered_suspended = FALSE;
			}			
	
        $and = ' and ';
	if ($num_wanted_expired)  { $o = $num_wanted_expired; } else { $o = FALSE; } 
	if ($num_offered_expired) {
		if ($o) { $o = $num_offered_expired.$and.$o; $and = ', '; }
			else  { $o = $num_offered_expired; 
		}
        }
	if ($num_offered_suspended) {
		if ($o) { $o = $num_offered_suspended.$and.$o; $and = ', '; }
			else  { $o = $num_offered_suspended; 
		}
	}	
	if ($num_offered_published) {
		if ($o) { $o = $num_offered_published.$and.$o; $and = ', '; }
			else  { $o = $num_offered_published; 
		}
	}						
	if ($num_wanted_suspended) {
		if ($o) { $o = $num_wanted_suspended.$and.$o; $and = ', '; }
			else  { $o = $num_wanted_suspended; 
		}
	}	
	if ($num_wanted_published) {
		if ($o) { $o = $num_wanted_published.$and.$o; $and = ', '; }
			else  { $o = $num_wanted_published; 
		}
	}				
		
	if ($o) {	
		$o = 'You have '.$o.'.<br /><br />';		
	} else {
		$o = 'You have no adverts published.<br /><br />';
	}
	echo $o;	?>
	

			<form name="updateForm" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
						
			<?php if ($offered_ads && $hide_photo_warning) { 
				$welcome_msg = FALSE;				
				
					$class = "odd";
					foreach($offered_ads as $id => $ad) {
						echo createLargeSummary($ad,"offered",$class);
						if (!$ad['suspended']) {
							echo createDisplayEmails($ad,"offered",$class,$_SESSION['advert_recommendations']);
						} else {
						  echo '<p class="mb0 mt0">&nbsp;</p>';
						}
						$class = ($class == "even")? "odd":"even";
					}
				
				?>
		<!-- </table> -->
			<?php } ?>
			
			
			<?php if ($wanted_ads && $hide_photo_warning) { 
			  $welcome_msg = FALSE;				
						
					$class = "odd";
					foreach($wanted_ads as $id => $ad) {
						echo createLargeSummary($ad,"wanted",$class);
						if (!$ad['suspended']) {						
							echo createDisplayEmails($ad,"wanted",$class,$_SESSION['advert_recommendations']);						
						} else {
						  echo '<p class="mb0 mt0">&nbsp;</p>';
						}							
						$class = ($class == "even")? "odd":"even";
					}
				
				?>
			</table>
			<?php } ?>
			
			</form>
			<?php if ($welcome_msg) { ?>
		  <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;width:560px;">
				<h2 class="mt0 mb0">About Christian Flatshare</h2>
	      <p class="mt10 mb20">Christian Flatshare is a non-profit organisation, helping accommodation seekers connect with the local church.<br />
	      Creating an informative (and fun) advert will help you to get the best response from Christian Flatshare.</p>
              <p class="mt15 mb0">
	        <b>** Offering accommodation?</b><br />Place an Offered accommodation (<u>with pictures!</u>), and reply to Wanted ads.</P>
              <p class="mt10 mb10">
	        <b>** Looking for accommodation?</b><br />Place a Wanted accommodation ad, and reply to Offered ads.<br />
                (<b>Note:</b> <i>some people with accommodation to offer only browse the Wanted ads)</i><br /> 
	      <br /></p>
	      <p class="mt0 mb10">Sharing Christian Flatshare with your friends and church leadership can help others to connect with their local church community. On behalf of thousands who have used Christian Flatshare, we are grateful to those who have taken initiative to share Christian Flatshare with others. </p>
	      <p align="justify" class="mb0 mt0"><strong>Please enjoy Christian Flatshare</strong></p>
	    </div>
	    <?php } ?>				
		<?php } ?> <!-- End IF for account suspended -->
		</div>
		
		<div class="cs" style="width:20px; height:270px;"><!---->
		</div>
		
		<div class="cr">
			<?php print $theme['side']; ?>
            <div class="fb-like-container">
                <p>Used Christian Flatshare successfully?</p>
                <div class="fb-like" data-href="http://www.christianflatshare.org" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-action="recommend"></div>
            </div>
				<p class="mt20 mb10" align="right">
				<span class="grey">Donate to help support Christian Flatshare.</span> </p>
						
<p class="mt5 mb0">				
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="5647421">
<input type="image" align="right" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
				</p>						
						
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
