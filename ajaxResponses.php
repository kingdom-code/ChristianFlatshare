<?php

use CFS\ChurchDirectory\CFSChurchDirectory;
use CFS\GeoEncoding\CFSGeoEncoding;
use CFS\Database\CFSDatabase;

header('Content-type: application/json');

// Autoloader
require_once 'web/global.php';

if (isset($_GET['target'])) {
    switch($_GET['target']) {
        case 'churchInfo':
            if (isset($_GET['church_id'])) {
                $CFSChurchDirectory = new CFSChurchDirectory();
                $result = $CFSChurchDirectory->getChurchInfo($_GET['church_id']);
                if ($result === FALSE) {
                    return FALSE;
                }
                else {
                    print json_encode($result);
                }
            }
            else {
                return FALSE;
            }
            break;
        case 'geoEncode':
            if (isset($_GET['lat']) && isset($_GET['lng'])) {
                $geoEncoder = new CFSGeoEncoding();
                print $geoEncoder->getReverseLookupJSON($_GET['lat'], $_GET['lng']);
            }
            else {
                return TRUE;
            }
            break;
        case 'saveDefault':        
            if ($currentUser) {
                $CFSDatabase = new CFSDatabase();
                $connection = $CFSDatabase->getConnection();
                
                $defaults = $_SESSION['search_defaults'];
            
                $defaults[$_GET['key']] = $_GET['value'];
            
                $sql = "UPDATE cf_users SET search_defaults = :defaults WHERE user_id = :user";
                $saveDefaults = $connection->prepare($sql);
                $saveDefaults->bindValue('defaults', serialize($defaults));
                $saveDefaults->bindValue('user', $currentUser['user_id']);
                $saveDefaults->execute();
            }
            
            $_SESSION['search_defaults'][$_GET['key']] = $_GET['value'];
            break;
        case 'agePreview':
            // CONVERT AGES
            list($min, $max) = explode('-', $_GET['age']);
        
            $min = ageConvert($min, 'min_age');
            $max = ageConvert($max, 'max_age');
            
            $age = cleanAge($min, $max, $_GET['type']);
            
            print json_encode($age);
            break;
    }
}
