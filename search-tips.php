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
<title>Search Tips - Christian Flatshare</title>
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
			<div id="header_search_by_church" class="header" style="height:36px;">
				<h1 class="m0"><span>Quick Search  Tips</span></h1>
				<h2>tips for effective searching </h2>
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
	
			<br />
			<div id="tablist_2">
				<ul>
					<li style="margin-left:15px;"><a href="#" class="selected" id="link_tab_1"><div><div>Searching for people</div></div></a></li>
					<li><a href="#" id="link_tab_2"><div><div>Searching for accommodation</div></div></a></li>
					<li><a href="#" id="link_tab_3"><div><div>Showing the newest adverts</div></div></a></li>
				</ul>				
			</div>
			
			
			<div class="newtab" id="tab_1">
			<a name="1" id="1"></a>
			<h2 class="mb0">Finding people for accommodation you have... </h2>
			<ol class="mt10">
				<li> Select &quot;Wanted accommodation&quot;</li>
				<li> Choose accommodation type</li>
				<li> Enter a location (part or full   postcode, town or area name)</li>
				<li> Radius: a radius isn&rsquo;t used when   searching wanted accommodation ads, as the radius is  specified in the   adverts you are searching (see example)</li>
			  </ol>
			
		  <p><strong>Example:</strong><br />
		  Let's say, to take a London example,   you have a flat in Barnes SW13 with a room to let... if you tick &quot;House   / Flatshare&quot; and specify SW13 as the location, you will be shown all wanted   accommodation ads of people who are looking for &ldquo;House / Flatshare&rdquo; type   accommodation in SW13. This would include, for example, someone looking for   accommodation in &quot;3 miles of Ealing (W5)&quot; or &quot;1 miles of Hammersmith (W6)&quot;, which are areas nearby.</p>
			<p class="mb10">Christian Flatshare also shows you who is   willing to &quot;pal-up&quot;, and willing to join with others to look for accommodation a given area.</p>
			<p class="mt0 mb10"><img src="images/static_page_photos/wanted_ad_quick_search_tips.gif" width="863" height="122" /></p>
			
			
			<p class="mt10">
			<table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="274" valign="top"><strong>TIP:</strong> If you place an Offered accommodation advert CFS will automatically show you any Wanted accommodation adverts that match the accommodation you are offering, (as shown below), which saves you the searching:</td>
					<td width="5"></td>
          <td width="566"><img src="images/static_page_photos/ad_match_wanted_quick_search.jpg" width="576" height="143" /> </td>
        </tr>
			  </table>
			</p>
<div class="cc0"><!----></div>						
			<div class="hr"><!----></div>
			</div>		
			
			
			
			
			<div class="newtab" id="tab_2" style="display:none;">
			<a name="2" id="2"></a>
			<h2 class="mb0">Finding accommodation... </h2>
			<ol class="mt10">
				<li> Select &quot;Offered    accommodation ads&quot;</li>
				<li>Choose accommodation type </li>
				<li>Enter a location (part or full   postcode, town or area name)</li>
			  <li>Raduis: choose a search radius in miles </li>
		  </ol>
			
			<p><strong>Example:<br />
			</strong>Let&rsquo;s say you are looking for  accommodation in Camberwell (SE5) in a house or flat share, or a sharing with a family. You can check the boxes for the accommodation type,   enter Camberwell or   SE5 as the location. CFS will then display all of the   suitable results.</p>
			<p class="mb0"><img src="images/static_page_photos/offered_ad_quick_search_tips.gif" width="861" height="124" />
	<p class="mt10">
			<table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="274" valign="top"><strong>TIP:</strong> If you place a Wanted accommodation advert, you can ask CFS to email your automatically with suitable accommodation adverts as when they are posted on the website. CFS will automatically show you any Offered accommodation and Pal-Up adverts that match your requirements (to save you the searching):</td>
					<td width="5"></td>
          <td width="566"><img src="images/static_page_photos/ad_match_offered_quick_search.jpg" width="566" height="128" /> </td>
        </tr>
			  </table>

			</p>						
			<div class="cc0"><!----></div>						
			<div class="hr"><!----></div>			
			</div>
			
	
			<div class="newtab" id="tab_3" style="display:none;">
						<a name="3" id="3"></a><h2 class="mb0">Showing the newest adverts</h2>
			<p class="mt10">Once you have a list of results you can order them to show you the newest adverts first, of order them by price or the date available (for offered ads) or date wanted from (for wanted ads).</p>
			<p class="mt10"><img src="images/static_page_photos/new_ads_quick_search.gif" width="866" height="222" /></p>
			<div class="cc0"><!----></div>			
			<div class="hr"><!----></div>
			</div>		
					
			<p class="mb0"><a href="index.php">Return to the welcome page</a>  </p>
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
