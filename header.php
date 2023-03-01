<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php wp_head(); ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MF9VL3Q');</script>
<!-- End Google Tag Manager -->

</head>
<body <?php body_class(); ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MF9VL3Q"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php do_action( 'storefront_before_header' ); ?>
    <header class="site-header" role="banner" style="<?php storefront_header_styles(); ?>">
        <div class="header__logo">
            <div class="row">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link" rel="home">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo.png" srcset="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo@2x.png 2x" alt="<?php echo get_bloginfo( 'name' ); ?>">
                </a>
            </div>
        </div>
        <button class="mobile-menu mobile-menu--collapse" id="js-menu-toggle" type="button">
            <div class="mobile-menu__box">
                <div class="mobile-menu__inner"></div>
                <p class="mobile-menu__text">Menu</p>
            </div>
        </button>
        <div class="header__top-bar bg-green">
            <div class="row">
                <div class="top-bar-nav"><div style=" display:inline-block;margin-right: 10px;" >
                <!-- Feedback Company Widget (start) -->
<script type="text/javascript" id="__fbcw__493ca93a-cd20-4fb5-ae13-c50e47423459">
    "use strict";!function(){
window.FeedbackCompanyWidgets=window.FeedbackCompanyWidgets||{queue:[],loaders:[
]};var options={uuid:"493ca93a-cd20-4fb5-ae13-c50e47423459",version:"1.2.1",prefix:""};if(
void 0===window.FeedbackCompanyWidget){if(
window.FeedbackCompanyWidgets.queue.push(options),!document.getElementById(
"__fbcw_FeedbackCompanyWidget")){var scriptTag=document.createElement("script")
;scriptTag.onload=function(){if(window.FeedbackCompanyWidget)for(
;0<window.FeedbackCompanyWidgets.queue.length;
)options=window.FeedbackCompanyWidgets.queue.pop(),
window.FeedbackCompanyWidgets.loaders.push(
new window.FeedbackCompanyWidgetLoader(options))},
scriptTag.id="__fbcw_FeedbackCompanyWidget",
scriptTag.src="https://www.feedbackcompany.com/includes/widgets/feedback-company-widget.min.js"
,document.body.appendChild(scriptTag)}
}else window.FeedbackCompanyWidgets.loaders.push(
new window.FeedbackCompanyWidgetLoader(options))}();
</script>
<!-- Feedback Company Widget (end) --></div>
                <div style=" display:inline-block;margin-right: 10px;" ><?php echo do_shortcode( '[language-switcher]' ); ?></div>
                
                    <?php if ( is_user_logged_in() ) { ?>
                        Hallo <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('My Account','woothemes'); ?>"><?php global $current_user;
  $current_user = wp_get_current_user();
  if ($current_user->user_firstname != '') {
     echo $current_user->user_firstname;
  }
  else {
     echo $current_user->user_login;
  }
?></a>
                    <?php } 
                    else { ?>
                        <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('Login / Register','woothemes'); ?>"><?php _e('Inloggen','woothemes'); ?></a>
                        <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('Login / Register','woothemes'); ?>"><?php _e('Registreren','woothemes'); ?></a>
                    <?php } ?>
                </div>
                <div class="top-bar-cart bg-white">
                    <?php storefront_header_cart(); ?>
                </div>
                <div class="top-bar-search bg-grey-darkest">
                    <?php storefront_product_search(); ?>
                   
                </div>
                
            </div>
        </div>
        <div class="header__nav bg-grey-lighter">
            <div class="row">
                <?php 
                    wp_nav_menu( array(
                        'menu' => 'Main Nav'
                    ));
                ?>
            </div>
            <?php
                /**
                 * Functions hooked into storefront_header action
                 *
                 * @hooked storefront_skip_links                       - 0
                 * @hooked storefront_social_icons                     - 10
                 * @hooked storefront_site_branding                    - 20
                 * @hooked storefront_secondary_navigation             - 30
                 * @hooked storefront_product_search                   - 40
                 * @hooked storefront_primary_navigation_wrapper       - 42
                 * @hooked storefront_primary_navigation               - 50
                 * @hooked storefront_header_cart                      - 60
                 * @hooked storefront_primary_navigation_wrapper_close - 68
                 */
                // do_action( 'storefront_header' );
            ?>
        </div>
    </header>

    <?php
    /**
     * Functions hooked in to storefront_before_content
     *
     * @hooked storefront_header_widget_region - 10
     */
    do_action( 'storefront_before_content' );

    if ( is_front_page() ) :
    get_template_part( 'template-parts/homepage-hero' );
    endif;
    ?>

    <div class="site-content" tabindex="-1">
        <div class="row">
        
        <?php
        /**
         * Functions hooked in to storefront_content_top
         *
         * @hooked woocommerce_breadcrumb - 10
         */
        do_action( 'storefront_content_top' );