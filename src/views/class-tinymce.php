<?php
/**
 * Class TinyMCE
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes;

/**
 * Class TinyMCE
 *
 * @package whisk-recipes
 */
class TinyMCE {
	/**
	 * TinyMCE constructor.
	 */
	public function __construct() {
	}

	public function setup_hooks() {
		add_action( 'media_buttons', array( $this, 'add_media_button' ) );

		//if ( 'true' !== get_user_option( 'rich_editing' ) ) {
		//	return;
		//}

		add_filter( 'mce_external_plugins', array( $this, 'add_buttons' ) );
		add_filter( 'mce_buttons', array( $this, 'register_buttons' ) );
	}

	public function add_buttons( $plugin_array ) {
		$plugin_array['whisk_recipe'] = Utils::get_plugin_file_uri( 'assets/js/tinymce.min.js' );

		return $plugin_array;
	}

	/**
	 * Register buttons.
	 *
	 * @param array $buttons Default buttons.
	 *
	 * @return mixed
	 */
	public function register_buttons( $buttons ) {
		$buttons[] = 'whisk_recipe';

		return $buttons;
	}

	/**
	 * Add custom button for TinyMCE.
	 *
	 * @link https://www.sitepoint.com/adding-a-media-button-to-the-content-editor/
	 */
	public function add_media_button() {
		if ( 'whisk_recipe' === get_current_screen()->id ) {
			return;
		}
		?>
		<button type="button" class="button" id="whisk-add-recipe-toggle">
			<span class="wp-media-buttons-icon dashicons dashicons-carrot"></span>
			Whisk Recipe
		</button>
		<?php
	}
}
