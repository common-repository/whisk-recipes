<?php
/**
 * Recipe Cooking Technique.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;
use Whisk\Recipes\Utils;

/**
 * Class Cooking_Technique
 */
class Cooking_Technique {
	/**
	 * Default terms.
	 *
	 * @var string[]
	 */
	private $defaults = array(
		'Baking',
		'Barbecuing',
		'Blanching',
		'Blending',
		'Boiling',
		'Braising',
		'Broiling',
		'Browning',
		'Chilling',
		'Cooking',
		'Deep frying',
		'Freezing',
		'Frying',
		'Grilling',
		'Knife skills',
		'Marinating',
		'Microwaving',
		'Mixer',
		'Parboiling',
		'Pan frying',
		'Poaching',
		'Pickling and preserving',
		'Pressure cooking',
		'Roasting',
		'Saute',
		'Simmering',
		'Shaking',
		'Slow cooking',
		'Smoking',
		'Stewing',
		'Steaming',
		'Stirring',
		'Stir-frying',
		'Toasting',
		'Uncooked',
		'Whisking',
		'Wok skills',
	);

	/**
	 * Option name that set to 1 if terms already imported.
	 */
	const INSTALLED = 'whisk_cooking_technique_installed';

	/**
	 * Option name that set to 1 if term imported from whisk API.
	 */
	const IMPORTED = 'whisk_studio_imported';

	/**
	 * Taxonomy name (slug).
	 */
	const TAXONOMY = 'whisk_cooking_technique';

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
			'name'                       => _x( 'Cooking Techniques', 'Taxonomy General Name', 'whisk-recipes' ),
			'singular_name'              => _x( 'Cooking Technique', 'Taxonomy Singular Name', 'whisk-recipes' ),
			'menu_name'                  => __( 'Cooking Techniques', 'whisk-recipes' ),
			'all_items'                  => __( 'All Cooking Techniques', 'whisk-recipes' ),
			'parent_item'                => __( 'Parent Cooking Technique', 'whisk-recipes' ),
			'parent_item_colon'          => __( 'Parent Cooking Technique:', 'whisk-recipes' ),
			'new_item_name'              => __( 'New Cooking Technique', 'whisk-recipes' ),
			'add_new_item'               => __( 'Add Cooking Technique', 'whisk-recipes' ),
			'edit_item'                  => __( 'Edit Cooking Technique', 'whisk-recipes' ),
			'update_item'                => __( 'Update Cooking Technique', 'whisk-recipes' ),
			'view_item'                  => __( 'View Cooking Technique', 'whisk-recipes' ),
			'separate_items_with_commas' => __( 'Separate Cooking Techniques with commas', 'whisk-recipes' ),
			'add_or_remove_items'        => __( 'Add or remove Cooking Techniques', 'whisk-recipes' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'whisk-recipes' ),
			'popular_items'              => __( 'Popular Cooking Techniques', 'whisk-recipes' ),
			'search_items'               => __( 'Search Cooking Techniques', 'whisk-recipes' ),
			'not_found'                  => __( 'Not Found', 'whisk-recipes' ),
			'no_terms'                   => __( 'No Cooking Techniques', 'whisk-recipes' ),
			'items_list'                 => __( 'Cooking Techniques list', 'whisk-recipes' ),
			'items_list_navigation'      => __( 'Cooking Techniques list navigation', 'whisk-recipes' ),
		);

		$rewrite = array(
			'slug'         => 'cooking_techniques',
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
			'rest_base'         => 'cooking_techniques',
		);

		register_taxonomy( self::get_default_taxonomy_name(), array( Recipe::get_cpt_name() ), $args );
	}

	/**
	 * Get ingredients for select.
	 *
	 * @return int|\WP_Error|\WP_Term[]
	 */
	public static function get_terms_for_select() {
		$args = array(
			'taxonomy'   => self::get_taxonomy_name(),
			'hide_empty' => false,
			'fields'     => 'id=>name',
		);

		return get_terms( $args );
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
			$name = get_option( '_whisk_cooking_technique_taxonomy_name' );
		}

		return apply_filters( 'whisk_recipes_cooking_technique_taxonomy_name', $name );
	}
}
