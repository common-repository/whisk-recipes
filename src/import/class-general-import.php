<?php
namespace Whisk\Recipes\Import;

/**
 * Class General_Import
 *
 * @package whisk-recipes
 */
class General_Import {

	/**
	 * Importers as array of their names => post types
	 *
	 * @var string[]
	 */
	private $importers = [
		'Tasty Recipes'      => 'tasty_recipe',
		'WP Recipe Maker'    => 'wprm_recipe',
		'WP Ultimate Recipe' => 'recipe',
		'EasyRecipe'         => 'easy_recipe',
		'Zip Recipes'        => 'zip_recipes',
	];

	/**
	 * Background process
	 *
	 * @var Background_Import
	 */
	private $background_import;

	/**
	 * Progress transient name.
	 *
	 * @var string
	 */
	private $progress_transient_name = 'whisk_import_progress';

	/**
	 * General_Import constructor.
	 *
	 * @param Background_Import $import Background processes class.
	 */
	public function __construct( Background_Import $import ) {
		$this->background_import = $import;

		$this->add_hooks();
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		add_action( 'admin_menu', [ $this, 'register_import_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_whisk_do_import', [ $this, 'do_import' ] );
			add_action( 'wp_ajax_whisk_get_progress', [ $this, 'get_progress' ] );
		}
		add_filter( 'wp_kses_allowed_html', [ $this, 'kses_add_img_tags' ], 10, 2 );
	}

	/**
	 * Adding import page.
	 */
	public function register_import_page() {
		add_submenu_page( 'edit.php?post_type=whisk_recipe', __( 'Convert Recipes', 'whisk-recipes' ), __( 'Convert Recipes', 'whisk-recipes' ), 'manage_options', 'wsk_import', [ $this, 'import_page_callback' ] );
	}

	/**
	 * Admin scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'whisk_recipe_page_wsk_import' === $screen->id ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_script(
				'whisk-admin-import',
				WHISK_RECIPES_URL . '/assets/js/admin-import.min.js',
				'',
				WHISK_RECIPES_VERSION,
				true
			);
			wp_localize_script(
				'whisk-admin-import',
				'whisk_import',
				[
					'nonce'      => wp_create_nonce( 'whisk' ),
					'complete'   => __( 'Import complete. Please reload page if you want to run another import.', 'whisk-recipes' ),
					'inProgress' => __( 'Import in progress', 'whisk-recipes' ),
				]
			);

			wp_enqueue_style(
				'whisk-admin-import-css',
				WHISK_RECIPES_URL . '/assets/css/admin-import.min.css',
				array(),
				WHISK_RECIPES_VERSION
			);
		}
	}

	/**
	 * Import page view.
	 *
	 * @throws \Exception Error.
	 */
	public function import_page_callback() {
		$template_data = [];
		foreach ( $this->importers as $importer_name => $importer_slug ) {
			$importer        = $this->load_importer( $importer_slug );
			$template_data[] = [
				'slug'        => $importer_slug,
				'name'        => $importer_name,
				'found_posts' => $importer->count_posts(),
			];
		}
		include_once WHISK_RECIPES_PATH . '/templates/import-general-page.php';
	}

	/**
	 * Import ajax callback.
	 *
	 * @throws \Exception Error.
	 */
	public function do_import() {
		check_ajax_referer( 'whisk', 'nonce' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$importer_name = isset( $_POST['importer'] ) ? wp_unslash( $_POST['importer'] ) : '';

		if ( ! in_array( $importer_name, $this->importers, true ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Something went wrong', 'whisk-recipes' ),
				]
			);
		}
		$importer = self::load_importer( $importer_name );
		$posts    = $importer->get_all_posts_ids();
		if ( ! $posts ) {
			wp_send_json_error(
				[
					'message' => __( 'No found posts', 'whisk-recipes' ),
				]
			);
		}
		set_transient( $this->progress_transient_name, 0, 12 * HOUR_IN_SECONDS );
		set_transient( 'whisk_current_importer', $importer_name, 1 * HOUR_IN_SECONDS );

		// Add post ids to queue.
		if ( 'zip_recipes' === $importer_name ) {
			foreach ( (array) $posts as $post ) {
				$this->background_import->push_to_queue( [ 'zip_recipes' => $post->recipe_id ] );
			}
		} else {
			foreach ( (array) $posts as $post ) {
				$this->background_import->push_to_queue( $post->ID );
			}
		}
		$this->background_import->save()->dispatch();

		wp_send_json_success(
			[
				'message' => __( 'Import process started', 'whisk-recipes' ),
			]
		);
	}

	/**
	 * Ajax callback with progress data.
	 *
	 * @throws \Exception Error message.
	 */
	public function get_progress() {
		$progress      = get_transient( $this->progress_transient_name );
		$importer_name = get_transient( 'whisk_current_importer' ) ? get_transient( 'whisk_current_importer' ) : '';
		$total         = 0;
		if ( $importer_name ) {
			$importer = self::load_importer( $importer_name );
			$total    = $importer->count_posts();
		}

		wp_send_json_success(
			[
				'progress' => $progress,
				'importer' => $importer_name,
				'total'    => $total,
			]
		);
	}

	/**
	 * Add srset attribute to img tag.
	 *
	 * @param array        $allowed Allowed tags.
	 * @param string|array $context Context.
	 *
	 * @return mixed
	 */
	public function kses_add_img_tags( $allowed, $context ) {
		if ( 'post' === $context ) {
			$allowed['img']['srcset'] = true;
		}

		return $allowed;
	}

	/**
	 * Instantiate importer class
	 *
	 * @param string $name Importer name.
	 *
	 * @return Easyrecipe_Import|Recipemaker_Import|Tasty_Import|Ultimate_Import|Zip_Import
	 * @throws \Exception Error.
	 */
	public static function load_importer( $name ) {
		switch ( $name ) {
			case 'tasty_recipe':
				return new Tasty_Import();
			case 'wprm_recipe':
				return new Recipemaker_Import();
			case 'recipe':
				return new Ultimate_Import();
			case 'easy_recipe':
				return new Easyrecipe_Import();
			case 'zip_recipes':
				return new Zip_Import();
			default:
				throw new \Exception( 'Class not found' );
		}
	}

}
