<?php
    if ( have_posts() )
    {
?><div class="favlist-items"><?php

        while ( have_posts() ) : the_post();

            get_template_part( 'template-parts/content', 'favlist' );

        endwhile;

        // Previous/next page navigation.
        the_posts_pagination( array(
            'prev_text'          => __( 'Previous page' ),
            'next_text'          => __( 'See more' ),
            'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page' ) . ' </span>',
        ) );
?></div><?php
    }
?>
