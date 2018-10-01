<?php

// Autoloader
require_once 'web/global.php';

// Import friends of the current user
print $CFSFacebook->importFriends($currentUser);

?>