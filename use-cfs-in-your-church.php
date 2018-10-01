<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

$result             = mysqli_query($GLOBALS['mysql_conn'], "select count(church_name) from cf_church_directory;");
$stats['churches']  = cfs_mysqli_result($result,0,0);

$showForm   = TRUE;
$debug      = NULL;
$msg        = NULL;
$error      = NULL;
  
	if (isset($_POST['email'])) {
		$email = strtolower(trim($_POST['email']));
	} else if (isset($currentUser['email_address'])) {
		$email = $currentUser['email_address'];
	} else {
		$email = NULL;
	}
	if (isset($_POST['church'])) { $church = $_POST['church']; } else { $church = NULL; }
	if (isset($_POST['name'])) { $name = $_POST['name']; } else { $name = NULL; }	
	if (isset($_POST['email'])) { $email = $_POST['email']; } else { $email = NULL; }		
	if (isset($_POST['position'])) { $position = $_POST['position']; } else { $position = NULL; }			
	if (isset($_POST['url'])) { $url = $_POST['url']; } else { $url = NULL; }
	if (isset($_POST['address'])) { $address = $_POST['address']; } else { $address = NULL; }
	
	if (isset($_POST['question_id'])) { $question_id = $_POST['question_id']; 
	                                       } else { 
																		  $question_id = NULL; 
																		 }
	if (isset($_POST['answer'])) { $answer = $_POST['answer']; } else { $answer = NULL; }		
//	if (isset($_POST['directory'])) { $directory = $_POST['directory']; } else { $directory = NULL; }
	
if ($_POST) {
  	if (TrustedUser($_SESSION['u_id']) != 'trusted') {
		// CAPTCHA validation
		if (empty($answer)) {
			$error['captcha'] = '<p class="error">Please choose an answer</p>';
		} else {
			$query = "select answers from cf_captcha_questions where question_id = '".$question_id."';";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			$correctAnswers = explode(",",cfs_mysqli_result($result,0,0));
			$check = in_array($answer,$correctAnswers);
			if ($check === FALSE) {
				$error['captcha'] = '<p class="error">Your answer was incorrect, please try again.</p>';
			}		
		}
	} // if trusted		
		
		if (!$error) {
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/directory_request.html.twig', array(
                'name' => $name,
                'email' => $email,
                'church' => $church,
                'position' => $position,
                'url' => $url,
                'address' => $address,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Directory Request';
            $to = array('website@christianflatshare.org', 'ryanwdavies@gmail.com');
            
            if ($name != 'ZAP' && $church != '180' && ! stristr("$address","prada")  && ! stristr("$address","watches") && ! stristr("$address","hand"))
            {
             $msg = $CFSMailer->createMessage($subject, $body, $to);
             $sent = $CFSMailer->sendMessage($msg);
            }  
             else
            { $sent=1; } 
    		
      if ($email == 'dukang2004@yahoo.com')	{
          $showForm = FALSE; // don't show form
          $msg = '<p class="mt20 green"><strong>Thank you. We will reply shortly.</strong></p>'."\n";
      }
      else {
          if ($sent > 0) {	
              $showForm = FALSE; // don't show form
              $msg = '<p class="mt20 green"><strong>Thank you. We will reply shortly.</strong></p>'."\n";
          }
          else {
              $msg = '<p class="error">'.ORDER_OUR_STUFF_FAILURE.'</p>'."\n";
          } // end IF mail send
      } // end email = dukang2004
		
		}
	
	}

