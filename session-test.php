<?php

	session_start();
	ini_set("session.gc_maxlifetime","10");
	$lifetime = ini_get("session.gc_maxlifetime"); 
	
	
	echo "<p>Max lifetime for session is ".$lifetime." seconds</p>";
	

?>
<pre><?php print print_r($_SESSION,true)?></pre>
<p>The session variable test has the value: <strong><?php print $_SESSION['test']?></strong></p>
<p>Countdown: <span id="countdown"><?php print $lifetime?></span></p>
<script language="javascript" type="text/javascript">

	var timerID = 0;
	var seconds = <?php print $lifetime?>;
	
	timerID = setTimeout("updateTimer()",1000);
	
	function updateTimer() {
		seconds = seconds - 1;
		document.getElementById("countdown").innerHTML = seconds;
		timerID = setTimeout("updateTimer()",1000);
	}

</script>
<?php print phpinfo(8)?>