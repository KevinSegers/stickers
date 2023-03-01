<?php
// Patches and change for WooCommerce Product Addons

// We extend the default addon display to allow us to group addons (Tekstgrootte is a select and includes the
// Breedte and Kapitaalhoogte fields).
class Stickers_Product_Addon_Display extends WC_Product_Addons_Display {

    public function __construct($original) {
        // Remove the actions and filters added to the original

        // For some reason these don't get executed at all...
        //remove_action( 'wp_enqueue_scripts', array( $original, 'styles' ) );
        //remove_action( 'wc_quick_view_enqueue_scripts', array( $original, 'addon_scripts' ) );

        // Remove these
        remove_action( 'wp_enqueue_scripts', array( $original, 'quick_view_single_compat' ) );
        remove_action( 'woocommerce_before_add_to_cart_button', array( $original, 'display' ), 10 );
        remove_action( 'woocommerce_before_variations_form', array( $original, 'reposition_display_for_variable_product' ), 10 );
        remove_action( 'woocommerce_product_addons_end', array( $original, 'totals' ), 10 );
        remove_filter( 'add_to_cart_text', array( $original, 'add_to_cart_text'));
        remove_filter( 'woocommerce_product_add_to_cart_text', array( $original, 'add_to_cart_text'));
        remove_filter( 'woocommerce_add_to_cart_url', array( $original, 'add_to_cart_url' ));
        remove_filter( 'woocommerce_product_add_to_cart_url', array( $original, 'add_to_cart_url' ));
        remove_filter( 'woocommerce_product_supports', array( $original, 'ajax_add_to_cart_supports' ));
        remove_filter( 'woocommerce_is_purchasable', array( $original, 'prevent_purchase_at_grouped_level' ));
        remove_filter( 'woocommerce_order_item_display_meta_value', array( $original, 'fix_file_uploaded_display' ) );
    }

    public function display( $post_id = false, $prefix = false ) {
        global $product;
        // don't show subtotals for products with addons
        $custom_sticker = get_post_meta($product->get_id(), 'sticker_custom_text', true) == '1';
        if (!$custom_sticker) {
            add_filter( 'woocommerce_product_addons_show_grand_total', '__return_false');
        }

        if ( ! $post_id ) {
            global $post;
            $post_id = $post->ID;
        }

        // We do not currently support grouped or external products.
        if ( 'grouped' === $product->get_type() || 'external' === $product->get_type() ) {
            return;
        }

        $this->addon_scripts();

        $product_addons = WC_Product_Addons_Helper::get_product_addons( $post_id, $prefix );
        $first = true;
        if ( is_array( $product_addons ) && sizeof( $product_addons ) > 0 ) {

            do_action( 'woocommerce_product_addons_start', $post_id );

            $product_addons = $this->group_product_addons($post_id, $product_addons);

            $previous_heading = null;
            foreach ( $product_addons as $addon ) {
                if ( ! isset( $addon['field_name'] ) )
                    continue;
                if ($first) {
                    echo '<div class="custom-product-fields__block custom-product-options text">';
                    $first = false;
                }
                $previous_heading = $this->display_addon($product, $addon, $previous_heading);
            }
            if (!$first) {
                echo '</div>';
            }

            do_action( 'woocommerce_product_addons_end', $post_id );
        }
    }

    protected function group_product_addons($post_id, &$addons) {
        // Grab the group configuration. It should look like this: "Tekstgrootte=Kapitaalhoogte|Breedte"
        $groups = get_post_meta($post_id, 'addon_groups');
        // No config, no special treatment needed. Bail out here.
        if (empty($groups)) {
            return $addons;
        }
        // Group the addons by name
        $addons_by_name = [];
        foreach ($addons as $addon) {
            $addons_by_name[$addon['name']] = $addon;
        }
        // Now map the member-addons to their group-addons
        $names_by_group = [];
        $member_level_addon_names = [];
        foreach ($groups as $group) {
            $index = stripos($group, '=');
            if ($index > 0) {
                $group_name = substr($group, 0, $index);
                $members = explode('|', substr($group, $index + 1));
                $new_members = [];
                foreach ($members as $member) {
                    $clean_member = trim($member);
                    $new_members[] = $clean_member;
                    $add_addons_to_group[$clean_member] = $group_name;
                }
                $names_by_group[$group_name] = $new_members;
                $member_level_addon_names = array_merge($member_level_addon_names, $new_members);
            }
        }
        $member_level_addon_names = array_unique($member_level_addon_names);
        $top_level_addons = [];
        foreach ($addons as $addon) {
            $name = $addon['name'];
            if (in_array($name, $member_level_addon_names)) {
                continue;
            }
            $member_addons = [];
            $member_names = (array_key_exists($name, $names_by_group) ? $names_by_group[$name] : []);
            if (empty($member_names)) {
                $top_level_addons[] = $addon;
                continue;
            }
            foreach ($member_names as $member_name) {
                if (array_key_exists($member_name, $addons_by_name)) {
                    $member_addons[] = $addons_by_name[$member_name];
                }
            }
            $addon['member_addons'] = $member_addons;
            $top_level_addons[] = $addon;
        }
        return $top_level_addons;
    }

