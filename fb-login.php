<?php

$validatingSession = true;

// Autoloader
require_once 'web/global.php';

$CFSFacebook->getSessionFromRedirect($currentUser);

// $user = $CFSFacebook->getUserFromFacebook($current_user);
//
// $_SESSION['u_id'] = $user['user_id'];
// $_SESSION['u_name'] = $user['name'];
// $_SESSION['u_email'] = $user['email_address'];
// $_SESSION['u_access'] = $user['access'];
// $_SESSION['show_hidden_ads'] = 'no';
//
// // Redirect according to user priviledges
// if ($_SESSION['u_access'] == 'member') {
//     header("Location: your-account-manage-posts.php");
// }
// else if ($_SESSION['u_access'] == 'advertiser') {
//     header("Location: advertisers.php");
// }
// else {
//     header("Location: administrator/index.php");
// }
