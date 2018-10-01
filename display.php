<?php

use CFS\GeoEncoding\CFSGeoEncoding;
use CFS\Database\CFSDatabase;

// Autoloader
require_once 'web/global.php';

require('includes/class.randompass.php');	// Random password generator class
require('includes/class.pager.php');		// Pager class

if (!isset($show_hidden_ads)) $show_hidden_ads = '';
if (!isset($sqlTableExt)) $sqlTableExt = '';
if (!isset($class)) $class = '';
//if (!isset($index)) $index = '';
if (!isset($place)) $place = '';
if (!isset($_GET['place'])) $_GET['place'] = '';


$geoHelper = new CFSGeoEncoding();
$CFSDatabase = new CFSDatabase();
$connection = $CFSDatabase->getConnection();

	// Initialise variables
	$error = NULL;
	$t = "";
	$pageTitle = "Display results";
	$drillDown = FALSE;
	$hiddenFields = "";
	$mapHiddenFields = "";
	$mapString = '?ad=';
  $sqlWhereExt = NULL;
  $sqlSelectExt = NULL;
	$now = new DateTime();
    $debug = NULL;
	
	// search_type needs to be defined for anything that links to this page
	if (isset($_GET['search_type'])) { $search_type = $_GET['search_type']; } else { $search_type = NULL; }	
	if (isset($_GET['post_type'])) { $post_type = $_GET['post_type']; } else { $post_type = "offered"; }
		
	// ad_id passed in during search type ad_matches and palup
	if (isset($_GET['match_ad_id'])) { $match_ad_id = $_GET['match_ad_id']; } else { $match_ad_id = NULL; }		
	if (isset($_GET['match_post_type']) && !isset($_GET['post_type'])) { 
	  if ($_GET['search_type']=="palup") {
			$post_type = "wanted";  
			$match_post_type = $_GET['match_post_type']; 		

		} else {
			$post_type = ($_GET['match_post_type']=="offered")? "wanted":"offered";		
			$match_post_type = $_GET['match_post_type']; 			
		}

	} else { 
		$match_post_type = NULL; 
	}			

	// The quick search variables
	if (!isset($_GET['match_post_type'])) { 
		if (isset($_GET['post_type'])) { 	
		  $post_type = $_GET['post_type'];
		} else {
			$post_type = "offered"; 
		}
	} 
	

	//	if (isset($_GET['post_type'])) { $post_type = $_GET['post_type']; } else { $post_type = "offered"; }
	if (isset($_GET['place'])) { $place = trim($_GET['place']); } else { $place = NULL; }
	if (isset($_GET['flatshare'])) { $flatshare = $_GET['flatshare']; } else { $flatshare = NULL; }
	if (isset($_GET['familyshare'])) { $familyshare = $_GET['familyshare']; } else { $familyshare = NULL; }
	if (isset($_GET['wholeplace'])) { $wholeplace = $_GET['wholeplace']; } else { $wholeplace = NULL; }
	if (isset($_GET['radius'])) { $radius = $_GET['radius']; } else { 
		if ($post_type == "offered") {
			$radius = DEFAULT_RADIUS; 
		} else {
			$radius = 1;
		}
	}
	if (isset($_GET['pcm'])) { $pcm = $_GET['pcm']; } else { $pcm = NULL; }
	
	// The "Quick links" and Drill-down variables
	if (isset($_GET['place'])) { $place = trim($_GET['place']); } else { $place = NULL; }
	if (isset($_GET['area'])) { $area = $_GET['area']; } else { $area = NULL; }	
	if (isset($_GET['county'])) { $county = $_GET['county']; } else { $county = NULL; }
	if (isset($_GET['town'])) { $town = $_GET['town']; } else { $town = NULL; }
	if (isset($_GET['postcode'])) { $postcode = $_GET['postcode']; } else { $postcode = NULL; }
	
	// The sort and pager variables
	if (isset($_REQUEST['start'])) { $start = $_REQUEST['start']; } else { $start = 0; }
	if (isset($_REQUEST['sortNum'])) { $sortNum = $_REQUEST['sortNum']; } else { $sortNum = 10; }
	if (isset($_REQUEST['sortField'])) { $sortField = $_REQUEST['sortField']; } else { $sortField = "available_date asc"; }	
