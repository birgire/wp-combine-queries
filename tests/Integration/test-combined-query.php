<?php
/**
 * Class Test_CombinedQuery
 *
 * @package CombinedQuery
 */

/**
 * Test Combined Query
 *
 * @group CombinedQuery
 */
class Test_CombinedQuery extends WP_UnitTestCase {

	/**
	 * Tests combined query with default order.
	 */
	public function test_combined_query_with_default_order() {

		// Create pages.
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page1',
				'post_date'  => '2019-12-04 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page2',
				'post_date'  => '2019-12-03 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page3',
				'post_date'  => '2019-12-02 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page4',
				'post_date'  => '2019-12-01 00:00:00',
			)
		);

		// Create posts.
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post1',
				'post_date'  => '2018-12-04 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post2',
				'post_date'  => '2018-12-03 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post3',
				'post_date'  => '2018-12-02 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post4',
				'post_date'  => '2018-12-01 00:00:00',
			)
		);

		//-----------------
		// Sub query #1:
		//-----------------
		$args1 = [
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'orderby'        => 'title',
			'order'          => 'desc',
		];

		//-----------------
		// Sub query #2:
		//-----------------
		$args2 = [
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'asc',
		];

		//---------------------------
		// Combined queries #1 + #2:
		//---------------------------
		$args = [
			'posts_per_page' => 4,
			'combined_query' => [
				'args'  => [ $args1, $args2 ],
				'union' => 'UNION',
			],
		];

		// Order by order desc (default of WP_Query).
		$query = new WP_Query( $args );

		$this->assertCount( 4, $query->posts );
		$this->assertContains( 'UNION', $query->request );
		$this->assertContains( 'as combined', $query->request );
		$this->assertSame( $query->posts[0]->post_title, 'page4' );
		$this->assertSame( $query->posts[1]->post_title, 'post2' );
		$this->assertSame( $query->posts[2]->post_title, 'post3' );
		$this->assertSame( $query->posts[3]->post_title, 'post4' );
	}

	/**
	 * Tests combined query with argument order.
	 *
	 * @ticket 19
	 */
	public function test_combined_query_with_argument_order() {

		// Arrange.
		// Create pages.
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page1',
				'post_date'  => '2019-12-04 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page2',
				'post_date'  => '2019-12-03 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page3',
				'post_date'  => '2019-12-02 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'page4',
				'post_date'  => '2019-12-01 00:00:00',
			)
		);

		// Create posts.
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post1',
				'post_date'  => '2018-12-04 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post2',
				'post_date'  => '2018-12-03 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post3',
				'post_date'  => '2018-12-02 00:00:00',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'post4',
				'post_date'  => '2018-12-01 00:00:00',
			)
		);

		//-----------------
		// Sub query #1:
		//-----------------
		$args1 = [
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'orderby'        => 'title',
			'order'          => 'desc',
		];

		//-----------------
		// Sub query #2:
		//-----------------
		$args2 = [
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'asc',
		];

		//---------------------------
		// Combined queries #1 + #2:
		//---------------------------
		$args = [
			'posts_per_page' => 4,
			'combined_query' => [
				'args'  => [ $args1, $args2 ],
				'union' => 'UNION',
			],
		];

		// Act.
		// Keep order by arguments arg1, arg2.
		add_filter( 'cq_orderby', '__return_empty_string' );
		$query = new WP_Query( $args );

		// Assert.
		$this->assertCount( 4, $query->posts );
		$this->assertContains( 'UNION', $query->request );
		$this->assertContains( 'as combined', $query->request );
		$this->assertSame( $query->posts[0]->post_title, 'page4' );
		$this->assertSame( $query->posts[1]->post_title, 'post4' );
		$this->assertSame( $query->posts[2]->post_title, 'post3' );
		$this->assertSame( $query->posts[3]->post_title, 'post2' );
	}

	/**
	 * Tests three combined query.
	 */
	public function test_three_combined_queries() {

		register_post_type( 'foo' );

		self::factory()->post->create_many( 5, array( 'post_type' => 'foo' ) );
		self::factory()->post->create_many( 5, array( 'post_type' => 'post' ) );
		self::factory()->post->create_many( 5, array( 'post_type' => 'page' ) );

		//-----------------
		// Sub query #1:
		//-----------------
		$args1 = [
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'orderby'        => 'title',
			'order'          => 'asc',
		];

		//-----------------
		// Sub query #2:
		//-----------------
		$args2 = [
			'post_type'      => 'post',
			'posts_per_page' => 2,
			'orderby'        => 'date',
			'order'          => 'asc',
		];

		//-----------------
		// Sub query #1:
		//-----------------
		$args3 = [
			'post_type'      => 'foo',
			'posts_per_page' => 3,
			'orderby'        => 'title',
			'order'          => 'desc',
		];

		//---------------------------
		// Combined queries #1 + #2 + #3:
		//---------------------------
		$args  = [
			'posts_per_page' => 10,
			'combined_query' => [
				'args'  => [ $args1, $args2, $args3 ],
				'union' => 'UNION',
			],
		];
		$query = new WP_Query( $args );

		unregister_post_type( 'foo' );

		$this->assertCount( 6, $query->posts );
		$this->assertContains( ' as combined', $query->request );
		$this->assertSame( $query->posts[0]->post_type, 'page' );
		$this->assertSame( $query->posts[1]->post_type, 'post' );
		$this->assertSame( $query->posts[2]->post_type, 'post' );
		$this->assertSame( $query->posts[3]->post_type, 'foo' );
		$this->assertSame( $query->posts[4]->post_type, 'foo' );
		$this->assertSame( $query->posts[5]->post_type, 'foo' );
	}

	/**
	 * Tests UNION ALL combined query.
	 *
	 */
	public function test_union_all_combined_queries() {

		self::factory()->post->create_many( 6, array( 'post_type' => 'page' ) );

		//-----------------
		// Sub query #1:
		//-----------------
		$args1 = [
			'post_type'      => 'page',
			'posts_per_page' => 2,
			'orderby'        => 'title',
			'order'          => 'asc',
		];

		//-----------------
		// Sub query #2:
		//-----------------
		$args2 = [
			'post_type'      => 'page',
			'posts_per_page' => 2,
			'orderby'        => 'title',
			'order'          => 'asc',
		];

		//---------------------------
		// Combined queries #1 + #2:
		//---------------------------
		$args  = [
			'posts_per_page' => 10,
			'combined_query' => [
				'args'  => [ $args1, $args2 ],
				'union' => 'UNION ALL',
			],
		];
		$query = new WP_Query( $args );

		$this->assertCount( 4, $query->posts );
		$this->assertContains( ' as combined', $query->request );
		$this->assertContains( 'UNION ALL', $query->request );
	}

}
