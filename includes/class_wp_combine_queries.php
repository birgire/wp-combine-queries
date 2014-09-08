<?php

/**
 * Class WP_Combine_Queries
 * 
 * @uses WP_Query_Empty
 *
 */

class WP_Combine_Queries extends WP_Query 
{
    protected $args    = array();
    protected $sub_sql = array();
    protected $sql     = '';

    public function __construct( $args = array() )
    {
        $defaults = array(
            'sublimit'       => 1000,
            'posts_per_page' => 10,
            'paged'          => 1,
            'union'          => 'UNION',
            'args'           => array(),
        );

        $this->args = wp_parse_args( $args, $defaults );

        // Make sure paged > 0:
	$this->args['paged'] = ( $this->args['paged'] > 0 ) ? $this->args['paged'] : 1;

        // Make sure offset >= 0:
	$this->args['offset'] = ( isset( $this->args['offset'] ) ) ? (int) $this->args['offset'] : 0;

        // Make sure we have UNION or UNION ALL:
	$this->args['union'] = ( ! in_array( strtoupper( $this->args['union'] ), array( 'UNION', 'UNION ALL' ) ) ) ? $this->args['union'] : 'UNION';

        add_filter( 'posts_request',  array( $this, 'posts_request' ), PHP_INT_MAX  );

        parent::__construct( array( 'post_type' => 'post' ) );
    }

    public function posts_request( $request )
    {
        remove_filter( current_filter(), array( $this, __FUNCTION__ ), PHP_INT_MAX  );

        // Collect the generated SQL for each sub-query:
        foreach( (array) $this->args['args'] as $a )
        {
            $q = new WP_Query_Empty( $a, $this->args['sublimit'] );
            $this->sub_sql[] = $q->get_sql();
            unset( $q );
        }

        // Combine all the sub-queries into a single SQL query.
        // We must have at least two subqueries:
        if ( count( $this->sub_sql ) > 1 )
        {

            $unions = '(' . join( ') ' . $this->args['union'] . ' (', $this->sub_sql ) . ' ) ';

            $request = sprintf( "SELECT SQL_CALC_FOUND_ROWS * FROM ( {$unions} ) as combined LIMIT %s,%s",
                $this->args['posts_per_page'] * ( $this->args['paged']-1 ) + $this->args['offset'],
                $this->args['posts_per_page']
            );          
        }
        return $request;
    }

} // end class

