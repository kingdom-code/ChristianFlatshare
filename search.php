<?php

// Autoloader
require_once 'web/global.php';

connectToDB();
	
	// Initialise all needed variables
	if (isset($_GET['post_type'])) { $post_type = $_GET['post_type']; } else { $post_type = "offered"; }
	$now = new DateTime();
	
	// Create the $years array which contains this, the next and the following year.
	for($i=($now->format('Y'));$i<=($now->format('Y')+2);$i++) { $years[$i] = $i; }	
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Advanced Search - Christian Flatshare</title>
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
	function showTab(tabId) {
	
		for(var i=1;i<=2;i++) {
			if ($('tab'+i)) {
				$('tab'+i).className = 'tabHidden'; // hide the tab
				$('tab'+i+'link').className = ''; // change the class of the link
			}
		}
		$('tab'+tabId).className = 'tab'; // show the tab
		$('tab'+tabId+'link').className = 'current'; // change the class of the link
		return false;
	
	}
	
	function $(obj) {
		return document.getElementById(obj);
	}
	
	function trim(toTrim) {
		while(''+toTrim.charAt(0) == " ") { toTrim = toTrim.substring(1,toTrim.length); }
		while(''+toTrim.charAt(toTrim.length-1) == " ") { toTrim = toTrim.substring(0,toTrim.length-1); }
		return toTrim;
	}
	
	function showMoreDetails(post_type) {
		
		if ($(post_type+'_tab_2').style.display == "none") {
			// We need to show the extra details
			$('button_'+post_type+'_more').value = "Less detail";
			$(post_type+'_tab_2').style.display = "";
			$(post_type+'_tab_3').style.display = "";			
		} else {
			// We need to hide the extra details
			$('button_'+post_type+'_more').value = "Enter more details";
			$(post_type+'_tab_2').style.display = "none";
			$(post_type+'_tab_3').style.display = "none";
			// Clear all elements 
			var x = $(post_type+'_tab_2').getElementsByTagName("input");
			for (var i=0;i<x.length;i++) {
				// Clear all checkboxes
				switch (x[i].type) {
					case "radio": x[i].checked = false; break;
					case "checkbox": x[i].checked = false; break;
				}
			}
			$('suit_average_age').selectedIndex = 0;
			var x = $(post_type+'_tab_3').getElementsByTagName("input");
			for (var i=0;i<x.length;i++) {
				// Clear all checkboxes
				switch (x[i].type) {
					case "radio": x[i].checked = false; break;
					case "checkbox": x[i].checked = false; break;
				}
			}
			$('current_average_age').selectedIndex = 0;
		}
		
	}
	
	function doValidation(form) {
		
		var proceed = true; // Initially assume that validation is successful.
		var errorText = "Errors were found in your form. Please amend:\n\n";
		var price_check = /^\d+$/;
		var place_check = /^.+$/;
		
		// Validate place
		if (!place_check.test(trim(form.place.value))) {
			errorText += "- Please enter a part or full UK postcode or a place / town name\n";
			proceed = false;
		}
		
		// Validate pcm (if provided)
		if (trim(form.pcm.value) != '') {
			// Preg match pcm to be only digits
			if (form.pcm.value != "Any" && !price_check.test(form.pcm.value)) {
				errorText += "- Please make sure the price field contains only numbers (no decimals or currency signs)\n";
				proceed = false;
			}
		}		
		if (!proceed) { alert(errorText); }
		return proceed;
		
	}
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
<div id="header_advanced_search" class="header">
	<h1 class="m0"><span>Advanced search</span></h1>
</div>
<ul id="tablist">
	<li><a href="#" onclick="showTab('1');" id="tab1link" class="<?php print ($post_type=="offered"? "current":"")?>">Search for accommodation</a></li>
	<li><a href="#" onclick="showTab('2');" id="tab2link" class="<?php print ($post_type=="wanted"? "current":"")?>">Search for flatmates, lodgers or tenants</a></li>
