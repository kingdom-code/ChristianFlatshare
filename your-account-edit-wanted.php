<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit;}	
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	if (!isset($_REQUEST['id'])) { header("Location:your-account-manage-posts.php"); exit; } else { $id = $_REQUEST['id']; }
	if (isset($_POST['cancel'])) { header("Location:your-account-manage-posts.php"); exit; }	
	// First of all, check for ownership of the ad
	$query = "select count(*) from cf_wanted where user_id = '".$_SESSION['u_id']."' and wanted_id = '".$id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$check = cfs_mysqli_result($result,0,0);
	if (!$check) {
		header("Location: your-account-manage-posts.php"); exit;
	}
	// Initialise all needed variables
	$now = new Date();
	if (isset($_POST['cancel'])) { header("Location:index.php"); exit; }
	
	// FIRST PANE elements
	if (isset($_POST['location'])) { $location = $_POST['location']; } else { $location = NULL; }
	if (isset($_POST['postcode'])) { $postcode = $_POST['postcode']; } else { $postcode = NULL; }
	if (isset($_POST['distance_from_postcode'])) { $distance_from_postcode = $_POST['distance_from_postcode']; } else { $distance_from_postcode = 5; }
	if (isset($_POST['available_date'])) { $available_date = $_POST['available_date']; } else { $available_date = NULL; }
	if (isset($_POST['min_term'])) { $min_term = $_POST['min_term']; } else { $min_term = 0; }
	if (isset($_POST['max_term'])) { $max_term = $_POST['max_term']; } else { $max_term = 999; }
	if (isset($_POST['price_pcm'])) { $price_pcm = trim($_POST['price_pcm']); } else { $price_pcm = NULL; }	
	
	// SECOND PANE elements
	if (isset($_POST['accommodation_type_flat_share'])) { $accommodation_type_flat_share = $_POST['accommodation_type_flat_share']; } else { $accommodation_type_flat_share = NULL; }
	if (isset($_POST['accommodation_type_family_share'])) { $accommodation_type_family_share = $_POST['accommodation_type_family_share']; } else { $accommodation_type_family_share = NULL; }
	if (isset($_POST['accommodation_type_whole_place'])) { $accommodation_type_whole_place = $_POST['accommodation_type_whole_place']; } else { $accommodation_type_whole_place = NULL; }
	if (isset($_POST['building_type_house'])) { $building_type_house = $_POST['building_type_house']; } else { $building_type_house = NULL; }
	if (isset($_POST['building_type_flat'])) { $building_type_flat = $_POST['building_type_flat']; } else { $building_type_flat = NULL; }
	if (isset($_POST['bedrooms_required'])) { $bedrooms_required = $_POST['bedrooms_required']; } else { $bedrooms_required = 1; }
	if (isset($_POST['furnished'])) { $furnished = $_POST['furnished']; } else { $furnished = "furnished or unfurnished"; }
	if (isset($_POST['shared_lounge_area'])) { $shared_lounge_area = $_POST['shared_lounge_area']; } else { $shared_lounge_area = NULL; }
	if (isset($_POST['central_heating'])) { $central_heating = $_POST['central_heating']; } else { $central_heating = NULL; }
	if (isset($_POST['washing_machine'])) { $washing_machine = $_POST['washing_machine']; } else { $washing_machine = NULL; }
	if (isset($_POST['garden_or_terrace'])) { $garden_or_terrace = $_POST['garden_or_terrace']; } else { $garden_or_terrace = NULL; }
	if (isset($_POST['bicycle_store'])) { $bicycle_store = $_POST['bicycle_store']; } else { $bicycle_store = NULL; }
	if (isset($_POST['dish_washer'])) { $dish_washer = $_POST['dish_washer']; } else { $dish_washer = NULL; }
	if (isset($_POST['tumble_dryer'])) { $tumble_dryer = $_POST['tumble_dryer']; } else { $tumble_dryer = NULL; }
	if (isset($_POST['ensuite_bathroom'])) { $ensuite_bathroom = $_POST['ensuite_bathroom']; } else { $ensuite_bathroom = NULL; }
	if (isset($_POST['parking'])) { $parking = $_POST['parking']; } else { $parking = NULL; }
	
	// THIRD PANE elements
	if (isset($_POST['current_num_males'])) { $current_num_males = $_POST['current_num_males']; } else { $current_num_males = NULL; }
	if (isset($_POST['current_num_females'])) { $current_num_females = $_POST['current_num_females']; } else { $current_num_females = NULL; }
	if (isset($_POST['current_min_age'])) { $current_min_age = $_POST['current_min_age']; } else { $current_min_age = 0; }
	if (isset($_POST['current_max_age'])) { $current_max_age = $_POST['current_max_age']; } else { $current_max_age = 0; }
	if (isset($_POST['current_occupation'])) { $current_occupation = $_POST['current_occupation']; } else { $current_occupation = NULL; }
	if (isset($_POST['current_is_couple'])) { $current_is_couple = $_POST['current_is_couple']; } else { $current_is_couple = NULL; }
	if (isset($_POST['current_is_family'])) { $current_is_family = $_POST['current_is_family']; } else { $current_is_family = NULL; }
	if (isset($_POST['church_reference'])) { $church_reference = $_POST['church_reference']; } else { $church_reference = NULL; }
	if (isset($_POST['church_attended'])) { $church_attended = trim($_POST['church_attended']); } else { $church_attended = NULL; }
	if (isset($_POST['church_url'])) { $church_url = strip_http(trim($_POST['church_url'])); } else { $church_url = NULL; }	
	if (isset($_POST['accommodation_situation'])) { $accommodation_situation = trim($_POST['accommodation_situation']); } else { $accommodation_situation = NULL; }		
	
	// FOURTH PANE elements
	if (isset($_POST['shared_adult_members'])) { $shared_adult_members = $_POST['shared_adult_members']; } else { $shared_adult_members = NULL; }
	if (isset($_POST['shared_males'])) { $shared_males = $_POST['shared_males']; } else { $shared_males = NULL; }
	if (isset($_POST['shared_females'])) { $shared_females = $_POST['shared_females']; } else { $shared_females = NULL; }
	if (isset($_POST['shared_mixed'])) { $shared_mixed = $_POST['shared_mixed']; } else { $shared_mixed = NULL; }
	if (isset($_POST['shared_min_age'])) { $shared_min_age = $_POST['shared_min_age']; } else { $shared_min_age = NULL; }
	if (isset($_POST['shared_max_age'])) { $shared_max_age = $_POST['shared_max_age']; } else { $shared_max_age = NULL; }
	if (isset($_POST['shared_student'])) { $shared_student = $_POST['shared_student']; } else { $shared_student = NULL; }
	if (isset($_POST['shared_mature_student'])) { $shared_mature_student = $_POST['shared_mature_student']; } else { $shared_mature_student = NULL; }
	if (isset($_POST['shared_professional'])) { $shared_professional = $_POST['shared_professional']; } else { $shared_professional = NULL; }
	if (isset($_POST['shared_other'])) { $shared_other = $_POST['shared_other']; } else { $shared_other = NULL; }
	if (isset($_POST['shared_owner_lives_in'])) { $shared_owner_lives_in = $_POST['shared_owner_lives_in']; } else { $shared_owner_lives_in = NULL; }	
	if (isset($_POST['shared_married_couple'])) { $shared_married_couple = $_POST['shared_married_couple']; } else { $shared_married_couple = NULL; }
	if (isset($_POST['shared_family'])) { $shared_family = $_POST['shared_family']; } else { $shared_family = NULL; }
	
	// FIFTH PANE elements
	if (isset($_POST['contact_phone'])) { $contact_phone = trim($_POST['contact_phone']); } else { $contact_phone = NULL; }
	if (isset($_POST['contact_name'])) { $contact_name = trim($_POST['contact_name']); } else { $contact_name = NULL; }
	if (isset($_POST['flatmatch'])) { $flatmatch = $_POST['flatmatch']; } else { $flatmatch = 0; }
	if (isset($_POST['palup'])) { $palup = $_POST['palup']; } else { $palup = 0; }
	
	// If form was submitted, perform the necessary validation
	if ($_POST) {
		
		// VALIDATE FIRST PANE
		if (!$distance_from_postcode) { $error['distance_from_postcode'] = 'Please indicate distance from place'; }
		// If a min_term AND a max_term have been defined, make sure that max_term is after min_term
		if ($min_term && $max_term != "999") {
			if ($min_term > $max_term) {
				$error['term'] = 'Maximum term must be larger than the minimum term';
			}
		}
		
		// VALIDATE SECOND PANE
		// Make sure at least one accommodation_type was picked
		if (!$accommodation_type_flat_share && !$accommodation_type_family_share && !$accommodation_type_whole_place) {
			$error['accommodation_type'] = 'Please select one or more accommodation types';
		}		
		// Make sure at least one building_type was picked
		if (!$building_type_house && !$building_type_flat) {
			$error['building_type'] = 'Please indicate building type';
		}
		if (!preg_match('/^([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2})$/',$price_pcm)) { $error['price_pcm'] = 'Please enter a monthly mount, e.g. &quot;250&quot;'; }
		
		// VALIDATE THIRD PANE
		if (!$current_num_males && !$current_num_females) {
			$error['current_num_males'] = 'Please indicate how many males / females are looking for accommodation';
		}
		if ($current_min_age && $current_max_age) {
			if ($current_max_age < $current_min_age) { 
				$error['current_min_age'] = 'Youngest age must be less or equal to oldest age<br/>';
				$thirdPaneError = TRUE;
			}
			if (!$error['current_min_age']  
			    && ($current_num_males+$current_num_females) == 1   // one person, and age gap more than 5 yrs
					&& ($current_max_age-$current_min_age > 5)) {
				$sex = ($current_num_males == 1)? "male":"female";
				$error['current_min_age'] = 'You have put one '.$sex.' accommodation seeker, aged '.$current_min_age.'-'.$current_max_age.' yrs.<br />&nbsp;With one person the largest indicative range can be 5yrs.<br/>'; 
				$thirdPaneError = TRUE;
			}				
		} elseif (!$current_min_age) {
			$error['current_min_age'] = 'Please specify minimum age<br/>';
			$thirdPaneError = TRUE;
		} elseif (!$current_max_age) {
			$error['current_max_age'] = 'Please specify maximum age<br/>';
			$thirdPaneError = TRUE;
		}
		if (!$current_occupation) { $error['current_occupation'] = 'Please choose your occupation'; }
		//if (!$church_attended) {
		//	$error['church_attended'] = "Please enter your church name<br/>";
		//	$thirdPaneError = TRUE;
		//}
		
		// VALIDATE FOURTH PANE
		if (!$shared_adult_members && ($accommodation_type_family_share || $accommodation_type_flat_share)) {
			$error['shared_adult_members'] = "For Flat / House Share and Family Share, max number of adult members must be greater then zero<br/>";
		}		
		if (
			!$shared_males && 
			!$shared_females && 
			!$shared_mixed && 
			($accommodation_type_family_share || $accommodation_type_flat_share)
		) {
			$error['shared_gender'] = "Please choose household preference";
		}		
		if ($shared_min_age && $shared_max_age && ($shared_min_age > $shared_max_age)) {
			$error['shared_age'] = "Minimum age cannot be larger than maximum";
		}
		
		// VALIDATE FIFTH PANE
		if (!$contact_name) { 
			$error['contact_name'] = 'Please enter your contact name<br/>'; 
			$fifthPaneError = TRUE;
		}
		
		// Apply the necessary formating to the error array
		if ($error) {
			array_walk($error,'formatError');
		} else {
			
			// Calculate the expiry date
			// By default set it to 14 days after $available_date
			$temp = new Date($available_date);
			$temp->addSeconds(86400 * 14);
			$expiry_date = $temp->format("%Y-%m-%d");
						
			// NO ERROR: Update cf_wanted table.
			$query = '
			update cf_wanted set 
			
				last_updated_date = "'.$now->getDate().'",
				expiry_date = "'.$expiry_date.'",
				distance_from_postcode = "'.$distance_from_postcode.'",';
			// If location changed but NULL given, don't update DB
			if ($location && $postcode) {$query .= 'location = "'.$location.'",postcode = "'.$postcode.'", '; }
			$query .= '
				available_date = "'.$available_date.'",
				min_term = "'.$min_term.'",
				max_term = "'.$max_term.'",
				price_pcm = round("'.$price_pcm.'"),
				accommodation_type_flat_share = "'.$accommodation_type_flat_share.'",
				accommodation_type_family_share = "'.$accommodation_type_family_share.'",
				accommodation_type_whole_place = "'.$accommodation_type_whole_place.'",
				building_type_flat = "'.$building_type_flat.'",
				building_type_house = "'.$building_type_house.'",
				bedrooms_required = "'.$bedrooms_required.'",
				furnished = "'.$furnished.'",
				shared_lounge_area = "'.$shared_lounge_area.'",
				central_heating = "'.$central_heating.'",
				washing_machine = "'.$washing_machine.'",
				garden_or_terrace = "'.$garden_or_terrace.'",
				bicycle_store = "'.$bicycle_store.'",
				dish_washer = "'.$dish_washer.'",
				tumble_dryer = "'.$tumble_dryer.'",
				ensuite_bathroom = "'.$ensuite_bathroom.'",
				parking = "'.$parking.'",
				accommodation_situation = "'.$accommodation_situation.'",
				current_min_age = "'.$current_min_age.'",
				current_max_age = "'.$current_max_age.'",
				current_num_males = "'.$current_num_males.'",
				current_num_females = "'.$current_num_females.'",
				current_occupation = "'.$current_occupation.'",
				current_is_couple = "'.$current_is_couple.'",
				current_is_family = "'.$current_is_family.'",
				church_attended = "'.$church_attended.'",
				church_url = "'.$church_url.'",
				shared_adult_members = "'.$shared_adult_members.'",
				shared_males = "'.$shared_males.'",
				shared_females = "'.$shared_females.'",
				shared_mixed = "'.$shared_mixed.'",
				shared_min_age = "'.$shared_min_age.'",
				shared_max_age = "'.$shared_max_age.'",
				shared_student = "'.$shared_student.'",
				shared_mature_student = "'.$shared_mature_student.'",
				shared_professional = "'.$shared_professional.'",
				shared_owner_lives_in = "'.$shared_owner_lives_in.'",
				shared_married_couple = "'.$shared_married_couple.'",
				shared_family = "'.$shared_family.'",
				church_reference = "'.$church_reference.'",
				contact_name = "'.$contact_name.'",
				contact_phone = "'.$contact_phone.'",
				flatmatch = "'.$flatmatch.'",
				palup = "'.$palup.'",
				recommendations = "0",
				approved = "0"
								
			where wanted_id = "'.$id.'"';	
					// published = "'.DEFAULT_PUBLISH_STATUS.'"	 removed so that scam ads remain suspended.
			$debug .= debugEvent("Ze uber UPDATE query",$query);		
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				header("Location: your-account-manage-posts.php?report=updateSuccess");
			} else {
				// Send a failure email to an administrator
				$subject = 'WANTED AD UPDATE ERROR for ad id:'.$id;
				$message = new Email(TECH_EMAIL, "problems@christianflatshare.org", $subject);
				$text  = "Update query for wanted ad with id ".$id." failed\n\n";
				$text .= "Query text:\n\n".$query."\n\n";
				$text .= "MySQL error:\n\n".mysqli_error();
				$message->SetTextContent($text);
				$message->Send();	
				header("Location: your-account-manage-posts.php?report=updateFailure");
			}
			
		}
	
	} else {
	
		// Load ad information from the database
		$query = "select * from cf_wanted where wanted_id = '".$id."'";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		unset($ad['wanted_id']);
		unset($ad['user_id']);
		foreach($ad as $key => $value) { ${$key} = $value; } // Create variables for all array keys
			
	}
	
	// Create the $years array which contains this, the next and the following year.
	for($i=($now->getYear());$i<=($now->getYear()+2);$i++) { $years[$i] = $i; }
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Edit wanted ad - Christian Flatshare</title>
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
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<script type="text/javascript">
	// By default, the tabWasClicked flag is set to false
	// It's status changes to true the first time the showTab() function is called
	var tabWasClicked = false;
	function showTab(tabId) {
	
		tabWasClicked = true;
		$('tabError').style.display = "none";
		$('tabErrorArrows').style.display = "none";		
	
		for(var i=1;i<=5;i++) {
			$('tab'+i).className = 'tabHidden'; // hide the tab
			$('tab'+i+'link').className = ''; // change the class of the link
		}	
		
		$('tab'+tabId).className = 'tab'; // show the tab
		$('tab'+tabId+'link').className = 'current'; // change the class of the link
		return false;
	
	}
	
	function tabCheck() {
	
		if (tabWasClicked) {
			return true;
		} else {
			$('tabError').style.display = "";
			$('tabErrorArrows').style.display = "";
			return false;
		}
	
	}
	
	function $(obj) {
		return document.getElementById(obj);
	}
	
	function findLocation() {
	
		// If the current value of the button is "Change location" we simply need to reset the form
		if ($('findLocationLink').value == "Change location") {
		
			// Change the "locationPicker" text field	
			$('locationPicker').value = "";
			$('locationPicker').disabled = "";
			$('findLocationLink').value = "Find location";
			$('location').value = "";
			$('postcode').value = "";
			$('locationChoice').className = "grey style2";
			$('locationChoice').innerHTML = "please search for a location first";
			$('postcodeContainer').className = "grey style2";
			$('postcodeContainer').innerHTML = "please search for a location first";
			
		} else {
		
			var v = trim($('locationPicker').value);
			var postcode_regexp = <?php print REGEXP_UK_POSTCODE?>;
			var partial_postcode_regexp = <?php print REGEXP_UK_POSTCODE_FIRST_PART?>;
			
			// If v is a full UK postcode
			if (trim(v) == "") {
			
				alert("Please enter a location");
				
			} else {
			
				// Hide the "Find location" link and show the "Loading" label
				$('findLocationLink').style.display = "none";
				$('findLocationLoadingLabel').style.display = "";
				$('locationLabel').firstChild.nodeValue = v;
				
				// Depending on what v is (full postcode, partial postcode or a string)
				// we call the getLocation fuction (which does the AJAX call)
				if (postcode_regexp.test(v)) {			
		
					// Strip the last three characters from the postcode (to get it's first part)
					v = v.substring(0,v.length-3);
					v = trim(v);
					$('locationPicker').value = v;
					getLocation("postcode",v);
				
				} else if (partial_postcode_regexp.test(v)) {
					
					getLocation("postcode",v);
			
				} else {
					
					getLocation("string",v);
				
				}		
			
			}
		
		}
			
	}
	
	function getLocation(type,value){
	
		var xmlhttp = false; // Clear our fetching variable
		// Internet Explorer
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP")
		} catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP")
			} catch (e) {
				xmlhttp = false
			}
		}
		// Gecko browsers
		if (!xmlhttp) {
			try {
				xmlhttp = new XMLHttpRequest();
			} catch (e) {
				xmlhttp = false;
			}
		}
		
		// Depending on the type ("string" or "postcode")
		// call the appropriate php function.
		if (type == "string") {
			var url = 'ajax-functions.php?action=locationSearch&location=' + value;
		} else {
			var url = 'ajax-functions.php?action=locationSearch&postcode=' + value;
		}
			
		xmlhttp.open('GET', url, true);	
		xmlhttp.onreadystatechange = function() {
			switch (xmlhttp.readyState) {
				case 1: /* $('debug').value += "Send() has NOT been called yet.\r"; */ break;
				case 2: /* $('debug').value += "Send() has been called.\r"; */ break;
				case 3: /* $('debug').value += "Downloading...\r"; */ break;
				case 4:
					var data = xmlhttp.responseText; //The content data which has been retrieved ***
					if (data == "no results found") {
						$('findLocationLink').style.display = "";
						$('findLocationLoadingLabel').style.display = "none";
						alert("no results found");
					} else {
						// Data has been returned to us.
						// Stored them into the 2D array called locations
						eval("var locations = "+data+";");
						// We need to populate the locationsList <select> element.
						// Step 1: Clear it's options
						$('locationsList').options.length = 0;
						// Step 2: Iterate through the 2D array and create a new <option> for each value
						for (var i=0; i<locations.length; i++) {
							$('locationsList').options[$('locationsList').options.length] = new Option(locations[i][0],locations[i][1]);
						}
						$('locationsListContainer').style.display = "";
						$('findLocationLoadingLabel').style.display = "none";
						$('locationsCount').firstChild.nodeValue = locations.length;
					}
					break;
			}	
		}
		xmlhttp.send(null)
		return;
		
	}		
	
	function chooseLocation() {
		
		var obj = $('locationsList');
		// Only proceed if a location has been chosen
		if (obj.selectedIndex == -1) {
	
			alert("Please choose a location before proceeding...");
	
		} else {
	
			// Hide locations list
			$('locationsListContainer').style.display = "none";
		
			// Show the "Find location" link
			$('findLocationLink').style.display = "";
		
			var text = obj.options[obj.selectedIndex].text;
			// Remove the " (postcode)" from the text
			text = text.substring(0,text.indexOf(" ("));
			
			var value = obj.options[obj.selectedIndex].value;
		
			// Change the text of the "locationPicker" text field	
			$('locationPicker').value = text;
			$('locationPicker').disabled = "disabled";
			
			// Change the text of the "locationChoice"
			$('locationChoice').className = "bold";
			$('locationChoice').innerHTML = text;
			
			// Set the location hidden field
			$('location').value = text;
			
			// Change the value of the "postcode" hidden field
			$('postcode').value = value;
			
			// Change the value of the "findLocationLink" button to "Change location"
			$('findLocationLink').value = "Change location";
		
			// Change the value of the "postcodeContainer" label (and class)
			$('postcodeContainer').firstChild.nodeValue = value;
			$('postcodeContainer').className = "bold";
			
		}
		
	}
	
	function cancelChooseLocation() {
		
		$('locationsListContainer').style.display = "none";
		$('findLocationLink').style.display = "";
		
	}
	
	function selectRadio(id) {
		// Change the radio button state
		$('displayPic_'+id).checked = true;
	}	
	
	function trim(toTrim) {
		while(''+toTrim.charAt(0) == " ") { toTrim = toTrim.substring(1,toTrim.length); }
		while(''+toTrim.charAt(toTrim.length-1) == " ") { toTrim = toTrim.substring(0,toTrim.length-1); }
		return toTrim;
	}
		
		</script>
		<h1 class="m0">Edit wanted ad</h1>
		<p><span class="mb0">Please edit your ad with the form below and click &quot;Save changes&quot; and the bottom when done.</span></p>
		<?php print $insertError?>
		<?php if ($error) { ?>
		<p style="margin-bottom:0px;" class="error">Errors where found in your form. Please review all fields and re-submit.</p>
		<?php } ?>
		<form name="wanted" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
		<input type="hidden" name="id" value="<?php print $id?>" />
		<div class="fieldSet">
		<div class="fieldSetTitle">1. Location and dates</div>
				<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right" valign="top"> <span class="obligatory">*</span> Location or postcode: </td>
					<td>
						<p class="m0"><?php print $error['postcode']?><input type="text" name="locationPicker" id="locationPicker" value="<?php print stripslashes($location)?>" <?php if ($location) { ?>disabled="disabled"<?php } ?>/></p>
						<p style="margin:4px 0px 0px 0px"><input type="button" name="findLocationLink" id="findLocationLink" style="display:;" value="<?php print ($location? "Change" : "Find")?> location" onclick="findLocation(); return false;" /><span id="findLocationLoadingLabel" style="display:none;">Loading ...</span></p>					</td>
				</tr>	
				<tr>
					<td align="right">&nbsp;</td>
					<td>
						<div id="locationsListContainer" style="display:none;" class="mb10">
							<p class="mt0 mb10"><strong>CFS has found <span id="locationsCount">4</span> locations that match &quot;<span id="locationLabel">&nbsp;</span>&quot;</strong> <br />Please choose one of the following and press the &quot;Pick location&quot; button to continue :</p>
							<p class="mt0 mb10">
								<select name="locationsList" id="locationsList" size="5" ></select>
							</p>
							<p class="m0">
								<input type="button" name="locationsChooser" value="Pick location" onclick="chooseLocation();"/>
								<input type="button" name="locationsChooserCancel" id="locationsChooserCancel" value="Cancel" onclick="cancelChooseLocation();" />
							</p>
						</div>
						<p class="grey style2" style="margin:0px 0px 2px 0px;">Valid locations:</p>
						<ul class="grey style2" style="margin:0px 0px 0px 1em; padding-left:1em;">
							<li>The first part of a UK postcode (<em>e.g. W9</em>)</li> 
							<li>Name of a city, town or village,  (<em>e.g. Nottingham, Woodbridge, Long Melford</em>)</li> 
							<li>Name of a city district (<em>e.g. Hammersmith, Paddington, West Bridgford</em>)</li>
							<li>Name of a London tube station (<em>e.g. Westminster Station</em>)</li>
						</ul>					</td>
				</tr>
				<tr>
					<td width="200" align="right">Chosen location:</td>
					<td><span class="<?php print ($location? "bold":"grey style2")?>" id="locationChoice"><?php print ($location? $location : "please search for a location first")?></span>
						<input type="hidden" name="location" id="location" value="<?php print $location?>" />					</td>
				</tr>				
				<tr>
					<td align="right">Postcode :</td>
					<td>
						<?php if ($postcode) { ?>
						<label class="bold" id="postcodeContainer"><?php print $postcode?></label>
						<?php } else { ?>
						<label class="grey style2" id="postcodeContainer">please search for a location first</label>
						<?php } ?>
						<input type="hidden" name="postcode" id="postcode" value="<?php print $postcode?>" />					</td>
				</tr>
				</table>
		
		
			<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right">Max distance from postcode:</td>
					<td><?php print createDropDown("distance_from_postcode",getMilesArray(true),$distance_from_postcode)?><?php print $error['distance_from_postcode']?></td>
				</tr>
				<tr>
				  <td align="right">&nbsp;</td>
				  <td>&nbsp;</td>
			  </tr>
				<tr>
					<td align="right">Required from:</td>
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
			<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right" valign="top"><span class="obligatory">*</span> Accommodation type could be:</td>
					<td>
						
						<table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td><?php print createCheckbox("accommodation_type_flat_share","1",$accommodation_type_flat_share);?></td>
								<td>House / Flatshare (a house or flat  shared with others)</td>
								<td><?php print $error['accommodation_type']?></td>
							</tr>
							<tr>
								<td><?php print createCheckbox("accommodation_type_family_share","1",$accommodation_type_family_share);?></td>
								<td>Family Share (live with a family or a married couple)</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("accommodation_type_whole_place","1",$accommodation_type_whole_place);?></td>
								<td>Whole Place (an unoccupied flat or house)</td>
								<td>&nbsp;</td>
							</tr>
						</table>					</td>				
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Building type could be:</td>
					<td>
						<table cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td><?php print createCheckbox("building_type_house","1",$building_type_house);?></td>
								<td>House</td>
								<td><?php print createCheckbox("building_type_flat","1",$building_type_flat);?></td>
								<td>Flat</td>
								<td><?php print $error['building_type']?></td>
							</tr>
						</table>					</td>
				</tr>
				<tr>
					<td align="right">Number of bedrooms  you require:</td>
					<td><?php print createRadioGroup("bedrooms_required",getBedroomArray(),$bedrooms_required)?></td>
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
					<td width="200" align="right"><span class="obligatory">* </span>Monthly price (max), per bedroom:</td>
					<td>&pound;&nbsp;<input type="text" name="price_pcm" id="price_pcm" value="<?php print $price_pcm?>"/><?php print $error['price_pcm']?></td>
				</tr>				
				<tr>
					<td width="200" align="right" valign="top">The accommodation <em><u>must</u></em> have:</td>
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
								<td><?php print createCheckbox("parking","1",$parking);?>somewhere nearby to park a car</td>
							</tr>
							<tr>
								<td><?php print createCheckbox("bicycle_store","1",$bicycle_store);?>a suitable place to store a bicycle</td>
								<td>&nbsp;</td>
							</tr>
						</table>					</td>
				</tr>
			</table>
		</div>
		<div class="fieldSet">
		<div class="fieldSetTitle">3. Accommodation seekers</div>
			<table width="100%"  border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="190" align="right"><span class="obligatory">*</span> Age of youngest accommodation seeker: </td>
					<td><?php print $error['current_min_age']?><?php print createDropDown("current_min_age",getAgeArray("-- Please select --"),$current_min_age);?>
				<!--	<a href="#" 
						onmouseover="show_tooltip('<p><strong>Accommodation seeker age</strong></p><p>This is the age of those looking for accommodation.<br /><br />If only one person is looking then min and max age would be the same, although you may prefer to express an indicative  age range (e.g. 30-35).');" 
						onmouseout="hide_tooltip();"
					>(?)</a> -->
					</td>
				</tr>
				<tr>
					<td width="190" align="right"><span class="obligatory">*</span> Age of oldest accommodation seeker:  </td>
					<td><?php print $error['current_max_age']?><?php print createDropDown("current_max_age",getAgeArray("-- Please select --"),$current_max_age);?>
					<span class="grey"><strong> If one person,</strong> youngest and oldest would be the same. </span></td>
				</tr>
                <tr>
					<td width="190" align="right"><span class="obligatory">* </span>Number of males seekers:</td>
					<td><?php print createRadioGroup("current_num_males",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5"),$current_num_males)?><?php print $error['current_num_males']?></td>
				</tr>
				<tr>
					<td width="190" align="right"><span class="obligatory">*</span> Number of females seekers:</td>
					<td><?php print createRadioGroup("current_num_females",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5"),$current_num_females)?></td>
				</tr>				
				<tr>
					<td width="190" align="right">Occupation:</td>
					<td><?php print createRadioGroup("current_occupation",getOccupationArray(),$current_occupation)?><?php print $error['current_occupation']?></td>
				</tr>
				<tr>
					<td width="190" align="right">Are you a married couple:</td>
					<td><?php print createCheckbox("current_is_couple","1",$current_is_couple);?></td>
				</tr>
				<tr>
					<td width="190" align="right">Are you family with children:</td>
					<td><?php print createCheckbox("current_is_family","1",$current_is_family);?></td>
				</tr>
				<tr>
					<td width="190" align="right">Someone who, if asked, could provide <br />a recommendation from a church:</td>
					<td>
						<?php print createCheckbox("church_reference","1",$church_reference);?>
						<span class="grey">simply to say that you are known to a church fellowship who could in someway vouch for your character</span>					</td>
				</tr>
				<tr>
					<td width="190" align="right">Church attended:</td>
				  <td>
						<?php print $error['church_attended']?>
						<input type="text" name="church_attended" id="church_attended" value="<?php print stripslashes($church_attended)?>" />
				<!--	  <a href="#" 
						onmouseover="show_tooltip('<p><strong>Church attended</strong></p><p>If moving to a new area it can be of interest for others to see the church you most recently attended, where you currently live, or just &quot;looking for a church&quot; if you are looking to make a church connection for the first time.<br /><br />It is helpful to put the name of a church including a location, as there can be a lot of churches by the same name in an area.');" 
						onmouseout="hide_tooltip();"
					>(?)</a> -->
					  <span class="grey">&quot;St John's church, York&quot; / &quot;Looking for a church &quot;</span>					</td>
				</tr>
				<tr>
					<td width="190" align="right" valign="top">Church website: </td>
				    <td>
						<input type="text" name="church_url" id="church_url" value="<?php print stripslashes($church_url)?>" />
					  <span class="grey"> www.our-church-website.org</span>					</td>
				</tr>
				<tr>
					<td width="190" align="right" valign="top">Now say some nice<br />
					  things about yourself/yourselves.<br />
					  A full and friendly description
					  is helpful:
  <p id="accommodation_situation_label"><?php print nl2br(DESC_ACCOMMODATION_SITUATION)?></p></td><td><textarea name="accommodation_situation" rows="10" id="accommodation_situation" style="width:100%"><?php print stripslashes($accommodation_situation)?></textarea></td>
				</tr>		
			</table>
		</div>
		<div class="fieldSet">
		<div class="fieldSetTitle">4. Preferred household </div>
			<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Maximum number of adult members:</td>
					<td><?php print $error['shared_adult_members']?><?php print createRadioGroup("shared_adult_members",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4"=>"4+"),$shared_adult_members)?></td>
				</tr>
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Household could be: </td>
					<td>
						<table cellpadding="0" cellspacing="0" border="0" class="prTD5">
							<tr>	
								<td><?php print createCheckbox("shared_males","1",$shared_males)?></td>
								<td>All male</td>
								<td><?php print createCheckbox("shared_females","1",$shared_females)?></td>
								<td>All female</td>
								<td><?php print createCheckbox("shared_mixed","1",$shared_mixed)?></td>
								<td>Mixed household (male and female)</td>
								<td><?php print $error['shared_gender']?></td>
							</tr>
						</table>					</td>
				</tr>
				<tr>
					<td align="right">Age of youngest member:</td>
					<td><?php print createDropDown("shared_min_age",getAgeArray("Any"),$shared_min_age);?><?php print $error['shared_age']?></td>
				</tr>
				<tr>
					<td align="right">Age of oldest member:</td>
					<td><?php print createDropDown("shared_max_age",getAgeArray("Any"),$shared_max_age);?></td>
				</tr>
				
				<tr>
					<td width="200" align="right" valign="top">It could comprise of:</td>
					<td>
						<?php print createCheckbox("shared_student","1",$shared_student);?>Students (&lt;22yrs)<br />
						<?php print createCheckbox("shared_mature_student","1",$shared_mature_student);?>Mature students<br />
						<?php print createCheckbox("shared_professional","1",$shared_professional);?>Professionals					</td>
				</tr>
				<tr>
					<td width="200" align="right">The owner could be a member of the household:</td>
					<td><?php print createCheckbox("shared_owner_lives_in","1",$shared_owner_lives_in);?></td>
				</tr>
				<tr>
					<td width="200" align="right">It could have a married couple:</td>
					<td><?php print createCheckbox("shared_married_couple","1",$shared_married_couple);?></td>
				</tr>
				<tr>
					<td width="200" align="right">It could be family that has children:</td>
					<td><?php print createCheckbox("shared_family","1",$shared_family);?></td>
				</tr>
			</table>
		</div>
		<div class="fieldSet">
		<div class="fieldSetTitle">5. Contact details</div>
			<p><span class="mt10"><strong>CFS does not disclose your email address to those who see your advert.</strong> <br />
Those responding to your ad can do so through a form on CFS, which helps CFS to protect  members from certain types of scam attempts. <br />
If you wish to you may include your phone number and any additional contact details within your advert. </span></p>
			<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td width="200" align="right"><span class="obligatory">*</span> Contact name:</td>
					<td><?php print $error['contact_name']?><input name="contact_name" type="text" id="contact_name" value="<?php print stripslashes($contact_name)?>"/>
					&nbsp;<span class="grey">e.g. &quot;John Smith&quot;</span></td>
				</tr>				
				<tr>
					<td width="200" align="right">Contact phone number :</td>
					<td><input name="contact_phone" type="text" id="contact_phone" value="<?php print $contact_phone?>"/>
					&nbsp;<span class="grey">optional</span></td>
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
			<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
				<tr>
					<td colspan="2"><h2 class="m0">Flat-match</h2></td>
				</tr>
				<tr>
					<td width="20"><?php print createCheckbox("flatmatch",1,$flatmatch);?></td>
					<td>Flat-Match will automatically email you details of suitable new offered accommodation ads, while your ad remains published on the site.</td>
				</tr>
				<tr>
					<td colspan="2"><h2 class="m0">Pal-Up</h2></td>
				</tr>
				<tr>
					<td width="20"><?php print createCheckbox("palup","1",$palup);?></td>
					<td>Pal-up helps you to connect with others also looking for accommodation, to explore finding somewhere together. <br />
				    Pal-up automatically emails you new wanted accommodation adverts, similar to yours, and of those also willing to pal-up.</td>
				</tr>
			</table>
		</div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="mt10 mb10">
			<tr>
				<td valign="top">
				<input type="submit" name="Submit" value="Save changes"/>&nbsp;
				<input type="submit" name="cancel" value="Cancel" />
				</p>&nbsp;</td>
				<td align="right" valign="top">Having problems posting an ad?<br />Email <a href="mailto:problems@ChristianFlatShare.org">problems@ChristianFlatShare.org</a> </td>
			</tr>
		</table>
<!--		<p class="mb0">
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
