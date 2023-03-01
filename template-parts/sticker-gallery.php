<section class="sticker-gallery">
    <div class="sticker-gallery__header">
        <h3 class="btn--medium btn--round t-uppercase mb-small">Galerij</h3>
        <p class="t-white t-xs t-semibold t-center t-shade mb-z">Enkele voorbeelden van onze stickers.</p>
    </div>
    <div class="sticker-gallery__images">
        <div class="sticker-gallery__images-inner">
        <?php 
        // grab images that have been selected on the options page
        if( have_rows('homepage_stickers_galerij', 'option') ): ?>
            <?php while( have_rows('homepage_stickers_galerij', 'option') ): the_row();
                // store vars:
                $image = get_sub_field('image');
            ?>
            <div class="sticker-gallery__image">
                <img src="<?php echo $image; ?>" alt="">
            </div>
            <?php endwhile;
        endif; ?>
        </div>
    </div>
</section>