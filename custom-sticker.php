<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 28/11/16
 * Time: 20:22
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
@require_once('customized/colors.php');

global $sticker_attributes_as_radio_buttons;
global $sticker_attributes_as_colors;
$sticker_attributes_as_radio_buttons = [
    'pa_alignment', 'pa_mirrored'
];
$sticker_attributes_as_colors = [
    'pa_color'
];
$sticker_attributes_as_fonts = [
    'pa_font'
];

if (!function_exists('is_custom_sticker')) {
    function is_custom_sticker($product_id) {
        if (!isset($product_id)) {
            return false;
        }
        return get_post_meta($product_id, 'sticker_custom_text', true) == '1';
    }
}

if (!function_exists('render_product_attributes_description')) {

    function render_product_attributes_description($html, $attribute)
    {
        // get the attribute_description
        global $wpdb;
        $attribute_name = str_replace('pa_', '', $attribute);
        $attribute_taxonomies_query = "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies where attribute_name = '" . $attribute_name . "' order by attribute_name ASC;";
        $attrs = $wpdb->get_results($attribute_taxonomies_query);
        if (sizeof($attrs) > 0 && !empty($attrs[0]->attribute_description)) {
            $html .= '<p class="attribute_description">' . $attrs[0]->attribute_description . '</p>';
        }
        return $html;
    }

}


if (!function_exists('render_sticker_attribute_as_radiobuttons')) {

    function render_sticker_attribute_as_radiobuttons($args = array())
    {
        $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
            'options' => false,
            'attribute' => false,
            'product' => false,
            'selected' => false,
            'name' => '',
            'id' => '',
            'class' => '',
            'show_option_none' => __('Choose an option', 'woocommerce')
        ));
        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }

        $html = '';
        $html = render_product_attributes_description($html, $attribute);

        if (!empty($options)) {
            $terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all'));

            foreach ($terms as $term) {
                if (in_array($term->slug, $options)) {
                    $html .= '<label><input type="radio" value="' . esc_attr($term->slug) . '" name="' . esc_attr($name) . '" id="' . (esc_attr($name) . '-' . esc_attr($term->slug)) . '" ' . checked(sanitize_title($args['selected']), $term->slug, false) . '>&nbsp;&nbsp;' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name)) . '</label>';
                }
            }
        }


        echo apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args);
    }

}

if (!function_exists('render_sticker_attribute_as_colors')) {

    function render_sticker_attribute_as_colors($args = array())
    {
        $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
            'options' => false,
            'attribute' => false,
            'product' => false,
            'selected' => false,
            'name' => '',
            'id' => '',
            'class' => '',
            'show_option_none' => __('Choose an option', 'woocommerce'),
            'prefix' => ''
        ));
        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }

        $html = '';
        $html = render_product_attributes_description($html, $attribute);

        $html .= '<ul class="custom-colors ' . $args['class'] . '">';
        if (!empty($options)) {
            $terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all'));
            $html .= render_color_items($terms, $options, $name, $args['selected'], $args['prefix']);
        }
        $html .= '</ul>';


        echo apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args);
    }

    function render_sticker_attribute_as_bg_colors($args = array())
    {
        $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
            'options' => false,
            'attribute' => false,
            'product' => false,
            'selected' => false,
            'name' => '',
            'id' => '',
            'class' => '',
            'show_option_none' => __('Choose an option', 'woocommerce'),
            'prefix' => ''
        ));
        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }

        $html = '';
        // $html = render_product_attributes_description($html, $attribute);
        $html = '<p class="attribute_description">De achtergrondkleur die je hier kan selecteren is alleen als keuzehulp en hoort niet bij je sticker. Het is enkel de uitgesneden tekst die kleeft. Deze wordt voorzien van applicatietape.</p>';

        $html .= '<ul class="custom-colors ' . $args['class'] . '">';
        if (!empty($options)) {
            $html .= render_bg_color_items($options, $name, $args['selected'], $args['prefix']);
        }
        $html .= '</ul>';


        echo apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args);
    }

}


