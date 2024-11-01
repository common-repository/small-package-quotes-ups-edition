<?php

/**
 * UPS Small WooComerce | Class For Quote Settings Tab
 * @package     Woocommerce UPS Small Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UPS Small WooComerce | Class For Quote Settings Tab
 */
class Eniture_UPS_Small_Quote_Settings
{

    /**
     * Quote Setting Fields
     * @return Quote Setting Fields Array
     */
    function ups_small_quote_settings_tab()
    {
        $disable_transit = "";
        $disable_contract_service = "";
        $transit_package_required = "";

        $disable_hazardous = "";
        $hazardous_package_required = "";
        $contract_required_required = "";

        $action_transit = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'transit_days');
        if (is_array($action_transit)) {
            $disable_transit = "disabled_me";
            $transit_package_required = apply_filters('eniture_ups_small_plans_notification_link', $action_transit);
        }

        $action_hazardous = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'hazardous_material');
        if (is_array($action_hazardous)) {
            $disable_hazardous = "disabled_me";
            $hazardous_package_required = apply_filters('eniture_ups_small_plans_notification_link', $action_hazardous);
        }

        $action_contract = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'contract_services');
        if (is_array($action_contract)) {
            $disable_contract_service = "disabled_me";
            $contract_required_required = apply_filters('eniture_ups_small_plans_notification_link', $action_contract);
        }

        //**Plan_Validation: Cut Off Time & Ship Date Offset
        $disable_ups_small_cutOffTime_shipDateOffset = "";
        $ups_small_cutOffTime_shipDateOffset_package_required = "";
        $action_ups_small_cutOffTime_shipDateOffset = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'ups_small_cutOffTime_shipDateOffset');
        if (is_array($action_ups_small_cutOffTime_shipDateOffset)) {
            $disable_ups_small_cutOffTime_shipDateOffset = "disabled_me";
            $ups_small_cutOffTime_shipDateOffset_package_required = apply_filters('eniture_ups_small_plans_notification_link', $action_ups_small_cutOffTime_shipDateOffset);
        }

        $package_type_options = [
            'ship_alone' => __('Quote each item as shipping as its own package', 'small-package-quotes-ups-edition'),
            'ship_combine_and_alone' => __('Combine the weight of all items without dimensions and quote them as one package while quoting each item with dimensions as shipping as its own package', 'small-package-quotes-ups-edition'),
            'ship_one_package_70' => __('Quote shipping as if all items ship as one package up to 70 LB each', 'small-package-quotes-ups-edition'),
            'ship_one_package_150' => __('Quote shipping as if all items ship as one package up to 150 LB each', 'small-package-quotes-ups-edition'),
        ];
        $package_type_default = 'ship_alone';
        $ups_small_packaging_type = get_option("ups_small_packaging_type");
        if(!empty($ups_small_packaging_type) && $ups_small_packaging_type == 'old'){
            $package_type_default = 'eniture_packaging';
            $package_type_options['eniture_packaging'] = __('Use the default Eniture packaging algorithm', 'small-package-quotes-ups-edition');
        }

        $rate_source = get_option('ups_small_rate_source');
        if (empty($rate_source) || $rate_source == false) {
            $rate_source = 'negotiated_rates';
            update_option('ups_small_rate_source', $rate_source);
        }

        $hazardous_fee_option = get_option('ups_small_hazardous_fee_options');
        if (empty($hazardous_fee_option) || !$hazardous_fee_option) {
            $hazardous_fee_option = 'ship_as_own_package';
            update_option('ups_small_hazardous_fee_options', $hazardous_fee_option);
        }

        //**End: Cut Off Time & Ship Date Offset

        echo '<div class="ups_small_quote_section">';

        $settings = array(
            'ups_small_services' => array(
                'name' => __('Quote Service Options ', 'small-package-quotes-ups-edition'),
                'type' => 'title',
                'desc' => '',
                'id' => 'ups_small_quote_hdng'
            ),
            'ups_small_services1' => array(
                'name' => '',
                'type' => 'title',
                'desc' => '<p>The services selected will display in the cart if they are available for the origin and destination addresses, and if the UPS Small Package Quotes API has been enabled for the corresponding shipping zone.</p>',
                'id' => 'ups_small_quote_hdng1'
            ),
            'ups_small_domastic_srvcs' => array(
                'name' => __('Domestic Services', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'id' => 'ups_small_dom_srvc_hdng',
                'class' => 'dom_int_srvc_hdng'
            ),
            'ups_small_int_srvcs' => array(
                'name' => __('International Services', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'id' => 'ups_small_int_srvc_hdng',
                'class' => 'dom_int_srvc_hdng'
            ),
            'ups_small_select_all_services' => array(
                'name' => __('Select All', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'id' => 'wc_settings_select_all_',
                'class' => 'ups_small_all_services',
            ),
            'ups_small_select_all_int_services' => array(
                'name' => __('Select All', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'id' => 'wc_settings_select_int_all',
                'class' => 'ups_small_all_int_services',
            ),
            'ups_small_ground' => array(
                'name' => __('UPS Ground', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Ground',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_standard' => array(
                'name' => __('UPS Standard', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Standard',
                'class' => 'ups_small_int_quotes_services ups_international_service',
            ),
            'ups_small_ground_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_ground_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_standard_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_standard_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_ground_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_ground_markup',
                'class' => 'ups_small_quotes_markup_left_markup',
            ),
            'ups_small_standard_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_standard_markup',
                'class' => 'ups_small_quotes_markup_right_markup',
            ),
            'ups_small_2day_air' => array(
                'name' => __('UPS 2nd Day Air', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_2nd_Day_Air',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_worldwide_expedited' => array(
                'name' => __('UPS  Expedited | UPS Worldwide Expedited', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Worldwide_Expedited',
                'class' => 'ups_small_int_quotes_services ups_international_service',
            ),
            'ups_small_2day_air_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_2day_air_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_pkg_Worldwide_Expedited_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_pkg_Worldwide_Expedited_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_2day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_2day_air_markup',
                'class' => 'ups_small_quotes_markup_left_markup',
            ),
            'ups_small_pkg_Worldwide_Expedited_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_pkg_Worldwide_Expedited_markup',
                'class' => 'ups_small_quotes_markup_right_markup',
            ),
            'ups_small_2day_air_am' => array(
                'name' => __('UPS 2nd Day Air A.M', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_2nd_Day_Air_AM',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_worldwide_saver' => array(
                'name' => __('UPS Express Saver | UPS Worldwide Saver', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Saver',
                'class' => 'ups_small_int_quotes_services ups_international_service',
            ),
            'ups_small_2day_air_am_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_2day_air_am_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_worldwide_saver_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_worldwide_saver_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_2day_air_am_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_2day_air_am_markup',
                'class' => 'ups_small_quotes_markup_left_markup',
            ),
            'ups_small_worldwide_saver_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_worldwide_saver_markup',
                'class' => 'ups_small_quotes_markup_right_markup',
            ),
            'ups_small_next_day_air_saver' => array(
                'name' => __('UPS Next Day Air Saver', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Next_Day_Air_Saver',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_worldwide_express' => array(
                'name' => __('UPS Express | UPS Worldwide Express', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Worldwide_Express',
                'class' => 'ups_small_int_quotes_services ups_international_service',
            ),
            'ups_small_next_day_air_saver_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_next_day_air_saver_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_worldwide_express_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_worldwide_express_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_next_day_air_saver_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_next_day_air_saver_markup',
                'class' => 'ups_small_quotes_markup_left_markup',
            ),
            'ups_small_worldwide_express_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_worldwide_express_markup',
                'class' => 'ups_small_quotes_markup_right_markup',
            ),
            'ups_small_next_day_air' => array(
                'name' => __('UPS Next Day Air', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Next_Day_Air',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_worldwide_express_plus' => array(
                'name' => __('UPS Express Plus | UPS Worldwide Express Plus', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Worldwide_Express_Plus',
                'class' => 'ups_small_int_quotes_services ups_international_service',
            ),
            'ups_small_next_day_air_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_next_day_air_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_worldwide_express_plus_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_worldwide_express_plus_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_next_day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_next_day_air_markup',
                'class' => 'ups_small_quotes_markup_left_markup',
            ),
            'ups_small_worldwide_express_plus_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_worldwide_express_plus_markup',
                'class' => 'ups_small_quotes_markup_right_markup',
            ),
            'ups_small_next_day_air_early' => array(
                'name' => __('UPS Next Day Air Early', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_Next_Day_Air_Early_AM',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_next_day_air_early_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_small_next_day_air_early_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_next_day_air_early_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_next_day_air_early_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_small_next_day_air_early_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_next_day_air_early_markup',
                'class' => 'ups_small_quotes_markup_left_markup',
            ),
            'ups_small_next_day_air_early_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_small_3day_select_duplicated' => array(
                'name' => __('UPS 3 Day Select', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_small_pkg_3_Day_Select',
                'class' => 'ups_small_quotes_services',
            ),
            'ups_small_3day_select_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_small_3day_select_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_3day_select_label',
                'class' => 'ups_small_quotes_label_left',
            ),
            'ups_small_3day_select_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_small_3day_select_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_small_3day_select_markup',
                'class' => 'ups_small_quotes_markup_right_markup',
            ),
            'ups_small_3day_select_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_contract_services' => array(
                'name' => __('UPS Contract Services', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $contract_required_required,
                'id' => 'ups_contract_services'
            ),
            'ups_contract_services_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'to_have_access_to_UPS_contract_services' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden',
                'desc' => '<p>To have access to UPS contract services, they must be enabled on your account.</p>',
                'id' => 'to_have_access_to_UPS_contract_services'
            ),
            'ups_surepost_less_than_1_lb' => array(
                'name' => __('UPS SurePost Less than 1LB', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_surepost_less_than_1_lb',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_surepost_less_than_1_lb_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_less_than_1_lb_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_less_than_1_lb_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_surepost_less_than_1_lb_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_contract_services_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_contract_services_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_contract_services_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_1_lb_or_greater' => array(
                'name' => __('UPS SurePost 1LB or greater ', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_surepost_1_lb_or_greater',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_surepost_1_lb_or_greater_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_1_lb_or_greater_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_1_lb_or_greater_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_surepost_1_lb_or_greater_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_1_lb_or_greater_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_1_lb_or_greater_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_surepost_1_lb_or_greater_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_bound_printed_matter' => array(
                'name' => __('UPS SurePost Bound Printed Matter ', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_surepost_bound_printed_matter',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_surepost_bound_printed_matter_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_bound_printed_matter_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_bound_printed_matter_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_surepost_bound_printed_matter_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_bound_printed_matter_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_bound_printed_matter_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_surepost_bound_printed_matter_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_media_mail' => array(
                'name' => __('UPS SurePost Media Mail', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_surepost_media_mail',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_surepost_media_mail_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_media_mail_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_media_mail_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_surepost_media_mail_markup_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_surepost_media_mail_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_surepost_media_mail_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_surepost_media_mail_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_ground_with_freight_pricing' => array(
                'name' => __('UPS Ground with Freight Pricing', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_ground_with_freight_pricing',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_ground_with_freight_pricing_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_ground_with_freight_pricing_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_ground_with_freight_pricing_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_ground_with_freight_pricing_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_ground_with_freight_pricing_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_ground_with_freight_pricing_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_ground_with_freight_pricing_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),

            // USP simple rate start
            'ups_simple_rate_through_ground' => array(
                'name' => __('UPS Simple Rate - Ground', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_simple_rate_through_ground',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_ground_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_ground_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_ground_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_ground_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_ground_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_ground_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_ground_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
             'ups_simple_rate_through_3_day_select' => array(
                'name' => __('UPS Simple Rate - 3-day Select', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_simple_rate_through_3_day_select',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_3_day_select_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_3_day_select_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_3_day_select_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_3_day_select_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_3_day_select_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_3_day_select_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_3_day_select_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_2nd_day_air' => array(
                'name' => __('UPS Simple Rate - 2-day Air', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_simple_rate_through_2nd_day_air',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_2nd_day_air_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_2nd_day_air_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_2nd_day_air_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_2nd_day_air_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_2nd_day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_2nd_day_air_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_2nd_day_air_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_next_day_air_saver' => array(
                'name' => __('UPS Simple Rate - Next Day Air Saver', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'ups_simple_rate_through_next_day_air_saver',
                'class' => 'ups_small_quotes_services ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_next_day_air_saver_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_next_day_air_saver_label' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Label',
                'desc' => __('Leave blank to display service name.', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_next_day_air_saver_label',
                'class' => 'ups_small_quotes_label_left ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_next_day_air_saver_label_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
            'ups_simple_rate_through_next_day_air_saver_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'small-package-quotes-ups-edition'),
                'id' => 'ups_simple_rate_through_next_day_air_saver_markup',
                'class' => 'ups_small_quotes_markup_left_markup ' . $disable_contract_service,
            ),
            'ups_simple_rate_through_next_day_air_saver_markup_after' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden ups_small_services_hide_me',
            ),
          
            // USP simple rate end

            'price_sort_ups_small' => array(
                'name' => __("Don't sort shipping methods by price  ", 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => 'By default, the plugin will sort all shipping methods by price in ascending order.',
                'id' => 'shipping_methods_do_not_sort_by_price'
            ),

            // Package rating method when Standard Box Sizes isn't in use
            'ups_small_packaging_method_label' => array(
                'name' => __('Package rating method when Standard Box Sizes isn\'t in use', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'id' => 'ups_small_packaging_method_label'
            ),
            'ups_small_packaging_method' => array(
                'name' => '',
                'type' => 'radio',
                'default' => $package_type_default,
                'options' => $package_type_options,
                'id' => 'ups_small_packaging_method',
            ),

//          show delivery estimates options
            'service_ups_small_estimates_title' => array(
                'name' => __('Delivery Estimate Options ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => '',
                'id' => 'service_ups_small_estimates_title'
            ),
            'dont_show_estimates_ups_small' => array(
                'name' => '',
                'type' => 'radio',
                'class' => "",
                'default' => "dont_show_estimates",
                'options' => array(
                    'dont_show_estimates' => __("Don't display delivery estimates.", 'small-package-quotes-ups-edition'),
                    'delivery_days' => __('Display estimated number of days until delivery.', 'small-package-quotes-ups-edition'),
                    'delivery_date' => __('Display estimated delivery date.', 'small-package-quotes-ups-edition'),
                ),
                'id' => 'ups_small_delivery_estimates',
            ),
            'estimated_delivery_days_format' => array(
                'name' => '',
                'type' => 'text',
                'id' => 'estimated_delivery_days_format',
                'desc' => 'It will be used in place of the default label which is "Intransit days" e.g. Delivery days',
            ),
            'estimated_delivery_date_format' => array(
                'name' => '',
                'type' => 'text',
                'id' => 'estimated_delivery_date_format',
                'desc' => 'The date format should be valid and place it between the % symbols. The string before the % symbol will be used in place of the default label which is "Estimated delivery date". e.g. ETA %m-d-y% will display "ETA 04-02-24" and ETA %M D Y% will display "Apr Tue 2024". The default format for the estimated delivery date is "Expected delivery by dd-mm-yyyy".',
            ),
            //**Start: Cut Off Time & Ship Date Offset
            'ups_small_cutOffTime_shipDateOffset_ups_small' => array(
                'name' => __('Cut Off Time & Ship Date Offset ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $ups_small_cutOffTime_shipDateOffset_package_required,
                'id' => 'ups_small_cutOffTime_shipDateOffset'
            ),
            'orderCutoffTime_ups_small' => array(
                'name' => __('Order Cut Off Time ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'placeholder' => '--:-- --',
                'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                'id' => 'ups_small_orderCutoffTime',
                'class' => $disable_ups_small_cutOffTime_shipDateOffset,
            ),
            'shipmentOffsetDays_ups_small' => array(
                'name' => __('Fulfilment Offset Days ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                'placeholder' => 'Fulfilment Offset Days, e.g. 2',
                'id' => 'ups_small_shipmentOffsetDays',
                'class' => $disable_ups_small_cutOffTime_shipDateOffset,
            ),
            'all_shipment_days_ups_small' => array(
                'name' => __("What days do you ship orders?", 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => 'Select All',
                'class' => "all_shipment_days_ups_small $disable_ups_small_cutOffTime_shipDateOffset",
                'id' => 'all_shipment_days_ups_small'
            ),
            'monday_shipment_day_ups_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Monday',
                'class' => "ups_small_shipment_day $disable_ups_small_cutOffTime_shipDateOffset",
                'id' => 'monday_shipment_day_ups_small'
            ),
            'tuesday_shipment_day_ups_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Tuesday',
                'class' => "ups_small_shipment_day $disable_ups_small_cutOffTime_shipDateOffset",
                'id' => 'tuesday_shipment_day_ups_small'
            ),
            'wednesday_shipment_day_ups_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Wednesday',
                'class' => "ups_small_shipment_day $disable_ups_small_cutOffTime_shipDateOffset",
                'id' => 'wednesday_shipment_day_ups_small'
            ),
            'thursday_shipment_day_ups_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Thursday',
                'class' => "ups_small_shipment_day $disable_ups_small_cutOffTime_shipDateOffset",
                'id' => 'thursday_shipment_day_ups_small'
            ),
            'friday_shipment_day_ups_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Friday',
                'class' => "ups_small_shipment_day $disable_ups_small_cutOffTime_shipDateOffset",
                'id' => 'friday_shipment_day_ups_small'
            ),
            // Start Transit days            
            'ups_ground_transit_label' => array(
                'name' => __('Ground transit time restriction', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $transit_package_required,
                'id' => 'ups_ground_transit_label'
            ),
            'restrict_days_transit_package_ups_small' => array(
                'name' => __('Enter the number of transit days to restrict ground service to. Leave blank to disable this feature.', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => $disable_transit,
                'id' => 'restrict_days_transit_package_ups_small'
            ),
            'restrict_radio_btn_transit_ups_small' => array(
                'name' => '',
                'type' => 'radio',
                'class' => $disable_transit,
                'options' => array(
                    'TransitTimeInDays' => __('Restrict by the carrier\'s in transit days metric.', 'small-package-quotes-ups-edition'),
                    'CalenderDaysInTransit' => __('Restrict by the calendar days in transit.', 'small-package-quotes-ups-edition'),
                ),
                'id' => 'restrict_calendar_transit_small_packages_ups',
            ),
            /*
             * UPS Residentail Delivery, Handeling Fee And Hazardous Fee
             */
            'ups_small_3day_select' => array(
                'name' => '',
                'type' => 'title',
                'class' => 'hidden',
            ),
            'residential_delivery_options_label' => array(
                'name' => __('Residential Delivery', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'residential_delivery_options_label'
            ),
            'ups_small_residential_delivery' => array(
                'name' => __('Always quote as residential delivery', 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'id' => 'ups_small_quote_as_residential_delivery'
            ),
//              Auto-detect residential addresses notification
            'avaibility_auto_residential' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Auto-detect residential addresses module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                'id' => 'avaibility_auto_residential'
            ),
//              Use my standard box sizes notification
            'avaibility_box_sizing' => array(
                'name' => __('Use my standard box sizes', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/'>here</a> to add the Standard Box Sizes module. (<a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/#documentation'>Learn more</a>)",
                'id' => 'avaibility_box_sizing'
            ),
            // End Transit days 
            // start hazardous section
            'ups_hazardous_material_settings' => array(
                'name' => __('Hazardous material settings', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $hazardous_package_required,
                'id' => 'ups_hazardous_material_settings'
            ),
            'ups_small_hazardous_materials_shipments' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Only quote ground service for hazardous materials shipments',
                'class' => $disable_hazardous,
                'id' => 'ups_small_hazardous_materials_shipments'
            ),
            'ups_small_ground_hz_fee' => array(
                'name' => __('Ground Hazardous Material Fee ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Enter an amount, e.g 20. or Leave blank to disable.</span>',
                'class' => $disable_hazardous,
                'id' => 'ups_small_ground_hazardous_fee'
            ),
            'ups_small_air_hz_fee' => array(
                'name' => __('Air Hazardous Material Fee ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Enter an amount, e.g 20. or Leave blank to disable.</span>',
                'class' => $disable_hazardous,
                'id' => 'ups_small_air_hazardous_fee'
            ),
            // Package level hazardous fee options
            'ups_small_hazardous_fee_options' => array(
                'name' => '',
                'type' => 'radio',
                'default' => $hazardous_fee_option,
                'options' => array(
                    'ship_as_own_package' => __('Quote each item marked as hazmat as shipping as its own package.', 'small-package-quotes-ups-edition'),
                    'combine_quantities' => __('Combine quantities of the same item marked as hazmat into a single package before applying the hazmat fee.', 'small-package-quotes-ups-edition'),
                ),
                'id' => 'ups_small_hazardous_fee_options'
            ),
            // end hazardous section
            'ups_small_hand_free' => array(
                'name' => __('Handling Fee / Markup ', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.</span>',
                'id' => 'ups_small_hand_fee_mark_up'
            ),
            'ups_small_enable_logs' => array(
                'name' => __("Enable Logs  ", 'small-package-quotes-ups-edition'),
                'type' => 'checkbox',
                'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                'id' => 'ups_small_enable_logs'
            ),
            // rate source section
            'ups_small_rate_source' => array(
                'name' => __('Rate source ', 'small-package-quotes-ups-edition'),
                'type' => 'radio',
                'default' => $rate_source,
                'options' => array(
                    'negotiated_rates' =>  __('Use my negotiated rates.', 'small-package-quotes-ups-edition'),
                    'list_rates' =>  __('Use retail (list) rates.', 'small-package-quotes-ups-edition'),
                ),
                'id' => 'ups_small_rate_source'
            ),
            //Ignore items with the following Shipping Class(es) By (K)
            'en_ignore_items_through_freight_classification' => array(
                'name' => __('Ignore items with the following Shipping Class(es)', 'small-package-quotes-ups-edition'),
                'type' => 'text',
                'desc' => "Enter the <a target='_blank' href = '" . get_admin_url() . "admin.php?page=wc-settings&tab=shipping&section=classes'>Shipping Slug</a> you'd like the plugin to ignore. Use commas to separate multiple Shipping Slug.",
                'id' => 'en_ignore_items_through_freight_classification'
            ),
            'allow_other_plugins_ups_small' => array(
                'name' => __('Allow other plugins to show quotes ', 'small-package-quotes-ups-edition'),
                'type' => 'select',
                'default' => '3',
                'desc' => '',
                'id' => 'wc_settings_wwe_allow_other_plugins',
                'options' => array(
                    'no' => __('NO', 'small-package-quotes-ups-edition'),
                    'yes' => __('YES', 'small-package-quotes-ups-edition')
                )
            ),
            'unable_retrieve_shipping_clear_ups_small' => array(
                'title' => '',
                'name' => '',
                'desc' => '',
                'id' => 'wc_unable_retrieve_shipping_clear_ups_small',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            'unable_retrieve_shipping_ups_small' => array(
                'name' => __('Checkout options if the plugin fails to return a rate ', 'small-package-quotes-ups-edition'),
                'type' => 'title',
                'desc' => 'When the plugin is unable to retrieve shipping quotes and no other shipping options are provided by an alternative source:',
                'id' => 'wc_settings_unable_retrieve_shipping_ups_small'
            ),
            'pervent_checkout_proceed_ups_small' => array(
                'name' => '',
                'type' => 'radio',
                'id' => 'pervent_checkout_proceed_wwe_small_packages',
                'options' => array(
                    'allow' => '',
                    'prevent' => '',
                ),
                'id' => 'wc_pervent_proceed_checkout_eniture',
            ),
            'section_end_quote' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_quote_section_end'
            )
        );
        return $settings;
    }

}