</ul>
<div class="<?php print ($post_type=="offered"? "tab":"tabHidden")?>" id="tab1">
<form name="search_offered" method="get" action="display.php" onsubmit="return doValidation(this);">
<input type="hidden" name="search_type" value="place" />
<input type="hidden" name="post_type" value="offered" />
<div class="fieldSet">
	<div class="fieldSetTitle">Tell us about the accommodation you are looking for:</div>
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td width="200" align="right" valign="top">Location or postcode:</td>
			<td><?php print $error['place']?>
				<input type="text" name="place" value="<?php print $place?>" />
				<p class="m0 grey">
				The first part of a UK postcode (<em>e.g. W9</em>)<br />
				Name of a city, town or village (<em>e.g. Nottingham, Woodbridge, Long Melford</em>)<br />
				Name of a city district (<em>e.g. Hammersmith, Paddington, West Bridgform</em>)<br />
				Name of a London tube station (<em>e.g. Westminster Station, Bow Station</em>)
				</p>
			</td>
		</tr>	
		<tr>
		<tr>
			<td width="200" align="right">Max distance from postcode :</td>
			<td><?php print createDropDown("radius",getMilesArray(),5)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Should be available on:</td>
			<td><?php print createDateDropDown("available_date",180,$available_date,FALSE,"dateSelector")?></td>
		</tr>
		<tr>
			<td width="200" align="right">Max term accommodation is required:</td>
			<td><?php print createDropDown("max_term",getTermsArray("maximum"),$max_term);?></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Accommodation type can be:</td>
			<td>
				<?php print $error['accommodation_type']?>
				<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><?php print createCheckbox("flatshare","1","1");?></td>
						<td>House / Flatshare (a flat or house shared with others)</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("familyshare","1");?></td>
						<td>Family Share (live with a family or a married couple)</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("wholeplace","1");?></td>
						<td>Whole Place (an unoccupied flat or house)</td>
					</tr>
				</table>				
			</td>				
		</tr>
		<tr>
			<td width="200" align="right">Building type can be:</td>
			<td><?php print createRadioGroup("building_type",array(0=>"House or Flat","house"=>"House","flat"=>"Flat"))?></td>
		</tr>			
		<tr>
			<td width="200" align="right">Maximum price, PCM :</td>
			<td><?php print $error['pcm']?>&pound;&nbsp;<input type="text" name="pcm" id="pcm" value="<?php print $pcm?>"/>&nbsp;<span class="grey">(per room)</span></td>
		</tr>
		<tr>
			<td width="200" align="right">Number of bedrooms required:</td>
			<td><?php print createRadioGroup("bedrooms_required",getBedroomArray(),$bedrooms_required)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Min number of double bedrooms:</td>
			<td><?php print createRadioGroup("bedrooms_double",getBedroomArray(true),$bedrooms_double)?></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">The accommodation must have:</td>
			<td>
				<table cellpadding="0" cellspacing="0" id="mod_cons">
					<tr>
						<td><?php print createCheckbox("shared_lounge_area","1",$shared_lounge_area);?>a shared lounge area</td>
						<td><?php print createCheckbox("dish_washer","1",$dish_washer);?>a dish washer</td>
					</tr>
					<tr>
						<!--<td><?php print createCheckbox("central_heating","1",$central_heating);?>central heating</td> -->
						<td><?php print createCheckbox("bicycle_store","1",$bicycle_store);?>a suitable place to store a bicycle</td>						
						<td><?php print createCheckbox("tumble_dryer","1",$tumble_dryer);?>a tumble dryer</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("washing_machine","1",$washing_machine);?>a washing machine</td>
						<td><?php print createCheckbox("ensuite_bathroom","1",$ensuite_bathroom);?>an ensuite bathroom</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("garden_or_terrace","1",$garden_or_terrace);?>a garden / roof terrace</td>
						<td><?php print createCheckbox("parking","1",$parking);?>Somewhere nearby to park a car</td>
					</tr>
				</table>
			</td>
		</tr>												
	</table>
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td align="left"><input type="button" name="button_offered_more" id="button_offered_more" value="Enter more details" onclick="showMoreDetails('offered');" /></td>
			<td align="right"><input type="submit" name="button_submit" id="button_submit" value="Show me the results" /></td>
		</tr>
	</table>
</div>
<div class="fieldSet" id="offered_tab_2" style="display:<?php print "none"?>;">
	<div class="fieldSetTitle">Tell us about the accommodation seekers:</div>
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td width="200" align="right">Gender:</td>
			<td><?php print createRadioGroup("sortSuit",getGenderArray(),$sortSuit)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Your (average) age:</td>
			<td><?php print createDropDown("suit_average_age",getAgeArray("-- Not specified --"),$suit_average_age)?></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Occupation:</td>
			<td>
				<?php print createCheckbox("suit_student","1",$suit_student);?>Student(s) (&lt;22yrs)<br />
				<?php print createCheckbox("suit_mature_student","1",$suit_mature_student);?>Mature student(s)<br />
				<?php print createCheckbox("suit_professional","1",$suit_professional);?>Professional(s)				
			</td>
		</tr>
		<tr>
			<td width="200" align="right">Are you a married couple:</td>
			<td><?php print createCheckbox("suit_married_couple","1",$suit_married_couple);?></td>
		</tr>
		<tr>
			<td width="200" align="right">Are you family with children:</td>
			<td><?php print createCheckbox("suit_family","1",$suit_family);?></td>
		</tr>			
	</table>
