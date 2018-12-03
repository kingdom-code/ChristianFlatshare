<?php

echo '4444';
sleep(3);
//header("Location: http://www.example.com/"); /* Redirect browser */
//header("Location: test.php"); /* Redirect browser */

echo '1111';
/* Make sure that code below does not get executed when we redirect. */
exit;
?>
