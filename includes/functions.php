<?php

use CFS\International\CFSInternational;
use CFS\Mailer\CFSMailer;
use CFS\GeoEncoding\CFSGeoEncoding;


// Connect to the database
function connectToDB() {
    $GLOBALS['mysql_conn'] = mysqli_connect(DB_HOST, DB_USER_NAME, DB_PASSWORD, DB_NAME);
    if (mysqli_connect_errno($GLOBALS['mysql_conn'])) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
}

function nextPreviousAd($post_type,$id) {
	// First, if we DON'T have the &t variable set on the $_GET string, we clear the result set
	if (!$_GET['t']) { unset($_SESSION['result_set']); }
	
	if ($_SESSION['result_set']) {
	  
		$res = $_SESSION['result_set'];
		$res_count = count($res);
		$pos = array_search($id,$res);
		$debug .= debugEvent("Pos:",$pos);
		// Get previous link
		if ($pos != 0) {
			$res_prev = ($pos - 1);
			$res_prev = $res[$res_prev];
		}
		// Get next link
		if ($pos != ($res_count - 1)) {
			$res_next = $res[$pos + 1];
			
		}
		// Create the html for the results navigator (next / prev links)
		
		$res  = '<table cellpadding="0" cellspacing="0" border="0" align="right">'."\n";
		$res .= '<tr>'."\n";
		if ($res_prev) {
			$res .= '<td width="16">';
			$res .= '<a href="details.php?id='.$res_prev.'&post_type='.$post_type.'&t=1#detail">';
			//$res .= '<img src="images/arrow_left.gif" alt="" width="16" height="17" border="0" />';
			$res .= 'Previous';
			$res .= '</a>';
			$res .= '</td>'."\n";
		}
		
		$res .= '<td style="padding:0px 10px;">Showing ad <strong>'.($pos+1).'</strong> of <strong>'.$res_count.'</strong></td>'."\n";
		
		if ($res_next) {
			$res .= '<td width="16">';
			$res .= '<a href="details.php?id='.$res_next.'&post_type='.$post_type.'&t=1#detail">';
			//$res .= '<img src="images/arrow_right.gif" alt="" width="16" height="17" border="0" />';
			$res .= 'Next';
			$res .= '</a>';
			$res .= '</td>'."\n";
		}
		$res .= '</tr>'."\n";
		$res .= '</table>'."\n";
		
		
	} // if session results set
	
	if (!$_GET['t']) {
		return '<a href="#" onclick="history.go(-1);">Return to the previous page</a>';
	}
  else {
		return $res;
	}
}	
	
