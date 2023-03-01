<?php
global $product;
global $sticker_attributes_as_radio_buttons;
global $sticker_attributes_as_colors;
global $sticker_attributes_as_fonts;
?>
<div class="js-product-attribute--<?php echo esc_attr($attribute_name); ?>">
    <p class="t-xs mb-tiny t-bold">
        <label for="<?php echo sanitize_title( $attribute_name ); ?>">
            <?php echo wc_attribute_label( $attribute_name ); ?><?php if ($attribute_name == 'pa_color'): ?>: <span class="js-custom-sticker-output-color"></span><?php endif; ?>
        </label>
    </p>
    <?php
    $selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) : $product->get_variation_default_attribute( $attribute_name );
    $render_args = array(
        'options' => $options,
        'attribute' => $attribute_name,
        'product' => $product,
        'selected' => $selected,
        'show_option_none' => false
    );
    if (in_array($attribute_name, $sticker_attributes_as_radio_buttons)) {
        render_sticker_attribute_as_radiobuttons($render_args);
    } elseif (in_array($attribute_name, $sticker_attributes_as_colors)) {
        $render_args['class'] = 'js-sticker-fg-colors';
        render_sticker_attribute_as_colors($render_args);
    } elseif (in_array($attribute_name, $sticker_attributes_as_fonts)) {
        render_sticker_attribute_as_fonts($render_args);
    } else {
        render_sticker_dropdown_variation_attribute_options($render_args);
    }
    ?>
</div>
