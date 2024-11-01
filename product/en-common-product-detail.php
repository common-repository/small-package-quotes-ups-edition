<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product detail page.
 */

/**
 * Add and show simple and variable products.
 * Class Eniture_UpsSpqProductCommonDetail
 * @package Eniture_UpsSpqProductCommonDetail
 */
if (!class_exists('Eniture_UpsSpqProductCommonDetail')) {

    class Eniture_UpsSpqProductCommonDetail
    {
        // Insurance
        public $insurance_disabled_plan = '';
        public $insurance_plan_required = '';

        /**
         * Hook for call.
         * Eniture_UpsSpqProductCommonDetail constructor.
         */
        public function __construct()
        {
            add_filter('en_app_common_plan_status', [$this, 'en_ups_freight_plan_status'], 10, 1);

            if (!has_filter('en_insurance_filter')) {
                // Add simple product fields
                add_action('woocommerce_product_options_shipping', [$this, 'en_show_product_fields'], 101, 3);
                add_action('woocommerce_process_product_meta', [$this, 'en_save_product_fields'], 101, 1);

                // Add variable product fields.
                add_action('woocommerce_product_after_variable_attributes', [$this, 'en_show_product_fields'], 101, 3);
                add_action('woocommerce_save_product_variation', [$this, 'en_save_product_fields'], 101, 1);

                // Check compatible with our old eniture plugins.
                add_filter('en_insurance_filter', [$this, 'en_compatible_other_eniture_plugins']);
            }
        }

        /**
         * Transportation insight plan status
         * @param array $plan_status
         * @return array
         */
        public function en_ups_freight_plan_status($plan_status)
        {
            $en_plugin_name = 'Small Package Quotes - UPS Edition';

            // Insurance plan status
            $plan_required = '0';
            $insurance_status = $en_plugin_name . ': Enabled.';
            $insurance = apply_filters("eniture_ups_small_quotes_plans_suscription_and_features", 'insurance_fee');
            if (is_array($insurance)) {
                $plan_required = '1';
                $insurance_status = $en_plugin_name . ': Upgrade to Standard Plan to enable.';
            }

            $plan_status['insurance']['ups_small'][] = 'ups_small';
            $plan_status['insurance']['plan_required'][] = $plan_required;
            $plan_status['insurance']['status'][] = $insurance_status;

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
                    $en_updated_product = (isset($_POST[$custom_field][$postId])) ? sanitize_text_field(wp_unslash($_POST[$custom_field][$postId])) : '';
                    $en_updated_product = $custom_field == '_dropship_location' ?
                        (maybe_serialize(is_array($en_updated_product) ? array_map('intval', $en_updated_product) : $en_updated_product)) : esc_attr($en_updated_product);
                    update_post_meta($postId, $custom_field, $en_updated_product);
                }
            }
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
                    'id' => '_en_insurance_fee',
                    'line_item' => 'insurance',
                    'class' => '_en_insurance_fee ' . $this->insurance_disabled_plan,
                    'label' => 'Insure this item',
                    'plans' => 'insurance_fee',
                    'description' => $this->insurance_plan_required,
                ]
            ];

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

            // Insurance plan status
            if (isset($plan_status['insurance'])) {
                if (!in_array(0, $plan_status['insurance']['plan_required'])) {
                    $this->insurance_disabled_plan = 'disabled_me';
                    $this->insurance_plan_required = apply_filters("eniture_ups_small_plans_notification_link", [2, 3]);
                } elseif (isset($plan_status['insurance']['status'])) {
                    $this->insurance_plan_required = implode(" <br>", $plan_status['insurance']['status']);
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
            if (class_exists("UpdateProductInsuranceDetailOption")) {
                array_pop($en_product_fields);
            }

            foreach ($en_product_fields as $key => $custom_field) {
                $en_field_type = (isset($custom_field['type'])) ? $custom_field['type'] : '';
                $en_action_function_name = 'en_product_' . $en_field_type;

                if (method_exists($this, $en_action_function_name)) {
                    $this->$en_action_function_name($custom_field, $postId);
                }
            }
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
            $custom_input_field = [
                'id' => $custom_field['id'] . '[' . $postId . ']',
                'label' => $custom_field['label'],
                'class' => $custom_field['class'],
                'placeholder' => $custom_field['label'],
                'value' => get_post_meta($postId, $custom_field['id'], true)
            ];

            if (isset($custom_field['description'])) {
                $custom_input_field['desc_tip'] = true;
                $custom_input_field['description'] = $custom_field['description'];
            }

            woocommerce_wp_text_input($custom_input_field);
        }
    }

    new Eniture_UpsSpqProductCommonDetail();
}
