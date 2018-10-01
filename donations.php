<?php

session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); }
	if (isset($_REQUEST['action'])) { $action = $_REQUEST['action']; } else { $action = NULL; }	
	if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; } else { $id = NULL; }
	if (isset($_REQUEST['post_type'])) { $post_type = $_REQUEST['post_type']; } else { $post_type = NULL; }
	
	if (notifyUser($id,$post_type, $twig)) {
		$result = '<p class="success">An email was sent for verification</p>';
	}
	
	
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
<div style="width:400px; float:left;">
	<h2 class="success mt0"><?php print ucwords($post_type)?> ad posted successfully!</h2>
	<p>Thank you for using Christian Flatshare</p>
	<p>Your offered accommodation advert has been added to the Christian Flatshare database.</p>
	<?php if (!$result) { ?>
	<p>It is currently in an <strong>unpublished</strong> state pending approval by an administrator. Adverts are usually approved in less than two hours (8am-10pm UK time).</p>
	<p><strong>Your will receive an email when your ad has been approved and is published, visible to all visitors of Christian Flatshare.</strong></p>
	<?php } else { ?>
	<?php print $result?>
	<?php } ?>
	<p>From here on you can:</p>
	<ol>
		<li><a href="details.php?post_type=<?php print $post_type?>&amp;id=<?php print $id?>">View your ad</a></li>
		<li><a href="your-account-upload-photos.php?post_type=<?php print $post_type?>&id=<?php print $id?>">Upload photographs for your ad</a></li>
		<li><a href="your-account-edit-<?php print $post_type?>.php?id=<?php print $id?>">Edit your ad (incl. Flatmatch &amp; Pal-Up preferences)</a></li>
		<li><a href="your-account-manage-posts.php">Go to your &quot;My ads&quot; page</a></li>
	</ol>
	</div>
<div class="cs" style="width:50px; height: 440px;"><!----></div>
<div style="width:400px; float:left;">
	<div id="donateText">
	<h2>Christian Flatshare is currently is free to use, and we like that very much.</h2>
	<p><u>If our user community  supports us</u> in mid-2007 we will introduce a small charge for posting  offered accommodation adverts; all other website features will remain free  - posting wanted adverts, automatic email alerts (Flat-Match and Pal-Up) - and all  advert searching.</p>
	<p>At the same time, Flatshare and Family Share ads will always be free for those who are unable to pay or, for whatever reason, prefer not to. </p>
	<p> The charges (&pound;2 for Flatshare and  Family Share adverts, and &pound;12 for Whole Place adverts) are to bring two  benefits:</p>
	<ul>
		<li class="mb10">To help maintain the quality/accuracy  of the adverts , as it requires a small commitment on the part of the  advertiser</li>
		<li> The monies raised will  contribute to the costs of our ministry (which are kept to a minimum) &ndash; any and all monies raised beyond our  costs will go to charity (see &ldquo;<a href="where-does-all-the-money-go.php">Where does all the money go?</a>&rdquo;)</li>
		</ul>
	<p>If you have enjoyed using CFS and wish to support our ministry now by making a  donation, <a href="donations-paypal.php">please visit this link</a>.  <br /> <br />  Yours,</p>
	<p>The Christian Flatshare Team.</p>
	</div>
</div>
<div class="cc0"><!----></div>
<!--<h2>Donate to a charity</h2>
<p>Please select one of the charities from the list below.</p>
<form name="donate" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
<input type="hidden" name="action" value="pay" />
<input type="hidden" name="id" value="<?php print $id?>" />
<input type="hidden" name="post_type" value="<?php print $post_type?>" />
<table width="500" border="0" cellpadding="0" cellspacing="10">
	<tr>
		<td width="20" valign="top"><input name="charity" type="radio" value="1" /></td>
		<td><strong>Charity 1 <br /></strong>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Fusce tellus dolor, ullamcorper vitae, dictum id, adipiscing sed, augue. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.<br /> <a href="#">Link to website</a> </td>
	</tr>
	<tr>
		<td valign="top"><input name="charity" type="radio" value="2" /></td>
		<td><strong>Charity 2 <br /></strong>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Fusce tellus dolor, ullamcorper vitae, dictum id, adipiscing sed, augue.<br /><a href="#">Link to website </a> </td>
	</tr>
	<tr>
		<td valign="top"><input name="charity" type="radio" value="3" /></td>
		<td><strong>Charity 3 <br /></strong>Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.<br /><a href="#">Link to website</a> </td>
	</tr>
	<tr>
		<td valign="top">&nbsp;</td>
		<td>
			<input type="button" name="pay" id="pay" value="Proceed to payment" disabled="disabled" />
			<input type="submit" name="simulatePay" id="simulatePay" value="Simulate payment" />
		</td>
	</tr>
</table>
</form>-->
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
