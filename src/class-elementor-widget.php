<?php
/**
 * Elementor
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes;

use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Class Elementor
 *
 * @package whisk-recipes
 */
class Elementor_Widget extends Widget_Base {
	/**
	 * Widget constructor.
	 *
	 * @param array $data Data.
	 * @param null  $args Arguments.
	 *
	 * @throws \Exception Exception.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	/**
	 * Retrieve heading widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'whisk-recipes';
	}

	/**
	 * Retrieve heading widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Whisk Recipes', 'whisk-recipes' );
	}

	/**
	 * Retrieve heading widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		$ui_theme = SettingsManager::get_settings_managers( 'editorPreferences' )
			->get_model()
			->get_settings( 'ui_theme' );

		return 'elementor-whisk-recipes elementor-whisk-recipes--' . $ui_theme;
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'general' );
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'general',
			[
				'label' => __( 'General', 'whisk-recipes' ),
			]
		);

		$this->add_control(
			'create_link',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw' => '<a href="' . esc_url( admin_url( 'post-new.php?post_type=whisk_recipe' ) ) .'" target="_blank">' . __( 'Create or edit Recipe', 'whisk-recipes' ) . '</a>',
			)
		);

		$this->add_control(
			'recipe_id',
			array(
				'type' => Controls_Manager::HIDDEN,
				'default' => false,
			)
		);

		$this->add_control(
			'recipe_select',
			array(
				'type' => 'whisk-recipe-select',
			)
		);

		$this->add_control(
			'recipe_share',
			[
				'label'   => __( 'Share block', 'whisk-recipes' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'recipe_author',
			[
				'label'   => __( 'Author block', 'whisk-recipes' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'recipe_comments',
			[
				'label'   => __( 'Comments block', 'whisk-recipes' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( $settings['recipe_id'] ) {
			$attributes = array();

			$attributes['id'] = $settings['recipe_id'];

			if ( $settings['recipe_share'] ) {
				$attributes['share'] = 'yes';
			}

			if ( $settings['recipe_author'] ) {
				$attributes['author'] = 'yes';
			}

			if ( $settings['recipe_comments'] ) {
				$attributes['comments'] = 'yes';
			}

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo do_shortcode( sprintf( '[whisk-recipe%s]', $this->array_to_shortcode( $attributes ) ) );
			} else {
				printf( '[whisk-recipe%s]', $this->array_to_shortcode( $attributes ) );
			}


		} else {
			_e( 'Please, select a recipe first.', 'whisk-recipes' );
		}
	}

	public function array_to_shortcode( $attributes ) {
		$result = '';

		foreach ( $attributes as $attribute => $value ) {
			$result .= sprintf( ' %s="%s"', $attribute, esc_attr( $value ) );
		}

		return $result;
	}
}