// Initialise variables coming to the page from either POST (default),
// GET or both (REQUEST).
// usage: $temp = init_var("temp","GET","a default valuew");
function init_var($variable, $array_to_check = "POST", $default = NULL) {
	if (!$variable) {
		return false;
	}

	// Establish where we're looking for the var	
	switch($array_to_check) {
		case "POST":
			$array_to_check = $_POST;
			break;
		case "GET":
			$array_to_check = $_GET;
			break;
		case "REQUEST":
			$array_to_check = $_REQUEST;
			break;
	}
	
	// Perform the check
    if (isset($array_to_check[$variable])) {
        return trim($array_to_check[$variable]);
    }
    else {
        return $default;
    }
}
	
	// Sharing Christian Flatshare box...
	function sharingCFS() {
		$hour = date("g");
    if ($hour == 1 || $hour == 4 || $hour == 7 || $hour == 10) { 
	$s = '<div class="mt10" style="background-color:#97E6FF;padding:10px;border:1px solid #1478C2; width:215px;">';
    } elseif ($hour == 2 || $hour == 5 || $hour == 8 || $hour == 11) { 			
	$s .= '<div class="mt10" style="background-color:#E2F997;padding:10px;border:1px solid #006600; width:215px;">';
    } else { 
	$s .= '<div class="mt10" style="background-color:#FFCECE;padding:10px;border:1px solid #FF6F6F; width:215px;">';
    } 
	$s .= '<p align="center" class="mb0 mt0 style5">Sharing Christian Flatshare</p>
			<p align="center" class="mt0 mb10">Please share Christian Flatshare by:</p>
			<p class="mt0 mb5">1. Displaying this <a href="http://'.SITE.'A4%20CFS%20Poster.pdf" target="_blank">poster</a> in your church</p>
			<p class="mt0 mb5">2. Telling your church leadership</p>
			<p class="mt0 mb5">3. Linking your church or personal website to<br />&nbsp;&nbsp;&nbsp; Christian Flatshare</p>			
			<p class="mt0 mb5">4. Sharing CFS with your uni CU</p>									
			<p class="mt0 mb5">5. <b>Church leaders</b>: requesting your church<br />&nbsp;&nbsp;&nbsp; be added to the <a href="http://'.SITE.'churches-using-cfs.php" target="_blank">Church Directory</a> and <br />&nbsp;&nbsp;&nbsp; accommodation maps, with links<br />&nbsp;&nbsp;&nbsp; to your church website</p>						
			</div>';
	return $s;
	}	
	
	// This query creates a list of all adverts that the member repling to the advert has on CFS
	// It is used in the reply.php to show the adverts that the responder has placed on CFS
	function createSummaryForAllAds($user_id, $includeBulletsandPara=TRUE, $raw=FALSE) {
			$summary = null;
            $ads = array();
    	    if ($includeBulletsandPara) { $summary .= '<p>'; }	
			$query =  "select o.*, 
			           (CASE IFNULL(o.town_chosen,'')
	 			        WHEN '' THEN j.town 
			            ELSE o.town_chosen
				        END) as town
  			           from cf_offered o
					   left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 					   
                       where o.user_id = ".$user_id."
                       and o.expiry_date > curdate()
					   and o.suspended = 0						   
					   and o.published = 1";	
			$debug .= debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);

			 if ($includeBulletsandPara) {	$summary .= '<strong><li> '; }
             $ads[] = array('title' => getAdTitle($ad, "offered", FALSE), 'url' => getAdURL($ad, "offered"));
			  $summary .= getAdTitle($ad,"offered",TRUE, TRUE, TRUE);
			 if ($includeBulletsandPara) { $summary .= '</li></strong>'; } else { $summary .= '<br />'; }
			}
			
			$query =  "select w.*
							   from cf_wanted w
	               where w.user_id = ".$user_id."
                 and w.expiry_date > curdate()
							   and w.suspended = 0					   
                 and w.published = 1";
			$debug .= debugEvent("Selection query",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
            
            
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
			 
			 if ($includeBulletsandPara) { $summary .= '<strong><li>'; }
             $ads[] = array('title' => getAdTitle($ad, "wanted", FALSE), 'url' => getAdURL($ad, "wanted"));
			  $summary .= getAdTitle($ad,"wanted",TRUE, TRUE, TRUE);
			 if ($includeBulletsandPara) { $summary .= '</li></strong>'; }	else { $summary .= '<br />'; }
			}
  	    if ($includeBulletsandPara) { $summary .= '</p>'; }	
		if ($summary == '<p></p>') {
			$summary = '<strong>No adverts shown.</strong></p>';
		}
        
        if ($raw === TRUE) {
            return $ads;
        }
        
		return $summary;
	  }


	// Show for a given user the ads they have EVER published.
	// This is used in the ADMIN, Memeber edit area only
	function createSummaryForAllAdsAll($user_id, $includeBulletsandPara=TRUE) {
			$summary = null;
    	    if ($includeBulletsandPara) { $summary .= '<p>'; }	
			$query =  "select o.*, 
			           (CASE IFNULL(o.town_chosen,'')
	 			        WHEN '' THEN j.town 
			            ELSE o.town_chosen
				        END) as town,
								deleted, suspended
  			           from cf_offered_all o
					   left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 					   
                       where o.user_id = ".$user_id."
					   and o.published = 1";	
			$debug .= debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
			 
			 if ($ad['deleted'] == 1) {
			 	 $state = '<span class="obligatory">Deleted</span>';
			 } elseif ($ad['suspended'] == 1) {
			 	 $state = 'Suspended';
			 }
			 if ($includeBulletsandPara) {	$summary .= '<strong><li> '; } 
			 $summary .= getAdTitle($ad,"offered",TRUE, TRUE, TRUE).' '.$state;
			 if ($includeBulletsandPara) { $summary .= '</li></strong>'; } else { $summary .= '<br />'; }
			}
			
			$query =  "select w.*,								
								 deleted, suspended
							   from cf_wanted_all w
	               where w.user_id = ".$user_id."
                 and w.expiry_date > curdate()
                 and w.published = 1";
			$debug .= debugEvent("Selection query",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
			 
			 if ($ad['deleted'] == 1) {
			 	 $state = '<span class="obligatory">Deleted</span>';
			 } elseif ($ad['suspended'] == 1) {
			 	 $state = 'Suspended';
			 }			 
			 
			 if ($includeBulletsandPara) { $summary .= '<strong><li>'; }
			  $summary .= getAdTitle($ad,"wanted",TRUE, TRUE, TRUE).' '.$state;
			 if ($includeBulletsandPara) { $summary .= '</li></strong>'; }	else { $summary .= '<br />'; }
			}
  	    if ($includeBulletsandPara) { $summary .= '</p>'; }	
		if ($summary == '<p></p>') {
			$summary = '<strong>No adverts shown.</strong></p>';
		}			
		return $summary;
	  }
			
			

	// Summary of lastest ads for front page:
	// - One ad per member
	// - Picture ads only
	// - Change on the hour
	// - Ads posted in the last three days
	function createSummaryForNewAds() {
	        // Generate order by clause based on hour of the day
			// This is done to give freshness, but not randomness to the display
			$minutes = date (i);  // 00-59
			$quarter = round($minutes/15);
			switch ($quarter) {
				case 0: $sort = "created_date"; break;
				case 1: $sort = "user_id"; break;				
				case 2: $sort = "price_pcm"; break;
				case 3: $sort = "available_date"; break;
				default: $sort = "available_date"; break;								
	        }

			$summary = null;
			$query =  "SELECT o.*, 
					       (CASE IFNULL(o.town_chosen,'')
					        WHEN '' THEN j.town 
					        ELSE o.town_chosen
					        END) as town
						FROM cf_offered o
							left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 					   
						WHERE o.created_date > date(now() - INTERVAL 7 DAY)
						AND o.expiry_date > curdate()
						AND o.published = 1
						AND o.suspended = 0						
						AND EXISTS (SELECT 'x'
								    FROM   cf_photos as `p`
								    WHERE  o.offered_id = p.ad_id
								    AND    p.post_type = 'offered')
						ORDER BY o.".$sort." DESC
						LIMIT 6";	
			$debug .= debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			$class = "trOdd";
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
			 $summary .= '<tr class="'.$class.'"><td>'.getAdTitle($ad,"offered",TRUE, TRUE, TRUE).'</td></tr>';				 
	         $class = ($class == "trOdd")? "trEven":"trOdd";			 
			}
			
				$query = "SELECT w.*
					  FROM   cf_wanted w
					  WHERE w.created_date > date(now() - INTERVAL 15 DAY)
					  AND w.expiry_date > curdate()
					  AND w.published = 1
				      AND w.suspended = 0					  
					  AND EXISTS (SELECT 'x'
			 				      FROM   cf_photos as `p`
							      WHERE  w.wanted_id = p.ad_id
							      AND    p.post_type = 'wanted')
					  ORDER BY w.".$sort." DESC							  
					  LIMIT 5";
			$debug .= debugEvent("Selection query",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
//			 $summary .= '<a>'.getAdTitle($ad,"wanted",TRUE, TRUE).'<a><br />';				 
			 $summary .= '<tr class="'.$class.'"><td>'.getAdTitle($ad,"wanted",TRUE, TRUE, TRUE).'</td></tr>';				 
	         $class = ($class == "trOdd")? "trEven":"trOdd";		
			}
		return $summary;
	  }



	// List all saved ads
	function createSummaryofSavedAds($user_id) {
			$summary = null;
     	    $summary .= '<p>';
			$query =  "select o.*, 
			           (CASE IFNULL(o.town_chosen,'')
	 			        WHEN '' THEN j.town 
			            ELSE o.town_chosen
				        END) as town
  			           from cf_offered o,
					        cf_saved_ads s
					   left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 					   
                       where o.expiry_date > curdate()
					   and o.published = 1
					   and s.post_type = 'offered'
					   and s.ad_id = o.offered_id
					   and s.active = 1
					   and s.user_id = ".$user_id;	
			$debug .= debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
			 $summary .= '<strong><li>      '.getAdTitle($ad,"offered",TRUE, TRUE).'</li></strong>';	
			}
			
			$query =  "select w.*
					   from cf_wanted w, 
					        cf_saved_ads s
	                   where w.expiry_date > curdate()
                       and w.published = 1
					   and s.post_type = 'wanted'
					   and s.ad_id = w.wanted_id
					   and s.active = 1					   
					   and s.user_id = ".$user_id;
			$debug .= debugEvent("Selection query",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}
			for($i=0;$i<$num_results;$i++) {
			 $ad = mysqli_fetch_assoc($result);
			 $summary .= '<strong><li>      '.getAdTitle($ad,"wanted",TRUE, TRUE).'</li></strong>';	
			}
		$summary .= '</p>';
		if ($summary == '<p></p>') {
			$summary = '<strong>No adverts shown.</strong></p>';
		}			
		return $summary;
	  }
			
	function createBedroomSummary($ad) {
					// Offered line 1:  and Details
					// 1 double bedroom
					// 2 double bedrooms
					// 1 single bedroom
					// 4 bedrooms (2 doubles)
					if ($ad['room_share'] == 1) { 
						$line_one = '<strong>1</strong> shared double bedroom';
					} else {
					
						$single_rooms = $ad['bedrooms_available'] - $ad['bedrooms_double'];
						$double_rooms = $ad['bedrooms_double'];
						
						if ($single_rooms > 0 && $double_rooms > 0) {		
							// 4 bedrooms (2 doubles)	
							$line_one = '<strong>'.$ad['bedrooms_available'].'</strong> bedroom';	// Number of available bedrooms
							if ($ad['bedrooms_available'] > 1) { $line_one .= 's '; }				// Plural "s" (if needed)							
							$line_one .= '('.$double_rooms.' double';
							if ($double_rooms > 1) { $line_one .= 's'; }				// Plural "s" (if needed)							
							$line_one .= ')';
						} elseif ($double_rooms > 0) {		
							// 2 double bedrooms
							$line_one = '<strong>'.$double_rooms.'</strong> double bedroom';	// Number of available bedrooms
							if ($double_rooms > 1) { $line_one .= 's'; }				// Plural "s" (if needed)			
						} elseif ($single_rooms > 0) {		
							// 2 single bedrooms
							$line_one = '<strong>'.$single_rooms.'</strong> single bedroom';	// Number of available bedrooms
							if ($single_rooms > 1) { $line_one .= 's'; }				// Plural "s" (if needed)																		
					 	}
					}
					return $line_one;
			}



	
	// Creates a "summary" representation of an ad.
	function createSummaryV2($ad,$type,$class="",$hideLinks=FALSE,$churchDetails=FALSE,$includeToken=FALSE,$friends=NULL) {

		// Add the number of photos before the "report link"
		/*
		if ($ad['photos']) {
			$tempName = 'photo_'.$ad['photos'].'_'.($class=="odd"?"grey":"white").'.gif';
			$toReturn .= '<div class="photo_num" style="position:absolute;"><img src="images/'.$tempName.'" align="absmiddle" border="0" /></div>';
		}
		*/
        
        // Setup new international instance because ads are based on
        // the ad country rather than the user country
        $CFSIntlAd = new CFSInternational();
        $CFSIntlAd->setAppCountry($ad['country']);
	
		$toReturn = '<table cellpadding="0" cellspacing="10" border="0" width="100%" class="'.$class.'">';
		$toReturn .= '<tr>';
		
		// Left column (contains the thumbnailContainer)
		$toReturn .= '<td class="ps_left">'."\n";
		// Thumbnail image
		$toReturn .= '<div class="ps_thumbnailContainer">'."\n";
			$toReturn .= '<div class="thumbnailContainer">'."\n";
			$toReturn .= '<a href="/details.php?id='.$ad[$type.'_id'].'&post_type='.$type;
			if ($includeToken == TRUE) { $toReturn .= '&t=1'; }
			$toReturn .= '">';
			$toReturn .= '<img src="http://'.SITE.'images/pictures/'.$ad['picture'].'" border="0">';
			$toReturn .= '</a>';
			$toReturn .= '</div>'."\n";
		$toReturn .= '</div>'."\n";	
		$toReturn .= '</td>';
		
		// Right column (contains everything else)
		$toReturn .= '<td class="ps_right">'."\n";
		
			$toReturn .= '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="ps_adTitle">';
			$toReturn .= '<tr>';
			// Ad title
			$toReturn .= '<td class="ps_adTitle">';
			$toReturn .= getAdTitle($ad,$type,TRUE,FALSE,FALSE,$includeToken)."\n";
						
			// Photos
			/*
			if ($ad['photos']) { 
				$tempName = 'photo_'.$ad['photos'].'_'.($class=="odd"?"grey":"white").'.gif';
				$toReturn .= '<img src="images/'.$tempName.'" align="absmiddle" border="0" />';
			}
			*/
			$toReturn .= '</td>';
			// Ad age
			/*
			$toReturn .= '<td class="ps_adAge">('.$ad['ad_age'].($ad['ad_age']==1?" day old":" days old").')</td>';
			*/
			
			// Determine if ad replied to
        if (isset($_SESSION['u_id'])) {			
      		$number_of_replies = numberOfReplies($type, $ad[$type.'_id']);
				 				 
			   if ($number_of_replies > 0) { $number_of_replies = ' ('.$number_of_replies.')'; } else { $number_of_replies = ''; }
			} else {
			   $number_of_replies = FALSE;			
			}

			// Save ad 
			if (!$hideLinks) {
				// Only show the adSave button if we're not on map.php
				$toReturn .= '<td class="ps_adSave">';
				if (!strpos($_SERVER['PHP_SELF'],"map.php")) {
          
          $userId = getUserIdFromSession();
          
					if ($userId) {
						$toReturn .= '<a class="save_ad_button" id="save_'.$type.'_ad_'.$ad[$type.'_id'].'" href="#">';
						if ($ad['active']==1) {
							$toReturn .= '<img src="images/button_saved_ad.gif" height="17" border="0" align="absmiddle" />';		
						} elseif ($ad['active']==2) {
							$toReturn .= '<img src="images/button_hidden_ad.gif" height="17" border="0" align="absmiddle" />';
						} else {
							$toReturn .= '<img src="images/button_hidesave_ad.gif" height="17" border="0" align="absmiddle" />';						
						}
					$toReturn .= '</a>';
						$toReturn .= '</a>';
					} else {
					$toReturn .= '<a href="login.php?msg=save_ads" style="float:right;"><img src="images/button_hidesave_ad.gif" height="17" border="0" align="absmiddle" /></a>';							
					}
				}
				$toReturn .= '</td>';
			}
			
			$toReturn .= '</tr>';
			$toReturn .= '</table>';
			
			// Container for ad_details and ad_links
			//$toReturn .= '<div>';
			
			// Ad details
			//$toReturn .= '<div class="ps_details">'."\n";
			$toReturn .= '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
			$toReturn .= '<tr>';
			$toReturn .= '<td class="ps_details">';
			
			// If the churchDetails variable has been specified, we need to show details of the church
			// instead of the accommodation
			if ($churchDetails) {
			
				// Ad details
				$toReturn .= '<table cellpadding="0" cellspacing="0" border="0">'."\n";
					// Contact name
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Contact name:</td>'."\n";
					$toReturn .= '<td>'.(isset($_SESSION['u_id'])?$ad['contact_name']:'<span class="grey">login to see contact details </span><a href="#" class="tooltip" id="contact_details">(?)</a>');
			//		if ($ad['contact_phone'] && isset($_SESSION['u_id'])) { $toReturn .= ', '.$ad['contact_phone']; }
			//		else { $toReturn .= '<span class="grey"> (login to show phone number)</span>'; }					
					if ($ad['contact_phone']&&isset($_SESSION['u_id'])) { $toReturn .= ', '.$ad['contact_phone']; }			
					$toReturn .= '</td>'."\n";
					$toReturn .= '</tr>'."\n";
					// Church name
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Church name:</td>'."\n";
					$toReturn .= '<td>';
					$toReturn .= ($ad['church_attended'])? $ad['church_attended'] : '<span class="grey" title="Church name was not recorded but it may be included in ad text">not recorded for whole place ads</span>';
					$toReturn .= '</td>'."\n";
					$toReturn .= '</tr>'."\n";
					// Church URL
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Church url:</td>'."\n";
					$toReturn .= '<td>';
//					$toReturn .= '<a href="http://'.$ad['church_url'].'" target="_blank">'.$ad['church_url'].'</a>';
					$toReturn .= clickable_link(stripslashes($ad['church_url']));					
					$toReturn .= '</td>'."\n";
					$toReturn .= '</tr>'."\n";
					// Last logged in
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Last logged in:</td>'."\n";
					$toReturn .= '<td>';
					if ($ad['last_login_days']==0) {
					    $toReturn .= 'Today';
						} else if ($ad['last_login_days']==1) {
					    $toReturn .= 'Yesterday';
						} else {
					    $toReturn .= $ad['last_login_days'].' days ago';	
						}		
					// Ad age
					if ($ad['ad_age'] > 15) {
					  $toReturn .= '<span class="grey"> (advert more than 15 days old)</span>';
			//		  $toReturn .= '';			
					  } elseif ($ad['ad_age']==0) {
						  $toReturn .= '<span class="grey"> (advert created today)</span>';					  
					  } elseif ($ad['ad_age']==1) {
						  $toReturn .= '<span class="grey"> (advert created yesterday)</span>';					  
					  }else {
					  $toReturn .= '<span class="grey"> (advert '.$ad['ad_age'].' days old)</span>';
					}
					$toReturn .= '</td>';
					$toReturn .= '</tr>'."\n";						
				$toReturn .= '</table>';
			
			} else {
			
				if ($type == "offered") {
				
					$toReturn .= '<table cellpadding="0" cellspacing="0" border="0">'."\n";
		
					$line_one = createBedroomSummary($ad);
					if ($ad['accommodation_type'] == "whole place") {
				 	 $NumberOfAdPalups = getNumberOfAdPalups($ad['offered_id']);
					 if ($NumberOfAdPalups > 0) { $line_one .= '<span style="font-size:11px">  ('.$NumberOfAdPalups.' Pal-Up'; }
					 if ($NumberOfAdPalups > 1) { $line_one .= 's'; } 
					 if ($NumberOfAdPalups > 0) { $line_one .= '!)'; }
					 $line_one .= '</span>';
					}
					
					
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Room(s):</td>'."\n";
					$toReturn .= '<td>'.$line_one.'</td>'."\n";
					$toReturn .= '</tr>'."\n";
					
					// Offered line 2: 
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Monthly price:</td>'."\n";
					if ($ad['accommodation_type'] == "whole place" && $ad['room_letting'] == "1") {
						$toReturn .= '<td><strong>' . $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') . '</strong> whole place <span class="grey" style="font-size:11px">(letting by bedroom possible)</span></td>'."\n";
					} elseif ($ad['accommodation_type'] == "whole place" && $ad['room_letting'] == "0") {						
						$toReturn .= '<td><strong>' . $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') . '</strong> whole place</td>'."\n";					
					} else {
						$toReturn .= '<td><strong>' . $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') . '</strong> per bedroom '."\n";
						$toReturn .= '<span class="grey" style="font-size:11px">';
						
						if ($ad['incl_utilities']) { 
							$toReturn .= 'Inc. bills';
						} elseif ($ad['average_bills'] > 0 ) { 
							if (!$ad['incl_council_tax']) { 
								$toReturn .= 'Bills + CT ' . trim($CFSIntlAd->formatCountryCurrency($ad['average_bills'], 'app')); 
							} else {
								$toReturn .= 'Bills ' . trim($CFSIntlAd->formatCountryCurrency($ad['average_bills'], 'app')); 		
							}					
						}
							
						
						if ($ad['incl_council_tax'] && $ad['incl_utilities']) {
							$toReturn .= ' & CT'; 
						} elseif ($ad['incl_council_tax'] && $ad['average_bills'] > 0) {
							$toReturn .= ', inc. CT'; 						
						} elseif ((!$ad['incl_utilities'] && $ad['average_bills'] == 0) && $ad['incl_council_tax']) {
							$toReturn .= 'Inc. CT'; 						
						} elseif (!$ad['incl_council_tax'] && $ad['incl_utilities'] && $ad['average_bills'] > 0) {
								$toReturn .= ', CT ' . $CFSIntlAd->formatCountryCurrency($ad['average_bills'], 'app') .')'; 	
						} elseif (!$ad['incl_council_tax'] && $ad['incl_utilities']) {												
							$toReturn .= ''; 				
						} elseif ($ad['average_bills'] > 0 ) { 
							$toReturn .= ''; 																			 
				//		} else {
				//		$toReturn .= '(ex. CT)'; 	
						}
			
						
						
						
						$toReturn .= '</span>';
						$toReturn .= '</td';						
					}
					$toReturn .= '</tr>'."\n";
					 
					// Offered line 3: Male or female, 17-23yrs, Students, Mature Students, Professionals
					$isPlural = ($ad['bedrooms_available'] > 1)? TRUE:FALSE;
					switch($ad['suit_gender']) {
						case "Male(s)": $line_three = ($isPlural)? "Males":"Male"; break;
						case "Female(s)": $line_three = ($isPlural)? "Females":"Female"; break;
						case "Mixed": $line_three = ($isPlural)? "Males or females":"Male or female"; break;
					}
					$line_three .= ', ';
                    
                    $age = cleanAge($ad['suit_min_age'], $ad['suit_max_age'], 'suit');
                    
                    if ($age != 'Any age') {
                        $line_three .= $age . ', ';
                    }
                    
					if ($ad['suit_student'] && $ad['suit_mature_student'] && $ad['suit_professional']) {
						// Do not add a label (helps with thumb positioning
					} else {			
						if ($ad['suit_student']) { // If ad suits students
							$line_three .= "student";
							$line_three .= ($isPlural)? "s, ":", ";
						}
						if ($ad['suit_mature_student']) { // If ad suits mature students
							$line_three .= "mature student";
							$line_three .= ($isPlural)? "s, ":", ";
						}
						if ($ad['suit_professional']) { // If ad suits professionals
							$line_three .= "professional";
							$line_three .= ($isPlural)? "s, ":", ";			
						}
					}
					if (substr($line_three,-2,2) == ", ") { $line_three = substr($line_three,0,-2); } // Snip last comma and space (if there) 
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Would suit:</td>'."\n";
					$toReturn .= '<td>'.$line_three.'</li></td>'."\n";
					$toReturn .= '</tr>'."\n";
					
					// Offered line 4: "Now" or "DD Month-name YYYY"
          //error_log(__FILE__.' available_date='.$ad['available_date'], 0);
					//list($year, $month, $day) = explode('-',$ad['available_date']);
					//$date_from = new DateTime($year."-".$month."-".$day);
          // date_creates, expects YYYY-MM-DD
          $date_from = date_create($ad['available_date']);
			    if ($date_from > new DateTime()) { // If $date_from is in the future
					    $line_four = $date_from->format("d M Y");
					} else {
					  $line_four = 'Today';
					} 
					
      //  	if ($ad['accommodation_type'] == "whole place") {
			//	 	 $NumberOfAdPalups = getNumberOfAdPalups($ad['offered_id']);
			//		 if ($NumberOfAdPalups > 0) { $line_four .= '<span style="font-size:11px">  ('.$NumberOfAdPalups.' Pal-Up'; }
			//		 if ($NumberOfAdPalups > 1) { $line_four .= 's'; } 
			//		 if ($NumberOfAdPalups > 0) { $line_four .= '!)'; }
			//		 $line_four .= '</span>';
			//		}
					
					
					// Add short-term
					if ($ad['max_term'] <= 12) { 
						if ($ad['max_term'] == 1 ) { $weeks = ' wk'; } else { $weeks = ' wks'; }
						$line_four .= ' (short-term, '.$ad['max_term'].$weeks.')'; 
					}
					
					

					/*
					$line_four .= '<span style="font-size:10px" class="grey"> (logged in '."\n";
					if ($ad['last_login_days']==0) {
					  $line_four .= 'today)';
					} else if ($ad['last_login_days']==1) {
					  $line_four .= 'yesterday)';
					} else {
					  $line_four .= $ad['last_login_days'].' days ago)';	
					}		
					$line_four .= '</span>'."\n"; 
					*/
										
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Date available:</td>'."\n";
					$toReturn .= '<td >'.$line_four.'</td>';
					$toReturn .= '</tr>'."\n";
					$toReturn .= '</table>'."\n";
				}
				
				if ($type == "wanted") {
				
					$toReturn .= '<table cellpadding="0" cellspacing="0" border="0">'."\n";
					
					// Wanted line 1: 1 bedroom, flat share, family share, whole place, Pal-up
					$line_one = $ad['bedrooms_required'].' bedroom';
					if ($ad['bedrooms_required'] > 1) { $line_one .= 's'; }
					$line_one .= ' (';
				        if ($ad['accommodation_type_flat_share'] && $ad['accommodation_type_room_share']) { $line_one .= 'room or flatshare, '; }
					 else {
					    if ($ad['accommodation_type_room_share']) { $line_one .= 'room share, '; }
					    if ($ad['accommodation_type_flat_share']) { $line_one .= 'flatshare, '; }
					    if ($ad['accommodation_type_family_share']) { $line_one .= 'family share, '; }
					}
					if ($ad['accommodation_type_family_share']) { $line_one .= 'family share, '; }
					if ($ad['accommodation_type_whole_place']) { $line_one .= 'whole place, '; }
					if ($ad['palup']) { $line_one .= 'palup, '; }
					if (substr($line_one,-2,2) == ", ") { $line_one = substr($line_one,0,-2); } // Snip last comma and space (if there) 
					$line_one .= ')';					
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Room(s):</td>'."\n";
					$toReturn .= '<td>'.$line_one.'</td>'."\n";
					$toReturn .= '</tr>'."\n";
					
					// Wanted line 2: £*** per bedroom
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Monthly price:</td>'."\n";
					// 
					if ($ad['accommodation_type_whole_place'] && !$ad['accommodation_type_flat_share'] && !$ad['accommodation_type_family_share'] && !$ad['accommodation_type_room_share']) {
						$toReturn .= '<td><strong>' . $CFSIntlAd->formatCountryCurrency($ad['price_pcm'] * $ad['bedrooms_required']) . '</strong> whole place';
					} else {
						$toReturn .= '<td><strong>' . $CFSIntlAd->formatCountryCurrency($ad['price_pcm']) . '</strong> per bedroom';					
					}
					// For whole place, calculate the price:
					if ($ad['accommodation_type_whole_place'] 
							&& $ad['bedrooms_required'] > 1
							&& ($ad['accommodation_type_flat_share'] || $ad['accommodation_type_family_share'] || $ad['accommodation_type_room_share'])
						) {
						$toReturn .= ' (' . $CFSIntlAd->formatCountryCurrency($ad['bedrooms_required'] * $ad['price_pcm']) . ' whole place)';
					}
					$toReturn .= '</td>'."\n";
					$toReturn .= '</tr>'."\n";
					
					// Wanted line 3: 2 males, 3 females, 23-26yrs, mature students
                    // var_dump($ad['current_min_age']);
                    // var_dump($ad['current_max_age']);
                    //exit;
					$line_three = "";
					if ($ad['current_num_males']) {
					    $isPlural = ($ad['current_num_males'] > 1)? TRUE:FALSE;					
						$line_three .= $ad['current_num_males'].' male';
						if ($isPlural) { $line_three .= 's'; }
						$line_three .= ', ';
					}
					if ($ad['current_num_females']) {
					    $isPlural = ($ad['current_num_females'] > 1)? TRUE:FALSE;						
						$line_three .= $ad['current_num_females'].' female';
						if ($isPlural) { $line_three .= 's'; }
						$line_three .= ', ';
					}
                    
                    $line_three .= cleanAge($ad['current_min_age'], $ad['current_max_age'], 'current') . ', ';
                    
                    // if ($ad['current_min_age'] && $ad['current_max_age']) { // If we have minimum or maximum ages defined
                    //     if ($ad['current_min_age'] == $ad['current_max_age']) {
                    //         $line_three .= $ad['current_min_age']." yrs, ";
                    //     } else {
                    //         $line_three .= $ad['current_min_age']."-".$ad['current_max_age']." yrs, ";
                    //     }
                    // }
                    //                     else if ($ad['current_min_age'] == $ad['current_max_age'] && $ad['current_max_age'] == 51) {
                    //                         $line_three .= "over 51 yrs";
                    //                     }
                    //                     else if ($ad['current_min_age']) { // only minimum age defined
                    //     $line_three .= "over ".$ad['current_min_age']." yrs, ";
                    // } else if ($ad['current_max_age']) { // only maximum age defined
                    //     $line_three .= "under ".$ad['current_max_age']." yrs, ";
                    // }	
					if ($ad['current_num_males'] + $ad['current_num_females'] == 1) {
						switch($ad['current_occupation']) {
							case "Students (<22yrs)":
								$line_three .= "student (<22yrs)";
								break;
							default:
								$line_three .=	substr(strtolower($ad['current_occupation']),0,-1);
								break;
						}
					} else {
						$line_three .=	strtolower($ad['current_occupation']);
					}
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">To suit:</td>'."\n";
					$toReturn .= '<td>'.$line_three.'</td>'."\n";
					$toReturn .= '</tr>'."\n";
					
					// Wanted line 4: "22 August 2006 (short-term)" or "Now"
					// list($year, $month, $day) = explode('-',$ad['available_date']);
				 	// $date_from = new DateTime($year."-".$month."-".$day);
          // date_create, expects YYYY-MM-DD
          $date_from = date_create($ad['available_date']);
					if ($date_from > new DateTime()) { // If $date_from is in the future
					  $line_four = $date_from->format("d M Y");
					} else {
					  $line_four = 'Today';
					}
					
					// Add short-term		
					if ($ad['max_term'] <= 12) { 
						if ($ad['max_term'] == 1 ) { $weeks = ' wk'; } else { $weeks = ' wks'; }
						$line_four .= ' (short-term, '.$ad['max_term'].$weeks.')'; 
					}
					
					/*
					$line_four .= '<span style="font-size:10px" class="grey"> (logged in '."\n";
					if ($ad['last_login_days']==0) {
					  $line_four .= 'today)';
					} else if ($ad['last_login_days']==1) {
					  $line_four .= 'yesterday)';
					} else {
					  $line_four .= $ad['last_login_days'].' days ago)';	
					}		
					$line_four .= '</span>'."\n"; 
					*/		
					
					$toReturn .= '<tr>'."\n";
					$toReturn .= '<td class="ps_shaded">Required from:</td>'."\n";
					$toReturn .= '<td>'.$line_four.'</td>'."\n";
					$toReturn .= '</tr>'."\n";
					$toReturn .= '</table>'."\n";				
				
				}
			
			}
			
			//$toReturn .= '</div>'."\n";
			$toReturn .= '</td>';
			
			// If ad has additional photos, load small thumbs for each
			if ($ad['photos']) {
				$query = "
					SELECT 
						photo_id,
						photo_filename,
						caption
					FROM cf_photos
					WHERE ad_id = '".$ad[$type.'_id']."' 
					AND post_type = '".$type."'					
				";
				$pic_result = mysqli_query($GLOBALS['mysql_conn'], $query);
				$pic_count = mysqli_num_rows($pic_result);
							
				$toReturn .= '<td class="ps_thumbs">';
				// Two lines of 4 pics each, margin of 4 px on the left and bottom of each
				$counter = 1;
				while($thumb = mysqli_fetch_assoc($pic_result)) {
					list($w,$h) = getImgRatio("images/photos/".$thumb['photo_filename'],"",30,40,30);
					if ($counter == floor($pic_count / 2) + 1) {
						$toReturn .= '<div class="clear"><!----></div>';
					} 					
					$toReturn .= '<a href="details.php?id='.$ad[$type.'_id'].'&post_type='.$type.'&photo_id='.$thumb['photo_id'].'&t=1" style="display:none;" ';
					// Tooltip functionality
					//$toReturn .= 'class="image_tooltip" title="&lt;img src=&quot;images/photos/'.$thumb['photo_filename'].'&quot; height=&quot;120&quot;&gt;"';
					if ($thumb['caption']) {
						$toReturn .= 'title="'.$thumb['caption'].'"';
					}					
					$toReturn .= '>';
					$toReturn .= '<img src="thumbnailer.php?img=images/photos/'.$thumb['photo_filename'].'&w='.$w.'&h='.$h.'&bw=true" width="'.$w.'" height="'.$h.'" />';
					$toReturn .= '</a>';
					$counter++;
				}				
				$toReturn .= '</td>';
			
			}
			
			
			
			
			$toReturn .= '<td class="ps_links">';
			
			// Ad links
			if (!$hideLinks) {
				if ($includeToken) { $t = '&t=1'; } else { $t = ''; }
				if (accountSuspended()) {
					// Member has suspended their account
					$toReturn .= '<span class="grey">Account suspended</span><br />'."\n";
				} else {
					$toReturn .= '<a href="reply.php?'.$type.'_id='.$ad[$type.'_id'].$t.'">Reply to the advert'.$number_of_replies.'</a><br />'."\n";					
				}
				$toReturn .= '<a href="map.php?'.$type.'_id='.$ad[$type.'_id'].$t.'">View on map</a><br />'."\n";
				$toReturn .= '<a href="send-to-a-friend.php?'.$type.'_id='.$ad[$type.'_id'].$t.'">Send to a friend</a><br />'."\n";
				$toReturn .= '<a href="report.php?'.$type.'_id='.$ad[$type.'_id'].$t.'">Problem advert?</a>'."\n";
			}
			
			//$toReturn .= '</div>'."\n";
			$toReturn .= '</td>';
			$toReturn .= '</tr>';
			$toReturn .= '</table>';
			
		$toReturn .= '</td>'."\n";
		$toReturn .= '</tr><tr><td colspan="2">' . $friends . '</td></tr>';
        
		$toReturn .= '</table>';
		
		return $toReturn;
	
	}
	
	
	function numberOfReplies($type, $ad_id) {
			$query = "
					select NULL
					from cf_email_replies `e`, 
							 cf_users as `u_from`,
							 cf_".$type." as `ad`
					where ((e.from_user_id = '".$_SESSION['u_id']."'
								and e.from_user_id != ad.user_id						
							 )
							or
							 (e.from_user_id = '".$_SESSION['u_id']."'
								and e.to_user_id = '".$_SESSION['u_id']."'
								and e.from_user_id = ad.user_id
							))
					and   ad.".$type."_id = e.to_ad_id
					and   e.to_post_type = '".$type."'
					and   e.to_ad_id   = '".$ad_id."'
					and   u_from.user_id = e.from_user_id
					and   e.sender_deleted = 0				
					UNION ALL
					select NULL
					from cf_email_replies `e`, 
							 cf_users as `u_from`,
							 cf_".$type."_archive as `ad`
					where ((e.from_user_id = '".$_SESSION['u_id']."'
								and e.from_user_id != ad.user_id						
							 )
							or
							 (e.from_user_id = '".$_SESSION['u_id']."'
								and e.to_user_id = '".$_SESSION['u_id']."'
								and e.from_user_id = ad.user_id
							))
					and   ad.".$type."_id = e.to_ad_id
					and   e.to_post_type = '".$type."'
					and   e.to_ad_id   = '".$ad_id."'
					and   u_from.user_id = e.from_user_id
					and   e.sender_deleted = 0				
				";
 			   $debug = debugEvent("Email replies query",$query);
    		 $result = mysqli_query($GLOBALS['mysql_conn'], $query);
			   $number_of_replies = mysqli_num_rows($result);
				 return $number_of_replies;
	  }
	
	// Creates the "quick" symmary of offered (or wanted) ads that will
	// be shown in the display page and the map page (only for offered)
	function createQuickSummary($ad,$post_type,$counter = NULL) {
	  $e = NULL;
	  $temp = NULL;
		/* For each ad, return a row with the following columns (header row shown below):
			<tr>
				<th>Price PCMPP</th>
				<th>Available</th>
				<th>Description</th>
				<th>Photo?</th>
			</tr>	
		*/
        
        $CFSIntlAd = new CFSInternational();
        $CFSIntlAd->setAppCountry($ad['country']);
		
	
		$toReturn = '<tr>';
		
		if ($post_type == "offered") {
		
			// Price PCMPP
			$toReturn .= '<td align="center" valign="top"><strong>';
			if ($ad['accommodation_type'] == "whole place") {
				$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') .'</strong>&nbsp;whole&nbsp;place<br/><span class="grey">(' . $CFSIntlAd->formatCountryCurrency(round($ad['price_pcm']/$ad['bedrooms_available']), 'app') . '&nbsp;per&nbsp;bedroom)</span>'."\n";
			} else {
				$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') .'</strong>&nbsp;per&nbsp;bedroom'."\n";
			}
			$toReturn .= '</td>';
			
			// Available
			// list($year, $month, $day) = explode('-',$ad['available_date']);
			// $date_from = new DateTime($year."-".$month."-".$day);
      // date_create, expects YYYY-MM-DD
      $date_from = date_create($ad['available_date']);
			if (!isset($temp)) $temp = '';
			if ($date_from > new DateTime()) { // If $date_from is in the future
				$temp .= $date_from->format("d M Y");
			} else {
				$temp = 'Today';
			}
			// Add short-term 
			if ($ad['max_term'] <= 12) { 
				if ($ad['max_term'] == 1 ) { $weeks = ' wk'; } else { $weeks = ' wks'; }
				//$temp .= ' (short-term, '.$ad['max_term'].$weeks.')'; 
				$temp .= '<br/><span class="grey" (short-term, '.$weeks.')</span>'; 
			}

			$toReturn .= '<td align="center" valign="top">'.$temp.'</td>';
			
			// Description
			$toReturn .= '<td>';
			
				// Save ad link (do NOT SHOW on map.php, due to conflict between mootools and gmaps)
				if (!strpos($_SERVER['PHP_SELF'],"map.php")) {
			        if (isset($_SESSION['u_id'])) {				
			 	    // Determine if ad replied to
						$query = "
						select *
						from cf_email_replies `e`, 
						 cf_users as `u_from`
						where e.from_user_id = '".$_SESSION['u_id']."'
						and   e.to_post_type = '".$post_type."'
						and   e.to_ad_id   = '".$ad[$post_type.'_id']."'
						and   u_from.user_id = e.from_user_id
						order by e.reply_date desc;	
					    ";

						   $debug .= debugEvent("Email replies query",$query);
				  		 $result = mysqli_query($GLOBALS['mysql_conn'], $query);
						   $number_of_replies = mysqli_num_rows($result);
				      if ($number_of_replies > 0) { $number_of_replies = ' ('.$number_of_replies.')'; } else { $number_of_replies = ''; }
					} else {
					   $number_of_replies = FALSE;			
					}
				
          $userId = getUserIdFromSession();
        
					if ($userId) {  
					  $toReturn .= '<a id="save_offered_ad_'.$ad['offered_id'].'" href="#" class="save_ad_button" style="float:right;">';
						if ($ad['active']==1) {
							$toReturn .= '<img src="images/button_saved_ad.gif" height="17" border="0" align="absmiddle" />';		
						} elseif ($ad['active']==2) {
							$toReturn .= '<img src="images/button_hidden_ad.gif" height="17" border="0" align="absmiddle" />';
						} else {
							$toReturn .= '<img src="images/button_hidesave_ad.gif" height="17" border="0" align="absmiddle" />';						
						}
						$toReturn .= '</a>';
					} else {
					$toReturn .= '<a href="login.php?msg=save_ads" style="float:right;"><img src="images/button_hidesave_ad.gif" height="17" border="0" align="absmiddle" /></a>';				
					}
				}
			
				// Ad title
				
				// If $counter is specified (i.e. !== NULL) we are delign with a map quick list and we need to change the links
				if ($counter !== NULL) {
					$toReturn .= '<a href="#" onclick="google.maps.event.trigger(ad_markers['.$counter.'], \'click\'); return false;"><strong>';
					$toReturn .= getAdTitle($ad,$post_type,false);
					$toReturn .= '</strong></a>';
				} else {		
					// Otherwise enter the default ad title			
					$toReturn .= '<strong>'.getAdTitle($ad,$post_type).'</strong>';
				}
				
				$toReturn .= '<br/>';
				// To suit
				$isPlural = ($ad['bedrooms_available'] > 1)? TRUE:FALSE;
				switch($ad['suit_gender']) {
					case "Male(s)": $temp = ($isPlural)? "males":"male"; break;
					case "Female(s)": $temp = ($isPlural)? "females":"female"; break;
					case "Mixed": $temp = ($isPlural)? "males or females":"male or female"; break;
				}
				$temp .= ', ';
                
                $temp .= cleanAge($ad['suit_min_age'], $ad['suit_max_age'], 'suit') . ', ';
                		
				if ($ad['suit_student']) { // If ad suits students
					$temp .= "student";
					$temp .= ($isPlural)? "s, ":", ";
				}
				if ($ad['suit_mature_student']) { // If ad suits mature students
					$temp .= "mature student";
					$temp .= ($isPlural)? "s, ":", ";
				}
				if ($ad['suit_professional']) { // If ad suits professionals
					$temp .= "professional";
					$temp .= ($isPlural)? "s, ":", ";			
				}
				if (substr($temp,-2,2) == ", ") { $temp = substr($temp,0,-2); } // Snip last comma and space (if there) 
				$toReturn .= 'Would suit '.$temp;
				
				
				// Add the first 500 chars from the description
				$n = (800 - strlen($ad['household_description']));
				$n = ($n>0?$n:0);
				$n = 400 + $n;
				$t = strip_tags($ad['accommodation_description']);		
  				$t = substr($t,0,$n);				
				// If text truncated, truncate to nearest space.				
				if (strlen($ad['accommodation_description'])>$n) 
				{				  
				  $num_words = str_word_count($t);
				  $num_words = $num_words - 1;
 		          $words = str_word_count($t, 2);
	  	          $pos = array_keys($words);
				     if (count($words) > $num_words) {
				         $t = substr($t, 0, $pos[$num_words]).'...';
				         }
				}
				if ($t) { $toReturn .= '. <span class="grey"><strong>[accom.]</strong> '.$t.'</span>'; }
			 
				
				$t = $ad['household_description'];
				$t = substr($t,0,400);
				// If text truncated, truncate to nearest space.				
				if (strlen($ad['household_description'])>$n) 
				{				  
				  $num_words = str_word_count($t);
				  $num_words = $num_words - 1;
 		          $words = str_word_count($t, 2);
	  	          $pos = array_keys($words);
				     if (count($words) > $num_words) {
				         $t = substr($t, 0, $pos[$num_words]).'...';
				         }
				}
				if ($t) { $toReturn .= ' <span class="grey"><strong>[household]</strong> '.$t.$e.'</span>'; }
				// Append last logged in
				$toReturn .= '<br /><span class="grey"><strong>(last logged in '."\n";
				if ($ad['last_login_days']==0) {
				  $toReturn .= 'today)</strong></span>';
				} else if ($ad['last_login_days']==1) {
				  $toReturn .= 'yesterday)</strong></span>';
				} else {
				  $toReturn .= $ad['last_login_days'].' days ago)</strong></span>';	
				}		
				
				
			$toReturn .= '</td>';
		
		} else {   // WANTED AD
		
			// Price PCMPP
			$toReturn .= '<td align="center" valign="top"><strong>';
			
			$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm']).'</strong>&nbsp;per&nbsp;bedroom';
			// For whole place, calculate the price:
			if ($ad['accommodation_type_whole_place']) {
				$toReturn .= '<br/><span class="grey">(' . $CFSIntlAd->formatCountryCurrency(($ad['bedrooms_required'] * $ad['price_pcm'])) . ' whole place)</span>';
			}
			$toReturn .= '</td>';
			
			// Available
			// list($year, $month, $day) = explode('-',$ad['available_date']);
			// $date_from = new DateTime($year."-".$month."-".$day);
      // date_create, expects YYYY-MM-DD
      $date_from = date_create($ad['available_date']);
			if ($date_from > new DateTime()) { // If $date_from is in the future
				$temp .= $date_from->format("d M Y");
			} else {
				$temp = 'Today';
			}
		
			// Add short-term
			if ($ad['max_term'] <= 12) { 
				if ($ad['max_term'] == 1 ) { $weeks = ' wk'; } else { $weeks = ' wks'; }
				$temp .= '<br/><span class="grey">(short-term, '.$ad['max_term'].$weeks.')</span>'; 							
			}
			
			$toReturn .= '<td align="center" valign="top">'.$temp.'</td>';
			
			// Description
			$toReturn .= '<td valign="top">';
				
				// Save ad link
				if (!isset($_SESSION['u_id'])) $_SESSION['u_id'] = '';
				if ($_SESSION['u_id']) {
					$toReturn .= '<a id="save_wanted_ad_'.$ad['wanted_id'].'" href="#" class="save_ad_button" style="float:right;">';
					if ($ad['active']==1) {
						$toReturn .= '<img src="images/button_saved_ad.gif" height="17" border="0" align="absmiddle" />';		
					} elseif ($ad['active']==2) {
						$toReturn .= '<img src="images/button_hidden_ad.gif" height="17" border="0" align="absmiddle" />';
					} else {
						$toReturn .= '<img src="images/button_hidesave_ad.gif" height="17" border="0" align="absmiddle" />';						
					}
					$toReturn .= '</a>';
				} else {
					$toReturn .= '<a href="login.php?msg=save_ads" style="float:right;"><img src="images/button_hidesave_ad.gif" height="17" border="0" align="absmiddle" /></a>';				
				}
				$toReturn .= '<strong>'.getAdTitle($ad,$post_type).'</strong>';
				$toReturn .= '<br/>';
				
				// To suit
				$line_three = "To suit ";
				$isPlural = ($ad['bedrooms_required'] > 1)? TRUE:FALSE;
				if ($ad['current_num_males']) {
					$line_three .= $ad['current_num_males'].' male';
					if ($isPlural) { $line_three .= 's'; }
					$line_three .= ', ';
				}
				if ($ad['current_num_females']) {
					$line_three .= $ad['current_num_females'].' female';
					if ($isPlural) { $line_three .= 's'; }
					$line_three .= ', ';
				}
				if ($ad['current_min_age'] && $ad['current_max_age']) { // If we have minimum or maximum ages defined
					if ($ad['current_min_age'] == $ad['current_max_age']) {
						$line_three .= $ad['current_min_age']." yrs, ";
					} else {
						$line_three .= $ad['current_min_age']."-".$ad['current_max_age']." yrs, ";
					}
				} else if ($ad['current_min_age']) { // only minimum age defined
					$line_three .= "over ".$ad['current_min_age']." yrs, ";
				} else if ($ad['current_max_age']) { // only maximum age defined
					$line_three .= "under ".$ad['current_max_age']." yrs, ";
				}			
				if ($ad['current_num_males'] + $ad['current_num_females'] == 1) {
					switch($ad['current_occupation']) {
						case "Students (<22yrs)":
							$line_three .= "student (<22yrs)";
							break;
						default:
							$line_three .=	substr(strtolower($ad['current_occupation']),0,-1);
							break;
					}
				} else {
					$line_three .=	strtolower($ad['current_occupation']);
				}
				$toReturn .= $line_three." - ";
				
				// Flatshare info
				$temp = "";
				if ($ad['accommodation_type_flat_share'] && $ad['accommodation_type_flat_share']) { $temp .= 'room or flatshare, '; }
				else {
						  if ($ad['accommodation_type_flat_share']) { $temp .= 'flatshare, '; }
							if ($ad['accommodation_type_family_share']) { $temp .= 'family share, '; }
				}
				if ($ad['accommodation_type_whole_place']) { $temp .= 'whole place, '; }
				if ($ad['accommodation_type_room_share']) { $temp .= 'room share, '; }				
				if ($ad['palup']) { $temp .= 'pal-up, '; }
				if (substr($temp,-2,2) == ", ") { $temp = substr($temp,0,-2); } // Snip last comma and space (if there)
				$toReturn .= $temp.".";
				
				
				
				// Add the first 550 chars from the description
				$t = strip_tags($ad['accommodation_situation']);
				$t = substr($t,0,800);
				// If text truncated, truncate to nearest space.				
				if (strlen($ad['accommodation_situation'])>550) 
				{				  
				  $num_words = str_word_count($t);
				  $num_words = $num_words - 1;
 		          $words = str_word_count($t, 2);
	  	          $pos = array_keys($words);
				     if (count($words) > $num_words) {
				         $t = substr($t, 0, $pos[$num_words]).'...';
				         }
				}
				if ($t) { $toReturn .= ' <span class="grey">'.$t.'</span>'; }
			// Append last logged in
			$toReturn .= '<br /><span class="grey"><strong>(last logged in '."\n";
			if ($ad['last_login_days']==0) {
			  $toReturn .= 'today)</strong></span>';
			} else if ($ad['last_login_days']==1) {
			  $toReturn .= 'yesterday)</strong></span>';
			} else {
			  $toReturn .= $ad['last_login_days'].' days ago)</strong></span>';	
			}		

				
				
			$toReturn .= '</td>';
		}
		
		// Photos available
		$toReturn .= '<td align="center">';
		if ($ad['photos']) { 
			$tempName = 'photo_'.$ad['photos'].'_white.gif';
			$toReturn .= '<img src="images/'.$tempName.'" align="absmiddle" border="0" />';
		}
		$toReturn .= '</td>';
		
		$toReturn .= '</tr>';		
		return $toReturn;	
		
	}
	
	// Create a "summary" representation of an ad that will be
	// displayed in a Google Maps speech bubble overlay
	function createMapSummary($ad,$type) {
	
		// Note that since the $toReturn will be returned to a javascript function,
		// it should not have any line breaks
		
        $CFSIntlAd = new CFSInternational();
        $CFSIntlAd->setAppCountry($ad['country']);
        
		$toReturn  = '<table cellpadding="0" cellspacing="0" border="0">';
		$toReturn .= '<tr>';
		// Pic
		$toReturn .= '<td valign="top" style="padding-right:10px;">';
		$toReturn .= '<div class="thumbnailContainer">';
		$toReturn .= '<a href="'.$url.'details.php?id='.$ad[$type.'_id'].'&post_type='.$type.'">';
		$toReturn .= '<img src="images/pictures/'.$ad['picture'].'" border="0">';
		$toReturn .= '</a>';
		$toReturn .= '</div>';
		$toReturn .= '</td>';
		// Desc
		$toReturn .= '<td valign="top" ';
		// For wanted ads, specify a default width of 300px
		if ($type == "wanted") { $toReturn .= 'width="300"'; }
		$toReturn .= '>';
			
			if ($type == "offered") {
						
				// Line one: Accommodation desc
				if ($ad['room_share'] == 1) {
					$toReturn .= '<strong>Room Share</strong><br />';
				} else {
					$toReturn .= '<strong>'.ucwords(getPropertyDescription($ad['accommodation_type'],$ad['building_type'])).'</strong><br/>';
				}
				// Line two: 2 bedrooms in a 3 bedroom house
				$toReturn .= '<a href="details.php?id='.$ad['offered_id'].'&post_type=offered" target="_blank">';

			//	$toReturn .= $ad['bedrooms_available'].' bedroom';
			//	if ($ad['bedrooms_available'] > 1) { $toReturn .= 's'; }
			//	$toReturn .= ' in a '.$ad['bedrooms_total'].' bed '.$ad['building_type'];
					if ($ad['room_share'] == 1) { 
						$toReturn .= '1 room share';
					} else {
	 			    $toReturn .= $ad['bedrooms_available'].' bedroom';
					}
 			    if ($ad['accommodation_type'] != "whole place") {
			  	   if ($ad['bedrooms_available'] > 1) { $toReturn .= 's'; }
				   $toReturn .= ' in a '.$ad['bedrooms_total'].' bed '.$ad['building_type'];
				   } else {
				   $toReturn .= ' '.$ad['building_type'];
				}
				$toReturn .= '</a>';
			    $toReturn .= '<br/>';
			
				if ($ad['country'] == 'GB' || $ad['country'] == '') {
    				// Line three: Street_name, town (PO)
    				$toReturn .= stripslashes($ad['street_name']).', '.stripslashes($ad['town']);
    				$postcode = strtoupper(preg_replace('/\s.{3}$/','',$ad['postcode']));
    				$toReturn .= ' ('.$postcode.')';
    				$toReturn .= '<br/>';
				}
                else {
    				// Line three: Street_name, town (PO)
    				$address = array($ad['street'], $ad['region']);
    				$toReturn .= implode(', ', $address);
    				$toReturn .= '<br/>';
                }

				// Line four: Price: £****
				if ($ad['accommodation_type'] == "whole place") {
					$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') . ' whole place'; //(&pound;'.round($ad['price_pcm']/$ad['bedrooms_available']).' per bedroom)';
				} else {
					$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') . ' per bedroom ';
				}
				$toReturn .= '<span class="grey" style="font-size:11px">';
				
				if ($ad['accommodation_type'] != "whole place") {
					if ($ad['incl_utilities']) { 
						$toReturn .= '(inc. bills';
					} elseif ($ad['average_bills'] > 0 ) { 
						if (!$ad['incl_council_tax']) { 
							$toReturn .= '(bills + CT ' . trim($CFSIntlAd->formatCountryCurrency($ad['average_bills'], 'app'));
						} else {
							$toReturn .= '(bills '. trim($CFSIntlAd->formatCountryCurrency($ad['average_bills'], 'app')); 		
						}					
					}
						
					
					if ($ad['incl_council_tax'] && $ad['incl_utilities']) {
						$toReturn .= ' & CT)'; 
					} elseif ($ad['incl_council_tax'] && $ad['average_bills'] > 0) {
						$toReturn .= ', inc. CT)'; 						
					} elseif ((!$ad['incl_utilities'] && $ad['average_bills'] == 0) && $ad['incl_council_tax']) {
						$toReturn .= '(inc. CT)'; 						
					} elseif (!$ad['incl_council_tax'] && $ad['incl_utilities'] && $ad['average_bills'] > 0) {
							$toReturn .= ', CT '. $CFSIntlAd->formatCountryCurrency($ad['average_bills'], 'app') .')'; 	
					} elseif (!$ad['incl_council_tax'] && $ad['incl_utilities']) {												
						$toReturn .= ')'; 				
					} elseif ($ad['average_bills'] > 0 ) { 
						$toReturn .= ')'; 																			 
					}
				}								

				
				
				$toReturn .= '</span>';
				$toReturn .= '<br/>';				
				// Line five: Date available
				$available_date = new DateTime($ad['available_date']);
				if ($available_date > new DateTime()) { // If $date_from is in the future
					$toReturn .= 'Date available '.$available_date->format("d M Y");
				} else {
					$toReturn .= 'Available today ';
				}
				
				// Add short-term
				if ($ad['max_term'] <= 12) { 
					if ($ad['max_term'] == 1 ) { $weeks = ' wk'; } else { $weeks = ' wks'; }
					$toReturn .= ' (short-term, '.$ad['max_term'].$weeks.')'; 								
				}
			
				
			} elseif ($type == "wanted") {
			
				// Line one: Accommdation desc
				$toReturn .= '<strong>'.getAdTitle($ad,$type).'</strong><br/>';
				
				// Line two: 2 males, 3 females, 23-26yrs, mature students
				$isPlural = ($ad['bedrooms_required'] > 1)? TRUE:FALSE;
				if ($ad['current_num_males']) {
					$toReturn .= $ad['current_num_males'].' male';
					if ($isPlural) { $toReturn .= 's'; }
					$toReturn .= ', ';
				}
				if ($ad['current_num_females']) {
					$toReturn .= $ad['current_num_females'].' female';
					if ($isPlural) { $toReturn .= 's'; }
					$toReturn .= ', ';
				}
				if ($ad['current_min_age'] && $ad['current_max_age']) { // If we have minimum or maximum ages defined
					if ($ad['current_min_age'] == $ad['current_max_age']) {
						$toReturn .= $ad['current_min_age']." yrs, ";
					} else {
						$toReturn .= $ad['current_min_age']."-".$ad['current_max_age']." yrs, ";
					}
				} else if ($ad['current_min_age']) { // only minimum age defined
					$toReturn .= "over ".$ad['current_min_age']." yrs, ";
				} else if ($ad['current_max_age']) { // only maximum age defined
					$toReturn .= "under ".$ad['current_max_age']." yrs, ";
				}			
				if ($ad['current_num_males'] + $ad['current_num_females'] == 1) {
					switch($ad['current_occupation']) {
						case "Students (<22yrs)":
							$toReturn .= "student (<22yrs)";
							break;
						default:
							$toReturn .= substr(strtolower($ad['current_occupation']),0,-1);
							break;
					}
				} else {
					$toReturn .= strtolower($ad['current_occupation']);
				}
				$toReturn .= '<br/>';
				
				// Wanted line 4: Price
				if ($ad['accommodation_type'] == "whole place") {
					$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') .' whole place (&pound;'.round($ad['price_pcm']/$ad['bedrooms_available']).' per bedroom)';
				} else {
					$toReturn .= $CFSIntlAd->formatCountryCurrency($ad['price_pcm'], 'app') .' per bedroom';
				}
				$toReturn .= '<br/>';								
				
				// Wanted line 5: "22 August 2006 (short-term)" or "Now"
				$available_date = new DateTime($ad['available_date']);
				if ($available_date > new DateTime()) { // If $date_from is in the future
					$toReturn .= 'Required from '.$available_date->format("d M Y");
				} else {
					$toReturn .= 'Required today';
				}
				

				// Add short-term
				if ($ad['max_term'] <= 12) { 
					if ($ad['max_term'] == 1 ) { $weeks = ' wk'; } else { $weeks = ' wks'; }
					$toReturn .= ' (short-term, '.$ad['max_term'].$weeks.')'; 
				}

			
			}
			// Open in a new window link 
			$toReturn .= '<p style="margin:10px 0px 0px 0px;" align="right">';
			$toReturn .= '<a href="details.php?id='.$ad[$type.'_id'].'&post_type='.$type.'" target="_blank">';
			$toReturn .= 'Show the full ad &gt;';
			$toReturn .= '</a>';
			$toReturn .= '</p>';			
		$toReturn .= '</td>';
		$toReturn .= '</tr>';
		$toReturn .= '</table>';
			
		return $toReturn;
			
	}
	
	// Create a "summary" representation of a church that will be
	// displayed in a Google Maps speech bubble overlay
	function createChurchSummary($church) {
		if (!isset($toReturn)) $toReturn = '';
		$toReturn .= '<h1 class="mt0">'.$church['church_name'].'</h1>';
		$toReturn .= '<p>';
		if ($church['church_description']) {
			$toReturn .= addslashes($church['church_description']).', ';
		}
		$toReturn .= addslashes($church['church_location']).'</p>';
		// URL
		$toReturn .= '<p><a href="http://'.$church['church_url'].'" target="_blank">'.$church['church_url'].'</a></p>';
		return $toReturn;
	}
	
	
	// Function to get the ad title based on the ID. Calls getAdTitle.
	function getAdTitleByID($ad_id,$type,$includeTags = TRUE,$fullUrl = FALSE, $open_externally = FALSE) {
	
		if ($type == "offered") {
			$query =  "SELECT o.*, CURDATE(),
					       (CASE IFNULL(o.town_chosen,'')
					           WHEN '' THEN j.town 
					           ELSE o.town_chosen
					           END) as town
									FROM cf_offered o
											left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 					   
									WHERE o.offered_id = '".$ad_id."'
									UNION ALL
									SELECT o.*, 
					       (CASE IFNULL(o.town_chosen,'')
					           WHEN '' THEN j.town 
					           ELSE o.town_chosen
					           END) as town
									FROM cf_offered_archive o
											left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 					   
									WHERE o.offered_id = '".$ad_id."'									
								";	
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$ad = mysqli_fetch_assoc($result);
		} else {
		// Wanted
			$query =  "SELECT w.*
									FROM cf_wanted_all w
									WHERE w.wanted_id = '".$ad_id."'
								";	
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$ad = mysqli_fetch_assoc($result);		
		}
		
		$ad_title = getAdTitle($ad, $type, $includeTags,$fullUrl, $open_externally);
		return ($ad_title);
	}
	
    function getAdURL($ad, $type) {
        if ($type == 'offered') {
            return 'http://' . SITE . 'details.php?id=' . $ad['offered_id'] . '&post_type=offered';
        }
        else if ($type == 'wanted') {
            return 'http://' . SITE . 'details.php?id=' . $ad['wanted_id'] . '&post_type=wanted';
        }
    }
	
	/**
	 * Returns the header description of an ad
	 * E.g. for offered ads: 1 bedroom in a 4 bed house in Street_name, Town (SE16) - House share
	 * E.g. for wanted ads : 1 bedroom wanted in Maida Vale, London (W91KD)
	 *
	 * OFFERED ads fields needed:
	 * offered_id, bedrooms_available, bedrooms_total,  
	 * accommodation_type, building_type, street_name, postcode
	 *
	 * WANTED ads fields needed:
	 * wanted_id, bedrooms_required
	 * distance_from_postcode, location, postcode
	 *
	 */
	function getAdTitle($ad,$type,$includeTags = TRUE,$fullUrl = FALSE, $open_externally = FALSE, $include_token = FALSE) {
		if (!isset($toReturn)) $toReturn = '';

	
		if ($fullUrl) {
			$url = "http://".SITE;
		} else {
			$url = "";
		}
		
		$it = NULL;
		if ($include_token) {
			$it = '&t=1';
		}
	
		if ($type == "offered") {
		
            if ($includeTags) {
                if ($open_externally) {
            		$toReturn  = '<a href="'.$url.'details.php?id='.$ad['offered_id'].'&post_type=offered'.$it.'" target="_blank">';
            	} else {
            		$toReturn  = '<a href="'.$url.'details.php?id='.$ad['offered_id'].'&post_type=offered'.$it.'">';				
            	}
            }
			
			if ($ad['room_share'] == 1) {
				$toReturn .= '1 room share ';
			} else {
				$toReturn .= $ad['bedrooms_available'].' bedroom';
			}
            
			if ($ad['accommodation_type'] != "whole place") {
				if ($ad['bedrooms_available'] > 1) { $toReturn .= 's'; }
				$toReturn .= ' in a '.$ad['bedrooms_total'].' bed '.$ad['building_type'].' in ';
			} else {
				$toReturn .= ' '.$ad['building_type'].' in ';
			}
			
            if (!isset($ad['town'])) {
              $ad['town'] = NULL;
            }
            
            // Location details differ for England vs International
            switch($ad['country']) {
                case 'IE':
                case 'ZA':
                case 'US':
                case 'CA':
                case 'AU':
    			    // Street_name, Town
                    $address = array($ad['street'], $ad['area']);
                    $toReturn .= implode(', ', $address) . ' - ';
                    break;
            
                case 'GB':
                default:
        			// Street_name, Town			
        			$toReturn .= stripslashes($ad['street_name']).', ';
        			$toReturn .= ($ad['town_chosen'])? stripslashes($ad['town_chosen']) : stripslashes($ad['town']);
        			// Postcode (only the last part)
                                // Some Wanted addresses are not retrieving postcodes... temporary conditional formatting measure
                                if (trim($ad['postcode']) != '') {
        			 $postcode = strtoupper(getUKPostcodeFirstPart($ad['postcode']));
        			 $toReturn .= ' ('.$postcode.') - ';
                                } else {
        			 $toReturn .= ' - ';
                                } 
                    break;
            }
        
			// The accommodation type
			if ($ad['room_share'] == 1) {			
				$toReturn .= 'Room Share';
			} else {
				$toReturn .= getPropertyDescription($ad['accommodation_type'],$ad['building_type']);
			}
			
			if ($includeTags) {
				$toReturn .= '</a>';
			}
		
		} else if ($type == "wanted") {
		
			if ($includeTags) {
			    if ($open_externally) {
				$toReturn  = '<a href="'.$url.'details.php?id='.$ad['wanted_id'].'&post_type=wanted'.$it.'" target="_blank">';
				} else {
				$toReturn  = '<a href="'.$url.'details.php?id='.$ad['wanted_id'].'&post_type=wanted'.$it.'">';				
				}
			}
			$toReturn .= $ad['bedrooms_required'].' bedroom';
			if ($ad['bedrooms_required'] > 1) { $toReturn .= 's'; }
			$toReturn .= ' wanted in ';
			$toReturn .= $ad['distance_from_postcode'].' mile';
			if ($ad['distance_from_postcode'] > 1) { $toReturn .= 's'; }
            
            // Location details differ for England vs International
            switch($ad['country']) {
                case 'IE':
                case 'ZA':
                case 'US':
                case 'CA':
                case 'AU':
    			    // Street_name, Town
                    $address = array($ad['street'], $ad['area'], $ad['region']);
                    $toReturn .= ' of ' . implode(', ', $address);
                    break;
            
                case 'GB':
                default:
        			// Location, Postcode			
                    $toReturn .= ' of ' . stripslashes($ad['location']);
                    if (trim($ad['postcode']) != '') {
                     // Some data is coming through without postcodes; this is a temporary condional fromatting fix
                     $toReturn .= ' ('.getUKPostcodeFirstPart($ad['postcode']).')'; 
                    }
                    break;
            }
            
			if ($includeTags) {
				$toReturn .= '</a>';
			}
		
		}
		
		return $toReturn;
		
	}
	
	/**
	 * Returns the status without any formatting or db query
	 */
	function getStatus($paid_for,$approved,$suspended,$published) {
		/* Status is determined from the three flags: paid_for, approved and published
		 * 	1. Pending payment and approval
		 *  2. Pending payment
		 *	3. Pending approval
		 *	4. Published
		 *	5. Unpublished
		 */
		if ($suspended) {
			$status = "Suspended";
		} else if ($published) {
			$status = "Published";
		} else {
			if ($paid_for && $approved) {
				$status = "Unpublished";
			} else if ($paid_for && !$approved) {
				$status = "Pending approval";
			} else if (!$paid_for && $approved) {
				$status = "Pending payment";
			} else if (!$paid_for && !$approved) {
				$status = "Pending payment and approval";
			}	
		}
		return $status;
	}
	
	/**
	 * Returns the user status
	 */
	function getUserStatus($active) {
		if ($active) {
			return "Verified";
		} else {
			return "Non verified";
		}
	}

	/**
	 * Depending on the accommodation_type and building_type, it returns one of the following:
	 * Family House Share, Family Flatshare, Whole House, Whole Flat, Flat share, House share
	 * Depending on the values supplied
	 */
	function getPropertyDescription($accommodation_type,$building_type) {
		
		if ($accommodation_type == "whole place") {
			if ($building_type == "house") {
				return "Whole House";
			} else {
				return "Whole Flat";
			}
		}
		
		if ($accommodation_type == "family share") {
			if ($building_type == "house") {
				return "Family House Share";
			} else {
				return "Family Flatshare";
			}			
		}
		
		if ($accommodation_type == "flat share") {
			if ($building_type == "house") {
				return "House Share";
			} else {
				return "Flatshare";
			}			
		}		
	
	}

	// Creates a <select> drop down element
	function createDropDown($name,$data,$selected="",$class="",$style="",$javascript="",$suffix="") {
    $toReturn = NULL;
		$toReturn .= "\n\n";
		$toReturn .= '<select name="'.$name.'" id="'.$name.'" class="'.$class.'" style="'.$style.'" '.$javascript.'>'."\n";
		foreach($data as $value=>$text) {
			$toReturn .= '<option value="'.$value.'"';
			if ($selected == $value) { $toReturn .= ' selected="selected"'; }
			$toReturn .= '>'.$text.$suffix.'</option>'."\n";
		}
		$toReturn .= '</select>'."\n\n";
		return $toReturn;
	}
	
	// Creates a <select> drop down element
	// with a range of dates grouped by months
	function createDateDropDown($name,$num,$selected="",$showPleaseSelect=FALSE,$class="",$javascript="") {
		$toReturn = "\n\n";
		$toReturn .= '<select name="'.$name.'" id="'.$name.'" class="'.$class.'" ';
		if ($javascript) {
			$toReturn .= $javascript;
		}
		$toReturn .= ' >'."\n";
		// If we need to show the "-- Please select --" label
		if ($showPleaseSelect) { 
			if (is_bool($showPleaseSelect)) {
				$toReturn .= '<option value="0">-- Please select --</option>'; 
			} else {
				$toReturn .= '<option value="0">'.$showPleaseSelect.'</option>';
			}
		}
		$now = new DateTime();
		$currentMonth = "";
		for($i=1;$i<=$num;$i++) {
			if ($i!=1) { $now = $now->add(new DateInterval('P1D')); } // Go to next date
			if ($now->format('M') != $currentMonth) {
				if ($i != 1) { $toReturn .= '</optgroup>'; }
				$currentMonth = $now->format('M');
				$toReturn .= '<optgroup label="'.$now->format('M').' '.$now->format('Y').'">';
			}
			$value = $now->format('Y-m-d');
			$toReturn .= '<option value="'.$value.'"';
			// Shade the option if it's a Sat or Sun
			if ($now->format('N') == 0 || $now->format('N') == 6) {
				$toReturn .= ' class="weekend"';
			}
			// Select the option if needed
			if ($value == $selected) { $toReturn .= ' selected="selected"'; }
			$toReturn .= '>'.$now->format('D, d M').'</option>';
		}
		$toReturn .= '</optgroup>';		
		$toReturn .= '</select>'."\n\n";
		return $toReturn;		
	}
	
	// Creates a radio group (something like:)
	// <input name="building_type" type="radio" value="House" />House
	// <input name="building_type" type="radio" value="Flat" />Flat
	function createRadioGroup($name,$data,$selected="",$direction="horizontal",$class="",$style="",$javascript="") {
		$toReturn = "\n\n";
		foreach($data as $value=>$text) {
			
			$temp = $name.",".$name."_".$value;		
			$toReturn .= createRadio($temp,$value,$selected,$class,$style,$javascript);
			$toReturn .= '<label for="'.$name.'_'.$value.'">'.$text.'</label>';
			$toReturn .= ($direction == "horizontal")? '&nbsp;':'<br/>';
			$toReturn .= "\n";
		}
		$toReturn .= "\n\n";
		return $toReturn;
	}
	
	// Creates an XHTML single radio button, something like:
	// <input type="radio" name="*****" id="*****" value="*****" />
	function createRadio($name,$value,$selected="",$class="",$style="",$javascript="") {
		
		// If name contains a comma, then the text after the comma is the id
		$temp = explode(',',$name);
		$name = $temp[0];
		$id = $temp[1]? $temp[1]:$temp[0];
		
		$toReturn = '<input type="radio" ';
		$toReturn .= 'name="'.$name.'" ';
		$toReturn .= 'id="'.$id.'" ';
		$toReturn .= 'value="'.$value.'" ';
		if ($class) { $toReturn .= 'class="'.$class.'" '; }
		if ($style) { $toReturn .= 'style="'.$style.'" '; }
		if ($javascript) { $toReturn .= $javascript; }
		if ($selected == $value) { $toReturn .= ' checked="checked"'; }
		$toReturn .= '/>'."\n";
		return $toReturn;
		
	}	
	
	// Creates an XHTML checkbox, something like:
	// <input type="checkbox" name="*****" id="*****" value="*****" />
	function createCheckbox($name,$value,$selected="",$javascript="") {
		$temp = explode(",",$name);
		$name = $temp[0];
		$id = (isset($temp[1]))? $temp[1] : $temp[0];
		$toReturn = '<input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'"';
		if ($selected == $value) { $toReturn .= ' checked="checked"'; }
		if ($javascript) { $toReturn .= ' '.$javascript; }
		$toReturn .= '/>'."\n";
		return $toReturn;
	}
		
	// Returns a formatted string to be added to the debug output
	function debugEvent($title,$output) {
		$t = '<p><strong># '.$title.'</strong><br/>'.nl2br($output).'</p>';
		return $t;
	}
	
	// Formats the error messages
	function formatError(&$value, $key) {
		$value = '<span class="errorParagraph">&nbsp;'.$value.'</span>';
	}
	
	// Creates the login form
	function createLoginForm($email="",$password="",$remember="",$rr="") {
	
		$toReturn = '<form name="login" action="login.php" method="post" class="login-form">'."\n";
		// If we have a redirection request
		if ($rr) { $toReturn .= '<input type="hidden" name="rr" value="'.$rr.'" />'."\n"; }
		
		$toReturn .= '<table border="0" cellspacing="0" cellpadding="0" id="loginForm">'."\n";
		// Email field
		$toReturn .= '<tr><td><strong>Email:</strong></td></tr>'."\n";
		$toReturn .= '<tr><td class="pb5"><input name="email" type="text" id="email" class="input" value="'.$email.'" /></td></tr>'."\n";
		// Password field
		$toReturn .= '<tr><td><strong>Password:</strong></td></tr>'."\n";
		$toReturn .= '<tr><td class="pb5"><input name="password" type="password" id="password" class="input" value="'.$password.'" /></td></tr>'."\n";
		// Remember email checkbox
		$toReturn .= '<tr>'."\n";
		$toReturn .= '<td>'."\n";
		$toReturn .= '<table cellpadding="0" cellspacing="0"><tr><td>'."\n";
		$toReturn .= '<input type="checkbox" value="1" name="remember" id="remember"';
		if ($remember) { $toReturn .= ' checked="checked"'; }
		$toReturn .= '/> <label for="remember">Remember me?</label>	</td><td></td>'."\n";
		$toReturn .= '</tr></table>'."\n";	
		$toReturn .= '</td>'."\n";
		$toReturn .= '</tr>'."\n";
		// Forgotten password link
		$toReturn .= '<tr><td class="pb5"><a href="forgotten-password.php">Forgotten your password?</a></td></tr>'."\n";		
		// Login button
		$toReturn .= '<tr><td><input type="submit" name="login" value="Login"/>&nbsp;&nbsp;<a href="register.php">Join here</a></td></tr>'."\n";
		$toReturn .= '</table>'."\n";
		$toReturn .= '</form>'."\n";
		return $toReturn;
		
	}
	
	// Returns the number of all members
	function getNumberOfMembers() {
		$query = "select count(*) from cf_users where access = 'member'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
		$query = "SELECT COUNT(*) FROM cf_users
		     		  WHERE cast(created_date as date) = cast(now() as date)";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$today = $row[0];
							
		return $total.' - '.$today;
	}

	// Returns the number of all offered ads
	function getNumberOfOffered() {
		$query = "select count(*) from cf_offered where published = 1";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
		$query = "SELECT COUNT(*) FROM cf_offered
				       WHERE published = 1 
							 and cast(created_date as date) = cast(now() as date)";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$today = $row[0];		
		
		$query = "SELECT COUNT(*) FROM cf_offered
	            WHERE published = 1 
              and cast(created_date as date) = cast(now() as date)
              and offered_id > (SELECT max(offered_id) from cf_offered 
                           			WHERE cast(created_date as date) < cast(now() as date))";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$new_today = $row[0];				
		return $total.' - '.$today.', '.$new_today.' new today';
	}

	// Returns the number of all wanted ads
	function getNumberOfWanted() {
		$query = "select count(*) from cf_wanted where published = 1";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
		$query = "SELECT COUNT(*) FROM cf_wanted
				       WHERE published = 1 
							 and cast(created_date as date) = cast(now() as date)";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$today = $row[0];		

		$query = "SELECT COUNT(*) FROM cf_wanted
	            WHERE published = 1 
              and cast(created_date as date) = cast(now() as date)
              and wanted_id > (SELECT max(wanted_id) from cf_wanted
                           			WHERE cast(created_date as date) < cast(now() as date))";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$new_today = $row[0];			
		return $total.' - '.$today.', '.$new_today.' new today';
	}

	// Returns the total number of all ads (inc archive)
	function getTotalNumberOfAds() {
		$query = "SELECT (SELECT COUNT(*) FROM cf_wanted WHERE published = 1) + (SELECT COUNT(*) FROM cf_wanted_archive WHERE published = 1 ) + (SELECT COUNT(*) FROM cf_offered_archive WHERE published = 1 ) + (SELECT COUNT(*) FROM cf_offered WHERE published = 1 ) FROM dual";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		return $row[0];
	}


	// Returns the number of photos uploaded
	function getPhotoCount() {
		$query = "SELECT COUNT(*) FROM cf_photos";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		return $row[0];
	}
	
	// Returns the number of emails sent 
	function getNumberEmails() {
		$query = "SELECT COUNT(*) FROM cf_users u_from, cf_users u_to, cf_email_replies e 
	       	    WHERE  e.from_user_id = u_from.user_id 
 				  	  AND    e.to_user_id   = u_to.user_id  ";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
		$query = "SELECT COUNT(*) FROM cf_users u_from, cf_users u_to, cf_email_replies e 
	       	    WHERE  e.from_user_id = u_from.user_id 
 				  	  AND    e.to_user_id   = u_to.user_id
				      AND    cast(e.reply_date as date) = cast(now() as date)";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$today = $row[0];		
		
		return $total.' - '.$today;
	}
	
	// Returns the number of feedback comments
	function getNumberFeedback() {
		$query = "SELECT COUNT(*) FROM cf_feedback WHERE feedback != '' ";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		return $row[0];
	}

	// Returns the number of feedback comments
	function getNumberSavedAdsAdmin() {
		$query = "SELECT COUNT(*) FROM cf_saved_ads";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		return $row[0];
	}
		
	// Returns the number of feedback comments
	function getNumberLogins() {
		$query = "SELECT COUNT(*) FROM cf_user_logins
				  WHERE cast(login_date as date) = cast(now() as date)";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$logins = $row[0];
		
		$query = "SELECT COUNT(DISTINCT email_address) 
		          FROM cf_user_logins
				      WHERE cast(login_date as date) = cast(now() as date)";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$distinct_logins = $row[0];		
		
		return $logins." - ".$distinct_logins;
	}
	
	// Returns the number banner clicks
		function getBannerClickCount() {
		$query = "select count(*) from cf_banners_clicks";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
		$query = "SELECT COUNT(*) FROM cf_banners_clicks
				       WHERE cast(time as date) = cast(now() as date)";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$today = $row[0];		

		$query = "SELECT COUNT(DISTINCT IP) FROM cf_banners_clicks
				       WHERE cast(time as date) = cast(now() as date)";		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$distinct_today = $row[0];				
		return $total.' - today '.$today.', distinct today '.$distinct_today;
	}	
	
	
	// Returns the number of feedback comments
	function TrustedUser($user_id) {
		$query = "SELECT COUNT(*) 
							FROM cf_email_replies e, cf_users u
							WHERE u.user_id = e.from_user_id
							AND   u.suppressed_replies = 0
							AND   from_user_id = '".$user_id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		if ($row[0] > 9) {
			return 'trusted';
		} else {
			return 'not_trusted';			
		}
	}
				
				
	// Returns the number of feedback comments
	function getNumberSavedAds() {
    $userId = getUserIdFromSession();
    
		$query = "SELECT COUNT(*) 
							FROM cf_saved_ads
		          WHERE user_id = '" . $userId . "'
							AND active = 1";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result !== FALSE) {
		  $row = mysqli_fetch_row($result);
      return $row[0];
    }
    return 0;
	}
					
	// Returns the number of all members
	function getNumberEmailsInbox() {
		// Shows emails sent to the current uesr_id 
		// if email was suppressed it is not shown, 
		// unless teh current session is a scammer
    $userId = getUserIdFromSession();
    
		$query = "select count(*) 
							from cf_email_replies e
							where e.recipient_deleted = 0 
							and e.to_user_id = " . $userId . "
							";
						//	and     (e.suppressed_replies = 0 
						//	         and u.suppressed_replies = 0
        		//		  or  (u.suppressed_replies = 1
				     //          and e.from_user_id =  '".$_SESSION['u_id']."'))
						//	";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result !== FALSE) {
		  $row = mysqli_fetch_row($result);
      return $row[0];
    }
    return 0;
	}			

	// Returns the number of all members
	function getNumberEmailsSent() {
		// Shows emails sent to the current uesr_id 
		// if email was suppressed it is not shown, 
		// unless teh current session is a scammer
    
   $userId = getUserIdFromSession();
    
		$query = "select count(*) 
							from cf_email_replies e
							where e.sender_deleted = 0 
							and e.from_user_id = " . $userId . " 
							";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result !== FALSE) {
		  $row = mysqli_fetch_row($result);
      return $row[0];
    }
    return 0;
	}			
	
	// Returns the number of whole place palups
	function getTotalNumberOfPalups() {
		$query = "select count(*) 
							from cf_palups p
							";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result !== FALSE) {
		  $row = mysqli_fetch_row($result);
      return $row[0];
    }
    return 0;
	}								


      // Returns the number of Facebook users
        function getNumberOfFacebook() {
                $query = "select count(*) from cf_users where facebook_id is not null ";
                $result = mysqli_query($GLOBALS['mysql_conn'],$query);
    if ($result !== FALSE) {
                  $row = mysqli_fetch_row($result);
      return $row[0];
    }
    return 0;
        }


		
	// Returns the number of whole place palups
