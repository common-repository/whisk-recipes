<?php
/**
 * Class Proxy
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use WP_REST_Server;
use WP_REST_Request;

/**
 * Class Proxy
 *
 * @package whisk-recipes
 */
class Proxy {
	/**
	 * Proxy constructor.
	 */
	public function __construct() {
	}

	/**
	 * Setup hooks.
	 */
	public function setup_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
	}

	/**
	 * Register rest route.
	 */
	public function register_rest_route() {
		register_rest_route(
			'whisk/v1',
			'/proxy/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => function ( WP_REST_Request $request ) {
					header( 'Content-type: text/html; charset=UTF-8' );

					$cache_name = 'whisk_proxy_' . md5( $request->get_param( 'url' ) );

					$content = get_transient( $cache_name );

					if ( false !== $content ) {
						echo $content;

						return;
					}

					$response = wp_remote_get(
						$request->get_param( 'url' ),
						array(
							'timeout' => 10,
						)
					);

					if ( is_wp_error( $response ) ) {
						echo $response->get_error_message();

						return;
					} else {
						$content = wp_remote_retrieve_body( $response );
						set_transient( $cache_name, $content, DAY_IN_SECONDS );

						echo $content;

						return;
					}
				},
				'args'                => array(
					'url' => array(
						'type'     => 'string',
						'format'   => 'url',
						'required' => true,
					),
				),
			)
		);
	}
}
