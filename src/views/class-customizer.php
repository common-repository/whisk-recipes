<?php
/**
 * Class Customizer
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Views;

use Kirki;
use Whisk\Recipes\Utils;
use WP_Customize_Manager;

/**
 * Class Customizer
 */
class Customizer {

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @var WP_Customize_Manager
	 */
	private $wp_customize;

	/**
	 * Customizer constructor.
	 */
	public function __construct() {

		require_once Utils::get_plugin_path() . '/vendor/aristath/kirki/kirki.php';

		if ( ! class_exists( 'Kirki' ) ) {
			return;
		}

		Kirki::add_config(
			'whisk_theme_config_id',
			array(
				'capability'  => 'edit_theme_options',
				'option_type' => 'theme_mod',
			)
		);

		Kirki::add_section(
			'whisk_recipes',
			array(
				'title'       => esc_html__( 'Whisk Recipes', 'whisk-recipes' ),
				'description' => esc_html__( 'General Whisk Recipes section.', 'whisk-recipes' ),
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'color',
				'settings' => 'whisk_primary_color',
				'label'    => __( 'Primary color', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => '#3dc795',
				'output'   => array(
					array(
						'element'  => '.whisk-single .whisk-btn-primary',
						'property' => 'background-color',
						'suffix'   => '!important',
					),
					array(
						'element'  => '.whisk-single .whisk-link',
						'property' => 'color',
						'suffix'   => '!important',
					),
					array(
						'element'  => '.whisk-single .whisk-btn-link',
						'property' => 'color',
						'suffix'   => '!important',
					),
					array(
						'element'  => '.whisk-pagination .page-numbers.current, .whisk-notes__item:before',
						'property' => 'background-color',
						'suffix'   => '!important',
					),
					array(
						'element'  => '.whisk-play-video .whisk-icon',
						'property' => 'fill',
						'suffix'   => '!important',
					),
					array(
						'element'  => '.whisk-input_number__plus, .whisk-input_number__minus',
						'property' => 'color',
						'suffix'   => '!important',
					),
					array(
						'element'  => '.whisk-content a',
						'property' => 'color',
						'suffix'   => '!important',
					),
				),
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'color',
				'settings' => 'whisk_secondary_color',
				'label'    => __( 'Secondary color', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => '#333333',
				'output'   => array(
					array(
						'element'  => '.whisk-single, .whisk-archive, .whisk-description',
						'property' => 'color',
						'suffix'   => '!important',
					),
				),
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'slider',
				'settings' => 'whisk_border_radius',
				'label'    => esc_html__( 'Border radius', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => 25,
				'choices'  => [
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				],
				'output'   => array(
					array(
						'element'  => '.whisk-single .whisk-btn',
						'property' => 'border-radius',
						'suffix'   => 'px!important',
					),
				),
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'slider',
				'settings' => 'whisk_border_radius_sm',
				'label'    => esc_html__( 'Border radius small', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => 10,
				'choices'  => [
					'min'  => 0,
					'max'  => 20,
					'step' => 1,
				],
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'      => 'typography',
				'settings'  => 'whisk_typography',
				'label'     => esc_html__( 'Typography', 'whisk-recipes' ),
				'section'   => 'whisk_recipes',
				'default'   => array(
					'font-family'    => 'inherit',
					'variant'        => 'regular',
					'font-size'      => '16px',
					'line-height'    => '1.4',
					'letter-spacing' => '0',
					'text-transform' => 'none',
				),
				'priority'  => 10,
				'transport' => 'auto',
				'output'    => array(
					array(
						'element' => '.whisk-single,.whisk-archive',
					),
				),
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'toggle',
				'settings' => 'whisk_use_share_block',
				'label'    => esc_html__( 'Share block', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => '1',
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'toggle',
				'settings' => 'whisk_use_author_block',
				'label'    => esc_html__( 'Author block', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => '1',
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'toggle',
				'settings' => 'whisk_use_comments_block',
				'label'    => esc_html__( 'Comments block', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => '1',
			)
		);

		Kirki::add_field(
			'whisk_theme_config_id',
			array(
				'type'     => 'toggle',
				'settings' => 'whisk_use_nutrition_block',
				'label'    => esc_html__( 'Nutrition block', 'whisk-recipes' ),
				'section'  => 'whisk_recipes',
				'default'  => '1',
			)
		);

	}

	/**
	 * Setup hooks.
	 */
	public function setup_hooks() {
		// Disable the telemetry module.
		add_filter( 'kirki_telemetry', '__return_false' );

		add_action( 'customize_controls_print_scripts', array( $this, 'customize_controls_print_scripts' ) );
		add_action( 'wp_ajax_whisk_customizer_reset', array( $this, 'ajax_customizer_reset' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
	}

	/**
	 * Enqueue assets.
	 */
	public function customize_controls_print_scripts() {
		wp_enqueue_script(
			'whisk-zoom-customizer-reset',
			Utils::get_plugin_file_uri( 'assets/js/customizer.min.js' ),
			array( 'jquery' ),
			Utils::get_plugin_version(),
			true
		);

		wp_localize_script(
			'whisk-zoom-customizer-reset',
			'_WhiskZoomCustomizerReset',
			array(
				'reset'   => __( 'Reset', 'whisk-recipes' ),
				'confirm' => __( "Attention! This will remove all customizations ever made via customizer to this theme!\n\nThis action is irreversible!", 'whisk-recipes' ),
				'nonce'   => array(
					'reset' => wp_create_nonce( 'whisk-customizer-reset' ),
				),
			)
		);
	}

	/**
	 * Store a reference to `WP_Customize_Manager` instance
	 *
	 * @param WP_Customize_Manager $wp_customize WP_Customize_Manager instance.
	 */
	public function customize_register( WP_Customize_Manager $wp_customize ) {
		$this->wp_customize = $wp_customize;
	}

	/**
	 * Reset Customizer settings action.
	 */
	public function ajax_customizer_reset() {
		if ( ! $this->wp_customize->is_preview() ) {
			wp_send_json_error( 'not_preview' );
		}

		if ( ! check_ajax_referer( 'whisk-customizer-reset', 'nonce', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		$this->reset_customizer();

		wp_send_json_success();
	}

	/**
	 * Reset Customizer settings
	 */
	public function reset_customizer() {
		$settings = $this->wp_customize->settings();

		// remove theme_mod settings registered in customizer.
		foreach ( $settings as $setting ) {
			if ( 'theme_mod' === $setting->type ) {
				remove_theme_mod( $setting->id );
			}
		}
	}
}
