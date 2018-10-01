<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once __DIR__ . '/../web/global.php';

	// Select all ads that do not have photos and were published 2 or 10 days ago
	$query = "
	SELECT  u.user_id, u.email_address, o.offered_id, 'offered' as 'post_type'
	FROM 	cf_users u,
			cf_offered o
	WHERE o.published = 1
	AND  o.created_Date > date(now() - INTERVAL 2 DAY) -- published two days ago
	AND  o.created_Date < date(now() - INTERVAL 1 DAY) -- published two days ago
	AND  o.expiry_date > now()
	AND  o.suspended = 0
	AND  u.user_id = o.user_id
	AND NOT EXISTS (
		SELECT 'x'
		FROM   cf_photos as `p`
		WHERE  o.offered_id = p.ad_id
		AND    p.post_type = 'offered'
	)
	UNION
	SELECT u.user_id, u.email_address, w.wanted_id, 'wanted' as 'post_type'
	FROM cf_wanted w, cf_users u
	WHERE w.published = 1
	AND  w.created_Date > date(now() - INTERVAL 2 DAY) -- published two days ago
	AND  w.created_Date < date(now() - INTERVAL 1 DAY) -- published two days ago
	AND  w.expiry_date > now()
	AND  w.suspended = 0
	AND  u.user_id = w.user_id
	AND NOT EXISTS (
		SELECT 'x' 
		FROM   cf_photos as `p` 
		WHERE  w.wanted_id = p.ad_id 
		AND    p.post_type = 'wanted'
	)
	UNION
	SELECT  u.user_id, u.email_address, o.offered_id, 'offered' as 'post_type'
	FROM 	cf_users u,
			cf_offered o
	WHERE o.published = 1
	AND  o.created_Date > date(now() - INTERVAL 10 DAY) -- published two days ago
	AND  o.created_Date < date(now() - INTERVAL 9 DAY) -- published two days ago
	AND  o.expiry_date > now()
	AND  o.suspended = 0
	AND  u.user_id = o.user_id
	AND NOT EXISTS (
		SELECT 'x'
		FROM   cf_photos as `p`
		WHERE  o.offered_id = p.ad_id
		AND    p.post_type = 'offered'
	)
	UNION
	SELECT u.user_id, u.email_address, w.wanted_id, 'wanted' as 'post_type'
	FROM cf_wanted w, cf_users u
	WHERE w.published = 1
	AND  w.created_Date > date(now() - INTERVAL 10 DAY) -- published two days ago
	AND  w.created_Date < date(now() - INTERVAL 9 DAY) -- published two days ago
	AND  w.expiry_date > now()
	AND  w.suspended = 0
	AND  u.user_id = w.user_id
	AND NOT EXISTS (
		SELECT 'x' 
		FROM   cf_photos as `p` 
		WHERE  w.wanted_id = p.ad_id 
		AND    p.post_type = 'wanted'
	)	
	
	";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
    if ($result) {
    	if (mysqli_num_rows($result)) {
		
    		$count = 0;
    		while($row = mysqli_fetch_assoc($result)) {
            
                // Send Email
                $CFSMailer = new CFSMailer();
            
                // Get Body
                if ($row['post_type'] == 'wanted') {            
                    $body = $twig->render('emails/photo_reminder_wanted.html.twig', array(
                        'image_url' => 'http://' . SITE . 'images/email_wanted_photos.jpg',
                    ));
                }
                else {
                    $body = $twig->render('emails/photo_reminder_offered.html.twig', array(
                        'image_url' => 'http://' . SITE . 'images/email_offered_photos.jpg',
                    ));
                }
            
                // Set variables
                $subject = 'Christian Flatshare - Photos will help your advert...';
                $to = $row['email_address'];
            
                $msg = $CFSMailer->createMessage($subject, $body, $to);
                $sent = $CFSMailer->sendMessage($msg);
            
    			$count++;
			
    		}
					
    		echo $count . " users were emailed with the photo reminder email.";
    	}
        else {
    		echo "0 users were emailed with the photo reminder email.";
    	}
	}
    else {
        echo "0 users were emailed with the photo reminder email.";
    }