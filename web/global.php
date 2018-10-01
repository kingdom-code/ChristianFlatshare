<?php

use CFS\International\CFSInternational;
use CFS\Facebook\CFSFacebook;
use CFS\User\CFSUser;
use CFS\Database\CFSDatabase;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/configuration.php';

error_reporting(E_ALL & ~E_NOTICE);

// Increase timeout to 24hrs
if (!isset($_SESSION)){
  ini_set('session.gc_maxlifetime', 86400);
  session_set_cookie_params(86400);
  session_start();
}

// Initialiase variables
if (!isset($rememeber)) $remember = '';
if (!isset($password)) $password = '';
if (!isset($email)) $email = '';

// Set country?
if (isset($_GET['iso'])) {
    $_SESSION['country_iso'] = $_GET['iso'];
}

// Initialise International Helper
$CFSIntl = new CFSInternational();

require_once __DIR__ . '/../includes/functions.php';

connectToDB();

// Initialise Templator
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__ . '/../cache/templates',
    'debug' => false,
));

// Initialise Facebook
$CFSFacebook = new CFSFacebook();

// Initialise User
$CFSUser = new CFSUser();
$currentUser = $CFSUser->getUser();

// Initialise Database
$CFSdb = new CFSDatabase();

// Update Hidden Ads Preference
if (isset($_GET['show_hidden_ads'])) {
    $hidden_ads = ($_GET['show_hidden_ads'] == 'yes') ? 1 : 0;
    
    if ($currentUser['show_hidden'] != $hidden_ads) {
        $CFSDatabase = new CFSDatabase();
        $connection = $CFSDatabase->getConnection();
        $sql = "UPDATE cf_users SET show_hidden = :hidden WHERE user_id = :user";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue("hidden", (int)$hidden_ads);
        $stmt->bindValue("user", $currentUser['user_id']);
        $result = $stmt->execute();
        
        $currentUser['show_hidden'] = $hidden_ads;
    }
}

$theme = array();
$userCountry = $CFSIntl->getUserCountry();

$homeURL = '/';

if (getUserIdFromSession()) {
    $loggedIn = TRUE;
}
else {
    $loggedIn = FALSE;
}

if (isset($validatingSession) && $validatingSession) return;

$theme['header'] = $twig->render('header.twig', array('homeURL' => $homeURL, 'loggedIn' => $loggedIn, 'country' => $userCountry['iso']));
$theme['superheader'] = $twig->render('superheader.twig', array('country' => $userCountry));

if ($loggedIn) {
    $currentPage = substr(strrchr($_SERVER['PHP_SELF'],"/"),1);
    $suspended = accountSuspended();
    $NumberEmailsInbox = getNumberEmailsInbox();
    $NumberEmailsSent = getNumberEmailsSent();
    $NumberSavedAds = getNumberSavedAds();
    $NumberOfPalups = getNumberOfPalups();
    
    $menu = array();
    
	// For advertisers
	if (isset($_SESSION['u_access']) && $_SESSION['u_access'] == "advertiser") {
        $menu['Advertisers Menu']['advertisers.php']                     = array('title' => 'Your banners', 'num' => NULL);
        $menu['Advertisers Menu']['advertisers-manage-banner.php']       = array('title' => 'Upload a new banner', 'num' => NULL);
        
        //if (!$currentUser['facebookEnabled']) {
            $menu['Advertisers Menu']['your-account-change-password.php']    = array('title' => 'Change password', 'num' => NULL);
        //}
        
        if ($currentUser['facebookEnabled']) {
            $menu['Your account']['fb-logout.php']  = array('title' => 'Logout', 'num' => NULL);
        }
        else {
            $menu['Your account']['logout.php']     = array('title' => 'Logout', 'num' => NULL);
        }
    }
    else {
        if (!$suspended) {
            $menu['Post adverts']['post-choice.php']                        = array('title' => 'Post an Accommodation Advert', 'num' => NULL);
            $menu['Your ads and messages']['your-account-manage-posts.php'] = array('title' => 'Your adverts', 'num' => NULL);
        }
        
        $menu['Your ads and messages']['your-account-received-messages.php']  = array('title' => 'Your messages', 'num' => $NumberEmailsInbox);
        $menu['Your ads and messages']['your-account-sent-messages.php']      = array('title' => 'Your sent messages', 'num' => $NumberEmailsSent);
        $menu['Your ads and messages']['your-account-saved-ads.php']          = array('title' => 'Your saved adverts', 'num' => $NumberSavedAds);
        $menu['Your ads and messages']['your-account-whole-place-palups.php'] = array('title' => 'Your whole place pal-ups', 'num' => $NumberOfPalups);
        
        if (!$suspended) {
            $menu['Your account']['your-suspend-account.php']           = array('title' => 'Suspend your account', 'num' => NULL);
            
            //if (!$currentUser['facebookEnabled']) {
                $menu['Your account']['your-account-change-password.php']   = array('title' => 'Change password', 'num' => NULL);
            //}
            
            $menu['Your account']['your-account-change-name.php']       = array('title' => 'Change email or name', 'num' => NULL);
        }
        else {
            $menu['Your account']['your-account-manage-posts.php']   = array('title' => 'Un-suspend your account', 'num' => NULL);
        }
        
        if ($currentUser['facebookEnabled']) {
            $menu['Your account']['fb-logout.php']  = array('title' => 'Logout', 'num' => NULL);
        }
        else {
            $menu['Your account']['logout.php']     = array('title' => 'Logout', 'num' => NULL);
        }
    }
    
    // Refresh FB Friends?
    $now = new \DateTime(NULL, new \DateTimeZone('UTC'));
    $seven_days_ago = $now->sub(new \DateInterval('P7D'));
    $refreshFBFriends = (($currentUser['fb_friends_last_refreshed'] <= $seven_days_ago && $currentUser['fb_friends_last_refreshed'] != NULL)) ? FALSE : TRUE;
    
    $theme['side'] = $twig->render('memberMenu.twig', array('menu' => $menu, 'currentPage' => $currentPage, 'showHidden' => $currentUser['show_hidden'], 'FacebookLoginURL' => $CFSFacebook->getLoginURL(), 'currentUser' => $currentUser, 'refreshFBFriends' => $refreshFBFriends, 'country' => $userCountry));
}
else {
    $currentPage = substr(strrchr($_SERVER['PHP_SELF'],"/"),1);
    $theme['side'] = $twig->render('loginOrRegister.twig', array('loginForm' => createLoginForm($email,$password,$remember), 'FacebookLoginURL' => $CFSFacebook->getLoginURL(), 'currentPage' => $currentPage, 'country' => $userCountry));
}
