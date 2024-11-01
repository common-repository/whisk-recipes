<?php
/**
 * Recipe Model.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Vendor\Carbon_Fields\Container;
use Whisk\Recipes\Vendor\Carbon_Fields\Container\Post_Meta_Container;
use Whisk\Recipes\Vendor\Carbon_Fields\Field;
use Whisk\Recipes\Utils;
use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;
use WP_Post;
use WP_Term;
use WP_Screen;

/**
 * Class Recipe_Model
 */
class Recipe {
	/**
	 * Custom post type name.
	 */
	const CPT = 'whisk_recipe';

	const IMAGE_SIZE_LARGE     = 'whisk-recipe-large';
	const IMAGE_SIZE_MEDIUM    = 'whisk-recipe-medium';
	const IMAGE_SIZE_THUMBNAIL = 'whisk-recipe-thumbnail';

	/**
	 * Поля для меппинга.
	 *
	 * @var array[string]
	 */
	private $mapper = [];

	/**
	 * Sync instance.
	 *
	 * @var Sync $sync
	 */
	private $sync;

	/**
	 * Recipe_Model constructor.
	 *
	 * @param Sync $sync Sync instance.
	 */
	public function __construct( Sync $sync ) {
		$this->sync = $sync;
	}

	/**
	 * Setup recipe hooks.
	 */
	public function setup_hooks() {
		add_filter( 'hidden_meta_boxes', array( $this, 'default_hidden_meta_boxes' ), 10, 2 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'register_post_type_args', array( $this, 'register_post_type_args' ), 10, 2 );
		add_action( 'carbon_fields_register_fields', array( $this, 'register_fields' ) );
		add_filter( 'single_template', array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
		add_filter( 'home_template', array( $this, 'archive_template' ) );
		add_filter( 'manage_' . self::get_cpt_name() . '_posts_columns', array( $this, 'add_featured_image_column' ), 5 );
		add_action( 'manage_' . self::get_cpt_name() . '_posts_custom_column', array( $this, 'fill_featured_image_column' ), 5, 2 );
		add_filter( 'get_user_option_closedpostboxes_' . self::get_cpt_name(), array( $this, 'collapse_meta_box' ) );
		//add_action( 'edit_post_' . self::get_cpt_name(), array( $this, 'send_message_to_parent' ), 10, 2 );
		//add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_whisk_search_recipes', array( $this, 'ajax_search_recipes' ) );
		//add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'carbon_fields_post_meta_container_saved', array( $this, 'set_categories' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 100, 2 );
		add_action( 'plugins_loaded', array( $this, 'add_image_sizes' ) );
	}

	public function post_submitbox_misc_actions( WP_Post $post ) {
		if ( self::get_cpt_name() !== $post->post_type ) {
			return;
		}

		?>
		<div class="misc-pub-section misc-pub-section--whisk">
			<table>
				<tr>
					<td><em></em></td>
					<td>Studio ID:</td>
					<td><input style="border: 0" size="10" type="text" value="<?php echo esc_attr( $this->sync->get_recipe_id( $post->ID ) ); ?>" /></td>
				</tr>
			</table>
		</div>
		<?php
	}

	public function register_post_type_args( $args, $post_type ) {
		if ( self::get_cpt_name() !== $post_type ) {
			return $args;
		}

		$args['capabilities_'] = array(
			'delete_posts'       => 'do_not_allow',
			'delete_other_posts' => 'do_not_allow',
		);

		do_action('qm/debug', $args);



		return $args;
	}

	/**
	 * Hide recipe meta boxes.
	 *
	 * @param array     $hidden Array of hidden meta boxes.
	 * @param WP_Screen $screen WP_Screen instance.
	 *
	 * @return array
	 */
	public function default_hidden_meta_boxes( $hidden, WP_Screen $screen ) {

		if ( self::get_cpt_name() !== $screen->post_type ) {
			return $hidden;
		}

		if ( ! Helper::get_theme_option( 'whisk_use_mapping' ) ) {
			return $hidden;
		}

		$hidden[] = 'carbon_fields_container_whisk_data';

		return $hidden;
	}

	/**
	 * Get default Recipe custom post type name.
	 *
	 * @return mixed|void
	 */
	public static function get_default_cpt_name() {
		return self::CPT;
	}

	/**
	 * Get Recipe custom post type name.
	 *
	 * @return mixed|void
	 */
	public static function get_cpt_name() {

		$name = self::get_default_cpt_name();

		if ( 'yes' === get_option( '_whisk_use_mapping' ) ) {
			$name = get_option( '_whisk_recipe_cpt_name' );
		}

		return apply_filters( 'whisk_recipes_recipe_cpt_name', $name );
	}

	/**
	 * Get recipe placeholder URL.
	 *
	 * @return string
	 */
	public static function get_placeholder_url() {
		return Utils::get_plugin_file_uri( 'assets/images/recipe-placeholder.svg' );
	}

	/**
	 * Add custom image sizes.
	 */
	public function add_image_sizes() {
		add_image_size( self::IMAGE_SIZE_THUMBNAIL, 300, 300, false );
		add_image_size( self::IMAGE_SIZE_MEDIUM, 600, 600, false );
		add_image_size( self::IMAGE_SIZE_LARGE, 1200, 1200, false );
	}

	/**
	 * Get recipe thumbnail URL.
	 *
	 * @param WP_Post $post WP_Post instance.
	 * @param string  $size Size name.
	 *
	 * @return false|string
	 */
	public static function get_recipe_thumbnail_url( $post = null, $size = self::IMAGE_SIZE_THUMBNAIL ) {
		if ( ! has_post_thumbnail( $post ) ) {
			return self::get_placeholder_url();
		}

		return get_the_post_thumbnail_url( $post, $size );
	}

	/**
	 * Get recipe thumbnail HTML.
	 *
	 * @param WP_Post $post WP_Post instance.
	 * @param string  $size Size name.
	 * @param array   $attr Array of attribute values for the image markup, keyed by attribute name.
	 *
	 * @return string
	 */
	public static function get_recipe_thumbnail( $post = null, $size = self::IMAGE_SIZE_THUMBNAIL, $attr = '' ) {

		if ( ! has_post_thumbnail( $post ) ) {
			return sprintf( '<img src="%s"%s>', self::get_placeholder_url(), Utils::array_to_html_attributes( $attr ) );
		}

		return get_the_post_thumbnail( $post, $size, $attr );
	}

	/**
	 * Filters the permalink for a post of a custom post type.
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function post_type_link( $post_link, WP_Post $post ) {

		if ( self::get_cpt_name() !== $post->post_type || 'publish' !== $post->post_status || ! whisk_carbon_get_theme_option( 'whisk_semantic_url' ) ) {
			return $post_link;
		}

		/**
		 * Terms list.
		 *
		 * @var WP_Term[] $terms
		 */
		$terms = get_the_terms( $post->ID, whisk_carbon_get_theme_option( 'whisk_semantic_url_taxonomy' ) );

		if ( $terms ) {
			return site_url( sprintf( '/recipes/%s/%s/', $terms[0]->slug, $post->post_name ) );
		} else {
			return site_url( sprintf( '/recipes/d/%s/', $post->post_name ) );
		}
	}