if (!function_exists('render_color_items')) {

    function render_color_items($terms, $options, $name, $selected, $prefix = '')
    {
        $html = '';
        foreach ($terms as $term) {
            if (in_array($term->slug, $options)) {
                $color = $term->name;
                $esc_color = esc_attr($color);
                $slug = $term->slug;
                $esc_slug = esc_attr($slug);
                $html_name = esc_attr($prefix . $name);
                $html_id = esc_attr($prefix . $name . '-' . $slug);
                $checked_attr = checked(sanitize_title($selected), $slug, false);
                $color_hex = Stickers\CustomSticker\color_name_to_hex($color);
                $html .= '<li>'
                    . '<input type="radio" data-color-hex="' . $color_hex . '" data-color="' . $esc_color . '" value="' . $esc_slug . '" name="' . $html_name . '" id="' . $html_id . '" ' . $checked_attr . ' />'
                    . '<label style="background-color:' . $color_hex . '; color:#000000" for="' . $html_id . '"><span>' . esc_html($color) . '</span></label>'
                    . '</li>';
            }
        }
        return $html;
    }

    function render_bg_color_items($options, $name, $selected, $prefix = '')
    {
        $html = '';
        foreach ($options as $option) {
            $color = $option;
            $esc_color = esc_attr($color);
            $slug = strtolower($option);
            $esc_slug = esc_attr($slug);
            $html_name = esc_attr($prefix . $name);
            $html_id = esc_attr($prefix . $name . '-' . $slug);
            $html .= '<li>'
                . '<input type="radio" data-color="' . $esc_color . '" value="' . $esc_slug . '" name="' . $html_name . '" id="' . $html_id . '" />'
                . '<label style="background-color:' . $esc_color . '; color:#000000" for="' . $html_id . '"><span>' . esc_html($color) . '</span></label>'
                . '</li>';
        }
        return $html;
    }

}


if (!function_exists('render_sticker_attribute_as_fonts')) {

    function render_sticker_attribute_as_fonts($args = array())
    {
        $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
            'options' => false,
            'attribute' => false,
            'product' => false,
            'selected' => false,
            'name' => '',
            'id' => '',
            'class' => '',
            'show_option_none' => __('Choose an option', 'woocommerce')
        ));

        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
        $id = $args['id'] ? $args['id'] : sanitize_title($attribute);
        $class = $args['class'];
        $show_option_none = $args['show_option_none'] ? true : false;
        $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }

        $html = '';
        $html = render_product_attributes_description($html, $attribute);

        $html .= '<select id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" name="' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
        $html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

        if (!empty($options)) {
            if ($product && taxonomy_exists($attribute)) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all'));

                foreach ($terms as $term) {
                    if (in_array($term->slug, $options)) {
                        $html .= '<option style="font-family: ft-' . esc_attr($term->slug) . ';" data-font-family="ft-' . esc_attr($term->slug) . '" value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name)) . '</option>';
                    }
                }
            } else {
                foreach ($options as $option) {
                    // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                    $selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);
                    $html .= '<option style="font-family: ft-' . esc_attr($option) . ';" data-font-family="ft-' . esc_attr($option) . '"  value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
                }
            }
        }

        $html .= '</select>';


        echo apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args);
    }

}


if (!function_exists('render_sticker_dropdown_variation_attribute_options')) {

    function render_sticker_dropdown_variation_attribute_options( $args = array() ) {
        $args = wp_parse_args( apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ), array(
            'options'          => false,
            'attribute'        => false,
            'product'          => false,
            'selected' 	       => false,
            'name'             => '',
            'id'               => '',
            'class'            => '',
            'show_option_none' => __( 'Choose an option', 'woocommerce' )
        ) );

        $options          = $args['options'];
        $product          = $args['product'];
        $attribute        = $args['attribute'];
        $name             = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
        $id               = $args['id'] ? $args['id'] : sanitize_title( $attribute );
        $class            = $args['class'];
        $show_option_none      = $args['show_option_none'] ? true : false;
        $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

        if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
            $attributes = $product->get_variation_attributes();
            $options    = $attributes[ $attribute ];
        }

        $html = '';
        $html = render_product_attributes_description($html, $attribute);
        if ($name == 'attribute_grootte') {
            $html = '<div class="addon-description"><p>De grootte van de sticker is telkens de afmeting van de langste zijde. Dit kan dus de breedte òf de hoogte zijn, afhankelijk van de vorm van de sticker.</p></div>';
        }
        if ($name == 'attribute_afmetingen-streamers') {
            $html = '<div class="addon-description"><p>Afmetingen van de sticker: hoogte x breedte.</p></div>';
        }

        $html .= '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
        $html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

        if ( ! empty( $options ) ) {
            if ( $product && taxonomy_exists( $attribute ) ) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

                foreach ( $terms as $term ) {
                    if ( in_array( $term->slug, $options ) ) {
                        $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
                    }
                }
            } else {
                foreach ( $options as $option ) {
                    // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                    $selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
                    $html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
                }
            }
        }

        $html .= '</select>';

        

        echo apply_filters( 'woocommerce_dropdown_variation_attribute_options_html', $html, $args );
    }

}
