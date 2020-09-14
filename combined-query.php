<?php
/**
 * Plugin Name:  Combined Query
 * Description:  This plugin allows you to combine multiple WP_Query() queries, into a single one.
 * Plugin URI:   https://github.com/birgire/wp-combined-queries
 * Author:       birgire
 * GitHub Plugin URI: https://github.com/birgire/wp-combined-queries.git
 * Author URI:   https://github.com/birgire
 * License:      MIT
 * Version:      1.2.1
 */

namespace CombinedQuery;

/**
 * Init.
 */
add_action(
	'init',
	function() {

		global $wpdb;

		// Composer autoload.
		if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			require __DIR__ . '/vendor/autoload.php';
		}
		// Fallback for those who don't use Composer.
		else {
			require_once __DIR__ . '/includes/Main.php';
			require_once __DIR__ . '/includes/Generator.php';
			require_once __DIR__ . '/includes/EmptyQuery.php';
		}

		if ( class_exists( __NAMESPACE__ . '\\Main' ) ) {
			$main = new Main();
			$main->init( new Generator( new EmptyQuery() ), $wpdb );
		}

	}
);

