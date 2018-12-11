<?php

// Autoloader
require_once 'web/global.php';

if (isset($_GET['from']) && !empty($_GET['from'])) {
	header("Location: " . $_GET['from']);
	exit;
}

if (isset($_COOKIE['CF_LOGIN'])) {
    $temp = preg_split("/\|/",$_COOKIE['CF_LOGIN']);
    $email = $temp[0];
    $password = $temp[1];
    $remember = true;
}
else {
    $email = NULL;
    $password = NULL;
    $remember = TRUE;
}

$debug = null;

if ($userCountry['iso'] == 'GB') {
    // GB ONLY
    $offeredTowns = "";
    $wantedTowns = "";
	
    // Create the town list for offered accommodation
    $query = "
    	select j.area,count(j.area) as `num`
    	from cf_offered as `o`
    	left join cf_jibble_postcodes as `j` on j.postcode = SUBSTRING_INDEX(o.postcode,' ',1)
    	where o.published = '1' and o.expiry_date >= now() and suspended != 1 ";
    	if (isset($_SESSION['u_id']) && $_SESSION['show_hidden_ads']=='no') {			
    	    $query .= "and o.offered_id NOT IN (select ad_id from cf_saved_ads 
    				   where user_id = ".$_SESSION['u_id']." and post_type = 'offered' and active=2) ";						
    		}
    $query .= "		
    	group by j.area
    	order by j.area asc, num desc;	
    ";
    $debug .= debugEvent("Lookup query for place:", $query);
    $result = mysqli_query($GLOBALS['mysql_conn'], $query);
    $offeredAreas = NULL;
    if (mysqli_num_rows($result) > 0) {
    	while ($area = mysqli_fetch_assoc($result)) {
    		$searchType = ($area['area'] == "Greater London")? "london" : "area";
    		$offeredAreas .= '<li><a href="display.php?search_type='.$searchType.'&post_type=offered&area='.$area['area'].'">'.$area['area'].' ('.$area['num'].')</a></li>'."\n";
    	}
    } else {
    	$offeredAreas .= '<li><em>There are no offered ads in the system yet</em></li>'."\n";
    }

    // Create the town list for wanted accommodation
    $query = "
    	select j.area,count(j.area) as `num`
    	from cf_wanted as `w`
    	left join cf_jibble_postcodes as `j` on j.postcode = w.postcode
    	where w.published = '1' and w.expiry_date >= now() and suspended = '0' ";
    	if (isset($_SESSION['u_id']) && $_SESSION['show_hidden_ads']=='no') {			
    	    $query .= "and w.wanted_id NOT IN (select ad_id from cf_saved_ads 
    				   where user_id = ".$_SESSION['u_id']." and post_type = 'wanted' and active=2) ";						
    		}
    $query .= "					
    	group by j.area
    	order by j.area asc,num desc;
    ";
    $result = mysqli_query($GLOBALS['mysql_conn'], $query);
    $wantedAreas = NULL;
    if (mysqli_num_rows($result) > 0) {
    	while ($area = mysqli_fetch_assoc($result)) {
    		$searchType = ($area['area'] == "Greater London")? "london" : "area";
    		$wantedAreas .= '<li><a href="display.php?search_type='.$searchType.'&post_type=wanted&area='.$area['area'].'">'.$area['area'].' ('.$area['num'].')</a></li>'."\n";
    	}
    } else {
    	$wantedAreas .= '<li><em>There are no wanted ads in the system yet</em></li>'."\n";
    }

}

$result = mysqli_query($GLOBALS['mysql_conn'], "SELECT count(church_name) FROM cf_church_directory");
$stats['churches'] = cfs_mysqli_result($result,0,0);

// The default position of the map 
$defaultPos = 0;

?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Christian Flatshare - Supported by hundreds of churches nationwide - Christian Accommodation</title>
        
        <!-- FACEBOOK OG TAGS -->
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Christian Flatshare" />
        <meta property="og:locale" content="en_gb" />
        <meta property="og:site_name" content="Christian Flatshare" />
        <meta property="og:url" content="http://www.christianflatshare.org" />
        <meta property="og:image" content="http://www.christianflatshare.org/images/CFS_fb_like.jpg" />
      <!--  <meta property="og:description" content="Connecting accommodation seekers with the local church community: Flat/House share, Family share and Whole place ads. Supported by over 400 churches." />  -->
            <meta property="og:description" content="Connecting accommodation seekers with the local church community - in the USA, Australia, Canada, Ireland and the UK. Join through Facebook today!" />
        <meta property="fb:admins" content="848085692" />
        <!-- END OF FACEBOOK TAGS -->
        
        <meta name="keywords" content="christian accommodation,flatshare,houseshare,roommate,roomshare,church,CFS,letting,jesus,christ"/>
        <meta name="description" content="Christian Flatshare - helping accommodation seekers connect with the local church. Flatshare, Family Share and Whole Place accommodation adverts."/> 

