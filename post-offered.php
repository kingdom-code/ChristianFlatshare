<?php
    use CFS\Database\CFSDatabase;
    use CFS\Image\CFSImage;

    ini_set("session.gc_maxlifetime","3600");
    session_start();

    // Autoloader
    require_once 'web/global.php';

    connectToDB();

    $debug = NULL;

    $database = new CFSDatabase();
    $connection = $database->getConnection();

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }

	// ********************************************************************************
	// Initalise variables
	// ********************************************************************************
	$id						= init_var("id","REQUEST");					// Necessary, refers to ad being edited
	$step					= init_var("step","REQUEST",1);				// Necessary, refers to current step in the process
	if ($step > 6) {
        $step = 1;
    }
	$step_request	        = init_var("step_request");
	$direction		        = init_var("submit","POST","next_step");	// By default, we're going forward in steps
    
        if (isset($_POST['submit_save_changes_x']) && $_POST['submit_save_changes_x'] > 0) { $direction = "save_changes";}
	if (isset($_POST['submit_prev_step_x']) && $_POST['submit_prev_step_x'] > 0) { $direction = "prev_step"; }
	if (isset($_POST['submit_next_step_x']) && $_POST['submit_next_step_x'] > 0) { $direction = "next_step"; }
	if (isset($_POST['submit_publish_x']) && $_POST['submit_publish_x'] > 0) { $direction = "publish"; }
    
	$error		= array();
	$update_error	= "";
    
	// ********************************************************************************
	// Security check
	// Ensure ad we're editing belongs to current user
	// ********************************************************************************
	$query = "select * from cf_offered where offered_id = '".$id."'";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    
	if (!$result || mysqli_num_rows($result) == 0) { header("Location: post-choice.php"); exit; }
	$ad = mysqli_fetch_assoc($result);
	if ($ad['user_id'] != $_SESSION['u_id']) {
		header("Location: your-account-manage-posts.php");
		//die("have permissions to edit this advertisement.");
	}
    
	// FIRST PANE elements
	$postcode				    = init_var("postcode");
	$street_name		                    = init_var("street_name");
	$street_name_label		    = ($street_name)? $street_name : '<span style="color:#959595;">Enter postcode first</span>';
	$town					    = init_var("town");
	$town_label				    = ($town) ? '' : '<span style="color:#959595;">Enter postcode first</span>';
	$town_list				    = array();
	$longitude				    = init_var("longitude");
	$latitude				    = init_var("latitude");
	$available_date		    	            = init_var("available_date");
	$min_term				    = init_var("min_term", "POST", 0);
	$max_term				    = init_var("max_term", "POST", 0);
        $street                     = init_var("street");
    $area                       = init_var("area");
    $region                     = init_var("region");
    $lookup_type                = init_var("lookup_type");
    $country                    = init_var("country");
    $postal_code                = init_var("postal_code");
    
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
	$building_type		        = init_var("building_type","POST","house");
	$price_pcm                  = (isset($_POST['price_pcm'])) ? $CFSIntl->parseCountryCurrency($_POST['price_pcm'], 'app') : NULL;
	$deposit_required           = (isset($_POST['deposit_required'])) ? $CFSIntl->parseCountryCurrency($_POST['deposit_required'], 'app') : NULL;
	$incl_utilities             = init_var("incl_utilities");
	$incl_council_tax           = init_var("incl_council_tax");
    $average_bills              = (isset($_POST['average_bills'])) ? $CFSIntl->parseCountryCurrency($_POST['average_bills'], 'app') : NULL;
	$room_share			        = init_var("room_share");
    $single_bedrooms_available  = init_var("single_bedrooms_available", "POST", 0);
    $double_bedrooms_available  = init_var("double_bedrooms_available", "POST", 0);
	$bedrooms_available         = init_var("bedrooms_available", "POST", 0);
	$bedrooms_double            = init_var("bedrooms_double", "POST", 0);
	$bedrooms_total	            = init_var("bedrooms_total", "POST", 0);
	$furnished	                = init_var("furnished");
	$room_letting               = init_var("room_letting");
	$parking                    = init_var("parking","POST","None");
	$shared_lounge_area         = init_var("shared_lounge_area");
	$central_heating            = init_var("central_heating");
	$washing_machine            = init_var("washing_machine");
	$garden_or_terrace          = init_var("garden_or_terrace");
	$bicycle_store              = init_var("bicycle_store");
	$dish_washer                = init_var("dish_washer");
	$tumble_dryer               = init_var("tumble_dryer");
	$ensuite_bathroom           = init_var("ensuite_bathroom");
	$shared_broadband           = init_var("shared_broadband");
	$cleaner                    = init_var("cleaner");
	$accommodation_description	= addslashes(init_var("accommodation_description"));
    
	// THIRD PANE elements
	$current_age				= init_var("current_age","POST",0); // Not for DB insert, used to calc min_age and max_age if one person.
	$current_num_males			= init_var("current_num_males");
	$current_num_females		= init_var("current_num_females");
	$current_occupation			= init_var("current_occupation","POST","Professionals");
	$owner_lives_in				= init_var("owner_lives_in");
	$current_is_couple			= init_var("current_is_couple");
	$current_is_family			= init_var("current_is_family");
	$church_attended			= addslashes(init_var("church_attended"));
	$church_url                 = (isset($_POST['church_url'])) ? addslashes(strip_http(trim($_POST['church_url']))) : NULL;
	$household_description		= addslashes(init_var("household_description"));
    
	// FOURTH PANE elements
	$suit_gender				= init_var("suit_gender","POST","Mixed");
	$suit_student				= init_var("suit_student");
	$suit_mature_student		= init_var("suit_mature_student");
	$suit_professional			= init_var("suit_professional");
	$suit_married_couple		= init_var("suit_married_couple");
	$suit_family				= init_var("suit_family");
	$church_reference			= init_var("church_reference");
    
	// FIFTH PANE elements
	$contact_name				= addslashes(init_var("contact_name"));
	$contact_phone				= addslashes(init_var("contact_phone"));
	$picture					= init_var("picture");
    
	// SIXTH PANE elements
	$photo_update_captions		= init_var("photo_update_captions");
	$photo_delete				= init_var("photo_delete");
    
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
    else if (isset($ad['suit_min_age']) && isset($ad['suit_max_age'])) {
        if ($ad['suit_min_age'] <= 18) $ad['suit_min_age'] = 18;
        if ($ad['suit_max_age'] >= 51) $ad['suit_max_age'] = 51;
        
        $suitAgeRangePreview = cleanAge($ad['suit_min_age'], $ad['suit_max_age'], 'suit');
        $suitAgeRange = reverseAgeConvert($ad['suit_min_age']) . '-' . reverseAgeConvert($ad['suit_max_age']);
    }
    else {
        $suitAgeRangePreview = cleanAge(18, 51, 'suit');
        $suitAgeRange = '1-8';
    }

    if (!isset($ad['accommodation_type'])) {
        $ad['accommodation_type'] = 'whole place';
    }

	// ********************************************************************************
	// WHOLE PLACE EXCEPTION
	// When we're dealing with a "whole place" ad type we do not need to gather
	// information about the "Current Household" (step 3) so redirect
	// ********************************************************************************
	if ($ad['accommodation_type'] == "whole place" && $step == 3) {

		header("Location: ".$_SERVER['PHP_SELF']."?id=".$id."&step=4");
		exit;

	}

	// ********************************************************************************
	// Handle POST action
	// $_POST to this page = we need to 1) validate and 2) update db with data
	// ********************************************************************************
	if ($_POST) {

		switch($step) {

			// STEP 1: Location and dates
			case 1:

				/*

					EXAMPLE of post submission:

					Array
					(
						[id] => 5322
						[step] => 1
						[longitude] => -0.187888879799631
						[latitude] => 51.528253308387
						[postcode] => W9 1JD
						[street_name] => Elgin Avenue
						[town] => Maida Hill
						[available_date] => 2008-07-16
						[min_term] => 20
						[max_term] => 36
						[submit_x] => 37
						[submit_y] => 12
						[submit] => next_step
					)

				*/
				$debug .= debugEvent("Step 1 POST", print_r($_POST,true));

                if ((empty($longitude) || empty($latitude)) && $lookup_type == "geo") {
                    $error['geo'] = 'Please search for your accommodation location';
                }

                
                // Added to make sure we have a postal code!
                if (empty($postal_code) && $country != 'IE') {
                    $error['geo'] =  "Type in an address and select a match from the list shown.";
                }

                if ($street == 'undefined' || $region == 'undefined' || $area == 'undefined') {
                    $error['geo'] = 'Please move the marker to display a proper address';
                }


		if (!preg_match(REGEXP_UK_POSTCODE_STRICT, $postcode) && $lookup_type == "postcode") {
					$error['postcode'] = ' Please enter your postcode INCLUDING the space, e.g. W6 9PJ';
				}
                else if (preg_match(REGEXP_UK_POSTCODE_STRICT, $postcode) && $lookup_type == "postcode") {
					// Load the list of alternative towns
					$query = "
						SELECT	place_name as `town`
						FROM	cf_uk_places
						WHERE	place_name NOT LIKE '%Avenue'
						AND		place_name NOT LIKE '%Road'
						AND		place_name NOT LIKE '%Station'
						AND		place_name NOT LIKE '% Street'
						AND		postcode = '".substr($postcode,0,-3)."'
						UNION
						SELECT	town
						FROM	cf_jibble_postcodes
						WHERE	postcode = '".substr($postcode,0,-3)."'
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (mysqli_num_rows($result)) {
						$town_list = array();
						while ($row = mysqli_fetch_assoc($result)) {
							$town_list[$row['town']] = $row['town'];
						}
					}
				}

				if ($available_date == 0 ) {
					$error['available_date'] = ' Please choose the date the accommodation is available';
				}


				if ($min_term && $max_term && ($min_term > $max_term)) {
					$error['term'] = ' Max term must be more than the minimum term';
				}

				if ($street_name == "" && $town == "" && $lookup_type == "postcode") {
					$error['street_name'] = ' Please press "Find address" after entering your postcode';
				}

				// If we've encountered no error
				if (!$error) {

                    $sql = "UPDATE cf_offered SET
                        last_updated_date = now(),
                        postcode = :postcode,
                        street_name = :street_name,
                        town_chosen = :town,
                        latitude = :latitude,
                        longitude = :longitude,
                        country_id = 1,
                        available_date = :available_date,
                        expiry_date = DATE_ADD(:available_date, interval 10 day),
                        min_term = :min_term,
                        max_term = :max_term,
                        street = :street,
                        area = :area,
                        region = :region,
                        country = :country,
                        postal_code = :postal_code
                    WHERE offered_id = :id
                    AND user_id = :user_id";
                    
    
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue("postcode", ($postal_code==""?$postcode:$postal_code));     
                    $stmt->bindValue("street_name", ($street==""?$street_name:$street));  
                    $stmt->bindValue("town", ($area=""?$town:$area));                                    
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
                    $stmt->bindValue("user_id", getUserIdFromSession());
                    $result = $stmt->execute();

					// Update DB
					$debug .= debugEvent("Step 1 query:",$sql);
					if ($result === FALSE) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting offered ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}

				}

				break;

			// STEP 2: Accommodation details
			case 2:

				/*
					Example of POST data for step 2:

					Array
					(
						[id] => 5322
						[step] => 2
						[building_type] => flat
						[price_pcm] => 213123
						[deposit_required] => 32411sfd
						[incl_council_tax] => 1
						[average_bills] => xcd
						[room_share] => 1
						[bedrooms_available] => 8
						[bedrooms_double] => 1
						[bedrooms_total] => 3
						[furnished] => 1
						[room_letting] => 1
						[parking] => Permit on-street
						[shared_lounge_area] => 1
						[dish_washer] => 1
						[central_heating] => 1
						[tumble_dryer] => 1
						[washing_machine] => 1
						[ensuite_bathroom] => 1
						[garden_or_terrace] => 1
						[shared_broadband] => 1
						[bicycle_store] => 1
						[cleaner] => 1
						[accommodation_description] =>
						[submit_x] => 35
						[submit_y] => 25
						[submit] => prev_step
					)
				*/
				$debug .= debugEvent("Step 2 POST",print_r($_POST,true));


					// Here we go from having:
					// single_bedrooms_available + double_bedrooms_available = bedrooms_total
					// to
					// bedrooms_available - bedrooms_double =  bedrooms_total
					if ($ad['room_share'] == 1) {
						$bedrooms_available = 1;
						$bedrooms_double = 1;
					} else {
						$bedrooms_available = $double_bedrooms_available + $single_bedrooms_available;
						$bedrooms_double = $double_bedrooms_available;
					}


/*				$debug .= debugEvent("Step double_bedrooms_available",print_r($double_bedrooms_available,true));
				$debug .= debugEvent("Step single_bedrooms_available",print_r($single_bedrooms_available,true));
				$debug .= debugEvent("Step bedrooms_available",print_r($bedrooms_available,true));
				$debug .= debugEvent("Step bedrooms_double",print_r($bedrooms_double,true));
				$debug .= debugEvent("Step bedrooms_total",print_r($bedrooms_total,true));
		*/



				// Validate only if going forward
				if ($direction != "prev_step") {
                    
					// Exception handling:
					// If we're dealing with a "whole place", $bedrooms available = $bedrooms_total
					if ($ad['accommodation_type'] == "whole place") {
						$bedrooms_total = $bedrooms_available;
					}
                    
					if (!$building_type) $error['building_type'] = 'Please indicate a building type';
					if (!$price_pcm) $error['price_pcm'] = 'Enter an amount (e.g. &quot;250&quot;, without a "' . $appCountry['currency_symbol'] .'" sign)';
					if ($price_pcm == 0) $error['price_pcm'] = 'Enter an amount, e.g. &quot;250&quot;, without a "'. $appCountry['currency_symbol'] .'" sign';
					//if (!$deposit_required) $error['deposit_required'] = 'Enter an amount, e.g. &quot;250&quot;, without a "'. $appCountry['currency_symbol'] .'" sign';
                    
					// Validate average bills ONLY if ad is not "whole place"
					if ($average_bills) {
						if (!$average_bills) $error['average_bills'] = 'Enter an amount, e.g. &quot;250&quot;, without a "'. $appCountry['currency_symbol'] .'" sign';
					}
					if ($single_bedrooms_available == 0 && $double_bedrooms_available == 0 && $ad['room_share'] == 0) {
						$error['bedrooms_available'] = 'Please select number of bedrooms offered';
					}
					// If flatshare or family_share, bedrooms_total must be > than bedrooms_available
					if ($ad['accommodation_type'] &&
							($ad['accommodation_type'] == "flat share" || $ad['accommodation_type'] == "family share") &&
							($ad['room_share'] != "1") &&
							$bedrooms_total == $bedrooms_available) {
						$error['bedrooms_total'] = '<br />Bedrooms offered must be less than the total number of bedrooms.<br/>You may mean to have choosen the "Whole Place" advert type.<br />';
					}

					if ($ad['accommodation_type'] &&
						($ad['accommodation_type'] == "whole place") &&
						$bedrooms_total <> $bedrooms_available) {
					}
					if (!$bedrooms_total) {
						$error['bedrooms_total'] = 'Please select total number of bedrooms in the property';
					}
//		    if ($bedrooms_available && $bedrooms_double && ($bedrooms_double > $bedrooms_available)) {
//						$error['bedrooms_double'] = 'Number of double bedrooms exceeds number of bedrooms offered.';
//					}

					if ($bedrooms_total && $bedrooms_available && ($bedrooms_available > $bedrooms_total)) {
						$error['bedrooms_available'] = 'Number of bedrooms offered cannot exceed total';
					}

					if ($bedrooms_total && $bedrooms_available && ($bedrooms_available == $bedrooms_total) && ($ad['accommodation_type'] != "whole place") && ($ad['room_share'] != "1")) {
						if ($bedrooms_total > 1) {$plural = 's';} else {$plural = '';}
						$error['bedrooms_total'] = '<br />The number of bedrooms offered should not equal the total number of bedrooms.<br /><br /><span class="grey">If you have </span>"'.$bedrooms_total.' bedroom'.$plural.' available in a '.$bedrooms_total.' bed property"<span class="grey"> you should place a "Whole Place"<br /> advert. You can express willingness to let bedroom individually to flat sharers there.</span>';
					}

                    if ($country == 'GB') {
    					if ($ad['accommodation_type'] != "whole place") {
    						if ($average_bills == 0 && ($incl_utilities == 0 || $incl_council_tax == 0)) {
    							// Bills and CT are not both included, and no value for bills has been given.
    							if ($incl_utilities == 0 && $incl_council_tax == 0) {
    								$error['average_bills'] = '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Please enter an estimate for bills and council tax';
    							} elseif ($incl_utilities == 0 && $incl_council_tax == 1) {
    								$error['average_bills'] = 'Please enter an estimate for bills';
    							} elseif ($incl_utilities == 1 && $incl_council_tax == 0) {
    								$error['average_bills'] = 'Please enter an estimate for council tax';
    							}
    						}
                        }
                        elseif ($average_bills == 0 && $incl_council_tax == 0 && $ad['offered_id'] > 5416) {
    					    $error['average_bills'] = 'Please enter an estimate for council tax';
    					}
                    }
                    else {
    					if ($ad['accommodation_type'] != "whole place") {
    						if ($average_bills == 0 && ($incl_utilities == 0)) {
    							// Bills and CT are not both included, and no value for bills has been given.
    							if ($incl_utilities == 0) {
    								$error['average_bills'] = 'Please enter an estimate for bills';
    							}
    						}
                        }
                    }

					if (strlen($accommodation_description) < 150) {
						$error['accommodation_description'] = 'Please enter at least 150 characters';
					}


//				$debug .= debugEvent("Step error['bedrooms_total']",print_r($error['bedrooms_total'],true));
//				$debug .= debugEvent("Step error['bedrooms_total']",print_r($error['bedrooms_total'],true));

				} // direction previous step

				// If we've encountered no error
				if (!$error) {

					// Update DB
					//--		room_share							= '".$room_share."', -- NEVER CHANGES
					$query = "
						UPDATE cf_offered SET
							last_updated_date 			= now(),
							building_type 					= '".$building_type."',
							price_pcm  							= '".$price_pcm."',
							deposit_required  			= '".$deposit_required."',
							incl_utilities  				= '".intval($incl_utilities)."',
							incl_council_tax 				= '".intval($incl_council_tax)."',
							average_bills 					= '".$average_bills."',
							bedrooms_total  				= '".$bedrooms_total ."',
							bedrooms_available  		= '".$bedrooms_available."',
							bedrooms_double  				= '".$bedrooms_double ."',
							furnished  							= '".intval($furnished)."',
							room_letting						= '".intval($room_letting)."',
							parking  								= '".$parking ."',
							shared_lounge_area  		= '".intval($shared_lounge_area)."',
							central_heating  				= '".intval($central_heating)."',
							washing_machine  				= '".intval($washing_machine)."',
							garden_or_terrace  			= '".intval($garden_or_terrace)."',
							bicycle_store 					= '".intval($bicycle_store)."',
							dish_washer  						= '".intval($dish_washer)."',
							tumble_dryer  					= '".intval($tumble_dryer)."',
							ensuite_bathroom  			= '".intval($ensuite_bathroom)."',
							shared_broadband  			= '".intval($shared_broadband)."',
							cleaner  								= '".intval($cleaner)."',
							accommodation_description 	= '".$accommodation_description."'
						WHERE offered_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';
					";
					$debug .= debugEvent("Step 2 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting offered ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}

				}

				break;

			case 3:

				/*

					Example $_POST data:

					Array
					(
						[id] => 5322
						[step] => 3
						[current_num_males] => 0
						[current_num_females] => 1
						[current_min_age] => 0
						[current_max_age] => 0
						[current_occupation] => Students (<22yrs)
						[owner_lives_in] => 1
						[current_is_couple] => 1
						[current_is_family] => 1
						[church_attended] =>
						[church_url] =>
						[household_description] =>
						[submit_x] => 25
						[submit_y] => 16
						[submit] => next_step
					)

				*/

				// Validate only if going forward
				if ($direction != "prev_step") {

					if ($ad['accommodation_type'] != "whole place") {

						if (!$current_num_males && !$current_num_females) {
							$error['current_num_males'] = "Please select number of the members of the household<br/>";
						}
						if (!$current_occupation) {
							$error['current_occupation'] = "Please select occupation of the members of the household<br/>";
						}
						if (!$church_attended) {
							$error['church_attended'] = "Please enter your church name<br/>";
						}
						if (strlen($household_description) < 150) {
							$error['household_description'] = 'Please enter at least 150 characters';
						}
					}

				}
                
                // CONVERT AGES
                list($min, $max) = explode('-', $currentAgeRange);
                
                $minimum_age = ageConvert($min, 'min_age');
                $maximum_age = ageConvert($max, 'max_age');
                
				// If we've encountered no error
				if (!$error) {

					// Update DB
					$query = "
						UPDATE cf_offered SET
							last_updated_date 			= now(),
							current_num_males		= '".$current_num_males."',
							current_num_females		= '".$current_num_females."',
							current_min_age			= '".$minimum_age."',
							current_max_age			= '".$maximum_age."',
							current_occupation		= '".$current_occupation."',
							owner_lives_in			= '".$owner_lives_in."',
							current_is_couple		= '".$current_is_couple."',
							current_is_family		= '".$current_is_family."',
							church_attended			= '".$church_attended."',
							church_url				= '".$church_url."',
							household_description	= '".$household_description."'
						WHERE offered_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';
					";
					$debug .= debugEvent("Step 2 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting offered ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}

				}

				break;

			case 4:

				/*

					Example of $_POST:

					Array
					(
						[id] => 5322
						[step] => 4
						[suit_gender] => Male(s)
						[suit_min_age] => 0
						[suit_max_age] => 0
						[suit_professional] => 1
						[suit_mature_student] => 1
						[suit_student] => 1
						[suit_married_couple] => 1
						[suit_family] => 1
						[church_reference] => 1
						[submit_x] => 39
						[submit_y] => 17
						[submit] => next_step
					)

				*/

                // CONVERT AGES
                list($min, $max) = explode('-', $suitAgeRange);
                
                $minimum_age = ageConvert($min, 'min_age');
                $maximum_age = ageConvert($max, 'max_age');

				// If we've encountered no error
				if (!$error) {

					// Update DB
					$query = "
						UPDATE cf_offered SET
							last_updated_date 			= now(),
							suit_gender				= '".$suit_gender."',
							suit_min_age			= '".$minimum_age."',
							suit_max_age			= '".$maximum_age."',
							suit_student			= '".intval($suit_student)."',
							suit_mature_student		= '".intval($suit_mature_student)."',
							suit_professional		= '".intval($suit_professional)."',
							suit_married_couple		= '".intval($suit_married_couple)."',
							suit_family				= '".intval($suit_family)."',
							church_reference 		= '".intval($church_reference)."'
						WHERE offered_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';
					";
					$debug .= debugEvent("Step 4 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting offered ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}

				}

				break;

			case 5:

				/*

					Array
					(
						[id] => 5322
						[step] => 5
						[contact_name] =>
						[contact_phone] =>
						[displayPic] => lamp.gif
						[submit_x] => 35
						[submit_y] => 11
						[submit] => prev_step
					)

				*/

				// Validate only if going forward
				if ($direction != "prev_step") {

					if (!$contact_name) {
						$error['contact_name'] = 'Please enter your contact name';
					}
					if (!$picture) {
						$error['picture'] = 'Please choose an advert picture for your ad';
					}

				}

				// If we've encountered no error
				if (!$error) {

					// Update DB
					$query = "
						UPDATE cf_offered SET
							last_updated_date 			= now(),
							picture						= '".$picture."',
							contact_name				= '".$contact_name."',
							contact_phone				= '".$contact_phone."'
						WHERE offered_id = '".$id."'
						AND user_id = '".$_SESSION['u_id']."';
					";
					$debug .= debugEvent("Step 5 query:",$query);
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (!$result) {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting offered ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
					}

				}

				break;

			case "6":
            
				//require('includes/class.upload.php');

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
                                $filename = $image->scaleAndSave($id, 'offered', 640, 480);
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
					
                    // The post data will contain something along the lines of:
                    // [id] => 1622
                    // [post_type] => offered
                    // [ad_caption_1204] => (enter caption)
                    // [ad_caption_1198] => (enter caption)
                    // [ad_caption_1205] => (enter caption)
					
					foreach($_POST as $key => $value) {
						if (preg_match('/^ad_caption_(\d+)$/',$key,$matches) && trim($value) != "(enter caption)") {
							$query = "update cf_photos set caption = '".trim($value)."' where post_type = 'offered' and photo_id = '".$matches[1]."'";
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
					$query = "select * from cf_photos where post_type = 'offered' and photo_id in (".$sqlWhere.");";
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
					$query = "delete from cf_photos where photo_id in (".$sqlWhere.");";
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
					//			$temp = new Date($available_date);
					//			$temp->addSeconds(86400 * 10);
					//			$expiry_date = $temp->format("%Y-%m-%d");

					// If scammer, with suppresed replies, set PUBLISHED to 0
					$query  = 'SELECT "X" FROM cf_users WHERE (suppressed_replies = 1 OR suppressed_replies = NULL) ';
					$query .= 'AND user_id = '.$_SESSION['u_id'];
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					$num_results = mysqli_num_rows($result);
					if ($num_results == 1) { $published = 0; } else { $published = DEFAULT_PUBLISH_STATUS; };

          // If Whole Place, we set Published to 0 (not published), for manual review
          // if ($ad['accommodation_type'] == "whole place") { $published = 0; }

					// If we're dealing with a new ad (i.e. "e" token is not present)
					// then set published to 1 and redirect
					$query = "
						UPDATE cf_offered SET
							expiry_date = date_add('".$ad['available_date']."',interval 10 day),
							published = '".$published."'
						WHERE offered_id = '".$id."'
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) {
						header("Location: details.php?post_type=offered&id=".$id."&new_ad=1");
						exit;
					} else {
						$update_error = '<p style="margin-bottom:0px;" class="error">ERROR: An error occured when inserting offered ad in the ChristianFlatshare database. Please contact '.TECH_EMAIL.'</p>';
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

			// Exception to the step calculation above: We're dealing
			// with a whole place and we're trying to go back ("prev_step")
			// from step 4 to step 3.
			if ($ad['accommodation_type'] == "whole place" && $direction == "prev_step" && $step == 3) {
				$step = 2;
			}

			// Redirect: We'll either redirect to the next / previous step OR
			// if the "save changes" button was pressed ($direction == "save"), back to the
			// "Your ads"
			// RD: modifed to test for the X value of the Input "name", as the input type uses an image which will
			// not pass its Value through in IE, okay in FF.
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

		$debug .= debugEvent('$ direciton :',print_r($direction,true));
        if (isset($_POST['submit'])) {
            $debug .= debugEvent('POST submit:',print_r($_POST['submit'],true));
        }
		$debug .= debugEvent('AD data:',print_r($ad,true));
		// We'll iterate through the $ad variable and create the necessary variables
		// which will be automatically picked up by our form
		foreach($ad as $k => $v) {

			// Cerntain data from the ad are not needed
			if (
				$k != "offered_id" &&
				$k != "user_id" &&
				$k != "country_id" &&
				$k != "created_date" &&
				$k != "last_updated_date" &&
				$k != "published" &&
				$k != "times_viewed" &&
				$k != "paid_for" &&
				$k != "approved" &&
				$k != "suspended" &&
				$k != "recommendations"
			) {
				${$k} = $v;
			}

		}
		// A few quick mods where column names DON'T match field names
		if ($town_chosen) {
			$town = $town_chosen;
			$town_label = $town_chosen;
			$town_list = array($town=>$town);
		}
		if ($street_name) { $street_name_label = $street_name; }
		if (($current_num_males + $current_num_females) == 1) {
			$current_age = $current_max_age;
		}
		// Here we go from having:
		// bedrooms_available - bedrooms_double =  bedrooms_total
		// to
		// single_bedrooms_available + double_bedrooms_available = bedrooms_total
		$double_bedrooms_available = $ad['bedrooms_double'];
		$single_bedrooms_available = $ad['bedrooms_available'] - $ad['bedrooms_double'];

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
		$query = "select * from cf_photos where ad_id = '".$id."' and post_type = 'offered' order by photo_sort asc;";
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
			$photos .= '<br/></p><br/>Select a photograph from above and use the buttons below to rotate it 90&deg; or delete it:';
	//		$photos .= '<input type="submit" name="photo_delete" value="Delete photo(s)" onclick="return validateDeletion();" />';
			$photos .= '</p>'."\n";
		}
	}

	// Format error
	if ($error) { array_walk($error, 'formatError'); }
	$debug .= debugEvent('Current step:',$step);
//	$debug .= debugEvent('Current step:',var_dump($error));
	$debug .= debugEvent('Session vars:',print_r($_SESSION,true));
	$debug .= debugEvent('Session vars:',print_r($_SESSION,true));
	$debug .= debugEvent('POST vars:',print_r($_POST,true));
	$debug .= debugEvent('REQUEST vars:',print_r($_REQUEST,true));
	$debug .= debugEvent('GET vars:',print_r($_GET,true));


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
<!-- InstanceBeginEditable name="doctitle" -->
<title>Offered Accommodation ad - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css?v=2" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />

<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/scripts/jquery.nouislider.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
<script language="javascript" type="text/javascript" src="includes/icons.js"></script>


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
		var myTips = new Tips('.tooltip');

		if ($('tip_pricing')) {
			$('tip_pricing').store('tip:title', 'Pricing');
			$('tip_pricing').store('tip:text', 'If you are offering more than one bedroom, and at different prices,<br/>it is usually best to put the average price per bedroom here and include<br/>the different prices in your advert text.</p>');
		}

		if ($('tip_pricing2')) {
			$('tip_pricing2').store('tip:title', 'Pricing advice...');
			$('tip_pricing2').store('tip:text', 'Offering a lower than market price can help to attract<br />a greater number of potential tennants.<br /><br />With more potential tennants to choose from you maybe better able<br /> to choose the best tennant for you; the one whom you can share a<br />good relationship with, and both be a blessing to eachother.</p>');
		}

		if ($('tip_current_household_family_share')) {
			$('tip_current_household_family_share').store('tip:title', 'The Current Household');
			$('tip_current_household_family_share').store('tip:text', 'This describes the household which your new lodger would move in to.');
		}

		if ($('tip_current_household_flat_share')) {
			$('tip_current_household_flat_share').store('tip:title', 'The Current Household');
			$('tip_current_household_flat_share').store('tip:text', 'This describes the household which your new housemate would move in to.');
		}

		if ($('tip_room_letting')) {
			$('tip_room_letting').store('tip:title', 'Individual Bedroom Letting');
      $('tip_room_letting').store('tip:text', 'If you are letting a whole house or flat, you would consider letting the bedrooms in property to<br />individuals wanting to start a flat or house share. Ticking this option expresses that you are willing<br />to consider letting the bedrooms individually.');
		}

		if ($('tip_postcode')) {
			$('tip_postcode').store('tip:title', 'Postcode');
 	  	$('tip_postcode').store('tip:text', 'If your postcode is not recognised by CFS, click on "Contact Us"<br /> and send us your full address, including the postocde. We can then <br />add your postcode to CFS.<br /><br />This is sometimes necessary with very new postcodes.</p>');
		}

		if ($('tip_professional')) {
			$('tip_professional').store('tip:title', 'Occupation');
			$('tip_professional').store('tip:text', 'We use &quot;professional&quot; to indicate that someone is at the stage of life when they are<br /> working in some capacity (butler, butcher, or banker), or looking for work. It can also<br /> describe someone who is retired. <br /><br />Please use the text box to add further details if required.');
		}

               if ($('tip_sliders')) {
                        $('tip_sliders').store('tip:title', 'Sliders');
                        $('tip_sliders').store('tip:text', 'Click on the sliders to move them to indicate the age range. <br /><br /><b>Note</b> - if both sliders are on the same value, one will be on top of the other. If you have difficulty<br /> moving   the       sliders at this point, please try moving the slider shown in one direction a few times.');


                }





		// Preload all necessary buttons
		var myImages = new Asset.images([
			'/images/buttons/form_button_next_over.gif',
			'/images/buttons/form_button_previous_over.gif',
			'/images/buttons/form__over.gif',
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

				if ($('')) {
					$('').addEvent('mouseenter',function() {
						this.src = '/images/buttons/form__over.gif';
					});
					$('').addEvent('mouseleave',function() {
						this.src = '/images/buttons/form_.gif';
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
		$('offered_form').submit();

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
			window.location = 'your-account-rotate-photo.php?type=offered&ad_id=<?php print $id?>&photo_id='+selectedCheckbox.value+'&direction='+direction;
		}
	}

	<?php } ?>

</script>
<script language="javascript" type="text/javascript" src="includes/slimbox-new/slimbox.js"></script>
<link href="includes/slimbox-new/slimbox.css" rel="stylesheet" type="text/css" />


<link href="styles/dialog_box.css" rel="stylesheet" type="text/css" />

<!-- GOOGLE MAPS API v3  -->
<script src="https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
<!--<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script> -->
<script src="scripts/chooser.js"></script>
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td><h1 class="mb0">Offered Accommodation ad</h1></td>
				<td align="right" valign="bottom">
					<h2 class="grey mb0">
						<?php if ($ad['room_share'] == 1) {
								echo "Room Share advert";
							} elseif ($ad['accommodation_type'] == "flat share" ) {
								echo "House or Flatshare ad";
							} else {
								echo ucwords($ad['accommodation_type'])." advert";
							} ?>
					</h2>
				</td>
			</tr>
			</table>
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
				<li><a href="<?php print $tab_links[2]?>" id="tab2link" class="<?php print ($step == 2)? 'current':''?>">Accommodation&nbsp;Details</a></li>
				<?php if ($ad['accommodation_type'] != "whole place") { ?>
				<li><a href="<?php print $tab_links[3]?>" id="tab3link" class="<?php print ($step == 3)? 'current':''?>">The&nbsp;Current&nbsp;Household</a></li>
				<?php } ?>
				<?php if ($ad['accommodation_type'] == "whole place") { ?>
					<li><a href="<?php print $tab_links[4]?>" id="tab4link" class="<?php print ($step == 4)? 'current':''?>">Your&nbsp;New&nbsp;Tenants(s)</a></li>
				<?php } elseif ($ad['accommodation_type'] == "family share") { ?>
					<li><a href="<?php print $tab_links[4]?>" id="tab4link" class="<?php print ($step == 4)? 'current':''?>">Your&nbsp;New&nbsp;Lodger(s)</a></li>
				<?php } elseif ($ad['accommodation_type'] == "flat share" && $ad['room_share'] == "0") { ?>
					<li><a href="<?php print $tab_links[4]?>" id="tab4link" class="<?php print ($step == 4)? 'current':''?>">Your&nbsp;New&nbsp;House/Flatmate(s)</a></li>
				<?php } else {?>
					<li><a href="<?php print $tab_links[4]?>" id="tab4link" class="<?php print ($step == 4)? 'current':''?>">Your&nbsp;New&nbsp;Roommate</a></li>
				<?php } ?>
				<li><a href="<?php print $tab_links[5]?>" id="tab5link" class="<?php print ($step == 5)? 'current':''?>">Contact&nbsp;Details&nbsp;&amp;&nbsp;Advert&nbsp;Picture</a></li>
				<li><a href="<?php print $tab_links[6]?>" id="tab6link" class="<?php print ($step == 6)? 'current':''?>">Add&nbsp;Photos</a></li>
			</ul>

			<form name="offered_form" id="offered_form" action="<?php print $_SERVER['PHP_SELF']?>" method="post" <?php if ($step == 6) { ?>enctype="multipart/form-data"<?php } ?>>
			<input type="hidden" name="id" value="<?php print $id?>" />
			<input type="hidden" name="step" value="<?php print $step?>" />
			<input type="hidden" name="step_request" id="step_request" value="" />

			<div class="tab">

				<?php if ($step == 1) { ?>

				<!-- STEP 1 : LOCATION AND DATES -->
				<script language="javascript" type="text/javascript" src="includes/offered-ad-address-picker.js"></script> 
				<input type="hidden" name="longitude" id="longitude" value="<?php print $longitude; ?>" />
				<input type="hidden" name="latitude" id="latitude" value="<?php print $latitude; ?>" />

				<h2 class="formHeader m0 mb10">Tell us the accommodation postcode and dates it is available</h2>
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
                                <div id="addressExtra"><span>Drag marker to update address</span><a href="" id="resetChooser">Enter a new location</a></div>
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
    						<td width="200" align="right">Address:</td>
    						<td><span id="addressName" style="font-weight: bold;"><?php print (isset($error['geo'])) ? $address.' '.$error['geo'] : $address ; ?></span></td>
    					</tr>

					<tr>
						<td align="right">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span> Date available from:</td>
						<td><?php print createDateDropDown("available_date",300,$available_date,TRUE,"dateSelector")?><?php print (isset($error['available_date'])) ? $error['available_date'] : NULL ;?></td>
					</tr>
					<tr>
						<td align="right">Minimum term:</td>
						<td><?php print createDropDown("min_term",getTermsArray("minimum"),$min_term);?>&nbsp;<span class="grey style4">length of stay</span></td>
					</tr>
					<tr>
						<td align="right" valign="top">Maximum term:</td>
						<td><?php print createDropDown("max_term",getTermsArray("maximum"),$max_term);?>&nbsp;<span class="grey style4">length of stay (3 months or less is described &quot;short-term&quot;)</span><?php print (isset($error['term'])) ? $error['term'] : NULL ; ?></td>
					</tr>
				</table>

				<?php } ?>	<!-- Step 1 -->

				<?php if ($step == 2) { ?>

				<!-- STEP 2 : Accommodation details -->
				<h2 class="formHeader m0 mb10">Describe the accommodation offered</h2>

				<?php if ($ad['accommodation_type'] == "whole place") { ?>
						<script language="javascript" type="text/javascript">
							function pickBuildingType(v) {

							<!-- V can be "house" or "flat" -->
								if (v == "house") {
										$('building_type_label_0').set('text','house');
										$('building_type_label_1').set('text','house');
										$('building_type_label_2').set('text','house');
										$('building_type_label_3').set('text','house');
										$('building_type_label_4').set('text',' bedroom house');
										$('building_type_label_5').set('text',' house');
								} else {
										$('building_type_label_0').set('text','flat');
										$('building_type_label_1').set('text','flat');
										$('building_type_label_2').set('text','flat');
										$('building_type_label_3').set('text','flat');
										$('building_type_label_4').set('text',' bedroom flat');
										$('building_type_label_5').set('text',' flat');
								}
							}
						</script>
				<?php } else { ?>
						<script language="javascript" type="text/javascript">
							function pickBuildingType(v) {

								<!-- V can be "house" or "flat" -->
								if (v == "house") {
										$('building_type_label_0').set('text','house');
										$('building_type_label_2').set('text','house');
								} else {
										$('building_type_label_0').set('text','flat');
										$('building_type_label_2').set('text','flat');
								}
							}
						</script>
				<?php } ?>

				<table border="0" cellpadding="0" cellspacing="9" class="noBorder" width="100%">
          <tr valign="top">
            <td width="275" align="right" >Building type:</td>
            <td width="525"><?php print createRadioGroup("building_type",array("house"=>"House","flat"=>"Flat"),$building_type,"vertical",'','','onclick="pickBuildingType(this.value);"')?>
                <?php print (isset($error['building_type'])) ? $error['building_type'] : NULL; ?>            </td>
          </tr>
          <?php if ($ad['room_share'] != 1) { ?>
          <tr>
            <?php if ($ad['accommodation_type'] != "whole place") { ?>
            <td width="275" align="right"><span class="obligatory">*</span> How many single bedrooms are offered: </td>
            <?php } else { ?>
            <td width="275" align="right"><span class="obligatory">*</span> Number of single bedrooms in the <span id="building_type_label_1"><?php print  $ad['building_type'] ?></span>: </td>
            <?php } ?>

            <td><!-- for whole place we add a JS function, and the text is different -->
            <?php if ($ad['accommodation_type'] == "whole place") { ?>
            	<?php print createDropDown("single_bedrooms_available",getBedroomArray(true, "None", " single bedroom", " single bedrooms"),$single_bedrooms_available,'','width:130px;','onclick="accommodationDescription(this.value);"')?>
            <?php } else { ?>
              <?php print createDropDown("single_bedrooms_available",getBedroomArray(true, "None", " single bedroom offered", " single bedrooms offered"),$single_bedrooms_available,'','width:160px;') ?>
            <?php } ?>
                <?php print (isset($error['bedrooms_available'])) ? $error['bedrooms_available'] : NULL ; ?>
					  </td>
          </tr>
          <?php } ?> <!-- End if room share -->

          <?php if ($ad['accommodation_type'] != "whole place") { ?>
          <tr>
            <?php if ($ad['room_share'] == 1) { ?>
            <td width="275" align="right">Bedroom offered:</td>
            <?php } else { ?>
            <td width="275" align="right"><span class="obligatory">*</span> How many double bedrooms are offered:</td>
            <?php } ?>

            <td><?php if ($ad['room_share'] == 1) { ?>
                <strong>1 shared double room</strong>
                <?php } else { ?>
                <?php print createDropDown("double_bedrooms_available",getBedroomArray(true, "None", " double bedroom offered", " double bedrooms offered"),$double_bedrooms_available,'','width:170px;')?>
                <?php } ?>
                <?php print $error['bedrooms_available']?>
					  </td>
          </tr>
          <?php } else { ?> <!-- if Whole place -->
          <tr>
            <td width="275" align="right"><span class="obligatory">*</span> Number of double bedrooms in the  <span id="building_type_label_2"><?php print  $ad['building_type'] ?></span>:</td>
            <td><?php if ($ad['accommodation_type'] == "whole place") { ?>
                <!-- for whole place we add a JS function -->
                <?php print createDropDown("double_bedrooms_available",getBedroomArray(true, "None", " double bedroom", " double bedrooms"),$double_bedrooms_available,'','width:140px;','onclick="accommodationDescription(this.value);"')?>
                <?php } else { ?>
                <?php print createDropDown("double_bedrooms_available",getBedroomArray(true, "None", " double bedroom available", " double bedrooms available"),$double_bedrooms_available,'','width:170px;')?>
                <?php } ?>
                <?php print $error['bedrooms_available']?></td>
          </tr>
          <?php } ?>

          <tr>
            <?php if ($ad['accommodation_type'] != "whole place") { ?>
							<?php if ($ad['room_share'] == "1") { ?>
							<td width="275" align="right"><label for="furnished">Is the bedroom furnished:</label></td>
							<?php } else { ?>
							<td width="275" align="right"><label for="furnished">Are the bedroom(s) offered furnished:</label></td>
							<?php } ?>
            <?php } else { ?>
            <td width="275" align="right"><label for="furnished">Is the <span id="building_type_label_3"><?php print  $ad['building_type'] ?></span> furnished:</label></td>
            <?php } ?>
            <td><?php print createCheckbox("furnished","1",$furnished)?></td>
          </tr>

          <?php if ($ad['accommodation_type'] == "whole place") { ?>
					<tr>
						<td width="275" align="right"><label for="room_letting">Would you consider letting the bedrooms individually:</label></td>
            <td><?php print createCheckbox("room_letting","1",$room_letting);?><strong><a href="#" id="tip_room_letting" class="tooltip">(?)</a></strong></td>
					</tr>
          <?php } ?>

          <!-- JS to update the total number of rooms descriprtion, for Whole Place ads -->
          <script language="javascript" type="text/javascript">

						function accommodationDescription() {

							// Get total number of bedrooms
							var singles = parseInt($('single_bedrooms_available').getSelected()[0].value);
							var doubles = parseInt($('double_bedrooms_available').getSelected()[0].value);
							var total = singles + doubles;

							$('accommodation_label').set('text',total);

						}

						function fieldFocus(id) {
							if ($(id).value == "0") {
								$(id).value = "";
							}
						}

						function fieldBlur(id) {
							$(id).value = $(id).value.trim();
							if ($(id).value == "") {
								$(id).value = "0";
							}
						}
					</script>


					<?php if ($ad['accommodation_type'] != "whole place") { ?>
<!-- The function disables average bills when BOTH include bills and CT are checked -->
<script language="javascript" type="text/javascript">
function hide_average_bills() {
	var incl_utilities   = $('incl_utilities').checked;
	var incl_council_tax = $('incl_council_tax').checked;
	var country = document.getElementById('country').value;

	if (incl_utilities && incl_council_tax ) {
		document.getElementById('average_bills').value="0";
		document.getElementById('average_bills').disabled=true;
	}
    else {
		document.getElementById('average_bills').disabled=false;
	}

	<!-- Change label dependant of options selected: -->
    if (country == 'GB') {
		if (!incl_council_tax && !incl_utilities) {

			$('initial_bills_label').set('text','Indication of monthly share of bills + council tax:');
			$('initial_bills_label').setStyle('display','');
			$('average_bills').setStyle('display','');
			$('bills_askrisk_label').setStyle('display','');
			$('bills_askrisk_label2').setStyle('display','');
			$('average_bills_right_label').set('text','council tax and household bills contribution (per bedroom)');
			$('average_bills_right_label').setStyle('display','');

		} else if (incl_council_tax && !incl_utilities) {

			$('initial_bills_label').set('text','Indication of monthly share of bills:');
			$('initial_bills_label').setStyle('display','');
			$('average_bills').setStyle('display','');
			$('bills_askrisk_label').setStyle('display','');
			$('bills_askrisk_label2').setStyle('display','');
			$('average_bills_right_label').set('text','household bills contribution (per bedroom)');
			$('average_bills_right_label').setStyle('display','');

		} else if (!incl_council_tax && incl_utilities) {

			$('initial_bills_label').set('text','Indication of monthly share of council tax:');
			$('initial_bills_label').setStyle('display','');
			$('average_bills').setStyle('display','');
			$('bills_askrisk_label').setStyle('display','');
            $('bills_askrisk_label2').setStyle('display','');
			$('average_bills_right_label').set('text','council tax contribution (per bedroom)');
			$('average_bills_right_label').setStyle('display','');

		} else if (incl_council_tax && incl_utilities) {

			$('initial_bills_label').set('text',' ');	  <!-- set to a blank character so the page does not jump up -->
			$('average_bills').setStyle('display','none');
			$('average_bills_right_label').setStyle('display','none');
			$('bills_askrisk_label').setStyle('display','none');
			$('bills_askrisk_label2').setStyle('display','none');
		}
    }
    else {
        if (!incl_utilities) {

    		$('initial_bills_label').set('text','Indication of monthly share of bills:');
    		$('initial_bills_label').setStyle('display','');
    		$('average_bills').setStyle('display','');
    		$('bills_askrisk_label').setStyle('display','');
    		$('bills_askrisk_label2').setStyle('display','');
    		$('average_bills_right_label').set('text','household bills contribution (per bedroom)');
    		$('average_bills_right_label').setStyle('display','');

    	} else if (incl_utilities) {

    		$('initial_bills_label').set('text',' ');	  <!-- set to a blank character so the page does not jump up -->
    		$('average_bills').setStyle('display','none');
    		$('average_bills_right_label').setStyle('display','none');
    		$('bills_askrisk_label').setStyle('display','none');
            $('bills_askrisk_label2').setStyle('display','none');
    	}
    }
}
</script>
					<?php } else { ?>
						<script language="javascript" type="text/javascript">
							function hide_average_bills() {
								var incl_council_tax = $('incl_council_tax').checked;

								if (incl_council_tax )
								{
									document.getElementById('average_bills').value="0";
									document.getElementById('average_bills').disabled=true;
									$('bills_askrisk_label').setStyle('display','none');
								} else {
									document.getElementById('average_bills').disabled=false;
									$('bills_askrisk_label').setStyle('display','');
								}
							}
						</script>
					<?php } ?>
          <tr>
            <?php if ($ad['accommodation_type'] != "whole place") { ?>
            <td width="275" align="right"><span class="obligatory">*</span> Total number of bedrooms in the <span id="building_type_label_2"><?php print  $ad['building_type'] ?></span>:</td>
            <?php } else { ?>
            <td width="275" align="right">Accommodation description:</td>
            <?php } ?>
            <td>
							<?php if ($ad['accommodation_type'] != "whole place") { ?>
              	<?php print createDropDown("bedrooms_total",getBedroomArray(false, "", " bedroom property", " bedroom property" ),$bedrooms_total,'','width:130px;')?>
                <?php print $error['bedrooms_total']?>
              <?php } else { ?>
                <strong>
              	<span id="accommodation_label"><?php print $bedrooms_total;?></span>&nbsp;<span id="building_type_label_4">bedroom <?php print $building_type;?></span></strong>
              <?php } ?>
					  </td>
          </tr>
          <tr>
            <td align="right">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="275" align="right"><span class="obligatory">*</span> MONTHLY Price:</td>
            <td><?php print $appCountry['currency_symbol']; ?>&nbsp;
                <input type="text" size="5" name="price_pcm" id="price_pcm" value="<?php if ($price_pcm < 1) { echo "0"; } else { echo $price_pcm; } ?>" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/>
              &nbsp;
                <?php if ($ad['accommodation_type'] != "whole place") { ?>
	                <?php if ($ad['room_share'] == "1") { ?>
  				            for the room share
          	  	  <?php } else { ?>
            	  			per bedroom
		              <?php } ?>
         	  	  <?php } else { ?>
								 for the<span id="building_type_label_5"> <?php print $building_type;?></span>
    	          <?php } ?>

	              <?php if ($ad['accommodation_type'] != "whole place" && $ad['room_share'] != "1") { ?>
  	            <strong><a href="#" id="tip_pricing" class="tooltip">(?)</a> </strong>
    	          <?php } elseif ($ad['accommodation_type'] == "whole place" || $ad['accommodation_type'] == "family share") { ?>
  	            	<strong>&nbsp;<a href="#" id="tip_pricing2" class="tooltip">(pricing advice...)</a></strong>
    	          <?php } ?>
              <?php print $error['price_pcm']?>
					  </td>
          </tr>

          <tr>
            <td width="275" align="right">Deposit:</td>
            <td><?php print $appCountry['currency_symbol']; ?>&nbsp;
                <input type="text" size="5" name="deposit_required" id="deposit_required" value="<?php if ($deposit_required < 1) { echo "0"; } else { echo $deposit_required; } ?>" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/>
              <?php print $error['deposit_required']?></td>
          </tr>
					</table>

					<table border="0" cellpadding="0" cellspacing="0" class="noBorder" width="100%">
				  <tr >
            <td width="285" align="right" valign="top" style="padding-top:10px;padding-bottom:2px"><label for="incl_utilities">Bills are included:</label></td>
				    <td width="30"style="padding-top:10px;padding-bottom:2px" valign="bottom">&nbsp;&nbsp;&nbsp;<?php print createCheckbox("incl_utilities","1",$incl_utilities,'onclick="hide_average_bills()"');?></td><td><label for="incl_utilities"><span class="grey">gas, water, electricity</span></label></td>
			    </tr>
                <input type="hidden" name="country" id="country" value="<?php print $appCountry['iso']; ?>" />
          <?php if($appCountry['iso'] == 'GB'): // No council tax for international flats ?>
          <tr>
            <td width="285" align="right" valign="middle"><label for="incl_council_tax">Council tax is included:</label></td>
            <td>&nbsp;&nbsp;&nbsp;<?php print createCheckbox("incl_council_tax","1",$incl_council_tax,'onclick="hide_average_bills()"');?></td><td></td>
          </tr>
          <?php else: ?>
              <checkbox id="incl_council_tax" />
          <?php endif; ?>
 			  	</table>

					<table border="0" cellpadding="0" cellspacing="0" class="noBorder" width="100%">
          <?php if ($ad['accommodation_type'] != "whole place") { ?>
          <tr>
<!--            <td width="285" align="right" style="padding-top:10px">Indication of share of monthly bills:</td>   -->
<?php

if ($appCountry['iso'] == 'GB') {
    if ($incl_council_tax == 0 && $incl_utilities == 0) {
        $initial_bills_label = "Indication of monthly share of bills + council tax:";
        $initial_bills_right_label = "council tax and household bills contribution (per bedroom)";
    }
    elseif ($incl_council_tax == 1 && $incl_utilities == 0) {
        $initial_bills_label = "Indication of monthly share of bills:";
        $initial_bills_right_label = "household bills contribution (per bedroom)";
    }
    elseif ($incl_council_tax == 0 && $incl_utilities == 1) {
        $initial_bills_label = "Indication of monthly share of council tax:";
        $initial_bills_right_label = "council tax contribution (per bedroom)";
    }
    else {
        $initial_bills_label = " ";
        $initial_bills_right_label = "council tax and bills are included";
    }
}
else {
    if ($incl_utilities == 0) {
        $initial_bills_label = "Indication of monthly share of bills:";
        $initial_bills_right_label = "household bills contribution (per bedroom)";
    }
    else if ($incl_utilities == 1) {
        $initial_bills_label = " ";
        $initial_bills_right_label = "bills are included";
    }
}
?>
            <td width="285" align="right" style="padding-top:10px"><span id="bills_askrisk_label" class="obligatory">*</span> <span id=initial_bills_label><?php print $initial_bills_label?></span></td>
            <td style="padding-top:10px"><span id="bills_askrisk_label2">&nbsp;&nbsp;<?php print $appCountry['currency_symbol']; ?>&nbsp;</span>
                <input type="text" size="5" name="average_bills" id="average_bills" <?php if ($incl_council_tax == 1 && $incl_utilities == 1) { echo "disabled=true"; } ?> value="<?php if ($average_bills < 1) { echo "0"; } else { echo $average_bills; } ?>" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/>
              &nbsp;<span class="grey" id="average_bills_right_label"><?php print $initial_bills_right_label?><?php print $error['average_bills']?></span>
							</td>
          </tr>
          <?php } else if ($appCountry['iso'] == 'GB') { ?>
          <tr>
						<!-- The usage of the average bills field changes for Whole Place ads, from ID 5416 -->
						<?php if ($ad['offered_id'] > 5416) { ?>
  	          <td width="285" align="right" style="padding-top:10px"><span  id="bills_askrisk_label" class="obligatory">*</span> Council tax:</td>
						<?php } else { ?>
	            <td width="285" align="right" style="padding-top:10px">Estimate of bills:</td>
						<?php } ?>
            <td style="padding-top:10px">&nbsp;&nbsp;<?php print $appCountry['currency_symbol']; ?>&nbsp;
                <input type="text" size="5" name="average_bills" id="average_bills" <?php if ($incl_council_tax == 1) { echo "disabled=true"; } ?> value="<?php if ($average_bills < 1) { echo "0"; } else { echo $average_bills; } ?>" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/>
              &nbsp;<span class="grey" id="average_bills_right_label"></span>
             <?php print $error['average_bills']?></td>
          </tr>
          <?php } ?>
					</table>
					<table border="0" cellpadding="0" cellspacing="9" class="noBorder" width="100%">
          <tr>
            <td align="right">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="275" align="right" valign="top">Car Parking available:</td>
            <td><?php print createDropDown("parking",getParkingArray(),$parking,"vertical")?></td>
          </tr>
          <tr>
            <td width="275" align="right" valign="top">The accommodation has: </td>
            <td><table cellpadding="0" cellspacing="0" id="mod_cons">
                <tr>
                  <td width="20"><?php print createCheckbox("shared_lounge_area","1",$shared_lounge_area);?></td><td width="180">
                      <label for="shared_lounge_area">a shared lounge area</label></td>
                  <td><?php print createCheckbox("dish_washer","1",$dish_washer);?></td>
                     <td><label for="dish_washer">a dish washer</label></td>
                </tr>
                <tr>
                  <td><?php print createCheckbox("garden_or_terrace","1",$garden_or_terrace);?></td>
                    <td><label for="garden_or_terrace">a garden / roof terrace</label></td>
                  <td><?php print createCheckbox("tumble_dryer","1",$tumble_dryer);?></td>
                    <td><label for="tumble_dryer">a tumble dryer</label></td>
                </tr>
                <tr>
                  <td><?php print createCheckbox("ensuite_bathroom","1",$ensuite_bathroom);?></td>
                    <td><label for="ensuite_bathroom">an ensuite bathroom</label></td>
                  <td><?php print createCheckbox("washing_machine","1",$washing_machine);?></td>
                    <td><label for="washing_machine">a washing machine</label></td>
                </tr>
                <tr>
                  <td><?php print createCheckbox("bicycle_store","1",$bicycle_store);?></td>
                    <td><label for="bicycle_store">a suitable place to store a bicycle</label></td>
                  <td><?php print createCheckbox("cleaner","1",$cleaner);?></td>
                     <td><label for="cleaner">a cleaner that visits</label></td>
                </tr>
                <tr>
                  <td><?php print createCheckbox("shared_broadband","1",$shared_broadband);?></td>
                     <td><label for="shared_broadband">access to shared broadband</label></td>
                  <td></td>
                  <!--			<td><?php print createCheckbox("central_heating","1",$central_heating);?><label for="central_heating">central heating</label></td> -->
                </tr>
            </table></td>
          </tr>
        </table>
				<table border="0" cellpadding="0" cellspacing="10" class="noBorder" width="100%">
					<tr>
						<td valign="top">
							<?php print $error['accommodation_description']?><p class="mb2 mt0 grey" style="font-size:12px">&nbsp;<strong><span class="obligatory">*</span> More about the <span id="building_type_label_0"><?php print ucwords($building_type);?></span>...</strong></p>
							<textarea name="accommodation_description" rows="13" id="accommodation_description" style="overflow:auto;width:100%;padding:3px; font-size: 12px;font-family: arial, helvetica, sans-serif;"><?php print stripslashes(trim($accommodation_description))?></textarea>
							<div><strong id="char_count">0</strong> characters entered. <span class="grey">Minimum: 150 characters. Recommended: 300+ characters.</span></div>						</td>
						<td width="200" height="185" align="right" valign="top" style="padding-bottom:10px;padding-top:30px">
							<div><img src="images/tag_cloud_accommodation.gif" width="198" height="181" /></div>						</td>
					</tr>
				</table>

				<script language="javascript" type="text/javascript">

								window.addEvent("domready",function(){

									$('accommodation_description').addEvent('keydown',function(e){

										var count = $('accommodation_description').value.length;

										if (count > 3000) {
											$('accommodation_description').value = $('accommodation_description').value.substring(0,3000);
										} else {
											updateCounter();
										}
									});

									function updateCounter() {

										var count = $('accommodation_description').value.trim().length;
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

				<?php if ($step == 3) { ?>

				<h2 class="formHeader m0">Tell us about your household</h2>
				<?php if ($ad['accommodation_type'] == "flat share") { ?>
					<p class="mt0 mb10"><span class="style1 m0 formSubHeader"><strong>(which your new <?php print  $ad['building_type']; ?>mate would move in to)</strong></span></p>
					<!-- <strong><a href="#" id="tip_current_household_flat_share" class="tooltip">(?)</a></strong> -->
				<?php } else { ?>
					<p class="mt0 mb10"><span class="style1 m0 formSubHeader"><strong>(which your new lodger would move in to)</strong></span></p>
					<!--  <strong><a href="#" id="tip_current_household_family_share" class="tooltip">(?)</a></strong></p>		-->
				<?php } ?>
				<script language="javascript" type="text/javascript">

					function update_sex() {

						var males = parseInt($('current_num_males').getSelected()[0].value);
						var females = parseInt($('current_num_females').getSelected()[0].value);

						if (males == 1) {
							$('current_age_sex').set('text','His ');
						} else {
							$('current_age_sex').set('text','Her ');
						}
					}


				</script>
				<table width="100%" border="0" cellpadding="0" cellspacing="10" class="noBorder" >
					<tr>
						<td width="200" align="right"><span class="obligatory">*</span> Number of male adult members:</td>
						<td><?php print createDropDown("current_num_males",array("0"=>"None","1"=>"1 male","2"=>"2 males","3"=>"3 males","4"=>"4 males","5+"=>"5+  males"),$current_num_males,'','width:200px;','',' in the household')?><?php print $error['current_num_males']?></td>
					</tr>
					<tr>
						<td width="200" align="right"><span class="obligatory">*</span> Number of female adult members:</td>
						<td><?php print createDropDown("current_num_females",array("0"=>"None","1"=>"1 female","2"=>"2 females","3"=>"3 females","4"=>"4 females","5+"=>"5+ females"),$current_num_females,'','width:200px;','',' in the household')?></td>
					</tr>
				</table>
				<table width="100%" border="0" cellpadding="0" cellspacing="10" class="noBorder">
					<tr>
						<td width="200" align="right"><span class="obligatory">*</span> Age range of adult household:<br /><strong><a href="#" class="tooltip" id="tip_sliders">(?)</a></strong> <span class="grey">move the sliders to indicate range</span></td>
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
				<table width="100%" border="0" cellpadding="0" cellspacing="7" class="noBorder">
					<tr>
						<td style="padding-top:15px" width="200" align="right"><span class="obligatory">*</span> Occupation:</td>
						<td style="padding-top:15px"><?php print createRadioGroup("current_occupation",getOccupationArray(),$current_occupation)?><?php print $error['current_occupation']?> <strong><a href="#" class="tooltip" id="tip_professional">(?)</a></strong></td>
					</tr>
					<tr>
						<td width="200" align="right"><label for="owner_lives_in">Owner is a member of the household:</label></td>
						<td><?php print createCheckbox("owner_lives_in","1",$owner_lives_in);?></td>
					</tr>
					<tr>
						<td width="200" align="right"><label for="current_is_couple">Household has a married couple:</label></td>
						<td><?php print createCheckbox("current_is_couple","1",$current_is_couple);?></td>
					</tr>
					<?php if ($ad['accommodation_type'] != 'flat share') { ?>
					<tr>
						<td width="200" align="right"><label for="current_is_family">Household has children:</label></td>
						<td><?php print createCheckbox("current_is_family","1",$current_is_family);?></td>
					</tr>
					<?php } ?>
					<tr>
						<td>&nbsp;
						</td>
					</tr>
					<tr>
						<td width="200" align="right"><span class="obligatory">*</span> Church attended:</td>
					  <td>
							<?php print $error['church_attended']?>
							<input type="text" name="church_attended" id="church_attended" value="<?php print stripslashes($church_attended)?>" />
						  <span class="grey">&quot;St John's, Bath &quot; / &quot;Looking for a church &quot;</span>
						</td>
					</tr>
					<tr>
						<td width="200" align="right">Church website(s): </td>
					  <td>
							<input  style="width:260px;" type="text" name="church_url" id="church_url" value="<?php print $church_url?>" />
					</tr>
				</table>

				<table width="100%" border="0" cellpadding="0" cellspacing="10" class="noBorder" >
					<tr>
						<td><?php print $error['household_description']?>
								<p class="mb2 mt0"><span class="grey" style="font-size:12px">&nbsp;<span class="obligatory">*</span> <strong>More about the Household...</strong></span></p>
							<textarea name="household_description" rows="13" id="household_description" style="overflow:auto;width:100%;padding:2px; font-size:12px;"><?php print stripslashes(trim($household_description))?></textarea>
							<div><strong id="char_count">0</strong> characters entered. <span class="grey">Minimum: 150 characters. Recommended: 300+ characters.</span></div>
						</td>
						<td width="200" align="right" valign="top" style="padding-top:25px;">
							<div><img src="images/tag_cloud_household.gif" width="198" height="184" /></div>
						</td>

					</tr>
				</table>
						<script language="javascript" type="text/javascript">

							window.addEvent("domready",function(){

									$('household_description').addEvent('keydown',function(e){

										var count = $('household_description').value.length;

										if (count > 5000) {
											$('household_description').value = $('household_description').value.substring(0,5000);
										} else {
											updateCounter();
										}
									});

									function updateCounter() {

										var count = $('household_description').value.trim().length;
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

					<?php if ($ad['accommodation_type'] == "whole place") { ?>
						<h2 class="formHeader m0">Tell us about the new tenant(s)</h2>
						<p class="style1 m0 formSubHeader"><strong>(who would be suitable for the <?php print  $ad['building_type'] ?>)</strong></p>
					<?php } elseif ($ad['accommodation_type'] == "family share") { ?>
						<h2 class="formHeader m0">Tell us who your new lodger(s) could be</h2>
				<!--		<p class="style1 m0 formSubHeader"><strong>(the lodgers who you would like to live with you)</strong></p>	-->
					<?php } elseif ($ad['accommodation_type'] == "flat share" && $ad['room_share'] != "1") { ?>
						<h2 class="formHeader m0">Tell us who your new <?php print  ($ad['building_type'] == "house")?"housemate":"flatmate" ?><?php print  $plural ?> could be</h2>
					<!--	<p class="style1 m0 formSubHeader"><strong>(describe who your new <?php print  ($ad['building_type'] == "house")?"housemate":"flatmate" ?><?php print  $plural ?> could be)</strong></p>		-->
					<?php } elseif ($ad['accommodation_type'] == "flat share" && $ad['room_share'] == "1") { ?>
						<h2 class="formHeader m0">Tell us who your new roommate could be</h2>
					<!--	<p class="style1 m0 formSubHeader"><strong>(describe who your new roommate could be)</strong></p>	-->
					<?php } ?>
				<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
					<tr>
						<td width="200" align="right">Sex:</td>
						<td>
							<?php print $error['suit_gender']?>
							<?php print createRadioGroup("suit_gender",getGenderArray("Male(s) or female(s)"),$suit_gender)?>
						</td>
					</tr>
                    
					<tr>
						<td align="right" valign="top">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
                    
					<tr>
						<td width="200" align="right" valign="top"><span class="obligatory">*</span> Age range:<br /><strong><a href="#" class="tooltip" id="tip_sliders">(?)</a></strong> <span class="grey">move the sliders to indicate range</span></td>
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
						<td align="right" valign="top">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td width="200" align="right" valign="top">Occupation:</td>
						<td>
							<?php print createCheckbox("suit_professional","1",$suit_professional);?><label for="suit_professional">Professionals</label><br />
							<?php print createCheckbox("suit_mature_student","1",$suit_mature_student);?><label for="suit_mature_student">Mature students</label><br />
							<?php print createCheckbox("suit_student","1",$suit_student);?><label for="suit_student">Students (&lt;22yrs)</label>
						</td>
					</tr>
					<tr>
						<td width="200" align="right"><label for="suit_married_couple">Could be a married couple:</label></td>
						<td><?php print createCheckbox("suit_married_couple","1",$suit_married_couple);?></td>
					</tr>
					<tr>
						<td width="200" align="right"><label for="suit_family">Could be a family with children:</label></td>
						<td><?php print createCheckbox("suit_family","1",$suit_family);?></td>
					</tr>
					<tr style="padding-bottom:10px;">
						<td width="200" align="right"><label for="church_reference">Would be someone who, if asked, could<br />provide a recommendation from a church:</label></td>
						<td>
							<?php print createCheckbox("church_reference","1", $church_reference);?>
							<span class="grey"><label for="church_reference">simply to say that they are known to a church fellowship which could in someway vouch for their character</label></span>
						</td>
					</tr>
				</table>


				<?php } ?>

				<?php if ($step == 5) { ?>

					<h2 class="formHeader m0">Your contact details</h2>
					<p class="formSubHeader m0">&nbsp;</p>
					<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
						<tr>
							<td align="right"><span class="obligatory">*</span> Contact name:</td>
							<td><input name="contact_name" type="text" id="contact_name" value="<?php print stripslashes($contact_name)?>"/>
							&nbsp;<span class="grey style4">e.g. &quot;John Smith&quot;</span>&nbsp;<?php print $error['contact_name']?></td>
						</tr>
						<tr>
							<td align="right">Contact phone number:</td>
						  <td><input name="contact_phone" type="text" id="contact_phone" value="<?php print $contact_phone?>"/>&nbsp;<span class="grey style4">optional</span></td>
						</tr>
					</table>
					<p><strong>CFS does not disclose your email address</strong><br />
					People responding can do so through a form which sends you an alert email, and sends their message to Your messages.<br />
					You may include your phone number and any additional contact details within your advert.</span></p>

					<p><span class="mb5 mt10"><strong>Choose a retro advert picture to describe your advert (just for fun) </strong></span> <?php print $error['displayPic']?></p>

					<div id="displayPicWrapper"><div id="displayPicCanvas"><?php print $displayPicCanvas?></div></div>

				<?php } ?>

				<?php if ($step == 6) { ?>

				<h2 class="formHeader m0">Add photos</h2>

				<?php if ($photoCount <= 7) { ?>
	      <p class="mb0"><strong>Adding photos will help you to get the best response from your advert!</strong><br /><br />Recommended photos:<br />- Bedrooms<br />- Bathrooms<br />- Kitchen<br />- Living areas<br />- Of the outside of the <?php print $building_type ?><?php if ($ad['accommodation_type'] != "whole place") { ?><br />People photos which introduce the household can be especially helpful, and fun too... maybe your holiday snaps??<?php } ?><br /><br /></p>

					<div id="uploadBack" style="width:550px">
						<p class="mt0">Use the form below to add a photos for this ad. You may add up to 8 photos. <br />
						(Max size 20MB, file types: JPEG, minimum image size: 480x480px)</p>
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
							<?php	if ($step == 1) {
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
										echo '<input name="submit_publish" id="" type="image" value="publish" src="images/buttons/form_button_publish.gif" />';
									}
								} else {
									echo '<input name="submit_next_step" id="" type="image" value="publish" src="images/buttons/form_button_next.gif" />';
								}
						} else { ?> <!--show if already published -->
							<td>
							<span class="grey"><strong>Click on the grey form tabs above to change parts of your ad,<br />e.g. click on the grey &quot;Accommodation Details&quot; tab.</strong></span></td>
						<?php } ?>


						<?php if ($ad['published'] != 2) { ?>
						<td align="right" valign="bottom"><a href="#" onclick="return showCancelBox();"><img src="images/buttons/form_button_cancel.gif" id="button_cancel" border="0" /></a></td>
						<td align="right" width="117" valign="bottom">
							<input name="submit_save_changes" id="button_save_changes" type="image" value="save_changes" src="images/buttons/form_button_save_changes.gif">
						</td>
						<?php } else { ?>
			<!--			<td align="right" valign="bottom"></td>
						<td align="right" width="117" valign="bottom">
							<input name="submit_save_changes" id="button_save_for_later" type="image" value="save_changes" src="images/buttons/form_button_save_for_later.png">
						</td>	-->
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
