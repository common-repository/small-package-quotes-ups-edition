<?php

/**
 * Includes Shipping Rules Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Eniture_UpsSmallShippingRulesAjaxReq")) {

    class Eniture_UpsSmallShippingRulesAjaxReq
    {
        /**
         * Get shipping rules ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_save_shipping_rule', array($this, 'save_shipping_rule_ajax'));
            add_action('wp_ajax_en_save_shipping_rule', array($this, 'save_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_edit_shipping_rule', array($this, 'edit_shipping_rule_ajax'));
            add_action('wp_ajax_en_edit_shipping_rule', array($this, 'edit_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_delete_shipping_rule', array($this, 'delete_shipping_rule_ajax'));
            add_action('wp_ajax_en_delete_shipping_rule', array($this, 'delete_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_update_shipping_rule_status', array($this, 'update_shipping_rule_status_ajax'));
            add_action('wp_ajax_en_update_shipping_rule_status', array($this, 'update_shipping_rule_status_ajax'));
        }

        // MARK: Save Shipping Rule
        /**
         * Save Shipping Rule Function
         * @global $wpdb
         */
        function save_shipping_rule_ajax()
        {
            global $wpdb;

            $insert_qry = $update_qry = '';
            $error = false;
            $data = $_POST;
            $get_shipping_rule_id = (isset($data['rule_id']) && intval($data['rule_id'])) ? $data['rule_id'] : "";
            $last_id = $get_shipping_rule_id;
            $get_shipping_rule = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "eniture_ups_small_shipping_rules WHERE name = %s", $data['name']));
            unset($data['action']);
            unset($data['rule_id']);
            
            if (!empty($get_shipping_rule_id)) {
                $data['settings'] = wp_json_encode($data['settings']);
                $update_qry = $wpdb->update(
                    $wpdb->prefix . 'eniture_ups_small_shipping_rules', $data, array('id' => $get_shipping_rule_id)
                );

                $update_qry = (!empty($get_shipping_rule) && reset($get_shipping_rule)->id == $get_shipping_rule_id) ? 1 : $update_qry;
            } else {
                if (!empty($get_shipping_rule)) {
                    $error = true;
                } else {
                    $data['settings'] = wp_json_encode($data['settings']);
                    $insert_qry = $wpdb->insert($wpdb->prefix . 'eniture_ups_small_shipping_rules', $data);
                    $last_id = $wpdb->insert_id;
                }
            }

            $shipping_rules_list = array('name' => $data["name"], 'type' => $data["type"], 'is_active' => $data["is_active"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $last_id, 'error' => $error);

            echo wp_json_encode($shipping_rules_list);
            exit;
        }

        // MARK: Edit Shipping Rule
        /**
         * Edit Shipping Rule Function
         * @global $wpdb
         */
        function edit_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? sanitize_text_field(wp_unslash($_POST['edit_id'])) : "";
            $shipping_rules_list = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "eniture_ups_small_shipping_rules WHERE id=$get_shipping_rule_id"
            );
            $product_tags_markup = $this->get_product_tags_markup($shipping_rules_list);
            $states_markup = $this->get_country_states_markup($shipping_rules_list);
            $data = ['rule_data' => reset($shipping_rules_list), 'product_tags_markup' => $product_tags_markup, 'country_states_markup' => $states_markup];

            echo wp_json_encode($data);
            exit;
        }

        // MARK: Delete Shipping Rule
        /**
         * Delete Shipping Rule Function
         * @global $wpdb
         */
        function delete_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? sanitize_text_field(wp_unslash($_POST['delete_id'])) : "";
            $qry = $wpdb->delete($wpdb->prefix . 'eniture_ups_small_shipping_rules', array('id' => $get_shipping_rule_id));

            echo wp_json_encode(['query' => $qry]);
            exit;
        }

        // MARK: Update Shipping Rule Status
        /**
         * Update Shipping Rule Status Function
         * @global $wpdb
         */
        function update_shipping_rule_status_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['rule_id']) && intval($_POST['rule_id'])) ? sanitize_text_field(wp_unslash($_POST['rule_id'])) : "";
            $is_active = isset($_POST['is_active']) ? sanitize_text_field(wp_unslash($_POST['is_active'])) : "";
            $data = ['is_active' => $is_active];
            
            $update_qry = $wpdb->update(
                $wpdb->prefix . 'eniture_ups_small_shipping_rules', $data, array('id' => $get_shipping_rule_id)
            );

            echo wp_json_encode(['id' => $get_shipping_rule_id, 'is_active' => $is_active, 'update_qry' => $update_qry]);
            exit;
        }

        // MARK: Get Product Tags
        /**
         * Get Product Tags Function
         * @global $wpdb
         */
        function get_product_tags_markup($shipping_rules_list)
        {
            $tags_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $tags_data = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_tags_detials = $this->get_selected_tags_details($tags_data['filter_by_product_tag_value']);

            if (!empty($selected_tags_detials) && is_array($selected_tags_detials)) {
                foreach ($selected_tags_detials as $key => $tag) {
                    $tags_options .= "<option selected='selected' value='" . esc_attr($tag['term_taxonomy_id']) . "'>" . esc_html($tag['name']) . "</option>";
                }
            }

            if (empty($tags_data['filter_by_product_tag_value']) || !is_array($tags_data['filter_by_product_tag_value'])) {
                $tags_data['filter_by_product_tag_value'] = [];
            }

            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
            if (!empty($en_woo_product_tags) && is_array($tags_data['filter_by_product_tag_value'])) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (!in_array($tag->term_id, $tags_data['filter_by_product_tag_value'])) {
                        $tags_options .= "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                    }
                }
            }

            return $tags_options;
        }

        // MARK: Get Selected Tags Details
        /**
         * Get Selected Tags Details Function
         * @global $wpdb
         */
        function get_selected_tags_details($products_tags_arr)
        {
            if (empty($products_tags_arr) || !is_array($products_tags_arr)) {
                return [];
            }

            $tags_detail = [];
            $count = 0;
            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );

            if (isset($en_woo_product_tags) && !empty($en_woo_product_tags)) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (in_array($tag->term_taxonomy_id, $products_tags_arr)) {
                        $tags_detail[$count]['term_id'] = $tag->term_id;
                        $tags_detail[$count]['name'] = $tag->name;
                        $tags_detail[$count]['slug'] = $tag->slug;
                        $tags_detail[$count]['term_taxonomy_id'] = $tag->term_taxonomy_id;
                        $tags_detail[$count]['description'] = $tag->description;
                        $count++;
                    }
                }
            }

            return $tags_detail;
        }

        // MARK: Get Country States
        /**
         * Get Country States Function
         * @global $wpdb
         */
        function get_country_states_markup($shipping_rules_list)
        {
            $states_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $settings = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_states_detials = $this->get_selected_states_details($settings);

            if (!empty($selected_states_detials) && is_array($selected_states_detials)) {
                foreach ($selected_states_detials as $s_code => $s_label) {
                    $states_options .= "<option selected='selected' value='" . esc_attr($s_code) . "'>" . esc_html($s_label) . "</option>";
                }
            }

            if (empty($settings['filter_by_state_value']) || !is_array($settings['filter_by_state_value'])) {
                $settings['filter_by_state_value'] = [];
            }

            $countries_obj = new WC_Countries();
            $selected_country = isset($settings['filter_by_country_value']) ? $settings['filter_by_country_value'] : '';
            $en_woo_states = $countries_obj->get_states($selected_country);

            if (!empty($en_woo_states) && is_array($settings['filter_by_state_value'])) {
                foreach ($en_woo_states as $s_code => $s_label) {
                    if (!in_array($s_code, $settings['filter_by_state_value'])) {
                        $states_options .= "<option value='" . esc_attr($s_code) . "'>" . esc_html($s_label) . "</option>";
                    }
                }
            }

            return $states_options;
        }

        // MARK: Get Selected States Details
        /**
         * Get Selected States Details
         * @global $wpdb
         */
        function get_selected_states_details($settings)
        {
            $states_arr = isset($settings['filter_by_state_value']) ? $settings['filter_by_state_value'] : [];
            if (empty($states_arr) || !is_array($states_arr)) {
                return [];
            }

            $countries_obj = new WC_Countries();
            $selected_country = isset($settings['filter_by_country_value']) ? $settings['filter_by_country_value'] : '';
            $en_woo_states = $countries_obj->get_states($selected_country);
            $states_detail = [];

            if (isset($en_woo_states) && !empty($en_woo_states)) {
                foreach ($en_woo_states as $key => $state) {
                    if (in_array($key, $states_arr)) $states_detail[$key] = $state;
                }
            }

            return $states_detail;
        }

        /**
         * Get the large cart settings array.
         * @return array Returns the max items and max weight per package in an array format or empty array if not found.
         */
        function get_large_cart_settings()
        {
            global $wpdb;
            $rules = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "eniture_ups_small_shipping_rules"), ARRAY_A);
            if (empty($rules)) return [];

            $response = [];
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;

                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';
                if ($rule_type == 'Large Cart Settings' && !empty($settings['max_items']) && !empty($settings['max_weight_per_package'])) {
                    $response['largeCartSettingFlag'] = '1';
                    $response['largeCartMaxItems'] = $settings['max_items'];
                    $response['largeCartWeightPerPackage'] = $settings['max_weight_per_package'];
                    break;
                }
            }

            return $response;
        }
    }
}

new Eniture_UpsSmallShippingRulesAjaxReq();