function getNumberOfPalups() {
  $userId = getUserIdFromSession();
  
	$query = "select count(*) 
						from cf_palups p
						where p.user_id = " . $userId . " 
						and active = 1
						";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
  if ($result !== FALSE) {
	  $row = mysqli_fetch_row($result);
    return $row[0];
  }
  return 0;
}								

  // Return user ID (integer) or NULL
  function getUserIdFromSession() {
    if (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) {
        return (int) $_SESSION['u_id'];
    }
    
    return FALSE;
  }

	// Returns the number of whole place palups, for an advert
	function getNumberOfAdPalups($offered_id) {
		$query = "select count(*) 
							from cf_palups p, cf_wanted w
							where p.active = 1
							and p.offered_id = '".$offered_id."'  
							and p.wanted_id = w.wanted_id
							and w.suspended = 0
                                                        and w.published = 1
                                                        and w.expiry_date > CURDATE()
  					  ";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		return $row[0];
	}						
		
	// Returns the number of offered views
	function getTotalNumberOfOfferedViews() {
		$query = "select sum(times_viewed) 
							from cf_offered
							";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];

//		$query = "select sum(times_viewed) from cf_offered where DATE_FORMAT(last_updated_date , '%Y-%m-%d') = CURDATE()";
//		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
//		$row = mysqli_fetch_row($result);
//		$today = $row[0];
		
		return $total;
	}								
	
	// Returns the number wanted views
	function getTotalNumberOfWantedViews() {
		$query = "select sum(times_viewed) 
							from cf_wanted
							";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
//		$query = "select sum(times_viewed) from cf_wanted where DATE_FORMAT(last_updated_date , '%Y-%m-%d') = CURDATE()";
//		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
//		$row = mysqli_fetch_row($result);
//		$today = $row[0];

		return $total;
	}			
	
	// Returns the number wanted views
	function getNumberofBannerAds() {
		$query = "select count(*) from cf_banners
							";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$row = mysqli_fetch_row($result);
		$total = $row[0];
		
		return $total;
	}			
	
	

												
	
	// Retuns an array of viable member / visitor ages
	// The CF system accepts all ages from 17 to 70.
	function getAgeArray($zeroLabel, $suffix = "") {
		$toReturn = array();
		if ($zeroLabel) {
			$toReturn[0] = $zeroLabel;
		}
		for($i=17;$i<=70;$i++) { $toReturn[$i] = $i.$suffix; }
		return $toReturn;
	}
    
	function getAgeRangeArray($zeroLabel) {
        return array(
            0 => $zeroLabel,
            '18-20' => '18-20',
            '21-25' => '21-25',
            '26-30' => '26-30',
            '31-35' => '31-35',
            '36-40' => '36-40',
            '41-45' => '41-45',
            '45-50' => '45-50',
            '51-60' => '51-60',
            '60' => '60+',
        );
	}
	
	// Returns a array of all countries
	function getCountryArray() {
		$query = "select country_id,name from cf_countries order by sort asc";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		while($row = mysqli_fetch_assoc($result)) {
			$toReturn[$row['country_id']] = $row['name'];
		}
		return $toReturn;
	}
	
	// Returns an array of 31 days of the year
	function getDaysArray() {
		for($i=1;$i<=31;$i++) {
			$temp = ($i < 10)? "0".$i:$i;
			$tempArray[str_pad($temp,2,"0")] = str_pad($temp,2,"0");
		}
		return $tempArray;
	}
	
	// Returns an array of the twelve months
	function getMonthsArray() {
		return array (
			"01"=>"January",
			"02"=>"February",
			"03"=>"March",
			"04"=>"April",
			"05"=>"May",
			"06"=>"June",
			"07"=>"July",
			"08"=>"August",
			"09"=>"September",
			"10"=>"October",
			"11"=>"November",
			"12"=>"December"
		);
	}
		
	// Returns an array of "terms" used for minimum and maximum length of stay
	function getTermsArray($type) {
		if ($type == "minimum") {
			return array (
				"0"		=>	"No minimum",
				"1"		=>	"1 week",				
				"2"		=>	"2 weeks",
				"4"		=>	"4 weeks",
				"6"		=>	"6 weeks",
				"8"		=>	"8 weeks",
				"10"	=>	"10 weeks",
				"12"	=>	"3 months",
				"16"	=>	"4 months",
				"20"	=>	"5 months",
				"24"	=>	"6 months",
				"28"	=>	"7 months",
				"32"	=>	"8 months",
				"36"	=>	"9 months",
				"40"	=>	"10 months",
				"44"	=>	"11 months",
				"52"	=>	"12 months"
			);
		} else {
			return array (
				"999"	=>	"No maximum",
				"1"		=>	"1 week",				
				"2"		=>	"2 weeks",
				"4"		=>	"4 weeks",
				"6"		=>	"6 weeks",
				"8"		=>	"8 weeks",
				"10"	=>	"10 weeks",
				"12"	=>	"3 months",
				"16"	=>	"4 months",
				"20"	=>	"5 months",				
				"24"	=>	"6 months",
				"28"	=>	"7 months",
				"32"	=>	"8 months",
				"36"	=>	"9 months",
				"40"	=>	"10 months",
				"44"	=>	"11 months",				
				"52"	=>	"12 months"
			);
		}
	}
	
	// Returns an array of the three possible accommodation types
	function getAccommodationTypeArray() {
		return array (
			"flat share" => "House / Flatshare (a house or flat shared with others)",
			"family share" => "Family Share (live with a family or a married couple)",
			"whole place" => "Whole Place (an unoccupied flat or house)"			
		);
	}
	
	// Returns an array of the parking options
	function getParkingArray() {
		return array (
			"Off street"=>"Off street",
			"Free on-street"=>"Free on-street",
			"Permit on-street"=>"Permit on-street",
			"None"=>"None",
			"Unknown"=>"Unknown"
		);
	}
	
	// Returns an array for the "bedroom" choices
	function getBedroomArray($includeZero = false, $none = "", $single_suffix = "", $plural_suffix = "") {
		
//		if ($includeZero == TRUE) {
//			// Only called with includeZero = TRUE for the "double bedrooms" drop down
//			// Create the text to insert
//			$i = 'double ';
//		} else {
//			$i = '';
//		}
		
		$toReturn = array(
			0		=>	$none,
			1		=>	"1 ".$single_suffix,
			2		=>	"2 ".$plural_suffix,
			3		=>	"3 ".$plural_suffix,
			4		=>	"4 ".$plural_suffix,
			5		=>	"5 ".$plural_suffix,
			6		=>	"6 ".$plural_suffix,
			7		=>	"7 ".$plural_suffix,
			8		=>	"8 ".$plural_suffix			
		);
		if (!$includeZero) {
			unset($toReturn[0]);
		}
		return $toReturn;

	}
	
	// Returns an array for the "furnished" choices
	function getFurnishedArray() {
		return array (
			"furnished" => "Furnished",
			"unfurnished" => "Unfurnished",
			"furnished or unfurnished" => "Furnished or Unfurnished"
		);
	}
	
	// Returns an array for the "occupation" choices
	function getOccupationArray($includeZero = false) {
		$toReturn = array();
		if ($includeZero) {
			$toReturn[0] = "-- Not specified --";
		}
		$toReturn["Students (<22yrs)"] = "Students (<22yrs)";
		$toReturn["Mature Students"] = "Mature Students";
		$toReturn["Professionals"] = "Professionals";
		return $toReturn;
	}
	
	// Returns an array for the "gender" choices
	function getGenderArray($mixedLabel = "Male(s) or female(s)") {
		return array(
			"Male(s)" => "Male(s)",
			"Female(s)" => "Female(s)",
			"Mixed" => $mixedLabel
		);
	}
	
	// Retusn an array for the "expiry_date" choices
	function getExpirationArray() {
		return array(
			"1 week"  => "1 week",
			"2 weeks" => "2 weeks",
			"3 weeks" => "3 weeks",
			"4 weeks" => "4 weeks",
			"5 weeks" => "5 weeks"
		);
	}
	
	// Return an array for the number of male & female members of family
	function getMemberNumberArray() {
		return array(
			"0"=>"0",
			"1"=>"1",
			"2"=>"2",
			"3"=>"3",
			"4+"=>"4+"
		);
	}
	
	// Returns an array for the various sort options (depending on the parameter)
	function getSortArray($type) {
	
		switch ($type) {
		
			case "num":
				return array(
					"10"=>"10 results",
					"20"=>"20 results",
					"50"=>"50 results"
				);
				break;
				
			case "field":
				return array(
					"price_pcm desc"		=>		"Price, highest first",
					"price_pcm asc"			=>		"Price, lowest first",
					"available_date desc"	=>		"Date available, descending",
					"available_date asc"	=>		"Date available, ascending",
					"created_date desc"		=>		"Advert age, newest first",
					"created_date asc"		=>		"Advert age, oldest first",
					"last_login_days asc"	=>		"Days since login, descending",
					"last_login_days desc"	=>		"Days since login, ascending"					
				);
				break;
				
			case "field_ad_matches":
				return array(
					"price_pcm desc"		=>		"Price, highest first",
					"price_pcm asc"			=>		"Price, lowest first",
					"available_date desc"	=>		"Date available, descending",
					"available_date asc"	=>		"Date available, ascending",
					"created_date desc"		=>		"Advert age, newest first",
					"created_date asc"		=>		"Advert age, oldest first"
				);
				break;				
				
			case "suit-offered":
				return array(
					"0"			=>	"Male or Female",
					"Female(s)"	=>	"Female",
					"Male(s)"	=>	"Male",
					"Couple"	=>	"A married couple",
					"Family"	=>	"A family"
				);
				break;
				
			case "suit-wanted":
				return array(
					"0"			=>	"Male and/or Female",
					"Female"	=>	"Female",
					"Male"		=>	"Male",
					"Couple"	=>	"A married couple",
					"Family"	=>	"A family"			
				);			
				break;

		}
	
	}
	
	// Returns an image name from a given list
	// If a member does not choose an image, we choose a random one, to provide variety
	function getRandomImage() {
		$input = array(
				"armchair-2.gif",
				"armchair-1.gif",
				"cooking-2.gif",
				"flowers-1.gif",
				"flowers-2.gif",
				"frisby.gif",
				"lamp.gif",
				"kitchen.gif",
				"pots-and-pans.gif",
				"retro-couch.gif",
				"sofa.gif",
				"spring-clean.gif",
				"telephone.gif",
				"tea.gif",
				"toaster.gif",
				"veranda-chair.gif",
				"hat-stand.gif");		
		shuffle($input);
		return $input[1];
    }
	

	// Returns an array of miles
	function getMilesArray() {
		// TODO: 1,2,3,4,5,7,10,15,20,25
		return array (
			"1"		=>	"1 mile",
			"2"		=>	"2 miles",
			"3"		=>	"3 miles",
		//	"4"		=>	"4 miles",
			"5"		=>	"5 miles",
			"7"		=>	"7 miles",
			"10"	=>	"10 miles",	
			"15"	=>	"15 miles",
			"20"	=>	"20 miles",				
			"25"	=>	"25 miles"
		);
	}	
	
	// Returns an array of the various property description
	// Family House Share, Family Flatshare, Whole House, Whole Flat, Flat share, House Share
	function getPropertyDescriptionArray($includeZero = false) {
		return array (
			"0" => "a flatshare or house share",
			"a family house share" => "a family house share",
			"a family flat share" => "a family flat share",
			"a whole house" => "a whole house",
			"a whole flat" => "a whole flat",
			"a flat share" => "a flat share",
			"a house share" => "a house share"
		);
	}
	
	/* Returns a two dimensional array with the column header links and icons (if needed)
	 *
	 * For example, the headerMapping from offered-ads.php is the following:
	 * $headerMapping = array (
	 *		"ID" => "offered_id",
	 *		"Member name" => "surname,first_name",
	 *		"Created" => "created_date",
	 *		"Last Updated" => "last_updated_date",
	 *		"Views" => "times_viewed",
	 *		"Status" => "published,approved,paid_for";
	 *	);
	 *
	 */
	function createHeaderLinks($headerMapping,$link,$orderBy,$direction) {
		
		// $hl is the two dimensional array we will return
		$hl = NULL;
		
		// Iterate through the headerMapping array and for each column
		// create the appropriate link and icons (if needed)
		foreach($headerMapping as $columnName => $dbOrderField) {
			
			$hl[$columnName]['href'] = $_SERVER['PHP_SELF']."?orderBy=".$dbOrderField;
			
			if ($orderBy == $dbOrderField) {
				// And icon of the current direction
				$hl[$columnName]['icon']  = '&nbsp;<img src="../images/icon-'.$direction.'ending.gif" width="7" height="7" border="0"/>';			
				// Display link with reversed direction
				$direction = ($direction == "desc")? "asc":"desc";
				$hl[$columnName]['href'] .="&direction=".$direction;
			} else {
				// No icon
				$hl[$columnName]['icon']  = "";				
				// Simple "desc" link
				$hl[$columnName]['href']  = $_SERVER['PHP_SELF']."?orderBy=".$dbOrderField."&direction=desc";
			}
			
			$hl[$columnName]['href'] .= $link;
		
		}
		return $hl;
		
	}
	
	// Returns a list of all files in a directory (sorted alphabetically)
	function parseDirectory($dir) {
		
		// $toReturn is an array that will contain the list of files
		$toReturn = array();
		
		$path = opendir($dir);
		while (false !== ($file = readdir($path))) {
			if($file!="." && $file!=".." && $file != ".htaccess") {
				if(is_file($dir."/".$file)) {
					$files[]=$file;
				} else {
					$dirs[]= $dir."/".$file;
				} 
			}
		}


		if ($files) {
			natcasesort($files);
			foreach ($files as $file){
				$toReturn[] = urldecode($file);
			}   
		}

		closedir($path);   
		return $toReturn;

	}	
	
	// Removes the extension (.*** || .****) from a string
	function removeExtension($str) {
		$ext = strrchr($str, '.');
		if($ext !== false) {
			$str = substr($str, 0, -strlen($ext));
		}
		return $str;
	} 
	
	/* Creates the profile card
	 * <div id="profile">
	 * 	<img src="images/pictures/no-image.gif" width="120" height="90" class="thumbnail" />
	 * 	<h2 id="profileName">Angelos Chaidas</h2>
	 * 	<p id="profileEmail">angelos@culture-mind.com</p>
	 * 	<p id="profileGender">Male, 25 years old.</p>
	 * 	<p id="profileStatisticsHeader">Member statistics:</p>
	 * 	<table width="100%" border="0" cellspacing="0" cellpadding="4" id="profileStatistics">
	 * 		<tr class="trOdd">
	 * 			<td>Number of offered ads: </td>
	 * 			<td>0</td>
	 * 		</tr>
	 * 		<tr class="trEven">
	 * 			<td>Number of wanted ads: </td>
	 * 			<td>0</td>
	 * 		</tr>
	 * 		<tr class="trOdd">
	 * 			<td>Join CFS: </td>
	 * 			<td>Monday, 25th Juie </td>
	 * 		</tr>
	 * 		<tr class="trEven">
	 * 			<td>Last login: </td>
	 * 			<td>Saturday, 25th June 2006 </td>
	 * 		</tr>
	 * 		<tr class="trOdd">
	 * 			<td>Last profile update: </td>
	 * 			<td>sadfsadfq</td>
	 * 		</tr>
	 * 		<tr class="trEven">
	 * 			<td>Subscribed to news: </td>
	 * 			<td>Yes</td>
	 * 		</tr>
	 * 	</table>
	 * </div>
	 */	
	function createProfile($user_id) {
		
		// Get all user information (and format dates accordingly)
		$query = "
			select
				u.user_id,
				date_format(created_date,'%d/%m/%Y - %k:%i') as `created_date`,
				date_format(last_updated_date,'%d/%m/%Y - %k:%i') as `last_updated_date`,
				date_format(last_login,'%d/%m/%Y - %k:%i') as `last_login`,
				email_address,first_name,surname,gender,
				DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(dob)), '%y') as `age`,
				if(news_opt_in,'Yes','No') as `news_opt_in`,
				ifnull(o.count,0) as `offered_ads`,
				ifnull(w.count,0) as `wanted_ads`
			from cf_users as `u`
			left join ( 
				select user_id,count(offered_id) as `count` from cf_offered group by user_id
			) as `o` on o.user_id = u.user_id
			left join (
				select user_id,count(wanted_id) as `count` from cf_wanted group by user_id
			) as `w` on w.user_id = u.user_id
			where u.user_id = '".$user_id."';
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (!$result) {
			return "Could not get user profile";
		} else {
			$data = mysqli_fetch_assoc($result);
			$toReturn  = '<div id="profile">'."\n";
			$toReturn .= '<h2 id="profileName">'.$data['first_name'].' '.$data['surname'].'</h2>'."\n";
			$toReturn .= '<p id="profileEmail">'.$data['email_address'].'</p>'."\n";
			/*if ($data['gender'] || $data['age']) {
				$toReturn .= '<p id="profileGender">';
				if ($data['gender']) { $toReturn .= ($data['gender'] == "m")? "Male, ":"Female, "; }
				if ($data['age']) { $toReturn .= $data['age']." years old."; } else { $toReturn = substr($toReturn,0,-2); }
				$toReturn .= '</p>'."\n";
			}*/
			$toReturn .= '<p id="profileStatisticsHeader">Member statistics:</p>'."\n";
			$toReturn .= '<table width="100%" border="0" cellspacing="0" cellpadding="4" id="profileStatistics">'."\n";
			$toReturn .= '<tr class="trOdd">'."\n";
			$toReturn .= '<td>Number of offered ads: </td>'."\n";
			$toReturn .= '<td>'.$data['offered_ads'].'</td>'."\n";
			$toReturn .= '</tr>'."\n";
			$toReturn .= '<tr class="trEven">'."\n";
			$toReturn .= '<td>Number of wanted ads: </td>'."\n";
			$toReturn .= '<td>'.$data['wanted_ads'].'</td>'."\n";
			$toReturn .= '</tr>'."\n";
			$toReturn .= '<tr class="trOdd">'."\n";
			$toReturn .= '<td>Joined Christian Flatshare on: </td>'."\n";
			$toReturn .= '<td>'.$data['created_date'].'</td>'."\n";
			$toReturn .= '</tr>'."\n";
			$toReturn .= '<tr class="trEven">'."\n";
			$toReturn .= '<td>Last login: </td>'."\n";
			$toReturn .= '<td>'.$data['last_login'].'</td>'."\n";
			$toReturn .= '</tr>'."\n";
			$toReturn .= '<tr class="trOdd">'."\n";
			$toReturn .= '<td>Last profile update: </td>'."\n";
			$toReturn .= '<td>'.$data['last_updated_date'].'</td>'."\n";
			$toReturn .= '</tr>'."\n";
			$toReturn .= '<tr class="trEven">'."\n";
			$toReturn .= '<td>Subscribed to news: </td>'."\n";
			$toReturn .= '<td>'.$data['news_opt_in'].'</td>'."\n";
			$toReturn .= '</tr>'."\n";
			$toReturn .= '</table>'."\n";
			$toReturn .= '</div>'."\n";
			return $toReturn;
		}		
		
	}
	
	function accountSuspended() {
	
		if (isset($_SESSION['u_id']) && !$_SESSION['u_id']) {
			$query = "select account_suspended from cf_users where user_id = ".$_SESSION['u_id'];
			$result= mysqli_query($GLOBALS['mysql_conn'], $query);
			$user_record = mysqli_fetch_assoc($result);	
			($user_record['account_suspended']==1)?$suspended=TRUE:$suspended=FALSE;
		} else { 
			$suspended = FALSE; 
		}
		
		return $suspended;
	}
	
	// Creates the member's menu
	function createMembersMenu() {
	
		$currentPage = substr(strrchr($_SERVER['PHP_SELF'],"/"),1);
				
		// Get account suspended status, TRUE or FALSE
		$suspended = accountSuspended();
		
		//if ($currentPage == "index.php") {
		//	$toReturn .= '<h2 class="success">Welcome '.$_SESSION['u_name'].'!</strong></h2>'."\n";
		//} else {
		//}
		//$toReturn .= '<br/><a href="logout.php">Logout</a>';

		// Start the grey box
    $toReturn = NULL;
		$toReturn .= '<div class="box_grey mb20">'."\n";
		$toReturn .= '<div class="tr"><span class="l"></span><span class="r"></span></div>'."\n";
		$toReturn .= '<div class="mr">'."\n";
		
		// For advertisers
		if (isset($_SESSION['u_access']) && $_SESSION['u_access'] == "advertiser") {
		
			$toReturn .= '<a style="float: right;" href="logout.php"><strong>logout</strong></a>'."\n";
			$toReturn .= '<h2 class="mt0">Advertisers Menu</h2>'."\n";
			$toReturn .= '<p class="mb0">'."\n";
			$toReturn .= '<a href="advertisers.php">Your banners</a><br/>'."\n";
			$toReturn .= '<a href="advertisers-manage-banner.php">Upload a new banner</a><br/>'."\n";			
  		$toReturn .= '<br /><a href="your-account-change-password.php">Change password</a><br/>';

		  $toReturn .= '</p>';		
		} else {
		
			// Member's menu content
			$toReturn .= '<a href="logout.php" style="float:right;"><strong>logout</strong></a>';	
			$toReturn .= '<h2 class="mt0">Member\'s Menu</h2>';
	
			if (!$suspended) {
				// Post adverts
				$toReturn .= '<strong>Post adverts</strong><br/>';
				$toReturn .= '<a href="post-choice.php">Post an Accommodation Advert</a><br/>'."\n";
				$toReturn .= '<br/>'."\n";
			}
		
			// View your ads
			$toReturn .= '<strong>Your ads and messages</strong><br />';
			if (!$suspended) {				
				if ($currentPage == "your-account-manage-posts.php") { $toReturn .= '<strong>'; }
					$toReturn .= '<a href="your-account-manage-posts.php">Your ads</a><br/>';
				if ($currentPage == "your-account-manage-posts.php") { $toReturn .= '</strong>'; }
			}
			
			if ($currentPage == "your-account-received-messages.php") { $toReturn .= '<strong>'; }
			$NumberEmailsInbox = getNumberEmailsInbox();
			if ($NumberEmailsInbox > 0) { $NumberEmailsInbox = '('.$NumberEmailsInbox.')'; } else { $NumberEmailsInbox = ''; }
			$toReturn .= '<a href="your-account-received-messages.php">Your messages '.$NumberEmailsInbox.'</a></strong><br/>';
			if ($currentPage == "your-account-received-messages.php") { $toReturn .= '</strong>'; }

			if ($currentPage == "your-account-sent-messages.php") { $toReturn .= '<strong>'; }
			$NumberEmailsSent = getNumberEmailsSent();
			if ($NumberEmailsSent > 0) { $NumberEmailsSent = '('.$NumberEmailsSent.')'; } else {	$NumberEmailsSent = ''; }
			$toReturn .= '<a href="your-account-sent-messages.php">Your sent messages '.$NumberEmailsSent.'</a><br/>';
			if ($currentPage == "your-account-sent-messages.php") { $toReturn .= '</strong>'; }
			
			$toReturn .= '<p class="mt5 mb0"></p>';
			if ($currentPage == "your-account-saved-ads.php") { $toReturn .= '<strong>'; }
	  	$NumberSavedAds = getNumberSavedAds();
			if ($NumberSavedAds > 0) { $NumberSavedAds = '('.$NumberSavedAds.')'; } else { $NumberSavedAds = ''; }			
			$toReturn .= '<a href="your-account-saved-ads.php">Your saved ads '.$NumberSavedAds.'</a><br/>';
			if ($currentPage == "your-account-saved-ads.php") { $toReturn .= '</strong>'; }
	

				
			if ($currentPage == "your-account-whole-place-palups.php") { $toReturn .= '<strong>'; }
			$NumberOfPalups = getNumberOfPalups();
			if ($NumberOfPalups > 0) { $NumberOfPalups = '('.$NumberOfPalups.')'; } else {	$NumberOfPalups = ''; }			
			$toReturn .= '<a href="your-account-whole-place-palups.php">Your whole place pal-ups '.$NumberOfPalups.'</a><br/>';
			if ($currentPage == "your-account-whole-place-palups.php") { $toReturn .= '</strong>'; }

			$toReturn .= '<br />';
//			$toReturn .= '<a href="your-account-your-messages.php">Your messages</a><br/><br/>';
//			if ($currentPage == "your-account-your-messages.php") { $toReturn .= '</strong>'; }					
//	        $toReturn .= 'View, edit and delete your ads.<br/>Add photos, un-expire ads,<br/> and "<a href="facebook-your-ad.php">Facebook your ad</a>"'; 
			
			// View your acccount
			if (!$suspended) {			
			$toReturn .= '<strong>Your account</strong><br />';		
	
	
				if ($currentPage == "your-suspend-account.php") { $toReturn .= '<strong>'; }						
				$toReturn .= '<a href="your-suspend-account.php">Suspend your account</a><br/>';
				if ($currentPage == "your-suspend-account.php") { $toReturn .= '</strong>'; }						
				
				if ($currentPage == "your-account-change-password.php") { $toReturn .= '<strong>'; }		
				$toReturn .= '<a href="your-account-change-password.php">Change password</a><br/>';
				if ($currentPage == "your-account-change-password.php") { $toReturn .= '</strong>'; }		
	
				if ($currentPage == "your-account-change-name.php") { $toReturn .= '<strong>'; }
				$toReturn .= '<a href="your-account-change-name.php">Change email or name</a><br/>';
				if ($currentPage == "your-account-change-name.php") { $toReturn .= '</strong>'; }			
	
			} else {
			$toReturn .= '<strong>Your account is currently suspended</strong><br />';
				if ($currentPage == "your-account-manage-posts.php") { $toReturn .= '<strong>'; }
					$toReturn .= '<a href="your-account-manage-posts.php">Un-suspend your account</a><br/>';
				if ($currentPage == "your-account-manage-posts.php") { $toReturn .= '</strong>'; }
			}
			
			// Edit your profile
			/*$toReturn .= '<li>';
			if ($currentPage == "your-account-edit-profile.php") { $toReturn .= '<strong>'; }
			$toReturn .= '<a href="your-account-edit-profile.php">Edit your profile</a>';
			if ($currentPage == "your-account-edit-profile.php") { $toReturn .= '</strong>'; }
			$toReturn .= '</li>'."\n";*/
			// Change password
			
				// Logout
			//$toReturn .= '<br/><a href="logout.php">Logout</a>';
			
			$toReturn .= '<br />'."\n";
		
			// How to post an advert
			if ($_SESSION['show_hidden_ads']=='yes') { 
				$toReturn .= 'Searches will <a href="'.$_SERVER['PHP_SELF'].'?show_hidden_ads=no">show</a> your hidden ads<br />';
			} else { 
				$toReturn .= 'Searches will <a href="'.$_SERVER['PHP_SELF'].'?show_hidden_ads=yes">hide</a> your hidden ads<br />';
			}
			
		}	
			
	    // Close the grey box
		$toReturn .= '</div>'."\n";
		$toReturn .= '<div class="br"><span class="l"></span><span class="r"></span></div>'."\n";
		$toReturn .= '</div>'."\n";		
		
		return $toReturn;
	}
	


	// Returns the tracking code for each page
	function getTrackingCode($action="",$name="",$group="") {
    $toReturn = NULL;
	   if (ENABLE_TRACKING == "YES") {
		// Google analytics
		$toReturn .= '<!-- Start GOOGLE ANALYTICS -->
		<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			var pageTracker = _gat._getTracker("UA-701233-1");
			pageTracker._initData();
			pageTracker._trackPageview();
		</script>
		<!-- End GOOGLE ANALYTICS -->'."\n\n";
		} else {			
		$toReturn = "";	
	    }
		return $toReturn;
	}
	
	
	// Returns the proper ratio for an image when resized
	function getImgRatio($img,$w="",$h="",$wmax="",$hmax="") {
		
		if (!$w && !$h) { return false; }
		$size = @getimagesize($img); // Get the size of the image
		if (!$size) { return false; }
		$wbig = $size[0];
		$hbig = $size[1];
		// We have a thumb width and need to calculate the height
		if (!$h) { $h = $hbig / $wbig * $w; }
		// We have a thumb height and need to calculate the width
		if (!$w) { $w = $wbig / $hbig * $h; }
		// Calculated width exceeds wmax? : Change the height
		if ($wmax && $w > $wmax) { $w = $wmax; $h = $hbig / $wbig * $w; }
		// Calculated height exceeds hmax? : Change the width
		if ($hmax && $h > $hmax) { $h = $hmax; $w = $wbig / $hbig * $h; }
		return array(round($w),round($h));
	
	}
	
	// Removes the "http://" part from the beginning of a string
	function strip_http($s) {
		if (substr($s,0,7) == "http://") {
			$s = substr($s,7);
		}
		return $s;
	}
	
	function pre($array,$removeKeys=FALSE) {
		// Stops execution and outputs the contents of the array
		// Optional : Removes all the keys that start with a "button"
		if ($removeKeys) {
			foreach($array as $key=>$value) {
				if (substr($key,0,6) == "button") {
					unset($array[$key]);
				}
			}
		}
		die('<pre>'.print_r($array,true).'</pre>');
	}
	
	// Notifies the user that his / her ad was published.
	function notifyUser($id, $post_type, $twig, $admin=FALSE) {

		if ($admin) { $path = '../'; }
		
		// Send an email to the user about the ad
		if ($post_type == "offered") {
			$query = "
				select 
				date_format(o.expiry_date,'%M %D') as `expiry_date`,
				o.offered_id,
				o.postcode,
				o.bedrooms_available,
				o.bedrooms_total,
				o.accommodation_type,
				o.room_share,
				o.building_type,
				o.street_name,
				o.published,
                o.country,
                o.street,
                o.area,
				(CASE IFNULL(o.town_chosen,'')
			     WHEN '' THEN j.town 
			     ELSE o.town_chosen
  			      END) as town,
				u.first_name,
				u.surname,
				u.password,
				u.email_address
				from cf_offered as `o`
				left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 
				left join cf_users as `u` on u.user_id = o.user_id 
				where o.offered_id = '".$id."'
                                and o.user_id = '".$_SESSION['u_id']."';	
			";
		} else {
			$query = "
				select 
				date_format(w.expiry_date,'%M %D') as `expiry_date`,
				w.wanted_id,
				w.postcode,
				w.bedrooms_required,
				w.distance_from_postcode,
				w.location,	
				w.published,
                w.country,
                w.street,
                w.area,	
				u.first_name,
				u.surname,
				u.password,
				u.email_address
				from cf_wanted as `w`
				left join cf_users as `u` on u.user_id = w.user_id 
				where w.wanted_id = '".$id."'
				and w.user_id = '".$_SESSION['u_id']."';
 			";		
		}
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		
		$title = getAdTitle($ad,$post_type,TRUE,TRUE);
		$ad_link = 'http://'.SITE.'details.php?id='.$id.'&post_type='.$post_type;
		
		// Create the hash of last_login and email_address
		$hash = md5($ad['password'].$ad['email_address']);
		
		if ($post_type == "offered") { 
			$date_type = "available from"; 
			$keep_link    = 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=offered&id='.$ad['offered_id'].'&hash='.$hash;
			$suspend_link = 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=offered&id='.$ad['offered_id'].'&hash='.$hash;
			} else { 
			$date_type = "wanted from"; 
			$keep_link    = 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=wanted&id='.$ad['wanted_id'].'&hash='.$hash;
			$suspend_link = 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=wanted&id='.$ad['wanted_id'].'&hash='.$hash;			
			}
		
		// Only email user if ad has been published
	//	if ($ad['published'] || $ad['accommodation_type'] == 'whole place') {
            
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/published.html.twig', array(
                'first_name' => $ad['first_name'],
                'ad' => array('title' => strip_tags($title), 'url' => $ad_link, 'expiry_date' => $ad['expiry_date']),
                'date_type' => $date_type,
                'keep_url' => $keep_link,
                'suspend_url' => $suspend_link,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Your advert has been published:' . strip_tags($title);
            $to = $ad['email_address'];
 
            $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
            $sent = $CFSMailer->sendMessage($msg);            

            // Alert Ryan for new Whole Place ads
            // Using this approach as BCC has issues
				    if ($ad['accommodation_type'] == 'whole place') {
							$to ='ryanwdavies+newadvert@gmail.com';
						  $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
              $sent = $CFSMailer->sendMessage($msg);	
  					}
		
			if ($sent > 0) {
				return true; 
			} else {
				return false;
			}
			
	//	} else {
		
			return false;
	//	}
	}	
	//
	
	function makeClickableLinks($text) {
        return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $text);
	}
	
	function clickable_link($text) 
	{ 
	# this functions deserves credit to the fine folks at phpbb.com 
	
	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text); 
	
	// pad it with a space so we can match things at the start of the 1st line. 
	$ret = ' ' . $text; 
	
	// matches an "xxxx://yyyy" URL at the start of a line, or after a space. 
	// xxxx can only be alpha characters. 
	// yyyy is anything up to the first space, newline, comma, double quote or < 
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret); 
	
	// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing 
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-" 
	// zzzz is optional.. will contain everything up to the first space, newline, 
	// comma, double quote or <. 
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret); 
	
	// matches an email@domain type address at the start of a line, or after a space. 
	// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".". 
	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret); 
	
	// Remove our padding.. 
	$ret = substr($ret, 1); 
	return $ret; 
	} 
	
    // WARNING CONFLICT WITH CORE FUNCTION

	// $src_img - a GD image resource
