<?php

// Autoloader
require_once 'web/global.php';

$CFSFacebook->logout();

unset($_SESSION);
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();
header("Location: index.php");