if ($showForm) {
		// Get a random captcha question
		$query = "select * from cf_captcha_questions order by rand() limit 0,1";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$question = mysqli_fetch_assoc($result);
		// Load the corrent answers of the question plus a few incorrect ones to bring the total to 9
		$correctAnswers = "";
		$temp = explode(",",$question['answers']);
		$count = 9 - count($temp);
		foreach($temp as $value) {
			$correctAnswers .= "'".$value."',";
		}
		$correctAnswers = substr($correctAnswers,0,-1);
		$query = "
			select * from (
				select * from (select * from cf_captcha_answers where answer_id not in (".$correctAnswers.") order by rand() limit 0,8) as `wrong_answers`
			union
				select * from (select * from cf_captcha_answers where answer_id in (".$correctAnswers.") order by rand() limit 0,1) as `right_answer`
			) as `answers` order by rand();	
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query) or examine($query);
		// Tabulate the answers in a three by three grid
		$counter = 0;
		$captcha = '<tr>';
		while ($row = mysqli_fetch_assoc($result)) {
			if ($counter % 3 == 0) {
				$captcha .= '</tr>';
				$captcha .= '<tr>';
			}
			$captcha .= '<td width="100px">';
			// Create the checkbox
			$captcha .= '<input type="radio" name="answer" value="'.$row['answer_id'].'" id="answer_'.$row['answer_id'].'" />';
			// Create the answer text
			$captcha .= '<label for="answer_'.$row['answer_id'].'">'.$row['text'].'</label>';
			$captcha .= '</td>';
			
			$counter++;
		}
		$captcha .= '</tr>';
				
	}	// Show form		
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Use Christian Flatshare in your church - Christian Flatshare</title>
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
			
			<div id="header_use_cfs_in_your_church" class="header">
				<h1 class="m0"><span>Use Christian Flatshare in your church</span></h1>
				<h2 class="m0"><span>if you would like to use CFS in your church or organisation then here are some things to help you...</span></h2>
			</div>				
			<div style="width:400px; float:left;">
				<p class="mt0">Sharing Christian Flatshare (CFS) with your church can help those moving to your area to connect with your church. We hope that  all Christian Flatshare members will share CFS.</p>
				<p class="mt0">You can download posters to share CFS with your church - and of course, however praiseworthy it may be, do ask permission from your vicar, pastor or priest, before starting your promotional campaign in  church.</p>
			  <br />		
				<h2 class="mb10">CFS  Posters</h2>
				
			  <table border="0" cellpadding="0" cellspacing="0" class="mb20">
				<tr>
					<td width="236"><img src="images/use_cfs_page_poster.gif"  align="left" /></td>

					<td width="166" valign="top">
					<p class="mt10 mb0">Noticeboard posters:</p>
					 <strong><a href="A4 CFS Poster.pdf" target="_blank">A4 Poster</a> </strong><span class="grey">(1.2MB PDF)</span><br />
					 <strong><a href="A5 CFS Poster.pdf" target="_blank">A5 Poster</a> </strong><span class="grey">(395KB PDB)</span>
					</td>
				</tr></table>		


				<h2 class="mb5 mt20">Linking websites to CFS</h2>
				<p class="mt0 mb20">A link from your website will help others to find CFS. If you would like to link your website to CFS then below are some graphics that you might like to use. CFS links to and includes on its accommodation maps the Churches, Christian Organisations and Student CUs in the CFS church directory. </p>
				<br />	
				<p><img src="images/linking/cfs-link-logo-stacked.gif" alt="Christian FlatShare Logo" width="235" height="78" /></p>
				<br />
				<p style="padding-left:60px"><img src="images/linking/cfs-link-nologo-stacked.gif" alt="Christian FlatShare Logo" width="173" height="77" /></p>						
	      <br />
				<p align="right" style="padding-right:10px"><img src="images/linking/cfs-link-nologo.gif" alt="Christian FlatShare Logo" width="340" height="38" /></p>
				<p class="mt20 mb10"><img src="images/linking/cfs-link-logo.gif" alt="Christian FlatShare Logo" width="391" height="56" /></p>
				
				<br />
		
				<p align="left" style="padding-left:20px"><img src="images/linking/cfs-link-icon.gif" alt="Christian FlatShare Icon" width="103" height="140" /></p>
				<p>&nbsp;</p>
		</div>
			<div id="columnSeparator" style="width:50px; height: 660px;"><!----></div>
			<div style="width:400px; float:left;">	
				<table border="0" cellpadding="0" cellspacing="0" class="mb0">
					<tr>
						<td>
				<h2 class="mb0">CFS Church Directory</h2>
				<p class="mt0">Church leaders, use the form below to add your church to          <a href="churches-using-cfs.php">CFS' church directory</a> and onto the accommodation maps, with links to your website.</p>
				<p class="mt0"><?php print $stats['churches']?> churches and organisations have supported CFS.</p></td>
						<td width="196"><img src="images/icon-order-our-stuff.gif" width="176" height="134" align="right" /></td>
					</tr>
				</table>				
				<?php print $msg?>
				<?php if ($showForm) { ?>
          
<?php
$userName = (isset($currentUser['name'])) ? $currentUser['name'] : NULL;
$userEmail = (isset($currentUser['email_address'])) ? $currentUser['email_address'] : NULL;
?>
			<br />
			<div id="contactUsForm">
					<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
				<input type="hidden" name="question_id" value="<?php print $question['question_id']?>" />					
					<table border="0" cellpadding="0" cellspacing="3" width="100%">
					<tr>
						<td width="140px" align="right">Your name:&nbsp;</td>
						<td  width="370px" align="left"><input name="name" size="25" type="text" id="name" value="<?php print $userName; ?>"/></td>
					</tr>
					<tr>
						<td align="right">Your email address:&nbsp;</td>
						<td><input name="email" type="text" size="30" id="order_our_stuff_email" value="<?php print $userEmail; ?>"/></td>
					</tr>
										
					<tr>
						<td align="right">Staff position:&nbsp;</td>
						<td><input name="position" type="text"  size="28" id="order_our_stuff_position"  value="<?php print $position?>"/></td>
					</tr>															
					<tr>
						<td align="right">Church&nbsp;or&nbsp;organisation:&nbsp;</td>
						<td><input name="church" type="text"  size="37" id="order_our_stuff_organisation"  value="<?php print $church?>"/></td>
					</tr>

					<tr>
						<td align="right">Website address:&nbsp;</td>
						<td><input name="url" size="37" type="text" id="order_our_stuff_church_name" value="<?php print $url?>"/></td>
					</tr>					
					</table>					

					<p class="mb0 mt10">&nbsp;Your postal address (and meeting place address if they are different).</p>
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
					<tr>
						<td align="left">
						<textarea name="address" rows="10" id="order_out_stuff_address" style="width:100%;padding:2px; font-size: 11px;"><?php print $address?></textarea>
						</td>
					</tr>
  		   	<?php if (TrustedUser($userId) != 'trusted') { ?>
							<tr style="padding-bottom:15px;padding-top:5px;">

								<td width="500"><strong>Human test</strong><br/>
									<p class="mt0">To help protect against automatic spam messages, we'll need to detect whether you're  a real person or not (no offence) by asking you a simple question...</p>
									<?php print $error['captcha']?>
									<p class="mb0"><strong><?php print $question['question_text']?></strong></p>
									<table border="0" cellspacing="4" cellpadding="0">
										<?php print $captcha?>
								</table>					</td>
								<td>&nbsp;</td>
							</tr>
					<?php } ?> <!-- END TrustedUser -->								
 				  <tr>
						<td align="right"><input type="submit" name="Submit" value="Send Directory Entry" /></td>
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
