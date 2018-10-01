<?php
session_start();

// Autoloader
require_once 'web/global.php';

if (isset($_POST['sent'])) { $sent = $_POST['sent']; }else{ $sent = NULL; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Frequently Asked Questions - Christian Flatshare</title>
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
	<script language="javascript" type="text/javascript">
		
		var slide = new Array();
		window.addEvent('domready', function(){
			
			// TAB FUNCTIONALITY
			// Get all links inside tablist_2
			$$('#tablist_2 a').each(function(a){
			
				// Assing an on click handler to this link
				a.addEvent('click',function(){
				
					// Reset all links to their original state
					$$('#tablist_2 a').removeClass('selected');
					// Hide all tabs
					$$('div.newtab').setStyle('display','none');
					
					// Set the current link to the UP state
					this.addClass('selected');
					// Show the appropriate tab
					var id = this.id.substr(5);
					$(id).setStyle('display','');				
				
				});
			
			
			});
			
			
			// TAB #1 (faq sliding panes)		
			// Get all links on the page that have a class of "paneSwitch" and
			// assign the "pane open / close" functionality to them
			$$('a.paneSwitch').each(function(a){
				// Assign a slide transition to each faq div
				slide[a.id] = new Fx.Slide("pane_"+a.id,{transition: Fx.Transitions.Quad.easeOut});
				// Initially have each answer div hidden
				slide[a.id].hide();						
				// add an event listener to all links to show the answer divs
				a.addEvent('click',function(e) {
					e = new Event(e);
					slide[this.id].toggle();
					e.stop();
				});
			});
		});

	</script>

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
			<div id="header_frequently_asked_questions" class="header">
				<h1>Frequently Asked Questions</h1>
				<h2>...and their answers</h2>
			</div>
			
			<!-- 
			
				TO ADD A NEW TAB:
			
				Add a new line to the <div id="tablist_2">. E.g.:
				
				<li><a href="#" id="link_tab_{ID}"><div><div>Antoher tab</div></div></a></li>
				
				And then add a new <div class="newtab" id="tab_{ID}"> .......... content here ......... </div>
				
				Just ensure that the {ID} of the <a> matches the {ID} of the <div>			
			
			-->
			
			<?php if ($_GET['sent'] == "true") { ?>
			<div>
				<p class="mt0 mb0">		
					<table cellpadding="5" cellspacing="0" width="100%">
					<tr align="left">
					<td></td>
					<td style="padding-top:00px;padding-left:0px;padding-right:0px;padding-bottom:10px;" align="left">
					
					<div class="mt10" style="width:420px;background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
					 <p class="mt0 mb5" align="center"><strong>Thank you for your message</strong></p>
					 <p class="mt0 mb0 obligatory" align="center"><strong>If the answer to your question is in the FAQs below, we assume you will find your answer here and not need to reply to your message. </strong></p>
					</div>					
				  <div class="mt10 mb0" style="width:420px;;padding:10px;">
				<!--	 <p class="mt0 mb0" align="center"><strong>If you are <i>offerring</i> or <i>looking</i> for accommodation, please read below for</strong><strong> details on how you can place an advert.</strong></p>
				-->
					</div>
					</td>
					</tr>
					</table>
					</p>
			</div>
			<?php } ?>
						
			<div id="tablist_2">
				<ul>
					<li style="margin-left:15px;"><a href="#" class="selected" id="link_tab_1"><div><div>FAQs</div></div></a></li>
					<li><a href="#" id="link_tab_2"><div><div>Finding Accommodation</div></div></a></li>
					<li><a href="#" id="link_tab_3"><div><div>Offering Accommodation</div></div></a></li>
					<li><a href="#" id="link_tab_4"><div><div>Being Safe Online</div></div></a></li>
					<li><a href="#" id="link_tab_5"><div><div>Flat-finding tips</div></div></a></li>				
   			  <li><a href="#" id="link_tab_6"><div><div>Letting accommodation</div></div></a></li>				
<!--					<li><a href="#" id="link_tab_test"><div><div></div></div></a></li>-->
<!--					<li><a href="#" id="link_tab_test"><div><div>Banner Advertising</div></div></a></li>				-->
				</ul>				
			</div>

			<div class="newtab" id="tab_1">
			<!-- <h2 class="mb10 mt0">Frequently Asked Questions</h2> -->
				<div class="two_column_canvas">
					<div class="col1">
					<p class="mt0">If you cannot find the answer to your questions in these pages please <a href="contact-us.php">contact us</a>.</p>
					<img src="images/static_page_photos/FAQs.jpg" width="366" height="245" class="photo_border" />

					<h2 class="mb20 mt10">Answers...</h2>


	<table border="0" cellpadding="8" cellspacing="0" >
				<tr class="trEven" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq1"></a><strong>How do I place an advert?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:20px;" >
						For detailed instructions on how to place an advert, cick on the grey tab ("Finding accommodation" or "Offering accommodation").
					</td>
				</tr>
				
								
				<tr class="trOdd">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq2"></a><strong>How much does CFS cost to use?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:20px;">
					 Christian Flatshare is free to join and use.<br /><br />
					 Christian Flatshare is also free to share with your church and friends... and here are lovely two posters to do that! <a href="A4 CFS Poster.pdf">A4 landscape</a> and <a href="A5 CFS Poster.pdf">A5 portrait</a>.
					</td>
				</tr>
				
				

								
				<tr class="trEven">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq3"></a><strong>Do I have to join to use Christian Flatshare?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:20px;">
					 Anyone can search and view accommodation adverts.<br /><br />
					You must join to reply to adverts by email, to post adverts and to receive automatic email notifications about new adverts (&quot;Flat-Match&quot; and &quot;Pal-Up&quot;). It is free to join and use CFS. <a href="register.php">Click here to join</a>.<br /><br />
						Once joinged CFS members are able use all Christian Flatshare features including advert posting, seeing your advert statistics, and receiving automatic &ldquo;Flat-Match&rdquo; and &ldquo;Pal-Up&rdquo; emails).
					</td>
				</tr>
				
								
				<tr class="trOdd" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq4"></a><strong>How do I change or edit my advert?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:20px;" >
