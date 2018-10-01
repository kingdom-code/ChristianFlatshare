<?php

use CFS\Image\CFSImage;

session_start();

// Autoloader
require_once 'web/global.php';

connectToDB();

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	// Initialise needed variables
	$error = NULL;
	if (isset($_REQUEST['photo_id'])) { $photo_id = $_REQUEST['photo_id'] * 1; } else { header("Location: my-account-manage-ads.php"); exit; }
	if (isset($_GET['direction'])) { $direction = $_GET['direction']; } else { $direction = "clockwise"; }
    
	// Ensure supplied photo_id is valid
	$query = "select * from cf_photos where photo_id = '".$photo_id."'";
	//echo $query."<br/>";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!mysqli_num_rows($result)) {
		die("invalid photo_id");
		exit;
	} else {
		$photo = mysqli_fetch_assoc($result);
		// Ensure photo belongs to the currently logged in user (unless he is an admin)
		$post_type = $photo['post_type'];
		$query = "
			select a.user_id, u.access
			from cf_".$post_type." as `a`
			left join cf_users as `u` on u.user_id = a.user_id
			where a.".$post_type."_id = '".$photo['ad_id']."';		
		";
		//echo $query."<br/>";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (!mysqli_num_rows($result)) {
			die("User does not exist any more");
		} else {
			$user_data = mysqli_fetch_assoc($result);
			// Limit only members
			if ($_SESSION['u_access'] == "member") {
				if ($user_data['user_id'] != $_SESSION['u_id']) {
					die("You do not have permission to modify this photograph");
				}
			}	
		}
	}
	
	// At this stage we know we have permission to rotate a photo that we know exists
	ini_set("memory_limit","20M");
	ini_set("max_execution_time",60);
	
    $filepath = __DIR__ . '/images/photos/' . $photo['photo_filename'];
    
    if (is_readable($filepath)) {
        $file = new SplFileInfo($filepath);
        $image = new CFSImage($file);
        $image->rotateImage($direction);
    }
    
	if ($_GET['ad_id'] && $_GET['type']) {
		// Redirect to step 6
		header("Location: post-".$_GET['type'].".php?step=6&id=".$_GET['ad_id']);
	}
    else {
		// ...we're here from the my ads page / section
		header("Location: post-".$post_type.".php?step=6&id=".$photo['ad_id']);
	}
    
    exit;