<?php

use CFS\Mailer\CFSMailer;
use CFS\Database\CFSDatabase;

// Autoloader
require_once __DIR__ . '/../web/global.php';

// Send Email
$CFSMailer = new CFSMailer();

	// Select all OFFERED ADS that are about to expire in four days
	$query = "
		select
		DATE_FORMAT(o.expiry_date,'%M %D') as `expiry_date`,
		u.first_name,
		u.surname,
		u.email_address,
		u.password,
		o.offered_id, 
		o.bedrooms_available, 
		o.bedrooms_total, 
		o.accommodation_type, 
		o.room_share,
		o.building_type, 
		o.street_name, 
		o.postcode,
		o.town_chosen,
        o.country,
        o.street,
        o.area,
		j.town 
		from cf_offered o, cf_jibble_postcodes j, cf_users u 
		where j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
		and DATE(o.expiry_date) = DATE_ADD(DATE(NOW()),INTERVAL 2 DAY)
		and o.suspended = 0                               -- advert is not suspended		
		and o.published = '1'
		and u.user_id = o.user_id		
		and IFNULL(u.suppressed_replies,0) != 1          -- not a scammer (their ads are not published)		
	";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result) {
        $offered_count = mysqli_num_rows($result);
    }
    else {
        $offered_count = 0;
    }
	if ($offered_count) {
		// We do have offered ads that are about to expire. Send emails.
		while($ad = mysqli_fetch_assoc($result)) {
		
        $post_type = "offered";
		    
    		// Create the hash of last_login and email_address
    		$hash = md5($ad['password'].$ad['email_address']);
		
    		$date_type = "available from"; 
    		$keep_link    = 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=offered&id='.$ad['offered_id'].'&hash='.$hash;
    		$suspend_link = 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=offered&id='.$ad['offered_id'].'&hash='.$hash;
        $id = $ad['offered_id'];
            
    		$title = getAdTitleByID($id, $post_type, FALSE, FALSE, FALSE);
    		$ad_link = 'http://'.SITE.'details.php?id='.$id.'&post_type='.$post_type;

            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/ad_expiry.html.twig', array(
                'first_name' => $ad['first_name'],
                'ad' => array('title' => $title, 'url' => $ad_link, 'expiry_date' => $ad['expiry_date']),
                'date_type' => $date_type,
                'keep_url' => $keep_link,
                'suspend_url' => $suspend_link,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Your advert expires in 2 days!';
            //$bcc = 'ryanwdavies@gmail.com';
            //$to = 'ryanwdavies@gmail.com';
            $to = $ad['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
            $sent = $CFSMailer->sendMessage($msg);
            
            if ($sent > 0) {
                $success = TRUE;
            }
            else {
                $success = FALSE;
            }
		}		
	}

	// Select all WANTED ADS that are about to expire in four days
	$query = "
		select
		DATE_FORMAT(w.expiry_date,'%M %D') as `expiry_date`,
		u.first_name,
		u.surname,
		u.email_address,
		u.password,
		w.wanted_id, 
		w.bedrooms_required,
		w.distance_from_postcode, 
		w.location,
    w.country,
    w.street,
    w.area, 
		w.postcode
		from cf_wanted w, cf_users u
		where w.expiry_date = DATE_ADD(DATE(NOW()),INTERVAL 2 DAY) 
		and w.wanted_id > 100
		and w.published = '1'
		and w.suspended = 0                               -- advert is not suspended		
		and u.user_id = w.user_id		
		and IFNULL(u.suppressed_replies,0) != 1          -- not a scammer (their ads are not published)				
	";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result) {
        $wanted_count = mysqli_num_rows($result);
    }
    else {
        $wanted_count = 0;
    }
	if ($wanted_count) {
		// We do have wanted ads that are about to expire. Send emails.
		while($ad = mysqli_fetch_assoc($result)) {
			
        $post_type = "wanted";
		    
    		// Create the hash of last_login and email_address
    		$hash = md5($ad['password'].$ad['email_address']);
		
    		$date_type = "wanted from"; 
   			$keep_link    = 'http://'.SITE.'email-actions.php?action=keep_ad&post_type=wanted&id='.$ad['wanted_id'].'&hash='.$hash;
   			$suspend_link = 'http://'.SITE.'email-actions.php?action=suspend_ad&post_type=wanted&id='.$ad['wanted_id'].'&hash='.$hash;	
        $id = $ad['wanted_id'];
    		$title = getAdTitleByID($id, $post_type, FALSE, FALSE, FALSE);
    		$ad_link = 'http://'.SITE.'details.php?id='.$id.'&post_type='.$post_type;
            
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/ad_expiry.html.twig', array(
                'first_name' => $ad['first_name'],
                'ad' => array('title' => $title, 'url' => $ad_link, 'expiry_date' => $ad['expiry_date']),
                'date_type' => $date_type,
                'keep_url' => $keep_link,
                'suspend_url' => $suspend_link,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Your advert expires in 2 days!';
            //$bcc = 'ryanwdavies@gmail.com';
            //$to = 'ryanwdavies@gmail.com';
            $to = $ad['email_address'];
            
            $msg = $CFSMailer->createMessage($subject, $body, $to, $bcc);
            $sent = $CFSMailer->sendMessage($msg);
            
            if ($sent > 0) {
                $success = TRUE;
            }
            else {
                $success = FALSE;
            }
		}		
	}
	
	echo $offered_count . " users were emailed about offered ads.\n";
	echo $wanted_count . " users were emailed about wanted ads.\n";
	
?>
