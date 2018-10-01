<?php
session_start();

// Autoloader
require_once 'web/global.php';

connectToDB();	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Search by Church</title>
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
	function $(obj) {
		return document.getElementById(obj);
	}
	
	function trim(toTrim) {
		while(''+toTrim.charAt(0) == " ") { toTrim = toTrim.substring(1,toTrim.length); }
		while(''+toTrim.charAt(toTrim.length-1) == " ") { toTrim = toTrim.substring(0,toTrim.length-1); }
		return toTrim;
	}
	
	function clearError(obj) {
		$(obj).innerHTML = "&nbsp;";
		return true;
	}
	
	function toggleSearch(v) {
	
		if (v == "url") {
			$('search_by_name_canvas').style.display = "none";
			$('church_name').value = "";
			$('search_by_url_canvas').style.display = "";
		} else {
			$('search_by_url_canvas').style.display = "none";
			$('c_url').value = "";
			$('search_by_name_canvas').style.display = "";			
		}
	
	}
	
	function submitForm() {
	
		// Before we do anything, validate
		var proceed = true; // Initially assume that validation is successful.
		var place_check = /^.+$/;
		
		if ($('search_by_url_canvas').style.display != "none") {
			// Validate the church_by_url form
			// Validate place
			if (!place_check.test(trim(document.search_by_church.church_url.value))) {
				$('church_url_error').innerHTML = 'Please enter the internet address (URL) of your church.';
				proceed = false;
			}				
		}
		
		if ($('search_by_name_canvas').style.display != "none") {
			// Either church name or church acronym must be non-empty
			if (
				!place_check.test(trim(document.search_by_church.church_name.value)) &&
				!place_check.test(trim(document.search_by_church.church_acronym.value))
			) {
				$('church_name_error').innerHTML = 'Please enter a church name or acronym to proceed.';
				proceed = false;
			}			
		}
		
		// If no errors were encountered with the form
		if (proceed) {
		
			var v = $('location').value;
			var postcode_regexp = <?php print REGEXP_UK_POSTCODE?>;
			var partial_postcode_regexp = <?php print REGEXP_UK_POSTCODE_FIRST_PART?>;
			
			// If v is a full UK postcode
			if (trim(v) == "") {
			
				// If the location is not specified, submit the form
				document.search_by_church.submit();
				
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
					$('location').value = v;
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
		
			// Change the text of the "location" text field	
			$('location').value = text;
			
			// Change the value of the "postcode" hidden field
			$('postcode').value = value;
			
			document.search_by_church.submit();
		
		}
		
	}
	
	function cancelChooseLocation() {
		
		$('locationsListContainer').style.display = "none";
		$('findLocationLink').style.display = "";
		
	}
	
</script><!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
<div id="header_search_by_church" class="header">
	<h1 class="m0"><span>Search by Church</span></h1>
</div>
<p class="mt0"><strong>Here you can search by a church's name or website address.</strong><br />
  Please note that not all members will have included a church website in their adverts.</p>
<form name="search_by_church" method="get" action="display.php">
<input type="hidden" name="search_type" id="search_type" value="church"  />
<input type="hidden" name="postcode" id="postcode" value="" />
<input type="hidden" name="summary_type" id="summary_type" value="church" />
<table cellpadding="0" cellspacing="10" border="0">
	<tr>
		<td width="120" align="right" valign="top">Advert type: </td>
		<td>
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><input type="radio" name="post_type" id="post_type_offered" value="offered" checked="checked" /></td>
					<td><label for="post_type_offered">Offered accommodation ads</label></td>
				</tr>
				<tr>
					<td><input type="radio" name="post_type" id="post_type_wanted" value="wanted" /></td>
					<td><label for="post_type_wanted">Wanted accommodation ads</label></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top">Search type: </td>
		<td>
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><input type="radio" name="church_type" id="church_type_name" value="name" checked="checked" onclick="toggleSearch('name');" /></td>
					<td><label for="church_type_name">Search by church name</label></td>
				</tr>
				<tr>
					<td><input name="church_type" type="radio" id="church_type_url" value="url" onclick="toggleSearch('url');" /></td>
					<td><label for="church_type_url">Search by church website address</label></td>
					
				</tr>
			</table>
		</td>
	</tr>
</table>
<div id="search_by_url_canvas" style="display:<?php print "none"?>;">
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td align="right">&nbsp;</td>
			<td><strong>Enter Church website:</strong> </td>
		</tr>
		<tr>
			<td width="120" align="right"><span class="obligatory">*</span> Church website:</td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="padding-right:10px;"><input name="church_url" id="c_url" type="text" size="60" onclick="clearError('church_url_error');" /></td>
						<td><span class="error" id="church_url_error">&nbsp;</span></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right">&nbsp;</td>
			<td class="grey"> Searches are not case sensitive. You can enter the full website address, or just a part of it:<br/> 
			  e.g. &quot;www.ourchurch.org&quot;, &quot;ourchurch.org&quot; or &quot;ourchurch&quot;. </td>
		</tr>
		<tr>
			<td align="right">&nbsp;</td>
			<td class="grey"><table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td width="130">Good  example:</td>
					<td>&quot;ourchurch.org&quot;</td>
				</tr>
				<tr>
					<td>Bad  example:</td>
					<td>&quot;www.ourchurch.org/index.html&quot;</td>
				</tr>
			</table></td>
		</tr>	
	</table>
</div>
<div id="search_by_name_canvas" style="display:;">
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td align="right">&nbsp;</td>
			<td><strong>Enter Church name and / or acronym:</strong> </td>
		</tr>
		<tr>
			<td width="120" align="right"><span class="obligatory">*</span> Church name: </td>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="padding-right:10px;"><input name="church_name" type="text" id="church_name" onclick="clearError('church_name_error');" size="60" /></td>
						<td><span class="error" id="church_name_error">&nbsp;</span></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right">Acronym: </td>
			<td class="grey"><input name="church_acronym" type="text" id="church_acronym" onclick="clearError('church_url_error');" size="60" /></td>
		</tr>
		<tr>
			<td align="right">&nbsp;</td>
			<td class="grey">Searches are not case sensitive. You can  enter the full name or just a word or an acronym:<br/> 
			  e.g. for &quot;Holy Trinity Cambridge&quot;, &quot;Trinity Cambridge&quot; or &quot;Trinity&quot;, or &quot;HTC&quot; </td>
		</tr>
	</table>
</div>
<table cellpadding="0" cellspacing="10" border="0">
	<tr>
		<td align="right">&nbsp;</td>
		<td><strong>Accommodation location within 10 miles of </strong>(optional) <strong>:</strong> </td>
	</tr>
	<tr>
		<td width="120" align="right" valign="top">Near:</td>
		<td>
			<p class="m0"><input type="text" name="location" id="location" value="<?php print stripslashes($location)?>" />
			</p>
			<p style="margin:4px 0px 0px 0px">
				<input type="button" name="findLocationLink" id="findLocationLink" style="display:;" value="Show me the results" onclick="submitForm(); return false;" />
				<span id="findLocationLoadingLabel" style="display:none;">Loading ...</span>
			</p>
		</td>
	</tr>
	<tr>
		<td width="120">&nbsp;</td>
		<td>
			<div id="locationsListContainer" style="display:<?php print "none"?>;" class="mb10">
				<p class="mt0 mb10"><strong>CFS has found <span id="locationsCount">4</span> locations that match &quot;<span id="locationLabel">&nbsp;</span>&quot;</strong> <br />Please choose one of the following and press the &quot;Pick location&quot; button to continue :</p>
				<p class="mt0 mb10">
					<select name="locationsList" id="locationsList" size="5" ></select>
				</p>
				<p class="m0">
					<input type="button" name="locationsChooser" value="Pick location" onclick="chooseLocation();"/>
					<input type="button" name="locationsChooserCancel" id="locationsChooserCancel" value="Cancel" onclick="cancelChooseLocation();" />
				</p>
			</div>
			<p class="grey" style="margin:0px 0px 2px 0px;">Valid locations:</p>
			<ul class="grey" style="margin:0px 0px 0px 1em; padding-left:1em;">
				<li>The first part of a UK postcode (<em>e.g. W9</em>)</li> 
				<li>Name of a city, town or village,  (<em>e.g. Nottingham, Woodbridge, Long Melford</em>)</li> 
				<li>Name of a city district (<em>e.g. Hammersmith, Paddington, West Bridgford</em>)</li>
				<li>Name of a London tube station (<em>e.g. Westminster Station, Bow Station</em>)</li>
			</ul>
		</td>
	</tr>	
</table>
</form>
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
