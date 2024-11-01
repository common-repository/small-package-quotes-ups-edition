jQuery(document).ready(function () {

    jQuery(".ups_small_quotes_markup_left_markup, .ups_small_quotes_markup_right_markup, .ups_small_quotes_label_left").focus(function (e) {
        jQuery("#" + this.id).css({'border-color': '#ddd'});
    });
    jQuery("#order_shipping_line_items .shipping .display_meta").css('display', 'none');

    jQuery("#wc_settings_wwe_allow_other_plugins").closest('tr').addClass("wc_settings_wwe_allow_other_plugins");

    jQuery(".ups_small_services_hide_me").closest('tr').addClass("ups_small_services_hide_me");
    jQuery("#to_have_access_to_UPS_contract_services").closest('tr').addClass("to_have_access_to_UPS_contract_services");

    jQuery("#restrict_days_transit_package_ups_small").closest('tr').addClass("restrict_days_transit_package_ups_small");
    jQuery("#ups_ground_transit_label").closest('tr').addClass("ups_ground_transit_label");
    jQuery("#ups_small_ground_hazardous_fee").closest('tr').addClass("ups_small_ground_hazardous_fee");
    jQuery("#ups_small_air_hazardous_fee").closest('tr').addClass("ups_small_air_hazardous_fee");
    jQuery("input[name*='ups_small_hazardous_materials_shipments']").closest('tr').addClass('ups_small_hazardous_materials_shipments');
    jQuery("input[name*='restrict_calendar_transit_small_packages_ups']").closest('tr').addClass('restrict_calendar_transit_small_packages_ups');
    jQuery("input[name*='ups_hazardous_material_settings']").closest('tr').addClass('ups_hazardous_material_settings');
    jQuery(".ups_contract_services_empty_label").closest('tr').addClass('ups_contract_services_empty_tr');
    // jQuery("input[name*='ups_contract_services']").closest('tr').addClass('ups_ground_transit_label');
    jQuery("input[name*='ups_contract_services']").closest('tr').addClass('ups_contract_services_tr');
    jQuery("#ups_contract_services_markup_label_empty").addClass('ups_contract_services_hidden');
    jQuery("#ups_small_hand_fee_mark_up , .ups_small_quotes_markup_left_markup , .ups_small_quotes_markup_right_markup , #ups_small_air_hazardous_fee, #ups_small_ground_hazardous_fee").attr('maxlength', 7);
    jQuery("#instore-pickup-desc").attr('maxlength', 25);
    jQuery("#local-delivery-desc").attr('maxlength', 25);

    jQuery("#ups_contract_services_markup_label_empty").closest('tr').addClass("ups_small_quotes_right_label_hidden");
    jQuery("#ups_surepost_1_lb_or_greater_markup_label_empty").closest('tr').addClass("ups_small_quotes_right_label_hidden");
    jQuery("#ups_surepost_bound_printed_matter_markup_label_empty").closest('tr').addClass("ups_small_quotes_right_label_hidden");
    jQuery("#ups_surepost_media_mail_markup_label_empty").closest('tr').addClass("ups_small_quotes_right_label_hidden");
    jQuery("#ups_ground_with_freight_pricing_markup_label_empty").closest('tr').addClass("ups_small_quotes_right_label_hidden");


    jQuery("#ups_small_quote_as_residential_delivery").closest('tr').addClass("ups_small_quote_as_residential_delivery");
    jQuery("#residential_delivery_options_label").closest('tr').addClass("residential_delivery_options_label");
    jQuery("#avaibility_auto_residential").closest('tr').addClass("avaibility_auto_residential");
    jQuery("#ups_contract_services1").closest('td').addClass("ups_contract_services1_td");
    jQuery(".ups_small_quotes_markup_right_markup").closest('tr').addClass("ups_small_quotes_markup_right_markup_tr");
    jQuery("#shipping_methods_do_not_sort_by_price").closest('tr').addClass("shipping_methods_do_not_sort_by_price_tr");
    jQuery("#ups_contract_services").closest('tr').addClass("ups_contract_services_heading");
    jQuery("#ups_small_hand_fee_mark_up").closest('tr').addClass("ups_small_hand_fee_mark_up_tr");

    //          estimated delivery options
    jQuery(".ups_small_dont_show_estimate_option").closest('tr').addClass("ups_small_dont_show_estimate_option_tr");
    jQuery("#service_small_estimates_title").closest('tr').addClass("service_small_estimates_title_tr");
    jQuery("input[name=ups_small_delivery_estimates]").closest('tr').addClass("ups_small_delivery_estimates_tr");
    jQuery("#service_ups_small_estimates_title").closest('tr').addClass("service_ups_small_estimates_title_tr");
    jQuery("#estimated_delivery_days_format").closest('tr').addClass("estimated_delivery_days_format_tr");
    jQuery("#estimated_delivery_date_format").closest('tr').addClass("estimated_delivery_date_format_tr");

    jQuery(".ups_small_shipment_day").closest('tr').addClass("ups_small_shipment_day_tr");
    jQuery("#all_shipment_days_ups_small").closest('tr').addClass("all_shipment_days_ups_small_tr");
    jQuery("#ups_small_cutOffTime_shipDateOffset").closest('tr').addClass("ups_small_cutOffTime_shipDateOffset_required_label");
    jQuery("#ups_small_orderCutoffTime").closest('tr').addClass("ups_small_cutOffTime_shipDateOffset");
    jQuery("#ups_small_shipmentOffsetDays").closest('tr').addClass("ups_small_cutOffTime_shipDateOffset");
    jQuery("#en_ignore_items_through_freight_classification").closest('tr').addClass("en_ignore_items_through_freight_classification_tr");
    jQuery("input[name=restrict_calendar_transit_small_packages_ups]").closest('tr').addClass("restrict_calendar_transit_small_packages_ups_tr");
    jQuery("input[name=ups_small_hazardous_fee_options]").closest('tr').addClass("ups_small_hazardous_fee_options_tr");
    jQuery("#ups_small_enable_logs").closest('tr').addClass("ups_small_enable_logs_tr");
    jQuery("input[name=ups_small_rate_source]").closest('tr').addClass("ups_small_rate_source_tr");
    jQuery("#ups_small_rate_source").closest('tr').addClass("ups_small_rate_source_tr");

    jQuery('#ups_small_shipmentOffsetDays').attr('min', 1);

    // toggle delivery estimate label fields display
    jQuery('#estimated_delivery_days_format').attr('maxlength', 50);
    jQuery('#estimated_delivery_date_format').attr('maxlength', 50);
    toggle_ups_delivery_estimate_label_fields();
    jQuery('input[name=ups_small_delivery_estimates]').on('change', toggle_ups_delivery_estimate_label_fields);

    var upsSmallCurrentTime = eniture_ups_small_admin_script.ups_small_order_cutoff_time;
    if (upsSmallCurrentTime != '') {
        jQuery('#ups_small_orderCutoffTime').wickedpicker({
            now: upsSmallCurrentTime,
            title: 'Cut Off Time'
        });
    } else {
        jQuery('#ups_small_orderCutoffTime').wickedpicker({
            now: '',
            title: 'Cut Off Time'
        });
    }

    /*
     * Uncheck Week days Select All Checkbox
     */

    jQuery(".ups_small_shipment_day").on('change load', function () {
        var checkboxes = jQuery('.ups_small_shipment_day:checked').length;
        var un_checkboxes = jQuery('.ups_small_shipment_day').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.all_shipment_days_ups_small').prop('checked', true);
        } else {
            jQuery('.all_shipment_days_ups_small').prop('checked', false);
        }
    });

    /*
     * Select All Shipment Week days
     */

    var all_int_checkboxes = jQuery('.all_shipment_days_ups_small');
    if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
        jQuery('.all_shipment_days_ups_small').prop('checked', true);
    }

    jQuery(".all_shipment_days_ups_small").change(function () {
        if (this.checked) {
            jQuery(".ups_small_shipment_day").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".ups_small_shipment_day").each(function () {
                this.checked = false;
            });
        }
    });

    //** Start: Validat Shipment Offset Days
    jQuery("#ups_small_shipmentOffsetDays").keydown(function (e) {
        if (e.keyCode == 8)
            return;

        var val = jQuery("#ups_small_shipmentOffsetDays").val();
        if (val.length > 1 || e.keyCode == 190) {
            e.preventDefault();
        }
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

    });
    // Allow: only positive numbers
    jQuery("#ups_small_shipmentOffsetDays").keyup(function (e) {
        if (e.keyCode == 189) {
            e.preventDefault();
            jQuery("#ups_small_shipmentOffsetDays").val('');
        }

    });

    // To update packaging type
    if(eniture_ups_small_admin_script.ups_small_packaging_type == ''){
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: 'eniture_ups_small_activate_hit_to_update_plan'},
            success: function (data_response) {}
        });
    }

    jQuery("#ups_small_ground_hazardous_fee , #ups_small_air_hazardous_fee , #ups_small_hand_fee_mark_up, #restrict_days_transit_package_ups_small, #ups_small_shipmentOffsetDays").focus(function (e) {
        jQuery("#" + this.id).css({'border-color': '#ddd'});
    });

    var prevent_text_box = jQuery('.prevent_text_box').length;
    if (!prevent_text_box > 0) {
        jQuery("input[name*='wc_pervent_proceed_checkout_eniture']").closest('tr').addClass('wc_pervent_proceed_checkout_eniture');
        jQuery(".wc_pervent_proceed_checkout_eniture input[value*='allow']").after('Allow user to continue to check out and display this message <br><textarea  name="allow_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250">' + eniture_ups_small_admin_script.allow_proceed_checkout_eniture + '</textarea><br><span class="description"> Enter a maximum of 250 characters.</span>');
        jQuery(".wc_pervent_proceed_checkout_eniture input[value*='prevent']").after('Prevent user from checking out and display this message <br><textarea name="prevent_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250">' + eniture_ups_small_admin_script.prevent_proceed_checkout_eniture + '</textarea><br><span class="description"> Enter a maximum of 250 characters.</span>');
    }

    var url = get_url_vars_ups_freight()["tab"];
    if (url === 'ups_small') {
        jQuery('#footer-left').attr('id', 'wc-footer-left');
    }
    /*
     * Add err class on connection settings page
     */
    jQuery('.ups_small_connection_section input[type="text"]').each(function () {
        if (jQuery(this).parent().find('.err').length < 1) {
            jQuery(this).after('<span class="err"></span>');
        }
    });


    /*
     * Add maxlength Attribute on Account Number Connection Setting Page
     */

    jQuery("#ups_small_account_number").attr('maxlength', '8');

    jQuery("#ups_client_id").attr('minlength', '1');
    jQuery("#ups_client_secret").attr('minlength', '1');
    jQuery("#ups_client_id").attr('maxlength', '100');
    jQuery("#ups_client_secret").attr('maxlength', '100');
    jQuery("#ups_new_api_username").attr('maxlength', '100');
    jQuery("#ups_new_api_password").attr('maxlength', '100');


    jQuery('.ups_small_connection_section .form-table').before('<div class="ups_sm_warning_msg"><p><b>Note!</b> You must have a UPS Small account to use this application. If you do not have one contact UPS at 800-742-5877 or <a href="https://www.ups.com/one-to-one/login" target="_blank" >register online</a>.</p>');
    jQuery('.ups_sm_warning_msg').first().show();

    /*
     * Add Title To Connection Setting Fields
     */

    jQuery('#ups_small_api_access_key').attr('title', 'API Access Key');
    jQuery('#ups_small_password').attr('title', 'Password');
    jQuery('#ups_small_account_number').attr('title', 'Account Number');
    jQuery('#ups_small_username').attr('title', 'Username');
    jQuery('#ups_small_licence_key').attr('title', 'Eniture API Key');
    jQuery('#ups_small_ground_hazardous_fee').attr('title', 'Ground Hazardous Material Fee');
    jQuery('#ups_small_air_hazardous_fee').attr('title', 'Air Hazardous Material Fee');
    jQuery('#ups_small_hand_fee_mark_up').attr('title', 'Handling Fee / Markup');
    jQuery('#ups_client_id').attr('title', 'Client ID');
    jQuery('#ups_client_secret').attr('title', 'Client Secret');
    jQuery('#ups_new_api_username').attr('title', 'Username');
    jQuery('#ups_new_api_password').attr('title', 'Password');

    /*
     * Add CSS Class To Quote Services
     */
    jQuery("#ups_small_dom_srvc_hdng").closest('tr').addClass("ups_small_dom_srvc_hdng_tr");
    jQuery("#ups_small_int_srvc_hdng").closest('tr').addClass("ups_small_int_srvc_hdng_tr");
    jQuery('.bold-text').closest('tr').addClass('ups_small_quotes_services_tr');
    jQuery('.ups_small_quotes_services').closest('tr').addClass('ups_small_quotes_services_tr');
    jQuery('.ups_small_quotes_services').closest('td').addClass('ups_small_quotes_services_td');
    jQuery('.ups_small_int_quotes_services').closest('tr').addClass('ups_small_quotes_services_tr');
    jQuery('.ups_international_service').closest('tr').addClass('ups_small_quotes_services_tr_right');
    jQuery('.ups_small_all_services').closest('tr').addClass('wc_settings_domestic_select_all');
    jQuery('.ups_small_all_int_services').closest('tr').addClass('wc_settings_inter_select_all');
    jQuery('.ups_small_int_quotes_services').closest('td').addClass('ups_small_quotes_services_td');
    jQuery('.ups_international_service').closest('td').addClass('ups_small_quotes_services_td_right');

    jQuery('#ups_surepost_less_than_1_lb').closest('tr').addClass('ups_small_contract_services_tr');
    jQuery('#ups_surepost_1_lb_or_greater').closest('tr').addClass('ups_small_contract_services_tr');
    jQuery('#ups_surepost_bound_printed_matter').closest('tr').addClass('ups_small_contract_services_tr');
    jQuery('#ups_surepost_media_mail').closest('tr').addClass('ups_small_contract_services_tr');
    jQuery('#ups_ground_with_freight_pricing').closest('tr').addClass('ups_small_contract_services_tr');
    jQuery('#ups_surepost_less_than_1_lb').closest('td').addClass('ups_small_contract_services_td');
    jQuery('#ups_surepost_1_lb_or_greater').closest('td').addClass('ups_small_contract_services_td');
    jQuery('#ups_surepost_bound_printed_matter').closest('td').addClass('ups_small_contract_services_td');
    jQuery('#ups_surepost_media_mail').closest('td').addClass('ups_small_contract_services_td');
    jQuery('#ups_ground_with_freight_pricing').closest('td').addClass('ups_small_contract_services_td');

    /*
     * Uncheck Select All Checkbox
     */

    jQuery(".ups_small_quotes_services").on('change load', function () {
        var checkboxes = jQuery('.ups_small_quotes_services:checked').length;

        var un_checkboxes = jQuery('.ups_small_quotes_services').length;

        if (checkboxes === un_checkboxes) {
            jQuery('.ups_small_all_services').prop('checked', true);
        } else {
            jQuery('.ups_small_all_services').prop('checked', false);
        }
    });

    /*
     * Uncheck International Services Select All Checkbox
     */

    jQuery(".ups_small_int_quotes_services").on('change load', function () {
        var int_checkboxes = jQuery('.ups_small_int_quotes_services:checked').length;
        var int_un_checkboxes = jQuery('.ups_small_int_quotes_services').length;

        if (int_checkboxes === int_un_checkboxes) {
            jQuery('.ups_small_all_int_services').prop('checked', true);
        } else {
            jQuery('.ups_small_all_int_services').prop('checked', false);
        }
    });

    if (typeof ups_connection_section_api_endpoint == 'function') {
        ups_connection_section_api_endpoint();
    }

    jQuery('#ups_api_endpoint').on('change', function () {
        ups_connection_section_api_endpoint();
    });


    /*
     * Select All Services
     */
    var sm_all_checkboxes = jQuery('.ups_small_quotes_services');
    if (sm_all_checkboxes.length === sm_all_checkboxes.filter(":checked").length) {
        jQuery('.ups_small_all_services').prop('checked', true);
    }

    jQuery(".ups_small_all_services").change(function () {
        if (this.checked) {
            jQuery(".ups_small_quotes_services").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".ups_small_quotes_services").each(function () {
                this.checked = false;
            });
        }
    });
    /*
         * Select All Services International
         */
    var all_int_checkboxes = jQuery('.ups_small_int_quotes_services');
    if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
        jQuery('.ups_small_all_int_services').prop('checked', true);
    }

    jQuery(".ups_small_all_int_services").change(function () {
        if (this.checked) {
            jQuery(".ups_small_int_quotes_services").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".ups_small_int_quotes_services").each(function () {
                this.checked = false;
            });
        }
    });

    /*
         * Connection Settings Input Validation On Save
         */

    jQuery(".ups_small_connection_section .button-primary, .ups_small_connection_section .is-primary").click(function () {
        var has_err = true;
        jQuery(".ups_small_connection_section tbody input[type='text']").each(function () {
            var input = jQuery(this).val();
            var response = validateString(input);
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');

            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';

            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }

            has_err = (response != true && optional == 0) ? false : has_err;
        });
        var input = has_err;
        if (input === false) {
            return false;
        }
    });
    var en_wd_origin_city = jQuery("#en_wd_origin_city").val();
    /*
     * Test Connection
     */

    jQuery(".ups_small_connection_section .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary ups_small_test_connection">Test Connection</a>');
    jQuery('.ups_small_test_connection').click(function (e) {
       var has_err = true;
        jQuery(".ups_small_connection_section tbody input[type='text']").each(function () {
            var input = jQuery(this).val();
            var response = validateString(input);
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');

            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';

            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }

            has_err = (response != true && optional == 0) ? false : has_err;
        });
        var input = has_err;
        if (input === false) {
            return false;
        }

        let api_endpoint = jQuery('#ups_api_endpoint').val();
        var postForm = {
            'action': 'eniture_ups_small_test_connection',
            'ups_small_acc_number': jQuery('#ups_small_account_number').val(),
            'ups_small_license': jQuery('#ups_small_licence_key').val(),
            'api_end_point': api_endpoint,
        };

        if(api_endpoint == 'ups_old_api'){
            postForm.ups_small_username = jQuery('#ups_small_username').val();
            postForm.ups_small_password = jQuery('#ups_small_password').val();
            postForm.ups_small_api_access_key = jQuery('#ups_small_api_access_key').val();
        }else{
            postForm.client_id = jQuery('#ups_client_id').val();
            postForm.client_secret = jQuery('#ups_client_secret').val();
            postForm.ups_small_username = jQuery('#ups_new_api_username').val();
            postForm.ups_small_password = jQuery('#ups_new_api_password').val();
        }

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: postForm,
            dataType: 'json',

            beforeSend: function () {
                jQuery('#ups_small_api_access_key, #ups_small_password, #ups_small_account_number, #ups_small_username, #ups_small_licence_key, #ups_client_id, #ups_client_secret, #ups_new_api_username, #ups_new_api_password').addClass('ups_small_test_conn_prosessing');
            },
            success: function (data) {
                jQuery('#ups_small_api_access_key, #ups_small_password, #ups_small_account_number, #ups_small_username, #ups_small_licence_key, #ups_client_id, #ups_client_secret,  #ups_new_api_username, #ups_new_api_password').removeClass('ups_small_test_conn_prosessing');
                jQuery(".ups_small_success_message, .ups_small_error_message, .updated").remove();

                if (data.success) {
                    jQuery('.ups_sm_warning_msg').before('<div class="notice notice-success ups_small_success_message"><p><strong>Success!</strong> ' + data.success + '</p></div>');
                } else if (data.error) {
                    jQuery('.ups_sm_warning_msg').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error! </strong>' + data.error + '</p></div>');
                } else {
                    jQuery('.ups_sm_warning_msg').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error! </strong>Please verify credentials and try again.</p></div>');
                }
            }
        });
        e.preventDefault();
    });
    // fdo va
    jQuery('#fd_online_id_ups_s').click(function (e) {
        var postForm = {
            'action': 'ups_s_fd',
            'company_id': jQuery('#freightdesk_online_id').val(),
            'disconnect': jQuery('#fd_online_id_ups_s').attr("data")
        }
        var id_lenght = jQuery('#freightdesk_online_id').val();
        var disc_data = jQuery('#fd_online_id_ups_s').attr("data");
        if(typeof (id_lenght) != "undefined" && id_lenght.length < 1) {
            jQuery(".ups_small_error_message").remove();
            jQuery('.user_guide_fdo').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error!</strong> FreightDesk Online ID is Required.</p></div>');
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: postForm,
            beforeSend: function () {
                jQuery('#freightdesk_online_id').addClass('ups_small_test_conn_prosessing');
            },
            success: function (data_response) {
                if(typeof (data_response) == "undefined"){
                    return;
                }
                var fd_data = JSON.parse(data_response);
                jQuery('#freightdesk_online_id').css('background', '#fff');
                jQuery(".ups_small_error_message").remove();
                if((typeof (fd_data.is_valid) != 'undefined' && fd_data.is_valid == false) || (typeof (fd_data.status) != 'undefined' && fd_data.is_valid == 'ERROR')) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'SUCCESS') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-success ups_small_success_message"><p><strong>Success! ' + fd_data.message + '</strong></p></div>');
                    window.location.reload(true);
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'ERROR') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if (fd_data.is_valid == 'true') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error!</strong> FreightDesk Online ID is not valid.</p></div>');
                } else if (fd_data.is_valid == 'true' && fd_data.is_connected) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error ups_small_error_message"><p><strong>Error!</strong> Your store is already connected with FreightDesk Online.</p></div>');

                } else if (fd_data.is_valid == true && fd_data.is_connected == false && fd_data.redirect_url != null) {
                    window.location = fd_data.redirect_url;
                } else if (fd_data.is_connected == true) {
                    jQuery('#con_dis').empty();
                    jQuery('#con_dis').append('<a href="#" id="fd_online_id_ups_s" data="disconnect" class="button-primary">Disconnect</a>')
                }
            }
        });
        e.preventDefault();
    });

    //          JS for edit product nested fields
    jQuery("._nestedMaterials").closest('p').addClass("_nestedMaterials_tr");
    jQuery("._nestedPercentage").closest('p').addClass("_nestedPercentage_tr");
    jQuery("._maxNestedItems").closest('p').addClass("_maxNestedItems_tr");
    jQuery("._nestedDimension").closest('p').addClass("_nestedDimension_tr");
    jQuery("._nestedStakingProperty").closest('p').addClass("_nestedStakingProperty_tr");

    if (!jQuery('._nestedMaterials').is(":checked")) {
        jQuery('._nestedPercentage_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._maxNestedItems_tr').hide();
        jQuery('._nestedDimension_tr').hide();
        jQuery('._nestedStakingProperty_tr').hide();
    } else {
        jQuery('._nestedPercentage_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._maxNestedItems_tr').show();
        jQuery('._nestedDimension_tr').show();
        jQuery('._nestedStakingProperty_tr').show();
    }

    jQuery("._nestedPercentage").attr('min', '0');
    jQuery("._maxNestedItems").attr('min', '0');
    jQuery("._nestedPercentage").attr('max', '100');
    jQuery("._maxNestedItems").attr('max', '100');
    jQuery("._nestedPercentage").attr('maxlength', '3');
    jQuery("._maxNestedItems").attr('maxlength', '3');

    if (jQuery("._nestedPercentage").val() == '') {
        jQuery("._nestedPercentage").val(0);
    }

    var delivery_estimate = jQuery('input[name=ups_small_delivery_estimates]:checked').val();
    if (delivery_estimate == undefined) {
        jQuery('.ups_small_dont_show_estimate_option').prop("checked", true);
    }

    var delivery_estimate_val = jQuery('input[name=ups_small_delivery_estimates]:checked').val();
    if (delivery_estimate_val == 'dont_show_estimates') {
        jQuery("#ups_small_orderCutoffTime").prop('disabled', true);
        jQuery("#ups_small_shipmentOffsetDays").prop('disabled', true);
        jQuery('.all_shipment_days_ups_small, .ups_small_shipment_day').prop('disabled', true);
    } else {
        jQuery("#ups_small_orderCutoffTime").prop('disabled', false);
        jQuery("#ups_small_shipmentOffsetDays").prop('disabled', false);
        jQuery('.all_shipment_days_ups_small, .ups_small_shipment_day').prop('disabled', false);
    }


    jQuery('.ups_small_quote_section .button-primary, .ups_small_quote_section .is-primary').on('click', function (e) {
        jQuery(".updated").hide();
        jQuery('.error').remove();

        if (!en_ups_small_handling_fee_validation()) {
            return false;
        } else if (!en_ups_small_air_hazardous_material_fee_validation()) {
            return false;
        } else if (!en_ups_small_ground_hazardous_material_fee_validation()) {
            return false;
        } else if (!en_ups_small_ground_transit_validation()) {
            return false;
        } else if (!ups_small_palletshipclass()) {
            return false;
        }

        var ups_small_shipmentOffsetDays = jQuery("#ups_small_shipmentOffsetDays").val();
        if (ups_small_shipmentOffsetDays != "" && ups_small_shipmentOffsetDays < 1) {

            jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_orderCutoffTime_error"><p><strong>Error! </strong>Days should not be less than 1.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.ups_small_orderCutoffTime_error').position().top
            });
            jQuery("#ups_small_shipmentOffsetDays").css({'border-color': '#e81123'});
            return false
        }
        if (ups_small_shipmentOffsetDays != "" && ups_small_shipmentOffsetDays > 8) {

            jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_orderCutoffTime_error"><p><strong>Error! </strong>Days should be less than or equal to 8.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.ups_small_orderCutoffTime_error').position().top
            });
            jQuery("#ups_small_shipmentOffsetDays").css({'border-color': '#e81123'});
            return false
        }

        var numberOnlyRegex = /^[0-9]+$/;

        if (ups_small_shipmentOffsetDays != "" && !numberOnlyRegex.test(ups_small_shipmentOffsetDays)) {

            jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_orderCutoffTime_error"><p><strong>Error! </strong>Entered Days are not valid.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.ups_small_orderCutoffTime_error').position().top
            });
            jQuery("#ups_small_shipmentOffsetDays").css({'border-color': '#e81123'});
            return false
        }

        let ups_small_quotes_markup_left_markup_class = jQuery('.ups_small_quotes_markup_left_markup');
        jQuery(ups_small_quotes_markup_left_markup_class).each(function () {

            if (jQuery('#' + this.id).val() != '' && !en_ups_small_domestic_markup_service(this.id)) {
                e.preventDefault();
                return false;
            }
        });


        let ups_small_quotes_markup_right_markup = jQuery('.ups_small_quotes_markup_right_markup');
        jQuery(ups_small_quotes_markup_right_markup).each(function () {

            if (jQuery('#' + this.id).val() != '' && !en_ups_small_international_markup_service(this.id)) {
                e.preventDefault();
                return false;
            }
        });


        var num_of_checkboxes = jQuery('.ups_small_quotes_services:checked').length;
        var num_of_int_checkboxes = jQuery('.ups_small_int_quotes_services:checked').length;

        /*
         * Check Number of Selected Services
         */

        if (num_of_checkboxes < 1 && num_of_int_checkboxes < 1) {
            no_service_selected_ups_small(num_of_checkboxes);
            return false;
        }

        /*Custom Error Message Validation*/
        var checkedValCustomMsg = jQuery("input[name='wc_pervent_proceed_checkout_eniture']:checked").val();
        var allow_proceed_checkout_eniture = jQuery("textarea[name=allow_proceed_checkout_eniture]").val();
        var prevent_proceed_checkout_eniture = jQuery("textarea[name=prevent_proceed_checkout_eniture]").val();

        if (checkedValCustomMsg == 'allow' && allow_proceed_checkout_eniture == '') {
            jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_custom_error_message"><p><strong>Error! </strong>Custom message field is empty.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.ups_small_custom_error_message').position().top
            });
            return false;
        }
        if (checkedValCustomMsg == 'prevent' && prevent_proceed_checkout_eniture == '') {
            jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_custom_error_message"><p><strong>Error! </strong>Custom message field is empty.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.ups_small_custom_error_message').position().top
            });
            return false;
        }

    });

    //      estimated delivery options js
    jQuery("input[name=ups_small_delivery_estimates]").change(function () {
        var delivery_estimate_val = jQuery('input[name=ups_small_delivery_estimates]:checked').val();
        if (delivery_estimate_val == 'dont_show_estimates') {
            jQuery("#ups_small_orderCutoffTime").prop('disabled', true);
            jQuery("#ups_small_shipmentOffsetDays").prop('disabled', true);
            jQuery('.all_shipment_days_ups_small, .ups_small_shipment_day').prop('disabled', true);
        } else {
            jQuery("#ups_small_orderCutoffTime").prop('disabled', false);
            jQuery("#ups_small_shipmentOffsetDays").prop('disabled', false);
            jQuery('.all_shipment_days_ups_small, .ups_small_shipment_day').prop('disabled', false);
        }
    });
    //      Nested fields validation on product details
    jQuery("._nestedPercentage").keydown(function (eve) {
        stopSpecialCharacters(eve);
        var nestedPercentage = jQuery('._nestedPercentage').val();
        if (nestedPercentage.length == 2) {
            var newValue = nestedPercentage + '' + eve.key;
            if (newValue > 100) {
                return false;
            }
        }
    });

    jQuery("._maxNestedItems").keydown(function (eve) {
        ups_small_stop_special_characters(eve);
    });

    jQuery("._nestedMaterials").change(function () {
        if (!jQuery('._nestedMaterials').is(":checked")) {
            jQuery('._nestedPercentage_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._maxNestedItems_tr').hide();
            jQuery('._nestedDimension_tr').hide();
            jQuery('._nestedStakingProperty_tr').hide();
        } else {
            jQuery('._nestedPercentage_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._maxNestedItems_tr').show();
            jQuery('._nestedDimension_tr').show();
            jQuery('._nestedStakingProperty_tr').show();
        }

    });

    jQuery(document).on("click", '._nestedMaterials', function(e) {
        const checkbox_class = jQuery(e.target).attr("class");
        const name = jQuery(e.target).attr("name");
        const checked = jQuery(e.target).prop('checked');

        if (checkbox_class?.includes('_nestedMaterials')) {
            const id = name?.split('_nestedMaterials')[1];
            setNestMatDisplay(id, checked);
        }
    });

    // Callback function to execute when mutations are observed
    const handleMutations = (mutationList) => {
        let childs = [];
        for (const mutation of mutationList) {
            childs = mutation?.target?.children;
            if (childs?.length) setNestedMaterialsUI();
          }
    };
    const observer = new MutationObserver(handleMutations),
        targetNode = document.querySelector('.woocommerce_variations.wc-metaboxes'),
        config = { childList: true, subtree: true };
    if (targetNode) observer.observe(targetNode, config);

    jQuery(".ups_small_quotes_markup_left_label").closest('tr').addClass('ups_small_quotes_left_label');
    jQuery(".ups_small_quotes_markup_right_label").closest('tr').addClass('ups_small_quotes_right_label');
    jQuery(".ups_small_quotes_markup_left_markup").closest('tr').addClass('ups_small_quotes_left_markup');
    jQuery(".ups_small_quotes_markup_right_markup").closest('tr').addClass('ups_small_quotes_right_markup');
    jQuery("#ups_small_next_day_air_early_markup_label_empty").closest('tr').addClass('ups_small_next_day_air_early_markup_label_empty_tr');
    jQuery("#ups_small_3day_select_markup_label_empty").closest('tr').addClass('ups_small_3day_select_markup_label_empty_tr');
    jQuery(".ups_small_quotes_label_left").attr('maxlength', 50);
    jQuery(".ups_small_quotes_label_left").closest('tr').addClass('ups_small_quotes_left_markup');

    //** Start: Validation for domestic service level markup

    jQuery(".ups_small_quotes_markup_left_markup, .ups_small_quotes_markup_right_markup, #en_wd_origin_markup, #en_wd_dropship_markup, ._en_product_markup").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }

        if(jQuery(this).val().length > 7){
            e.preventDefault();
        }

    });

    jQuery(".ups_small_quotes_markup_left_markup, .ups_small_quotes_markup_right_markup, #en_wd_origin_markup, #en_wd_dropship_markup, ._en_product_markup").keyup(function (e) {

        var val = jQuery(this).val();
        jQuery(this).css({"border": "1px solid #ddd"});

        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery(this).val(newval);

        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
        if (val.split('>').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
            newval = newval.substring(newval, newval.length - countGreaterThan - 1);
            jQuery(this).val(newval);
        }
        if (val.split('_').length - 1 > 0) {
            var newval = val.substring(0, val.length - 1);
            var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
            newval = newval.substring(newval, newval.length - countUnderScore - 1);
            jQuery(this).val(newval);
        }
    });

});

