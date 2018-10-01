<?php
session_start();

// Autoloader
require_once 'web/global.php';

connectToDB();

  $debug = NULL;
	
	// If advertiser: redirect
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
	// Set the flag for the welcome message to TRUE
 	$welcome_msg = TRUE;	
	$scam_msg = TRUE;

	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }

  // If POST update warning_ack flag 
  if ($_POST['cmd']=='login-message') {
    $query = "update cf_users set warning_ack = 1 where user_id = '".$_SESSION['u_id']."' ";
    $result = mysqli_query($GLOBALS['mysql_conn'], $query);
    $user_record = mysqli_fetch_assoc($result);
    if ($result) {
        header("Location: your-account-manage-posts.php?report=warning_ack"); exit; 
      } else {
        // Send a failure email to an administrator
        $subject = 'WANTED AD UPDATE ERROR for ad id:'.$id;
        $message = new Email(TECH_EMAIL, "problems@christianflatshare.org", $subject);
        $text  = "Update query for warning_ack with user id ".$id." failed\n\n";
        $text .= "Query text:\n\n".$query."\n\n";
        $text .= "MySQL error:\n\n".mysqli_error();
        $message->SetTextContent($text);
        $message->Send();
        header("Location: your-account-manage-posts.php?report=updateFailure"); exit; 
      }
  }

	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your ads - Manage your ads - Christian Flatshare</title>
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
  <script language="javascript" type="text/javascript" src="scripts/share.js"></script>

<!-- InstanceBeginEditable name="head" -->
	<script language="javascript" type="text/javascript" src="includes/moodalbox/moodalbox.js"></script>
	<script language="javascript" type="text/javascript">

		function hideMessage() {
		
			$('new_ad').style.display = "none";
			$('new_ad_close_button').style.display = "none"; 
		
		}

		function toggleticks(e) {
		if(e.checked) { e.parentElement.parentElement.setAttribute('style', 'width:540px; padding-right:10px; padding:3px; border: 0px;'); }
		else { e.parentElement.parentElement.setAttribute('style','width:540px; padding-right:10px; padding:3px; border:0px; background-color: #ff9999;'); }
		if(document.getElementById('scamtick1').checked && document.getElementById('scamtick2').checked && document.getElementById('scamtick3').checked) { document.getElementById('checkallwarn').style.display = 'none'; }
		}

		function checkticks() {
		if(!(document.getElementById('scamtick1').checked) || !(document.getElementById('scamtick2').checked) || !(document.getElementById('scamtick3').checked))
			{
			document.getElementById('checkallwarn').style.display = 'block';
			return false;
			}
		else document.forms['scamform'].submit();
		}
		
	</script>
	<link href="includes/moodalbox/moodalbox.css" rel="stylesheet" type="text/css" />
<style type="text/css">
label {
    display: block;
    padding-left: 15px;
    text-indent: -15px;
}
input {
    width: 13px;
    height: 13px;
    padding: 0;
    margin:0;
    vertical-align: bottom;
    position: relative;
    top: -1px;
    *overflow: hidden;
}
</style>
	<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>


