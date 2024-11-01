<?php
/**
 * Plugin Name:    Small Package Quotes - UPS Edition
 * Plugin URI:     https://eniture.com/products/
 * Description:    Dynamically retrieves your negotiated shipping rates from UPS and displays the results in the WooCommerce shopping cart.
 * Version:        4.5.12
 * Author:         Eniture Technology
 * Author URI:     http://eniture.com/
 * Text Domain:    small-package-quotes-ups-edition
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.6
 * Requires PHP:      7.4
 */
if (!defined('ABSPATH')) {
    exit;
}
define('ENITURE_UPS_SPQ_MAIN_DOMAIN', 'https://eniture.com');
define('ENITURE_UPS_DOMAIN_HITTING_URL', 'https://ws032.eniture.com');
define('ENITURE_UPS_SPQ_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

define('UPS_MAIN_FILE', __FILE__);
// Define reference
function eniture_ups_small_plugin($plugins)
{
    $plugins['spq'] = (isset($plugins['spq'])) ? array_merge($plugins['spq'], ['ups_small' => 'Eniture_WC_ups_small']) : ['ups_small' => 'Eniture_WC_ups_small'];
    return $plugins;
}

add_filter('en_plugins', 'eniture_ups_small_plugin');
if (!function_exists('eniture_woo_plans_notification_PD')) {

    function eniture_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($en = 1; $en <= 25; $en++) {
            $settings = get_option($eniture_plugins_id . $en);
            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? $product_detail_options[$key][$action] . ", $plugin_name" : "$plugin_name";
                    }
                }
            }
        }

        return $product_detail_options;
    }

    add_filter('en_woo_plans_notification_action', 'eniture_woo_plans_notification_PD', 10, 1);
}

if (!function_exists('eniture_woo_plans_notification_message')) {

    function eniture_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_notification_message_action', 'eniture_woo_plans_notification_message', 10, 2);
}

//Product detail set plans notification message for nested checkbox
if (!function_exists('eniture_woo_plans_nested_notification_message')) {

    function eniture_woo_plans_nested_notification_message($enable_plugins, $disable_plugins, $feature)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0 && $feature == 'nested_material') ? " $disable_plugins: Upgrade to <b>Advance Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_nested_notification_message_action', 'eniture_woo_plans_nested_notification_message', 10, 3);
}

/**
 * Load scripts for UPS Small json tree view
 */
