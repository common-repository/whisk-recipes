<?php
/**
 * Recipe Tags.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Utils;
use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;

/**
 * Class Tag
 */
class Tag {

	/**
	 * Taxonomy name (slug).
	 */
	const TAXONOMY = 'whisk_tag';

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
	}

	/**
	 * Register Post Taxonomy.
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Tags', 'Taxonomy General Name', 'whisk-recipes' ),
			'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'whisk-recipes' ),
			'menu_name'                  => __( 'Tags', 'whisk-recipes' ),
			'all_items'                  => __( 'All Tags', 'whisk-recipes' ),
			'parent_item'                => __( 'Parent Tag', 'whisk-recipes' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'whisk-recipes' ),
			'new_item_name'              => __( 'New Tag', 'whisk-recipes' ),
			'add_new_item'               => __( 'Add Tag', 'whisk-recipes' ),
			'edit_item'                  => __( 'Edit Tag', 'whisk-recipes' ),
			'update_item'                => __( 'Update Tag', 'whisk-recipes' ),
			'view_item'                  => __( 'View Tag', 'whisk-recipes' ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'whisk-recipes' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'whisk-recipes' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'whisk-recipes' ),
			'popular_items'              => __( 'Popular Tags', 'whisk-recipes' ),
			'search_items'               => __( 'Search Tags', 'whisk-recipes' ),
			'not_found'                  => __( 'Not Found', 'whisk-recipes' ),
			'no_terms'                   => __( 'No Tags', 'whisk-recipes' ),
			'items_list'                 => __( 'Tags list', 'whisk-recipes' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'whisk-recipes' ),
		);

		$rewrite = array(
			'slug'         => 'tags',
			'with_front'   => true,
			'hierarchical' => true,
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
			'rest_base'         => 'tags',
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
			$name = get_option( '_whisk_tag_taxonomy_name' );
		}

		return apply_filters( 'whisk_recipes_tag_taxonomy_name', $name );
	}
}