<!-- InstanceEndEditable -->
<link href="styles/web.css?v=1" rel="stylesheet" type="text/css" />
<link href="styles/headers.css?v=1" rel="stylesheet" type="text/css" />
<link href="css/global.css?v=1" rel="stylesheet" type="text/css" />
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
	<script language="javascript" type="text/javascript" src="includes/autocompleter/observer.js"></script>
	<script language="javascript" type="text/javascript" src="includes/autocompleter/autocompleter.js"></script>
	<script language="javascript" type="text/javascript" src="includes/autocompleter/autocompleter.request.js"></script>

<script type="text/javascript">

	function doValidation(form) {
		
		var proceed = true; // Initially assume that validation is successful.
		var errorText = "Errors were found in your form. Please amend:\n\n";
		var price_check = /^\d+$/;
		var place_check = /^.+$/;
		
		// Validate place
			if (!place_check.test(form.place.value.trim())) {
			$('location_desc').innerHTML = "please enter a UK postcode or a place name";				
			proceed = false;
		}
		return proceed;
		
	}

	// Ensures that when "wanted" post_type is selected, the radius is set by default to not required
	function quickSearchSelection(post_type) {
	
		switch(post_type) {
		
			case "offered":
				$('radius').disabled = "";
				$('radius').style.display = "";
				$('radius_label').innerHTML = "";
				$('radius').selected = 4;				
				$('radius_search').innerHTML = "Search radius";				
				break;
				
			case "wanted":
				$('radius').disabled = "disabled";
				$('radius').style.display = "none";
				$('radius').selectedIndex = 0;
				//$('radius_label').innerHTML = "not required - radius is specified in wanted ads (see quick search tips)";
				$('radius_label').innerHTML = "n/a - see quick seach tips";
				$('radius_search').innerHTML = "";				
				break;						
		
		}
	
	}	

	var area = "";
	var position = new Array();
	position['northern_ireland'] = 1;
	position['scotland'] = 2;
	position['north_east'] = 3;
	position['north_west'] = 4;
	position['north'] = 5;
	position['east_midlands'] = 6;
	position['west_midlands'] = 7;
	position['wales'] = 8;
	position['east'] = 9;
	position['south_west'] = 10;
	position['south_east'] = 11;
	position['london'] = 12;	
	
	function doOver(e) {
		e = (e) ? e : ((event) ? event : null);
		if (e) {
			target = (e.target)? e.target : ((e.srcElement)? e.srcElement : null);
			if (target) {
				// Safari bug:
				if (target.nodeType == 3) { target = target.parentNode; }
				area = target.id.substr(8);
				$('church_directory_map').style.backgroundPosition = "-"+position[area]*280+"px 50%";
			}
	    }
	}
	
	function doOut() {
		$('church_directory_map').style.backgroundPosition = "<?php print $defaultPos?>px 50%";
	}
    
    
	// AUTOCOMPLETE FUNCTIONALITY
	window.addEvent('domready', function(){
		var place = $('place');
		// Create the loading element
			var indicator = new Element('div', {'class': 'autocompleter-loading', 'styles': {'display': 'none'}}).set('html','<!---->').injectInside('autocomplete_loading_canvas');
			var completer = new Autocompleter.Request.JSON(place, 'ajax-autocomplete.php', {
			'inheritWidth': false,
				'indicator': indicator
		});	
	});	

</script>

<?php if ($userCountry['iso'] == 'GB') : ?>
<script type="text/javascript">
	// AUTOCOMPLETE FUNCTIONALITY
	window.addEvent('domready', function(){
		var place = $('place');
		// Create the loading element
			var indicator = new Element('div', {'class': 'autocompleter-loading', 'styles': {'display': 'none'}}).set('html','<!---->').injectInside('autocomplete_loading_canvas');
			var completer = new Autocompleter.Request.JSON(place, 'ajax-autocomplete.php', {
			'inheritWidth': false,
				'indicator': indicator
		});	
	});	

</script>
<link href="styles/autocompleter.css" rel="stylesheet" type="text/css" />

