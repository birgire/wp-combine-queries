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

This should also work for main queries, by using the `posts_request` filter, for example.


###Installation

Upload the plugin to your plugin folder and activate it.

Then use some of the examples in your theme or plugin.

###Default Parameters Of `WP_Combine_Query`:

    $args = array(
       'posts_per_page' => 5,
       'paged'          => 1,
       'sublimit'       => 1000,
       'union'          => 'UNION',
       'args'           => array(),
    );


###Example 1: 

Here we display all posts published today, sorted by comment count and after that all posts (excluding today's post) sorted by comment count.
This [example](http://wordpress.stackexchange.com/questions/159228/combining-two-wordpress-queries-with-pagination-is-not-working) was provided by Robert Hue.

We can add the following code into our theme or in a plugin:

    /**
     * Example #1 - Combine two sub queries:
     */

    //-----------------
    // Query part #1:
    //-----------------
    $args1 = array( 
        'post_type'           => 'post',
        'orderby'             => 'comment_count',
        'ignore_sticky_posts' => 1,  
        'date_query'          => array(
            array(
                'after' => date('Y-m-d'),
            ),
            'inclusive'  => true,
         )
    );

    //-----------------   
    // Query part #2:
    //-----------------
    $args2 = array(
        'post_type'           => 'post',
        'orderby'             => 'comment_count',
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
       'sublimit'       => 1000,
       'args'           => array( $args1, $args2 ),
    );

    if( class_exists( 'WP_Combine_Queries' ) ):

        $results = new WP_Combine_Queries( $args );
       
        // Loop:
        if( $results->have_posts() ):         
            while( $results->have_posts() ): $results->the_post();
    	        the_title();	    
            endwhile;
        else:
            _e( 'Sorry no posts found!' ); 
        endif;
	 
    endif;

###Example 2:

We could also combine more than two sub queries:

    /**
     * Example #2 - Combine four sub queries:
     */

     $args = array( 
         'posts_per_page' => 10,
         'paged'          => 1,
         'sublimit'       => 1000,
         'args'           => array( $args1, $args2, $args3, $args4 ),
      );

      if( class_exists( 'WP_Combine_Queries' ) ):

          $results = new WP_Combine_Queries( $args );
          
          // Loop: ...

      endif;

###Changelog

0.0.3
 - Added: GPL2+ License part
 - Removed: Dropped namespace + anonymous function for wider PHP support.

0.0.2 
 - Added: Input parameter 'union' with possible values UNION and UNION ALL.
 - Fixed: Empty paged resulted in a sql error. ( Props: Robert Hue)

