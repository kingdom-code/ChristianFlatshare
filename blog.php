<?php
    session_start();

    // Autoloader
    require_once 'web/global.php';

    require('includes/class.phpmailer.php');		// Email class

    connectToDB();

	$pageTitle = "Contact us";
	$showForm = TRUE;
	
	if (isset($_POST['email'])) {
		$email = strtolower(trim($_POST['email']));
	} else if (isset($_SESSION['u_email'])) {
		$email = $_SESSION['u_email'];
	} else {
		$email = NULL;
	}
	if (isset($_POST['feedback'])) { $feedback = $_POST['feedback']; } else { $feedback = NULL; }
	
	if ($_POST) {
	
		// Validate name & email
		if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {
			$error['email'] = '<span class="error">Please enter a valid email address:</span><br/>';
		}
		
		if ($error) {
		
			// Do not proceed
			
		} else {
		
			$email = "name = ".$name." email = ".$email;
			// Email ad owner
			$mail    = new PHPMailer();
			$mail->From     = ADMIN_FROM;
			$mail->FromName = ADMIN_FROM;
			$mail->Subject  = "Blog news feedback ".$email;
			$mail->MsgHTML(sprintf(CONTACT_US_BODY,$email,$area,$feedback));
			$mail->AddAddress("website@christianflatshare.org", "website@christianflatshare.org");			
		    $mail->AddBCC("ryanwdavies@hotmail.com", "ryanwdavies@hotmail.com");			
			if ($mail->Send()) {	
				$showForm = FALSE; // don't show form
				$msg = '<p class="success">Thank you for your message.<br />If your message requires an answer we will reply shortly.</p><br />'."\n";
			} else {
				$msg = '<p class="error">'.CONTACT_US_FAILURE.'</p>'."\n";
			}
			$pageTitle = "Contact us thanks";
		
		}
	
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php print $pageTitle?> - Christian Flatshare</title>
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
<script language="javascript" type="text/javascript">
	function fieldFocus(id) {
		if ($(id).value == "e.g. Cambridge, CB5") {
			$(id).value = "";
		}
	}
	
	function fieldBlur(id) {
		$(id).value = $(id).value.trim();
		if ($(id).value == "") {
			$(id).value = "e.g. Cambridge, CB5";
		}
	}
		
</script>			

			<div id="header_blog" class="header">
				<h1>CFS Stories </h1>
				<h2>CFS news, stories, testimonies and some of the lovely things you've said...</h2>
			</div>	
			
		<div style="width:450px; float:left;"> 
				<p class="mt0">...and even if it&rsquo;s just to find out what we&rsquo;d all like for Christmas, we&rsquo;d love to hear from you:</p>
	
		
				
				<!--
				<table width="100%" border="0" cellpadding="4" cellspacing="0" class="greyTable">
					<tr class="trOdd">
						<th>Purpose of email </th>
						<th>Address to use </th>
					</tr>
					<tr class="trOdd">
						<td>General Enquiries:</td>
						<td><img src="images/email_enquiries.gif" width="161" height="11" /></td>
					</tr>
					<tr class="trEven">
						<td>Promotional Material Enquiries:</td>
						<td><img src="images/email_promotion.gif" width="166" height="11" /></td>
					</tr>
					<tr class="trOdd">
						<td>Merchandise  Order Fulfilment Enquiries:</td>
						<td><img src="images/email_wheresmystuff.gif" width="190" height="11" /></td>
					</tr>
					<tr class="trEven">
						<td>CFS Directory Entries: </td>
						<td><img src="images/email_directory.gif" width="161" height="11" /></td>
					</tr>
					<tr class="trOdd">
						<td>Technical Issues: </td>
						<td><img src="images/email_problem.gif" width="161" height="11" /></td>
					</tr>
				</table>
				-->
				<p>Nice letters, cakes, and all those Christmas presents can be posted to us directly at the CFS main global HQ:</p>
			<p class="mb5" style="font-size:12px;"><strong>Christian Flatshare<br />PO BOX 47182<br />London W6 6PR </strong></p><br />
		<!--	<p class="mt0 mb15" style="font-size:12px;"><strong>020 7183 2949<span class="f10 f12"> (9am-8pm, Mon-Sat)</span></strong></p> -->

		</div>
			<div id="columnSeparator" style="width:50px; height:500px;"><!----></div>
			<div style="width:350px; float:left;">
	      <p class="mt0">If you have some feedback about Christian Flatshare (good or bad) or a story that you would like to share with others, please let us know using the form below.</p>
	      <p class="mt0 mb20">Share your stories to encourage others! </p>
	      <p>
				  <?php print $msg?>
				</p>					
			  <?php if ($showForm) { ?>
				<div id="contactUsForm">
					<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
					<tr>
						<td align="left"><span class="obligatory">*</span> Your name:</td>
						<td><input name="name" type="text" id="name" width="180" value="<?php print $_SESSION['u_name']?>"/></td>
					</tr>

					<tr>
						<td align="left"><span class="obligatory">*</span> Your area:</td>
						<td valign="top"><input name="area" width="140" type="text" id="area" value="e.g. Cambridge, CB5" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);"/></td>
					</tr>					
					
					<tr>
						<td align="left"><span class="obligatory">*</span> Your email address:<br /><span class="grey">(which isn't published)</span></td>
						<td valign="top"><input name="email" width="140" type="text" id="email" value="<?php print $_SESSION['u_email']?>"/></td>
					</tr>
					</table>					


					<p class="mt10 mb0">&nbsp;&nbsp;Your message to us...</p>
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
					<tr>
						<td align="right">
						<textarea name="feedback" rows="12" id="feedback" style="width:100%;padding:2px; font-size: 12px;"><?php print $feedback?></textarea>
						(please include your phone number if you would like to be called back)
						</td>
					</tr>
					<tr>
						<td align="right">
						<input type="submit" name="Submit" value="Send your message" />
						</td>
					</tr>
					</table>
					</form>
				</div>						 
 				<?php } ?>		
						 
						 
			</div>
			<div class="cc0"><!----></div>
		<p><a href="#" onclick="history.go(-1);">Return to the previous page</a>        </p>
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
