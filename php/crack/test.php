<?php 
 session_start();
 // $v = isset($_SESSION['v']) ? $_SESSION{'v'] : 0;
$_SESSION['v']++;
 
 $t = isset($_POST['email']) ? $_POST['email'] : '';
?>

<form method="post">
Email:<input type="email" value="<?= $t ?>" name="email" /><br>

<input type="submit" value="tt"/>
</form>
<pre>
<?= "value=".$t ?><br>
<?= "v=".$v ?><br>
<?= "session v=".$_SESSION['v'] ?><br>
<?= "v=".var_dump($_SESSION) ?>
</pre>
<?php
//session_unset()
?>