function ups_small_stop_special_characters(e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if (jQuery.inArray(e.keyCode, [46, 9, 27, 13, 110, 190, 189]) !== -1 ||
        // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
        // let it happen, don't do anything
        e.preventDefault();
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 90)) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 186 && e.keyCode != 8) {
        e.preventDefault();
    }
    if (e.keyCode == 186 || e.keyCode == 190 || e.keyCode == 189 || (e.keyCode > 64 && e.keyCode < 91)) {
        e.preventDefault();
        return;
    }
}

/*
* Validate Selecting Services
*/
function no_service_selected_ups_small(num_of_checkboxes) {
    jQuery(".updated").hide();
    jQuery(".ups_small_quote_section h2:first-child").after('<div id="message" class="error inline no_srvc_select"><p><strong>Error! </strong>Please select at least one quote service.</p></div>');
    jQuery('html, body').animate({
        'scrollTop': jQuery('.no_srvc_select').position().top
    });
    return false;
}

function en_ups_small_domestic_markup_service(id) {

    var en_ups_small_domestic_markup_service = jQuery('#' + id).val();
    var en_ups_small_domestic_markup_service_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
    var numeric_values_regex = /^[0-9]{1,7}$/;
    if (numeric_values_regex.test(en_ups_small_domestic_markup_service)) {
        return true;
    } else if (!en_ups_small_domestic_markup_service_regex.test(en_ups_small_domestic_markup_service)) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_dom_markup_service_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_small_dom_markup_service_error').position().top
        });
        jQuery("#" + id).css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function en_ups_small_international_markup_service(id) {

    var en_ups_small_international_markup_service = jQuery('#' + id).val();
    var en_ups_small_international_markup_service_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
    var numeric_values_regex = /^[0-9]{1,7}$/;
    if (numeric_values_regex.test(en_ups_small_international_markup_service)) {
        return true;
    } else if (!en_ups_small_international_markup_service_regex.test(en_ups_small_international_markup_service)) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_int_markup_service_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_small_int_markup_service_error').position().top
        });
        jQuery("#" + id).css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function ups_small_palletshipclass() {
    var en_ship_class = jQuery('#en_ignore_items_through_freight_classification').val();
    var en_ship_class_arr = en_ship_class.split(',');
    var en_ship_class_trim_arr = en_ship_class_arr.map(Function.prototype.call, String.prototype.trim);
    if (en_ship_class_trim_arr.indexOf('ltl_freight') != -1) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_pallet_weight_error"><p><strong>Error! </strong>Shipping Slug of <b>ltl_freight</b> can not be ignored.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_small_pallet_weight_error').position().top
        });
        jQuery("#en_ignore_items_through_freight_classification").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function en_ups_small_handling_fee_validation() {

    var handling_fee = jQuery('#ups_small_hand_fee_mark_up').val();
    var handling_fee_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
    var numeric_values_regex = /^[0-9]{1,7}$/;
    if (handling_fee != '' && numeric_values_regex.test(handling_fee)) {
        return true;
    } else if (handling_fee != '' && !handling_fee_regex.test(handling_fee) || handling_fee.split('.').length - 1 > 1) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_handlng_fee_error"><p><strong>Error! </strong>Handling fee format should be 100.20 or 10%.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_small_handlng_fee_error').position().top
        });
        jQuery("#ups_small_hand_fee_mark_up").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function en_ups_small_air_hazardous_material_fee_validation() {

    var air_hazardous_fee = jQuery('#ups_small_air_hazardous_fee').val();
    var air_hazardous_fee_regex = /^([0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
    var numeric_values_regex = /^[0-9]{1,7}$/;
    if (air_hazardous_fee != '' && numeric_values_regex.test(air_hazardous_fee)) {
        return true;
    } else if (air_hazardous_fee != '' && (!air_hazardous_fee_regex.test(air_hazardous_fee) || airHazardousValidNumber(air_hazardous_fee))) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_air_hazardous_fee_error"><p><strong>Error! </strong>Air hazardous material fee format should be 100.20 or 10%.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_small_air_hazardous_fee_error').position().top
        });
        jQuery("#ups_small_air_hazardous_fee").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function airHazardousValidNumber(air_hazardous_fee) {

    if (air_hazardous_fee.indexOf("-") == -1) {
        return false
    } else {
        return true;
    }
}

function en_ups_small_ground_hazardous_material_fee_validation() {

    var ground_hazardous_fee = jQuery('#ups_small_ground_hazardous_fee').val();
    var ground_hazardous_regex = /^([0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
    var numeric_values_regex = /^[0-9]{1,7}$/;
    if (ground_hazardous_fee != '' && numeric_values_regex.test(ground_hazardous_fee)) {
        return true;
    } else if (ground_hazardous_fee != '' && (!ground_hazardous_regex.test(ground_hazardous_fee) || airHazardousValidNumber(ground_hazardous_fee))) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_small_ground_hazardous_fee_error"><p><strong>Error! </strong>Ground hazardous material fee format should be 100.20 or 10%.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_small_ground_hazardous_fee_error').position().top
        });
        jQuery("#ups_small_ground_hazardous_fee").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}

function groundTransitValidNumber(ground_hazardous_fee) {

    if (ground_hazardous_fee.indexOf("-") == -1) {
        return false
    } else {
        return true;
    }
}

function en_ups_small_ground_transit_validation() {
    var ground_transit_value = jQuery('#restrict_days_transit_package_ups_small').val();
    var ground_transit_regex = /^[0-9]{1,2}$/;
    if (ground_transit_value != '' && !ground_transit_regex.test(ground_transit_value)) {
        jQuery("#mainform .ups_small_quote_section").prepend('<div id="message" class="error inline ups_ground_transit_error"><p><strong>Error! </strong>Maximum 2 numeric characters are allowed for transit day field.</p></div>');
        jQuery('html, body').animate({
            'scrollTop': jQuery('.ups_ground_transit_error').position().top
        });
        jQuery("#restrict_days_transit_package_ups_small").css({'border-color': '#e81123'});
        return false;
    } else {
        return true;
    }
}


/*
 * Validate Input If Empty or Invalid
 */

function validateInput(form_id) {
    var has_err = true;
    jQuery(form_id + " input[type='text']").each(function () {
        var input = jQuery(this).val();
        var response = validateString(input);
        var errorText = jQuery(this).attr('title');
        var optional = jQuery(this).data('optional');

        var errorElement = jQuery(this).parent().find('.err');
        jQuery(errorElement).html('');

        optional = (optional === undefined) ? 0 : 1;
        errorText = (errorText != undefined) ? errorText : '';

        if ((optional == 0) && (response == false || response == 'empty')) {
            errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
            jQuery(errorElement).html(errorText);
        }

        has_err = (response != true && optional == 0) ? false : has_err;
    });
    return has_err;
}

/*
 * Check Input Value Is Not String
 */

function isValidNumber(value, noNegative) {
    if (typeof (noNegative) === 'undefined')
        noNegative = false;
    var isValidNumber = false;
    var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
    if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
        if (value.indexOf(".") >= 0) {
            var n = value.split(".");

            if (n[n.length - 1].length <= 4) {
                isValidNumber = true;
            } else {
                isValidNumber = 'decimal_point_err';
            }
        } else {
            isValidNumber = true;
        }
    }
    return isValidNumber;
}

