function initialize() {
  var options = {
    componentRestrictions: {country: document.getElementById('quickSearchCountry').value}
  };
  
  var input = document.getElementById('searchTextField');
  var autocomplete = new google.maps.places.Autocomplete(input, options);
  
  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    input.className = '';
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      // Inform the user that the place was not found and return.
      input.className = 'notfound';
      return;
    }

    //console.log(place);
    
    var lat = place.geometry.location.lat();
    var lng = place.geometry.location.lng();
    
    document.getElementById('quickSearchLat').value = lat;
    document.getElementById('quickSearchLng').value = lng;
    
    document.getElementById('quickSearch').submit();
    //window.location.href = '?lat=' + lat + '&lng=' + lng;
    
    //console.log(place.geometry.location.lat());
    //console.log(place.geometry.location.lng());
    
    var address = '';
    if (place.address_components) {
      address = [
        (place.address_components[0] && place.address_components[0].short_name || ''),
        (place.address_components[1] && place.address_components[1].short_name || ''),
        (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }
    
    //document.getElementById('result').innerHTML = '<div><strong>' + place.name + '</strong><br>' + address;
  });
}
google.maps.event.addDomListener(window, 'load', initialize);