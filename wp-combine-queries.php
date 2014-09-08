<?php 
/**
 * Plugin Name: Combine Queries
 * Description: This plugin allows you to combine multiple WP_Query() queries, into a single one, with the WP_Combine_Query() class. 
 * Plugin URI:  https://github.com/birgire/wp-combine-queries
 * Author:      birgire
 * Author URI:  https://github.com/birgire
 * License:     GPL2+
 * Version:     0.0.4
 */

/*
    Copyright 2014 birgire

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/**
 * Autoload classes:
 */

spl_autoload_register( 'combine_queries_autoload' );

function combine_queries_autoload( $class_name )
{
    $path_part 	= plugin_dir_path( __FILE__ );
    $arr 	= explode( '\\', $class_name );
    $name_part 	= strtolower( array_pop( $arr ) );            
    $file_name 	= sprintf( '%sincludes/class_%s.php', $path_part, $name_part );
    if( file_exists( $file_name ) )
        require_once( $file_name );

}

