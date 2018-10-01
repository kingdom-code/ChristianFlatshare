<?php
	session_start();
    
    // Autoloader
    require_once 'web/global.php';
    
    connectToDB();
    
	$result = mysqli_query($GLOBALS['mysql_conn'], "select count(church_name) from cf_church_directory;");
	$stats['churches'] = cfs_mysqli_result($result,0,0);
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>About our vision and us - Christian Flatshare</title>
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
		<div id="header_about_us" class="header">
				<h1>About Christian Flatshare </h1>
				<h2>so you'd like to know a little about us... when our  birthdays are, and what we try to do?</h2>
		</div>
		
		
		
		


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
			
			<div id="tablist_2">
				<ul>
					<li style="margin-left:15px;"><a href="#" class="selected" id="link_tab_1"><div><div>Adverts and features</div></div></a></li>
					<li><a href="#" id="link_tab_2"><div><div>Background and operation</div></div></a></li>
					<li><a href="#" id="link_tab_3"><div><div>Church and CFS member support</div></div></a></li>
				</ul>				
			</div>

		
		
		

			<div class="newtab" id="tab_1">
			<a name="2" id="2"></a><h2>Adverts and features</h2>
			<div style="width:400px; float:left;">
			<!-- <h2 class="mb5"><span>Accommodation</span></h2> -->
		<!--	<p align="left" class="mb5"> <strong>Finding accommodation </strong></p> -->
		
			<p align="justify" class="mt10">Christian Flatshare (CFS) is a non-profit organisation which helps accommodation seekers to connect with their local church community.<br /><br />CFS is fee to use, and is supported by its members (ahem).</p>
					 
		<p class="mt20 mb0" align="justify"><strong>Offering accommodation</strong></p>						 
		<p class="mt5 mb10" align="justify">Those with accommodation to offer should place Offered Accommodation adverts. You can change your advert, suspend it, and receive messages from those who respond to it. Adding photos is key and helps to get the best response.</p>
		<p class="mt10 mb0" align="justify">Those placing Offered accommodation adverts can choose that they would prefer someone who, if asked, could provide a recommendation from a current or previous church staff member.</p>
		<p class="mt20 mb0" align="justify"><strong>Finding  accommodation</strong></p>			
		<p class="mt5 mb20" align="justify">Those looking for accommodation should place a Wanted Accommodation advert. Once a Wanted is placed Offered adverts matching automatically highlight matching Offered Accommodation adverts and enables several other features of CFS. CFS offers those looking for accommodation the option to choose <b>"Pal-Up"</b> in their Wanted advert, which indicates they are willing to connect with others with similar accommodation needs to look for accommodation together.</p>
		

<p class="mt20 mb0" align="justify"><strong>Short-term accommodation</strong></p>		
		<p class="mt5 mb10" align="justify"><span class="mt10 mb0">Offered an Wanted accommodation adverts posted for a period of 12 weeks or less are highlighted as being <strong>short-term</strong> adverts. This can be useful for all sorts of short-term requirements, and can be used for finding house-sitters too. There is not a separate category for house-sitting; those offering house-sitting opportunities or services should instead put that detail in the description of their advert. Short-term adverts can be filtered for in searches.</span> </p>
			
	<!-- <p class="mt20 mb0" align="justify"><strong>Other features</strong></p>		 -->
