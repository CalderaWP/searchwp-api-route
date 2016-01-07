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
				'methods'               => \WP_REST_Server::READABLE,
				'permission_callback'   => array( $this, 'permissions_check' ),
				'args'                  => $this->the_args(),
				'callback'              => array( $this, 'the_search' ),

			)
		);
	}

	/**
	 * Prepare arguments for the swp_api endpoint of this route.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	protected function the_args() {
		$args = array( 's' => array(
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
				'sanitize_callback' => array( $this, 'limit_posts_per_page')
			),
			'nopaging' => array(
				'default' => 0,
				'sanitize_callback' => array( $this, 'sanatize_bool' ),
			),
			'load_posts' => array(
				'default' => 1,
				'sanitize_callback' => array( $this, 'sanatize_bool' ),
			),
			'page' => array(
				'default' => 1,
				'sanitize_callback' => 'absint',
			),
			'post__in' => array(
				'default' => false,
				'sanitize_callback' => array( $this, 'comma_arg' )
			),
			'post__not_in' => array(
				'default' => false,
				'sanitize_callback' => array( $this, 'comma_arg' )
			),
			'tax_query' => array(
				'default' => false,
				'sanitize_callback' => array( $this, 'sanatize_array'),
				'validate_callback' => array( $this, 'validate_tax_query' ),
			),
			'meta_query' => array(
				'default' => false,
				'sanitize_callback' => array( $this, 'sanatize_array'),
				'validate_callback' => array( $this, 'validate_meta_query' ),
			),
			'date_query' => array(
				'default' => false,
				'sanitize_callback' => array( $this, 'sanatize_array'),
				'validate_callback' => array( $this, 'validate_date_query' ),
			),
		);

		/**
		 * Filter args for endpoint
		 *
		 * @since 0.3.0
		 *
		 * @param array $args Array of args to be passed as $args argument of register_rest_route()
		 */
		return apply_filters( 'cwp_swp_api_args', $args );

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
		$allowed = array_keys( $this->the_args() );
		$allowed = array_flip( $allowed );
		foreach( $args as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				unset( $args[ $key ] );
			}

		}

		if( ! empty( $args[ 'meta_query' ] ) ) {
			$args[ 'meta_query' ] = array( $args[ 'meta_query' ] );
		}

		if( ! empty( $args[ 'tax_query' ] ) ) {
			$args[ 'tax_query' ] = array( $args[ 'tax_query' ] );
		}

		/**
		 * Filter query args before running query
		 *
		 * @since 1.1.0
		 *
		 * @param array $args Query args
		 * @param \WP_REST_Request $request Currnet rquest
		 */
		$args = apply_filters( 'cwp_swp_api_search_args', $args, $request );


		if ( ! empty( $args[ 's' ] ) &&  class_exists( "SWP_Query" ) ) {
			$search = new \SWP_Query( $args );
		}else{
			$search = new \WP_Query( $args );
		}

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
		if ( class_exists( 'SWP' ) && ! SWP()->is_valid_engine( $engine ) ) {
			return new \WP_Error( 'swp-api-invalid-search-engine', __( 'Invalid search engine', 'cwp-searchwp-api' ) );

		}

		return $engine;

	}


	/**
	 * Cap posts_per_page at 50 (or filter val) to prevent huge requests functioning as DDOS.
	 *
	 * @since 0.1.0
	 *
	 * @param $posts_per_page
	 *
	 * @return int
	 */
	public function limit_posts_per_page( $posts_per_page ) {
		$posts_per_page = absint( $posts_per_page );

		/**
		 * Change the maximum number of posts per page
		 *
		 * @since 0.3.0
		 *
		 * @param int $max Maximum posts per page.
		 */
		$max = apply_filters( 'cwp_swp_api_max_posts_per_page', 50 );
		if ( $posts_per_page > (int) $max  ) {
			$posts_per_page = $max;
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
		if ( ! empty( $array ) ) {
			array_walk( $array, function(&$n) {
				if( is_null( $n ) ) {
					$n = false;
				}else{
					if( is_array( $n ) ) {
						foreach( $n as $key => $value ) {
							$n[ $key ] = trim( strip_tags( stripslashes( $value ) ) );
						}
					}else{
						$n = trim( strip_tags( stripslashes( $n ) ) );
					}

				}
			});
		}

		return $array;

	}

	/**
	 * Ensure that the meta query is valid
	 *
	 * @since 0.1.0
	 *
	 * @param array $query
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function validate_meta_query( $query, $request ) {
		if ( ! is_array( $query ) ) {
			return array();

		}

		$meta_query = $request->get_param( 'meta_query' );
		if( isset( $_GET[ 'meta_query' ] ) && is_array( $_GET[ 'meta_query' ] ) && isset( $_GET[ 'meta_query' ][ 'key' ], $_GET[ 'meta_query' ][ 'value' ] ) && is_array( $_GET[ 'meta_query' ][ 'key' ] ) ) {
			if( ! isset( $_GET[ 'meta_relation' ] ) ) {
				$_GET[ 'meta_relation' ] = 'AND';
			}

			$meta_query = array(
				'relation' => strip_tags( $_GET[ 'meta_relation' ] ),

			);


			foreach( $_GET[ 'meta_query' ][ 'key' ] as $i => $key ) {
				if( isset( $_GET[ 'meta_query' ][ 'value' ][ $i ] ) ) {
					if( ! isset( $_GET[ 'meta_query' ][ 'compare' ][ $i ] ) ) {
						$_GET[ 'meta_query' ][ 'compare' ][ $i ] = 'IN';
					}

					if( is_array( $_GET[ 'meta_query' ][ 'value' ] [ $i ] ) ) {
						$value = array();
						foreach($_GET[ 'meta_query' ][ 'value' ] [ $i ] as $_value  ) {
							$value[] = strip_tags( $_value );
						}
					}else{
						$value = strip_tags( $_GET[ 'meta_query' ][ 'value' ] [ $i ] );
					}

					$meta_query[] = array(
						'key' => strip_tags( $key ),
						'value' => $value,
						'compare' => strip_tags( strtoupper( $_GET['meta_query']['compare'][ $i ] ) )
					);
				}

			}

			if( isset( $meta_query[ 'key' ] ) ){
				unset( $meta_query[ 'key' ] );
			}

			if( isset( $meta_query[ 'value' ] ) ){
				unset( $meta_query[ 'value' ] );
			}

			$request->set_param( 'meta_query', $meta_query );

			return true;


		}

		$required_keys = array(
			'key',
			'value',
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
		if ( ! is_array( $query ) ) {
			return array();

		}

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
		if ( ! is_array( $query ) ) {
			return array();

		}
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

	/**
	 * Permissions check.
	 *
	 * Hardcoded to allow, with filter to change.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return boolean
	 */
	public function permissions_check( $request ) {
		/**
		 * Overide public nature of endpoint.
		 *
		 * @since 0.1.0
		 *
		 * @param bool $allowed. If true, request is allowed. Change to false to prevent.
		 */
		$allowed = apply_filters( 'cwp_swp_api_allow_query', true, $request );
		return (bool) $allowed;

	}

	/**
	 * Ensure a possible boolean is a boolean and convert to an actual boolean
	 *
	 * @since 0.2.0
	 *
	 * @param mixed $maybe_bool Value to check.
	 *
	 * @return bool
	 */
	public function sanatize_bool( $maybe_bool ) {
		if ( is_int( $maybe_bool ) || is_string( $maybe_bool ) || is_bool( $maybe_bool ) ) {

			if ( filter_var( $maybe_bool, FILTER_VALIDATE_BOOLEAN ) ) {
				return (bool) $maybe_bool;

			}

		}

		return false;

	}

	/**
	 * Convert a possibly comma-seperated argument to an array
	 *
	 * @since 0.2.0
	 *
	 * @param string $arg
	 *
	 * @return array
	 */
	public function comma_arg( $arg ) {

		if ( strpos( $arg, ',' ) ) {
			return explode( ',', $arg );
		}elseif ( 0 < absint( $arg ) ) {
			return array( $arg );
		}else{
			return $arg;
		}


	}

}
