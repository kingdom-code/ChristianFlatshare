<?php

use CFS\Database\CFSDatabase;
use CFS\GeoEncoding\CFSGeoEncoding;
use CFS\Image\CFSImage;
    
ini_set("session.gc_maxlifetime","3600");
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

$database = new CFSDatabase();
$connection = $database->getConnection();
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// ********************************************************************************
	// Initalise variables
	// ********************************************************************************
	$id				= init_var("id","REQUEST");					// Necessary, refers to ad being edited
	$step			= init_var("step","REQUEST",1);				// Necessary, refers to current step in the process
	if ($step > 6) { $step = 1; }
	$step_request	= init_var("step_request");
	$direction		= init_var("submit","POST","next_step");	// By default, we're going forward in steps
	
	// http://www.webmasterworld.com/forum88/4894.htm Problem using "VALUE = "	in IE	
        if ($_POST['submit_save_changes_x'] > 0) { $direction = "save_changes";} 
	if ($_POST['submit_prev_step_x'] > 0) 	 { $direction = "prev_step"; }
	if ($_POST['submit_next_step_x'] > 0)	   { $direction = "next_step"; }	
	if ($_POST['submit_publish_x'] > 0) 	   { $direction = "publish"; }		
		
	$error			= array();
	$update_error	= "";
	
	// FIRST PANE elements
	$location				= init_var("location");
	$postcode				= init_var("postcode");
	$distance_from_postcode	                = init_var("distance_from_postcode", "POST", 5);	
	$available_date			        = init_var("available_date");
	$min_term				= init_var("min_term","POST",0);
	$max_term				= init_var("max_term","POST",999);
    $street                 = init_var("street");
    $area                   = init_var("area");
    $region                 = init_var("region");
    $lookup_type            = init_var("lookup_type");
    $country                = init_var("country");
    $postal_code            = init_var("postal_code");
	$longitude				= init_var("longitude");
	$latitude				= init_var("latitude");
    
    // Use correct country settings
    // If we have a country in the databse use that, otherwise
    // use the users country to start the ad off
    if (!empty($ad['latitude'])) {
        $country = (empty($ad['country'])) ? 'GB' : $ad['country'];
        $CFSIntl->setAppCountry($country);
    }
    else {
        $CFSIntl->setAppCountry($userCountry['iso']);
    }
    $appCountry = $CFSIntl->getAppCountry();

	// SECOND PANE elements
	$accommodation_type_flat_share		= init_var("accommodation_type_flat_share");
	$accommodation_type_family_share	= init_var("accommodation_type_family_share");
	$accommodation_type_whole_place		= init_var("accommodation_type_whole_place");	
	$accommodation_type_room_share		= init_var("accommodation_type_room_share");	
	if (isset($_POST['building_type_house'])) { $building_type_house = $_POST['building_type_house']; } else { if (!$_POST) { $building_type_house = 1; } else { $building_type_house = 1; } }
	if (isset($_POST['building_type_flat'])) { $building_type_flat = $_POST['building_type_flat']; } else { if (!$_POST) { $building_type_flat = 1; } else { $building_type_flat = 1; } }
	$bedrooms_required			        = init_var("bedrooms_required","POST",1);
    $price_pcm                          = (isset($_POST['price_pcm'])) ? $CFSIntl->parseCountryCurrency($_POST['price_pcm'], 'app') : NULL;	
	$furnished					        = init_var("furnished","POST","furnished or unfurnished");
	$shared_lounge_area			        = init_var("shared_lounge_area");
	$central_heating			        = init_var("central_heating");
	$washing_machine			        = init_var("washing_machine");
	$garden_or_terrace			        = init_var("garden_or_terrace");
	$bicycle_store				        = init_var("bicycle_store");
	$dish_washer				        = init_var("dish_washer");
	$tumble_dryer				        = init_var("tumble_dryer");
	$ensuite_bathroom			        = init_var("ensuite_bathroom");
	$parking					        = init_var("parking");
	
	// THIRD PANE elements
	$current_age						= init_var("current_age","POST",0); // Not for DB insert, used to calc min_age and max_age if one person.	
	$current_num_males			        = init_var("current_num_males");
	$current_num_females		        = init_var("current_num_females");
	$current_min_age				    = init_var("current_min_age","POST",0);
	$current_max_age				    = init_var("current_max_age","POST",0);
	$current_occupation			        = init_var("current_occupation");
	$current_is_couple			        = init_var("current_is_couple");
	$current_is_family			        = init_var("current_is_family");
	$church_reference				    = init_var("church_reference");
	$church_attended				    = addslashes(init_var("church_attended"));
	if (isset($_POST['church_url'])) { $church_url = addslashes(strip_http(trim($_POST['church_url']))); } else { $church_url = NULL; }	
	$accommodation_situation	        = addslashes(init_var("accommodation_situation"));
	
	// FOURTH PANE elements
	if (isset($_POST['shared_adult_members'])) { $shared_adult_members = $_POST['shared_adult_members']; } else { $shared_adult_members = ($_POST)? NULL : "4+"; }
	$shared_males 				        = init_var("shared_males");
	$shared_females 			        = init_var("shared_females");
	$shared_mixed 				        = init_var("shared_mixed");
	$shared_min_age 			        = init_var("shared_min_age");
	$shared_max_age 			        = init_var("shared_max_age");
	$shared_student 			        = init_var("shared_student");
	$shared_mature_student 		        = init_var("shared_mature_student");
	$shared_professional 		        = init_var("shared_professional");
	$shared_other 				        = init_var("shared_other");
	$shared_owner_lives_in 		        = init_var("shared_owner_lives_in");
	$shared_married_couple 		        = init_var("shared_married_couple");
	$shared_family 				        = init_var("shared_family");
	
	// FIFTH PANE elements
	$contact_name				        = addslashes(init_var("contact_name"));
	$contact_phone				        = addslashes(init_var("contact_phone"));
	$flatmatch					        = init_var("flatmatch","POST",0);
	$palup						        = init_var("palup","POST",0);
	$picture					        = init_var("picture");
    
	// SIXTH PANE elements
	$photo_update_captions		        = init_var("photo_update_captions");
	$photo_delete				        = init_var("photo_delete");

	// ********************************************************************************
	// Security check
	// Ensure ad we're editing belongs to current user
	// ********************************************************************************
	$query = "select * from cf_wanted where wanted_id = '".$id."'";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!$result || mysqli_num_rows($result) == 0) { header("Location: post-choice.php"); exit; }
	$ad = mysqli_fetch_assoc($result);
	if ($ad['user_id'] != $_SESSION['u_id']) {
		header("Location: your-account-manage-posts.php");		
		//die("You do not have permissions to edit this advertisement.");
	}
 
    // Age Slider Setup
    if (isset($_POST['currentAgeRange'])) {
        $currentAgeRange = $_POST['currentAgeRange'];
    }
    else if (isset($ad['current_min_age']) && isset($ad['current_max_age'])) {
        if ($ad['current_min_age'] <= 18) $ad['current_min_age'] = 18;
        if ($ad['current_max_age'] >= 51) $ad['current_max_age'] = 51;
        
        $currentAgeRangePreview = cleanAge($ad['current_max_age'], $ad['current_max_age'], 'current');
        $currentAgeRange = reverseAgeConvert($ad['current_min_age']) . '-' . reverseAgeConvert($ad['current_max_age']);
    }
    else {
        $currentAgeRangePreview = cleanAge(21, 45, 'current');
        $currentAgeRange = '2-6';
    }
    
    if (isset($_POST['suitAgeRange'])) {
        $suitAgeRange = $_POST['suitAgeRange'];
    }
    else if (isset($ad['shared_min_age']) && isset($ad['shared_max_age'])) {
        if ($ad['shared_min_age'] <= 18) $ad['shared_min_age'] = 18;
        if ($ad['shared_max_age'] >= 51) $ad['shared_max_age'] = 51;
        
        $suitAgeRangePreview = cleanAge($ad['shared_min_age'], $ad['shared_max_age'], 'suit');
        $suitAgeRange = reverseAgeConvert($ad['shared_min_age']) . '-' . reverseAgeConvert($ad['shared_max_age']);
    }
    else {
        $suitAgeRangePreview = cleanAge(18, 51, 'suit');
        $suitAgeRange = '1-8';
    }
    
    // LEGACY SUPPORT
    // Convert just postcodes into Lat/Lng Data
    if (!empty($ad['postcode']) && empty($ad['latitude'])) {
        $geoEncode = new CFSGeoEncoding();
        $location = $geoEncode->getLookup($ad['postcode'], 'GB');
        $address = $geoEncode->getReverseLookup($location['lat'], $location['lng']);
        
        $sql = "UPDATE cf_wanted SET
            latitude = :latitude,
            longitude = :longitude,
            street = :street,
            area = :area,
            region = :region,
            country = :country,
            postal_code = :postal_code
        WHERE wanted_id = :id
        AND user_id = :user_id";
        
        $stmt = $connection->prepare($sql);
        $stmt->bindValue("latitude", $address['latitude']);
        $stmt->bindValue("longitude", $address['longitude']);
        $stmt->bindValue("street", $address['street']);
        $stmt->bindValue("area", $address['area']);
        $stmt->bindValue("region", $address['region']);
        $stmt->bindValue("country", $address['country']);
        $stmt->bindValue("postal_code", $address['postal_code']);
        $stmt->bindValue("id", $id);
        $stmt->bindValue("user_id", getUserIdFromSession());
        $result = $stmt->execute();
        
        $sql = "SELECT * FROM cf_wanted WHERE wanted_id = :id";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue("id", $id);
        $stmt->execute();
        $ad = $stmt->fetch();
    }

	// ********************************************************************************
	// Whole place exception
	// If ONLY whole place is chosen, set a flag (to help us hide elements as needed)
	// ********************************************************************************
	if (
		$ad['accommodation_type_whole_place'] &&
		!$ad['accommodation_type_flat_share'] &&
		!$ad['accommodation_type_family_share'] &&
		!$ad['accommodation_type_room_share']
	){
		$whole_place = TRUE;
		if ($step == 4) {
		
			header("Location: ".$_SERVER['PHP_SELF']."?id=".$id."&step=5");
			exit;
		
		}
	} else {
		$whole_place = FALSE;
	}
  
	// ********************************************************************************
	// Handle POST action
	// $_POST to this page = we need to 1) validate and 2) update db with data
	// ********************************************************************************
	if ($_POST) {
	
		switch($step) {
		
			// STEP 1: Location and dates
			case 1:
			
				$debug .= debugEvent("Step 1 POST",print_r($_POST,true));	
				
                                if (empty($longitude) || empty($latitude)) {
                                  $error['geo'] = 'Type in desired location and select a match from the list shown';
                                }

                               if ($street == 'undefined' || $region == 'undefined' || $area == 'undefined') {
                                 $error['geo'] = 'Please move the marker to display a proper address';
                               }

				if (!$distance_from_postcode) {
					$error['distance_from_postcode'] = 'Please indicate distance from place';
				}
				
            	  		if ($available_date == 0 ) {
					$error['available_date'] = ' Please choose the date when accommodation is needed';
				}							
		
				// If a min_term AND a max_term have been defined, make sure that max_term is after min_term
				if ($min_term && $max_term != "999") {
					if ($min_term > $max_term) {
						$error['term'] = 'Maximum term must be larger than the minimum term';
					}
				}
                
				// If we've encountered no error
				if (!$error) {

                    $sql = "UPDATE cf_wanted SET
                        last_updated_date = now(),
                        location = :location,";
                    // if GB ensure that only the first half of the postcode is stored,
                    // otherwise this causes problems with jibble/seach
                    if ($appCountry['iso'] == 'GB') {
                       $sql .= "postcode = SUBSTRING_INDEX(:postcode,' ',1),";
                    } else {
                       $sql .= "postcode = :postcode,";
                    }
                    $sql .= "distance_from_postcode = :distance_from_postcode,
                        latitude = :latitude,
                        longitude = :longitude,
                        available_date = :available_date,
                        expiry_date = DATE_ADD(:available_date, interval 10 day),
                        min_term = :min_term,
                        max_term = :max_term,
                        street = :street,
                        area = :area,
                        region = :region,
                        country = :country,
                        postal_code = :postal_code
                    WHERE wanted_id = :id
                    AND user_id = :user_id";
                    
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue("postcode", $postal_code);
                    $stmt->bindValue("distance_from_postcode", $distance_from_postcode);
                    $stmt->bindValue("latitude", $latitude);
                    $stmt->bindValue("longitude", $longitude);
                    $stmt->bindValue("available_date", $available_date);
                    $stmt->bindValue("min_term", $min_term);
                    $stmt->bindValue("max_term", $max_term);
                    $stmt->bindValue("street", $street);
                    $stmt->bindValue("area", $area);
                    $stmt->bindValue("region", $region);
                    $stmt->bindValue("country", $country);
                    $stmt->bindValue("postal_code", $postal_code);
                    $stmt->bindValue("id", $id);
                    $stmt->bindValue("location", $street . ', ' . $area);
                    $stmt->bindValue("user_id", getUserIdFromSession());
                    $result = $stmt->execute();

					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting wanted ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}
				
				}
					
				break;
				
			// STEP 2: Accommodation details
			case 2:

				$debug .= debugEvent("Step 1 POST",print_r($_POST,true));
				
				// Validate only if going forward
				if ($direction != "prev_step") {
				
					// Make sure at least one accommodation_type was picked
					if (!$accommodation_type_flat_share && !$accommodation_type_family_share && !$accommodation_type_whole_place && !$accommodation_type_room_share) {
						$error['accommodation_type'] = 'Please select one or more accommodation types';
					}		
					// Make sure at least one building_type was picked
					if (!$building_type_house && !$building_type_flat) {
						$error['building_type'] = 'Please indicate a building type';
					}
					if (!preg_match('/^([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2})$/',trim(preg_replace(REGEXP_CURRENCY,'', str_replace('\\','',$price_pcm))))) { 
						$error['price_pcm'] = 'Please enter a monthly price (e.g. &quot;250&quot;, without a "<html>&pound; </html>" sign)';
					}
					if ($price_pcm == 0 ) { $error['price_pcm'] = 'Enter an amount, e.g. &quot;250&quot;, without a "<html>&pound; </html>" sign'; }					
					
					
				}
				
				// If we've encountered no error
				if (!$error) {
				
					// Update DB
					$query = "
						UPDATE cf_wanted SET
							
							accommodation_type_flat_share		= '".intval($accommodation_type_flat_share)."',
							accommodation_type_family_share		= '".intval($accommodation_type_family_share)."',
							accommodation_type_whole_place		= '".intval($accommodation_type_whole_place)."',
							accommodation_type_room_share		= '".intval($accommodation_type_room_share)."',
							building_type_flat		= '".intval($building_type_flat)."', 
							building_type_house		= '".intval($building_type_house)."',
							bedrooms_required		= '".$bedrooms_required."', 
							price_pcm				= '".$price_pcm."',
							furnished				= '".$furnished."',
							shared_lounge_area  	= '".intval($shared_lounge_area) ."',
							central_heating  		= '".intval($central_heating)."',
							washing_machine  		= '".intval($washing_machine)."',
							garden_or_terrace  		= '".intval($garden_or_terrace)."',
							bicycle_store 			= '".intval($bicycle_store)."',
							dish_washer  			= '".intval($dish_washer)."',
							tumble_dryer  			= '".intval($tumble_dryer)."',
							ensuite_bathroom  		= '".intval($ensuite_bathroom)."',
							parking  				= '".intval($parking)."'
						WHERE wanted_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';
					";
					$debug .= debugEvent("Step 2 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting wanted ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}
				
				}
								
				break;
				
			case 3:
				
				// Validate only if going forward
				if ($direction != "prev_step") {

					if (!$current_num_males && !$current_num_females) {
						$error['current_num_males'] = 'Please select how many males / females are looking for accommodation';
					}	
					
                    // CONVERT AGES
                    list($min, $max) = explode('-', $currentAgeRange);
                
                    $minimum_age = ageConvert($min, 'min_age');
                    $maximum_age = ageConvert($max, 'max_age');

					if (!$current_occupation) { 
						$error['current_occupation'] = 'Please choose your occupation';
					}
					if (!$church_attended) {
						$error['church_attended'] = "Please enter church name<br/>";
					}
				
					if (strlen($accommodation_situation) < 150) {
						$error['accommodation_situation'] = 'Please enter at least 150 characters';
					}
				
				} // prev step if
				
				// If we've encountered no error
				if (!$error) {
				
					// Update DB
					$query = "
						UPDATE cf_wanted SET
							current_num_males 		= '".$current_num_males."',            
							current_num_females 	= '".$current_num_females."',        
							current_min_age 		= '".$minimum_age."',                
							current_max_age 		= '".$maximum_age."',                
							current_occupation	 	= '".$current_occupation."',     
							current_is_couple 		= '".intval($current_is_couple)."',            
							current_is_family 		= '".intval($current_is_family)."',            
							church_reference 		= '".intval($church_reference)."',              
							church_attended 		= '".$church_attended."',                
							church_url 				= '".$church_url."',                          
							accommodation_situation = '".$accommodation_situation."'
						WHERE wanted_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';					
					";
					$debug .= debugEvent("Step 3 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting wanted ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}
				
				}			
				
				break;
				
			case 4:
				
				if (!$whole_place) {
				
					// Validate only if going forward
					if ($direction != "prev_step") {
					
						if (!$shared_adult_members && ($accommodation_type_family_share || $accommodation_type_flat_share || $accommodation_type_room_share)) {
							$error['shared_adult_members'] = "Please select the maximum number of adult household members<br/>";
						}
						if (
							!$shared_males && 
							!$shared_females && 
							!$shared_mixed ) {
							$error['shared_gender'] = "Please choose your household preference";
						}		
						
						if ($shared_min_age && $shared_max_age && ($shared_min_age > $shared_max_age)) {
							$error['shared_age'] = "Min age cannot be larger than max";
						}
						
					}
                    
                    // CONVERT AGES
                    list($min, $max) = explode('-', $suitAgeRange);
                
                    $minimum_age = ageConvert($min, 'min_age');
                    $maximum_age = ageConvert($max, 'max_age');
                    
					// If we've encountered no error
					if (!$error) {
					
						// Update DB
						$query = "
							UPDATE cf_wanted SET
								last_updated_date 			= now(),
								shared_adult_members 		= '".$shared_adult_members."',
								shared_males 				= '".intval($shared_males)."',
								shared_females 				= '".intval($shared_females)."',
								shared_mixed 				= '".intval($shared_mixed)."',
								shared_min_age 				= '".$minimum_age."',
								shared_max_age 				= '".$maximum_age."',
								shared_student 				= '".intval($shared_student)."',
								shared_mature_student 		= '".intval($shared_mature_student)."',
								shared_professional			= '".intval($shared_professional)."',
								shared_owner_lives_in 		= '".intval($shared_owner_lives_in)."',
								shared_married_couple 		= '".intval($shared_married_couple)."',
								shared_family 				= '".intval($shared_family)."'
							WHERE wanted_id = '".$id."'
							AND user_id = '".$_SESSION['u_id']."';					
						";
						$debug .= debugEvent("Step 4 query:",$query);
						$result = mysqli_query($GLOBALS['mysql_conn'], $query);
						if (!$result) {
							$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting wanted ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
						}
					
					}	
				
				}
				
				break;
				
			case 5:
			
				// Validate only if going forward
				if ($direction != "prev_step") {
	
					if (!$contact_name) { 
						$error['contact_name'] = 'Please enter your contact name<br/>'; 
					}
					if (!$picture) {
						$error['picture'] = 'Please choose an advert picture for your ad';
					}
					
				}

				// If we've encountered no error
				if (!$error) {
                    
					// Update DB
					$query = "
						UPDATE cf_wanted SET
							last_updated_date 			= now(),
							picture						= '".$picture."',
							contact_name				= '".$contact_name."',
							contact_phone				= '".$contact_phone."',
							flatmatch					= '".intval($flatmatch)."',
							palup						= '".intval($palup)."'
						WHERE wanted_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';					
					";
					$debug .= debugEvent("Step 5 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting wanted ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}
				
				}
				
				break;
				
			case "6":
				
				/*
					
					The $direction variable usually gets "prev_step" or "next_step" as values,
					depending on which button was clicked (previous or next respectively).
					
					In step 6, we're handling image uploads so the direction variable will also get
					some other values (namely the names of each of the extra submit buttons we have
					appart from "prev_step" and "next_step".
					
					Specifically:
					
					"Upload photo" -> User has associated a file and is uploading it
					
				*/
				
				if ($direction == "Upload photo") {
					
                    // Get any potentially uploaded file
                    if (isset($_FILES['userfile']['tmp_name']) && is_readable($_FILES['userfile']['tmp_name'])) {
                        $file = new SplFileInfo($_FILES['userfile']['tmp_name']);
                        $image = new CFSImage($file);
                    }
                    else {
                        $image = NULL;
                    }
                
                    if ($image !== NULL) {
                        try {
                            // Validate file type
                            $image->validateFileExtension(array('jpg'));
                    
                            // Validate file size (in MB)
                            $image->validateFileSize(20);
                    
                            // Validate image size (in px)
                            $image->validateImageSize(480, 480);
                        } catch(Exception $e) {
                            $error['upload'] = $e->getMessage();
                        }
                    
                        if (!isset($error['upload'])) {
                            // Attempt scale and save
                            try {
                                // Assume portrait
                                $filename = $image->scaleAndSave($id, 'wanted', 640, 480);
                            } catch(Exception $e) {
                                $error['upload'] = $e->getMessage();
                            }
                        
                            if (isset($filename)) {
                                print $filename;
                            }
                        }
                    }				
				
				}
				
				// If we're updating the captions of the photos
				if ($photo_update_captions) {
					/*
					The post data will contain something along the lines of:
					[id] => 1622
					[post_type] => wanted
					[ad_caption_1204] => (enter caption)
					[ad_caption_1198] => (enter caption)
					[ad_caption_1205] => (enter caption)
					*/
					foreach($_POST as $key => $value) {
						if (preg_match('/^ad_caption_(\d+)$/',$key,$matches) && trim($value) != "(enter caption)") {
							$query = "update cf_photos set caption = '".trim($value)."' where post_type = 'wanted' and photo_id = '".$matches[1]."'";
							$result = mysqli_query($GLOBALS['mysql_conn'], $query);
						}
					}	
				}	
				
				// If we're deleting photos
				if ($photo_delete) {
				
					// First, find out all details for the selected images
					$sqlWhere = "";
					foreach($_POST['ad'] as $ad_id) { $sqlWhere .= $ad_id.","; }
					$sqlWhere = substr($sqlWhere,0,-1);
					$query = "select * from cf_photos where post_type = 'wanted' and photo_id in (".$sqlWhere.");";
					$debug .= debugEvent("Delete query #1",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					// Now delete all the image files
					while ($row = mysqli_fetch_assoc($result)) {
                        if (is_readable("images/photos/" . $row['photo_filename'])) {
                            $file = new SplFileInfo("images/photos/" . $row['photo_filename']);
                            $image = new CFSImage($file);
                            $image->removeImage();
                        }
					}
					$query = "delete from cf_photos where post_type = 'wanted' and photo_id in (".$sqlWhere.");";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						$msg = '<p class="success">Photo(s) deleted</p>';
					} else {
						$msg = '<p class="error">There was an error deleting photo(s). Please contact '.TECH_EMAIL.'</p>';
					}
					break;	
				
				}
				
				// IF WE'RE PUBLISHING
				
				if ($direction == "publish") {
				
					// Calculate the expiry date
					// By default set it to 10 days after $available_date
					//$temp = new Date($available_date);
					//$temp->addSeconds(86400 * 10);
					//$expiry_date = $temp->format("%Y-%m-%d");
					
					// If scammer, with suppresed replies, set PUBLISHED to 0
					$query  = 'SELECT "X" FROM cf_users WHERE (suppressed_replies = 1 OR suppressed_replies = NULL) ';
					$query .= 'AND user_id = '.$_SESSION['u_id'];
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					$num_results = mysqli_num_rows($result); 
					if ($num_results == 1) { $published = 0; } else { $published = DEFAULT_PUBLISH_STATUS; };
					
					// If we're dealing with a new ad (i.e. "e" token is not present)
					// then set published to 1 and redirect
					$query = "
						UPDATE cf_wanted SET
							expiry_date = date_add('".$ad['available_date']."',interval 10 day),
							published = '".$published."'
						WHERE wanted_id = '".$id."'
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
					if ($result) {
						header("Location: details.php?post_type=wanted&id=".$id."&new_ad=1");
						exit;
					} else {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting wanted ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}
					
				}
				
				break;	
				
	
		} // End big $step switch
		
		// If we don't have any errors, proceed to next or previous step
		if (!$error && !$update_error) {
		
		
			// Proceed to the necessary step
			if ($direction == "next_step") {
				$step = $step + 1;
				if ($step > 6) { $step = 6; }
			}
			if ($direction == "prev_step") {
				$step = $step - 1;
				if ($step < 1) { $step = 1; }
			}
			
			// If we're redirecting to a specific step
			if ($step_request) { $step = $step_request; }
			
			// STEP 4 EXCEPTION:
			// If "whole place" only, we need to give step 4 a miss
			if ($whole_place && $step == 4) {
				if ($direction == "next_step") { $step = 5; }
				if ($direction == "prev_step") { $step = 3; }			
			}
			
			// Redirect: We'll either redirect to the next / previous step OR
			// if the "save changes" button was pressed ($direction == "save"), back to the
			// "Your ads"
			if ($direction == "save_changes") {
				header("Location: your-account-manage-posts.php");
				exit;
			}
		
			// At this stage we're ready to show next step
			// Redirect using $_GET to enable browser back + forward buttons
			header("Location: ".$_SERVER['PHP_SELF']."?id=".$id."&step=".$step."#m");
			exit;
		
		}		
	
	} else {
	
		// ********************************************************************************
		// Load data from database
		// When displaying each step, we need to load existing data
		// ********************************************************************************
		$debug .= debugEvent('AD data:',print_r($ad,true));
		// We'll iterate through the $ad variable and create the necessary variables 
		// which will be automatically picked up by our form
		foreach($ad as $k => $v) {
		
			// Cerntain data from the ad are not needed
			if (
				$k != "wanted_id" &&
				$k != "user_id" &&
				$k != "created_date" &&
				$k != "last_updated_date" &&
				$k != "published" &&
				$k != "times_viewed" &&
				$k != "paid_for" &&
				$k != "approved" &&
				$k != "suspended" &&
				$k != "recommendations"
			) {
                if ($v !== NULL) {
                    ${$k} = $v;
                }
                
                if ($k == 'flatmatch' && $v === NULL) {
                    ${$k} = 1;
                }
                
                if ($k == 'palup' && $v === NULL) {
                    ${$k} = 1;
                }
                
                if ($k == 'distance_from_postcode' && $v == 0) {
                  ${$k} = 5;
                }
			}	
		
		}
		// A few quick mods where column names DON'T match field names
		// #################################################
		
	}

	// If we're on step 5, we need to create the list of picture thumbnails
	if ($step == 5) {
	
		$displayPicCanvas = "";
		$pics = parseDirectory("images/pictures");
		shuffle($pics);
	
		// Load the pic filename -> title details from db into the custom_titles array
		$custom_titles = array();
		$query = "SELECT filename,display_title FROM cf_picture_titles WHERE display_title != '';";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				$custom_titles[$row['filename']] = $row['display_title'];
			}
		}
		
		foreach($pics as $pic) {
	
			// Get the name of the file (replace hyphens with spaces, uppercase words)
			$name = removeExtension(ucwords(preg_replace('/-/',' ',$pic)));
			// Substitute with custom title if needed
			if (isset($custom_titles[$pic])) { $name = $custom_titles[$pic]; }		
			$displayPicCanvas .= '<div class="displayPicContainer">'."\n";
			$displayPicCanvas .= '<a href="#" onclick="javascript: $(\'picture_'.$pic.'\').checked = true; return false;">';
			$displayPicCanvas .= '<div class="thumbnailContainer"><img src="images/pictures/'.urldecode($pic).'" border="0"/></div>';
			$displayPicCanvas .= '</a>'."\n";
			$displayPicCanvas .= '<p><input type="radio" name="picture" id="picture_'.$pic.'" value="'.$pic.'" ';
			if ($picture == $pic) { $displayPicCanvas .= 'checked="checked"'; }
			$displayPicCanvas .= '/>'.$name.'</p>'."\n";
			$displayPicCanvas .= '</div>'."\n";
	
		}
		
	}
	
	// If we're on step 6, we need to load all photos for this ad
	if ($step == 6) {
		$query = "select * from cf_photos where ad_id = '".$id."' and post_type = 'wanted' order by photo_sort asc;";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$photoCount = mysqli_num_rows($result);
		if (!$photoCount) {
			$photos = '<p>You have not added any photographs yet</p>';
		} else {
			$photos  = "\n\n";
			$photos .= '<p class="mt0">You have added '.mysqli_num_rows($result);
			$photos .= (mysqli_num_rows($result)!=1)? ' photos' : ' photo';
			$photos .= ' to this advert. Use the buttons below to update captions and rotate or delete photos.<br />Click on a photo below to see it enlarged with your caption.</p> ';
			while($row = mysqli_fetch_assoc($result)) {
	
				// The image must have a max height of 90px and must fit on a 120 * 90 area
				list($w,$h) = getImgRatio("images/photos/".$row['photo_filename'],"",90,120,90);
				$photos .= '<div class="uploadPhotoContainer" id="photo_'.$row['photo_id'].'">'."\n";
				$photos .= '<a href="images/photos/'.$row['photo_filename'].'" rel="lightbox[photos]" '.($row['caption']? 'title="'.$row['caption'].'"':'').'>'."\n";
				$photos .= '<img src="thumbnailer.php?img=images/photos/'.$row['photo_filename'].'&w='.$w.'&h='.$h.'&rnd='.rand(1,100000).'" border="0"/>';
				$photos .= '</a>';
	
				// The caption box
				if ($row['caption']) { $tempValue = $row['caption']; } else { $tempValue = "(enter caption)"; }
				$photos .= '<div class="uploadPhotoCaption"><input type="text" name="ad_caption_'.$row['photo_id'].'" id="ad_caption_'.$row['photo_id'].'" value="'.$tempValue.'" maxlength="120" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);" /></div>'."\n";
	
				// The selection checkbox
				$photos .= '<div><input type="checkbox" name="ad[]" value="'.$row['photo_id'].'" onclick="return toggle(this);"/></div>'."\n";
				$photos .= '</div>'."\n";	
			}
			$photos .= '<div class="clear" style="height:0px;"><!----></div>'."\n";
			$photos .= '<p class="m0">';
			$photos .= '<input type="submit" name="photo_update_captions" value="Save all captions" />&nbsp;';
			$photos .= '<br/></p><br/>Select a photograph from above and use the buttons below to rotate it 90o or delete it:';
//			$photos .= '<input type="submit" name="photo_delete" value="Delete photo(s)" onclick="return validateDeletion();" />';
			$photos .= '</p>'."\n";
		}
	}	
	
	// Format error
	if ($error) { array_walk($error,'formatError'); }
	
	$debug .= debugEvent('Current step:',$step);
	$debug .= debugEvent('Session vars:',print_r($_SESSION,true));
	
	// First time creation? Or editing existing ad?
	// If ad['published'] == 2 it means we are dealing with an
	// ad that's newly created, in which case we'll disable the 
	// tab navigation (jumping steps etc.) Alternatively, (published == 0 || 1)
	// we assume that user is "editing" an existing ad so we'll allow navigation
	$tab_links = array();
	for ($i = 1; $i <= 6; $i++) {
    	$tab_links[$i] = ($ad['published'] == 2)? '#' : $_SERVER['PHP_SELF'].'?id='.$id.'&step='.$i;	
	}
	$debug .= debugEvent("tablinks:",print_r($tab_links,true));
	
    // Setup Rotue
    if (isset($street) && !empty($street)) {
        $address = $street . ', ' . $area . ', ' . $region;
    }
    else if (isset($street_name) && !empty($street_name)) {
        $address = $street_name . ', ' . $town . ' (' . $postcode . ')';
    }
    else {
        $address = '-';
    }
    
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'> 

