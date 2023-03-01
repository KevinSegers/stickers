<?php
global $product;
global $sticker_attributes_as_radio_buttons;
global $sticker_attributes_as_colors;
global $sticker_attributes_as_fonts;
?>
<div class="js-product-attribute--bg-colors">
    <p class="t-xs mb-tiny t-bold"><label for="<?php echo sanitize_title( $attribute_name ); ?>">Achtergrondkleur</label></p>
    <div class="form-group form-group--checkbox js-sticker-transparent">
        <input type="checkbox" checked name="js-transparent" id="js-transparent" value="transparent" />
        <label for="js-transparent">Transparant</label>
    </div>

    <?php
    $new_options = ['White', 'WhiteSmoke', 'LightGrey', 'DarkGray', 'Gray', 'Black', 'Green', 'LimeGreen',
        'Lime', 'MediumSpringGreen', 'Turquoise', 'PaleTurquoise', 'DeepSkyBlue', 'DodgerBlue', 'Blue',
        'DarkBlue', 'DarkSlateBlue', 'MediumSlateBlue', 'Orchid', 'HotPink', 'LightPink', 'LightSalmon',
        'Salmon', 'Tomato', 'Red', 'Crimson', 'FireBrick', 'DarkRed', 'SaddleBrown', 'Sienna', 'Chocolate',
        'Darkorange', 'Orange', 'Gold', 'Yellow', 'LemonChiffon'];
    $selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
    $render_args = array(
        'options' => $new_options,
        'attribute' => $attribute_name,
        'product' => $product,
        'selected' => $selected,
        'show_option_none' => false,
        'class' => 'js-sticker-bg-colors',
        'prefix' => 'bg'
    );
    render_sticker_attribute_as_bg_colors($render_args);
    ?>
</div>