<body>
    <!-- FACEBOOK JS SDK -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=241207662692677";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
    <div style="  line-height:30px; height:270px; width:100px; float:left; padding:5px;"></div>
   <div class="cl" id="cl">


	 	<h1 class="mt0"><!----></h1> 
			
			
			<?php	
			$query = "select warning_ack from cf_users where user_id = '".$_SESSION['u_id']."' ";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);		
			$user_record = mysqli_fetch_assoc($result);	
			if($user_record['warning_ack'] != 1)
				{
				// Show scam warning
				if ($scam_msg) { ?>
					<div class="mt10 mb0" style="background-color:#FFFFCC;padding-left:17px;padding-right:15px;padding-top:15px;padding-bottom:0px;border:1px solid #FFCC00;width:595px;">
						<h1 class="mt0 mb0">Notice about scams</h1>
						<p class="mt10 mb20">Christian Flatshare, as with all accommodation websites, is targeted on occasion by people trying to scam others. We<br /> would like all members to be properly informed about this and ask that you read and acknowledge these statements:</p>

						<form id="scamform" method="post" action="<?php print $_SERVER['PHP_SELF']?>" onsubmit="event.preventDefault(); checkticks();">
     			<table>
  					<tr><td style="padding-left:15px;padding-top:0px;padding-bottom:0px;">
							<div align="right" id="scamtick1div" style="width:540px; padding-right:10px; padding:3px; border:0px; background-color: #ff9999;"><label for="scamtick1">I have read the below, "About scam adverts" - <input type="checkbox" id="scamtick1" onClick="toggleticks(this);"></label></div>
					</td></tr>
					 <tr><td style="padding-left:15px;padding-top:0px;padding-bottom:0px;">
							<div align="right" id="scamtick2div" style="width:540px; padding-right:10px; padding:3px; border:0px; background-color: #ff9999;"><label for="scamtick2">I understand the risks in paying a <a href="https://www.gov.uk/tenancy-deposit-protection/overview" target="_blank">deposit</a> for a property I have not seen and without a contract - <input type="checkbox" id="scamtick2" onClick="toggleticks(this);"></label></div>
					</td></tr>
					<tr><td style="padding-left:15px;padding-top:0px;padding-bottom:0px;">
				  	<div align="right" id="scamtick3div" style="width:540px; padding-right:10px; padding:3px; border:0px; background-color: #ff9999;"><label for="scamtick3">If offering accommodation I will not ask for a <a href="https://www.gov.uk/tenancy-deposit-protection/overview" target="_blank">deposit</a> until the property has been seen/contracts signed - <input type="checkbox" id="scamtick3" onClick="toggleticks(this);"></label></div>
					</td></tr>
					<tr><td style="padding-left:30px;padding-bottom:0px;">
							<div id="checkallwarn" style="display: none;"><p class="mt15 mb0"><font color="red">Please tick all the boxes before proceeding</font></p></div>
  					  <input type="hidden" name="cmd" value="login-message">
							<p align="right" style="padding-left:0px;padding-top:0px;padding-bottom:7px;"><input style="width: 90px; height: 20px; padding: 0; margin:0; vertical-align:bottom; position: relative; top: -1px; *overflow: hidden;" align="right" type="submit" value="Proceed"></p>
  				</tr></td></table>
						</form>
       </div>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td style="padding-left:0px;padding-bottom:15px;padding-top:10px">
 				<p class="mt20 mb10" style="font-size: 14px;font-weight: bold;padding-left:0px;">About scam adverts...</p>
          <p class="mt10 mb10">Scam adverts or scam offers of accommodation are for property that may not exist and are likely to have these characteristics:</p>
        <table>
         <tr>
          <td>&nbsp;&nbsp;</td>
          <td>
          <li>The landlord will be <b>unable to show you the flat</b> (away from town with a convoluted excuse)</li>
          <li>The landlord will ask you to <b>send a handsome deposit</b> to hold the flat (which you will not have seen - bad idea)</li>
          <li>The landlord will sometimes request <b>payment by Western Union</b> (an <u>untraceable money transfer</u> service - also a bad idea)</li>
          <li>The landlord will engage you on details that would normally occur later in the dicussion (details of the flat, arrangements, solicitor details), and this is before you have even seen the flat. This is all to lure you into discussion and make you feel that it is genuine, so that you get ahead of yourself and will pay a deposit (for a flat that you have not seen, and may not exist) </li>
          <li>The landlord will avoid using the CFS messaging for correspondence, as they know this can be monitored </li>
          <li>The landlord may start asking for <b>lots of ID</b> (passport, driving license, bank statements as proof of income) - which can be used in identity theft and should only be sent to a party you trust, such as a registered letting agent</li>
          <li>The landlord will often (not always) avoid telephone contact; they are often in a different country so will sound distant, and can use an &quot;070...&quot; phone number (which is a low-cost international  internet phone number) </li></td>
         </tr>
        </table>
        <p class="mt10 mb20">Someone placing such an advert will gently pressure/tempt you to send a deposit - advertising great accommodation, in a <i>great</i> location, and saying there are many others waiting to see it - and saying they will hold it for you if you are the first to send a deposit (to the landlord you haven't met, and for the property you haven't seen - all bad). </p>
        <p class="mt10 mb20">So quite easy to spot really, and especially now that you are informed.</p>
        <p  class="mt0 mb10" style="font-size: 14px;font-weight: bold;padding-left:0px;">Good Landlords...</p>
          <p class="mt0 mb10">Conversely, you should expect of landlords (and especially within the church community), that they would:</p>
          <table  style="width:100%">
         <tr>
          <td>&nbsp;&nbsp;</td>
          <td>

          <li><b><i>Want</i> to show you the property</b>, so that you can view it and be informed before paying a deposit</li>
          <li>Use a <b>normal bank account</b>, which provides traceability, for both parties</li>
          <li>Want to <b>produce documents and agree in writing</b> details of your arrangement</li>
          <p class="mt10 mb10">As such, the process should be transparent and straight forwards. Common sense prevails.</p>
          </td>
         </tr>
        </table>
        </td>
      </tr>
      </table>

						<!-- <p align="justify" class="mb0 mt0"><strong>Please enjoy Christian Flatshare</strong></p> -->

					<?php }		
				}
			else
				{
				// Display usual page
				
				} ?>				
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
