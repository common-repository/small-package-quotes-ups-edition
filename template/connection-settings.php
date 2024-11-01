<?php
/**
 * UPS Small WooComerce |  Connection Settings Tab Class
 * @package     Woocommerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UPS Small WooComerce |  Connection Settings Tab Class
 */
class Eniture_UPS_Small_Connection_Settings
{
    /**
     * Connection Settings Fields
     * @return Connection Settings Array
     */
    public function ups_small_con_setting()
    {
        $default_api = empty(get_option('ups_small_username')) ? 'ups_new_api' : 'ups_old_api';
        
        echo '<div class="ups_small_connection_section">';
        $settings = array(
            'section_title_ups_small' => array(
                'name' => '',
                'type' => 'title',
                'id' => 'ups_small_connection_title',
            ),

            'ups_api_endpoint' => array(
                'name' => __('Which API will you connect to? ', 'small-package-quotes-ups-edition'),
                'type' => 'select',
                'default' => $default_api,
                'id' => 'ups_api_endpoint',
                'options' => array(
                    'ups_old_api' => __('Legacy API', 'small-package-quotes-ups-edition'),
                    'ups_new_api' => __('New API', 'small-package-quotes-ups-edition')
                )
            ),

            'acc_number_ups_small' => array(
                'name' => __('Account Number ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_small_account_number'
            ),

            // New API
            'ups_client_id' => array(
                'name' => __('Client ID ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_client_id',
                'class' => 'ups_new_api_field'
            ),

            'ups_client_secret' => array(
                'name' => __('Client Secret ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_client_secret',
                'class' => 'ups_new_api_field'
            ),
            'ups_new_api_username' => array(
                'name' => __('Username ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_new_api_username',
                'class' => 'ups_new_api_field'
            ),
            'ups_new_api_password' => array(
                'name' => __('Password ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_new_api_password',
                'class' => 'ups_new_api_field'
            ),

            // Old API
            'username_ups_small' => array(
                'name' => __('Username ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_small_username',
                'class' => 'ups_old_api_field'
            ),

            'password_ups_small' => array(
                'name' => __('Password ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_small_password',
                'class' => 'ups_old_api_field'
            ),

            'auth_key_ups_small' => array(
                'name' => __('API Access Key ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_small_api_access_key',
                'class' => 'ups_old_api_field'
            ),

            'licence_key_ups_small' => array(
                'name' => __('Eniture API Key ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => __('Obtain a Eniture API Key from <a href="https://eniture.com/woocommerce-ups-small-package-plugin/" target="_blank" >eniture.com </a>', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_licence_key'
            ),

            'section_end_ups_small' => array(
                'type' => 'sectionend',
                'id' => 'ups_small_licence_key'
            ),
        );
        return $settings;
    }
}
