<?php

/**
 * WWE Small Get Distance
 *
 * @package     WWE Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Distance Request Class
 */
class Eniture_Get_ups_small_distance
{

    function __construct()
    {
        add_filter("eniture_wd_get_address", array($this, "eniture_ups_small_address"), 10, 2);
    }

    /**
     * Get Address Upon Access Level
     * @param $map_address
     * @param $accessLevel
     */
    function ups_small_address($map_address, $accessLevel, $destinationZip = array())
    {

        $domain = eniture_ups_small_get_domain();
        $postData = array(
            'acessLevel' => $accessLevel,
            'address' => $map_address,
            'originAddresses' => (isset($map_address)) ? $map_address : "",
            'destinationAddress' => (isset($destinationZip)) ? $destinationZip : "",
            'eniureLicenceKey' => get_option('ups_small_licence_key'),

            'ServerName' => $domain,
        );


        $Ups_Small_Curl_Request = new Eniture_UPS_Small_Curl_Request();
        $output = $Ups_Small_Curl_Request->ups_small_get_curl_response(ENITURE_UPS_DOMAIN_HITTING_URL . '/addon/google-location.php', $postData);


        return $output;
    }

}
