<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div id="en_jtv_showing_res" class="overlay">
    <div class="popup">
        <h2 id="en_title_popup">Response</h2>
        <a class="close" href="#">&times;</a>
        <div class="content">
            <div>
                <span id="en_jtv_parse_error" style="color: darkred;"></span>
            </div>
            <div id="en_res_popup"></div>
            <script type="text/javascript">
                function en_jtv_res_detail(json) {
                    jQuery('#en_title_popup').text('Response');
                    jQuery("#en_res_popup").empty();
                    var tree = en_jtv_create_dom(json, true);
                    document.getElementById('en_res_popup').appendChild(tree);
                    en_jtv_show_data(json);
                }

                let item_per_shipment = {}, unpacked_wt_dims, package_count = 0;
                
                function en_packging_details(data) {
                    item_per_shipment = {};
                    unpacked_wt_dims = '';
                    package_count = 0;
                    let count = 1, packaging_output = '';

                    if (data.bins_array && Object.keys(data.bins_array).length) {
                        const {unpacked_shipment, shipment, total_count, item_per_shipment, bins_array} = data;

                        Object.keys(bins_array).forEach((details, zip) => {
                            details = bins_array[details];
                            
                            packaging_output += "<div class='en-package-details'>";
                            packaging_output += en_output_bins_packed(unpacked_shipment, shipment, details, zip, total_count);
                            packaging_output += "</div>";
                            count++;
                        });

                        jQuery('#en_title_popup').text('Packaging Details');
                        jQuery("#en_res_popup").empty();
                        jQuery("#en_res_popup").html(packaging_output);
                    }
                }

                function en_output_bins_packed(unpacked_shipment, shipment, details, zip, total_count)
                {
                    let main_bin_img = '', item_own_pkg = '', bin_count = 1, unpacked_flag = 0, box_output = "", packed_items = 1, count = 0, index = 't', index_count = 0, previous_dims = null, packed_dims = null, current_dims = null, boxes_output_arr = [], total_complete_img = 0;
                    
                    box_output += "<div class='per-package'>";

                    if (details && details?.length) {
                        for (const bin_details of details) {
                            box_output = "";
                            let dims = box_dims(bin_details?.bin_data);

                            switch (true) {
                                case (bin_details?.bin_data?.type && bin_details?.bin_data?.type == "item" && dims):
                                    index = "These items don't have dimensions and therefore couldn't be placed in a box. Shipping rates for these items were retrieved based on weight only.";
                                    packed_items = 3;
                                    break;

                                case (bin_details?.bin_data?.type && bin_details?.bin_data?.type == "item"):
                                    index = "These items were quoted as shipping as their own package.";
                                    packed_items = 2;
                                    break;
                                default :
                                    index = "Packed items";
                                    packed_items = 1;
                                    break;
                            }

                            let main_bin_img = bin_details?.image_complete;

                            if (packed_items == 2 || packed_items > 3) {
                                dims = bin_details?.items['0']?.w;

                                if (packed_items == 2) {
                                    dims = bin_details?.items['0']?.w . bin_details?.items['0']?.h . bin_details?.items['0']?.d;
                                }

                                dims && previous_dims == null ? previous_dims = dims : '';

                                if (unpacked_shipment == 'single') {
                                    !boxes_output_arr[packed_items] ? box_output += "<h4 class='packed_items'>" + index + "</h4>" : "";
                                } else {
                                    unpacked_flag && unpacked_flag == 0 ? box_output += "<h4 class='packed_items'>" + index + "</h4>" : '';

                                    unpacked_flag = 1;
                                    if (dims == previous_dims && (current_dims == null)) {
                                        current_dims = previous_dims;
                                    } else if (current_dims != dims) {
                                        current_dims = dims;
                                        box_output += "<h4 class='packed_items'>" + index + "</h4>";
                                    }
                                }

                                box_output += '<div class="en-package-' + bin_count + ' unpacked_item_parent unpacked_setting">';
                                box_output += '<div class="unpacked_item_child">';
                                product_name = bin_details?.bin_data?.product_name ? bin_details?.bin_data?.product_name : '';

                                if (packed_items != 3) {
                                    box_output += '<div class="en-product-steps-details">';
                                    box_output += "<span class='set_position'>" + product_name + "</span> <br>";
                                    box_output += "<span class='en-prdouct-steps-dimensions'>" + bin_details?.bin_data?.d + ' x ' + bin_details?.bin_data?.w + ' x ' + bin_details?.bin_data?.h + "</span>";
                                    box_output += '</div>';
                                    box_output += '<img  class="package-complete-image-tag image_setting" src="' + main_bin_img + '" />';
                                } else {
                                    box_output += '<img  class="package-complete-image-tag image_setting_no_dims" src="' + main_bin_img + '" />';
                                    box_output += '<div class="en-product-steps-details">';
                                    box_output += '<span class="en_product_weight"> ' + product_name + ' </span> ';
                                    box_output += '<span class="en_product_weight"> Weight = ' + bin_details?.bin_data?.weight + ' lbs</span> ';
                                    box_output += '</div>';
                                }

                                box_output += '</div>';
                                box_output += '</div>';
                            } else {
                                dims = bin_details?.items['0']?.w;
                                dims && (previous_dims == null) ? previous_dims = dims : '';
                                
                                if (shipment == 'single') {
                                    !boxes_output_arr[packed_items] ? box_output += "<h4 class=''>" + index + "</h4>" : "";
                                } else {
                                    if ((dims == previous_dims && (current_dims == null)) || (item_per_shipment['t' + (bin_count - 1)] && item_per_shipment['t' + (bin_count - 1)] == bin_count)) {
                                        current_dims = previous_dims;
                                        bin_count = (item_per_shipment['t' + (bin_count - 1)]) && (item_per_shipment['t' + (bin_count - 1)] == bin_count) ? 1 : bin_count;
                                        box_output += "<h4 class=''>" + index + "</h4>";
                                    } else if (current_dims != dims) {
                                        current_dims = dims;
                                        box_output += "<h4 class=''>" + index + "</h4>";
                                    }
                                }

                                total_count = shipment && item_per_shipment['t' + (bin_count - 1)] && shipment != "single" ? item_per_shipment['t' + (bin_count - 1)] : total_count;

                                box_output += '<div class="en-package-' + bin_count + ' packed_items">';
                                box_output += '<div class="en-full-row">';
                                box_output += '<div class="en-left before-steps-info">';
                                box_output += '<p class="reduce_space"><b>Box ' + bin_count + ' of ' + total_count + '</b></p>';
                                box_output += '<p class="reduce_space_total_item"><b>Number of items: ' + bin_details?.items?.length + '</b></p>';
                                box_output += '<p class="reduce_space_total_item box-prod-title">' + bin_details?.bin_data?.box_title + ' <strong>' + item_own_pkg + '</strong></p>';
                                box_output += '<div class="package-dimensions align_pkg_dims">'
                                    + '<p>' + bin_details?.bin_data?.d + ' x ' + bin_details?.bin_data?.w + ' x ' + bin_details?.bin_data?.h + '</p>'
                                    + '</div>';
                                box_output += '</div>';

                                bin_count = item_per_shipment['t' + (bin_count - 1)] && (item_per_shipment['t' + (bin_count - 1)] == bin_count) ? 0 : bin_count;

                                box_output += '<div class="package-complete-image align_pkg">';
                                box_output += '<img class="package-complete-image-tag" src="' + main_bin_img + '" />';
                                box_output += '</div>';
                                box_output += '</div>';
                                box_output += '</div>';
                                box_output += en_output_items_packed(bin_details, zip);
                            }

                            bin_count++;
                            boxes_output_arr[packed_items] ? boxes_output_arr[packed_items] += box_output : boxes_output_arr[packed_items] = box_output;
                        }
                    }

                    boxes_output_arr = boxes_output_arr.length > 0 ? boxes_output_arr.filter((el) => el) : '';

                    return boxes_output_arr[0];
                }

                function box_dims(bin_data) {
                    return (bin_data?.w && bin_data?.h && bin_data?.d) && !(bin_data?.w > 0 || bin_data?.h > 0 || bin_data?.d > 0) ? true : false;
                }

                function en_output_items_packed(bin_details, zip)
                {
                    box_output = "";
                    box_output += '<div class="package-steps-block">';
                    box_output += "<p class='packed_items'><strong>Steps:</strong></p>";
                    total_items_packet = bin_details?.items?.length;
                    item_image = '';

                    /* Items packed details */
                    for (const item_details of bin_details?.items) {                        
                        box_output += '<div class="package-steps-product">';
                        box_output += '<img class="en-prduct-steps-image" src="' + item_details?.image_sbs + '" />';
                        box_output += '<div class="en-product-steps-details">';
                        
                        product_name = item_details?.product_name ? item_details?.product_name : '';
                        box_output += '<p class="en-prdouct-steps-dimensions">' + product_name + '</p>';
                        box_output += '<p class="en-prdouct-steps-dimensions">' + item_details?.d + ' x ' + item_details?.w + ' x ' + item_details?.h + '</p>';
                        
                        box_output += '</div>';
                        box_output += '</div>';
                    }

                    /* Clear the float effect */
                    box_output += '<div class="en-clear"></div>';
                    box_output += '</div>';

                    return box_output;
                }
            </script>
        </div>
    </div>
</div>

