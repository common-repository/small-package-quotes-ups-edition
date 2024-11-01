<?php

/**
 * UPS Small WooComerce |  Get Shipping Package Class
 * @package     Woo-commerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UPS Small WooComerce |  Get Shipping Package Class
 */
class Eniture_UPS_Small_Shipping_Get_Package
{

    public $hasLTLShipment = 0;
    public $errors = [];
    public $order_details;

    // Micro Warehouse
    public $products = [];
    public $dropship_location_array = [];
    public $warehouse_products = [];
    public $destination_Address_ups_small;
    public $origin = [];
    // Images for FDO
    public $en_fdo_image_urls = [];
    private $is_dest_country_matched = false;
    private $is_country_filter_applied = false;
    private $is_restrict_service_rule_applied = false;

    public function __construct()
    {
        $this->is_dest_country_matched = false;
        $this->is_country_filter_applied = false;
        $this->is_restrict_service_rule_applied = false;
    }

    /**
     * Grouping For Shipments
     * @param $package
     * @param $ups_small_res_inst
     * @param $freight_zipcode
     * @return Shipment Grouped Array
     */
    function group_ups_small_shipment($package, $ups_small_res_inst, $freight_zipcode)
    {
        if (empty($freight_zipcode)) {
            return [];
        }

        if (isset($package['sPackage']) && !empty($package['sPackage'])) {
            return $package['sPackage'];
        }

        $wc_settings_wwe_ignore_items = get_option("en_ignore_items_through_freight_classification");
        $en_get_current_classes = strlen($wc_settings_wwe_ignore_items) > 0 ? trim(strtolower($wc_settings_wwe_ignore_items)) : '';
        $en_get_current_classes_arr = strlen($en_get_current_classes) > 0 ? array_map('trim', explode(',', $en_get_current_classes)) : [];

        $pStatus = (isset($package['itemType']) && !empty($package['itemType'])) ? $package['itemType'] : "";
        $ups_small_woo_obj = new Eniture_UPS_Small_Woo_Update_Changes();
        $sm_zipcode = $ups_small_woo_obj->ups_small_postcode();
        $ups_small_package = [];

        // Micro Warehouse
        $UPS_Get_Shipping_Quotes = new Eniture_UPS_Get_Shipping_Quotes();
        $this->destination_Address_ups_small = $UPS_Get_Shipping_Quotes->destinationAddressUpsSmall();
        // threshold
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

        $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);

        $shipment_weight_arr = [];

