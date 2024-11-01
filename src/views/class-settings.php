<?php
/**
 * Class Settings
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Views;

use Whisk\Recipes\Models\Avoidance;
use Whisk\Recipes\Models\Diet;
use Whisk\Recipes\Models\Nutrition;
use Whisk\Recipes\Models\Tag;
use Whisk\Recipes\Vendor\Carbon_Fields\Container;
use Whisk\Recipes\Vendor\Carbon_Fields\Field;
use Whisk\Recipes\Models\Cooking_Technique;
use Whisk\Recipes\Models\Cuisine;
use Whisk\Recipes\Models\Meal_Type;
use Whisk\Recipes\Models\Recipe;
use Whisk\Recipes\Utils;
use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;

/**
 * Class Settings
 *
 * @package whisk-recipes
 */
class Settings {

	/**
	 * Settings constructor.
	 */
	public function __construct() {
	}

	/**
	 * Setup hooks.
	 */
	public function setup_hooks() {
		add_action( 'carbon_fields_register_fields', array ( $this, 'register_fields' ) );
		add_action( 'carbon_fields_theme_options_container_saved', array ( Utils::class, 'schedule_flush_rewrite_rules' ) );
		add_action( 'admin_enqueue_scripts', array ( $this, 'enqueue_code_editor' ) );
	}

	/**
	 * Enqueue CodeMirror editor.
	 *
	 * @link https://codemirror.net/doc/manual.html
	 */
	public function enqueue_code_editor() {
		if ( 'whisk_recipe_page_settings' !== get_current_screen()->id ) {
			return;
		}

		$args = array (
			'type'       => 'css',
			'codemirror' => array (
				'indentUnit'  => 4,
				'lineNumbers' => false,
			),
		);

		$settings = wp_enqueue_code_editor( $args );

		if ( false === $settings ) {
			return;
		}

		wp_add_inline_script(
			'code-editor',
			sprintf( 'jQuery( function( $ ) { let $custom_css = $( "[name*=whisk_custom_css]" ); wp.codeEditor.initialize( $custom_css, %s ); } );', wp_json_encode( $settings ) )
		);
	}

	/**
	 * Get default author
	 *
	 * @return mixed
	 */
	public static function get_default_author() {
		return whisk_carbon_get_theme_option( 'whisk_default_author' );
	}

	/**
	 * Get list of users.
	 *
	 * @return array
	 */
	public static function get_authors() {
		$args = array (
			'fields' => array ( 'ID', 'display_name' ),
		);

		return wp_list_pluck( get_users( $args ), 'display_name', 'ID' );
	}