<title>Wanted Accommodation ad - Christian Flatshare</title>

<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css?v=2" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />

<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/scripts/jquery.nouislider.min.js?v=2"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>

<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
<script language="javascript" type="text/javascript" src="includes/icons.js"></script>

<!-- GOOGLE MAPS API v3  -->
<script src="https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
<!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script> -->
<script src="scripts/chooser.js?v=3"></script>

<!-- InstanceBeginEditable name="head" -->
<script language="javascript" type="text/javascript">

	<?php 
		
		if ($error) { 
	
			// If validation has failed we want the dialog box to appear
			echo 'var unsaved_changes = true;';
			
		} else {
		
			// By default we assume that no unsaved changes exist
			echo 'var unsaved_changes = false;';
			
		}
	
	?>
	var ignore_url = false;
	var step_request = "";

	window.addEvent('domready',function() {
		
		//tip_pricing
		//<p><strong>Pricing</strong></p>
		var myTips = new Tips('.tooltip');
		
		if ($('tip_bedrooms_required')) {
			$('tip_bedrooms_required').store('tip:title', 'Bedrooms required');
			$('tip_bedrooms_required').store('tip:text', 'Please choose the number of rooms you require to rent.');
		}
		
		if ($('tip_price_pcm')) {
			$('tip_price_pcm').store('tip:title', 'Price per Bedroom');
			$('tip_price_pcm').store('tip:text', 'Please enter the maximum price per bedroom for the accommodation you would.<br /><br />For \"Whole Place\" ads, specifying <strong>3 bedrooms</strong> and <strong>&pound;300 per bedroom</strong><br />would indicate that you are looking to pay up to <strong>&pound;900 per month</strong> for a whole place.<br />The Whole Place price is show below.');
		}		

		if ($('tip_church_attended')) {
			$('tip_church_attended').store('tip:title', 'Church attended');
			$('tip_church_attended').store('tip:text', 'If moving to a new area it can be of interest for others to see your<br />home church (or most recently attended), or just &quot;looking for a church&quot;<br />if you are looking to make a church connection for the first time.<br /><br />It is helpful to put the name of a church and its location, as there can<br />be many churches of the same name.');
		}				
		
		if ($('tip_professional')) {
			$('tip_professional').store('tip:title', 'Occupation');
			$('tip_professional').store('tip:text', 'We use &quot;professional&quot; to indicate that someone is at the stage of life when they are<br /> working in some capacity (butler, butcher, or banker), or looking for work. It can also<br /> describe someone who is retired. <br /><br />Please use your advert\'s text boxes to add more details accommodation seeker(s).');
		}						

               if ($('tip_sliders')) {
                        $('tip_sliders').store('tip:title', 'Sliders');
                        $('tip_sliders').store('tip:text', 'Click on the sliders to move them to indicate the age range. <br /><br /><b>Note</b> - if both sliders are on the same value, one will be on top of the other. If you have difficulty<br /> moving the       sliders at this point, please try moving the slider shown in one direction a few times.');
                }

		
		
		// Preload all necessary buttons	
		var myImages = new Asset.images([
			'/images/buttons/form_button_next_over.gif',
			'/images/buttons/form_button_previous_over.gif',
			'/images/buttons/form_button_publish_over.gif',
			'/images/buttons/form_button_save_changes_over.gif'],{ 
			
			onComplete: function(){
			
				if ($('button_next_step')) {
					$('button_next_step').addEvent('mouseenter',function() {
						this.src = '/images/buttons/form_button_next_over.gif';
					});
					$('button_next_step').addEvent('mouseleave',function() {
						this.src = '/images/buttons/form_button_next.gif';
					});
				}
				
				if ($('button_prev_step')) {
					$('button_prev_step').addEvent('mouseenter',function() {
						this.src = '/images/buttons/form_button_back_over.gif';
					});
					$('button_prev_step').addEvent('mouseleave',function() {
						this.src = '/images/buttons/form_button_back.gif';
					});
				}
				
				if ($('button_publish')) {
					$('button_publish').addEvent('mouseenter',function() {
						this.src = '/images/buttons/form_button_publish_over.gif';
					});
					$('button_publish').addEvent('mouseleave',function() {
						this.src = '/images/buttons/form_button_publish.gif';
					});				
				}
				if ($('button_save_changes')) {
					$('button_save_changes').addEvent('mouseenter',function() {
						this.src = '/images/buttons/form_button_save_changes_over.gif';
					});
					$('button_save_changes').addEvent('mouseleave',function() {
						this.src = '/images/buttons/form_button_save_changes.gif';
					});	
				}
			
			}
			
		});
		
		
		<?php if ($ad['published'] != 2) { ?>
	
		// Monitor all text, radio and checkboxes
		$$('#mainContent input').each(function(i){
			if (i.type == "text") {
				i.addEvent('keydown',function(){ unsaved_changes = true; });
			} else if (i.type == "checkbox" || i.type == "radio") {
				i.addEvent('change',function(){ unsaved_changes = true; });
			}
		});
		
		// Monitor all dropdowns
		$$('#mainContent select').each(function(i){
			i.addEvent('change',function(){ unsaved_changes = true; });
		});
		
		// Monitor all textareas
		$$('#mainContent textarea').each(function(i){
			i.addEvent('keydown',function(){ unsaved_changes = true; });
		});
			
		if ($('tablist')) {
		
			// Get all links inside tablist
			$$('#tablist a').each(function(a){
				// Handle the mouseclick
				a.addEvent('click',function(e) {
					
					if (unsaved_changes) {
						
						e.stop();
						ignore_url =  this.href;
						step_request = this.id;
						var pos = window.getScrollTop() + (window.getHeight() / 2);
						$('dialog_box').setStyle('top',pos);
						$('dialog_box').setStyle('display','');
					}
				
				});
			});
		
		}
	
		<?php } ?>
		
	});
	
	function submitDialog() {
	
		$('step_request').value = step_request.substr(3,1);
		$('wanted_form').submit();
	
	}
	
	function showCancelBox() {
		
		// Center box vertically (for large scrolling pages)
		var pos = window.getScrollTop() + (window.getHeight() / 2);
		$('cancel_box').setStyle('top',pos);
		// Show cancel box
		$('cancel_box').setStyle('display','');
		// Return false to avoid page jumping (from the link)
		return false;
	
	}
	
	<?php if ($step == 6) { ?>
	
	function fieldFocus(id) {
		if ($(id).value == "(enter caption)") {
			$(id).value = "";
		}
	}
	
	function fieldBlur(id) {
		$(id).value = $(id).value.trim();
		if ($(id).value == "") {
			$(id).value = "(enter caption)";
		}
	}
	
	function validateDeletion() {
		// Iterate through all the checkboxes of the deletionForm
		var x = $('photo_canvas').getElementsByTagName("input");
		var proceed = false;
		for (var i=0;i<x.length;i++) {
			// If at least one checkbox is checked, proceed
			if (x[i].type == "checkbox" && x[i].checked) {
				proceed = true;
			}
		}
		if (!proceed) {
			alert("Please select at least one photo to delete");
			return false;
		} else {
			return confirm("Proceed with deletion?");
		}
	}
	
	function toggle(obj) {
		var id = obj.value;
		if (obj.checked) {
			$('photo_'+id).className = "uploadPhotoContainer selected";
		} else {
			$('photo_'+id).className = "uploadPhotoContainer";
		}
		return true;
	}
	
	function doRotate(direction) {
		if (!direction) { return false; }
		// Iterate through all the checkboxes, ensure only one is checked
		var x = $('photo_canvas').getElementsByTagName("input");
		var selectedNumber = 0;
		var selectedCheckbox = null;
		for (var i=0;i<x.length;i++) {
			// If at least one checkbox is checked, proceed
			if (x[i].type == "checkbox" && x[i].checked) {
				selectedNumber++;
				selectedCheckbox = x[i];
			}
		}
		if (!selectedNumber) {
			alert("Please select one photograph to rotate");
		} else if (selectedNumber > 1) {
			alert("Please ensure you have selected only one photograph to rotate");
		} else {
			window.location = 'your-account-rotate-photo.php?type=wanted&ad_id=<?php print $id?>&photo_id='+selectedCheckbox.value+'&direction='+direction;
		}
	}
	
	
	<?php } ?>

