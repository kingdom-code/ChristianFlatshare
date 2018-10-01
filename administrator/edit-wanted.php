<?php

// Autoloader
require_once __DIR__ . '/../web/global.php';
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }
	// Dissallow access if an id has not been specified
	if (!isset($_REQUEST['id'])) { header("Location:index.php"); exit; } else { $id = $_REQUEST['id']; }
	if (isset($_POST['cancel'])) { header("Location:wanted-ads.php"); exit; }
	
	// Initialise all needed variables
	$now = new DateTime();
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
	if (isset($_POST['current_min_age'])) { $current_min_age = $_POST['current_min_age']; } else { $current_min_age = 17; }
	if (isset($_POST['current_max_age'])) { $current_max_age = $_POST['current_max_age']; } else { $current_max_age = 60; }
	if (isset($_POST['current_occupation'])) { $current_occupation = $_POST['current_occupation']; } else { $current_occupation = NULL; }
	if (isset($_POST['current_is_couple'])) { $current_is_couple = $_POST['current_is_couple']; } else { $current_is_couple = NULL; }
	if (isset($_POST['current_is_family'])) { $current_is_family = $_POST['current_is_family']; } else { $current_is_family = NULL; }
	if (isset($_POST['church_reference'])) { $church_reference = $_POST['church_reference']; } else { $church_reference = 1; }
	if (isset($_POST['church_attended'])) { $church_attended = $_POST['church_attended']; } else { $church_attended = NULL; }
	if (isset($_POST['church_url'])) { $church_url = strip_http(trim($_POST['church_url'])); } else { $church_url = NULL; }	
	if (isset($_POST['accommodation_situation'])) { $accommodation_situation = $_POST['accommodation_situation']; } else { $accommodation_situation = NULL; }		
	
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
	if (isset($_POST['expiry_date'])) { $expiry_date = $_POST['expiry_date']; } else { $expiry_date = NULL; }
	if (isset($_POST['flatmatch'])) { $flatmatch = $_POST['flatmatch']; } else { $flatmatch = 1; }
	if (isset($_POST['palup'])) { $palup = $_POST['palup']; } else { $palup = 0; }
	
	// If form was submitted, perform the necessary validation
	if ($_POST) {
		
		// VALIDATE FIRST PANE
		if (!$distance_from_postcode) { $error['distance_from_postcode'] = 'Please indicate distance from place'; }

		// If a min_term AND a max_term have been defined, make sure that max_term is after min_term
		if ($min_term && $max_term != "999") {
			if ($min_term > $max_term) {
				$error['term'] = 'Max term must be larger than the minimum term';
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
		if (!preg_match('/^[1-9]\d*$/',$price_pcm)) { $error['price_pcm'] = 'Please enter a monthly price with no decimal points'; }
		
		// VALIDATE THIRD PANE
		if (!$current_num_males && !$current_num_females) {
			$error['current_num_males'] = 'Please indicate how many males / females are looking for a flatshare';
		}
		if ($current_min_age && $current_max_age) {
			if ($current_max_age < $current_min_age) { $error['age'] = 'Minimum age must be less or equal to max age'; }
		}
		if (!$current_occupation) { $error['current_occupation'] = 'Please choose your occupation'; }
		
		// VALIDATE FOURTH PANE
		if (!$shared_adult_members && ($accommodation_type_family_share || $accommodation_type_flat_share)) {
			$error['shared_adult_members'] = "For flatshare and family share, max number of adult members must be greater then zero<br/>";
		}		
		if (
			!$shared_males && 
			!$shared_females && 
			!$shared_mixed && 
			($accommodation_type_family_share || $accommodation_type_flat_share)
		) {
			$error['shared_gender'] = "Please choose your household gender preference";
		}		
		if ($shared_min_age && $shared_max_age && ($shared_min_age > $shared_max_age)) {
			$error['shared_age'] = "Min age cannot be larger than max";
		}
		
		// VALIDATE FIFTH PANE
		if (!$expiry_date) { $error['expiry_date'] = 'Please select expiration date'; }
		
		// Apply the necessary formating to the error array
		if ($error) {
			array_walk($error,formatError);
		} else {
			
			// NO ERROR: Update cf_wanted table.
			
			$query = '
			update cf_wanted set 
			
				last_updated_date = "'.$now->getDate().'",
				expiry_date = "'.$expiry_date.'",
				distance_from_postcode = "'.$distance_from_postcode.'",
				available_date = "'.$available_date.'",
				min_term = "'.$min_term.'",
				max_term = "'.$max_term.'",
				price_pcm = "'.$price_pcm.'",
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
				approved = "0",
				published = "'.DEFAULT_PUBLISH_STATUS.'"					
				
			where wanted_id = "'.$id.'"';	
			$debug .= debugEvent("Ze uber UPDATE query",$query);		
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				header("Location: wanted-ads.php?report=updateSuccess");
			} else {
				die($query);
				header("Location: wanted-ads.php?report=updateFailure");
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
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/admin.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>ChristianFlatShare.org administration</title>
<!-- InstanceEndEditable -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../styles/admin.css" rel="stylesheet" type="text/css" />
<!-- InstanceParam name="highlightPage" type="text" value="4" -->
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
		<li><a href="offered-ads.php" class="">View offered ads</a></li>
		<li><a href="wanted-ads.php" class="current">View wanted ads</a></li>
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
<p style="margin-bottom:0px;" class="error">Errors where found in your form. Please review all fields and re-submit.</p>
<?php } ?>
<form name="wanted" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="id" value="<?php print $id?>" />
<div class="fieldSet">
<div class="fieldSetTitle">1. Location and dates</div>
	<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
		<tr>
			<td width="200" align="right" valign="top"> Location or postcode: </td>
					<td><strong><?php print $location?></strong><input type="hidden" name="location" value="<?php print stripslashes($location)?>" /></td>
		</tr>		
		<tr>
			<td align="right">Postcode :</td>
			<td><strong><?php print $postcode?></strong><input type="hidden" name="postcode" value="<?php print $postcode?>" /></td>
		</tr>
		<tr>
			<td width="200" align="right">Max distance from postcode :</td>
			<td><?php print createDropDown("distance_from_postcode",getMilesArray(true),$distance_from_postcode)?><?php print $error['distance_from_postcode']?></td>
		</tr>
		<tr>
			<td align="right">Should be available from:</td>
			<td><?php print createDateDropDown("available_date",180,$available_date,FALSE,"dateSelector")?></td>
		</tr>
		<tr>
			<td align="right">Minimum term  :</td>
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
	<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
		<tr>
			<td width="200" align="right" valign="top">Accommodation type could be:</td>
			<td>
				
				<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><?php print createCheckbox("accommodation_type_flat_share","1",$accommodation_type_flat_share);?></td>
						<td>Flatshare (a flat or house shared with others)</td>
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
				</table>
			</td>				
		</tr>
		<tr>
			<td width="200" align="right">Building type could be:</td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td><?php print createCheckbox("building_type_house","1",$building_type_house);?></td>
						<td>House</td>
						<td><?php print createCheckbox("building_type_flat","1",$building_type_flat);?></td>
						<td>Flat</td>
						<td><?php print $error['building_type']?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right">Number of bedrooms required:</td>
			<td><?php print createRadioGroup("bedrooms_required",getBedroomArray(),$bedrooms_required)?></td>
		</tr>
		<tr>
			<td align="right">Furnishing:</td>
			<td><?php print createDropDown("furnished",getFurnishedArray(),$furnished);?></td>
		</tr>
		<tr>
			<td width="200" align="right">Maximum price <u>per bedroom</u> PCM:</td>
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
				</table>
			</td>
		</tr>
	</table>
</div>
<div class="fieldSet">
<div class="fieldSetTitle">3. Your details</div>
	<table width="100%"  border="0" cellpadding="0" cellspacing="10" class="noBorder">
		<tr>
			<td width="190" align="right">Number of males:</td>
			<td><?php print createRadioGroup("current_num_males",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5"),$current_num_males)?><?php print $error['current_num_males']?></td>
		</tr>
		<tr>
			<td width="190" align="right">Number of females:</td>
			<td><?php print createRadioGroup("current_num_females",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5"),$current_num_females)?></td>
		</tr>
		<tr>
			<td width="190" align="right">Minumum age: </td>
					<td><?php print createDropDown("current_min_age",getAgeArray(),$current_min_age);?><?php print $error['age']?>&nbsp;<span class="grey">This is the age range of the accommodation seeker(s). If one person, min and max would be the same.</span></td>
		</tr>
		<tr>
			<td width="190" align="right">Maximum age: </td>
			<td><?php print createDropDown("current_max_age",getAgeArray(),$current_max_age);?></td>
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
				<span class="grey">(simply to say that you are known to a church fellowship who could in someway vouch for your character)</span>
			</td>
		</tr>
		<tr>
			<td width="190" align="right">Church attended:</td>
			<td>
						<input type="text" name="church_attended" id="church_attended" value="<?php print stripslashes($church_attended)?>" />
				<span class="grey">(e.g. &quot;your church, your town&quot;)</span>
			</td>
		</tr>
		<tr>
			<td width="190" align="right" valign="top">Church URL: </td>
			<td>
				<input type="text" name="church_url" id="church_url" value="<?php print $church_url?>" />
				<span class="grey">(e.g. www.our-church-website.org)</span>
			</td>
		</tr>
		<tr>
			<td width="190" align="right" valign="top">Now say some nice things (and some informative things) about yourself(/selves):
				<p id="accommodation_situation_label"><?php print nl2br(DESC_ACCOMMODATION_SITUATION)?></p></td>
					<td><textarea name="accommodation_situation" rows="10" id="accommodation_situation" style="width:100%"><?php print stripslashes($accommodation_situation)?></textarea></td>
		</tr>		
	</table>
</div>
<div class="fieldSet">
<div class="fieldSetTitle">4. Preferred Household </div>
	<table border="0" cellpadding="0" cellspacing="10" class="noBorder">
		<tr>
			<td width="200" align="right">Maximum number of adult members:</td>
					<td><?php print $error['shared_adult_members']?><?php print createRadioGroup("shared_adult_members",array("0"=>"0","1"=>"1","2"=>"2","3"=>"3","4"=>"4+"),$shared_adult_members)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Gender:</td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" class="prTD5">
					<tr>	
						<td><?php print createCheckbox("shared_males","1",$shared_males)?></td>
						<td>All male</td>
						<td><?php print createCheckbox("shared_females","1",$shared_females)?></td>
						<td>All female</td>
						<td><?php print createCheckbox("shared_mixed","1",$shared_mixed)?></td>
						<td>Mixed household</td>
						<td><?php print $error['shared_gender']?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right">Minumum age: </td>
			<td><?php print createDropDown("shared_min_age",getAgeArray(false,true),$shared_min_age);?><?php print $error['shared_age']?></td>
		</tr>
		<tr>
			<td align="right">Maximum age: </td>
			<td><?php print createDropDown("shared_max_age",getAgeArray(false,true),$shared_max_age);?></td>
		</tr>
		
		<tr>
			<td width="200" align="right" valign="top">It could comprise of:</td>
			<td>
				<?php print createCheckbox("shared_student","1",$shared_student);?>Students (&lt;22yrs)<br />
				<?php print createCheckbox("shared_mature_student","1",$shared_mature_student);?>Mature students<br />
				<?php print createCheckbox("shared_professional","1",$shared_professional);?>Professionals
			</td>
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
	<p>Those responding to your ad can do so through a website form, <strong>which doesn't disclose your email address</strong> unless you have included it in the advert. If you would like your phone number on the advert please add it below.</p>
	<table  border="0" cellpadding="0" cellspacing="10" class="noBorder">
		<tr>
			<td width="200" align="right">Contact name:</td>
					<td><input name="contact_name" type="text" id="contact_name" value="<?php print stripslashes($contact_name)?>"/>&nbsp;<span class="grey">(e.g. &quot;John Smith&quot;)</span></td>
		</tr>				
		<tr>
			<td width="200" align="right">Contact phone number :</td>
			<td><input name="contact_phone" type="text" id="contact_phone" value="<?php print $contact_phone?>"/>&nbsp;<span class="grey">(optional)</span></td>
		</tr>
		<tr>
					<td width="200" align="right" valign="top">Advert expires on:</td>
			<td><?php print createDateDropDown("expiry_date",42,$expiry_date,TRUE,"dateSelector")?><?php print $error['expiry_date']?></td>
		</tr>
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
			<td>Pal-Up is being introduced to people who are also looking for accommodation, and who are willing to Pal-Up to look for somewhere together. Pal-Up will automatically email you details of new wanted accommodation ads with a similar requirement to yours and who have also chosen to Pal-Up.</td>
		</tr>
	</table>
</div>
<p>Please complete all 5 sections of the form before proceeding.</p>
<p class="mb0">
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
