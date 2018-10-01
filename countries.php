<?php
session_start();

// Autoloader
require_once 'web/global.php';

connectToDB();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Countries - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body class="countries-page">
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->

            <div style="width:599px; float:left;">
                <h1 class="mt0 mb10">Please choose a country</h1>
            </div>

			<div style="width:599px; float:left;">

				<table width="133%" border="0" cellspacing="10" cellpadding="0">
					<tr>
  					<td width="50" valign="top">
						<td width="117" valign="top"><a href="/index.php?iso=US&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>"><img src="images/flags/flag_usa.gif" alt="Flag of USA" width="117" height="88" /></a></td>
						<td width="215" valign="middle"><strong><a href="/index.php?iso=US&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>">United States of America</a></strong></a></td>

						<td width="143" valign="top"><a href="/index.php?iso=AU&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>"><img src="images/flags/flag_australia.gif" alt="Flag of Canada" width="117" height="88" /></a></td>
						<td width="210" valign="middle"><strong><a href="/index.php?iso=AU&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>">Australia</a></strong></td>
				
					</tr>
					<tr>
	      		<td width="50" valign="top">
						<td width="117" valign="top"><a href="/index.php?iso=CA&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>"><img src="images/flags/flag_canada.gif" alt="Flag of Canada" width="117" height="88" /></a></td>
						<td width="215" valign="middle"><strong><a href="/index.php?iso=CA&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>">Canada</a></strong></td>
								
						<td valign="top"><a href="/index.php?iso=IE&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>"><img src="images/flags/flag_ireland.gif" alt="Flag of Ireland" width="117" height="88" /></a></td>
						<td valign="middle"><strong><a href="/index.php?iso=IE&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>">Ireland</a></strong></td>				
					</tr>										
					<tr>
					  					<td width="50" valign="top">
						<td valign="top"><a href="/index.php?iso=ZA&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>"><img src="images/flags/flag_south_africa.gif" alt="Flag of South Africa" width="117" height="88" /></a></td>
						<td valign="middle"><strong><a href="/index.php?iso=ZA&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>">South Africa</a></strong></td>
						<td valign="top"><a href="/index.php?iso=GB&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>"><img src="images/flags/flag_uk.gif" alt="Flag of United Kingdom" width="117" height="88" /></a></td>
						<td valign="middle"><strong><a href="/index.php?iso=GB&amp;from=<?php print ($_GET['from']) ? $_GET['from'] : ''; ?>">United Kingdom</a></strong></td>
						
		
					</tr>															
				</table>				
				
			</div>

			<div class="cc0"><!----></div>
		<!-- InstanceEndEditable -->
		</div>
		<div class="redMenu">
			<ul>
				<!--<li><a href="../flat-finding-tips.php">flat finding tips</a></li>-->
				<li><a href="advertising.php">advertising</a></li>			
				<li><a href="where-does-all-the-money-go.php">where does the money go?</a></li>
				<li><a href="glossary.php">glossary</a></li>
				<li><a href="terms-and-conditions.php">terms &amp; conditions</a></li>
				<li><a href="privacy-policy.php">privacy policy</a></li>
				<li><a href="contact-us.php">contact us</a></li>
				<li class="noSeparator"><a href="resources.php">links &amp; resources</a></li>
			</ul>
		</div>
		<div id="footerText">
			<p class="m0"><strong>Christian Flatshare... helping accommodation seekers connect with the local church community<br />
			Finding homes, growing churches and building communities </strong>&copy; ChristianFlatShare.org 2007-<?php print date("Y")?></p>
	  </div>
	</div>
	<div id="footer"><img src="images/spacer.gif" alt="*" width="1" height="12"/></div>
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
<?php print getTrackingCode();?>
</body>
<!-- InstanceEnd --></html>