</script>
<script language="javascript" type="text/javascript" src="includes/slimbox-new/slimbox.js"></script>
<link href="includes/slimbox-new/slimbox.css" rel="stylesheet" type="text/css" />

<style type="text/css">
<!--
.style1 {font-weight: bold}
-->
</style>
<link href="styles/dialog_box.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			<h1 class="mb0">Wanted Accommodation ad</h1>
			<div id="dialog_box" class="dialog_box" style="display:none;">
			<div class="dialog_canvas">
				<h1 class="m0">Unsaved Changes</h1>
				<div class="dialog_text">
					<p class="mt0">Some of the changes you made <strong>have not yet been saved</strong>.<br/>
					Navigating away from this page will loose those.</p>
					<p class="mb0">Would you like to save those changes now?</p>
				</div>
				<div class="dialog_buttons">
				<table cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="padding-right:10px;">
							<input name="dialog_save" id="dialog_save" type="button" value="Save changes" onclick="javascript:submitDialog();" />&nbsp;&nbsp;
							<input name="dialog_cancel" id="dialog_cancel" type="button" value="Cancel" onclick="javascript:$('dialog_box').setStyle('display','none');" />
						</td>
						<td align="right"><input name="dialog_ignore" id="dialog_ignore" type="button" value="Ignore changes" onclick="javascript:location.href=ignore_url;" /></td>
					</tr>
				</table>
				</div>
			</div>
			</div>
			
			<div id="cancel_box" class="dialog_box" style="display:none;">
			<div class="dialog_canvas">
				<h1 class="m0">Cancel ad editing?</h1>
				<div class="dialog_text">
					<p class="mt0">Are you sure you want to navigate away from this page?</p>
					<p class="mb0"><strong>Your unsaved changes will be lost</strong></p>
				</div>
				<div class="dialog_buttons">
				<table cellpadding="0" cellspacing="0" align="center">
					<tr>
						<td style="padding-right:10px;">
							<input type="button" value="OK" onclick="location.href='your-account-manage-posts.php';" style="width:120px;"/>&nbsp;&nbsp;
							<input type="button" value="No, stay here" onclick="javascript:$('cancel_box').setStyle('display','none');"  style="width:120px;"/>
						</td>
					</tr>
				</table>
				</div>
			</div>
			</div>
			
			<noscript>
				<div class="no_js_warning">				
					<h1><img src="images/no_js_error.gif" alt="" width="48" height="48" />Warning: Javascript is turned off in your browser!</h1>
					<p>Certain functionality of CFS requires Javascript to be turned on<br/>
					Instructions for <a href="http://support.microsoft.com/gp/howtoscript" target="_blank">Internet Explorer</a>&nbsp;|&nbsp;<a href="http://support.mozilla.com/en-US/kb/JavaScript#_Enabling_and_disabling_JavaScript" target="_blank">Mozilla Firefox</a>&nbsp;|&nbsp;<a href="http://www.opera.com/support/search/view/657/" target="_blank">Opera</a>&nbsp;|&nbsp;<a href="http://www.apple.com/safari/" target="_blank">Safari</a></p>
				</div>
			</noscript>
			<?php	if ($ad['published'] == 2) { ?> 
				<br />Once published you can change your advert at anytime.
			<?php } else { ?>
				<p class="mb0 mt0">&nbsp;</p>				
			<?php } ?>	
			
			<?php if ($error) { ?><p class="error mb0 mt0">Some problems were found. Please review the messages in red.</p><?php } ?>
			<?php print $update_error?>
			<ul id="tablist" <?php if ($ad['published'] == 2) { ?>class="tablist_linking_disabled"<?php } ?>>
				<li><a href="<?php print $tab_links[1]?>" id="tab1link" class="<?php print ($step == 1)? 'current':''?>">Location&nbsp;and&nbsp;Dates</a></li>
				<li><a href="<?php print $tab_links[2]?>" id="tab2link" class="<?php print ($step == 2)? 'current':''?>">Accommodation&nbsp;Wanted</a></li>
				<li><a href="<?php print $tab_links[3]?>" id="tab3link" class="<?php print ($step == 3)? 'current':''?>">Accommodation&nbsp;Seeker(s)</a></li>
				<?php if (!$whole_place) { ?>
				<li><a href="<?php print $tab_links[4]?>" id="tab4link" class="<?php print ($step == 4)? 'current':''?>">Preferred&nbsp;Household</a></li>
				<?php } ?>
				<li><a href="<?php print $tab_links[5]?>" id="tab5link" class="<?php print ($step == 5)? 'current':''?>">Contact&nbsp;Details&nbsp;&amp;&nbsp;Advert&nbsp;Picture</a></li>
				<li><a href="<?php print $tab_links[6]?>" id="tab6link" class="<?php print ($step == 6)? 'current':''?>">Add&nbsp;Photos</a></li>
			</ul>
			
			<form name="wanted_form" id="wanted_form" action="<?php print $_SERVER['PHP_SELF']?>" method="post" <?php if ($step == 6) { ?>enctype="multipart/form-data"<?php } ?>>
			<input type="hidden" name="id" value="<?php print $id?>" />
			<input type="hidden" name="step" value="<?php print $step?>" />
			<input type="hidden" name="step_request" id="step_request" value="" />

			<div class="tab">
				
				<?php if ($step == 1) { ?>
				
				<!-- STEP 1 : LOCATION AND DATES -->
				<script language="javascript" type="text/javascript" src="includes/wanted-ad-address-picker.js"></script>
				<h2 class="formHeader m0">Tell us where and when you'd like accommodation</h2>
				<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
					<tr>
						<td width="200" align="right">Country:</td>
						<td><span style="font-weight: bold;"><?php print $appCountry['name']; ?></span> (<a href="/countries.php?from=<?php print $_SERVER['PHP_SELF']?>?id=<?php print $id; ?>">Change</a>)</td>
					</tr>
					<tr>
						<td width="200" align="right" valign="top"> <p style="margin:0px;line-height:26px;"><span class="obligatory">*</span> Location of accommodation:</p></td>
						<td>
                            <input type="hidden" name="lookup_type" id="lookup_type" value="geo" />
                            <input type="hidden" name="country" id="postcodeChooserCountry" value="<?php print $appCountry['iso']; ?>" />
                            <input name="postcode" type="text" id="postcodeChooser" />
                            <div id="locationChooser" style="width:500px;height:300px;display:none;"></div>
                            <div id="addressExtra"><span>Drag circle/marker to update location</span><a href="" id="resetChooser">Enter a new location</a></div>
            				<input type="hidden" name="longitude" id="longitude" value="<?php print $longitude; ?>" />
            				<input type="hidden" name="latitude" id="latitude" value="<?php print $latitude; ?>" />
                            <input type="hidden" name="street" id="route" value="<?php print $street; ?>" />
                            <input type="hidden" name="area" id="locality" value="<?php print $area; ?>" />
                            <input type="hidden" name="region" id="admin_level" value="<?php print $region; ?>" />
                            <input type="hidden" name="country" id="country" value="<?php print $country; ?>" />
                            <input type="hidden" name="postal_code" id="postal_code" value="<?php print $postal_code; ?>" />
                            <!-- FOR GB -->
                            <input type="hidden" name="street_name" id="route" value="<?php print $street_name; ?>" />
                            <input type="hidden" name="town" id="locality" value="<?php print $town; ?>" />
						</td>
					</tr>

					<tr>
						<td width="200" align="right">Location: </td>
						<td><span id="addressName" style="font-weight: bold;"><?php print (isset($error['geo'])) ? $address.' '.$error['geo'] : $address ; ?></span></td>
					</tr>
					<tr>
						<td width="200" align="right">Max distance from location:</td>
						<td><?php print createDropDown("distance_from_postcode", getMilesArray(true), $distance_from_postcode); ?><?php print $error['distance_from_postcode']?></td>
					</tr>
					<tr>
						<td align="right">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span> Required from:</td>
						<td><?php print createDateDropDown("available_date",300,$available_date,TRUE,"dateSelector")?><?php print $error['available_date']?></td>
					</tr>
					<tr>
						<td align="right">Minimum term:</td>
						<td><?php print createDropDown("min_term",getTermsArray("minimum"),$min_term);?>&nbsp;<span class="grey style2">length of stay</span></td>
					</tr>
					<tr>
						<td align="right">Maximum term:</td>
						<td><?php print createDropDown("max_term",getTermsArray("maximum"),$max_term);?>&nbsp;<span class="grey style2">length of stay (3 months or less is described &quot;short-term&quot;)</span><?php print $error['term']?></td>
					</tr>
				</table>
								
				
				<?php } ?>	
				
				<?php if ($step == 2) { ?>
				
				<!-- STEP 2 : Accommodation details -->
				<h2 class="formHeader m0 mb5">Tell us the accommodation type(s) you're interested in and its particulars</h2>
				<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
					<tr>
						<td width="200" align="right" valign="top"><span class="obligatory">*</span> Accommodation type could be:</td>
						<td>
							
							<table cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td><?php print createCheckbox("accommodation_type_flat_share","1",$accommodation_type_flat_share);?></td>
									<td><label for="accommodation_type_flat_share">House / Flatshare  <span class="grey"> - a house or flat shared with others</span></label></td>
									<td><?php print $error['accommodation_type']?></td>
								</tr>
								<tr>
									<td><?php print createCheckbox("accommodation_type_room_share","1",$accommodation_type_room_share);?></td>
									<td><label for="accommodation_type_room_share">Room Share <span class="grey"> - a double room shared with same sex</span></label></td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td><?php print createCheckbox("accommodation_type_family_share","1",$accommodation_type_family_share);?></td>
									<td><label for="accommodation_type_family_share">Family Share  <span class="grey"> - live with a family or a married couple</span></label></td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td><?php print createCheckbox("accommodation_type_whole_place","1",$accommodation_type_whole_place);?></td>
									<td><label for="accommodation_type_whole_place">Whole Place <span class="grey"> - an unoccupied house or flat</span></label></td>
									<td>&nbsp;</td>
								</tr>
							</table>
						</td>				
					</tr>
					<tr>
						<td width="200" align="right" valign="top"><span class="obligatory">*</span> Building type could be:</td>
						<td>
							<table cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td><?php print createCheckbox("building_type_house","1",$building_type_house);?></td>
									<td><label for="building_type_house">House</label></td>
								</tr>
								<tr>
									<td><?php print createCheckbox("building_type_flat","1",$building_type_flat);?></td>
									<td><label for="building_type_flat">Flat</label></td>
								</tr>
									<td><?php print $error['building_type']?></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span> Number of bedrooms required:</td>
						<td><?php print createDropDown("bedrooms_required",getBedroomArray(false, "", " bedroom required", " bedrooms required"),$bedrooms_required)?>
							<strong><a href="#" class="tooltip" id="tip_bedrooms_required">(?)</a></strong>
						</td>
					</tr>
					<tr>
						<td width="200" align="right"><span class="obligatory">*</span> MONTHLY price:</td>
						<td>
							<?php print $appCountry['currency_symbol']; ?>&nbsp;<input name="price_pcm" type="text" id="price_pcm" value="<?php if ($price_pcm < 1) { echo "0"; } else { echo $price_pcm; } ?>" size="5" onchange="update_whole_place_price()" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/>					  
							&nbsp;per bedroom (max)&nbsp;<strong><a href="#" class="tooltip" id="tip_price_pcm">(?)</a></strong> <?php print $error['price_pcm']?>							
							</td>
					</tr>	
					<tr>
						<td id="whole_place_equiv" align="right"  style="display:none;">Whole Place price:</td>
						<td><span id="whole_place_equiv_pound_sign" style="display:none;"><?php print $appCountry['currency_symbol']; ?> </span><strong><span id="whole_place_equiv_price" style="display:none;">Enter the price per bedroom.</span></strong>	</td>
					</tr>							
										
					<tr>
						<td align="right">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>							
					<tr>
						<td align="right">Furnishing:</td>
						<td><?php print createDropDown("furnished",getFurnishedArray(),$furnished);?></td>
					</tr>
					<tr>
						<td width="200" align="right" valign="top">The accommodation <u>must</u> have:</td>
						<td>
							<table cellpadding="0" cellspacing="0" id="mod_cons">
								<tr>
									<td width="10"><?php print createCheckbox("shared_lounge_area","1",$shared_lounge_area);?></td><td width="180"><label for="shared_lounge_area">a shared lounge area</label></td>
									<td><?php print createCheckbox("washing_machine","1",$washing_machine);?></td><td><label for="washing_machine">a washing machine</label></td>									
								</tr>
								<tr>
			 			 <!-- <td><?php print createCheckbox("central_heating","1",$central_heating);?><label for="central_heating">central heating</label></td> -->
									<td><?php print createCheckbox("ensuite_bathroom","1",$ensuite_bathroom);?></td><td><label for="ensuite_bathroom">an ensuite bathroom</label></td>						 
									<td><?php print createCheckbox("dish_washer","1",$dish_washer);?></td><td><label for="dish_washer">a dish washer</label></td>						 
								</tr>
								<tr>
									<td><?php print createCheckbox("garden_or_terrace","1",$garden_or_terrace);?></td><td><label for="garden_or_terrace">a garden / roof terrace</label></td>									
									<td><?php print createCheckbox("tumble_dryer","1",$tumble_dryer);?></td><td><label for="tumble_dryer">a tumble dryer</label></td>									
								</tr>
								<tr>
									<td><?php print createCheckbox("bicycle_store","1",$bicycle_store);?></td><td><label for="bicycle_store">somewhere to store a bicycle</label></td>
									<td><?php print createCheckbox("parking","1",$parking);?></td><td><label for="parking">somewhere nearby to park a car</label></td>
								</tr>
								<tr>

									<td>&nbsp;</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>		
				
						<script language="javascript" type="text/javascript">
						
						window.addEvent("domready",function(){
						
							$('accommodation_type_whole_place').addEvent('click',check_whole_place);
							check_whole_place();
							
							$('bedrooms_required').addEvent('change',update_whole_place_price);
							update_whole_place_price();

						});
						
						function check_whole_place() {
								
							var whole_place = $('accommodation_type_whole_place').checked;
								
							if (whole_place) {
								$('whole_place_equiv').setStyle('display','')																
								$('whole_place_equiv_price').setStyle('display','')
								$('whole_place_equiv_pound_sign').setStyle('display','')	
								 update_whole_place_price();							
							} else {
								$('whole_place_equiv').setStyle('display','none')																
								$('whole_place_equiv_price').setStyle('display','none')
								$('whole_place_equiv_pound_sign').setStyle('display','none')								
							}
								
						}					


						function update_whole_place_price() {
								
							var bedroom_price = document.getElementById("price_pcm").value;								
							var number_of_rooms = parseInt($('bedrooms_required').getSelected()[0].value);								
							var whole_place_price = Math.floor(number_of_rooms * bedroom_price);

							if ($('accommodation_type_whole_place').checked) {
								if ( whole_place_price == 0 ) {
									$('whole_place_equiv_price').set('text','Please enter the amount per bedroom');		
									$('whole_place_equiv_pound_sign').setStyle('display','none')														
								} else 
								if (isNaN(whole_place_price) == true ) {			
									$('whole_place_equiv_price').set('text','You have not entered a number');
									$('whole_place_equiv_pound_sign').setStyle('display','none')											
								} else {							
									$('whole_place_equiv_price').set('text',whole_place_price);
									$('whole_place_equiv_pound_sign').setStyle('display','')											
								}
								$('whole_place_equiv_price').setStyle('display','none')
								$('whole_place_equiv_price').setStyle('display','')
							}
						}					
						
						function fieldFocus(id) {
							if ($(id).value == "0") {
								$(id).value = "";
							}
						}
						
						function fieldBlur(id) {
							$(id).value = $(id).value.trim();
							if (!isNaN($(id).value)) {
								$(id).value = Math.floor($(id).value.trim());
							}
							
							if ($(id).value == "") {
								$(id).value = "0";
								update_whole_place_price();
							}
						}

					</script>						
						
				
				<?php } ?>
				
				<?php if ($step == 3) { ?>
					
				
				<h2 class="formHeader m0">Describe the accommodation seeker(s)</h2>
				<p class="style1 m0 mb5 formSubHeader"><strong>(the people looking for accommodation)</strong></p>			
				
				<table width="100%"  border="0" cellpadding="0" cellspacing="10" class="noBorder">
					<tr>
						<td width="220" align="right"><span class="obligatory">*</span> Number of males seeking accommodation:</td>
						<td><?php print createDropDown("current_num_males",array("0"=>"None","1"=>"1 male","2"=>"2 males","3"=>"3 males","4"=>"4 males","5+"=>"5+  males"),$current_num_males,'','width:80px;','')?><?php print $error['current_num_males']?>
						</td>
					</tr>
					<tr>
						<td width="220" align="right"><span class="obligatory">*</span> Number of females seeking accommodation:</td>
						<td><?php print createDropDown("current_num_females",array("0"=>"None","1"=>"1 female","2"=>"2 females","3"=>"3 females","4"=>"4 females","5+"=>"5+ females"),$current_num_females,'','width:90px;','')?></td>
					</tr>
					
					</table>
					<table width="100%" border="0" cellpadding="0" cellspacing="10" class="noBorder"id="age_ranges">
					<tr>
						<td width="200" align="right"><strong><a href="#" class="tooltip" id="tip_sliders">(?)</a></strong> <span class="obligatory">*</span> Age range of accommodation seekers:<br /><span class="grey">move both sliders to show age range</span></td>
						<td>
                            <div class="age-preview-container">Will show as: <span class="age-preview"><?php print $currentAgeRangePreview; ?></span></div>
                            <div class="age-container current">
                                <div class="age-slider noUiSlider"></div>
                            </div>
                            <ul class="ages">
                                <li>18-20</li>
                                <li>21-25</li>
                                <li>26-30</li>
                                <li>31-35</li>
                                <li>36-40</li>
                                <li>41-45</li>
                                <li>46-50</li>
                                <li>51+</li>
                            </ul>
                            <input type="hidden" name="currentAgeRange" id="ageRange" value="<?php print $currentAgeRange; ?>" />
                        </td>
					</tr>
                    
					</table>	
					<table width="100%"  border="0" cellpadding="0" cellspacing="10" class="noBorder">	
					<tr>
						<td width="220" align="right"><span class="obligatory">*</span> Occupation:</td>
						<td><?php print createRadioGroup("current_occupation",getOccupationArray(),$current_occupation)?> <strong><a href="#" class="tooltip" id="tip_professional">(?)</a></strong><?php print $error['current_occupation']?></td>
					</tr>

					<tr>
						<td width="220" align="right"><label for="current_is_couple">Are you a married couple:</label></td>
						<td><?php print createCheckbox("current_is_couple","1",$current_is_couple);?></td>
					</tr>
					<tr>
						<td width="220" align="right"><label for="current_is_family">Are you family with children:</label></td>
						<td><?php print createCheckbox("current_is_family","1",$current_is_family);?></td>
					</tr>
					<tr>
						<td align="right">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>					
	
					<tr>
						<td width="220" align="right"><span class="obligatory">*</span> Church attended:</td>
						<td>
							<?php print $error['church_attended']?>
							<input type="text" name="church_attended" id="church_attended" value="<?php print stripslashes($church_attended)?>" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/>
							<strong><a href="#" class="tooltip" id="tip_church_attended">(?)</a></strong>
							<span class="grey style2">&quot;St John's church, York &quot; / &quot;Looking for a church &quot;</span>						</td>
					</tr>
					<tr>
						<td width="220" align="right">Church website: </td>
						<td>
							<input type="text" name="church_url" id="church_url" value="<?php print stripslashes($church_url); ?>" />						</td>
					</tr>
					<tr>
						<td width="220" align="right"><label for="church_reference">Could provide a recommendation <br />from a church if asked:</label></td>
						<td>
							<?php print createCheckbox("church_reference","1",$church_reference);?>
							<span class="grey style2"><label for="church_reference">simply to say that you are known to a church fellowship who could in someway vouch for your character</label></span>						</td>
					</tr>				
				</table>
			<table width="100%"  border="0" cellpadding="0" cellspacing="10" class="noBorder">					
					<tr>
						<td>
						<p class="mb0 mt0 grey" style="font-size:12px"><span class="obligatory">*</span> <strong>More about the Accommodation Seeker(s)...</strong></p>
						<?php print $error['accommodation_situation']?>
						<textarea name="accommodation_situation" rows="13" id="accommodation_situation" style="width:100%;padding:3px; font-size:12px;"><?php print stripslashes(trim($accommodation_situation))?></textarea>
						<div><strong id="char_count">0</strong> characters entered. <span class="grey">Minimum: 150 characters. Recommended: 300+ characters.</span></div>											
						</td>
						<td width="220" align="right" valign="top" style="padding-top:25px">
						  <div><img src="images/tag_cloud_seeker.gif" width="218" height="185" /></div>						
						</td>						
					</tr>		
				</table>
				<script language="javascript" type="text/javascript">
								
					window.addEvent("domready",function(){ 
								
						$('accommodation_situation').addEvent('keydown',function(e){
										
								var count = $('accommodation_situation').value.length;
										
									if (count > 5000) {
										$('accommodation_situation').value = $('accommodation_situation').value.substring(0,5000); 
									} else {
										updateCounter();
									}
								});			
									
								function updateCounter() {
									
									var count = $('accommodation_situation').value.trim().length;
									$('char_count').set('text',count);	

										if (count > 150) {
											$('char_count').setStyle('color','#009900');
										} else {
											$('char_count').setStyle('color','#000000');
										}	
									
									}
									
									updateCounter();				
									
								});
								
							</script>
					
				<?php } ?>
				
				<?php if ($step == 4) { ?>
					
					<h2 class="formHeader m0">Tell us about the household you'd like to live with</h2>
					<!-- Change sub headings: if Whole Place is selected, in addition to other accomodation types, show: -->
					<?php if ($ad['accommodation_type_whole_place'] == 1) { ?>
						<p class="style1 m0 formSubHeader"><strong>(these questions will not apply to the selected &quot;Whole Place&quot; accommodation type)</strong></p>
					<?php } else { ?>
					<!-- <p class="style1 m0 formSubHeader"><strong>(these questions apply only to Room, Flat or Family Share households)</strong></p>	-->
					<?php } ?>
					<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
						<tr>
							<td width="200" align="right" style="padding-top:10px">Maximum number of adult members:</td>
							<td style="padding-top:10px"><?php print createRadioGroup("shared_adult_members",array("1"=>"1","2"=>"2","3"=>"3","4"=>"Any number"),$shared_adult_members)?><?php print $error['shared_adult_members']?></td>
						</tr>
						<tr>
							<td width="200" align="right" valign="top"><span class="obligatory">*</span> Household could be: </td>
							<td>
								<table cellpadding="0" cellspacing="0" border="0" class="prTD5">
									<?php if ($error['shared_gender']) { ?>
									<tr>
										<td colspan="2"><?php print $error['shared_gender']?></td>
									</tr>							
									<?php } ?>
									<tr>	
										<td><?php print createCheckbox("shared_males","1",$shared_males)?></td>
										<td><label for="shared_males">All male</label></td>
									</tr>
									<tr>
										<td><?php print createCheckbox("shared_females","1",$shared_females)?></td>
										<td><label for="shared_females">All female</label></td>
									</tr>
									<tr>
										<td><?php print createCheckbox("shared_mixed","1",$shared_mixed)?></label>	</td>
										<td><label for="shared_mixed">Mixed household (male and female)</label></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td align="right">&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
                        

    					<tr>
    						<td width="200" align="right"><strong><a href="#" class="tooltip" id="tip_sliders">(?)</a></strong> <span class="obligatory">*</span> Age range of desired household:<br /><span class="grey">move both sliders to indicate range</span></td>

    						<td>
                                <div class="age-preview-container">Will show as: <span class="age-preview"><?php print $suitAgeRangePreview; ?></span></div>
                                <div class="age-container suit">
                                    <div class="age-slider noUiSlider"></div>
                                </div>
                                <ul class="ages">
                                    <li>18-20</li>
                                    <li>21-25</li>
                                    <li>26-30</li>
                                    <li>31-35</li>
                                    <li>36-40</li>
                                    <li>41-45</li>
                                    <li>46-50</li>
                                    <li>51+</li>
                                </ul>
                                <input type="hidden" name="suitAgeRange" id="ageRange" value="<?php print $suitAgeRange; ?>" />
                            </td>
    					</tr>
  
                    
						
						<tr>
							<td width="200" align="right" valign="top">It could comprise of:</td>
							<td>
								<?php print createCheckbox("shared_professional","1",$shared_professional);?><label for="shared_professional">Professionals</label><br />
								<?php print createCheckbox("shared_mature_student","1",$shared_mature_student);?><label for="shared_mature_student">Mature students</label><br />											
								<?php print createCheckbox("shared_student","1",$shared_student);?><label for="shared_student">Students (&lt;22yrs)</label>
							</td>
						</tr>
						<tr>
							<td width="200" align="right"><label for="shared_owner_lives_in">The owner could be a member of <br />
							the household:</label></td>
							<td><?php print createCheckbox("shared_owner_lives_in","1",$shared_owner_lives_in);?></td>
						</tr>
						<tr>
							<td width="200" align="right"><label for="shared_married_couple">It could have a married couple:</label></td>
							<td><?php print createCheckbox("shared_married_couple","1",$shared_married_couple);?></td>
						</tr>
						<tr>
							<td width="200" align="right"><label for="shared_family">It could be family that has children:</label></td>
							<td><?php print createCheckbox("shared_family","1",$shared_family);?></td>
						</tr>
					</table>					
				
				<?php } ?>	
				
				<?php if ($step == 5) { ?>
				
					<h2 class="formHeader m0">Your contact details</h2>
					<p class="formHeader m0">&nbsp;</p>
					<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
						<tr>
							<td align="right"><span class="obligatory">*</span> Contact name:</td>
							<td><input name="contact_name" type="text" id="contact_name" value="<?php print stripslashes($contact_name)?>"/>
							&nbsp;<span class="grey style4">e.g. &quot;John Smith&quot;</span>&nbsp;<?php print $error['contact_name']?></td>
						</tr>				
						<tr>
							<td align="right">Contact phone number:</td>
						  <td><input name="contact_phone" type="text" id="contact_phone" value="<?php print stripslashes($contact_phone); ?>"/>&nbsp;<span class="grey style4">optional</span></td>
						</tr>
					</table>
					<p><strong>CFS does not disclose your email address</strong><br />
					People responding can do so through a form which sends you an alert email, and sends their message to Your messages.<br /> 
					You may include your phone number and any additional contact details within your advert.</span></p>
					
					<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
						<tr>
							<td colspan="2"><h2 class="m0">Flat-Match</h2></td>
						</tr>
						<tr>
							<td width="20"><?php print createCheckbox("flatmatch",1,$flatmatch);?></td>
							<td><label for="flatmatch">Flat-Match  automatically emails you details of suitable new Wanted Accommodation ads, while your Wanted Accommodation ad<br/>remains published on Christian Flatshare.</label></td>
						</tr>
						<tr>
							<td colspan="2"><h2 class="m0">Pal-Up</h2></td>
						</tr>
						<tr>
							<td width="20"><?php print createCheckbox("palup","1",$palup);?></td>
							<td><label for="palup">
								Pal-up helps you to connect with others  looking for accommodation, to explore finding somewhere together. <br />
								Pal-up automatically emails you new Wanted Accommodation adverts, similar to yours, from those also willing to pal-up.
							</label>
							</td>
						</tr>
					</table>
					
					<p><span class="mb5 mt10"><strong>Choose a retro advert picture to describe your advert (just for fun) </strong></span> <?php print $error['displayPic']?></p>
				
					<div id="displayPicWrapper"><div id="displayPicCanvas"><?php print $displayPicCanvas?></div></div>				
				
				<?php } ?>	
				
				<?php if ($step == 6) { ?>
				
				<h2 class="formHeader m0">Add photos</h2>
				
				<?php if ($ad['current_num_males'] + $ad['current_num_females'] > 1) { $plural = 's'; } else { $plural = ''; } ?>
				<?php if ($photoCount <= 7) { ?>
					<p><strong>Adding photos will help you to get the best response from your advert!</strong><br />
				Photos help to introduce the accommodation seeker<?php print $plural?>, and they can be fun too ... maybe your holiday snaps??</p>
					<div id="uploadBack" style="width:550px">
						<p class="mt0">Use the form below to add a photos for this ad. You may add up to 8 photos. <br />
						(Max size 20MB, file types: JPEG)</p>
						<?php print $error['upload']?>
						<script language="javascript" type="text/javascript">
							
							function showLoader() {
							
								$('photo_loader').setStyle('display','');
								return true;
							
							}
						
						</script>
						<table border="0" cellspacing="0" cellpadding="0" class="prTD5">
							<tr>
							<td><input name="userfile" type="file" size="60" /></td>
							<td><input type="submit" name="submit" value="Upload photo" onclick="return showLoader();"/></td>
							<td><img src="images/photo-loader.gif" width="16" height="16" id="photo_loader" style="display:none;"/></td>
							</tr>
						</table>
					</div>
					<?php } else { ?>
						<p>You have added the maximum number of photos allowed.</p>
					<?php } ?>
					
					<?php if ($photoCount == 0 && $ad['published'] == 2) {
							echo "<br /><br /><strong>You can add photos later too, once your ad is published.</strong><br />";				
						} else { ?>
							<h2 class="mb2">Your advert photos</h2>													
							<div id="photo_canvas">
							<?php print $photos?>
							</div>							
					<?php } ?>					
					
					
					<?php if ($photoCount) { ?>
					<p><input type="submit" name="photo_delete" value="Delete photo(s)" onclick="return validateDeletion();" />&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="button" value="Rotate clockwise" onclick="doRotate('anticlockwise');"/>&nbsp;<input type="button" value="Rotate anti-clockwise" onclick="doRotate('clockwise');"/></p>
					<?php } ?>
				
				<?php } ?>		
				
				<table cellpadding="0" cellspacing="10" border="0" width="100%">
					<tr>
					<?php	if ($ad['published'] == 2) { ?> <!-- Don't show buttons if ad published  -->					
						<td width="77">
						<?php
							if ($step == 1) {
								// Show disabled back button
								echo '<img src="images/buttons/form_button_back_disabled.gif" title="Cannot go back from step 1" />';
							} else {
								echo '<input name="submit_prev_step" id="button_prev_step" type="image" value="prev_step" src="images/buttons/form_button_back.gif" />';
							}
						?>
						</td>
						<td>
						<?php
							if ($step == 6) {
								// New ad: "Publish button"
								if ($ad['published'] == "2") {
									echo '<input name="submit_publish" id="button_publish" type="image" value="publish" src="images/buttons/form_button_publish.gif" />';
								}								
							} else {
								echo '<input name="submit_next_step" id="button_next_step" type="image" value="next_step" src="images/buttons/form_button_next.gif" />';
							}
						} else { ?> <!--show if already published -->
							<td>
							<span class="grey"><strong>Click on the grey form tabs above to change parts of your ad,<br />e.g. click on the grey &quot;Accommodation Details&quot; tab.</strong></span></td>
						<?php } ?>							
						</td>
						
						
						<?php if ($ad['published'] != 2) { ?>
						<td align="right"><a href="#" onclick="return showCancelBox();"><img src="images/buttons/form_button_cancel.gif" name="button_cancel" border="0" id="button_cancel" /></a></td>
						<td align="right" width="117"><input name="submit_save_changes" id="button_save_changes" type="image" value="save_changes" src="images/buttons/form_button_save_changes.gif" /></td>
						<?php } ?>
					</tr>
				</table>
				
					
				
			</div>
			<div class="clear"><!----></div>
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="mt10 mb10">
				<tr>
					<td align="right" valign="top">
						Problems posting your ad?<br />
						If you require help, <a href="contact-us.php" target="_blank">please contact us.</a><br />
			<!--			 020 7183 2949 (9am-8pm, Mon-Sat)  -->
					</td>
				</tr>
			</table>
			
			</form>
			
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
