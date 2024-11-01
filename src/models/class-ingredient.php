<?php
/**
 * Recipe Ingredient.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Vendor\Carbon_Fields\Container;
use Whisk\Recipes\Vendor\Carbon_Fields\Field;
use Whisk\Recipes\Utils;

/**
 * Class Ingredient
 */
class Ingredient {
	/**
	 * Taxonomy name (slug).
	 */
	const TAXONOMY = 'whisk_ingredient';

	const IMAGE_SIZE = 'whisk-ingredient';

	/**
	 * Recipe_Model constructor.
	 */
	public function __construct() {
	}

	/**
	 * Setup recipe hooks.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_fields' ) );
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns', array( $this, 'add_image_column' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column', array( $this, 'fill_image_column' ), 10, 3 );
		add_action( 'wp_ajax_get_ingredients_for_ajax', array( $this, 'get_terms_for_ajax' ) );
		add_action( 'plugins_loaded', array( $this, 'add_image_size' ) );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'set_placeholder' ), 10, 3 );
	}

	/**
	 * Filters the attachment image source result.
	 *
	 * @param array|false  $image Array of image data, or boolean false if no image is available.
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size          Requested size of image. Image size name, or array of width.
	 *
	 * @return array
	 */
	public function set_placeholder( $image, $attachment_id, $size ) {
		if ( self::IMAGE_SIZE !== $size ) {
			return $image;
		}

		if ( ! $image ) {
			$image = array(
				Utils::get_plugin_file_uri( 'assets/images/ingredient-placeholder.svg' ),
				80,
				80,
				false,
			);
		}

		return $image;
	}

	/**
	 * Add custom image size for ingredient.
	 */
	public function add_image_size() {
		add_image_size( self::IMAGE_SIZE, 80, 80, true );
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

			$image_id = whisk_carbon_get_term_meta( $term_id, 'whisk_ingredient_image' );

			if ( $image_id ) {
				return sprintf( '<img width="50" src="%s" />', wp_get_attachment_image_url( $image_id, self::IMAGE_SIZE ) );
			}
		}

		return $string;
	}

	/**
	 * Register Post Taxonomy.
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Ingredients', 'Taxonomy General Name', 'whisk-recipes' ),
			'singular_name'              => _x( 'Ingredient', 'Taxonomy Singular Name', 'whisk-recipes' ),
			'menu_name'                  => __( 'Ingredients', 'whisk-recipes' ),
			'all_items'                  => __( 'All Ingredients', 'whisk-recipes' ),
			'parent_item'                => __( 'Parent Ingredient', 'whisk-recipes' ),
			'parent_item_colon'          => __( 'Parent Ingredient:', 'whisk-recipes' ),
			'new_item_name'              => __( 'New Ingredient', 'whisk-recipes' ),
			'add_new_item'               => __( 'Add Ingredient', 'whisk-recipes' ),
			'edit_item'                  => __( 'Edit Ingredient', 'whisk-recipes' ),
			'update_item'                => __( 'Update Ingredient', 'whisk-recipes' ),
			'view_item'                  => __( 'View Ingredient', 'whisk-recipes' ),
			'separate_items_with_commas' => __( 'Separate Ingredients with commas', 'whisk-recipes' ),
			'add_or_remove_items'        => __( 'Add or remove Ingredients', 'whisk-recipes' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'whisk-recipes' ),
			'popular_items'              => __( 'Popular Ingredients', 'whisk-recipes' ),
			'search_items'               => __( 'Search Ingredients', 'whisk-recipes' ),
			'not_found'                  => __( 'Not Found', 'whisk-recipes' ),
			'no_terms'                   => __( 'No Ingredients', 'whisk-recipes' ),
			'items_list'                 => __( 'Ingredients list', 'whisk-recipes' ),
			'items_list_navigation'      => __( 'Ingredients list navigation', 'whisk-recipes' ),
		);

		$rewrite = array(
			'slug'         => 'ingredients',
			'with_front'   => true,
			'hierarchical' => false,
		);

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
			'rest_base'         => 'ingredients',
		);

		register_taxonomy( self::TAXONOMY, array( Recipe::get_cpt_name() ), $args );
	}

	/**
	 * Add local field group.
	 */
	public function register_fields() {
		Container::make( 'term_meta', 'additional_for_ingredient', __( 'Additional', 'whisk-recipes' ) )
			->where( 'term_taxonomy', '=', self::TAXONOMY )
			->add_fields(
				array(
					Field::make( 'image', 'whisk_ingredient_image', __( 'Image', 'whisk-recipes' ) ),
					Field::make( 'textarea', 'whisk_ingredient_nutrition_info', __( 'Nutrition Info', 'whisk-recipes' ) ),
				)
			);
	}

	/**
	 * Get ingredients for select.
	 *
	 * @param string $term Term for search.
	 *
	 * @return int|\WP_Error|\WP_Term[]
	 */
	public static function get_terms_for_select( $term = '' ) {
		$args = array(
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'id=>name',
		);

		if ( $term ) {
			$args['search'] = $term;
		}

		return get_terms( $args );
	}

	/**
	 * Get terms for ajax.
	 */
	public function get_terms_for_ajax() {
		$results = array();

		$term = trim( $_REQUEST['term'] );

		foreach ( self::get_terms_for_select( $term ) as $id => $value ) {
			$results[] = array(
				'id'    => $id,
				'value' => $value,
			);
		}

		$results[] = array(
			'id'    => 'new',
			'value' => __( 'Add new', 'whisk-recipes' ),
		);

		wp_send_json( $results );
	}

	/**
	 * Convert amount.
	 *
	 * @param string $amount
	 *
	 * @return string
	 */
	public static function convert_amount( $amount ) {
		$replacement = array(
			'¼' => 0.25,
			'½' => 0.5,
			'¾' => 0.75,
			'⅓' => 0.33,
			'⅛' => 0.125,
		);

		return strtr( $amount, $replacement );
	}
}