/*
 * Validate Input String
 */

function validateString(string) {
    if (string == '') {
        return 'empty';
    } else {
        return true;
    }
}

/**
 * Read a page's GET URL variables and return them as an associative array.
 */
function get_url_vars_ups_freight() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

// Update plan
if (typeof en_update_plan != 'function') {
    function en_update_plan(input) {
        let action = jQuery(input).attr('data-action');
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: action},
            success: function (data_response) {
                window.location.reload(true);
            }
        });
    }
}

/**
 * Hide and show test connection fields based on API selection
 */
 function ups_connection_section_api_endpoint() {
    jQuery("#ups_new_api_username").data('optional', '1');
    jQuery("#ups_new_api_password").data('optional', '1');
     
    let api_endpoint = jQuery('#ups_api_endpoint').val();
    if(api_endpoint == 'ups_old_api'){
        jQuery('.ups_new_api_field').closest('tr').hide();
        jQuery('.ups_old_api_field').closest('tr').show();

        jQuery("#ups_client_id").data('optional', '1');
        jQuery("#ups_client_secret").data('optional', '1');

        jQuery("#ups_small_username").removeData('optional');
        jQuery("#ups_small_password").removeData('optional');
        jQuery("#ups_small_api_access_key").removeData('optional');

    }else{
        jQuery('.ups_old_api_field').closest('tr').hide();
        jQuery('.ups_new_api_field').closest('tr').show();

        jQuery("#ups_small_username").data('optional', '1');
        jQuery("#ups_small_password").data('optional', '1');
        jQuery("#ups_small_api_access_key").data('optional', '1');

        jQuery("#ups_client_id").removeData('optional');
        jQuery("#ups_client_secret").removeData('optional');
    }

}

