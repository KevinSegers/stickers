<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
require_once('custom-sticker.php');
require_once('customized/pricing.php');

add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );
/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
    wp_dequeue_style( 'storefront-icons' );
    wp_dequeue_style( 'ewd-ufaq-style' );
    wp_dequeue_style( 'contact-form-7' );
    wp_dequeue_style( 'woocommerce-addons-css' );
    wp_deregister_script( 'storefront-sticky-add-to-cart' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */

add_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );


// change default css path
add_action( 'wp_enqueue_scripts', 'enqueue_theme_css' );
function enqueue_theme_css(){
    wp_dequeue_style('storefront-child-style');
    wp_enqueue_style(
        'storefront-child-style',
        get_stylesheet_directory_uri() . '/assets/css/style.css'
    );
    wp_enqueue_script(
        'stickers-js',
        get_stylesheet_directory_uri() . '/assets/js/main-min.js'
    );
    if (is_product() || is_page_template('template-custom-sticker.php')) {
        wp_enqueue_style(
            'personalized-stickers-style',
            get_stylesheet_directory_uri() . '/assets/css/fonts-personalization.css'
        );
        wp_enqueue_script(
            'svg-js',
            get_stylesheet_directory_uri() . '/assets/js/svg.min.js'
        );
        wp_enqueue_script(
            'url-search-params',
            get_stylesheet_directory_uri() . '/assets/js/url-search-params.js'
        );
        wp_enqueue_script(
            'custom-sticker-js',
            get_stylesheet_directory_uri() . '/assets/js/custom-sticker.js'
        );
    }
}


// enable lightbox
add_action( 'wp_enqueue_scripts', 'frontend_scripts_include_lightbox' );

