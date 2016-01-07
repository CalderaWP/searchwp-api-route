<?php
/**
 * Plugin Name: SearchWP API
 * Plugin URI:  http://CalderaWP.com
 * Description: Adds an endpoint to the WordPress REST API for searching via SearchWP
 * Version:     1.1.0
 * Author:      Josh Pollock for CalderaWP LLC <Josh@CalderaWP.com>
 * Author URI:  https://CalderaWP.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cwp-searchwp-api
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Boot it up.
 */
add_action( 'rest_api_init', 'cwp_swp_api_boot', 25 );

/**
 * Check dependencies and boot the API if possible.
 *
 * @since 0.1.0
 *
 * @uses "rest_api_init"
 */
function cwp_swp_api_boot(){
	if ( defined( 'REST_API_VERSION' ) && version_compare( REST_API_VERSION,'2.0', '>=' ) ) {
		include_once( dirname( __FILE__ ) . '/route.php' );
		$api = new calderawp\swp_api\route( 'post' );
		$api->the_route();
	}

}
