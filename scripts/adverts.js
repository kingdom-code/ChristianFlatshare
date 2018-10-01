jQuery(document).ready(function($) {
    
    var circles = [];
    var circleBounds = new google.maps.LatLngBounds();
    
    $('#display_2').change(function(){
        setup();
    });
    
    var rawLocations = $('#advertLocations').val();
    if (rawLocations.length) {
        setup();
    }

    // Prevent return within input elements
    $("form :input").on("keypress", function(e) {
        return e.keyCode != 13;
    });
    
    $("form").submit(function() {
       
        var locations = [];

        $.each(circles, function(index, circle) {
            var radius = circle.getRadius();
            var center = circle.getCenter();
            var lat = center.lat();
            var lng = center.lng();

            locations.push([lat, lng, (radius / 1609.344)]);
        });
       
        $('#advertLocations').val(JSON.stringify(locations));
    });
    
    function setup() {
        // Add Map
        var mapOptions = {
            center: new google.maps.LatLng(-33.8688, 151.2195),
            zoom: 18,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById('locationMap'), mapOptions);
        
        // Populate values
        var rawLocations = $('#advertLocations').val();
        if (rawLocations.length) {
            var locations = jQuery.parseJSON(rawLocations);
            
            $.each(locations, function(index, circle) {
                if ($('#advertCountry').length) {
                    // Editing
                    var circleOptions = {
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#FF0000',
                        fillOpacity: 0.35,
                        map: map,
                        center: new google.maps.LatLng(circle[0], circle[1]),
                        radius: circle[2] * 1609.344
                    };
                    newCircle = new google.maps.Circle(circleOptions);
                
                
                    google.maps.event.addListener(newCircle, 'click', function() {
                        this.setMap(null);
                        var index = circles.indexOf(this);
                        circles.splice(index, 1);
                    });
                }
                else {
                    // Viewing
                    var circleOptions = {
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#FF0000',
                        fillOpacity: 0.35,
                        map: map,
                        clickable: false,
                        center: new google.maps.LatLng(circle[0], circle[1]),
                        radius: circle[2] * 1609.344
                    };
                    newCircle = new google.maps.Circle(circleOptions);
                
                
                    google.maps.event.addListener(newCircle, 'click', function() {
                        this.setMap(null);
                        var index = circles.indexOf(this);
                        circles.splice(index, 1);
                    });
                }
                
                circles.push(newCircle);
                circleBounds.union(newCircle.getBounds());
            });
            
            $("#locationMap").show();
            // Show map if hidden
            google.maps.event.trigger(map, 'resize');
            map.fitBounds(circleBounds);
        }
        
        // Add Autocomplete field
        if ($('#advertCountry').length) {        
            var autocompleteOptions = {
                componentRestrictions: {country: document.getElementById('advertCountry').value}
            };
    
            var input = document.getElementById('advertLocationPlace');
            var autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);
    
            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                input.className = '';
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    // Inform the user that the place was not found and return.
                    input.className = 'notfound';
                    return;
                }
            
                $("#locationMap").show();
            
                var circleOptions = {
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: map,
                    center: place.geometry.location,
                    radius: $('#radius').val() * 1609.344
                };
                newCircle = new google.maps.Circle(circleOptions);
            
                google.maps.event.addListener(newCircle, 'click', function() {
                    this.setMap(null);
                    var index = array.indexOf(this);
                    circles.splice(index, 1);
                });
            
                circles.push(newCircle);
                circleBounds.union(newCircle.getBounds());
            
                // Show map if hidden
                google.maps.event.trigger(map, 'resize');
                map.fitBounds(circleBounds);
            });
        }
    }
});