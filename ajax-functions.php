<?php

// Autoloader
require_once 'web/global.php';

	switch($_GET['action']) {
	
		// Called from any page where a "save ad" button was clicked
		case "save":
			
            header('Content-type: application/json');
            
			$post_type = $_GET['post_type'];
			$id = $_GET['id'];
			
			// First, find out if the ad has already been saved
			$query = "
				select * from cf_saved_ads where
				user_id = '".$_SESSION['u_id']."' and ad_id = '".$id."' and post_type = '".$post_type."'
			";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (mysqli_num_rows($result)) {
				// Ad exists on the database, act according to the "active" column
				$row = mysqli_fetch_assoc($result);
				if ($row['active']==2) {
					// Was hidden(2), now set to saved (1) 
					$query = "
						update cf_saved_ads set active = '1' where 
						user_id = '".$_SESSION['u_id']."' and 
						ad_id = '".$id."' and 
						post_type ='".$post_type."'
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) { print json_encode(array('result' => 'update_saved')); }
				} elseif ($row['active']==1) {
					// Was saved (1), now un-saved (0) "
					$query = "
						update cf_saved_ads set active = '0' where 
						user_id = '".$_SESSION['u_id']."' and 
						ad_id = '".$id."' and 
						post_type ='".$post_type."'
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) { print json_encode(array('result' => 'update_unsaved')); }
				} elseif ($row['active']==0) {
					// Was un-saved(0), now hidden (2) "
					$query = "
						update cf_saved_ads set active = '2' where 
						user_id = '".$_SESSION['u_id']."' and 
						ad_id = '".$id."' and 
						post_type ='".$post_type."'
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if ($result) { print json_encode(array('result' => 'update_hidden')); }
				}				
			} else {
				// Ad is not on the db table, insert it initially as active = 2, hidden
				$query = "
					insert into cf_saved_ads (user_id,ad_id,post_type,date_saved,active)
					values ('".$_SESSION['u_id']."','".$id."','".$post_type."',now(),2);
				";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if ($result) {
                    print json_encode(array('result' => 'insert_success'));
				} else {
                    print json_encode(array('result' => 'insert_failure'));
				}
			}
			break;
		
		// Called from post-offered.php returns the geocode details of the very first address returned
		// when doing a postcode lookup on postcode anywhere
		case "quickAddressSearch":
		
			$postcode = $_GET['postcode'];
			
			// Step 1: Do a lookup on the cf_postcode_cache table
			$query = "select * from cf_postcode_cache where postcode = '".$postcode."';";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			
			if (mysqli_num_rows($result)) {
				
				$data = mysqli_fetch_assoc($result);
				// Before we create the js object, let's get the list of towns for that postcode
				// from jibble
				$query = "
					SELECT	place_name as `town`
					FROM	cf_uk_places 
					WHERE	place_name NOT LIKE '%Avenue' 
					AND		place_name NOT LIKE '%Road' 
					AND		place_name NOT LIKE '%Station' 
					AND		place_name NOT LIKE '% Street' 
					AND		postcode = '".trim(substr($postcode,0,-3))."' 
					UNION 
					SELECT	town 
					FROM	cf_jibble_postcodes 
					WHERE	postcode = '".trim(substr($postcode,0,-3))."'					
				";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if (mysqli_num_rows($result)) {
					$extraTowns = array();
					while ($row = mysqli_fetch_assoc($result)) {
						$extraTowns[] = $row['town'];
					}
				}
					
				/*
					Let's create a javascript object with the following format:
					
					var test = {
						"street_name"	:	"Name of street",
						"town"			:	"Name of town",
						"extra_towns"	:	["name of town","name of town" etc...]
						"longitude"		:	-50.34654562,
						"latitude"		:	0.006545644
					};
					
				*/
				echo "{";
				echo '"street_name" : "'.$data['street_name'].'", ';
				echo '"town" : "'.$data['town'].'", ';
				if ($extraTowns) {
					echo '"extra_towns" : [';
					foreach($extraTowns as $value) {
						$toEcho .= '"'.$value.'",';
					}
					$toEcho = substr($toEcho,0,-1);
					echo $toEcho;
					echo '],';
				}
				echo '"longitude" : '.$data['longitude'].', ';
				echo '"latitude" : '.$data['latitude'];				
				echo "}";
				
			} else {
			
				// FIRST QUERY: LIST OF ADDRESSES
				$url = "http://services.postcodeanywhere.co.uk/json.aspx?";
				$url .= "&action=lookup";
				$url .= "&type=by_postcode";
				$url .= "&postcode=".urlencode($postcode);
				$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
				$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
                
				// Make the request and get the XML data as a string
				$json = file_get_contents($url);
                $data = json_decode($json);

				// 1: No properties existing on postcode 
                if (empty($data)) {
					echo "ERROR: No properties found on this postcode";
				} else {			
					// Get the id of the very first postcode
					$id = $data[0]->id;
					
					// SECOND QUERY: Fetch the raw address details from PostcodeAnywhere
					$url = "http://services.postcodeanywhere.co.uk/json.aspx?";
					$url .= "&action=fetch";
					$url .= "&id=".urlencode($id);
					$url .= "&language=english";
					$url .= "&style=raw";
					$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
					$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
					
					// Make the request and get the XML data as a string
					$json = file_get_contents($url);
					$data = json_decode($json);
                    
					// If an error has occured
					if (isset($data['error_number'])) {
						echo "ERROR (address): ".$data['message'];
					} else {
						// STREET NAME
						$street_name = $data[0]->thoroughfare_name;
						if ($data[0]->thoroughfare_descriptor) { $street_name .= ' '.$data[0]->thoroughfare_descriptor; }
						
						// TOWN
						// For consistency, instead of the PostcodeAnywhere town, we will query our
						// own cf_jibble_postcodes table to find the town for the given postcode
						$temp = substr($data[0]->postcode,0,strpos($data[0]->postcode," "));
						$query = "select town from cf_jibble_postcodes where postcode = '".$temp."';";
						$result = mysqli_query($GLOBALS['mysql_conn'], $query);
						if ($result) {
							$town = cfs_mysqli_result($result,0,0);
						} else {
							$town = $data['post_town'];
						}
					}
					
					// Fetch the geographic address details from PostcodeAnywhere
					$url = "http://services.postcodeanywhere.co.uk/json.aspx?";
					$url .= "&action=geocode";
					$url .= "&id=".urlencode($id);
					$url .= "&accuracy=HIGH";
					$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
					$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
                    
					// Make the request and get the XML data as a string
					$json = file_get_contents($url);
					$geodata = json_decode($json);

					// If an error has occured
					if (isset($geodata['error_number'])) {
						
						// Repeat the exercise but this time with a lower accuracy setting
						$url = "http://services.postcodeanywhere.co.uk/json.aspx?";
						$url .= "&action=geocode";
						$url .= "&id=".urlencode($id);
						$url .= "&accuracy=LOW";
						$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
						$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
    					$json = file_get_contents($url);
    					$geodata = json_decode($json);
						
						if (isset($geodata['error_number'])) {
							echo "We're sorry but we cannot retrieve the geo-coordinates for your postcode.";
						} else {
							$latitude = $geodata[0]->wgs84_latitude;
							$longitude = $geodata[0]->wgs84_longitude;					
						}
						
					} else {
						$latitude = $geodata[0]->wgs84_latitude;
						$longitude = $geodata[0]->wgs84_longitude;				
					}
					
					// Before we create the js object, let's get the list of towns for that postcode
					// from jibble
					$query = "
						SELECT	place_name as `town`
						FROM	cf_uk_places 
						WHERE	place_name NOT LIKE '%Avenue' 
						AND		place_name NOT LIKE '%Road' 
						AND		place_name NOT LIKE '%Station' 
						AND		place_name NOT LIKE '% Street' 
						AND		postcode = '".trim(substr($postcode,0,-3))."' 
						UNION 
						SELECT	town 
						FROM	cf_jibble_postcodes 
						WHERE	postcode = '".trim(substr($postcode,0,-3))."'					
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (mysqli_num_rows($result)) {
						$extraTowns = array();
						while ($row = mysqli_fetch_assoc($result)) {
							$extraTowns[] = $row['town'];
						}
					}
					
					/* 
						Let's create a javascript object with the following format:
					
						var test = {
							"street_name"	:	"Name of street",
							"town"			:	"Name of town",
							"extra_towns"	:	["name of town","name of town" etc...]
							"longitude"		:	-50.34654562,
							"latitude"		:	0.006545644
						};
					
					*/
                    $toEcho = NULL;
					echo "{";
					echo '"street_name" : "'.$street_name.'", ';
					echo '"town" : "'.$town.'", ';
					if ($extraTowns) {
						echo '"extra_towns" : [';
						foreach($extraTowns as $value) {
							$toEcho .= '"'.$value.'",';
						}
						$toEcho = substr($toEcho,0,-1);
						echo $toEcho;
						echo '],';
					}
					echo '"longitude" : '.$longitude.', ';
					echo '"latitude" : '.$latitude;				
					echo "}";
						
					// Finally, let's cache the information for the current postcode
					/*
					$query = "
						insert into cf_postcode_cache (
							postcode,street_name,town,longitude,latitude
						) values (
							'".$data['postcode']."','".$street_name."','".$town."',".$longitude.",".$latitude."
						);
					";
					$result = @mysqli_query($GLOBALS['mysql_conn'], $query);
					*/				
					
				}			
			
			}
		
			break;
		
		// NOT USED UNLESS AJAX PICKER IS ENABLED:
		// Called from post-offered.phpx when user is looking for all possible addreses for a given postcode
		case "postcodeSearch":
		
			$postcode = $_GET['postcode'];
			
			// Step 1: Do a lookup on the cf_postcode_cache table
			$query = "select * from cf_postcode_cache where postcode = '".$postcode."';";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			
			if (mysqli_num_rows($result)) {
				$data = mysqli_fetch_assoc($result);
				/*
					We have already done a lookup on this postcode.
					Return the street_name, town, longitude and latitude directly
					
					var test = {
						"street_name"	:	"Name of street",
						"town"			:	"Name of town",
						"longitude"		:	-50.34654562,
						"latitude"		:	0.006545644
					};
					
				*/
				echo "{";
				echo '"street_name" : "'.$data['street_name'].'", ';
				echo '"town" : "'.$data['town'].'", ';
				echo '"longitude" : '.$data['longitude'].', ';
				echo '"latitude" : '.$data['latitude'];				
				echo "}";
				
			} else {
						
				// TO DO : REVALIDATE POSTCODE (INCASE PEOPLE ARE MESSING WITH THE QUERY STRING)
				
				// First query the PostcodeAnywhere service
				$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
				$url .= "&action=lookup";
				$url .= "&type=by_postcode";
				$url .= "&postcode=".urlencode($postcode);
				$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
				$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
				
				// Make the request and get the XML data as a string
				$data = file_get_contents($url);
	
				// Now use the PEAR XML Unserializer to convert the XML document into a *D array
				require_once "includes/class.unserializer.php";
				$options = array(
					'complexType' => 'array',
					'parseAttributes' => 'true'			
				);			
				$xml = new XML_Unserializer($options);
				$result = $xml->unserialize($data,false); // The second parameter is "false" which indicates that we're parsing a string
				$data = $xml->getUnserializedData();
				
				// 1: No properties existing on postcode 
				// i.e. $data['Data']['Items'] == 0
				if (isset($data['Data']['Item']['error_number'])) {
					echo "ERROR: ".$data['Data']['Item']['message'];
				} else if (!$data['Data']['Items']) {
					echo "ERROR: No properties found on this postcode";
				} else {
					// 2: Properties were found
					$toEcho = '[';
					if ($data['Data']['Items'] == 1) { // If only one property was found
						$toEcho .= '["'.$data['Data']['Item']['id'].'","'.$data['Data']['Item']['description'].'"]';
					} else { // Else if more than one property was found
						foreach($data['Data']['Item'] as $value) {
							$toEcho .= '["'.$value['id'].'","'.$value['description'].'"],';
						}
						$toEcho = substr($toEcho,0,-1); // Snip last comma
					}
					$toEcho .= ']';
					echo $toEcho;
				}
				
			}			

			break;
			
		// NOT USED UNLESS AJAX PICKER IS ENABLED:
		// From post-offered.php when user is getting the details for a specific address.
		case "addressLookup":
			
			// Called after a postcodeSearch has happened. 
			$id = $_GET['id'];
			
			// Fetch the raw address details from PostcodeAnywhere
			$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
			$url .= "&action=fetch";
			$url .= "&id=".urlencode($id);
			$url .= "&language=english";
			$url .= "&style=raw";
			$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
			$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
			
			// Make the request and get the XML data as a string
			$data = file_get_contents($url);
			
			// Now use the PEAR XML Unserializer to conver the XML document into a *D array
			require_once "includes/class.unserializer.php";
			$options = array(
				'complexType' => 'array',
				'parseAttributes' => 'true'			
			);
			
			// Create an instance of the XML Unserializer class
			$xml = new XML_Unserializer($options);
			$result = $xml->unserialize($data,false); // The second parameter is "false" which indicates that we're parsing a string
			$data = $xml->getUnserializedData();
			
			// If an error has occured
			if (isset($data['Data']['Item']['error_number'])) {
				echo "ERROR (address): ".$data['Data']['Item']['message'];
			} else {
				unset($data['Schema']);
				$data = $data['Data']['Item'];
				
				// STREET NAME
				$street_name = $data['thoroughfare_name'];
				if ($data['thoroughfare_descriptor']) { $street_name .= ' '.$data['thoroughfare_descriptor']; }
				
				// TOWN
				// For consistency, instead of the PostcodeAnywhere town, we will query our
				// own cf_jibble_postcodes table to find the town for the given postcode
				$temp = substr($data['postcode'],0,strpos($data['postcode']," "));
				$query = "select town from cf_jibble_postcodes where postcode = '".$temp."';";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if ($result) {
					$town = cfs_mysqli_result($result,0,0);
				} else {
					$town = $data['post_town'];
				}
				
			}
			
			// Fetch the geographic address details from PostcodeAnywhere
			$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
			$url .= "&action=geocode";
			$url .= "&id=".urlencode($id);
			$url .= "&accuracy=HIGH";
			$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
			$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
			
			// Make the request and get the XML data as a string
			$geodata = file_get_contents($url);
			
			// Now use the PEAR XML Unserializer to conver the XML document into a *D array
			require_once "includes/class.unserializer.php";
			$options = array(
				'complexType' => 'array',
				'parseAttributes' => 'true'			
			);
			
			// Create an instance of the XML Unserializer class
			$xml = new XML_Unserializer($options);
			$result = $xml->unserialize($geodata,false); // The second parameter is "false" which indicates that we're parsing a string
			$geodata = $xml->getUnserializedData();
			
			// If an error has occured
			if (isset($geodata['Data']['Item']['error_number'])) {
				
				// Repeat the exercise but this time with a lower accuracy setting
				$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
				$url .= "&action=geocode";
				$url .= "&id=".urlencode($id);
				$url .= "&accuracy=LOW";
				$url .= "&account_code=".urlencode(POSTCODE_ANYWHERE_ACCOUNT_CODE);
				$url .= "&license_code=".urlencode(POSTCODE_ANYWHERE_LICENCE_CODE);
				$geodata = file_get_contents($url);
				$result = $xml->unserialize($geodata,false); // The second parameter is "false" which indicates that we're parsing a string
				$geodata = $xml->getUnserializedData();
				
				if (isset($geodata['Data']['Item']['error_number'])) {
					echo "We're sorry but we cannot retrieve the geo-coordinates for your postcode.";
				} else {
					$latitude = $geodata['Data']['Item']['wgs84_latitude'];
					$longitude = $geodata['Data']['Item']['wgs84_longitude'];					
				}
				
			} else {
				$latitude = $geodata['Data']['Item']['wgs84_latitude'];
				$longitude = $geodata['Data']['Item']['wgs84_longitude'];				
			}
			
			/* 
				Let's create a javascript object with the following format:
			
				var test = {
					"street_name"	:	"Name of street",
					"town"			:	"Name of town",
					"longitude"		:	-50.34654562,
					"latitude"		:	0.006545644
				};
			
			*/
			echo "{";
			echo '"street_name" : "'.$street_name.'", ';
			echo '"town" : "'.$town.'", ';
			echo '"longitude" : '.$longitude.', ';
			echo '"latitude" : '.$latitude;				
			echo "}";
				
				// Finally, let's cache the information for the current postcode
				$query = "
					insert into cf_postcode_cache (
						postcode,street_name,town,longitude,latitude
					) values (
						'".$data['postcode']."','".$street_name."','".$town."',".$longitude.",".$latitude."
					);
				";
				$result = @mysqli_query($GLOBALS['mysql_conn'], $query);
				
			break;		
		
		// POST - WANTED: Return a list of locations with the appropriate postcode next to them	
		case "locationSearch":
			// TO DO: USE JSON INSTEAD OF 2D ARRAY LITERAL NOTATION
			// TO DO: HANDLE LOCATION ERROR
			$location = $_GET['location'];
			$postcode = $_GET['postcode'];
			
			// If a postcode has been specified, do a lookup
			// on the cf_uk_places table against the postcode column
			if ($postcode) { 
				
				$query = "
					select p.place_id,p.place_name,c.county,p.postcode
					from cf_uk_places as p
					left join cf_jibble_postcodes as c on c.postcode = p.postcode
					where p.postcode = '".$postcode."' order by p.place_name limit 0,40
				";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);

				if (mysqli_num_rows($result) == 0) {
					echo 'no results found';
				} else {
					$toEcho = '[';
					// Create the 2D javascript array (literal notation)
					while ($row = mysqli_fetch_assoc($result)) {
						$toEcho .= '["'.$row['place_name'].', '.$row['county'].' ('.$row['postcode'].')","'.$row['postcode'].'"],';
					}
					$toEcho = substr($toEcho,0,-1);
					$toEcho .= ']';
					echo $toEcho;			
				}
				exit;
				
			}
			
			// If a string has been specified, do a lookup
			// on the cf_uk_places against the place_name column
			if ($location) {
			
				// Step 1: Establish if place is a valid town / area name
				$query = "
					select p.place_id,p.place_name,c.county,p.postcode
					from cf_uk_places as p
					left join cf_jibble_postcodes as c on c.postcode = p.postcode
					where p.place_name like '".$location."%' order by p.place_name,c.county,p.postcode limit 0,40			
				";
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			
				// Step 2: If we have no results, do a soundex search
				if (mysqli_num_rows($result) == 0) {
					$query = "
						select p.place_id,p.place_name,c.county,p.postcode
						from cf_uk_places as p
						left join cf_jibble_postcodes as c on c.postcode = p.postcode
						where p.place_name sounds like '".$location."' order by p.place_name limit 0,40;					
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				}
			
				// Step 3: Return the results
				if (mysqli_num_rows($result) == 0) {
					
					echo 'no results found';
								
				} else {
				
					$toEcho = '[';
					// Create the 2D javascript array (literal notation)
					while ($row = mysqli_fetch_assoc($result)) {
						$toEcho .= '["'.$row['place_name'].', '.$row['county'].' ('.$row['postcode'].')","'.$row['postcode'].'"],';
					}
					$toEcho = substr($toEcho,0,-1);
					$toEcho .= ']';
					echo $toEcho;			
				}
				exit;
				
			}			
			break;
	
		default:
			header("Location: index.php"); exit;
			break;
	
	}

?>