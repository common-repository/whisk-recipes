<?php
/**
 * Class Main
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes;

use Elementor\Widgets_Manager;
use Elementor\Controls_Manager;
use Elementor\Core\Settings\Manager as SettingsManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

use Whisk\Recipes\Models\Studio;
use Whisk\Recipes\Models\Sync;
use Whisk\Recipes\Vendor\Auryn\Injector;
use Whisk\Recipes\Vendor\Auryn\ConfigException;
use Whisk\Recipes\Vendor\Auryn\InjectionException;
use Whisk\Recipes\Vendor\Carbon_Fields\Carbon_Fields;

use Whisk\Recipes\Controllers\API;
use Whisk\Recipes\Import\Background_Import;
use Whisk\Recipes\Models\Avoidance;
use Whisk\Recipes\Models\Cooking_Technique;
use Whisk\Recipes\Gutenberg\Gutenberg;
use Whisk\Recipes\Models\Cuisine;
use Whisk\Recipes\Models\Diet;
use Whisk\Recipes\Models\Equipment;
use Whisk\Recipes\Models\Ingredient;
use Whisk\Recipes\Models\Meal_Type;
use Whisk\Recipes\Models\Nutrition;
use Whisk\Recipes\Models\Proxy;
use Whisk\Recipes\Models\Rating;
use Whisk\Recipes\Models\Recipe;
use Whisk\Recipes\Models\Schema;
use Whisk\Recipes\Models\Tag;
use Whisk\Recipes\Models\Template_Loader;
use Whisk\Recipes\Onboarding\Onboarding;
use Whisk\Recipes\Views\Restricted_Grocers;
use Whisk\Recipes\Views\Settings;
use Whisk\Recipes\Views\Customizer;
use Whisk\Recipes\Import\General_Import;
use Whisk\Recipes\Views\Tracking;

/**
 * Class Main
 */
class Main {

	/**
	 * Template_Loader instance.
	 *
	 * @var Template_Loader $template_loader
	 */
	private $template_loader;

	/**
	 * Injector instance.
	 *
	 * @var Injector
	 */
	private $injector;

	/**
	 * Main constructor.
	 *
	 * @param Injector $injector Injector instance.
	 */
	public function __construct( Injector $injector ) {
		$this->injector = $injector;
	}

	/**
	 * Init plugin.
	 */
	public function init() {
		$this->load();
		$this->setup_hooks();

		do_action( 'whisk_recipes_init', $this );

		if ( is_admin() ) {
			do_action( 'whisk_recipes_admin_init', $this );
		}
	}

	/**
	 * Loads the plugin into WordPress.
	 */
	public function load() {
		$this->template_loader = new Template_Loader();

		// Monolog logger.
		$this->injector->alias(
			LoggerInterface::class,
			Logger::class
		);

		$this->injector->share( Logger::class );

		$this->injector->define(
			Logger::class,
			array(
				':name' => Utils::get_plugin_slug(),
			)
		);

		( $this->make( Proxy::class ) )->setup_hooks();
		( $this->make( Sync::class ) )->setup_hooks();
		( $this->make( Tag::class ) )->setup_hooks();
		( $this->make( Ingredient::class ) )->setup_hooks();
		//( $this->make( Equipment::class ) )->setup_hooks();
		( $this->make( Diet::class ) )->setup_hooks();
		( $this->make( Avoidance::class ) )->setup_hooks();
		( $this->make( Customizer::class ) )->setup_hooks();
		( $this->make( Cuisine::class ) )->setup_hooks();
		( $this->make( Meal_Type::class ) )->setup_hooks();
		( $this->make( Cooking_Technique::class ) )->setup_hooks();
		( $this->make( Nutrition::class ) )->setup_hooks();
		( $this->make( Rating::class ) )->setup_hooks();
		( $this->make( Recipe::class ) )->setup_hooks();
		( $this->make( Schema::class ) )->setup_hooks();
		( $this->make( Settings::class ) )->setup_hooks();
		( $this->make( Shortcode::class ) )->setup_hooks();
		( $this->make( API::class ) )->setup_hooks();
		( $this->make( Tracking::class ) )->setup_hooks();
		( $this->make( Restricted_Grocers::class ) )->setup_hooks();

		if ( is_admin() ) {
			//( $this->make( Studio::class ) )->setup_hooks();
			( $this->make( Onboarding::class ) )->add_hooks();

			( $this->make( Background_Import::class ) );
			( $this->make( General_Import::class ) )->add_hooks();

			//if ( ! Utils::is_gutenberg_editor_active() ) {
			//	( $this->make( TinyMCE::class ) )->setup_hooks();
			//}
		}

		if ( Utils::is_gutenberg_editor_active() ) {
			( $this->make( Gutenberg::class ) )->add_hooks();
		}
	}

