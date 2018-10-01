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
<title>How to join - Christian Flatshare</title>
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
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
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
				<h1 class="mb5"><span>How to join Christian Flatshare </span></h1>
				<h2 class="mt0"><span>joining CFS posting and adverts posting tips...</span></h2>
		</div>	
		<div class="two_column_canvas">
		<div class="col1">
			<p class="mt0">Christian Flatshare (CFS) is  an accommodation notice board where members can post offered and wanted accommodation adverts, and where others can reply to those adverts.</p>
			<h2 class="mb5"><span>How to join CFS</span></h2>
			<p class="mt0"> To post an advert you must first become a member of CFS. To do that simply:</p>
			<ol>
				<li>Click on &quot;<a href="register.php">Click Christian Flatshare</a>&quot; here and complete the short membership form. You will be asked to provide an email address (which acts as your login name) and to choose a password.</li>
			    <li>CFS will send you an email with your password and membership details. You should ensure that you receive  this and that it is not sent to your JUNK mail or is stopped by a corporate  firewall. If you do not receive this email, you are not likely to receive other alerts from Christian Flatshare (such as new message alerts). <br />
			      <br />
			  You can now click &quot;Login&quot; to start using Christian Flatshare. </li>
			</ol>
			<h2 class="m0">&nbsp;</h2>
		  </div>
		<div class="col2">
			<h2 class="mb5 mt0"><span>Advert posting tips</span></h2>
			<p class="mt0"> Some advert posting tips which are well worth following... </p>
			<ol class="mb0">
				<li>A warm, <strong>helpful and informative description</strong> of the accommodation and those  living there is <em>always</em> helpful.  Adverts that have a brief description attract less response. It is  worth taking the time to thoughtfully describe your ad. When posting the ad, can  initially put a brief description and later edit it to expand it. It may be helpful to look at other adverts on CFS for inspiration before posting your own.</li>
				<li><strong>Adding <span class="obligatory">*</span>photos<span class="obligatory">*</span></strong> to your ad can help give a good impression of what the accommodation is like and to get the best ad response - and photos can be fun too!</li>
				<li>If you have a website or a social-networking page, then including a link to it will help others to find out more about you.</li>
				<li><strong>Regularly logging in</strong> <strong>to CFS </strong>will help others see you are actively using CFS as your advert(s) will show how many days since you last logged in.</li>
				<li class="mb0"><span class="style6">Your advert will automatically expire 14 days after its &quot;Available From date&quot; (Offered ads) or &quot;Wanted from date&quot; (Wanted ads). You can extend the time it appears on CFS simply by</span> moving the Available or Wanted from date forwards (see &quot;Your ads&quot; for more details on this).	          To remove your advert click on &quot;Yours ads&quot; (top right), and click on &quot;Delete this ad&quot;.</li>
		    </ol>
<br />
<br />
		    <p>Read further on:<br />
		    <a href="how-to-find-accommodation.php">Looking for Accommodation?</a> <br/>			
            <a href="how-to-offer-accommodation.php">Accommodation to Offer?</a><br />
		    <a href="frequently-asked-questions.php">Frequently Asked Questions</a><br />
		    <a href="being-safe-online.php">Being Safe Online</a><br/>			
      
          
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
