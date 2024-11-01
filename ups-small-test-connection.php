<?php

/**
 * UPS Small WooComerce |  Test Connection AJAX Request
 * @package     Woo-commerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_nopriv_eniture_ups_small_test_connection', 'eniture_ups_test_submit');
add_action('wp_ajax_eniture_ups_small_test_connection', 'eniture_ups_test_submit');

/**
 * UPS Small Test Connection AJAX Request
 */
function eniture_ups_test_submit()
{
    $iAcountNumber = (isset($_POST['ups_small_acc_number'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_acc_number'] )) : '';
    $sLicenseKey = (isset($_POST['ups_small_license'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_license'] )) : '';
    $domain = eniture_ups_small_get_domain();
    $data = array(
        'plateform' => 'WordPress',
        'carrierName' => 'upsSmall',
        'ups_account_number' => $iAcountNumber,
        'plugin_licence_key' => $sLicenseKey,
        'ups_domain_name' => $domain,
    );

    if(isset($_POST['api_end_point']) && sanitize_text_field(wp_unslash($_POST['api_end_point'])) == 'ups_old_api'){
        $data['ups_license_key'] = (isset($_POST['ups_small_api_access_key'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_api_access_key'] )) : '';
        $data['ups_username'] = (isset($_POST['ups_small_username'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_username'] )) : '';
        $data['ups_password'] = (isset($_POST['ups_small_password'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_password'] )) : '';
    }else{
        $data['ApiVersion'] = '2.0';
        $data['clientId'] = (isset($_POST['client_id'])) ? sanitize_text_field(wp_unslash( $_POST['client_id'] )) : "";
        $data['clientSecret'] = (isset($_POST['client_secret'])) ? sanitize_text_field(wp_unslash( $_POST['client_secret'] )) : "";
        $data['ups_username'] = (isset($_POST['ups_small_username'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_username'] )) : '';
        $data['ups_password'] = (isset($_POST['ups_small_password'])) ? sanitize_text_field(wp_unslash( $_POST['ups_small_password'] )) : '';
    }

    $ups_small_curl_obj = new Eniture_UPS_Small_Curl_Request();
    $sResponseData = $ups_small_curl_obj->ups_small_get_curl_response(ENITURE_UPS_DOMAIN_HITTING_URL . '/s/ups/auth.php', $data);
    $sResponseData = json_decode($sResponseData);

    if (isset($sResponseData->severity) && $sResponseData->severity == 'SUCCESS' || isset($sResponseData->success) && $sResponseData->success == 1) {
        $sResult = array('success' => "The test resulted in a successful connection.");
    }else if (isset($sResponseData->severity) && $sResponseData->severity == 'ERROR') {
        $message = (isset($sResponseData->message) && !empty($sResponseData->message)) ? $sResponseData->message : "Please verify credentials and try again.";
        $sResult = array('error' => $message);
    } else {
        $sResult = ($sResponseData->error == 1) ? 'Please verify credentials and try again.' : $sResponseData->error;
        $sResult = array('error' => $sResult);
    }

    echo wp_json_encode($sResult);
    exit();
}

/**
 * Get Host
 * @param type $url
 * @return type
 */
if (!function_exists('eniture_getHost')) {

    function eniture_getHost($url)
    {
        $parseUrl = wp_parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }

}
    
