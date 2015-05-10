<?php

namespace Birgir\CombinedQuery;

/**
 * Class Main
 *
 * @since 1.0.0
 */

class Main
{
    private $orderby        = '';
    private $combined_query = [];
    private $generator      = null;
    private $db             = null;
                              	
    public function init( Generator $generator, \wpdb $db )
    {
        $this->generator = $generator;
        $this->db = $db;

        add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], PHP_INT_MAX );
    }

    public function pre_get_posts( \WP_Query $q )
    {
     	if( $q->get( 'combined_query' ) )
        {
            $defaults = [
                'union'          => 'UNION',
                'args'           => [],
            ];
            $this->combined_query = wp_parse_args( $q->get( 'combined_query' ), $defaults );

            // Setup SQL generation:
            add_filter( 'posts_request',  [ $this, 'posts_request' ], PHP_INT_MAX, 2 );

            // Get the orderby part:
            add_filter( 'posts_orderby',  [ $this, 'posts_orderby' ], PHP_INT_MAX  );
        }
    }

    public function posts_orderby( $orderby )
    {
        // Only run once:
     	remove_filter( current_filter(), [ $this, __FUNCTION__ ], PHP_INT_MAX );

        // Store the order by:
        $this->orderby = $orderby;

        return $orderby;
    }

    public function get_posts_per_page( \WP_Query $q )
    {
        return 
            isset( $q->query_vars['posts_per_page'] ) 
            ? $q->query_vars['posts_per_page'] 
            : get_option( 'posts_per_page' );
    }

    public function get_offset( \WP_Query $q )
    {
        return 
            isset( $this->query_vars['offset'] ) 
            ? $this->query_vars['offset'] 
            : 0;
    }

    public function get_paged( \WP_Query $q )
    {
        return 
            isset( $q->query_vars['paged'] ) && 0 < $q->query_vars['paged'] 
            ? $q->query_vars['paged'] 
            : 1;
    }

    public function get_orderby( \WP_Query $q )
    {
        $orderby = isset( $this->orderby ) ? str_replace( $this->db->posts . '.', '', $this->orderby ) : '';
        $orderby = apply_filters( 'cq_orderby', $orderby );
        if( ! empty( $orderby ) )
            $orderby = ' ORDER BY ' . $orderby;
        return $orderby;
    }

    public function get_union()
    {
        return 
            ! in_array( strtoupper( $this->combined_query['union'] ), [ 'UNION', 'UNION ALL' ] ) 
            ? $this->combined_query['union'] 
            : 'UNION';
    }

    public function posts_request( $request, \WP_Query $q )
    {
        // Only run once:
     	remove_filter( current_filter(), [ $this, __FUNCTION__ ], PHP_INT_MAX  );

        // Combine all the sub-queries into a single SQL query.
        $generated_request = $this->generator->get_request( 
            $this->combined_query['args'], 
            $this->get_union(), 
            $this->get_orderby( $q ), 
            $this->get_posts_per_page( $q ), 
            $this->get_paged( $q ), 
            $this->get_offset( $q ) 
        );

        return empty( $generated_request ) ? $request : $generated_request;
    }

} // end class

