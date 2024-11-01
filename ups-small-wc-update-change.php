<?php
/**
 * UPS Small WooComerce |  Class for new and old functions
 * @package     Woo-commerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
* Eniture UPS Small WooComerce | Class for new and old functions
*/
class Eniture_UPS_Small_Woo_Update_Changes 
{
    /**
     * WooCommerce Version Number
     * @var int
     */
    public $WooVersion;

    /**
     * Update Changes Constructor
     */
    function __construct() 
    {
        if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugin_folder    = get_plugins('/' . 'woocommerce');
        $plugin_file      = 'woocommerce.php';
        $this->WooVersion = $plugin_folder[$plugin_file]['Version'];

    }

    /**
     * WooCommerce Update Changes For Postcode
     * @return int
     */
    function ups_small_postcode()
    { 
        $sPostCode = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sPostCode = WC()->customer->get_postcode();
                break;
            case ($this->WooVersion >= '3.0'):
                $sPostCode = WC()->customer->get_billing_postcode();
                break;

            default:
                break;
        }
        return $sPostCode;
    }
    /**
     * WooCommerce Update Changes For State
     * @return int
     */
    function ups_small_getState()
    { 
        $sState = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sState = WC()->customer->get_state();
                break;
            case ($this->WooVersion >= '3.0'):
                $sState = WC()->customer->get_billing_state();
                break;

            default:
                break;
        }
        return $sState;
    }

    /**
     * WooCommerce Update Changes For City
     * @return int
     */
    function ups_small_getCity()
    { 
        $sCity = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sCity = WC()->customer->get_city();
                break;
            case ($this->WooVersion >= '3.0'):
                $sCity = WC()->customer->get_billing_city();
                break;

            default:
                break;
        }
        return $sCity;
    }

    /**
     * WooCommerce Update Changes For City
     * @return int
     */
    function ups_small_getCountry()
    { 
        $sCountry = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sCountry = WC()->customer->get_country();
                break;
            case ($this->WooVersion >= '3.0'):
                $sCountry = WC()->customer->get_billing_country();
                break;

            default:
                break;
        }
        return $sCountry;
    }

}