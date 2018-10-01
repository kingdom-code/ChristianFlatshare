<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	// Dissallow access if an id has not been specified
	if (!isset($_REQUEST['id'])) { header("Location:index.php"); exit; } else { $id = $_REQUEST['id']; }
	if (isset($_POST['cancel'])) { header("Location:offered-ads.php"); exit; }
		
	// Initialise all needed variables
	$now = new DateTime();
	if (isset($_POST['cancel'])) { header("Location:index.php"); exit; }
	
	// FIRST PANE elements
	if (isset($_POST['postcode'])) { $postcode = $_POST['postcode']; } else { $postcode = NULL; }
	if (isset($_POST['street_name'])) { $street_name = $_POST['street_name']; } else { $street_name = NULL; }
	if (isset($_POST['town'])) { $town = $_POST['town']; } else { $town = NULL; }
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
	if (isset($_POST['accommodation_description'])) { $accommodation_description = $_POST['accommodation_description']; } else { $accommodation_description = NULL; }
	
	// THIRD PANE elements
	if (isset($_POST['current_min_age'])) { $current_min_age = $_POST['current_min_age']; } else { $current_min_age = 17; }
	if (isset($_POST['current_max_age'])) { $current_max_age = $_POST['current_max_age']; } else { $current_max_age = 60; }
	if (isset($_POST['current_num_males'])) { $current_num_males = $_POST['current_num_males']; } else { $current_num_males = NULL; }
	if (isset($_POST['current_num_females'])) { $current_num_females = $_POST['current_num_females']; } else { $current_num_females = NULL; }
	if (isset($_POST['current_occupation'])) { $current_occupation = $_POST['current_occupation']; } else { $current_occupation = NULL; }
	if (isset($_POST['owner_lives_in'])) { $owner_lives_in = $_POST['owner_lives_in']; } else { $owner_lives_in = NULL; }
	if (isset($_POST['current_is_couple'])) { $current_is_couple = $_POST['current_is_couple']; } else { $current_is_couple = NULL; }
	if (isset($_POST['current_is_family'])) { $current_is_family = $_POST['current_is_family']; } else { $current_is_family = NULL; }
	if (isset($_POST['church_attended'])) { $church_attended = $_POST['church_attended']; } else { $church_attended = NULL; }

	if (isset($_POST['church_url'])) { $church_url = strip_http(trim($_POST['church_url'])); } else { $church_url = NULL; }
	if (isset($_POST['household_description'])) { $household_description = $_POST['household_description']; } else { $household_description = NULL; }
	
	// FOURTH PANE elements
	if (isset($_POST['suit_gender'])) { $suit_gender = $_POST['suit_gender']; } else { $suit_gender = "Mixed"; }
	if (isset($_POST['suit_min_age'])) { $suit_min_age = $_POST['suit_min_age']; } else { $suit_min_age = NULL; }
	if (isset($_POST['suit_max_age'])) { $suit_max_age = $_POST['suit_max_age']; } else { $suit_max_age = NULL; }
	if (isset($_POST['suit_student'])) { $suit_student = $_POST['suit_student']; } else { $suit_student = NULL; }
	if (isset($_POST['suit_mature_student'])) { $suit_mature_student = $_POST['suit_mature_student']; } else { $suit_mature_student = NULL; }
	if (isset($_POST['suit_professional'])) { $suit_professional = $_POST['suit_professional']; } else { $suit_professional = NULL; }
	if (isset($_POST['suit_married_couple'])) { $suit_married_couple = $_POST['suit_married_couple']; } else { $suit_married_couple = NULL; }
	if (isset($_POST['suit_family'])) { $suit_family = $_POST['suit_family']; } else { $suit_family = NULL; }
	if (isset($_POST['church_reference'])) { $church_reference = $_POST['church_reference']; } else { $church_reference = 1; }
	
	// FIFTH PANE elements
	if (isset($_POST['contact_name'])) { $contact_name = $_POST['contact_name']; } else { $contact_name = NULL; }
	if (isset($_POST['contact_phone'])) { $contact_phone = $_POST['contact_phone']; } else { $contact_phone = NULL; }
	if (isset($_POST['expiry_date'])) { $expiry_date = $_POST['expiry_date']; } else { $expiry_date = NULL; }
	
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
		if (!preg_match('/^[1-9]\d*$/',$price_pcm)) { $error['price_pcm'] = 'Please enter a monthly price with no decimal points'; }
		if ($deposit_required) {
			if (!preg_match('/^[1-9]\d*$/',$deposit_required)) { $error['deposit_required'] = 'Deposit must be an integer value with no decimal points'; }
		}
		if ($average_bills) {
			if (!preg_match('/^[1-9]\d*$/',$average_bills)) { $error['average_bills'] = 'Average bills must be an integer value with no decimal points'; }
		}
		if (!$bedrooms_available) { $error['bedrooms_available'] = 'Please select number of offered bedrooms'; }
		if ($accommodation_type && 
			($accommodation_type == "flat share" || $accommodation_type == "family share") &&
			$bedrooms_total == $bedrooms_available) {
			$error['bedrooms_available'] = 'You have not picked &quot;Whole place&quot;.<br/>Available bedrooms must be less than the total number of bedrooms.<br/>';
		}		
		if (!$bedrooms_total) { $error['bedrooms_total'] = 'Please select total number of bedrooms in the property'; }
		if ($bedrooms_available && $bedrooms_double && ($bedrooms_double > $bedrooms_available)) { $error['bedrooms_double'] = 'Number of double bedrooms exceeds available ones.'; }
		if ($bedrooms_total && $bedrooms_available && ($bedrooms_available > $bedrooms_total)) { $error['bedrooms_available'] = 'Number of available bedrooms cannot exceed total'; }
		
		// VALIDATE THIRD PANE (only if accommodation_type != "whole place")
		if ($accommodation_type != "whole place") {
			if ($current_min_age && $current_max_age) {
				if ($current_max_age < $current_min_age) { $error['age'] = 'Minimum age must be less or equal to max age'; }
			}
			if (!$current_num_males && !$current_num_females) {
				$error['current_num_males'] = "Please indicate number of current members of the household<br/>";
			}
			if (!$current_occupation) {
				$error['current_occupation'] = "Please indicate occupation of current members of the household<br/>";
			}			
		}
		
		// VALIDATE FOURTH PANE
		if ($suit_min_age && $suit_max_age && ($suit_max_age < $suit_min_age)) {
			$error['suit_age'] = 'Minimum age must be less or equal to max age';
		}
		
		// VALIDATE FIFTH PANE
		if (!$contact_name) { $error['contact_name'] = 'Please enter your contact name'; }
		if (!$expiry_date) { $error['expiry_date'] = 'Please select the date of expiration for this ad'; }
		
		// Apply the necessary formating to the error array
		if ($error) {
			
			array_walk($error,formatError);
		
		} else {
			
			// NO ERROR: Update cf_offered table
			$query = '
			update cf_offered set
				
				last_updated_date = "'.$now->getDate().'",
				expiry_date = "'.$expiry_date.'",
				available_date = "'.$available_date.'",
				min_term = "'.$min_term.'",
				max_term = "'.$max_term.'",
				price_pcm = "'.$price_pcm.'",
				deposit_required = "'.$deposit_required.'",
				incl_utilities = "'.$incl_utilities.'",
				incl_council_tax = "'.$incl_council_tax.'",
				average_bills = "'.$average_bills.'",
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
				approved = "0",
				published = "'.DEFAULT_PUBLISH_STATUS.'"
				
			where offered_id = "'.$id.'"';	
			$debug .= debugEvent("Ze uber UPDATE query",$query);		
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				header("Location: offered-ads.php?report=updateSuccess");
			} else {
				header("Location: offered-ads.php?report=updateFailure");
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
		foreach($ad as $key => $value) { ${$key} = $value; } // Create variables for all array keys
		
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/admin.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>ChristianFlatShare.org administration</title>
<!-- InstanceEndEditable -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
				$('price_pcm_left_label').firstChild.nodeValue = "House price PCM:";
			} else { // It's a flat
				$('price_pcm_left_label').firstChild.nodeValue = "Flat price PCM:";
			}
			$('price_pcm_right_label').firstChild.nodeValue = "";
			
		} else {

			$('tab3wholeplace').style.display = "none";
			$('tab3contents').style.display = "";
			
			// Also, change the price_pcm labels
			$('price_pcm_left_label').firstChild.nodeValue = "Price per room PCM:";
			$('price_pcm_right_label').firstChild.nodeValue = "(Average price per room)";
			
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
<!-- InstanceEndEditable -->
<link href="../styles/admin.css" rel="stylesheet" type="text/css" />
<!-- InstanceParam name="highlightPage" type="text" value="3" -->
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="HEADER">
    <tr>
        <td width="370"><img src="../images/admin-header.gif" width="355" height="40" /></td>
        <td align="right" style="padding-right:15px;" nowrap="nowrap"><a href="../logout.php">Log out administrator</a></td>
    </tr>
</table>
<div><img src="../images/admin-header-blue-back.gif" width="100%" height="6"></div>
<div id="MENU">
	<ul>
		<li><a href="index.php" class="">Main menu</a></li>
		<li><a href="members.php" class="">View members</a></li>
		<li><a href="offered-ads.php" class="current">View offered ads</a></li>
		<li><a href="wanted-ads.php" class="">View wanted ads</a></li>
		<li><a href="emails.php" class="">Email Replies</a></li>	
		<li><a href="cfs_feedback.php" class="">Feedback</a></li>				
		<li><a href="logins.php" class="">Logins</a></li>				
		<li><a href="stats.php" class="">Statistics</a></li>						
		<!--<li><a href="#" class="">View payment history</a></li>-->
	</ul>
</div>
<div><img src="../images/spacer.gif" width="100%" height="1"></div>
<div id="MAIN_CONTENT">
<!-- InstanceBeginEditable name="mainContent" -->
<h1 class="mt0">Offered Ads - Editing ad id <?php print $id?></h1>
<p><strong>Use the form below to edit ad <?php print $id?>.</strong><br />Please make sure you select a new auto-expiration period (at the bottom of this form).</p>
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
			<td width="200" align="right" valign="top"> <p style="margin:0px;line-height:26px;">Full postcode of offered place:</p></td>
			<td><strong><?php print $postcode?></strong><input type="hidden" name="postcode" value="<?php print $postcode?>" /></td>
		</tr>
		<tr>
			<td width="200" align="right">Street name:</td>
			<td><strong><?php print $street_name?></strong><input type="hidden" name="street_name" value="<?php print $street_name?>" /></td>
		</tr>
		<tr>
			<td width="200" align="right">Town:</td>
			<td><strong><?php print $town?></strong><input type="hidden" name="town" value="<?php print $town?>" /></td>
		</tr>
		<tr>
			<td align="right">Date available from:</td>
			<td><?php print createDateDropDown("available_date",180,$available_date,FALSE,"dateSelector")?></td>
		</tr>
		<tr>
			<td align="right">Minimum term:</td>
			<td><?php print createDropDown("min_term",getTermsArray("minimum"),$min_term);?>&nbsp;<span class="grey">(length of stay)</span></td>
		</tr>
		<tr>
			<td align="right">Maximum term:</td>
			<td><?php print createDropDown("max_term",getTermsArray("maximum"),$max_term);?>&nbsp;<span class="grey">(length of stay)</span><?php print $error['term']?></td>
		</tr>
	</table>
</div>
<div class="fieldSet">
<div class="fieldSetTitle">2. Details of accommodation</div>
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
				$price_pcm_left_label = 'Price per room PCM:';
				$price_pcm_right_label = '(Average price per room)';
			} else {
				if ($building_type == "house") {
					$price_pcm_left_label = 'House price PCM:';
				} else {
					$price_pcm_left_label = 'Flat price PCM:';
				}
				$price_pcm_right_label = '&nbsp;';
			}
		?>
		<tr>
			<td width="200" align="right"><span id="price_pcm_left_label"><?php print $price_pcm_left_label?></span></td>
			<td>&pound;&nbsp;<input type="text" name="price_pcm" id="price_pcm" value="<?php print $price_pcm?>"/>&nbsp;<span class="grey" id="price_pcm_right_label"><?php print $price_pcm_right_label?></span><?php print $error['price_pcm']?></td>
		</tr>
		<tr>
			<td width="200" align="right">Deposit required:</td>
			<td>&pound;&nbsp;<input type="text" name="deposit_required" id="deposit_required" value="<?php print $deposit_required?>" /><?php print $error['deposit_required']?></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Utilities included:</td>
			<td>
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td><?php print createCheckbox("incl_utilities","1",$incl_utilities);?></td>
						<td>Utilities (Gas, Water, Electricity)</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("incl_council_tax","1",$incl_council_tax);?></td>
						<td>Council tax</td>
					</tr>
				</table>						
			</td>
		</tr>
		<tr>
			<td width="200" align="right">Indication of share of monthly bills:</td>
			<td>&pound;&nbsp;<input type="text" name="average_bills" id="average_bills" value="<?php print $average_bills?>" /><?php print $error['average_bills']?></td>
		</tr>
		<tr>
			<td width="200" align="right">Number of bedrooms available:</td>
					<td><?php print $error['bedrooms_available']?><?php print createRadioGroup("bedrooms_available",getBedroomArray(),$bedrooms_available)?></td>
		</tr>
		<tr>
			<td width="200" align="right">How many available rooms are double-sized:</td>
			<td><?php print createRadioGroup("bedrooms_double",getBedroomArray(true),$bedrooms_double)?><?php print $error['bedrooms_double']?></td>
		</tr>
		<tr>
			<td width="200" align="right">Total number of bedrooms in the accommodation:</td>
			<td><?php print createRadioGroup("bedrooms_total",getBedroomArray(),$bedrooms_total)?></td>
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
				</table>
			</td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Say some other things which are helpful to know (about your place):<p id="accommodation_description_label"><?php print nl2br(DESC_ACCOMMODATION)?></p></td>
					<td><textarea name="accommodation_description" rows="8" id="accommodation_description" style="width:100%"><?php print stripslashes($accommodation_description)?></textarea></td>
		</tr>
	</table>
