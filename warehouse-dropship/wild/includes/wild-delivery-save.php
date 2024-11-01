<?php

/**
 * Includes Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Eniture_WooWdAddonsAjaxReqIncludes")) {

    class Eniture_WooWdAddonsAjaxReqIncludes
    {

        public $plugin_standards;
        public $selected_plan;
        public $EnWooAddonAutoResidDetectionTemplate;

        /**
         * Get Address ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_eniture_wd_get_address', array($this, 'eniture_get_address_api_ajax'));
            add_action('wp_ajax_eniture_wd_get_address', array($this, 'eniture_get_address_api_ajax'));

            add_action('wp_ajax_nopriv_eniture_wd_delete_dropship', array($this, 'eniture_delete_dropship_ajax'));
            add_action('wp_ajax_eniture_wd_delete_dropship', array($this, 'eniture_delete_dropship_ajax'));

            add_action('wp_ajax_nopriv_eniture_wd_save_warehouse', array($this, 'eniture_save_warehouse_ajax'));
            add_action('wp_ajax_eniture_wd_save_warehouse', array($this, 'eniture_save_warehouse_ajax'));

            add_action('wp_ajax_nopriv_eniture_wd_save_dropship', array($this, 'eniture_save_dropship_ajax'));
            add_action('wp_ajax_eniture_wd_save_dropship', array($this, 'eniture_save_dropship_ajax'));


            add_action('wp_ajax_nopriv_eniture_wd_edit_dropship', array($this, 'eniture_edit_dropship_ajax'));
            add_action('wp_ajax_eniture_wd_edit_dropship', array($this, 'eniture_edit_dropship_ajax'));

            add_action('wp_ajax_nopriv_eniture_wd_delete_warehouse', array($this, 'eniture_delete_warehouse_ajax'));
            add_action('wp_ajax_eniture_wd_delete_warehouse', array($this, 'eniture_delete_warehouse_ajax'));

            add_action('wp_ajax_nopriv_eniture_wd_edit_warehouse', array($this, 'eniture_edit_warehouse_ajax'));
            add_action('wp_ajax_eniture_wd_edit_warehouse', array($this, 'eniture_edit_warehouse_ajax'));
        }

        /**
         * Get Address From ZipCode Using API
         */
        function eniture_get_address_api_ajax()
        {
            if (isset($_POST['origin_zip'])) {
                $map_address = (isset($_POST['origin_zip'])) ? sanitize_text_field(wp_unslash( $_POST['origin_zip'] )) : "";
                $zipCode = str_replace(' ', '', $map_address);
                $accessLevel = 'address';
                $Get_sm_distance = new Eniture_Get_ups_small_distance();
                $resp_json = $Get_sm_distance->ups_small_address($zipCode, $accessLevel);
                $map_result = json_decode($resp_json, true);

                $city = "";
                $state = "";
                $country = "";
                $postcode_localities = 0;
                $address_type = $city_name = $city_option = '';

                if (isset($map_result['error']) && !empty($map_result['error'])) {
                    echo wp_json_encode(array('apiResp' => 'apiErr'));
                    exit;
                }
                if (isset($map_result['results'], $map_result['status']) && (empty($map_result['results'])) && ($map_result['status'] == "ZERO_RESULTS")) {
                    echo wp_json_encode(array('result' => 'ZERO_RESULTS'));
                    exit;
                }
                if (count($map_result['results']) == 0) {
                    echo wp_json_encode(array('result' => 'false'));
                    exit;
                }
                $first_city = '';
                if (count($map_result['results']) > 0) {
                    $arrComponents = $map_result['results'][0]['address_components'];
                    if (isset($map_result['results'][0]['postcode_localities']) && $map_result['results'][0]['postcode_localities']) {
                        foreach ($map_result['results'][0]['postcode_localities'] as $index => $component) {
                            $first_city = ($index == 0) ? $component : $first_city;
                            $city_option .= '<option value="' . trim($component) . ' "> ' . $component . ' </option>';
                        }
                        $city = '<select id="' . $address_type . '_city" class="city-multiselect select en_wd_multi_state city_select_css" name="' . $address_type . '_city" aria-required="true" aria-invalid="false">
                                    ' . $city_option . '</select>';
                        $postcode_localities = 1;
                    } elseif ($arrComponents) {
                        foreach ($arrComponents as $index => $component) {
                            $type = $component['types'][0];
                            if ($city == "" && ($type == "sublocality_level_1" || $type == "locality")) {
                                $city_name = trim($component['long_name']);
                            }
                        }
                    }
                    if ($arrComponents) {
                        foreach ($arrComponents as $index => $state_app) {
                            $type = $state_app['types'][0];
                            if ($state == "" && ($type == "administrative_area_level_1")) {
                                $state_name = trim($state_app['short_name']);
                                $state = $state_name;
                            }
                            if ($country == "" && ($type == "country")) {
                                $country_name = trim($state_app['short_name']);
                                $country = $country_name;
                            }
                        }
                    }
                    echo wp_json_encode(array('first_city' => $first_city, 'city' => $city_name, 'city_option' => $city, 'state' => $state, 'country' => $country, 'postcode_localities' => $postcode_localities));
                    exit;
                }
            }
        }

        /**
         * Validate Input Fields
         * @param type $sPostData
         * @return string
         */
        function pkg_validate_post_data($sPostData)
        {
            foreach ($sPostData as $key => &$tag) {
                $check_characters = $key == "city" ? preg_match('/[#$%@^&!_*()+=\[\]\';,\/{}|":<>?~\\\\]/', $tag) : preg_match('/[#$%@^&!_*()+=\-\[\]\';,\/{}|":<>?~\\\\]/', $tag);
                // preferred origin
                if ($check_characters != 1 ||
                    $key == "pref_loc_lfq" ||
                    $key == "pref_loc_spq" ||
                    $key == "address" ||
                    $key == "match_postal_local_delivery" ||
                    $key == "match_postal_store_pickup" ||
                    $key == "checkout_desc_local_delivery" ||
                    $key == "checkout_desc_store_pickup" ||
                    // Terminal phone number
                    $key == "phone_instore" ||
                    $key == "nickname" ||
                    $key == "origin_markup") {
                    $data[$key] = sanitize_text_field($tag);
                } else {
                    $data[$key] = 'Error';
                }
            }

            return $data;
        }

        /**
         * Filtered Data Array
         * @param $validateData
         * @return array
         */
        function filtered_data($validateData)
        {
            return array(
                'city' => $validateData["city"],
                'state' => $validateData["state"],
                'zip' => preg_replace('/\s+/', '', $validateData["zip"]),
                'country' => $validateData["country"],
                'location' => $validateData["location"],
                'nickname' => (isset($validateData["nickname"])) ? $validateData["nickname"] : "",
            );
        }

        /**
         * Save Warehouse Function
         * @global $wpdb
         */
        function eniture_save_warehouse_ajax()
        {
            global $wpdb;
            $html = "";

            if (isset($_POST['origin_country']) && $_POST['origin_country'] != '') {
                $origin_country_code = sanitize_text_field( wp_unslash( $_POST['origin_country']) );
                $countrycode = strtolower($origin_country_code);
                $origin_country = ($countrycode == 'un') ? 'US' : $origin_country_code;
            }

            $input_data_arr = array(
                'city'                         =>  isset($_POST['origin_city']) ? sanitize_text_field(wp_unslash($_POST['origin_city'])) : '',
                'state'                        =>  isset($_POST['origin_state']) ? sanitize_text_field(wp_unslash($_POST['origin_state'])) : '',
                // Origin terminal address
                'address'                      =>  isset($_POST['origin_address']) ? sanitize_text_field(wp_unslash($_POST['origin_address'])) : '',
                'zip'                          =>  isset($_POST['origin_zip']) ? sanitize_text_field(wp_unslash($_POST['origin_zip'])) : '',
                'country'                      =>  $origin_country,
                'location'                     =>  isset($_POST['location']) ? sanitize_text_field(wp_unslash($_POST['location'])) : '',
                'enable_store_pickup'          =>  isset($_POST['enable_instore']) && $_POST['enable_instore'] === 'true' ? 1 : 0,
                'miles_store_pickup'           =>  isset($_POST['address_miles_instore']) ? sanitize_text_field(wp_unslash($_POST['address_miles_instore'])) : '',
                'match_postal_store_pickup'    =>  isset($_POST['zipmatch_instore']) ? sanitize_text_field(wp_unslash($_POST['zipmatch_instore'])) : '',
                'checkout_desc_store_pickup'   =>  isset($_POST['desc_instore']) ? sanitize_text_field(wp_unslash($_POST['desc_instore'])) : '',
                // Terminal phone number
                'phone_instore'                =>  isset($_POST['phone_instore']) ? sanitize_text_field(wp_unslash($_POST['phone_instore'])) : '',
                'enable_local_delivery'        =>  isset($_POST['enable_delivery']) && $_POST['enable_delivery'] === 'true' ? 1 : 0,
                'miles_local_delivery'         =>  isset($_POST['address_miles_delivery']) ? sanitize_text_field(wp_unslash($_POST['address_miles_delivery'])) : '',
                'match_postal_local_delivery'  =>  isset($_POST['zipmatch_delivery']) ? sanitize_text_field(wp_unslash($_POST['zipmatch_delivery'])) : '',
                'checkout_desc_local_delivery' =>  isset($_POST['desc_delivery']) ? sanitize_text_field(wp_unslash($_POST['desc_delivery'])) : '',
                'fee_local_delivery'           =>  isset($_POST['fee_delivery']) ? sanitize_text_field(wp_unslash($_POST['fee_delivery'])) : '',
                'suppress_local_delivery'      =>  isset($_POST['supppress_delivery']) ? (sanitize_text_field(wp_unslash($_POST['supppress_delivery'])) === 'true' ? 1 : 0) : 0,
                'origin_markup'                =>  isset($_POST['origin_markup']) ? sanitize_text_field(wp_unslash($_POST['origin_markup'])) : '',

            );
            // preferred origin
            if (is_plugin_active('preferred-origin/preferred-origin.php')) {
                $input_data_arr = apply_filters('en_pref_field', $input_data_arr, $_POST);
            }
            $validateData = $this->pkg_validate_post_data($input_data_arr);
            $get_warehouse = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE city = %s AND state = %s AND zip = %s AND country = %s",
                    $validateData["city"],
                    $validateData["state"],
                    $validateData["zip"],
                    $validateData["country"]
                )
            );

            $insert_qry = $update_qry = '';
            if ($validateData["city"] != 'Error') {
                $data = $validateData;
                
                if (isset($validateData["city"])) {
                    $get_warehouse_id = (isset($_POST['origin_id']) && intval($_POST['origin_id'])) ? sanitize_text_field(wp_unslash($_POST['origin_id'])) : "";
                    if ($get_warehouse_id && (empty($get_warehouse) || (!empty($get_warehouse) && reset($get_warehouse)->id == $get_warehouse_id))) {
                        $update_qry = $wpdb->update(
                            $wpdb->prefix . 'warehouse', $data, array('id' => $get_warehouse_id)
                        );

                        $update_qry = (!empty($get_warehouse) && reset($get_warehouse)->id == $get_warehouse_id) ? 1 : $update_qry;
                    } else {
                        if (empty($get_warehouse)) {
                            $insert_qry = $wpdb->insert(
                                $wpdb->prefix . 'warehouse', $data
                            );

                            $html = eniture_warehouse_template(TRUE);
                        }
                    }
                }
                $lastid = $wpdb->insert_id;
                if ($lastid == 0) {
                    $lastid = $get_warehouse_id;
                }
                $warehous_list = array('origin_city' => $data["city"], 'origin_state' => $data["state"], 'origin_zip' => $data["zip"], 'origin_country' => $data["country"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $lastid, 'html' => $html);
                echo wp_json_encode($warehous_list);
                exit;
            } else {
                echo "false";
                exit;
            }
        }


        /**
         * Edit Warehouse Function
         * @global $wpdb
         */
        function eniture_edit_warehouse_ajax()
        {
            // Terminal phone number
            global $wpdb;
            $get_warehouse_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? sanitize_text_field(wp_unslash($_POST['edit_id'])) : "";
            $warehous_list = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id=%d",
                    $get_warehouse_id
                )
            );

            echo wp_json_encode($warehous_list);
            exit;
        }


        /**
         * Delete Warehouse Function
         * @global $wpdb
         */
        function eniture_delete_warehouse_ajax()
        {
            global $wpdb;
            $get_warehouse_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? sanitize_text_field(wp_unslash($_POST['delete_id'])) : "";
            $qry = $wpdb->delete($wpdb->prefix . 'warehouse', array('id' => $get_warehouse_id, 'location' => 'warehouse'));

            $html = eniture_warehouse_template(TRUE);
            echo wp_json_encode($html);
            exit;
        }


        /**
         * Save Dropship Function
         * @global $wpdb
         */
        function eniture_save_dropship_ajax()
        {
            global $wpdb;
            $html = "";

            if (isset($_POST['dropship_country']) && $_POST['dropship_country'] != '') {
                $dropship_contry = sanitize_text_field( wp_unslash( $_POST['dropship_country']) );
                $countrycode = strtolower($dropship_contry);
                $dropship_contry = ($countrycode == 'un') ? 'US' : $dropship_contry;
            }

            $input_data_arr = array(
                'city'     => isset($_POST['dropship_city']) ? sanitize_text_field(wp_unslash($_POST['dropship_city'])) : '',
                'state'    => isset($_POST['dropship_state']) ? sanitize_text_field(wp_unslash($_POST['dropship_state'])) : '',
                // Origin terminal address
                'address'  => isset($_POST['dropship_address']) ? sanitize_text_field(wp_unslash($_POST['dropship_address'])) : '',
                'zip'      => isset($_POST['dropship_zip']) ? sanitize_text_field(wp_unslash($_POST['dropship_zip'])) : '',
                'country'  => $dropship_contry,
                'location' => isset($_POST['location']) ? sanitize_text_field(wp_unslash($_POST['location'])) : '',
                'nickname' => isset($_POST['nickname']) ? sanitize_text_field(wp_unslash($_POST['nickname'])) : '',

                'enable_store_pickup'          =>  isset($_POST['enable_instore']) && $_POST['enable_instore'] === 'true' ? 1 : 0,
                'miles_store_pickup'           =>  isset($_POST['address_miles_instore']) ? sanitize_text_field( wp_unslash($_POST['address_miles_instore'])) : '',
                'match_postal_store_pickup'    =>  isset($_POST['zipmatch_instore']) ? sanitize_text_field( wp_unslash($_POST['zipmatch_instore'])) : '',
                'checkout_desc_store_pickup'   =>  isset($_POST['desc_instore']) ? sanitize_text_field( wp_unslash($_POST['desc_instore'])) : '',
                // Terminal phone number
                'phone_instore'                =>  isset($_POST['phone_instore']) ? sanitize_text_field(wp_unslash($_POST['phone_instore'])) : '',
                'enable_local_delivery'        =>  isset($_POST['enable_delivery']) && $_POST['enable_delivery'] === 'true' ? 1 : 0,
                'miles_local_delivery'         =>  isset($_POST['address_miles_delivery']) ? sanitize_text_field(wp_unslash($_POST['address_miles_delivery'])) : '',
                'match_postal_local_delivery'  =>  isset($_POST['zipmatch_delivery']) ? sanitize_text_field(wp_unslash($_POST['zipmatch_delivery'])) : '',
                'checkout_desc_local_delivery' =>  isset($_POST['desc_delivery']) ? sanitize_text_field(wp_unslash($_POST['desc_delivery'])) : '',
                'fee_local_delivery'           =>  isset($_POST['fee_delivery']) ? sanitize_text_field(wp_unslash($_POST['fee_delivery'])) : '',
                'suppress_local_delivery'      =>  isset($_POST['supppress_delivery']) ? (sanitize_text_field(wp_unslash($_POST['supppress_delivery'])) === 'true' ? 1 : 0) : 0,
                'origin_markup'                =>  isset($_POST['origin_markup']) ? sanitize_text_field(wp_unslash($_POST['origin_markup'])) : '',
            );
            // preferred origin
            if (is_plugin_active('preferred-origin/preferred-origin.php')) {
                $input_data_arr = apply_filters('en_pref_field', $input_data_arr, $_POST);
            }
            $validateData = $this->pkg_validate_post_data($input_data_arr);
            $get_dropship = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE city = %s AND state = %s AND zip = %s AND nickname = %s AND country = %s",
                    $validateData["city"],
                    $validateData["state"],
                    $validateData["zip"],
                    $validateData["nickname"],
                    $validateData["country"]
                )
            );
        
            $insert_qry = $update_qry = '';
            if ($validateData["city"] != 'Error' && $validateData["nickname"] != 'Error') {
                $data = $validateData;

                if (isset($validateData["city"])) {
                    $get_dropship_id = (isset($_POST['dropship_id']) && intval($_POST['dropship_id'])) ? sanitize_text_field(wp_unslash($_POST['dropship_id'])) : "";

                    if ($get_dropship_id != '' && (empty($get_dropship) || (!empty($get_dropship) && reset($get_dropship)->id == $get_dropship_id))) {
                        $update_qry = $wpdb->update(
                            $wpdb->prefix . 'warehouse', $data, array('id' => $get_dropship_id)
                        );

                        $update_qry = (!empty($get_dropship) && reset($get_dropship)->id == $get_dropship_id) ? 1 : $update_qry;
                    } else {
                        if (empty($get_dropship)) {
                            $insert_qry = $wpdb->insert(
                                $wpdb->prefix . 'warehouse', $data
                            );

                            $html = eniture_dropship_template(TRUE);
                        }
                    }
                }
                $lastid = $wpdb->insert_id;
                if ($lastid == 0) {
                    $lastid = $get_dropship_id;
                }
                $warehous_list = array('nickname' => $data["nickname"], 'origin_city' => $data["city"], 'origin_state' => $data["state"], 'origin_zip' => $data["zip"], 'origin_country' => $data["country"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $lastid, 'html' => $html);
                echo wp_json_encode($warehous_list);
                exit;
            } else {
                echo "false";
                exit;
            }
        }


        /**
         * Edit Dropship Function
         * @global $wpdb
         */
        function eniture_edit_dropship_ajax()
        {
            // Terminal phone number
            global $wpdb;
            $get_dropship_id = (isset($_POST['dropship_edit_id']) && intval($_POST['dropship_edit_id'])) ? sanitize_text_field(wp_unslash($_POST['dropship_edit_id'])) : "";
            $warehous_list = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id=%d",
                    $get_dropship_id
                )
            );
            echo wp_json_encode($warehous_list);
            exit;
        }


        /**
         * Delete Dropship Function
         * @global $wpdb
         */
        function eniture_delete_dropship_ajax()
        {

            global $wpdb;

            $get_dropship_id = (isset($_POST['dropship_delete_id']) && intval($_POST['dropship_delete_id'])) ? sanitize_text_field(wp_unslash($_POST['dropship_delete_id'])) : "";
            $dropship_id = isset($_POST['dropship_delete_id']) ? sanitize_text_field(wp_unslash($_POST['dropship_delete_id'])) : "";
            $get_dropship_array = array($dropship_id);
            $get_dropship_val = array_map('intval', $get_dropship_array);
            $ser = maybe_serialize($get_dropship_val);
            $get_post_id = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT group_concat(post_id) as post_ids_list FROM `" . $wpdb->prefix . "postmeta` WHERE `meta_key` = '_dropship_location' AND (`meta_value` LIKE %s OR `meta_value` = %s)",
                    '%' . $ser . '%',
                    $dropship_id
                )
            );
            $post_id = reset($get_post_id)->post_ids_list;

            if (isset($post_id)) {
                $wpdb->query("UPDATE `" . $wpdb->prefix . "postmeta` SET `meta_value` = '' WHERE `meta_key` IN('_enable_dropship','_dropship_location') AND `post_id` IN ($post_id)");
            }

            $qry = $wpdb->delete($wpdb->prefix . "warehouse", array('id' => $get_dropship_id, 'location' => 'dropship'));

            $html = eniture_dropship_template(TRUE);
            echo wp_json_encode($html);
            exit;
        }

    }
}

new Eniture_WooWdAddonsAjaxReqIncludes();