	/**
	 * Register fields.
	 */
	public function register_fields() {
		$settings = Container::make( 'theme_options', __( 'Settings', 'whisk-recipes' ) );

		$settings
			->set_page_parent( 'edit.php?post_type=whisk_recipe' )
			->set_page_file( 'settings' )
			->add_tab(
				__( 'General', 'whisk-recipes' ),
				array (
					Field::make( 'checkbox', 'whisk_semantic_url', __( 'Use Semantic URL structure', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_help_text( __( 'Enable this option if you want recipes to have their own semantic URLs, like <code>site.com/recipes/%recipe-type%/%recipe-title%</code> with custom archive and category URLs. Do not turn this on if you prefer to have recipes inside regular WordPress posts, because it will lead to content duplication and may negatively impact your SEO', 'whisk-recipes' ) ),
					Field::make( 'select', 'whisk_semantic_url_taxonomy', __( 'Taxonomy', 'whisk-recipes' ) )
					     ->add_options(
						     array (
							     'whisk_meal_type'         => __( 'Meal Type', 'whisk-recipes' ),
							     'whisk_avoidance'         => __( 'Avoidance', 'whisk-recipes' ),
							     'whisk_cooking_technique' => __( 'Cooking Technique', 'whisk-recipes' ),
							     'whisk_cuisine'           => __( 'Cuisine', 'whisk-recipes' ),
							     'whisk_diet'              => __( 'Diet', 'whisk-recipes' ),
							     'whisk_nutrition'         => __( 'Nutrition', 'whisk-recipes' ),
						     )
					     )
					     ->set_help_text( __( 'Warning! Changing this setting on a live site with existing recipes may have negative effect on SEO and affect your rankings in search engines', 'whisk-recipes' ) )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_semantic_url',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_archive_on_front_page', __( 'Use Recipes Archive on Front Page', 'whisk-recipes' ) )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_semantic_url',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'text', 'whisk_archive_posts_per_page', __( 'Archive posts per page', 'whisk-recipes' ) )
					     ->set_attribute( 'type', 'number' )
					     ->set_attribute( 'min', 1 )
					     ->set_attribute( 'max', 100 )
					     ->set_width( 30 )
					     ->set_default_value( get_option( 'posts_per_page' ) )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_semantic_url',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_tracking_enabled', __( 'Allow sending analytics data', 'whisk-recipes' ) )
					     ->set_default_value( false ),
					Field::make( 'text', 'whisk_tracking_id', __( 'Tracking ID', 'whisk-recipes' ) )
					     ->set_required( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_tracking_enabled',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_schema_enabled', __( 'Use Microdata', 'whisk-recipes' ) )
					     ->set_default_value( true ),
					Field::make( 'select', 'whisk_default_author', __( 'Default Author', 'whisk-recipes' ) )
					     ->set_options( self::get_authors() ),
				)
			)
			->add_tab(
				__( 'Share List', 'whisk-recipes' ),
				array (
					Field::make( 'checkbox', 'whisk_share_list', __( 'Use share list', 'whisk-recipes' ) )
					     ->set_default_value( true ),
					Field::make( 'checkbox', 'whisk_share_twitter', __( 'Twitter', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_facebook', __( 'Facebook', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_vkontakte', __( 'Vkontakte', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_telegram', __( 'Telegram', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_linkedin', __( 'Linkedin', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_whatsapp', __( 'WhatsApp', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_viber', __( 'Viber', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'checkbox', 'whisk_share_pinterest', __( 'Pinterest', 'whisk-recipes' ) )
					     ->set_default_value( true )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_share_list',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'text', 'whisk_instagram_username', __( 'Instagram username', 'whisk-recipes' ) )
					     ->set_attribute( 'placeholder', '@whiskrecipe' )
					     ->set_default_value( '@whiskrecipe' ),
					Field::make( 'text', 'whisk_instagram_hashtag', __( 'Instagram hashtag', 'whisk-recipes' ) )
					     ->set_attribute( 'placeholder', '#whiskrecipe' )
					     ->set_default_value( '#whiskrecipe' ),
				)
			)
			->add_tab(
				__( 'Custom CSS', 'whisk-recipes' ),
				array (
					Field::make( 'textarea', 'whisk_custom_css', __( 'Custom CSS', 'whisk-recipes' ) )
					     ->set_rows( 10 )
					     ->set_id( 'cf_qqq' ),
				)
			)
			->add_tab(
				__( 'Data Mapping', 'whisk-recipes' ),
				array (
					Field::make( 'checkbox', 'whisk_use_mapping', __( 'Data Mapping', 'whisk-recipes' ) )
					     ->set_help_text( __( 'Data mapping is used to map some of Whisk data into custom-coded recipe solutions. For example, you can have auto-labelling and nutrition information for your existing recipes, even if they are a completely different CPT. This will allow you to store all the data inside the current database structure and add Whisk features without having to rebuild everything from scratch. Developer help might still be needed so do not turn this ON unless you know exactly why you need it.', 'whisk-recipes' ) ),
					Field::make( 'select', 'whisk_recipe_cpt_name', __( 'Recipe CPT', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_post_types' ) )
					     ->set_default_value( Recipe::get_default_cpt_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_meal_type_taxonomy_name', __( 'Meal Type', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Meal_Type::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_avoidance_taxonomy_name', __( 'Avoidance', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Avoidance::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_cuisine_taxonomy_name', __( 'Cuisine', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Cuisine::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_cooking_technique_taxonomy_name', __( 'Technique', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Cooking_Technique::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_tag_taxonomy_name', __( 'Tag', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Tag::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_diet_taxonomy_name', __( 'Diet', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Diet::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_nutrition_taxonomy_name', __( 'Nutrition', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_taxonomies' ) )
					     ->set_default_value( Nutrition::get_default_taxonomy_name() )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
					Field::make( 'select', 'whisk_servings_name', __( 'Servings', 'whisk-recipes' ) )
					     ->set_options( array ( Utils::class, 'get_all_public_meta' ) )
					     ->set_default_value( '_whisk_servings' )
					     ->set_conditional_logic(
						     array (
							     array (
								     'field' => 'whisk_use_mapping',
								     'value' => true,
							     ),
						     )
					     ),
				)
			);

		if ( ! empty( $_COOKIE['showWhiskAPI'] ) ) {

			$settings
				->add_tab(
					__( 'Whisk API', 'whisk-recipes' ),
					array (
						Field::make( 'separator', 'whisk_api_keys_separator', __( 'API key management', 'whisk-recipes' ) ),
						Field::make( 'text', 'whisk_api_token', __( 'Key', 'whisk-recipes' ) )
						     ->set_help_text( 'Copy & paste the Key from <a href="https://studio.whisk.com/api-keys/key-management" target="_blank">Recipe Content Platform API backend</a>' ),
						Field::make( 'text', 'whisk_api_integration_id', __( 'Integration ID', 'whisk-recipes' ) )
						     ->set_help_text( 'This field is created automatically' )
						     ->set_attribute( 'readOnly', true ),
						Field::make( 'separator', 'whisk_api_sync_separator', __( 'Studio sync', 'whisk-recipes' ) ),
						Field::make( 'html', 'whisk_api_sync', __( 'Sync', 'whisk-recipes' ) )
						     ->set_html( '<p><input id="whisk_api_sync" type="button" class="button button-default" value="Sync recipes" /></p>' )
						     ->set_help_text( 'Click <code>Sync Recipes</code> to start adding or updating recipes from your Whisk Studio account. It will happen in background and you can close this page.' ),
					)
				)
				->add_tab(
					__( 'Restricted Grocers', 'whisk-recipes' ),
					array (
						Field::make( 'checkbox', 'whisk_enable_restricted_grocers', __( 'Enable Restricted Grocers List', 'whisk-recipes' ) )
						     ->set_help_text( __( '<p>Restrict some retailers from appearing in the shopping list. If not set, all available retailers are shown. Each retailer should be prefixed with the country code to control which retailer will be available in which country. For example, GB:AmazonFresh, US:Walmart. To see the list of all retailers that we support, read <a href="https://docs.whisk.com/resources/supported-retailers" target="_blank">Integrated Retailers</a>.</p><p>For example:</p>US:Walmart,US:Bakersplus,US:Citymarket,US:Fredmeyer', 'whisk-recipes' ) ),

						Field::make( 'textarea', 'whisk_restricted_grocers', __( 'List of restricted grocers', 'whisk-recipes' ) )
						     ->set_attribute( 'placeholder', 'US:Walmart' )
						     ->set_rows( 10 )
						     ->set_id( 'cf_rg' )
					)
				);


		}
	}

	/**
	 * Get help tab content.
	 *
	 * @return string
	 */
	public function get_help_tab() {

		return sprintf(
			'<iframe frameborder="0" src="%s?url=https://whisk.com/help/wp-recipes-plugin-help/" width="100%%" height="1000" />',
			rest_url( 'whisk/v1/proxy' )
		);


	}
}
