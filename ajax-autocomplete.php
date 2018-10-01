<?php

// Autoloader
require_once 'web/global.php';

connectToDB();

header('Content-type: application/json');

// If the home page auto-completer posted to this page	
if (isset($_POST['value'])) {

	$v = trim($_POST['value']);

	$query = "
		SELECT CONCAT(place_name,', ',cj.county, ' (',cj.postcode,')') 
		FROM   cf_uk_places ckp, 
			   cf_jibble_postcodes cj 
		WHERE  ckp.place_name LIKE '".$v."%' 
		AND    ckp.postcode = cj.postcode 
		UNION 
		SELECT CONCAT(cj.postcode, ' ', place_name,', ',cj.county) 
		FROM   cf_uk_places ckp, 
			   cf_jibble_postcodes cj 
		WHERE  ckp.postcode LIKE '".$v."%' 
		AND    ckp.postcode = cj.postcode 
		LIMIT 0,20		
	";
    
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
    $results = array();
    
	if (mysqli_num_rows($result) !== FALSE) {
		while($row = mysqli_fetch_row($result)) {
			$results[] = $row[0];			
		}	
	} else {
		$results[] = "No results found";
	}	
    
    print json_encode($results);
}
?>