<p class="m0">To edit your advert, go to the &quot;Your ads&quot; page (click in the top right corner), and there you will see your advert summary shown.</p>
							<p class="m0">&nbsp;</p>
							<p class="m0">In the top right had corner of your advert you should see these links: </p>
							<p class="m0">&nbsp;</p>
							<p align="center" class="m0"><img src="images/static_page_photos/edit_ad.png" width="252" height="56" /></p>
							<p align="center" class="m0">&nbsp;</p>
							<p align="left" class="m0">You can edit your advert to change its contents of your advert, suspend it, delete it or ad photos. </p>
							<p align="left" class="m0">&nbsp;</p>
							<p align="left" class="m0 mb0">If you do not see these links, please disable any "ad blocker" software and update any safeweb software you may use (some of these have been known to block CFS features, unnecessarily).<br />
	 				 </td>
				</tr>
				

								
								
	<tr class="trEven" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq5"></a><strong>What is &quot;Flat-Match&quot;?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:20px;" >
						Flat-Match is an automatic process that matching the accommodation offered to the accommodation wanted adverts, based on around attributes given in both ads. If a person has posted an accommodation wanted advert and has chosen to use automatic Flat-Match (by ticking the Flat-Match tick box on their advert) they will receive a notification email when any new accommodation offered advert suitable for them is posted on CFS. Automatic Flat-Match emails can be stopped at anytime by using "edit my adverts" and un-ticking the automatic Flat-Match option in the wanted advert.
					</td>
				</tr>
				
								
				<tr class="trOdd">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq6"></a><strong>What is &quot;Pal-Up&quot;?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:20px;">
					 &quot;Pal-Up&quot; is an option that you can enable on a wanted accommodation ad to express that you are willing to be in contact with others looking for accommodation, to find a place together. Pal-Up will match wanted ads for those who are looking for accommodation in same location and with similar criteria, and who have both chosen to receive Pal-Up messages.<br />
								<br /> 
							Similar to Flat-Match, members posting accommodation wanted adverts can choose to Pal-Up by ticking the Pal-Up tick box in their advert. CFS will then send the member details of other accommodation wanted adverts posted subsequently on CFS, and which have similar criteria (price, location, and preferred household). The older of two adverts is the one which will receive the Pal-Up messages.<br />
							<br />
							Pal-Up emails can be stopped at anytime by using &quot;edit my adverts&quot; and un-ticking the Pal-Up option in the wanted advert.
					</td>
				</tr>								
				
				
				
				
				
						<tr class="trEven" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq7"></a><strong>Will landlords accept tennants receiving DSS support?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:20px;" >
						Landlords will have their own requirements. If you are receiving DSS support, unless otherwise stated in an advert, you should reply to offered accommodation adverts, and ask if the landlord is willing to accept a tenant on DSS income support.
					</td>
				</tr>
				
								
				<tr class="trOdd">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq8"></a><strong>Can I change the email address I use for my account?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:20px;">
					 To change your email address, first login, and then click on &quot;Change email or name&quot; which is shown on the menu on the right.
					</td>
				</tr>
				
				
				
				
				
				
				<tr class="trEven" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq9"></a><strong>Can I remove my advert at any time?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:20px;" >
						Once logged in you can remove an advert by going to  &quot;Your ads&quot;, and clicking on &quot;Delete ad&quot;.
					</td>
				</tr>
				
								
				<tr class="trOdd">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq10"></a><strong>Can I see how many people have viewed my advert?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:20px;">
					 The number of times an advert has been viewed in detail is shown at the bottom of adverts.
					</td>
				</tr>
				
				
				
				
     </table>				
									
					
	
					
					
					
					</div>
					<div class="col2">			
					
					<h2 class="mb10 mt0">Questions...</h2>
					<li><a href="#faq1">How do I place an advert?</a></li>									
					<li><a href="search-tips.php">How do I search for for accommodation?</a></li>									
					<li><a href="#faq2">How much does CFS cost to use?</a></li>
					<li><a href="#faq3">Do I have to join to use Christian Flatshare?</a></li>
	        <li> <a href="#faq4">How do I change or edit my advert?</a></li>					
					<li> <a href="#faq5">What is &ldquo;Flat-Match&rdquo;?</a></li>
					<li> <a href="#faq6">What is "Pal-Up"?</a></li>
					<li><a href="#faq7" >Will landlords accept tennants receiving DSS support?</a></li>
					<li><a href="#faq8">Can I change the email address I use for my account?</a></li>					
					<li><a href="#faq9">Can I remove my advert at any time?</a></li>
					<li> <a href="#faq10">Why are accommodation prices only on a monthly basis?</a></li>					
					<li><a href="#faq11">Can I see how many people have viewed my advert?</a></li>
					<li> <a href="#faq12">Do you keep all my personal information private?</a></li>
					<li> <a href="#faq13">How do I report  inaccurate or problem adverts? </a></li>
					<li> <a href="#faq14">How long can an advert appear on Christian Flatshare for?</a></li>
						
	<p style="padding-top:30px"></p>						
 
 			<table border="0" cellpadding="8" cellspacing="0" >		
				<tr class="trOdd" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq10"></a><strong>Why are accommodation prices only on a monthly basis?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:15px;" >
						Christian Flatshare expresses prices in calendar months to offer an easy basis for comparison. Those letting accommodation and preferring to express the arrangement in weekly terms should use PCM as an expression of the average month, based on 4.33 weeks, and include details of the weekly payment in the accommodation description.
					</td>
				</tr>
				
								
				<tr class="trEven">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq11"></a><strong>Can I see how many people have viewed my advert?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:15px;">
					 The count of the number of times your advert has been displayed is shown both at the bottom of your advert, in grey, and on the &quot;Your ads&quot; page shown in your advert summary.
					</td>
				</tr>
				
				
				
				<tr class="trOdd" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq12"></a><strong>Do you keep all my personal information private?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:15px;" >
						We will never disclose your personal information to a third party (unless required to do so by law). Information contained in adverts posted by CFS  members will only contain the information that the member has chosen to  provide. The details provided in the advert free text sections are visible on the internet and to search engines; the contact name and phone number is only display when someone is logged in, and will not be visible to search engines.
							<br /><br />
							The email address that a member provides during registration is not  disclosed on the CFS website (although a member may include it in the advert text if they wish to); those responding to accommodation adverts do so using the website which will not disclose the adverts owner's email  address.<br />
							<br />
							For more information on our data controls please see our "<a href="privacy-policy.php">Privacy Policy</a>".
					</td>
				</tr>
				
								
				<tr class="trEven">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq11"></a><strong>Can I see how many people have viewed my advert?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:15px;">
					 The count of the number of times your advert has been displayed is shown both at the bottom of your advert, in grey, and on the &quot;Your ads&quot; page shown in your advert summary.
					</td>
				</tr>
				
				
								
								
				<tr class="trOdd" >
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq13"></a><strong>How do I report  inaccurate or problem adverts?</strong>
					</td>
				</tr>
				<tr class="trOdd">
					<td style="padding-bottom:15px;" >
						If you encounter an inaccurate with an advert, it is best to reply to the advert to let the person who has created it know of about the problem.<br /><br />
						If you notice something unusual or concerning about and advert, then please report it to us by clicking on the &quot;Report ad to CFS admin&quot; link on the advert. Messages sent in this way will be sent directly to CFS administrators.<br />
							<br />
							We are keen to maintain the standard of accommodation adverts on CFS and would wish to remove any inappropriate content as promptly as possible.
					</td>
				</tr>
								
				<tr class="trEven">
					<td style="padding-top:15px;padding-bottom:0px;">
						<a name="faq14"></a><strong>How long can an advert appear on Christian Flatshare for?</strong>
					</td>
				</tr>
				<tr class="trEven">
					<td style="padding-bottom:15px;">
					 Adverts can remain on CFS indefinitely, subject to these criteria:<br />
					 <br />
					 - The advert's &quot;Available from&quot; (for offered accommodation adverts), or &quot;Required from&quot; (for wanted accommodation adverts), is not more than 10 days older than the current date. If this occurs, the owner is notified and given options to change the advert, otherwise the advert will expire and no longer appear in searches.<br />
					 - The owner of the advert logs in to CFS every 30 days (otherwise their adverts are automatically suspended).<br />
					 <br />
					 These measure are taken to automatically remove adverts that are no longer in use.
					</td>
				</tr>
				
																
				
				
			</table>					
						
						
					</div>
					<div class="cc0"><!----></div>
				</div>
				<br /><br />
			</div>
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			

			<div class="newtab" id="tab_2" style="display:none;">
			
				<div class="two_column_canvas">
					<div class="col1">
						<h2 class="mb5 mt0">Posting Wanted Accommodation adverts</h2>
						<p class="mt0 mb5">It is FREE to place adverts.</p>												
						<p class="mt0">If you are looking for accommodation you should place a wanted accommodation advert. <a href="register.php">Join CFS</a>, click login and click &quot;<strong>Post an Accommodation Advert!</strong>&quot;.	</p>
				<!--		<p class="mb10">To post an advert you must first <a href="register.php">join Christian Flatshare</a> (CFS).</p>	-->					
						<p><br />Posting a Wanted ad <u>before</u> replying to Offered ads helps you because:</p>
						<p class="mt0 mb5">1. When you reply to Offered ads your Wanted ad (which decribes you and your requirements) is automatically included in your message</p>
						<p class="mt0 mb5">2. You are automatically shown Offered ads that match your Wanted ad requirements</p>
						<p class="mt0 mb5">3. Those offering accommodation will be able to see and reply to your advert</p>
						<p>Taking care t put good descriptions and adding photos helps you get the best response. After you have posted an advert you can (from &quot;Your ads&quot;, top right corner):
						<ul>
						<li><strong>Preview</strong> your ad</li>
						<li><strong>Add photos</strong></li>
						<li><strong>Edit</strong> your ad to change its details</li>
						<li><strong>Suspend </strong>your ad temporarily </li>
						<li> <strong>Delete</strong> your ad</li>
						<li> <strong>Facebook</strong> your ad, to share with Facebook friends</li>
						<li> Change your advert's advert picture </li>
						</ul>
						<h2 class="mb5 mt0">&nbsp;</h2>
						<h2 class="mb5 mt0">Adverts Matching Yours </h2>
						<p class="mt0">Offered accommodation adverts that match your Wanted advert will be shown below your advert summary, on the Your ads page. You should review these adverts and reply to any that are suitable.</p>				
					</div>
					<div class="col2">
						<h2 class="mb5 mt0">Replying to Offerd Accommodation ads</h2>
						<p class="mt0"> You can search offered accommodation adverts from the front page. When you reply to any adverts it is helpful if you write a friendly note which introduces you and your accommodation requirement.</p>
						<p class="mt0">	If you have placed a Wanted accommodation advert a link to it will automatically be included in your reply, so the recipiant can see your advert. </p>
							<p class="photoContainer"><img src="images/static_page_photos/helen_with_laptop.jpg" width="366" height="245" class="photo_border" /></p>
						<h2 class="mb5"><span>Advert posting tips</span></h2>
						<p class="mt0"> Some advert posting tips which are well worth following... </p>
						<ol class="mb0">
						<li class="mt0 mb5">A <strong>friendly, helpful and informative description</strong> of those looking for accommodation is <em>always</em> a good start.  Adverts with brief descriptions attract less response. When posting the ad, can  initially put a brief description and later edit it to add more.</li>
						<li class="mt0 mb5"><strong>Adding <span class="obligatory">*</span>photos<span class="obligatory">*</span></strong> can help to introduce the accommodation seeker(s) - and can be fun too!</li>
						<li class="mt0 mb5">If you have a website or a social-networking page, then including a link to it will help others to find out more about you.</li>
						<li> class="mt0 mb5"<strong>Regularly logging in</strong> to CFS will help others see you are actively using CFS as your advert(s) will show how many days since you last logged in.</li>
						</ol>
		
					</div>
					<div class="cc0"><!----></div>
				</div>			
			
			</div>
			


			<div class="newtab" id="tab_3" style="display:none;">
				<div class="two_column_canvas">
					<div class="col1">
						<h2 class="mb5 mt0">Posting Offered Accommodation adverts</h2>
						<p class="mt0 mb5">It is FREE to place adverts.</p>						
						<p class="mt0 mb5">If you have accommodation to offer you should place an Offered accommodation advert. <a href="register.php">Join CFS</a>, click login and click &quot;<strong>Post an Accommodation Advert!</strong>&quot;.</p>
			<!--			<p>To post an advert you must first <a href="register.php">join Christian Flatshare</a> (CFS).<p>	-->
						<p><br />You can place different types of Offered adverts:</p>
						<ul>
						<li><strong>House or Flatshare </strong>- Houses or flats shared with others</li>
						<li><strong>Room Share </strong>- A room shared with someone of the same sex</li>
						<li><strong>Family Share </strong>- Accommodation shared with a family with children or with a married couple</li>
						<li> <strong>Whole Place </strong>- An unoccupied house or flat</li>
						</ul>

						<p><br />After you have posted an advert you can:
						<ul>
						<li><strong>Preview</strong> your ad</li>
						<li><strong>Add photos</strong></li>
						<li><strong>Edit</strong> your ad to change its details</li>
						<li><strong>Suspend </strong>your ad temporarily </li>
						<li> <strong>Delete</strong> your ad</li>
						<li> <strong>Facebook</strong> your ad, to share with Facebook friends</li>
						<li> Change your advert's advert picture </li>
						</ul>

						<h2 class="mb5 mt0">&nbsp;</h2>
						<h2 class="mb5 mt0">Adverts Matching Yours </h2>
						<p class="mt0">Wanted accommodation adverts that match your Offered advert will be shown below your advert summary, on the Your ads page. You should reply to any adverts that you think match.</p>
					</div>
					<div class="col2">
						<h2 class="mb5 mt0">Replying to Wanted Accommodation ads</h2>
						<p class="mt0"> If you enter a postcode of accommodation you have to offer in Quick Search (on the front page) you will be shown Wanted ads from who are looking for accommodation in that area.<br />
						<br />
						If you have posted an Offered accommodation advert and reply to the Wanted ads, a link to your advert is automatically included in your reply.</p>
													<p class="photoContainer"><img src="images/static_page_photos/tim_katy_and_sara_sw13.jpg" width="366" height="245" class="photo_border" /></p>
						<h2 class="mb5"><span>Advert posting tips</span></h2>
						<p class="mt0"> Some advert posting tips which are well worth following... </p>
						<ol class="mb0">
						<li>A <strong>warm, helpful and informative description</strong> of the accommodation (and any living there) is <em>always</em> helpful. Adverts that have a brief description and have no photos attract less response. Once an advert is posted you can edit it to add photos and add more details.</li>
						<li><strong>Adding <span class="obligatory">*</span>photos<span class="obligatory">*</span></strong> is key to getting the best response as they can show what the accommodation is like, and can help introduce others living in the accommodation - photos can be fun too!</li>
						<li>If you have a website or a social-networking page, then including a link to it will help others to find out more about you.</li>
						<li><strong>Regularly logging in</strong> to CFS will help others see you are actively using CFS as your advert(s) will show how many days since you last logged in.</li>
						</ol>
						<p>
						
					</div>
					<div class="cc0"><!----></div>
				</div>
			</div>
			


			<div class="newtab" id="tab_4" style="display:none;">
				<div class="two_column_canvas">
					<div class="col1">
					<p class="mt0"><span class="style15" style="margin-bottom: 0px"><span class="style9">Below are some common sense pointers to observe when interacting with others online, and will help you to spot any unusual behaviour. </span></span></p>
					<p class="mt0">Scam  attempts are commonplace in websites that allow interaction between people.  Here are some pointers which may help you detect scams: </p>
					<ol>
					<li>Always <strong>see the accommodation first</strong>, before paying a deposit or rent.</li>
					<li>Any requests for payments through untraceable money transfer services such as &quot;<strong>Western Union Money Transfer</strong>&quot; or &quot;<strong>Moneygram</strong>&quot;       should be treat<span class="style6">ed as highly suspicious.</span></li>
					<li><a href="http://www.consumerdirect.gov.uk/watch_out/scams/cheque-overpayment/" target="_blank">Overpayments scams</a>: <strong>overpayments</strong> made with a request for the 'change' (sometimes       blamed on a clerical error) should be treated suspiciously.</li>
					<li>Be wary of anyone who appears to want to remain distant from you, and does not wish to see you or the accommodation before parting with money.</li>
					<li>Do not disclose your<strong> banking information</strong> to individuals over the internet.</li>
					</ol>
					</div>
					<div class="col2">
					<ol start="6">		
					<li>Do not send images or<strong> copies of identification</strong>, such as your driving       license or passport, which can be used in identity theft.</li>
					<li>Do not sign a contract or make a payment without seeing the accommodation first. Adverts for accommodation that does not exist is a common scam, and can be       used to catch those moving to the UK. Protracted or seemingly awkward       situations which make it difficult to see accommodation before paying a       deposit or rent should be treated very cautiously. Those unable to see the       accommodation might like to consider making use of a church reference and       obtain church contact details through an official church website. Those       working in a church office are likely to be only to willing to help vouch       for the existance of a person, and their accommodation situation, and likewise those offering accommodation willing to connect you with their church to make.</li>	
					</ol>
					<p class="mt0">If you receive a suspicious scam-type email we advise  that you do not reply to the email, as this can only encourage those who send them. Those sending scam emails are likely to have sent multiple similar emails and will not be waiting on individual replies.  Please <a href="contact-us.php">contact us</a> so that we can prevent the same email going to other members.</p>
					</div>
					<div class="cc0"><!----></div>
				</div>			
			</div>	
			
			
			
			
			
			<div class="newtab" id="tab_5" style="display:none;">
			<h2>Some flat-finding tips from our international team of flat finding experts (ahem)...</h2>
			<div class="two_column_canvas columnListMargins">
			<div>

			</div>
			<div class="col1">
                   <b>Deposits</b></br></br>Landlord's must keep deposits in a government-backed tenancy deposit scheme (TDP). <a href="https://www.gov.uk/tenancy-deposit-protection/overview" target="_blank">Full details here</a>.</br></br>

