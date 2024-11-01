<?php
/**
 * Class Restricted Grocers
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Views;

use Whisk\Recipes\Utils;

/**
 * Class Restricted Grocers
 *
 * @package whisk-recipes
 */
class Restricted_Grocers {

	/**
	 * Restricted Grocers constructor.
	 **/
	public function __construct() {
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	public function setup_hooks() {
		add_action( 'wp_enqueue_scripts', array ( $this, 'print_restricted_grocers' ), 11 );
	}

	/**
	 * Print restricted grocers as global setting
	 *
	 * @return void
	 */
	public function print_restricted_grocers() {

		if ( Utils::is_restricted_grocers_enabled() ) {
			wp_add_inline_script(
				Utils::get_plugin_prefix() . '-sdk',
				sprintf( "whisk.queue.push(function() { whisk.config.set( 'shoppingList.onlineCheckout.disallowedRetailers', %s ); } );", $this->get_restricted_grocers() )
			);
		}

	}

	/**
	 * Get restricted grocers from site settings
	 *
	 * @return string
	 */
	public function get_restricted_grocers() {

		$restricted_string = get_option( '_whisk_restricted_grocers', '' );
		$restricted_arr    = explode( ",", $restricted_string );

		return wp_json_encode( $restricted_arr );
	}
}
