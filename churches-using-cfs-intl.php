<?php

use CFS\ChurchDirectory\CFSChurchDirectory;

session_start();

// Autoloader
require_once 'web/global.php';

$CFSChurchDirectory = new CFSChurchDirectory();
$CFSChurchDirectory->setCountry(getCurrentCountry());

$total = $CFSChurchDirectory->getNumberOfChurches();
$regions = $CFSChurchDirectory->getRegions();

$current_region = (isset($_GET['region'])) ? $_GET['region'] : $CFSChurchDirectory->getDefaultRegionForCountry();

$churches = $CFSChurchDirectory->getChurchesForRegion($current_region);
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
<!-- GOOGLE MAPS API v3  -->
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
<script src="scripts/directory.js"></script>
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
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
					<p align="justify" class="mt0 mb10"><strong><?php print $total; ?>
			     churches and Christian organisations around the world support CFS, together with tens of thousands of Christian Flatshare members. </strong></p>
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
            <div class="">
                <?php print $twig->render('churchDirectoryList.twig', array('churches' => $churches, 'regions' => $regions, 'current_region' => $current_region)); ?>
            </div>
			<div class="clear"><!----></div>
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
