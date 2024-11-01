<?php

/**
 * UPS Small WooComerce |  Curl Response Class
 * @package     Woocommerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Eniture UPS Small WooComerce | Curl Response Class
 */
class Eniture_UPS_Small_Curl_Request
{

    /**
     * Get Curl Response
     * @param $url
     * @param $postData
     * @return string | array
     */
    function ups_small_get_curl_response($url, $postData)
    {
        if (!empty($url) && !empty($postData)) {
            $field_string = http_build_query($postData);
            do_action("eniture_debug_mood", "Build Query (ups small)", $field_string);


            $response = wp_remote_post($url, array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $field_string,
                )
            );

            $output = wp_remote_retrieve_body($response);

            return $output;
        }
    }

}
