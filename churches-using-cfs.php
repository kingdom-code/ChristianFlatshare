<?php
session_start();
    
// Autoloader
require_once 'web/global.php';

connectToDB();
	
	
  $result = mysqli_query($GLOBALS['mysql_conn'], "select count(church_name) from cf_church_directory;");
	$stats['churches'] = cfs_mysqli_result($result,0,0);
	if (isset($_GET['area'])) { $area = $_GET['area']; } else { $area = NULL; }
	
	if ($area) {
		$churchList = "";
		// Load all churches in the supplied area
		$query = "
			select * from cf_church_directory where substring_index(postcode,' ',1) in (
				select distinct postcode from cf_jibble_postcodes where area = '".$area."'
			) order by substring_index(church_location,' ',1),church_name;
		";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
			$class = "trOdd";
			while($row = mysqli_fetch_assoc($result)) {
				$churchList .= '<tr class="'.$class.'">';
				// First cell, link and description
				$churchList .= '<td>';
				if ($row['church_url']) { $churchList .= '<a href="http://'.$row['church_url'].'" target="_blank">'; }
				$churchList .= $row['church_name'];
				if ($row['church_url']) { $churchList .= '</a>'; }
				if ($row['church_description']) { $churchList .= ', '.$row['church_description']; }
				$churchList .= '</td>';
				// Second cell, location
				$churchList .= '<td><a href="#" onclick="return showMap(\''.addslashes($row['church_name']).'\',\''.addslashes($row['church_description']).'\',\''.$row['church_location'].'\',\''.$row['church_url'].'\',\''.$row['longitude'].'\',\''.$row['latitude'].'\');">'.$row['church_location'].'</a></td>';
				// Third cell, map link
				/*$churchList .= '<td><a href="#">Map</a></td>';*/
				$churchList .= '</tr>';
				$class = ($class == "trOdd")? "trEven" : "trOdd";
			}		
		}
	}
	
	// Load the list of areas that we have churches on
	$areaList = "";
	$query = "
		select 
			j.area,
			count(*) as `count`
		from cf_church_directory as `cd`
		left join cf_jibble_postcodes as `j` on substring_index(cd.postcode,' ',1) = j.postcode
		where cd.postcode != ''
		group by j.area
		order by j.area asc;	
	";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	while($row = mysqli_fetch_assoc($result)) {
		$areas[$row['area']] = $row['count'];
		$areaList .= '<li>';
		$areaList .= '<a href="'.$_SERVER['PHP_SELF'].'?area='.$row['area'].'#directory">';
		if ($row['area'] == $area) { $areaList .= '<strong>'; }
		$areaList .= $row['area'];
		if ($row['area'] == $area) { $areaList .= '</strong>'; }
		$areaList .= '</a> ('.$row['count'].')';
		$areaList .= '</li>';	
	}
	
	// The array used to calculate the position of the background image (if an area is selected)
	$position['northern_ireland'] = 1;
	$position['scotland'] = 2;
	$position['north_east'] = 3;
	$position['north_west'] = 4;
	$position['north'] = 5;
	$position['east_midlands'] = 6;
	$position['west_midlands'] = 7;
	$position['wales'] = 8;
	$position['east'] = 9;
	$position['south_west'] = 10;
	$position['south_east'] = 11;
	$position['greater_london'] = 12;
	if ($area) {
		$defaultPos = $position[strtolower(preg_replace("/\s/","_",$area))] * 280 * -1;
	} else {
		$defaultPos = 0;
	}
	// Remove percistency (REMOVE THIS TO RETAIN PERSISTENCY)
	$defaultPos = 0;
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Churches supporting Christian Flatshare</title>
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
<script src="http://maps.google.com/maps?file=api&v=2&key=<?php print GOOGLE_MAPS_API_KEY?>" type="text/javascript"></script>
<script language="javascript" type="text/javascript">

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
	
	function $(obj) {
		return document.getElementById(obj);
	}
		
	/* Shows map for each church */
	function showMap(name,description,location,url,long,lat) {
		
		// Center the div
		var arrayPageSize = getPageSize();
		var arrayPageScroll = getPageScroll();
		var church_map_canvas = $('church_map_canvas');
		
		var top = arrayPageScroll[1] + ((arrayPageSize[3] - church_map_canvas.offsetHeight) / 2) + 'px';
		var left = ((arrayPageSize[0] - church_map_canvas.offsetWidth) / 2) + 'px';
		
		//alert(top+" : "+left);
		
		church_map_canvas.style.top = top;
		church_map_canvas.style.left = left;
		
		// Draw the google map
		var bounds = new GLatLngBounds();
		
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("gmap"));
			map.setCenter(new GLatLng(0,0),0); // Necessary to perform a map.setCenter() call before starting to add markers
			
			// Add the map controls
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
			
			// Create the church icon
			var churchIcon = new GIcon();
			churchIcon.image = "images/gmap-church-icon.png";
