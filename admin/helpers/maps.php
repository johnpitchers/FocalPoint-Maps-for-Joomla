<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 *  Mapping class based Google Maps API v3
 */
class mapsAPI
{

    public function getLatLong($geoaddress)
    {

        //encode the geoaddress to replace spaces with +, etc
        $address = urlencode($geoaddress);

        //Assemble the request URL. This URL asks for the results in JSON format.
        $geocodeURL = "//maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&sensor=false";

        //Use CURL to get the coordinates from Google.
        $ch = curl_init($geocodeURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {

            // convert the response into an object
            $geocode = json_decode($result);

            // uncomment the below line to see the full response.
            //FocalpointHelper::printNdie($data);

            if ($geocode->status == "OK") {
                //All good! Set the latitude and longitude
                $latitude = $geocode->results[0]->geometry->location->lat;
                $longitude = $geocode->results[0]->geometry->location->lng;
            } else {
                //Status isn't "OK". Usually the address is mistyped and Google cant geocode it.
                throw new Exception(JText::_('COM_FOCALPOINT_GOOGLE_GEOLOCATION_ERROR') . $geocode->status);
                return false;
            }
        } else {
            //HTTP error code.
            throw new Exception("HTTP_FAIL_" . $httpCode, $httpCode);
            return false;
        }

        return array($latitude, $longitude);
    }
}

?>
