<?php

session_start();

// Autoloader
require_once __DIR__ . '/../web/global.php';
// require_once 'web/global.php';

require('includes/class.phpmailer.php');		// Email class

connectToDB();
	
	if (isset($_POST['offered_id'])) { $offered_id = $_POST['offered_id']; } else { $offered_id = NULL; }
	if (isset($_POST['wanted_id'])) { $wanted_id = $_POST['wanted_id']; } else { $wanted_id = NULL; }
	
	if ($_POST) {
	
		// Load the information for the current wanted ad
		$query = "
			select w.*,j.x,j.y 
			from cf_wanted w
			left join cf_jibble_postcodes j on j.postcode = w.postcode
			where w.wanted_id = '".$_POST['wanted_id']."';
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$w = mysqli_fetch_assoc($result);
		//pre($w);
		
		// Also, load all offered ads for the side by side comparison.
		foreach($offered_id as $value) { $sqlIn .= $value.","; }
		$sqlIn = substr($sqlIn,0,-1);
		
		$query = "
			select round(sqrt(power((x-".$w['x']."),2)+power((y-".$w['y']."),2)) / 1609,2) as `distance`,
			o.*
			from cf_offered o
			left join cf_jibble_postcodes j on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1) 
			where offered_id in (".$sqlIn.") 
			order by o.offered_id asc;		
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die($query);
		$c = mysqli_num_rows($result);
		// Stored everything into a 2D array
		$d = array();
		while($row = mysqli_fetch_assoc($result)) { $d[] = $row; }
		//pre($d);
		
		// FLATMATCH QUERY
		$query = "
			SELECT 
			
			o.offered_id,
			o.postcode as `offered_postcode`,
			w.wanted_id,
			w.postcode as `wanted_postcode`,
			sqrt(power((j1.x-j2.x),2)+power((j1.y-j2.y),2)) as `distance`,
			o.bedrooms_available,
			o.bedrooms_total,
			o.accommodation_type,
			o.room_share,
			o.building_type,
			o.street_name,
			j1.town,
			o.postcode,
			w.bedrooms_required,
			w.distance_from_postcode,
			w.location
			
			FROM cf_offered as `o`
			INNER JOIN cf_wanted as `w`
			LEFT JOIN cf_jibble_postcodes as `j1` on j1.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
			LEFT JOIN cf_jibble_postcodes as `j2` on j2.postcode = w.postcode
			
			WHERE
			
			# Flat-Match is enabled
			w.flatmatch = 1
			
			# New ads only
			AND  o.created_date > ifnull(w.last_flatmatch,'2005-04-04')
			
			# Both ads are pulished and unexpired
			AND  o.published = 1
			AND  o.expiry_date > now() 
			AND  o.suspended = 0 
			AND  w.published = 1
			AND  w.expiry_date > now() 
			AND  w.suspended = 0 
			
			# ***********************************************
			# LOCATION AND DATES
			# ***********************************************
			
			# Postcode and distance from postcode
			AND sqrt(power((j1.x-j2.x),2)+power((j1.y-j2.y),2)) < (1609 * w.distance_from_postcode)
			#  o.location in within w.distance_from_postcode of w.postcode
			
			# W Available from (means required from) > O Available date
			AND  w.available_date >= o.available_date
			
			# Min / Max terms
			AND  w.min_term >= o.min_term  
			AND  w.min_term <= o.max_term
			
			AND  w.max_term <= o.max_term 
			
			# Accommodation type
			AND  (
				(w.accommodation_type_flat_share = 1 AND o.accommodation_type = 'flat share') OR
				(w.accommodation_type_family_share = 1 AND o.accommodation_type = 'family share') OR
				(w.accommodation_type_whole_place = 1 AND o.accommodation_type = 'whole place')
			)
			
			# Building type
			AND  (
			(w.building_type_flat = 1 AND o.building_type = 'flat') OR
			(w.building_type_house = 1 AND o.building_type = 'house')
			)
			
			# Number of bedrooms required
			AND w.bedrooms_required <= o.bedrooms_available
			
			# Price 
			AND w.price_pcm >= o.price_pcm
			
			#Features
			AND w.shared_lounge_area <= o.shared_lounge_area
			AND w.central_heating <= o.central_heating
			AND w.washing_machine <= o.washing_machine
			AND w.garden_or_terrace <= o.garden_or_terrace
			AND w.bicycle_store <= o.bicycle_store
			AND w.dish_washer <= o.dish_washer
			AND w.tumble_dryer <= o.tumble_dryer
			AND (
				(w.parking = 0) OR
				(w.parking = 1 AND o.parking != 'None')
			)
			
			# ***********************************************
			# MATCH WANTED YOUR DETAILS TO OFFERED WOULD SUIT
			# ***********************************************
			
			# Number of rooms
			AND (w.current_num_males + w.current_num_females) <= o.bedrooms_available
			
			# Family, married couple, reference
			AND w.current_is_couple  <= o.suit_married_couple 
			AND w.current_is_family  <= o.suit_family 
			AND w.church_reference   >= o.church_reference 
			
			# Age
			AND w.current_min_age >= o.suit_min_age
			AND (o.suit_max_age = 0 OR w.current_max_age <= o.suit_max_age)
			  
			# Occupation
			AND (
				(w.current_occupation = 'Professionals' AND o.suit_professional = 1) OR
				(w.current_occupation = 'Mature Students' AND o.suit_mature_student = 1) OR
				(w.current_occupation = 'Students (<22yrs)' AND o.suit_student = 1)
			)
			
			# *********************************************************
			# MATCH WANTED PREFFERED HOUSEHOLD TO OFFERED THE HOUSEHOLD
			# *********************************************************
			
			# Max number in the household, with logic to 4+ members
			AND (o.current_num_males + o.current_num_females <= w.shared_adult_members OR w.shared_adult_members = 4)
			
			# Age
			AND w.shared_min_age <= o.current_min_age
			AND (w.shared_max_age >= o.current_max_age OR shared_max_age = 0)
			  
			# Occupation
			AND (
				(o.current_occupation is null) OR
				(o.current_occupation = 'Professionals' AND w.shared_professional = 1) OR
				(o.current_occupation = 'Mature Students' AND w.shared_mature_student = 1) OR
				(o.current_occupation = 'Students (<22yrs)' AND w.shared_student = 1)
			)
			
			# Gender
			AND (
				(w.shared_males = 1 AND o.current_num_females = 0) OR
				(w.shared_females = 1 AND o.current_num_males = 0) OR
				(w.shared_mixed = 1 AND o.current_num_males > 0 AND o.current_num_females > 0)
			);		
		";
		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or die(mysqli_error());
		$finalCount = mysqli_num_rows($result);
		if ($finalCount) {
			while($row = mysqli_fetch_assoc($result)) {
				$data[] = $row;
			}
		}
	
	}
	
	// Create the wanted_id drop down
	$query = "select wanted_id, location, postcode from cf_wanted order by wanted_id asc;";
	$tempResult = mysqli_query($GLOBALS['mysql_conn'], $query);
	$temp = array();
	while($row = mysqli_fetch_assoc($tempResult)) {
		$temp[$row['wanted_id']] = $row['wanted_id']." - ".$row['location'].", ".$row['postcode'];
	}
	
	// Create the offered_id list
	$query = "select offered_id,street_name,postcode from cf_offered order by offered_id asc;";
	$tempResult = mysqli_query($GLOBALS['mysql_conn'], $query);
	$o = '<select multiple="multiple" size="10" name="offered_id[]">';
	while($row = mysqli_fetch_assoc($tempResult)) {
		$o .= '<option value="'.$row['offered_id'].'"';
		if ($offered_id && in_array($row['offered_id'],$offered_id)) {
			$o .= ' selected="selected"';
		}
		$o .= '>'.$row['offered_id'].' - '.$row['street_name'].', '.$row['postcode'].'</option>';
	}
	$o .= '</select>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<style type="text/css">
	body,input,select,textarea { font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px; }
	bodt { margin:20px; }
	.borders { border-collapse:collapse; }
	.borders td { border:1px solid #CCCCCC; }
	.borders th { border:1px solid #CCCCCC; background-color:#EAEAEA; }
	.red { background-color:#FFC4C4; }
	.green { background-color:#DBFFB7; }
</style>
<body>
<h1>Flatmatch validator </h1>
<form name="flatmatch" action="<?php print $_SERVER['PHP_SELF']?>" method="post">
<table cellpadding="0" cellspacing="10" border="0">
	<tr>
		<td align="right">Enter wanted id:</td>
		<td><?php print createDropDown("wanted_id",$temp,$_POST['wanted_id'])?></td>
	</tr>
	<tr>
		<td width="130" align="right" valign="top">Select offered ads:<br /><span style="color:#999999;">You can select more than one using the CTRL key</span> </td>
		<td><?php print $o?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="Submit" value="Submit" /></td>
	</tr>
</table>
</form>
<?php if ($_POST) { ?>
<p>The Flatmatch query reports: <strong><?php print $finalCount?></strong> matches</p>
<?php if ($data) { ?>
<pre><?php print print_r($data,true)?></pre>
<?php } ?>
<h2>Verification:</h2>
<table border="0" cellspacing="0" cellpadding="4" class="borders">
	<tr>
		<th>Wanted field </th>
		<th>Wanted ID <?php print $w['wanted_id']?></th>
		<th>Offered field </th>
		<?php for($i=0;$i<$c;$i++) { ?>
		<th>Offered_id<br/><?php print $d[$i]['offered_id']?></th>
		<?php } ?>
	</tr>
	<tr>
		<td>w.flatmatch</td>
		<td><?php print $w['flatmatch']?></td>
		<td>&nbsp;</td>
		<?php for($i=0;$i<$c;$i++) { ?>
		<td>&nbsp;</td>
		<?php } ?>
	</tr>
	<tr>
		<td>w.distance_from_postcode</td>
		<td><?php print $w['distance_from_postcode']?></td>
		<td>Distance</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($d[$i]['distance'] <= $w['distance_from_postcode']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['distance'];
				echo '</td>';
			}			
		?>		
	</tr>
	<tr>
		<td>w.last_flatmatch</td>
		<td><?php print $w['last_flatmatch']?></td>
		<td>o.created_date</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				$tempDate1 = new Date($d[$i]['created_date']);
				$tempDate2 = new Date($w['last_flatmatch']);
				if ($tempDate1->after($tempDate2)) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['created_date'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.published</td>
		<td><?php print $w['published']?></td>
		<td>o.published</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($d[$i]['published']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['published'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.expiry_date</td>
		<td><?php print $w['expiry_date']?></td>
		<td>o.expiry_date</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				$tempDate1 = new Date($d[$i]['expiry_date']);
				$tempDate2 = new Date();
				if ($tempDate1->after($tempDate2)) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['expiry_date'];
				echo '</td>';
			}			
		?>		
	</tr>
	<tr>
		<td>w.available_date</td>
		<td><?php print $w['available_date']?></td>
		<td>o.available_date</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				$tempDate1 = new Date($d[$i]['available_date']);
				$tempDate2 = new Date($w['available_date']);
				if ($tempDate2->after($tempDate1)) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['available_date'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.min_term</td>
		<td><?php print $w['min_term']?></td>
		<td>o.min_term</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					$w['min_term'] >= $d[$i]['min_term'] &&
					$w['min_term'] <= $d[$i]['max_term']
				) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['min_term'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.max_term</td>
		<td><?php print $w['max_term']?></td>
		<td>o.max_term</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['max_term'] <= $d[$i]['max_term']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['max_term'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.accommodation_type_flatshare</td>
		<td><?php print $w['accommodation_type_flat_share']?></td>
		<td rowspan="3">o.accommodation_type</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					($w['accommodation_type_flat_share'] == 1 && $d[$i]['accommodation_type'] == "flat share") OR
					($w['accommodation_type_family_share'] == 1 && $d[$i]['accommodation_type'] == "family share") OR
					($w['accommodation_type_whole_place'] == 1 && $d[$i]['accommodation_type'] == "whole place")					
				) { $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="3" class="'.$class.'">';
				echo $d[$i]['accommodation_type'];
				echo '</td>';
			}			
		?>		
	</tr>
	<tr>
		<td>w.accommodation_type_family_share</td>
		<td><?php print $w['accommodation_type_family_share']?></td>
	</tr>
	<tr>
		<td>w.accommodation_type_whole_place</td>
		<td><?php print $w['accommodation_type_whole_place']?></td>
	</tr>
	<tr>
		<td>w.building_type_flat</td>
		<td><?php print $w['building_type_flat']?></td>
		<td rowspan="2">o.building_type</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					($w['building_type_flat'] == 1 && $d[$i]['building_type'] == "flat") OR
					($w['building_type_house'] == 1 && $d[$i]['building_type'] == "house")
				) { $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="2" class="'.$class.'">';
				echo $d[$i]['building_type'];
				echo '</td>';
			}			
		?>		
	</tr>
	<tr>
		<td>w.building_type_house</td>
		<td><?php print $w['building_type_house']?></td>
	</tr>
	<tr>
		<td>w.bedrooms_required</td>
		<td><?php print $w['bedrooms_required']?></td>
		<td>o.bedrooms_available</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['bedrooms_required'] <= $d[$i]['bedrooms_available']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['bedrooms_available'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.price_pcm</td>
		<td><?php print $w['price_pcm']?></td>
		<td>o.price_pcm</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['price_pcm'] >= $d[$i]['price_pcm']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['price_pcm'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.shared_lounge_area</td>
		<td><?php print $w['shared_lounge_area']?></td>
		<td>o.shared_lounge_area</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['shared_lounge_area'] <= $d[$i]['shared_lounge_area']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['shared_lounge_area'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.central_heating</td>
		<td><?php print $w['central_heating']?></td>
		<td>o.central_heating</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['central_heating'] <= $d[$i]['central_heating']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['central_heating'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.washing_machine</td>
		<td><?php print $w['washing_machine']?></td>
		<td>o.washing_machine</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['washing_machine'] <= $d[$i]['washing_machine']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['washing_machine'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.garden_or_terrace</td>
		<td><?php print $w['garden_or_terrace']?></td>
		<td>o.garden_or_terrace</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['garden_or_terrace'] <= $d[$i]['garden_or_terrace']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['garden_or_terrace'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.bicycle_store</td>
		<td><?php print $w['bicycle_store']?></td>
		<td>o.bicycle_store</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['bicycle_store'] <= $d[$i]['bicycle_store']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['bicycle_store'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.dish_washer</td>
		<td><?php print $w['dish_washer']?></td>
		<td>o.dish_washer</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['dish_washer'] <= $d[$i]['dish_washer']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['dish_washer'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.tumble_dryer</td>
		<td><?php print $w['tumble_dryer']?></td>
		<td>o.tumble_dryer</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['tumble_dryer'] <= $d[$i]['tumble_dryer']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['tumble_dryer'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.parking</td>
		<td><?php print $w['parking']?></td>
		<td>o.parking</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (!$w['parking'] || ($w['parking'] && $d[$i]['parking'] != "None")) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['parking'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.current_num_males</td>
		<td><?php print $w['current_num_males']?></td>
		<td rowspan="2">o.bedrooms_available</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (($w['current_num_males'] + $w['current_num_females']) <= $d[$i]['bedrooms_available']) { $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="2" class="'.$class.'">';
				echo $d[$i]['bedrooms_available'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.current_num_females</td>
		<td><?php print $w['current_num_females']?></td>
	</tr>
	<tr>
		<td>w.current_is_couple</td>
		<td><?php print $w['current_is_couple']?></td>
		<td>o.suit_married_couple</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['current_is_couple'] <= $d[$i]['suit_married_couple']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['suit_married_couple'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.current_is_family</td>
		<td><?php print $w['current_is_family']?></td>
		<td>o.suit_family</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['current_is_family'] <= $d[$i]['suit_family']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['suit_family'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.church_reference</td>
		<td><?php print $w['church_reference']?></td>
		<td>o.church_reference</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['church_reference'] >= $d[$i]['church_reference']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['church_reference'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.current_min_age</td>
		<td><?php print $w['current_min_age']?></td>
		<td>o.suit_min_age</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['current_min_age'] >= $d[$i]['suit_min_age']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['suit_min_age'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.current_max_age</td>
		<td><?php print $w['current_max_age']?></td>
		<td>o.suit_max_age</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (!$d[$i]['suit_max_age'] || $w['current_max_age'] <= $d[$i]['suit_max_age']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['suit_max_age'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td rowspan="3">w.current_occupation</td>
		<td rowspan="3"><?php print $w['current_occupation']?></td>
		<td>o.suit_professional</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					($w['current_occupation'] == 'Professionals' && $d[$i]['suit_professional']) OR
					($w['current_occupation'] == 'Mature Students' && $d[$i]['suit_mature_student']) OR
					($w['current_occupation'] == 'Students (<22yrs)' && $d[$i]['suit_student'])					
				) { $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="3" class="'.$class.'">';
				echo 'Pr:'.$d[$i]['suit_professional']."<br/>";
				echo 'Ms:'.$d[$i]['suit_mature_student']."<br/>";
				echo 'S:'.$d[$i]['suit_student'];				
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>o.suit_mature_student</td>
	</tr>
	<tr>
		<td>o.suit_student</td>
	</tr>
	<tr>
		<td rowspan="2">w.shared_adult_members</td>
		<td rowspan="2"><?php print $w['shared_adult_members']?></td>
		<td>o.current_num_males</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					($w['shared_adults_members'] == 4 ) OR
					($d[$i]['current_num_males'] + $d[$i]['current_num_females'] <= $w['shared_adult_members'])					
				) { $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="2" class="'.$class.'">';
				echo 'M:'.$d[$i]['current_num_males']."<br/>";
				echo 'F:'.$d[$i]['current_num_females'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>o.current_num_females</td>
	</tr>
	<tr>
		<td>w.shared_min_age</td>
		<td><?php print $w['shared_min_age']?></td>
		<td>o.current_min_age</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if ($w['shared_min_age'] <= $d[$i]['current_min_age']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['current_min_age'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.shared_max_age</td>
		<td><?php print $w['shared_max_age']?></td>
		<td>o.current_max_age</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (!$w['shared_max_age'] || $w['shared_max_age'] >= $d[$i]['current_max_age']) { $class = "green"; } else { $class = "red"; }
				echo '<td class="'.$class.'">';
				echo $d[$i]['current_max_age'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.shared_professional</td>
		<td><?php print $w['shared_professional']?></td>
		<td rowspan="3">o.current_occupation</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					(!$d[$i]['current_occupation']) ||
					($w['shared_professional'] && $d[$i]['current_occupation'] == 'Professionals') ||
					($w['shared_mature_student'] && $d[$i]['current_occupation'] == 'Mature Students') ||
					($w['shread_student'] && $d[$i]['current_occupation'] == 'Students (<22yrs)')
				) { $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="3" class="'.$class.'">';
				echo $d[$i]['current_occupation'];
				echo '</td>';
			}			
		?>
	</tr>
	<tr>
		<td>w.shared_mature_student</td>
		<td><?php print $w['shared_mature_student']?></td>
	</tr>
	<tr>
		<td>w.shared_student</td>
		<td><?php print $w['shared_student']?></td>
	</tr>
	<tr>
		<td>w.shared_males</td>
		<td><?php print $w['shared_males']?></td>
		<td rowspan="3">o.current_num_females,<br />o.current_num_males</td>
		<?php 
			for($i=0;$i<$c;$i++) { 
				// Logic
				if (
					($w['shared_males'] && $d[$i]['current_num_females'] == 0) ||
					($w['shared_females'] && $d[$i]['current_num_males'] == 0) ||
					($w['shared_mixed'] && $d[$i]['current_num_females'] != 0 && $d[$i]['current_num_males'] != 0)
				){ $class = "green"; } else { $class = "red"; }
				echo '<td rowspan="3" class="'.$class.'">';
				echo 'M: '.$d[$i]['current_num_males']."<br/>";
				echo 'F: '.$d[$i]['current_num_females'];
				echo '</td>';
			}			
		?>	
	</tr>
	<tr>
		<td>w.shared_females</td>
		<td><?php print $w['shared_females']?></td>
	</tr>
	<tr>
		<td>w.shared_mixed</td>
		<td><?php print $w['shared_mixed']?></td>
	</tr>
</table>
<?php } ?>
</body>
</html>
