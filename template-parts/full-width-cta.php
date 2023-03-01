<section class="cta-full bg-grey-lightest pt-large pb-large">
    <div class="row">
        <div class="cta-full__inner t-center">
            <h4 class="t-l t-uppercase"><?php the_field('cta_full-title', 'option'); ?></h4>
            <p class="t-s"><?php the_field('cta_full-text', 'option'); ?></p>
        <?php
        if ( 'ja' == get_field('categorie_of_niet', 'option') ) {
            ?>
            <p><a href="<?php echo get_term_link( get_field( 'cta_cat-link', 'option' ) ); ?>" class="btn btn--large btn--green btn--round t-uppercase"><?php the_field('cta_full-link-text', 'option'); ?>&nbsp;&rsaquo;</a></p>
        <?php
        } elseif ( 'nee' == get_field('categorie_of_niet', 'option') ) {
            ?>
            <p><a href="<?php the_field('cta-other-link', 'option') ?>" class="btn btn--large btn--green btn--round t-uppercase"><?php the_field('cta_full-link-text', 'option'); ?>&nbsp;&rsaquo;</a></p>
        <?php
        }
        ?>
        </div>
    </div>
</section>