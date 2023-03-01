<section class="cta-own-stickers">
    <div class="row own-stickers__inner">
        <div class="own-stickers__text mb-medium">
            <h2 class="t-m t-uppercase t-bold mb-small"><?php the_field('titel', 'option'); ?></h2>
            <?php the_field('lijst', 'option'); ?>
            <p class="mt-small">
                <a href="<?php the_field('button', 'option'); ?>" class="btn btn--green btn--medium btn--round t-uppercase"><?php the_field('button_tekst', 'option'); ?>&nbsp;&rsaquo;</a>
            </p>
        </div>
        <div class="own-stickers__img">
            <img src="<?php the_field('afbeelding', 'option'); ?>" alt="<?php the_field('titel', 'option'); ?>">
        </div>
    </div>
</section>