<p class="mt10 mb10" align="justify"><span class="mt10 mb0"><b>Facebook</b> - CFS allows members to connect their account with Facebook, so that they can see friends in common with others using CFS.<br /><br /><b>Flat-match and Pal-up</b> - Those with adverts can choose to receive emails about adverts which match their requirements.</span> </p>		
		<p class="mt5 mb10" align="justify"><span class="mt10 mb0">Please <a href="frequently-asked-questions.php">see the FAQs</a> for further details.</p>

		
		
			</div>
			
			
			<div class="cs" style="width:50px; height:440px;"><!----></div>
			<div style="width:400px; float:left;">
		
   <p><br /></p>
		
		<img src="images/static_page_photos/tim_katy_and_sara_sw13.jpg" width="366" height="253" align="center" class="photo_border" />
		


		<p class="mt20 mb0" align="justify"><strong>Advert types</strong></p>		
			<p class="mt10 mb2" align="justify"><strong>House Share or Flatshare</strong>: accommodation shared with those at a similar age or stage in life (students, mature students and professionals).</p>
						<p class="mt10 mb2" align="justify"><strong>Room Share</strong>: a bedroom shared with someone of the same sex.</p>
				    <p class="mt10 mb2" align="justify"><strong>Family Share</strong>: accommodation with a family with children, or a married couple (lodging).</p>
				    <p class="mt10 mb2" align="justify"><strong>Whole Place</strong>: an unoccupied house or flat.</p>						
					<p class="mt10 mb2" align="justify"><strong>Wanted Accommodation</strong>: adverts of those looking for accommodation.</p>						

					<p align="justify">Students, mature students, professionals; singles,  married couples, families - CFS is for finding homes and is intended to serve the accommodation needs of the whole church community. </p>			
	
			
		<p class="mt5 mb10" align="justify"><span class="mt10 mb0"></span> </p>
			
			</div>
			</div>


			
			<div class="newtab" id="tab_2" style="display:none;">
			<a name="1" id="1"></a><h2>Background and operation</h2>
			<div style="width:400px; float:left;">
			
					<!--	<p align="justify" class="mt0">If our members support us, from late 2008 a small charge will be introduced for posting accommodation offered adverts; &pound;2 for Flat/House Share and Family Share ads, and &pound;10 for Whole Place ads. Flat/House Share and Family Share ads will remain free for those who are unable to pay.</p>
		 -->
		 
		 
        <p class="mt10" align="justify">Christian Flatshare was founded following the experience of those moving to London, who shared a house with others who shared their faith, and were encouraged by the faith community - which offered encouragement, accountability and support. CFS was created to help others make similar connections, and is a resource for church communities (in the UK, US, CA, AU, IE and SA). </p>
        
			<p class="mt20 mb5" align="justify"><strong>Connecting  with the local church </strong></p>
			<p class="mt0 mb10" align="justify">Christian Flatshare helps accommodation seekers to connect with a local church community, and for churches to connect with those moving to their area.</p>
			<p align="justify">Christian Flatshare can be used by members of a church to share their accommodation notices with those inside and outside their congregation, which can help connect them with those moving to the same area.</p>
				<p align="justify">Where one lives is likely to be an influence in choosing which church to attend: by engaging in the accommodation process, a church leader can help facilitate the growth of the church in the immediate community. CFS sees accommodation as a doorway to the local church community. </p>
				
		
			
			
			
			</div>
			<div class="cs" style="width:50px; height:340px;"><!----></div>
			<div style="width:400px; float:left;">

			<img src="images/static_page_photos/church.jpg" width="366" height="253" align="center" class="photo_border" />
			
			
					<p align="justify">CFS is operated as a non-profit organisation (see <a href="where-does-all-the-money-go.php">where does the money go?</a>). The many tens of thousands of pounds required create and to promote Christian Flatshare have been given by those closest to Christian Flatshare as an investment in Christian community. Those using CFS may make donations to help meet our operational costs. </p>
		
		
        <p align="justify">Should you have any enquiries about Christian Flatshare please  <a href="contact-us.php">contact us</a>.</p>
			
				</div>

			</div>
			


			<div class="newtab" id="tab_3" style="display:none;">
			<a name="3" id="3"></a><h2>Church and CFS member support</h2>
			<div style="width:400px; float:left;">
				
				
						<h2 align="justify" class="mt20 mb5"><span>Church Support </span></h2>
			  <p class="mt10" align="justify">CFS  has been supported by the leadership of <a href="churches-using-cfs.php?area=Greater%20London#directory">
			    <?php print $stats['churches']?>
			    </a> churches and organisations since January2007.</p>
			  <p class="mt10" align="justify">Church support is key for Christian Flatshare.</p>
			  <p class="mb0" align="justify"><span class="mt0">Church and Christian organisation leaders can support CFS by requesting to be added to the Christian Flatshare  <a href="use-cfs-in-your-church.php">directory</a>. Those churches and organisations listed appear on CFS' accommodation maps, with links back to them.</span>
			    <!-- <p align="justify">The CFS Directory helps to make us accountable to those who continue to allow us to include them.</p> -->
	 		  Church leaders can also: 
			<ul style="margin-left: 15px; padding-left: 0;" class="mt5 mb0">					
		    <li class="mt5 mb0">Share CFS with their church community</li>
				<li class="mt3 mb0">Link their church website to Christian Flatshare </li>					 					 

				<li class="mt3 mb0">Display a <a href="use-cfs-in-your-church.php">poster</a> in church</li>					 
			</ul>
		</p>

				
			</div>
			
			<div class="cs" style="width:50px; height:340px;"><!----></div>
			
			<div style="width:400px; float:left;">
			  <h2 class="mb5"><span>Member Support </span></h2>
	<p class="mt10 mb0" align="justify">Christian Flatshare members can support best by:
			<ul style="margin-left: 15px; padding-left: 0;" class="mt0 mb0">				
			    <li class="mt5 mb0">Sharing CFS with your church leader</li>
				   <li class="mt3 mb0">Displaying this lovely  <a href="use-cfs-in-your-church.php">poster</a> in your church!</li>					 
				   <li class="mt3 mb0">Reporting any problems (including spelling mistakes!)</li>					 
				   <li class="mt3 mb0">Clicking the Facebook &quot;Recommend&quot;... </li>					 					 
               </ul>					 

<div class="mt10" class="fb-like-container-home"><div class="fb-like" data-href="http://www.christianflatshare.org" data-           send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-               action="recommend"></div></div>

</p>
				<p class="mt20" align="justify">Anyone can donate to Christian Flatshare to help meet CFS' monthly running costs (which are seldom met by donations alone). Costs are kept to a minimum, and web site administration is provided by volunteers.</p>
				<p align="justify" class="mb10">Should donations ever exceed our annual operating costs, the excess will be given to selected charities (see <a href="where-does-all-the-money-go.php">where does the money go?</a> for more information on financial matters).</p>

			</div>
			</div>

			<div class="cc0"><!----></div>
			<div class="hr"><!----></div>			



		
					
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
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
