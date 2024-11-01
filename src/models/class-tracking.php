<?php
/**
 * Class Tracking
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Views;

use Whisk\Recipes\Models\Recipe;
use Whisk\Recipes\Models\Sync;
use Whisk\Recipes\Utils;
use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;

/**
 * Class Tracking
 *
 * @package whisk-recipes
 */
class Tracking {
	/**
	 * Tracking ID.
	 *
	 * @var string
	 */
	private $tracking_id;

	/**
	 * Sync instance.
	 *
	 * @var Sync $sync
	 */
	private $sync;

	/**
	 * Tracking constructor.
	 *
	 * @param Sync $sync Sync instance.
	 */
	public function __construct( Sync $sync ) {
		$this->sync = $sync;
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'set_tracking_id' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'send_tracking_id' ), 11 );
		add_action( 'wp_head', array( $this, 'add_meta_tag' ), 11 );
	}

	/**
	 * Add meta tag with Tracking ID.
	 */
	public function add_meta_tag() {

		if ( ! $this->is_enabled() || ! is_singular( Recipe::get_cpt_name() ) ) {
			return;
		}

		printf( '<meta name="whisk-recipe-id" content="%s" />', esc_attr( $this->sync->get_recipe_id( get_the_ID() ) ) );
	}

	/**
	 * Send tracking ID.
	 *
	 * @return void
	 */
	public function send_tracking_id() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		wp_add_inline_script(
			Utils::get_plugin_prefix() . '-sdk',
			sprintf( "whisk.queue.push(function() { whisk.config.set( 'global.trackingId', '%s' ); } );", esc_js( $this->get_tracking_id() ) )
		);
	}

	/**
	 * Set tracking identify.
	 *
	 * @return void
	 */
	public function set_tracking_id() {
		$this->tracking_id = Helper::get_theme_option( 'whisk_tracking_id' );
	}

	/**
	 * Get tracking identify.
	 *
	 * @return string
	 */
	public function get_tracking_id() {
		return $this->tracking_id;
	}

	/**
	 * Check if allowing sending analytics data.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return ( Helper::get_theme_option( 'whisk_tracking_enabled' ) && ! empty( $this->get_tracking_id() ) );
	}
}
