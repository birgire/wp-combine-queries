<?php
/**
 * Plugin Name: Combine Queries
 * Description: This plugin allows you to combine multiple WP_Query() queries, into a single one, with the WP_Combine_Query() class.
 * Plugin URI:  https://github.com/birgire/wp-combine-queries
 * Author:      birgire
 * Author URI:  https://github.com/birgire
 * License:     GPL2+
 * Version:     0.1.1
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

define( 'WP_COMBINE_ROOT', dirname( __FILE__ ) );

add_action( 'init', 'wp_combine_queries_classloader' );

function wp_combine_queries_classloader() {
	spl_autoload_register( 'wp_combine_queries_autoloader' );
}

function wp_combine_queries_autoloader() {
	if ( file_exists( WP_COMBINE_ROOT . '/includes/class.wp-combine-queries.php' ) ) {
		require_once ( WP_COMBINE_ROOT . '/includes/class.wp-combine-queries.php' );
	}
	if ( file_exists( WP_COMBINE_ROOT . '/includes/class.wp-query-empty.php' ) ) {
		require_once ( WP_COMBINE_ROOT . '/includes/class.wp-query-empty.php' );
	}
}

