<?php
    if($list->getPostIds())
    {
        $args = [
            'post_type' => 'any',
            'post__in' => $list->getPostIds(true),
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
            'orderby' => 'post_in'
        ];

        global $wp_query;

        $wp_query = new \WP_Query($args);

        $this->found_posts = $wp_query->found_posts;
        $this->post_count = $wp_query->post_count;

        if ( have_posts() )
        {
?><div class="favlist-items"><?php

            while ( have_posts() ) : the_post();
                get_template_part( 'template-parts/content', (get_post_format() ? get_post_format() : get_post_type()) );

            endwhile;

            // Previous/next page navigation.
            the_posts_pagination( array(
                'prev_text'          => __( 'Previous page' ),
                'next_text'          => __( 'See more' ),
                'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page' ) . ' </span>',
            ) );
?></div><?php
        }

        wp_reset_query();
    }
?>
