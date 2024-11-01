<?php

if (!defined("ABSPATH")) {
    exit();
}

if (!class_exists("Eniture_Ups_Small_Auto_Residential_Detection")) {

    class Eniture_Ups_Small_Auto_Residential_Detection {

        public $label_sfx_arr;

        public function __construct() {
            $this->label_sfx_arr = array();
        }

        public function filter_label_sufex_array_ups_small($result) {
            (isset($result->residentialStatus) && ($result->residentialStatus == "r")) ? array_push($this->label_sfx_arr, "R") : "";
            (isset($result->liftGateStatus) && ($result->liftGateStatus == "l")) ? array_push($this->label_sfx_arr, "L") : "";
            return array_unique($this->label_sfx_arr);
        }

    }

    new Eniture_Ups_Small_Auto_Residential_Detection();
}