function frontend_scripts_include_lightbox() {
  global $woocommerce;
  $suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
  $lightbox_en = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;

  if ( $lightbox_en ) {
    wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
    wp_enqueue_script( 'prettyPhoto-init', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
    wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
  }
}


// remove Sticky Add to Cart
if ( ! function_exists( 'storefront_sticky_single_add_to_cart' ) ) {
    function storefront_sticky_single_add_to_cart() {
        // cut the crap
    }
}


// homepage categories block
if ( ! function_exists( 'storefront_product_categories' ) ) {
    /**
     * Display Product Categories
     * Hooked into the `homepage` action in the homepage template
     *
     * @since  1.0.0
     * @param array $args the product section args.
     * @return void
     */
    function storefront_product_categories( $args ) {

        if ( storefront_is_woocommerce_activated() ) {

            $args = apply_filters( 'storefront_product_categories_args', array(
                'limit'             => 18,
                'columns'           => 6,
                'child_categories'  => 0,
                'orderby'           => 'menu_order',
                // 'title'             => __( 'Al onze stickers', 'storefront' ),
                // 'subtitle'          => __( 'Baby aan boord stickers, bloemenstickers, hondenstickers, paardenstickers, smileys, sportstickers, bumperstickers, ... .' ),
            ) );

            echo '<section class="storefront-product-section storefront-product-categories pb-large" aria-label="Stickers Categorieën">';

            do_action( 'storefront_homepage_before_product_categories' );

            // echo '<h2 class="t-m t-uppercase t-bold mb-small">' . wp_kses_post( $args['title'] ) . '</h2>';
            // echo '<p class="t-s mb-small">' . wp_kses_post( $args['subtitle'] ) . '</p>';

            do_action( 'storefront_homepage_after_product_categories_title' );

            echo storefront_do_shortcode( 'product_categories', array(
                'number'  => intval( $args['limit'] ),
                'columns' => intval( $args['columns'] ),
                'orderby' => esc_attr( $args['orderby'] ),
                'parent'  => esc_attr( $args['child_categories'] ),
            ) );

            do_action( 'storefront_homepage_after_product_categories' );

            echo '</section>';
        }
    }
}

// homepage new products block
if ( ! function_exists( 'storefront_recent_products' ) ) {
    /**
     * Display Recent Products
     * Hooked into the `homepage` action in the homepage template
     *
     * @since  1.0.0
     * @param array $args the product section args.
     * @return void
     */
    function storefront_recent_products( $args ) {

        if ( storefront_is_woocommerce_activated() ) {

            $args = apply_filters( 'storefront_recent_products_args', array(
                'limit'             => 6,
                'columns'           => 6,
                'title'             => __( 'Nieuwe stickers', 'storefront' ),
                'subtitle'          => __( 'Onze meest recente stickers in één oogopslag.', 'storefront' ),
                'link'              => __( 'Bekijk hier al onze nieuwe&nbsp;stickers&nbsp;&rsaquo;', 'storefront' )
            ) );

            echo '<section class="storefront-product-section storefront-recent-products mb-large bg-grey-lightest" aria-label="Recent Products"><div class="row">';

            do_action( 'storefront_homepage_before_recent_products' );

            echo '<div class="product-section__header bg-white"><h2 class="t-s t-green t-uppercase t-bold mb-small">' . wp_kses_post( $args['title'] ) . '</h2><p class="t-xs t-grey-darkest">' . wp_kses_post( $args['subtitle'] ) . '</p><p class="t-xs t-semibold t-grey-darkest"><a href="/stickers/?orderby=date">' . wp_kses_post( $args['link'] ) . '</a></p></div>';

            do_action( 'storefront_homepage_after_recent_products_title' );

            echo storefront_do_shortcode( 'recent_products', array(
                'per_page' => intval( $args['limit'] ),
                'columns'  => intval( $args['columns'] ),
            ) );

            do_action( 'storefront_homepage_after_recent_products' );

            echo '</div></section>';
        }
    }
}

// homepage best sellers products block
if ( ! function_exists( 'storefront_best_selling_products' ) ) {
    /**
     * Display Best Selling Products
     * Hooked into the `homepage` action in the homepage template
     *
     * @since 2.0.0
     * @param array $args the product section args.
     * @return void
     */
    function storefront_best_selling_products( $args ) {
        if ( storefront_is_woocommerce_activated() ) {
            $args = apply_filters( 'storefront_best_selling_products_args', array(
                'limit'   => 4,
                'columns' => 4,
                'title'   => esc_attr__( 'Best Sellers', 'storefront' ),
            ) );
            echo '<section class="storefront-product-section storefront-best-selling-products mb-large bg-grey-lightest" aria-label="Bestsellers">';
            if (is_front_page()){
                echo '<div class="row">';
            }
            do_action( 'storefront_homepage_before_best_selling_products' );
            echo '<h2 class="section-title">' . wp_kses_post( $args['title'] ) . '</h2>';
            do_action( 'storefront_homepage_after_best_selling_products_title' );
            echo storefront_do_shortcode( 'best_selling_products', array(
                'per_page' => intval( $args['limit'] ),
                'columns'  => intval( $args['columns'] ),
            ) );
            do_action( 'storefront_homepage_after_best_selling_products' );
            if (is_front_page()){
                echo '</div>';
            }
            echo '</section>';
        }
    }
}

// change 'on sale' text
add_filter( 'woocommerce_sale_flash', 'wc_custom_replace_sale_text' );
function wc_custom_replace_sale_text( $html ) {
    return str_replace( __( 'Sale!', 'woocommerce' ), __( 'Promo', 'woocommerce' ), $html );
}


// wrap product thumbs with div
add_action( 'woocommerce_before_shop_loop_item_title', 'product_thumb_open', 5, 2);
add_action( 'woocommerce_before_shop_loop_item_title', 'product_thumb_close', 12, 2);
function product_thumb_open() {
    echo '<div class="product__thumb product__thumb--prod">';
}
function product_thumb_close() {
    echo '</div>';
}

// wrap category product thumbs with div
add_action( 'woocommerce_before_subcategory_title', 'wrap_category_product_thumb_open', 5, 2);
add_action( 'woocommerce_before_subcategory_title', 'wrap_category_product_thumb_close', 12, 2);
function wrap_category_product_thumb_open() {
    echo '<div class="product__thumb product__thumb--prod">';
}
function wrap_category_product_thumb_close() {
    echo '</div>';
}

//Remove feed link from header
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
//Remove emoji css
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');


// add top sellers content after product listing page (category listing)
// add_action( 'woocommerce_after_main_content', 'storefront_best_selling_products', 30 );
add_action( 'woocommerce_after_shop_loop', 'storefront_best_selling_products', 90 );


// add full width CTA after product pages & category pages
function add_cta() {
    // is_shop → /stickers/
    if ( is_shop() OR is_product_category() OR is_product() ) {
        echo '<div class="full-width-cta">';
        get_template_part( "template-parts/full-width-cta" );
        echo '</div>';
    }
    // else{
    //     // echo '<h1>nope :(</h1>';
    // }
}
add_action( 'storefront_before_footer', 'add_cta' , 90 );


// customise single product (remove / re-order)
// hooks: https://businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 1 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 3 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_excerpt', 3 );

// if custom stickers category, add notice below product summary
function add_custom_cat_message() {
    if ( is_product() && has_term( 'singles', 'product_cat' ) ) {
        wc_get_template_part( "template-parts/product_cat_notice" );
    }
}
add_action( 'woocommerce_before_single_product_summary', 'add_custom_cat_message' , 4 );

// if custom stickers category, show custom fields
function add_custom_cat_fields() {
    // if ( is_product() && has_term( 'albums', 'product_cat' ) ) {
    if ( is_product() ) {
        //wc_get_template_part( "template-parts/product_custom_values" );
    }
}
add_action( 'woocommerce_single_product_summary', 'add_custom_cat_fields' , 1 );


// Display 48 products per page
add_action( 'loop_shop_per_page', 'display_x_products_per_page', 20);
function display_x_products_per_page($cols) {
    return 48;
}

// add trust icons below product details
function show_trust_icons() {
    if ( is_product() ) {
        wc_get_template_part( "template-parts/product_trust_icons" );
    }
}
add_action( 'woocommerce_after_add_to_cart_form', 'show_trust_icons' , 90 );

// wrap img & summary divs
add_action( 'woocommerce_before_single_product_summary', 'wrap_img_summary_open', 10);
add_action( 'woocommerce_after_single_product_summary', 'wrap_img_summary_close', 10);
function wrap_img_summary_open() {
    echo '<div class="product-details">';
}
function wrap_img_summary_close() {
    echo '</div>';
}

// remove unused bits:
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );// desc tab
remove_action( 'woocommerce_review_before', 'woocommerce_review_display_gravatar', 10 );
remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
remove_action( 'woocommerce_review_comment_text', 'woocommerce_review_display_comment_text', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


// move the description tab into the existing structure
// https://docs.woocommerce.com/document/editing-product-data-tabs/#section-4
function woocommerce_template_product_description() {
    wc_get_template( 'single-product/tabs/description.php' );
}
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_product_description', 20 );

// remove product desc title
add_filter( 'woocommerce_product_description_heading', 'remove_product_description_heading' );
    function remove_product_description_heading() {
    return '';
}

// add best sellers below product data
add_action( 'woocommerce_after_single_product_summary', 'storefront_best_selling_products', 90 );


// Remove category count functionality
add_filter( 'woocommerce_subcategory_count_html', 'hide_category_count' );
function hide_category_count() {
    // return;
}


/**
 * Disable Posts' meta from being preloaded
 * This fixes memory problems in the WordPress Admin
 * http://www.junaidbhura.com/wordpress-admin-fix-fatal-error-allowed-memory-size-error/
 */
function jb_pre_get_posts( WP_Query $wp_query ) {
    if ( in_array( $wp_query->get( 'post_type' ), array( 'my_post_type_1', 'my_post_type_2' ) ) ) {
        $wp_query->set( 'update_post_meta_cache', false );
    }
}

// Only do this for admin
if ( is_admin() ) {
    add_action( 'pre_get_posts', 'jb_pre_get_posts' );
}

// add options page
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();
}

