<?php

class CWP_SWP_API_Tests extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->route = '/swp_api/search';
		$args = array(
			'post_title' => 'one',
			'post_content' => 'lorem ipsum tofu',
		);
		$this->post_one = wp_insert_post($args );
		update_post_meta( $this->post_one, 'food', 'vegetarian' );

		$args = array(
			'post_title' => 'two',
			'post_content' => 'lorem ipsum taco',
		);
		$this->post_two = wp_insert_post($args );
		update_post_meta( $this->post_two, 'food', 'mexican' );

		$args = array(
			'post_title' => 'three',
			'post_content' => 'bean burrito',
		);
		$this->post_three = wp_insert_post($args );
		update_post_meta( $this->post_three, 'food', 'mexican' );

	}

	public function tearDown() {
		parent::tearDown();
		wp_delete_post( $this->post_one );
		wp_delete_post( $this->post_two );
		wp_delete_post( $this->post_three );

		global $wpdb;
		$args = array( 'meta_key' => 'food' );
		$deleted = $wpdb->delete( $wpdb->postmeta, $args, $where_format = null );

	}

	/**
	 * Test that our tests work
	 */
	function test_tests() {
		$this->assertTrue( true );
	}

	public function test_register_routes() {
		$routes = $this->server->get_routes();


		$this->assertArrayHasKey( $this->route, $routes );

	}


	function test_s_title() {
		$request = new WP_REST_Request( 'GET', '/swp_api/search' );
		$request->set_query_params( array(
			's'           => 'one',
		) );
		$response = $this->server->dispatch( $request );

		$response = rest_ensure_response( $response );

		//$this->assertEquals( array(), $response->get_data() );
		$this->assertEquals( 200, $response->get_status() );
	}


}

