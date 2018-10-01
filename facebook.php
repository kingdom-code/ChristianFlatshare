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
<title>Weclome from Facebook - Christian Flatshare</title>
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
		<div id="header_facebook_your_ad" class="header">
			<h1>Weclome from Facebook</h1>
			<h2>Christian Flatshare Facebook group and fan page </h2>
		</div>
		<div class="two_column_canvas">
		  <div class="col1">
				  <p class="mt0">Welcome from Christian Flatshare!. </p>
				  <p>CFS  allows you to easily add a link to your advert on your Facebook profile mini-feed, which  will help to share  your ad with all of your Facebook friends. Your friends can pass the advert on to others too by  clicking &ldquo;Share+&rdquo;.</p>
				  <p>Once you have created an ad, to  add it to your Facebook mini-feed:</p>
				  <ol start="1" type="1">
					<li>Login to CFS and click on &ldquo;<strong>Your       ads</strong>&rdquo; (top right)</li>
					<li>Click on &ldquo;<strong>Facebook your ad</strong>&rdquo;       and you&rsquo;re done!</li>
				  </ol>
				  <p>If you have uploaded photos for your advert you will be able to select on of these to show in your Facebook link. </p>
				  <p>Your ad may soon move down  and out of sight on your mini-feed. To reposition it at the top either click on &quot;Facebook your ad&quot; again, or click on &quot;<strong>Share+</strong>&quot; and &quot;Post to Profile&quot; on the existing mini-feed link.</p>
				  

			  <h2 class="m0"><span>Christian Flatshare Facebook Groups</span></h2>
				  <p class="mt0">Christian Flatshare has a <a href="http://www.facebook.com/group.php?gid=7329528471&amp;ref=nf" target="_blank">Facebook group</a> and a <a href="http://www.facebook.com/profile.php?id=7482755783" target="_blank">Facebook Fan Page</a>. You may like to join the Facebook group and the fan page, which helps to share Christian Flatshare within the Facebook community and can be fun! </p>
				  <p>&nbsp;</p>
		  </div>
			<div class="col2">
				<p class="mt0"><img src="images/facebook_group.gif" alt="Facebook your CFS ads" width="373" height="434" /></p>
			</div>
			<div class="clear"><!----></div>
		</div>		
        <p><br />
        <a href="#" onclick="history.go(-1);">Return to the previous page</a>        </p>
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