	/**
	 * Set recipe categories.
	 *
	 * @param int                 $post_id   Post ID.
	 * @param Post_Meta_Container $container Container object.
	 *
	 * @return mixed
	 */
	public function set_categories( $post_id, Post_Meta_Container $container ) {

		if ( get_post_type( $post_id ) !== self::get_cpt_name() ) {
			return false;
		}

		// Привязать таксономии к посту.
		//if ( 'carbon_fields_container_categories' === $container->get_id() ) {

			foreach ( $container->get_fields() as $field ) {
				if ( 'taxonomy' === $field->get_type() ) {
					wp_set_post_terms( $post_id, $field->get_value(), $field->get_taxonomy() );
				}
			}
		//}
	}

	/**
	 * Admin body class.
	 *
	 * @param string $classes Current classes.
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['whisk_iframe'] ) || ! empty( $_GET['whisk_from_iframe'] ) ) {
			$classes .= ' whisk-in-iframe';
		}

		return $classes;
	}

	/**
	 * Send message to parent.
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    WP_Post instance.
	 *
	 * @link https://wordpress.stackexchange.com/questions/152033/how-to-add-an-admin-notice-upon-post-save-update
	 */
	public function send_message_to_parent( $post_ID, WP_Post $post ) {
		if ( wp_is_post_autosave( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
			return;
		}

		add_filter(
			'redirect_post_location',
			function ( $location ) {
				remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );

				return add_query_arg( array( 'whisk_from_iframe' => 'true' ), $location );
			},
			99
		);
	}

