<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Eniture_UpsSpqLogsPackaging')) {
    class Eniture_UpsSpqLogsPackaging
    {
        public $package_count = 0;
        public $item_per_shipment;
        public $unpacked_wt_dims;
        public $bin_response;
        public $item_details;

        public function en_get_bins_packed($bin_response, $products_name_arr)
        {
            $this->bin_response = $bin_response;
            $total_count = 0;
            $shipment = '';
            $unpacked_shipment = '';
            $bins_array = array();

            $shipment_array = (array)$this->bin_response;
            $shipment_origin = array_keys($shipment_array);
            $bins_array = $this->arrange_bins_response_order($this->bin_response);

            // get count of each shipment
            if (isset($bins_array['1'])) {
                $this->packed_item_shipment($shipment_array, $shipment_origin);
            }

            if (isset($bins_array['3'])) {
                $this->unpacked_wt_dims = count($bins_array['3']);
                $this->unpacked_wt_dims = isset($this->unpacked_wt_dims) && ($this->unpacked_wt_dims > 1) ? 'multiple' : 'single';
            }
            
            $shipment = isset($shipment_origin) && (count($shipment_origin) == 1) ? 'single' : 'multiple';

            // check shipment of unpacked items who have dims
            if (isset($bins_array['2']) && count($bins_array['2']) > 1 && ($bins_array['2']['0']->bin_data->w != 0)) {
                $dims = NULL;

                foreach ($bins_array['2'] as $value) {
                    if ($dims == NULL) {
                        $dims = $value->bin_data->w;
                    } else {
                        if ($value->bin_data->w == $dims && $shipment != 'multiple') {
                            $unpacked_shipment = 'single';
                        } else {
                            $unpacked_shipment = 'multiple';
                        }
                    }
                }
            }

            foreach ($this->bin_response as $zip => $details) {
                if (isset($details->home_ground_pricing->bins_packed)) {
                    $details = $details->home_ground_pricing;
                } elseif (isset($details->weight_based_pricing->bins_packed)) {
                    $details = $details->weight_based_pricing;
                } elseif (isset($details->one_rate_pricing->bins_packed)) {
                    $details = $details->one_rate_pricing;
                }

                (isset($details->bins_packed) && !empty($details->bins_packed)) ? $total_count = $total_count + $this->getBinDataCount($details->bins_packed) : '';
            }

            foreach ($bins_array as $zip => $details) {
                $bins_array[$zip] = $this->appendProductTitle($details, $products_name_arr);
            }

            $data = [
                'bins_array'        => $bins_array,
                'unpacked_shipment' => $unpacked_shipment,
                'shipment'          => $shipment,
                'total_count'       => $total_count,
                'item_per_shipment' => $this->item_per_shipment
            ];

            return $data;
        }

        public function arrange_bins_response_order($bins_response)
        {
            $sorted_bins_resp = array();
            foreach ($bins_response as $zip => $details) {

                if (isset($details->home_ground_pricing->bins_packed)) {
                    $details = $details->home_ground_pricing;
                } elseif (isset($details->weight_based_pricing->bins_packed)) {
                    $details = $details->weight_based_pricing;
                } elseif (isset($details->one_rate_pricing->bins_packed)) {
                    $details = $details->one_rate_pricing;
                }

                if (isset($details->bins_packed) && !empty($details->bins_packed)) {
                    foreach ($details->bins_packed as $bins_detail) {
                        if ($bins_detail->bin_data->w == 0 && $bins_detail->bin_data->type == 'item') {
                            $sorted_bins_resp['3'][] = $bins_detail;
                        } else {
                            if (isset($bins_detail->bin_data->type) && $bins_detail->bin_data->type == 'item') {
                                $sorted_bins_resp['2'][] = $bins_detail;
                            } else {
                                $sorted_bins_resp['1'][] = $bins_detail;
                            }
                        }
                    }
                }
            }

            $keys = array();
            isset($sorted_bins_resp['1']) && !empty($sorted_bins_resp['1']) ? $keys['1'] = $sorted_bins_resp['1'] : array();
            isset($sorted_bins_resp['2']) && !empty($sorted_bins_resp['2']) ? $keys['2'] = $sorted_bins_resp['2'] : array();
            isset($sorted_bins_resp['3']) && !empty($sorted_bins_resp['3']) ? $keys['3'] = $sorted_bins_resp['3'] : array();

            return $keys;
        }

        public function packed_item_shipment($shipment_array)
        {
            $count = 0;
            $index = 't';
            $index_count = 0;

            foreach ($shipment_array as $bin_detail) {
                $bin_data_w = (isset($bin_detail->bins_packed[$index_count]->bin_data->w)) ? $bin_detail->bins_packed[$index_count]->bin_data->w : 0;
                
                if (empty($bin_detail->bins_packed[$index_count]->bin_data->type) && ($bin_data_w != 0)) {
                    $index = $index . $index_count;
                    $this->item_per_shipment[$index] = count($bin_detail->bins_packed);
                    $index_count = $index_count + 1;
                    $index = 't';
                }
         
                $count = $count + 1;
            }
        }

        public function getBinDataCount($details)
        {
            $binDataCount = 0;
            foreach ($details as $key => $binDetails) {
                $bin_data_count = isset($binDetails->bin_data) && !empty($binDetails->bin_data) && !isset($binDetails->bin_data->w) ? count((array)$binDetails->bin_data) : 1;
                $binDataCount = $binDataCount + $bin_data_count;
            }
         
            $this->package_count = $this->package_count + $binDataCount;
         
            return $binDataCount;
        }

        public function appendProductTitle($bin_details, $products_name_arr)
        {
            /* Items packed details */
            foreach ($bin_details as $k => $value) {
                if (isset($value->bin_data->id) && isset($value->items)) {
                    $bin_details[$k]->bin_data->box_title = get_the_title($value->bin_data->id);

                    foreach ($value->items as $key => $item_details) {
                        $product_title = !empty($products_name_arr[$item_details->id]) ? $products_name_arr[$item_details->id] : '';
                        $bin_details[$k]->items[$key]->product_name = $product_title;
                    }
                }
            }

            return $bin_details;
        }
    }
}