<?php
/**
Plugin Name: The SearchWP API
 */
add_action( 'rest_api_init', function() {
	include_once( dirname( __FILE__ ) . '/route.php' );
	$api = new calderawp\swp_api\route(  'post' );
	$api->the_route();
});
