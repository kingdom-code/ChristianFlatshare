<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once __DIR__ . '/../web/global.php';
// require_once 'web/global.php';

connectToDB();
	
	// FLATMATCH QUERY
	$query = "
			
			SELECT 
			
			o.offered_id,
			o.postcode as `offered_postcode`,
			w.wanted_id,
			w.user_id,
			w.postcode as `wanted_postcode`,
			sqrt(power((j1.x-j2.x),2)+power((j1.y-j2.y),2)) as `distance`,
			o.bedrooms_available,
			o.bedrooms_total,
			o.accommodation_type,
			o.room_share,
			o.building_type,
			o.street_name,
            o.country,
            o.street,
            o.area,
			(CASE IFNULL(o.town_chosen,'')
						WHEN '' THEN j1.town 
						ELSE o.town_chosen
						END) as town,
			o.postcode,
			w.bedrooms_required,
			w.distance_from_postcode,
			w.location,
            w.country as wanted_country,
            w.street as wanted_street,
            w.area as wanted_area,
			u.first_name,
			u.surname,
			u.email_address,
			u.password
			
			FROM cf_offered as `o`
			INNER JOIN cf_wanted as `w`
			LEFT JOIN cf_jibble_postcodes as `j1` on j1.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
			LEFT JOIN cf_jibble_postcodes as `j2` on j2.postcode = w.postcode
			LEFT JOIN cf_users as `u` on u.user_id = w.user_id
			
			WHERE
			
			# Flat-Match is enabled
			w.flatmatch = 1
			
			# New ads only
			AND  o.created_date > ifnull(w.last_flatmatch,w.created_date)
			AND  o.created_date > w.last_updated_date
      # Match only offered adverts created in within the last 10 days
      AND  ABS(DATEDIFF(o.created_date,NOW())) <= 10

			
			# Both ads are published and unexpired
			AND  o.published = 1
			AND  o.expiry_date > now()
			AND  o.suspended = 0 
			AND  w.published = 1
			AND  w.expiry_date > now()
			AND  w.suspended = 0 
	
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
			#)
				 		
						
			# Family, married couple, reference
			AND w.current_is_couple  <= o.suit_married_couple 
			AND w.current_is_family  <= o.suit_family 
			#AND w.church_reference   >= o.church_reference 
			
			# Age
			#AND w.current_min_age >= o.suit_min_age
			#AND (o.suit_max_age = 0 OR w.current_max_age <= o.suit_max_age)
			  
			# Occupation
			AND (
				(o.current_occupation is null) OR
				(o.suit_professional   = w.shared_professional) OR
				(o.suit_mature_student = w.shared_mature_student) OR
				(o.suit_student        = w.shared_student)
			)			
			
			# *********************************************************
			# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
			# *********************************************************
			
			# Max number in the household, with logic to 4+ members
			#AND ((o.current_num_males + o.current_num_females) <= w.shared_adult_members 
			#      OR w.shared_adult_members = 4)
			
			# Age
			#AND w.shared_min_age <= o.current_min_age
			#AND (w.shared_max_age >= o.current_max_age OR w.shared_max_age = 0)
			  
			# Occupation
			#AND (
			#	(o.current_occupation is null) OR
			#	(o.suit_professional= w.shared_professional) OR
			#	(o.suit_mature_student = w.shared_mature_student) OR
			#	(o.suit_student = w.shared_student)
			#)
			
			# Gender
			AND (
				(w.shared_males = 1 AND o.current_num_females = 0) OR
				(w.shared_females = 1 AND o.current_num_males = 0) OR
				(w.shared_mixed = 1 AND o.current_num_males > 0 AND o.current_num_females > 0) OR
				(w.current_num_females > 0 AND o.current_num_females > 0) OR
				(w.current_num_males > 0 AND o.current_num_males > 0) OR				
				(w.accommodation_type_family_share = 1 AND o.accommodation_type = 'family share')
			)
		    AND (
			     o.suit_gender = 'Mixed' OR
	    	  (w.current_num_males > 0     AND w.current_num_females > 0) OR
			    (o.suit_gender = 'Male(s)'   AND w.current_num_females = 0) OR
			    (o.suit_gender = 'Female(s)' AND w.current_num_males = 0)
			)
		";
		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
		$query = preg_replace('/\t*/','',$query); // Strip the starting tabs
		$count = mysqli_num_rows($result);
		$t = "";
	
		if ($count) {
			
			while($row = mysqli_fetch_assoc($result)) {

				// Create the hash of last_login and email_address
				$hash = md5($row['password'].$row['email_address']);				
			
				// Create the arrays to send to the getAdTitle page
				$offered_temp = array(
					'offered_id' 			=> $row['offered_id'],
					'bedrooms_available'     	=> $row['bedrooms_available'],
					'bedrooms_total'		=> $row['bedrooms_total'],
					'building_type' 		=> $row['building_type'],
					'street_name' 			=> $row['street_name'],
					'town_chosen' 			=> $row['town_chosen'],					
					'postcode' 			=> $row['postcode'],
					'accommodation_type'            => $row['accommodation_type'],
                    'country' 	            => $row['country'],
                    'sreet' 	            => $row['street'],
                    'area' 	                => $row['area']
				);
				$offered_title = getAdTitle($offered_temp, "offered", FALSE);
				$wanted_temp = array (
					'wanted_id'					=> $row['wanted_id'],
					'bedrooms_required'			=> $row['bedrooms_required'],
					'distance_from_postcode'	=> $row['distance_from_postcode'],
					'location'					=> $row['location'],
					'postcode'					=> $row['wanted_postcode'],
                    'country' 	                => $row['wanted_country'],
                    'sreet' 	                => $row['wanted_street'],
                    'area' 	                    => $row['wanted_area']
				);
				$wanted_title = getAdTitle($wanted_temp, "wanted", FALSE);
				
                // Send Email
                $CFSMailer = new CFSMailer();
                
                // Get Body
                $body = $twig->render('emails/flat_match.html.twig', array(
                    'first_name' => $row['first_name'],
                    'offered' => array('title' => $offered_title, 'url' => 'http://' . SITE . 'details.php?id=' . $row['offered_id'] . '&post_type=offered'),
                    'wanted' => array('title' => $wanted_title, 'url' => 'http://' . SITE . 'details.php?id=' . $row['wanted_id'] . '&post_type=wanted'),
                    'keep_url' => 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=wanted&id='.$row['wanted_id'].'&hash='.$hash,
                    'suspend_url' => 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=wanted&id='.$row['wanted_id'].'&hash='.$hash,
                ));
                
                // Set variables
                $subject = 'Christian Flatshare - Flat-Match, ' . $offered_title;
                $to = $row['email_address'];
    	        //$bcc = 'ryan.davies@christianflatshare.org';
                
                $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
                $sent = $CFSMailer->sendMessage($msg);
				
				// Set the "last_flatmatch" of the wanted ad in question
				$updateQuery = "update cf_wanted set last_flatmatch = now() where wanted_id = '".$row['wanted_id']."';";
				$updateResult = mysqli_query($GLOBALS['mysql_conn'], $updateQuery);
				
				// And finally, create one more row on the table
				
				$t .= '<tr>'."\n";
				// WANTED_ID
				$t .= '<td>'.$row['wanted_id'].'</td>'."\n";
				$t .= '<td width="200">'.$wanted_title.'</td>'."\n";
				$t .= '<td>matched with </td>'."\n";
				$t .= '<td>'.$row['offered_id'].'</td>'."\n";
				$t .= '<td width="200">'.$offered_title.'</td>'."\n";
				$t .= '<td>'.$row['first_name'].' '.$row['surname'].'</td>'."\n";
				if ($sent > 0) {
					$t .= '<td class="green">Email sent to '.$row['email_address'].'</td>'."\n";
				} else {
					$t .= '<td class="red">Error sending to '.$row['email_address'].'</td>'."\n";
				}
				$t .= '</tr>'."\n";	
			
			}
			
		} else {
		
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Flat-Match</title>
</head>
<style type="text/css">
	body,input,select,textarea { font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px; }
	body { margin:20px; }
	h1 { font-size:21px; font-weight:normal; }
	.borders { border-collapse:collapse; }
	.borders td { border:1px solid #CCCCCC; }
	.borders th { border:1px solid #CCCCCC; background-color:#EAEAEA; }
	.red { background-color:#FFC4C4; }
	.green { background-color:#DBFFB7; }
	.mt0 { margin-top:0px; }
	.mb0 { margin-bottom:0px; }
</style>
<body>
<h1>Flatmatch query found <strong><?php print $count?></strong> matches</h1>
<table cellpadding="4" cellspacing="0" class="borders" style="margin-bottom:2em;">
	<tr>
		<th>Wanted id</th>
		<th>Wanted deatils </th>
		<th>&nbsp;</th>
		<th>Offered id</th>
		<th>Offered details </th>
		<th>Wanted ad user </th>
		<th>Action</th>
	</tr>
	<?php print $t?>
</table>
<div style="padding:10px; background-color:#F5F5F5; border:1px solid #CCCCCC;">
<p class="mt0"><strong>Query that was executed:</strong></p>
<pre class="mb0"><?php print $query?></pre>
</div>
</body>
</html>
