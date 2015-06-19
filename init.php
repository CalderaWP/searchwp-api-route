<?php
/**
 * Initialize the route.
 *
 * @package   swp_api
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */
add_action( 'rest_api_init', function() {
	if ( class_exists( 'SWP_Query' ) && defined( 'REST_API_VERSION' ) && version_compare( REST_API_VERSION,'2.0-beta2', '>=' ) ) {
		include_once( dirname( __FILE__ ) . '/route.php' );
		$api = new calderawp\swp_api\route( 'post' );
		$api->the_route();
	}
});
