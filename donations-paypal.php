<?php

session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();


	// Dissallow access if user not logged in

	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- InstanceBeginEditable name="doctitle" -->
<title>Donate</title>

<!-- InstanceEndEditable -->

<link href="styles/web.css" rel="stylesheet" type="text/css" />

<link href="styles/headers.css" rel="stylesheet" type="text/css" />

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

<!-- InstanceBeginEditable name="head" -->

<script language="javascript" type="text/javascript">

	function popup() {

		window.open("paypal.html", "popup","resizable=1,height=480,width=580");

		return false;

	}

</script>

<!-- InstanceEndEditable -->

<!-- InstanceParam name="mainContentClass" type="text" value="" -->

</head>



<body>

<div id="canvas">

	<div id="header"><!----></div>

	<div id="content">

		<div id="logoContainer">

			<div id="logo"><a href="index.php"><img src="images/logo.gif" alt="Christian Flatshare logo (click to return to home page)" width="462" height="71" border="0" /></a></div>

			<div id="iconCanvas">			

				<a href="index.php">

					<img src="images/icon-1-over.gif" width="80" height="60" border="0" class="iconOver" />

					<img src="images/icon-1.gif" width="80" height="60" border="0" />

					<div class="iconText">Home page</div>

				</a>

				<a href="countries.php">

					<img src="images/icon-2-over.gif" width="80" height="60" border="0" class="iconOver" />

					<img src="images/icon-2.gif" width="80" height="60" border="0" />

					<div class="iconText">Countries</div>

				</a>			

				<a href="contact-us.php">

					<img src="images/icon-3-over.gif" width="80" height="60" border="0" class="iconOver" />

					<img src="images/icon-3.gif" width="80" height="60" border="0" />

					<div class="iconText">Contact us</div>

				</a>

			<?php if (!$_SESSION['u_id']) { ?>

				<a href="login.php">

					<img src="images/icon-4-over.gif" width="80" height="60" border="0" class="iconOver" />

					<img src="images/icon-4.gif" width="80" height="60" border="0" />

					<div class="iconText">Register / Login</div>

				</a>

			<?php } else { ?>

				<a href="your-account-manage-posts.php"> 

					<img src="images/icon-my-ads-over.gif" width="80" height="60" border="0" class="iconOver" />

					<img src="images/icon-my-ads.gif" width="80" height="60" border="0" />

					<div class="iconText">Your ads</div>

				</a>

			<?php } ?>

			</div>									

		</div>

		<a name="m"></a>		

		<div class="redMenu">

			<ul>

				<li><a href="about-us.php">about Christian Flatshare</a></li>

				<li><a href="what-is-a-christian.php">what is a Christian?</a></li>

				<li><a href="stories.php">CFS Stories</a></li>

				<li><a href="use-cfs-in-your-church.php">use CFS in YOUR church</a></li>

				<li><a href="churches-using-cfs.php?area=Greater%20London#directory">churches using CFS</a></li>

				<li class="noSeparator"><a href="frequently-asked-questions.php">Frequently Asked Questions</a></li>

			</ul>

		</div>

		<div class="" id="mainContent">

		<!-- InstanceBeginEditable name="mainContent" -->

		<h1 class="mt0">Donate to ChristianFlatshare.org</h1>

		<p><strong>Thank you for your interest in donating to ChristianFlatShare.org!</strong><br />We feel we are unable to accept donations until we have completed our charity registration,<br />which will hopefully occur later this year. </p>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="paypal" id="paypal">

		<!--<div id="paypalCanvas">

		<p class="mt0">PayPal will allow you to make payments of up to &pound;500 without registering as a member and will accept credit cards issued in most countries.</p>

		<table border="0" cellpadding="10" cellspacing="0">

			<tr>

				<td width="109" align="center"><img src="images/pic-paypal-solutions.gif" border="0" height="100" width="109"></td>

				<td rowspan="2" align="left" valign="top" bgcolor="#d6e1e9" style="border:1px solid #527996;">

					<p class="mt0">Reference:<br/><input name="item_name" value="ChristianFlatshare.org donation" size="45" maxlength="127" type="text"></p>

					<p class="mb0">Amount:</p>

					<table cellpadding="0" cellspacing="0">

						<tr>

							<td>£&nbsp;</td>

							<td><input name="amount" size="8" maxlength="8" type="text"></td>

							<td style="padding-left:4px;"><input name="submit" value="Pay now with PayPal" type="submit" /></td></tr>

					</table>

					<p class="mb0"><strong style="color:#527996;">You will be redirected to a secure PayPal payment page where your card details will be required provided.</strong></p>					

				</td>

			</tr>

			<tr>

				<td align="center">No registration required for payments up to &pound;500</td>

			</tr>

		</table>

		</div>-->

		<p class="mb0"><a href="#" onclick="history.go(-1);">Return to the previous page</a></p>

		<input name="cmd" value="_xclick" type="hidden">

		<input name="business" value="donations@christianflatshare.org" type="hidden">

		<input name="no_note" value="1" type="hidden">

		<input name="currency_code" value="GBP" type="hidden">

		<input name="lc" value="GB" type="hidden">

		</form>

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

