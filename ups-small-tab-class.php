<?php

/**
 * UPS Small WooComerce |  Setting Tab Class
 * @package     Woo-commerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Eniture UPS Small WooComerce |  Setting Tab Class
 */
class Eniture_WC_Settings_UPS_Small extends WC_Settings_Page
{

    /**
     * Setting Tabs constructor
     */
    public function __construct()
    {
        $this->id = eniture_woo_plugin_ups_small;
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    /**
     * UPS Small Setting Tab For Woo-commerce
     * @param $settings_tabs
     * @return array
     */
    public function add_settings_tab($settings_tabs)
    {
        $settings_tabs[$this->id] = __('UPS', 'small-package-quotes-ups-edition');
        return $settings_tabs;
    }

    /**
     * UPS Small Setting Sections
     * @return array
     */
    public function get_sections()
    {

        $sections = array(
            '' => __('Connection Settings', 'small-package-quotes-ups-edition'),
            'section-1' => __('Quote Settings', 'small-package-quotes-ups-edition'),
            'section-2' => __('Warehouses', 'small-package-quotes-ups-edition'),
            'shipping-rules' => __('Shipping Rules', 'small-package-quotes-ups-edition'),
            // fdo va
            'section-4' => __('FreightDesk Online', 'small-package-quotes-ups-edition'),
            'section-5' => __('Validate Addresses', 'small-package-quotes-ups-edition'),
            'section-3' => __('User Guide', 'small-package-quotes-ups-edition'),
        );

        // Logs data
        $enable_logs = get_option('ups_small_enable_logs');
        if ($enable_logs == 'yes') {
            $sections['en-logs'] = 'Logs';
        } 

        $sections = apply_filters('en_woo_addons_sections', $sections, eniture_woo_plugin_ups_small);

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * UPS Small Warehouse Tab
     */
    public function ups_small_warehouse()
    {
        require_once 'warehouse-dropship/wild/warehouse/warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/dropship_template.php';
    }

    /**
     * UPS Small User Guide Tab
     */
    public function ups_user_guide()
    {
        include_once('template/guide.php');
    }

    /**
     * UPS Small Get Settings Pages
     * @param $section
     * @return array
     */
    public function get_settings($section = null)
    {
        ob_start();
        switch ($section) {
            case 'section-0' :
                $settings = (new Eniture_UPS_Small_Connection_Settings())->ups_small_con_setting();
                break;
            case 'section-1':
                $ups_small_qsettings = new Eniture_UPS_Small_Quote_Settings();
                $settings = $ups_small_qsettings->ups_small_quote_settings_tab();
                break;
            case 'section-2' :
                $this->ups_small_warehouse();
                $settings = array();
                break;
            case 'section-3' :
                $this->ups_user_guide();
                $settings = array();
                break;
            // fdo va
            case 'section-4' :
                $this->freightdesk_online_section();
                $settings = [];
                break;

            case 'section-5' :
                $this->validate_addresses_section();
                $settings = [];
                break;

            case 'shipping-rules' :
                include_once('shipping-rules/shipping-rules-template.php');
                $settings = [];
                break;

            case 'en-logs' :
                require_once 'logs/en-logs.php';
                $settings = [];
                break;

            default:
                $ups_small_con_settings = new Eniture_UPS_Small_Connection_Settings();
                $settings = $ups_small_con_settings->ups_small_con_setting();
                break;
        }
        $settings = apply_filters('en_woo_addons_settings', $settings, $section, eniture_woo_plugin_ups_small);
        $settings = $this->avaibility_addon($settings);
        return apply_filters('woocommerce-settings-ups-small', $settings, $section);
    }

    /**
     * @param array type $settings
     * @return array type
     */
    function avaibility_addon($settings)
    {
        if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
            unset($settings['avaibility_auto_residential']);
        }
        if (is_plugin_active('standard-box-sizes/en-standard-box-sizes.php') || is_plugin_active('standard-box-sizes/standard-box-sizes.php')) {
            unset($settings['avaibility_box_sizing']);
        }

        return $settings;
    }

    /**
     * UPS Small settings output
     * @global $current_section
     */
    public function output()
    {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * UPS Small Save Settings
     * @global $current_section
     */
    public function save()
    {
        global $current_section;
        $settings = $this->get_settings($current_section);
        if (isset($_POST['ups_small_orderCutoffTime']) && sanitize_text_field(wp_unslash($_POST['ups_small_orderCutoffTime'])) != '') {
            $time24Formate = $this->getTimeIn24Hours(sanitize_text_field(wp_unslash( $_POST['ups_small_orderCutoffTime'] )));
            $_POST['ups_small_orderCutoffTime'] = $time24Formate;
        }
        WC_Admin_Settings::save_fields($settings);
    }

    /**
     * @param $timeStr
     * @return false|string
     */
    public function getTimeIn24Hours($timeStr)
    {
        $cutOffTime = explode(' ', $timeStr);
        $hours = $cutOffTime[0];
        $separator = $cutOffTime[1];
        $minutes = $cutOffTime[2];
        $meridiem = $cutOffTime[3];
        $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
        return gmdate("H:i", strtotime($cutOffTime));
    }
    // fdo va
    /**
     * FreightDesk Online section
     */
    public function freightdesk_online_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/freightdesk-online-section.php';
    }

    /**
     * Validate Addresses Section
     */
    public function validate_addresses_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/validate-addresses-section.php';
    }

}

return new Eniture_WC_Settings_UPS_Small();
