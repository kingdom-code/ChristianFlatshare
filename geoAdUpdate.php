<?php
// Autoloader
require_once 'web/global.php';

use CFS\GeoEncoding\CFSGeoEncoding;

$geoEncoder = new CFSGeoEncoding();

$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname' => DB_NAME,
    'user' => DB_USER_NAME,
    'password' => DB_PASSWORD,
    'host' => DB_HOST,
    'driver' => 'pdo_mysql',
);
$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$sql = "SELECT * FROM cf_banners_locations WHERE latitude is NULL";
$stmt = $connection->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll();

$sql = "UPDATE cf_banners_locations SET latitude = :lat, longitude = :lng WHERE location_id = :location";

foreach ($results as $location) {
    $address = $geoEncoder->getLookup($location['place'], 'GB');
    
    $insert = $connection->prepare($sql);
    $insert->bindValue("lat", $address['lat']);
    $insert->bindValue("lng", $address['lng']);
    $insert->bindValue("location", $location['location_id']);
    $insert->execute();
}

?>