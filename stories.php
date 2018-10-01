<?php

use CFS\Mailer\CFSMailer;

// Autoloader
require_once 'web/global.php';

connectToDB();

$pageTitle      = "CFS Stories";
$showForm       = TRUE;
$msg            = NULL;
$error          = NULL;

$feedback       = (isset($_POST['feedback'])) ? $_POST['feedback'] : NULL ;
$name           = (isset($currentUser['name'])) ? $currentUser['name'] : NULL ;
$name           = (isset($_POST['name'])) ? $_POST['name'] : NULL ;
$email          = (isset($currentUser['email_address'])) ? $currentUser['email_address'] : NULL ;
$email          = (isset($_POST['email'])) ? $_POST['email'] : NULL ;
$area           = (isset($_POST['area'])) ? $_POST['area'] : NULL;
$question_id    = (isset($_POST['question_id'])) ? $_POST['question_id'] : NULL;
$answer         = (isset($_POST['answer'])) ? $_POST['answer'] : NULL;

if (!empty($_POST)) {
    // Validate name & email
    if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,4})$/',$email)) {
        $error['email'] = '<span class="error">Please enter a valid email address:</span><br/>';
    }
    
   	if ($name === NULL) {
        $error['name'] = '<span class="error">Please enter your name:</span><br/>';
    }
    
    if (TrustedUser($_SESSION['u_id']) != 'trusted') {
        // CAPTCHA validation
        if (empty($answer)) {
            $error['captcha'] = '<p class="error">Please choose an answer</p>';
        }
        else {
            $query = "select answers from cf_captcha_questions where question_id = '".$question_id."';";
            $result = mysqli_query($GLOBALS['mysql_conn'], $query);
            $correctAnswers = explode(",",cfs_mysqli_result($result,0,0));
            $check = in_array($answer,$correctAnswers);
            if ($check === FALSE) {
                $error['captcha'] = '<p class="error">Your answer was incorrect, please try again.</p>';
            }		
        }
    }
	
  	if (empty($error)) {
        // Send Email
        $CFSMailer = new CFSMailer();
        
        // Get Body
        $body = $twig->render('emails/story.html.twig', array(
            'name' => $name,
            'email' => $email,
            'area' => $area,
            'message' => $feedback,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
        ));
        
        // Set variables
        $subject = 'Christian Flatshare - Story';
        $to = array('website@christianflatshare.org', 'ryanwdavies@gmail.com');
        if ($name != 'ZAP' && ! stristr("$feedback","prada")  && ! stristr("$feedback","jewel")  && ! stristr("$feedback","watches")  && ! stristr("$feedback","a href") && ! stristr("$feedback","hand") && !               stristr("$feedback","Replica") && ! stristr("$feedback","discount") )
        {
         $msg = $CFSMailer->createMessage($subject, $body, $to);
         $sent = $CFSMailer->sendMessage($msg);
        }  else {
          $sent=1;
        }
		
        if ($email == 'dukang2004@yahoo.com') {
            $showForm = FALSE; // don't show form
            $msg = '<p class="success">Thank you for your sending your feedback.<br />If your message requires an answer we will reply shortly.</p><br />'."\n";
        }
        else {
            if ($sent > 0) {	
                $showForm = FALSE; // don't show form
                $msg = '<p class="success">Thank you for your sending your feedback.<br />If your message requires an answer we will reply shortly.</p><br />'."\n";
            }
            else {
                $msg = '<p class="error">There was a problem sending your message.<br />Please contact Christian Flatshare.</p>'."\n";
            }
        }
		$pageTitle = "CFS Stories - thank you";
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
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			<div id="header_stories" class="header">
				<h1>CFS Stories </h1>
				<h2>CFS stories, news, and some of the lovely things you've said...</h2>
			</div>	
		<div style="width:450px; float:left;"> 
		
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="grey"><strong><span style="font-size:13px;">"</span></strong>
				Christian Flatshare has been a great blessing more than once in the last year or so. Firstly when I needed to move out of a nightmare flat that had been condemned by the council- I only had 3 months before getting married and didn't know where I'd find a place for such a short let. There was just the right room- for exactly three months, with a lovely girl who is now a close friend. Perfect! Then, when we were looking to rent a place as a married couple, we'd been messed around by agents who were inflating prices and playing us off other people- we were praying for a place in our budget as time was running out. Christian flatshare came through again, with a fab home that has blessed tonnes of other people beyond us already- not to mention lovely christian landlord and neighbours! Thank God for the way he provides through his people (and this site)! :) 
					<strong><span style="font-size:13px;">"</span></strong></td></tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px">&nbsp;</td>
						<td align="right" style="padding-bottom:7px"><span class="light_blue">Maria Hogan, London</span></td>						
					</tr>		
					</table>			
					
		
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="light_blue"><strong><span style="font-size:13px;">"</span></strong>
					A slightly different story.... I used CFS 4 years ago as I was looking for somewhere to live in London. Long story short, I met up with a guy who had a spare room and we ended up dating, getting engaged and have now been happily married for nearly 3.5 years!! Who knew that CFS could also be a way of finding love! 
					<strong><span style="font-size:13px;">"</span></strong></td></tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px">&nbsp;</td>
						<td align="right" style="padding-bottom:7px"><span class="light_blue">Purfleet</span></td>						
					</tr>		
					</table>			
			
		
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="grey"><strong><span style="font-size:14px;">"</span></strong>
					Excellent way of renting our flat. Our new tenants have decided to go to our local church too! Will definitely be using again and recommend to friends.
					<strong><span style="font-size:14px;">"</span></strong></td></tr>
					</table>
					
					
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="grey"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="grey">Melissa Day, London</span></td>						
					</tr>					
					</table>		
				
		
		

					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="light_blue"><strong><span style="font-size:13px;">"</span></strong>
					Hi! Just wanted to let you know how amazing this website is - thank you so much! I'm just returning from Tanzania after a year serving missionaries and am moving to London...it's been so easy to get going and look at options for living with Christians in the right area for work, at the right price, with the right kind of facilities etc. So...well done and keep it going! Many thanks.
					<strong><span style="font-size:13px;">"</span></strong></td></tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px">&nbsp;</td>
						<td align="right" style="padding-bottom:7px"><span class="light_blue">Helen Stacey, <a href="http://www.hopac.net/">Haven of Peace Academy</a>, Dar es Salaam</span></td>						
					</tr>		
					</table>			
			
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="grey"><strong><span style="font-size:14px;">"</span></strong>
					Big fan of your website!! I found the most amazing people to live with on here and I love the honesty and straight forwardness. Thanks for putting this together! 
					<strong><span style="font-size:14px;">"</span></strong></td></tr>
					</table>
					
					
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="grey"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="grey">Anon., London</span></td>						
					</tr>					
					</table>		
		
		
					
										

			
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="light_blue"><strong><span style="font-size:13px;">"</span></strong>
					We are a young couple looking to rent out our spare room. I didn&rsquo;t know anything like this existed and was hesitant to advertise broadly, but my husband googled Christian Flatshare and your site came up. It is perfect and just what we wanted. God bless you! 
					<strong><span style="font-size:13px;">"</span></strong></td></tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px">&nbsp;</td>
						<td align="right" style="padding-bottom:7px"><span class="light_blue">Thiago Sousa Da Silva, Edinburgh (EH11)</span></td>						
					</tr>		
					</table>			
			
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="grey"><strong><span style="font-size:14px;">"</span></strong>
					Thanks CFS. I can't believe how incredibly easy it was to find a fantastic new flatmate in three days. We were able to "get to know" the people before they came to visit the house through them posting their personal situation details on the site. The people who came already knew about us and the house through the photos and details we posted on the site. It meant that every one who came was a pretty good match. Just wish we had some more rooms for the others to move in!!!!  
					<strong><span style="font-size:14px;">"</span></strong></td></tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="grey"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="grey">Rob Wyatt, Wimbledon (SW19)</span></td>						
					</tr>					
					</table>		
		
		
		
		
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
					  <td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="light_blue"><strong><span style="font-size:13px;">"</span></strong>
					I like all the tick boxes - it makes a quick and easy way to convey a lot of important information and makes it easy to compare different houses as they all have to provide similar info.
					<strong><span style="font-size:13px;">"</span></strong></td>
					</tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="light_blue"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="light_blue">Anna Harris, Streatham (SW16)</span></td>						
					</tr>		
					</table>
					
	
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="grey"><strong><span style="font-size:14px;">"</span></strong>
					Hi CFS! Fantastic... we posted an advert with you a few weeks ago and had a call after less than 2 minutes! We already have our first happy tenant, and will be looking for a second tenant now that we have refurbished the second room. I would be more than willing to pay for this service - congratulations for the great design, user-friendliness and excellent communication. Any plans to put e.g. a sample contract to help regulate landlords / tenants? (with all disclaimers to protect CFS etc!) God bless your ministry, Ranjeet 
					<strong><span style="font-size:14px;">"</span></strong></td></tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="grey"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="grey">Ranjeet Guptara, Camden Town (NW1)</span></td>						
					</tr>					
					</table>
					
					
					
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr class="box_light_grey">
					<td align="left" style="font-size:11px;padding-left:5px;padding-right:5px;padding-top:1px;padding-bottom:1px"><span class="grey">
					Thank you Ranjeet, and a good idea. We will look to include some materials to help landlords in the next CFS upgrade. Donations: our greatest hope and prayer at the moment is that members will continue to take initiatives to share CFS with their churches, student groups and friends. 
					</span></td>
					</tr>
					<tr class="box_light_grey">
					<td align="right" style="font-size:11px;padding-left:5px;padding-right:5px;padding-top:2px"><span class="grey">
					Christian Flatshare support
					</span></td></tr>
					<tr><td style="padding-bottom:7px"></td></tr>
					</table>
					
					
	
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
					  <td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="light_blue"><strong><span style="font-size:13px;">"</span></strong>
					 I liked using Christian Flatshare to advertise my room as I had so many messages and it really helped me find the right person to move into my room with less stress. Thank You Christian Flatshare!! You're the best :)
					<strong><span style="font-size:13px;">"</span></strong></td>
					</tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="light_blue"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="light_blue">Kandi Statham, West Wimbledon (SW20)</span></td>						
					</tr>		
					</table>
					
	
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td align="left" style="font-size:12px;padding-left:5px;padding-right:5px"><span class="grey"><strong><span style="font-size:14px;">"</span></strong>
					Just a quick note to say that we are a young Christian couple looking to let our spare room out for some extra income, and we were so blessed to find this site. Thank you so much!
					<strong><span style="font-size:14px;">"</span></strong></td></tr>
					</table>
					<table border="0" cellpadding="0" cellspacing="2" width="100%">										
					<tr>
						<td align="left" style="padding-bottom:7px"><span class="grey"></span></td>
						<td align="right" style="padding-bottom:7px"><span class="grey">Beth Sayers, Birmingham (B37)</span></td>						
					</tr>					
					</table>
 
		</div>
			<div id="columnSeparator" style="width:50px; height:500px;"><!----></div>
			<div style="width:350px; float:left;">
	      <p class="mt0">If you have a Christian Flatshare story or some comments that you would like to share with others, please let us know.  </p>
	      <p class="mt0 mb20">Share your stories and encourage others.</p>
	      <p>
				  <?php print $msg?>
				</p>					

		  <?php if ($showForm) { ?>
<?php
$userName = (isset($currentUser['name'])) ? $currentUser['name'] : NULL;
$userEmail = (isset($currentUser['email_address'])) ? $currentUser['email_address'] : NULL;
?>
        
				<div id="contactUsForm">
					<form name="replyToAdOwner" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
					<input type="hidden" name="question_id" value="<?php print $question['question_id']?>" />							
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
					<tr>
						<td align="right"><span class="obligatory">*</span> Your name:&nbsp;</td>
						<td align="left"><?php print $error['name']?><input name="name" type="text" id="name" size="35" value="<?php print $userName; ?>"/></td>
					</tr>
					<tr>
						<td align="right"><span class="obligatory">*</span> Your email:&nbsp;</td>
						<td align="left"><?php print $error['email']?><input name="email" type="text" id="email" size="35" value="<?php print $userEmail; ?>"/></td>
					</tr>					
					<tr>
						<td  align="right" valign="top">Your location:&nbsp;</td>
						<td align="left" ><input name="area" size="40" type="text" id="area"/><br /><span class="grey">(e.g. Cambridge, CB5)</span></td>
					</tr>					
					</table>					

					<p class="mt10 mb0">&nbsp;&nbsp;Your story, feedback or testimony - the masterpiece...</p>
					<table border="0" cellpadding="0" cellspacing="5" width="100%">
					<tr>
						<td align="right">
						<textarea name="feedback" rows="10" id="feedback" style="width:100%;padding:2px; font-size: 12px;"><?php print $feedback?></textarea>
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
						<td align="right">
						<input type="submit" name="Submit" value="Send your story" />
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
