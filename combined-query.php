<?php
/**
 * Plugin Name:  Combined Query
 * Description:  This plugin allows you to combine multiple WP_Query() queries, into a single one.
 * Plugin URI:   https://github.com/birgire/wp-combined-queries
 * Author:       birgire
 * GitHub Plugin URI: https://github.com/birgire/wp-combined-queries.git
 * Author URI:   https://github.com/birgire
 * License:      MIT
 * Version:      1.0.1
 */

namespace Birgir\CombinedQuery;


/**
 * Autoload
 */

\add_action( 'plugins_loaded', function()
{
    if ( file_exists( __DIR__ . '/vendor/autoload.php' ) )
    {
        require __DIR__ . '/vendor/autoload.php';
    }
} );


/**
 * Init
 */
\add_action( 'init', function()
{
    if( class_exists( __NAMESPACE__ . '\\Main' ) )
    {
     	$o = new Main;
        $o->init( new Generator( new EmptyQuery ), $GLOBALS['wpdb'] );
    }

} );