@require_once('ext/wc_product_addons.php');

@require_once('ext/wc_billing_vat_number.php');


// translate some strings not picked up by the translation files
function translate_text_multiple( $translated ) {
    $text = array(
        'Search products&hellip;' => 'Producten zoeken&hellip;',
        'Proceed to checkout' => 'Doorgaan naar afrekenen',
        'Subtotal' => 'Subtotaal',
        'Update cart' => 'Winkelmand bijwerken',
        'View cart' => 'Winkelmand bekijken',
        'Apply coupon' => 'Kortingsbon toepassen',
        'Options total' => 'Totaal opties',
        'Sub total:' => 'Subtotaal:',
        'Grand total' => 'Totaal',
        'Bestelnotities' => 'Opmerking',
        'Select an option...' => 'Maak een keuze...',
        'Bestelnummer' => 'Bestelcode',
        'Estimate for %s' => 'Schatting voor %s',
        'This is only an estimate. Prices will be updated during checkout.' => 'Dit is louter een schatting. De prijzen zullen bijgewerkt worden tijdens de checkout.'
    );
    $translated = str_ireplace(  array_keys($text),  $text,  $translated );
    return $translated;
}
add_filter( 'gettext', 'translate_text_multiple', 20 );

function translate_shipping_option_label() {
    return "Verzendmethoden";
}
add_filter("woocommerce_shipping_package_name", 'translate_shipping_option_label', 20);



