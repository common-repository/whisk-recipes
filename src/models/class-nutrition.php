<?php
/**
 * Recipe Nutrition.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;
use Whisk\Recipes\Utils;

/**
 * Class Nutrition
 */
class Nutrition {

	/**
	 * Default terms.
	 *
	 * @var string[]
	 */
	private $defaults = array(
		'Cholesterol free',
		'Diabetes friendly',
		'Fat free',
		'Healthy',
		'High fiber',
		'High monounsaturated fat',
		'High polyunsaturated fat',
		'High protein',
		'High unsaturated fat',
		'Low cholesterol',
		'Low carb',
		'Low energy',
		'Low fat',
		'Low salt',
		'Low saturated fat',
		'Low sodium',
		'Low sugars',
		'Salt free',
		'Saturated fat free',
		'Sodium free',
		'Source of fiber',
		'Source of protein',
		'Sugars free',
		'Very low salt',
		'Very low sodium',
	);

	/**
	 * Option name that set to 1 if terms already imported.
	 */
	const INSTALLED = 'whisk_nutrition_installed';

	/**
	 * Option name that set to 1 if term imported from whisk API.
	 */
	const IMPORTED = 'whisk_studio_imported';

	/**
	 * Taxonomy name (slug).
	 */
	const TAXONOMY = 'whisk_nutrition';

	/**
	 * Nutrition constructor.
	 */
	public function __construct() {
	}

	/**
	 * Setup recipe hooks.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		register_activation_hook( Utils::get_plugin_file(), array( $this, 'create_default_terms' ) );
	}

	/**
	 * Create default terms on first plugin activation.
	 */
	public function create_default_terms() {
		if ( get_option( self::INSTALLED ) ) {
			return;
		}

		$this->register_taxonomy();

		foreach ( $this->defaults as $term_name ) {
			$term = wp_create_term( $term_name, self::get_default_taxonomy_name() );
			add_term_meta( $term['term_id'], self::IMPORTED, '1' );
		}

		add_option( self::INSTALLED, 1, '', 'no' );
	}

	/**
	 * Register Post Taxonomy.
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Nutrition labels', 'Taxonomy General Name', 'whisk-recipes' ),
			'singular_name'              => _x( 'Nutrition label', 'Taxonomy Singular Name', 'whisk-recipes' ),
			'menu_name'                  => __( 'Nutrition labels', 'whisk-recipes' ),
			'all_items'                  => __( 'All Nutrition labels', 'whisk-recipes' ),
			'parent_item'                => __( 'Parent Nutrition label', 'whisk-recipes' ),
			'parent_item_colon'          => __( 'Parent Nutrition label:', 'whisk-recipes' ),
			'new_item_name'              => __( 'New Nutrition label', 'whisk-recipes' ),
			'add_new_item'               => __( 'Add Nutrition label', 'whisk-recipes' ),
			'edit_item'                  => __( 'Edit Nutrition label', 'whisk-recipes' ),
			'update_item'                => __( 'Update Nutrition label', 'whisk-recipes' ),
			'view_item'                  => __( 'View Nutrition label', 'whisk-recipes' ),
			'separate_items_with_commas' => __( 'Separate Nutrition with commas', 'whisk-recipes' ),
			'add_or_remove_items'        => __( 'Add or remove Nutrition', 'whisk-recipes' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'whisk-recipes' ),
			'popular_items'              => __( 'Popular Nutrition labels', 'whisk-recipes' ),
			'search_items'               => __( 'Search Nutrition labels', 'whisk-recipes' ),
			'not_found'                  => __( 'Not Found', 'whisk-recipes' ),
			'no_terms'                   => __( 'No Nutrition labels', 'whisk-recipes' ),
			'items_list'                 => __( 'Nutrition list', 'whisk-recipes' ),
			'items_list_navigation'      => __( 'Nutrition list navigation', 'whisk-recipes' ),
		);

		$rewrite = array(
			'slug'         => 'nutrition',
			'with_front'   => true,
			'hierarchical' => false,
		);

		if ( Helper::get_theme_option( 'whisk_semantic_url' ) && self::get_default_taxonomy_name() === Helper::get_theme_option( 'whisk_semantic_url_taxonomy' ) ) {
			$rewrite['slug'] = 'recipes';
		}

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => ! Utils::is_mapping_enabled(),
			'meta_box_cb'       => false,
			'show_ui'           => true,
			'show_admin_column' => false,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => $rewrite,
			'show_in_rest'      => true,
			'rest_base'         => 'nutrition',
		);

		register_taxonomy( self::get_default_taxonomy_name(), array( Recipe::get_cpt_name() ), $args );
	}

	/**
	 * Get default Recipe custom post type name.
	 *
	 * @return mixed|void
	 */
	public static function get_default_taxonomy_name() {
		return self::TAXONOMY;
	}

	/**
	 * Get Recipe custom post type name.
	 *
	 * @return mixed|void
	 */
	public static function get_taxonomy_name() {

		$name = self::get_default_taxonomy_name();

		if ( 'yes' === get_option( '_whisk_use_mapping' ) ) {
			$name = get_option( '_whisk_nutrition_taxonomy_name' );
		}

		return apply_filters( 'whisk_recipes_nutrition_taxonomy_name', $name );
	}
}
