Combined Query
=================

WordPress plugin - Combined Query

### Description

This experimental plugin allows you to combine multiple `WP_Query` queries into a single one, using the `combined_query` attribute.

This started as an answer on Stackoverflow, see [here](http://stackoverflow.com/questions/23555109/wordpress-combine-queries/) and [here](http://wordpress.stackexchange.com/questions/159228/combining-two-wordpress-queries-with-pagination-is-not-working/).

The idea behind this plugin is to combine the SQL queries for each `WP_Query()` query with `UNION` or `UNION ALL`.

I first noticed this technique in a [great answer on WordPress Development](http://wordpress.stackexchange.com/a/912/26350) by Mike Schinkel.

I use the trick mentioned [here](http://stackoverflow.com/a/7587423/2078474) to preserve the order of `UNION` sub queries. 

This implementation supports combining `N` sub-queries.


### Notice about the new 1.0.0 version

This version is a total rewrite of the plugin. 

The `WP_Combine_Query` class has been removed in favour of simply using the `combined_query` attribute of the `WP_Query` class.

Now the plugin only supports PHP versions 5.4+.
	
### Settings 

The supported settings for the `combined_query` attribute:

    'combined_query' => [        
        'args'           => [ $args1, $args2, ... ], // Array (default [])
        'union'          => 'UNION',                 // String Possible values are UNION or UNION ALL (default UNION)
		'posts_per_page' => 10,                      // Integer 1,2,...
		'offset'         => 0,                       // Integer 0,1,...
		'orderby'        => 'meta_value_num',        // String (post_name, ..., name, ..., none, meta_value, meta_value_num )
		'order'          => 'DESC',                  // String (ASC,DESC)
     ]

If you want to remove duplicated posts use `UNION`, else use `UNION ALL`.
	 
### Custom filters

There are two custom filters currently available:

    // Modify combined ordering:
    add_filter( 'cq_orderby', function( $orderby ) {
        return $orderby;
    });
    
    // Modify sub fields:
    add_filter( 'cq_sub_fields', function( $fields ) {
        return $fields;
    });


To keep the order by arguments arg1, arg2, ... use:

    'combined_query' => [
        ...
        'orderby' => 'none',
        ...
    ]

or

    add_filter( 'cq_orderby', '__return_empty_string' );
    $query = new WP_Query( $args );
    remove_filter( 'cq_orderby', '__return_empty_string' );


### Installation

Upload the plugin to the plugin folder and activate it.

To install dependencies with Composer (not required):

    composer install
	
or

    php composer.phar install

within our folder. See [here](https://getcomposer.org/doc/00-intro.md) for more information on how to install Composer.
	
Then play with the examples below, in your theme or in a plugin. 

Have fun ;-)

### Example 1: 

Here we want to display the first published page in an alphabetical order and then the three oldest published posts:

	//-----------------
	// Sub query #1:
	//-----------------
	$args1 = [
		'post_type'      => 'page',
		'posts_per_page' => 1,
		'orderby'        => 'title',
		'order'          => 'asc',
	];

	//-----------------
	// Sub query #2:
	//-----------------
	$args2 = [
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'asc',
	];

	//---------------------------
	// Combined queries #1 + #2:
	//---------------------------
	$args = [
		'combined_query' => [        
		'args'           => [ $args1, $args2 ],
		'union'          => 'UNION',
		'posts_per_page' => 4,
		'orderby'        => 'none',
	]
	];

	//---------
	// Output:
	//---------
	$q = new WP_Query( $args );
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


### Example 2: 

Here we want to display all foo posts with a date query, sorted by comment count and after that all bar posts sorted by comment count.
Then we sort all by decreasing comment count.

    //-----------------
    // Sub query #1:
    //-----------------
    $args1 = [ 
        'post_type'           => 'foo',
        'orderby'             => 'comment_count',
	'order'               => 'desc',
        'posts_per_page'      => 100, // adjust to your needs
        'date_query'          => [
            [
                'after' => date('Y-m-d'),
            ],
            'inclusive'  => true,
         ]
    ];

    //-----------------   
    // Sub query #2:
    //-----------------
    $args2 = [
        'post_type'           => 'bar',
        'orderby'             => 'comment_count',
	'order'               => 'desc',
        'posts_per_page'      => 100, // adjust to your needs
    ];

    //--------------------------- 
    // Combined queries #1 + #2:
    //---------------------------
    $args = [
        'combined_query' => [        
		'args'                => [ $args1, $args2 ],
		'posts_per_page'      => 5,
		'paged'               => 2,
		'orderby'             => 'comment_count',
		'order'               => 'desc',
        ]
    );

    //---------
    // Output:
    //---------
    // See example 1


### Example 3:

Let's combine two meta queries and order by a common meta value:

    //-----------------
    // Sub query #1:
    //-----------------
    $args1 = [
       'post_type'      => 'cars',
       'posts_per_page' => 10,
       'orderby'        => 'title',
       'order'          => 'asc',
       'meta_query'     => [
            [
                'key'      => 'doors',
                'value'    => 0,
                'compare'  => '>=',
                'type'     => 'UNSIGNED'
            ],
        ],
    ];

    //-----------------
    // Sub query #2:
    //-----------------
    $args2 = [
       'post_type'      => 'post',
       'posts_per_page' => 10,
       'orderby'        => 'date',
       'order'          => 'desc',
       'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => 'cars',
            ],
        ],
        'meta_query'     => [
            [
                'key'      => 'doors',
                'value'    => 0,
                'compare'  => '>=',
                'type'     => 'UNSIGNED'
            ],
        ],  
    ];


    //------------------------------
    // Order by a common meta value
    //------------------------------

    // Modify sub fields:
    add_filter( 'cq_sub_fields', $callback = function( $fields ) {
        return $fields . ', meta_value';
    });

    //---------------------------
    // Combined queries #1 + #2:
    //---------------------------
    $args = [
        'combined_query' => [        
		'args'           => [ $args1, $args2 ],
		'posts_per_page' => 5,
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
        ]
    ];

    //---------
    // Output:
    //---------
    // See example 1

	remove_filter( 'cq_sub_fields', $callback );

### Example 4:

We could also combine more than two sub queries, here's an example of four sub-queries:

     $args = [ 
        'combined_query' => [        
		'args' => [ $args1, $args2, $args3, $args4 ],
		...
        ]
      ];

    //---------
    // Output:
    //---------
    // See example 1


### Example 5:

The above examples are all for secondary queries. So let's add a query to the main home query:

    add_action( 'pre_get_posts', function( \WP_Query $q ) {   
        
	if( $q->is_home() && $q->is_main_query() ) {
	
		//-----------------
		// Sub query #1:
		//-----------------
		$args1 = [
		   'post_type'	    => 'page',
		   'posts_per_page' => 1,
		   'orderby'        => 'title',
		   'order'          => 'asc',
		];

		//-----------------
		// Original query #2:
		//-----------------    
		$args2 = $q->query;

		//---------------------------
		// Combined queries #1 + #2:
		//---------------------------
		$args = [
			'combined_query' => [
				'args'           => [ $args1, $args2 ],
				'union'          => 'UNION',
				'posts_per_page' => 4,
				'orderby'        => 'none',
			]
		];
        
		//-----------------------
		// Modify the Main query:
		//-----------------------
		$q->set( 'combined_query', $args['combined_query'] );
        }
    } );




### Changelog

1.2.1 (2020-09-14)
 - Fixed: Readme.

1.2.0 (2020-09-14)
 - Added: Support for ordering by 'none'.
 - Added: Test cases.
 - Added: Support for adding parameters (posts_per_page, offset, orderby, order) inside combined_query args.
 - Fixed: Hooks handling for pre_get_posts example. 
 - Adjusted: Examples.
 
1.1.1 (2020-09-04)
 - Added:    Example how to keep the order by arguments arg1, arg2, ...
 - Adjusted: UNION ALL test

1.1.0 (2020-09-04)
 - Added:   Ticket #19 - Add test cases for argument order workaround. (Props: therealgilles)
 - Cleanup: phpcs
 
1.0.5 (2016-05-08)
 - Fixed:    Ticket #8 - Fallback for those who don't use Composer.
 - Improved: Removed an explicit call to $GLOBALS['wpdb'] through the use keyword.
 - Improved: Simplified the namespace to only CombinedQuery.

1.0.4 (2016-04-21)
 - Fixed:    Adjusted the paged bug that sneaked in with verion 1.0.2 yesterday.
 - Improved: Simplified the example that uses get_query_var() that can now handle default as an input parameter.

1.0.3 (2016-04-21)
 - Fixed:    Ticket #7 - Not able to set the "UNION ALL" union option
 - Improved: Inline docs

1.0.2 (2016-04-20)
 - Fixed: Ticket #6 - Escape % in the Generator class. (Props: @DArcMattr)
 - Improved: Inline docs

1.0.1 (2015-11-09)
 - Fixed: Remove vendor dependency and let the user install it via 'composer install' (Props: @pdufour)
 - Fixed: Ignore sticky posts in the EmptyQuery class
 
1.0.0 (2015-05-10)
 - ** Total Plugin Rewrite ** 
 - Closed: Ticket #3
 - Added: New classes Main, EmptyQuery and Generator. 
 - Added: Support for 'combined_query' attribute of the WP_Query class.
 - Added: Support only for PHP 5.4+
 - Added: Autoload via Composer. 
 - Added: New filter 'cq_sub_fields' instead of 'cq_sub_fields'
 - Added: New filter 'cq_orderby' instead of 'cq_orderby'

0.1.3 (2015-05-09)
 - Added: Support for ignory_sticky_posts.
 - Fixed: Minor 

0.1.2 (2015-05-08)
 - Added: Support for the GitHub Updater. 
 - Added: New filter 'wcq_sub_fields' 
 - Added: New filter 'wcq_orderby' 
 - Added: New example for meta value ordering
 - Fixed: Ordering didn't work correctly.

0.1.1
 - Changed: Coding style and autoloading (Props: @egill)

0.1  Various plugin improvements, for example:
 - Added: orderby in the combined query.
 - Added: posts_per_page in the sub queries.
 - Added: offset in the sub queries.
 - Added: paged in the sub queries.
 - Removed: sublimit in the combined query, use posts_per_page instead in sub queries.
 - Fixed: Issue #1 related to max_num_pages (Props: @hellofantastic).

0.0.4
 - Added: support for offset in the combined query

0.0.3
 - Added: GPL2+ License part
 - Removed: Dropped namespace + anonymous function for wider PHP support.

0.0.2 
 - Added: Input parameter 'union' with possible values UNION and UNION ALL.
 - Fixed: Empty paged resulted in a sql error. (Props: Robert Hue)