// $angle - degrees to rotate clockwise, in degrees
// returns a GD image resource
// USAGE:
// $im = imagecreatefrompng('test.png');
// $im = imagerotate($im, 15);
// header('Content-type: image/png');
// imagepng($im);
function CFSimageRotate($src_img, $angle, $bicubic=false) {
  
   // convert degrees to radians
   $angle = $angle + 180;
   $angle = deg2rad($angle);
  
   $src_x = imagesx($src_img);
   $src_y = imagesy($src_img);
  
   $center_x = floor($src_x/2);
   $center_y = floor($src_y/2);

   $cosangle = cos($angle);
   $sinangle = sin($angle);

   $corners=array(array(0,0), array($src_x,0), array($src_x,$src_y), array(0,$src_y));

   foreach($corners as $key=>$value) {
     $value[0]-=$center_x;        //Translate coords to center for rotation
     $value[1]-=$center_y;
     $temp=array();
     $temp[0]=$value[0]*$cosangle+$value[1]*$sinangle;
     $temp[1]=$value[1]*$cosangle-$value[0]*$sinangle;
     $corners[$key]=$temp;    
   }
   
   $min_x=1000000000000000;
   $max_x=-1000000000000000;
   $min_y=1000000000000000;
   $max_y=-1000000000000000;
   
   foreach($corners as $key => $value) {
     if($value[0]<$min_x)
       $min_x=$value[0];
     if($value[0]>$max_x)
       $max_x=$value[0];
   
     if($value[1]<$min_y)
       $min_y=$value[1];
     if($value[1]>$max_y)
       $max_y=$value[1];
   }

   $rotate_width=round($max_x-$min_x);
   $rotate_height=round($max_y-$min_y);

   $rotate=imagecreatetruecolor($rotate_width,$rotate_height);
   imagealphablending($rotate, false);
   imagesavealpha($rotate, true);

   //Reset center to center of our image
   $newcenter_x = ($rotate_width)/2;
   $newcenter_y = ($rotate_height)/2;

   for ($y = 0; $y < ($rotate_height); $y++) {
     for ($x = 0; $x < ($rotate_width); $x++) {
       // rotate...
       $old_x = round((($newcenter_x-$x) * $cosangle + ($newcenter_y-$y) * $sinangle))
         + $center_x;
       $old_y = round((($newcenter_y-$y) * $cosangle - ($newcenter_x-$x) * $sinangle))
         + $center_y;
      
       if ( $old_x >= 0 && $old_x < $src_x
             && $old_y >= 0 && $old_y < $src_y ) {

           $color = imagecolorat($src_img, $old_x, $old_y);
       } else {
         // this line sets the background colour
         $color = imagecolorallocatealpha($src_img, 255, 255, 255, 127);
       }
       imagesetpixel($rotate, $x, $y, $color);
     }
   }
   
  return($rotate);
  }


 function getIPNumber($ipAddress) {
	    // Return the IP number
			$query = "SELECT (SUBSTRING_INDEX('".$ipAddress."','.',1)*(256*256*256))
						+ (SUBSTRING_INDEX(SUBSTRING_INDEX('".$ipAddress."','.',2),'.',-1)*(256*256))
						+ (SUBSTRING_INDEX(SUBSTRING_INDEX('".$ipAddress."','.',3),'.',-1)*256)
						+ SUBSTRING_INDEX(SUBSTRING_INDEX('".$ipAddress."','.',4),'.',-1) as `ip_number`";
			$debug = debugEvent("Selection query", $query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$ip_number = mysqli_fetch_assoc($result);
		return $ip_number['ip_number'];
}
	
 function recordLogin($email, $ipNumber) {
     connectToDB();
     
      // Get the location for an IP address
			// The SQL is written in two parts it suits MySQL optmiser
      
			// Get ip_from for the cf_ip table
			$query =  "SELECT MAX(ip_from) as `ip_from` from cf_ip b WHERE ip_from < ".$ipNumber." limit 1";
			$debug = debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$ip_from = mysqli_fetch_assoc($result);
      
			// get IP details
			$query =  "SELECT country_name, region, city, ip_name
        FROM cf_ip  
        WHERE ip_from = ".$ip_from['ip_from'];
			$debug .= debugEvent("Select IP info query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$ip_location = mysqli_fetch_assoc($result);
			
	  	// Get user details by email
      $query = "SELECT user_id, suppressed_replies
        FROM cf_users 
        WHERE email_address = LOWER('".$email."')";
			$debug .= debugEvent("User info query",$query);					  
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
      $user_info = mysqli_fetch_assoc($result);
			
			if (isset($user_info['user_id'])){$user_id = $user_info['user_id'];}else{$user_id = 0;}
			if (isset($user_info['suppressed_replies'])) {
        $suppressed_replies = $user_info['suppressed_replies'];
			}
      else{
        $suppressed_replies = 0;
      }
			
		 	// Record login attempt
	 	    $query  = "INSERT INTO cf_user_logins ( 
							   user_id, 
							   email_address, 
							   login_date, 
							   suppressed_replies, 
							   ip, 
							   ip_number, 
							   ip_city,
							   ip_region,
							   ip_country,
							   ip_name
			  	) VALUES ( ";
			$query .= $user_id.",";
			$query .= "'".$email."',"; // email address passed in
			$query .= "NOW(),"; // last_updated_date
			$query .= $suppressed_replies.",";
			$query .= "'".$_SERVER['REMOTE_ADDR']."',";
			$query .= $ipNumber.",";
			$query .= "'".$ip_location['city']."',";
			$query .= "'".$ip_location['region']."',";
			$query .= "'".$ip_location['country_name']."',";
			$query .= "'".$ip_location['ip_name']."'";									
			$query .= ")";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$debug .= debugEvent("Insert query",$query);
		}


	function createSummaryForLogins($user_id) {
			$summary = null;
			$query =  "SELECT login_instance_id,
					   login_date,
					   suppressed_replies,
					   ip,
					   ip_city,
					   ip_region,
					   ip_country,
					   ip_name
					   FROM cf_user_logins
					   WHERE user_id = ".$user_id."
					   ORDER BY login_instance_id DESC";
			$debug .= debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}			

			$summary .= '<table width="100%" border="0" cellpadding="4" cellspacing="0" class="greyTable">'."\n";
			$summary .= '<tr>'."\n";
			$summary .= '<th >Login ID</th>'."\n";
			$summary .= '<th >Login Date</th>'."\n";
			$summary .= '<th >Suppressed_replies</th>'."\n";
			$summary .= '<th >IP</th>'."\n";
			$summary .= '<th >City</th>'."\n";			
			$summary .= '<th >Region</th>'."\n";			
			$summary .= '<th >Countrty</th>'."\n";									
			$summary .= '<th >IP Name</th>'."\n";												
			$summary .= '</tr>'."\n";
			
			for($i=0;$i<$num_results;$i++) {
			$logins = mysqli_fetch_assoc($result);
				$summary .= '<tr>'."\n";
				$summary .= '<td >'.$logins['login_instance_id'].'</td>'."\n";
				$summary .= '<td >'.$logins['login_date'].'</td>'."\n";
				$summary .= '<td >'.$logins['suppressed_replies'].'</td>'."\n";
				$summary .= '<td >'.$logins['ip'].'</td>'."\n";
				$summary .= '<td >'.$logins['ip_city'].'</td>'."\n";			
				$summary .= '<td >'.$logins['ip_region'].'</td>'."\n";			
				$summary .= '<td >'.$logins['ip_country'].'</td>'."\n";									
				$summary .= '<td >'.$logins['ip_name'].'</td>'."\n";		 
				$summary .= '</tr>'."\n";			 
			}
		$summary .= '</table>'."\n";						
		return $summary;
	  }

	function createSummaryForEmails($user_id) {
	   // Edit member page
			$summary = null;
			$query = "SELECT  from_user_id,
												concat_ws(u_from.first_name,u_from.surname) as from_name,
												to_user_id, 
								        concat_ws(u_to.first_name, u_to.surname ) as to_name,
						 			 	    to_ad_id, 
				 							  to_post_type, 
										    message, 
						 				    reply_date,
												e.suppressed_replies as suppressed_replies
  						FROM   cf_users u_from, 
 									   cf_users u_to, 
							       cf_email_replies e
						WHERE  e.from_user_id = u_from.user_id 
						AND    e.to_user_id   = u_to.user_id 
						AND    (e.from_user_id = ".$user_id." 
								OR  e.to_user_id = ".$user_id.")
   	  	    ORDER BY e.reply_id";
			$debug .= debugEvent("Selection query",$query);			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result != "") {
				$num_results = mysqli_num_rows($result); 
			} else {
			    $num_results = 0;
			}			

			$summary .= '<table width="100%" border="0" cellpadding="4" cellspacing="0" class="greyTable">'."\n";
			$summary .= '<tr>'."\n";
			$summary .= '<th>From Name</th>'."\n";
			$summary .= '<th>To Name</th>'."\n";
			$summary .= '<th>Stopped</th>'."\n";
			$summary .= '<th width="180">Subject</th>'."\n";
			$summary .= '<th>Message</th>'."\n";			
			$summary .= '<th>Date</th>'."\n";			
			$summary .= '</tr>'."\n";
			
			for($i=0;$i<$num_results;$i++) {
			$emails = mysqli_fetch_assoc($result);
				$summary .= '<tr>'."\n";
				if ($user_id == $emails['from_user_id']) {
					$summary .= '<td >'.$emails['from_name'].'</td>'."\n";
				} else {
					$summary .= '<td ><a href="edit-member.php?user_id='.$emails['from_user_id'].'">'.$emails['from_name'].'</a></td>'."\n";				
				}
				
			
				if ($user_id == $emails['to_user_id']) {
					$summary .= '<td >'.$emails['to_name'].'</td>'."\n";
					} else {
					$summary .= '<td ><a href="edit-member.php?user_id='.$emails['to_user_id'].'">'.$emails['to_name'].'</a></td>'."\n";				
				}
				
				$summary .= '<td >'.$emails['suppressed_replies'].'</td>'."\n";
				$summary .= '<td >'.getAdTitleByID($emails['to_ad_id'],$emails['to_post_type'],TRUE,TRUE,TRUE).'</td>'."\n";
				$summary .= '<td >'.$emails['message'].'</td>'."\n";			
				$summary .= '<td >'.$emails['reply_date'].'</td>'."\n";			
				$summary .= '</tr>'."\n";			 
			}
		$summary .= '</table>'."\n";			
		return $summary;
	  }
			
    // Check if user has a wanted ad
	  function checkForWantedAd($count=FALSE, $id='') {	
		 if ($id == '') {				 
			 $query = "SELECT wanted_id 
								 FROM   cf_wanted 
								 WHERE  expiry_date > CURDATE()             
								 AND    suspended = 0
								 AND    published = 1 
                             AND    user_id = '". getUserIdFromSession() ."'";
		 } else {
			 $query = "SELECT wanted_id 
						 FROM   cf_wanted 
						 WHERE  expiry_date > CURDATE()
						 AND    suspended = 0 
						 AND    published = 1 					                               
						 AND    user_id = '".$id."'";				
		 }
		 $result = mysqli_query($GLOBALS['mysql_conn'], $query);
		 
		 if ($count == FALSE) {
			 if (mysqli_num_rows($result)) { $wanted_ad = TRUE; } else { $wanted_ad = FALSE; } 
		 } else {
			 $wanted_ad = mysqli_num_rows($result);
		 }
		 return $wanted_ad;
		 }
		 
		 
    // Check if user has a offered ad
	  function checkForOfferedAd($count=FALSE, $id='') {	
		 if ($id == '') {
			 $query = "SELECT offered_id 
						 FROM   cf_offered 
						 WHERE  expiry_date > CURDATE()
						 AND    suspended = 0 
						 AND    published = 1 					                               
						 AND    user_id = '".$_SESSION['u_id']."'";
		 } else { 
			 $query = "SELECT offered_id 
						 FROM   cf_offered 
						 WHERE  expiry_date > CURDATE()
						 AND    suspended = 0 
						 AND    published = 1 					                               
						 AND    user_id = '".$id."'";		 
		 }
		 $result = mysqli_query($GLOBALS['mysql_conn'], $query);
		 
	   if ($count == FALSE) {
			 if (mysqli_num_rows($result)) { $offered_ad = TRUE; } else { $offered_ad = FALSE; } 
		 } else {
			 $offered_ad = mysqli_num_rows($result);
		  }
		 return $offered_ad;
		 }
		 
    // Check if user has added photos to their advert
	  function photoCount($ad_id, $post_type) {	
		 $query = "SELECT photo_id
				 		   FROM   cf_photos 
						   WHERE  ad_id = ".$ad_id."
 						   AND    post_type = '".$post_type."'";
		 $result = mysqli_query($GLOBALS['mysql_conn'], $query);
		 return mysqli_num_rows($result);
		 }		
		
		
		// Matching Ads
		function matchingAds($ad_id, $post_type) {		
		
		// Returns a list of saved Offered ads that match the wanted ad
			$query = "			
				SELECT 'x'
				FROM cf_offered as `o`
				INNER JOIN cf_wanted as `w`
				LEFT JOIN cf_jibble_postcodes as `j1` on j1.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
				LEFT JOIN cf_jibble_postcodes as `j2` on j2.postcode = SUBSTRING_INDEX(w.postcode,' ',1)
				LEFT JOIN cf_users as `u` on u.user_id = w.user_id
				LEFT JOIN cf_saved_ads as `s` 
								on s.ad_id = w.wanted_id and 
								s.post_type = 'wanted' and 
								s.user_id = '".$_SESSION['u_id']."'
			WHERE
			# Both ads are published and unexpired
			      o.published = 1
	  	 AND  w. published = 1					 
			 AND  o.expiry_date > now()
			 AND  w.expiry_date > now()			 
		";
		if ($post_type == "wanted") {
						 $query .= "AND  w.wanted_id = ".$ad_id." 
						 						AND  o.suspended = 0";        // offered ads should be not be suspended; we don't mind it the current ad is suspended
		} else { $query .= "AND  o.offered_id = ".$ad_id." 
												AND  w.suspended = 0";	
		}
		$query .= "	
	
			# *********************************************************
			# LOCATION AND DATES
			# *********************************************************
			
			# Postcode and distance from postcode
			# o.location in within w.distance_from_postcode of w.postcode			
			AND (sqrt(power((j1.x-j2.x),2)+power((j1.y-j2.y),2)) < (1609 * w.distance_from_postcode)
			     OR
			 		 sqrt(power((j1.x-j2.x),2)+power((j1.y-j2.y),2)) < (1609 * 4)			
					 )
			
			# W Available from (means required from) and O Available date are within 10 days of eachother
			AND (ABS(DATEDIFF(w.available_date,o.available_date)) <= 15
			     OR (o.available_date < NOW()
			        AND ABS(DATEDIFF(w.available_date,NOW())) <= 15
			        )
			     OR (w.available_date < NOW()
				        AND ABS(DATEDIFF(o.available_date,NOW())) <= 15
				       )
				  )
			
			# Min / Max terms
			AND  w.min_term <= o.max_term
			AND  w.max_term >= o.min_term
			
			# Accommodation type
			AND  (
				(w.accommodation_type_flat_share = 1 AND o.accommodation_type = 'flat share') OR
				(w.accommodation_type_family_share = 1 AND o.accommodation_type = 'family share') OR
				(w.accommodation_type_whole_place = 1 AND o.accommodation_type = 'whole place') OR
				(w.accommodation_type_flat_share = 1 AND o.room_letting = 1 ) 			
			)
			
			# Building type
			#AND  (
			#(w.building_type_flat = 1 AND o.building_type = 'flat') OR
			#(w.building_type_house = 1 AND o.building_type = 'house')
			#)
			

			# Price, with logic for 
			AND ((w.price_pcm >= (o.price_pcm * 0.78) AND o.accommodation_type != 'whole place')
				OR
			     (w.price_pcm >= ((o.price_pcm * 0.78)/o.bedrooms_available) AND o.accommodation_type = 'whole place')
			     )
			
			#Features
			#AND w.shared_lounge_area <= o.shared_lounge_area
			#AND w.central_heating <= o.central_heating
			#AND w.washing_machine <= o.washing_machine
			#AND w.garden_or_terrace <= o.garden_or_terrace
			#AND w.bicycle_store <= o.bicycle_store
			# AND w.dish_washer <= o.dish_washer
			# AND w.tumble_dryer <= o.tumble_dryer
			#AND (
			#	(w.parking = 0) OR
			#	(w.parking = 1 AND o.parking != 'None')
			#)
			
			# *********************************************************
			# MATCH WANTED YOUR DETAILS TO OFFERED WOULD SUIT
			# *********************************************************
			
			# Number of rooms
			AND ((w.bedrooms_required <= o.bedrooms_available
			     AND (o.accommodation_type != 'whole place'
					      OR o.room_letting = 1))
			    OR
			     (w.bedrooms_required = o.bedrooms_available		
			     AND o.accommodation_type = 'whole place')
			)
			     
			#AND (
			#	 (w.current_num_males + w.current_num_females) <= o.bedrooms_available
			#	 OR
			#	 w.current_is_couple = 1 AND o.bedrooms_available > 0
			#	)
				 		
			# Family, married couple, reference
			AND w.current_is_couple  <= o.suit_married_couple 
			AND w.current_is_family  <= o.suit_family 
			#AND w.church_reference   >= o.church_reference 
			
			# Age
			#AND w.current_min_age >= o.suit_min_age
			#AND (o.suit_max_age = 0 OR w.current_max_age <= o.suit_max_age)
			
			# RD 17-MAY-08 removed occuplational matches  
			# Occupation
			#AND (
			#	(o.current_occupation is null) OR
			#	(o.suit_professional   = w.shared_professional) OR
			#	(o.suit_mature_student = w.shared_mature_student) OR
			#	(o.suit_student        = w.shared_student)
			#)			
			
			# *********************************************************
			# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
			# *********************************************************
			# Max number in the household, with logic to 4+ members
			#AND ((o.current_num_males + o.current_num_females) <= w.shared_adult_members 
			#     OR w.shared_adult_members = 4)
			
			# Age
			#AND w.shared_min_age <= o.current_min_age
			#AND (w.shared_max_age >= o.current_max_age OR w.shared_max_age = 0)
			
			# RD 17-MAY-08 removed occuplational matches	  
			# Occupation
			#AND (
			#	(o.current_occupation is null) OR
			#	(o.suit_professional= w.shared_professional) OR
			#	(o.suit_mature_student = w.shared_mature_student) OR
			#	(o.suit_student = w.shared_student)
			#)
			
			# Gender
			AND (
				(w.shared_males = 1   AND o.current_num_females = 0) OR
				(w.shared_females = 1 AND o.current_num_males = 0) OR
				(w.shared_mixed = 1   AND o.current_num_males > 0 AND o.current_num_females > 0) OR
				(w.current_num_females > 0 AND o.current_num_females > 0) OR
				(w.current_num_males > 0 AND o.current_num_males > 0) OR
				(o.accommodation_type = 'family share')
			)
		    AND (
			     o.suit_gender = 'Mixed' OR
			    (w.current_num_males > 0     AND w.current_num_females > 0) OR
			    (o.suit_gender = 'Male(s)'   AND w.current_num_females = 0) OR
			    (o.suit_gender = 'Female(s)' AND w.current_num_males = 0)
			)
			";
	// Exclude hidden ads
	if ($_SESSION['show_hidden_ads']=='no' && $post_type == "wanted") {
			$query .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and o.offered_id=ad_id  and post_type = 'offered' and active=2) ";  
	}
	if ($_SESSION['show_hidden_ads']=='no' && $post_type == "offered") {
  	  $query .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id=ad_id and post_type = 'wanted' and active=2) ";	
 	}							

		$query = preg_replace('/\t*/','',$query); // Strip the starting tabs
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);		
		$o = mysqli_num_rows($result);
		
	//	return $o.'   '.nl2br($query);
		return $o;
	}
	
	
	
	// Matching PalUp Ads
	function matchingPalups($ad_id) {		
	$query = "
		SELECT 
		'X' 
		FROM 
		cf_wanted as `wo`
		INNER JOIN cf_wanted as `w`
		INNER JOIN cf_jibble_postcodes as `jo`
		INNER JOIN cf_jibble_postcodes as `jn`
		INNER JOIN cf_users as `u` 
		left join cf_saved_ads as `s` 
				on s.ad_id = w.wanted_id and 
				s.post_type = 'wanted' and 
				s.user_id = '".$_SESSION['u_id']."'
		WHERE u.user_id = wo.user_id
		
		# Flat-Match is enabled
		# AND wo.palup = 1  results shown regardless of palup option chosen
		AND w.palup = 1
		
		# Do not compare ads of the same id
		AND wo.wanted_id != w.wanted_id 
		
		# New ads only: The w must be newer than wo, who is the recipient of the email
		# RD 28-JUNE-07, 		AND w.created_date > ifnull(wo.last_flatmatch,'2005-01-01')
		#AND w.created_date > ifnull(wo.last_palup,wo.created_date)
		#AND w.created_date > wo.last_updated_date
		
		# Ensure that w is newer than wo
		#AND w.created_date > wo.created_date
		
		# Both ads are pulished and unexpired
		AND  wo.published = 1
		AND  wo.expiry_date > now()
		#AND  wo.suspended = 0 # The ad being matched can be suspended
		AND  wo.wanted_id = ".$ad_id."
		AND  w.published = 1
		AND  w.expiry_date > now() 
		AND  w.suspended = 0 
		
		# ***********************************************
		# LOCATION AND DATES
		# ***********************************************
		
		# The distance between the two wanted ads 
		# is LESS than the sum of the distance_from_postcode for both wanted ads
		AND (sqrt(power((jo.x-jn.x),2)+power((jo.y-jn.y),2)) < (1609 * (wo.distance_from_postcode + w.distance_from_postcode))
		    OR
		    sqrt(power((jo.x-jn.x),2)+power((jo.y-jn.y),2)) < (1609 * (4 + 4))
		    )		
                AND SUBSTRING_INDEX(wo.postcode,' ',1)  = jo.postcode
		AND SUBSTRING_INDEX(w.postcode,' ',1) = jn.postcode

		# The distance from one date to the other is less than 2 weeks
		AND (ABS(DATEDIFF(w.available_date,wo.available_date)) <= 15
		     OR (w.available_date < NOW()
		         AND ABS(DATEDIFF(wo.available_date,NOW())) <= 15
		        )
		     OR (wo.available_date < NOW()
		         AND ABS(DATEDIFF(w.available_date,NOW())) <= 15
		        )
		    )
		 
		 
		# Accommodation type
		# RD 03-AUG-07 Accommodation type excluded: we can assume it will be whole place 
    # or flatshare
		#AND  (   
		#	(w.accommodation_type_flat_share   = wo.accommodation_type_flat_share) OR
		#	(w.accommodation_type_family_share = wo.accommodation_type_family_share) OR
		#	(w.accommodation_type_whole_place  = wo.accommodation_type_whole_place)
		#)
		
		# Prices within 20% of each other
		# RD 25-JUL-07, removed price
		# AND w.price_pcm >= (wo.price_pcm *.78)

		# ******************************************************
		# MATCH WANTED YOUR DETAILS TO WANTED PREFERED HOUSEHOLD
		# ******************************************************
		
		# Family, married couple,reference
		# RD 03-AUG-07 Changed to <= logic, to compare the 1s and 0s
		AND w.current_is_couple <= wo.shared_married_couple
 		AND w.current_is_family <= wo.shared_family 
		AND wo.current_is_couple <= w.shared_married_couple
		AND wo.current_is_family <= w.shared_family
		
		
		# Occupation
		#AND (
		#(w.current_occupation = 'Professionals' AND wo.shared_professional = 1) OR
		#(w.current_occupation = 'Mature Students' AND wo.shared_mature_student = 1) OR
		#(w.current_occupation = 'Students (<22yrs)' AND wo.shared_student = 1) OR
		#(wo.shared_student = 0 AND wo.shared_mature_student = 0 AND wo.shared_professional = 0)
		#)
		#AND (
		#(wo.current_occupation = 'Professionals' AND w.shared_professional = 1) OR
		#(wo.current_occupation = 'Mature Students' AND w.shared_mature_student = 1) OR
		#(wo.current_occupation = 'Students (<22yrs)' AND w.shared_student = 1) OR
		#(w.shared_student = 0 AND w.shared_mature_student = 0 AND w.shared_professional = 0)
		#)

		# *********************************************************
		# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
		# *********************************************************
		
		# Age
		#AND  w.shared_min_age <= wo.current_min_age
		#AND (w.shared_max_age >= wo.current_max_age OR w.shared_max_age = 0)
		#AND  wo.shared_min_age <= w.current_min_age
		#AND (wo.shared_max_age >= w.current_max_age OR wo.shared_max_age = 0)

		# Gender
		AND (
			(w.shared_males = 1 AND wo.current_num_females = 0) OR
			(w.shared_females = 1 AND wo.current_num_males = 0) OR
		(w.shared_mixed = 1 AND wo.current_num_males > 0 AND wo.current_num_females > 0)   OR
		(w.shared_mixed = 1 AND wo.current_num_males > 0  AND w.current_num_males > 0)    OR
		(w.shared_mixed = 1 AND wo.current_num_females > 0 AND w.current_num_females > 0) OR
		(w.shared_males = 0 AND w.shared_females = 0 AND w.shared_mixed = 0) OR
		(w.current_num_males   > 0 AND wo.current_num_males > 0)   OR
		(w.current_num_females > 0 AND wo.current_num_females > 0) 		
		) 
		AND (
			(wo.shared_males = 1 AND w.current_num_females = 0) OR
			(wo.shared_females = 1 AND w.current_num_males = 0) OR
		(wo.shared_mixed = 1 AND w.current_num_males > 0 AND w.current_num_females > 0)   OR
		(wo.shared_mixed = 1 AND w.current_num_males > 0 AND wo.current_num_males > 0)     OR
		(wo.shared_mixed = 1 AND w.current_num_females > 0 AND wo.current_num_females > 0) OR
		(wo.shared_males = 0 AND wo.shared_females = 0 AND wo.shared_mixed = 0) OR
		(w.current_num_males   > 0 AND wo.current_num_males > 0)   OR
		(w.current_num_females > 0 AND wo.current_num_females > 0) 
  	)
	";
	// Exclude hidden ads
	if ($_SESSION['show_hidden_ads']=='no') {
 		 $query .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id and post_type = 'wanted' and active=2) ";	 }							


	$query = preg_replace('/\t*/','',$query); // Strip the starting tabs
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);		
	$o = mysqli_num_rows($result);
	//return $o.'   '.nl2br($query);
	return $o;	
	
}


	// loadBanner()
	// Responsible for loading the banner advertisements for the whole of the site
	function loadBanner($type="728", $place=NULL, $frontpage=FALSE, $num_photos="0", $userCountryISO='GB') {
	
		// format the $type
		if ($type == "728") {
			$type = "728x90";
			$w = 728;
			$h = 90;			
		} else if ($type == "120") {
			$type = "120x240";
			$w = 120;
			$h = 240;	
		}
	
		// For front page-only banners
		if ($frontpage) {
			
			$query = "
				SELECT banner_id,link,filename
				FROM cf_banners
				WHERE frontpage = 1
				AND type = '".$type."'
				AND approved = 1
				AND suspended != 1
				AND deleted != 1								
				AND now() > date_from
				AND now() < date_to
                AND country = '" . $userCountryISO . "'
			";
			
		} else {
		
			// All other banners
			$query = "
				SELECT banner_id,link,filename
				FROM cf_banners
				WHERE type = '".$type."'
				AND display = 1
				AND approved = 1 
				AND suspended != 1
				AND deleted != 1								
				AND now() > date_from
				AND now() < date_to
                AND country = '" . $userCountryISO . "'
			";
			
			// If a place (first part of postcode at this stage) is specified,
			// do the distance query
            if (is_array($place)) {
                // If place is an array we have lat lng data
                
                $geoHelper = new CFSGeoEncoding();
                $earthDistanceSQL = $geoHelper->earth_distance_sql($place[0], $place[1], 'l');
                
				$query .= "
				
				UNION
							
				SELECT 
					b.banner_id,
					b.link,
					b.filename
				
				FROM cf_banners b
				LEFT JOIN cf_banners_locations l ON l.banner_id = b.banner_id
				
				WHERE b.type = '".$type."'
				AND b.approved = 1
				AND b.suspended != 1
				AND deleted != 1				
				AND now() > b.date_from
				AND now() < b.date_to
				AND b.display = 2
				AND " . $earthDistanceSQL . " < (1609 * l.radius)
                AND l.latitude is not NULL
                AND l.longitude is not NULL";
            }
            else if ($place) {
			
				// Get only the first part of the postcode
				$temp = preg_match(REGEXP_UK_POSTCODE,$place,$matches);
				if ($matches) {
					$place = $matches[1];
				}
			
				$query .= "
				
				UNION
							
				SELECT 
					b.banner_id,
					b.link,
					b.filename
				
				FROM cf_banners b
				LEFT JOIN cf_banners_locations l ON l.banner_id = b.banner_id
				LEFT JOIN cf_jibble_postcodes j1 ON l.place = j1.postcode
				LEFT JOIN cf_jibble_postcodes j2 ON j2.postcode = '".$place."'
				
				WHERE b.type = '".$type."'
				AND b.approved = 1
				AND b.suspended != 1
				AND deleted != 1				
				AND now() > b.date_from
				AND now() < b.date_to
				AND b.display = 2
				AND sqrt(power((j1.x-j2.x),2)+power((j1.y-j2.y),2)) < (1609 * l.radius)";
			
			}
			
		}
		
		// Execute the query
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die($query);
		$banner_count = $result? mysqli_num_rows($result) : 0;
		
		// Return empty if no banners, choose a random one if there are some
		if (!$banner_count) { 
			return ""; 
		} 
		
		
		if ($w == 120 && $frontpage) {
		
			// For type == 120 and frontpage = 1, we need to output TWO banners side by side
			// Store all results in an array
			while($row = mysqli_fetch_assoc($result)) {
				$banners[] = $row;
			}
			// Randomise the order of the array
			shuffle($banners);
			//return '<pre>'.print_r($banners,0).'</pre>';
			
			for($i=0;$i<2;$i++) {
			
				if ($banners[$i]) {
		
					// Prep the link
					$link = 'clicks.php?lets_have_a_look_at='.$banners[$i]['banner_id'];
			
					// Output the HTML for the banner
					$toReturn .= '<div class="banner_'.$type.'" style="float:left;width:120px;">';
					$toReturn .= '<div class="banner_image">';
					$toReturn .= '<a href="'.$link.'">';
					$toReturn .= '<img src="images/banners/'.$banners[$i]['filename'].'" width="'.$w.'" height="'.$h.'" border="0" />';
					$toReturn .= '</a>';
					$toReturn .= '</div>';
					$toReturn .= '</div>';
				
				}
				
			}
			
			$toReturn .= '<div class="clear"><!----></div>';	
		
		} else {
			
			// 40% CFS ads, or front page
		  if (rand(1,100) > 40 || $frontpage == TRUE)  { 		
			  				
				// For all other normal cases load a random number from the result set
				$random_row = rand(0,($banner_count-1));
				mysqli_data_seek($result,$random_row);
				$banner = mysqli_fetch_assoc($result);
			  
				// Update times viewed
			  $query = "UPDATE cf_banners SET times_viewed = times_viewed + 1 WHERE banner_id = ".$banner['banner_id'];
			  mysqli_query($GLOBALS['mysql_conn'], $query);
				
				// Prep the link
				$link = 'clicks.php?lets_have_a_look_at='.$banner['banner_id'];
			
				// Output the HTML for the banner
				$toReturn = '<div class="banner_'.$type.'">';
				$toReturn .= '<div class="banner_image">';
				$toReturn .= '<a href="'.$link.'">';
				$toReturn .= '<img src="images/banners/'.$banner['filename'].'" width="'.$w.'" height="'.$h.'" border="0" />';
				$toReturn .= '</a>'; 
				$toReturn .= '</div>';
				
				if ($w == "728") {
					$toReturn .= '<div class="banner_call_to_action">';
					$toReturn .= '<a href="advertising.php">';
					$toReturn .= '<img src="images/banner_call_to_action.gif" width="71" height="39" border="0" /><br/>';
					$toReturn .= 'Click here to<br/>find out more';
					$toReturn .= '</a>';
					$toReturn .= '</div>';
				} 
				$toReturn .= '</div>';
			} else { // use Google ads:
			
				// Output the HTML for the banner
			  $toReturn = '<div class="banner_'.$type.'">';
		 		$toReturn .= '<table cellpadding="0" cellspacing="0" width="100%"><tr><td>';
				
			 	if ($w == "728") {
	  			$toReturn .= '<div class="banner_call_to_action">';
		  		$toReturn .= '<a href="advertising.php">';
			  	$toReturn .= '<img src="images/banner_call_to_action.gif" width="71" height="39" border="0" /><br/>';
					$toReturn .= 'Click here to<br/>find out more';
		  		$toReturn .= '</a>';
					$toReturn .= '</div>';
					
					$toReturn .= '</td>
												<td>&nbsp;</td>
												<td>';
					$rand_number = rand(1,100);
					if ($rand_number < 33)  { 									
					$toReturn .= '<script type="text/javascript"><!--
						google_ad_client = "pub-3776682804513044";
						/* 728x90 Leader Board created 12/04/09 */
						google_ad_slot = "7699358474";
						google_ad_width = 728;
						google_ad_height = 90;
						//-->
						</script>
						<script type="text/javascript"
						src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
						</script>';		
					} elseif ($rand_number > 66) {
					$toReturn .= '<script type="text/javascript"><!--
						google_ad_client = "pub-3776682804513044";
						/* 728x90 (2), created 12/04/09 */
						google_ad_slot = "5180753968";
						google_ad_width = 728;
						google_ad_height = 90;
						//-->
						</script>
						<script type="text/javascript"
						src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
						</script>';
					} else {
					$toReturn .= '<script type="text/javascript"><!--
						google_ad_client = "pub-3776682804513044";
						/* 728x90 (3), created 12/04/09 */
						google_ad_slot = "9207555741";
						google_ad_width = 728;
						google_ad_height = 90;
						//-->
						</script>
						<script type="text/javascript"
						src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
						</script>';
					}
				} else { // Verticle Banner
					// Count Number of photos
						$toReturn .= '<p class="mb10 mt0">&nbsp;</p>';					
					if ($num_photos > 5) {
						$toReturn .= '<script type="text/javascript"><!--
							google_ad_client = "pub-3776682804513044";
							/* 120x240 verticle banner, created 12/04/09 */
							google_ad_slot = "8956928573";
							google_ad_width = 120;
							google_ad_height = 240;
							//-->
							</script>
							<script type="text/javascript"
							src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
							</script>';
						} else {  // more than 4 photos; long photo strip
							$toReturn .= '<script type="text/javascript"><!--
								google_ad_client = "pub-3776682804513044";
								/* 120x600, created 12/04/09 */
								google_ad_slot = "8801139998";
								google_ad_width = 120;
								google_ad_height = 600;
								//-->
								</script>
								<script type="text/javascript"
								src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
								</script>';
						}
				}
						
				$toReturn .= '</td></tr></table>';
				$toReturn .= '</div>';			
			} // Use Google ads
		}
	
		return $toReturn;

	
	}
	