</div>
<div class="fieldSet">
<div class="fieldSetTitle">3. The Household</div>
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
			<td width="200" align="right">Minimum adult age: </td>
			<td><?php print createDropDown("current_min_age",getAgeArray(),$current_min_age);?><?php print $error['age']?></td>
		</tr>
		<tr>
					<td width="200" align="right">Maximum adult age:</td>
			<td><?php print createDropDown("current_max_age",getAgeArray(),$current_max_age);?></td>
		</tr>
		<tr>
			<td width="200" align="right">Number of adult male members:</td>
					<td><?php print $error['current_num_males']?><?php print createRadioGroup("current_num_males",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4+"=>"4+"),$current_num_males)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Number of adult female members:</td>
			<td><?php print createRadioGroup("current_num_females",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4+"=>"4+"),$current_num_females)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Occupation:</td>
					<td><?php print $error['current_occupation']?><?php print createRadioGroup("current_occupation",getOccupationArray(),$current_occupation)?></td>
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
			<td width="200" align="right">Church attended: </td>
			<td>
						<input type="text" name="church_attended" id="church_attended" value="<?php print stripslashes($church_attended)?>" />
				<span class="grey">(e.g. &quot;your church, your town&quot;)</span>			</td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Church URL: </td>
			<td>
				<input type="text" name="church_url" id="church_url" value="<?php print $church_url?>" />
				<span class="grey">(e.g.  www.our-church-website.org)</span>			</td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Now say some nice (and some informative) things about yourself(/selves):
				<p id="household_description_label"><?php print nl2br(DESC_HOUSEHOLD)?></p></td>
					<td><textarea name="household_description" rows="12" id="household_description" style="width:100%"><?php print stripslashes($household_description)?></textarea></td>
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
			<td width="200" align="right">Gender:</td>
			<td>
				<?php print $error['suit_gender']?>
				<?php print createRadioGroup("suit_gender",getGenderArray(),$suit_gender)?>
			</td>
		</tr>
		<tr>
			<td align="right">Minumum adult age: </td>
			<td><?php print createDropDown("suit_min_age",getAgeArray(false,true),$suit_min_age);?></td>
		</tr>
		<tr>
			<td align="right">Maximum adult age: </td>
			<td><?php print createDropDown("suit_max_age",getAgeArray(false,true),$suit_max_age);?><?php print $error['suit_age']?></td>
		</tr>
		
		<tr>
			<td width="200" align="right" valign="top">Occupation:</td>
			<td>
				<?php print createCheckbox("suit_student","1",$suit_student);?>Students (&lt;22yrs)<br />
				<?php print createCheckbox("suit_mature_student","1",$suit_mature_student);?>Mature students<br />
				<?php print createCheckbox("suit_professional","1",$suit_professional);?>Professionals
			</td>
		</tr>
		<tr>
			<td width="200" align="right">A married couple:</td>
			<td><?php print createCheckbox("suit_married_couple","1",$suit_married_couple);?></td>
		</tr>
		<tr>
			<td width="200" align="right">A family with children:</td>
			<td><?php print createCheckbox("suit_family","1",$suit_family);?></td>
		</tr>
		<tr>
			<td width="200" align="right">Someone who, if asked, could provide <br />a recommendation from a church:</td>
			<td>
				<?php print createCheckbox("church_reference","1",$church_reference);?>
				<span class="grey">(simply to say that they are known to a church fellowship which could in someway vouch for their character)</span>
			</td>
		</tr>
	</table>
</div>
<div class="fieldSet">
<div class="fieldSetTitle">5. Your contact details</div>
			<p>Those responding to your ad can do so through a website form, <strong>which doesn't disclose your email address</strong> unless you have included it in the advert.<br/>If you would like your phone number on the advert please add it below.</p>
	<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
		<tr>
			<td width="200" align="right">Contact name:</td>
					<td><input name="contact_name" type="text" id="contact_name" value="<?php print stripslashes($contact_name)?>"/>&nbsp;<span class="grey">(e.g. &quot;John Smith&quot;)</span>&nbsp;<?php print $error['contact_name']?></td>
		</tr>				
		<tr>
			<td width="200" align="right">Contact phone number (optional):</td>
			<td><input name="contact_phone" type="text" id="contact_phone" value="<?php print $contact_phone?>"/></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Advert auto-expires on:</td>
			<td><?php print createDateDropDown("expiry_date",42,$expiry_date,TRUE,"dateSelector")?><?php print $error['expiry_date']?></td>
		</tr>
	</table>
</div>
<p>Please complete all 5 sections of the form before proceeding.</p>
<p class="m0">
	<input type="submit" name="Submit" value="Save changes"/>&nbsp;
	<input type="submit" name="cancel" value="Cancel" />
</p>
</form>
<p><a href="index.php">Return to the main administration page</a></p>
<!-- InstanceEndEditable -->
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
</body>
<!-- InstanceEnd --></html>