//			churchIcon.iconSize = new GSize(73, 56);
//			churchIcon.iconAnchor = new GPoint(36, 56);
//			churchIcon.infoWindowAnchor = new GPoint(32, 26);
					churchIcon.iconSize = new GSize(51, 39);
					churchIcon.iconAnchor = new GPoint(36, 56);
					churchIcon.infoWindowAnchor = new GPoint(32, 26);			
			churchIcon.shadow = "http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-church-icon-shadow.png";
//			churchIcon.shadowSize = new GSize(73, 56);
					churchIcon.shadowSize = new GSize(52, 39);			
				
			point = new GLatLng(lat,long);	
			map.addOverlay(new GMarker(point,churchIcon));
			
			// Set the map centre
			map.setCenter(point,15);
			
			//map.setZoom(<?php print $zoomLevel?>);
		}	
		
		// Change the title and details of the map canvas
		$('church_map_title').innerHTML = name + ', ' + description;	
		$('church_map_location').innerHTML = location;
		
		return false;
		
	}
	
	function hideMap() {
		$('church_map_canvas').style.left = "-9999px";
		return false;
	}
	
	//
	// getPageScroll()
	// Returns array with x,y page scroll values.
	// Core code from - quirksmode.org
	//
	function getPageScroll(){
	
		var yScroll;
	
		if (self.pageYOffset) {
			yScroll = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
			yScroll = document.documentElement.scrollTop;
		} else if (document.body) {// all other Explorers
			yScroll = document.body.scrollTop;
		}
	
		arrayPageScroll = new Array('',yScroll) 
		return arrayPageScroll;
	}
	
	//
	// getPageSize()
	// Returns array with page width, height and window width, height
	// Core code from - quirksmode.org
	// Edit for Firefox by pHaez
	//
	function getPageSize(){
		
		var xScroll, yScroll;
		
		if (window.innerHeight && window.scrollMaxY) {	
			xScroll = document.body.scrollWidth;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}
		
		var windowWidth, windowHeight;
		if (self.innerHeight) {	// all except Explorer
			windowWidth = self.innerWidth;
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}	
		
		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else { 
			pageHeight = yScroll;
		}
	
		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){	
			pageWidth = windowWidth;
		} else {
			pageWidth = xScroll;
		}
	
	
		arrayPageSize = new Array(pageWidth,pageHeight+40,windowWidth,windowHeight) 
		return arrayPageSize;
	}
	
