<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.1
 */

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $post, $product;
$custom_sticker = get_post_meta($post->ID, 'sticker_custom_text', true) == '1';
if ($custom_sticker) :
?>

<?php else : ?>
<div class="images">
	<?php
		if ( has_post_thumbnail() ) {
			$attachment_count = count( $product->get_gallery_image_ids() );
			$gallery          = $attachment_count > 0 ? '[product-gallery]' : '';
			$props            = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
			$thumbnail_url = get_the_post_thumbnail_url($post->ID);
			$extra_classes = '';
			if (stripos(strrev($thumbnail_url), strrev('.svg')) === 0) {
			    $extra_classes = 'style-svg';
            }
			$image            = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
				'title'	 => $props['title'],
				'alt'    => $props['alt'],
                'class' => $extra_classes
			) );
			echo apply_filters(
				'woocommerce_single_product_image_html',
				sprintf(
					// '<div class="images__inner">%s</div>',
					'<div class="images__inner"><a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto%s">%s</a></div>',
					esc_url( $props['url'] ),
					esc_attr( $props['caption'] ),
					$gallery,
					$image
				),
				$post->ID
			);
		} else {
			echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $post->ID );
		}

		do_action( 'woocommerce_product_thumbnails' );

		if ( has_term( 'Baby aan boord', 'product_cat' ) || has_term( 'Hond aan boord', 'product_cat' ) ) {
	     echo '<div class="mb-small t-small">Je krijgt eerst het ontwerp per e-mail (nà ontvangst van je betaling). Na je goedkeuring maken we dan de sticker.</div>';
	    }
	?>
</div>
<?php endif;