if (!function_exists('eniture_ups_small_jtv_script')) {
    function eniture_ups_small_jtv_script()
    {
        wp_register_style('ups_small_json_tree_view_style', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('ups_small_json_tree_view_script', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.0');

        wp_enqueue_style('ups_small_json_tree_view_style');
        wp_enqueue_script('ups_small_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);

        // Shipping rules script and styles
        wp_enqueue_script('en_woo_sr_script', plugin_dir_url(__FILE__) . '/shipping-rules/assets/js/shipping_rules.js', array(), '1.0.4');
        wp_localize_script('en_woo_sr_script', 'script', array(
            'pluginsUrl' => plugins_url(),
        ));
        wp_register_style('shipping_rules_section', plugin_dir_url(__FILE__) . '/shipping-rules/assets/css/shipping_rules.css', false, '1.0.1');
        wp_enqueue_style('shipping_rules_section');
    }

    add_action('admin_init', 'eniture_ups_small_jtv_script');
}

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'eniture_ups_small_wc_avaibility_err');
}

/**
 * Check woocommerce installlation
 */
function eniture_ups_small_wc_avaibility_err()
{
    $message = "UPS Small is enabled, but not effective. It requires WooCommerce to work, please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin.";
    echo '<div class="error"> <p>' . esc_html($message) . '</p></div>';
}

add_action('admin_init', 'eniture_ups_small_check_woo_version');

/**
 * Check woo-commerce version compatibility
 */
function eniture_ups_small_check_woo_version()
{
    $woo_version = eniture_ups_small_wc_version_number();
    $version = '2.6';
    if (!version_compare($woo_version, $version, ">=")) {
        add_action('admin_notices', 'eniture_ups_small_wc_version_failure');
    }
}

/**
 * Check woo-commerce version incompatibility
 */
function eniture_ups_small_wc_version_failure()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            esc_html_e('UPS Small plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'eniture-ups-small-version-failure');
            ?>
        </p>
    </div>
    <?php
}

/**
 * Return woo-commerce version
 * @return string
 */
function eniture_ups_small_wc_version_number()
{
    $plugin_folder = get_plugins('/' . 'woocommerce');
    $plugin_file = 'woocommerce.php';

    if (isset($plugin_folder[$plugin_file]['Version'])) {
        return $plugin_folder[$plugin_file]['Version'];
    } else {
        return NULL;
    }
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_plugin_active_for_network('woocommerce/woocommerce.php')) {

    add_action('admin_enqueue_scripts', 'eniture_ups_small_admin_script');

    /**
     * Load scripts for UPS Small
     */
    function eniture_ups_small_admin_script()
    {

        wp_register_style('eniture_ups_small_style', plugin_dir_url(__FILE__) . '/css/ups-small-style.css', false, '1.1.7');
        wp_enqueue_style('eniture_ups_small_style');
    }

    add_filter('plugin_action_links', 'eniture_ups_small_add_action_plugin', 10, 5);

    /**
     * UPS Small action links
     * @staticvar type $plugin
     * @param $actions
     * @param $plugin_file
     * @return settings array
     */
    function eniture_ups_small_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;

        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);

        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=ups_small">' . __('Settings', 'small-package-quotes-ups-edition') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com/" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

    add_action('admin_enqueue_scripts', 'eniture_ups_small_script');

    /**
     * Load Front-end scripts for ups
     */
    function eniture_ups_small_script()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('eniture_ups_small_script', plugin_dir_url(__FILE__) . 'js/en-ups-small.js', array(), '1.0.8');
        wp_localize_script('eniture_ups_small_script', 'eniture_ups_small_admin_script', array(
            'plugins_url' => plugins_url(),
            'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
            'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
            'ups_small_order_cutoff_time' => get_option("ups_small_orderCutoffTime"),
            'ups_small_packaging_type' => get_option("ups_small_packaging_type")
        ));
    }

    /**
     * Include Plugin Files
     */
    require_once('warehouse-dropship/wild-delivery.php');
    require_once('shipping-rules/shipping-rules-save.php');
    require_once('warehouse-dropship/get-distance-request.php');
    require_once('standard-package-addon/standard-package-addon.php');
    require_once 'update-plan.php';
    require_once 'fdo/en-fdo.php';
    require_once 'fdo/en-sbs.php';
    require_once('ups-small-test-connection.php');
    require_once 'helper/en_helper_class.php';
    require_once('db/ups-small-db.php');
    require_once('ups-small-auto-residential.php');
    require_once('ups-small-admin-filter.php');
    require_once('ups-small-shipping-class.php');
    require_once('template/connection-settings.php');
    require_once('template/quote-settings.php');

    require_once 'product/en-common-product-detail.php';
    require_once 'product/en-product-detail.php';

    // Origin terminal address
    add_action('admin_init', 'ups_small_update_warehouse');
    add_action('admin_init', 'eniture_create_ups_small_shipping_rules_db');
    require_once 'template/csv-export.php';
    require_once('order-details/en-order-export.php');
    require_once('order-details/en-order-widget.php');
    require_once('template/products-nested-options.php');
    require_once('ups-small-carrier-service.php');
    require_once('ups-small-group-package.php');
    require_once('ups-small-wc-update-change.php');
    require_once('ups-small-curl-class.php');
    require_once('ups_small_version_compact.php');

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    /**
     * UPS Small Activation Hook
     */
    register_activation_hook(__FILE__, 'eniture_create_ups_small_wh_db');
    register_activation_hook(__FILE__, 'eniture_create_ups_small_option');
    register_activation_hook(__FILE__, 'eniture_old_store_ups_sm_dropship_status');
    register_activation_hook(__FILE__, 'eniture_ups_small_activate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'eniture_ups_small_deactivate_hit_to_update_plan');
    register_activation_hook(__FILE__, 'eniture_create_ups_small_shipping_rules_db');
    register_deactivation_hook(__FILE__, 'eniture_ups_small_deactivate_plugin');

    /**
     * UPS Small Action And Filters
     */
    add_filter('woocommerce_shipping_methods', 'eniture_add_ups_small');
    add_filter('woocommerce_get_settings_pages', 'eniture_ups_small_shipping_sections');
    add_action('woocommerce_shipping_init', 'eniture_ups_small_init');
    add_filter('woocommerce_package_rates', 'eniture_ups_small_hide_shipping');
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_action('init', 'eniture_ups_small_no_method_available');
    add_action('init', 'eniture_ups_small_default_error_message_selection');
    add_action('init', 'eniture_create_ups_small_wh_db');

    /**
     * Update Default custom error message selection
     */
    function eniture_ups_small_default_error_message_selection()
    {
        $custom_error_selection = get_option('wc_pervent_proceed_checkout_eniture');
        if (empty($custom_error_selection)) {
            update_option('wc_pervent_proceed_checkout_eniture', 'prevent', true);
            update_option('prevent_proceed_checkout_eniture', 'There are no shipping methods available for the address provided. Please check the address.', true);
        }
    }
}

