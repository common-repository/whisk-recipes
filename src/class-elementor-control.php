<?php
/**
 * Elementor Modal Controll.
 *
 * Elementor control for inserting WP Recipe Maker recipes.
 *
 * @since 5.1.0
 */

namespace Whisk\Recipes;

use \Elementor\Base_Data_Control;
use Whisk\Recipes\Models\Recipe;

class Elementor_Control extends Base_Data_Control {

	/**
	 * Recipe_Model constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	public function get_type() {
		return 'whisk-recipe-select';
	}

	public function enqueue() {
		wp_enqueue_script( 'whisk-elementor', Utils::get_plugin_file_uri( 'assets/js/elementor-control.min.js' ), array( 'jquery' ), Utils::get_plugin_version(), true );

		wp_localize_script(
			'whisk-elementor',
			'whisk_elementor',
			array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'whisk' ),
				'latest_recipes' => Recipe::get_latest( 20, 'id' ),
			)
		);
	}

	public function get_default_value() {
		return false;
	}

	public function get_value( $control, $settings ) {
		return 6;
	}

	public function content_template() {
		?>
		<div id="whisk-recipe-select-placeholder"></div>
		<?php
	}
}
