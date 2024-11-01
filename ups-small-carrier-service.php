<?php

/**
 * UPS Small WooComerce |  Getting Quotes
 * @package     Woo-commerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UPS Small WooComerce | Get Quotes For UPS Small
 */
class Eniture_UPS_Get_Shipping_Quotes extends Eniture_EnUpsSmallFdo
{

    /**
     * Global Variable
     * @var int
     */
    public $ups_sm_errors = array();

    /**
     * Global Variable
     * @var int
     */
    public $no_services_select = array();
    public $hazardous_status;
    public $en_wd_origin_array;
    public $product_detail = [];
    public $forcefully_residential_delivery = FALSE;

    /**
     * Array For Getting Quotes
     * @param $packages
     * @param $content
     * @return Array For Getting Web Quotes
     */
    function ups_small_shipping_array($packages, $content, $package_plugin = "")
    {
        $itemTypeVal = "";
        // FDO
        $en_fdo_meta_data = $post_data = array();
        $shipping_package_obj = new Eniture_UPS_Small_Shipping_Get_Package();
        $destinationAddressUpsSmall = $this->destinationAddressUpsSmall();

        $freightClass_ltl_gross_small = array();
        foreach ($content['contents'] as $item_id => $values) {

            $_product = $values['data'];
            $freight_class = $shipping_package_obj->ups_small_check_freight_class($_product);

            // Flat shipping
            $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            $en_fs_existence = apply_filters('en_fs_existence', false);
            $fs = get_post_meta($product_id, '_enable_fs', true);
            if (!$_product->is_virtual()) {
                if ($freight_class == 'ltl_freight' && !($en_fs_existence && $fs == 'yes')) {
                    $freightClass_ltl_gross_small[] = $freight_class;
                }
            }
        }

        $exceedWeight = get_option('wc_settings_wwe_return_LTL_quotes');

        if (isset($content['itemType'])) {
            $itemTypeVal = $content['itemType'];
        }

        if (count($freightClass_ltl_gross_small) > 0) {
            $find_ltl_class = 'ltl';
            if ((!isset($itemTypeVal) || $itemTypeVal != 'ltl')) {
                return $find_ltl_class;
            }
        }

        $ups_simple_rate_active = false;
        $action_contract = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'contract_services');
        $ups_simple_service = $this->ups_simple_service_options();
        if (!is_array($action_contract) && !empty($ups_simple_service)) {
            $ups_simple_rate_active = true;
        }

