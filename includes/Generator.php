<?php

namespace Birgir\CombinedQuery;

/**
 * Class Generator
 *
 * @since 1.0.0
 */

class Generator
{
    private $empty_query;

    public function __construct( EmptyQuery $empty_query )
    {
        $this->empty_query = $empty_query;
    }

    public function get_sqls( $args = [] )
    {
        $sqls = [];

        $this->empty_query->cq_activate();

        // Collect the generated SQL for each sub-query:
        foreach ( (array) $args as $sub_args )
        {
			$this->empty_query->query( $sub_args );
            $sqls[] = $this->empty_query->cq_get_sql();
        }

        $this->empty_query->cq_deactivate();

        unset( $empty_query );

        return $sqls;
    }

	static public function esc_percent( $in_string )
	{
        $return = str_replace( '%', '%%', $in_string );

		return $return;
	}

    public function get_request( $args = [], $union = '', $orderby = '', $ppp = 1, $paged = 1, $offset = 0 )
    {
        $request = '';

		$sqls = $this->get_sqls( $args );

        if ( 0 < count( $sqls ) )
        {
			$union = Generator::esc_percent( $union );
			$sqls = Generator::esc_percent( $sqls );

            $unions  = '(' . join( ') ' . $union . ' (', $sqls ) . ' ) ';
            $request = sprintf(
				"SELECT SQL_CALC_FOUND_ROWS * FROM ( {$unions} ) as combined {$orderby} LIMIT %d, %d",
                $ppp * ( $paged - 1 ) + $offset,
                $ppp
            );
		}

        return $request;
    }

} // end class