<?php else: ?>
<!-- GOOGLE MAPS API v3  -->
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
<script src="scripts/search.js"></script>
<?php endif; ?>

<script language="javascript" type="text/javascript" src="includes/ls_crossfade.js"></script>
<!--
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script> 
 <script type="text/javascript" src="https://blockchain.info/Resources/wallet/pay-now-button.js"></script>   -->
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

			<div id="columnLeft">
				<!-- Home page rotator -->
				<div style="height:196px; margin-bottom:20px;">
					<div id="crossfade_canvas"><img src="images/rotator_home_page/default.jpg" style="position:absolute; margin-top:-20px; z-index:1;" /></div>
				</div>				
				<!-- Quick Search Box -->
				<form name="quickSearch" id="quickSearch" method="get" action="display.php" onsubmit="return doValidation(this);">
          <input type="hidden" name="lat" id="quickSearchLat" value="" />
          <input type="hidden" name="lng" id="quickSearchLng" value="" />
          <input type="hidden" name="country" id="quickSearchCountry" value="<?php print getCurrentCountry(); ?>" />
				  <input type="hidden" name="search_type" value="geo" />
				<div class="box_grey mb20">
					<div class="tr"><span class="l"></span><span class="r"></span></div>
					<div class="mr">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td width="260" valign="top">
								<h2 class="mt0 mb10">Quick Search</h2>											
						<table border="0" cellpadding="0" cellspacing="0" class="mb10">
							<tr>
							   <td alight="left" height="5"></td>	
							</tr>
								<td><input type="radio" name="post_type" id="post_type_1" value="offered" checked="checked" onclick="quickSearchSelection('offered');" /></td>
								<td><label for="post_type_1">Offered accommodation ads </label></td>
							</tr>
							<tr>
								<td><input type="radio" name="post_type" id="post_type_2" value="wanted" onclick="quickSearchSelection('wanted');" /></td>
								<td><label for="post_type_2">Wanted accommodation ads </label></td>
							</tr>
						</table>
									
								</td>
								<td valign="top">
									<table border="0" cellpadding="0" cellspacing="0" >
							<tr>
							   <td alight="left" height="10"></td>	
							</tr>
							<tr>
											<td><input name="flatshare" type="checkbox" id="flatshare" value="1" checked="checked" /></td>
											<td height="20" nowrap="nowrap"><label for="flatshare">House / Flat / Room Share <span class="grey">(sharing with others)</span></label></td>
							</tr>
							<tr>
											<td><input name="familyshare" type="checkbox" id="familyshare" value="1" checked="checked" /></td>
											<td height="20"><label for="familyshare">Family Share <span class="grey">(live with a family or a married couple)</span></label></td>
							</tr>
							<tr>
											<td><input name="wholeplace" type="checkbox" id="wholeplace" value="1" checked="checked" /></td>
											<td height="20"><label for="wholeplace">Whole Place <span class="grey">(an unoccupied flat or house)</span></label></td>
							</tr>
						</table>
								</td>
						  </tr>
					  </table>

						<table cellpadding="0" cellspacing="0" class="mb0">
						<tr>
                            <?php if ($userCountry['iso'] == 'GB') : ?>
						  	   <td width="300">Location: <span class="grey" id="location_desc">city, town, tube station, postcode</span></td>
                            <?php else: ?>
						  	   <td width="300">Location: <span class="grey" id="location_desc">city, town, zip code</span></td>
                            <?php endif; ?>
							<!--<td width="33">&nbsp;</td>	-->
						</tr>
						 </table>
                         
                        <table cellpadding="0" cellspacing="0" class="mt0 mb10">
                        	<tr>
                                <?php if ($userCountry['iso'] == 'GB') : ?>
                                    <input type="hidden" name="search_type" value="place" />
                        		    <td width="200" style="padding-right:4px;"><input name="place" id="place" type="text" value="" style="width:220px;"/></td>
                                    <td width="34" align="left" id="autocomplete_loading_canvas"></td>	
                        		    <td alight="left"><?php print createDropDown("radius", getMilesArray(), DEFAULT_RADIUS)?><span class="grey" id="radius_label"></span></td>
                        			<td>&nbsp;</td>
                        			<td alight="left" height="20"><span id="radius_search">Search radius</span></td>
                                <?php else: ?>
                        		    <td width="200" style="padding-right:4px;"><input name="place" id="searchTextField" type="text" value="" style="width:220px;"/></td>
                        		    <td alight="left"><?php print createDropDown("radius", getMilesArray(), DEFAULT_RADIUS)?><span class="grey" id="radius_label"></span></td>
                        			<td>&nbsp;</td>
                        			<td alight="left" height="20"><span id="radius_search">Search radius</span></td>
                                <?php endif; ?>
                        	</tr>
                        </table>
                        <table cellpadding="0" cellspacing="0" border="0">
                        	<tr>
                                <td style="padding-right:10px;"><input type="Submit" name="button_submit" id="button_submit" value="Search" /></td>
                                <?php if ($userCountry['iso'] == 'GB') : ?>
                                    <td><a href="search-tips.php">Quick search tips</a> <span class="grey">|</span> <a href="search-by-church.php">Search by church</a> <span class="grey">|</span> <a href="search.php?post_type=offered">Advanced  search</a></td>
                                <?php else: ?>
                        		    <td><a href="search-tips.php">Quick search tips</a></td>
                                <?php endif; ?>
                        	</tr>
                        </table>
					</div>
					<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>		
				</form>
				<!-- Quick links box -->

   <!-- Conditional date display -->
    <?php $date = "2018-08-17"; 
          $unix_date = strtotime ($date); 
          if ( $unix_date > time() ) { ?>
               <div class="mt0" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:500px;">
                <p class="mt0 mb5" style="font-size:12px;"><b>Please note:</b></span><br /></p>
                <p class="mt0 mb5">The photo upload issue which affected some members has been fixed now.</h>
                <p class="mt0 mb0">Thank you to all who reported the problem - CFS admin, 17:40 13th August</p>
               </div><br/>
       <?php } ?>

                <?php if ($userCountry['iso'] == 'GB') : ?>
                    <div class="box_grey mb10">
                    <div class="tr"><span class="l"></span><span class="r"></span></div>
                        <div class="tr"></div>
                        <div class="mr">
                            <div id="quick_links">
                                <h2 class="mt0">
                                    Quick Links
                                </h2>Click the map to see accommodation wanted and offered.<br>
                                <span class="grey">Posting an advert helps you get the most from CFS.<br>
                                (See <a href="frequently-asked-questions.php">Frequently Asked Questions</a>).</span>
                                <p>
                                </p>
                                <div id="cfs_poster" align="top" style="margin-top:63px;background-image:url(images/front_page_poster.gif);">
                                    <div>
                                        <a href="use-cfs-in-your-church.php" id="cfs_poster_link" name="cfs_poster_link"></a>
                                    </div>
                                </div>
                            </div>
                            <div id="church_directory_map" style="background-position:&lt;?php print $defaultPos?&gt;px;">
                                <img src="images/spacer.gif" width="280" height="361" border="0" usemap="#Map"> <map name="Map" id="Map">
                                    <area shape="poly" coords="237,293,225,288,214,294,212,304,225,311,236,309,239,302" href="display.php?search_type=london&amp;area=Greater%20London" alt="Adverts in London" id="hotspot_london">
                                    <area shape="poly" coords="35,141,8,164,1,176,7,187,23,193,34,182,40,194,61,197,78,182,67,155,60,144" href="display.php?search_type=map&amp;area=Northern%20Ireland" alt="Adverts in Northern Ireland" id="hotspot_northern_ireland">
                                    <area shape="poly" coords="39,11,27,36,24,65,38,88,66,141,88,167,139,157,167,119,161,81,178,49,161,0,65,0" href="display.php?search_type=map&amp;area=Scotland" alt="Adverts in Scotland" id="hotspot_scotland">
                                    <area shape="poly" coords="200,167,180,135,166,118,147,149,161,179,206,176" href="display.php?search_type=map&amp;area=North%20East" alt="Adverts in the North East" id="hotspot_north_east">
                                    <area shape="poly" coords="233,209,212,175,161,178,151,192,164,201,161,207,167,214,178,225,193,223,212,219,224,220" href="display.php?search_type=map&amp;area=North" alt="Adverts in the North" id="hotspot_north">
                                    <area shape="poly" coords="145,148,126,160,92,183,106,220,132,223,144,236,166,225,159,204,147,191,159,179" href="display.php?search_type=map&amp;area=North%20West" alt="Adverts in the North West" id="hotspot_north_west">
                                    <area shape="poly" coords="143,235,126,220,91,219,82,244,60,285,67,294,127,307,147,287,133,275,131,253,134,239" href="display.php?search_type=map&amp;area=Wales" alt="Adverts in Wales" id="hotspot_wales">
                                    <area shape="poly" coords="181,278,190,262,174,241,168,223,131,237,130,274,142,284" href="display.php?search_type=map&amp;area=West%20Midlands" alt="Adverts in the West Midlands" id="hotspot_west_midlands">
                                    <area shape="poly" coords="189,279,217,268,217,251,238,246,237,232,235,216,217,221,187,226,163,211,170,227,182,253" href="display.php?search_type=map&amp;area=East%20Midlands" alt="Adverts in the East Midlands" id="hotspot_east_midlands">
                                    <area shape="poly" coords="245,237,279,243,280,269,267,280,227,275,217,269,217,250,241,243" href="display.php?search_type=map&amp;area=East" alt="Adverts in the East" id="hotspot_east">
                                    <area shape="poly" coords="179,280,148,285,128,307,95,312,38,357,122,357,177,336,180,305" href="display.php?search_type=map&amp;area=South%20West" alt="Adverts in the South West" id="hotspot_south_west">
                                    <area shape="poly" coords="267,281,226,277,216,270,189,281,180,282,180,334,191,341,252,330,279,312" href="display.php?search_type=map&amp;area=South%20East" alt="Adverts in the South East" id="hotspot_south_east">
                                </map> <script language="javascript" type="text/javascript">
                                        var x = document.getElementsByTagName("area");
                                                    for (var i=0; i < x.length; i++) {
                                                        target = x[i];
                                                        target.onmouseover = doOver;
                                                        target.onmouseout = doOut;
                                                    }
                                </script>
                            </div>
                            <div class="cc0">
                            </div>
                        </div>
                        <div class="br"></div>
   <div class="br"><span class="l"></span><span class="r"></span></div>
                    </div>
                <?php else: ?>
    		       <div id="quickLinks">
    			    <h2>Quick Links</h2>
                        <div class="offered">
                            <div class="inner"></div>
                            <div class="clearfix"></div>
                        </div>
                        <hr />
                        <div class="wanted">
                            <div class="inner"></div>
                            <div class="clearfix"></div>
                        </div>
    				</div>
                <?php endif; ?>
			</div>
		<div id="columnRight">
            <?php print $theme['side']; ?>
            <?php if ($userCountry['iso'] == 'GB') : ?>
                <p class="mt0 mb20" align="justify">Christian Flatshare is supported by <a href="churches-using-cfs.php?area=Greater%20London#directory"><?php print $stats['churches']?></a> churches and Christian organisations, together with tens of thousands of CFS members.</p>
            <?php else: ?>
                <p class="mt0 mb20" align="justify">Christian Flatshare is supported by <a href="churches-using-cfs-intl.php"><?php print $stats['churches']?></a> churches and Christian organisations, together with tens of thousands of CFS members.</p>	
            <?php endif; ?>
