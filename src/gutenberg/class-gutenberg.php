<?php
/**
 * Gutenberg.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Gutenberg;

use Whisk\Recipes\Models\Recipe;

/**
 * Class Gutenberg
 */
class Gutenberg {

	private $simple_meta_list = [
		'_whisk_servings',
		'_whisk_servings_unit',
		'_whisk_video_url',
		'_whisk_video',
		'_whisk_ingredients_estimated_cost',
		'_whisk_prep_time_days',
		'_whisk_prep_time_hours',
		'_whisk_prep_time_minutes',
		'_whisk_cook_time_days',
		'_whisk_cook_time_hours',
		'_whisk_cook_time_minutes',
		'_whisk_resting_time_days',
		'_whisk_resting_time_hours',
		'_whisk_resting_time_minutes',
	];

	private $categories_list = [
		'whisk_tags',
		'whisk_meal_types',
		'whisk_diets',
		'whisk_cuisines',
		'whisk_cooking_techniques',
		'whisk_nutrition'
	];

	private $repeaters = [
		'whisk_notes',
		'whisk_instructions',
		'whisk_ingredients',
	];

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		add_action( 'init', [ $this, 'block_init' ] );
		add_filter( 'block_categories', [ $this, 'add_category' ] );
		add_action( 'rest_api_init', [ $this, 'register_recipe_meta' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_route' ] );
		add_filter( 'rest_prepare_whisk_recipe', [ $this, 'post_featured_image_json' ], 10, 3 );
	}

	/**
	 * Registers all block assets so that they can be enqueued through the block editor
	 * in the corresponding context.
	 *
	 * @throws \Error Error.
	 */
	public function block_init() {
		$dir = dirname( __FILE__ );

		$script_asset_path = "$dir/build/index.asset.php";
		if ( ! file_exists( $script_asset_path ) ) {
			throw new \Error(
				'You need to run `npm start` or `npm run build` for the "whisk/recipe" block first.'
			);
		}

		$script_asset = require $script_asset_path;
		wp_register_script(
			'whisk-recipe-block-editor',
			WHISK_RECIPES_URL . '/src/gutenberg/build/index.js',
			$script_asset['dependencies'],
			WHISK_RECIPES_VERSION,
			true
		);
		wp_set_script_translations( 'whisk-recipe-block-editor', 'whisk-recipes' );
		wp_localize_script(
			'whisk-recipe-block-editor',
			'whisk_guten',
			[
				'nonce'          => wp_create_nonce( 'whisk' ),
				'latest_recipes' => Recipe::get_latest( 20, 'id' ),
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
			]
		);

		wp_register_style(
			'whisk-recipe-block-editor',
			WHISK_RECIPES_URL . '/src/gutenberg/build/index.css',
			array(),
			WHISK_RECIPES_VERSION
		);

		if ( is_admin() ) {
			wp_enqueue_style(
				'whisk-recipe',
				WHISK_RECIPES_URL . '/assets/css/app.min.css',
				array(),
				WHISK_RECIPES_VERSION
			);
		}

		register_block_type(
			'whisk/recipe',
			array(
				'editor_script'   => 'whisk-recipe-block-editor',
				'editor_style'    => 'whisk-recipe-block-editor',
				'style'           => 'whisk-recipe-block',
				'render_callback' => [ $this, 'render_recipe_block' ],
				'attributes' => [
					'id'      => [
						'type'    => 'number',
						'default' => null,
					],
					'updated' => [
						'type'    => 'string',
						'default' => '',
					]
				],
			)
		);
	}

	/**
	 * Server side render for block.
	 *
	 * @param array $atts Block attributes.
	 *
	 * @return string
	 */
	public function render_recipe_block( $atts ) {
		if ( ! isset( $atts['id'] ) || ! $atts['id'] ) {
			return '';
		}
		$id = $atts['id'];

		return do_shortcode( "[whisk-recipe id={$id}]" );
	}

	/**
	 * Add custom category for Gutenberg blocks.
	 *
	 * @param array $categories Core categories.
	 *
	 * @return array
	 */
	public function add_category( $categories ) {

		return array_merge(
			$categories,
			[
				[
					'slug'  => 'whisk',
					'title' => __( 'Whisk Recipes', 'whisk-recipes' ),
				],
			]
		);
	}

	function register_recipe_meta() {
		// Simple meta fields.
		foreach ( $this->simple_meta_list as $field ) {
			register_rest_field(
				'whisk_recipe',
				$field,
				array(
					'get_callback'    => function ( $object, $field_name, $request ) {
						return get_post_meta( $object['id'], $field_name, true );
					},
					'update_callback' => function ( $value, $object, $field_name ) {
						if ( ! $value || ! is_string( $value ) ) {
							return;
						}

						return update_post_meta( $object->ID, $field_name, wp_strip_all_tags( $value ) );
					},
					'schema'          => null,
				)
			);
		}

		// Carbon taxonomy fields.
		foreach ( $this->categories_list as $field ) {
			register_rest_field(
				'whisk_recipe',
				$field,
				array(
					'get_callback'    => function ( $object, $field_name, $request ) {
						$field_array = whisk_carbon_get_post_meta( $object['id'], $field_name );
						$value_array = [];
						if ( $field_array ) {
							foreach ( $field_array as $term_id ) {
								$term = get_term( $term_id );
								if ( ! is_wp_error( $term ) ) {
									$value_array[] = [
										'value' => $term->term_id,
										'label' => $term->name,
									];
								}
							}
						}

						return $value_array;
					},
					'update_callback' => function ( $value, $object, $field_name ) {
						if ( ! is_array( $value ) ) {
							return null;
						}
						$value = array_map(
							function( $el ) {
								return (int) $el;
							},
							$value
						);
						return whisk_carbon_set_post_meta( $object->ID, $field_name, $value );
					},
					'schema'          => null,
				)
			);
		}

		// Carbon repeater fields.
		foreach ( $this->repeaters as $field ) {
			register_rest_field(
				'whisk_recipe',
				$field,
				array(
					'get_callback'    => function ( $object, $field_name, $request ) {
						$field_array = whisk_carbon_get_post_meta( $object['id'], $field_name );
						// Fetch image url for instructions.
						if ( 'whisk_instructions' === $field_name ) {
							foreach ( $field_array as $index => $item ) {
								if ( isset( $item['whisk_step_image'] ) ) {
									$image_id = $item['whisk_step_image'];
									$image_url = wp_get_attachment_image_src( $image_id, 'original' );
									if ( $image_url ) {
										$field_array[ $index ]['whisk_step_image_url'] = $image_url[0];
									}
								}
							}
						}
						// Fetch ingredients names for ingredients.
						if ( 'whisk_ingredients' === $field_name ) {
							foreach ( $field_array as $index => $item ) {
								$value_array = [];
								if ( isset( $item['whisk_ingredient_id'] ) ) {
									$term_id = $item['whisk_ingredient_id'][0];
									$term = get_term( $term_id );
									if ( ! is_wp_error( $term ) ) {
										$value_array[] = [
											'value' => $term->term_id,
											'label' => $term->name,
										];
										$field_array[ $index ]['whisk_ingredient_id'] = $value_array;
									}
								}
							}
						}

						return $field_array;
					},
					'update_callback' => function ( $value, $object, $field_name ) {
						if ( ! is_array( $value ) ) {
							return null;
						}
						return whisk_carbon_set_post_meta( $object->ID, $field_name, $value );
					},
					'schema'          => null,
				)
			);
		}

	}

	public function register_rest_route() {
		register_rest_route(
			'whisk/v1',
			'/whisk_instructions/',
			array(
				'methods'             => 'POST',
				'callback'            => function ( \WP_REST_Request $request ) {
					$idx      = $request->get_param( 'idx' );
					$group = $request->get_param( 'group' );
					$subfield = $request->get_param( 'subfield' );
					$value    = $request->get_param( 'value' );
					$id       = $request->get_param( 'id' );

					whisk_carbon_set_post_meta( $id, "whisk_instructions[{$idx}]:{$group}/{$subfield}", $value );

					return new \WP_REST_Response( true, 200 );
				},
				'permission_callback' => 'is_user_logged_in',
				'args' => array(
					'id'       => array( // recipe post id.
						'type'     => 'integer',
						'required' => true,
					),
					'idx'      => array( // repeater item position.
						'type'     => 'integer',
						'required' => true,
					),
					'group'    => array( // repeater group name.
						'type'     => 'string',
						'required' => true,
					),
					'subfield' => array( // repeater subfield name.
						'type'     => 'string',
						'required' => true,
					),
					'value'    => array( // repeater subfield name.
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);
	}

	public function post_featured_image_json( $data, $post, $context ) {
		// Adding featured image url.
		$featured_image_id  = $data->data['featured_media'];
		$featured_image_url = wp_get_attachment_image_src( $featured_image_id, 'original' );

		if ( $featured_image_url ) {
			$data->data['featured_image_url'] = $featured_image_url[0];
		} else {
			$data->data['featured_image_url'] = '';
		}

		// Adding video url.
		$recipe_video_id               = get_post_meta( $post->ID, '_whisk_video', true );
		$recipe_video_url              = $recipe_video_id ? wp_get_attachment_url( $recipe_video_id ) : '';
		$data->data['whisk_video_url'] = $recipe_video_url;

		return $data;
	}
}