    protected function display_addon($product, $addon, $previous_heading) {
        $name = $addon['name'];
        $description = $addon['description'];
        if ($addon['type'] === "heading") {
            // @mediasoft
            // We don't output "heading"s. These were introduced later on and *all* defined addons were split into
            // headings and values. We're not going to change all of them, so we intercept and pass them along to inject
            // them into the next addon.
            $previous_heading = array(
                "name" => $name,
                "description" => $description
            );
            // Don't output at all!
            return $previous_heading;
        }
        $set_title_format = null;
        if ($previous_heading) {
            // The previous addon was a heading -> inject the values in this addon.
            $name = $previous_heading["name"];
            $description = $previous_heading["description"];
            $set_title_format = 'heading';
            $previous_heading = null;
        }
        $addon_data = array(
            'addon'       => $addon,
            'required'    => WC_Product_Addons_Helper::is_addon_required( $addon ),
            'name'        => $name,
            'description' => $description,
            'type'        => $addon['type']
        );
        if ($set_title_format) {
            // Consequence of the injecting the heading of the previous addon
            $addon_data['addon']['title_format'] = 'heading';
        }
        wc_get_template( 'addons/addon-start.php', $addon_data, 'woocommerce-product-addons', $this->plugin_path() . '/templates/' );

        echo $this->get_addon_html( $addon );

        if (array_key_exists('member_addons', $addon)) {
            $member_addons = $addon['member_addons'];
            if (!empty($member_addons)) {
                foreach ($member_addons as $member) {
                    $this->display_addon($product, $member, null);
                }
            }
        }

        wc_get_template( 'addons/addon-end.php', array(
            'addon'    => $addon,
        ), 'woocommerce-product-addons', $this->plugin_path() . '/templates/' );
        return $previous_heading;
    }

}
$GLOBALS['Product_Addon_Display'] = new Stickers_Product_Addon_Display($GLOBALS['Product_Addon_Display']);


// The default spot where addons are rendered doesn't quite fit with our goals. Remove the default actions and rebind
// them to some custom hooks. See woocommerce/single-product/add-to-cart/variable.php
function reposition_addons() {
    global $Product_Addon_Display;
    remove_action( 'woocommerce_single_variation', array( $Product_Addon_Display, 'display' ), 15 );
    add_action( 'stickers_before_attributes', array( $Product_Addon_Display, 'display' ), 15 );
    remove_action('woocommerce-product-addons_end', array($Product_Addon_Display, 'totals'), 10);
    add_action('stickers-post-attributes', array($Product_Addon_Display, 'totals'), 4);
}

add_action( 'woocommerce_before_variations_form', 'reposition_addons', 10 );

// URL for cart items with addons don't include the addon information which is pretty annoying.
// We add it ourselves.
function customize_cart_item_with_addons_url($url, $cart_item, $cart_item_key) {
    if (!$url || $url === '') {
        // An "invisible" product will pass no URL. We still need one, so fetch the product and build the initial URL
        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $url = $_product->get_permalink( $cart_item );
    }
    if (isset($cart_item['addons'])) {
        $addons = $cart_item['addons'];
        $parts = [];
        // We don't use urlencode on the value because the cart logic will pass the link through esc_url. And that
        // will strip newlines. Our custom text might contain newlines. We want newlines. We need them.
        // So base64 to the rescue!
        foreach ($addons as $addon) {
            $parts[] = 'addon-' . urlencode($addon['field_name']) . '=' . base64_encode($addon['value']);
        }
        if (count($parts)) {
            $separator = strpos($url, '?') >= 0 ? '&' : '?';
            $url .= $separator . implode('&', $parts);
        }
    }
    return $url;
}
add_filter('woocommerce_cart_item_permalink', 'customize_cart_item_with_addons_url', 1, 3);


// This will add the field name (technical name) of the addon to the addon-details of the item in the cart.
// This allows us to use that technical name and include it in the URL.
function customize_cart_item_addon_data($data, $addon, $product_id, $post_data) {
    $data[count($data) - 1]['field_name'] = $addon['field_name'];
    return $data;
}
add_filter('woocommerce_product_addon_cart_item_data', 'customize_cart_item_addon_data', 10, 4);
