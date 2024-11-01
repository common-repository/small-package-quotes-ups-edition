<?php

/**
 * transit days
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Eniture_UpsSmallTransitDays")) {

    class Eniture_UpsSmallTransitDays
    {
        // Exclude ground service
        public function ups_enable_disable_ups_ground($result)
        {
            $transit_day_type = get_option('restrict_calendar_transit_small_packages_ups'); //get value of check box to see which one is checked
            $days_to_restrict = get_option('restrict_days_transit_package_ups_small');
            $action = get_option("ups_small_package");
            $package = $transit_days = apply_filters('eniture_ups_small_quotes_plans_suscription_and_features', 'transit_days');
            $package = (isset($package) && ($package == 1 || $package == 2)) ? TRUE : FALSE;
            $ups_rate = isset($result->q) ? $result->q : [];
            $ups_rate = empty($ups_rate) && isset($result->ups_rate) ? $result->ups_rate : $ups_rate;
            if ($package && strlen(trim($days_to_restrict)) > 0 && strlen($transit_day_type) > 0) {
                foreach ($ups_rate as $key => $service) {
                    if ((isset($service->Service, $service->Service->Code) && $service->Service->Code == "03")
                    || (isset($service->RatedShipment, $service->RatedShipment->Service, $service->RatedShipment->Service->Code) && $service->RatedShipment->Service->Code == "03")) {
                        $estimated_arrival_days = (isset($service->$transit_day_type)) ? $service->$transit_day_type : 0;
                        if ($estimated_arrival_days > $days_to_restrict) {
                            $ups_services = (array)$result->ups_services;
                            $ups_services = array_flip($ups_services);
                            $index = (isset($ups_services['UPS Ground'])) ? $ups_services['UPS Ground'] : "";
                            if(isset($result->q->$index)){
                                unset($result->q->$index);
                            }else if(isset($result->ups_rate->$index)){
                                unset($result->ups_rate->$index);
                            }
                        }
                    }
                }
            }
            return $result;
        }
    }
}
        

