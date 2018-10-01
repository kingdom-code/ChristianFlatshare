<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }	
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	if (!isset($_REQUEST['id'])) { header("Location:your-account-manage-posts.php"); exit; } else { $id = $_REQUEST['id']; }
	if (isset($_POST['cancel'])) { header("Location:your-account-manage-posts.php"); exit; }
	
	// First of all, check for ownership of the ad
	$query = "select count(*) from cf_offered where user_id = '".$_SESSION['u_id']."' and offered_id = '".$id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$check = cfs_mysqli_result($result,0,0);
	if (!$check) {
		header("Location: your-account-manage-posts.php"); exit;
	}
	
	// Initialise all needed variables
	$now = new Date();
	if (isset($_POST['cancel'])) { header("Location:index.php"); exit; }
	
	// FIRST PANE elements
	if (isset($_POST['postcode'])) { $postcode = $_POST['postcode']; } else { $postcode = NULL; }
	if (isset($_POST['street_name'])) { $street_name = $_POST['street_name']; } else { $street_name = NULL; }
	if (isset($_POST['town_chosen'])) { $town_chosen = $_POST['town_chosen']; } else { $town_chosen = NULL; }
	if (isset($_POST['available_date'])) { $available_date = $_POST['available_date']; } else { $available_date = NULL; }
	if (isset($_POST['min_term'])) { $min_term = $_POST['min_term']; } else { $min_term = 0; }
	if (isset($_POST['max_term'])) { $max_term = $_POST['max_term']; } else { $max_term = 0; }
	
	// SECOND PANE elements	
	if (isset($_POST['accommodation_type'])) { $accommodation_type = $_POST['accommodation_type']; } else { $accommodation_type = "flat share"; }
	if (isset($_POST['building_type'])) { $building_type = $_POST['building_type']; } else { $building_type = "house"; }
	if (isset($_POST['price_pcm'])) { $price_pcm = trim($_POST['price_pcm']); } else { $price_pcm = NULL; }
	if (isset($_POST['deposit_required'])) { $deposit_required = trim($_POST['deposit_required']); } else { $deposit_required = NULL; }
	if (isset($_POST['incl_utilities'])) { $incl_utilities = $_POST['incl_utilities']; } else { $incl_utilities = NULL; }
	if (isset($_POST['incl_council_tax'])) { $incl_council_tax = $_POST['incl_council_tax']; } else { $incl_council_tax = NULL; }
	if (isset($_POST['average_bills'])) { $average_bills = $_POST['average_bills']; } else { $average_bills = NULL; }
	if (isset($_POST['bedrooms_available'])) { $bedrooms_available = $_POST['bedrooms_available']; } else { $bedrooms_available = 1; }
	if (isset($_POST['bedrooms_double'])) { $bedrooms_double = $_POST['bedrooms_double']; } else { $bedrooms_double = 0; }
	if (isset($_POST['bedrooms_total'])) { $bedrooms_total = $_POST['bedrooms_total']; } else { $bedrooms_total = 1; }
	if (isset($_POST['furnished'])) { $furnished = $_POST['furnished']; } else { $furnished = NULL; }
	if (isset($_POST['parking'])) { $parking = $_POST['parking']; } else { $parking = "None"; }
	if (isset($_POST['shared_lounge_area'])) { $shared_lounge_area = $_POST['shared_lounge_area']; } else { $shared_lounge_area = NULL; }
	if (isset($_POST['central_heating'])) { $central_heating = $_POST['central_heating']; } else { $central_heating = NULL; }
	if (isset($_POST['washing_machine'])) { $washing_machine = $_POST['washing_machine']; } else { $washing_machine = NULL; }
	if (isset($_POST['garden_or_terrace'])) { $garden_or_terrace = $_POST['garden_or_terrace']; } else { $garden_or_terrace = NULL; }
	if (isset($_POST['bicycle_store'])) { $bicycle_store = $_POST['bicycle_store']; } else { $bicycle_store = NULL; }
	if (isset($_POST['dish_washer'])) { $dish_washer = $_POST['dish_washer']; } else { $dish_washer = NULL; }
	if (isset($_POST['tumble_dryer'])) { $tumble_dryer = $_POST['tumble_dryer']; } else { $tumble_dryer = NULL; }
	if (isset($_POST['ensuite_bathroom'])) { $ensuite_bathroom = $_POST['ensuite_bathroom']; } else { $ensuite_bathroom = NULL; }
	if (isset($_POST['shared_broadband'])) { $shared_broadband = $_POST['shared_broadband']; } else { $shared_broadband = NULL; }
	if (isset($_POST['cleaner'])) { $cleaner = $_POST['cleaner']; } else { $cleaner = NULL; }
	if (isset($_POST['accommodation_description'])) { $accommodation_description = trim($_POST['accommodation_description']); } else { $accommodation_description = NULL; }
	
	// THIRD PANE elements
	if (isset($_POST['current_min_age'])) { $current_min_age = $_POST['current_min_age']; } else { $current_min_age = 0; }
	if (isset($_POST['current_max_age'])) { $current_max_age = $_POST['current_max_age']; } else { $current_max_age = 0; }
	if (isset($_POST['current_num_males'])) { $current_num_males = $_POST['current_num_males']; } else { $current_num_males = NULL; }
	if (isset($_POST['current_num_females'])) { $current_num_females = $_POST['current_num_females']; } else { $current_num_females = NULL; }
	if (isset($_POST['current_occupation'])) { $current_occupation = $_POST['current_occupation']; } else { $current_occupation = NULL; }
	if (isset($_POST['owner_lives_in'])) { $owner_lives_in = $_POST['owner_lives_in']; } else { $owner_lives_in = NULL; }
	if (isset($_POST['current_is_couple'])) { $current_is_couple = $_POST['current_is_couple']; } else { $current_is_couple = NULL; }
	if (isset($_POST['current_is_family'])) { $current_is_family = $_POST['current_is_family']; } else { $current_is_family = NULL; }
	if (isset($_POST['church_attended'])) { $church_attended = trim($_POST['church_attended']); } else { $church_attended = NULL; }
	if (isset($_POST['church_url'])) { $church_url = strip_http(trim($_POST['church_url'])); } else { $church_url = NULL; }
	if (isset($_POST['household_description'])) { $household_description = trim($_POST['household_description']); } else { $household_description = NULL; }
	
	// FOURTH PANE elements
	if (isset($_POST['suit_gender'])) { $suit_gender = $_POST['suit_gender']; } else { $suit_gender = "Mixed"; }
	if (isset($_POST['suit_min_age'])) { $suit_min_age = $_POST['suit_min_age']; } else { $suit_min_age = NULL; }
	if (isset($_POST['suit_max_age'])) { $suit_max_age = $_POST['suit_max_age']; } else { $suit_max_age = NULL; }
	if (isset($_POST['suit_student'])) { $suit_student = $_POST['suit_student']; } else { $suit_student = NULL; }
	if (isset($_POST['suit_mature_student'])) { $suit_mature_student = $_POST['suit_mature_student']; } else { $suit_mature_student = NULL; }
	if (isset($_POST['suit_professional'])) { $suit_professional = $_POST['suit_professional']; } else { $suit_professional = NULL; }
	if (isset($_POST['suit_married_couple'])) { $suit_married_couple = $_POST['suit_married_couple']; } else { $suit_married_couple = NULL; }
	if (isset($_POST['suit_family'])) { $suit_family = $_POST['suit_family']; } else { $suit_family = NULL; }
	if (isset($_POST['church_reference'])) { $church_reference = $_POST['church_reference']; } else { $church_reference = NULL; }
	
	// FIFTH PANE elements
	if (isset($_POST['contact_name'])) { $contact_name = $_POST['contact_name']; } else { $contact_name = NULL; }
	if (isset($_POST['contact_phone'])) { $contact_phone = $_POST['contact_phone']; } else { $contact_phone = NULL; }
	
	
	// If form was submitted, perform the necessary validation
	if ($_POST) {
		// VALIDATE FIRST PANE
		if ($min_term && $max_term) {
			if ($min_term > $max_term) {
				$error['term'] = 'Max term must be larger than the minimum term';
			}
		}
		
		// VALIDATE SECOND PANE
		if (!$accommodation_type) { $error['accommodation_type'] = 'Please select accommodation type'; }
		if (!$building_type) { $error['building_type'] = 'Please indicate building type'; }
		if (!preg_match('/^([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2})$/',$price_pcm)) { $error['price_pcm'] = 'Price must be an amount, e.g. &quot;250&quot;'; }
		if ($deposit_required) {
			if (!preg_match('/^([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2})$/',$deposit_required)) { $error['deposit_required'] = 'Deposit must an amount, e.g. &quot;250&quot;'; }
		}
		if ($average_bills) {
			if (!preg_match('/^([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2})$/',$average_bills)) { $error['average_bills'] = 'Average bills must be an amount, e.g. &quot;50&quot;'; }
		}
		if (!$bedrooms_available) { $error['bedrooms_available'] = 'Please select number of offered bedrooms'; }
		if ($accommodation_type && 
			($accommodation_type == "flat share" || $accommodation_type == "family share") &&
			$bedrooms_total == $bedrooms_available) {
			$error['bedrooms_available'] = 'For &quot;House / Flatshare&quot; and &quot;Family Share&quot; the number of<br/> bedrooms offered must be less than the total number of bedrooms.<br/>You may mean to choose the accommodation type "Whole Place".<br />';
		}		
		if ($accommodation_type && 
			($accommodation_type == "whole place") &&
			$bedrooms_total <> $bedrooms_available) {
			$error['bedrooms_available'] = 'For &quot;Whole Place&quot; type accommodation ads the number of<br/>bedrooms offered must be equal to the total number of bedrooms.<br/>Maybe you mean to choose "House / Flatshare" type accommodation.<br />';
		}			
		
		if (!$bedrooms_total) { $error['bedrooms_total'] = 'Please select total number of bedrooms in the property'; }
		if ($bedrooms_available && $bedrooms_double && ($bedrooms_double > $bedrooms_available)) { $error['bedrooms_double'] = 'Number of double bedrooms exceeds offered ones.'; }
		if ($bedrooms_total && $bedrooms_available && ($bedrooms_available > $bedrooms_total)) { $error['bedrooms_available'] = 'Number of bedrooms offered cannot exceed total'; }
		
		// VALIDATE THIRD PANE (only if accommodation_type != "whole place")
		if ($accommodation_type != "whole place") {
			if ($current_min_age && $current_max_age) {
				if ($current_max_age < $current_min_age) { 
					$error['current_min_age'] = 'Youngest age must be less or equal to maximum age<br/>'; 
					$thirdPaneError = TRUE;
				}
				if (!$error['current_min_age']  
				    && ($current_num_males+$current_num_females) == 1   // one person, and age gap more than 5 yrs
						&& ($current_max_age-$current_min_age > 5)) {
					$sex = ($current_num_males == 1)? "male":"female";
					$error['current_min_age'] = 'You have put one '.$sex.' household member, aged '.$current_min_age.'-'.$current_max_age.' yrs.<br />&nbsp;With one person the largest indicative range can be 5yrs.<br/>'; 
					$thirdPaneError = TRUE;
				}		
			} elseif (!$current_min_age) {
				$error['current_min_age'] = 'Please give the youngest member\'s age<br/>'; 
				$thirdPaneError = TRUE;
			} elseif (!$current_max_age) {
				$error['current_max_age'] = 'Please give the oldest member\'s age<br/>'; 
				$thirdPaneError = TRUE;
			}
			if (!$current_num_males && !$current_num_females) {
				$error['current_num_males'] = "Please indicate number of the members of the household<br/>";
			}
			if (!$current_occupation) {
				$error['current_occupation'] = "Please indicate occupation of current members of the household<br/>";
			}
			if (!$church_attended) {
				$error['church_attended'] = "Please enter your church name<br/>";
				$thirdPaneError = TRUE;
			}						
		}
		
		// VALIDATE FOURTH PANE
		if ($suit_min_age && $suit_max_age && ($suit_max_age < $suit_min_age)) {
			$error['suit_age'] = 'Minimum age must be less or equal to maximum age';
		}
		
		// VALIDATE FIFTH PANE
		if (!$contact_name) { $error['contact_name'] = 'Please enter your contact name'; }
		
		// Apply the necessary formating to the error array
		if ($error) {
			
				// Load ad information from the database
					$query  = "select o.*,j.town ";
					$query .= "from cf_offered as `o` ";
					$query .= "left join cf_jibble_postcodes as `j` on SUBSTRING_INDEX(o.postcode,' ',1) = j.postcode ";
					$query .= "where o.offered_id = '".$id."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					$ad = mysqli_fetch_assoc($result);
					unset($ad['offered_id']);
					unset($ad['user_id']);
					$debug .= debugEvent("foreeach SQL ",$query);				
					foreach($ad as $key => $value) { ${$key} = $value; } // Create variables for all array keys
					
					// In the case the "town_chosen" is empty, substitute with teh jibble town
					if (!$town_chosen) { $town_chosen = $town; }
					
					// Load the list of alternative towns 
					$query = "
						SELECT	place_name as `town`
						FROM	cf_uk_places 
						WHERE	place_name NOT LIKE '%Avenue' 
						AND		place_name NOT LIKE '%Road' 
						AND		place_name NOT LIKE '%Station' 
						AND		place_name NOT LIKE '% Street' 
						AND		postcode = TRIM('".substr($postcode,0,-3)."') 
						UNION 
						SELECT	town 
						FROM	cf_jibble_postcodes 
						WHERE	postcode = TRIM('".substr($postcode,0,-3)."')			
					";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
					if (mysqli_num_rows($result)) {
						$town_list = array();
						while ($row = mysqli_fetch_assoc($result)) {
							$town_list[$row['town']] = $row['town'];
						}
					}
			
			array_walk($error,'formatError');
		
		} else {
			
			// Calculate the expiry date
			// By default set it to 14 days after $available_date
			$temp = new Date($available_date);
			$temp->addSeconds(86400 * 14);
			$expiry_date = $temp->format("%Y-%m-%d");
			
			// NO ERROR: Update cf_offered table
			$query = '
			update cf_offered set
				
				last_updated_date = "'.$now->getDate().'",
				expiry_date = "'.$expiry_date.'",
				available_date = "'.$available_date.'",
				town_chosen = "'.$town_chosen.'",
				min_term = "'.$min_term.'",
				max_term = "'.$max_term.'",
				price_pcm = round("'.$price_pcm.'"),
				deposit_required = round("'.$deposit_required.'"),
				incl_utilities = "'.$incl_utilities.'",
				incl_council_tax = "'.$incl_council_tax.'",
				average_bills = round("'.$average_bills.'"),
				accommodation_type = "'.$accommodation_type.'",
				building_type = "'.$building_type.'",
				bedrooms_total = "'.$bedrooms_total.'",
				bedrooms_available = "'.$bedrooms_available.'",
				bedrooms_double = "'.$bedrooms_double.'",
				furnished = "'.$furnished.'",
				parking = "'.$parking.'",
				shared_lounge_area = "'.$shared_lounge_area.'",
				central_heating = "'.$central_heating.'",
				washing_machine = "'.$washing_machine.'",
				garden_or_terrace = "'.$garden_or_terrace.'",
				bicycle_store = "'.$bicycle_store.'",
				dish_washer = "'.$dish_washer.'",
				tumble_dryer = "'.$tumble_dryer.'",
				ensuite_bathroom = "'.$ensuite_bathroom.'",
				shared_broadband = "'.$shared_broadband.'",
				cleaner = "'.$cleaner.'",
				accommodation_description = "'.$accommodation_description.'",
				current_min_age = "'.$current_min_age.'",
				current_max_age = "'.$current_max_age.'",
				current_num_males = "'.$current_num_males.'",
				current_num_females = "'.$current_num_females.'",
				current_occupation = "'.$current_occupation.'",
				owner_lives_in = "'.$owner_lives_in.'",
				current_is_couple = "'.$current_is_couple.'",
				current_is_family = "'.$current_is_family.'",
				church_attended = "'.$church_attended.'",
				church_url = "'.$church_url.'",
				household_description = "'.$household_description.'",
				suit_gender = "'.$suit_gender.'",
				suit_min_age = "'.$suit_min_age.'",
				suit_max_age = "'.$suit_max_age.'",
				suit_student = "'.$suit_student.'",
				suit_mature_student = "'.$suit_mature_student.'",
				suit_professional = "'.$suit_professional.'",
				suit_married_couple = "'.$suit_married_couple.'",
				suit_family = "'.$suit_family.'",
				church_reference = "'.$church_reference.'",
				contact_name = "'.$contact_name.'",
				contact_phone = "'.$contact_phone.'",
				recommendations = 0,
				approved = "0"
				
			where offered_id = "'.$id.'"';	
			// published = "'.DEFAULT_PUBLISH_STATUS.'" removeed so that scam ads
			$debug .= debugEvent("Ze uber UPDATE query",$query);		
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				header("Location: your-account-manage-posts.php?report=updateSuccess");
			} else {
				// Send a failure email to an administrator
				$subject = 'OFFERED AD UPDATE ERROR for ad id:'.$id;
				$message = new Email(TECH_EMAIL, "problems@christianflatshare.org", $subject);
				$text  = "Update query for offered ad with id ".$id." failed\n\n";
				$text .= "Query text:\n\n".$query."\n\n";
				$text .= "MySQL error:\n\n".mysqli_error();
				$message->SetTextContent($text);
				$message->Send();				
				header("Location: your-account-manage-posts.php?report=updateFailure");
			}
						
		}
	} else {
	
		// Load ad information from the database
		$query  = "select o.*,j.town ";
		$query .= "from cf_offered as `o` ";
		$query .= "left join cf_jibble_postcodes as `j` on SUBSTRING_INDEX(o.postcode,' ',1) = j.postcode ";
		$query .= "where o.offered_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		unset($ad['offered_id']);
		unset($ad['user_id']);
		$debug .= debugEvent("foreeach SQL ",$query);				
		foreach($ad as $key => $value) { ${$key} = $value; } // Create variables for all array keys
		
		// In the case the "town_chosen" is empty, substitute with teh jibble town
		if (!$town_chosen) { $town_chosen = $town; }
		
		// Load the list of alternative towns 
		$query = "
			SELECT	place_name as `town`
			FROM	cf_uk_places 
			WHERE	place_name NOT LIKE '%Avenue' 
			AND		place_name NOT LIKE '%Road' 
			AND		place_name NOT LIKE '%Station' 
			AND		place_name NOT LIKE '% Street' 
			AND		postcode = TRIM('".substr($postcode,0,-3)."') 
			UNION 
			SELECT	town 
			FROM	cf_jibble_postcodes 
			WHERE	postcode = TRIM('".substr($postcode,0,-3)."')			
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
			$town_list = array();
			while ($row = mysqli_fetch_assoc($result)) {
				$town_list[$row['town']] = $row['town'];
			}
		}
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Edit offered ad - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	function pickAccommodationType(v) {
	
		// V can be "flat share", "family share" or "whole place"
		// If v == "whole place" tab 3 will not be accessible any more.
		// Hence, we need to clear it's contents.
		if (v == "whole place") {
			$('current_min_age').selectedIndex = 0; // Select "17"
			$('current_max_age').selectedIndex = 43; // Select "60"
			document.forms[0].current_num_males[0].checked = true;
			document.forms[0].current_num_females[0].checked = true;
			document.forms[0].current_occupation[0].checked = false;
			document.forms[0].current_occupation[1].checked = false;
			document.forms[0].current_occupation[2].checked = false;
			$('owner_lives_in').checked = false;
			$('current_is_couple').checked = false;
			$('current_is_family').checked = false;
			$('church_attended').value = "";
			$('church_url').value = "";
			$('household_description').value = "";
			$('tab3contents').style.display = "none";
			$('tab3wholeplace').style.display = "";
			
			// Also, change the price_pcm labels
			building_type = document.forms[0].building_type;
			if (building_type[0].checked) { // If it's a House
				$('price_pcm_left_label').firstChild.nodeValue = "Monthly price:";
			} else { // It's a flat
				$('price_pcm_left_label').firstChild.nodeValue = "Monthly price:";
			}
			$('price_pcm_right_label').firstChild.nodeValue = "";
			
		} else {
			$('tab3wholeplace').style.display = "none";
			$('tab3contents').style.display = "";
			
			// Also, change the price_pcm labels
			$('price_pcm_left_label').firstChild.nodeValue = "Monthly price, per bedroom:";
			$('price_pcm_right_label').firstChild.nodeValue = "price per bedroom";
		}
		
	}
	
	function changePricePcmLabels() {
		
		if (document.forms[0].accommodation_type[2].checked) { // If "Whole Place" is selected
			if (document.forms[0].building_type[0].checked) { // If it's a House
				$('price_pcm_left_label').firstChild.nodeValue = "House price PCM:";
			} else { // It's a flat
				$('price_pcm_left_label').firstChild.nodeValue = "Flat price PCM:";
			}	
		}
		
	}	
	
	function $(obj) {
		return document.getElementById(obj);
	}
	
	function trim(toTrim) {
		while(''+toTrim.charAt(0) == " ") { toTrim = toTrim.substring(1,toTrim.length); }
		while(''+toTrim.charAt(toTrim.length-1) == " ") { toTrim = toTrim.substring(0,toTrim.length-1); }
		return toTrim;
	}	
	
