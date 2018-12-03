<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

	$pageTitle = "Contact us";
	$showForm = TRUE;
	
	if (isset($_POST['feedback'])) { $feedback = $_POST['feedback']; } else { $feedback = NULL; }
	if (isset($_POST['name'])) { $name = $_POST['name']; } else { $name = NULL; }
	if (isset($_POST['email'])) { $email = $_POST['email']; } else { $email = NULL; }	
	if (isset($_POST['number'])) { $number = $_POST['number']; } else { $number = NULL; }
	if (isset($_POST['church'])) { $church = $_POST['church']; } else { $church = NULL; }			
	
    if ($email == NULL && isset($_SESSION['u_email'])) {
        $email = $_SESSION['u_email'];
    }
    
    if ($name == NULL && isset($_SESSION['u_name'])) {
        $name = $_SESSION['u_name'];
    }

	if (isset($_POST['question_id'])) { $question_id = $_POST['question_id']; 
	                                       } else { 
																		  $question_id = NULL; 
																		 }
	if (isset($_POST['answer'])) { $answer = $_POST['answer']; } else { $answer = NULL; }	
	
	
	
	
	if ($_POST) {
		if (TrustedUser($_SESSION['u_id']) == 'trusted') {
			// no CAPTCHA validation 
		} else {
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
		
		
	
		// Validate name & email
		if (!preg_match(REGEXP_EMAIL,$email)) {
			$error['email'] = '<span class="error">Please enter a valid email address:</span><br/>';
		}
   	if ($name == "") {
			$error['name'] = '<span class="error">Please enter your name:</span><br/>';
		}		
		
		if ($error) {
			// Do not proceed
		}
        else {
            // Send Email
            $CFSMailer = new CFSMailer();
            
            // Get Body
            $body = $twig->render('emails/contact.html.twig', array(
                'name' => $name,
                'email' => $email,
                'telephone_number' => $number,
                'church' => $church,
                'message' => $feedback,
            ));
            
            // Set variables
            $subject = 'Christian Flatshare - Website contact';
            $to = array('website@christianflatshare.org', 'ryanwdavies@gmail.com');

      	    if ($church != '180' && ! stristr("$feedback","prada")  && ! stristr("$feedback","watches") && ! stristr("$feedback","hand"))
             {
             $msg = $CFSMailer->createMessage($subject, $body, $to, NULL, $email);
             $sent = $CFSMailer->sendMessage($msg);
             }
            //else
             { $sent = 1; } 

            if ($email == 'dukang2004@yahoo.com') {
                $showForm = FALSE; // don't show form
                header("Location:frequently-asked-questions.php?sent=true"); 
                exit;
            }
            else {
                if ($sent > 0) {
                    $showForm = FALSE; // don't show form
                    header("Location:frequently-asked-questions.php?sent=true"); 
                    exit;
				}
                else {
                    $msg = '<p class="error">'.CONTACT_US_FAILURE.'</p>'."\n";
                }
            }
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



<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript" src="https://blockchain.info//Resources/wallet/pay-now-button.js"></script>
 <style>
 ccadd {
   font-family: Lucida Console, Monaco, monospace;
   font-size:11px;
   color:#878484;
  }
 ccname {
   font-family: Lucida Console, Monaco, monospace;
  }
 </style>

</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			
			<div id="header_contact_us" class="header">
				<h1>Contact us</h1>
				<h2>if you'd like to drop us a line about CFS...</h2>
			</div>	
			
		<div style="width:450px; float:left;">
				<p class="mt0 mb5">...and even if it&rsquo;s just to find out what we&rsquo;d all like for Christmas, we&rsquo;d love to hear from you.</p>


                                <div class="mt20" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:440px;">
				<b>Note:</b><br />&nbsp;- If you are looking for accommodation place a Wanted accommodation advert.<br />&nbsp;- If you have accommodation to offer place an Offered accommodation advert.<br />
				<p class="mt5 mb10">Please see <a href="frequently-asked-questions.php">Frequently Asked Questions</a> for details on how to do that.</p>				
</div>

			  <?php if ($showForm) { ?>
				
				<br />				
				<div id="contactUsForm">
					<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
					<input type="hidden" name="question_id" value="<?php print $question['question_id']?>" />					
					<table border="0" cellpadding="0" cellspacing="2">
					<tr style="mt20">
						<td width="120" align="right" valign="middle"><span class="obligatory">*</span> Your name:&nbsp;</td>
						<td width="240"><?php print $error['name']?><input name="name" type="text" id="name" width="200" value="<?php print $_SESSION['u_name']?>" style="padding:2px; font-size: 12px;"/></td>
					</tr>
					<tr>
						<td align="right" valign="middle"><span class="obligatory">*</span> Your email address:&nbsp;</td>
						<td><?php print $error['email']?><input name="email" type="text" id="email" value="<?php print $_SESSION['u_email']?>" style="padding:2px; font-size: 12px;"/></td>
					</tr>
					<tr>
						<td align="right" valign="middle">Telephone number:&nbsp;</td>
						<td><input name="number" type="text" id="number"  value="<?php print $number?>" style="padding:2px; font-size: 12px;"/> <span class="grey">(optional)</span></td>
					</tr>					
					<tr>
						<td align="right" valign="middle">Church or organisation:&nbsp;</td>
						<td><input name="church" type="text" id="church"  value="<?php print $church?>" style="padding:2px; font-size: 12px;"/></td>
					</tr>
					</table>					


					<p class="mt10 mb0">&nbsp;&nbsp;Your message to us...</p>
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
					<tr>
						<td align="right">
						<textarea name="feedback" rows="12" id="feedback" style="width:100%;padding:2px; font-size: 12px;"><?php print $feedback?></textarea>
						</td>
					</tr>
				<?php if (TrustedUser($_SESSION['u_id']) != 'trusted') { ?>
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
						<td align="right">
						<input type="submit" name="Submit" value="Send your message" />
						</td>
					</tr>
					</table>
					</form>
				</div>
				<?php } else { ?>		
				<p class="mt5 mb0">		
					<table cellpadding="5" cellspacing="0" width="100%">
					<tr align="left">
					<td style="padding-top:00px;padding-left:0px;padding-right:0px;padding-bottom:10px;" align="left">
					 <div class="mt10" style="width:420px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
					 
					<p class="mt0 obligatory" align="center"><strong>If you are looking for accommodation, or have accommodation to offer, please see <a href="frequently-asked-questions.php">Frequently Asked Questions</a> for details on placing adverts.</strong></p>
					
					<p class="success mt5 mb0">Message was sent:</p>
					<p class="grey mt5 mb0"><?php print  nl2br(makeClickableLinks(trim($feedback))) ?></p>

					</div>
					</td>
					</tr>
					</table>
					</p>
				<?php } ?>
				
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
				</table>>
				-->
		<!--	<p class="mt0 mb15" style="font-size:12px;"><strong>020 7183 2949<span class="f10 f12"> (9am-8pm, Mon-Sat)</span></strong></p> -->

		</div>
			<div id="columnSeparator" style="width:50px; height:500px;"><!----></div>
			<div style="width:350px; float:left;">
			  	<p class="mb5 mt0" style="font-size:15px; font-weight:bold; color:#990000; line-height:normal;"> CFS Feedback</p>
		  	    <p class="mt0" align="justify">Your candid feedback about Christian Flatshare is welcomed. Your feedback will help to improve and refine Christian Flatshare.</p>
		  	    <p class="mt0" align="justify">If you have notice inaccuracies within certain adverts, please reply directly to the advert's owner so the owner can correct them. </p>
<br />  <p>Nice letters, cakes, and all those Christmas presents can be posted to us directly at the CFS main global HQ:</p>
                        <p class="mb5" style="font-size:12px;"><strong>Christian Flatshare<br />
                        18 Crefeld Close <br />
                        London W6 8EL </strong></p>


 <p class="mb0" style="font-size:12px;"><br /><strong>Please support CFS</p>

                        <p class="mt5 mb15" align="left">Your contribution will help grow Christian Flatshare.</p>
                        <p class="mt0 mb0">
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="5647421">
<input type="image" align="left" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The      safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="30">
</form>

<p>
 <ccadd>
BTC: 1EiZBwPjBuJewuiWR9ohuPZ6vKvEHCZySp<br/>
LTC: LR7jzLf18YFHFLE8JAmnxN6nJod9V9KKrr<br/>
BCH: 1NWuebqz869eyj4VWC5QL3h6JrEAKBDYQa<br/>
ETH: 
0x6807a2b1a3a30a045acfbfd628777a9479fc29e6
 </ccadd>
</p>
<p class="mb0">
 <br/>
 <strong><span style="font-size:12px;">CFS on Github: community development</span></strong><br/><br/>
 CFS will be added to GitHub summer 2018 to allow community development. <a href="https://github.com/ChristianFlatshare" target="_blank">https://github.com/ChristianFlatshare</a>
</p>


<!-- 

<p class="mb5" style="font-size:12px;"><br /><strong>Bitcoin Donations (BTC)</p>
<p class="mt0 mb20" align="justify">To donate to Christian Flatshare using Bitcoin please click on the icon below to show you an address for your donation.</p>
<div style="font-size:16px;margin:0 auto;width:250px" class="blockchain-btn"
     data-address="1DtZpWPMaZwBjcwGtT9fvFmaZB9xcaE4B5"
     data-shared="false">
    <div class="blockchain stage-begin">
        <img src="https://blockchain.info//Resources/buttons/donate_64.png"/>
    </div>
    <div class="blockchain stage-loading" style="text-align:center">
        <img src="https://blockchain.info//Resources/loading-large.gif"/>
    </div>
    <div class="blockchain stage-ready">
         <p align="center">To Donate To Chrisitan Flatshare </br>please use Bitcoin Address: <b>[[address]]</b></p>
         <p align="center" class="qr-code"></p>
    </div>
    <div class="blockchain stage-paid">
         </br>Donation of <b>[[value]] BTC</b> Received. Thank You.
    </div>
    <div class="blockchain stage-error">
        <font color="red">[[error]]</font>
    </div>
</div>

   --> 

	
			</div>
			<br />
			<blockquote>&nbsp;</blockquote>
			<div class="cc0"><!----></div><br />
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
