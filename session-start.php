<?php

ini_set("session.gc_maxlifetime","10");
session_start();
$_SESSION['test'] = "Hello world!";

?>
<p>The session variable 'test' now has the value <strong>Hello world!</strong></p>
<p><a href="session-test.php">Visit the test page to see the lifetime</a></p>

