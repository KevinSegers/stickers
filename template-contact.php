<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Contact
 *
 * @package storefront
 */

get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <h1><?php the_title(); ?></h1>
            <div class="contact-details pt-small pb-small" style="overflow:hidden;">
                <div class="pull-left pr-medium">
                    <strong class="t-m t-semibold t-grey-darker">Onze gegevens</strong><br /><br />
                    <p class="">
                        <?php the_field('adres', 'option'); ?><br />
                        <?php the_field('telefoonnummer', 'option'); ?><br />
                    </p>
                </div>
                <div class="pull-left">
                    <strong class="t-s t-semibold t-grey-darker">&nbsp;</strong><br /><br />
                    <p class="">
                        <a href="mailto:<?php the_field('emailadres', 'option'); ?>"><?php the_field('emailadres', 'option'); ?></a><br />
                        BTW: <?php the_field('btw_nummer', 'option'); ?><br />
                        <?php the_field('iban_nummer', 'option'); ?><br />
                        <?php the_field('bic_nummer', 'option'); ?>
                    </p>
                </div>
            </div>
            <hr class="mb-small">
            <?php the_content(); ?>   

        </main><!-- #main -->
    </div><!-- #primary -->

    <div id="secondary" class="widget-area" role="complementary">
        <?php dynamic_sidebar( 'sidebar-1' ); ?>
    </div>
    
    <?php
        get_template_part( 'template-parts/other-features' );
    ?>
<?php
get_footer();
