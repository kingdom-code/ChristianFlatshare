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
<title>CFS and Facebook - Christian Flatshare</title>
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
			<h1>Enhance with Facebook</h1>
			<h2>Share your ad and see mutual Facebook friends..!</h2>
		</div>


		<div class="two_column_canvas">
		  <div class="col1"><br />
 <h2 class="mb5"  valign="top">See mutual friends</h2>
CFS allows you to link your Facebook account with your CFS account. This will allow anyone that has also linked their CFS account with Facebook to see any friends that you have in common. <br /><br /
		    </p>

                  </div>

                  <div class="col2">
<br /><h2 class="mb5"  valign="top">Privacy</h2>
                  
Linking your account with Facebook respects the same privacy that you would enjoy on Facebook. Your facebook account is not displayed, only any friends that you might have in common with other people.<br /><br /><span class="obligatory"><b>* </b></span>We DO NOT publish any stories to your news feed (except when you click "Share on Facebook" or "Recommend").<br />
                    </p>


	    	</div>
		<div class="clear"><!----></div>
                  <p class="mt0"><img src="images/FB-friends.gif" alt="Mutual friends on Facebook" width="864" height="190" /></p>
		</div>		

                 <div class="two_column_canvas">
                  <div class="col1"><br />
    <table  width="100%"><tr  valign="top"><td  valign="top" >
                      <h2 class="mb5"  valign="top">Share on Facebook</h2>
    </td><td alight="right">
 <p class="mt0" align="right"><img src="images/share-on-FB.gif" alt="Share of Facebook" width="126" height="34" /></p>
                       </td></tr></table>
<p>  Clicking "Share of Facebook" button on adverts allows you to easily share adverts with friends on Facebook. If you have uploaded photos to your advert you will be able to select on of these to show   in your Facebook link - it's going to look great!<br /><br /></p>


                  </div>
                  <div class="col2">

                   </div><br />
               <p class="mt0" align="left"><img src="images/share-FB-link.gif" alt="Share ad on Facebook" width="519" height="302" /></p>

                </div>
          <div class="clear"><!----></div>



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