/**
 * ups small plugin update now
 */
function eniture_ups_small_update_now($upgrader_object, $options)
{
    $en_ups_small_path_name = plugin_basename(UPS_MAIN_FILE);
    $plugin_info = get_plugins();
    $plugin_version = (isset($plugin_info[$en_ups_small_path_name]['Version'])) ? $plugin_info[$en_ups_small_path_name]['Version'] : '';

    if (isset($options['action']) && $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
        foreach($options['plugins'] as $each_plugin) {
            if ($each_plugin == $en_ups_small_path_name) {
                if (!function_exists('eniture_ups_small_activate_hit_to_update_plan')) {
                    require_once(__DIR__ . '/update-plan.php');
                }
        
                if (function_exists('eniture_ups_small_activate_hit_to_update_plan') &&
                    function_exists('eniture_old_store_ups_sm_dropship_status') &&
                    function_exists('eniture_create_ups_small_wh_db') &&
                    function_exists('eniture_create_ups_small_option') && 
                    function_exists('eniture_create_ups_small_shipping_rules_db')) {
        
                    eniture_ups_small_activate_hit_to_update_plan();
                    eniture_old_store_ups_sm_dropship_status();           
                    eniture_create_ups_small_wh_db();
                    eniture_create_ups_small_option();
                    eniture_create_ups_small_shipping_rules_db();
                }
        
                update_option('en_ups_small_update_now', $plugin_version);
            }
        }
    }
}

add_action( 'upgrader_process_complete', 'eniture_ups_small_update_now', 10, 2);


define("eniture_woo_plugin_ups_small", "ups_small");

add_action('admin_enqueue_scripts', 'eniture_ups_small_frontend_checkout_script');

/**
 * Load Front-end scripts for ODFL
 */
function eniture_ups_small_frontend_checkout_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('eniture_ups_small_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-ups-small-checkout.js', array(), '1.0.0');
    wp_localize_script('eniture_ups_small_frontend_checkout_script', 'frontend_script', array(
        'pluginsUrl' => plugins_url(),
    ));
    wp_register_style('eniture_ups_small_wickedpicker_style', 'https://cdn.jsdelivr.net/npm/wickedpicker@0.4.3/dist/wickedpicker.min.css', false, '2.0.3');
    wp_enqueue_style('eniture_ups_small_wickedpicker_style');
    wp_register_script('eniture_ups_small_wickedpicker_style', plugin_dir_url(__FILE__) . '/js/wickedpicker.js', false, '2.0.3');
    wp_enqueue_script('eniture_ups_small_wickedpicker_style');
}

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
/**
 * Get Domain Name
 */
if (!function_exists('eniture_ups_small_get_domain')) {

    function eniture_ups_small_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return eniture_getHost($url);
    }
}

/**
 * Plans Common Hooks
 */
add_filter('eniture_ups_small_quotes_plans_suscription_and_features', 'eniture_ups_small_quotes_plans_suscription_and_features', 1);

function eniture_ups_small_quotes_plans_suscription_and_features($feature)
{
    $package = get_option('ups_small_package');
    $features = array
    (
        'instore_pickup_local_devlivery' => array('3'),
        'transit_days' => array('3'),
        'insurance_fee' => array('2', '3'),
        'contract_services' => array('2', '3'),
        'nested_material' => array('3'),
        'ups_small_cutOffTime_shipDateOffset' => array('2', '3'),
        'hazardous_material' => array('2', '3')
    );
    if (get_option('ups_small_quotes_store_type') == "1") {
        $features['multi_warehouse'] = array('2', '3');
        $features['multi_dropship'] = array('', '0', '1', '2', '3');
        $features['hazardous_material'] = array('2', '3');
        $features['contract_services'] = array('2', '3');
        $features['nested_material'] = array('3');
    } else {
        $dropship_status = get_option('en_old_user_dropship_status');
        $warehouse_status = get_option('en_old_user_warehouse_status');

        isset($dropship_status) && ($dropship_status == "0") ? $features['multi_dropship'] = array('', '0', '1', '2', '3') : '';
        isset($warehouse_status) && ($warehouse_status == "0") ? $features['multi_warehouse'] = array('2', '3') : '';
    }

    return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
}