//	if (isset($_REQUEST['sortField'])) { $sortField = $_REQUEST['sortField']; } else { $sortField = "created_date desc"; }
//	if (isset($_REQUEST['sortField'])) { $sortField = $_REQUEST['sortField']; } else { $sortField = "last_login_days asc"; }
	if (isset($_REQUEST['sortSuit'])) { $sortSuit = $_REQUEST['sortSuit']; } else { $sortSuit = NULL; }
	if (isset($_REQUEST['sortBed1'])) { $sortBed1 = $_REQUEST['sortBed1']; } else { $sortBed1 = NULL; }
	if (isset($_REQUEST['sortBed2'])) { $sortBed2 = $_REQUEST['sortBed2']; } else { $sortBed2 = NULL; }
	if (isset($_REQUEST['sortBed3'])) { $sortBed3 = $_REQUEST['sortBed3']; } else { $sortBed3 = NULL; }
	if (isset($_REQUEST['sortBed4'])) { $sortBed4 = $_REQUEST['sortBed4']; } else { $sortBed4 = NULL; }
	if (!$sortBed1 && !$sortBed2 && !$sortBed3 && !$sortBed4) {
		$sortBed1 = 1;
		$sortBed2 = 1;
		$sortBed3 = 1;
		$sortBed4 = 1;
	}
  // Short-term
	if (isset($_REQUEST['sortShortTerm'])) { $sortShortTerm = $_REQUEST['sortShortTerm']; } else { $sortShortTerm = 0; }	
	
	// The advanced search variables for both OFFERED & WANTED ADS
	if (isset($_GET['available_date'])) { $available_date = $_GET['available_date']; } else { $available_date = NULL; }
	if (isset($_GET['min_term'])) { $min_term = $_GET['min_term']; } else { $min_term = NULL; }
	if (isset($_GET['max_term'])) { $max_term = $_GET['max_term']; } else { $max_term = NULL; }
	if (isset($_GET['building_type'])) { $building_type = $_GET['building_type']; } else { $building_type = NULL; }
	if (isset($_GET['pcm'])) { $pcm = $_GET['pcm']; } else { $pcm = NULL; }
	if (isset($_GET['bedrooms_required'])) { $bedrooms_required = $_GET['bedrooms_required']; } else { $bedrooms_required = NULL; }
	if (isset($_GET['furnished'])) { $furnished = $_GET['furnished']; } else { $furnished = NULL; }
	if (isset($_GET['bedrooms_double'])) { $bedrooms_double = $_GET['bedrooms_double']; } else { $bedrooms_double = NULL; }
	if (isset($_GET['shared_lounge_area'])) { $shared_lounge_area = $_GET['shared_lounge_area']; } else { $shared_lounge_area = NULL; }
	if (isset($_GET['dish_washer'])) { $dish_washer = $_GET['dish_washer']; } else { $dish_washer = NULL; }
	if (isset($_GET['central_heating'])) { $central_heating = $_GET['central_heating']; } else { $central_heating = NULL; }
	if (isset($_GET['tumble_dryer'])) { $tumble_dryer = $_GET['tumble_dryer']; } else { $tumble_dryer = NULL; }
	if (isset($_GET['washing_machine'])) { $washing_machine = $_GET['washing_machine']; } else { $washing_machine = NULL; }
	if (isset($_GET['ensuite_bathroom'])) { $ensuite_bathroom = $_GET['ensuite_bathroom']; } else { $ensuite_bathroom = NULL; }
	if (isset($_GET['garden_or_terrace'])) { $garden_or_terrace = $_GET['garden_or_terrace']; } else { $garden_or_terrace = NULL; }
	if (isset($_GET['parking'])) { $parking = $_GET['parking']; } else { $parking = NULL; }
	if (isset($_GET['bicycle_store'])) { $bicycle_store = $_GET['bicycle_store']; } else { $bicycle_store = NULL; }
	if (isset($_GET['suit_gender'])) { $suit_gender = $_GET['suit_gender']; } else { $suit_gender = NULL; }
	if (isset($_GET['suit_average_age'])) { $suit_average_age = $_GET['suit_average_age']; } else { $suit_average_age = NULL; }
	if (isset($_GET['suit_student'])) { $suit_student = $_GET['suit_student']; } else { $suit_student = NULL; }
	if (isset($_GET['suit_mature_student'])) { $suit_mature_student = $_GET['suit_mature_student']; } else { $suit_mature_student = NULL; }
	if (isset($_GET['suit_professional'])) { $suit_professional = $_GET['suit_professional']; } else { $suit_professional = NULL; }
	if (isset($_GET['suit_married_couple'])) { $suit_married_couple = $_GET['suit_married_couple']; } else { $suit_married_couple = NULL; }	
	if (isset($_GET['suit_family'])) { $suit_family = $_GET['suit_family']; } else { $suit_family = NULL; }
	if (isset($_GET['current_max_members'])) { $current_max_members = $_GET['current_max_members']; } else { $current_max_members = NULL; }
	if (isset($_GET['current_average_age'])) { $current_average_age = $_GET['current_average_age']; } else { $current_average_age = NULL; }
	if (isset($_GET['current_gender'])) { $current_gender = $_GET['current_gender']; } else { $current_gender = NULL; }
	if (isset($_GET['current_males'])) { $current_males = $_GET['current_males']; } else { $current_males = NULL; }
	if (isset($_GET['current_females'])) { $current_females = $_GET['current_females']; } else { $current_females = NULL; }
	if (isset($_GET['current_occupation'])) { $current_occupation = $_GET['current_occupation']; } else { $current_occupation = NULL; }
	if (isset($_GET['current_students'])) { $current_students = $_GET['current_students']; } else { $current_students = NULL; }
	if (isset($_GET['current_mature_students'])) { $current_mature_students = $_GET['current_mature_students']; } else { $current_mature_students = NULL; }
	if (isset($_GET['current_professionals'])) { $current_professionals = $_GET['current_professionals']; } else { $current_professionals = NULL; }
	if (isset($_GET['owner_lives_in'])) { $owner_lives_in = $_GET['owner_lives_in']; } else { $owner_lives_in = NULL; }
	if (isset($_GET['current_is_couple'])) { $current_is_couple = $_GET['current_is_couple']; } else { $current_is_couple = NULL; }
	if (isset($_GET['current_is_family'])) { $current_is_family = $_GET['current_is_family']; } else { $current_is_family = NULL; }
	if (isset($_GET['church_reference'])) { $church_reference = $_GET['church_reference']; } else { $church_reference = NULL; }
	if (isset($_GET['shared_adult_members'])) { $shared_adult_members = $_GET['shared_adult_members']; } else { $shared_adult_members = NULL; }
	if (isset($_GET['shared_average_age'])) { $shared_average_age = $_GET['shared_average_age']; } else { $shared_average_age = NULL; }
	if (isset($_GET['shared_gender'])) { $shared_gender = $_GET['shared_gender']; } else { $shared_gender = NULL; }
	if (isset($_GET['shared_student'])) { $shared_student = $_GET['shared_student']; } else { $shared_student = NULL; }
	if (isset($_GET['shared_mature_student'])) { $shared_mature_student = $_GET['shared_mature_student']; } else { $shared_mature_student = NULL; }
	if (isset($_GET['shared_professional'])) { $shared_professional = $_GET['shared_professional']; } else { $shared_professional = NULL; }
	
	// Which type of summary to show?
	// ad, church, quick?
	if (isset($_GET['summary_type'])) { $summary_type = $_GET['summary_type']; } else { $summary_type = "ad"; }
					
	// The "Search by church" name & url variables
	if (isset($_GET['church_url'])) { $church_url = trim($_GET['church_url']); } else { $church_url = NULL; }
	if (isset($_GET['church_type'])) { $church_type = trim($_GET['church_type']); } else { $church_type = NULL; }
	if (isset($_GET['church_name'])) { $church_name = trim($_GET['church_name']); } else { $church_name = NULL; }
	if (isset($_GET['church_acronym'])) { $church_acronym = $_GET['church_acronym']; } else { $church_acronym = NULL; }	
	if (isset($_GET['location'])) { $location = $_GET['location']; } else { $location = NULL; }
	
	// Create the sqlAlias, either "w" or "o"
	$sqlAlias = substr($post_type,0,1);
	
	// Act according to the specified search_type
	// I'm using consequtive IF statements instead of a SWITCH because to accommodate situations
	// where the search_type needs to change on the fly (i.e. an arbitrary place name resolves to a single
	// town name in which case search_type changes from "place" to "town".
	
	// GEO search
	// If an random "geo" has been supplied, choose whether to drill-down or modify main query
    
    // ==============================
    // GEO ENCODING
    // ==============================
    if ($search_type == "geo") {
        // Place searches should now return lat/lng data

        // Get the Lat Lng data
        if (isset($_GET['lat']) && !empty($_GET['lat'])) {
            $latitude = $_GET['lat'];
        }
        else {
            $latitude = NULL;
        }

        if (isset($_GET['lng']) && !empty($_GET['lng'])) {
            $longitude = $_GET['lng'];
        }
        else {
            $longitude = NULL;
        }
	
    	$hiddenFields .= '<input type="hidden" name="place" value="'.$place.'" />'."\n";
	
    	// If we only have ONE place (i.e. $drillDown is emtpy) and $error['place'] is not set,
    	// get the longitude and lattitude of that place and extend the main query to include
    	// the triangulation
    	if (!$drillDown && !$error) {

            $sqlTableExt = NULL;
            $sqlWhereExt = NULL;

            switch($post_type) {
                case 'offered':
                    $earthDistanceSQL = $geoHelper->earth_distance_sql($latitude, $longitude, 'o');
                    $sqlSelectExt = ", " . $earthDistanceSQL . " as distance ";
                    $sqlWhereExt .= "and " . $earthDistanceSQL . " < " . ($radius * 1609) . " and latitude is not NULL and longitude is not NULL \n";
                    break;
                case 'wanted':
                    $earthDistanceSQL = $geoHelper->earth_distance_sql($latitude, $longitude, 'w');
                    $sqlSelectExt = ", " . $earthDistanceSQL . " as distance ";
                    $sqlWhereExt .= "and " . $earthDistanceSQL . " < " . ($radius * 1609) . " and latitude is not NULL and longitude is not NULL \n";
                    break;
            }
    	}

    	if ($post_type == "offered") {
    		$searchHeader = "Results within a <strong>".$radius."</strong> mile radius from <strong>" . strtoupper($place) . "</strong>";
    	}
        else {
    		$searchHeader = "Showing all wanted ads interested in accommodation near <strong>" . strtoupper($place) . "</strong>";
    	}
    }
	
    // ==============================
    // INTERNATIONAL AREA SEARCH
    // ==============================
    if ($search_type == "intarea") {
    	if (!$drillDown && !$error) {
            $sqlTableExt = NULL;
            $sqlWhereExt = NULL;
            $sqlSelectExt = NULL;
            
            if ($_GET['area'] == 'all') {
                $sqlWhereExt .= "and region = '" . $_GET['region'] . "' \n";
            }
            else {
                $sqlWhereExt .= "and area = '" . $_GET['area'] . "' and region = '" . $_GET['region'] . "' \n";
            }
    	}
        
    	if ($post_type == "offered") {
    		$searchHeader = "Results within a <strong>".$radius."</strong> mile radius from <strong>" . strtoupper($place) . "</strong>";
    	}
        else {
    		$searchHeader = "Showing all wanted ads interested in accommodation near <strong>" . strtoupper($place) . "</strong>";
    	}
    }
    
	// ARBITRARY PLACE search
	// If an random "place" has been supplied, choose whether to drill-down or modify main query
	if ($search_type == "place") {
	
		// If the result comes from the autocomplete field we have two cases
		// 1. W9 Maida Hill, Greater London - postcode first
		// 2. Maida Vale, Greater London (W9) - postcode last in parentheses
		if (preg_match('/^([A-Z]{1,2}[0-9][A-Z0-9]?).*$/i',$place,$matches)) {
			$place = $matches[1];
		}	
		if (preg_match('/^.* \(([A-Z]{1,2}[0-9][A-Z0-9]?)\)$/i',$place,$matches)) {
			$place = $matches[1];
		}
		
		$hiddenFields .= '<input type="hidden" name="place" value="'.$place.'" />'."\n";
	
		// STEP 1.1: Establish if place is a fully formed UK postcode
		if (preg_match(REGEXP_UK_POSTCODE,$place,$matches)) {
			$place = $matches[1]; // Replace the full postcode with it's first part
		
		// STEP 1.2: Establish if place is the first part of a valid UK postcode
		} else if (preg_match(REGEXP_UK_POSTCODE_FIRST_PART,$place)) {
			// Do nothing
		
		// STEP 1.3: Establish if place is a valid town / area name
		} else {
			
			$pageTitle = "Searching for &quot;".ucwords($place)."&quot;";
			
			// There is a chance we will be drilling-down. Create the link for it.
			$link  = '?search_type=place&post_type='.$post_type;
			
			foreach($_GET as $key=>$value) {
				if (substr($key,0,6) != "button" && $key != "post_type") {
					$link .= "&".$key."=".$value;
				}
			}			
			//	Find matches for $place - exception made for "London"
			$query = "
				select p.place_id,p.place_name,p.postcode,j.county
				from cf_uk_places as `p`
				left join cf_jibble_postcodes as `j` on j.postcode = p.postcode
				where p.place_name like if(strcmp('".$place."','London')=0,'London, City Centre','".$place."%')
				order by j.county asc, p.place_name, p.postcode asc 
				limit 0,50;				
			";
			$debug .= debugEvent("Lookup query for place:",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if (mysqli_num_rows($result)) {
				// If we have multiple results, display a list
				if (mysqli_num_rows($result) > 1) {
					if (mysqli_num_rows($result) == 50) {
						$drillDown  = '<p>Please select one of the following matches, grouped by county. Results are limited to 50 matches.</p>';
					} else {
						$drillDown  = '<p>Please select one of the following matches, grouped by county.</p>';
					}
					$tempCounty = "";
					$drillDown .= '<ul>';
					while($row = mysqli_fetch_assoc($result)) {
						$drillDown .= '<li';
						if ($tempCounty != $row['county']) { $drillDown .= ' class="mt10"'; }
						$drillDown .= '>';
						$drillDown .= '<a href="'.$_SERVER['PHP_SELF'].$link.'&place='.$row['postcode'].'">';
						$drillDown .= $row['place_name'].', '.$row['county'].' ('.$row['postcode'].')';
						$drillDown .= '</a>';
						$drillDown .= '</li>';
						$tempCounty = $row['county'];
						
					}
					$drillDown .= '</ul>';
				} else {
					// We only have one result, store the postcode in the $place variable.
					$place = cfs_mysqli_result($result,0,2);
				}
			} else {
				// We do NOT have any results. Do a SOUNDEX search
				$query = "
					select p.place_id,p.place_name,p.postcode,j.county
					from cf_uk_places as `p`
					left join cf_jibble_postcodes as `j` on j.postcode = p.postcode
					where p.place_name sounds like '%".$place."%'
					order by j.county asc, p.place_name, p.postcode asc 
					limit 0,50;				
				";
				$debug .= debugEvent("Soundex query for place:",$query);
				$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				if (mysqli_num_rows($result)) {
					// Always create a list, even if we have only one match
					if (mysqli_num_rows($result) == 50) {
						$drillDown  = '<p>Please select one of the following matches, grouped by county. Results are limited to 50 matches.</p>';
					} else {
						$drillDown  = '<p>Please select one of the following matches, grouped by county.</p>';
					}
					$tempCounty = "";
					$drillDown .= '<ul>';
					while($row = mysqli_fetch_assoc($result)) {
						$drillDown .= '<li';
						if ($tempCounty != $row['county']) { $drillDown .= ' class="mt10"'; }
						$drillDown .= '>';
						$drillDown .= '<a href="'.$_SERVER['PHP_SELF'].$link.'&place='.$row['postcode'].'">';
						$drillDown .= $row['place_name'].', '.$row['county'].' ('.$row['postcode'].')';
						$drillDown .= '</a>';
						$drillDown .= '</li>';
						$tempCounty = $row['county'];
					}
					$drillDown .= '</ul>';
				} else {
					// No results found even with the SOUNDEX search.
					$error = '<p class="error">No results where found matching the location &quot;'.$place.'&quot;</p>';
					$error .= '<h2>Suggestions:</h2>';
					$error .= '<ul>';
					$error .= '<li>Make sure the place name was spelled correctly</li>';
					$error .= '<li>Check your postcode format</li>';
					$error .= '</ul>';
				}				
			}
						
		}
		
		// If we only have ONE place (i.e. $drillDown is emtpy) and $error['place'] is not set,
		// get the longitude and lattitude of that place and extend the main query to include
		// the triangulation
		if (!$drillDown && !$error) {
			$result = mysqli_query($GLOBALS['mysql_conn'], "select x,y from cf_jibble_postcodes where postcode = '".$place."'");
			if (!mysqli_num_rows($result)) {
				// If the supplied postcode somehow DOES NOT match what is in our jibble database
				$error = 'We cannot find any UK postcodes that start with <strong>'.$place.'</strong>.';				
			} else {
				list($x,$y) = mysqli_fetch_row($result);
				/* 
				 * Now add the various extensions to the final SQL statement which should look something like this:
				 *
				 * select * from cf_offered
				 * left join cf_jibble_postcodes on cf_jibble_postcodes.postcode = SUBSTRING_INDEX(cf_offered.postcode,' ',1)
				 * where published = '1' and suspended = '0' and (sqrt(power((x-525500),2)+power((y-182400),2)) < 8045) order by created_date desc limit 0, 10;
				 *
				 */
				$sqlSelectExt = ",sqrt(power((x-".$x."),2)+power((y-".$y."),2)) as `distance`,j.town ";
				$sqlTableExt = "left join cf_jibble_postcodes as `j` on j.postcode = ";
				if ($post_type == "offered") {
					$sqlTableExt .= "SUBSTRING_INDEX(o.postcode,' ',1)";
				} else if ($post_type == "wanted") {
					$sqlTableExt .= "w.postcode";
				}
				if ($post_type == "offered") {
					$sqlWhereExt .= "and sqrt(power((x-".$x."),2)+power((y-".$y."),2)) < ".($radius * 1609)." \n";
				} else { 
					$sqlWhereExt .= "and sqrt(power((x-".$x."),2)+power((y-".$y."),2)) < (w.distance_from_postcode * 1609) \n";
				}
			}
			
		}
		
		if ($post_type == "offered") {
			$searchHeader = "Results within a <strong>".$radius."</strong> mile radius from <strong>".strtoupper($place)."</strong>";
		} else {
			$searchHeader = "Showing all wanted ads interested in accommodation near <strong>".strtoupper($place)."</strong>";
		}
	
	}	
	
	// AREA search
	// User has clicked on one of the quicklinks on the index page
	if ($search_type == "area") {
		
		// 1. Ensure that the necessary variables are defined
		if (!$area) { header("Location: index.php?warn=No area defined"); exit; }
		
		// 2. We will be presenting a drill down of all counties in that area
		$pageTitle = "Listing all counties in ".$area." with accommodation ".$post_type;
	
		/* 
			We will need to do a drill-down to the "county" level
			using the cf_jibble_postcodes table. The queries used are:
			
			OFFERED AD:
			
				select j.county,count(j.county) as `num`
				from cf_offered as `o`
				left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
				where o.published = '1' and o.suspended = '0' and j.area = 'Greater London'
				group by j.county
				order by num desc,j.county asc;	
				
			WANTED AD:
			
				select j.county,count(j.county) as `num`
				from cf_wanted as `w`
				left join cf_jibble_postcodes as `j` on j.postcode = w.postcode
				where w.published = '1' and w.suspended = '0' and j.area = 'East'
				group by j.county
				order by num desc,j.county asc;		
		
		*/
		
		$query  = "select j.county,count(j.county) as `total` ";
		if ($post_type == "offered") {
			$query .= "from cf_offered as `o` ";
			$query .= "left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) ";
			$query .= "where o.published = '1' and o.suspended = '0' and o.expiry_date >= now() ";
			// if logged in, hide hidden ads
			if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 1) {
		     $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and o.offered_id = ad_id  and post_type = 'offered' and active=2) "; 
			 }
		} else {
			$query .= "from cf_wanted as `w` ";
			$query .= "left join cf_jibble_postcodes as `j` on j.postcode = w.postcode ";
			$query .= "where w.published = '1' and w.suspended = '0' and w.expiry_date >= now() ";
			if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 1) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id = ad_id  and post_type = 'wanted' and active=2) ";			
			}
		}
		$query .= "and j.area = '".$area."' ";
		$query .= "group by j.county ";
		$query .= "order by j.county asc, total desc;";
		
		// Contruct the drill-down list	
		$debug .= debugEvent("Construct the dril-down list(2):",$query);				 
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (!$result || mysqli_num_rows($result) == 0) {
			
			// An erroneous area name was supplied.
			// Clear the drillDown data and alert the user.
			$drillDown = NULL;
			$error  = '<p class="error">We cannot find an area named <strong>&quot;'.$area.'&quot;</strong> in our database.</p>';
			$error .= '<h2>Suggestions:</h2>';
			$error .= '<p style="margin-bottom:100px;">Return to the <a href="index.php">welcome page</a> and choose an area from the &quot;Quick Links&quot; section.</p>';
		
		} else {
			
			// First add the image 
			$mapLink = strtolower(preg_replace("/\s/","-",$area));
			$drillDown  = '<div class="mt10">';
			$drillDown .= '<div class="quickLinksMap"><img src="images/maps/quick-links-'.$mapLink.'.gif" alt="Map of '.$area.'" /></div>';
		
			// Secondly add the list of counties
			$drillDown .= '<div style="float:left;">';
			$drillDown .= '<p>Counties for <strong>'.$area.'</strong> with ads:</p>';
			$drillDown .= '<ul>';			
			while($data = mysqli_fetch_assoc($result)) {
				$drillDown .= '<li>';
				$drillDown .= '<a href="display.php?search_type=county&post_type='.$post_type.'&county='.$data['county'].'">';
				$drillDown .= $data['county'].' ('.$data['total'].')';
				$drillDown .= '</a>';
				$drillDown .= '</li>';
			}
			$drillDown .= '</ul>';
			$drillDown .= '<p><a href="display.php?search_type=all_counties&post_type='.$post_type.'&area='.$area.'">Show all of the above</a></p>'."\n";									
			$drillDown .= '</div>';
			$drillDown .= '<div class="clear"><!----></div>';
			$drillDown .= '</div>';
		}
	
	}
	
	// INTERACTIVE MAP area search
	// User has clicked on an area on the interactive map on the index page.
	// Show both offered and wanted ads for that area
	if ($search_type == "map") {
		
		// 1. Ensure that the necessary variables are defined
		if (!$area) { header("Location: index.php"); exit; }
		
		// 2. We will be presenting a drill down of all counties in that area
		$pageTitle = "Listing all counties in ".$area;
	
		/* 
			We will need to do a drill-down to the "county" level
			using the cf_jibble_postcodes table. The queries used are:
			
			OFFERED AD:
			
				select j.county,count(j.county) as `num`
				from cf_offered as `o`
				left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
				where o.published = '1' and o.suspended = '0' and j.area = 'Greater London'
				group by j.county
				order by num desc,j.county asc;	
				
			WANTED AD:
			
				select j.county,count(j.county) as `num`
				from cf_wanted as `w`
				left join cf_jibble_postcodes as `j` on j.postcode = w.postcode
				where w.published = '1' and w.suspended = '0' and j.area = 'East'
				group by j.county
				order by num desc,j.county asc;		
		
		*/
		
		// First find out all offered ads in this area
		$query  = "
			select j.county,count(j.county) as `total`
			from cf_offered as `o`
			left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
			where o.published = '1' and o.suspended = '0' and o.expiry_date >= now()
			and j.area = '".$area."' ";
		if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and o.offered_id=ad_id and post_type = 'offered' and active=2) ";			
		}
		$query .= "group by j.county 
				   order by j.county asc, total desc;";
		$debug .= debugEvent("Lookup query for place:",$query);				   
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result) != 0) {
			$offeredResults  = '<p><strong>Offered ads</strong>, listed by county:</p>';
			$offeredResults .= '<ul>';			
			while($data = mysqli_fetch_assoc($result)) {
				$offeredResults .= '<li>';
				$offeredResults .= '<a href="display.php?search_type=county&post_type=offered&county='.$data['county'].'">';
				$offeredResults .= $data['county'].' ('.$data['total'].')';
				$offeredResults .= '</a>';
				$offeredResults .= '</li>';
			}
			$offeredResults .= '</ul>';			
			$offeredResults .= '<p><a href="display.php?search_type=all_counties&post_type=offered&area='.$area.'">Show all of the above</a></p>'."\n";						
		
		}
		
		// Secondly, find out all wanted ads in this area	
		$query  = "
			select j.county,count(j.county) as `total`
			from cf_wanted as `w`
			left join cf_jibble_postcodes as `j` on j.postcode = w.postcode 
			where w.published = '1' and w.suspended = '0' and w.expiry_date >= now() 
			and j.area = '".$area."' ";
		if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id=ad_id and post_type = 'wanted' and active=2) ";			
		}			
		$query .= "group by j.county 
				   order by j.county asc, total desc;";
		$debug .= debugEvent("Lookup query for place wanted:",$query);				 					 
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result) != 0) {
			$wantedResults  = '<br />';		
			$wantedResults .= '<p><strong>Wanted ads</strong>, listed by county:</p>';
			$wantedResults .= '<ul>';			
			while($data = mysqli_fetch_assoc($result)) {
				$wantedResults .= '<li>';
				$wantedResults .= '<a href="display.php?search_type=county&post_type=wanted&county='.$data['county'].'">';
				$wantedResults .= $data['county'].' ('.$data['total'].')';
				$wantedResults .= '</a>';
				$wantedResults .= '</li>';
			}
			$wantedResults .= '</ul>';			
			$wantedResults .= '<p><a href="display.php?search_type=all_counties&post_type=wanted&area='.$area.'">Show all of the above</a></p>'."\n";						
		}
		
		// Contruct the drill-down list	
		$debug .= debugEvent("Construct the drill-down list(3):",$query);				 
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (!isset($wantedResults) && !isset($offeredResults)) {
			
			// An erroneous area name was supplied.
			// Clear the drillDown data and alert the user.
			$drillDown = NULL;
			$error  = '<p class="error">No results found for <strong>&quot;'.$area.'&quot;</strong> in our database.</p>';
			$error .= '<h2>Suggestions:</h2>';
			$error .= '<p style="margin-bottom:100px;">Return to the <a href="index.php">welcome page</a> and choose an area from the &quot;Quick Links&quot; section.</p>';
		
		} else {
			
			// First add the image 
			$mapLink = strtolower(preg_replace("/\s/","-",$area));
			$drillDown  = '<div class="mt10">';
			$drillDown .= '<div class="quickLinksMap"><img src="images/maps/quick-links-'.$mapLink.'.gif" alt="Map of '.$area.'" /></div>';

			$drillDown .= '<div style="float:left;">';
            
            if (isset($offeredResults)) {
                $drillDown .= $offeredResults;
            }
            
            if (isset($wantedResults)) {
                $drillDown .= $wantedResults;
            }
            
			$drillDown .= '</div>';
			
			$drillDown .= '<div class="clear"><!----></div>';
			$drillDown .= '</div>';
		}
		
		
	}
	
	// LONDON search
	// An exception to the AREA search, for area == "Greater London" we have a special drill-down screen
	if ($search_type == "london") {
		/*
			For London, we need to choose all offered / wanted ads
			and group them by the "london_area" column on jibble_postcodes
			
			The queries for both offered & wanted ads are exactly the same
			as the ones for the $county (see below) with the addition of the "j.london_area" field
			
		*/
		$query  = "select j.town, count(j.town) as `total`, ";
		if ($post_type == "offered") {
			$query .= "SUBSTRING_INDEX(o.postcode,' ',1) as `postcode`,j.london_area ";
			$query .= "from cf_offered as `o` ";
			$query .= "left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) ";
			$query .= "where o.published = '1' and o.suspended = '0'  and o.expiry_date >= now() ";
			if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']."  and o.offered_id=ad_id  and post_type = 'offered' and active=2) ";						
			}
		} else {
			$query .= "w.postcode,j.london_area ";
			$query .= "from cf_wanted as `w` ";
			$query .= "left join cf_jibble_postcodes as `j` on j.postcode = w.postcode ";
			$query .= "where w.published = '1' and w.suspended = '0'  and w.expiry_date >= now() ";
			if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id=ad_id and post_type = 'wanted' and active=2)";						
			}			
		}
		$query .= "and j.county = 'Greater London' ";
		$query .= "group by j.town ";
		$query .= "order by j.london_area asc,j.town asc;";
		$debug .= debugEvent("Greater london - specific query",$query);	
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		
		$drillDown .= '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">';
		$drillDown .= '<tr>';
		$drillDown .= '<td>Please click on an area on the London Map, or scroll down to see the list of adverts and areas:</td>';
		$drillDown .= '<td align="right">Showing all:&nbsp;&nbsp;&nbsp;';
		if ($post_type == "offered") {
			$drillDown .= '<strong>Offered ads</strong>';
		} else {
			$drillDown .= '<a href="display.php?search_type=london&post_type=offered&area=Greater London">Offered ads</a>';
		}
		$drillDown .= '&nbsp;|&nbsp;';
		if ($post_type == "wanted") {
			$drillDown .= '<strong>Wanted ads</strong>';
		} else {
			$drillDown .= '<a href="display.php?search_type=london&post_type=wanted&area=Greater London">Wanted ads</strong>';
		}
		$drillDown .= '</td>';
		$drillDown .= '</tr>';
		$drillDown .= '</table>';		
		
		// Iterate through the results and store everything in an array.
		// First key will be the london_area
		$london = array();
		while($row = mysqli_fetch_assoc($result)) {
			$london[$row['london_area']][] = array (
				"town" => $row['town'],
				"postcode" => $row['postcode'],
				"total" => $row['total']
			);
		}
		//pre($london);
		
		// We will display the london areas in a clockwise fashion from N to NW.
		$londonSort = array (
			"C" => "Central",
			"N" => "North",
			"E" => "East",
			"SE" => "South East",
			"SW" => "South West",
			"W" => "West",
			"NW" => "North West"
		);
		$temp = "";
		
		/*
		foreach($londonSort as $areaCode => $areaName) {
			if (isset($london[$areaCode])) { // Only display the area if we have properties for it
				
				$temp .= '<table cellspacing="10" cellpadding="0" border="0" class="gl_contents" width="100%">';
				// Header row
				$temp .= '<tr>';
					$temp .= '<td><h2>'.$areaName.'</h2></td>';
					$temp .= '<td align="right" colspan="4">';
					// Get all towns in this areaCode
					$link = "&town=";
					foreach($london[$areaCode] as $t) { $link .= $t['town'].","; }
					$link = substr($link,0,-1); // Snip last comma
					// Add the newly created link to the $mapLinks array (to be used when creating the map)
					$mapLinks[$areaCode] = 'display.php?search_type=town&post_type='.$post_type.$link.'&county=Greater%20London';
					$temp .= '<a href="display.php?search_type=town&post_type='.$post_type.$link.'&county=Greater%20London">Show all</a>';
					$temp .= '</td>';
				$temp .= '</tr>'."\n";
				// Table contents
				$temp .= '<tr>';
				$counter = 1;
				foreach($london[$areaCode] as $areaData) {
					if (($counter-1) % 5 == 0) {
						$temp .= '</tr>'."\n";
						$temp .= '<tr>'."\n";
					}
					$temp .= '<td width="20%">'."\n";
					$temp .= '<a href="display.php?search_type=town&post_type='.$post_type.'&town='.$areaData['town'].'&county=Greater%20London">';
					$temp .= $areaData['town'].' ('.$areaData['total'].')';
					$temp .= '</a>'."\n";
					$temp .= '</td>'."\n";
					$counter ++;
				}
				$temp .= '</tr>'."\n";
				$temp .= '</table>'."\n";
				
			}
		}
		*/
		
		$temp .= '<table cellspacing="10" cellpadding="0" border="0" class="gl_contents" width="100%">';
		$temp .= '<tr>';
		
		foreach($londonSort as $areaCode => $areaName) {
			if (isset($london[$areaCode])) { // Only display the area if we have properties for it
				
				$temp .= '<td valign="top">';
				
				// Area name
				$temp .= '<h2 class="m0">'.$areaName.'</h2>';
				
				// "Show all link"
				$link = "&town=";
				foreach($london[$areaCode] as $t) { $link .= $t['town'].","; }
				$link = substr($link,0,-1); // Snip last comma
				// Add the newly created link to the $mapLinks array (to be used when creating the map)
				$mapLinks[$areaCode] = 'display.php?search_type=town&post_type='.$post_type.str_replace("'","%60",$link).'&county=Greater%20London';
				$temp .= '<p class="mt0"><a href="display.php?search_type=town&post_type='.$post_type.str_replace("'","%60",$link).'&county=Greater%20London">Show all</a></p>';
				
				// List of town for this areaCode
				foreach($london[$areaCode] as $areaData) {
					$temp .= '<a href="display.php?search_type=town&post_type='.$post_type.'&town='.str_replace("'","%60",$areaData['town']).'&county=Greater%20London">';
					$temp .= $areaData['town'].' ('.$areaData['total'].')';
					$temp .= '</a><br/>'."\n";
					$counter ++;
				}
				
				$temp .= '</td>'."\n";
				
			}
		}
		
		$temp .= '</tr>';
		$temp .= '</table>';		
		

		// Add the london map
		$drillDown .= '<div id="londonMap">';
		$drillDown .= '<img src="images/map-london-postcodes.jpg" width="848" height="610" border="0" usemap="#London" />'."\n";
		$drillDown .= '<map name="London" id="London">'."\n";
		// NW
		if ($mapLinks['NW']) {
			$drillDown .= '<area shape="poly" coords="4,100,46,107,89,99,153,54,177,66,212,47,228,41,246,43,256,55,260,72,285,76,289,90,290,100,286,111,283,122,283,144,295,149,311,149,318,160,324,171,335,181,350,205,364,206,369,200,380,221,387,232,384,244,390,261,373,272,343,277,320,248,309,248,303,253,290,250,273,256,257,271,228,269,194,256,176,246,170,225,110,205,86,219,71,224,55,233,51,243,5,248" href="'.$mapLinks['NW'].'" />'."\n";
		}
		// N
		if ($mapLinks['N']) {
			$drillDown .= '<area shape="poly" coords="492,407" href="#" /><area shape="poly" coords="254,2,519,3,524,23,519,34,515,50,517,73,506,84,496,110,480,141,471,164,466,200,450,216,447,257,419,255,399,254,399,227,386,200,366,187,355,188,328,142,314,133,290,132,302,97,300,67,267,62,262,39,233,29" href="'.$mapLinks['N'].'" />'."\n";
		}
		// E
		if ($mapLinks['E']) {
			$drillDown .= '<area shape="poly" coords="458,268,462,218,476,209,484,160,512,105,529,73,529,45,547,5,570,4,577,36,582,70,628,100,668,98,675,85,724,68,771,72,794,61,844,43,845,317,810,329,801,299,781,289,761,285,729,274,703,277,668,294,655,307,637,314,585,317,562,302,543,307,547,336,537,347,523,316,512,303,493,303,468,312,461,289" href="'.$mapLinks['E'].'" />'."\n";
		}
		// Central
		if ($mapLinks['C']) {
			$drillDown .= '<area shape="poly" coords="453,263,399,260,378,276,387,289,385,305,392,312,411,305,430,305,460,313" href="'.$mapLinks['C'].'" />'."\n";
		}
		// W
		if ($mapLinks['W']) {
			$drillDown .= '<area shape="poly" coords="373,279,381,290,382,303,363,317,318,319,299,337,287,351,266,346,243,346,231,368,222,388,199,355,200,375,188,404,207,417,225,430,212,462,217,484,212,492,196,478,179,474,174,513,178,541,150,564,119,536,105,538,77,536,82,516,60,505,41,487,4,460,5,260,64,252,64,239,76,231,97,228,112,215,164,233,168,257,193,266,222,277,270,277,281,262,307,259,324,272,343,284" href="'.$mapLinks['W'].'" />'."\n";
		}
		// SW
		if ($mapLinks['SW']) {
			$drillDown .= '<area shape="poly" coords="383,310,391,318,387,350,418,374,416,389,421,404,407,430,409,449,403,484,415,500,407,525,395,555,403,580,413,608,191,608,188,586,172,573,156,573,184,538,186,486,218,502,221,480,226,446,230,424,196,401,206,374,225,396,247,350,269,352,289,360,321,325,365,326" href="'.$mapLinks['SW'].'" />'."\n";
		}
		// SE
		if ($mapLinks['SE']) {
			$drillDown .= '<area shape="poly" coords="407,313,397,349,423,372,429,402,416,436,415,484,421,500,404,560,419,608,759,608,758,564,768,484,797,439,831,374,826,350,788,340,778,310,718,294,683,308,663,328,585,336,561,322,565,348,549,363,511,346,505,324,495,321,482,329" href="'.$mapLinks['SE'].'" />'."\n";
		}
		$drillDown .= '</map>'."\n";
		$drillDown .= '</div>'."\n";
		
		// Add the london areas
		$drillDown .= $temp;
		
		//pre($mapLinks);
	}
	
	// COUNTY search
	// User has clicked on one of the counties in the drilldown results of an area search
	if ($search_type == "county") {
	
		// 1. Ensure that the necessary variables are defined
		if (!$county) { header("Location: index.php?warn=No county defined"); exit; }

		// 1.5 Find out the area of this county and load the appropriate map
		$query = "select area from cf_jibble_postcodes where county = '".$county."';";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
			$tempArea = cfs_mysqli_result($result,0,0);
		}
		
		// 2. Drill down to "town" level
		$pageTitle = "Listing all towns / districts of ".$county." with accommodation ".$post_type;
	
		/* 
			We will need to do a drill-down to the "town" level
			using the cf_jibble_postcodes table. The queries used are:
			
			OFFERED AD:
			
				select j.town,count(j.town) as `total`,SUBSTRING_INDEX(o.postcode,' ',1) as `postcode`
				from cf_offered as `o`
				left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
				where o.published = '1' and o.suspended = '0'  and j.county = 'Greater London'
				group by j.town
				order by total desc,j.town asc;
				
			WANTED AD:
			
				select j.town,count(j.town) as `total`,w.postcode
				from cf_wanted as `w`
				left join cf_jibble_postcodes as `j` on j.postcode = w.postcode
				where w.published = '1' and w.suspended = '0'  and j.county = 'Suffolk'
				group by j.town
				order by total desc,j.town asc;	
		
		*/
	
		$query  = "select j.town, count(j.town) as `total`, ";
		if ($post_type == "offered") {
			$query .= "SUBSTRING_INDEX(o.postcode,' ',1) as `postcode` ";
			$query .= "from cf_offered as `o` ";
			$query .= "left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) ";
			$query .= "where o.published = '1' and o.suspended = '0' and o.expiry_date >= now() ";
			if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and o.offered_id=ad_id and post_type = 'offered' and active=2) ";						
			}

		} else {
			$query .= "w.postcode ";
			$query .= "from cf_wanted as `w` ";
			$query .= "left join cf_jibble_postcodes as `j` on j.postcode = w.postcode ";
			$query .= "where w.published = '1' and w.suspended = '0' and w.expiry_date >= now() ";
			if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= "and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id=ad_id and post_type = 'wanted' and active=2) ";						
			}
		}
		$query .= "and j.county = '".$county."' ";
		$query .= "group by j.town ";
		$query .= "order by j.town asc,total desc; ";
		$debug = debugEvent("Query",$query);
		$drillDown = "";
		
		// First add the image 
		if ($tempArea) {
			$mapLink = strtolower(preg_replace("/\s/","-",$tempArea));
			$drillDown .= '<div class="mt10">';
			$drillDown .= '<div class="quickLinksMap"><img src="images/maps/quick-links-'.$mapLink.'.gif" alt="Map of '.$area.'" /></div>';
			$drillDown .= '<div style="float:left;">';
		}

		// Contruct the drill-down list
		$drillDown .= '<p>Please select one of the towns / districts in <strong>'.$county.'</strong>:</p>';
		$drillDown .= '<ul>';
		$debug .= debugEvent("Construct drill down:",$query);				 
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (!$result || mysqli_num_rows($result) == 0) {
			// An erroneous county name was supplied.
			// Clear the drillDown data and alert the user.
			$drillDown = NULL;
			$error  = '<p class="error">We cannot find a county named <strong>&quot;'.$county.'&quot;</strong> in our database.</p>';
			$error .= '<h2>Suggestions:</h2>';
			$error .= '<ul style="margin-bottom:100px;">';
			$error .= '<li>Return to the <a href="index.php">welcome page</a> and choose an area from the &quot;Quick Links&quot; section.</li>';
			$error .= '<li>Return to the <a href="index.php">welcome page</a> and enter the county name into the &quot;Quick Search&quot; field.</li>';
			$error .= '</ul>';
		} else {
			$tempLink = "";
			while($data = mysqli_fetch_assoc($result)) {
				$tempLink .= $data['town'].",";
				$drillDown .= '<li>';
				$drillDown .= '<a href="display.php?search_type=town&post_type='.$post_type.'&town='.$data['town'].'&county='.$county.'">';
				$drillDown .= $data['town'].' ('.$data['total'].')';
				$drillDown .= '</a>';
				$drillDown .= '</li>';
			}
			$drillDown .= '</ul>';
			$tempLink = substr($tempLink,0,-1);
			$drillDown .= '<p><a href="display.php?search_type=town&post_type='.$post_type.'&town='.$tempLink.'&county='.$county.'"></br>Show all of the above</a></p>'."\n";
			unset($tempLink);
		}
		
		if ($tempArea) {
			$drillDown .= '</div>';
			$drillDown .= '<div class="clear"><!----></div>';
			$drillDown .= '</div>';
		}
			
	}
	
	// TOWN search
	// The town variable can either contain a single town or a list of towns ("Show all of the above" link
	if ($search_type == "town") {
	
		// 1. Ensure that the necessary variables are defined
		if (!$county) { header("Location: index.php?warn=No county defined"); exit; }
		if (!$town) { header("Location: index.php?warn=No town speficied"); exit; }
		
		// 2. Modify the main query to include only results from the given town and county
		$sqlSelectExt = ", j.town";
		$sqlTableExt = "left join cf_jibble_postcodes as `j` on j.postcode = ";
		if ($post_type == "offered") {
			$sqlTableExt .= "SUBSTRING_INDEX(o.postcode,' ',1)";
		} else {
			$sqlTableExt .= "w.postcode";
		}
		// Town could either be a single town name or a comma-separated list of towns
		if (strpos($town,",") !== FALSE) {
			$sqlWhereExt = "and j.town in (";
			$townArray = explode(",",$town);
			foreach($townArray as $value) {
				$sqlWhereExt .= "'".$value."',";
			}
			$sqlWhereExt = substr($sqlWhereExt,0,-1);
			$sqlWhereExt .= ") \n";
			$sqlWhereExt .= "and j.county = '".$county."'";
			$pageTitle = "Showing all accommodation ".$post_type." in ".$town;
		} else {
			$sqlWhereExt = "and j.town = '".$town."' ";
			$sqlWhereExt .= "and j.county = '".$county."'";
			$sqlWhereExt .= "\n";
			$pageTitle = "Showing all accommodation ".$post_type." in ".$town.", ".$county;
		}	
	}

	// ALL COUNTIES search (RD 27-OCT-07)
	// This procudes results at the AREA level, for all the counties with in it. 
	// Its use is primarily for the AREA level listing of COUNTIES, and the "Show all of the above" link
	if ($search_type == "all_counties") {
	
		// 1. Ensure that the necessary variables are defined
		if (!$area) { header("Location: index.php?warn=No area defined"); exit; }
		
		// 2. Modify the main query to include only results from area
		$sqlSelectExt = ", j.area";
		$sqlTableExt = "left join cf_jibble_postcodes as `j` on j.postcode = ";
		if ($post_type == "offered") {
			$sqlTableExt .= "SUBSTRING_INDEX(o.postcode,' ',1)";
		} else {
			$sqlTableExt .= "w.postcode";
		}
		// All ads within the given AREA
			$sqlWhereExt .= "and j.area = '".$area."'";
			$pageTitle = "Showing all accommodation ".$post_type." ads in the area of  ".$area;
	}
	
	// CHURCH search
	if ($search_type == "church") {

		// If church_url was specified
		if ($church_url && $church_url != "http://") {
			$church_url = preg_replace('/http:\/\//','',$church_url);
			// Add the church_url
			$sqlWhereExt .= "and ".$sqlAlias.".church_url like '%".$church_url."%' ";
		} 
		
		// If church_name or church_acronym was specified
		// remove " " "'" "." with
		// REPLACE(REPLACE(REPLACE(church_attended,' ',''), '\'',''), '.','')		
	  $church_name = preg_replace("/\'/","",$church_name);		
	  $church_name = preg_replace("/\./","",$church_name);		
	  $church_name = preg_replace("/ /","",$church_name);		

		if ($church_name && $church_acronym) {
			$sqlWhereExt .= "and (REPLACE(REPLACE(REPLACE(".$sqlAlias.".church_attended,' ',''), '\'',''), '.','') like '%".$church_name."%'  or ".$sqlAlias.".church_attended like '%".$church_acronym."%')";
		} else if ($church_name) {
			$sqlWhereExt .= "and REPLACE(REPLACE(REPLACE(".$sqlAlias.".church_attended,' ',''), '\'',''), '.','') like '%".$church_name."%'  ";
		} else if ($church_acronym) {
			$sqlWhereExt .= "and ".$sqlAlias.".church_attended like '%".$church_acronym."%' ";
		}

		// If a postcode has been specified, modify the main query to do a location - based search
		if ($postcode) {
			$result = mysqli_query($GLOBALS['mysql_conn'], "select x,y from cf_jibble_postcodes where postcode = '".$postcode."'");
			if (!mysqli_num_rows($result)) {
				// If the supplied postcode somehow DOES NOT match what is in our jibble database
				$error = 'We cannot find any UK postcodes that start with <strong>'.$postcode.'</strong>.';				
			} else {
				list($x,$y) = mysqli_fetch_row($result);
				/* 
				 * Now add the various extensions to the final SQL statement which should look something like this:
				 *
				 * select * from cf_offered
				 * left join cf_jibble_postcodes on cf_jibble_postcodes.postcode = SUBSTRING_INDEX(cf_offered.postcode,' ',1)
				 * where published = '1' and suspended = '0'  and (sqrt(power((x-525500),2)+power((y-182400),2)) < 8045) order by created_date desc limit 0, 10;
				 *
				 */
				$sqlSelectExt = ",sqrt(power((x-".$x."),2)+power((y-".$y."),2)) as `distance`,j.town ";
				$sqlTableExt = "left join cf_jibble_postcodes as `j` on j.postcode = ";
				if ($post_type == "offered") {
					$sqlTableExt .= "SUBSTRING_INDEX(o.postcode,' ',1)";
				} else if ($post_type == "wanted") {
					$sqlTableExt .= "w.postcode";
				}
				if ($post_type == "offered") {
					$sqlWhereExt .= "and sqrt(power((x-".$x."),2)+power((y-".$y."),2)) < ".(10 * 1609)." \n";
				} else { 
					$sqlWhereExt .= "and sqrt(power((x-".$x."),2)+power((y-".$y."),2)) < (w.distance_from_postcode * 1609) \n";
				}
			}
		}
		
	}
    
	/***************************** MODIFYING THE MAIN QUERY *****************************/
		
	// MODIFY MAIN QUERY: Quick & Advanced search common elements
	if ($flatshare || $familyshare || $wholeplace) {
		if ($post_type == "offered") {
			$sqlWhereExt .= "and (";
			if ($flatshare) { $sqlWhereExt .= "o.accommodation_type = 'flat share' or "; }
			if ($familyshare) { $sqlWhereExt .= "o.accommodation_type = 'family share' or "; }
			if ($wholeplace) { $sqlWhereExt .= "o.accommodation_type = 'whole place' or "; }
			$sqlWhereExt = substr($sqlWhereExt,0,-3);
			$sqlWhereExt .= ") \n";
		} else if ($post_type == "wanted") {
			$sqlWhereExt .= "and (";
			if ($flatshare) { $sqlWhereExt .= "w.accommodation_type_flat_share = 1 or "; }
			if ($familyshare) { $sqlWhereExt .= "w.accommodation_type_family_share = 1 or "; }
			if ($wholeplace) { $sqlWhereExt .= "w.accommodation_type_whole_place = 1 or "; }
			$sqlWhereExt = substr($sqlWhereExt,0,-3);
			$sqlWhereExt .= ") \n";
		}
	}
	if ($pcm && $pcm != "Any") { $sqlWhereExt .= "and price_pcm <= '".$pcm."' \n"; }
	
	// MODIFY MAIN QUERY: Advanced search elements for both offered and wanted ads
	if ($available_date) 		{ $sqlWhereExt .= "and available_date <= '".$available_date."' \n"; }
	if ($max_term) 				{ $sqlWhereExt .= "and min_term <= ".$max_term." \n"; }
	if ($shared_lounge_area) 	{ $sqlWhereExt .= "and shared_lounge_area = 1 \n"; }
	if ($dish_washer) 			{ $sqlWhereExt .= "and dish_washer = 1 \n"; }
	if ($central_heating) 		{ $sqlWhereExt .= "and central_heating = 1 \n"; }
	if ($tumble_dryer) 			{ $sqlWhereExt .= "and tumble_dryer = 1 \n"; }
	if ($washing_machine) 		{ $sqlWhereExt .= "and washing_machine = 1 \n"; }
	if ($ensuite_bathroom) 		{ $sqlWhereExt .= "and ensuite_bathroom = 1 \n"; }
	if ($garden_or_terrace) 	{ $sqlWhereExt .= "and garden_or_terrace = 1 \n"; }
	if ($bicycle_store) 		{ $sqlWhereExt .= "and bicycle_store = 1 \n"; }

	// MODIFY MAIN QUERY: Advanced search elements for offered ads
	if ($post_type == "offered") {
	
		if ($parking) 				{ $sqlWhereExt .= "and parking != 'None' \n"; }
	
		// "Tell us about the accommodation you're looking for" pane
		if ($building_type)			{ $sqlWhereExt .= "and o.building_type = '".$building_type."' \n"; }
		if ($bedrooms_required) 	{ $sqlWhereExt .= "and o.bedrooms_available >= ".$bedrooms_required." \n"; }
		if ($bedrooms_double) 		{ $sqlWhereExt .= "and o.bedrooms_double >= ".$bedrooms_double." \n"; }

		// "Tell us about the accommodation seekers" pane
		if ($suit_average_age) 		{ $sqlWhereExt .= "and o.suit_min_age <= '".$suit_average_age."' and (o.suit_max_age = 0 || o.suit_max_age >= '".$suit_average_age."') \n"; }
		if ($suit_student) 			{ $sqlWhereExt .= "and o.suit_student = 1 \n"; }
		if ($suit_mature_student) 	{ $sqlWhereExt .= "and o.suit_mature_student = 1 \n"; }
		if ($suit_professional) 	{ $sqlWhereExt .= "and o.suit_professional = 1 \n"; }
		if ($suit_married_couple) 	{ $sqlWhereExt .= "and o.suit_married_couple = 1 \n"; }
		if ($suit_family) 			{ $sqlWhereExt .= "and o.suit_family = 1 \n"; }

		// "Tell us who you'd like to share with:" pane
		if ($current_max_members) 	{ $sqlWhereExt .= "and (o.current_num_males + o.current_num_females) <= ".$current_max_members." \n"; }
		if ($current_average_age)	{ $sqlWhereExt .= "and o.current_min_age <= '".$current_average_age."' and (o.current_max_age = 0 || o.current_max_age >= '".$current_average_age."') \n"; }
		if ($current_gender)		{
			switch($current_gender) {
				case "Male(s)":
					$sqlWhereExt .= "and o.current_num_females = 0 \n";
					break;
				case "Female(s)":
					$sqlWhereExt .= "and o.current_num_males = 0 \n";
					break;
			}
		}
		// If the following have been specified, it means exclusion
		if ($current_students)			{ $sqlWhereExt .= "and (o.current_occupation = '' || o.current_occupation != 'Students (<22yrs)') \n"; }
		if ($current_mature_students)	{ $sqlWhereExt .= "and (o.current_occupation = '' || o.current_occupation != 'Mature Students') \n"; }
		if ($current_professionals)		{ $sqlWhereExt .= "and (o.current_occupation = '' || o.current_occupation != 'Professionals') \n"; }
		if ($owner_lives_in)			{ $sqlWhereExt .= "and o.owner_lives_in != 1 \n"; }
		if ($current_is_couple)			{ $sqlWhereExt .= "and o.current_is_couple != 1 \n"; }
		if ($current_is_family)			{ $sqlWhereExt .= "and o.current_is_family != 1 \n"; }
		
	}
	
	if ($post_type == "wanted") {
	
		if ($parking) 				{ $sqlWhereExt .= "and parking = 1 \n"; }
		
		if ($min_term)				{ $sqlWhereExt .= "and (w.min_term = 0 || w.min_term >= ".$min_term.") \n"; }
		if ($building_type)			{ 
			if ($building_type == "house") {
				$sqlWhereExt .= "and w.building_type_flat != '1' \n"; 
			} else {
				$sqlWhereExt .= "and w.building_type_house != '1' \n"; 
			}
		}
		if ($bedrooms_required) 	{ $sqlWhereExt .= "and w.bedrooms_required >= ".$bedrooms_required." \n"; }
		if ($furnished)				{ $sqlWhereExt .= "and w.furnished = ".$furnished." \n"; }
		
		// "Tell us about the household wanted:" pane
		if ($shared_adult_members)	{ $sqlWhereExt .= "and w.shared_adult_members <= '".$shared_adult_members."' \n"; }
		if ($shared_average_age)	{ $sqlWhereExt .= "and w.shared_min_age <= '".$shared_average_age."' and (w.shared_max_age = 0 || w.shared_max_age >= '".$shared_average_age."') \n"; }
		if ($shared_gender)	{
			switch($shared_gender) {
				case "Mixed": $sqlWhereExt .= "and w.shared_mixed = 1 \n"; break;
				case "Male(s)": $sqlWhereExt .= "and w.shared_males = 1 \n"; break;
				case "Female(s)": $sqlWhereExt .= "and w.shared_females = 1 \n"; break;
			}
		}
		
		if ($shared_student)			        { $sqlWhereExt .= "and w.shared_student = 1 \n"; }
		if ($shared_mature_student)		        { $sqlWhereExt .= "and w.shared_matured_student = 1 \n"; }
		if ($shared_professional)		        { $sqlWhereExt .= "and w.shared_professional = 1 \n"; }
		if (isset($shared_owner_lives_in))		{ $sqlWhereExt .= "and w.shared_owner_lives_in = 1 \n"; }
		if (isset($shared_married_couple))		{ $sqlWhereExt .= "and w.shared_married_couple = 1 \n"; }
		if (isset($shared_family))				{ $sqlWhereExt .= "and w.shared_family = 1 \n"; }
		
		// "Tell us about the accommodation seekers:" pane
		if ($current_average_age)		{ $sqlWhereExt .= "and w.current_min_age <= '".$current_average_age."' and (w.current_max_age = 0 || w.current_max_age >= '".$current_average_age."') \n"; }
		if ($current_occupation)		{ $sqlWhereExt .= "and w.current_occupation = '".$current_occupation."' \n"; }		
		if ($current_is_couple)			{ $sqlWhereExt .= "and w.current_is_couple = 1 \n"; }
		if ($current_is_family)			{ $sqlWhereExt .= "and w.current_is_family = 1 \n"; }
		if ($current_is_couple)			{ $sqlWhereExt .= "and w.current_is_couple = 1 \n"; }
		if ($church_reference)			{ $sqlWhereExt .= "and w.church_reference = 1 \n"; }		
	}
	
	// Add the number of photos for all ads to the query
	$sqlSelectExt .= ", (select count(*) from cf_photos where post_type = '".$post_type."' and ad_id = ";
	if ($post_type == "offered") {
		$sqlSelectExt .= "o.offered_id";
	} else {
		$sqlSelectExt .= "w.wanted_id";
	}
	$sqlSelectExt .= ") as `photos`";
	
	// Add the number of days since this ad has been posted
	$sqlSelectExt .= ", DATEDIFF(curdate(),created_date) as `ad_age` ";	
	
	// If the "Would suit" or "To suit" filtering control has been selected
	if ($sortSuit) {
		if ($post_type == "offered") {
			if ($sortSuit == "Couple") {
				$sqlWhereExt .= "and o.suit_married_couple = '1'\n";
			} elseif ($sortSuit == "Family") {
				$sqlWhereExt .= "and o.suit_family = '1'\n";
			} else {
				$sqlWhereExt .= "and o.suit_gender in ('".$sortSuit."','Mixed')\n";
			}
		} else {
			// IF user has specified "to suit female(s)" then current_num_males must be zero
			if ($sortSuit == "Couple") {
				$sqlWhereExt .= "and w.current_is_couple = '1'\n";
			} elseif ($sortSuit == "Family") {
				$sqlWhereExt .= "and w.current_is_family = '1'\n";
			} elseif ($sortSuit == "Female") {
				$sqlWhereExt .= "and w.current_num_males = 0\n";
			} else if ($sortSuit == "Male") {
				$sqlWhereExt .= "and w.current_num_females = 0\n";
			}
		}
	}
	
	// The "number of bedrooms" filter:
	if (!$sortBed1 || !$sortBed2 || !$sortBed3 || !$sortBed4) {
		// i.e. if the user has opted to NOT show ads with a certain number of bedrooms
		if ($post_type == "offered") {
			if (!$sortBed1) { $sqlWhereExt .= "and o.bedrooms_available != 1\n"; }
			if (!$sortBed2) { $sqlWhereExt .= "and o.bedrooms_available != 2\n"; }
			if (!$sortBed3) { $sqlWhereExt .= "and o.bedrooms_available != 3\n"; }
			if (!$sortBed4) { $sqlWhereExt .= "and o.bedrooms_available < 4\n"; }
		} else {
			if (!$sortBed1) { $sqlWhereExt .= "and w.bedrooms_required != 1\n"; }
			if (!$sortBed2) { $sqlWhereExt .= "and w.bedrooms_required != 2\n"; }
			if (!$sortBed3) { $sqlWhereExt .= "and w.bedrooms_required != 3\n"; }
			if (!$sortBed4) { $sqlWhereExt .= "and w.bedrooms_required < 4\n"; }
		}
	}
	
	// Short-term filter:
	if ($sortShortTerm == 1) {
			if ($post_type == "offered") {
			 $sqlWhereExt .= "and o.max_term <= 12\n"; 
		} else {
			 $sqlWhereExt .= "and w.max_term <= 12\n"; 		
		}
	}
	/*************************** END MODIFYING THE MAIN QUERY ***************************/
	
	// EXECUTE MAIN QUERY
	// Only if we do not have $drilldown or $error data
	if (!$drillDown && !$error) {
		// If a link from Your Ads, to show matching wanted ads or offerd ads
		// Wanted ads are shown if they match the requirements of an offered ad
		// Offered ads are shown if they maych the requirements of a wanted ad
		// "ad_matches" search type is called with AD_ID and AD_TYPE
		if ($search_type == "ad_matches") {
			if ($match_post_type == "wanted") {
				// Wanted ad passed in, return offered ads
				$matches_query_top = "			
							SELECT o.*, 
								 (CASE IFNULL(o.town_chosen,'')
									WHEN '' THEN j1.town 
									ELSE o.town_chosen
									END) as town,
									DATEDIFF(curdate(),o.created_date) as `ad_age`, 									
								 (select DATEDIFF(curdate(),last_login)  from cf_users where cf_users.user_id = o.user_id) last_login_days,
								s.active as `active` ";
				
			// Add the number of photos for all ads to the query
				$matches_query_top .= ", (select count(*) from cf_photos where post_type = '".$post_type."' and ad_id = o.offered_id) as `photos`";								
				
				$matches_query_top_count = "select ".$sqlAlias.".".$post_type."_id ";								
				
				$matches_query_bottom = "	FROM  cf_offered as `o`
							INNER JOIN cf_wanted as `w`
							LEFT JOIN cf_jibble_postcodes as `j1` on j1.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
							LEFT JOIN cf_jibble_postcodes as `j2` on j2.postcode = SUBSTRING_INDEX(w.postcode,' ',1)
							LEFT JOIN cf_users as `u` on u.user_id = w.user_id
							LEFT JOIN cf_saved_ads as `s` 
											on s.ad_id = o.offered_id and 
											s.post_type = 'offered' and 
											s.user_id = '".$_SESSION['u_id']."'
						";
				} else {  // match_post_type = offered, return wanted ads
					$matches_query_top = "	
									SELECT w.*, 
									DATEDIFF(curdate(),w.created_date) as `ad_age`, 
								 	(select DATEDIFF(curdate(),last_login)  from cf_users where cf_users.user_id = w.user_id) last_login_days,
									s.active as `active`	";
					$matches_query_top .= ", (select count(*) from cf_photos where post_type = '".$post_type."' and ad_id = w.wanted_id) as `photos`";								
									
									
					$matches_query_top_count = "select ".$sqlAlias.".".$post_type."_id ";
					$matches_query_bottom = "	
							FROM  cf_wanted as `w`
							INNER JOIN cf_offered as `o`
							LEFT JOIN cf_jibble_postcodes as `j1` on j1.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
							LEFT JOIN cf_jibble_postcodes as `j2` on j2.postcode = SUBSTRING_INDEX(w.postcode,' ',1)
							LEFT JOIN cf_users as `u` on u.user_id = o.user_id							
							LEFT JOIN cf_saved_ads as `s` 
										on s.ad_id = w.wanted_id and 
										s.post_type = 'wanted' and 
										s.user_id = '".$_SESSION['u_id']."'";
				}
				// offered ad version, show wanted matches
				
			$matches_query_bottom .=	"		WHERE
						# Both ads are published and unexpired
									o.published = 1
						 AND  w. published = 1					 
						 AND  o.expiry_date > now()
						 AND  w.expiry_date > now()			 
				";
							
			if ($match_post_type == "wanted") {
							 $matches_query_bottom .= "AND  w.wanted_id = ".$match_ad_id." 
													AND  o.suspended = 0";        // offered ads should be not be suspended; we don't mind it the current ad is suspended
			} else { $matches_query_bottom .= "AND  o.offered_id = ".$match_ad_id." 
													AND  w.suspended = 0";	}
			$matches_query_bottom .= "
						# *********************************************************
						# LOCATION AND DATES
						# *********************************************************
						
						# Postcode and distance from postcode
						# o.location in within w.distance_from_postcode of w.postcode			
						# W Available from (means required from) and O Available date are within 10 days of eachother
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
						
						# Number of bedrooms required
						#AND w.bedrooms_required <= o.bedrooms_available
						
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
						#	)
									
						# Family, married couple, reference
						AND w.current_is_couple  <= o.suit_married_couple 
						AND w.current_is_family  <= o.suit_family 
						#AND w.church_reference   >= o.church_reference 
						
						# Age
						#AND w.current_min_age >= o.suit_min_age
						#AND (o.suit_max_age = 0 OR w.current_max_age <= o.suit_max_age)
							
						# RD 17-MAY-08 removed occuplational matches							
						# Occupation
						#AND (
						#	(o.current_occupation is null) OR
						#	(o.suit_professional   = w.shared_professional) OR
						#	(o.suit_mature_student = w.shared_mature_student) OR
						#	(o.suit_student        = w.shared_student)
						#)			
						
						# *********************************************************
						# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
						# *********************************************************
						# Max number in the household, with logic to 4+ members
						#AND ((o.current_num_males + o.current_num_females) <= w.shared_adult_members 
						#     OR w.shared_adult_members = 4)
						
						# Age
						#AND w.shared_min_age <= o.current_min_age
						#AND (w.shared_max_age >= o.current_max_age OR w.shared_max_age = 0)
						
						# RD 17-MAY-08 removed occuplational matches	  
						# Occupation
						#AND (
						#	(o.current_occupation is null) OR
						#	(o.suit_professional= w.shared_professional) OR
						#	(o.suit_mature_student = w.shared_mature_student) OR
						#	(o.suit_student = w.shared_student)
						#)
						
						# Gender
						AND (
							(w.shared_males = 1   AND o.current_num_females = 0) OR
							(w.shared_females = 1 AND o.current_num_males = 0) OR
							(w.shared_mixed = 1   AND o.current_num_males > 0 AND o.current_num_females > 0) OR
							(w.current_num_females > 0 AND o.current_num_females > 0) OR
							(w.current_num_males > 0 AND o.current_num_males > 0) OR
							(o.accommodation_type = 'family share')
						)
							AND (
								 o.suit_gender = 'Mixed' OR
								(w.current_num_males > 0     AND w.current_num_females > 0) OR
								(o.suit_gender = 'Male(s)'   AND w.current_num_females = 0) OR
								(o.suit_gender = 'Female(s)' AND w.current_num_males = 0)
						)
						";
			$matches_query_bottom .= $sqlWhereExt;
			// Exclude hidden ads
			if ($currentUser['show_hidden'] == 0 && $match_post_type == "wanted") {
				$matches_query_bottom .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and o.offered_id=ad_id and post_type = 'offered' and active=2) ";  }
			if ($currentUser['show_hidden'] == 0 && $match_post_type == "offered") {
				$matches_query_bottom .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id=ad_id and post_type = 'wanted' and active=2) ";	 }				
		} // End if - ad_matches
										
										
		if ($search_type == "palup") {			
			$matches_query_top_count = "select ".$sqlAlias.".".$post_type."_id ";							
			$matches_query_top = "
			SELECT w.*, 
							DATEDIFF(curdate(),w.created_date) as `ad_age`, 
						 (select DATEDIFF(curdate(),last_login)  from cf_users where cf_users.user_id = w.user_id) last_login_days,
							s.active as `active`	";
			$matches_query_bottom = "
			FROM 
			cf_wanted as `wo`
			INNER JOIN cf_wanted as `w`
			INNER JOIN cf_jibble_postcodes as `jo`
			INNER JOIN cf_jibble_postcodes as `jn`
			INNER JOIN cf_users as `u` 
			left join cf_saved_ads as `s` 
					on s.ad_id = w.wanted_id and 
					s.post_type = 'wanted' and 
					s.user_id = '".$_SESSION['u_id']."'
			WHERE u.user_id = wo.user_id
			
			# Flat-Match is enabled
			# AND wo.palup = 1  results shown regardless of palup option chosen
			AND w.palup = 1
			
			# Do not compare ads of the same id
			AND wo.wanted_id != w.wanted_id 
			
			# New ads only: The w must be newer than wo, who is the recipient of the email
			# RD 28-JUNE-07, 		AND w.created_date > ifnull(wo.last_flatmatch,'2005-01-01')
			#AND w.created_date > ifnull(wo.last_palup,wo.created_date)
			#AND w.created_date > wo.last_updated_date
			
			# Ensure that w is newer than wo
			#AND w.created_date > wo.created_date
			
			# Both ads are pulished and unexpired
			AND  wo.published = 1
			AND  wo.expiry_date > now()
			#AND  wo.suspended = 0 # The ad being matched can be suspended
			AND  wo.wanted_id = ".$match_ad_id."
			AND  w.published = 1
			AND  w.expiry_date > now() 
			AND  w.suspended = 0 
	
			# ***********************************************
			# LOCATION AND DATES
			# ***********************************************
			
			# The distance between the two wanted ads 
			# is LESS than the sum of the distance_from_postcode for both wanted ads
			AND (sqrt(power((jo.x-jn.x),2)+power((jo.y-jn.y),2)) < (1609 * (wo.distance_from_postcode + w.distance_from_postcode))
			    OR
		  	  sqrt(power((jo.x-jn.x),2)+power((jo.y-jn.y),2)) < (1609 * (4 + 4))
		   	 )	
			AND SUBSTRING_INDEX(wo.postcode,' ',1) = jo.postcode
			AND SUBSTRING_INDEX(w.postcode,' ',1) = jn.postcode
	
			# The distance from one date to the other is less than 2 weeks
			AND (ABS(DATEDIFF(w.available_date,wo.available_date)) <= 15
		     OR (w.available_date < NOW()
		         AND ABS(DATEDIFF(wo.available_date,NOW())) <= 15
		        )
		     OR (wo.available_date < NOW()
		         AND ABS(DATEDIFF(w.available_date,NOW())) <= 15
		        )
		    )
		 
			 
			# Accommodation type
			# RD 03-AUG-07 Accommodation type excluded: we can assume it will be whole place 
					# or flatshare
			#AND  (   
			#	(w.accommodation_type_flat_share   = wo.accommodation_type_flat_share) OR
			#	(w.accommodation_type_family_share = wo.accommodation_type_family_share) OR
			#	(w.accommodation_type_whole_place  = wo.accommodation_type_whole_place)
			#)
			
			# Prices within 20% of each other
			# RD 25-JUL-07, removed price
			# AND w.price_pcm >= (wo.price_pcm *.78)
	
			# ******************************************************
			# MATCH WANTED YOUR DETAILS TO WANTED PREFERED HOUSEHOLD
			# ******************************************************
			
			# Family, married couple,reference
			# RD 03-AUG-07 Changed to <= logic, to compare the 1s and 0s
			AND w.current_is_couple <= wo.shared_married_couple
			AND w.current_is_family <= wo.shared_family 
			AND wo.current_is_couple <= w.shared_married_couple
			AND wo.current_is_family <= w.shared_family
			
			
			# Occupation
			#AND (
			#(w.current_occupation = 'Professionals' AND wo.shared_professional = 1) OR
			#(w.current_occupation = 'Mature Students' AND wo.shared_mature_student = 1) OR
			#(w.current_occupation = 'Students (<22yrs)' AND wo.shared_student = 1) OR
			#(wo.shared_student = 0 AND wo.shared_mature_student = 0 AND wo.shared_professional = 0)
			#)
			#AND (
			#(wo.current_occupation = 'Professionals' AND w.shared_professional = 1) OR
			#(wo.current_occupation = 'Mature Students' AND w.shared_mature_student = 1) OR
			#(wo.current_occupation = 'Students (<22yrs)' AND w.shared_student = 1) OR
			#(w.shared_student = 0 AND w.shared_mature_student = 0 AND w.shared_professional = 0)
			#)
	
			# *********************************************************
			# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
			# *********************************************************
			
			# Age
			#AND  w.shared_min_age <= wo.current_min_age
			#AND (w.shared_max_age >= wo.current_max_age OR w.shared_max_age = 0)
			#AND  wo.shared_min_age <= w.current_min_age
			#AND (wo.shared_max_age >= w.current_max_age OR wo.shared_max_age = 0)
		
			# Gender
			AND (
				(w.shared_males = 1 AND wo.current_num_females = 0) OR
				(w.shared_females = 1 AND wo.current_num_males = 0) OR
			(w.shared_mixed = 1 AND wo.current_num_males > 0 AND wo.current_num_females > 0)   OR
			(w.shared_mixed = 1 AND wo.current_num_males > 0  AND w.current_num_males > 0)    OR
			(w.shared_mixed = 1 AND wo.current_num_females > 0 AND w.current_num_females > 0) OR
			(w.shared_males = 0 AND w.shared_females = 0 AND w.shared_mixed = 0) OR
			(w.current_num_males   > 0 AND wo.current_num_males > 0)   OR
			(w.current_num_females > 0 AND wo.current_num_females > 0) 			
			) 
			AND (
				(wo.shared_males = 1 AND w.current_num_females = 0) OR
				(wo.shared_females = 1 AND w.current_num_males = 0) OR
			(wo.shared_mixed = 1 AND w.current_num_males > 0 AND w.current_num_females > 0)   OR
			(wo.shared_mixed = 1 AND w.current_num_males > 0 AND wo.current_num_males > 0)     OR
			(wo.shared_mixed = 1 AND w.current_num_females > 0 AND wo.current_num_females > 0) OR
			(wo.shared_males = 0 AND wo.shared_females = 0 AND wo.shared_mixed = 0) OR
			(w.current_num_males   > 0 AND wo.current_num_males > 0)   OR
			(w.current_num_females > 0 AND wo.current_num_females > 0) 			
			)
		";				
			$matches_query_bottom .= $sqlWhereExt;						
			if ($currentUser['show_hidden'] == 0 && $match_post_type == "offered") {
				$matches_query_bottom .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and w.wanted_id=ad_id and post_type = 'wanted' and active=2) ";	
			}												
		}	// end if palup

		// Build the count query
		$query  = "select ".$post_type."_id from cf_".$post_type." as `".$sqlAlias."` ".$sqlTableExt." \n";
		$query .= "where ".$sqlAlias.".published = '1' and ".$sqlAlias.".suspended = '0' and ".$sqlAlias.".expiry_date >= now() ".$sqlWhereExt;
		
		// Conditional hidden ads predicate 
		if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and ".$sqlAlias.'.'.$post_type."_id=ad_id and post_type = '".$post_type."' and active=2) ";						
		}
		
		if ($search_type == "ad_matches" || $search_type == "palup") { $query = $matches_query_top_count.$matches_query_bottom; }
		// Conditional order by, required as sqlAlias not used for cf_user
		if ($sortField=="last_login_days desc" || $sortField=="last_login_days asc") {
 		  $query .= "order by ".$sortField.", ".$sqlAlias.".".$post_type."_id desc";
		} else {
		  $query .= "order by ".$sqlAlias.".".$sortField.", ".$sqlAlias.".".$post_type."_id desc";		
		}
		
        
        // WE QUERY HERE???
        
        
		$debug .= debugEvent("Search query (count for pager)",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
    if ($result !== FALSE) {
      $count = mysqli_num_rows($result);
    }
    else {
      $count = 0;
    }

		// Populate the $_SESSION['result_set'] variable with all the id's 
		// to be used in the "Next / previous" functionality of the details.php page
		$result_set = array();
    if ($result !== FALSE) {
  		while($row = mysqli_fetch_row($result)) {
  			$result_set[] = $row[0];
  		}
    
  		$_SESSION['result_set'] = $result_set;
  		unset($result_set);	
    }
    
    // JDL: Removed array $_SESSION from String
		$debug .= debugEvent("Session variable 'result_set':", print_r($_SESSION['result_set'],true));	
		
		//$row = mysqli_fetch_row($result);
		//$count = $row[0];	
				
		// The GET string that will be appended in all pager links
		$link = $_SERVER['PHP_SELF'];
		$link .= "?search_type=".$search_type;
        $link .= (isset($latitude)) ? '&lat=' . $latitude : NULL;
        $link .= (isset($longitude)) ? '&lng=' . $longitude : NULL;
		if ($search_type == "ad_matches" || $search_type == "palup" ) {
			$link .= "&match_post_type=".$match_post_type;
			$link .= "&match_ad_id=".$match_ad_id;			
		} else {
			$link .= "&post_type=".$post_type;
		} 
		if ($search_type == "all_counties") { $link .= "&area=".$area; };
		if ($sortNum) { $link .= "&sortNum=".$sortNum; }
		if ($sortField) { $link .= "&sortField=".$sortField; }
		if ($sortSuit) { $link .= "&sortSuit=".$sortSuit; }
		if ($sortBed1) { $link .= "&sortBed1=1"; }
		if ($sortBed2) { $link .= "&sortBed2=1"; }
		if ($sortBed3) { $link .= "&sortBed3=1"; }
		if ($sortBed4) { $link .= "&sortBed4=1"; }	
    if ($sortShortTerm) { $link .= "&sortShortTerm=1"; }	
		if ($place) { $link .= "&place=".$place; }
		if ($town) { $link .= "&town=".$town; }
		if ($county) { $link .= "&county=".$county; }
		if ($flatshare) { $link .= "&flatshare=".$flatshare; }
		if ($familyshare) { $link .= "&familyshare=".$familyshare; }
		if ($wholeplace) { $link .= "&wholeplace=".$wholeplace; }
		if ($radius) { $link .= "&radius=".$radius; }
		if ($pcm) { $link .= "&pcm=".$pcm; }
		if ($summary_type) { $link .= "&summary_type=".$summary_type; }
		// Search by church parameters
		if ($search_type == "church") {
			if ($church_url) { $link .= "&church_url=".$church_url; }
			if ($church_type) { $link .= "&church_type=".$church_type; }
			if ($church_url) { $link .= "&church_url=".$church_url; }
			if ($church_acronym) { $link .= "&church_acronym=".$church_acronym; }
			if ($church_name) { $link .= "&church_name=".$church_name; }
			if ($postcode) { $link .= "&postcode=".$postcode; }			
		}
		
		// Create a pager for count query
		$pager = new Pager($count,$start,$sortNum,$link);	
		
		// Add the "Saved status" of the ad (ONLY if the user is logged in)
		if (isset($_SESSION['u_id'])) {
			$sqlSelectExt .= ",s.active as `active` ";
			$sqlTableExt .= "
				left join cf_saved_ads as `s` 
				on s.ad_id = ".$sqlAlias.".".$post_type."_id and 
				s.post_type = '".$post_type."' and 
				s.user_id = '".$_SESSION['u_id']."'
			";
		}
		
		// Build the main search query 
		$query  = "select ".$sqlAlias.".*".$sqlSelectExt.", (select DATEDIFF(curdate(),last_login)  from cf_users where cf_users.user_id = ".$sqlAlias.".user_id) last_login_days \n";	
		$query .= "from cf_".$post_type." as `".$sqlAlias."` ".$sqlTableExt."\n";
		$query .= "where ".$sqlAlias.".published = '1' and ".$sqlAlias.".suspended = '0' and ".$sqlAlias.".expiry_date >= now() ".$sqlWhereExt;
										
		// Conditional hidden ads predicate 
		if (isset($_SESSION['u_id']) && $currentUser['show_hidden'] == 0) {			
		    $query .= " and NOT EXISTS (select NULL from cf_saved_ads where user_id = ".$_SESSION['u_id']." and ".$sqlAlias.'.'.$post_type."_id=ad_id and post_type = '".$post_type."' and active=2) ";						
		}				
		
		// if ad or palup matching, swap in the correct SQL
		if ($search_type == "ad_matches" || $search_type == "palup") { $query = $matches_query_top.$matches_query_bottom; }										

		// Conditional order by, required as sqlAlias not used for cf_user
		if ($sortField=="last_login_days desc" || $sortField=="last_login_days asc") {
 		  $query .= "order by ".$sortField.", ".$post_type."_id desc limit ".$start.", ".$sortNum;
		} else {
		  $query .= "order by ".$sqlAlias.".".$sortField.", ".$sqlAlias.".".$post_type."_id desc limit ".$start.", ".$sortNum;		
		}

        // Another search query??

		$debug .= debugEvent("Search query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);

		if (!$result && !mysqli_num_rows($result)) {
			// No results found
			
			if ($search_type == "church") {
			
				if ($church_url) {
					$error  = '<h2 class="mt0">No ads found with a church URL matching &quot;'.$church_url.'&quot;</h2>';	
				} elseif ($church_name) {
					$error  = '<h2 class="mt0">No ads found with a church name matching &quot;'.$church_name.'&quot;</h2>';	
				} elseif ($church_acronym) {
					$error  = '<h2 class="mt0">No ads found with a church acronym matching &quot;'.$church_acronym.'&quot;</h2>';	
				} // End church_url
				
				$error .= '<p style="margin-bottom:100px;">Please return to the <a href="search-by-church.php">Search by Church</a> page and try again.</p>';
			
			} else { // Not "church"
						
				if ($post_type == "offered") {
				    // if postcode then uesr uppercase, else ucwords
				    if (preg_match(REGEXP_UK_POSTCODE_FIRST_PART,$_GET['place'])) {
						  $error  = '<h2>No results found within '.$radius.' miles of &quot;'.strtoupper($_GET['place']).'&quot; that match your criteria.</h2>';
				   	} else {
						  $error  = '<h2>No results found within '.$radius.' miles of &quot;'.ucwords($_GET['place']).'&quot; that match your criteria.</h2>';		
					  }			
					$error .= '<p>Please try:</p>';
					$error .= '<ul>';
					$error .= '<li>Increasing the <strong>radius</strong> of your search</li>';
					$error .= '<li>Using the <strong>&quot;Quick Links&quot;</strong> on the <a href="index.php">welcome page</a> to see results in your county or town</li>';
					$error .= '</ul>';
					$error .= '<p><br />';
					
					 if (!checkForWantedAd()) { 
					   	$error .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:460px;">
			 	<span class="style5"><strong>Looking for accommodation??</strong></span><br /> 
		Posting a ';
			          if (isset($_SESSION['u_id'])) {
						    	$error .= '<a href="post-choice.php" target="_blank">Wanted Accommodation</a>';
						    } else {
						      $error .=  'Wanted Accommodation';
					    	 }
					    $error .= ' advert will help those offering accommodation to find you.</p>

			<strong>Posting an ad helps you to get the best from Christian Flatshare.</strong> 
			<br />Links to your ads are included automatically when you reply to other ads.			
          </div>
		  <br />';
		         } // End check for Wanted ad
				/*	$error .= '</p><p class="mt0"><span class="style3"><strong>Looking for accommodation??</strong></span><br />
								Place a Wanted Accommodation advert and you can ask Christian Flatshare to automatically email you with suitable new adverts.</p>Creating a friendly, fun and informative advert will help those offering acommodation to find you.';		
					*/			
								
					$error .= '<p><br>';				
				} else { // post_type = Wanted 
				    if (preg_match(REGEXP_UK_POSTCODE_FIRST_PART,$_GET['place'])) {
							if (!$_GET['place']) {
								$error  = '<h2>No results found.</h2>';						
							} else {
								$error  = '<h2>No results found for '.strtoupper($_GET['place']).'</h2>';														
							}
					  } else {
							if (!$_GET['place']) {
								$error  = '<h2>No results found.</h2>';						
							} else {
								$error  = '<h2>No results found for '.ucwords($_GET['place']).'</h2>';
							}					
					   }	 // End preg_match REGEXP_UK_POSTCODE_FIRST_PART

					$error .= '<p><strong>For the accommodation type and the location you have entered,<br/>there are no matching wanted accommodation ads presently.</strong></p>';
					$error .= '<p>Using the &quot;Quick Links&quot; on the <a href="index.php">welcome page</a> may give a helpful indication<br/>of those looking for accommodation and in which areas.</p>';		
					
					 if (!checkForOfferedAd()) { 
					$error .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:475px;">
			 	<span class="style5"><strong>Accommodation to offer??</strong></span><br /> 
		Posting an ';
			       if (isset($_SESSION['u_id'])) {
							$error .= '<a href="post-choice.php" target="_blank">Offered Accommodation</a>';
					 	 } else {
					  	$error .=  'Offered Accommodation';
						 }
					$error .= ' advert will help those looking for accommodation to find yours.</p>
			<strong>Posting an ad helps you to get the best from Christian Flatshare.</strong>
			<br />Links to your ads are included automatically when you reply to other ads.			 
          </div>
		  <br />';		
		           } // If checkForOfferdAd
			  	} // End post_type == "offered"
			} // End search_type == "church"
			
		} else { // Results are found
			
            // If we're displaying the map then clean the output and just print that.
            // In the future we should make this more efficient by not constructing the
            // previous HTML output.
            if ($summary_type == "map") {
                $properties = array();
                
                while($ad = mysqli_fetch_assoc($result)) {
                    // Get mututal fb friends
                    $mutualFriends = NULL;
                    $mutualFriends = $CFSFacebook->getMutualFriends($currentUser['user_id'], $ad['user_id']);
                    $friends = $twig->render('mutualFriends.html.twig', array('friends' => $mutualFriends));
                    
                    $properties[] = (object) array(
                        'lat' => $ad['latitude'],
                        'lng' => $ad['longitude'],
                        'html' => createSummaryV2($ad,$post_type,$class,FALSE,FALSE,TRUE, $friends),
                        'title' => getAdTitle($ad, "offered", FALSE),
                    );
                }
                
                // We need to fetch results from the directory
            	// Load all the Churches and place icons on the map
                
                // If doing a geo search we have lat lng data, limit results to search radius
                if ($search_type == "geo") {
                    $earthDistanceSQL = $geoHelper->earth_distance_sql($latitude, $longitude, 'd');
                    $extraWhere = " AND " . $earthDistanceSQL . " < " . ($radius * 1609);
                }
                else {
                    $extraWhere = NULL;
                }
                
                $sql = "SELECT d.* from cf_church_directory AS d WHERE d.longitude is not NULL AND d.latitude is not NULL AND d.church_type = 'C'" . $extraWhere;
                $getChurches = $connection->prepare($sql);
                $getChurches->execute();
	            $churchesResult = $getChurches->fetchAll();
                $churches = array();
                
                foreach($churchesResult as $church) {
                    $churches[] = (object) array(
                        'lat' => $church['latitude'],
                        'lng' => $church['longitude'],
                        'html' => createChurchSummary($church),
                        'title' => $church['church_name'] . "\n" . $church['church_location'],
                    );
                }
                
                $sql = "SELECT d.* from cf_church_directory AS d WHERE d.longitude is not NULL AND d.latitude is not NULL AND d.church_type = 'O'" . $extraWhere;
                $getOrganisations = $connection->prepare($sql);
                $getOrganisations->execute();
                $organisationsResult = $getOrganisations->fetchAll();
                $organisations = array();
                
                foreach($organisationsResult as $organisation) {
                    $organisations[] = (object) array(
                        'lat' => $organisation['latitude'],
                        'lng' => $organisation['longitude'],
                        'html' => createChurchSummary($organisation),
                        'title' => $organisation['church_name'] . "\n" . $organisation['church_location'],
                    );
                }
                
                $sql = "SELECT d.* from cf_church_directory AS d WHERE d.longitude is not NULL AND d.latitude is not NULL AND d.church_type = 'S'" . $extraWhere;
                $getStudentGroups = $connection->prepare($sql);
                $getStudentGroups->execute();
                $studentGroupsResult = $getStudentGroups->fetchAll();
                $studentGroups = array();
                
                foreach($studentGroupsResult as $studentGroup) {
                    $studentGroups[] = (object) array(
                        'lat' => $studentGroup['latitude'],
                        'lng' => $studentGroup['longitude'],
                        'html' => createChurchSummary($studentGroup),
                        'title' => $studentGroup['church_name'] . "\n" . $studentGroup['church_location'],
                    );
                }
                
                if (!isset($_SESSION['search_defaults'])) {
                    if ($currentUser && !empty($currentUser['search_defaults'])) {
                        $defaults = unserialize($currentUser['search_defaults']);
                    }
                    else {
                        $defaults = array(
                            'show_churches' => 1,
                            'show_organisations' => 1,
                            'show_student_groups' => 1,
                            'overlay' => 'transit',
                        );
                    }
                    
                    $_SESSION['search_defaults'] = $defaults;
                }
                else {
                    $defaults = $_SESSION['search_defaults'];
                }
                
                function intToChecked($int) {
                    if ($int == 1) {
                        return ' checked="checked"';
                    }
                    else {
                        return NULL;
                    }
                }
                
                function isChecked($defaults, $key, $value) {
                    return ($defaults[$key] == $value) ? ' checked="checked"' : NULL;
                }
                
                $t = '<div id="mapOptions">
                    <div class="markers">
                        <div class="title">Show:</div>
                        <div class="item">
                            <input type="checkbox" name="showChurches" id="showChurches" value="1"' . intToChecked($defaults['show_churches']) . ' />
                            <label for="showChurches">Churches</label>
                        </div>
                        <div class="item">
                            <input type="checkbox" name="showOrganisations" id="showOrganisations" value="1"' . intToChecked($defaults['show_organisations']) . ' />
                            <label for="showOrganisations">Organisations</label>
                        </div>
                        <div class="item">
                            <input type="checkbox" name="showStudentGroups" id="showStudentGroups" value="1"' . intToChecked($defaults['show_student_groups']) . ' />
                            <label for="showStudentGroups">Student Groups</label>
                        </div>
                    </div>
                    <div class="overlays">
                        <div class="title">Overlays:</div>
                        <div class="item">
                            <input type="radio" name="overlays" id="overlayTubes" value="transit"' . isChecked($defaults, 'overlay', 'transit') . ' />
                            <label for="overlayTubes">Tube / transit lines</label>
                        </div>
                        <div class="item">
                            <input type="radio" name="overlays" id="overlayBicycle" value="bicycling"' . isChecked($defaults, 'overlay', 'bicycling') . ' />
                            <label for="overlayBicycle">Bicycle routes</label>
                        </div>
                        <div class="item">
                            <input type="radio" name="overlays" id="overlayNone" value="clear"' . isChecked($defaults, 'overlay', 'clear') . ' />
                            <label for="overlayNone">None</label>
                        </div>
                    </div>
                </div>
                <div id="mapResultsContainer">
                    <div id="resultsMap"></div>
                    <div id="resultInfo"></div>
                </div>
                <script type="text/javascript">
                    var properties = ' . json_encode($properties) . ';
                    var churches = ' . json_encode($churches) . ';
                    var organisations = ' . json_encode($organisations) . ';
                    var studentGroups = ' . json_encode($studentGroups) . ';
                </script>';
            }
            
			// Contruct the table
			$iteration = 1;
			$first_postcode = NULL;
						
			while($ad = mysqli_fetch_assoc($result)) {
				// Populate the first_postcode variable (used to display the location-specific banners)
				$first_postcode = $ad['postcode'];
			    
				$class = (isset($class) && $class == "odd")? "even":"odd";
				if ($post_type == "offered") {
					$mapString .= $ad['offered_id'].",";
					$mapHiddenFields .= '<input type="hidden" name="ad[]" value="'.$ad['offered_id'].'" />';
				}
				if ($summary_type == "church") {
					$t .= createSummaryV2($ad,$post_type,$class,FALSE,TRUE,TRUE);
				} else if ($summary_type == "quick") {
					$t .= createQuickSummary($ad,$post_type);
				} else {
                    // Get mututal fb friends
                    $mutualFriends = NULL;
                    $mutualFriends = $CFSFacebook->getMutualFriends($currentUser['user_id'], $ad['user_id']);
                    $friends = $twig->render('mutualFriends.html.twig', array('friends' => $mutualFriends));
                    
					$t .= createSummaryV2($ad,$post_type,$class,FALSE,FALSE,TRUE, $friends);
				}
				
				// Add a banner
				if ($summary_type != "quick") {
					if ($iteration == 6) {
						$iteration = 1;
						$t .= loadBanner("728",$first_postcode);
					} else {
						$iteration++;
					}
				} // End $summary_type != "quick"
			}	// End While ad results
            
			$mapString = substr($mapString,0,-1); // snip last comma
			
			// If we're showing the "quick list" we need to prepend and append the table html
			if ($summary_type == "quick") {
				$temp = '<table cellpadding="0" cellspacing="0" border="0" id="quick_summary">';
				$temp .= '<tr>';
				$temp .= '<th>Monthly&nbsp;price<br />per&nbsp;bedroom</th>';
				if ($post_type == "offered") {
					$temp .= '<th>Date<br />Available</th>';
				} else {
					$temp .= '<th>Required<br />From</th>';				
				}
				$temp .= '<th>Description</th>';
				$temp .= '<th>Photo?</th>';
				$temp .= '</tr>';
				$temp .= $t;
				$temp .= '</table>';
				$t = $temp;
				unset($temp);			
			} // End  summary_type == "quick"
            
		} // Results are found??

  }
	
	// Create all the hidden fields (for the sort form)
	foreach($_GET as $key=>$value) {
		if (substr($key,0,6) != "button" && substr($key,0,4) != "sort" && $key != "place" && $key != "summary_type" && $key != "show_hidden_ads") {
			$hiddenFields .= '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$value.'" />'."\n";
		}
	}
    
    if (!isset($searchHeader)) {
        $searchHeader = NULL;
    }
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php print $pageTitle?> - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<script>
jQuery(document).ready(function($) {
    jQuery(".FBFriends img").tooltip({ position: { my: "center top", at: "center bottom+5" } });
});
</script>
<script language="javascript" type="text/javascript" src="scripts/save-hide.js"></script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
<!-- Other -->
<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<?php if ($summary_type == "church" || $summary_type == "ad") { ?>
<script language="javascript" type="text/javascript" src="includes/photo_thumbs.js"></script>
<?php } ?>

<!-- GOOGLE MAPS API v3  -->
<!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script> -->
<script src="https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
<script type="text/javascript" src="includes/google/markerclusterer.js"></script> 
<script src="scripts/map.js"></script>

<style type="text/css">
<!--
.style3 {font-size: 14px}
a:visited {
	text-decoration: none;
}
.style5 {
	font-size: 14px;
	font-weight: bold;
}
a:link {
	text-decoration: none;
}
a:hover {
	text-decoration: underline;
	color: #0033FF;
}
a:active {
	text-decoration: none;
}
-->
</style>


<script language="javascript" type="text/javascript">
	window.addEvent('domready',function() {
		
		//tip_pricing
		//<p><strong>Pricing</strong></p>
		var myTips = new Tips('.tooltip');
	
		if ($('contact_details')) {
			$('contact_details').store('tip:title', 'Contact Details');
			$('contact_details').store('tip:text', 'Member contact details are only shown once you are logged in.<br /><br />Logging in first is required to prevent member contact details from<br />being stored by search engines when they visit Christian Flatshare.<br /><br />Login or join (free of charge) to see all details.');
		}
		
	});
</script>


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
			
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
				<tr>
					<td><h1 class="m0">Results</h1></td>
					<td align="right"><a href="<?php print $homeURL; ?>">Return to the previous page</a></td>
				</tr>
			</table>			
			
			<?php if ($drillDown) { ?>
				<div style="min-height:200px;">
				<?php print $drillDown?>
				</div>
			<?php } else if ($error) { ?>
				<?php print $error?>
			<?php } else { ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td>
							<form name="sortForm" id="form" method="get" action="<?php print $_SERVER['PHP_SELF']?>">
							<input type="hidden" name="summary_type" id="summary_type" value="<?php print $summary_type?>" />
							<input type="hidden" name="show_hidden_ads" id="show_hidden_ads" value="<?php print $show_hidden_ads?>" />
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td>
									<?php print $hiddenFields?>
									<table border="0" cellpadding="2" cellspacing="0" class="greyTable" width="100%">
										<tr>
											<th>Show</th>
											<th>Sort by</th>
											<?php if ($post_type == "offered") { ?>
											<th>Would suit</th>
											<?php } else { ?>
											<th>To suit</th>
											<?php } ?>
											<th>Number of bedrooms</th>
											<th>Short-term only</th>											
											<th colspan="2">Actions</th>
								<!--		 <th>Number of bedrooms</th>											 -->
										</tr>
										<tr>
											<td align="center"><?php print createDropDown("sortNum",getSortArray("num"),$sortNum);?></td>
											

											  <!-- we use the "field_ad_matches" array for ad matches, as the queries do not include the
												CF_USER records. If may be possible to include them later, but this is a temporary work around -->
								<td><?php print createDropDown("sortField",getSortArray("field_ad_matches"),$sortField);?></td>
								<!-- Update: use with "Days since last login" for all cases -->
								<!--														-->
								<!--				<td><?php print createDropDown("sortField",getSortArray("field"),$sortField);?></td> -->
								<!--					-->
										
										
											<?php if ($post_type == "offered") { ?>
											<td align="center"><?php print createDropDown("sortSuit",getSortArray("suit-offered"),$sortSuit);?></td>
											<?php } else { ?>
											<td align="center"><?php print createDropDown("sortSuit",getSortArray("suit-wanted"),$sortSuit);?></td>
											<?php } ?>
											<td align="center">
												<table cellpadding="0" cellspacing="0" border="0" class="noBorder">
													<tr>
														<td><?php print createCheckBox("sortBed1",1,$sortBed1)?></td>
														<td><label for="sortBed1">1</label></td>
														<td><?php print createCheckBox("sortBed2",1,$sortBed2)?></td>
														<td><label for="sortBed2">2</label></td>
														<td><?php print createCheckBox("sortBed3",1,$sortBed3)?></td>
														<td><label for="sortBed3">3</label></td>
														<td><?php print createCheckBox("sortBed4",1,$sortBed4)?></td>
														<td><label for="sortBed4">4+</label></td>
													</tr>
												</table>
											</td>
				
										<td align="center">
												<table cellpadding="0" cellspacing="0" border="0" class="noBorder">
													<tr>
														<!-- <td><?php print createCheckBox("sortShortTerm",false,$sortShortTerm)?></td> -->
														<td><input name="sortShortTerm" type="checkbox" id="sortShortTerm" value="1" <?php if($sortShortTerm==1){ echo "checked=\"unchecked\"";}?> /></td>
														<td><label for="sortShortTerm">12wks or less</label></td>
													</tr>
												</table>
											</td>				
			      				<td align="center"><input name="button_sort" type="submit" id="button_sort" value="Update results" /></td>																		
										</tr>
									</table>
									</td>
								</tr>
							</table>
							</form>
						</td>
					</tr>
				</table>
				<table width="100%" cellpadding="0" cellspacing="0" class="displaySeparatorTable" border="0">
					<tr>
						<td style="padding-bottom: 10px;">
							<ul class="displayOptions">
                                <li class="first"><a href="#" onclick="document.getElementById('summary_type').value = 'ad'; document.sortForm.submit();" class="<?php print ($summary_type == "ad"?"bold":"")?>">Detailed</a></li>
							    <li><a href="#" onclick="document.getElementById('summary_type').value = 'church'; document.sortForm.submit();" class="<?php print ($summary_type == "church"?"bold":"")?>">Contact</a></li>
							    <li><a href="#" onclick="document.getElementById('summary_type').value = 'quick'; document.sortForm.submit();" class="<?php print ($summary_type == "quick"?"bold":"")?>">Quicklist</a></li>
							<?php if ($post_type != "wanted") { ?><li><a href="#" onclick="document.getElementById('summary_type').value = 'map'; document.sortForm.submit();" class="<?php print ($summary_type == "map"?"bold":"")?>">Map</a></li><?php } ?>						    </ul>
                        </td>
						<td align="right" valign="center">
					        <?php if (isset($_SESSION['u_id'])) { ?>
								<a href="your-account-saved-ads.php">Your saved ads</a><br />
								<?php if ($currentUser['show_hidden'] == 0) { ?>
								    <!-- Hidden ads are hidden -->
									<a href="#" onclick="document.getElementById('show_hidden_ads').value = 'yes'; document.sortForm.submit();">Show your hidden ads</a>								
								  <?php } else { ?>
								    <!-- Hidden ads are shown -->								  
									<a href="#" onclick="document.getElementById('show_hidden_ads').value = 'no'; document.sortForm.submit();">Hide your hidden ads</a>
						        <?php } ?>
							<?php } ?>
						</td>
					</tr>
				</table>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:1em;">
                  <tr>
                    <td>Showing <strong>
                      <?php print $pager->getFirstItem();?>
                      </strong> - <strong>
                        <?php print $pager->getLastItem();?>
                        </strong> out of <strong>
                          <?php print $count?>
                          </strong> ads.
                      <?php print $searchHeader?></td>
                    <td align="right"><?php print $pager->createLinks()?></td>
                  </tr>
                </table>
                <?php if ($currentUser === NULL): ?>
                    <a href="<?php print $CFSFacebook->getLoginURL(); ?>" class="fb-msg">Enhance your experience by seeing mutuals friends of you and the advert poster, login with Facebook.</a>
                <?php elseif ($currentUser['facebookEnabled'] === FALSE): ?>
                    <a href="<?php print $CFSFacebook->getLoginURL(); ?>" class="fb-msg">Enhance your experience by seeing mutuals friends of you and the advert poster, enhance your account with Facebook.</a>
                <?php endif; ?>
                <!-- THE ADS -->
				<?php print $t;?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-top:1em;">
					<tr>
						<td>Showing <strong><?php print $pager->getFirstItem();?></strong> - <strong><?php print $pager->getLastItem();?></strong> out of <strong><?php print $count?></strong> ads. <?php print $searchHeader?></td>
						<td align="right"><?php print $pager->createLinks()?></td>
					</tr>
		</table>
			<?php } ?>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-top:1em;">
			<tr class="mb0">
				<td valign="bottom"><a href="<?php print $homeURL; ?>">Return to the welcome page</a></td>
				<?php if (isset($_SESSION['u_id']) && ($search_type == "ad_matches" || $search_type == "palup" || $search_type == "place" || $search_type == "town" || $search_type == "counties_all")) { ?>
				<td align="right"><span class="grey">TIP* use &quot;Hide/Save ad&quot; to mark ads as Hidden, so they are not shown <br />in future search results, or as Saved, shown in &quot;Your saved ads&quot;</span></td>
				<?php } ?>
			</tr>					
		</table>			
<!--			<p class="mb0"><a href="index.php">Return to the welcome page</a></p>	 -->
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
