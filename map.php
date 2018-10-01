<?php

// Autoloader
require_once 'web/global.php';

	$showForm = TRUE;
	$quick_summary = "";
	$debug = NULL;
  
	// Redirect if no data has been sent
	if (!$_GET) {
		header("location:index.php"); 
		exit;
	}
	
	// If this page is called with a single offered id, create the summary and the map
	if (isset($_GET['offered_id'])) { 
	
		$action = "single";
		$offered_id = $_GET['offered_id'];
		$query  = "select o.*,
      		       (CASE IFNULL(o.town_chosen,'NULL')
			        WHEN 'NULL' THEN j.town 
			        ELSE o.town_chosen
			        END) as town,DATEDIFF(curdate(),created_date) as `ad_age`  ";
		// If user is logged on, get ad "saved" status
		if (isset($_SESSION['u_id'])) {
			$query .= ", s.active as `active` ";
		}
		$query .= "";
		$query .= "
			from cf_offered as `o` 
			left join cf_jibble_postcodes as `j` on SUBSTRING_INDEX(o.postcode,' ',1) = j.postcode 
		";
		// If user is logged on, get ad "saved" status
		if (isset($_SESSION['u_id'])) {
			$query .= "
				left join cf_saved_ads as `s` 
				on s.ad_id = o.offered_id and 
				s.post_type = 'offered' and 
				s.user_id = '".$_SESSION['u_id']."' 
			";
		}
		
		$query .= "
			where o.offered_id = '".$offered_id."'
		";
		$debug .= debugEvent("Selection query",$query); 
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		$summary = createSummaryV2($ad,"offered","odd",FALSE,FALSE,$_GET['t']);
	
		$adLink = '?offered_id='.$_GET['offered_id'];
	
	}
	
	// For wanted ads, we need to map a circle
	if (isset($_GET['wanted_id'])) {
		
		$action = "area";
		$wanted_id = $_GET['wanted_id'];
		$query = "
			select 
			w.*,
			j.longitude,
			j.latitude,
			w.longitude as new_longitude,
			w.latitude as new_latitude,
			j.town,
			DATEDIFF(curdate(),w.created_date) as `ad_age`  
			from cf_wanted as `w` 
      
			left join cf_jibble_postcodes as `j` on w.postcode = j.postcode 
			where w.wanted_id = '".$wanted_id."'";
		$debug .= debugEvent("Selection query",$query); 
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		$summary = createSummaryV2($ad,"wanted","odd",FALSE,FALSE,$_GET['t']);
    
    $ad['latitude'] = (empty($ad['latitude']))? $ad['new_latitude'] : $ad['latitude'];
    $ad['longitude'] = (empty($ad['longitude']))? $ad['new_longitude'] : $ad['longitude'];
	}
	
	// If one or more offered_id's have been sent using $_POST
	if (isset($_REQUEST['ad'])) {
	
		$adLink = "?ad=";
		$action = "multiple";
		$sqlWhere = "";
		// If the id's were sent using POST
		if (isset($_POST['ad'])) {
			$tempArray = $_POST['ad'];
		} else {
			$tempArray = explode(",",$_GET['ad']);
		}
		foreach($tempArray as $id) {
			$sqlWhere .= $id.",";
			$adLink .= $id.",";
		}
    
		$adLink = substr($adLink,0,-1);
		$sqlWhere = substr($sqlWhere,0,-1);
		$query = "
			select 
			o.*,
			(CASE IFNULL(o.town_chosen,'NULL')
			   WHEN 'NULL' THEN j.town 
			   ELSE o.town_chosen
			 END) as town,
			j.postcode,
			(select count(*) from cf_photos where post_type = 'offered' and ad_id = o.offered_id) as `photos`,
			(select DATEDIFF(curdate(),last_login) from cf_users where cf_users.user_id = o.user_id) as `last_login_days`
			from cf_offered as `o`
			left join cf_jibble_postcodes as `j` on SUBSTRING_INDEX(o.postcode,' ',1) = j.postcode
			where o.offered_id in (".$sqlWhere.")
			and o.published = '1' and o.suspended = '0';
		";
		$debug .= debugEvent("Multiple markers query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	}
	
	// Load all the Churches and place icons on the map
	$query = "select * from cf_church_directory where longitude != '' and church_type = 'C'";
	$churchResult = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	$query = "select * from cf_church_directory where longitude != '' and church_type = 'O'";
	$organisationResult = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	$query = "select * from cf_church_directory where longitude != '' and church_type = 'S'";
	$studentResult = mysqli_query($GLOBALS['mysql_conn'], $query);
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Display on a map - Christian Flatshare</title>
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

        <!-- GOOGLE MAPS API v3  -->
        <!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script> -->
        <script src="https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key=<?php print GOOGLE_CLOUD_BROWSER_KEY?>"></script>
        <script type="text/javascript" src="includes/google/markerclusterer.js"></script>
        <script src="scripts/map.js"></script>

<script type="text/javascript">

 // Global variables		
 var map;
 var mgr;
 var church_mgr;
 var organisation_mgr;
 var fellowship_mgr;
 var ad_markers = [];
 var church_markers = [];
 var organisation_markers = [];
 var student_markers = [];
 var markers = [];
 var icon;
 var bounds = new google.maps.LatLngBounds();			
 var posn = new google.maps.LatLng; 
// var marker;
 var openInfoWindow;
 
 var accomImage;
 var accomShadow;
 var churchImage;
 var churchShadow;
 var orgImage;
 var orgShadow;
 var studentImage;
 var studentShadow; 
 
 var areaCircle;
 var toggleOrgLayer = 1;
 var toggleStudentLayer = 1;
 var toggleChurchLayer = 1;
	
 var markerCluster = null;
	
 var churchMarker;
 var orgMarker;
 var fellowshipMarker;
 var accomMarker;
 var infoWindow;
 var transitLayer = null;
 var bicyclingLayer = null;
 var currentLayer = null;
 // Use an arbitary LatLng to create the map
 var myLatlng = new google.maps.LatLng(51.1063,-0.12714000);
 var myOptions = {
      zoom : 14,
      center: myLatlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
			

	 var mcChurchOptions = {gridSize: 100, maxZoom: 15, styles: [{
 				height: 53,
				// The blue marker
				url: "/images/gmap_cluster_marker1.png",
				width: 53}]
			};	 
 	 var mcAdOptions = {gridSize: 50, maxZoom: 15, styles: [{
				height: 53,
				// The yellow marker
				url: "/images/gmap_cluster_marker2.png",
				width: 53}]
			};	 
 	 var mcOrgOptions = {gridSize: 50, maxZoom: 15, styles: [{
	      opt_textColor: 'white',
				height: 53,
				// The blue marker
				url: "/images/gmap_cluster_marker1.png",
				width: 53}]
			};
 	 var mcStudentOptions = {gridSize: 50, maxZoom: 15, styles: [{
				height: 53,
				// The blue marker
				url: "/images/gmap_cluster_marker1.png",
				width: 53}]
			};
			
						
 var accomImage = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-icon.png");
 var accomShadow = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-icon-shadow.png");
					

		 <?php if ($action == "multiple") { ?>			
			// Data for the ads
			var ad_layer = [{
			"": [0,17],
			"places": [
				/*
				 {
				"name"	:	"Pittsburgh Engineering Office",
				"posn"	:	[40.444541, -79.946254],
				"html"	:	""
				 }
				*/
			 <?php
				 $t = "";
				 while ($ad = mysqli_fetch_assoc($result)) {
					$t .= '{'."\n";
					$t .= '"lat" : '.$ad['latitude'].",\n";														
					$t .= '"long" : '.$ad['longitude'].",\n";		
					$t .= '"html" : "'.addslashes(createMapSummary($ad,"offered")).'"'.",\n";
					$t .= '"title" : "'.addslashes(strip_tags(getAdTitle($ad,"offered"))).'"'."\n";					
					$t .= '},';
				 }
				 $t = substr($t,0,-1);
				 echo $t;
				?>
				]
			 }];
			<?php } ?>


			// Data for churches
			var church_layer = [{
				"": [0,17],
				"places": [
					<?php
						$t = "";
						while($church = mysqli_fetch_assoc($churchResult)) {
							$t .= '{'."\n";
							$t .= '"lat" : '.$church['latitude'].",\n";														
							$t .= '"long" : '.$church['longitude'].",\n";				
							$t .= '"html" : "'.addslashes(createChurchSummary($church)).'"'.",\n";
							$t .= '"title" : "'.addslashes($church['church_name']).'\n'.addslashes($church['church_location']).'"'."\n";		
							$t .= '},';
						}
						$t = substr($t,0,-1);
						echo $t;		
					?>
				]			
			}];


			// Data for organisations
			var organisation_layer = [{
				"": [0,17],
				"places": [
					<?php
						$t = "";
						while($church = mysqli_fetch_assoc($organisationResult)) {
							$t .= '{'."\n";
 	 			     	$t .= '"lat" : '.$church['latitude'].",\n";														
							$t .= '"long" : '.$church['longitude'].",\n";	
							$t .= '"html" : "'.addslashes(createChurchSummary($church)).'"'.",\n";
							$t .= '"title" : "'.addslashes($church['church_name']).'\n'.strip_tags($church['church_location']).'"'."\n";									
							$t .= '},';
						}
						$t = substr($t,0,-1);
						echo $t;		
					?>
				]			
			}];
				
			// Data for student groups
			var student_layer = [{
				"": [0,17],
				"places": [
					<?php
						$t = "";
						while($church = mysqli_fetch_assoc($studentResult)) {
							$t .= '{'."\n";
					    $t .= '"lat" : '.$church['latitude'].",\n";														
							$t .= '"long" : '.$church['longitude'].",\n";	
							$t .= '"html" : "'.addslashes(createChurchSummary($church)).'"'.",\n";
							$t .= '"title" : "'.addslashes($church['church_name']).'\n'.addslashes($church['church_location']).'"'."\n";									
							$t .= '},';
						}
						$t = substr($t,0,-1);
						echo $t;		
					?>
				]			
			}];			
						

						
  function initialize() {
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
    transitLayer = new google.maps.TransitLayer();
    bicyclingLayer = new google.maps.BicyclingLayer();
		transitLayer.setMap(map);
    currentLayer = transitLayer;		
		 
		 
	 <?php if ($action == "single") { ?>	// If one offered advert
					
		 // Add a new point
		 var point = new google.maps.LatLng(<?php print $ad['latitude']?>,<?php print $ad['longitude']?>);
     //map, point, icon, shadow, title, contenthtml		 
     
		 var marker = new createMarker(map, point, accomImage, accomShadow, '<?php print addslashes(strip_tags(getAdTitle($ad,"offered"))); ?>', '<?php print trim(preg_replace('/\s+/', ' ', addslashes(createMapSummary($ad,"offered")))); ?>', true, true);

		// With a single item, initially show it and transition the map centre down a bit
    map.setCenter(new google.maps.LatLng(<?php print $ad['latitude']?>+.004,<?php print $ad['longitude']?>));


		 
 	 <?php }  else if ($action == "area") { ?>
		
		 var point = new google.maps.LatLng(<?php print $ad['latitude']; ?>,<?php print $ad['longitude']; ?>);
    
		 // Define circle options
     var circleOptions = {
      strokeColor: "#FF0000",
      strokeOpacity: 0.8,
      strokeWeight: 2.5,
      fillColor: "#FF0000",
      fillOpacity: 0.06,
      map: map,
      center: point,
      radius: <?php print $ad['distance_from_postcode']?>*1609 // miles to meters
     };
     areaCircle = new google.maps.Circle(circleOptions);
 	   marker = new createMarker(map, point, accomImage, accomShadow, ' <?php print strip_tags(getAdTitle($ad,"wanted"))?>', '<?php print trim(preg_replace('/\s+/', ' ', addslashes(createMapSummary($ad,"wanted")))); ?>',true,true);			
		 

		 // Set a custom  level
		 <?php
			$d = $ad['distance_from_postcode'];
			if ($d <= 2) {
				$Level = 13;
 			 } else if ($d > 2 && $d <5 ) {
				$Level = 12;
 			 } else if ($d >= 5 && $d < 10) {
				$Level = 11;
 			 } else if ($d == 10) {
				$Level = 10;
			 } else { 
				$Level = 9;
			 }
			?>
			map.setZoom(<?php print $Level?>);
		 
     
	
  

	   // Derrive map shift down coefficeient to allow the open infoWindow to display
		 <?php
		  $d = $ad['distance_from_postcode'];
			if ($d == 1) {
				$shift = .006;
 			 } else if ($d == 2) {
				$shift = 0.008;
 			 } else if ($d >= 3 && $d <= 7) {
				$shift = .024;
 			 } else if ($d >= 10 && $d <= 25) {
				$shift = .094;
 			 }
		 ?>

		// With a single item, initially show it and transition the map centre down a bit
    map.setCenter(new google.maps.LatLng(<?php print $ad['latitude']?>+<?php print $shift?>,<?php print $ad['longitude']?>));




 	 <?php } else if ($action == "multiple") { ?>
				
		setupAdMarkers();
				
	  <?php }?>
		
		
	  setupChurchMarkers();
	  setupStudentMarkers();		
    setupOrganisationMarkers();


   churchMarkerCluster = new MarkerClusterer(map, church_markers, mcChurchOptions);
   orgMarkerCluster = new MarkerClusterer(map, organisation_markers, mcOrgOptions);	 	 
   studentMarkerCluster = new MarkerClusterer(map, student_markers, mcStudentOptions);	 	 
  // adMarkerCluster = new MarkerClusterer(map, ad_markers, mcAdOptions);	 

  } // End intialise function



	function createMarker(map, point, icon, shadow, title, contenthtml, bounce, openinfo) {
	   if (bounce) {
		   animation = google.maps.Animation.DROP;
			} else {
	 		 animation = null;
     }
			
     var marker = new google.maps.Marker({
		  map: map,
      position: point,		 
		  draggable: false,		 
      icon: icon,
		  animation: animation,
      shadow: shadow,
		  title: title
 	   });
  	 markers.push(marker);		 
		 
     marker.infoWindow = new google.maps.InfoWindow();	
     marker.infoWindow.setContent(contenthtml);			 
     
     // Open when clicked
     google.maps.event.addListener(marker, 'click', function() {
         // Close all open InfoWindow
         for (var i = 0; i < markers.length; i++) {
             markers[i].infoWindow.close();
         }
         
         marker.infoWindow.open(map,marker);
     });
		 
		 
     // Close open windows when map clicked
     google.maps.event.addListener(map, 'click', function(event) {
         if (marker.infoWindow) {
             marker.infoWindow.close();
         }
     }); 
		 
		 
    <?php if ($action == "area") { ?>		 
		 	// Close open windows when cicle area is clicked
      google.maps.event.addListener(areaCircle, 'click', function(event) {
      if (marker.infoWindow) {
         marker.infoWindow.close();
      }
		 }); 
    <?php } ?>		 		
		 
		 if (openinfo) {
		  marker.infoWindow.open(map,marker);
 //     setTimeout(function () { marker.infoWindow.close(); }, 2500);			
		 }
     marker.setMap(map);
 		 return marker;
 	} // End createMarker

	
	function changeChurchLayer(toggle) {
	 var id = toggle.value
	 if (toggle.checked) {
		 for (var i = 0; i < church_markers.length; i++) {
       church_markers[i].setMap(map);   }
     churchMarkerCluster = new MarkerClusterer(map, church_markers, mcChurchOptions);			 
    } else {
		  for (var i = 0; i < church_markers.length; i++) {
      church_markers[i].setMap(null);    
			churchMarkerCluster.clearMarkers(church_markers[i]);
      }
	  }
	} // End changeChurchLayer
	 
	function changeStudentLayer(toggle) {
	 var id = toggle.value
	 if (toggle.checked) {
		 for (var i = 0; i < student_markers.length; i++) {
       student_markers[i].setMap(map);   }
     studentMarkerCluster = new MarkerClusterer(map, student_markers, mcStudentOptions);			 			 
    } else {
		  for (var i = 0; i < student_markers.length; i++) {
       student_markers[i].setMap(null);    }		 
			 studentMarkerCluster.clearMarkers(student_markers[i]);
	  }
	} // End changeStudentLayer

	function changeOrgLayer(toggle) {
	 var id = toggle.value
	 if (toggle.checked) {
		 for (var i = 0; i < organisation_markers.length; i++) {
       organisation_markers[i].setMap(map);   }
    } else {
		  for (var i = 0; i < organisation_markers.length; i++) {
       organisation_markers[i].setMap(null);    }		 
			 orgMarkerCluster.clearMarkers(organisation_markers[i]);
	  }
	} // End changeChurchLayer
 
		 
		 

	

	
	
  function transit() {
    clearLayer();
    transitLayer.setMap(map);
    currentLayer = transitLayer;
  }
  
  function bicycling() {
    clearLayer();
    bicyclingLayer.setMap(map);
    currentLayer = bicyclingLayer;
  }
      
  function clearLayer() {
    if (currentLayer != null) {
      currentLayer.setMap(null);
    }
  }
	
	

		
		
		function setupAdMarkers() {
				ad_markers.length = 0;
				for (var i in ad_layer) {
					var layer = ad_layer[i];
			//	var markers = [];
					for (var j in layer["places"]) {
					       var place = layer["places"][j];
					 	     posn = new google.maps.LatLng(place["lat"], place["long"]);
								 // Ryan D, 07-JUL-12
								 // A numeric test is required as, for some unknown reason, rubbish comes out of 
								 // layer["places"], which when added to bounds adds non-numerics to bounds(NaN)
								 // and causes problems.
  	      	     if (!isNaN(place["lat"]) && !isNaN(place["long"]))	
								 {							 
								   bounds.extend(posn);		
							     var marker = createMarker(map,posn,accomImage,accomShadow,place["title"],place["html"],false,false); 
									 marker.setZIndex(google.maps.Marker.MAX_ZINDEX);
						       ad_markers.push(marker);
								}
					}
					//mgr.addMarkers(markers, layer[""][0], layer[""][1]);
				}
				map.fitBounds(bounds);
			} // End setupAdMarkers
			


	function setupOrganisationMarkers() {
		// Create the Student group icons	(angelos named these fellowship)
		var orgImage = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-organisation-icon.png");
		var orgShadow = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-organisation-icon-shadow.png");		
		organisation_markers.length = 0;
		
		for (var i in organisation_layer) {
		  	  var layer = organisation_layer[i];
	//			  var markers = [];
			    for (var j in layer["places"]) {
						       var place = layer["places"][j];
					 	       var posn = new google.maps.LatLng(place["lat"], place["long"]);
					 	       var marker = createMarker(map,posn,orgImage,orgShadow,place["title"],place["html"],false,false); 
					 	       organisation_markers.push(marker);
					  }
				 	// church_mgr.addMarkers(markers, layer[""][0], layer[""][1]);
				}
      			//church_mgr.refresh();
   } // End setupChurchMarkers

	function setupStudentMarkers() {
		// Create the Student group icons	(angelos named these fellowship)
		var studentImage = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-fellowship-icon.png");
		var studentShadow = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-fellowship-icon-shadow.png");		
		student_markers.length = 0;
		
		for (var i in student_layer) {
		  	  var layer = student_layer[i];
	//			  var markers = [];
			    for (var j in layer["places"]) {
						       var place = layer["places"][j];
					 	       var posn = new google.maps.LatLng(place["lat"], place["long"]);
					 	       var marker = createMarker(map,posn,studentImage,studentShadow,place["title"],place["html"],false,false); 
					 	       student_markers.push(marker);
					  }
				 	// church_mgr.addMarkers(markers, layer[""][0], layer[""][1]);
				}
      			//church_mgr.refresh();
   } // End setupChurchMarkers


	function setupChurchMarkers() {
		var churchImage = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-church-icon.png");
		var churchShadow = new google.maps.MarkerImage("http://<?php print $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])?>/images/gmap-church-icon-shadow.png");		
		church_markers.length = 0;
		
		for (var i in church_layer) {
		  	  var layer = church_layer[i];
		//		  var markers = [];
			    for (var j in layer["places"]) {
						       var place = layer["places"][j];
					 	       var posn = new google.maps.LatLng(place["lat"], place["long"]);
					 	       var marker = createMarker(map,posn,churchImage,churchShadow,place["title"],place["html"],false,false); 
					 	       church_markers.push(marker);
					  }
				 	// church_mgr.addMarkers(markers, layer[""][0], layer["zoom"][1]);
				}
      			//church_mgr.refresh();
   } // End setupChurchMarkers
	
 google.maps.event.addDomListener(window, 'load', initialize);	
		

</script>
</head>
<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
		  <tr>
		    <td><h1 class="m0">View on map</h1></td>
				  <td align="right" class="mb0">
				    <?php 			

		 		// Next and previous functionality
				$debug .= debugEvent("$_SESSION variable 'result_set':",print_r($_SESSION['result_set'],true));
				if ($_GET['offered_id']) {
					echo nextPreviousAd("offered", $_GET['offered_id']);	
				} else { 
					echo nextPreviousAd("wanted", $_GET['wanted_id']);	
				}
				$debug .= debugEvent("REferer:",print_r($_SERVER,true)); 
				?>			    </td>
			  </tr>
		  </table>
		<?php if ($action == "single" || $action == "area") { ?>
		<?php print $summary?>
		<?php } ?>
		<table width="100%" class="mb0 mt5" valign="top" border="0" cellpadding="0" cellspacing="0">
		 <tr>
      <td>
			 <table border="0" cellpadding="0" cellspacing="0" width="350px">		
			  <tr>
				<td><strong>Display on map:</strong></td>
   			<td valign="top"><input onclick="return changeChurchLayer(this);" name="showchurches" type="checkbox" id="showchurches" value="1" checked="checked" /></td>
				<td valign="top"><label for="showchurches">Churches</label></td>		
          <td height="10"><input onclick="transit()" type="radio" name="layer" id="trButton" value="traffic" checked="checked"/></td>
          <td align="left"><label for="trButton">Tube / transit lines</label></td>								
				</tr>
				<tr>				 
				<td></td>
				<td valign="top"><input onclick="return changeOrgLayer(this);" name="showorgs" type="checkbox" id="showorgs" value="1" checked="checked" /></td>
				<td valign="top"><label for="showorgs">Organisations</label></td>						
          <td height="10"><input onclick="bicycling();" type="radio" name="layer" id="bcButton" value="bicycling"/></td>
          <td align="left"><label for="bcButton">Bicycle routes</label></td>				
				</tr>
				<tr>				
				<td></td>				
				<td valign="top"><input onclick="return changeStudentLayer(this);" name="showstudent" type="checkbox" id="showstudent" value="1" checked="checked" /></td>
				<td valign="top"><label for="showstudent">Student groups</label></td>			
          <td height="10"><input onclick="clearLayer();" type="radio" name="layer" id="clearButton" value="clear"/></td>
          <td align="left"><label for="clearButton">Normal</label></td>							
				</tr>
			</table>
			</td>
			<td align="left" valign="top">
			
	    </td>
		  <td align="right" valign="top">Please <a href="use-cfs-in-your-church.php" target="_blank">share</a> Christian Flatshare with your church!<br /><span class="grey">Click the orange man and drag him on the map to see<br />the Streetview. Click "X" in the top-right to exit.</span></td>
		  </tr>
		  </table>
			
	

		<div id="map_canvas" style="width: 100%; height: 450px; margin-top:10px;"></div>
		
					
		
		<?php if ($action == "multiple") { ?>
		<div id="map_quick_summary">
		  <table cellpadding="0" cellspacing="0" border="0" id="quick_summary">
		    <tr>
		      <th>Monthly price<br />
		        per bedroom</th>
					  <th>Date<br />
				    Available</th>
					  <th>Description</th>
					  <th>Photo?</th>
				  </tr>	
		    <?php 
				// We've already iterated the result set once (when creating the map markers)
				// so reset it to the first row
				mysqli_data_seek($result,0);
				// For each row, create a quick summary
				$counter = 0;
				while($ad = mysqli_fetch_assoc($result)) {
					echo createQuickSummary($ad,"offered",$counter);
					$counter++;
				}
				?>
		    </table>
		  </div>
		<?php } ?>

		 <table width="100%" class="mb0 mt5" valign="top" border="0" cellpadding="0" cellspacing="0">
		 <tr>
			<td align="left" valign="top"><span class="grey">
   <!--    Christian Flatshare's maps have recently been upgraded.<br />Please <a href="contact-us.php">let us know</a>	if you encounter any problems.</span> -->
	    </td>
		  <td align="right" valign="top">If you would like your church or organisation added the accommodation maps,<br /> church and organisation leaders please <a href="use-cfs-in-your-church.php">use this form</a> to inform us.</td>
		  </tr>
		  </table>


		<!--<p>
		<img src="images/church-icon.gif" width="36" height="26" hspace="4" vspace="0" align="absmiddle" />Churches listed in the <a href="churches-using-cfs.php">CFS directory</a>.&nbsp;&nbsp;&nbsp;
		<img src="images/organisation-icon.gif" width="34" height="26" hspace="4" align="absmiddle" />Organisations listed in the <a href="churches-using-cfs.php">CFS directory</a>.		</p>-->
		<p class="mb0"><a href="index.php">Back to welcome page</a></p>
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
</html>