</script>
<style type="text/css">
<!--
a:link {
	text-decoration: none;
}
a:visited {
	text-decoration: none;
}
a:hover {
	text-decoration: underline;
	color: #0033FF;
}
a:active {
	text-decoration: none;
}
-->
</style><!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			<div id="church_map_canvas" style="top:0px;left:-9999px;">
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
					<tr>
						<td>
							<h1 id="church_map_title">Church name, description</h1>
							<p id="church_map_location">Location</p>
						</td>
						<td align="right" valign="top"><a href="#" onclick="return hideMap();">Hide this map</a></td>
					</tr>
				</table>
				<div id="gmap">Map of church loading...</div>
			</div>
			<div id="header_churches_using_cfs" class="header">
				<h1>Churches using Christian Flatshare </h1>
				<h2>Christian Flatshare church directory...</h2>
			</div>	
			
			<div class="two_column_canvas mb20">
				<div class="col1 ">		
					<p align="justify" class="mt0 mb10"><strong><?php print $stats['churches']?>
			     churches and Christian organisations support CFS, together with tens of thousands of Christian Flatshare members. </strong></p>
					<p align="justify" class="mt0 mb10">If your church or organisation is supportive of CFS and would like be included in CFS' church directory, then please <a href="use-cfs-in-your-church.php">add your church</a>, or <a href="contact-us.php">contact us</a> to let us know. Maybe send your photo for this page?</p>&nbsp;<img src="images/static_page_photos/church.jpg" class="photo_border"/>
				 <br /><a name="directory"></a>
				</div>
					
				<div  class="col2">
					<p class="mt0">Being included in CFS' church directory indicates to CFS' visitors that:</p>
						<li>
						  <p align="justify" class="mb10">Your church or organisation may be using CFS internally for accommodation, possibly in addition to other websites or other traditional methods. </p>
						</li>
						<li>
						  <span align="justify">Your church or organisation is supportive of CFS' vision and thinks that CFS is likely to be a good place to visit to look for accommodation.</span>
						</li>
					

					<p align="justify"><span class="mb0">Churches and organisations included in CFS' directory are shown both on the accommodation maps, with links to those church and organisation websites and in the CFS church directory. We hope that this will help those looking for accommodation to connect with those churches.</span></p>
					<p align="justify" class="mt0">The CFS directory helps to make CFS accountable to those who continue allow CFS to include them. <br />
					  <br />			      
				    CFS wants to used purposefully by church leaders, to serve the church family with their support. We are thankful, and on behalf of the many thousands who have used CFS, to those church leaders who are giving their support to CFS. </p>
			  </div>
				<div class="clear"><!----></div>
			</div>
			<div style="width:440px;margin-right:19px;float:left;">
			
				<div class="box_grey mb10">
					<div class="tr"><span class="l"></span><span class="r"></span></div>
					<div class="mr">
						<h2 class="m0">CFS Directory</h2>
						
						<div id="church_directory_map" style="background-position:<?php print $defaultPos?>px;">
							<img src="images/spacer.gif" width="280" height="361" border="0" usemap="#Map" />
							<map name="Map" id="Map">
							<?php if ($areas['Greater London']) { ?>
							<area shape="poly" coords="237,293,225,288,214,294,212,304,225,311,236,309,239,302" href="churches-using-cfs.php?area=Greater London#directory" alt="Churches and Organisations that support Christian Flatshare in London" id="hotspot_london"/>
							<?php } ?>
							<?php if ($areas['Northern Ireland']) { ?>						
							<area shape="poly" coords="34,141,7,164,0,176,6,187,22,193,33,182,39,194,60,197,77,182,66,155,59,144" href="churches-using-cfs.php?area=Northern Ireland#directory" alt="Churches and Organisations that support Christian Flatshare in Northern Ireland" id="hotspot_northern_ireland" />
							<?php } ?>
							<?php if ($areas['Scotland']) { ?>						
							<area shape="poly" coords="39,12,27,37,24,66,38,89,66,142,88,168,139,158,167,120,161,82,178,50,161,1,65,1" href="churches-using-cfs.php?area=Scotland#directory" alt="Churches and Organisations that support Christian Flatshare in Scotland" id="hotspot_scotland"/>
							<?php } ?>
							<?php if ($areas['North East']) { ?>						
							<area shape="poly" coords="200,167,180,135,166,118,147,149,161,179,206,176" href="churches-using-cfs.php?area=North East#directory" alt="Churches and Organisations that support Christian Flatshare in the North East" id="hotspot_north_east"/>
							<?php } ?>
							<?php if ($areas['North']) { ?>
							<area shape="poly" coords="233,209,212,175,161,178,151,192,164,201,161,207,167,214,178,225,193,223,212,219,224,220" href="churches-using-cfs.php?area=North#directory" alt="Churches and Organisations that support Christian Flatshare in the North" id="hotspot_north" />
							<?php } ?>
							<?php if ($areas['North West']) { ?>
							<area shape="poly" coords="147,148,128,160,94,183,108,220,134,223,146,236,168,225,161,204,149,191,161,179" href="churches-using-cfs.php?area=North West#directory" alt="Churches and Organisations that support Christian Flatshare in the North West" id="hotspot_north_west" />
							<?php } ?>
							<?php if ($areas['Wales']) { ?>
							<area shape="poly" coords="143,235,126,220,91,219,82,244,60,285,67,294,127,307,147,287,133,275,131,253,134,239" href="churches-using-cfs.php?area=Wales#directory" alt="Churches and Organisations that support Christian Flatshare in Wales" id="hotspot_wales" />
							<?php } ?>
							<?php if ($areas['West Midlands']) { ?>
							<area shape="poly" coords="181,278,190,262,174,241,168,223,131,237,130,274,142,284" href="churches-using-cfs.php?area=West Midlands#directory" alt="Churches and Organisations that support Christian Flatshare in the West Midlands" id="hotspot_west_midlands" />
							<?php } ?>
							<?php if ($areas['East Midlands']) { ?>
							<area shape="poly" coords="189,279,217,268,217,251,238,246,237,232,235,216,217,221,187,226,163,211,170,227,182,253" href="churches-using-cfs.php?area=East Midlands#directory" alt="Churches and Organisations that support Christian Flatshare in the East Midlands" id="hotspot_east_midlands" />
							<?php } ?>
							<?php if ($areas['East']) { ?>
							<area shape="poly" coords="245,237,279,243,280,269,267,280,227,275,217,269,217,250,241,243" href="churches-using-cfs.php?area=East#directory" alt="Churches and Organisations that support Christian Flatshare in the East" id="hotspot_east" />
							<?php } ?>
							<?php if ($areas['South West']) { ?>
							<area shape="poly" coords="179,280,148,285,128,307,95,312,38,357,122,357,177,336,180,305" href="churches-using-cfs.php?area=South West#directory" alt="Churches and Organisations that support Christian Flatshare in the South West" id="hotspot_south_west" />
							<?php } ?>
							<?php if ($areas['South East']) { ?>
							<area shape="poly" coords="267,281,226,277,216,270,189,281,180,282,180,334,191,341,252,330,279,312" href="churches-using-cfs.php?area=South East#directory" alt="Churches and Organisations that support Christian Flatshare in the South East" id="hotspot_south_east" />
							<?php } ?>
						  </map>
							<script language="javascript" type="text/javascript">
								var x = document.getElementsByTagName("area");
								for (var i=0; i < x.length; i++) {
									target = x[i];
									target.onmouseover = doOver;
									target.onmouseout = doOut;
								}
							</script>
					  </div>					
						
						<p>Please choose an area from the list below to display  supporting churches and organisations:</p>
						<ul class="church_list">
							<?php print $areaList?>
						</ul>
						<div class="clear"><!----></div>
					</div>
					<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
			</div>
			<div style="float:left;width:389px;">
				<?php if ($churchList) { ?>			
				<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="greyTable">
					<tr>
						<th colspan="3">Churches and Organisations in <?php print $area?><br/><span style="font-weight:normal;">Click on the area link for a map</span></th>
					</tr>
					<?php print $churchList?>
				</table>
				<?php } ?>
			</div>
			<div class="clear"><!----></div>
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