	/**
	 * Admin notices.
	 */
	public function admin_notices() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['whisk_from_iframe'] ) ) {
			?>
			<script>
				var recipe_id = '<?php echo absint( isset( $_GET['post'] ) ? $_GET['post'] : 0 ); ?>';
				window.parent.postMessage(recipe_id, '*');
			</script>
			<?php
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Collapse all meta box.
	 *
	 * @param array $closed Closed meta box array.
	 *
	 * @return array
	 */
	public function collapse_meta_box( $closed ) {

		if ( ! $closed ) {
			$closed = array();
		}

		$to_close = array(
			'carbon_fields_container_general',
			'carbon_fields_container_nutrition_information',
			'carbon_fields_container_times',
			'carbon_fields_container_ingredients',
			'carbon_fields_container_instructions',
			'carbon_fields_container_notes',
			'carbon_fields_container_categories',
			'carbon_fields_container_social_media',
		);

		//if ( Helper::get_theme_option( 'whisk_use_mapping' ) ) {
		//	$to_close[] = 'carbon_fields_container_whisk_data';
		//}

		return array_merge( $closed, $to_close );
	}

	/**
	 * Register Post Types.
	 */
	public function register_post_type() {

		$public   = whisk_carbon_get_theme_option( 'whisk_semantic_url' );
		$taxonomy = whisk_carbon_get_theme_option( 'whisk_semantic_url_taxonomy' );

		$labels = array(
			'name'                     => __( 'Recipes', 'whisk-recipes' ),
			'singular_name'            => __( 'Recipe', 'whisk-recipes' ),
			'menu_name'                => __( 'Whisk Recipes', 'whisk-recipes' ),
			'all_items'                => __( 'All Recipes', 'whisk-recipes' ),
			'add_new'                  => __( 'Add new', 'whisk-recipes' ),
			'add_new_item'             => __( 'Add new Recipe', 'whisk-recipes' ),
			'edit_item'                => __( 'Edit Recipe', 'whisk-recipes' ),
			'new_item'                 => __( 'New Recipe', 'whisk-recipes' ),
			'view_item'                => __( 'View Recipe', 'whisk-recipes' ),
			'view_items'               => __( 'View Recipes', 'whisk-recipes' ),
			'search_items'             => __( 'Search Recipes', 'whisk-recipes' ),
			'not_found'                => __( 'No Recipes found', 'whisk-recipes' ),
			'not_found_in_trash'       => __( 'No Recipes found in trash', 'whisk-recipes' ),
			'parent'                   => __( 'Parent Recipe:', 'whisk-recipes' ),
			'featured_image'           => __( 'Featured image for this Recipe', 'whisk-recipes' ),
			'set_featured_image'       => __( 'Set featured image for this Recipe', 'whisk-recipes' ),
			'remove_featured_image'    => __( 'Remove featured image for this Recipe', 'whisk-recipes' ),
			'use_featured_image'       => __( 'Use as featured image for this Recipe', 'whisk-recipes' ),
			'archives'                 => __( 'Recipe archives', 'whisk-recipes' ),
			'insert_into_item'         => __( 'Insert into Recipe', 'whisk-recipes' ),
			'uploaded_to_this_item'    => __( 'Upload to this Recipe', 'whisk-recipes' ),
			'filter_items_list'        => __( 'Filter Recipes list', 'whisk-recipes' ),
			'items_list_navigation'    => __( 'Recipes list navigation', 'whisk-recipes' ),
			'items_list'               => __( 'Recipes list', 'whisk-recipes' ),
			'attributes'               => __( 'Recipes attributes', 'whisk-recipes' ),
			'name_admin_bar'           => __( 'Recipe', 'whisk-recipes' ),
			'item_published'           => __( 'Recipe published', 'whisk-recipes' ),
			'item_published_privately' => __( 'Recipe published privately.', 'whisk-recipes' ),
			'item_reverted_to_draft'   => __( 'Recipe reverted to draft.', 'whisk-recipes' ),
			'item_scheduled'           => __( 'Recipe scheduled', 'whisk-recipes' ),
			'item_updated'             => __( 'Recipe updated.', 'whisk-recipes' ),
			'parent_item_colon'        => __( 'Parent Recipe:', 'whisk-recipes' ),
		);

		$args = array(
			'label'               => __( 'Recipes', 'whisk-recipes' ),
			'labels'              => $labels,
			'description'         => '',
			'public'              => $public,
			'publicly_queryable'  => $public,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'rest_base'           => 'recipes',
			'has_archive'         => 'recipes',
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'delete_with_user'    => false,
			'exclude_from_search' => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => array(
				'slug' => sprintf( 'recipes/%%%s%%', $taxonomy ),
			),
			'query_var'           => $public,
			'menu_icon'           => Utils::get_plugin_file_uri( 'assets/images/menu-icon.svg' ),

			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'comments',
				'revisions',
				'author',
			),
		);

		if ( $public && $taxonomy ) {
			$args['taxonomies'] = array( $taxonomy );
		}

		register_post_type( self::get_default_cpt_name(), $args );

		//global $wp_roles;
		//do_action('qm/debug', $wp_roles);
		//$wp_roles->add_cap('administrator','delete_posts');
		//$wp_roles->add_cap('administrator','delete_others_posts');
	}

	/**
	 * Add local field group.
	 *
	 * @link https://docs.carbonfields.net/#/fields/text
	 */
	public function register_fields() {
		Container::make( 'post_meta', 'whisk_data', __( 'Recipe Data', 'whisk-recipes' ) )
			->where( 'post_type', '=', self::get_cpt_name() )
			/**
			 * Recipe Header
			 */
			->add_tab(
				__( 'Recipe Header', 'whisk-recipes' ),
				array(
					Field::make( 'separator', 'whisk_general_separator', __( 'General', 'whisk-recipes' ) ),
					Field::make( 'select', 'whisk_recipe_difficulty', __( 'Difficulty', 'whisk-recipes' ) )
						->add_options(
							array(
								0 => __( 'Not set', 'whisk-recipes' ),
								1 => __( 'Simple', 'whisk-recipes' ),
								2 => __( 'Medium', 'whisk-recipes' ),
								3 => __( 'Complicated', 'whisk-recipes' ),
							)
						),
					Field::make( 'text', 'whisk_servings', __( 'Servings', 'whisk-recipes' ) )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', '1' )
						->set_attribute( 'placeholder', 4 )
						->set_default_value( 1 ),
					Field::make( 'text', 'whisk_servings_unit', __( 'Servings Unit', 'whisk-recipes' ) )
						->set_default_value( 'Servings' )
						->set_attribute( 'placeholder', __( 'Servings, people, packages', 'whisk-recipes' ) ),
					Field::make( 'textarea', 'whisk_recipe_excerpt', __( 'Description', 'whisk-recipes' ) )
						->set_attribute( 'placeholder', __( 'A description of the recipe for the header', 'whisk-recipes' ) ),
					Field::make( 'separator', 'whisk_time_separator', __( 'Time', 'whisk-recipes' ) ),
					Field::make( 'html', 'whisk_recipe_times_prep_time' )
						->set_width( 12 )
						->set_html( __( 'Prep Time', 'whisk-recipes' ) ),
					//Field::make( 'text', 'whisk_prep_time_days', '' )
					//	->set_width( 10 )
					//	->set_attribute( 'placeholder', __( 'Days', 'whisk-recipes' ) )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					Field::make( 'text', 'whisk_prep_time_hours', '' )
						->set_width( 10 )
						->set_attribute( 'placeholder', __( 'Hours', 'whisk-recipes' ) )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', 0 )
						->set_attribute( 'step', 0.1 ),
					Field::make( 'text', 'whisk_prep_time_minutes', '' )
						->set_width( 10 )
						->set_attribute( 'placeholder', __( 'Minutes', 'whisk-recipes' ) )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', 0 )
						->set_attribute( 'step', 0.1 ),
					Field::make( 'html', 'whisk_prep_time_html' )
						->set_width( 58 ),
					Field::make( 'html', 'whisk_recipe_times_cook_time' )
						->set_width( 12 )
						->set_html( __( 'Cook Time', 'whisk-recipes' ) ),
					//Field::make( 'text', 'whisk_cook_time_days', '' )
					//	->set_attribute( 'placeholder', __( 'Days', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					Field::make( 'text', 'whisk_cook_time_hours', '' )
						->set_attribute( 'placeholder', __( 'Hours', 'whisk-recipes' ) )
						->set_width( 10 )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', 0 )
						->set_attribute( 'step', 0.1 ),
					Field::make( 'text', 'whisk_cook_time_minutes', '' )
						->set_attribute( 'placeholder', __( 'Minutes', 'whisk-recipes' ) )
						->set_width( 10 )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', 0 )
						->set_attribute( 'step', 0.1 ),
					Field::make( 'html', 'whisk_cook_time_html' )
						->set_width( 58 ),


					Field::make( 'html', 'whisk_recipe_times_total_time' )
						->set_width( 12 )
						->set_html( __( 'Total Time', 'whisk-recipes' ) ),
					Field::make( 'text', 'whisk_total_time_hours', '' )
						->set_attribute( 'placeholder', __( 'Hours', 'whisk-recipes' ) )
						->set_width( 10 )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', 0 )
						->set_attribute( 'step', 0.1 ),
					Field::make( 'text', 'whisk_total_time_minutes', '' )
						->set_attribute( 'placeholder', __( 'Minutes', 'whisk-recipes' ) )
						->set_width( 10 )
						->set_attribute( 'type', 'number' )
						->set_attribute( 'min', 0 )
						->set_attribute( 'step', 0.1 ),
					Field::make( 'html', 'whisk_total_time_html' )
						->set_width( 58 ),
					//Field::make( 'html', 'whisk_recipe_times_resting_time' )
					//	->set_width( 12 )
					//	->set_html( __( 'Resting Time', 'whisk-recipes' ) ),
					//Field::make( 'text', 'whisk_resting_time_days', '' )
					//	->set_attribute( 'placeholder', __( 'Days', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					//Field::make( 'text', 'whisk_resting_time_hours', '' )
					//	->set_attribute( 'placeholder', __( 'Hours', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					//Field::make( 'text', 'whisk_resting_time_minutes', '' )
					//	->set_attribute( 'placeholder', __( 'Minutes', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					//Field::make( 'html', 'whisk_resting_time_html' )
					//	->set_width( 58 ),
					//Field::make( 'html', 'whisk_recipe_times_custom_time' )
					//	->set_width( 12 )
					//	->set_html( __( 'Custom Label', 'whisk-recipes' ) ),
					//Field::make( 'text', 'whisk_custom_time_label', '' )
					//	->set_width( 30 )
					//	->set_attribute( 'placeholder', __( 'Resting Time', 'whisk-recipes' ) ),
					//Field::make( 'html', 'whisk_custom_time_html2' )
					//	->set_width( 58 ),
					//Field::make( 'html', 'whisk_recipe_times_custom_time3' )
					//	->set_width( 12 )
					//	->set_html( __( 'Custom Time', 'whisk-recipes' ) ),
					//Field::make( 'text', 'whisk_custom_time_days', '' )
					//	->set_attribute( 'placeholder', __( 'Days', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					//Field::make( 'text', 'whisk_custom_time_hours', '' )
					//	->set_attribute( 'placeholder', __( 'Hours', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					//Field::make( 'text', 'whisk_custom_time_minutes', '' )
					//	->set_attribute( 'placeholder', __( 'Minutes', 'whisk-recipes' ) )
					//	->set_width( 10 )
					//	->set_attribute( 'type', 'number' )
					//	->set_attribute( 'min', 0 )
					//	->set_attribute( 'step', 0.1 ),
					//Field::make( 'html', 'whisk_custom_time_html' )
					//	->set_width( 58 ),
					Field::make( 'separator', 'whisk_categories_separator', __( 'Categories', 'whisk-recipes' ) ),
					Field::make( 'taxonomy', 'whisk_tags', __( 'Tags', 'whisk-recipes' ) )
						->set_taxonomy( Tag::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
					//Field::make( 'taxonomy', 'whisk_equipments', __( 'Equipment', 'whisk-recipes' ) )
					//	->set_taxonomy( 'whisk_equipment' )
					//	->set_width( 25 )
					//	->set_multiple( true ),
					Field::make( 'taxonomy', 'whisk_diets', __( 'Diets', 'whisk-recipes' ) )
						->set_taxonomy( Diet::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
					Field::make( 'taxonomy', 'whisk_avoidance', __( 'Avoidance', 'whisk-recipes' ) )
						->set_taxonomy( Avoidance::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
					Field::make( 'taxonomy', 'whisk_cuisines', __( 'Cuisines', 'whisk-recipes' ) )
						->set_taxonomy( Cuisine::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
					Field::make( 'taxonomy', 'whisk_meal_types', __( 'Meal Types', 'whisk-recipes' ) )
						->set_taxonomy( Meal_Type::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
					Field::make( 'taxonomy', 'whisk_cooking_techniques', __( 'Cooking Techniques', 'whisk-recipes' ) )
						->set_taxonomy( Cooking_Technique::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
					Field::make( 'taxonomy', 'whisk_nutrition', __( 'Nutrition Labels', 'whisk-recipes' ) )
						->set_taxonomy( Nutrition::get_taxonomy_name() )
						->set_width( 25 )
						->set_multiple( true ),
				)
			)
			/**
			 * Recipe Images.
			 */
			->add_tab(
				__( 'Recipe Media', 'whisk-recipes' ),
				array(
					Field::make( 'separator', 'whisk_images_separator', __( 'Images', 'whisk-recipes' ) ),
					Field::make( 'media_gallery', 'whisk_images', __( 'Media Gallery', 'whisk-recipes' ) ),
					Field::make( 'separator', 'whisk_video_separator', __( 'Video', 'whisk-recipes' ) ),
					Field::make( 'file', 'whisk_video', __( 'File', 'whisk-recipes' ) )
						->set_type( 'video' ),
					Field::make( 'text', 'whisk_video_url', __( 'URL', 'whisk-recipes' ) )
						->set_attribute( 'type', 'url' ),
				)
			)
			/**
			 * Ingredients.
			 */
			->add_tab(
				__( 'Ingredients', 'whisk-recipes' ),
				array(
					Field::make( 'checkbox', 'whisk_simple_ingredients', __( 'Simple Ingredients', 'whisk-recipes' ) )
						->set_option_value( '1' ),
					Field::make( 'html', 'whisk_simple_ingredients_description', __( 'Imported Description', 'whisk-recipes' ) )
						->set_html( sprintf( '<p>%s</p>', __( 'Simple ingredient field is used for imported recipes but you can use it regularly, if you want simplicity. However, we recommend using all ingredient fields like Amount, Units, Name and Notes for better SEO.', 'whisk-recipes' ) ) ),
					Field::make( 'rich_text', 'whisk_simple_ingredients_text', __( 'Ingredients Text', 'whisk-recipes' ) )
						->set_settings(
							array(
								'media_buttons' => false,
								'quicktags'     => false,
								'teeny'         => true,
							)
						)
						->set_conditional_logic(
							array(
								array(
									'field' => 'whisk_simple_ingredients',
									'value' => '1',
								),
							)
						),
					Field::make( 'complex', 'whisk_ingredients', '' )
						->set_collapsed( true )
						->setup_labels(
							array(
								'plural_name'   => 'ingredients',
								'singular_name' => 'ingredient',
							)
						)
						->set_conditional_logic(
							array(
								array(
									'field' => 'whisk_simple_ingredients',
									'value' => '',
								),
							)
						)
						->add_fields(
							array(
								Field::make( 'rich_input', 'whisk_ingredient_amount', __( 'Amount', 'whisk-recipes' ) )
									->set_buttons( array( '¼', '½', '¾', '⅓', '⅛' ) )
									->set_width( 20 )
									->set_attribute( 'placeholder', '' ),
								Field::make( 'text', 'whisk_ingredient_unit', __( 'Unit', 'whisk-recipes' ) )
									->set_width( 10 )
									->set_attribute( 'placeholder', __( 'g', 'whisk-recipes' ) ),
								Field::make( 'taxonomy', 'whisk_ingredient_id', __( 'Name', 'whisk-recipes' ) )
									->set_taxonomy( 'whisk_ingredient' )
									->set_width( 35 ),
								Field::make( 'text', 'whisk_ingredient_note', __( 'Note', 'whisk-recipes' ) )
									->set_width( 35 )
									->set_attribute( 'placeholder', 'extra virgin' ),
							)
						)
						->set_header_template( "<%- whisk_ingredient_id[0] ? whisk_ingredient_id[0].label : 'Empty' %>" ),
					//Field::make( 'text', 'whisk_ingredients_estimated_cost', __( 'Estimated Cost', 'whisk-recipes' ) ),
				)
			)
			/**
			 * Instructions.
			 */
			->add_tab(
				__( 'Instructions', 'whisk-recipes' ),
				array(
					Field::make( 'checkbox', 'whisk_simple_instructions', __( 'Simple Instructions', 'whisk-recipes' ) )
						->set_option_value( '1' ),
					Field::make( 'html', 'whisk_simple_instructions_description', __( 'Imported Description' ) )
						->set_html( sprintf( '<p>%s</p>', __( 'Simple instructions field is used for imported recipes but you can use it regularly, if you want simplicity. However, we recommend using all instructions fields for better SEO.' ) ) ),
					Field::make( 'rich_text', 'whisk_simple_instructions_text', __( 'Instructions Text', 'whisk-recipes' ) )
						->set_settings(
							array(
								'media_buttons' => false,
								'quicktags'     => false,
								'teeny'         => true,
							)
						)
						->set_conditional_logic(
							array(
								array(
									'field' => 'whisk_simple_instructions',
									'value' => '1',
								),
							)
						),
					Field::make( 'complex', 'whisk_instructions', '' )
						->set_conditional_logic(
							array(
								array(
									'field' => 'whisk_simple_instructions',
									'value' => '',
								),

							)
						)
						->set_collapsed( true )
						->setup_labels(
							array(
								'plural_name'   => 'steps',
								'singular_name' => 'step',
							)
						)
						->add_fields(
							'separator',
							__( 'Group Title', 'whisk-recipes' ),
							array(
								Field::make( 'text', 'whisk_step_separator_name', __( 'Group Title', 'whisk-recipes' ) ),
							)
						)
						->set_header_template( 'Group: <%- whisk_step_separator_name %>' )
						->add_fields(
							'step',
							__( 'Step', 'whisk-recipes' ),
							array(
								Field::make( 'text', 'whisk_step_summary', __( 'Step Summary', 'whisk-recipes' ) ),
								Field::make( 'rich_text', 'whisk_step_instruction', __( 'Step Instruction', 'whisk-recipes' ) )
									->set_settings(
										array(
											'media_buttons' => false,
											'quicktags' => false,
											'teeny'     => true,
										)
									),
								//Field::make( 'text', 'whisk_step_video_url', __( 'Step video URL', 'whisk-recipes' ) )
								//	->set_attribute( 'type', 'url' ),
								//Field::make( 'file', 'whisk_step_video', __( 'Step Video', 'whisk-recipes' ) )
								//	->set_width( 50 )
								//	->set_type( 'video' )
								//	->set_help_text( 'Optional. An video for the step.' ),
								Field::make( 'image', 'whisk_step_image', __( 'Step Image', 'whisk-recipes' ) )
									->set_width( 50 )
									->set_help_text( 'Optional. An image for the step.' ),
							)
						)
						->set_header_template( '<%- whisk_step_summary ? whisk_step_summary : whisk_step_instruction.replace(/(<([^>]+)>)/ig, "") %>' ),
				)
			)
			/**
			 * Notes.
			 */
			->add_tab(
				__( 'Tips', 'whisk-recipes' ),
				array(
					Field::make( 'checkbox', 'whisk_simple_notes', __( 'Simple Tips', 'whisk-recipes' ) )
						->set_option_value( '1' ),
					Field::make( 'html', 'whisk_simple_notes_description', __( 'Imported Description' ) )
						->set_html( sprintf( '<p>%s</p>', __( 'Simple tips field is used for imported recipes but you can use it regularly, if you want simplicity. However, we recommend using all instructions fields for better SEO.' ) ) ),
					Field::make( 'rich_text', 'whisk_simple_notes_text', __( 'Tips Text', 'whisk-recipes' ) )
						->set_settings(
							array(
								'media_buttons' => false,
								'quicktags'     => false,
								'teeny'         => true,
							)
						)
						->set_conditional_logic(
							array(
								array(
									'field' => 'whisk_simple_notes',
									'value' => '1',
								),
							)
						),
					Field::make( 'complex', 'whisk_notes', '' )
						->set_conditional_logic(
							array(
								array(
									'field' => 'whisk_simple_notes',
									'value' => '',
								),
							)
						)
						->set_collapsed( true )
						->setup_labels(
							array(
								'plural_name'   => 'notes',
								'singular_name' => 'note',
							)
						)
						->add_fields(
							array(
								Field::make( 'rich_text', 'whisk_note', __( 'Note', 'whisk-recipes' ) )
									->set_settings(
										array(
											'media_buttons' => false,
											'quicktags' => false,
											'teeny'     => true,
										)
									),
							)
						)
						->set_header_template( "<%- whisk_note ? _.truncate(whisk_note.replace(/(<([^>]+)>)/ig, ''), {'length': 30}) : 'Empty' %>" ),
				)
			)
			/**
			 * Sharing Images.
			 */
			//->add_tab(
			//	__( 'Sharing Images', 'whisk-recipes' ),
			//	array(
			//		Field::make( 'separator', 'whisk_pinterest_separator', __( 'Pinterest', 'whisk-recipes' ) ),
			//		Field::make( 'image', 'whisk_pinterest_image', __( 'Image', 'whisk-recipes' ) )->set_help_text( __( 'If empty, post featured image or Open Graph image from YOAST plugin will be used.', 'whisk-recipes' ) ),
			//		Field::make( 'textarea', 'whisk_pinterest_description', __( 'Description', 'whisk-recipes' ) ),
			//		Field::make( 'separator', 'whisk_facebook_separator', __( 'Facebook', 'whisk-recipes' ) ),
			//		Field::make( 'image', 'whisk_facebook_image', __( 'Image', 'whisk-recipes' ) )->set_help_text( __( 'If empty, post featured image or Open Graph image from YOAST plugin will be used.', 'whisk-recipes' ) ),
			//		Field::make( 'separator', 'whisk_twitter_separator', __( 'Twitter', 'whisk-recipes' ) ),
			//		Field::make( 'image', 'whisk_twitter_image', __( 'Image', 'whisk-recipes' ) )->set_help_text( __( 'If empty, post featured image or Open Graph image from YOAST plugin will be used.', 'whisk-recipes' ) ),
			//	)
			//)
			/**
			 * Nutrition Information.
			 */
			->add_tab(
				__( 'Nutrition Information', 'whisk-recipes' ),
				self::get_nutrition_fields()
			);
	}

	/**
	 * Set template for single recipe.
	 *
	 * @param string $template Default template path.
	 *
	 * @return string
	 */
	public function single_template( $template ) {
		if ( is_singular( self::get_default_cpt_name() ) ) {
			$template = WHISK_RECIPES_PATH . '/templates/single-recipe.php';
		}

		return $template;
	}

	/**
	 * Set template for single recipe.
	 *
	 * @param string $template Default template path.
	 *
	 * @return string
	 */
	public function archive_template( $template ) {
		$taxonomies = array(
			'whisk_avoidance',
			'whisk_cooking_technique',
			'whisk_cuisine',
			'whisk_diet',
			'whisk_equipment',
			'whisk_ingredient',
			'whisk_meal_type',
			'whisk_nutrition',
			'whisk_tag',
		);

		if ( is_post_type_archive( self::get_default_cpt_name() ) || is_tax( $taxonomies ) ) {
			$template = WHISK_RECIPES_PATH . '/templates/archive-recipe.php';
		}

		return $template;
	}

	/**
	 * Add featured image column.
	 *
	 * @param array $defaults Array of defaults.
	 *
	 * @return array
	 */
	public function add_featured_image_column( $defaults ) {
		$columns = array(
			'whisk_featured_image' => __( 'Image', 'whisk-recipes' ),
			'whisk_shortcode'      => __( 'Shortcode', 'whisk-recipes' ),
		);

		return array_slice( $defaults, 0, 1 ) + $columns + $defaults;
	}

	/**
	 * Fill featured image column.
	 *
	 * @param string $column_name Column name.
	 * @param int    $id          Post ID.
	 */
	public function fill_featured_image_column( $column_name, $id ) {
		if ( 'whisk_featured_image' === $column_name ) {
			the_post_thumbnail( array( 50, 50 ) );
		}

		if ( 'whisk_shortcode' === $column_name ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<code style="white-space: nowrap">[whisk-recipe id="%d"]</code>', $id );
		}
	}

	/**
	 * Get ingredients for given recipe.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return mixed
	 */
	public function get_ingredients( $recipe_id ) {
		return whisk_carbon_get_post_meta( $recipe_id, 'whisk_ingredients' );
	}

	/**
	 * Get recipe energy.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return mixed
	 */
	public static function get_calories( $recipe_id ) {
		return whisk_carbon_get_post_meta( $recipe_id, 'whisk_enerc_kcal' );
	}

	/**
	 * Get author name for recipe.
	 *
	 * @param int $author_id Author ID.
	 *
	 * @return string
	 */
	public static function get_author_name( $author_id ) {
		return get_the_author_meta( 'display_name', $author_id );
	}

	/**
	 * Get recipe name by recipe_id.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return array|int|string
	 */
	public function get_name( $recipe_id ) {
		return get_post_field( 'post_title', $recipe_id );
	}

	/**
	 * Get recipe date published by recipe_id.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return false|int|string
	 */
	public function get_date_published( $recipe_id ) {
		return get_post_time( 'c', false, $recipe_id );
	}

	/**
	 * Get recipe date modified by recipe_id.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return false|int|string
	 */
	public function get_date_modified( $recipe_id ) {
		return get_post_modified_time( 'c', false, $recipe_id );
	}

	/**
	 * Get cook time in seconds.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return float|int
	 */
	public static function get_cook_time( $recipe_id ) {
		$time = 0;

		$days = whisk_carbon_get_post_meta( $recipe_id, 'whisk_cook_time_days' );

		if ( $days ) {
			$time += $days * DAY_IN_SECONDS;
		}

		$hours = whisk_carbon_get_post_meta( $recipe_id, 'whisk_cook_time_hours' );

		if ( $hours ) {
			$time += $hours * HOUR_IN_SECONDS;
		}

		$minutes = whisk_carbon_get_post_meta( $recipe_id, 'whisk_cook_time_minutes' );

		if ( $minutes ) {
			$time += $minutes * MINUTE_IN_SECONDS;
		}

		return $time;
	}

	/**
	 * Get prep time in seconds.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return float|int
	 */
	public static function get_prep_time( $recipe_id ) {
		$time = 0;

		$days = whisk_carbon_get_post_meta( $recipe_id, 'whisk_prep_time_days' );

		if ( $days ) {
			$time += $days * DAY_IN_SECONDS;
		}

		$hours = whisk_carbon_get_post_meta( $recipe_id, 'whisk_prep_time_hours' );

		if ( $hours ) {
			$time += $hours * HOUR_IN_SECONDS;
		}

		$minutes = whisk_carbon_get_post_meta( $recipe_id, 'whisk_prep_time_minutes' );

		if ( $minutes ) {
			$time += $minutes * MINUTE_IN_SECONDS;
		}

		return $time;
	}

	/**
	 * Get resting time in seconds.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return float|int
	 */
	public static function get_resting_time( $recipe_id ) {
		$time = 0;

		$days = whisk_carbon_get_post_meta( $recipe_id, 'whisk_resting_time_days' );

		if ( $days ) {
			$time += $days * DAY_IN_SECONDS;
		}

		$hours = whisk_carbon_get_post_meta( $recipe_id, 'whisk_resting_time_hours' );

		if ( $hours ) {
			$time += $hours * HOUR_IN_SECONDS;
		}

		$minutes = whisk_carbon_get_post_meta( $recipe_id, 'whisk_resting_time_minutes' );

		if ( $minutes ) {
			$time += $minutes * MINUTE_IN_SECONDS;
		}

		return $time;
	}

	/**
	 * Get total time in seconds.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return int
	 */
	public static function get_total_time( $recipe_id ) {
		return self::get_prep_time( $recipe_id ) + self::get_cook_time( $recipe_id ) + self::get_resting_time( $recipe_id );
	}

	/**
	 * Get recipe tags/keywords.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return bool|false|\WP_Error|\WP_Term[]
	 */
	public function get_keywords( $recipe_id ) {
		$args = array(
			'hide_empty' => false,
			'include'    => whisk_carbon_get_post_meta( $recipe_id, 'whisk_tags' ),
			'fields'     => 'id=>name',
			'taxonomy'   => 'whisk_tag',
		);

		return get_terms( $args );
	}

	/**
	 * Get recipe cuisines.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return bool|false|\WP_Error|\WP_Term[]
	 */
	public function get_recipe_cuisine( $recipe_id ) {
		$args = array(
			'hide_empty' => false,
			'include'    => whisk_carbon_get_post_meta( $recipe_id, 'whisk_cuisines' ),
			'fields'     => 'id=>name',
			'taxonomy'   => 'whisk_cuisine',
		);

		return get_terms( $args );
	}

	/**
	 * Get recipe categories.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return bool|false|\WP_Error|\WP_Term[]
	 */
	public function get_recipe_category( $recipe_id ) {
		$args = array(
			'hide_empty' => false,
			'include'    => whisk_carbon_get_post_meta( $recipe_id, 'whisk_meal_types' ),
			'fields'     => 'id=>name',
			'taxonomy'   => 'whisk_meal_type',
		);

		return get_terms( $args );
	}

	/**
	 * Get recipe yield.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return string
	 */
	public static function get_recipe_yield( $recipe_id ) {
		$servings = whisk_carbon_get_post_meta( $recipe_id, 'whisk_servings' );

		return ( $servings <= 0 ) ? 1 : $servings;
	}

	/**
	 * Get recipe yield.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return string
	 */
	public static function get_recipe_yield_label( $recipe_id ) {
		$label = whisk_carbon_get_post_meta( $recipe_id, 'whisk_servings_unit' );

		return ( empty( $label ) ) ? __( 'Servings', 'whisk-recipes' ) : $label;
	}

	/**
	 * Get recipe instructions.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return array
	 */
	public function get_recipe_instructions( $recipe_id ) {
		return whisk_carbon_get_post_meta( $recipe_id, 'whisk_instructions' );
	}

	/**
	 * Get nutrition units.
	 *
	 * @return string[]
	 */
	public static function get_nutrition_units() {
		return array(
			'NUTRITION_UNIT_G'    => 'g',
			'NUTRITION_UNIT_MG'   => 'mg',
			'NUTRITION_UNIT_MKG'  => 'mkg',
			'NUTRITION_UNIT_KCAL' => 'kcal',
		);
	}

	/**
	 * Get nutrition.
	 *
	 * @return array
	 */
	public static function get_nutrition() {
		return array(
			'whisk_enerc_kcal'     => array(
				'title'     => __( 'Calories', 'whisk-recipes' ),
				'reference' => '2000',
			),
			'whisk_fat_unsat'      => array(
				'title'     => __( 'Unsaturated Fat', 'whisk-recipes' ),
				'reference' => '1',
			),
			'whisk_fat'            => array(
				'title'     => __( 'Fat', 'whisk-recipes' ),
				'reference' => '70',
			),
			'whisk_fasat'          => array(
				'title'     => __( 'Saturated Fat', 'whisk-recipes' ),
				'reference' => '20',
			),
			'whisk_fatrn'          => array(
				'title'     => __( 'Trans Fat', 'whisk-recipes' ),
				'reference' => '1',
			),
			'whisk_fams'           => array(
				'title'     => __( 'Monounsaturated Fat', 'whisk-recipes' ),
				'reference' => '1',
			),
			'whisk_fapu'           => array(
				'title'     => __( 'Polyunsaturated Fat', 'whisk-recipes' ),
				'reference' => '1',
			),
			'whisk_chocdf'         => array(
				'title'     => __( 'Carbs', 'whisk-recipes' ),
				'reference' => '260',
			),
			'whisk_fibtg'          => array(
				'title'     => __( 'Fiber', 'whisk-recipes' ),
				'reference' => '28',
			),
			'whisk_sugar'          => array(
				'title'     => __( 'Sugars', 'whisk-recipes' ),
				'reference' => '90',
			),
			'whisk_procnt'         => array(
				'title'     => __( 'Protein', 'whisk-recipes' ),
				'reference' => '50',
			),
			'whisk_chole'          => array(
				'title'     => __( 'Cholesterol', 'whisk-recipes' ),
				'reference' => '300',
			),
			'whisk_na'             => array(
				'title'     => __( 'Sodium', 'whisk-recipes' ),
				'reference' => '2000',
			),
			'whisk_ca'             => array(
				'title'     => __( 'Calcium', 'whisk-recipes' ),
				'reference' => '800',
			),
			'whisk_mg'             => array(
				'title'     => __( 'Magnesium', 'whisk-recipes' ),
				'reference' => '375',
			),
			'whisk_k'              => array(
				'title'     => __( 'Potassium', 'whisk-recipes' ),
				'reference' => '3500',
			),
			'whisk_fe'             => array(
				'title'     => __( 'Iron', 'whisk-recipes' ),
				'reference' => '14',
			),
			'whisk_zn'             => array(
				'title'     => __( 'Zinc', 'whisk-recipes' ),
				'reference' => '10',
			),
			'whisk_p'              => array(
				'title'     => __( 'Phosphorus', 'whisk-recipes' ),
				'reference' => '700',
			),
			'whisk_vita_rae'       => array(
				'title'     => __( 'Vitamin A', 'whisk-recipes' ),
				'reference' => '800',
			),
			'whisk_thia'           => array(
				'title'     => __( 'Thiamin B1', 'whisk-recipes' ),
				'reference' => '1.1',
			),
			'whisk_ribf'           => array(
				'title'     => __( 'Riboflavin B2', 'whisk-recipes' ),
				'reference' => '1.4',
			),
			'whisk_nia'            => array(
				'title'     => __( 'Niacin B3', 'whisk-recipes' ),
				'reference' => '16',
			),
			'whisk_vitb6a'         => array(
				'title'     => __( 'Vitamin B6', 'whisk-recipes' ),
				'reference' => '1.4',
			),
			'whisk_fol'            => array(
				'title'     => __( 'Folic Acid B9', 'whisk-recipes' ),
				'reference' => '200',
			),
			'whisk_vitb12'         => array(
				'title'     => __( 'Vitamin B12', 'whisk-recipes' ),
				'reference' => '2.5',
			),
			'whisk_vitc'           => array(
				'title'     => __( 'Vitamin C', 'whisk-recipes' ),
				'reference' => '80',
			),
			'whisk_vitd'           => array(
				'title'     => __( 'Vitamin D', 'whisk-recipes' ),
				'reference' => '5',
			),
			'whisk_tocpha'         => array(
				'title'     => __( 'Vitamin E', 'whisk-recipes' ),
				'reference' => '12',
			),
			'whisk_vitk1'          => array(
				'title'     => __( 'Vitamin K', 'whisk-recipes' ),
				'reference' => '75',
			),
			'whisk_glycemic_index' => array(
				'title'     => __( 'Glycemic Index', 'whisk-recipes' ),
				'reference' => '1',
			),
			'whisk_glycemic_load'  => array(
				'title'     => __( 'Glycemic Load', 'whisk-recipes' ),
				'reference' => '1',
			),
			'whisk_health_score'   => array(
				'title'     => __( 'Health Score', 'whisk-recipes' ),
				'reference' => '1',
			),
		);
	}

	/**
	 * Get nutrition fields.
	 *
	 * @return array
	 */
	public static function get_nutrition_fields() {
		$fields = array();

		foreach ( self::get_nutrition() as $key => $data ) {
			$fields[] = Field::make( 'text', $key, $data['title'] )->set_width( 25 );
		}

		return $fields;
	}

	/**
	 * Get recipe nutrition.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return array
	 */
	public static function get_recipe_nutrition( $recipe_id ) {
		$results    = array();
		$black_list = array(
			'whisk_glycemic_index',
			'whisk_glycemic_load',
			'whisk_health_score',
		);

		$fields = self::get_nutrition();

		foreach ( $fields as $nutrition => $data ) {
			if ( in_array( $nutrition, $black_list, true ) ) {
				continue;
			}
			$value     = whisk_carbon_get_post_meta( $recipe_id, $nutrition );
			$influence = get_post_meta( $recipe_id, '_' . $nutrition . '_influence', true );

			if ( ! empty( $value ) ) {
				$results[ $nutrition ] = array(
					'id'        => $nutrition,
					'title'     => $data['title'],
					'value'     => $value,
					'influence' => ltrim( $influence, '-' ),
					'impact'    => ( '-' === substr( $influence, 0, 1 ) ) ? 'negative' : 'positive',
					'daily'     => round( floatval( $value ) * 100 / $data['reference'] ),
				);
			}
		}

		return $results;
	}

	/**
	 * Get health score color.
	 *
	 * @param float $score Score value.
	 *
	 * @return string
	 */
	public static function get_health_score_color( $score ) {

		if ( $score >= 8 ) {
			return 'high';
		}

		if ( $score >= 6 && $score < 8 ) {
			return 'medium';
		}

		if ( $score >= 0 && $score < 6 ) {
			return 'low';
		}
	}

	/**
	 * Get the x latest recipes.
	 *
	 * @param int   $limit   Number of recipes to get, defaults to 10.
	 * @param mixed $display How to display the recipes.
	 *
	 * @since    4.0.0
	 *
	 * @return array
	 */
	public static function get_latest( $limit = 10, $display = 'name' ) {
		$recipes = array();

		$args = array(
			'post_type'      => self::get_cpt_name(),
			'post_status'    => 'any',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => $limit,
			'offset'         => 0,
		);

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts = $query->posts;

			foreach ( $posts as $post ) {
				switch ( $display ) {
					case 'id':
						$text = $post->ID . ' ' . $post->post_title;
						break;
					default:
						$text = $post->post_title;
				}

				$recipes[] = array(
					'id'   => $post->ID,
					'text' => $text,
				);
			}
		}

		return $recipes;
	}

	/**
	 * Search for recipes by keyword.
	 *
	 * @since    1.8.0
	 */
	public static function ajax_search_recipes() {
		if ( check_ajax_referer( 'whisk', 'security', false ) ) {
			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // Input var okay.

			$recipes         = array();
			$recipes_with_id = array();

			$args = array(
				'post_type'      => self::get_cpt_name(),
				'post_status'    => 'any',
				'posts_per_page' => 100,
				's'              => $search,
			);

			$query = new \WP_Query( $args );

			$posts = $query->posts;
			foreach ( $posts as $post ) {
				$recipes[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);

				$recipes_with_id[] = array(
					'id'   => $post->ID,
					'text' => $post->ID . ' - ' . $post->post_title,
				);
			}

			wp_send_json_success(
				array(
					'recipes'         => $recipes,
					'recipes_with_id' => $recipes_with_id,
				)
			);
		}

		wp_die();
	}

	/**
	 * Get glycemic index.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return mixed
	 */
	public static function get_glycemic_index( $recipe_id ) {
		return get_post_meta( $recipe_id, '_whisk_glycemic_index', true );
	}

	/**
	 * Get glycemic load.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return mixed
	 */
	public static function get_glycemic_load( $recipe_id ) {
		return get_post_meta( $recipe_id, '_whisk_glycemic_load', true );
	}

	/**
	 * Get glycemic index label.
	 *
	 * @param int $glycemic_index Glycemic index.
	 *
	 * @return string
	 */
	public static function get_glycemic_index_label( $glycemic_index ) {

		if ( $glycemic_index <= 55 ) {
			$label = __( 'Low', 'whisk-recipes' );
		}

		if ( $glycemic_index >= 56 && $glycemic_index <= 69 ) {
			$label = __( 'Moderate', 'whisk-recipes' );
		}

		if ( $glycemic_index >= 70 ) {
			$label = __( 'High', 'whisk-recipes' );
		}

		return $label;
	}

	/**
	 * Get glycemic load label.
	 *
	 * @param float $glycemic_load Glycemic load.
	 *
	 * @return string
	 */
	public static function get_glycemic_load_label( $glycemic_load ) {
		$glycemic_load = round( $glycemic_load );

		if ( $glycemic_load >= 0 && $glycemic_load <= 10 ) {
			$label = __( 'Low', 'whisk-recipes' );
		}

		if ( $glycemic_load >= 11 && $glycemic_load <= 19 ) {
			$label = __( 'Moderate', 'whisk-recipes' );
		}

		if ( $glycemic_load >= 20 ) {
			$label = __( 'High', 'whisk-recipes' );
		}

		return $label;
	}

	/**
	 * Get health score.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return mixed
	 */
	public static function get_health_score( $recipe_id ) {
		return get_post_meta( $recipe_id, '_whisk_health_score', true );
	}

	/**
	 * Get recipe video by recipe ID.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return string
	 */
	public static function get_recipe_video( $recipe_id ) {
		global $wp_embed;

		$video = whisk_carbon_get_post_meta( $recipe_id, 'whisk_video' );

		if ( $video ) {
			return wp_video_shortcode(
				array(
					'src'    => wp_get_attachment_url( $video ),
					'width'  => 16,
					'height' => 9,
					'class'  => 'whisk-video',
				)
			);
		}

		$url = whisk_carbon_get_post_meta( $recipe_id, 'whisk_video_url' );

		if ( $url ) {
			return $wp_embed->autoembed( $url );
		}

		return '';
	}

	/**
	 * Display recipe video player by recipe ID.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return void
	 */
	public static function the_recipe_video( $recipe_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get_recipe_video( $recipe_id );
	}

	/**
	 * Get recipe terms.
	 *
	 * @param int    $recipe_id Recipe ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return int|\WP_Error|WP_Term[]
	 */
	public static function get_recipe_terms( $recipe_id, $taxonomy ) {
		return get_the_terms( $recipe_id, $taxonomy );
	}

	public static function the_recipe_terms( $recipe_id, $taxonomy ) {
		$terms = self::get_recipe_terms( $recipe_id, $taxonomy );
	}

	/**
	 * Get recipe complexity.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return int
	 */
	public static function get_recipe_complexity( $recipe_id ) {
		return whisk_carbon_get_post_meta( $recipe_id, 'whisk_recipe_difficulty' );
	}

	/**
	 * Get label for recipe complexity
	 *
	 * @param int $complexity Complexity value.
	 *
	 * @return mixed
	 */
	public static function get_recipe_complexity_label( $complexity ) {
		$labels = array(
			0 => __( 'Not set', 'whisk-recipes' ),
			1 => __( 'Simple', 'whisk-recipes' ),
			2 => __( 'Medium', 'whisk-recipes' ),
			3 => __( 'Complicated', 'whisk-recipes' ),
		);

		return $labels[ $complexity ];
	}

	/**
	 * Get recipe excerpt/description by recipe ID.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return string
	 */
	public static function get_recipe_excerpt( $recipe_id ) {
		$excerpt = whisk_carbon_get_post_meta( $recipe_id, 'whisk_recipe_excerpt' );

		if ( empty( $excerpt ) ) {
			$excerpt = get_the_excerpt( $recipe_id );
		}

		return wp_strip_all_tags( $excerpt );
	}
}