        if (isset($package['contents'])) {
            $pack = $package['contents'];
            foreach ($pack as $item_id => $values) {
                $_product = $values['data'];

                // Images for FDO
                $this->en_fdo_image_urls($values, $_product);

                // Flat rate pricing
                $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                $en_flat_rate_price = $this->en_get_flat_rate_price($values, $_product);
                if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                    continue;
                }
               $shipping_class = $_product->shipping_class;
              // free shipping
                if ($shipping_class == 'free-shipping-et') {
                   continue;
                }
                // Flat shipping
                $en_fs_existence = apply_filters('en_fs_existence', false);
                $fs = get_post_meta($product_id, '_enable_fs', true);
                if ($en_fs_existence && $fs == 'yes') {
                    continue;
                }

                // Get product shipping class
                $en_ship_class = strtolower($values['data']->get_shipping_class());
                if (in_array($en_ship_class, $en_get_current_classes_arr)) {
                    continue;
                }

                // Shippable handling units
                $values = apply_filters('en_shippable_handling_units_request', $values, $values, $_product);
                $shippable = [];
                if (isset($values['shippable']) && !empty($values['shippable'])) {
                    $shippable = $values['shippable'];
                }

                $nestedPercentage = 0;
                $nestedDimension = "";
                $nestedItems = "";
                $StakingProperty = "";

                $locationId = 0;
                $small_err = 0;
                $exceedWeight = get_option('en_plugins_return_LTL_quotes');

                $dimension_unit = get_option('woocommerce_dimension_unit');
                // convert product dimensions in feet, centimeter, miles, kilometer into Inches
                if ($dimension_unit == 'ft' || $dimension_unit == 'cm' || $dimension_unit == 'mi' || $dimension_unit == 'km') {

                    $dimensions = $this->dimensions_conversion($_product);
                    $height = $dimensions['height'];
                    $width = $dimensions['width'];
                    $length = $dimensions['length'];
                } else {
                    $p_height = str_replace( array( "'",'"' ),'',$_product->get_height());
					$p_width = str_replace( array( "'",'"' ),'',$_product->get_width());
					$p_length = str_replace( array( "'",'"' ),'',$_product->get_length());
                    $p_height = is_numeric($p_height) ? $p_height : 0;
                    $p_width = is_numeric($p_width) ? $p_width : 0;
                    $p_length = is_numeric($p_length) ? $p_length : 0;
					$height = wc_get_dimension($p_height, 'in');
					$width = wc_get_dimension($p_width, 'in');
					$length = wc_get_dimension($p_length, 'in');
                }

                /**
                 * These are the custom hooks of stovil.com (Hunter) to get dynamic dimensions and weight.
                */
                $height = apply_filters('en_filter_product_height', $height, $item_id);
                $width = apply_filters('en_filter_product_width', $width, $item_id);
                $length = apply_filters('en_filter_product_length', $length, $item_id);

                $height = (strlen($height) > 0) ? $height : "0";
                $width = (strlen($width) > 0) ? $width : "0";
                $length = (strlen($length) > 0) ? $length : "0";
                
                $product_weight = apply_filters('en_filter_product_weight', wc_get_weight($_product->get_weight(), 'lbs'), $item_id);

                $dimensions = $length * $width * $height;
                $weight = ((float)$product_weight * (float)$values['quantity']);
                // Mutiple packages
                $en_multiple_package = $this->en_multiple_package($values, $_product);

                $freight_enable_class = $this->ups_small_check_freight_class($_product);
                $locations_list = $this->ups_small_origin_address($values, $_product);
                $origin_address = $ups_small_res_inst->ups_small_multi_warehouse($locations_list, $freight_zipcode);
                // preferred origin
                if (is_plugin_active('preferred-origin/preferred-origin.php')) {
                    $origin_address = apply_filters('en_selection_of_locations', $origin_address['zip'], 'ups_small');
                    if (empty($origin_address) || empty($origin_address['zip'])) {
                        continue;
                    }
                }
                
                $product_level_markup = $this->ups_small_get_product_level_markup($_product, $values['variation_id'], $product_id, $values['quantity']);

                $locationId = (isset($origin_address['locationId'])) ? $origin_address['locationId'] : '';
                $locationZip = (isset($origin_address['zip'])) ? $origin_address['zip'] : '';
                $locationId = $locationZip;

                $shipment_weight_arr[$locationId] = (isset($shipment_weight_arr[$locationId])) ? $shipment_weight_arr[$locationId] + $weight : $weight;
                $ptype = $this->ups_small_check_product_type($freight_enable_class, $exceedWeight, $product_weight, $en_multiple_package, $shipment_weight_arr[$locationId]);
                // Micro Warehouse
                (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
                $this->products[] = $post_id;

                if (in_array($en_ship_class, $en_get_current_classes_arr) && ($ptype == 'ltl' || $ptype != 'ltl')) {
                    $ptype = 'ignored_items';
                }

                if (isset($ups_small_package[$locationId]['is_ignored_items']) && $ups_small_package[$locationId]['is_ignored_items'] == 'ignored_items') {
                    $ups_small_package[$locationId]['is_ignored_items'] = 'ignored_items';
                } else {
                    $ups_small_package[$locationId]['is_ignored_items'] = $ptype;
                }

                if (isset($ups_small_package[$locationId]['is_shipment']) && $ups_small_package[$locationId]['is_shipment'] == 'ltl') {
                    $ups_small_package[$locationId]['is_shipment'] = 'ltl';
                } else {
                    $ups_small_package[$locationId]['is_shipment'] = $ptype;
                }

                if (!empty($origin_address) && ($product_weight <= $weight_threshold || $en_multiple_package == 'yes') && !in_array($en_ship_class, $en_get_current_classes_arr)) {

                    // Nested Material
                    $nested_material = $this->en_nested_material($values, $_product);
                    if ($nested_material == "yes") {
                        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                        $nestedPercentage = get_post_meta($post_id, '_nestedPercentage', true);
                        $nestedDimension = get_post_meta($post_id, '_nestedDimension', true);
                        $nestedItems = get_post_meta($post_id, '_maxNestedItems', true);
                        $StakingProperty = get_post_meta($post_id, '_nestedStakingProperty', true);
                    }

                    $ups_small_package[$locationId]['origin'] = $origin_address;
                    $ups_small_package[$locationId]['origin']['ptype'] = $ptype;

                    $hazardous_material = $this->en_hazardous_material($values, $_product);
                    $hm_plan = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'hazardous_material');
                    $hm_status = (!is_array($hm_plan) && $hazardous_material == 'yes') ? TRUE : FALSE;

                    // Shippable handling units
                    $ship_item_alone = '0';
                    extract($shippable);

                    $product_title = str_replace(array("'", '"'), '', $_product->get_title());
                    $parent_id = $_product->get_id();
                    if (isset($values['variation_id']) && $values['variation_id'] > 0) {
                        $variation = wc_get_product($values['variation_id']);
                        $parent_id = $variation->get_parent_id();
                        $product_title = $variation->get_name();
                    }

                    $product_title = is_string($product_title) && !empty($product_title) ? str_replace(array("'", '"'), '', $product_title) : '';

                    $en_items = [
                        'productId' => $parent_id,
                        'productName' => $product_title,
                        'productQty' => $values['quantity'],
                        'product_name' => $values['quantity'] . " x " . $product_title,
                        'products' => $product_title,
                        'productPrice' => $_product->get_price(),
                        'productWeight' => $product_weight,
                        'productLength' => $length,
                        'productWidth' => $width,
                        'productHeight' => $height,
                        'ptype' => $ptype,
                        'nestedMaterial' => $nested_material,
                        'nestedPercentage' => $nestedPercentage,
                        'nestedDimension' => $nestedDimension,
                        'nestedItems' => $nestedItems,
                        'stakingProperty' => $StakingProperty,
                        // FDO
                        'hazardousMaterial' => $hm_status,
                        'productType' => ($_product->get_type() == 'variation') ? 'variant' : 'simple',
                        'productSku' => $_product->get_sku(),
                        'actualProductPrice' => $_product->get_price(),
                        'attributes' => $_product->get_attributes(),
                        'variantId' => ($_product->get_type() == 'variation') ? $_product->get_id() : '',
                        'hazmat' => $hazardous_material,

                        // Shippable handling units
                        'ship_item_alone' => $ship_item_alone,

                        'markup' => $product_level_markup
                    ];

                    // Hook for flexibility adding to package
                    $en_items = apply_filters('en_group_package', $en_items, $values, $_product);

                    // Micro Warehouse
                    $items[$post_id] = $en_items;

                    if (!$_product->is_virtual()) {
                        $_product = $values['data'];
                        $ups_small_package[$locationId]['items'][] = $en_items;
                    }

                    // Hazardous Material
                    if ($hazardous_material == "yes" && !isset($ups_small_package[$locationId]['hazardous_material'])) {
                        $ups_small_package[$locationId]['hazardous_material'] = TRUE;
                    }

                    // Except Ground Transit
                    $exempt_ground_transit_restriction = $this->exempt_ground_transit_restriction($values, $_product);
                    if($exempt_ground_transit_restriction == 'yes' && !isset($ups_small_package[$locationId]['exempt_ground_transit_restriction'])){
                        $ups_small_package[$locationId]['exempt_ground_transit_restriction'] = 1;
                    }

                    $insurance = $this->en_insurance_checked($values, $_product);
                    if($insurance == 'yes' && !isset($ups_small_package[$locationId]['is_shipment_insure'])){
                        $ups_small_package[$locationId]['is_shipment_insure'] = 1;
                    }

                    // Product tags
                    $product_tags = get_the_terms($product_id, 'product_tag');
                    $product_tags = empty($product_tags) ? get_the_terms($parent_id, 'product_tag') : $product_tags;
                    if (!empty($product_tags)) {
                        $product_tag_names = array_map(function($tag) { return $tag->term_id; }, $product_tags);

                        if (isset($ups_small_package[$locationId]['product_tags'])) {
                            $ups_small_package[$locationId]['product_tags'] = array_merge($ups_small_package[$locationId]['product_tags'], $product_tag_names);
                        } else {
                            $ups_small_package[$locationId]['product_tags'] = $product_tag_names;
                        }
                    } else {
                        $ups_small_package[$locationId]['product_tags'] = [];
                    }

                    // Product quantity
                    if (isset($ups_small_package[$locationId]['product_quantities'])) {
                        $ups_small_package[$locationId]['product_quantities'] += floatval($values['quantity']);
                    } else {
                        $ups_small_package[$locationId]['product_quantities'] = floatval($values['quantity']);
                    }

                    // Product price
                    if (isset($ups_small_package[$locationId]['product_prices'])) {
                        $ups_small_package[$locationId]['product_prices'] += (floatval($_product->get_price()) * floatval($values['quantity']));
                    } else {
                        $ups_small_package[$locationId]['product_prices'] = (floatval($_product->get_price()) * floatval($values['quantity']));
                    }
                }

                // Micro Warehouse
                $items_shipment[$post_id] = $ptype == 'ltl' ? true : false;

                if ($pStatus == '' && $ptype == 'ltl') {
                    return $ups_small_package = [];
                }

                if ($dimensions == 0 && $product_weight == 0) {
                    $ups_small_package[$locationId]['no_parameter'] = 'NOPARAM';
                }

                if ($locationId > 0) {
                    $ups_small_package[$locationId]['shipment_weight'] = isset($ups_small_package[$locationId]['shipment_weight']) ? $ups_small_package[$locationId]['shipment_weight'] + $weight : $weight;
                }
                if ((isset($ups_small_package[$locationId]['is_ignored_items']) &&
                        $ups_small_package[$locationId]['is_ignored_items'] == 'ignored_items') && !isset($ups_small_package[$locationId]['items'])) {
                    unset($ups_small_package[$locationId]);
                }
            }