add_filter('eniture_ups_small_plans_notification_link', 'eniture_ups_small_plans_notification_link', 1);

function eniture_ups_small_plans_notification_link($plans)
{
    $plan = current($plans);
    $plan_to_upgrade = "";
    switch ($plan) {
        case 1:
            $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-ups-small-package-plugin/'>Basic Plan required.</a>";
            break;
        case 2:
            $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-ups-small-package-plugin/' target='_blank'>Standard Plan required.</a>";
            break;
        case 3:
            $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-ups-small-package-plugin/' target='_blank'>Advanced Plan required.</a>";
            break;
    }

    return $plan_to_upgrade;
}

/**
 *
 * old customer check dropship / warehouse status on plugin update
 */
function eniture_old_store_ups_sm_dropship_status()
{
    global $wpdb;
//  Check total no. of dropships on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $num = $wpdb->get_var("select count(*) from ". $table_name ." where location = 'dropship' ");

    if (get_option('en_old_user_dropship_status') == "0" && get_option('ups_small_quotes_store_type') == "0") {
        $dropship_status = ($num > 1) ? 1 : 0;

        update_option('en_old_user_dropship_status', "$dropship_status");
    } elseif (get_option('en_old_user_dropship_status') == "" && get_option('ups_small_quotes_store_type') == "0") {
        $dropship_status = ($num == 1) ? 0 : 1;

        update_option('en_old_user_dropship_status', "$dropship_status");
    }

    $warehouse_num = $wpdb->get_var("select count(*) from ". $wpdb->prefix." warehouse where location = 'warehouse' ");

    if (get_option('en_old_user_warehouse_status') == "0" && get_option('ups_small_quotes_store_type') == "0") {
        $warehouse_status = ($warehouse_num > 1) ? 1 : 0;

        update_option('en_old_user_warehouse_status', "$warehouse_status");
    } elseif (get_option('en_old_user_warehouse_status') == "" && get_option('ups_small_quotes_store_type') == "0") {
        $warehouse_status = ($warehouse_num == 1) ? 0 : 1;

        update_option('en_old_user_warehouse_status', "$warehouse_status");
    }
}
// fdo va
add_action('wp_ajax_nopriv_ups_s_fd', 'eniture_ups_s_fd_api');
add_action('wp_ajax_ups_s_fd', 'eniture_ups_s_fd_api');
/**
 * UPS AJAX Request
 */
function eniture_ups_s_fd_api()
{
    $store_name = eniture_ups_small_get_domain();
    $company_id = isset($_POST['company_id']) ? sanitize_text_field(wp_unslash($_POST['company_id'])) : '';
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=ups_small&section=section-4',
    ];
    if (is_array($data) && count($data) > 0) {
        if(isset($_POST['disconnect']) && sanitize_text_field(wp_unslash($_POST['disconnect'])) != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if($_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo wp_json_encode(json_decode($response));
    exit();
}
add_action('rest_api_init', 'eniture_rest_api_init_status_ups_s');
function eniture_rest_api_init_status_ups_s()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'eniture_ups_s_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array $request
 * @return array|void
 */
function eniture_ups_s_fdo_data_status(WP_REST_Request $request)
{
    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

/**
 * Filter to check ground transit status
 */
if (!function_exists('eniture_ups_check_ground_transit_restrict_status')) {

    function eniture_ups_check_ground_transit_restrict_status($ground_transit_statuses)
    {
        $ground_restrict_value = (false !== get_option('restrict_days_transit_package_ups_small')) ? get_option('restrict_days_transit_package_ups_small') : '';
        if ('' !== $ground_restrict_value && strlen(trim($ground_restrict_value)) 
            && apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'transit_days')) {
            $ground_transit_statuses['ups'] = '1';
        }

        return $ground_transit_statuses;
    }

    add_filter('eniture_check_ground_transit_restrict_status', 'eniture_ups_check_ground_transit_restrict_status', 11, 1);
}
