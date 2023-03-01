<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>

        </div><!-- .row -->
        <?php
            if (is_front_page()){
                echo storefront_best_selling_products([]);
                get_template_part( 'template-parts/cta-own_stickers' );
                get_template_part( 'template-parts/sticker-gallery' );
                get_template_part( 'template-parts/other-features' );
            }
        ?>
    </div><!-- #content -->
    
    <?php do_action( 'storefront_before_footer' ); ?>
    
    <div class="subfooter pt-large pb-large bg-white">
        <p class="t-xs t-italic t-center mb-z">Stickers.be werkt samen met deze partners:</p>
        <div class="subfooter__logos">
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-bancontact.svg" alt=""></div>
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-visa.svg" alt=""></div>
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-maestro.svg" alt=""></div>
            <div class="subfooter__logo subfooter__logo--paypal"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-paypal.svg" alt=""></div>
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-bpost.svg" alt=""></div>
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-belfius.svg" alt=""></div>
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-kbc.svg" alt=""></div>
            <div class="subfooter__logo"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/branding/logo-ideal.svg" alt=""></div>
        </div>
    </div>

    <footer id="colophon" class="page-footer bg-grey-lighter" role="contentinfo">
        <div class="row">
            <div class="page-footer__inner pt-large pb-large">
                <div class="page-footer-block">
                    <strong class="t-s t-semibold t-grey-darker">Stickers.be</strong>
                    <?php 
                        wp_nav_menu( array(
                            'menu' => 'footer-general'
                        ));
                    ?>
                </div>
                <div class="page-footer-block">
                    <strong class="t-s t-semibold t-grey-darker">Klantendienst</strong>
                    <?php 
                        wp_nav_menu( array(
                            'menu' => 'footer-customer-service'
                        ));
                    ?>
                </div>
                <div class="page-footer-block">
                    <strong class="t-s t-semibold t-grey-darker">Inschrijven nieuwsbrief</strong>
                    <p class="t-xs">Blijf op de hoogte van nieuwe producten, promoties en weetjes.</p>
                    <form action="//stickers.us5.list-manage.com/subscribe/post?u=d807359e718b4d5f6322ea19a&amp;id=ef27f80d47" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                        <div class="mc-field-group form-group">
                            <label>
                                <input type="email" value="" placeholder="E-mailadres" name="EMAIL" class="input input--grey-border" />
                            </label>
                        </div>
                        <div class="form-group">
                            <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_d807359e718b4d5f6322ea19a_ef27f80d47" tabindex="-1" value=""></div>
                            <button type="submit" class="btn btn--green btn--small t-uppercase btn--round">Inschrijven</button>
                        </div>
                    </form>
                </div>
                <div class="page-footer-block">
                    <strong class="t-s t-semibold t-grey-darker">Onze gegevens</strong>
                    <div class="page-footer-block__sub">
                        <p class="t-xs">
                            <?php the_field('adres', 'option'); ?><br />
                            <?php the_field('telefoonnummer', 'option'); ?><br />
                            <a href="mailto:<?php the_field('emailadres', 'option'); ?>"><?php the_field('emailadres', 'option'); ?></a><br />
                            BTW: <?php the_field('btw_nummer', 'option'); ?><br />
                        </p>
                    </div>
                </div>
            </div>
        </div><!-- .row -->
        <div class="bg-grey-darkest">
            <div class="row">
                <div class="page-footer__info pt-medium pb-medium">
                    <div class="page-footer__info-block">
                    <?php 
                        wp_nav_menu( array(
                            'menu' => 'footer-info'
                        ));
                    ?>
                    </div>
                    <div class="page-footer__info-block">
                        <p>
                            &copy;<?php echo date("Y") ?> stickers.be<br />
                            Alle rechten voorbehouden<br />
                            Al onze stickers worden getoond inclusief 21% btw.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->

    <?php do_action( 'storefront_after_footer' ); ?>


    <?php
    /**
     * Functions hooked in to storefront_footer action
     *
     * @hooked storefront_footer_widgets - 10
     * @hooked storefront_credit         - 20
     */
    do_action( 'storefront_footer' ); ?>


<?php wp_footer(); ?>
</body>
</html>