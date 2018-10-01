jQuery(document).ready(function($) {
    $('.regionSelector select').change(function(){
       var region = $(this).val();
        window.location = '?region=' + region;
    });
    
    $('a.mapPopUp').click(function(){
        var church_id = $(this).attr('href').substring(1);
        loadAndDisplayMapForChurch(church_id);
        return false;
    });
    
    function loadAndDisplayMapForChurch(id) {
        // Get church info via AJAX
        var info = null;
        
        $.ajax({url: "ajaxResponses.php?target=churchInfo&church_id=" + id}).done(function ( info ) {
            // Display Lightbox
            if (!$('#popUpMap').length) {
                $('body').append('<div id="popUpMapContainer"><h4 class="title"></h4><p class="subtitle"></p><div id="popUpMap"></div></div>');
                $('body').append('<div id="popUpOverlay"></div>');
                $('body').addClass('popUpActive');
                
                $('#popUpOverlay').click(function(){
                   $(this).fadeOut('fast');
                   $('#popUpMapContainer').fadeOut('fast');
                   $('body').removeClass('popUpActive');
                });
            }
            else {
                $('#popUpOverlay').fadeIn('fast');
                $('#popUpMapContainer').fadeIn('fast');
                $('body').addClass('popUpActive');
            }
            
            $("#popUpMapContainer .title").html('<a href="http://' + info.church_url + '">' + info.church_name + '</a>');
            var address = [info.route, info.locality, info.postal_code];
            $("#popUpMapContainer .subtitle").html(address.join(', '));
            
            // Add Map
            var mapOptions = {
                center: new google.maps.LatLng(info.latitude, info.longitude),
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById('popUpMap'), mapOptions);
            
            // Add Marker
            var marker = new google.maps.Marker({
                map: map,
                draggable: false,
                animation: google.maps.Animation.DROP,
                visible: true,
                position: new google.maps.LatLng(info.latitude, info.longitude)
            });
        });
    }
});