<?php

// Autoloader
require_once 'web/global.php';

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

$result = mysqli_query($GLOBALS['mysql_conn'], "SELECT count(church_name) FROM cf_church_directory");
$stats['churches'] = cfs_mysqli_result($result,0,0);

// The default position of the map 
$defaultPos = 0;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Christian Flatshare - Supported by hundreds of churches nationwide - Christian Accommodation</title>
<meta name="keywords" content="christian accommodation,flatshare,houseshare,roommate,roomshare,church,CFS,letting,jesus,christ"/>
<meta name="description" content="Christian Flatshare - helping accommodation seekers connect with the local church. Flatshare, Family Share and Whole Place accommodation adverts."/> 

<!-- FACEBOOK OG TAGS -->
<meta property="og:title" content="Christian Flatshare" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://www.christianflatshare.org" />
<meta property="og:image" content="http://www.christianflatshare.org/images/CFS_fb_like.jpg" />
<meta property="og:description" content="Connecting accommodation seekers with the local church community: Flat/House share, Family share and Whole place ads. Supported by over 400 churches." /> 
<meta property="og:site_name" content="Christian Flatshare" />
<meta property="fb:admins" content="848085692" />
<!-- END OF FACEBOOK TAGS -->


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

</script>
<script language="javascript" type="text/javascript" src="includes/ls_crossfade.js"></script>
<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->

    <!-- GOOGLE MAPS API v3  -->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
    <script src="scripts/search.js"></script>
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
		  <!-- EMERCENCY MESSAGE START 		
				<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00; width:475px;">
<strong>Christian Flatshare network issues - 8th May, 22.25</strong><br />
Our intenet host is currently experiencing network difficulties and are working to resolve them as soon as possible.
          </div><br />
		  <div>&nbsp;</div>
		   EMERCENCY MESSAGE END -->
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
							<td width="300">Location: <span class="grey" id="location_desc">city, town, tube station, postcode</span></td>
							<!--<td width="33">&nbsp;</td>	-->
						</tr>
						 </table>
						<table cellpadding="0" cellspacing="0" class="mt0 mb10">
						<tr>
							<tr>
							<td width="200" style="padding-right:4px;"><input name="place" id="searchTextField" type="text" value="" style="width:220px;"/></td>
							<td alight="left"><?php print createDropDown("radius",getMilesArray(),DEFAULT_RADIUS)?><span class="grey" id="radius_label"></span></td>
								<td>&nbsp;</td>
								<td alight="left" height="20"><span id="radius_search">Search radius</span></td>								
							</tr>
					  </table>
					  
					  
				
						<table cellpadding="0" cellspacing="0" border="0">
							<tr>
                                <td style="padding-right:10px;"><input type="Submit" name="button_submit" id="button_submit" value="Search" /></td>
								<td><a href="search-tips.php">Quick search tips</a></td>
							</tr>
						</table>
					</div>
					<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>		
				</form>
				<!-- Quick links box -->
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
       
			</div>
		<div id="columnRight">
            <?php print $theme['side']; ?>
			<p class="mt0 mb20" align="justify">Christian Flatshare is supported by <a href="churches-using-cfs-intl.php"><?php print $stats['churches']?></a> churches and Christian organisations, together with tens of thousands of CFS members.</p>		

<div class="fb-like-container-home"><div class="fb-like" data-href="http://www.christianflatshare.org" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial" data-action="recommend"></div></div>
		
<p class="mt20 mb0" align="left">
	<b>Update</b><br />
Christian Flatshare has been upgraded to support advert posting in America, Canada, Australia and South Africa.<br /><br />If you encounter any problems using CFS, please let us know.<br />

<span class="grey">Christian Flatshare support, 14th April.</span> 
				</p>

				
			<p class="mt20 mb10" align="right">
				<span class="grey">Donate to help support Christian Flatshare.</span> </p>
			<p class="mt10 mb0">				
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="5647421">
<input type="image" align="right" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
</p>
<!--				
				<p align="center" style="padding-top:40px;"><?php print $stats['churches']?> Churches, Christian Organisations and Student Groups support Christian Flatshare, together with thousands of CFS members.</p>
-->


 <?php if (isset($_SESSION['u_id']) && !$_SESSION['u_id']) { ?>
<!--			<table style="min-height:425px; height:425px; width:100%;border:0px;padding:0;spacing:0">
			<tr>
			 <td valign="bottom">
				Advertise your church ministry or event on CFS, free of charge. See <a href="advertising.php">advertising</a>.
			 </td>
			</tr>
		</table>
											
<p style="padding-top:70px;" align="center" >Advertise your church ministry or event on CFS, free of charge. See <a href="advertising.php">advertising</a>	.</p> -->

		<?php } ?>



				
		</div>

			<div class="cc0"><!----></div>
			<?php print  loadBanner("728", NULL, TRUE, 0, $userCountry['iso']) ?>

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
