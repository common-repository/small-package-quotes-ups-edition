<?php

/**
 * UPS Small WooComerce |  Shipping Calculation
 * @package     Woo-commerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UPS Small WooComerce |  Shipping Calculation Method
 */
function eniture_ups_small_init()
{
    if (!class_exists('Eniture_WC_ups_small')) {

        /**
         * UPS Small WooComerce | Shipping Calculation Class
         */
        class Eniture_WC_ups_small extends WC_Shipping_Method
        {

            public $smpkgFoundErr = array();
            public $filter_data = array();
            public $smpkgQuoteErr = array();
            public $order_detail;
            public $is_autoresid;
            public $accessorials;
            public $helper_obj;
            public $instore_pickup_and_local_delivery;
            public $group_small_shipments;
            public $web_service_inst;
            public $package_plugin;
            public $InstorPickupLocalDelivery;
            public $woocommerce_package_rates;
            public $quote_settings;
            public $shipment_type;
            public $eniture_rates = [];
            public $VersionCompat;
            public $en_not_returned_the_quotes = FALSE;
            public $minPrices = [];
            // FDO
            public $en_fdo_meta_data = [];
            // Virtual Products
            public $en_fdo_meta_data_third_party = [];

            /**
             * Woo-commerce Shipping Field Attributes
             * @param $instance_id
             */
            public function __construct($instance_id = 0)
            {
                error_reporting(0);
                $this->id = 'ups_small';
                $this->helper_obj = new Eniture_Ups_Small_Helper_Class();
                $this->instance_id = absint($instance_id);
                $this->method_title = __('UPS', 'small-package-quotes-ups-edition');
                $this->method_description = __('Parcel rates from UPS.', 'small-package-quotes-ups-edition');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->enabled = "yes";
                $this->title = "Small Package Quotes - UPS Edition";
                $this->init();
            }

            /**
             * Update UPS Small Woo-commerce Shipping Settings
             */
            function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Enable Woo-commerce Shipping For UPS Small
             */
            function init_form_fields()
            {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable / Disable', 'small-package-quotes-ups-edition'),
                        'type' => 'checkbox',
                        'label' => __('Enable This Shipping Service', 'small-package-quotes-ups-edition'),
                        'default' => 'no',
                        'id' => 'ups_small_enable_disable_shipping'
                    )
                );
            }

            /**
             * Virtual Products
             */
            public function en_virtual_products()
            {
                global $woocommerce;
                $products = $woocommerce->cart->get_cart();
                $items = $product_name = [];
                foreach ($products as $key => $product_obj) {
                    $product = $product_obj['data'];
                    $is_virtual = $product->get_virtual();

                    if ($is_virtual == 'yes') {
                        $attributes = $product->get_attributes();
                        $product_qty = $product_obj['quantity'];
                        $product_title = str_replace(array("'", '"'), '', $product->get_title());
                        $product_name[] = $product_qty . " x " . $product_title;

                        $meta_data = [];
                        if (!empty($attributes)) {
                            foreach ($attributes as $attr_key => $attr_value) {
                                $meta_data[] = [
                                    'key' => $attr_key,
                                    'value' => $attr_value,
                                ];
                            }
                        }

                        $items[] = [
                            'id' => $product_obj['product_id'],
                            'name' => $product_title,
                            'quantity' => $product_qty,
                            'price' => $product->get_price(),
                            'weight' => 0,
                            'length' => 0,
                            'width' => 0,
                            'height' => 0,
                            'type' => 'virtual',
                            'product' => 'virtual',
                            'sku' => $product->get_sku(),
                            'attributes' => $attributes,
                            'variant_id' => 0,
                            'meta_data' => $meta_data,
                        ];
                    }
                }

                $virtual_rate = [];

                if (!empty($items)) {
                    $virtual_rate = [
                        'id' => 'en_virtual_rate',
                        'label' => 'Virtual Quote',
                        'cost' => 0,
                    ];

                    $virtual_fdo = [
                        'plugin_type' => 'small',
                        'plugin_name' => 'ups_small',
                        'accessorials' => '',
                        'items' => $items,
                        'address' => '',
                        'handling_unit_details' => '',
                        'rate' => $virtual_rate,
                    ];

                    $meta_data = [
                        'sender_origin' => 'Virtual Product',
                        'product_name' => wp_json_encode($product_name),
                        'en_fdo_meta_data' => $virtual_fdo,
                    ];

                    $virtual_rate['meta_data'] = $meta_data;

                }

                return $virtual_rate;
            }

            /**
             * Multi shipment query
             * @param array $en_rates
             * @param string $accessorial
             */
            public function en_multi_shipment($en_rates, $accessorial, $origin)
            {
                $accessorial .= '_ups_small';
                $en_rates = (isset($en_rates) && (is_array($en_rates))) ? array_slice($en_rates, 0, 1) : [];
                $total_cost = array_sum($this->VersionCompat->enArrayColumn($en_rates, 'cost'));

                !$total_cost > 0 ? $this->en_not_returned_the_quotes = TRUE : '';

                $en_rates = !empty($en_rates) ? reset($en_rates) : [];
                $this->minPrices[$origin] = $en_rates;
                // FDO
                $this->en_fdo_meta_data[$origin] = (isset($en_rates['meta_data']['en_fdo_meta_data'])) ? $en_rates['meta_data']['en_fdo_meta_data'] : [];

                if (isset($this->eniture_rates[$accessorial])) {
                    $this->eniture_rates[$accessorial]['cost'] += $total_cost;
                } else {
                    $this->eniture_rates[$accessorial] = [
                        'id' => $accessorial,
                        'label' => 'Shipping',
                        'cost' => $total_cost,
                        'label_sufex' => str_split($accessorial),
                        'plugin_name' => 'upsSmall',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    ];
                }
            }

            /**
             * Single shipment query
             * @param array $en_rates
             * @param string $accessorial
             */
            public function en_single_shipment($en_rates, $accessorial, $origin)
            {
                $en_rates = isset($en_rates) && is_array($en_rates) ? $en_rates : [];
                $this->eniture_rates = array_merge($this->eniture_rates, $en_rates);
            }

            /**
             * Calculate Shipping Rates For UPS Small
             * @param $package
             * @return boolean
             */
            public function calculate_shipping($package = array(), $eniture_admin_order_action = false)
            {
                if (is_admin() && !wp_doing_ajax() && !$eniture_admin_order_action) {
                    return [];
                }

                $this->package_plugin = get_option('ups_small_package');

                $coupn = WC()->cart->get_coupons();
                if (isset($coupn) && !empty($coupn)) {
                    $free_shipping = $this->ups_shipping_coupon_rate($coupn);
                    if ($free_shipping == 'y')
                        return FALSE;
                }
                $label_sufex_arr = array();
                $ups_small_woo_obj = new Eniture_UPS_Small_Woo_Update_Changes();
                (strlen(WC()->customer->get_shipping_postcode()) > 0) ? $freight_zipcode = WC()->customer->get_shipping_postcode() : $freight_zipcode = $ups_small_woo_obj->ups_small_postcode();
                $get_packg_obj = new Eniture_UPS_Small_Shipping_Get_Package();
                $ups_small_res_inst = new Eniture_UPS_Get_Shipping_Quotes();

                $this->web_service_inst = $ups_small_res_inst;
                $this->VersionCompat = new Eniture_VersionCompat();
                $quotes = array();

                $this->ups_get_hazardous_fields();

                // Free shipping
                if ($this->quote_settings['handling_fee'] == '-100%') {
                    $rates = array(
                        'id' => 'ups_small:' . 'free',
                        'label' => 'Free Shipping',
                        'cost' => 0,
                        'plugin_name' => 'upsSmall',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    );
                    $this->add_rate($rates);
                    
                    return [];
                }

                $ups_small_package = $get_packg_obj->group_ups_small_shipment($package, $ups_small_res_inst, $freight_zipcode);
                // Apply hide methods shipping rules
                $shipping_rules_applied = $get_packg_obj->apply_shipping_rules($ups_small_package);
                if ($shipping_rules_applied) {
                    return [];
                }

                // Suppress small rates when weight threshold is met
                $supress_parcel_rates = apply_filters('en_suppress_parcel_rates_hook', '');
                if (!empty($ups_small_package) && is_array($ups_small_package) && $supress_parcel_rates) {
                    foreach ($ups_small_package as $org_id => $pckg) {
                        $total_shipment_weight = 0;

                        $shipment_items = !empty($pckg['items']) ? $pckg['items'] : []; 
                        foreach ($shipment_items as $item) {
                            $total_shipment_weight += (floatval($item['productWeight']) * $item['productQty']);
                        }

                        $ups_small_package[$org_id]['shipment_weight'] = $total_shipment_weight;
                        $weight_threshold = get_option('en_weight_threshold_lfq');
                        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
                        
                        if ($total_shipment_weight > $weight_threshold) {
                            $ups_small_package[$org_id]['is_shipment'] = 'ltl';
                            $ups_small_package[$org_id]['origin']['ptype'] = 'ltl';
                        }
                    }
                }
                
                $SmPkgWebServiceArr = $ups_small_res_inst->ups_small_shipping_array($ups_small_package, $package, $this->package_plugin);
                $this->instore_pickup_and_local_delivery = FALSE;

                if ($SmPkgWebServiceArr === "ltl") {
                    return FALSE;
                }

                foreach ($SmPkgWebServiceArr as $locId => $sPackage) {

                    $package_bins = (isset($sPackage['bins'])) ? $sPackage['bins'] : [];
                    $en_box_fee = (isset($sPackage['en_box_fee'])) ? $sPackage['en_box_fee'] : [];
                    $en_multi_box_qty = (isset($sPackage['ups_small_pkg_product_quantity'])) ? $sPackage['ups_small_pkg_product_quantity'] : [];
                    $ups_bins = (isset($sPackage['ups_bins'])) ? $sPackage['ups_bins'] : [];
                    $hazardous_status = (isset($sPackage['hazardous_status'])) ? $sPackage['hazardous_status'] : '';
                    $package_bins = !empty($ups_bins) ? $package_bins + $ups_bins : $package_bins;
                    if (!isset($sPackage['ups_small_pkg_senderZip'])) {
                        continue;
                    }

                    $speed_ship_senderZip = $sPackage['ups_small_pkg_senderZip'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['product_name'] = wp_json_encode($sPackage['product_name']);
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['products'] = $sPackage['products'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['sender_origin'] = $sPackage['sender_origin'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['package_bins'] = $package_bins;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['en_box_fee'] = $en_box_fee;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['en_multi_box_qty'] = $en_multi_box_qty;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['hazardous_status'] = $hazardous_status;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['origin_markup'] = $sPackage['origin_markup'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['product_level_markup_arr'] = $sPackage['product_level_markup_arr'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['exempt_ground_transit_restriction'] = (isset($sPackage['exempt_ground_transit_restriction'])) ? $sPackage['exempt_ground_transit_restriction'] : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['hazardousItems'] = $sPackage['hazardousItems'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['hazardousItemsCount'] = $sPackage['hazardousItemsCount'];

                    // FDO
                    $en_fdo_meta_data = (isset($sPackage['en_fdo_meta_data'])) ? $sPackage['en_fdo_meta_data'] : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['en_fdo_meta_data'] = $en_fdo_meta_data;

                    if ($sPackage != 'ltl') {

                        $quotesValue = $ups_small_res_inst->ups_small_get_quotes($sPackage);
                        
                        if(!(isset($sPackage['exempt_ground_transit_restriction']) && $sPackage['exempt_ground_transit_restriction'] == 'yes')){
                            $ups_transit_days = new Eniture_UpsSmallTransitDays();
                            $quotesValue = $ups_transit_days->ups_enable_disable_ups_ground($quotesValue);
                        }

                        $hazardousIndex = array(
                            "hazardousMaterial" => $sPackage["hazardousMaterial"]
                        );

                        $quotes[$locId] = (object)array_merge((array)$quotesValue, (array)$hazardousIndex);
                    }
                }
                // Virtual products
                $virtual_rate = $this->en_virtual_products();

                $en_is_shipment = (count($quotes) > 1) || !empty($virtual_rate) ? 'en_multi_shipment' : 'en_single_shipment';

                $this->quote_settings['shipment'] = $en_is_shipment;
                $this->eniture_rates = [];

                $en_rates = $quotes;
                
                foreach ($en_rates as $origin => $step_for_rates) {
                    // Apply override rates shipping rules
                    $step_for_rates = $get_packg_obj->apply_shipping_rules($ups_small_package, true, $step_for_rates, $origin);
                    $product_detail = (isset($this->web_service_inst->product_detail[$origin])) ? $this->web_service_inst->product_detail[$origin] : array();
                    $filterd_rates = $this->web_service_inst->en_ups_small_quotes($step_for_rates, $product_detail, $this->quote_settings);
                    $en_sorting_rates = (isset($filterd_rates['en_sorting_rates'])) ? $filterd_rates['en_sorting_rates'] : "";

                    $this->InstorPickupLocalDelivery = (isset($filterd_rates['InstorPickupLocalDelivery'])) ? $filterd_rates['InstorPickupLocalDelivery'] : "";
                    if (isset($filterd_rates['en_sorting_rates']))
                        unset($filterd_rates['en_sorting_rates']);

                    if (isset($filterd_rates['InstorPickupLocalDelivery']))
                        unset($filterd_rates['InstorPickupLocalDelivery']);

                    if (is_array($filterd_rates) && !empty($filterd_rates)) {
                        foreach ($filterd_rates as $accessorial => $service) {
                            (!empty($filterd_rates[$accessorial])) ? array_multisort($en_sorting_rates[$accessorial], SORT_ASC, $filterd_rates[$accessorial]) : $en_sorting_rates[$accessorial] = [];
                            $this->$en_is_shipment($filterd_rates[$accessorial], $accessorial, $origin);
                        }
                    } else {
                        $this->en_not_returned_the_quotes = TRUE;
                    }
                }
                if ($this->en_not_returned_the_quotes) {
                    return [];
                }
                if ($en_is_shipment == 'en_single_shipment') {
                    // In-store pickup and local delivery
                    $instore_pickup_local_devlivery_action = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
                    if ($this->web_service_inst->en_wd_origin_array['suppress_local_delivery'] == "1" && (!is_array($instore_pickup_local_devlivery_action))) {
                        $this->eniture_rates = apply_filters('suppress_local_delivery', $this->eniture_rates, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);
                    }
                }
                $rad_status = true;
                $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                    if(get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                        $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                    }
                }
                $accessorials = $rad_status == true ? ['R' => 'residential delivery'] : [];

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);

                // Images for FDO
                $image_urls = apply_filters('en_fdo_image_urls_merge', []);
                // Virtual products
                if (!empty($virtual_rate)) {
                    $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                    $this->en_fdo_meta_data_third_party = !empty($this->en_fdo_meta_data_third_party) ? array_merge($this->en_fdo_meta_data_third_party, $en_virtual_fdo_meta_data) : $en_virtual_fdo_meta_data;
                }
                $en_rates = $this->eniture_rates;

                do_action("eniture_debug_mood", "Ups Small Eniture Quotes Rates", $en_rates);
                foreach ($en_rates as $accessorial => $rate) {

                    // Show delivery estimates
                    if ($en_is_shipment == 'en_single_shipment') {

                        $ups_small_delivey_estimate = get_option('ups_small_delivery_estimates');

                        if (isset($ups_small_delivey_estimate) && !empty($ups_small_delivey_estimate) && $ups_small_delivey_estimate != 'dont_show_estimates') {
                            if ($ups_small_delivey_estimate == 'delivery_date' && !empty($rate['transit_time'])) {
                                $date_label = $this->get_estimated_delivery_date_label($rate);
                                $rate['label'] .= ' (' . $date_label . ')';
                            } else if ($ups_small_delivey_estimate == 'delivery_days' && !empty($rate['delivery_days'])) {
                                $custom_day_format = get_option('estimated_delivery_days_format');
                                $day_label = !empty($custom_day_format) ? $custom_day_format : 'Intransit days';
                                $rate['label'] .= ' (' . $day_label . ': ' . $rate['delivery_days'] . ')';
                            }
                        }
                    }

                    if (isset($rate['label_sufex']) && !empty($rate['label_sufex'])) {
                        $label_sufex = array_intersect_key($accessorials, array_flip($rate['label_sufex']));
                        $rate['label'] .= (!empty($label_sufex)) ? ' with ' . implode(' and ', $label_sufex) : '';

                        // Order widget detail set
                        // FDO
                        if (isset($this->minPrices) && !empty($this->minPrices)) {
                            $rate['minPrices'] = $this->minPrices;
                            $rate['meta_data']['min_prices'] = wp_json_encode($this->minPrices);
                            $rate['meta_data']['en_fdo_meta_data']['data'] = array_values($this->en_fdo_meta_data);
                            // Virtual Products
                            (!empty($this->en_fdo_meta_data_third_party)) ? $rate['meta_data']['en_fdo_meta_data']['data'] = array_merge($rate['meta_data']['en_fdo_meta_data']['data'], $this->en_fdo_meta_data_third_party) : '';
                            $rate['meta_data']['en_fdo_meta_data']['shipment'] = 'multiple';
                            $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($rate['meta_data']['en_fdo_meta_data']);
                        } else {
                            $en_set_fdo_meta_data['data'] = [$rate['meta_data']['en_fdo_meta_data']];
                            $en_set_fdo_meta_data['shipment'] = 'sinlge';
                            $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);
                        }

                        // Images for FDO
                        $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);

                    }

                    $override_rate = isset($rate['override_rate']) ? $rate['override_rate'] : false;
                    if (isset($rate['cost']) && $rate['cost'] > 0 || $override_rate) {
                        $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? 'ups_small:' . $rate['id'] : '';
                        $this->add_rate($rate);
                        $en_rates[$accessorial] = array_merge($en_rates[$accessorial], $rate);
                    }
                }
                // Origin terminal address
                if ($en_is_shipment == 'en_single_shipment') {
                    (isset($this->InstorPickupLocalDelivery->localDelivery) && ($this->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($this->web_service_inst->en_wd_origin_array['fee_local_delivery'], $this->web_service_inst->en_wd_origin_array['checkout_desc_local_delivery'], $this->web_service_inst->en_wd_origin_array) : "";
                    (isset($this->InstorPickupLocalDelivery->inStorePickup) && ($this->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($this->web_service_inst->en_wd_origin_array['checkout_desc_store_pickup'], $this->web_service_inst->en_wd_origin_array, $this->InstorPickupLocalDelivery->totalDistance) : "";
                }

                return $en_rates;
            }

            /**
             * final rates sorting
             * @param array type $rates
             * @param array type $package
             * @return array type
             */
            function en_sort_woocommerce_available_shipping_methods($rates, $package)
            {
                // if there are no rates don't do anything

                if (!$rates) {
                    return array();
                }

                // check the option to sort shipping methods by price on quote settings 
                if (get_option('shipping_methods_do_not_sort_by_price') != 'yes') {

                    $local_delivery = isset($rates['local-delivery']) ? $rates['local-delivery'] : '';
                    $in_store_pick_up = isset($rates['in-store-pick-up']) ? $rates['in-store-pick-up'] : '';
                    // get an array of prices
                    $prices = array();
                    foreach ($rates as $rate) {
                        $prices[] = $rate->cost;
                    }

                    // use the prices to sort the rates
                    array_multisort($prices, $rates);

                    // unset instore-pickup & local delivery and set at the end of quotes array
                    if (isset($in_store_pick_up) && !empty($in_store_pick_up)) {
                        unset($rates['in-store-pick-up']);
                        $rates['in-store-pick-up'] = $in_store_pick_up;
                    }
                    if (isset($local_delivery) && !empty($local_delivery)) {
                        unset($rates['local-delivery']);
                        $rates['local-delivery'] = $local_delivery;
                    }
                }
                // return the rates
                return $rates;
            }

            /**
             * Add Hazardous Fee
             * @param string $service_code
             * @param array $quote_settings
             * @return string
             */
            function ups_add_hazardous_material($service_code)
            {
                $this->ups_get_hazardous_fields();
                return ($service_code == "03") ? $this->quote_settings['ground_hazardous_material_fee'] : $this->quote_settings['air_hazardous_material_fee'];
            }

            /**
             * Hazardous values quote settings
             */
            function ups_get_hazardous_fields()
            {
                $this->quote_settings = [];
                $this->quote_settings['hazardous_materials_shipments'] = get_option('ups_small_hazardous_materials_shipments');
                $this->quote_settings['ground_hazardous_material_fee'] = get_option('ups_small_ground_hazardous_fee');
                $this->quote_settings['air_hazardous_material_fee'] = get_option('ups_small_air_hazardous_fee');
                $this->quote_settings['air_hazardous_material_fee'] = get_option('ups_small_air_hazardous_fee');
                $this->quote_settings['dont_sort'] = get_option('shipping_methods_do_not_sort_by_price');
                $this->quote_settings['handling_fee'] = get_option('ups_small_hand_fee_mark_up');
                $this->quote_settings['services'] = [
                    'all' => $this->web_service_inst->service_options_array(),
                    'simple' => $this->web_service_inst->ups_simple_service_options()
                ];
            }

            /**
             * Pickup delivery quote
             * @return array type
             */
            function pickup_delivery($label, $en_wd_origin_array, $total_distance)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;

                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';
                // Origin terminal address
                $address = (isset($en_wd_origin_array['address'])) ? $en_wd_origin_array['address'] : '';
                $city = (isset($en_wd_origin_array['city'])) ? $en_wd_origin_array['city'] : '';
                $state = (isset($en_wd_origin_array['state'])) ? $en_wd_origin_array['state'] : '';
                $zip = (isset($en_wd_origin_array['zip'])) ? $en_wd_origin_array['zip'] : '';
                $phone_instore = (isset($en_wd_origin_array['phone_instore'])) ? $en_wd_origin_array['phone_instore'] : '';
                strlen($total_distance) > 0 ? $label .= ': Free | ' . str_replace("mi", "miles", $total_distance) . ' away' : '';
                strlen($address) > 0 ? $label .= ' | ' . $address : '';
                strlen($city) > 0 ? $label .= ', ' . $city : '';
                strlen($state) > 0 ? $label .= ' ' . $state : '';
                strlen($zip) > 0 ? $label .= ' ' . $zip : '';
                strlen($phone_instore) > 0 ? $label .= ' | ' . $phone_instore : '';

                $pickup_delivery = array(
                    'id' => 'ups_small:' . 'in-store-pick-up',
                    'cost' => 0,
                    'label' => $label,
                    'plugin_name' => 'upsSmall',
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($pickup_delivery);
            }

            /**
             * Local delivery quote
             * @param string type $cost
             * @return array type
             */
            function local_delivery($cost, $label, $en_wd_origin_array)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;
                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';

                $local_delivery = array(
                    'id' => 'ups_small:' . 'local-delivery',
                    'cost' => $cost,
                    'label' => $label,
                    'plugin_name' => 'upsSmall',
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($local_delivery);
            }

            /**
             * Check is free shipping or not
             * @param $coupon
             * @return string
             */
            function ups_shipping_coupon_rate($coupon)
            {
                foreach ($coupon as $key => $value) {
                    if ($value->get_free_shipping() == 1) {
                        $rates = array(
                            'id' => 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'upsSmall',
                            'plugin_type' => 'small',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);
                        return 'y';
                    }
                }
                return 'n';
            }

            function get_estimated_delivery_date_label($rate)
            {
                $delivery_time = isset($rate['delivery_time']) && !empty($rate['delivery_time']) && is_string($rate['delivery_time']) ? $rate['delivery_time'] : '';
                $default_date_format = 'D M d';
                $date_label = 'Expected delivery by ' . $delivery_time . ' on ' . gmdate($default_date_format, strtotime($rate['transit_time']));
                $custom_date_format = get_option('estimated_delivery_date_format');
                
                if (!empty($custom_date_format)) {
                    if (str_contains($custom_date_format, '%')) {
                        try {
                            $date_format_arr = explode('%', $custom_date_format);
                            $_label = $date_format_arr[0];
                            $_format = str_replace('%', '', $date_format_arr[1]);
                            $date_label = $_label . ' ' . $delivery_time . ' on ' . gmdate($_format, strtotime($rate['transit_time']));
                        } catch (\Throwable $th) {
                            $date_label = $date_label;
                        }
                    } else {
                        $date_label = $custom_date_format . ' ' . $delivery_time . ' on ' .  gmdate($default_date_format, strtotime($rate['transit_time']));
                    }
                }

                return $date_label;
            }
        }

    }
}
