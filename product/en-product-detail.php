<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product detail page.
 */

/**
 * Add and show simple and variable products.
 * Class Eniture_UpsSpqProductDetail
 * @package Eniture_UpsSpqProductDetail
 */
if (!class_exists('Eniture_UpsSpqProductDetail')) {

    class Eniture_UpsSpqProductDetail
    {
        // Hazardous
        public $hazardous_disabled_plan = '';
        public $hazardous_plan_required = '';

        /**
         * Hook for call.
         * Eniture_UpsSpqProductDetail constructor.
         */
        public function __construct()
        {
            add_filter('en_app_common_plan_status', [$this, 'en_ups_small_plan_status'], 10, 1);

            if (!has_filter('en_compatible_optimized_product_options') &&
                !has_filter('En_Plugins_dropship_filter') &&
                !has_filter('En_Plugins_variable_freight_classification_filter')) {
                // Add simple product fields
                add_action('woocommerce_product_options_shipping', [$this, 'en_show_product_fields'], 101, 3);
                add_action('woocommerce_process_product_meta', [$this, 'en_save_product_fields'], 101, 1);

                // Add variable product fields.
                add_action('woocommerce_product_after_variable_attributes', [$this, 'en_show_product_fields'], 101, 3);
                add_action('woocommerce_save_product_variation', [$this, 'en_save_product_fields'], 101, 1);

                // Check compatible with our old eniture plugins.
                add_filter('En_Plugins_dropship_filter', [$this, 'en_compatible_other_eniture_plugins']);
                add_filter('En_Plugins_variable_freight_classification_filter', [$this, 'en_compatible_other_eniture_plugins']);
            }

            $this->add_small_package_quote_fields();
        }

        /**
         * Transportation insight plan status
         * @param array $plan_status
         * @return array
         */
        public function en_ups_small_plan_status($plan_status)
        {
            $en_plugin_name = 'Small Package Quotes - UPS Edition';

            // Hazardous plan status
            $plan_required = '0';
            $hazardous_material_status = $en_plugin_name . ': Enabled.';
            $hazardous_material = apply_filters("eniture_ups_small_quotes_plans_suscription_and_features", 'hazardous_material');
            if (is_array($hazardous_material)) {
                $plan_required = '1';
                $hazardous_material_status = $en_plugin_name . ': Upgrade to Standard Plan to enable.';
            }

            $plan_status['hazardous_material']['ups_small'][] = 'ups_small';
            $plan_status['hazardous_material']['plan_required'][] = $plan_required;
            $plan_status['hazardous_material']['status'][] = $hazardous_material_status;

            // Nesting this item
            $plan_required = '0';
            $nesting_status = $en_plugin_name . ': Enabled.';
            $nesting_material = apply_filters("eniture_ups_small_quotes_plans_suscription_and_features", 'nested_material');
            if (is_array($nesting_material)) {
                $plan_required = '1';
                $nesting_status = $en_plugin_name . ': Upgrade to Advanced Plan to enable.';
            }

            $plan_status['nesting']['ups_small'][] = 'ups_small';
            $plan_status['nesting']['plan_required'][] = $plan_required;
            $plan_status['nesting']['status'][] = $nesting_status;

            return $plan_status;
        }

        /**
         * Restrict to show duplicate fields on product detail page.
         */
        public function en_compatible_other_eniture_plugins()
        {
            return true;
        }

        /**
         * Show product fields in variation and simple product.
         * @param array $loop
         * @param array $variation_data
         * @param array $variation
         */
        public function en_show_product_fields($loop, $variation_data = [], $variation = [])
        {
            $postId = (isset($variation->ID)) ? $variation->ID : get_the_ID();
            $this->en_custom_product_fields($postId);
        }

        /**
         * Save the simple product fields.
         * @param int $postId
         */
        public function en_save_product_fields($postId)
        {
            if (isset($postId) && $postId > 0) {
                $en_product_fields = $this->en_product_fields_arr();

                foreach ($en_product_fields as $key => $custom_field) {
                    $custom_field = (isset($custom_field['id'])) ? $custom_field['id'] : '';
                    $en_updated_product = (isset($_POST[$custom_field][$postId])) ? sanitize_text_field(wp_unslash( $_POST[$custom_field][$postId] )) : '';
                    $en_updated_product = $custom_field == '_dropship_location' ?
                        (maybe_serialize(is_array($en_updated_product) ? array_map('intval', $en_updated_product) : $en_updated_product)) : esc_attr($en_updated_product);
                    update_post_meta($postId, $custom_field, $en_updated_product);
                }
            }
        }

        /**
         * Created dropship list get from db
         * @return array
         */
        public function en_dropship_list()
        {
            global $wpdb;
            $dropship = (array)$wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship'"), ARRAY_A);
            $en_dropship_list = [];
            foreach ($dropship as $list) {
                $en_nickname = (isset($list['nickname']) && strlen($list['nickname']) > 0) ? $list['nickname'] . ' - ' : '';
                $en_country = (isset($list['country']) && strlen($list['country']) > 0) ? '(' . $list['country'] . ')' : '';
                $en_zip = (isset($list['zip']) && strlen($list['zip']) > 0) ? $list['zip'] : '';
                $en_city = (isset($list['city']) && strlen($list['city']) > 0) ? $list['city'] : '';
                $en_state = (isset($list['state']) && strlen($list['state']) > 0) ? $list['state'] : '';
                $location = "$en_nickname $en_zip, $en_city, $en_state $en_country";
                $en_dropship_list[$list['id']] = $location;
            }

            return $en_dropship_list;
        }

        /**
         * Product Fields Array
         * @return array
         */
        public function en_product_fields_arr()
        {
            $en_product_fields = [
                [
                    'type' => 'checkbox',
                    'id' => '_enable_dropship',
                    'class' => '_enable_dropship',
                    'line_item' => 'location',
                    'label' => 'Enable Drop Ship Location',
                ],
                [
                    'type' => 'dropdown',
                    'id' => '_dropship_location',
                    'class' => '_dropship_location short',
                    'line_item' => 'locationId',
                    'label' => 'Drop ship location',
                    'options' => $this->en_dropship_list()
                ],
                [
                    'type' => 'input_field',
                    'id' => '_en_product_markup',
                    'class' => '_en_product_markup short',
                    'label' => __( 'Markup', 'small-package-quotes-ups-edition' ),
                    'placeholder' => 'e.g Currency 1.00 or percentage 5%',
                    'description' => 'Increases the amount of the returned quote by a specified amount prior to displaying it in the shopping cart. The number entered will be interpreted as dollars and cents unless it is followed by a % sign. For example, entering 5.00 will cause $5.00 to be added to the quotes. Entering 5.00% will cause each quote to be multiplied by 1.05 (= 1 + 5%).'
                ],
                [
                    'type' => 'checkbox',
                    'id' => '_hazardousmaterials',
                    'line_item' => 'isHazmatLineItem',
                    'class' => '_en_hazardous_material ' . $this->hazardous_disabled_plan,
                    'label' => 'Hazardous material',
                    'plans' => 'hazardous_material',
                    'description' => $this->hazardous_plan_required,
                ]
            ];

            // Micro Warehouse
            $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (stripos(implode($all_plugins), 'micro-warehouse-shipping.php') || is_plugin_active_for_network('micro-warehouse-shipping-for-woocommerce/micro-warehouse-shipping.php')) {
                $en_product_fields = array_slice($en_product_fields, 2);
            }

            // We can use hook for add new product field from other plugin add-on
            $en_product_fields = apply_filters('en_product_fields', $en_product_fields);
            return $en_product_fields;
        }

        /**
         * Common plans status
         */
        public function en_app_common_plan_status()
        {
            $plan_status = apply_filters('en_app_common_plan_status', []);

            // Hazardous plan status
            if (isset($plan_status['hazardous_material'])) {
                if (!in_array(0, $plan_status['hazardous_material']['plan_required'])) {
                    $this->hazardous_disabled_plan = 'disabled_me';
                    $this->hazardous_plan_required = apply_filters("eniture_ups_small_plans_notification_link", [2, 3]);
                } elseif (isset($plan_status['hazardous_material']['status'])) {
                    $this->hazardous_plan_required = implode(" <br>", $plan_status['hazardous_material']['status']);
                }
            }
        }

        /**
         * Show Product Fields
         * @param int $postId
         */
        public function en_custom_product_fields($postId)
        {
            $this->en_app_common_plan_status();
            $en_product_fields = $this->en_product_fields_arr();

            // Check compatability hazardous materials with other plugins.
            if (class_exists("UpdateProductDetailOption")) {
                array_pop($en_product_fields);
            }

            $this->populate_product_fields($en_product_fields, $postId);
        }

        /**
         * Dynamic checkbox field show on product detail page
         * @param array $custom_field
         * @param int $postId
         */
        public function en_product_checkbox($custom_field, $postId)
        {
            $custom_checkbox_field = [
                'id' => $custom_field['id'] . '[' . $postId . ']',
                'value' => get_post_meta($postId, $custom_field['id'], true),
                'label' => $custom_field['label'],
                'class' => $custom_field['class'],
            ];

            if (isset($custom_field['description'])) {
                $custom_checkbox_field['description'] = $custom_field['description'];
            }

            woocommerce_wp_checkbox($custom_checkbox_field);
        }

        /**
         * Dynamic dropdown field show on product detail page
         * @param array $custom_field
         * @param int $postId
         */
        public function en_product_dropdown($custom_field, $postId)
        {
            $get_meta = get_post_meta($postId, $custom_field['id'], true);
            $assigned_option = is_serialized($get_meta) ? maybe_unserialize($get_meta) : $get_meta;
            $custom_dropdown_field = [
                'id' => $custom_field['id'] . '[' . $postId . ']',
                'label' => $custom_field['label'],
                'class' => $custom_field['class'],
                'value' => $assigned_option,
                'options' => $custom_field['options']
            ];

            woocommerce_wp_select($custom_dropdown_field);
        }

        /**
         * Dynamic input field show on product detail page
         * @param array $custom_field
         * @param int $postId
         */
        public function en_product_input_field($custom_field, $postId)
        {
            $placeholder = (isset($custom_field['placeholder'])) ? $custom_field['placeholder'] : $custom_field['label'];

            $custom_input_field = [
                'id' => $custom_field['id'] . '[' . $postId . ']',
                'label' => $custom_field['label'],
                'class' => $custom_field['class'],
                'placeholder' => $placeholder,
                'value' => get_post_meta($postId, $custom_field['id'], true)
            ];

            if (isset($custom_field['description'])) {
                $custom_input_field['desc_tip'] = true;
                $custom_input_field['description'] = $custom_field['description'];
            }

            woocommerce_wp_text_input($custom_input_field);
        }

        /**
         * Add small package quotes fields
         */
        public function add_small_package_quote_fields(){
            if (!has_filter('en_small_package_quotes_fields')) {
                // Add simple product fields
                add_action('woocommerce_product_options_shipping', [$this, 'en_show_spq_fields'], 101, 3);
                add_action('woocommerce_process_product_meta', [$this, 'en_save_spq_fields'], 101, 1);

                // Add variable product fields.
                add_action('woocommerce_product_after_variable_attributes', [$this, 'en_show_spq_fields'], 101, 3);
                add_action('woocommerce_save_product_variation', [$this, 'en_save_spq_fields'], 101, 1);

                // Check compatible with our old eniture plugins.
                add_filter('en_small_package_quotes_fields', [$this, 'en_small_package_quotes_fields']);
            }
        }

        /**
         * Show product fields in variation and simple product.
         * @param array $loop
         * @param array $variation_data
         * @param array $variation
         */
        public function en_show_spq_fields($loop, $variation_data = [], $variation = [])
        {
            $postId = (isset($variation->ID)) ? $variation->ID : get_the_ID();
            $this->en_show_spq_custom_product_fields($postId);
        }

        /**
         * Show SPQ Fields
         * @param int $postId
         */
        public function en_show_spq_custom_product_fields($postId)
        {
            $this->populate_product_fields($this->en_spq_fields_arr(), $postId);
        }

        /**
         * Populate Product Fields
         */
        public function populate_product_fields($en_product_fields, $postId){
            foreach ($en_product_fields as $key => $custom_field) {
                $en_field_type = (isset($custom_field['type'])) ? $custom_field['type'] : '';
                $en_action_function_name = 'en_product_' . $en_field_type;

                if (method_exists($this, $en_action_function_name)) {
                    $this->$en_action_function_name($custom_field, $postId);
                }
            }
        }

        /**
         * Save SPQ product fields.
         * @param int $postId
         */
        public function en_save_spq_fields($postId)
        {
            if (isset($postId) && $postId > 0) {
                $en_product_fields = $this->en_spq_fields_arr();

                foreach ($en_product_fields as $key => $custom_field) {
                    $custom_field = (isset($custom_field['id'])) ? $custom_field['id'] : '';
                    $en_updated_product = (isset($_POST[$custom_field][$postId])) ? sanitize_text_field(wp_unslash( $_POST[$custom_field][$postId] )) : '';
                    update_post_meta($postId, $custom_field, $en_updated_product);
                }
            }
        }

        /**
         * SPQ Fields Array
         * @return array
         */
        public function en_spq_fields_arr()
        {
            $en_product_fields = [];
            $transit_restriction_enable_carrier_arr = apply_filters('eniture_check_ground_transit_restrict_status', []);
            if(count($transit_restriction_enable_carrier_arr) > 0){
                $en_product_fields[] = [
                    'type' => 'checkbox',
                    'id' => '_en_exempt_ground_transit_restriction',
                    'class' => '_en_exempt_ground_transit_restriction',
                    'label' => 'Exempt from Ground Transit Time Restriction',
                ];
            }
            return $en_product_fields;
        }

        /**
         * Restrict to show duplicate fields on product detail page.
         */
        public function en_small_package_quotes_fields()
        {
            return true;
        }
    }

    new Eniture_UpsSpqProductDetail();
}
