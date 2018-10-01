<?php

	/* CONSTANTS */
	$accountCode = "LONGH11112";
	$licenseCode = "RE46-TE92-PG96-FJ41";
	$url = "";

	if (isset($_POST['postcode'])) { $postcode = $_POST['postcode']; } else { $postcode = NULL; }
	if (isset($_REQUEST['propertyId'])) { $propertyId = $_REQUEST['propertyId']; } else { $propertyId = NULL; }
	
	// Step 1: A postcode has been entered.
	// Query the PostcodeAnywhere system to get a list of all properties in this postcode.
	if ($postcode) {
	
		// First query the PostcodeAnywhere service
		$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
		$url .= "&action=lookup";
		$url .= "&type=by_postcode";
		$url .= "&postcode=".urlencode($postcode);
		$url .= "&account_code=".urlencode($accountCode);
		$url .= "&license_code=".urlencode($licenseCode);
		$url .= "&machine_id=".urlencode($machineId);
		
		// Make the request and get the XML data as a string
		$data = file_get_contents($url);
		
		// Now use the PEAR XML Unserializer to conver the XML document into a *D array
		require_once "includes/class.unserializer.php";
		$options = array(
			'complexType' => 'array',
			'parseAttributes' => 'true'			
		);
		
		// Create an instance of the XML Unserializer class
		$xml = new XML_Unserializer($options);
		$result = $xml->unserialize($data,false); // The second parameter is "false" which indicates that we're parsing a string
		$data = $xml->getUnserializedData();
		
		// 1: No properties existing on postcode 
		// i.e. $data['Data']['Items'] == 0
		if (!$data['Data']['Items']) {
			echo "We could not find the postcode in our database";
		} else if (isset($data['Data']['Item']['error_number'])) {
			echo $data['Data']['Item']['message'];		
		} else {
			// 2: Properties were found
			if ($data['Data']['Items'] == 1) { // If only one property was found
				$options .= '<option value="'.$data['Data']['Item']['id'].'">'.$data['Data']['Item']['description'].'</option>';
			} else { // Else if more than one property was found
				foreach($data['Data']['Item'] as $value) {
					$options .= '<option value="'.$value['id'].'">'.$value['description'].'</option>'."\n";
				}
			}
			//die('<pre>'.print_r($data,true).'</pre>');
			$step = 2;
		}
		
	}
	
	// Step 2: A property id has been selected.
	// Query the PostcodeAnywhere system to find the address and geographic details for this property.
	if ($propertyId) {
	
		// First query the PostcodeAnywhere service
		$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
		$url .= "&action=geocode";
		$url .= "&id=".urlencode($propertyId);
		$url .= "&accuracy=HIGH";
		$url .= "&account_code=".urlencode($accountCode);
		$url .= "&license_code=".urlencode($licenseCode);

		// Make the request and get the XML data as a string
		$data = file_get_contents($url);
		
		// Now use the PEAR XML Unserializer to conver the XML document into a *D array
		require_once "includes/class.unserializer.php";
		$options = array(
			'complexType' => 'array',
			'parseAttributes' => 'true'			
		);
		
		// Create an instance of the XML Unserializer class
		$xml = new XML_Unserializer($options);
		$result = $xml->unserialize($data,false); // The second parameter is "false" which indicates that we're parsing a string
		$data = $xml->getUnserializedData();
		
		// If an error has occured
		if (isset($data['Data']['Item']['error_number'])) {
			
			echo "2nd attempt<br/><br>";
			// Repeat the exercise but this time with a low accuracy setting
			$url = "http://services.postcodeanywhere.co.uk/xml.aspx?";
			$url .= "&action=geocode";
			$url .= "&id=".urlencode($propertyId);
			$url .= "&accuracy=LOW";
			$url .= "&account_code=".urlencode($accountCode);
			$url .= "&license_code=".urlencode($licenseCode);
			$data = file_get_contents($url);
			$result = $xml->unserialize($data,false);
			$data = $xml->getUnserializedData();
			die('<pre>'.print_r($data,true).'</pre>');
			
		}
		
		die('<pre>'.print_r($data,true).'</pre>');
		// 1: No properties existing on postcode 
		// i.e. $data['Data']['Items'] == 0
		/*if (!$data['Data']['Items']) {
			echo "We could not find the postcode in our database";
		} else if (isset($data['Data']['Item']['error_number'])) {
			echo $data['Data']['Item']['message'];		
		} else {
			// 2: Properties were found
			if ($data['Data']['Items'] == 1) { // If only one property was found
				$options .= '<option value="'.$data['Data']['Item']['id'].'">'.$data['Data']['Item']['description'].'</option>';
			} else { // Else if more than one property was found
				foreach($data['Data']['Item'] as $value) {
					$options .= '<option value="'.$value['id'].'">'.$value['description'].'</option>'."\n";
				}
			}
			//die('<pre>'.print_r($data,true).'</pre>');
			$step = 3;
		}*/
	
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<body>

<h1>Step 1: Enter your postcode:</h1>
<form name="postcodeEntry" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<p>
	<input type="text" name="postcode" value="<?php print $postcode?>" />
	<input type="submit" name="Submit" value="Submit" />
</p>
</form>
<?php if ($step > 1) { ?>
<h1>Step 2: Choose your flat / house:</h1>
<form name="idSelection" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<p>
	<select name="propertyId" size="10">
		<?php print $options?>
	</select>
	<input type="submit" name="Submit" value="Submit" />
</p>
</form>
<p><strong>Please note:</strong><br />Two PostcodeAnywhere credits will be subtracted from your account by pressing the "Submit" button.</p>
<?php } ?>
<?php if ($step > 2) { ?>
<h1>WE KNOW WHERE YOU LIVE!</h1>
<p>(Muaahahahahahaha-har har har haaaaaaar).</p>
<?php } ?>

</body>
</html>
