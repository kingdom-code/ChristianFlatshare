jQuery(document).ready(function($) {
    // Add Map
    var mapOptions = {
        center: new google.maps.LatLng(51.1063,-0.12714000),
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById('resultsMap'), mapOptions);
    
    // Map Bounds
    var bounds = new google.maps.LatLngBounds();
    
    // Setup MarkerClusterers
     var mcPropertiersOptions = {gridSize: 20, maxZoom: 15, styles: [{
          height: 53,
          // The yellow marker
          url: "/images/gmap_cluster_marker2.png",
          width: 53}]
      };
    var propertyMarkers = getPropertyMarkers(map, properties);
    var mcPropertiers = new MarkerClusterer(map, propertyMarkers, mcPropertiersOptions);
   
 
    var mcChurchesOptions = {gridSize: 100, maxZoom: 15, styles: [{
         height: 53,
         // The blue marker
         url: "/images/gmap_cluster_marker1.png",
         width: 53}]
     };
    var churchMarkers = getChurchMarkers(map, churches, 'church');
    var mcChurches = null;

    
    var mcOrganisationsOptions = {gridSize: 100, maxZoom: 15, styles: [{
         opt_textColor: 'white',
         height: 53,
         // The blue marker
         url: "/images/gmap_cluster_marker1.png",
         width: 53}]
     };
    var organisationsMarkers = getChurchMarkers(map, organisations, 'organisation');
    var mcOrganisations = null;

    
    var mcStudentGroupsOptions = {gridSize: 100, maxZoom: 15, styles: [{
         height: 53,
         // The blue marker
         url: "/images/gmap_cluster_marker1.png",
         width: 53}]
      };
    var studentGroupsMarkers = getChurchMarkers(map, studentGroups, 'studentGroup');
    var mcStudentGroups = null;
    
    $('.markers input:checked').each(function(key, value){
        var option = $(value).attr('id');
        
        if (option == 'showChurches') {
            showChurches();
        }
        else if (option == 'showOrganisations') {
            showOrganisations();
        }
        else if (option == 'showStudentGroups') {
            showStudentGroups();
        }
    });
    
    $('.markers input').change(function(){
        var option = $(this).attr('id');
        var state = $(this).is(':checked');
        
        console.log(option);
        console.log(state);
        
        if (option == 'showChurches') {
            (state == true) ? showChurches() : hideChurches();
            var key = 'show_churches';
        }
        else if (option == 'showOrganisations') {
            (state == true) ? showOrganisations() : hideOrganisations();
            var key = 'show_organisations';
        }
        else if (option == 'showStudentGroups') {
            (state == true) ? showStudentGroups() : hideStudentGroups();
            var key = 'show_student_groups';
        }
        var i = state ? 1 : 0;
        $.ajax({url: "ajaxResponses.php?target=saveDefault&key=" + key + "&value=" + i});
    });
    
    // Map Overlays
    var currentOverlay = null;
    
    var enabledOverlay = $('.overlays input:checked');
    if (enabledOverlay.length > 0) {
        var overlay = enabledOverlay.val();
        if (overlay == 'transit') {
            enableTransitOverlay();
        }
        else if (overlay == 'bicycling') {
            enableBicyclingOverlay();
        }
    }
    
    $('.overlays input').change(function(){
        var newOverlay = $('.overlays input:checked');
        if (newOverlay.length > 0) {
            var overlay = newOverlay.val();
            if (overlay == 'transit') {
                enableTransitOverlay();
            }
            else if (overlay == 'bicycling') {
                enableBicyclingOverlay();
            }
            else {
                clearOverlay();
            }
            
            $.ajax({url: "ajaxResponses.php?target=saveDefault&key=overlay&value=" + overlay});
        }
    });
    
    // Fit markers in map
    fitMap(map, bounds);
    
    // Info Window
    var infoWindow = new google.maps.InfoWindow();
    var originalPosition = null;
    var originalZoom = null;
    var detailOn = false;
    
    function getPropertyMarkers(map, properties) {
        var markers = [];
        
        var host = $(location).attr('host');
        var protocol = $(location).attr('protocol');
        
		var icon = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-icon.png");
		var shadow = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-icon-shadow.png");	
        
        for (var i = 0; i < properties.length; i++) {
            var property = properties[i];
            var position = new google.maps.LatLng(property.lat, property.lng);
            var marker = new google.maps.Marker({
              position: position,
              icon: icon,
              shadow: shadow,
              title: property.title,
              draggable: false,
              visible: true,
              html: property.html
            });
            
            google.maps.event.addListener(marker, 'click', function() {
                markerClicked(map, this);
                infoWindow.close();
            });
            
            bounds.extend(position);
            
            markers.push(marker);
        }
        
        return markers;
    }
    
    function getChurchMarkers(map, churches, icon) {
        var markers = [];
        
        var host = $(location).attr('host');
        var protocol = $(location).attr('protocol');
        
        if (icon == 'church') {
    		var icon = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-church-icon.png");
    		var shadow = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-church-icon-shadow.png");	
        }
        else if (icon == 'organisation') {
    		var icon = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-organisation-icon.png");
    		var shadow = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-organisation-icon-shadow.png");	
        }
        else if (icon == 'studentGroup') {
    		var icon = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-fellowship-icon.png");
    		var shadow = new google.maps.MarkerImage(protocol + '//' + host + "/images/gmap-fellowship-icon-shadow.png");	
        }
        
        for (var i = 0; i < churches.length; i++) {
            var church = churches[i];
            var position = new google.maps.LatLng(church.lat, church.lng);
            var marker = new google.maps.Marker({
              position: position,
              icon: icon,
              shadow: shadow,
              title: church.title,
              draggable: false,
              visible: true,
              html: church.html
            });
            
            google.maps.event.addListener(marker, 'click', function() {
                if (detailOn == true) return false;
                infoWindow.setContent(this.html);	
                infoWindow.open(map, this);
            });
            
            markers.push(marker);
        }
        
        return markers;
    }
    
    function fitMap(map, bounds) {
        map.setCenter(bounds.getCenter());
        map.fitBounds(bounds);
    }
    
    function setMarkers(map, properties) {
        var bounds = new google.maps.LatLngBounds();
        
        for (var i = 0; i < properties.length; i++) {
            var property = properties[i];
            var position = new google.maps.LatLng(property.lat, property.lng);
            var marker = new google.maps.Marker({
              position: position,
              map: map,
              title: property.title,
              draggable: false,
              animation: google.maps.Animation.DROP,
              visible: true,
              html: property.html
            });
            
            google.maps.event.addListener(marker, 'click', function() {
                markerClicked(map, marker);
            });
            
            bounds.extend(position);
        }
        
        map.setCenter(bounds.getCenter());
        map.fitBounds(bounds); 
    }
    
    function markerClicked(map, marker) {
        // Stop people clicking on a marker twice and setting
        // the original position to the zoomed in position
        //if (detailOn == true) return false;
        
        originalPosition = map.getCenter();
        originalZoom = map.getZoom();
        detailOn = true;
        
        map.setOptions({disableDefaultUI: true, draggable: false});
        
        var position = marker.getPosition();
        var newPosition = new google.maps.LatLng(position.lat(),position.lng());
        
        //map.panTo(newPosition);
        //map.setZoom(17);
        
        jQuery('#resultInfo').html('<div class="inner"><a href="#" class="closeInfo">Close<span></span></a>' + marker.html + '</div>');
        jQuery('#resultInfo').show();
        
        hideSaveAd();
        
        jQuery(".FBFriends img").tooltip({ position: { my: "center top", at: "center bottom+5" } });
        
        jQuery('.closeInfo').click(function(){
            resetMap();
            return false;
        });
    }
      
    function resetMap() {
        detailOn = false;
        map.panTo(originalPosition);
        map.setZoom(originalZoom);
        map.setOptions({disableDefaultUI: false, draggable: true});
        jQuery('#resultInfo').hide();
    }
    
    function clearOverlay() {
      if (currentOverlay != null) {
        currentOverlay.setMap(null);
      }
    }
    
    function enableTransitOverlay() {
        clearOverlay();
        transitLayer = new google.maps.TransitLayer();
        transitLayer.setMap(map);
        currentOverlay = transitLayer;
    }
    
    function enableBicyclingOverlay() {
        clearOverlay();
        bicyclingLayer = new google.maps.BicyclingLayer();
        bicyclingLayer.setMap(map);
        currentOverlay = bicyclingLayer;
    }
    
    function showChurches() {
        if (mcChurches == null) {
            mcChurches = new MarkerClusterer(map, churchMarkers, mcChurchesOptions);
        }
        else {
            mcChurches.addMarkers(churchMarkers);
        }
    }
    
    function hideChurches() {
        mcChurches.clearMarkers();
    }
    
    function showOrganisations() {
        if (mcOrganisations == null) {
            mcOrganisations = new MarkerClusterer(map, organisationsMarkers, mcOrganisationsOptions);
        }
        else {
            mcOrganisations.addMarkers(organisationsMarkers);
        }
    }
    
    function hideOrganisations() {
        mcOrganisations.clearMarkers();
    }
    
    function showStudentGroups() {
        if (mcStudentGroups == null) {
            mcStudentGroups = new MarkerClusterer(map, studentGroupsMarkers, mcStudentGroupsOptions);
        }
        else {
            mcStudentGroups.addMarkers(studentGroupsMarkers);
        }
    }
    
    function hideStudentGroups() {
        mcStudentGroups.clearMarkers();
    }
    
    function hideSaveAd() {
        $('a.save_ad_button').click(function(){
            var id = $(this).attr('id');
            var parts = id.split('_');
        
            $.ajax({url: "ajax-functions.php?action=save&post_type=" + parts[1] + "&id=" + parts[3]}).done(function(r) {
    			if (r.result == "insert_success" || r.result == "update_hidden") {
    				// Change the image to a green button
    				$('#'+id).children('img').attr('src', 'images/button_hidden_ad.gif');
    			}
                else if (r.result == "update_unsaved") {
                    $('#'+id).children('img').attr('src', 'images/button_hidesave_ad.gif');
    			}
                else if (r.result == "update_saved") {
                    $('#'+id).children('img').attr('src', 'images/button_saved_ad.gif');
    			}
                else {								
    				alert("An error occurred when updating the status of your saved ad.\n We apologise for the inconvenience.\nPlease contact problems@chirstianflatshare.org.");
    			}
            });
        
            return false;
        });
    }
    
});