</div>
<div class="fieldSet" id="offered_tab_3" style="display:<?php print "none"?>;">
	<div class="fieldSetTitle">Tell us who you'd like to share with:</div>
	<table border="0" cellpadding="0" cellspacing="10">
		<tr>
			<td width="300" align="right">Maximum number of members:</td>
			<td><?php print createRadioGroup("current_max_members",array("1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5+"),$current_max_members)?></td>
		</tr>
		<tr>
			<td width="300" align="right">Average adult age (approx):</td>
			<td><?php print createDropDown("current_average_age",getAgeArray("-- Not specified --"),$current_average_age)?></td>
		</tr>
		<tr>
			<td width="300" align="right">Gender:</td>
			<td><?php print createRadioGroup("current_gender",getGenderArray("A mixed household"),$current_gender)?></td>
		</tr>	 
		<tr>
			<td width="300" align="right" valign="top">Household could comprise of:</td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="padding-right:10px;">Students (&lt;22 yrs)</td>
						<td><?php print createRadioGroup("current_students",array(0=>"Yes",1=>"No"));?></td>
					</tr>
					<tr>
						<td style="padding-right:10px;">Mature Students</td>
						<td><?php print createRadioGroup("current_mature_students",array(0=>"Yes",1=>"No"));?></td>
					</tr>
						<td style="padding-right:10px;">Professionals</td>
						<td><?php print createRadioGroup("current_professionals",array(0=>"Yes",1=>"No"));?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="300" align="right">The owner could be a member of the household:</td>
			<td><?php print createRadioGroup("owner_lives_in",array(0=>"Yes",1=>"No"));?></td>
		</tr>
		<tr>
			<td width="300" align="right">Household could have a married couple:</td>
			<td><?php print createRadioGroup("current_is_couple",array(0=>"Yes",1=>"No"));?></td>
		</tr>
		<tr>
			<td width="300" align="right">Household could have a family with children:</td>
			<td><?php print createRadioGroup("current_is_family",array(0=>"Yes",1=>"No"));?></td>
		</tr>
	</table>
	</div>
</form>
</div>
<div class="<?php print ($post_type=="wanted"? "tab":"tabHidden")?>" id="tab2">
<form name="search_wanted" method="get" action="display.php" onsubmit="return doValidation(this);">
<input type="hidden" name="search_type" value="place" />
<input type="hidden" name="post_type" value="wanted" />
<div class="fieldSet">
	<div class="fieldSetTitle">Tell us about housemate, lodger or tennats you are looking for:</div>
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td width="200" align="right" valign="top">Location or postcode:</td>
			<td><?php print $error['place']?>
				<input type="text" name="place" value="<?php print $place?>" />
				<p class="m0 grey">
				The first part of a UK postcode (<em>e.g. W9</em>)<br />
				Name of a city, town or village (<em>e.g. Nottingham, Woodbridge, Long Melford</em>)<br />
				Name of a city district (<em>e.g. Hammersmith, Paddington, West Bridgform</em>)<br />
				Name of a London tube station (<em>e.g. Westminster Station, Bow Station</em>)				</p>			</td>
		</tr>
		<tr>
			<td width="200" align="right">Should be available on:</td>
			<td><?php print createDateDropDown("available_date",180,$available_date,FALSE,"dateSelector")?></td>
		</tr>
		<tr>
			<td width="200" align="right">Minimum length of stay:</td>
			<td><?php print createDropDown("min_term",getTermsArray("minimum"),$min_term);?></td>
		</tr>		
		<tr>
			<td width="200" align="right">Maximum length of stay:</td>
			<td><?php print createDropDown("max_term",getTermsArray("maximum"),$max_term);?></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">Accommodation type can be:</td>
			<td>
				<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><?php print createCheckbox("flatshare","1","1");?></td>
						<td>Flatshare (a flat or house shared with others)</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("familyshare","1");?></td>
						<td>Family Share (live with a family or a married couple)</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("wholeplace","1");?></td>
						<td>Whole Place (an unoccupied flat or house)</td>
					</tr>
				</table>
			</td>				
		</tr>
		<tr>
			<td width="200" align="right">Building type can be:</td>
			<td><?php print createRadioGroup("building_type",array(0=>"House or Flat","house"=>"House","flat"=>"Flat"))?></td>
		</tr>			
		<tr>
			<td width="200" align="right">Maximum price, PCM :</td>
			<td><?php print $error['pcm']?>&pound;&nbsp;<input type="text" name="pcm" id="pcm" value="<?php print $pcm?>"/>&nbsp;<span class="grey">(per room)</span></td>
		</tr>
		<tr>
			<td width="200" align="right">Number of bedrooms wanted:</td>
			<td><?php print createRadioGroup("bedrooms_required",getBedroomArray(),$bedrooms_required)?></td>
		</tr>
		<tr>
			<td width="200" align="right">Furnishing:</td>
			<td><?php print createDropDown("furnished",getFurnishedArray(),$furnished);?></td>
		</tr>
		<tr>
			<td width="200" align="right" valign="top">The accommodation must have:</td>
			<td>
				<table cellpadding="0" cellspacing="0" id="mod_cons">
					<tr>
						<td><?php print createCheckbox("shared_lounge_area","1",$shared_lounge_area);?>a shared lounge area</td>
						<td><?php print createCheckbox("dish_washer","1",$dish_washer);?>a dish washer</td>
					</tr>
					<tr>
					<!--	<td><?php print createCheckbox("central_heating","1",$central_heating);?>central heating</td> -->
						<td><?php print createCheckbox("bicycle_store","1",$bicycle_store);?>a suitable place to store a bicycle</td>
						<td><?php print createCheckbox("tumble_dryer","1",$tumble_dryer);?>a tumble dryer</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("washing_machine","1",$washing_machine);?>a washing machine</td>
						<td><?php print createCheckbox("ensuite_bathroom","1",$ensuite_bathroom);?>an ensuite bathroom</td>
					</tr>
					<tr>
						<td><?php print createCheckbox("garden_or_terrace","1",$garden_or_terrace);?>a garden / roof terrace</td>
						<td><?php print createCheckbox("parking","1",$parking);?>Somewhere nearby to park a car</td>
					</tr>
				</table>			</td>
		</tr>												
	</table>
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td align="left"><input type="button" name="button_wanted_more" id="button_wanted_more" value="Enter more details" onclick="showMoreDetails('wanted');" /></td>
			<td align="right"><input type="submit" name="button_submit" id="button_submit" value="Show me the results" /></td>
		</tr>
	</table>
