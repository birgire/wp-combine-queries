wp-combine-queries
=================

WordPress plugin - Combine Queries

###Description

This experimental plugin allows you to combine multiple `WP_Query` queries into a single one, using the `WP_Combine_Query` class.

This started as an answer on Stackoverflow, see [here](http://stackoverflow.com/questions/23555109/wordpress-combine-queries/) and [here](http://wordpress.stackexchange.com/questions/159228/combining-two-wordpress-queries-with-pagination-is-not-working/).

The idea behind this plugin is to use combine the SQL queries for each `WP_Query()` query with `UNION` or `UNION ALL`.

I first noticed this technique in a [great answer on WordPress Development](http://wordpress.stackexchange.com/a/912/26350) by Mike Schinkel.

Here we extend the `WP_Query` class to achieve this goal. We actually do that twice:

 - `WP_Query_Empty`: to get the generated SQL query of each sub-queries, but without doing the database query.
 - `WP_Query_Combine`: to fetch the posts.

I use the trick mentioned [here](http://stackoverflow.com/a/7587423/2078474) to preserve the order of `UNION` sub queries. We can modify it accordingly with our `sublimit` parameter.

This implementation supports combining `N` sub-queries.

This should also work for main queries, by using the `posts_request` filter, for example. But this needs more testing.

###Notice about the new 0.1 version

The `sublimit` parameter is no longer needed or supported. Instead the `posts_per_page` can be used in the subqueries. If it's not used, then the native default value is used.

This should add more flexibility.

You can now also order the combined result, see the examples below.

###Installation

Upload the plugin to your plugin folder and activate it.

Then use some of the examples below in your theme or a plugin.

###Default Parameters Of `WP_Combine_Query`:

    $args = array(
       'posts_per_page' => 5,
       'paged'          => 1,
       'offset'         => 0,
       'union'          => 'UNION',  // Possible values are UNION or UNION ALL
       'args'           => array(),
    );


###Example 1a: 

Here we want to display the newest published page and then the three oldest published posts:

    //-----------------
    // Sub query #1:
    //-----------------
    $args1 = array(
       'post_type'      => 'page',
       'posts_per_page' => 1,
       'orderby'        => 'title',
       'order'          => 'asc',
    );
  
    //-----------------
    // Sub query #2:
    //-----------------
    $args2 = array(
       'post_type'      => 'post',
       'posts_per_page' => 3,
       'orderby'        => 'date',
       'order'          => 'asc',
    );

    //---------------------------
    // Combined queries #1 + #2:
    //---------------------------
    $args = array(
        'posts_per_page' => 4,
        'args'           => array( $args1, $args2 ),
    );

    //---------
    // Output:
    //---------
    if( class_exists( 'WP_Combine_Queries' ) ):
        $q = new WP_Combine_Queries( $args );
        if( $q->have_posts() ):
            ?><ul><?php
            while( $q->have_posts() ): $q->the_post();
	        ?><li><a href="<?php the_permalink();?>"><?php the_title();?></a></li><?php
            endwhile;
            ?></ul><?php
            wp_reset_postdata();
        else:
            _e( 'Sorry no posts found!' );
        endif;       
    endif;       


###Example 1b: 

If we want to order the combined query in example 1a, we can use for example:

    //---------------------------
    // Combined queries #1 + #2:
    //---------------------------
    $args = array(
        'posts_per_page' => 4,
        'args'           => array( $args1, $args2 ),
        'orderby'        => array( 'date' => 'asc', 'title' => 'desc' )
    );


###Example 2: 

Here we want to display all posts published today, sorted by comment count and after that all posts (excluding today's post) sorted by comment count.
This [example](http://wordpress.stackexchange.com/questions/159228/combining-two-wordpress-queries-with-pagination-is-not-working) was provided by Robert Hue.

    //-----------------
    // Sub query #1:
    //-----------------
    $args1 = array( 
        'post_type'           => 'post',
        'orderby'             => 'comment_count',
        'posts_per_page'      => 100, // adjust to your needs
        'ignore_sticky_posts' => 1,  
        'date_query'          => array(
            array(
                'after' => date('Y-m-d'),
            ),
            'inclusive'  => true,
         )
    );

    //-----------------   
    // Sub query #2:
    //-----------------
    $args2 = array(
        'post_type'           => 'post',
        'orderby'             => 'comment_count',
        'posts_per_page'      => 100, // adjust to your needs
        'ignore_sticky_posts' => 1,
        'date_query'          => array(
            array(
               'before' => date('Y-m-d'),
            ),
            'inclusive'  => false,
         )  
    );

    //--------------------------- 
    // Combined queries #1 + #2:
    //---------------------------
    $args = array(
       'posts_per_page' => 5,
       'paged'          => ( $paged = get_query_var( 'page' ) ) ? $paged : 1 ,
       'args'           => array( $args1, $args2 ),
    );

    //---------
    // Output:
    //---------
    // See example 1a

###Example 3:

We could also combine more than two sub queries, here's an example of four sub-queries:

     $args = array( 
         'posts_per_page' => 10,
         'paged'          => 1,
         'args'           => array( $args1, $args2, $args3, $args4 ),
      );

    //---------
    // Output:
    //---------
    // See example 1a


###Changelog

0.1  Various plugin improvements, for example:
 - Added: orderby in the combined query.
 - Added: posts_per_page in the sub queries.
 - Added: offset in the sub queries.
 - Added: paged in the sub queries.
 - Removed: sublimit in the combined query, use posts_per_page instead in sub queries.
 - Fixed: Issue #1 related to max_num_pages (Props @hellofantastic).

0.0.4
 - Added: support for offset in the combined query

0.0.3
 - Added: GPL2+ License part
 - Removed: Dropped namespace + anonymous function for wider PHP support.

0.0.2 
 - Added: Input parameter 'union' with possible values UNION and UNION ALL.
 - Fixed: Empty paged resulted in a sql error. ( Props: Robert Hue)

