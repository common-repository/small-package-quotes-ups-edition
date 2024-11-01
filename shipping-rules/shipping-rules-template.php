<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shipping Rules Template
 */
if (!function_exists('eniture_shipping_rules_template')) {
    function eniture_shipping_rules_template($action = false)
    {
      ob_start();

      global $wpdb;
      $shipping_rules_list = $wpdb->get_results(
          "SELECT * FROM " . $wpdb->prefix . "eniture_ups_small_shipping_rules"
      );

      ?>
        <div>
          <table class="en_wd_warehouse_list" id="en_ups_shipping_rules_list">
            <!-- Table Headings -->
            <thead>
                  <tr>
                      <th class="en_wd_warehouse_list_heading">
                          Rule Name
                      </th>
                      <th class="en_wd_warehouse_list_heading">
                          Type
                      </th>
                      <th class="en_wd_warehouse_list_heading">
                          Filters
                      </th>
                      <th class="en_wd_warehouse_list_heading">
                          Available
                      </th>
                      <th class="en_wd_warehouse_list_heading">
                          Action
                      </th>
                  </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
              <?php
                if (count($shipping_rules_list) > 0) {
                  $count = 0;
                  foreach ($shipping_rules_list as $rule) {
                    $rule->settings = !empty($rule->settings) ? json_decode($rule->settings) : [];

                    ?>
                      <tr id="sr_row_<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>" class="en_ups_sr_row">
                        <td class="en_wd_warehouse_list_data"><?php echo esc_attr($rule->name); ?></td>
                        <td class="en_wd_warehouse_list_data"><?php echo esc_attr($rule->type); ?></td>
                        <td class="en_wd_warehouse_list_data"><?php echo isset($rule->settings->filter_name) ? esc_attr($rule->settings->filter_name ): ''; ?></td>
                        <td class="en_wd_warehouse_list_data">
                          <a href="#" class="en_ups_sr_status_link" data-id="<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>" data-status="<?php echo esc_attr($rule->is_active); ?>"><?php echo esc_attr($rule->is_active) ? 'Yes' : 'No'; ?></a>
                        </td>
                        <td class="en_wd_warehouse_list_data">
                          <!-- Edit rule link -->
                          <a href="#" class="en_ups_sr_edit_link" data-id="<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>">
                            <img src="<?php echo esc_attr(plugins_url()); ?>/small-package-quotes-ups-edition/warehouse-dropship/wild/assets/images/edit.png" title="Edit">
                          </a>
                          <!-- Delete rule link -->
                          <a href="#" class="en_ups_sr_delete_link" data-id="<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>">
                            <img src="<?php echo esc_attr(plugins_url()); ?>/small-package-quotes-ups-edition/warehouse-dropship/wild/assets/images/delete.png" title="Delete">
                          </a>
                        </td>
                      </tr>
                    <?php

                    $count++;
                  }
                } else {
                  ?>
                    <tr class="new_warehouse_add en_ups_empty_sr_row" data-id=0>
                      <td class="en_wd_warehouse_list_data" colspan="5" style="text-align: center;">
                        No data found!
                      </td>
                    </tr>
                  <?php
                }
              ?>
            </tbody>
          </table>
        </div>
      <?php

      if ($action) {
          $ob_get_clean = ob_get_clean();
          return $ob_get_clean;
      }
    }
}
?>

