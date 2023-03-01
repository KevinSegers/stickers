<div class="homepage-slider">
<?php 
if( ( is_front_page() ) && (have_rows('homepage_hero', 'option') )): ?>
    <?php while( have_rows('homepage_hero', 'option') ): the_row();
        // store vars:
        $title = get_sub_field('title');
        $text = get_sub_field('text');
        $type = get_sub_field('type');
        $link = get_sub_field('link');
        $position = get_sub_field('position');
        $category = get_sub_field('category');
        $background_image = get_sub_field('background_image');
    ?>
        <div class="homepage-slider__slide homepage-slide--<?php echo $position; ?> bg-cover" style="background-image: url(<?php echo $background_image; ?>);">
            <div class="row">
                <div class="homepage-slider__cta">
                    <h2 class="t-xl t-uppercase t-bold t-white mb-small"><?php echo $title; ?></h2>
                    <a class="btn btn--green btn--large t-uppercase" href="<?php if( $type == "category" ): echo get_category_link( $category ); else: echo $link; endif; ?>"><?php echo $text; ?>&nbsp;&rsaquo;</a>
                </div>
            </div>
        </div>
    <?php endwhile;
endif; ?>
</div>