</script>
		<script type="text/javascript">
		
			// Code based on the excellent DynamicDrive DHTML Code Library (www.dynamicdrive.com)
			// For a more powerful and old browser compatible example, visit http://www.dynamicdrive.com/
		
			var offsetxpoint=20 //Customize x offset of tooltip
			var offsetypoint=0 //Customize y offset of tooltip
			var ie=document.all
			var ns6=document.getElementById && !document.all
			var enabletip=false;
			var tipobj= document.getElementById("dhtmltooltip");
		
			function show_tooltip(tooltipContent){
				if (ns6||ie){
					tipobj.innerHTML = tooltipContent;
					enabletip = true;
					return false;
				}
			}
			
			function hide_tooltip(){
				if (ns6||ie){
					enabletip = false;
					tipobj.style.visibility = "hidden";
					tipobj.style.left = "-1000px";
					tipobj.style.backgroundColor = '';
					tipobj.style.width = '';
				}
			}
			
			function position_tooltip(e){
				if (enabletip) {
					var curX=(ns6)?e.pageX : event.x+document.body.scrollLeft;
					var curY=(ns6)?e.pageY : event.y+document.body.scrollTop;
					tipobj.style.left=curX+offsetxpoint+"px";
					tipobj.style.top=curY+offsetypoint+"px";
					tipobj.style.visibility="visible";
				}
			}
			
			document.onmousemove=position_tooltip
			
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
		<h1 class="mt0">Edit Offered ad</h1>
		<p class="mb0">Please edit your ad with the form below and click &quot;Save changes&quot; and the bottom when done. </p>
		<?php print $insertError?>
		<?php if ($error) { ?>
		<p class="error mb0">Errors where found in your form. Please review all fields and re-submit.</p>
		<?php } ?>
		<form name="offered" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
		<input type="hidden" name="id" value="<?php print $id?>" />
		<div class="fieldSet">
		<div class="fieldSetTitle">1. Location and dates</div>
			<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right" valign="top"> <p style="margin:0px;line-height:26px;">Postcode:</p></td>
					<td><strong><?php print $postcode?></strong><input type="hidden" name="postcode" value="<?php print $postcode?>" /></td>
				</tr>
				<tr>
					<td width="200" align="right">Street name:</td>
					<td><strong><?php print $street_name?></strong><input type="hidden" name="street_name" value="<?php print $street_name?>" /></td>
				</tr>
				<tr>
					<td width="200" align="right">Town: </td>
					<td><?php print createDropDown("town_chosen",$town_list,$town_chosen)?></td>
				</tr>
				<tr>
				  <td align="right">&nbsp;</td>
				  <td>&nbsp;</td>
			  </tr>
				<tr>
					<td align="right">Date available from:</td>
					<td><?php print createDateDropDown("available_date",180,$available_date,FALSE,"dateSelector")?></td>
				</tr>
				<tr>
					<td align="right">Minimum term:</td>
					<td><?php print createDropDown("min_term",getTermsArray("minimum"),$min_term);?>&nbsp;<span class="grey">length of stay</span></td>
				</tr>
				<tr>
					<td align="right">Maximum term:</td>
					<td><?php print createDropDown("max_term",getTermsArray("maximum"),$max_term);?>&nbsp;<span class="grey">length of stay</span><?php print $error['term']?></td>
				</tr>
			</table>
		</div>
		<div class="fieldSet">
		<div class="fieldSetTitle">2. Accommodation details </div>
			<table border="0" cellpadding="0" cellspacing="10" class="noBorder" width="100%">
				<tr>
					<td width="200" align="right" valign="top">Accommodation type:</td>
					<td><?php print $error['accommodation_type']?><?php print createRadioGroup("accommodation_type",getAccommodationTypeArray(),$accommodation_type,"vertical",'','','onclick="pickAccommodationType(this.value);"');?></td>
				</tr>
				<tr>
					<td width="200" align="right">Building type:</td>
					<td><?php print $error['building_type']?><?php print createRadioGroup("building_type",array("house"=>"House","flat"=>"Flat"),$building_type,'','','','onclick="changePricePcmLabels();"')?></td>
				</tr>
				<?php
					// Create the price_pcm_left_label and price_pcm_right_label
					if ($accommodation_type != "whole place") {
						$price_pcm_left_label = 'Monthly price:';
						$price_pcm_right_label = 'price per bedroom';
					} else {
						if ($building_type == "house") {
							$price_pcm_left_label = 'Monthly price:';
						} else {
							$price_pcm_left_label = 'Monthly price:';
						}
						$price_pcm_right_label = '&nbsp;';
					}
				?>
				<tr>
					<td width="200" align="right"><span id="price_pcm_left_label"><span class="obligatory">*</span>
