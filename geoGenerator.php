<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
<style>
body {
    margin: 0;
    padding: 0;
    background: #000;
}

#map {
    width: 100%;
    height: 90%;
}
#address {
    width: 100%;
    height: 10%;
    display: table;
    color: #FFF;
    background: #000;
}
.inner {
    display: table-cell;
    vertical-align: middle;
    text-indent: 20px;
    color: #FFF;
    font-family: helvetica, arial, sans-serif;
}

span {
    text-align: right;
    color: #666;
    float: right;
    padding-right: 20px;
}

em {
    color: #FF0000;
}

</style>
<div id="map"></div>
<div id="address"><div class="inner">Click on the map</div></div>
<script>
    
// Add Map
var mapOptions = {
    center: new google.maps.LatLng(51.4676, -0.1402),
    zoom: 15,
    mapTypeId: google.maps.MapTypeId.ROADMAP
};
var map = new google.maps.Map(document.getElementById('map'), mapOptions);

google.maps.event.addListener(map, 'click', function(event) {
    var lat = event.latLng.lat();
    var lng = event.latLng.lng();

    jQuery.ajax({url: "geoSaver.php?lat=" + lat + "&lng=" + lng}).done(function ( data ) {
        var address_components = [data.street, data.area, data.region, data.postal_code, data.country];
        $('.inner').html(address_components.join(', ') + '<span>' + data.latitude + ', ' + data.longitude + '</span>');
    }).fail(function ( data ) {
        $('.inner').html('<em>Invalid address</em>');
    });
});

</script>