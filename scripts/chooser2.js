function initialize() {
    var latitude    = document.getElementById('latitude').value;
    var longitude   = document.getElementById('longitude').value;
    
    // Add Map
    var mapOptions = {
        center: new google.maps.LatLng(-33.8688, 151.2195),
        zoom: 18,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById('locationChooser'), mapOptions);
    
    // Prevent return within input elements
    jQuery("form :input").on("keypress", function(e) {
        return e.keyCode != 13;
    });
    
    jQuery("#resetChooser").click(function() {
        document.getElementById('latitude').value           = '';
        document.getElementById('longitude').value          = '';
        document.getElementById('route').value              = '';
        document.getElementById('locality').value           = '';
        document.getElementById('admin_level').value        = '';
        document.getElementById('country').value            = '';
        document.getElementById('postal_code').value        = '';
        document.getElementById('addressName').innerHTML    = '';
        document.getElementById('postcodeChooser').value    = '';
        
        document.getElementById('locationChooser').style.display = 'none';
        document.getElementById('postcodeChooser').style.display = 'block';
        document.getElementById('addressExtra').style.display = 'none';
        return false;
    });
    
    // Add Marker (Offered)
    var marker = new google.maps.Marker({
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP,
    });
    
    // Add Circle (Wanted)
    var circle = new google.maps.Circle({
        strokeColor: '#FF0000',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#FF0000',
        fillOpacity: 0.35,
        map: map,
        clickable: true,
        draggable: true
    });
    
    google.maps.event.addListener(marker, 'dragend', function() {
        var position = marker.getPosition();
        var lat = position.lat();
        var lng = position.lng();
        populateAddress(lat, lng);
        
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });
    
    google.maps.event.addListener(circle, 'dragend', function() {
        var position = circle.getCenter();
        var lat = position.lat();
        var lng = position.lng();
        populateAddress(lat, lng);
        
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });
    
    // Add Autocomplete field
    var autocompleteOptions = {
        componentRestrictions: {country: document.getElementById('postcodeChooserCountry').value}
    };
    
    var input = document.getElementById('postcodeChooser');
    var autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);
    autocomplete.bindTo('bounds', map);
    
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        input.className = '';
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            // Inform the user that the place was not found and return.
            input.className = 'notfound';
            return;
        }
        
        // Show map if hidden
        document.getElementById('locationChooser').style.display = 'block';
        document.getElementById('postcodeChooser').style.display = 'none';
        document.getElementById('addressExtra').style.display = 'block';
        google.maps.event.trigger(map, 'resize');
        map.setCenter(place.geometry.location);
        map.setZoom(17);
        
        var lat = place.geometry.location.lat();
        var lng = place.geometry.location.lng();
        populateAddress(lat, lng);
        
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        
        if (jQuery("#distance_from_postcode").length) {
            var radius = jQuery("#distance_from_postcode").val();
            circle.setCenter(place.geometry.location);
            circle.setRadius(radius * 1609.344);
            map.fitBounds(circle.getBounds());
        }
        else {
            marker.setVisible(true);
            marker.setPosition(place.geometry.location);
        }
    });
    
    if (jQuery("#distance_from_postcode").length) {
        jQuery("#distance_from_postcode").change(function(){
            var radius = jQuery("#distance_from_postcode").val();
            circle.setRadius(radius * 1609.344);
        });
    }
    
    if (latitude != "" || longitude != "") {
        // Show map if hidden
        document.getElementById('locationChooser').style.display = 'block';
        var position = new google.maps.LatLng(latitude, longitude);
        console.log(position);
        marker.setVisible(true);
        marker.setPosition(position);
        map.setCenter(position);
        map.setZoom(17);
        google.maps.event.trigger(map, 'resize');
        document.getElementById('postcodeChooser').style.display = 'none';
        document.getElementById('addressExtra').style.display = 'block';
    }
}

function populateAddress(lat, lng) {
    jQuery.ajax({url: "ajaxResponses.php?target=geoEncode&lat=" + lat + "&lng=" + lng}).done(function ( address ) {
        document.getElementById('route').value = address.street;
        document.getElementById('locality').value = address.area;
        document.getElementById('admin_level').value = address.region;
        document.getElementById('country').value = address.country;
        document.getElementById('postal_code').value = (address.postal_code) ? address.postal_code : '' ;
        document.getElementById('addressName').innerHTML = [address.street, address.area, address.region].join(', ');
    });
}

jQuery(document).ready(function($) {
    if ($('#locationChooser').length) {
        google.maps.event.addDomListener(window, 'load', initialize);
    }
    
    
    if ($('.age-slider').length) {
        var range = $("#ageRange").val();
        range = range.split('-');
        
        $(".age-slider").noUiSlider({
            range: [1, 8],
            start: [range[0], range[1]],
            step: 1,
            connect: true,
            handles: 2,
            orientation: "horizontal",
            slide: function(){
                var values = $(this).val();
                $("#ageRange").val(values[0] + "-" + values[1]);
            }
        });
    }
});