/**
 * Return an array of countries supported by CFS
 */
function getCountries() {
  $countries = array();
  
  $result = mysqli_query($GLOBALS['mysql_conn'], "SELECT * FROM cf_countries WHERE active = 1 ORDER BY name");
  if ($result !== FALSE) {
		while($row = mysqli_fetch_assoc($result)) {
			$countries[$row['iso']] = $row;
		}
  }
  
  return $countries;
}

function getHeader() {
  $countries = getCountries();
  
  $html = '<div class="inner"><select id="countrySelection">';
  foreach ($countries as $iso => $country) {
    $html .= '<option value="' . $iso . '">' . $country['name'] . '</option>';
  }
  $html .= '</select></div>';

  return $html;
}

function getCurrentCountry() {    
    // If set in session use that
    if (isset($_SESSION['country_iso'])) {
        return $_SESSION['country_iso'];
    }
    
    // Else lookup from IP Address
    // This will only match countries we support, no result? Then default to GB
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $result = mysqli_query($GLOBALS['mysql_conn'], "SELECT l.country_iso FROM cf_ip_lookup AS l INNER JOIN cf_countries AS c ON l.country_iso = c.iso WHERE (INET_ATON(" . $ip_address . ") BETWEEN l.starting_ip AND l.ending_ip) AND c.active = 1");
    if ($result !== FALSE) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['country_iso'])) {
            $_SESSION['country_iso'] = $row['country_iso'];
            return $row['country_iso'];
        }
    }
    
    // Else default to GB
    return 'GB';
}

