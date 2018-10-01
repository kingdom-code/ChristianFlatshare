<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- TemplateBeginEditable name="doctitle" -->
<title>Christian Flatshare</title>
<!-- TemplateEndEditable -->
<link href="../styles/web.css" rel="stylesheet" type="text/css" />
<link href="../styles/headers.css" rel="stylesheet" type="text/css" />
<link href="../favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<script language="javascript" type="text/javascript" src="../includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="../includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="../includes/icons.js"></script>
<!-- TemplateBeginEditable name="head" --><!-- TemplateEndEditable -->
<!-- TemplateParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
	<div id="header"><!----></div>
	<div id="content">
		<div id="logoContainer">
			<div id="logo"><a href="../index.php"><img src="../images/logo.gif" alt="Christian Flatshare logo (click to return to home page)" width="462" height="71" border="0" /></a></div>
			<div id="iconCanvas">			
				<a href="../index.php">
					<img src="../images/icon-1-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="../images/icon-1.gif" width="80" height="60" border="0" />
					<div class="iconText">Home page</div>
				</a>
				<a href="../countries.php">
					<img src="../images/icon-2-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="../images/icon-2.gif" width="80" height="60" border="0" />
					<div class="iconText">Countries</div>
				</a>			
				<a href="../contact-us.php">
					<img src="../images/icon-3-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="../images/icon-3.gif" width="80" height="60" border="0" />
					<div class="iconText">Contact us</div>
				</a>
			<?php if (!$_SESSION['u_id']) { ?>
				<a href="../login.php">
					<img src="../images/icon-4-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="../images/icon-4.gif" width="80" height="60" border="0" />
					<div class="iconText">Register / Login</div>
				</a>
			<?php } else { ?>
				<a href="../your-account-manage-posts.php"> 
					<img src="../images/icon-my-ads-over.gif" width="80" height="60" border="0" class="iconOver" />
					<img src="../images/icon-my-ads.gif" width="80" height="60" border="0" />
					<div class="iconText">Your ads</div>
				</a>
			<?php } ?>
			</div>									
		</div>
		<a name="m"></a>		
		<div class="redMenu">
			<ul>
				<li><a href="../about-us.php">about Christian Flatshare</a></li>
				<li><a href="../what-is-a-christian.php">what is a Christian?</a></li>
				<li><a href="../stories.php">CFS Stories</a></li>
				<li><a href="../use-cfs-in-your-church.php">use CFS in YOUR church</a></li>
				<li><a href="../churches-using-cfs.php?area=Greater London#directory">churches using CFS</a></li>
				<li class="noSeparator"><a href="../frequently-asked-questions.php">Frequently Asked Questions</a></li>
			</ul>
		</div>
		<div class="@@(_document['mainContentClass'])@@" id="mainContent">
		<!-- TemplateBeginEditable name="mainContent" -->
			<h1 class="mb0">Welcome to ChristianFlaShare.org!</h1>
			<p class="m0">Enter some new text here...</p>
		<!-- TemplateEndEditable -->
		</div>
		<div class="redMenu">
			<ul>
				<!--<li><a href="../flat-finding-tips.php">flat finding tips</a></li>-->
				<li><a href="../advertising.php">advertising</a></li>			
				<li><a href="../where-does-all-the-money-go.php">where does the money go?</a></li>
				<li><a href="../glossary.php">glossary</a></li>
				<li><a href="../terms-and-conditions.php">terms &amp; conditions</a></li>
				<li><a href="../privacy-policy.php">privacy policy</a></li>
				<li><a href="../contact-us.php">contact us</a></li>
				<li class="noSeparator"><a href="../resources.php">links &amp; resources</a></li>
			</ul>
		</div>
		<div id="footerText">
			<p class="m0"><strong>Christian Flatshare... helping accommodation seekers connect with the local church community<br />
			Finding homes, growing churches and building communities </strong>&copy; ChristianFlatShare.org 2007-<?php print date("Y")?></p>
	  </div>
	</div>
	<div id="footer"><img src="../images/spacer.gif" alt="*" width="1" height="12"/></div>
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
<?php print getTrackingCode();?>
</body>
</html>
