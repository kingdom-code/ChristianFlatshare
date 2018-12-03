<?php
// Note - cannot have any output before setcookie
if ( ! isset($_COOKIE['zapp']) ) {
    setcookie('zapp', '42', time()+3600);
}
?>
<?php print_r($_COOKIE); ?>
<p><a href="cookie.php">Click Me!</a> or press Refresh</p>
<?php print_r($_COOKIE); ?>
