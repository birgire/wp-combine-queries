<?php

/**
 * Class WP_Combine_Queries
 *
 * @since 0.0.1
 * @uses WP_Query_Empty
 *
 */
class WP_Combine_Queries extends WP_Query {
	/**
	 * Array of input arguments
	 * @var    array
	 * @since  0.0.1
	 */
	protected $args = array();

	/**
	 * Array of sub SQL queries
	 * @var    array
	 * @since  0.0.1
	 */
	protected $sub_sql = array();

	/**
	 * Generated SQL query
	 * @var    string
	 * @since  0.0.1
	 */
	protected $sql = '';

	/**
	 * Order SQL query
	 * @var    string
	 * @since  0.0.1
	 */
	protected $orderby = '';

	/**
	 * The constructor
	 *
	 * @since  0.0.1
	 * @param  array $args
	 * @return object WP_Query
	 */
	public function __construct( $args = array() ) {
		// Default setup:
		$defaults = array(
			'posts_per_page' => 10,
			'paged'          => 1,
			'union'          => 'UNION',
			'args'           => array(),
		);

		$this->args = wp_parse_args( $args, $defaults );

		// Make sure paged > 0:
		$this->args['paged'] = ( 0 < $this->args['paged'] ) ? $this->args['paged'] : 1;

		// Make sure offset >= 0:
		$this->args['offset'] = ( isset( $this->args['offset'] ) ) ? (int) $this->args['offset'] : 0;

		// Make sure we have UNION or UNION ALL:
		$this->args['union'] = ( ! in_array( strtoupper( $this->args['union'] ), array( 'UNION', 'UNION ALL' ) ) ) ? $this->args['union'] : 'UNION';

		// Setup SQL generation:
		add_filter( 'posts_request',  array( $this, 'posts_request' ), PHP_INT_MAX  );

		// Setup SQL generation:
		add_filter( 'posts_orderby',  array( $this, 'posts_orderby' ), PHP_INT_MAX  );

		// Setup parent WP_Query constructor:
		$parents_args = array();

		// posts_per_page:
		if ( isset( $this->args['posts_per_page'] ) ) {
			$parents_args['posts_per_page'] = $this->args['posts_per_page'];
		}

		// order:
		if ( isset( $this->args['order'] ) ) {
			$parents_args['order'] = $this->args['order'];
		}

		// orderby:
		if ( isset( $this->args['orderby'] ) ) {
			$parents_args['orderby'] = $this->args['orderby'];
		}

		// offset:
		if ( isset( $this->args['offset'] ) ) {
			$parents_args['offset'] = $this->args['offset'];
		}

		// paged:
		if ( isset( $this->args['paged'] ) ) {
			$parents_args['paged'] = $this->args['paged'];
		}

		// Call the parent WP_Query constructor:
		parent::__construct( $parents_args );

	}

	/**
	* Get the SQL for the ordering
	*
	* @since  0.1
	* @param  string $orderby
	* @return string $orderby
	*/
	public function posts_orderby( $orderby ) {
		remove_filter( current_filter(), array( $this, __FUNCTION__ ), PHP_INT_MAX  );
		$this->orderby = $orderby;
		return $orderby;
	}

	/**
	* Construct the SQL query from sub queries
	*
	* @since  0.0.1
	* @param  string $request
	* @return string $request
	*/
	public function posts_request( $request ) {
		remove_filter( current_filter(), array( $this, __FUNCTION__ ), PHP_INT_MAX  );

		// Collect the generated SQL for each sub-query:
		foreach ( (array) $this->args['args'] as $a ) {
			// Fetch the generated SQL sub queries:
			$q = new WP_Query_Empty( $a );
			$this->sub_sql[] = $q->get_sql();
			unset( $q );
		}

		// Order by:
		$orderby = '';
		if ( isset( $this->args['orderby'] ) ) {
			$orderby = str_replace( $GLOBALS['wpdb']->posts . '.', '', $this->orderby );
		}

		$orderby = apply_filters( 'wcq_orderby', $orderby );

                if( ! empty( $orderby ) ){
			$orderby = ' ORDER BY ' . $orderby;
		}

		// Combine all the sub-queries into a single SQL query.
		$request = '';
		if ( 0 < count( $this->sub_sql ) ) {
			$unions  = '(' . join( ') ' . $this->args['union'] . ' (', $this->sub_sql ) . ' ) ';
			$request = sprintf( "SELECT SQL_CALC_FOUND_ROWS * FROM ( {$unions} ) as combined {$orderby} LIMIT %s,%s",
				$this->args['posts_per_page'] * ( $this->args['paged'] - 1 ) + $this->args['offset'],
				$this->args['posts_per_page']
			);
		}
		echo $request;
		return $request;
	}

} // end class

