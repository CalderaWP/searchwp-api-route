<?php

namespace calderawp\swp_api\tests;

class the_tests extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );



	}

	public function tearDown() {
		parent::tearDown();

	}

	/**
	 * Test that our tests work
	 *
	 * @since 0.2.0
	 *
	 * @covers josh
	 */
	public function test_tests() {
		$this->assertTrue( true );
	}

	/**
	 * Test that our route is registered properly
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers cwp_swp_api_boot()
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();


		$this->assertArrayHasKey( '/swp_api/search', $routes );

	}

	/**
	 * Test that S query is properly formed.
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_s() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => 'one',
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right s
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 's', $params );
		$this->assertEquals( $params[ 's' ], 'one' );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test that engine query is properly formed.
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_engine_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => 'hats',
		) );

		$response = $this->server->dispatch( $request );

		//test we have the right default engine
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'engine', $params );
		$this->assertEquals( $params[ 'engine' ], 'default' );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test that the engine validation works properly
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::validate_engine()
	 */
	public function test_engine_validation() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => 'hats',
			'engine'      => 'notreal'
		) );

		$response = $this->server->dispatch( $request );
		$response = rest_ensure_response( $response );

		//test for correct response code
		$this->assertEquals( 400, $response->get_status() );

		//test for correct error message
		$response_data = $response->get_data();
		$this->assertEquals( 'Invalid parameter(s): engine (Invalid search engine)', $response_data[0][ 'message' ] );

	}

	/**
	 * Test posts per page
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_posts_per_page() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'posts_per_page'           => 7,
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'posts_per_page', $params );
		$this->assertEquals( $params[ 'posts_per_page' ],  7 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test that posts per page is properly limited to 50 max
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::limit_posts_per_page()
	 */
	public function test_posts_per_page_limit() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'posts_per_page'           => 57,
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertEquals( $params[ 'posts_per_page' ],  50 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Make sure our boolean sanitization/validation works properly before using it in further tests.
	 * @covers \calderawp\swp_api\route::sanatize_bool()
	 */
	public function test_bool_sanitization() {
		$class = new \calderawp\swp_api\route( 'swp-api' );

		//test for truth
		$this->assertTrue( $class->sanatize_bool( 'true' ) );
		$this->assertTrue( $class->sanatize_bool( 'TRUE' ) );
		$this->assertTrue( $class->sanatize_bool( true ) );
		$this->assertTrue( $class->sanatize_bool( '1' ) );
		$this->assertTrue( $class->sanatize_bool( 1 ) );
		$this->assertFalse( $class->sanatize_bool( 'false' ) );

		//test for false
		$this->assertFalse( $class->sanatize_bool( 'FALSE' ) );
		$this->assertFalse( $class->sanatize_bool( false ) );
		$this->assertFalse( $class->sanatize_bool( '0' ) );
		$this->assertFalse( $class->sanatize_bool( 0 ) );

		//make sure invalid input returns false
		$this->assertFalse( $class->sanatize_bool( 3 ) );
		$this->assertFalse( $class->sanatize_bool( 'hats' ) );
		$this->assertFalse( $class->sanatize_bool( new \stdClass() ) );
		$this->assertFalse( $class->sanatize_bool( array() ) );

	}

	/**
	 * Test nopaging
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::sanatize_bool()
	 */
	public function test_nopaging() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'nopaging'           => "true",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'nopaging', $params );
		$this->assertEquals( $params[ 'nopaging' ],  1 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test nopaging default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_nopaging_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => "Gandalf",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'nopaging', $params );
		$this->assertEquals( $params[ 'nopaging' ],  0 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test nopaging
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::sanatize_bool()
	 */
	public function test_load_posts() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'load_posts'           => "false",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'load_posts', $params );
		$this->assertEquals( $params[ 'load_posts' ],  0 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test nopaging default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_load_posts_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => "Bilbo",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'load_posts', $params );
		$this->assertEquals( $params[ 'load_posts' ],  1 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test page
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_page() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'page'           => "5",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'page', $params );
		$this->assertEquals( $params[ 'page' ],  5 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test page default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_load_page_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => "Sam",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'page', $params );
		$this->assertEquals( $params[ 'page' ],  1 );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Ensure we can convert comma separated args to an array properly before using \calderawp\swp_api\route::comma_arg() in tests.
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_comma_arg() {

		$class = new \calderawp\swp_api\route( 'swp-api' );

		$this->assertEquals( array( 1,2,3 ), $class->comma_arg( '1,2,3' ) );
		$this->assertEquals( 0, $class->comma_arg( 0 ) );
		$this->assertEquals( 'mordor', $class->comma_arg( 'mordor' ) );


	}

	/**
	 * Test post__in
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_post__in_single() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'post__in'           => "5",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'post__in', $params );
		$this->assertEquals( $params[ 'post__in' ],  array( 5 ) );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test post__in
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_post__in_array() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'post__in'           => "5,9",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'post__in', $params );
		$this->assertEquals( $params[ 'post__in' ],  array( 5,9 ) );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test post_in default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_post__in_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => "Sam",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'post__in', $params );
		$this->assertFalse( $params[ 'post__in' ] );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test post__not_in with a single post ID
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_post__not_in_single() {
		return;
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'post__in'           => "5",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'post__not_in', $params );
		$this->assertEquals( $params[ 'post__not_in' ], array( 5 ) );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test post__not_in with an array of post IDs.
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_post__not_in_array() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'post__not_in'           => "5,9",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'post__not_in', $params );
		$this->assertEquals( $params[ 'post__not_in' ],  array( 5,9 ) );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test post__not_in default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::comma_arg()
	 */
	public function test_post__not_in_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => "Merry",
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertArrayHasKey( 'post__not_in', $params );
		$this->assertFalse( $params[ 'post__not_in' ] );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test the sanatize_array() method will properly strip tags.
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::sanatize_array()
	 */
	public function test_sanatize_array() {
		$class = new \calderawp\swp_api\route( 'swp-api' );

		$clean = array(
			0 => 'balrog',
			1 => 'dragon'
		);
		$this->assertEquals( $clean, $class->sanatize_array( $clean ) );

		$unclean = array(
			0 => '<p><strong>balrog</strong></p>',
			1 => 'dragon',
		);

		$this->assertEquals( $clean, $class->sanatize_array( $unclean ) );

		$unclean = array(
			0 => '<p><strong>balrog</strong></p>',
			1 => '<em>dragon</em>',
		);

		$this->assertEquals( $clean, $class->sanatize_array( $unclean ) );

		$unclean = array(
			0 => 'balrog',
			1 => '<em>dragon</em>',
		);

		$this->assertEquals( $clean, $class->sanatize_array( $unclean ) );

	}

	/**
	 * Test the validate_meta_query() method will properly validate a meta query
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::validate_meta_query()
	 */
	public function test_validate_meta_query() {
		$class = new \calderawp\swp_api\route( 'swp-api' );

		$query = array(
			'key' => 'hobbit',
			'value' => 'pippin',
			'compare' => 'OR'
		);


		$this->assertTrue( $class->validate_meta_query( $query, new \WP_REST_Request()  ) );

		$query = array(
			'value' => 'pippin',
			'compare' => 'OR'
		);
		$this->assertFalse( $class->validate_meta_query( $query, new \WP_REST_Request() ) );


	}

	/**
	 * Test the validate_tax_query() method will properly validate a tax query
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::validate_tax_query()
	 */
	public function test_validate_tax_query() {
		$class = new \calderawp\swp_api\route( 'swp-api' );

		$query = array(
			'taxonomy' => 'hobbit',
			'field' => 'slug',
			'terms' => 'hobbit'
		);

		$this->assertTrue( $class->validate_tax_query( $query ) );

		$query = array(
			'value' => 'pippin',
		);
		$this->assertFalse( $class->validate_tax_query( $query ) );


	}

	/**
	 * Test the validate_date_query() method will properly validate a date query
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::validate_date_query()
	 */
	public function test_validate_date_query() {
		$class = new \calderawp\swp_api\route( 'swp-api' );

		$query = array(
			'year' => 1,
			'month' => 2,
			'day' => '3'
		);

		$this->assertTrue( $class->validate_date_query( $query ) );

		$query = array(
			'value' => 'pippin',
		);
		$this->assertFalse( $class->validate_date_query( $query ) );


	}

	/**
	 * Test tax_query
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::validate_tax_query()
	 */
	public function test_tax_query() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'tax_query'           => array(
				'taxonomy' => 1,
				'field' => 2,
				'terms' => 3
			),
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();


		$this->assertArrayHasKey( 'tax_query', $params );
		$this->assertArrayHasKey( 'taxonomy', $params[ 'tax_query' ]);
		$this->assertArrayHasKey( 'field',  $params[ 'tax_query' ] );
		$this->assertArrayHasKey( 'terms', $params[ 'tax_query' ] );

		$this->assertEquals( $params[ 'tax_query' ][ 'taxonomy' ], "1" );
		$this->assertEquals( $params[ 'tax_query' ][ 'field' ], "2" );
		$this->assertEquals( $params[ 'tax_query' ][ 'terms' ], "3" );


		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test tax_query default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_tax_query_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's' => 'Boromir'
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertFalse( $params[ 'tax_query' ] );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test meta_query
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::validate_meta_query()
	 */
	public function test_meta_query() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'meta_query'           => array(
				'key' => 1,
				'value' => 2,
				'compare' => 3
			),
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();


		$this->assertArrayHasKey( 'meta_query', $params );
		$this->assertArrayHasKey( 'key', $params[ 'meta_query' ]);
		$this->assertArrayHasKey( 'value',  $params[ 'meta_query' ] );
		$this->assertArrayHasKey( 'compare', $params[ 'meta_query' ] );

		$this->assertEquals( $params[ 'meta_query' ][ 'key' ], "1" );
		$this->assertEquals( $params[ 'meta_query' ][ 'value' ], "2" );
		$this->assertEquals( $params[ 'meta_query' ][ 'compare' ], "3" );


		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test meta_query default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 */
	public function test_meta_query_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's' => 'Legolas'
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertFalse( $params[ 'meta_query' ] );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test date_query
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()
	 * @covers \calderawp\swp_api\route::validate_date_query()
	 */
	public function test_date_query() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			'date_query'           => array(
				'year' => 1,
				'month' => 2,
				'day' => 3
			),
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();


		$this->assertArrayHasKey( 'date_query', $params );
		$this->assertArrayHasKey( 'year', $params[ 'date_query' ]);
		$this->assertArrayHasKey( 'month',  $params[ 'date_query' ] );
		$this->assertArrayHasKey( 'day', $params[ 'date_query' ] );

		$this->assertEquals( $params[ 'date_query' ][ 'year' ], "1" );
		$this->assertEquals( $params[ 'date_query' ][ 'month' ], "2" );
		$this->assertEquals( $params[ 'date_query' ][ 'day' ], "3" );


		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}

	/**
	 * Test date_query default
	 *
	 * @since 0.2.0
	 *
	 * @covers \calderawp\swp_api\route::the_route()
	 * @covers \calderawp\swp_api\route::the_search()

	 */
	public function test_date_default() {
		$request = new \WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's' => 'Elrond'
		) );
		$response = $this->server->dispatch( $request );

		//test we have the right args
		$params = (array) $request->get_params();
		$this->assertFalse( $params[ 'date_query' ] );

		//test for correct response code
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

	}



}

