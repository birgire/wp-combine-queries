<?php

/**
 * Class WP_Query_Empty
 *
 */

class WP_Query_Empty extends WP_Query 
{
    protected $args      = array();
    protected $sql       = '';
    protected $limits    = '';
    protected $sublimit  = 0;

    public function __construct( $args = array(), $sublimit = 1000 )
    {
        $this->args     = $args;
        $this->sublimit = $sublimit;

        add_filter( 'posts_clauses',  array( $this, 'posts_clauses' ), PHP_INT_MAX  );
        add_filter( 'posts_request',  array( $this, 'posts_request' ), PHP_INT_MAX  );

        parent::__construct( $args );
    }

    public function posts_request( $request )
    {
        remove_filter( current_filter(), array( $this, __FUNCTION__ ), PHP_INT_MAX );
        $this->sql = $this->modify( $request );             
        return '';
    }

    public function posts_clauses( $clauses )
    {
        remove_filter( current_filter(), array( $this, __FUNCTION__ ), PHP_INT_MAX  );
        $this->limits = $clauses['limits'];
        return $clauses;
    }

    protected function modify( $request )
    {
        $request = str_ireplace( 'SQL_CALC_FOUND_ROWS', '', $request );

        if( $this->sublimit > 0 )
            return str_ireplace( $this->limits, sprintf( 'LIMIT %d', $this->sublimit ), $request );
        else
            return $request;
    }

   public function get_sql( )
    {
        return $this->sql;
    }

} // end class