<div class="fb-like-container-home"><div class="fb-like" data-href="http://www.christianflatshare.org" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-action="recommend"></div></div>

<!--		
<p class="mt20 mb0" align="left">
	<b>Update</b> - 
<a href="facebook-your-ad.php">Enhance with Facebook!</a><br /><br />
Christian Flatshare has been extended to serve the church in America, Canada, Australia, Ireland and South Africa.<br /><br />

 <span class="grey">Christian Flatshare support, 14th April.</span>  -->

<!--  <p class="mb0" style="font-size:12px;"><br /><strong>Bitcoin Donations (BTC)</p>
        <p class="mt5">If you would like to support CFS by sending Bitcoin, we welcome that to the address below - thank you!<br /><br />1DtZpWPMaZwBjcwGtT9fvFmaZB9xcaE4B5</p>
       </p>
-->

 <p class="mb0" style="font-size:12px;"><br /><strong>Donate to CFS!</p>

                        <p class="mt5 mb15" align="left">Please help support Christian Flatshare.</p>
                        <p class="mt0 mb0">
                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="5647421">
<input type="image" align="left" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The      safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="30">
</form>
 <!--
<div style="font-size:16px;margin:0 auto;width:240px" class="blockchain-btn"
     data-address="1DtZpWPMaZwBjcwGtT9fvFmaZB9xcaE4B5"
     data-shared="false">
    <div class="mt20 blockchain stage-begin">
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
</p>


				
		</div>


			<div class="cc0"><!----></div>
            <?php if ($userCountry['iso'] == 'GB') : ?>
                <?php print  loadBanner("728",NULL,TRUE); ?>
            <?php else: ?>
                <?php print loadBanner("728", NULL, TRUE, 0, $userCountry['iso']) ?>
            <?php endif; ?>
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
