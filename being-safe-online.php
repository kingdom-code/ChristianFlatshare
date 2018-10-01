<?php
session_start();

// Autoloader
require_once 'web/global.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Being safe online - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
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
<!-- InstanceBeginEditable name="head" -->
<style type="text/css">
<!--
.style15 {font-family: Tahoma}
.style9 {font-size: 11px;
	font-family: Tahoma;
}
-->
</style>
<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<div id="how to post an advert" class="header_no_image">
				<h1 class="mb5"><span>Being safe online </span></h1>
				<h2 class="m0"><span>some common sense internet saftey tips</span></h2>
		</div>	
		<div class="two_column_canvas">
		<div class="col1">
		<br/><p class="mt0"><span class="style15" style="margin-bottom: 0px"><span class="style9">Below are some common sense pointers to observe when interacting with others online, and will help you to spot any unusual behaviour. </span></span></p>
			<p class="mt0">Scam  attempts are commonplace in websites that allow interaction between people.  Here are some pointers which may help you detect scams: </p>
			<ol>
				<li>Always <strong>see the accommodation first</strong>, before paying a deposit or rent.</li>
				<li>Any requests for payments through untraceable money transfer services such as &quot;<strong>Western Union Money Transfer</strong>&quot; or &quot;<strong>Moneygram</strong>&quot;       should be treat<span class="style6">ed as highly suspicious.</span></li>
				<li><a href="http://www.consumerdirect.gov.uk/watch_out/scams/cheque-overpayment/" target="_blank">Overpayments scams</a>: <strong>overpayments</strong> made with a request for the 'change' (sometimes       blamed on a clerical error) should be treated suspiciously.</li>
				<li>Be wary of anyone who appears to want to remain distant from you, and does not wish to see you or the accommodation before parting with money.</li>
				<li>Do not disclose your<strong> banking information</strong> to individuals over the internet.</li>

		    </ol>

		</div>
		<div class="col2">
		<br/>
			<ol start="6">		
				<li>Do not send images or<strong> copies of identification</strong>, such as your driving       license or passport, which can be used in identity theft.</li>
				<li>Do not sign a contract or make a payment without seeing the accommodation first. Adverts for accommodation that does not exist is a common scam, and can be       used to catch those moving to the UK. Protracted or seemingly awkward       situations which make it difficult to see accommodation before paying a       deposit or rent should be treated very cautiously. Those unable to see the       accommodation might like to consider making use of a church reference and       obtain church contact details through an official church website. Those       working in a church office are likely to be only to willing to help vouch       for the existance of a person, and their accommodation situation, and likewise those offering accommodation willing to connect you with their church to make.</li>	
		</ol>
		    <p class="mt0">If you  receive a suspicious scam-type email we advise  that you do not reply to the email, as this can only encourage those who send them. Those sending scam emails are likely to have sent multiple similar emails and will not be waiting on individual replies.  Please forward unusual emails to us <a href="mailto:enquiries@ChristianFlatShare.org">enquiries@ChristianFlatShare.org</a> so that we can prevent the same email going to other members.</p>
		    <p>Read further on:<br />
		    <a href="how-to-find-accommodation.php">Looking for Accommodation?</a> <br/>			
            <a href="how-to-offer-accommodation.php">Accommodation to Offer?</a><br />
		    <a href="frequently-asked-questions.php">FAQs</a>
	      </div>
		<div class="cc0"><!----></div>
	</div>
	<p class="mb0"><a href="index.php">Return to the welcome page</a></p>
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
