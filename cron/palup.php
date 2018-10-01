<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once __DIR__ . '/../web/global.php';

connectToDB();

	/*
		Pal-UP
		.sends existing wanted (o = older) ad owners the links to newer wanted (w = wanted) ads, where their requirement match.            
		
			1. SELECT
			Select the records from the cron job, to generate the emails
		
			2. UPDATE
			Update the cf_wanted LAST_PALUP with current date
			This is to avoid re-sending ad emails and to enable us to run it as frequently as needed.
		
		`wo` to denote the older ad, and the recipient of the email
		`wn` to denote the new wanted ad
		
		*/	
		
	// PALUP QUERY
	$query = "
			
		SELECT 
		
		wo.wanted_id as `wo_wanted_id`,
		wo.bedrooms_required as `wo_bedrooms_required`,
		wo.distance_from_postcode as `wo_distance_from_postcode`,
		wo.location as `wo_location`,
		wo.postcode as `wo_postcode`,
        wo.country as wo_country,
        wo.street as wo_street,
        wo.area as wo_area,
		
		wn.wanted_id as `wn_wanted_id`,
		wn.bedrooms_required as `wn_bedrooms_required`,
		wn.distance_from_postcode as `wn_distance_from_postcode`,
		wn.location as `wn_location`,
		wn.postcode as `wn_postcode`,
        wn.country as wn_country,
        wn.street as wn_street,
        wn.area as wn_area,
		
		u.first_name,
		u.surname,
		u.email_address,
		u.password
		
		FROM 
		cf_wanted as `wo`,
		cf_wanted as `wn`, 
		cf_jibble_postcodes as `jo`,
		cf_jibble_postcodes as `jn`,
		cf_users as `u`
		
		WHERE u.user_id = wo.user_id
		
		# Flat-Match is enabled
		AND wo.palup = 1 AND wn.palup = 1
		
		# Do not compare ads of the same id
		AND wo.wanted_id != wn.wanted_id 
		
		# New ads only: The wn must be newer than wo, who is the recipient of the email
		# RD 28-JUNE-07, 		AND wn.created_date > ifnull(wo.last_flatmatch,'2005-01-01')
		AND wn.created_date > ifnull(wo.last_palup,wo.created_date)
		AND wn.created_date > wo.last_updated_date
		
		# Ensure that wn is newer than wo
		AND wn.created_date > wo.created_date
		
		# Both ads are pulished and unexpired
		AND  wo.published = 1
		AND  wo.expiry_date > now()
		AND  wo.suspended = 0
		AND  wn.published = 1
		AND  wn.expiry_date > now() 
		AND  wn.suspended = 0 

		# ***********************************************
		# LOCATION AND DATES
		# ***********************************************
		
		# The distance between the two wanted ads 
		# is LESS than the sum of the distance_from_postcode for both wanted ads
		AND (sqrt(power((jo.x-jn.x),2)+power((jo.y-jn.y),2)) < (1609 * (wo.distance_from_postcode + wn.distance_from_postcode))
				OR
				sqrt(power((jo.x-jn.x),2)+power((jo.y-jn.y),2)) < (1609 * (4 + 4))
 				)		
    AND wo.postcode = jo.postcode
		AND wn.postcode = jn.postcode

		# The distance from one date to the other is less than 2 weeks
		AND DATEDIFF(wn.available_date,wo.available_date) <= 15
		AND DATEDIFF(wn.available_date,wo.available_date) >= -15
		
		AND (ABS(DATEDIFF(wn.available_date,wo.available_date)) <= 15
		     OR (wn.available_date < NOW()
		         AND ABS(DATEDIFF(wo.available_date,NOW())) <= 15
		        )
		     OR (wo.available_date < NOW()
		         AND ABS(DATEDIFF(wn.available_date,NOW())) <= 15
		        )
		    )
		 
		# Accommodation type
		# RD 03-AUG-07 Accommodation type excluded: we can assume it will be whole place 
        # or flatshare
		#AND  (   
		#	(wn.accommodation_type_flat_share   = wo.accommodation_type_flat_share) OR
		#	(wn.accommodation_type_family_share = wo.accommodation_type_family_share) OR
		#	(wn.accommodation_type_whole_place  = wo.accommodation_type_whole_place)
		#)
		
		# Prices within 20% of each other
		# RD 25-JUL-07, removed price
		# AND wn.price_pcm >= (wo.price_pcm *.78)

		# ******************************************************
		# MATCH WANTED YOUR DETAILS TO WANTED PREFERED HOUSEHOLD
		# ******************************************************
		
		# Family, married couple,reference
		#AND wn.current_is_couple = wo.shared_married_couple
		#AND wn.current_is_family = wo.shared_family 
		#AND wo.current_is_couple = wn.shared_married_couple
		#AND wo.current_is_family = wn.shared_family
		
		# RD 03-AUG-07 Changed to <= logic, to compare the 1s and 0s
		AND wn.current_is_couple <= wo.shared_married_couple
		AND wn.current_is_family <= wo.shared_family 
		AND wo.current_is_couple <= wn.shared_married_couple
		AND wo.current_is_family <= wn.shared_family
		
		
		# Occupation
		AND (
		(wn.current_occupation = 'Professionals' AND wo.shared_professional = 1) OR
		(wn.current_occupation = 'Mature Students' AND wo.shared_mature_student = 1) OR
		(wn.current_occupation = 'Students (<22yrs)' AND wo.shared_student = 1) OR
		(wo.shared_student = 0 AND wo.shared_mature_student = 0 AND wo.shared_professional = 0)
		)
		AND (
		(wo.current_occupation = 'Professionals' AND wn.shared_professional = 1) OR
		(wo.current_occupation = 'Mature Students' AND wn.shared_mature_student = 1) OR
		(wo.current_occupation = 'Students (<22yrs)' AND wn.shared_student = 1) OR
		(wn.shared_student = 0 AND wn.shared_mature_student = 0 AND wn.shared_professional = 0)
		)

		# *********************************************************
		# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
		# *********************************************************
		
		# Age
		AND  wn.shared_min_age <= wo.current_min_age
		AND (wn.shared_max_age >= wo.current_max_age OR wn.shared_max_age = 0)
		AND  wo.shared_min_age <= wn.current_min_age
		AND (wo.shared_max_age >= wn.current_max_age OR wo.shared_max_age = 0)
		
		# Gender
		AND (
			(wn.shared_males = 1 AND wo.current_num_females = 0) OR
			(wn.shared_females = 1 AND wo.current_num_males = 0) OR
		(wn.shared_mixed = 1 AND wo.current_num_males > 0 AND wo.current_num_females > 0)   OR
		(wn.shared_mixed = 1 AND wo.current_num_males > 0  AND wn.current_num_males > 0)    OR
		(wn.shared_mixed = 1 AND wo.current_num_females > 0 AND wn.current_num_females > 0) OR
		(wn.shared_males = 0 AND wn.shared_females = 0 AND wn.shared_mixed = 0) OR
		(wn.current_num_males   > 0 AND wo.current_num_males > 0)   OR
		(wn.current_num_females > 0 AND wo.current_num_females > 0) 				
		) 
		AND (
			(wo.shared_males = 1 AND wn.current_num_females = 0) OR
			(wo.shared_females = 1 AND wn.current_num_males = 0) OR
		(wo.shared_mixed = 1 AND wn.current_num_males > 0 AND wn.current_num_females > 0)   OR
		(wo.shared_mixed = 1 AND wn.current_num_males > 0 AND wo.current_num_males > 0)     OR
		(wo.shared_mixed = 1 AND wn.current_num_females > 0 AND wo.current_num_females > 0) OR
		(wo.shared_males = 0 AND wo.shared_females = 0 AND wo.shared_mixed = 0) OR
		(wn.current_num_males   > 0 AND wo.current_num_males > 0)   OR
		(wn.current_num_females > 0 AND wo.current_num_females > 0) 
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
			
			$temp = array (
				'wanted_id'					=> $row['wo_wanted_id'],
				'bedrooms_required'			=> $row['wo_bedrooms_required'],
				'distance_from_postcode'	           => $row['wo_distance_from_postcode'],
				'location'					=> $row['wo_location'],
				'postcode'					=> $row['wo_postcode'],
                                'country'					=> $row['wo_country'],
                                'street'					=> $row['wo_street'],
                                'area'					    => $row['wo_area']
			);
			$my_wanted_title   = getAdTitle($temp, "wanted", FALSE);
                        $my_wanted_url     = getAdURL($temp, "wanted");
            
			$temp = array (
				'wanted_id'					=> $row['wn_wanted_id'],
				'bedrooms_required'			=> $row['wn_bedrooms_required'],
				'distance_from_postcode'	=> $row['wn_distance_from_postcode'],
				'location'					=> $row['wn_location'],
				'postcode'					=> $row['wn_postcode'],
                                'country'					=> $row['wn_country'],
                                'street'					=> $row['wn_street'],
                                'area'					    => $row['wn_area']
			);
			$wanted_title    = getAdTitle($temp, "wanted", FALSE);
                        $wanted_url      = getAdURL($temp, "wanted");		
			
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/palup.html.twig', array(
                'first_name' => $row['first_name'],
                'wanted' => array('title' => $wanted_title, 'url' => $wanted_url),
                'my_wanted' => array('title' => $my_wanted_title, 'url' => $my_wanted_url),
                'keep_url' => 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=wanted&id='.$row['wo_wanted_id'].'&hash='.$hash,
                'suspend_url' => 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=wanted&id='.$row['wo_wanted_id'].'&hash='.$hash,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Pal-Up, ' . $wanted_title;
            $to = $row['email_address'];
            // $bcc = 'ryanwdavies@gmail.com';
            
            $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
            $sent = $CFSMailer->sendMessage($msg);
			
			// Set the "last_flatmatch" of the wanted ad in question
			$updateQuery = "update cf_wanted set last_palup = now() where wanted_id = '".$row['wo_wanted_id']."';";
			$updateResult = mysqli_query($GLOBALS['mysql_conn'], $updateQuery);
			
			// And finally, create one more row on the table
			
			$t .= '<tr>'."\n";

			$t .= '<td>'.$row['wo_wanted_id'].'</td>'."\n";
			$t .= '<td width="200">'.$wanted_old_title.'</td>'."\n";
			$t .= '<td>matched with </td>'."\n";
			$t .= '<td>'.$row['wn_wanted_id'].'</td>'."\n";
			$t .= '<td width="200">'.$wanted_new_title.'</td>'."\n";
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
<title>Untitled Document</title>
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
<h1>Pal-Up query found <strong><?php print $count?></strong> matches</h1>
<table cellpadding="4" cellspacing="0" class="borders" style="margin-bottom:2em;">
	<tr>
		<th>Old wanted id</th>
		<th>Details </th>
		<th>&nbsp;</th>
		<th>New wanted id</th>
		<th>Details</th>
		<th>User </th>
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
