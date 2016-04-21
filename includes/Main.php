<?php

namespace Birgir\CombinedQuery;

/**
 * Class Main
 *
 * @since 1.0.0
 */

class Main
{
   
    /**
     * @var string
     */
    private $orderby;


    /**
     * @var array
     */
    private $combined_query;


    /**
     * @var \Birgir\CombinedQuery\Generator
     */
    private $generator;


    /**
     * @var \wpdb
     */
    private $db;
                              	

    /**
     * Init
     *
     * @since  1.0.0
     *
     * @param  \Birgir\CombinedQuery\Generator $generator
     * @param  \wpdb $db
     * @return void
     */
    public function init( Generator $generator, \wpdb $db )
    {
        $this->generator = $generator;
        $this->db = $db;

        add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], PHP_INT_MAX );
    }


    /**
     * Callback for the 'pre_get_posts' hook
     *
     * @since  1.0.0
     *
     * @param  \WP_Query $q
     * @return void
     */
    public function pre_get_posts( \WP_Query $q )
    {
     	if( $q->get( 'combined_query' ) )
        {
            // Default arguments
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

    /**
     * Callback for the 'posts_request' filter
     *
     * @since  1.0.0
     *
     * @param  string	 $request
     * @param  \WP_Query $q
     * @return string 
     */
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


    /**
     * Callback for the 'posts_orderby' filter
     *
     * @since  1.0.0
     *
     * @param  string $orderby
     * @return string $orderby
     */
    public function posts_orderby( $orderby )
    {
        // Only run once:
     	remove_filter( current_filter(), [ $this, __FUNCTION__ ], PHP_INT_MAX );

        // Store the order by:
        $this->orderby = $orderby;

        return $orderby;
    }


    /**
     * Get posts per page
     *
     * @since  1.0.0
     *
     * @param  \WP_Query $q
     * @return int 
     */
    public function get_posts_per_page( \WP_Query $q )
    {
        return 
            isset( $q->query_vars['posts_per_page'] ) 
            ? (int) $q->query_vars['posts_per_page'] 
            : (int) get_option( 'posts_per_page' );
    }


    /**
     * Get offset
     *
     * @since  1.0.0
     *
     * @param  \WP_Query $q
     * @return int
     */
    public function get_offset( \WP_Query $q )
    {
        return 
            isset( $this->query_vars['offset'] ) 
            ? (int) $this->query_vars['offset'] 
            : 0;
    }

    /**
     * Get paged
     *
     * @since  1.0.0
     *
     * @param  \WP_Query $q
     * @return int 
     */
    public function get_paged( \WP_Query $q )
    {
        return 
            ! isset( $q->query_vars['paged'] ) && 0 < $q->query_vars['paged'] 
            ? (int) $q->query_vars['paged'] 
            : 1;
    }


    /**
     * Get orderby
     *
     * @since  1.0.0
     *
     * @param  \WP_Query $q
     * @return string 
     */
    public function get_orderby( \WP_Query $q )
    {
        $orderby = isset( $this->orderby ) ? str_replace( $this->db->posts . '.', '', $this->orderby ) : '';
        $orderby = apply_filters( 'cq_orderby', $orderby );
        if( ! empty( $orderby ) )
            $orderby = ' ORDER BY ' . $orderby;
        return $orderby;
    }


    /**
     * Get union
     *
     * @since  1.0.0
     *
     * @return string 
     */
    public function get_union()
    {
        return 
            in_array( strtoupper( $this->combined_query['union'] ), [ 'UNION', 'UNION ALL' ] ) 
            ? $this->combined_query['union'] 
            : 'UNION';
    }


} // end class

