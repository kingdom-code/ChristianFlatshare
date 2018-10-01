<?php

// Autoloader
require_once 'web/global.php';

connectToDB();
	
	if (isset($_GET['lets_have_a_look_at'])) { 
		$banner_id = trim($_GET['lets_have_a_look_at']); 
	} else { 
		header("Location: index.php");
		exit;
	}
	
	// Load the information of the banner from teh database
	$query = "SELECT * FROM cf_banners WHERE banner_id = '".$banner_id."'";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	if (!$result) {
		header("Location: index.php");
		exit;
	} else if (mysqli_num_rows($result) == 0) {
		header("Location: index.php");
		exit;	
	} else {
		$banner = mysqli_fetch_assoc($result);
		
		// Capture statistics
		$query = "
			INSERT INTO cf_banners_clicks SET
				banner_id 	= 	'".$banner_id."',
				IP			= 	'".$_SERVER['REMOTE_ADDR']."',
				time		=	now();
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		// Redirect to the appropriate link
		
		header("Location: ".$banner['link']);		
		
	}
	
?>