add_filter( 'woocommerce_variable_sale_price_html', 'hide_variable_max_price', PHP_INT_MAX, 2 );
add_filter( 'woocommerce_variable_price_html',      'hide_variable_max_price', PHP_INT_MAX, 2 );
function hide_variable_max_price( $price, $_product ) {
    $min_price_regular = $_product->get_variation_regular_price( 'min', true );
    $min_price_sale    = $_product->get_variation_sale_price( 'min', true );
    return ( $min_price_sale == $min_price_regular ) ?
        wc_price( $min_price_regular ) :
        '<del>' . wc_price( $min_price_regular ) . '</del>' . '<ins>' . wc_price( $min_price_sale ) . '</ins>';
}

// do not require phone number when ordering
add_filter( 'woocommerce_billing_fields', 'wc_npr_filter_phone', 10, 1 );
function wc_npr_filter_phone( $address_fields ) {
    $address_fields['billing_phone']['required'] = false;
    return $address_fields;
}

// don't show county/state field
add_filter( 'woocommerce_billing_fields', 'woo_filter_state_billing', 10, 1 );
add_filter( 'woocommerce_shipping_fields', 'woo_filter_state_shipping', 10, 1 );

function woo_filter_state_billing( $address_fields ) {
    $address_fields['billing_state']['required'] = false;
    return $address_fields;
}
function woo_filter_state_shipping( $address_fields ) {
    $address_fields['shipping_state']['required'] = false;
    return $address_fields;
}


add_action( 'woocommerce_flat_rate_shipping_add_rate', 'apply_shipping_rate_to_custom_sticker', 10, 2 );
function apply_shipping_rate_to_custom_sticker($method, $rate) {
    $package = $rate['package'];
    $destination = isset($package['destination']) ? $package['destination'] : [];
    $country = isset($destination['country']) ? $destination['country'] : 'BE';
    $current_max_cost = $rate['cost'];
    foreach ( $package['contents'] as $item_id => $values ) {
        $data = $values['data'];
        if ($data->get_type() == 'variation' && $data->get_shipping_class() == 'eigen-tekst-stickers') {
            $addons = $values['addons'];
            $simple_addons = [];
            foreach ($addons as $addon) {
                $simple_addons[$addon['name']] = $addon;
            }
            $text = trim($simple_addons['Tekst']['value']);
            $font_slug = $values['variation']['attribute_pa_font'];
            $sizing_method = $simple_addons['Tekstgrootte']['value'];
            $desired_cap_height = null;
            $desired_width = null;
            if ($sizing_method == 'Breedte') {
                $desired_width = $simple_addons['Breedte']['value'];
            } elseif ($sizing_method == 'Kapitaalhoogte') {
                $desired_cap_height = $simple_addons['Kapitaalhoogte']['value'];
            }
            $cost = Stickers\CustomSticker\calculate_cost($country, $font_slug, $desired_cap_height, $desired_width, $text);
            if ($cost > $current_max_cost) {
                $current_max_cost = $cost;
                $method->add_rate(array(
                    'id'      => $method->get_rate_id(),
                    'label'   => $method->title,
                    'cost'    => $cost,
                    'package' => $rate['package'],
                ));
            }
        }
    }
    return $rate;
}


