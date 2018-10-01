	
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
		
			var v = $('locationPicker').value.trim();
			var postcode_regexp = /^([A-Z]{1,2}[0-9][0-9A-Z]?)\s{0,1}([0-9][A-Z]{2})$/i;
			var partial_postcode_regexp = /^[A-Z]{1,2}[0-9][A-Z0-9]?$/i;
			
			// If v is a full UK postcode
			if (v.trim() == "") {
			
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
					v = v.trim();
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
