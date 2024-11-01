<?php
/**
 * Recipe Meal Type.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Vendor\Carbon_Fields\Container;
use Whisk\Recipes\Vendor\Carbon_Fields\Field;
use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;
use Whisk\Recipes\Utils;

/**
 * Class Meal_Type
 */
class Meal_Type {

	/**
	 * Default terms.
	 *
	 * @var string[]
	 */
	private $defaults = array(
		'Aperitif',
		'Appetizers',
		'Beverages',
		'Breads',
		'Breakfast',
		'Brunch',
		'Cocktails',
		'Condiments and sauces',
		'Desserts',
		'Dinner',
		'Juices',
		'Lunch',
		'Main course',
		'Salads',
		'Side dishes',
		'Smoothies',
		'Snacks',
		'Soups and stews',
	);

	/**
	 * Option name that set to 1 if terms already imported.
	 */
	const INSTALLED = 'whisk_meal_type_installed';

	/**
	 * Option name that set to 1 if term imported from whisk API.
	 */
	const IMPORTED = 'whisk_studio_imported';

	/**
	 * Taxonomy name (slug).
	 */
	const TAXONOMY = 'whisk_meal_type';

	/**
	 * Meal_Type constructor.
	 */
	public function __construct() {
	}

	/**
	 * Setup recipe hooks.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_fields' ) );
		//add_filter( 'manage_edit-' . self::get_taxonomy_name() . '_columns', array( $this, 'add_image_column' ) );
		//add_filter( 'manage_' . self::get_taxonomy_name() . '_custom_column', array( $this, 'fill_image_column' ), 10, 3 );
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
	 * Add image column.
	 *
	 * @param array $defaults Array of defaults.
	 *
	 * @return array
	 */
	public function add_image_column( $defaults ) {
		$columns = array(
			'whisk_featured_image' => __( 'Image', 'whisk-recipes' ),
		);

		return array_slice( $defaults, 0, 1 ) + $columns + $defaults;
	}

	/**
	 * Fill featured image column.
	 *
	 * @param string $string      Column value.
	 * @param string $column_name Column name.
	 * @param int    $term_id     Term ID.
	 *
	 * @return string
	 */
	public function fill_image_column( $string, $column_name, $term_id ) {

		if ( 'whisk_featured_image' === $column_name ) {

			$image_id = whisk_carbon_get_term_meta( $term_id, 'whisk_meal_type_image' );

			if ( $image_id ) {
				return sprintf( '<img width="50" src="%s" />', wp_get_attachment_image_url( $image_id ) );
			}
		}

		return $string;
	}

	/**
	 * Register Post Taxonomy.
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Meal Types', 'Taxonomy General Name', 'whisk-recipes' ),
			'singular_name'              => _x( 'Meal Type', 'Taxonomy Singular Name', 'whisk-recipes' ),
			'menu_name'                  => __( 'Meal Types', 'whisk-recipes' ),
			'all_items'                  => __( 'All Meal Types', 'whisk-recipes' ),
			'parent_item'                => __( 'Parent Meal Type', 'whisk-recipes' ),
			'parent_item_colon'          => __( 'Parent Meal Type:', 'whisk-recipes' ),
			'new_item_name'              => __( 'New Meal Type', 'whisk-recipes' ),
			'add_new_item'               => __( 'Add Meal Type', 'whisk-recipes' ),
			'edit_item'                  => __( 'Edit Meal Type', 'whisk-recipes' ),
			'update_item'                => __( 'Update Meal Type', 'whisk-recipes' ),
			'view_item'                  => __( 'View Meal Type', 'whisk-recipes' ),
			'separate_items_with_commas' => __( 'Separate Meal Types with commas', 'whisk-recipes' ),
			'add_or_remove_items'        => __( 'Add or remove Meal Types', 'whisk-recipes' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'whisk-recipes' ),
			'popular_items'              => __( 'Popular Meal Types', 'whisk-recipes' ),
			'search_items'               => __( 'Search Meal Types', 'whisk-recipes' ),
			'not_found'                  => __( 'Not Found', 'whisk-recipes' ),
			'no_terms'                   => __( 'No Meal Types', 'whisk-recipes' ),
			'items_list'                 => __( 'Meal Types list', 'whisk-recipes' ),
			'items_list_navigation'      => __( 'Meal Types navigation', 'whisk-recipes' ),
		);

		$rewrite = array(
			'slug'         => 'meal-types',
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
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => $rewrite,
			'show_in_rest'      => true,
			'rest_base'         => 'meal-types',
		);

		register_taxonomy( self::get_default_taxonomy_name(), array( Recipe::get_cpt_name() ), $args );
	}

	/**
	 * Add local field group.
	 */
	public function register_fields() {
		Container::make( 'term_meta', __( 'Additional', 'whisk-recipes' ) )
			->where( 'term_taxonomy', '=', self::get_taxonomy_name() )
			->add_fields(
				array(
					Field::make( 'image', 'whisk_meal_type_image', __( 'Image', 'whisk-recipes' ) ),
				)
			);
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
			$name = get_option( '_whisk_meal_type_taxonomy_name' );
		}

		return apply_filters( 'whisk_recipes_meal_type_taxonomy_name', $name );
	}
}