//Page Slug Body Class
function add_slug_body_class( $classes ) {
global $post;
if ( isset( $post ) ) {
$classes[] = $post->post_type . '-' . $post->post_name;
}
return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );


// remove_footer_credit
add_action( 'init', 'custom_remove_footer_credit', 10 );

function custom_remove_footer_credit () {
    remove_action( 'storefront_footer', 'storefront_credit', 20 );
}

add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );


// Remove breedte_regels field and add "mm" suffix to "Breedte" to custom sticker order item in email
function cleanup_custom_sticker_email_meta($formatted_meta, $order_item_meta) {
    $cleaned = [];
    $mm_suffix = ' mm';
    foreach ($formatted_meta as $meta_id => $meta) {
        $key = '' . $meta['key']; // Force string
        // Remove "breedte_regels" field which is only used to determine price
        if (stripos($key, 'breedte_regels') === 0) {
            continue;
        }
        // Get the product id from the order item
        $product_id = $order_item_meta->product->get_parent_id();
        $custom_sticker = is_custom_sticker($product_id);
        if ($custom_sticker) {
            // Add "mm" to "Breedte" and "Kapitaalhoogte" field of custom stickers
            if ((stripos($key, 'Breedte') === 0 || stripos($key, 'Kapitaalhoogte') === 0)) {
                $meta['value'] = $meta['value'] . $mm_suffix;
            }
        }
        $cleaned[$meta_id] = $meta;
        // Calculate and add "Hoogte" field to custom stickers
        if ($custom_sticker && stripos($key, 'Breedte') === 0) {
            $font_slug = $order_item_meta->meta['pa_font'];
            $cap_height = $order_item_meta->meta['Kapitaalhoogte'];
            $width = $order_item_meta->meta['Breedte'];
            $text = trim($order_item_meta->meta['Tekst']);
            $dimensions = Stickers\CustomSticker\calculate_shipping_dimensions($font_slug, $cap_height, $width, $text);
            $cleaned[$meta_id . '-1'] = array(
                'key' => 'hoogte',
                'label' => 'Hoogte',
                'value' => $dimensions['height'] . $mm_suffix
            );
            if ($text) {
                $nr_lines = count(preg_split('/\n|\r\n?/', $text));
                $cleaned[$meta_id . '-2'] = array(
                    'key' => 'regels',
                    'label' => 'Regels',
                    'value' => $nr_lines
                );
            }
        }
    }
    return $cleaned;
}

add_filter('woocommerce_order_items_meta_get_formatted', 'cleanup_custom_sticker_email_meta', 10, 2);


