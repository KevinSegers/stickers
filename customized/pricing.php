<?php

namespace Stickers\CustomSticker {
    define('CUSTOMIZED_STICKER_INCLUDE', 1);
    @require_once('preview.php');

    const PRICING_CLASS_LTE_40 = 'x <= 40';
    const PRICING_CLASS_LTE_75 = 'x <= 75';
    const PRICING_CLASS_GT_75 = 'x > 75';

    const PRICING = array(
        'BE' => array(
            PRICING_CLASS_LTE_40 => 1.53,
            PRICING_CLASS_LTE_75 => 2.65,
            PRICING_CLASS_GT_75 => 4.92
        ),
        'NL' => array(
            PRICING_CLASS_LTE_40 => 1.82,
            PRICING_CLASS_LTE_75 => 4.09,
            PRICING_CLASS_GT_75 => 7.93
        )
    );

    function calculate_cost($country, $font_slug, $desired_cap_height, $desired_width, $text) {
        $text = trim($text);
        $size = calculate_max_size_for_shipping($font_slug, $desired_cap_height, $desired_width, $text);
        $class = PRICING_CLASS_GT_75;
        if ($size <= 400) {
            $class = PRICING_CLASS_LTE_40;
        } elseif ($size <= 750) {
            $class = PRICING_CLASS_LTE_75;
        } else {
            $class = PRICING_CLASS_GT_75;
        }
        return PRICING[$country][$class];
    }

    function calculate_max_size_for_shipping($font_slug, $desired_cap_height, $desired_width, $text) {
        $dimensions = calculate_shipping_dimensions($font_slug, $desired_cap_height, $desired_width, $text);
        return max($dimensions['width'], $dimensions['height']);
    }

    function calculate_shipping_dimensions($font_slug, $desired_cap_height, $desired_width, $text) {
        $font_details = get_font_details_from_slug($font_slug);
        $ttf_path = $font_details['ttf_path'];
        if (isset($desired_cap_height) && $desired_cap_height > 0) {
            $dimensions = calculate_dimensions_from_cap_height($desired_cap_height, $text, $ttf_path);
        } else {
            $dimensions = calculate_dimensions_from_width($desired_width, $text, $ttf_path);
        }
        return $dimensions;
    }

}
