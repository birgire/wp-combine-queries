<?php 
/**
 * Plugin Name: Combine Queries
 * Description: This plugin allows you to combine multiple WP_Query() queries, into a single one, with the WP_Combine_Query() class. 
 * Plugin URI:  https://github.com/birgire/wp-combine-queries
 * Author:      birgire
 * Author URI:  https://github.com/birgire
 * Version:     0.0.2
 */

namespace birgire;

/**
 * Minimum PHP version:
 */
define( 'COMBINE_QUERIES_MIN_PHP_VER', '5.3.3' );


/**
 * Autoload classes:
 */

spl_autoload_register(
    function( $class_name )
    {
        $path_part 	= plugin_dir_path( __FILE__ );
        $arr 		= explode( '\\', $class_name );
        $name_part 	= strtolower( array_pop( $arr ) );            
        $file_name 	= sprintf( '%sincludes/class_%s.php', $path_part, $name_part );
        if( file_exists( $file_name ) )
        {
            require_once( $file_name );
        }
    }
);

