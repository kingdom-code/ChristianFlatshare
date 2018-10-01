
	/* V2.0 of this function (see below for v1) does not need the AJAX address picker
	It simply returns the long / lat of the first returned value */
	function findAddressV2() {
		
		// First, uppercase whatever is inside the postcode field
		$('postcode').value = $('postcode').value.toUpperCase();
		
		// Secondly, validate the UK postcode
		var postcode = $('postcode').value.trim();
		var pattern = /^([A-Z]{1,2}[0-9][0-9A-Z]?)\s([0-9][A-Z]{2})$/i;
	
		if (!pattern.test(postcode)) {
			
			alert ('Please enter a valid UK postcode, INCLUDING a space e.g. W6 9PJ');
			$('postcode').focus();
			$('postcode').select();
			return false;
		
		} else {
		
			$('findAddressLink').style.display = "none";
			$('findAddressLoadingLabel').style.display = "";
			
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
		
			var url = 'ajax-functions.php?action=quickAddressSearch&postcode=' + postcode;
			xmlhttp.open('GET', url, true);	
			xmlhttp.onreadystatechange = function() {
				switch (xmlhttp.readyState) {
					case 1: break;
					case 2: break;
					case 3: break;
					case 4:
						var data = xmlhttp.responseText; //The content data which has been retrieved ***
						if (data.substring(0,5) == "ERROR") {
							alert("An error has occured with the submitted postcode:\n\n"+data);
						} else {
								// Data has been returned to us.
							// Stored them into the object called addresses
							var address = jQuery.parseJSON(data);
							
							// Populate the street_name label and field
							$('street_name_label').innerHTML = address.street_name;
							$('street_name').value = address.street_name;
							
							// Populate the town drop down
							$('town_label').innerHTML = "";
							$('town').options.length = 0;
							$('town').options[0] = new Option("-- Please select --","0");
							// If extra towns are supplied, add them to the drop down
							if (address.extra_towns) {
								var extra_towns = address.extra_towns;
								for (var i=0; i< extra_towns.length; i++) {
									$('town').options[(i+1)] = new Option(extra_towns[i],extra_towns[i]);
								}							
							}							
							$('town').style.display = "";
							
							// Populate the longitude and latitude hidden fields
							$('longitude').value = address.longitude;
							$('latitude').value = address.latitude;
							address = null;
						}
						// hide the loading label
						$('findAddressLoadingLabel').style.display = "none";
						// show the "find address" button
						$('findAddressLink').style.display = "";
						break;
				}	
			}
			xmlhttp.send(null)
			
		}
	}
	
