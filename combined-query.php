<?php
/**
 * Plugin Name:  Combined Query
 * Description:  This plugin allows you to combine multiple WP_Query() queries, into a single one.
 * Plugin URI:   https://github.com/birgire/wp-combined-queries
 * Author:       birgire
 * GitHub Plugin URI: https://github.com/birgire/wp-combined-queries.git
 * Author URI:   https://github.com/birgire
 * License:      MIT
 * Version:      1.0.5
 */

namespace CombinedQuery;

/**
 * Init
 */
\add_action( 'init', function() use ( &$wpdb )
{
    // Composer autoload
    if ( file_exists( __DIR__ . '/vendor/autoload.php' ) )
    {
        require __DIR__ . '/vendor/autoload.php';
    }
    // Fallback for those who don't use Composer
    else
    {
        require( __DIR__ . '/includes/Main.php' );
        require( __DIR__ . '/includes/Generator.php' );
        require( __DIR__ . '/includes/EmptyQuery.php' );
    }

    // PS: It's propably depatable to use an autoloader  when we hook into the 'init' action to create our instances ;-)

    if( class_exists( __NAMESPACE__ . '\\Main' ) )
    {
     	$main = new Main;
        $main->init( new Generator( new EmptyQuery ), $wpdb );
    }

} );