<?php print $price_pcm_left_label?></span></td>
					<td>&pound;&nbsp;<input type="text" size="10" name="price_pcm" id="price_pcm" value="<?php print $price_pcm?>"/>&nbsp;<span class="grey" id="price_pcm_right_label"><?php print $price_pcm_right_label?></span><?php print $error['price_pcm']?></td>
				</tr>
				<tr>
					<td width="200" align="right">Deposit required:</td>
					<td>&pound;&nbsp;<input type="text" size="10" name="deposit_required" id="deposit_required" value="<?php print $deposit_required?>" /><?php print $error['deposit_required']?></td>
				</tr>
				<tr>
					<td width="200" align="right" valign="top">Utilities included:</td>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td><?php print createCheckbox("incl_utilities","1",$incl_utilities);?></td>
								<td>Utilities (gas, water, electricity)</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("incl_council_tax","1",$incl_council_tax);?></td>
								<td>Council tax</td>
							</tr>
						</table>					</td>
				</tr>
				<tr>
					<td width="200" align="right">Indication of share of monthly bills:</td>
					<td>&pound;&nbsp;<input type="text" size="10" name="average_bills" id="average_bills" value="<?php print $average_bills?>" /><?php print $error['average_bills']?></td>
				</tr>
				<tr>
				  <td align="right">&nbsp;</td>
				  <td>&nbsp;</td>
			  </tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Number of bedrooms offered:</td>
					<td><?php print $error['bedrooms_available']?><?php print createRadioGroup("bedrooms_available",getBedroomArray(),$bedrooms_available)?></td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> How many of the offered bedrooms<br />
				    are double-sized:</td>
					<td><?php print createRadioGroup("bedrooms_double",getBedroomArray(true),$bedrooms_double)?><?php print $error['bedrooms_double']?></td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Total number of bedrooms <br />
				    in the accommodation:</td>
					<td><?php print createRadioGroup("bedrooms_total",getBedroomArray(),$bedrooms_total)?></td>
				</tr>				
				<tr>
				  <td align="right">&nbsp;</td>
				  <td>&nbsp;</td>
			  </tr>
				<tr>
					<td width="200" align="right">The accommodation is furnished:</td>
					<td><?php print createCheckbox("furnished","1",$furnished);?></td>
				</tr>
				<tr>
					<td width="200" align="right" valign="top">Car Parking is available:</td>
					<td><?php print createRadioGroup("parking",getParkingArray(),$parking,"vertical")?></td>
				</tr>
				<tr>
					<td width="200" align="right" valign="top">The accommodation has: </td>
					<td>
						<table cellpadding="0" cellspacing="0" id="mod_cons">
							<tr>
								<td><?php print createCheckbox("shared_lounge_area","1",$shared_lounge_area);?>a shared lounge area</td>
								<td><?php print createCheckbox("dish_washer","1",$dish_washer);?>a dish washer</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("central_heating","1",$central_heating);?>central heating</td>
								<td><?php print createCheckbox("tumble_dryer","1",$tumble_dryer);?>a tumble dryer</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("washing_machine","1",$washing_machine);?>a washing machine</td>
								<td><?php print createCheckbox("ensuite_bathroom","1",$ensuite_bathroom);?>an ensuite bathroom</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("garden_or_terrace","1",$garden_or_terrace);?>a garden / roof terrace</td>
								<td><?php print createCheckbox("shared_broadband","1",$shared_broadband);?>access to shared broadband</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("bicycle_store","1",$bicycle_store);?>a suitable place to store a bicycle</td>
								<td><?php print createCheckbox("cleaner","1",$cleaner);?>a cleaner that visits</td>
							</tr>
						</table>					</td>
				</tr>
				<tr>
					<td width="200" align="right" valign="top">Describe the accommodation:<p id="accommodation_description_label"><?php print nl2br(DESC_ACCOMMODATION)?></p></td>
					<td><textarea name="accommodation_description" rows="10" id="accommodation_description" style="width:100%"><?php print stripslashes($accommodation_description)?></textarea></td>
				</tr>
			</table>
	      </div>
		<div class="fieldSet">
		<div class="fieldSetTitle">3. The current household</div>
			<p>
			  <?php 
			
				// Decide whether to show tab 3
				if ($accommodation_type == "whole place") {
					$contentsStyle = 'display:none;';
					$warningStyle = 'display:;';
				} else {
					$contentsStyle = 'display:;';
					$warningStyle = 'display:none;';
				}
			
			?>
			<div id="tab3contents" style="<?php print $contentsStyle?>">
			<table width="100%" border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Number of  male adult members:</td>
					<td><?php print $error['current_num_males']?><?php print createRadioGroup("current_num_males",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4+"=>"4+"),$current_num_males)?></td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Number of  female adult members:</td>
					<td><?php print createRadioGroup("current_num_females",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4+"=>"4+"),$current_num_females)?></td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Age of youngest adult member: </td>
					<td><?php print $error['current_min_age']?><?php print createDropDown("current_min_age",getAgeArray("--"),$current_min_age);?>&nbsp;
				<!--	<a href="#" 
						onmouseover="show_tooltip('<p><strong>Household age</strong></p><p>This is the age of the household. If there is only one member then min and max age would be the same, although you may prefer to express an indicative age range (e.g. 30-35).');" 
						onmouseout="hide_tooltip();">(?)</a> -->
					</td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Age of oldest adult member: </td>
					<td><?php print $error['current_max_age']?><?php print createDropDown("current_max_age",getAgeArray("--"),$current_max_age);?>
					<span class="grey"> <strong>If one person</strong>, min and max would be the same.</span>
					</td>
				</tr>				
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Occupation:</td>
					<td><?php print $error['current_occupation']?><?php print createRadioGroup("current_occupation",getOccupationArray(),$current_occupation)?></td>
				</tr>
				<tr>
				  <td align="right">&nbsp;</td>
				  <td>&nbsp;</td>
			  </tr>
				<tr>
					<td width="200" align="right">Owner is a member of the household:</td>
					<td><?php print createCheckbox("owner_lives_in","1",$owner_lives_in);?></td>
				</tr>
				<tr>
					<td width="200" align="right">Household has a married couple:</td>
					<td><?php print createCheckbox("current_is_couple","1",$current_is_couple);?></td>
				</tr>
				<tr>
					<td width="200" align="right">Household has a family with children:</td>
					<td><?php print createCheckbox("current_is_family","1",$current_is_family);?></td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Church attended: </td>
				  <td>
						<?php print $error['church_attended']?>
						<input type="text" name="church_attended" id="church_attended" value="<?php print stripslashes($church_attended)?>" />
			    <span class="grey">e.g. &quot;St John's, Bath&quot; / &quot;Looking for a church &quot;</span>				</tr>
				<tr>
					<td width="200" align="right" valign="top">Church website(s): </td>
					<td>
						<input type="text" name="church_url" id="church_url" value="<?php print stripslashes($church_url)?>" />
						<span class="grey">e.g.  www.our-church-website.org</span>					</td>
				</tr>
				<tr>
					<td width="200" align="right" valign="top">Describe the household:
				      <p id="household_description_label"><?php print nl2br(DESC_HOUSEHOLD)?></p></td>
					<td><textarea name="household_description" rows="10" id="household_description" style="width:100%"><?php print stripslashes($household_description)?></textarea></td>
				</tr>
			</table>
			</div>
			<div id="tab3wholeplace" style="<?php print $warningStyle;?>">
				<p class="grey">You have chosen &quot;<strong>Whole Place</strong>&quot; as the <strong>accommodation type</strong> for your offered ad<br/>This means there is no household resident</p>
			</div>
		</div>
		<div class="fieldSet">
		<div class="fieldSetTitle">4. The new person(s)</div>
			<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right">Sex:</td>
					<td>
						<?php print $error['suit_gender']?>
						<?php print createRadioGroup("suit_gender",getGenderArray("Male(s) or female(s)"),$suit_gender)?>					</td>
				</tr>
				<tr>
					<td align="right">Minumum adult age: </td>
					<td><?php print createDropDown("suit_min_age",getAgeArray("Any"),$suit_min_age);?></td>
				</tr>
				<tr>
					<td align="right">Maximum adult age: </td>
					<td><?php print createDropDown("suit_max_age",getAgeArray("Any"),$suit_max_age);?><?php print $error['suit_age']?></td>
				</tr>
				
				<tr>
				  <td align="right" valign="top">&nbsp;</td>
				  <td>&nbsp;</td>
			  </tr>
				<tr>
					<td width="200" align="right" valign="top">Occupation:</td>
					<td>
						<?php print createCheckbox("suit_student","1",$suit_student);?>Students (&lt;22yrs)
						<br />
						<?php print createCheckbox("suit_mature_student","1",$suit_mature_student);?>Mature students<br />
						<?php print createCheckbox("suit_professional","1",$suit_professional);?>Professionals				  </td>
				</tr>
				<tr>
					<td width="200" align="right">Could be a married couple:</td>
					<td><?php print createCheckbox("suit_married_couple","1",$suit_married_couple);?></td>
				</tr>
				<tr>
					<td width="200" align="right">Could be a family with children:</td>
					<td><?php print createCheckbox("suit_family","1",$suit_family);?></td>
				</tr>
				<tr>
					<td width="200" align="right">Would be someone who, if asked, could<br />
provide a recommendation from a church:</td>
					<td>
						<?php print createCheckbox("church_reference","1",$church_reference);?>
						<span class="grey">simply to say that they are known to a church fellowship which could in someway vouch for their character</span>					</td>
				</tr>
			</table>
		</div>
		<div class="fieldSet">
		<div class="fieldSetTitle">5. Your contact details</div>
			<p><span class="mt10"><strong>CFS does not disclose your email address to those who see your advert.</strong> <br />
Those responding to your ad can do so through a form on CFS, which helps CFS to protect  members from certain types of scam attempts. <br />
If you wish to you may include your phone number and any additional contact details within your advert. </span></p>
			<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Contact name:</td>
					<td><input name="contact_name" type="text" id="contact_name" value="<?php print stripslashes($contact_name)?>"/>
					&nbsp;<span class="grey">e.g. &quot;John Smith&quot;</span>&nbsp;<?php print $error['contact_name']?></td>
				</tr>				
				<tr>
					<td width="200" align="right">Contact phone number (optional):</td>
					<td><input name="contact_phone" type="text" id="contact_phone" value="<?php print $contact_phone?>"/></td>
				</tr>
				<?php
				/*
				<tr>
					<td align="right" valign="top">Advert expires on:</td>
					<td><?php print createDateDropDown("expiry_date",42,$expiry_date,TRUE,"dateSelector")?><?php print $error['expiry_date']?></td>
				</tr>
				*/
				?>
			</table>
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="mt10 mb10">
			<tr>
				<td valign="top">			
				<input type="submit" name="Submit" value="Save changes"/>&nbsp;
				<input type="submit" name="cancel" value="Cancel" /></td>
				<td align="right" valign="top">Having problems posting an ad?<br />Email <a href="mailto:problems@ChristianFlatShare.org">problems@ChristianFlatShare.org</a> </td>
			</tr>
		</table>
<!--		<p class="m0">
			<input type="submit" name="Submit" value="Save changes"/>&nbsp;
			<input type="submit" name="cancel" value="Cancel" />
		</p>
-->		
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