function getCountryInfo() {
    $result = mysqli_query($GLOBALS['mysql_conn'], "SELECT * FROM cf_countries WHERE iso = '" . getCurrentCountry() . "'");
    if ($result !== FALSE) {
  		while($row = mysqli_fetch_assoc($result)) {
  			return $row;
  		}
    }
    
    return array();
}

function getCountryFlag() {
    return '<div class="inner"><img src="/images/mini-flags/' . strtolower(getCurrentCountry()) . '.png" /></div>';
}

/**
 * Needed for legacy ages held in the database
 * converts any ages into the new range format
 */
function cleanAge($min_age, $max_age, $type = 'current') {
    
    if ($min_age < 20) {
        $min = 18;
    }
    else if ($min_age < 25) {
        $min = 21;
    }
    else if ($min_age < 30) {
        $min = 26;
    }
    else if ($min_age < 35) {
        $min = 31;
    }
    else if ($min_age < 40) {
        $min = 36;
    }
    else if ($min_age < 45) {
        $min = 41;
    }
    else if ($min_age < 50) {
        $min = 46;
    }
    else if ($min_age <= 51) {
        $min = 51;
    }
    
    if ($max_age >= 51) {
        $max = 51;
    }
    else if ($max_age >= 46) {
        $max = 50;
    }
    else if ($max_age >= 41) {
        $max = 45;
    }
    else if ($max_age >= 36) {
        $max = 40;
    }
    else if ($max_age >= 31) {
        $max = 35;
    }
    else if ($max_age >= 26) {
        $max = 30;
    }
    else if ($max_age >= 21) {
        $max = 25;
    }
    else{
        $max = 20;
    }
    
    if ($type == 'suit') {
        if ($min == 18 && $max == 51) {
            return 'Any age';
        }
        else if ($min == 18) {
            return 'Under ' . $max . ' years old';
        }
        else if ($max == 51) {
            return 'Over ' . $min . ' years old';
        }
        /*
        if ($min_age == 0 && $max_age == 0) {
            return 'Any age';
        }
        else if ($min_age == 0) {
            return 'Under ' . $max . ' years old';
        }
        else if ($max_age == 0) {
            return 'Over ' . $min . ' years old';
        }
        */
    }
    
    if ($min == $max) {
        return '51+';
    }
    else {
        $extra = ($max == 51) ? '+' : '';
        return (($min) ? $min . '-' : '') . $max . $extra;
    }
}

