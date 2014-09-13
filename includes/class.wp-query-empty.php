<?php
/**
 * Class WP_Query_Empty
 *
 * @since 0.0.1
 */
class WP_Query_Empty extends WP_Query {
	/**
	 * Array of input arguments
	 * @var    array
	 * @since  0.0.1
	 */
	protected $args = array();

	/**
	 * SQL query string
	 * @var    string
	 * @since  0.0.1
	 */
	protected $sql = '';

	/**
	 * The constructor
	 *
	 * @since  0.0.1
	 * @param  array $args
	 * @return object WP_Query
	 */
	public function __construct( $args = array() ) {
		$this->args = $args;

		// If posts_per_page is -1, then there's no limit set,
		// but we do need a limit to be able to keep the order of UNION sub-queries:
		// @see http://stackoverflow.com/a/7587423/2078474
		if ( isset( $this->args['posts_per_page'] ) && '-1' == $this->args['posts_per_page'] )
			$this->args['posts_per_page'] = 999999;

		// Remove SQL_CALC_FOUND_ROWS from the SQL query:
		$this->args['no_found_rows'] = 1;

		// Create an empty query:
		add_filter( 'posts_request',  array( $this, 'posts_request' ), PHP_INT_MAX  );

		// Call the parent WP_Query constructor:
		parent::__construct( $this->args );
	}

	/**
	 * Construct the SQL query from sub queries
	 *
	 * @since  0.0.1
	 * @param  string $request
	 * @return string $request
	 */
	public function posts_request( $request ) {
		remove_filter( current_filter(), array( $this, __FUNCTION__ ), PHP_INT_MAX );

		// Store the current SQL query:
		$this->sql = $request;

		// Return an empty SQL query:
		return '';
	}

	/**
	 * Get the SQL query
	 *
	 * @since  0.0.1
	 * @return string $sql
	 */
	public function get_sql( ) {
		return $this->sql;
	}

} // end class
