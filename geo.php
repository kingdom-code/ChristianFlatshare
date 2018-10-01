<style>
h3 {
    font-family: arial, sans-serif;
}
.odd {
    background: #F1F1F1;
}
td {
    font-family: arial, sans-serif;
    padding: 6px 10px;
}
</style>
<?php
    session_start();
    
    require('includes/configuration.php');
    require('includes/functions.php');
    
    require_once 'vendor/autoload.php';
    
    use CFS\GeoEncoding\CFSGeoEncoding;
    
    connectToDB();
    
    $geohelper = new CFSGeoEncoding();
    
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'dbname' => 'christianflatshare',
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    );
    $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    
    $sql = "SELECT church_id, latitude, longitude, route, locality, admin_level, country, postal_code, church_name, church_url FROM cf_church_directory";
    $stmt = $connection->prepare($sql);
    $stmt->execute();
    $churches = $stmt->fetchAll();
    
    foreach ($churches as $church) {
        $address = $geohelper->getReverseLookup($church['latitude'], $church['longitude']);
        $sql = "UPDATE cf_church_directory SET route = :route, locality = :locality, admin_level = :admin_level, country = :country, postal_code = :postal_code WHERE church_id = :church_id";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue("route", $address['street']);
        $stmt->bindValue("locality", $address['county']);
        $stmt->bindValue("admin_level", $address['region']);
        $stmt->bindValue("country", $address['country']);
        $stmt->bindValue("postal_code", $address['postcode']);
        $stmt->bindValue("church_id", $church['church_id']);
        $stmt->execute();
        sleep(1);
    }
    
    exit;
    
    
    $addresses = array();

    
    
    // $addresses['GB'][] = $geohelper->getReverseLookup(51.64709, -0.25823);
    // $addresses['GB'][] = $geohelper->getReverseLookup(52.77113, -2.48276);
    // $addresses['GB'][] = $geohelper->getReverseLookup(54.16241, -2.14330);
    // $addresses['GB'][] = $geohelper->getReverseLookup(57.57655, -4.43932);
    // $addresses['GB'][] = $geohelper->getReverseLookup(52.14032, -4.16378);
    // 
    
    /*
    $addresses['US'][] = $geohelper->getReverseLookup(40.628987, -74.021676);
        $addresses['US'][] = $geohelper->getReverseLookup(28.14796, -81.60867);
        $addresses['US'][] = $geohelper->getReverseLookup(32.382505, -98.979285);
        $addresses['US'][] = $geohelper->getReverseLookup(40.63999, -116.93689);
        $addresses['US'][] = $geohelper->getReverseLookup(37.767290, -122.417449);*/
    
    

/*
    $addresses['CA'][] = $geohelper->getReverseLookup(49.16900, -122.84112);
    $addresses['CA'][] = $geohelper->getReverseLookup(53.517552, -113.484491);
    $addresses['CA'][] = $geohelper->getReverseLookup(45.54511, -73.62315);
    $addresses['CA'][] = $geohelper->getReverseLookup(49.01520, -88.26562);
    $addresses['CA'][] = $geohelper->getReverseLookup(53.917133, -122.77514);*/


    
    
/*
    $addresses['AU'][] = $geohelper->getReverseLookup(-31.94690, 115.84420);
    $addresses['AU'][] = $geohelper->getReverseLookup(-34.26404, 135.72542);
    $addresses['AU'][] = $geohelper->getReverseLookup(-37.850207, 145.070056);
    $addresses['AU'][] = $geohelper->getReverseLookup(-27.42360, 153.03823);
    $addresses['AU'][] = $geohelper->getReverseLookup(-12.49986, 130.99430);
    */

    
/*
    $addresses['ZA'][] = $geohelper->getReverseLookup(-33.94121, 18.57340);
    $addresses['ZA'][] = $geohelper->getReverseLookup(-33.96185, 22.46197);
    $addresses['ZA'][] = $geohelper->getReverseLookup(-28.44538, 21.24803);
    $addresses['ZA'][] = $geohelper->getReverseLookup(-26.19456, 27.96469);
    $addresses['ZA'][] = $geohelper->getReverseLookup(-23.90270, 29.44829);*/

    

    $addresses['IE'][] = $geohelper->getReverseLookup(51.896332, -8.485667);
    $addresses['IE'][] = $geohelper->getReverseLookup(52.78862, -7.33366);
    $addresses['IE'][] = $geohelper->getReverseLookup(53.350150, -6.257167);
    $addresses['IE'][] = $geohelper->getReverseLookup(53.280656, -9.042770);
    $addresses['IE'][] = $geohelper->getReverseLookup(52.446494, -9.485013);
    $addresses['IE'][] = $geohelper->getReverseLookup(51.996332, -8.585667);
    $addresses['IE'][] = $geohelper->getReverseLookup(52.58862, -7.43366);
    $addresses['IE'][] = $geohelper->getReverseLookup(53.650150, -6.157167);
    $addresses['IE'][] = $geohelper->getReverseLookup(53.480656, -9.142770);
    $addresses['IE'][] = $geohelper->getReverseLookup(52.146494, -9.385013);

    
    $current_country = NULL;
    foreach ($addresses as $country => $locations) {
        print '<h3>' . $country . '</h3><table>';
        foreach ($locations as $address) {
            print '<tr>
                <td>' . $address['street'] . '</td>
                <td class="odd">' . $address['county'] . '</td>
                <td>' . $address['region'] . '</td>
                <td class="odd">' . $address['country'] . '</td>
                <td>' . $address['postcode'] . '</td>
            </tr>';
        }
        print '</table>';
    }
    
	$result = mysqli_query($GLOBALS['mysql_conn'], "SELECT * FROM cf_wanted LIMIT 0,10");

    $i = 0;
    
    if ($result !== FALSE) {	
	    while($row = mysqli_fetch_assoc($result)) {
            $address = $addresses['IE'][$i];
            
            $query = "UPDATE cf_wanted SET approved = 1, suspended = 0, route = '" . $address['street'] . "', locality = '" . $address['county'] . "', admin_level = '" . $address['region'] . "', country = '" . $address['country'] . "', postal_code = '" . $address['postcode'] . "', latitude = '" . $address['latitude'] . "', longitude = '" . $address['longitude'] . "', published = 1, expiry_date = '2013-05-10' WHERE wanted_id = " . $row['wanted_id'];
            
            var_dump($query);
            
            mysqli_query($GLOBALS['mysql_conn'], $query);
            $i++;
        }
    }
?>