Never pay a depsoit until you have seen the accommodation, met other housemates (where applicable), and have witten down what the agreemnent is for letting. Ensure you have a recipet for your deposit. It is a good idea to include on the recipt when your deposit will be returned to you and any terms applicable. Common sense prevails in such matters and a parper record will help avoid any misunderstandings.</li>
                   </br></br><b>Recording arrangments</b></br>
			<p align="justify">One of the best pieces of advice we can give, is to <b>write things down</b> - price, dates, routines, obligations etc. This can be done formally using short-term tenancy agreement (<a href="../docs/Shorthold_tenancy_agreement.doc" target="_blank">example</a>, others online), or informally (especially useful when logging with a family or flatmates arrangments).<br /><br />Informally the arrangement could be recorded by both parties signing a friendly letter. Havng a written agreement is good practice, can help avoid any confusions, and ensures both parties have a record.<br /><br />Here is an example of a friendly letter which does that:</p>	
			
						<p align="right" style="padding-right:10px;padding-left:10px"><font face="Arial" color="green">The landlord<br />New address<br />New town<br /></font></p>					
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">Dear John,<br /><br />Sue and I are both very pleased that you are going to stay with us for a while. To avoid any misunderstandings, here is a list of the points that we have agreed:<br /><br />
						- Rent is &pound;300 per celendar month (payable on the 12th, by transfer). This includes all household bills (inc. broadband and TV license, but not personal calls made from the landline).</font></p>
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- Our agreed moving in date in 25th of July, for three months (ending 25th October). Please can you give us two weeks notice of any change to this.</font></p>						
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- On Tuesdays we have a weekly church meeting in  the house, which you are welcome to join at any point.</font></p>
						
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- We like to keep the house and kitchen tide, and free from washing up. We share in the cleaning and bins go out on Tuesday morning.</font></p>	
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- Sue gets up for work early, as as discussed we try to keep the house quite from 10.30pm.</font></p>		
		<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green"><br />Signed:<br />Date:<br /><br />Peter Johnson&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;John Smith<br /><br /></font></p>							
	
	
	<ul>
				
	<li>Visiting a new place - why not take a friend along? A second opinion is often helpful, and fun to visit a new place in pairs.</li>								
	
	   </ul>
		</div>
		<div class="col2 mt10">
			<ul>
									
			
      <li><span class="obligatory">Smoke alarms</span> &ndash; no place is a home without a <a href="http://www.direct.gov.uk/en/homeandcommunity/inyourhome/firesafety/dg_071751" target="_blank">smoke alarm</a>. If there isn&rsquo;t one fitted we recommend that you make enquiries to have one installed. Smoke alarms cost as little as &pound;5 and are easy to install. Smoke alarms should be tested regularly.</li>
				<li><span class="obligatory">Carbon monoxide alarms</span> &ndash; If the property has a gas supply a <a href="http://www.legislation.gov.uk/uksi/2015/1693/contents/made" target="_blank">carbon monoxide</a> alarm is strongly recommended by the Health and Safety Executive. </li>	
				
									
							
				<li>How could you bless your potential new flat mates? How could they bless you? What could you learn from them? How would you complement and encourage each other?</li>
				<li> Are there any household rules you should be aware of?</li>
			<li>Have you met all the members of your potential new household? It would normally be wisest to meet everyone, so that you know who you would be living with and so that you can assess how well you would all get on.</li>				
				<li> How tidy is your potential new household? How tidy do you like to be?</li>
				<li>The term that the accommodation is available &ndash; make sure you understand the date when you could move in, and the length of time the accommodation is available, if there is a specified end date.</li>
				<li> How is food and cleaning done? Is cleaning done? Are there any habits for cooking and eating together?  Is there a chef??</li>
				<li>If the person offering accommodation has specified in the ad &ldquo;would suit someone who, if asked, could provide a recommendation from a church&hellip;&rdquo;, then it may be helpful to take the friendly initiative and to offer to provide any such recommendation.</li>
				
					<li>Do you understand all the Terms and Conditions in the tenancy agreement? What is the required notice period? What sort of agreement is it, joint liability or individual liability?</li>
					<li> Are there any regular uses of the flat? A weekly bible study? A weekly indoor football contest??</li>
					<li>Do you understand how and when rent should be paid?</li>
					<li>Budget and bills &ndash; what are your obligations to the regular household bills? Is Council tax and the TV license included? Writing a budget can be helpful.</li>
					
					<li>Security &ndash; is the accommodation suitably secure? Are there window and door locks?</li>
					<li>Location, location, location &ndash; how far is the accommodation from your friends, work place, college, your sports club, the gym, your church, public transport, a supermarket? the beach, the cinema (single-screen or multiplex??), a nice picnic spot or an appealing cake shop??</li>
					<li>Insurance &ndash; is there contents insurance and will it adequately cover your belongings?</li>
					<li class="mb0"> Prayer - we need to be spiritually tuned-in for the big (and the small) decisions in life&hellip; prayer is the key for good guidance and for successful flat-finding. </li>
				</ul>
			</div>
			<div class="cc0"><!----></div>
			</div>
			</div>



		
			
			<div class="newtab" id="tab_6" style="display:none;">
			<h2>Accommodation letting pointers</h2>
			<div class="two_column_canvas columnListMargins">
			<div>

			</div>
			<div class="col1">
			<p align="justify">Whether letting accommodation to formally or informally, one of the best pieces of advice we can give is to <b>write things down</b> - price, dates, routines, obligations etc. A more formall method could be to use a short-term tenancy agreement. Here is an <a href="../docs/Shorthold_tenancy_agreement.doc" target="_blank">example</a>, and there are many others online. The arrangment maybe more informal (such as when logging with a family or some flatmate arrangments).<br /><br />Informally the arrangement could be recorded by both parties signing a friendly letter. This is good practice: it can help avoid any confusions, give all parties clarity, can set a good tone, and ensures both parties have a record. Writing such an informal arrangment is a helpful process and may uncover items you ought to include which you had not yet thought of.<br /><br />Here is an example of a friendly letter which does that:</p>	
			
						<p align="right" style="padding-right:10px;padding-left:10px"><font face="Arial" color="green">The landlord<br />New address<br />New town<br /></font></p>					
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">Dear John,<br /><br />Sue and I are both very pleased that you are going to stay with us for a while. To avoid any misunderstandings, here is a list of the points that we have agreed:<br /><br />
						- Rent is &pound;300 per celendar month (payable on the 12th, by transfer). This includes all household bills (inc. broadband and TV license, but not personal calls made from the landline).</font></p>
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- Our agreed moving in date in 25th of July, for three months (ending 25th October). Please can you give us two weeks notice of any change to this.</font></p>						
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- On Tuesdays we have a weekly church meeting in  the house, which you are welcome to join at any point.</font></p>
						
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- We like to keep the house and kitchen tide, and free from washing up. We share in the cleaning and bins go out on Tuesday morning.</font></p>	
						<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green">- Sue gets up for work early, as as discussed we try to keep the house quite from 10.30pm.</font></p>		
		<p class="mt0 mb5" align="justify" style="padding-right:20px"><font face="Arial" color="green"><br />Signed:<br />Date:<br /><br />Peter Johnson&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;John Smith<br /><br /></font></p>							
	
	

		</div>
		<div class="col2 mt10">
	<ul>


									
			
      <li><span class="obligatory">Smoke alarms</span> &ndash; no place is a home without a <a href="http://www.direct.gov.uk/en/homeandcommunity/inyourhome/firesafety/dg_071751" target="_blank">smoke alarm</a>. Smoke alarms cost as little as &pound;5 and are easy to install. Smoke alarms should be tested regularly.</li>
				<li><span class="obligatory">Carbon monoxide alarms</span> &ndash; If the property has a gas supply a <a href="http://www.legislation.gov.uk/uksi/2015/1693/contents/made" target="_blank">carbon monoxide</a> alarm is strongly recommended by the Health and Safety Executive. </li>	

		<li>If you are receiving a <b>deposit</b> you should ensure that this is properly recorded and a recipt given to the tenant. On the reciept, or in your written agreement, it is a good idea to include the terms of the deposit (when it will be repaid, and terms for any deductions).</li>	
					
							
				<li>Taking up references - when placing an Offered Accommodation advert to advertise accommodation you can choose the option &quot;Would prefer someone who, if asked, could provide a recommendation from someone on church staff&. This can be followed up as a form of reference. Work place or previous landlord references could be be taken up too, as appropriate.</li>


				<li>Setting a price - one approach to use is to set a price which is lower than market average, which may attract a greater number of interested tenants. From those tenants you can select the one best to you, and with which you can enjoy a good relationship.</li>
				
				</ul>
			</div>
			<div class="cc0"><!----></div>
			</div>
			</div>



			
			<p class="mb20"><a href="index.php">Return to the welcome page</a></p>
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
