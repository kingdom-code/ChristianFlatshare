<?php
// Autoloader
require_once 'web/global.php';
    
use CFS\GeoEncoding\CFSGeoEncoding;

$geoEncoder = new CFSGeoEncoding();

//$address = $geoEncoder->getReverseLookup($_GET['lat'], $_GET['lng']);

$address = $geoEncoder->getReverseLookupRaw($_GET['lat'], $_GET['lng']);

var_dump($address);
?>