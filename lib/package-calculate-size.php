<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author alignpx
 */
    if( !class_exists( 'Eniture_Plugin_Calculate_Size' ) ){
        
        class Eniture_Plugin_Calculate_Size {

            /*extranal lib,classes*/
            protected $virtualBoxObj;
            protected $db;
            protected $shop;


            /*function controls, even logging*/
            public $newRequest = true;
            public $defaultMaxWeightSmall = '150';


            /*extranal lib,classes*/
            public $lineItems;
            public $boxItems;
            public $bins = array();
            public $bin3D = array();

            public $shipmentGroups = array();
            public $requestKey;
            public $totalCartWeight;
            public $totalCartVolume;
            public $packagSize = array();
            public $numOfPackgs = array();

            public $lengthSum;
            public $widthSum;
            public $heightSum;
            public $lengthArr;
            public $widthArr;
            public $heightArr;

            function set_line_items( $line_item ) {
                $this->lineItems = $line_item;
                return $this->wwe_smpkg_box_items();
            }

           function wwe_smpkg_box_items(){

                if(in_array($this->requestKey, $this->shipmentGroups))
                    return;// do nothing if data is already set

                $this->shipmentGroups[] = $this->requestKey;
                foreach ($this->lineItems as $key => $sItem) {

                    /*whole shipment params*/
                    $boxingParams = isset($sItem['additional_settings']['boxing'])?$sItem['additional_settings']['boxing']:array();
                    $this->totalCartWeight = $this->totalCartWeight+($sItem['productQty'] * $sItem['productWeight']);
                    $this->lineItems[$key]['volume'] = $sItem['productQty']*$sItem['productLength']*$sItem['productWidth']*$sItem['productHeight'];
                    $this->totalCartVolume = $this->totalCartVolume+$this->lineItems[$key]['volume'];

                    /*item params shipment level*/
                    $this->lengthSum = $this->lengthSum + ($sItem['productQty'] * $sItem['productLength']);
                    $this->widthSum = $this->widthSum + ($sItem['productQty'] * $sItem['productWidth']);
                    $this->heightSum = $this->heightSum + ($sItem['productQty'] * $sItem['productHeight']);
                    $this->lengthArr[] = $sItem['productLength'];
                    $this->widthArr[] = $sItem['productWidth'];
                    $this->heightArr[] = $sItem['productHeight'];

                    /*line item params*/
                    $this->boxItems[$key]['w'] = $sItem['productWidth']; 
                    $this->boxItems[$key]['h'] = $sItem['productHeight'];
                    $this->boxItems[$key]['d'] = $sItem['productLength'];  
                    $this->boxItems[$key]['q'] = $sItem['productQty'];
                    $this->boxItems[$key]['vr'] = isset($boxingParams['allow_v_rotate'])?$boxingParams['allow_v_rotate']:'';
                    $this->boxItems[$key]['wg'] = $sItem['productWeight'];
                    $this->boxItems[$key]['id'] = $sItem['productId'];
                }


                $this->packagSize = $this->calculatePkgSize($this->lengthSum, $this->lengthArr, $this->widthSum, $this->widthArr, $this->heightSum, $this->heightArr);
                $this->newRequest = false;

                return $this->packagSize;

           }
            public function calculatePkgSize($calLength, $cartLength, $calwidth, $cartWidth, $calheight, $cartHeight) {

                // shipment level dimensional weight 
                $iteration = array();
                $iteration[1] = ceil($calLength) * ceil(max($cartWidth)) * ceil(max($cartHeight));
                $iteration[2] = ceil(max($cartLength)) * ceil(max($cartWidth)) * ceil($calheight);
                $iteration[3] = ceil(max($cartLength)) * ceil($calwidth) * ceil(max($cartHeight));
                // Get minimum dimension

                $dimensions = min($iteration);
                $min_iteration = array_keys($iteration, $dimensions);
                $min_iteration = $min_iteration[0];

                if ($min_iteration == 1) {
                    $box_lenght = ceil(max($cartLength));
                    $box_width = ceil(max($cartWidth));
                    $box_height = ceil($calheight);
                }
                if ($min_iteration == 2) {
                    $box_lenght = ceil($calLength);
                    $box_width = ceil(max($cartWidth));
                    $box_height = ceil(max($cartHeight));
                }
                if ($min_iteration == 3) {
                    $box_lenght = ceil(max($cartLength));
                    $box_width = ceil($calwidth);
                    $box_height = ceil(max($cartHeight));
                }


                $diminsion_size = array($box_lenght, $box_width, $box_height);
                rsort($diminsion_size);
                $response['size'] = $diminsion_size[0] + ((2 * $diminsion_size[1]) + (2 * $diminsion_size[2]));
                $response['diminsion_size'] = $diminsion_size;
                
                if( $this->lengthSum > 108 || $this->widthSum > 108 || $this->heightSum > 108 ){
                    $response['ltl_product'] = 'LTL';
                }

                return $response;
            }
        }
    }