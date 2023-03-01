<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.5
 */

defined( 'ABSPATH' ) || exit;

global $product;
global $sticker_attributes_as_radio_buttons;
global $sticker_attributes_as_colors;
global $sticker_attributes_as_fonts;

$attribute_keys = array_keys( $attributes );
$custom_sticker = get_post_meta($product->get_id(), 'sticker_custom_text', true) == '1';
$include_sticker_bg = get_post_meta($product->get_id(), 'sticker_include_bg', true) == '1';

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="js-sticker-form variations_form cart" method="post" data-full-custom-sticker="<?php echo ($custom_sticker ? 'yes' : 'no'); ?>" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ) ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>
    <div class="custom-product-fields">
	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
	<?php else : ?>
        <?php do_action( 'stickers_before_attributes' ); ?>
        <?php /*
        WARNING: .variations is necessary to update selects and price products correctly through WooCommerce JS.
        Don't wrap addon inputs within this class and don't put product attribute inputs outside of it
        */ ?>
        <div class="custom-product-fields__block custom-product-options variations">

            <?php
            foreach ( $attributes as $attribute_name => $options ) {
                $selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
                wc_get_template('template-parts/sticker-attribute.php', array(
                    'attribute_name' => $attribute_name,
                    'options' => $options
                ));
                // Include background color "mock" attribute since it's not actually a part of the product.
                if ($attribute_name == 'pa_color' && $include_sticker_bg) {
                    wc_get_template('template-parts/sticker-bg-color-attribute.php', array(
                        'attribute_name' => $attribute_name,
                        'options' => $options
                    ));
                }
            }
            ?>
		
    		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

    		<div class="single_variation_wrap">
                <?php do_action( 'stickers-post-attributes', $product->get_id() ); ?>
                
    			<?php
    				/**
    				 * woocommerce_before_single_variation Hook.
    				 */
    				do_action( 'woocommerce_before_single_variation' );

    				/**
    				 * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
    				 * @since 2.4.0
    				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
    				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
    				 */
    				do_action( 'woocommerce_single_variation' );

    				/**
    				 * woocommerce_after_single_variation Hook.
    				 */
    				do_action( 'woocommerce_after_single_variation' );
    			?>
    		</div>
        </div>
        <?php if ($custom_sticker): ?>
            <table>
                <tbody>
                <tr>
                    <th>Lettertype:</th>
                    <td class="js-custom-sticker-output-font"></td>
                </tr>
                <tr>
                    <th>Uitlijnen:</th>
                    <td class="js-custom-sticker-output-alignment"></td>
                </tr>
                <tr>
                    <th>Spiegelen:</th>
                    <td class="js-custom-sticker-output-mirrored"></td>
                </tr>
                <tr>
                    <th>Kapitaalhoogte:</th>
                    <td class="js-custom-sticker-output-cap-height"></td>
                </tr>
                <tr>
                    <th>Hoogte:</th>
                    <td class="js-custom-sticker-output-height"></td>
                </tr>
                <tr>
                    <th>Breedte:</th>
                    <td class="js-custom-sticker-output-width"></td>
                </tr>
                <tr>
                    <th>Kleur:</th>
                    <td class="js-custom-sticker-output-color"></td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
    </div>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
