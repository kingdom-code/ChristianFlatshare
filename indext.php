<?php
session_start();

// Autoloader
require_once 'web/global.php';

// Set country?
if (isset($_GET['iso'])) {
    $_SESSION['country_iso'] = $_GET['iso'];
}

// Redirect user depending on where they are coming from
$country = getCurrentCountry();

switch($country) {
    case 'GB':
        header('Location: home-uk.php');
        break;
    default:
        header('Location: home-international.php');
        break;
}