<!-- Shipping rules html markup -->
<div class="en_shipping_rules_setting_section">
    <br />
    <!-- Add rule button -->
    <div class="en_sr_add_btn">
      <a href="#en_wd_add_warehouse_btn" onclick="" title="Add Rule"
             class="en_wd_add_warehouse_btn" id="en_sr_add_shipping_rule_btn" name="avc">Add Rule</a>
    </div>

    <div class="updated inline warehouse_deleted sr_deleted">
      <p><strong>Success!</strong> Shipping rule is deleted successfully.</p>
    </div>
    <div class="updated inline warehouse_created sr_created">
        <p><strong>Success!</strong> Shipping rule is added successfully.</p>
    </div>
    <div class="updated inline warehouse_updated sr_updated">
        <p><strong>Success!</strong> Shipping rule is updated successfully.</p>
    </div>

    <!-- Shipping rules data table -->
    <?php eniture_shipping_rules_template(); ?>

    <!-- Add popup for new rule -->
    <div id="en_wd_add_warehouse_btn" class="en_wd_warehouse_overlay">
        <div class="en_wd_add_warehouse_popup">
            <h2 class="warehouse_heading">Add Rule</h2>
            <a class="close" href="#">&times;</a>
            <div class="content" id="en_ups_small_sr_content" style="overflow-y: auto; height: 80vh;">

              <div class="already_exist sr_already_exist">
                <strong>Error!</strong> Shipping rule with this name already exists.
              </div>
                <!-- Wordpress Form closed -->
                </form>

                <!-- Add shipping rule form -->
                <form id="add_shipping_rule" role="form">
                    <input type="hidden" name="edit_sr_form_id" value="" id="edit_sr_form_id">
                    <!-- Rule name -->
                    <div class="en_sr_form_control">
                        <label for="en_sr_rule_name">Rule Name <span style="color: red;">*</span></label>
                        <input type="text" title="Rule Name" name="en_sr_rule_name" id="en_sr_rule_name" maxlength="50">
                        <span class="en_sr_err"></span>
                    </div>

                    <!-- Rule type -->
                    <div class="en_sr_form_control">
                        <label for="en_ups_small_rule_type">Type</label>
                        <select name="en_ups_small_rule_type" id="en_ups_small_rule_type" title="Type">
                          <option value="Hide Methods">Hide Methods</option>
                          <option value="Override Rates">Override Rates</option>
                          <option value="Restrict To State">Restrict To State</option>
                          <option value="Large Cart Settings">Large Cart Settings</option>
                        </select>
                        <span class="en_sr_err"></span>
                    </div>

                    <!-- Apply to -->
                    <div class="en_sr_form_control en_ups_small_sr_apply_to">
                        <label for="apply_to">Apply to:</label>
                        <div id="en_sr_apply_to_cart">
                          <div><p></p></div>
                          <input type="radio" name="apply_to" value="cart" checked title="Cart"> <span>Cart</span>
                        </div>
                        <div id="en_sr_apply_to_shipment">
                          <div><p></p></div>
                          <input type="radio" name="apply_to" value="shipment" title="Shipment"> <span>Shipment</span>
                        </div>
                        <span class="en_sr_err"></span>
                    </div>
                    
                    <!-- Override rates section -->
                    <div class="en_sr_override_rates">
                      <!-- Service name -->
                      <div class="en_sr_form_control">
                          <label for="en_sr_service_name">Service</label>
                          <select name="en_sr_service_name" id="en_sr_service_name" data-optional="1" title="Service">
                            <option hidden value="">Select service</option>
                            <?php 
                              $services = ['03' => 'Ground', '02' => '2nd Day Air', '59' => '2nd Day Air A.M', '13' => 'Next Day Air Saver', '01' => 'Next Day Air', '14' => 'Next Day Air Early', '12' => '3 Day Select', '11' => 'Standard', '08' => 'Expedited | UPS Worldwide Expedited', '65' => 'Express Saver | UPS Worldwide Saver', '07' => 'Express | UPS Worldwide Express', '54' => 'Express Plus | UPS Worldwide Express Plus', '92' => 'SurePost Less than 1LB', '93' => 'SurePost 1LB or greater', '94' => 'SurePost Bound Printed Matter', '95' => 'SurePost Media Mail', 'GFP' => 'Ground with Freight Pricing', 'usrg' => 'Simple Rate - Ground', 'usr3' => 'Simple Rate - 3-day Select', 'usr2' => 'Simple Rate - 2-day Air', 'usr1' => 'Simple Rate - Next Day Air Saver'];
                              
                              foreach ($services as $key => $value) {
                                  echo "<option value='" . esc_attr($key) . "'>" . esc_html('UPS ' . $value) . "</option>";
                              }
                            ?>
                          </select>
                          <span class="en_sr_err"></span>
                      </div>
  
                      <!-- Service rate -->
                      <div class="en_sr_form_control">
                          <label for="en_sr_service_rate">Service rate (e.g. 5.25)</label>
                          <input type="text" title="Service rate" name="en_sr_service_rate" id="en_sr_service_rate" data-optional="1" maxlength="10" data-optional="1">
                          <span class="en_sr_err"></span>
                      </div>
                    </div>

                    <!-- Restrict to state rule section -->
                    <div class="en_sr_restrict_to_state">
                      <!-- Filter by Country -->
                      <div class="en_sr_form_control">
                        <label for="en_ups_small_filter_by_country">
                           Countries
                        </label>
                        <select name="en_ups_small_sr_country" id="en_ups_small_sr_country" title="Countries">
                          <option value="US">US</option>
                          <option value="CA">CA</option>
                        </select>
                      </div>

                      <!-- Filter by State -->
                      <div class="en_sr_form_control">
                        <label for="en_ups_small_filter_by_state" id="en_ups_small_filter_by_state_label">
                           States/Provinces
                        </label>
                      </div>
                      <!-- US States list -->
                      <div class="en_sr_form_control en_ups_small_sr_us_states_list">
                        <select name="en_sr_states_list" id="en_sr_states_list" multiple="multiple" data-attribute="en_state_list_value" data-optional="1" class="chosen_select en_state_list_value" style="width: 100% !important;" title="States/Provinces">
                          <?php 
                            global $woocommerce;
                            $countries_obj = new WC_Countries();
                            $us_states = $countries_obj->get_states('US');

                            if (isset($us_states) && !empty($us_states)) {
                              foreach ($us_states as $key => $value) {
                                  echo "<option class='us_state' value='" . esc_attr($key) . "'>" . esc_html($value) . "</option>";
                              }
                          }
                        ?>
                        </select>
                        <span class="en_sr_err"></span>
                        <span class="descrption">
                          Only customers from these states/provinces will be presented with shipping rates for this provider.
                        </span>
                      </div>

                      <!-- CA States list -->
                      <div class="en_sr_form_control en_ups_small_sr_ca_states_list">
                        <select name="en_sr_states_list" id="en_sr_states_list" multiple="multiple" data-attribute="en_state_list_value" data-optional="1" class="chosen_select en_state_list_value" style="width: 100% !important;" title="States/Provinces">
                          <?php 
                            global $woocommerce;
                            $countries_obj = new WC_Countries();
                            $ca_states = $countries_obj->get_states('CA');

                            if (isset($ca_states) && !empty($ca_states)) {
                                foreach ($ca_states as $key => $value) {
                                    echo "<option class='ca_state' value='" . esc_attr($key) . "'>" . esc_html($value) . "</option>";
                                }
                            }
                        ?>
                        </select>
                        <span class="en_sr_err"></span>
                        <span class="descrption">
                          Only customers from these states/provinces will be presented with shipping rates for this provider.
                        </span>
                      </div>
                    </div>

                    <!-- Large cart settings section -->
                    <div class="en_ups_small_sr_large_cart_settings">
                      <div class="en_sr_form_control">
                        <p>
                          The packaging algorithm identifies the ideal packaging solution via an iterative process. If the Cart contains a large number of items, the time the packaging algorithm requires to identify the ideal packaging solution can exceed the window of time WordPress allows for shipping quotes to be returned. In these cases, the packaging algorithm needs to be bypassed. Use the settings below to specify your preferences for when the packaging algorithm is to be bypassed. Test the results to make sure results (shipping quotes) are returned when a large number of items is in the Cart. The number of boxes you define and the diversity of the items in your product catalog will influence the time the packaging algorithm requires. Therefore, your settings will differ from those of other merchants.
                        </p>
                      </div>

                      <div class="en_sr_form_control">
                        <label for="en_sr_max_items">Max items <span style="color: red;">*</span></label>
                        <input type="text" title="Max items" name="en_sr_max_items" id="en_sr_max_items" data-optional="1" maxlength="10">
                        <span class="en_sr_err"></span>
                        <p class="description">When the Cart contains more than this number of items, the packaging algorithm will be bypassed and rates will be calculated exclusively on the basis of weight.</p>
                      </div>

                      <div class="en_sr_form_control">
                        <label for="en_sr_max_weight_per_package">Max weight per package <span style="color: red;">*</span></label>
                        <input type="text" title="Max weight per package" name="en_sr_max_weight_per_package" id="en_sr_max_weight_per_package" data-optional="1" maxlength="10">
                        <span class="en_sr_err"></span>
                        <p class="description">Specify the maximum weight allowed per package when the packaging algorithm is bypassed. The maximum permitted value is 150 LB.</p>
                      </div>
                    </div>

                    <!-- Filters section -->
                    <div class="en_ups_small_sr_filters_section">
                      <!-- Filter name -->
                      <div class="en_sr_form_control">
                          <label for="en_sr_filter_name">Filter Name</label>
                          <input type="text" title="Filter Name" name="en_sr_filter_name" id="en_sr_filter_name" data-optional="1" maxlength="50">
                          <span class="en_sr_err"></span>
                      </div>

                      <!-- Filter by Weight -->
                      <div class="en_sr_form_control">
                        <div>
                          <label for="filter_by_weight">
                            <input type="checkbox" title="Filter by weight" name="filter_by_weight" id="filter_by_weight"> Filter by weight
                          </label>
                        </div>
                        <span class="en_sr_err"></span>
                      </div>
                      <div class="group_sr_form_control">
                        <div class="en_sr_form_control">
                          <label for="en_sr_weight_from">From</label>
                          <input type="text" title="From" name="en_sr_weight_from" id="en_sr_weight_from" data-optional="1" maxlength="10">
                          <span class="en_sr_err"></span>
                        </div>
                        <div class="en_sr_form_control">
                          <label for="en_sr_weight_to">To</label>
                          <input type="text" title="To" name="en_sr_weight_to" id="en_sr_weight_to" data-optional="1" maxlength="10">
                          <span class="en_sr_err"></span>
                        </div>
                      </div>
  
                      <!-- Filter by Price -->
                      <div class="en_sr_form_control">
                        <div>
                          <label for="en_sr_filter_price">
                            <input type="checkbox" title="Filter by price" name="en_sr_filter_price" id="en_sr_filter_price"> Filter by price
                          </label>
                        </div>
                        <span class="en_sr_err"></span>
                      </div>
                      <div class="group_sr_form_control">
                        <div class="en_sr_form_control">
                          <label for="en_sr_price_from">From</label>
                          <input type="text" title="From" name="en_sr_price_from" id="en_sr_price_from" data-optional="1" maxlength="20">
                          <span class="en_sr_err"></span>
                        </div>
                        <div class="en_sr_form_control">
                          <label for="en_sr_price_to">To</label>
                          <input type="text" title="To" name="en_sr_price_to" id="en_sr_price_to" data-optional="1" maxlength="20">
                          <span class="en_sr_err"></span>
                        </div>
                      </div>
                      
                      <!-- Filter by Quantity -->
                      <div class="en_sr_form_control">
                        <div>
                          <label for="filter_by_quantity">
                            <input type="checkbox" title="Filter by quantity" name="filter_by_quantity" id="filter_by_quantity"> Filter by quantity
                          </label>
                        </div>
                        <span class="en_sr_err"></span>
                      </div>
                      <div class="group_sr_form_control">
                        <div class="en_sr_form_control">
                          <label for="en_sr_quantity_from">From</label>
                          <input type="text" title="From" name="en_sr_quantity_from" id="en_sr_quantity_from" data-optional="1" maxlength="20">
                          <span class="en_sr_err"></span>
                        </div>
                        <div class="en_sr_form_control">
                          <label for="en_sr_quantity_to">To</label>
                          <input type="text" title="To" name="en_sr_quantity_to" id="en_sr_quantity_to" data-optional="1" maxlength="20">
                          <span class="en_sr_err"></span>
                        </div>
                      </div>
  
                      <!-- Filter by product tag -->
                      <div class="en_sr_form_control">
                        <div>
                          <label for="filter_by_product_tag">
                            <input type="checkbox" title="Filter by product tag" name="filter_by_product_tag" id="filter_by_product_tag"> Filter by product tag
                          </label>
                        </div>
                        <span class="en_sr_err"></span>
                      </div>
  
                      <div class="en_sr_form_control">
                        <select id="en_sr_product_tags_list" multiple="multiple" data-attribute="en_product_tag_filter_value"
                                name="en_product_tag_filter_value"
                                title="Filter by product tag"
                                data-optional="1"
                                class="chosen_select en_product_tag_filter_value"
                                style="width: 100% !important;"
                                >
                            <?php
                              $en_products_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
                              if (isset($en_products_tags) && !empty($en_products_tags)) {
                                  foreach ($en_products_tags as $key => $tag) {
                                      echo "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                                  }
                              }
                            ?>
                        </select>
                        <span class="en_sr_err"></span>
                      </div>
                    </div>

                    <!-- Available checkbox -->
                    <div class="en_sr_form_control">
                      <div>
                        <label for="en_sr_avialable">
                          <input type="checkbox" title="Available" name="en_sr_avialable" id="en_sr_avialable" checked> Available
                        </label>
                      </div>
                      <span class="en_sr_err"></span>
                    </div>

                    <!-- Form submit button -->
                    <div class="form-btns">
                        <input type="submit" name="en_wd_submit_warehouse" value="Save" class="save_warehouse_form save_shipping_rule_form">
                    </div>
                </form>
            </div>
        </div>
    </div>
