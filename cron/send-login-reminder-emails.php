<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once __DIR__ . '/../web/global.php';

	// ***********************************************************
	// ***********************************************************
	// Select all OFFERED ADS whose user has NOT logged in for 20 days
	$query = "
		SELECT
		    o.town_chosen,
			j.town, 
			u.user_id, 
			u.first_name,
			u.email_address,
			u.last_login,
			u.password,
			o.offered_id,
			o.bedrooms_available, 
			o.bedrooms_total, 
			o.accommodation_type, 
			o.building_type, 
			o.street_name, 
			o.postcode,
            o.country,
            o.street,
            o.area,
			DATEDIFF(CURDATE(),u.last_login) as `last_login_days` 
		FROM   cf_users u, cf_offered o, cf_jibble_postcodes j
		WHERE  j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
	 	AND    (DATEDIFF(NOW(),last_login) =  21
 		        OR 
  				DATEDIFF(NOW(),last_login)  = 28)            -- 21st and 28th day		
		AND    o.expiry_date > CURDATE()                     -- advert is current
		AND    o.suspended = 0                               -- advert is not suspended
		AND    o.published = 1		
		AND    u.user_id = o.user_id
		AND    IFNULL(u.suppressed_replies,0) != 1          -- not a scammer
	";

	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$offered_count = mysqli_num_rows($result);
	if ($offered_count) {
		// We have offered ads whose user has not logged in for 20 days. Send email
		while($row = mysqli_fetch_assoc($result)) {
			
			// Create the arrays to send to the getAdTitle page
			$offered_title  = getAdTitle($row, "offered", FALSE);
			$offered_url    = getAdURL($row, "offered");
            
			// Create the hash of last_login and email_address
			$hash = md5($row['password'].$row['email_address']);
			
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/login_reminder.html.twig', array(
                'first_name' => $row['first_name'],
                'advert' => array('title' => $offered_title, 'url' => $offered_url),
                'last_login_days' => $row['last_login_days'],
                'keep_url' => 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=offered&id='.$row['offered_id'].'&hash='.$hash,
                'suspend_url' => 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=offered&id='.$row['offered_id'].'&hash='.$hash,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Is your advert still needed?';
            $to = $row['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to);
            $sent = $CFSMailer->sendMessage($msg);
		}		
	}

	// Select all OFFERED ADS whose user has NOT logged in for > 30 days
	$query = "
		SELECT
		    o.town_chosen,
			j.town, 
			u.user_id, 
			u.first_name,
			u.email_address,
			u.last_login,
			u.password,
			o.offered_id,
			o.bedrooms_available, 
			o.bedrooms_total, 
			o.accommodation_type, 
			o.building_type, 
			o.street_name, 
			o.postcode, 
            o.country,
            o.street,
            o.area,
			DATEDIFF(CURDATE(),u.last_login) as `last_login_days` 
		FROM   cf_users u, cf_offered o, cf_jibble_postcodes j
		WHERE  j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
	 	AND    DATEDIFF(NOW(),last_login)  > 30              -- more than 30 days
		AND    o.expiry_date > CURDATE()                     -- advert is current
		AND    o.suspended = 0                               -- advert is not suspended
		AND    o.published = 1		
		AND    u.user_id = o.user_id
		AND    IFNULL(u.suppressed_replies,0) != 1          -- not a scammer (their ads are not published)
	";

	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$offered_suspended_count = mysqli_num_rows($result);
	if ($offered_suspended_count) {
		// We have offered ads whose user has not logged in for more than 30 days. Send email
		// and suspend advert
		while($row = mysqli_fetch_assoc($result)) {
			
			// Create the arrays to send to the getAdTitle page
			$offered_title  = getAdTitle($row, "offered", FALSE);
			$offered_url    = getAdURL($row, "offered");
            
			// Create the hash of last_login and email_address
			$hash = md5($row['password'].$row['email_address']);
			
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/login_suspend_ad.html.twig', array(
                'first_name' => $row['first_name'],
                'advert' => array('title' => $offered_title, 'url' => $offered_url),
                'last_login_days' => $row['last_login_days'],
                'unsuspend_url' => 'http://'.SITE.'email-actions.php?action=unsuspend_ad&post_type=offered&id='.$row['offered_id'].'&hash='.$hash,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Your advert has been suspended';
            $to = $row['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to);
            $sent = $CFSMailer->sendMessage($msg);
            
			// Suspend selected ads			
			$update_query = "UPDATE cf_offered SET suspended = 1, last_updated_date = NOW() WHERE offered_id = ".$row['offered_id'];
			$update_result = mysqli_query($GLOBALS['mysql_conn'], $update_query);
			$update_query = "UPDATE cf_users SET last_login = NOW() WHERE user_id = ".$row['user_id'];
			$update_result = mysqli_query($GLOBALS['mysql_conn'], $update_query);				
		}		
	}


   

	// ***********************************************************
	// ***********************************************************
	// Select all WANTED ADS that are about to expire in four days
	$query = "
		SELECT
		  DATE_FORMAT(w.expiry_date,'%W, %D %M %Y') as `expiry_date`,
  		  u.first_name,
		  u.surname,
		  u.email_address,
		  u.password,
		  w.wanted_id, 
		  w.bedrooms_required,
		  w.distance_from_postcode, 
		  w.location, 
		  w.postcode,
          w.country,
          w.street,
          w.area,
  	  	  DATEDIFF(CURDATE(),u.last_login) as `last_login_days`		  
		FROM cf_wanted as `w` 
		left join cf_users as `u` on w.user_id = u.user_id
		where u.user_id = w.user_id
 		AND   (DATEDIFF(NOW(),last_login) =  21
 		        OR 
				DATEDIFF(NOW(),last_login) = 28)            -- 21st and 28th day		
		AND    w.expiry_date > CURDATE()                     -- advert is current
		AND    w.suspended = 0                               -- advert is not suspended
		AND    w.published = 1		
		AND    IFNULL(u.suppressed_replies,0) != 1          -- not a scammer
	";

	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$wanted_count = mysqli_num_rows($result);
	if ($wanted_count) {
		// We have offered ads whose user has not logged in for 20 days. Send email
		while($row = mysqli_fetch_assoc($result)) {
			
			// Create the arrays to send to the getAdTitle page
			$wanted_title  = getAdTitle($row, "wanted", FALSE);
			$wanted_url    = getAdURL($row, "wanted");
            
			// Create the hash of last_login and email_address
			$hash = md5($row['password'].$row['email_address']);
			
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/login_reminder.html.twig', array(
                'first_name' => $row['first_name'],
                'advert' => array('title' => $wanted_title, 'url' => $wanted_url),
                'last_login_days' => $row['last_login_days'],
                'keep_url' => 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=wanted&id='.$row['wanted_id'].'&hash='.$hash,
                'suspend_url' => 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=wanted&id='.$row['wanted_id'].'&hash='.$hash,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Is your advert still needed?';
            $to = $row['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to);
            $sent = $CFSMailer->sendMessage($msg);
		}		
	}
    
	// Suspend adverts	
	$query = "
		SELECT
		  DATE_FORMAT(w.expiry_date,'%W, %D %M %Y') as `expiry_date`,
  		  u.first_name,
		  u.surname,
		  u.email_address,
		  u.password,
		  w.wanted_id, 
		  w.bedrooms_required,
		  w.distance_from_postcode, 
		  w.location, 
		  w.postcode,
          w.country,
          w.street,
          w.area,
  	  	  DATEDIFF(CURDATE(),u.last_login) as `last_login_days`		  
		FROM cf_wanted as `w` 
		left join cf_users as `u` on w.user_id = u.user_id
		where u.user_id = w.user_id
 		AND    DATEDIFF(NOW(),last_login) > 30               -- 21st and 28th day		
		AND    w.expiry_date > CURDATE()                     -- advert is current
		AND    w.suspended = 0                               -- advert is not suspended
		AND    w.published = 1		
		AND    IFNULL(u.suppressed_replies,0) != 1           -- not a scammer
	";

	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$wanted_suspended_count = mysqli_num_rows($result);
	if ($wanted_suspended_count) {
		// We have offered ads whose user has not logged in for 20 days. Send email
		while($row = mysqli_fetch_assoc($result)) {
			
			// Create the arrays to send to the getAdTitle page
			$wanted_title  = getAdTitle($row, "wanted", FALSE);
			$wanted_url    = getAdURL($row, "wanted");
            
			// Create the hash of last_login and email_address
			$hash = md5($row['password'].$row['email_address']);
			
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/login_suspend_ad.html.twig', array(
                'first_name' => $row['first_name'],
                'advert' => array('title' => $wanted_title, 'url' => $wanted_url),
                'last_login_days' => $row['last_login_days'],
                'unsuspend_url' => 'http://'.SITE.'email-actions.php?action=unsuspend_ad&post_type=wanted&id='.$row['offered_id'].'&hash='.$hash,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Your advert has been suspended';
            $to = $row['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to);
            $sent = $CFSMailer->sendMessage($msg);

			// Suspend selected ads			
			$update_query = "UPDATE cf_wanted SET suspended = 1, last_updated_date = NOW() WHERE wanted_id = ".$row['wanted_id'];
			$update_result = mysqli_query($GLOBALS['mysql_conn'], $update_query);			
			$update_query = "UPDATE cf_users SET last_login = NOW() WHERE user_id = ".$row['user_id'];
			$update_result = mysqli_query($GLOBALS['mysql_conn'], $update_query);						
		}		
	}
	
		echo $offered_count . " users were emailed about offered ads - reminder.<br/>";
		echo $wanted_count . " users were emailed about wanted ads - reminder.<br/>";
		echo $offered_suspended_count . " users were emailed about offered ads - suspended.<br/>";
		echo $wanted_suspended_count . " users were emailed about wanted ads - suspended.<br/>";		
?>	