// Remove breedte_regels field and add "mm" suffix to "Breedte" to custom sticker order item in email
function cleanup_custom_sticker_order_item_meta($formatted_meta, $order_item) {
    $cleaned = [];
    $mm_suffix = ' mm';
    $meta_value_by_key = [];
    foreach ($formatted_meta as $meta_id => $meta) {
        $meta_value_by_key[$meta->key] = $meta->value;
    }
    foreach ($formatted_meta as $meta_id => $meta) {
        $key = '' . $meta->key; // Force string
        // Remove "breedte_regels" field which is only used to determine price
        if (stripos($key, 'breedte_regels') === 0) {
            continue;
        }
        $product = is_callable( array( $order_item, 'get_product' ) ) ? $order_item->get_product() : false;
        if (!$product) {
            continue;
        }
        // Get the product id from the order item
        $product_id = $product->get_parent_id();
        $custom_sticker = is_custom_sticker($product_id);
        if ($custom_sticker) {
            // Add "mm" to "Breedte" and "Kapitaalhoogte" field of custom stickers
            if ((stripos($key, 'Breedte') === 0 || stripos($key, 'Kapitaalhoogte') === 0)) {
                $meta->display_value = $meta->value . $mm_suffix;
            }
        }
        $cleaned[$meta_id] = $meta;
        // Calculate and add "Hoogte" field to custom stickers
        if ($custom_sticker && stripos($key, 'Breedte') === 0) {
            $font_slug = $meta_value_by_key['pa_font'];
            $cap_height = $meta_value_by_key['Kapitaalhoogte'];
            $width = $meta_value_by_key['Breedte'];
            $text = trim($meta_value_by_key['Tekst']);
            $dimensions = Stickers\CustomSticker\calculate_shipping_dimensions($font_slug, $cap_height, $width, $text);
            $cleaned[$meta_id . '-1'] = (object) array(
                'key' => 'hoogte',
                'display_key' => 'Hoogte',
                'value' => $dimensions['height'],
                'display_value' => $dimensions['height'] . $mm_suffix
            );
            if ($text) {
                $nr_lines = count(preg_split('/\n|\r\n?/', $text));
                $cleaned[$meta_id . '-2'] = (object) array(
                    'key' => 'regels',
                    'display_key' => 'Regels',
                    'value' => $nr_lines,
                    'display_value' => $nr_lines
                );
            }
        }
    }
    return $cleaned;
}

add_filter('woocommerce_order_item_get_formatted_meta_data', 'cleanup_custom_sticker_order_item_meta', 10, 2);

// add staffelkorting notice to simple products, above add to cart btn
function show_staffel() {
    global $product;

    if ( $product->is_type( 'simple' ) ) {
        wc_get_template_part( "template-parts/staffelkorting" );
    }
}
add_action( 'woocommerce_after_add_to_cart_quantity', 'show_staffel' , 90 );


// ---------------------------------------------------------------------------------------------------------------------
// Product title formatting
//
add_filter( 'woocommerce_is_attribute_in_product_name', '__return_false' );
// This removes the variation at some spots
add_filter( 'woocommerce_product_variation_title_include_attributes', 'custom_product_variation_title', 10, 2 );
function custom_product_variation_title($should_include_attributes, $product){
    $should_include_attributes = false;
    return $should_include_attributes;
}

// This restores the usage of the product title in the cart
function remove_variation_from_product_title( $title, $cart_item, $cart_item_key ) {
    $_product = $cart_item['data'];
    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

    if ( $_product->is_type( 'variation' ) ) {
        if ( ! $product_permalink ) {
            return $_product->get_title();
        } else {
            return sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title() );
        }
    }

    return $title;
}
add_filter( 'woocommerce_cart_item_name', 'remove_variation_from_product_title', 10, 3 );


/**
 * Remove 'hentry' from post_class()
 * https://gist.github.com/jaredatch/1629862
 */
function ja_remove_hentry( $class ) {
    $class = array_diff( $class, array( 'hentry' ) );   
    return $class;
}
add_filter( 'post_class', 'ja_remove_hentry' );

/**
 * Remove the generated product schema markup from Product Category and Shop pages.
 */
function wc_remove_product_schema_product_archive() {
	remove_action( 'woocommerce_shop_loop', array( WC()->structured_data, 'generate_product_data' ), 10, 0 );
}
add_action( 'woocommerce_init', 'wc_remove_product_schema_product_archive' );

add_action('woocommerce_after_shop_loop', 'bbc_woocommerce_after_shop_loop', 30 );
function bbc_woocommerce_after_shop_loop(){
    $term = get_queried_object();
    if( is_a( $term, WP_Term ) ){
        the_field( 'seo_description', $term );
    }
}