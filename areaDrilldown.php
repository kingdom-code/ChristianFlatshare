<?php
use CFS\Searching\CFSSearching;
use CFS\GeoEncoding\CFSGeoEncoding;

// Autoloader
require_once 'web/global.php';

$CFSSearching = new CFSSearching();
$CFSGeoEncoding = new CFSGeoEncoding();

$columns = array(
    '0' => array(),
    '1' => array(),
    '2' => array(),
    '3' => array(),
);

$i = 0;

if (isset($_GET['type'])) {
    if ($_GET['type'] == 'wanted') {
        $type = 'wanted';
    }
    else {
        $type = 'offered';
    }
}
else {
    $type = 'offered';
}

if (isset($_GET['area'])) {
    // Redirect to display
    if ($_GET['area'] == $_GET['region']) {
        $search = $_GET['area'];
    }
    else if ($_GET['area'] == 'all') {
        $search = $_GET['region'];
    } else {
        $search = $_GET['area'] . ', ' . $_GET['region'];
    }
    
    $arguments = array(
        'area' => $_GET['area'],
        'region' => $_GET['region'],
        'country' => $userCountry['iso'],
        'search_type' => 'intarea',
        'post_type' => $type,
        'flatshare' => 1,
        'familyshare' => 1,
        'wholeplace' => 1,
        'place' => $search,
        'radius' => 15,
    );
    
    $args = http_build_query($arguments);
    
    header("Location: display.php?" . $args);
}
elseif (isset($_GET['region'])) {
    // 2nd level down
    $results = $CFSSearching->getCountryListings($type, 'area', $_GET['region'], getCurrentCountry());
    $total = 0;
    
    foreach ($results as $area) {
        if ($i == 4) { $i = 0; }
        
        $columns[$i][] = array(
            'title' => $area['area'],
            'num_ads' => $area['num_ads'],
            'url' => 'areaDrilldown.php?type=' . $type . '&region=' . $_GET['region'] . '&area=' . $area['area'],
            'classes' => 'external',
        );
        
        $i++;
        $total += $area['num_ads'];
    }
    
    if ($i == 4) { $i = 0; }
    
    $all = 'areaDrilldown.php?type=' . $type . '&region=' . $_GET['region'] . '&area=all';
    $back = 'areaDrilldown.php?type=' . $type;
}
else {
    // 1st level down
    $results = $CFSSearching->getCountryListings($type, 'region', NULL, getCurrentCountry());
    
    foreach ($results as $area) {
        if ($i == 4) { $i = 0; }
        
        $columns[$i][] = array(
            'title' => $area['region'],
            'num_ads' => $area['num_ads'],
            'url' => 'areaDrilldown.php?type=' . $type . '&region=' . $area['region'],
            'classes' => NULL,
        );
        
        $i++;
    }
    
    $all = NULL;
    $back = NULL;
}

print $twig->render('areaDrilldown.html.twig', array('kind' => $type, 'columns' => $columns, 'back' => $back, 'all' => $all));