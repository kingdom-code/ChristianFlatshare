<?php

namespace CFS\GeoEncoding;

use Guzzle\Http\Client;

class CFSGeoEncoding {
    private function getEarthRadiusSemimajor() {
        return 6378137.0;
    }

    private function getEarthFlattening() {
        return 1/298.257223563;
    }

    private function getEarthRadiusSemiminor() {
        return $this->getEarthRadiusSemimajor() * (1 - $this->getEarthFlattening());
    }

    private function getEarthRadius($latitude=37.9) {
        //global $earth_radius_semimajor, $earth_radius_semiminor;
        // Estimate the Earth's radius at a given latitude.
        // Default to an approximate average radius for the United States.

        $lat = deg2rad($latitude);

        $x = cos($lat)/$this->getEarthRadiusSemimajor();
        $y = sin($lat)/$this->getEarthRadiusSemiminor();
        return 1 / (sqrt($x*$x + $y*$y));
    }

    public function earth_distance_sql($latitude, $longitude, $tbl_alias) {
        // Make a SQL expression that estimates the distance to the given location.
        $lat = deg2rad($latitude);
        $lng = deg2rad($longitude);
        $radius = $this->getEarthRadius($latitude);

        // If the table alias is specified, add on the separator.
        $tbl_alias = empty($tbl_alias) ? $tbl_alias : ($tbl_alias .'.');

        $coslat = cos($lat);
        $coslng = cos($lng);
        $sinlat = sin($lat);
        $sinlng = sin($lng);
        return "(COALESCE(ACOS($coslat*COS(RADIANS({$tbl_alias}latitude))*($coslng*COS(RADIANS({$tbl_alias}longitude)) + $sinlng*SIN(RADIANS({$tbl_alias}longitude))) + $sinlat*SIN(RADIANS({$tbl_alias}latitude))), 0.00000)*$radius)";
    }

    public function getLookup($string, $country_iso) {
        $client = new Client('https://maps.googleapis.com/maps/api/');
        $request = $client->get('geocode/json?address=' . $string . '&components=country:' . $country_iso . '&sensor=false&key=' . GOOGLE_CLOUD_BROWSER_KEY);
        $response = $request->send();
        $results = $response->json();
        
        return $results['results'][0]['geometry']['location'];
    }

    public function getReverseLookup($lat, $lng) {
        $client = new Client('https://maps.googleapis.com/maps/api/');
        $request = $client->get('geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false&key=' . GOOGLE_CLOUD_BROWSER_KEY);
        $response = $request->send();
        $results = $response->json();
        
        // Loop through results until we get one with a route
        // this protects against matching an establishment
        $i = 0;
        $num = count($results['results']);
        do {
            $address['route']       = NULL;
            $address['locality']    = NULL;
            $address['sublocality'] = NULL;
            $address['admin_1']     = NULL;
            $address['admin_2']     = NULL;
            $address['street']      = NULL;
            $address['county']      = NULL;
            $address['region']      = NULL;
            $address['country']     = NULL;
            $address['postal_code'] = NULL;
            $address['street_num']  = NULL;
            $address['postal_town'] = NULL;
            
            // Loop through the address components potentially returned by Google
            foreach ($results['results'][$i]['address_components'] as $component) {
                foreach ($component['types'] as $type) {
                    switch($type) {
                        case 'route':
                            $address['route'] = $component['long_name'];
                            break;
                        case 'locality':
                            $address['locality'] = $component['long_name'];
                            break;
                        case 'sublocality':
                            $address['sublocality'] = $component['long_name'];
                            break;
                        case 'administrative_area_level_2':
                            $address['admin_2'] = $component['long_name'];
                            break;
                        case 'administrative_area_level_1':
                            $address['admin_1'] = $component['long_name'];
                            break;
                        case 'country':
                            $address['country'] = $component['short_name'];
                            break;
                        case 'postal_code':
                            $address['postal_code'] = $component['long_name'];
                            break;
                        case 'street_number':
                            $address['street_num'] = $component['long_name'];
                            break;
                        case 'postal_town':
                            $address['postal_town'] = $component['long_name'];
                            break;
                    }
                }
            }
            $i++;
        } while (empty($address['route']) && $i < $num);
        
        // Bail out if we have to route at this stage
        if (empty($address['route'])) {
            return FALSE;
        }
        
        // Standardise the data we store for each country
        switch ($address['country']) {
            case 'GB':
                // UNITED KINGDOM
                // ==============
                
                // Get Area
                $area = (empty($address['locality'])) ? $address['postal_town'] : $address['locality'];
                
                // Get Region
                $region = $address['admin_2'];
                break;
            case 'US':
                // UNITED STATES
                // =============
                
                // Get Area
                $area = (empty($address['locality'])) ? $address['admin_2'] : $address['locality'];
                
                // Get Region
                $region = $address['admin_1'];
                break;
            case 'CA':
                // CANADA
                // ======
                
                // Get Area
                $area = (empty($address['locality'])) ? $address['admin_2'] : $address['locality'];
                
                // Get Region
                $region = $address['admin_1'];
                break;
            case 'AU':
                // AUSTRALIA
                // =========
                
                // Get Area
                $area = (empty($address['locality'])) ? $address['admin_2'] : $address['locality'];
                
                // Get Region
                $region = $address['admin_1'];
                break;
            case 'ZA':
                // SOUTH AFRICA
                // ============
            
                // Get Area
                $area = (empty($address['sublocality'])) ? $address['admin_2'] : $address['sublocality'];
                
                // Get Region
                $region = (empty($address['locality'])) ? $address['admin_1'] : $region = $address['locality'];
                break;
            case 'IE':
                // IRELAND
                // =======
                
                // Get Area
                if (($address['admin_1'] == $address['admin_2']) && !empty($address['locality'])) {
                    $area = $address['locality'];
                }
                else {
                    $area = $address['admin_2'];
                }
                
                // Get Region
                $region = $address['admin_1'];
                break;
            default:
                // UNSUPPORTED COUNTRY
                // ===================
                return FALSE;
                break;
        }
        
        return array(
            'street' => $address['route'],
            'area' => $area,
            'region' => $region,
            'country' => $address['country'],
            'postal_code' => $address['postal_code'],
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
        );
    }
    
    public function getReverseLookupJSON($lat, $lng) {
        $address = $this->getReverseLookup($lat, $lng);
        return json_encode($address);
    }
    
    public function getReverseLookupRaw($lat, $lng) {
        $client = new Client('https://maps.googleapis.com/maps/api/');
        $request = $client->get('geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false&key='.GOOGLE_CLOUD_BROWSER_KEY);
        $response = $request->send();
        $results = $response->json();
        
        return $results['results'];
    }
}
