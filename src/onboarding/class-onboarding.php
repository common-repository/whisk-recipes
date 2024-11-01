<?php
/**
 * Onboarding.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Onboarding;

use Whisk\Recipes\Controllers\API;
use Whisk\Recipes\Utils;

/**
 * Class Onboarding
 */
class Onboarding {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps;

	/**
	 * Onboarding constructor.
	 */
	public function __construct() {
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		add_action( 'admin_init', [ $this, 'onboarding_redirect' ] );
		add_action( 'admin_init', [ $this, 'onboarding_launch' ] );
		add_action( 'admin_menu', [ $this, 'register_onboarding' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_whisk_process_studio_login', [ $this, 'process_studio_login' ] );
		}
	}

	/**
	 * Register onboarding page.
	 */
	public function register_onboarding() {
		add_dashboard_page( '', '', 'manage_options', 'whisk-setup', '' );
	}

	/**
	 * Perform redirect to onboarding page
	 */
	public function onboarding_redirect() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( get_transient( '_whisk_onboarding_redirect' )
			&& apply_filters( 'whisk_enable_onboarding', true )
			&& current_user_can( 'manage_options' )
		) {
			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
			delete_transient( '_whisk_onboarding_redirect' );
			if ( 'whisk-setup' === $current_page ) {
				return;
			}
			wp_safe_redirect( admin_url( 'index.php?page=whisk-setup' ) );
			exit;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Show onboarding info.
	 */
	public function onboarding_launch() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['page'] ) || 'whisk-setup' !== $_GET['page'] ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		$this->steps = [
			'welcome'        => [
				'name' => __( 'Welcome!', 'whisk-recipes' ),
				'view' => 'step-welcome-view',
			],
			'urls_setup'     => [
				'name'    => __( 'Setup recipe URLs', 'whisk-recipes' ),
				'view'    => 'step-urls-setup-view',
				'handler' => 'step_urls_setup_handle',
			],
			'ready'          => [
				'name' => __( 'Ready!', 'whisk-recipes' ),
				'view' => 'step-ready-view',
			],
		];
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		foreach ( $this->steps as $step_name => $step ) {
			$this->steps[ $step_name ]['url'] = add_query_arg(
				[
					'page' => 'whisk-setup',
					'step' => $step_name,
				],
				admin_url( 'index.php' )
			);
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( [ $this, $this->steps[ $this->step ]['handler'] ], $this );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $this->get_next_step_url( $this->step ) ) {
			update_option( 'whisk_onboarding_passed', 1, false );
		}

		ob_start();
		$this->output_header();
		$this->output_steps();
		$this->output_content();
		$this->output_footer();

		exit;
	}

	/**
	 * Studio login step processing.
	 */
	public function process_studio_login() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$api_token      = isset( $_POST['api_key'] ) ? wp_unslash( $_POST['api_key'] ) : '';
		$integration_id = isset( $_POST['integration_id'] ) ? wp_unslash( $_POST['integration_id'] ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! $api_token || ! $integration_id ) {
			wp_send_json_error(
				[
					'message' => __( 'Key and Integration ID should not be empty', 'whisk-recipes' ),
				]
			);
		}

		update_option( '_whisk_api_token', $api_token, false );
		update_option( '_whisk_api_integration_id', $integration_id, false );

		wp_send_json_success(
			[
				'message' => __( 'Connection established successfully', 'whisk-recipes' ),
			]
		);

	}

	/**
	 * Urls setup step processing.
	 */
	private function step_urls_setup_handle() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$type = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : '';
		if ( $type && 'semantic' === $type && isset( $_POST['urls_base'] ) ) {
			$taxonomy = sanitize_key( $_POST['urls_base'] );
			whisk_carbon_set_theme_option( 'whisk_semantic_url', true );
			whisk_carbon_set_theme_option( 'whisk_semantic_url_taxonomy', $taxonomy );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		wp_safe_redirect( $this->get_next_step_url( 'urls_setup' ) );
		exit;
	}

	/**
	 * Header view.
	 */
	private function output_header() {
		$data = [
			'wp_version_class' => 'branch-' . str_replace( array( '.', ',' ), '-', floatval( get_bloginfo( 'version' ) ) ),
			'current_step'     => $this->step,
			'plugin_url'       => Utils::get_plugin_file_uri(),
		];

		set_current_screen();
		include_once 'views/ob-header.php';
	}

	/**
	 * Footer view.
	 */
	private function output_footer() {
		include_once 'views/ob-footer.php';
	}

	/**
	 * Steps view.
	 */
	private function output_steps() {
		$data = [
			'steps' => $this->steps,
		];

		include_once 'views/ob-steps.php';
	}

	/**
	 * Output the content for the current step.
	 */
	public function output_content() {
		echo '<div class="whisk-setup-content">';

		if ( isset( $this->steps[ $this->step ]['view'] ) && $this->steps[ $this->step ]['view'] ) {
			include_once "views/{$this->steps[ $this->step ]['view']}.php";
		}
		echo '</div>';
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['page'] ) || 'whisk-setup' !== $_GET['page'] ) {
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		wp_enqueue_style(
			'whisk_ob_styles',
			Utils::get_plugin_file_uri( 'assets/css/ob.min.css' ),
			array(),
			Utils::get_plugin_version()
		);
		wp_enqueue_script(
			'whisk-ob-script',
			Utils::get_plugin_file_uri( 'assets/js/ob.min.js' ),
			array(),
			Utils::get_plugin_version(),
			true
		);
		wp_localize_script(
			'whisk-ob-script',
			'whisk_ob',
			[
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'empty_error' => __( 'Key and Integration ID should not be empty', 'whisk-recipes' ),
			]
		);
	}

	/**
	 * Get next step url from current step.
	 *
	 * @param string $current_step Current onboarding step.
	 *
	 * @return string
	 */
	private function get_next_step_url( $current_step ) {
		$keys     = array_keys( $this->steps );
		$position = array_search( $current_step, $keys, true );
		if ( isset( $keys[ $position + 1 ] ) ) {
			$next_step = $keys[ $position + 1 ];
			$next_url  = add_query_arg(
				[
					'page' => 'whisk-setup',
					'step' => $next_step,
				],
				admin_url( 'index.php' )
			);

			return $next_url;
		}

		return '';
	}

	/**
	 * Get previous step url from current step.
	 *
	 * @param string $current_step Current onboarding step.
	 *
	 * @return string
	 */
	private function get_previous_step_url( $current_step ) {
		$keys     = array_keys( $this->steps );
		$position = array_search( $current_step, $keys, true );
		if ( isset( $keys[ $position - 1 ] ) ) {
			$prev_step = $keys[ $position - 1 ];
			$prev_url  = add_query_arg(
				[
					'page' => 'whisk-setup',
					'step' => $prev_step,
				],
				admin_url( 'index.php' )
			);

			return $prev_url;
		}

		return '';
	}

	/**
	 * Generate url for next onboarding step.
	 *
	 * @param string $step current step slug.
	 *
	 * @return string
	 */
	private function get_step_url( $step ) {
		if ( isset( $this->steps[ $step ] ) ) {
			return add_query_arg(
				array(
					'page' => 'whisk-setup',
					'step' => $step,
				),
				admin_url( 'index.php' )
			);
		}

		return '';
	}

	/**
	 * Check if onboarding should be launched.
	 *
	 * @return bool
	 */
	public static function is_new_install() {
		return ! get_option( 'whisk_onboarding_passed', 0 );
	}
}
