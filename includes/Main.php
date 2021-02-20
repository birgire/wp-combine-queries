<?php

namespace CombinedQuery;

use \WP_Query as WP_Query;
use \wpdb as wpdb;

/**
 * Class Main.
 *
 * @since 1.0.0
 */
class Main {

	/**
	 * @var string
	 */
	private $orderby;


	/**
	 * @var array
	 */
	private $combined_query;


	/**
	 * @var \CombinedQuery\Generator
	 */
	private $generator;


	/**
	 * @var wpdb
	 */
	private $db;


	/**
	 * Init.
	 *
	 * @since  1.0.0
	 *
	 * @param  \CombinedQuery\Generator $generator
	 * @param  wpdb                     $db
	 */
	public function init( Generator $generator, wpdb $db ) {
		$this->generator = $generator;
		$this->db        = $db;

		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], PHP_INT_MAX );
	}


	/**
	 * Callback for the 'pre_get_posts' hook.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Query $q
	 */
	public function pre_get_posts( WP_Query $q ) {

		if ( ! $q->get( 'combined_query' ) ) {
			return;
		}

		// Default arguments.
		$defaults = [
			'union' => 'UNION',
			'args'  => [],
		];

		$this->combined_query = wp_parse_args( $q->get( 'combined_query' ), $defaults );

		// Setup SQL generation.
		add_filter( 'posts_request', [ $this, 'posts_request' ], PHP_INT_MAX, 2 );

		// Get the orderby part.
		add_filter( 'posts_orderby', [ $this, 'posts_orderby' ], PHP_INT_MAX );

	}

	/**
	 * Callback for the 'posts_request' filter.
	 *
	 * @since  1.0.0
	 *
	 * @param  string   $request
	 * @param  WP_Query $q
	 * @return string
	 */
	public function posts_request( $request, WP_Query $q ) {

		if ( ! $q->get( 'combined_query' ) ) {
			return $request;
		}

		remove_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], PHP_INT_MAX );
		remove_filter( 'posts_request', [ $this, 'posts_request' ], PHP_INT_MAX );
		remove_filter( 'posts_orderby', [ $this, 'posts_orderby' ], PHP_INT_MAX );

		// Combine all the sub-queries into a single SQL query.
		$generated_request = $this->generator->get_request(
			$this->combined_query['args'],
			$this->get_union(),
			$this->get_ordering( $q ),
			$this->get_posts_per_page( $q ),
			$this->get_paged( $q ),
			$this->get_offset( $q )
		);

		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], PHP_INT_MAX );
		add_filter( 'posts_request', [ $this, 'posts_request' ], PHP_INT_MAX, 2 );
		add_filter( 'posts_orderby', [ $this, 'posts_orderby' ], PHP_INT_MAX );

		return empty( $generated_request ) ? $request : $generated_request;
	}


	/**
	 * Callback for the 'posts_orderby' filter
	 *
	 * @since  1.0.0
	 *
	 * @param  string $orderby
	 * @return string $orderby
	 */
	public function posts_orderby( $orderby ) {
		$this->orderby = $orderby;
		return $orderby;
	}


	/**
	 * Get posts per page
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Query $q
	 * @return int
	 */
	public function get_posts_per_page( WP_Query $q ) {

		if ( isset( $q->query_vars['combined_query']['posts_per_page'] ) ) {
			$posts_per_page = (int) $q->query_vars['combined_query']['posts_per_page'];
		} elseif ( isset( $q->query_vars['posts_per_page'] ) ) {
			$posts_per_page = (int) $q->query_vars['posts_per_page'];
		} else {
			$posts_per_page = (int) get_option( 'posts_per_page' );
		}

		return $posts_per_page;
	}


	/**
	 * Get offset.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Query $q
	 * @return int
	 */
	public function get_offset( WP_Query $q ) {

		if ( isset( $q->query_vars['combined_query']['offset'] ) ) {
			$offset = (int) $q->query_vars['combined_query']['offset'];
		} elseif ( isset( $q->query_vars['offset'] ) ) {
			$offset = (int) $q->query_vars['offset'];
		} else {
			$offset = 0;
		}

		return $offset;
	}

	/**
	 * Get paged.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Query $q
	 * @return int
	 */
	public function get_paged( WP_Query $q ) {
		if ( isset( $q->query_vars['combined_query']['paged'] ) && 0 < (int) $q->query_vars['combined_query']['paged'] ) {
			$paged = (int) $q->query_vars['combined_query']['paged'];
		} elseif ( isset( $q->query_vars['paged'] ) && 0 < (int) $q->query_vars['paged'] ) {
			$paged = (int) $q->query_vars['paged'];
		} else {
			$paged = 1;
		}
		return $paged;
	}


	/**
	 * Get orderby.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Query $q
	 * @return string
	 */
	public function get_ordering( WP_Query $q ) {
		if ( isset( $q->query_vars['combined_query']['orderby'], $q->query_vars['combined_query']['order'] ) ) {
			$ordering = $this->parse_orderby( $q->query_vars['combined_query']['orderby'] ) . ' ' . $this->parse_order( $q->query_vars['combined_query']['order'] );
		} elseif ( isset( $q->query_vars['combined_query']['orderby'] ) ) {
			$ordering = $this->parse_orderby( $q->query_vars['combined_query']['orderby'] ) . ' ' . $this->parse_order( '' );
		} elseif ( isset( $q->query_vars['combined_query']['order'] ) ) {
			$ordering = $this->parse_orderby( '' ) . ' ' . $this->parse_order( $q->query_vars['combined_query']['order'] );
		} elseif ( $this->orderby ) {
			$ordering = isset( $this->orderby ) ? str_replace( $this->db->posts . '.', 'combined.', $this->orderby ) : '';
		}

		// Orderby none.
		if ( in_array( trim( $ordering ), [ 'ASC', 'DESC' ], true ) ) {
			$ordering = '';
		}

		$ordering = apply_filters( 'cq_orderby', $ordering );
		if ( ! empty( $ordering ) ) {
			$ordering = ' ORDER BY ' . $ordering;
		}

		return $ordering;
	}

	/**
	 * Parse orderby.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $orderby
	 * @return string $orderby
	 */
	protected function parse_orderby( $orderby ) {

		switch ( $orderby ) {
			case 'post_name':
			case 'post_author':
			case 'post_date':
			case 'post_title':
			case 'post_modified':
			case 'post_parent':
			case 'post_type':
			case 'ID':
			case 'menu_order':
			case 'comment_count':
				$orderby_clause = "combined.{$orderby}";
				break;
			case 'name':
			case 'author':
			case 'date':
			case 'title':
			case 'modified':
			case 'parent':
			case 'type':
				$orderby_clause = "combined.post_{$orderby}";
				break;
			case 'none':
				$orderby_clause = '';
				break;
			case 'meta_value':
				$orderby_clause = 'combined.meta_value';
				break;
			case 'meta_value_num':
				$orderby_clause = 'combined.meta_value+0';
				break;
			default:
				$orderby_clause = 'combined.post_date';
				break;
		}

		return $orderby_clause;
	}

	/**
	 * Parse order.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $order
	 * @return string $order
	 */
	protected function parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'DESC';
		}

		return ( 'ASC' === strtoupper( $order ) ) ? 'ASC' : 'DESC';
	}

	/**
	 * Get union.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_union() {
		return in_array( strtoupper( $this->combined_query['union'] ), [ 'UNION', 'UNION ALL' ] )
			? strtoupper( $this->combined_query['union'] )
			: 'UNION';
	}

}