function ageConvert($age, $end) {
    switch($age) {
        case '1':
            $min_age = 18;
            $max_age = 20;
            break;
        case '2':
            $min_age = 21;
            $max_age = 25;
            break;
        case '3':
            $min_age = 26;
            $max_age = 30;
            break;
        case '4':
            $min_age = 31;
            $max_age = 35;
            break;
        case '5':
            $min_age = 36;
            $max_age = 40;
            break;
        case '6':
            $min_age = 41;
            $max_age = 45;
            break;
        case '7':
            $min_age = 46;
            $max_age = 50;
            break;
        case '8':
            $min_age = 51;
            $max_age = 51;
            break;
    }
    
    return $$end;
}

function reverseAgeConvert($age) {
    if ($age < 20) {
        return 1;
    }
    else if ($age > 20 && $age <= 25) {
        return 2;
    }
    else if ($age > 25 && $age <= 30) {
        return 3;
    }
    else if ($age > 30 && $age <= 35) {
        return 4;
    }
    else if ($age > 35 && $age <= 40) {
        return 5;
    }
    else if ($age > 40 && $age <= 45) {
        return 6;
    }
    else if ($age > 45 && $age <= 50) {
        return 7;
    }
    else if ($age <= 51) {
        return 8;
    }
}

function getUKPostcodeFirstPart($postcode)
{
    // validate input parameters
    $postcode = strtoupper($postcode);

    // UK mainland / Channel Islands (simplified version, since we do not require to validate it)
    if (preg_match('/^[A-Z]([A-Z]?\d(\d|[A-Z])?|\d[A-Z]?)\s*?\d[A-Z][A-Z]$/i', $postcode))
        return preg_replace('/^([A-Z]([A-Z]?\d(\d|[A-Z])?|\d[A-Z]?))\s*?(\d[A-Z][A-Z])$/i', '$1', $postcode);
    // British Forces
    if (preg_match('/^(BFPO)\s*?(\d{1,4})$/i', $postcode))
        return preg_replace('/^(BFPO)\s*?(\d{1,4})$/i', '$1', $postcode);
    // overseas territories
    if (preg_match('/^(ASCN|BBND|BIQQ|FIQQ|PCRN|SIQQ|STHL|TDCU|TKCA)\s*?(1ZZ)$/i', $postcode))
        return preg_replace('/^([A-Z]{4})\s*?(1ZZ)$/i', '$1', $postcode);

    // well ... even other form of postcode... return it as is
    return $postcode;
}

function cfs_mysqli_result($res, $row, $field=0) { 
    $res->data_seek($row); 
    $datarow = $res->fetch_array(); 
    return $datarow[$field]; 
}


