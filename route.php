<?php
/**
 * Adds a SearchWP Endpoint to the WordPress REST API
 *
 * @package   swp_api
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 Josh Pollock
 */

namespace calderawp\swp_api;

class route extends \WP_REST_Posts_Controller {


	/**
	 * Define the route
	 *
	 * @uses "rest_api_init"
	 *
	 * @since 0.1.0
	 */
	public function the_route() {
		register_rest_route( 'swp_api', '/search',
			array(
				'methods'         => \WP_REST_Server::READABLE,
				'callback'        => array( $this, 'the_search' ),
				'args' =>   array(
					's' => array(
						'default' => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'engine' => array(
						'default' => 'default',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( $this, 'validate_engine' ),
					),
					'posts_per_page' => array(
						'default' => get_option( 'posts_per_page', 15 ),
						'sanitize_callback' => 'absint',
						'validate_callback' => array( $this, 'limit_posts_per_page')
					),
					'nopaging' => array(
						'default' => 0,
						'sanitize_callback' => 'absint',
					),
					'load_posts' => array(
						'default' => 1,
						'sanitize_callback' => 'absint',
					),
					'page' => array(
						'default' => 1,
						'sanitize_callback' => 'absint',
					),
					'post__in' => array(
						'default' => array(),
						'sanitize_callback' => 'absint',
					),
					'post__not_in' => array(
						'default' => array(),
						'sanitize_callback' => '',
					),
					'tax_query' => array(
						'default' => array(),
						'sanitize_callback' => array( $this, 'sanatize_array'),
						'validate_callback' => array( $this, 'validate_tax_query' ),
					),
					'meta_query' => array(
						'default' => array(
							'key' => '',
							'value' => '',
							'compare' => '',
						),
						'sanitize_callback' => array( $this, 'sanatize_array'),
						'validate_callback' => array( $this, 'validate_meta_query' ),
					),
				)
			)
		);

	}

	/**
	 * Do search and respond.
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|WP_Error
	 */
	public function the_search( $request ) {
		$args = (array) $request->get_params();

		$search = new \SWP_Query( $args );
		$query_result = $search->posts;
		$posts = array();
		foreach ( $query_result as $post ) {
			$data = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $posts );

		return $response;
	}

	/**
	 * Ensure that this engine exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $engine
	 *
	 * @return bool
	 */
	public function validate_engine( $engine ){
		//@todo this
		return true;
	}


	/**
	 * Cap posts_per_page at 50 to prevent huge requests functioning as DDOS.
	 *
	 * @since 0.1.0
	 *
	 * @param $posts_per_page
	 *
	 * @return int
	 */
	public function limit_posts_per_page( $posts_per_page ) {
		if ( $posts_per_page > 50  ) {
			$posts_per_page = 50;
		}

		return $posts_per_page;
	}

	/**
	 * Apply strip_tags to each key of an array.
	 *
	 * @since 0.1.0
	 *
	 * @param $array
	 *
	 * @return array
	 */
	public function sanatize_array( $array ) {
		$array = array_map( "strip_tags", $array );
		return $array;

	}

	/**
	 * Ensure that the meta query is valid
	 *
	 * @since 0.1.0
	 *
	 * @param array $query
	 *
	 * @return bool
	 */
	public function validate_meta_query( $query ) {
		$required_keys = array(
			'key',
			'value',
			'compare'
		);

		$valid = $this->validate_query_keys( $query, $required_keys );

		return $valid;

	}

	/**
	 * Ensure that the tax query is valid
	 *
	 * @since 0.1.0
	 *
	 * @param array $query
	 *
	 * @return bool
	 */
	public function validate_tax_query( $query ) {
		$required_keys = array(
			'taxonomy',
			'field',
			'terms'
		);

		$valid = $this->validate_query_keys( $query, $required_keys );

		return $valid;
	}

	/**
	 * Ensure that the date query is valid
	 *
	 * @since 0.1.0
	 *
	 * @param array $query
	 *
	 * @return bool
	 */
	public function validate_date_query( $query ) {
		$required_keys = array(
			'year',
			'month',
			'day'
		);

		$valid = $this->validate_query_keys( $query, $required_keys );

		return $valid;
	}

	/**
	 * Ensure an array, in this case meta/tax/date query has the right keys.
	 *
	 * @since 0.1.0
	 *
	 * @param array $query
	 * @param array $required_keys
	 *
	 * @return bool
	 */
	protected function validate_query_keys( $query, $required_keys ) {
		foreach( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $query ) ) {
				return false;
			}
		}

		return true;
	}

}
