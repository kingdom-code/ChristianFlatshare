<?php
// Autoloader
require_once 'web/global.php';

header('Content-type: application/json');

use CFS\GeoEncoding\CFSGeoEncoding;

$geoEncoder = new CFSGeoEncoding();

$address = $geoEncoder->getReverseLookup($_GET['lat'], $_GET['lng']);

if ($address === FALSE) return FALSE;

$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname' => DB_NAME,
    'user' => DB_USER_NAME,
    'password' => DB_PASSWORD,
    'host' => DB_HOST,
    'driver' => 'pdo_mysql',
);
$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$sql = "INSERT INTO cf_global_data SET lat = :lat, lng = :lng, country = :country, street = :route, area = :locality, region = :admin_level, postal_code = :postal_code";
$stmt = $connection->prepare($sql);
$stmt->bindValue("lat", $address['latitude']);
$stmt->bindValue("lng", $address['longitude']);
$stmt->bindValue("country", $address['country']);
$stmt->bindValue("route", $address['street']);
$stmt->bindValue("locality", $address['area']);
$stmt->bindValue("admin_level", $address['region']);
$stmt->bindValue("postal_code", $address['postal_code']);
$stmt->execute();

print json_encode($address);
?>