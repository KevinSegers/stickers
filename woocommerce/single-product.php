<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$custom_sticker = false;
get_header( 'shop' );
while ( have_posts() ) : the_post();
    $custom_sticker = get_post_meta($post->ID, 'sticker_custom_text', true) == '1';
?>

	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		if (!$custom_sticker) {
            do_action('woocommerce_before_main_content');
        }
	?>

		<?php  ?>
			<?php
			if ($custom_sticker) {
        		wc_get_template_part( 'content', 'custom-product' );
    		} else {
                wc_get_template_part( 'content', 'single-product' );
			}
    		?>

	<?php
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		if (!$custom_sticker) {
			do_action( 'woocommerce_after_main_content' );
        }
	?>

	<?php
		/**
		 * woocommerce_sidebar hook.
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>
<?php endwhile; // end of the loop. ?>
<?php get_footer( 'shop' ); ?>