if (typeof ups_connection_section_api_endpoint == 'function') {
    ups_connection_section_api_endpoint();
}

function toggle_ups_delivery_estimate_label_fields ()
{   
    const estimatedDeliveryOption = jQuery('input[name=ups_small_delivery_estimates]:checked').val();
    
    if (estimatedDeliveryOption === 'delivery_days') {
        jQuery('.estimated_delivery_days_format_tr').show();
        jQuery('.estimated_delivery_date_format_tr').hide();
    } else if (estimatedDeliveryOption === 'delivery_date') {
        jQuery('.estimated_delivery_days_format_tr').hide();
        jQuery('.estimated_delivery_date_format_tr').show();
    } else {
        jQuery('.estimated_delivery_days_format_tr').hide();
        jQuery('.estimated_delivery_date_format_tr').hide();
    }
}

if (typeof setNestedMaterialsUI != 'function') {
    function setNestedMaterialsUI() {
        const nestedMaterials = jQuery('._nestedMaterials');
        const productMarkups = jQuery('._en_product_markup');
        
        if (productMarkups?.length) {
            for (const markup of productMarkups) {
                jQuery(markup).attr('maxlength', '7');

                jQuery(markup).keypress(function (e) {
                    if (!String.fromCharCode(e.keyCode).match(/^[0-9.%-]+$/))
                        return false;
                });
            }
        }

        if (nestedMaterials?.length) {
            for (let elem of nestedMaterials) {
                const className = elem.className;

                if (className?.includes('_nestedMaterials')) {
                    const checked = jQuery(elem).prop('checked'),
                        name = jQuery(elem).attr('name'),
                        id = name?.split('_nestedMaterials')[1];
                    setNestMatDisplay(id, checked);
                }
            }
        }
    }
}

if (typeof setNestMatDisplay != 'function') {
    function setNestMatDisplay(id, checked) {
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('min', '0');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('max', '100');
        jQuery(`input[name="_nestedPercentage${id}"]`).attr('maxlength', '3');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('min', '0');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('max', '100');
        jQuery(`input[name="_maxNestedItems${id}"]`).attr('maxlength', '3');

        jQuery(`input[name="_nestedPercentage${id}"], input[name="_maxNestedItems${id}"]`).keypress(function (e) {
            if (!String.fromCharCode(e.keyCode).match(/^[0-9]+$/))
                return false;
        });

        jQuery(`input[name="_nestedPercentage${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedDimension${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`input[name="_maxNestedItems${id}"]`).closest('p').css('display', checked ? '' : 'none');
        jQuery(`select[name="_nestedStakingProperty${id}"]`).closest('p').css('display', checked ? '' : 'none');
    }
}