        $Pweight = 0;
        $findLtl = 0;
        $hazardousMaterials = array();
        //threshold
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
        //      check plan for nested material
        $nested_plan = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'nested_material');
        $en_shipments = (isset($content['en_shipments'])) ? $content['en_shipments'] : [];
        foreach ($packages as $package) {
            $ship_item_alone = $shipmentWeekDays = $storeDateTime = $modifyShipmentDateTime = $shipmentOffsetDays = $orderCutoffTime = $products = $product_name = $stakingProperty = $productName = $productQty = $productPrice = $productWeight = $productLength = $productWidth = $productHeight = $nestingPercentage = $nestedDimension = $nestedItems = $hazardousItems = [];
            $hazardousItemsCount = 0;
            $this->en_wd_origin_array = (isset($package['origin'])) ? $package['origin'] : array();
            $package_zip = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : '';
            if (!strlen($package_zip) > 0) {
                continue;
            }

            $product_markup_shipment_arr = [];
            if ($package['origin']['ptype'] != 'ltl' && !($exceedWeight == 'yes' && $Pweight > $weight_threshold) &&
                (empty($en_shipments) || (!empty($en_shipments) && isset($en_shipments[$package_zip]))) &&
                (!isset($package['is_shipment']) || (isset($package['is_shipment']) && $package['is_shipment'] != 'ltl'))) {
                if (isset($package['items'])) {
                    $productIdCount = 0;
                    foreach ($package['items'] as $item) {
                        $Pweight = $item['productWeight'];
                        $productName[$productIdCount] = $item['productName'];
                        $productWeight[$productIdCount] = $item['productWeight'];
                        $productLength[$productIdCount] = $item['productLength'];
                        $productWidth[$productIdCount] = $item['productWidth'];
                        $productHeight[$productIdCount] = $item['productHeight'];
                        $productQty[$productIdCount] = $item['productQty'];
                        $productPrice[$productIdCount] = $item['productPrice'];
                        $nestingPercentage[$productIdCount] = $item['nestedPercentage'];
                        $nestedDimension[$productIdCount] = $item['nestedDimension'];
                        $nestedItems[$productIdCount] = $item['nestedItems'];
                        $stakingProperty[$productIdCount] = $item['stakingProperty'];
                        isset($item['nestedMaterial']) && !empty($item['nestedMaterial']) &&
                        $item['nestedMaterial'] == 'yes' && !is_array($nested_plan) ? $doNesting = 1 : "";
                        $ship_item_alone[$productIdCount] = $item['ship_item_alone'] || $item['hazardousMaterial'];

                        if(!empty($item['markup'])){
                            $product_markup_shipment_arr[$productIdCount] = $item['markup'];
                        }

                        $product_name[] = $item['product_name'];
                        $products[] = $item['products'];

                        // hazardous item check
                        $hazardousItems[] = isset($item['hazardousMaterial']) ? $item['hazardousMaterial'] : 0;
                        if (isset($item['hazardousMaterial']) && $item['hazardousMaterial'] == 1) {
                            $hazardous_fee_option = get_option('ups_small_hazardous_fee_options');
                            if ($hazardous_fee_option == 'combine_quantities') {
                                $hazardousItemsCount += 1;
                            } else {
                                $hazardousItemsCount += $item['productQty'];
                            }
                        }

                        $productIdCount++;
                    }
                }

                // **Start:      Cut Off Time & Ship Date Offset
                $ups_small_delivery_estimates = get_option('ups_small_delivery_estimates');
                // shipment days of a week
                $shipmentWeekDays = $this->ups_small_shipment_week_days();
                if ($ups_small_delivery_estimates == 'delivery_days' || $ups_small_delivery_estimates == 'delivery_date') {

                    $orderCutoffTime = get_option('ups_small_orderCutoffTime');
                    $shipmentOffsetDays = get_option('ups_small_shipmentOffsetDays');
                    $modifyShipmentDateTime = ($orderCutoffTime != '' || $shipmentOffsetDays != '' || (is_array($shipmentWeekDays) && count($shipmentWeekDays) > 0)) ? 1 : 0;
                    $storeDateTime = gmdate('Y-m-d H:i:s', current_time('timestamp'));
                }

                $domain = eniture_ups_small_get_domain();

                $index = 'small-package-quotes-ups-edition/small-package-quotes-ups-edition.php';
                $plugin_info = get_plugins();
                $plugin_version = $plugin_info[$index]['Version'];

                $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");

                $package_type = get_option('ups_small_packaging_method');
                $per_package_weight = '';
                if('ship_one_package_70' == $package_type){
                    $package_type = 'ship_as_one';
                    $per_package_weight = '70';
                }elseif('ship_one_package_150' == $package_type){
                    $package_type = 'ship_as_one';
                    $per_package_weight = '150';
                }

                // FDO
                $en_fdo_meta_data = $this->en_cart_package($package);

                // Version numbers
                $plugin_versions = $this->en_version_numbers();

                $s_post_data = array(
                    // Version numbers
                    'plugin_version' => $plugin_versions["en_current_plugin_version"],
                    'wordpress_version' => get_bloginfo('version'),
                    'woocommerce_version' => $plugin_versions["woocommerce_plugin_version"],
                    'plateform' => 'WordPress',
                    'carrierName' => 'upsSmall',
                    'ups_small_pkg_account_number' => get_option('ups_small_account_number'),
                    'ups_small_pkg_plugin_licence_key' => get_option('ups_small_licence_key'),
                    'ups_small_pkg_domain_name' => isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '',
                    'ups_small_pkg_domain_name' => $domain,
                    'ups_small_pkg_reciver_city' => $destinationAddressUpsSmall['city'],
                    'ups_small_pkg_receiver_state' => $destinationAddressUpsSmall['state'],
                    'ups_small_pkg_receiver_zip_code' => $destinationAddressUpsSmall['zip'],
                    'ups_small_pkg_receiver_countryCode' => $destinationAddressUpsSmall['country'],
                    'ups_small_pkg_senderCity' => $package['origin']['city'],
                    'ups_small_pkg_senderState' => $package['origin']['state'],
                    'ups_small_pkg_senderZip' => $package['origin']['zip'],
                    'ups_small_pkg_senderCountryCode' => $package['origin']['country'],
                    'ups_small_pkg_resid_delivery' => (get_option('ups_small_quote_as_residential_delivery') == 'yes') ? 'yes' : 'no',
                    'ups_small_pkg_product_width' => $productWidth,
                    'ups_small_pkg_product_height' => $productHeight,
                    'ups_small_pkg_product_length' => $productLength,
                    'ups_small_pkg_product_price' => $productPrice,
                    'ups_small_pkg_product_weight' => $productWeight,
                    'ups_small_pkg_product_quantity' => $productQty,
                    'ups_small_pkg_post_title' => $productName,
                    'suspend_residential' => get_option('suspend_automatic_detection_of_residential_addresses'),
                    'residential_detecion_flag' => $residential_detecion_flag,
                    'ups_small_pkg_3_Day_Select' => (get_option('ups_small_pkg_3_Day_Select') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Ground' => (get_option('ups_small_pkg_Ground') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_2nd_Day_Air' => (get_option('ups_small_pkg_2nd_Day_Air') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_2nd_Day_Air_AM' => (get_option('ups_small_pkg_2nd_Day_Air_AM') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Next_Day_Air' => (get_option('ups_small_pkg_Next_Day_Air') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Next_Day_Air_Saver' => (get_option('ups_small_pkg_Next_Day_Air_Saver') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Next_Day_Air_Early_AM' => (get_option('ups_small_pkg_Next_Day_Air_Early_AM') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Standard' => (get_option('ups_small_pkg_Standard') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Worldwide_Express' => (get_option('ups_small_pkg_Worldwide_Express') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Worldwide_Express_Plus' => (get_option('ups_small_pkg_Worldwide_Express_Plus') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Worldwide_Expedited' => (get_option('ups_small_pkg_Worldwide_Expedited') == 'yes') ? 'yes' : "",
                    'ups_small_pkg_Saver' => (get_option('ups_small_pkg_Saver') == 'yes') ? 'yes' : "",
                    'ups_tnt' => array(
                        'access_key' => 'FD3AF37E2608EF8A',
                        'user_id' => 'genesismannequin',
                        'password' => 'Genman345@',
                        'weihgt' => $productWeight,
                        'weihgt_code' => "LBS",
                        'pickup_date' => gmdate("Ymd"),
                        'total_packages_in_shipment' => '1',
                        'currency_code' => 'USD', // USD / CAD
                        'monetary_value' => '10',
                        'maximum_list_size' => '1',
                        'saturday_delivery_info_request_indicator' => 1
                    ),
                    'ups_small_surepost_less_than_1LB' => get_option('ups_surepost_less_than_1_lb'),
                    'ups_small_surepost_1LB_or_greater' => get_option('ups_surepost_1_lb_or_greater'),
                    'ups_small_surepost_bpm' => get_option('ups_surepost_bound_printed_matter'),
                    'ups_small_surepost_media_mail' => get_option('ups_surepost_media_mail'),
                    "ups_small_pkg_Ground_Freight_Pricing" => get_option('ups_ground_with_freight_pricing'),
                    // Nested indexes
                    'doNesting' => $doNesting,
                    'nesting_percentage' => $nestingPercentage,
                    'nesting_dimension' => $nestedDimension,
                    'nested_max_limit' => $nestedItems,
                    'nested_stack_property' => $stakingProperty,
                    'sender_origin' => $package['origin']['location'] . ": " . $package['origin']['city'] . ", " . $package['origin']['state'] . " " . $package['origin']['zip'],
                    'product_name' => $product_name,
                    'products' => $products,
                    'modifyShipmentDateTime' => $modifyShipmentDateTime,
                    'OrderCutoffTime' => $orderCutoffTime,
                    'shipmentOffsetDays' => $shipmentOffsetDays,
                    'storeDateTime' => $storeDateTime,
                    'shipmentWeekDays' => $shipmentWeekDays,
                    // FDO
                    'en_fdo_meta_data' => $en_fdo_meta_data,
                    // Shippable item
                    'ship_item_alone' => $ship_item_alone,
                    'origin_markup' => (isset($package['origin']['origin_markup'])) ? $package['origin']['origin_markup'] : 0,
                    'product_level_markup_arr' => $product_markup_shipment_arr,
                    'packagesType' => $package_type,
                    'perPackageWeight' => $per_package_weight,
                    // Sbs optimization mode
                    'sbsMode' => get_option('box_sizing_optimization_mode'),
                    'hazardousItems' => $hazardousItems,
                    'hazardousItemsCount' => $hazardousItemsCount,
                );

                // get large cart settings shipping rules
                $large_cart_settings = (new Eniture_UpsSmallShippingRulesAjaxReq())->get_large_cart_settings();
                $s_post_data = array_merge($s_post_data, $large_cart_settings);

                if((false === get_option('ups_api_endpoint')) || ('ups_old_api' == get_option('ups_api_endpoint'))){
                    $s_post_data['ups_small_pkg_username'] = get_option('ups_small_username');
                    $s_post_data['ups_small_pkg_password'] = get_option('ups_small_password');
                    $s_post_data['ups_small_pkg_authentication_key'] = get_option('ups_small_api_access_key');
                }else{
                    $s_post_data['ApiVersion'] = '2.0';
                    $s_post_data['clientId'] = get_option('ups_client_id');
                    $s_post_data['clientSecret'] = get_option('ups_client_secret');
                    $s_post_data['ups_small_pkg_username'] = get_option('ups_new_api_username');
                    $s_post_data['ups_small_pkg_password'] = get_option('ups_new_api_password');
                }

                // USP simple rate
                if ($ups_simple_rate_active) {
                    $s_post_data['upsSimpleRateActive'] = 1;
                    $ups_simple_services = [];
                    get_option('ups_simple_rate_through_ground') == 'yes' ? $ups_simple_services['usrg'] = 'ups_simple_rate_through_ground' : '';
                    get_option('ups_simple_rate_through_2nd_day_air') == 'yes' ? $ups_simple_services['usr2'] = 'ups_simple_rate_through_2nd_day_air' : '';
                    get_option('ups_simple_rate_through_next_day_air_saver') == 'yes' ? $ups_simple_services['usr1'] = 'ups_simple_rate_through_next_day_air_saver' : '';
                    get_option('ups_simple_rate_through_3_day_select') == 'yes' ? $ups_simple_services['usr3'] = 'ups_simple_rate_through_3_day_select' : '';
                    !empty($ups_simple_services) ? $s_post_data['upsSimpleRateSelectedServices'] = $ups_simple_services : '';
                }

                $post_data[$package['origin']['zip']] = apply_filters("en_woo_addons_carrier_service_quotes_request", $s_post_data, eniture_woo_plugin_ups_small);

                // Hazardous Material
                $hazardous_material = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'hazardous_material');

                if (!is_array($hazardous_material)) {
                    (isset($package['hazardous_material'])) ? $post_data[$package_zip]['hazardous_status'] = TRUE : "";
                    (isset($package['hazardous_material'])) ? $post_data[$package_zip]['hazardous_status'] = 'yes' : "";
                    // FDO
                    $post_data[$package_zip]['en_fdo_meta_data'] = array_merge($post_data[$package_zip]['en_fdo_meta_data'], $this->en_package_hazardous($package, $en_fdo_meta_data));
                }

                //Except Ground Transit Restriction
                if (!is_array(apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'transit_days'))) {
                    (isset($package['exempt_ground_transit_restriction'])) ? $post_data[$package_zip]['exempt_ground_transit_restriction'] = 'yes' : '';
                }

                // Insurance Fee
                $action_insurance = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'insurance_fee');
                if (!is_array($action_insurance) && isset($package['is_shipment_insure']) && $package['is_shipment_insure']) {
                    $post_data[$package['origin']['zip']]['includeDeclaredValue'] = 1;
                    $post_data[$package['origin']['zip']]['declaredValueCurrencyCode'] = 'USD';
                }

                // In-store pickup and local delivery
                $instore_pickup_local_devlivery_action = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

                if (!is_array($instore_pickup_local_devlivery_action)) {
                    $origin_zip = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : '';
                    $post_data[$origin_zip] = apply_filters('en_ups_small_wd_standard_plans', $post_data[$package['origin']['zip']], $post_data[$package['origin']['zip']]['ups_small_pkg_receiver_zip_code'], $this->en_wd_origin_array, $package_plugin);
                }
            }

            // Compatability with OLD SBS Addon
            $zip_code = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : 0;

            $post_data = apply_filters(
                'enit_box_sizes_post_array_filter', $post_data, $package, $zip_code
            );

            if (isset($post_data[$zip_code]['vertical_rotation'], $post_data[$zip_code]['length']) &&
                count($post_data[$zip_code]['length']) == count($post_data[$zip_code]['vertical_rotation']) &&
                !empty($post_data[$zip_code]['vertical_rotation'])) {
                $post_data[$zip_code]['vertical_rotation'] = array_combine(array_keys($post_data[$zip_code]['length']), $post_data[$zip_code]['vertical_rotation']);
            }
            if (isset($post_data[$zip_code]['shipBinAlone'], $post_data[$zip_code]['length']) &&
                count($post_data[$zip_code]['length']) == count($post_data[$zip_code]['shipBinAlone']) &&
                !empty($post_data[$zip_code]['shipBinAlone'])) {
                $post_data[$zip_code]['shipBinAlone'] = array_combine(array_keys($post_data[$zip_code]['length']), $post_data[$zip_code]['shipBinAlone']);
            }
        }

        do_action("eniture_debug_mood", "Quotes Request (ups small)", $post_data);
        do_action("eniture_debug_mood", "Plugin Features (ups small)", get_option('eniture_plugin_5'));

        return $post_data;
    }

    /**
     * Return version numbers
     * @return int
     */
    function en_version_numbers()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $get_plugin_data = get_plugin_data(UPS_MAIN_FILE);
        $plugin_version = (isset($get_plugin_data['Version'])) ? $get_plugin_data['Version'] : '';

        $versions = array(
            "woocommerce_plugin_version" => $wc_plugin,
            "en_current_plugin_version" => $plugin_version
        );

        return $versions;
    }

    /**
     * @return shipment days of a week
     */
    public function ups_small_shipment_week_days()
    {
        $shipment_days_of_week = array();

        if (get_option('all_shipment_days_ups_small') == 'yes') {
            return $shipment_days_of_week;
        }
        if (get_option('monday_shipment_day_ups_small') == 'yes') {
            $shipment_days_of_week[] = 1;
        }
        if (get_option('tuesday_shipment_day_ups_small') == 'yes') {
            $shipment_days_of_week[] = 2;
        }
        if (get_option('wednesday_shipment_day_ups_small') == 'yes') {
            $shipment_days_of_week[] = 3;
        }
        if (get_option('thursday_shipment_day_ups_small') == 'yes') {
            $shipment_days_of_week[] = 4;
        }
        if (get_option('friday_shipment_day_ups_small') == 'yes') {
            $shipment_days_of_week[] = 5;
        }

        return $shipment_days_of_week;
    }

    /**
     * receiver user
     * @return array type
     */
    function destinationAddressUpsSmall()
    {
        $en_order_accessories = apply_filters('en_order_accessories', []);
        if (isset($en_order_accessories) && !empty($en_order_accessories)) {
            return $en_order_accessories;
        }
        $ups_small_woo_obj = new Eniture_UPS_Small_Woo_Update_Changes();
        $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $ups_small_woo_obj->ups_small_postcode();
        $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $ups_small_woo_obj->ups_small_getState();
        $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $ups_small_woo_obj->ups_small_getCountry();
        $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $ups_small_woo_obj->ups_small_getCity();
        return array(
            'city' => $freight_city,
            'state' => $freight_state,
            'zip' => $freight_zipcode,
            'country' => $freight_country
        );
    }

    /**
     * URL Rewriting
     * @param $domain
     * @return Refined Domain URL
     */
    function ups_small_parse_url($domain)
    {
        $domain = trim($domain);
        $parsed = wp_parse_url($domain);

        if (empty($parsed['scheme'])) {
            $domain = 'http://' . ltrim($domain, '/');
        }

        $parse = wp_parse_url($domain);
        $refinded_domain_name = $parse['host'];
        $domain_array = explode('.', $refinded_domain_name);

        if (in_array('www', $domain_array)) {
            $key = array_search('www', $domain_array);
            unset($domain_array[$key]);
            if(phpversion() < 8) {
                $refinded_domain_name = implode($domain_array, '.'); 
            }else {
                $refinded_domain_name = implode('.', $domain_array);
            }
        }

        return $refinded_domain_name;
    }

    /**
     * Get Nearest Address If Multiple Warehouses
     * @param $warehous_list
     * @param $receiverZipCode
     * @return Warehouse Address
     */
    function ups_small_multi_warehouse($warehous_list)
    {
        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->ups_small_origin_array($warehous_list);
        }

        $ups_Small_distance_request = new Eniture_Get_ups_small_distance();
        $accessLevel = "MultiDistance";

        $response_json = $ups_Small_distance_request->ups_small_address($warehous_list, $accessLevel, $this->destinationAddressUpsSmall());
        $response_json = json_decode($response_json, TRUE);
        return $response_json['origin_with_min_dist'];
    }

    /**
     * Create Origin Array
     * @param $origin
     * @return Warehouse Address Array
     */
    function ups_small_origin_array($origin)
    {
        // In-store pickup and local delivery
        if (has_filter("en_ups_small_wd_origin_array_set")) {
            return apply_filters("en_ups_small_wd_origin_array_set", $origin);
        }

        $zip = $origin->zip;
        $city = $origin->city;
        $state = $origin->state;
        $country = ($origin->country == "CN") ? "CA" : $origin->country;
        $location = $origin->location;
        $locationId = $origin->id;
        return array('locationId' => $locationId, 'zip' => $zip, 'city' => $city, 'state' => $state, 'location' => $location, 'country' => $country);
    }

    /**
     * Get UPS Small Web Quotes
     * @param $request_data
     * @return Quotes Json
     */
    function ups_small_get_quotes($request_data)
    {
        // Check response from session
        $currentData = md5(wp_json_encode($request_data));
        $requestFromSession = WC()->session->get('previousRequestData');
        $requestFromSession = ((is_array($requestFromSession)) && (!empty($requestFromSession))) ? $requestFromSession : array();

        if (isset($requestFromSession[$currentData]) && (!empty($requestFromSession[$currentData]))) {
            $requestFromSession = json_decode($requestFromSession[$currentData]);
            // Eniture debug mood
            do_action("eniture_debug_mood", "Build Query session (ups small)", http_build_query($request_data));
            do_action("eniture_debug_mood", "Quotes Response session (ups small)", $requestFromSession);
            return $requestFromSession;
        }

        if (is_array($request_data) && count($request_data) > 0 && !empty($request_data['ups_small_pkg_senderZip'])) {
            $ups_small_curl_obj = new Eniture_UPS_Small_Curl_Request();
            $output = $ups_small_curl_obj->ups_small_get_curl_response(ENITURE_UPS_DOMAIN_HITTING_URL . '/s/ups/ups_quotes.php', $request_data);

            // requestKeySBS
            if (isset($request_data['requestKeySBS']) && strlen($request_data['requestKeySBS']) > 0) {
                $request_data['requestKey'] = $request_data['requestKeySBS'];
            } else {
                $request_data['requestKey'] = (isset($request_data['requestKey'])) ? $request_data['requestKey'] : md5(microtime() . wp_rand());
            }

            // Set response in session
            $response = json_decode($output, TRUE);

            if (isset($response['ups_rate']) && !empty($response['ups_rate'])) {
                if (isset($response['autoResidentialSubscriptionExpired']) &&
                    ($response['autoResidentialSubscriptionExpired'] == 1)) {
                    $flag_api_response = "no";
                    $request_data['residential_detecion_flag'] = $flag_api_response;
                    $currentData = md5(wp_json_encode($request_data));
                }

                $requestFromSession[$currentData] = $output;
                WC()->session->set('previousRequestData', $requestFromSession);
            }

            // Eniture debug mood
            do_action("eniture_debug_mood", "Quotes Response (ups small)", json_decode($output));

            return json_decode($output);
        }
    }

    /**
     * Get Calculate service level markup
     * @param $total_charge
     * @param $international_markup
     */
    function ups_small_calculate_service_level_markup($total_charge, $international_markup)
    {
        $international_markup = !$total_charge > 0 ? 0 : $international_markup;
        $grandTotal = 0;
        if (floatval($international_markup)) {
            $pos = strpos($international_markup, '%');
            if ($pos > 0) {
                $rest = substr($international_markup, $pos);
                $exp = explode($rest, $international_markup);
                $get = $exp[0];
                $percnt = $get / 100 * $total_charge;
                $grandTotal += $total_charge + $percnt;
            } else {
                $grandTotal += $total_charge + $international_markup;
            }
        } else {
            $grandTotal += $total_charge;
        }
        return $grandTotal;
    }

    /**
     * Return the array
     * @param object $result
     * @return object
     */
    public function en_bin_packaging_detail($result)
    {
        return isset($result->binPackaging->response) ? $result->binPackaging->response : [];
    }

    /**
     * Get Shipping Array For Multiple Shipment
     * @param $quotes
     * @return Shipping Rates Array
     */
    function en_ups_small_quotes($result, $product_detail, $quote_settings)
    {
        $en_box_fee = $en_count_rates = 0;
        $active_services = $this->service_options_array();
        $simple_services = $this->ups_simple_service_options();

        $en_rates = [];
        $en_sorting_rates = [];

        $no_quotes = true;
        $ups_quotes = (isset($result->q) && !empty($result->q)) ? $result->q : [];
        $ups_quotes = (empty($ups_quotes) && isset($result->ups_rate) && !empty($result->ups_rate)) ? $result->ups_rate : $ups_quotes;
        if (isset($result->ups_rate) && !empty($result->ups_rate)) {
            foreach ($result->ups_rate as $val) {
                (isset($val->RatedShipment)) ? $no_quotes = false : '';
            }
        }

        if (isset($result->q) && !empty($result->q)) {
            foreach ($result->q as $val) {
                (isset($val->Service)) ? $no_quotes = false : '';
            }
        }

        if (!empty($ups_quotes) && (!empty($active_services) || !empty($simple_services))) {
            $en_always_accessorial = [];
            $multiple_accessorials[] = ['S'];

            $this->forcefully_residential_delivery ? $multiple_accessorials[] = ['R'] : '';
            $ups_small_hazardous_materials_shipments = get_option('ups_small_hazardous_materials_shipments');
            (get_option('ups_small_quote_as_residential_delivery') == 'yes') ? $en_always_accessorial[] = 'R' : '';
            $hazardous_material = isset($product_detail['hazardous_status']) && $product_detail['hazardous_status'] == 'yes' ? TRUE : FALSE;
            $en_auto_residential_status = !in_array('R', $en_always_accessorial) && isset($result->residentialStatus) && $result->residentialStatus == 'r' ? 'r' : '';

            $handling_fee = get_option('ups_small_hand_fee_mark_up');
            ($hazardous_material) ? $en_always_accessorial[] = 'H' : '';
            $meta_data['accessorials'] = wp_json_encode($en_always_accessorial);
            $meta_data['sender_origin'] = (isset($product_detail['sender_origin'])) ? $product_detail['sender_origin'] : '';
            $meta_data['product_name'] = (isset($product_detail['product_name'])) ? $product_detail['product_name'] : '';
            $meta_data['plugin_name'] = "upsSmall";
            $meta_data['quote_settings'] = $quote_settings;

            // FDO
            $en_fdo_meta_data = (isset($product_detail['en_fdo_meta_data'])) ? $product_detail['en_fdo_meta_data'] : '';
            // FDO
            $en_auto_residential_status == 'r' ? $en_fdo_meta_data['accessorials']['residential'] = true : '';

            $package_bins = (isset($product_detail['package_bins'])) ? $product_detail['package_bins'] : [];
            $en_box_fee_arr = (isset($product_detail['en_box_fee']) && !empty($product_detail['en_box_fee'])) ? $product_detail['en_box_fee'] : [];
            $en_multi_box_qty = (isset($product_detail['en_multi_box_qty']) && !empty($product_detail['en_multi_box_qty'])) ? $product_detail['en_multi_box_qty'] : [];
            $products = (isset($product_detail['products'])) ? $product_detail['products'] : [];
            $hazardousItems = (isset($product_detail['hazardousItems'])) ? $product_detail['hazardousItems'] : [];
            $hazardousItemsForNonSbs = (isset($product_detail['hazardousItemsCount'])) ? $product_detail['hazardousItemsCount'] : [];

            if (isset($en_box_fee_arr) && is_array($en_box_fee_arr) && !empty($en_box_fee_arr)) {
                foreach ($en_box_fee_arr as $en_box_fee_key => $en_box_fee_value) {
                    $en_multi_box_quantity = (isset($en_multi_box_qty[$en_box_fee_key])) ? $en_multi_box_qty[$en_box_fee_key] : 0;
                    $en_box_fee += $en_box_fee_value * $en_multi_box_quantity;
                }
            }

            $bin_packaging_filtered = $this->en_bin_packaging_detail($result);
            $bin_packaging_filtered = !empty($bin_packaging_filtered) ? json_decode(wp_json_encode($bin_packaging_filtered), TRUE) : [];

            // Bin Packaging Box Fee|Product Title Start
            $en_box_total_price = 0;
            $en_harzardous_pckg_count = 0;
            if (isset($bin_packaging_filtered['bins_packed']) && !empty($bin_packaging_filtered['bins_packed'])) {
                foreach ($bin_packaging_filtered['bins_packed'] as $bins_packed_key => $bins_packed_value) {
                    $bin_data = (isset($bins_packed_value['bin_data'])) ? $bins_packed_value['bin_data'] : [];
                    $bin_items = (isset($bins_packed_value['items'])) ? $bins_packed_value['items'] : [];
                    $bin_id = (isset($bin_data['id'])) ? $bin_data['id'] : '';
                    $bin_type = (isset($bin_data['type'])) ? $bin_data['type'] : '';
                    $bins_detail = (isset($package_bins[$bin_id])) ? $package_bins[$bin_id] : [];
                    $en_box_price = (isset($bins_detail['box_price'])) ? $bins_detail['box_price'] : 0;
                    $en_box_total_price += $en_box_price;

                    $prev_bin_item_id = $prev_bin_id = '';
                    foreach ($bin_items as $bin_items_key => $bin_items_value) {
                        $bin_item_id = (isset($bin_items_value['id'])) ? $bin_items_value['id'] : '';
                        $get_product_name = (isset($products[$bin_item_id])) ? $products[$bin_item_id] : '';
                        if ($bin_type == 'item') {
                            $bin_packaging_filtered['bins_packed'][$bins_packed_key]['bin_data']['product_name'] = $get_product_name;
                        }

                        if (isset($bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key])) {
                            $bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key]['product_name'] = $get_product_name;
                        }

                        // Hazardous package check
                        $is_hazmat_item = isset($hazardousItems[$bin_item_id]) && ($bin_item_id != $prev_bin_item_id) && ($bin_id != $prev_bin_id) ? $hazardousItems[$bin_item_id] : false;
                        if ($is_hazmat_item) {
                            $en_harzardous_pckg_count++;
                        }

                        $prev_bin_item_id = $bin_item_id;
                        $prev_bin_id = $bin_id;
                    }
                }
            } else {
                $en_harzardous_pckg_count = $hazardousItemsForNonSbs;
            }

            if ($en_harzardous_pckg_count == 0) {
                $en_harzardous_pckg_count = 1;
            }

            $en_box_total_price += $en_box_fee;
            $meta_data['bin_packaging'] = wp_json_encode($bin_packaging_filtered);
            // FDO
            $en_fdo_meta_data['bin_packaging'] = $bin_packaging_filtered;
            $en_fdo_meta_data['bins'] = $package_bins;

            $InstorPickupLocalDelivery = (isset($result->InstorPickupLocalDelivery)) ? $result->InstorPickupLocalDelivery : [];
            $InstorPickupLocalDelivery = (empty($InstorPickupLocalDelivery) && isset($ups_quotes->InstorPickupLocalDelivery)) ? $ups_quotes->InstorPickupLocalDelivery : $InstorPickupLocalDelivery;
            $rate_source = get_option('ups_small_rate_source');
            $new_api_enaled = get_option('ups_api_endpoint') == 'ups_new_api';

            foreach ($ups_quotes as $service_key => $val) {
                if (isset($simple_services[$service_key])) {
                    $ups_simple_price = (isset($val->rate)) ? $val->rate : 0;
                    if (!$ups_simple_price > 0) {
                        continue;
                    }

                    // Only quote ground service for hazardous materials shipments
                    if ($service_key != 'usrg' && $hazardous_material && $ups_small_hazardous_materials_shipments == "yes") {
                        continue;
                    }

                    // product level markup
                    if(is_array($product_detail['product_level_markup_arr']) && count($product_detail['product_level_markup_arr']) > 0){
                        $ups_simple_price = $this->add_product_level_markup($ups_simple_price, $product_detail['product_level_markup_arr']);
                    }
                    
                    // origin level markup
                    if(!empty($product_detail['origin_markup'])){
                        $ups_simple_price = $this->ups_small_calculate_service_level_markup($ups_simple_price, $product_detail['origin_markup']);
                    }

                    // Adding service level markup
                    if (isset($simple_services[$service_key]['markup']) && !empty($simple_services[$service_key]['markup']) && isset($ups_simple_price) && !empty($ups_simple_price)) {
                        $service_markup = $simple_services[$service_key]['markup'];
                        $ups_simple_price = $this->ups_small_calculate_service_level_markup($ups_simple_price, $service_markup);
                    }

                    $service_title = $this->ups_carrier_service_name_by_code($service_key);
                    $en_simple_service = array(
                        'id' => $service_key,
                        'cost' => $ups_simple_price,
                        'rate' => $ups_simple_price,
                        'transit_time' => '',
                        'delivery_days' => '',
                        'title' => $service_title,
                        'label' => $service_title,
                        'label_as' => $service_title,
                        'service_name' => $service_key,
                        'meta_data' => $meta_data,
                        'origin_markup' => $product_detail['origin_markup'],
                        'product_level_markup_arr' => $product_detail['product_level_markup_arr'],
                        'surcharges' => $this->en_get_accessorials_prices($surcharges, $en_always_accessorial, $en_auto_residential_status, $grand_total),
                        'plugin_name' => 'upsSmall',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    );

                    // Cost of the rates
                    $en_simple_type = 'S';
                    $en_rates[$en_simple_type][$en_count_rates] = $en_simple_service;
                    $en_sorting_rates
                    [$en_simple_type]
                    [$en_count_rates]['cost'] = // Used for sorting of rates
                    $en_rates
                    [$en_simple_type]
                    [$en_count_rates]['cost'] = $ups_simple_price;

                    $en_rates[$en_simple_type][$en_count_rates]['label_sufex'] = ['S'];

                    // FDO
                    $en_fdo_meta_data['rate'] = $en_rates[$en_simple_type][$en_count_rates];
                    if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                        unset($en_fdo_meta_data['rate']['meta_data']);
                    }

                    $en_fdo_meta_data['quote_settings'] = $quote_settings;
                    $en_rates[$en_simple_type][$en_count_rates]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                    $en_rates[$en_simple_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials'] = [];
                    $en_count_rates++;
                    continue;
                }

                // UPS Ground with Freight Pricing
                if ($service_key == 'GFP') {
                    $val = $this->format_GFP_service_response($service_key, $val);
                }

                if(isset($val->Service)){
                    $rate_arr = $val;
                }else if(isset($val->RatedShipment)){
                    $rate_arr = $val->RatedShipment;
                }else{
                    $rate_arr = [];
                }

                if ((isset($rate_arr->Service->Code) && isset($active_services[$rate_arr->Service->Code]) && !empty($active_services[$rate_arr->Service->Code])) || $no_quotes) {
                    
                    // List (retail) rates
                    if (empty($rate_source) || $rate_source == false || $rate_source == 'list_rates') {
                        $MonetaryValue = (isset($rate_arr->TotalCharges->MonetaryValue) ? $rate_arr->TotalCharges->MonetaryValue : 0);
                    } else {
                        $MonetaryValue = 0;

                        // Negotiated rates
                        if ($new_api_enaled && isset($rate_arr->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
                            $MonetaryValue = $rate_arr->NegotiatedRateCharges->TotalCharge->MonetaryValue;
                        } elseif (isset($rate_arr->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue)) {
                            $MonetaryValue = $rate_arr->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                        }

                        if ($MonetaryValue == 0) {
                            $MonetaryValue = isset($rate_arr->TotalCharges->MonetaryValue) ? $rate_arr->TotalCharges->MonetaryValue : 0;
                        }
                    }

                    // Add hazardous fee
                    $MonetaryValue = $this->addHazardousFee($rate_arr->Service->Code, $MonetaryValue, $hazardous_material, $en_harzardous_pckg_count);

                    // product level markup
                    if(is_array($product_detail['product_level_markup_arr']) && count($product_detail['product_level_markup_arr']) > 0){
                        $MonetaryValue = $this->add_product_level_markup($MonetaryValue, $product_detail['product_level_markup_arr']);
                    }
                    
                    // origin level markup
                    if(!empty($product_detail['origin_markup'])){
                        $MonetaryValue = $this->ups_small_calculate_service_level_markup($MonetaryValue, $product_detail['origin_markup']);
                    }

                    // Adding service level markup
                    if (isset($active_services[$rate_arr->Service->Code]['markup']) && !empty($active_services[$rate_arr->Service->Code]['markup']) && isset($MonetaryValue) && !empty($MonetaryValue)) {
                        $service_markup = $active_services[$rate_arr->Service->Code]['markup'];
                        $MonetaryValue = $this->ups_small_calculate_service_level_markup($MonetaryValue, $service_markup);
                    }

                    $cost = $MonetaryValue;
                    $service_title = $this->ups_carrier_service_name_by_code($rate_arr->Service->Code);

                    // Adding markup / handling fee
                    if ($handling_fee != "") {
                        $grand_total = $this->calculate_handeling_fee($handling_fee, $cost);
                    } else {
                        $grand_total = $cost;
                    }

                    $surcharges = [];
                    $transit_time = (isset($val->deliveryDate)) ? $val->deliveryDate : '';
                    $delivery_days = (isset($val->totalTransitTimeInDays)) ? $val->totalTransitTimeInDays : '';
                    $delivery_time = (isset($val->deliveryTime)) ? $val->deliveryTime : '';
                    $service_type = 'ups_small';
                    $service_name = (isset($rate_arr->Service->Code)) ? $rate_arr->Service->Code : '';

                    if (($hazardous_material) && ($service_name != 03) && $service_name != 'GFP') {
                        if ($ups_small_hazardous_materials_shipments == "yes") {
                            continue;
                        }
                    }

                    // Add box fee
                    $en_service_cost = $grand_total > 0 ? $grand_total + (float)$en_box_total_price : 0;

                    $en_service = array(
                        'id' => $service_type . "_" . $service_name,
                        'service_type' => $service_type . "_" . $service_name,
                        'cost' => $en_service_cost,
                        'rate' => $en_service_cost,
                        'transit_time' => $transit_time,
                        'delivery_days' => $delivery_days,
                        'delivery_time' => $delivery_time,
                        'title' => $service_title,
                        'label' => $service_title,
                        'label_as' => $service_title,
                        'service_name' => $service_name,
                        'meta_data' => $meta_data,
                        'origin_markup' => $product_detail['origin_markup'],
                        'product_level_markup_arr' => $product_detail['product_level_markup_arr'],
                        'surcharges' => $this->en_get_accessorials_prices($surcharges, $en_always_accessorial, $en_auto_residential_status, $grand_total),
                        'plugin_name' => 'upsSmall',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture',
                        'override_rate' => isset($val->overrideRate) && $val->overrideRate
                    );

                    foreach ($multiple_accessorials as $multiple_accessorials_key => $accessorial) {
                        $en_fliped_accessorial = array_flip($accessorial);

                        // When auto-rad detected
                        (!$this->forcefully_residential_delivery && $en_auto_residential_status == 'r') ? $accessorial[] = 'R' : '';

                        $en_extra_charges = array_diff_key((isset($en_service['surcharges']) ? $en_service['surcharges'] : []), $en_fliped_accessorial);

                        $en_accessorial_type = implode('', $accessorial);
                        $en_rates[$en_accessorial_type][$en_count_rates] = $en_service;

                        // Service name changed GROUND HOME DELIVERY to FEDEX GROUND
                        if ((isset($en_service['service_type'], $en_service['title'], $en_service['label']) &&
                                $service_type == 'GROUND_HOME_DELIVERY') &&
                            $this->forcefully_residential_delivery &&
                            !in_array('R', $accessorial)) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['service_type'] = 'FEDEX_GROUND_home_ground_pricing';
                            $en_rates[$en_accessorial_type][$en_count_rates]['title'] = 'FedEx Ground';
                            $en_rates[$en_accessorial_type][$en_count_rates]['label'] = 'FedEx Ground';
                        }

                        // Cost of the rates
                        $en_sorting_rates
                        [$en_accessorial_type]
                        [$en_count_rates]['cost'] = // Used for sorting of rates
                        $en_rates
                        [$en_accessorial_type]
                        [$en_count_rates]['cost'] = (isset($en_service['cost']) ? $en_service['cost'] : 0) - array_sum($en_extra_charges);

                        $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['label_sufex'] = wp_json_encode($accessorial);
                        $en_rates[$en_accessorial_type][$en_count_rates]['label_sufex'] = $accessorial;
                        $alphabets = 'abcdefghijklmnopqrstuvwxyz';
                        $rand_string = substr(str_shuffle(str_repeat($alphabets, wp_rand(1, 10))), 1, 10);
                        if (isset($en_rates[$en_accessorial_type][$en_count_rates]['service_name'])) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['id'] = $en_rates[$en_accessorial_type][$en_count_rates]['service_name'];
                        } else if (isset($en_rates[$en_accessorial_type][$en_count_rates]['id'])) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['id'] = $en_rates[$en_accessorial_type][$en_count_rates]['id'];
                        } else {
                            $en_rates[$en_accessorial_type][$en_count_rates]['id'] = $rand_string;
                        }

                        // FDO
                        $en_fdo_meta_data['rate'] = $en_rates[$en_accessorial_type][$en_count_rates];
                        if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                            unset($en_fdo_meta_data['rate']['meta_data']);
                        }

                        $en_fdo_meta_data['quote_settings'] = $quote_settings;
                        $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                        $en_count_rates++;
                    }
                }
            }
        }

        $en_rates['en_sorting_rates'] = $en_sorting_rates;
        $en_rates['InstorPickupLocalDelivery'] = $InstorPickupLocalDelivery;

        return $en_rates;
    }

    /**
     * Get accessorials prices from api response
     * @param array $accessorials
     * @return array
     */
    public function en_get_accessorials_prices($accessorials, $en_always_accessorial, $en_auto_residential_status, $total_price)
    {
        $surcharges = [];
        $fuel_surcharges = 0;
        $mapp_surcharges = [
            'RESIDENTIAL_DELIVERY' => 'R',
        ];

        if (isset($accessorials->SurchargeType) && $accessorials->SurchargeType == 'FUEL') {
            $fuel_surcharges = $accessorials->Amount->Amount;
        }

        foreach ($accessorials as $key => $accessorial) {
            $key = (isset($accessorial->SurchargeType)) ? $accessorial->SurchargeType : '';
            ($key == 'FUEL') ? $fuel_surcharges = $accessorial->Amount->Amount : '';

            if (isset($mapp_surcharges[$key])) {
                $accessorial = (isset($accessorial->Amount->Amount)) ? $accessorial->Amount->Amount : 0;
                in_array($mapp_surcharges[$key], $en_always_accessorial) && !$this->forcefully_residential_delivery ?
                    $accessorial = 0 : '';
                $en_auto_residential_status == 'r' && $mapp_surcharges[$key] == 'R' && !$this->forcefully_residential_delivery ?
                    $accessorial = 0 : '';
                $surcharges[$mapp_surcharges[$key]] = $accessorial;
            }
        }

        if (isset($surcharges['R']) && $surcharges['R'] > 0) {
            $residential_surcharges = $surcharges['R'];
            $fuel_percentage = ($fuel_surcharges * 100) / ($total_price - $fuel_surcharges);
            $surcharges['R'] = $residential_surcharges + ($residential_surcharges * $fuel_percentage / 100);
        }

        return $surcharges;
    }

    /**
     * Calculate Handling Fee For Each Shipment
     * @param $handeling_fee
     * @param $total
     * @return Grand Total After Calculating Handling Fee
     */
    function calculate_handeling_fee($handeling_fee, $total)
    {
        $handeling_fee = !$total > 0 ? 0 : $handeling_fee;
        $grandTotal = 0;
        if (floatval($handeling_fee)) {
            $pos = strpos($handeling_fee, '%');
            if ($pos > 0) {
                $rest = substr($handeling_fee, $pos);
                $exp = explode($rest, $handeling_fee);
                $get = $exp[0];
                $percnt = $get / 100 * $total;
                $grandTotal += $total + $percnt;
            } else {
                $grandTotal += $total + $handeling_fee;
            }
        } else {
            $grandTotal += $total;
        }
        return $grandTotal;
    }

    /**
     * UPS Selected Services From Admin Configuration
     * @return All Selected Services Array
     */
    function service_options_array()
    {
        $active_services = array();

        if (get_option('ups_small_pkg_Next_Day_Air_Early_AM') == 'yes') {
            $active_services["14"] = ['name' => 'ups_small_pkg_Next_Day_Air_Early_AM', 'markup' => get_option('ups_small_next_day_air_early_markup')];
        }

        if (get_option('ups_small_pkg_Standard') == 'yes') {
            $active_services["11"] = ['name' => 'ups_small_pkg_Standard', 'markup' => get_option('ups_small_standard_markup')];
        }

        if (get_option('ups_small_pkg_Next_Day_Air') == 'yes') {
            $active_services["01"] = ['name' => 'ups_small_pkg_Next_Day_Air', 'markup' => get_option('ups_small_next_day_air_markup')];
        }

        if (get_option('ups_small_pkg_Worldwide_Expedited') == 'yes') {
            $active_services["08"] = ['name' => 'ups_small_pkg_Worldwide_Expedited', 'markup' => get_option('ups_small_pkg_Worldwide_Expedited_markup')];
        }

        if (get_option('ups_small_pkg_Next_Day_Air_Saver') == 'yes') {
            $active_services["13"] = ['name' => 'ups_small_pkg_Next_Day_Air_Saver', 'markup' => get_option('ups_small_next_day_air_saver_markup')];
        }

        if (get_option('ups_small_pkg_Saver') == 'yes') {
            $active_services["65"] = ['name' => 'ups_small_pkg_Saver', 'markup' => get_option('ups_small_worldwide_saver_markup')];
        }

        if (get_option('ups_small_pkg_2nd_Day_Air_AM') == 'yes') {
            $active_services["59"] = ['name' => 'ups_small_pkg_2nd_Day_Air_AM', 'markup' => get_option('ups_small_2day_air_am_markup')];
        }

        if (get_option('ups_small_pkg_Worldwide_Express') == 'yes') {
            $active_services["07"] = ['name' => 'ups_small_pkg_Worldwide_Express', 'markup' => get_option('ups_small_worldwide_express_markup')];
        }

        if (get_option('ups_small_pkg_2nd_Day_Air') == 'yes') {
            $active_services["02"] = ['name' => 'ups_small_pkg_2nd_Day_Air', 'markup' => get_option('ups_small_2day_air_markup')];
        }

        if (get_option('ups_small_pkg_Worldwide_Express_Plus') == 'yes') {
            $active_services["54"] = ['name' => 'ups_small_pkg_Worldwide_Express_Plus', 'markup' => get_option('ups_small_worldwide_express_plus_markup')];
        }

        if (get_option('ups_small_pkg_3_Day_Select') == 'yes') {
            $active_services["12"] = ['name' => 'ups_small_pkg_3_Day_Select', 'markup' => get_option('ups_small_3day_select_markup')];
        }

        if (get_option('ups_small_pkg_Ground') == 'yes') {
            $active_services["03"] = ['name' => 'ups_small_pkg_Ground', 'markup' => get_option('ups_small_ground_markup')];
        }
        if (get_option('ups_surepost_less_than_1_lb') == 'yes') {
            $active_services["92"] = ['name' => 'ups_surepost_less_than_1_lb', 'markup' => get_option('ups_contract_services_markup')];
        }
        if (get_option('ups_surepost_1_lb_or_greater') == 'yes') {
            $active_services["93"] = ['name' => 'ups_surepost_1_lb_or_greater', 'markup' => get_option('ups_surepost_1_lb_or_greater_markup')];
        }
        if (get_option('ups_surepost_bound_printed_matter') == 'yes') {
            $active_services["94"] = ['name' => 'ups_surepost_bound_printed_matter', 'markup' => get_option('ups_surepost_bound_printed_matter_markup')];
        }
        if (get_option('ups_surepost_media_mail') == 'yes') {
            $active_services["95"] = ['name' => 'ups_surepost_media_mail', 'markup' => get_option('ups_surepost_media_mail_markup')];
        }
        if (get_option('ups_ground_with_freight_pricing') == 'yes') {
            $active_services["GFP"] = ['name' => 'ups_ground_with_freight_pricing', 'markup' => get_option('ups_ground_with_freight_pricing_markup')];
        }

        return $active_services;
    }

    /**
     * UPS simple Services From Admin Configuration
     * @return All Selected Services Array
     */
    function ups_simple_service_options()
    {
        $active_services = [];

        // USP simple rate
        if (get_option('ups_simple_rate_through_ground') == 'yes') {
            $active_services["usrg"] = ['name' => 'ups_simple_rate_through_ground', 'markup' => get_option('ups_simple_rate_through_ground_markup')];
        }
        if (get_option('ups_simple_rate_through_2nd_day_air') == 'yes') {
            $active_services["usr2"] = ['name' => 'ups_simple_rate_through_2nd_day_air', 'markup' => get_option('ups_simple_rate_through_2nd_day_air_markup')];
        }
        if (get_option('ups_simple_rate_through_next_day_air_saver') == 'yes') {
            $active_services["usr1"] = ['name' => 'ups_simple_rate_through_next_day_air_saver', 'markup' => get_option('ups_simple_rate_through_next_day_air_saver_markup')];
        }
        if (get_option('ups_simple_rate_through_3_day_select') == 'yes') {
            $active_services["usr3"] = ['name' => 'ups_simple_rate_through_3_day_select', 'markup' => get_option('ups_simple_rate_through_3_day_select_markup')];
        }

        return $active_services;
    }

    /**
     * Get Names Of UPS Selected Services
     * @param $code
     * @return Services Names
     */
    function ups_carrier_service_name_by_code($code)
    {
        $service_name = '';
        switch ($code) {
            case '14':
                $service_name = !empty(get_option('ups_small_next_day_air_early_label')) ? get_option('ups_small_next_day_air_early_label') : 'UPS Next Day Air Early';
                break;
            case '01':
                $service_name = !empty(get_option('ups_small_next_day_air_label')) ? get_option('ups_small_next_day_air_label') : 'UPS Next Day Air';
                break;
            case '13':
                $service_name = !empty(get_option('ups_small_next_day_air_saver_label')) ? get_option('ups_small_next_day_air_saver_label') : 'UPS Next Day Air Saver';
                break;
            case '59':
                $service_name = !empty(get_option('ups_small_2day_air_am_label')) ? get_option('ups_small_2day_air_am_label') : 'UPS 2nd Day Air A.M.';
                break;
            case '02':
                $service_name = !empty(get_option('ups_small_2day_air_label')) ? get_option('ups_small_2day_air_label') : 'UPS 2nd Day Air';
                break;
            case '12':
                $service_name = !empty(get_option('ups_small_3day_select_label')) ? get_option('ups_small_3day_select_label') : 'UPS 3 Day Select';
                break;
            case '03':
                $service_name = !empty(get_option('ups_small_ground_label')) ? get_option('ups_small_ground_label') : 'UPS Ground';
                break;
            case '11':
                $service_name = !empty(get_option('ups_small_standard_label')) ? get_option('ups_small_standard_label') : 'UPS Standard';
                break;
            case '08':
                $service_name = !empty(get_option('ups_small_pkg_Worldwide_Expedited_label')) ? get_option('ups_small_pkg_Worldwide_Expedited_label') : 'UPS  Expedited | UPS Worldwide Expedited';
                break;
            case '65':
                $service_name = !empty(get_option('ups_small_worldwide_saver_label')) ? get_option('ups_small_worldwide_saver_label') : 'UPS Express Saver | UPS Worldwide Saver';
                break;
            case '07':
                $service_name = !empty(get_option('ups_small_worldwide_express_label')) ? get_option('ups_small_worldwide_express_label') : 'UPS Express | UPS Worldwide Express';
                break;
            case '54':
                $service_name = !empty(get_option('ups_small_worldwide_express_plus_label')) ? get_option('ups_small_worldwide_express_plus_label') : 'UPS Express Plus | UPS Worldwide Express Plus';
                break;
            case '92':
                $service_name = !empty(get_option('ups_surepost_less_than_1_lb_label')) ? get_option('ups_surepost_less_than_1_lb_label') : 'UPS SurePost Less than 1LB';
                break;
            case '93':
                $service_name = !empty(get_option('ups_surepost_1_lb_or_greater_label')) ? get_option('ups_surepost_1_lb_or_greater_label') : 'UPS SurePost 1LB or greater';
                break;
            case '94':
                $service_name = !empty(get_option('ups_surepost_bound_printed_matter_label')) ? get_option('ups_surepost_bound_printed_matter_label') : 'UPS SurePost BPM';
                break;
            case '95':
                $service_name = !empty(get_option('ups_surepost_media_mail_label')) ? get_option('ups_surepost_media_mail_label') : 'UPS SurePost Media Mail';
                break;
            case 'GFP':
                $service_name = !empty(get_option('ups_ground_with_freight_pricing_label')) ? get_option('ups_ground_with_freight_pricing_label') : 'UPS Ground with Freight Pricing';
                break;

            // USP simple rate
            case 'usrg':
                $service_name = !empty(get_option('ups_simple_rate_through_ground_label')) ? get_option('ups_simple_rate_through_ground_label') : 'UPS Simple Rate - Ground';
                break;
            case 'usr3':
                $service_name = !empty(get_option('ups_simple_rate_through_3_day_select_label')) ? get_option('ups_simple_rate_through_3_day_select_label') : 'UPS Simple Rate - 3-day Select';
                break;
            case 'usr2':
                $service_name = !empty(get_option('ups_simple_rate_through_2nd_day_air_label')) ? get_option('ups_simple_rate_through_2nd_day_air_label') : 'UPS Simple Rate - 2-day Air';
                break;
            case 'usr1':
                $service_name = !empty(get_option('ups_simple_rate_through_next_day_air_saver_label')) ? get_option('ups_simple_rate_through_next_day_air_saver_label') : 'UPS Simple Rate - Next Day Air Saver';
                break;
        }

        return $service_name;
    }

    /**
     * Get adding hazardous fee Calculated Total Price
     * @param $code
     * @param $price
     * @param $hazardous
     * @return Total Price
     */
    function addHazardousFee($code, $price, $hazardous, $harzardous_items_count)
    {
        $short_cods = array('air' => array("14", "01", "13", "59", "12", "02", "08", "65", "07", "54", '1DA', '1DM', '1DP', "92", "93", "94", "95", "GFP"));
        $only_ground_hazardous = get_option('ups_small_quote_exclude_air_service');
        $air_hazardous = get_option('ups_small_air_hazardous_fee');
        $price = floatval($price);
        $hazardousFee = get_option('ups_small_ground_hazardous_fee');

        $sure_post = ['92', '93', '94', '95', 'GFP'];
        if (in_array($code, $sure_post)) {
            $hazardousFee = 0;
            $air_hazardous = 0;
        }

        if ($hazardous && $price != 0) {
            $this->hazardous_status = 1;

            if (!in_array($code, $short_cods['air']) && $hazardousFee != '' && $hazardousFee != 0) {
                /* Ground Hazardous */
                $price = $price + $hazardousFee * $harzardous_items_count;
            }

            if ($only_ground_hazardous != 'yes' && in_array($code, $short_cods['air']) && floatval($air_hazardous) != 0) {
                /* Air Hazardous */
                $price = $price + $air_hazardous * $harzardous_items_count;
            } else if ($only_ground_hazardous == 'yes' && in_array($code, $short_cods['air'])) {
                $price = 0;
            }
        }

        return $price;
    }

    /**
     * Add product level markup to the code
     */
    function add_product_level_markup($cost, $product_level_markup_arr){
            
        foreach($product_level_markup_arr as $pro_id => $markup){
            $cost = $this->ups_small_calculate_service_level_markup($cost, $markup);
        }

        return $cost;
    }

    function format_GFP_service_response($service_code, $service)
    {
        // handle service error
        $error = isset($service->soapenvBody->soapenvFault->faultcode) || (isset($service->severity) && $service->severity == 'ERROR');
        if ($error) return $service;
        
        // Legacy API response
        $formatted_service = $service;
        $currency = isset($formatted_service->soapenvBody->rateRateResponse->rateRatedShipment->rateTotalCharges->rateCurrencyCode) ? $formatted_service->soapenvBody->rateRateResponse->rateRatedShipment->rateTotalCharges->rateCurrencyCode : '';
        $monetary_value = isset($formatted_service->soapenvBody->rateRateResponse->rateRatedShipment->rateTotalCharges->rateMonetaryValue) ? $formatted_service->soapenvBody->rateRateResponse->rateRatedShipment->rateTotalCharges->rateMonetaryValue : 0;

        // New API response
        $currency = isset($formatted_service->RateResponse->RatedShipment->TotalCharges->CurrencyCode) ? $formatted_service->RateResponse->RatedShipment->TotalCharges->CurrencyCode : $currency;
        $monetary_value = isset($formatted_service->RateResponse->RatedShipment->TotalCharges->MonetaryValue) ? $formatted_service->RateResponse->RatedShipment->TotalCharges->MonetaryValue : $monetary_value;

        $formatted_service = (object)[
            'RatedShipment' => (object)[
                'Service' => (object)[
                    'Code' => $service_code
                ],
                'TotalCharges' => (object)[
                    'CurrencyCode' => $currency,
                    'MonetaryValue' => $monetary_value
                ]
            ]
        ];

        return $formatted_service;
    }

}