            do_action("eniture_debug_mood", "Ups Small Quotes Request", $ups_small_package);

            $smallPluginExist = 0;
            $calledMethod = [];
            $eniturePluigns = json_decode(get_option('EN_Plugins'));
            if (!empty($eniturePluigns)) {
                foreach ($eniturePluigns as $enIndex => $enPlugin) {

                    $freightSmallClassName = 'WC_' . $enPlugin;

                    if (!in_array($freightSmallClassName, $calledMethod)) {

                        if (class_exists($freightSmallClassName)) {
                            $smallPluginExist = 1;
                        }

                        $calledMethod[] = $freightSmallClassName;
                    }
                }
            }

            // Micro Warehouse
            $eniureLicenceKey = get_option('ups_small_licence_key');
            $ups_small_package = apply_filters('en_micro_warehouse', $ups_small_package, $this->products, $this->dropship_location_array, $this->destination_Address_ups_small, $this->origin, 1, $items, $items_shipment, $this->warehouse_products, $eniureLicenceKey, 'ups_small');
            return $ups_small_package;
        }
        return [];
    }

    /**
     *  Get the product multiple package checkbox value.
     */
    public function en_multiple_package($product_object, $product_detail)
    {
        $post_id = (isset($product_object['variation_id']) && $product_object['variation_id'] > 0) ? $product_object['variation_id'] : $product_detail->get_id();
        return get_post_meta($post_id, '_en_multiple_packages', true);
    }

    /**
     * Set images urls | Images for FDO
     * @param array type $en_fdo_image_urls
     * @return array type
     */
    public function en_fdo_image_urls_merge($en_fdo_image_urls)
    {
        return array_merge($this->en_fdo_image_urls, $en_fdo_image_urls);
    }

    /**
     * Get images urls | Images for FDO
     * @param array type $values
     * @param array type $_product
     * @return array type
     */
    public function en_fdo_image_urls($values, $_product)
    {
        $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        $gallery_image_ids = $_product->get_gallery_image_ids();
        foreach ($gallery_image_ids as $key => $image_id) {
            $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
        }

        $image_id = $_product->get_image_id();
        $this->en_fdo_image_urls[$product_id] = [
            'product_id' => $product_id,
            'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
            'gallery_image_ids' => $gallery_image_ids
        ];

        add_filter('en_fdo_image_urls_merge', [$this, 'en_fdo_image_urls_merge'], 10, 1);
    }

    /**
     * Nested Material
     * @param array type $values
     * @param array type $_product
     * @return string type
     */
    function en_nested_material($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_nestedMaterials', true);
    }

    /**
     * Get Enabled Shipping Class Of Product
     * @param $_product
     * @return Shipping Class
     */
    function ups_small_check_freight_class($_product)
    {
        if ($_product->get_type() == 'variation') {
            $ship_class_id = $_product->get_shipping_class_id();

            if ($ship_class_id == 0) {
                $parent_data = $_product->get_parent_data();
                $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                $freight_enable_class = (isset($get_parent_term->slug)) ? $get_parent_term->slug : "";
            } else {
                $freight_enable_class = $_product->get_shipping_class();
            }
        } else {
            $freight_enable_class = $_product->get_shipping_class();
        }

        return $freight_enable_class;
    }

    /**
     * Check Product Type
     * @param $freight_enable_class
     * @param $exceedWeight
     * @param $weight
     * @return string
     */
    function ups_small_check_product_type($freight_enable_class, $exceedWeight, $weight, $en_multiple_package, $shipment_weight_sum)
    {
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $only_show_ltl_rates = get_option('en_only_show_ltl_rates_when_weight_exceeds');

        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
        if ($freight_enable_class == 'ltl_freight' || $freight_enable_class == 'ltl-freight') {
            $ptype = 'ltl';
        } else if ($exceedWeight == 'yes' 
        && ( ($weight > $weight_threshold && $en_multiple_package != 'yes') || (!empty($only_show_ltl_rates) && $only_show_ltl_rates == 'yes' && $shipment_weight_sum > $weight_threshold) )) {
            $ptype = 'ltl';
        } else {
            $ptype = 'small';
        }

        return $ptype;
    }

    /**
     * Hazardous Material in Product Detail page
     * @param array $values
     * @param array $_product
     * @return string
     */
    function en_hazardous_material($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_hazardousmaterials', true);
    }

    /**
     *
     * @param array $values
     * @param array $_product
     * @return string
     */
    function en_insurance_checked($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_en_insurance_fee', true);
    }

    /**
     * Get Origin Address
     * @param $values
     * @param $_product
     * @return Origin Address List
     * @global $wpdb
     */
    function ups_small_origin_address($values, $_product)
    {
        global $wpdb;
        $locations_list = [];
        (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
        $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
        if ($enable_dropship == 'yes') {
            $get_loc = get_post_meta($post_id, '_dropship_location', true);
            if ($get_loc == '') {
                // Micro Warehouse
                $this->warehouse_products[] = $post_id;
                return array('error' => 'wwe small dp location not found!');
            }

            // Multi Dropship
            $multi_dropship = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'multi_dropship');

            if (is_array($multi_dropship)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                );
            } else {
                $get_loc = ($get_loc !== '') ? maybe_unserialize($get_loc) : $get_loc;
                $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                );
            }

            // Micro Warehouse
            $this->multiple_dropship_of_prod($locations_list, $post_id);
            $eniture_debug_name = "Dropships";
        }
        if (empty($locations_list)) {
            $multi_warehouse = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'multi_warehouse');
            if (is_array($multi_warehouse)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                );
            } else {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                );
            }

            // Micro Warehouse
            $this->warehouse_products[] = $post_id;
            $eniture_debug_name = "Warehouses";
        }

        do_action("eniture_debug_mood", "Quotes $eniture_debug_name (ups small)", $locations_list);
        return $locations_list;
    }

    // Micro Warehouse
    public function multiple_dropship_of_prod($locations_list, $post_id)
    {
        $post_id = (string)$post_id;

        foreach ($locations_list as $key => $value) {
            $dropship_data = $this->address_array($value);

            $this->origin["D" . $dropship_data['zip']] = $dropship_data;
            if (!isset($this->dropship_location_array["D" . $dropship_data['zip']]) || !in_array($post_id, $this->dropship_location_array["D" . $dropship_data['zip']])) {
                $this->dropship_location_array["D" . $dropship_data['zip']][] = $post_id;
            }
        }

    }

    // Micro Warehouse
    public function address_array($value)
    {
        $dropship_data = [];

        $dropship_data['locationId'] = (isset($value->id)) ? $value->id : "";
        $dropship_data['zip'] = (isset($value->zip)) ? $value->zip : "";
        $dropship_data['city'] = (isset($value->city)) ? $value->city : "";
        $dropship_data['state'] = (isset($value->state)) ? $value->state : "";
        // Origin terminal address
        $dropship_data['address'] = (isset($value->address)) ? $value->address : "";
        // Terminal phone number
        $dropship_data['phone_instore'] = (isset($value->phone_instore)) ? $value->phone_instore : "";
        $dropship_data['location'] = (isset($value->location)) ? $value->location : "";
        $dropship_data['country'] = (isset($value->country)) ? $value->country : "";
        $dropship_data['enable_store_pickup'] = (isset($value->enable_store_pickup)) ? $value->enable_store_pickup : "";
        $dropship_data['fee_local_delivery'] = (isset($value->fee_local_delivery)) ? $value->fee_local_delivery : "";
        $dropship_data['suppress_local_delivery'] = (isset($value->suppress_local_delivery)) ? $value->suppress_local_delivery : "";
        $dropship_data['miles_store_pickup'] = (isset($value->miles_store_pickup)) ? $value->miles_store_pickup : "";
        $dropship_data['match_postal_store_pickup'] = (isset($value->match_postal_store_pickup)) ? $value->match_postal_store_pickup : "";
        $dropship_data['checkout_desc_store_pickup'] = (isset($value->checkout_desc_store_pickup)) ? $value->checkout_desc_store_pickup : "";
        $dropship_data['enable_local_delivery'] = (isset($value->enable_local_delivery)) ? $value->enable_local_delivery : "";
        $dropship_data['miles_local_delivery'] = (isset($value->miles_local_delivery)) ? $value->miles_local_delivery : "";
        $dropship_data['match_postal_local_delivery'] = (isset($value->match_postal_local_delivery)) ? $value->match_postal_local_delivery : "";
        $dropship_data['checkout_desc_local_delivery'] = (isset($value->checkout_desc_local_delivery)) ? $value->checkout_desc_local_delivery : "";

        $dropship_data['sender_origin'] = $dropship_data['location'] . ": " . $dropship_data['city'] . ", " . $dropship_data['state'] . " " . $dropship_data['zip'];

        return $dropship_data;
    }

    /**
     * @param type object
     * @return type array
     */
    function dimensions_conversion($_product)
    {

        $dimension_unit = get_option('woocommerce_dimension_unit');
        $dimensions = [];
        $height = is_numeric($_product->get_height()) ? $_product->get_height() : 0;
        $width = is_numeric($_product->get_width()) ? $_product->get_width() : 0;
        $length = is_numeric($_product->get_length()) ? $_product->get_length() : 0;
        switch ($dimension_unit) {

            case 'ft':
                $dimensions['height'] = round($height * 12, 2);
                $dimensions['width'] = round($width * 12, 2);
                $dimensions['length'] = round($length * 12, 2);
                break;

            case 'cm':
                $dimensions['height'] = round($height * 0.3937007874, 2);
                $dimensions['width'] = round($width * 0.3937007874, 2);
                $dimensions['length'] = round($length * 0.3937007874, 2);
                break;

            case 'mi':
                $dimensions['height'] = round($height * 63360, 2);
                $dimensions['width'] = round($width * 63360, 2);
                $dimensions['length'] = round($length * 63360, 2);
                break;

            case 'km':
                $dimensions['height'] = round($height * 39370.1, 2);
                $dimensions['width'] = round($width * 39370.1, 2);
                $dimensions['length'] = round($length * 39370.1, 2);
                break;
        }

        return $dimensions;
    }

    /**
    * Returns product level markup
    */
    function ups_small_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
    {
        $product_level_markup = 0;
            
            if ($_product->get_type() == 'variation') {
                $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
                if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                    $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
                }
            } else {
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }
            
            if (empty($product_level_markup)) {
                $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
            }

            if (!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
            && is_numeric($product_level_markup) && is_numeric($quantity))
            {
                $product_level_markup *= $quantity;
            } else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
                $position = strpos($product_level_markup, '%');
                $first_str = substr($product_level_markup, $position);
                $arr = explode($first_str, $product_level_markup);
                $percentage_value = $arr[0];
                $product_price = $_product->get_price();
    
                if (!empty($product_price)) {
                    $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
                } else {
                    $product_level_markup = 0;
                }
            }
    
            return $product_level_markup;
    }

    /**
     * Check except transit time restriction
     * @param array $values
     * @param array $_product
     * @return string
     */
    function exempt_ground_transit_restriction($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_en_exempt_ground_transit_restriction', true);
    }

    /**
     * Returns flat rate price and quantity
     */
    function en_get_flat_rate_price($values, $_product)
    {
        if ($_product->get_type() == 'variation') {
            $flat_rate_price = get_post_meta($values['variation_id'], 'en_flat_rate_price', true);
            if (strlen($flat_rate_price) < 1) {
                $flat_rate_price = get_post_meta($values['product_id'], 'en_flat_rate_price', true);
            }
        } else {
            $flat_rate_price = get_post_meta($_product->get_id(), 'en_flat_rate_price', true);
        }

        return $flat_rate_price;
    }

    function apply_shipping_rules($ups_small_package, $apply_on_rates = false, $rates = [], $loc_id = '')
    {
        if (empty($ups_small_package)) return $apply_on_rates ? $rates : false;

        global $wpdb;
        $rules = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "eniture_ups_small_shipping_rules"), ARRAY_A);

        if (empty($rules)) return $apply_on_rates ? $rates : false;
       
        $is_rule_applied = false;
        foreach ($rules as $rule) {
            if (!$rule['is_active']) continue;

            $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
            if (empty($settings)) continue;

            $rule_type = isset($rule['type']) ? $rule['type'] : '';

            if ($rule_type == 'Override Rates' && $apply_on_rates) {
                $rates = $this->apply_override_rates_rule($ups_small_package, $settings, $rates, $loc_id);
            } else if ($rule_type == 'Hide Methods') {
                $is_rule_applied = $this->apply_rule($settings, $ups_small_package);
                if ($is_rule_applied) break;
            } else if ($rule_type == 'Restrict To State' && $apply_on_rates) {
                $rates = $this->apply_restrict_to_state_rule($ups_small_package, $settings, $rates);
            }
        }

        return $apply_on_rates ? $rates : $is_rule_applied;
    }

    function apply_rule($settings, $ups_small_package)
    {
        $is_rule_applied = false;

        if ($settings['apply_to'] == 'cart') {
            $formatted_values = $this->get_formatted_values($ups_small_package);
            $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);
        } else {
            foreach ($ups_small_package as $key => $pkg) {
                $is_rule_applied = false;
                $shipments = [];
                $shipments[$key] = $pkg;

                $formatted_values = $this->get_formatted_values($shipments);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);

                if ($is_rule_applied) break;
            }
        }

        return $is_rule_applied;
    }

    function get_formatted_values($shipments)
    {
        $formatted_values = ['weight' => 0, 'price' => 0, 'quantity' => 0, 'tags' => []];

        foreach ($shipments as $pkg) {
            $formatted_values['weight'] += floatval($pkg['shipment_weight']);
            $formatted_values['price'] += floatval($pkg['product_prices']);
            $formatted_values['quantity'] += floatval($pkg['product_quantities']);
            $formatted_values['tags'] = array_merge($formatted_values['tags'], $pkg['product_tags']);
        }

        return $formatted_values;
    }

    function apply_rule_filters($settings, $formatted_values, $restict_service = false)
    {
        $is_filter_applied = false;
        $filters = ['weight', 'price', 'quantity'];
        // Add restrict service filters in case of restrict to state rule
        $filters = $restict_service ? array_merge(['country'], $filters) : $filters;

        // Check if any of the filter is checked
        $filters_checks = ['filter_by_weight', 'filter_by_price', 'filter_by_quantity', 'filter_by_product_tag', 'filter_by_country', 'filter_by_state'];
        $any_filter_checked = false;
        foreach ($filters_checks as $check) {
            if (isset($settings[$check]) && filter_var($settings[$check], FILTER_VALIDATE_BOOLEAN)) {
                $any_filter_checked = true;
                break;
            }
        }

        // If there is no filter check, then all rules will meet so rule will be treated as applied
        if (!$any_filter_checked) {
            return true;
        }

        $destination_address = (new Eniture_UPS_Get_Shipping_Quotes())->destinationAddressUpsSmall();

        foreach ($filters as $filter) {
            if (filter_var($settings['filter_by_' . $filter], FILTER_VALIDATE_BOOLEAN)) {
                if ($filter == 'country') {
                    $destination_country = isset($destination_address['country']) ? $destination_address['country'] : '';
                    $this->is_dest_country_matched = $this->is_country_matched($settings['filter_by_country_value'],$destination_country);
                    $is_filter_applied = $this->is_dest_country_matched;
                    $this->is_country_filter_applied = true;

                    if ($is_filter_applied && filter_var($settings['filter_by_state'], FILTER_VALIDATE_BOOLEAN)) {
                        $destination_state = isset($destination_address['state']) ? $destination_address['state'] : '';
                        $states = isset($settings['filter_by_state_value']) ? $settings['filter_by_state_value'] : [];
                        $is_filter_applied = $this->is_dest_country_matched = in_array($destination_state, $states);
                    }
                } else {
                    $is_filter_applied = $formatted_values[$filter] >= $settings['filter_by_' . $filter . '_from'];
                    if ($is_filter_applied && !empty($settings['filter_by_' . $filter . '_to'])) {
                        $is_filter_applied = $formatted_values[$filter] < $settings['filter_by_' . $filter . '_to'];
                    }
                }
            }

            if ($is_filter_applied) break;
        }

        if (filter_var($settings['filter_by_product_tag'], FILTER_VALIDATE_BOOLEAN) && !$is_filter_applied) {
            $product_tags = $settings['filter_by_product_tag_value'];
            $tags_check = array_filter($product_tags, function ($tag) use ($formatted_values) {
                return in_array($tag, $formatted_values['tags']);
            });
            $is_filter_applied = count($tags_check) > 0;
        }

        return $is_filter_applied;
    }

    function apply_override_rates_rule($ups_small_package, $settings, $rates, $loc_id)
    {
        $updated_rates = $rates;

        foreach ($ups_small_package as $org_zip => $pkg) {
            if ($loc_id != $org_zip) continue;

            $is_rule_applied = false;
            $shipments = [];
            $shipments[$org_zip] = $pkg;

            $formatted_values = $this->get_formatted_values($shipments);
            $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);

            if ($is_rule_applied) {
                $updated_rates = $this->get_updated_rates($updated_rates, $settings);
            };
        }

        return $updated_rates;
    }

    function apply_restrict_to_state_rule($ups_small_package, $settings, $rates) 
    {
        $formatted_values = $this->get_formatted_values($ups_small_package);
        $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values, true);
        $this->is_restrict_service_rule_applied = $is_rule_applied;

        return ($is_rule_applied || ($this->is_country_filter_applied && !$this->is_dest_country_matched)) ? $this->get_updated_rates($rates, $settings, true) : $rates;
    }

    function get_updated_rates($rates, $settings, $restict_service = false)
    {
        if ($restict_service) {
            if ($this->is_country_filter_applied && !$this->is_dest_country_matched) {
                $rates_keys = ['q', 'ups_rate', 'GFP', 'q_92', 'q_93', 'q_94', 'q_95', 'usrg', 'usr2', 'usr1', 'usr3'];
                
                foreach ($rates as $key => $value) {
                    foreach ($rates_keys as $rates_key) {
                        if ($key == $rates_key && isset($rates->$rates_key)) unset($rates->$rates_key);
                    }
                }
            }
        } else {
            foreach ($rates as $key => $result) {
                $ups_quotes = (isset($result->q) && !empty($result->q)) ? $result->q : [];
                $ups_quotes = (empty($ups_quotes) && isset($result->ups_rate) && !empty($result->ups_rate)) ? $result->ups_rate : $ups_quotes;
                $rates_key = isset($result->q) ? 'q' : '';
                $rates_key = isset($result->ups_rate) ? 'ups_rate' : $rates_key;
                $simple_rate_services = ['usrg', 'usr2', 'usr1', 'usr3'];
    
                if (empty($ups_quotes)) continue;
    
                foreach ($ups_quotes as $service_key => $val) {
                    if (isset($val->Service)) {
                        $rate_arr = $val;
                    } else if(isset($val->RatedShipment)) {
                        $rate_arr = $val->RatedShipment;
                    } else if (isset($val->soapenvBody->rateRateResponse->rateRatedShipment) || isset($val->RateResponse->RatedShipment)) {
                        $rate_arr = isset($val->soapenvBody->rateRateResponse->rateRatedShipment) ? $val->soapenvBody->rateRateResponse->rateRatedShipment : (isset($val->RateResponse->RatedShipment) ? $val->RateResponse->RatedShipment : []);
                        
                        if (isset($rate_arr->Service->Code)) {
                            $rate_arr->Service->Code = 'GFP';
                        } else {
                            $rate_arr->Service = (object)['Code' => 'GFP'];
                        }
                    } else {
                        $rate_arr = [];
                    }
    
                    $service_code = isset($settings['service']) ? $settings['service'] : '';
                    
                    // Replace service rate with new rate defined in the shipping rule
                    if (isset($rate_arr->Service->Code) && $rate_arr->Service->Code == $service_code && $rates_key != '') {
                        if ($service_key == 'GFP') {
                            // Legacy API response
                            if (isset($val->soapenvBody->rateRateResponse->rateRatedShipment->rateTotalCharges->rateMonetaryValue)) {
                                $rates[$key]->$rates_key->$service_key->soapenvBody->rateRateResponse->rateRatedShipment->rateTotalCharges->rateMonetaryValue = $settings['service_rate'];
                            }
        
                            // New API response
                            if (isset($val->RateResponse->RatedShipment->TotalCharges->MonetaryValue)) {
                                $rates[$key]->$rates_key->$service_key->RateResponse->RatedShipment->TotalCharges->MonetaryValue = $settings['service_rate'];
                            }
    
                            $rates[$key]->$rates_key->$service_key->overrideRate = true;
                        } else {
                            if (isset($rate_arr->TotalCharges->MonetaryValue)) {
                                if (isset($rates[$key]->$rates_key->$service_key->TotalCharges->MonetaryValue)) {
                                    $rates[$key]->$rates_key->$service_key->TotalCharges->MonetaryValue = $settings['service_rate'];
                                }
    
                                if (isset($rates[$key]->$rates_key->$service_key->RatedShipment->TotalCharges->MonetaryValue)) {
                                    $rates[$key]->$rates_key->$service_key->RatedShipment->TotalCharges->MonetaryValue = $settings['service_rate'];
                                }
                            }
        
                            if (isset($rate_arr->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
                                if (isset($rates[$key]->$rates_key->$service_key->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
                                    $rates[$key]->$rates_key->$service_key->NegotiatedRateCharges->TotalCharge->MonetaryValue = $settings['service_rate'];
                                }
    
                                if (isset($rates[$key]->$rates_key->$service_key->RatedShipment->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
                                    $rates[$key]->$rates_key->$service_key->RatedShipment->NegotiatedRateCharges->TotalCharge->MonetaryValue = $settings['service_rate'];
                                }
                            }
                            
                            if (isset($rate_arr->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue)) {
                                if (isset($rates[$key]->$rates_key->$service_key->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue)) {
                                    $rates[$key]->$rates_key->$service_key->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue = $settings['service_rate'];
                                }
    
                                if (isset($rates[$key]->$rates_key->$service_key->RatedShipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue)) {
                                    $rates[$key]->$rates_key->$service_key->RatedShipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue = $settings['service_rate'];
                                }
                            }
    
                            $rates[$key]->$rates_key->$service_key->overrideRate = true;
                        }
                    }
    
                    // UPS simple rate services
                    if (in_array($service_key, $simple_rate_services) && $service_key == $service_code && $rates_key != '' && isset($val->rate)) {
                        if ($rates[$key]->$rates_key->$service_key->rate) {
                            $rates[$key]->$rates_key->$service_key->rate = $settings['service_rate'];
                            $rates[$key]->$rates_key->$service_key->overrideRate = true;
                        }
                    }
                }
            }
        }

        return $rates;
    }

    function is_country_matched($selected_country, $destination_country) 
    {
        $is_matched = false;
        $selected_country = strtolower($selected_country);
        $destination_country = strtolower($destination_country);

        if ($selected_country == $destination_country || ($destination_country == 'usa' && $selected_country == 'us') || ($destination_country == 'can' && $selected_country == 'ca')) {
            $is_matched = true;
        }

        return $is_matched;
    }
}