</div>
<div class="fieldSet" id="wanted_tab_2" style="display:<?php print "none"?>;">
	<div class="fieldSetTitle">Tell us about the household wanted:</div>
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td width="300" align="right">Maximum number of members:</td>
			<td><?php print createRadioGroup("shared_adult_members",array("1"=>"1","2"=>"2","3"=>"3","4+"=>"4+"))?></td>
		</tr>
		<tr>
			<td width="300" align="right">Average adult age (approx):</td>
			<td><?php print createDropDown("shared_average_age",getAgeArray("-- Not specified --"))?></td>
		</tr>
		<tr>
			<td width="300" align="right" valign="top">Genders:</td>
			<td><?php print createRadioGroup("shared_gender",getGenderArray("A mixed household"))?></td>
		</tr>	
		<tr>
			<td width="300" align="right" valign="top">Household could comprise of:</td>
			<td><table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td><?php print createCheckbox("shared_student",1);?></td>
						<td style="padding-right:10px;">Students (&lt;22 yrs)</td>
						<td><?php print createCheckbox("shared_mature_student",1);?></td>
						<td style="padding-right:10px;">Mature Students</td>
						<td><?php print createCheckbox("shared_professional",1);?></td>
						<td style="padding-right:10px;">Professionals</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="300" align="right" class="grey">The owner could be a member of the household:</td>
			<td><?php print createCheckbox("shared_owner_lives_in",1);?></td>
		</tr>
		<tr>
			<td width="300" align="right" class="grey">Household could have a married couple:</td>
			<td><?php print createCheckbox("shared_married_couple",1);?></td>
		</tr>
		<tr>
			<td width="300" align="right" class="grey">Household could have a family with children:</td>
			<td><?php print createCheckbox("shared_family",1);?></td>
		</tr>
	</table>
</div>
<div class="fieldSet" id="wanted_tab_3" style="display:<?php print "none"?>;">
	<div class="fieldSetTitle">Tell us about the accommodation seekers:</div>
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td width="300" align="right">Gender:</td>
			<td><?php print createRadioGroup("sortSuit",getSortArray("suit-wanted"))?></td>
		</tr>
		<tr>
			<td width="300" align="right">Age range:</td>
			<td><?php print createDropDown("current_average_age",getAgeArray("-- Not specified --"))?></td>
		</tr>
		<tr>
			<td width="300" align="right">Occupation:</td>
			<td><?php print createDropDown("current_occupation",getOccupationArray(true))?></td>
		</tr>
		<tr>
			<td width="300" align="right">Are you a married couple:</td>
			<td><?php print createCheckbox("current_is_couple",1);?></td>
		</tr>
		<tr>
			<td width="300" align="right">Are you family with children:</td>
			<td><?php print createCheckbox("current_is_family",1);?></td>
		</tr>
		<tr>
			<td width="300" align="right">Someone who can, if asked to, provide<br />a recommendation from a previous church </td>
			<td><?php print createCheckbox("church_reference",1);?></td>
		</tr>			
	</table>
</div>
</form>
</div>
<!--<div class="tabHidden" id="tab2">
	asdfasdf
</div>-->
<p class="mb0"><a href="index.php">Back to welcome page</a></p>
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
