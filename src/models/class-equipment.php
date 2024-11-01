<?php
/**
 * Recipe Equipment.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Helper\Helper;
use Whisk\Recipes\Utils;

/**
 * Class Equipment
 */
class Equipment {

	/**
	 * Taxonomy name (slug).
	 */
	const TAXONOMY = 'whisk_equipment';

	const IMAGE_SIZE = 'whisk-equipment';

	/**
	 * Equipment constructor.
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
				320,
				320,
				false,
			);
		}

		return $image;
	}

	/**
	 * Add custom image size for ingredient.
	 */
	public function add_image_size() {
		add_image_size( self::IMAGE_SIZE, 320, 320, true );
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

			$image_id = whisk_carbon_get_term_meta( $term_id, 'whisk_equipment_image' );

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
			'name'                       => _x( 'Equipment', 'Taxonomy General Name', 'whisk-recipes' ),
			'singular_name'              => _x( 'Equipment', 'Taxonomy Singular Name', 'whisk-recipes' ),
			'menu_name'                  => __( 'Equipment', 'whisk-recipes' ),
			'all_items'                  => __( 'All Equipment', 'whisk-recipes' ),
			'parent_item'                => __( 'Parent Equipment', 'whisk-recipes' ),
			'parent_item_colon'          => __( 'Parent Equipment:', 'whisk-recipes' ),
			'new_item_name'              => __( 'New Equipment', 'whisk-recipes' ),
			'add_new_item'               => __( 'Add Equipment', 'whisk-recipes' ),
			'edit_item'                  => __( 'Edit Equipment', 'whisk-recipes' ),
			'update_item'                => __( 'Update Equipment', 'whisk-recipes' ),
			'view_item'                  => __( 'View Equipment', 'whisk-recipes' ),
			'separate_items_with_commas' => __( 'Separate Equipment with commas', 'whisk-recipes' ),
			'add_or_remove_items'        => __( 'Add or remove Equipment', 'whisk-recipes' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'whisk-recipes' ),
			'popular_items'              => __( 'Popular Equipment', 'whisk-recipes' ),
			'search_items'               => __( 'Search Equipment', 'whisk-recipes' ),
			'not_found'                  => __( 'Not Found', 'whisk-recipes' ),
			'no_terms'                   => __( 'No Equipment', 'whisk-recipes' ),
			'items_list'                 => __( 'Equipment list', 'whisk-recipes' ),
			'items_list_navigation'      => __( 'Equipment list navigation', 'whisk-recipes' ),
		);

		$rewrite = array(
			'slug'         => 'equipments',
			'with_front'   => true,
			'hierarchical' => false,
		);

		if ( Helper::get_theme_option( 'whisk_semantic_url' ) && self::TAXONOMY === Helper::get_theme_option( 'whisk_semantic_url_taxonomy' ) ) {
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
			'rest_base'         => 'equipments',
		);

		register_taxonomy( self::TAXONOMY, array( Recipe::get_cpt_name() ), $args );
	}

	/**
	 * Add local field group.
	 */
	public function register_fields() {
		Container::make( 'term_meta', __( 'Additional', 'whisk-recipes' ) )
			->where( 'term_taxonomy', '=', 'whisk_equipment' )
			->add_fields(
				array(
					Field::make( 'image', 'whisk_equipment_image', __( 'Image', 'whisk-recipes' ) ),
					Field::make( 'text', 'whisk_equipment_ref_link', __( 'Ref. Link', 'whisk-recipes' ) ),
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
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'id=>name',
		);

		return get_terms( $args );
	}
}
