<?php

if (!class_exists('Eniture_EnSbsProperties')) {
    class Eniture_EnSbsProperties
    {
        public function __construct()
        {
            add_filter('en_fdo_package', [$this, 'en_fdo_sbs_properties'], 10, 2);
        }

        public function en_fdo_sbs_properties($item, $item_data)
        {
            $sbs_properties = [
                '_en_rot_ver' => 'vertical_rotation',
                '_en_own_pack' => 'own_package',
                '_en_multiple_packages' => 'multiple_packages'
            ];

            $id = $variant_id = '';
            extract($item);
            foreach ($sbs_properties as $property => $index) {
                $post_id = (isset($variant_id) && $variant_id > 0) ? $variant_id : $id;
                $item[$index] = get_post_meta($post_id, $property, true);
            }

            return $item;
        }
    }

    new Eniture_EnSbsProperties();
}
