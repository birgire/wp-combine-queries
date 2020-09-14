<?php

namespace CombinedQuery;

use \WP_Query as WP_Query;

/**
 * Class EmptyQuery
 *
 * @since 1.0.0
 */
class EmptyQuery extends WP_Query {

	/**
	 * Array of input arguments
	 *
	 * @since  1.0.0
	 * @var    array
	 */
	protected $args = [];


	/**
	 * SQL query string
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	protected $sql = '';


	/**
	 * Activate the empty query mode
	 *
	 * @since  1.0.0
	 */
	public function cq_activate() {
		add_action( 'pre_get_posts', [ $this, 'cq_pre_get_posts' ], PHP_INT_MAX );
		add_filter( 'posts_fields', [ $this, 'cq_posts_fields' ], PHP_INT_MAX );
		add_filter( 'posts_request', [ $this, 'cq_posts_request' ], PHP_INT_MAX );
	}


	/**
	 * De-activation the empty query mode
	 *
	 * @since  1.0.0
	 */
	public function cq_deactivate() {
		remove_action( 'pre_get_posts', [ $this, 'cq_pre_get_posts' ], PHP_INT_MAX );
		remove_filter( 'posts_request', [ $this, 'cq_posts_request' ], PHP_INT_MAX );
		remove_filter( 'posts_fields', [ $this, 'cq_posts_fields' ], PHP_INT_MAX );
	}


	/**
	 * Callback for the 'pre_get_posts' hook.
	 *
	 * @since  1.0.0
	 * @param  WP_Query $q WP_Query object.
	 */
	public function cq_pre_get_posts( WP_Query $q ) {

		// If posts_per_page is -1, then there's no limit set,
		// but we do need a limit to be able to keep the order of UNION sub-queries
		// @see http://stackoverflow.com/a/7587423/2078474
		if ( $q->get( 'posts_per_page' ) && '-1' == $q->get( 'posts_per_page' ) ) {
			$q->set( 'posts_per_page', 999999 );
		}

		// Remove SQL_CALC_FOUND_ROWS from the SQL query.
		$q->set( 'no_found_rows', 1 );

		// Ignore sticky posts
		$q->set( 'ignore_sticky_posts', 1 );
	}


	/**
	 * Post fields.
	 *
	 * @since  1.0.0
	 * @param  array $fields Fields.
	 * @return array $fields Fields.
	 */
	public function cq_posts_fields( $fields ) {
		return apply_filters( 'cq_sub_fields', $fields );
	}


	/**
	 * Don't run the query (empty query).
	 *
	 * @since  1.0.0
	 * @param  string $request Request.
	 * @return string $request Request.
	 */
	public function cq_posts_request( $request ) {
		// Store the current SQL query.
		$this->sql = $request;

		// Return an empty SQL query.
		$request = '';

		return $request;
	}


	/**
	 * Get the SQL query.
	 *
	 * @since  1.0.0
	 * @return string $sql SQL.
	 */
	public function cq_get_sql() {
		return $this->sql;
	}

}