	/**
	 * Make a class from DIC.
	 *
	 * @param string $class_name Full class name.
	 * @param array  $args List of arguments.
	 *
	 * @return mixed
	 *
	 * @throws InjectionException If a cyclic gets detected when provisioning.
	 * @throws ConfigException If $nameOrInstance is not a string or an object.
	 */
	public function make( $class_name, $args = [] ) {

		$this->injector->share( $class_name );

		return $this->injector->make( $class_name, $args );
	}

	/**
	 * Setup main hooks.
	 */
	public function setup_hooks() {
		add_action( 'after_setup_theme', array( $this, 'carbon_boot' ) );
		add_action( 'init', array( $this, 'gutenberg_register_files' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_assets' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_async_attribute' ), 10, 2 );
		add_filter( 'comments_template', array( $this, 'comments_template' ) );
		add_filter( 'comment_form_fields', array( $this, 'reorder_comment_form_fields' ) );
		add_action( 'pre_get_posts', array( $this, 'alter_query' ) );
		add_action( 'wp_footer', array( $this, 'add_frontend_modals' ) );
		add_action( 'admin_footer', array( $this, 'add_admin_modals' ) );

		if ( did_action( 'elementor/loaded' ) ) {
			add_action( 'elementor/controls/controls_registered', array( $this, 'elementor_controls' ) );
			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'elementor_editor_scripts' ) );
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'elementor_require_widgets' ) );
		}

		register_activation_hook( Utils::get_plugin_file(), array( $this, 'activate' ) );
		register_deactivation_hook( Utils::get_plugin_file(), array( $this, 'deactivate' ) );

	}

	/**
	 * Enqueue scripts for editor.
	 */
	public function elementor_editor_scripts() {
		wp_enqueue_style(
			Utils::get_plugin_prefix() . '-admin-elementor',
			Utils::get_plugin_file_uri( 'assets/css/admin-elementor.min.css' ),
			array(),
			Utils::get_plugin_version()
		);
	}

	/**
	 * Elementor Compatibility.
	 *
	 * @since	5.0.0
	 *
	 * @param Controls_Manager $controls_manager Controls_Manager instance.
	 */
	public function elementor_controls( Controls_Manager $controls_manager ) {
		$controls_manager->register_control( 'whisk-recipe-select', new Elementor_Control() );
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.3
	 * @access public
	 *
	 * @param Widgets_Manager $widgets_manager Widgets_Manager instance.
	 */
	public function elementor_require_widgets( Widgets_Manager $widgets_manager ) {
		$widgets_manager->register_widget_type( new Elementor_Widget() );
	}

	/**
	 * Add frontend modals to footer.
	 */
	public function add_frontend_modals() {
		$this->template_loader->get_template_part( 'frontend/modals/modals' );
		$this->template_loader->get_template_part( 'frontend/modals/share' );
	}

	/**
	 * Add admin modals to footer.
	 */
	public function add_admin_modals() {
		$this->template_loader->get_template_part( 'admin/modals/add-ingredient' );
		$this->template_loader->get_template_part( 'admin/modals/add-recipe' );
	}

	/**
	 * Alter WP Query.
	 *
	 * @param \WP_Query $wp_query WP_Query instance.
	 */
	public function alter_query( \WP_Query $wp_query ) {
		if ( $wp_query->is_admin || ! $wp_query->is_main_query() ) {
			return;
		}

		if ( ! whisk_carbon_get_theme_option( 'whisk_semantic_url' ) || ! whisk_carbon_get_theme_option( 'whisk_archive_on_front_page' ) ) {
			return;
		}

		if ( $wp_query->is_home() ) {
			$wp_query->set( 'post_type', 'whisk_recipe' );
			$wp_query->set( 'posts_per_page', whisk_carbon_get_theme_option( 'whisk_archive_posts_per_page' ) );
			$wp_query->is_archive           = true;
			$wp_query->is_post_type_archive = true;
		} elseif ( 'page' === get_option( 'show_on_front' ) && $wp_query->get( 'page_id' ) === get_option( 'page_on_front' ) ) {
			$wp_query->set( 'post_type', 'whisk_recipe' );
			$wp_query->set( 'posts_per_page', whisk_carbon_get_theme_option( 'whisk_archive_posts_per_page' ) );
			$wp_query->set( 'page_id', '' );
			$wp_query->is_archive           = true;
			$wp_query->is_post_type_archive = true;
			$wp_query->is_page              = false;
			$wp_query->is_singular          = false;
		}
	}

	/**
	 * Reorder Comment Form Fields.
	 *
	 * @param array $fields Default fields.
	 *
	 * @return array
	 */
	public function reorder_comment_form_fields( $fields ) {
		global $post;

		if ( is_singular( 'whisk_recipe' ) && 'open' === $post->comment_status ) {
			$new_fields = array();

			$order = array( 'author', 'email', 'url', 'comment' );

			foreach ( $order as $key ) {
				$new_fields[ $key ] = $fields[ $key ];
				unset( $fields[ $key ] );
			}

			if ( $fields ) {
				foreach ( $fields as $key => $val ) {
					$new_fields[ $key ] = $val;
				}
			}

			return $new_fields;
		}

		return $fields;
	}

	/**
	 * Set comments template path.
	 *
	 * @param string $comment_template Default path to comments template.
	 *
	 * @return string
	 */
	public function comments_template( $comment_template ) {
		global $post;

		if ( is_singular( 'whisk_recipe' ) && 'open' === $post->comment_status ) {
			return Utils::get_plugin_path() . '/template-parts/frontend/comments.php';
		}

		return $comment_template;
	}

	/**
	 * Add plugin assets on frontend.
	 */
	public function frontend_enqueue_assets() {
		wp_enqueue_style(
			Utils::get_plugin_prefix(),
			Utils::get_plugin_file_uri( 'assets/css/app.min.css' ),
			array(),
			Utils::get_plugin_version()
		);

		wp_enqueue_style(
			Utils::get_plugin_prefix() . '-print',
			Utils::get_plugin_file_uri( 'assets/css/print.min.css' ),
			array(),
			Utils::get_plugin_version(),
			'print'
		);

		/**
		 * Whisk shopping list SDK.
		 *
		 * @link https://docs.whisk.com/shopping-list-sdk/basic-setup/basic-setup
		 */
		wp_enqueue_script(
			Utils::get_plugin_prefix() . '-sdk',
			'https://cdn.whisk.com/sdk/shopping-list.js',
			array( 'jquery' ),
			Utils::get_plugin_version(),
			true
		);

		wp_add_inline_script( Utils::get_plugin_prefix() . '-sdk', 'var whisk = whisk || {}; whisk.queue = whisk.queue || [];' );

		wp_enqueue_script(
			Utils::get_plugin_prefix(),
			Utils::get_plugin_file_uri( 'assets/js/app.min.js' ),
			array( 'jquery' ),
			Utils::get_plugin_version(),
			true
		);

		$custom_css = whisk_carbon_get_theme_option( 'whisk_custom_css' );

		if ( $custom_css ) {
			wp_add_inline_style( Utils::get_plugin_prefix(), $custom_css );
		}
	}

	/**
	 * Add async attribute for script tag.
	 *
	 * @param string $tag
	 * @param string $handle
	 *
	 * @return string|string[]
	 */
	public function add_async_attribute( $tag, $handle ) {
		if ( Utils::get_plugin_prefix() . '-sdk' !== $handle ) {
			return $tag;
		}

		return str_replace( ' src', ' async="async" src', $tag );
	}

	/**
	 * Fired on plugin activate.
	 */
	public function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		Utils::schedule_flush_rewrite_rules();

		update_option( Utils::get_plugin_slug() . '_version', Utils::get_plugin_version(), true );

		$ratings = "CREATE TABLE {$wpdb->prefix}whisk_ratings (
	                rating_id int(10) unsigned NOT NULL AUTO_INCREMENT,
	                recipe_id int(10) unsigned NOT NULL,
	                user_id int(10) unsigned NOT NULL,
	                rating tinyint(1) unsigned DEFAULT 0,
	                ip varchar(255) DEFAULT NULL,
	                user_agent varchar(255) DEFAULT NULL,
	                rating_date DATETIME DEFAULT NOW(),
	                PRIMARY KEY (rating_id),
	                UNIQUE KEY recipe_id (recipe_id,ip,user_agent),
	                UNIQUE KEY rating_id (rating_id)
				) {$charset_collate};";

		$sync = "CREATE TABLE {$wpdb->prefix}whisk_sync (
	                sync_id int(10) unsigned NOT NULL AUTO_INCREMENT,
	                recipe_id varchar(255) NOT NULL,
	                post_id int(10) unsigned NOT NULL,
	                sync_date bigint(20) unsigned NOT NULL,
	                status enum('completed','waiting','failed','deleted','running') DEFAULT 'waiting',
	                PRIMARY KEY (sync_id),
	                UNIQUE KEY sync_id (sync_id),
	                UNIQUE KEY recipe_id (recipe_id),
	                UNIQUE KEY recipe_post (recipe_id,post_id)
				) {$charset_collate};";

		dbDelta( $ratings );
		dbDelta( $sync );

		if ( Onboarding::is_new_install() ) {
			set_transient( '_whisk_onboarding_redirect', 1, 30 );
		}
	}

	/**
	 * Flush rewrite rules on plugin activate.
	 */
	public function flush_rewrite_rules() {
		if ( get_option( Utils::get_plugin_slug() . '_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_option( Utils::get_plugin_slug() . '_flush_rewrite_rules' );
		}
	}

	/**
	 * Fired on plugin deactivate.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Init Carbon Fields.
	 */
	public function carbon_boot() {
		Carbon_Fields::boot();
	}

	/**
	 * Remove meta box form Gutenberg editor.
	 */
	public function gutenberg_register_files() {
		wp_register_script(
			Utils::get_plugin_prefix() . '-gutenberg',
			Utils::get_plugin_file_uri( 'assets/js/gutenberg.min.js' ),
			array( 'wp-blocks', 'wp-edit-post' ),
			Utils::get_plugin_version(),
			true
		);

		register_block_type(
			'cc/ma-block-files',
			array(
				'editor_script' => Utils::get_plugin_prefix() . '-gutenberg',
			)
		);
	}

	/**
	 * Admin enqueue assets.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			Utils::get_plugin_prefix() . '-admin',
			Utils::get_plugin_file_uri( 'assets/js/admin.min.js' ),
			array( 'wp-element', 'wp-i18n', 'wp-editor', 'wp-hooks', 'jquery' ),
			Utils::get_plugin_version(),
			false
		);

		wp_enqueue_style(
			Utils::get_plugin_prefix() . '-admin',
			Utils::get_plugin_file_uri( 'assets/css/admin.min.css' ),
			array(),
			Utils::get_plugin_version()
		);

		add_thickbox();
	}
}
