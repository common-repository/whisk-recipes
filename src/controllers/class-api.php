<?php
/**
 * Class API.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Controllers;

use Whisk\Recipes\Models\Cooking_Technique;
use Whisk\Recipes\Models\Cuisine;
use Whisk\Recipes\Models\Ingredient;
use Whisk\Recipes\Models\Meal_Type;
use Whisk\Recipes\Models\Recipe;
use Whisk\Recipes\Models\Sync;
use Whisk\Recipes\Utils;
use Whisk\Recipes\Views\Settings;
use WP_Post;

/**
 * Class API
 */
class API {

	/**
	 * Token Exchange Endpoint.
	 */
	const TOKEN = 'https://login.whisk.com/oauth/v2/token';

	/**
	 * Cron name.
	 */
	const UPDATE_NUTRITION_EVENT = 'whisk_update_nutrition';

	/**
	 * Cron name.
	 */
	const IMPORT_RECIPES_EVENT = 'whisk_import_recipes';
	const IMPORT_RECIPES_SINGLE_EVENT = 'whisk_import_recipes_single';
	const CREATE_INTEGRATION_ID_EVENT = 'whisk_create_integration_id';

	/**
	 * API base URL.
	 */
	const BASE = 'https://api.studio.whisk.com';

	/**
	 * Recipe statuses list.
	 *
	 * @var string[] $recipe_statuses
	 */
	protected $recipe_statuses = array(
		'RECIPE_PUBLISHED_STATUS_DRAFT'     => 'draft',
		'RECIPE_PUBLISHED_STATUS_PUBLISHED' => 'publish',
	);

	/**
	 * API Token.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * API Integration ID.
	 *
	 * @var string
	 */
	protected $integration_id;

	/**
	 * Client ID.
	 *
	 * @var string
	 */
	protected $client_id;

	/**
	 * Secret.
	 *
	 * @var string
	 */
	protected $secret;

	/**
	 * Recipe instance.
	 *
	 * @var Recipe $recipe
	 */
	private $recipe;

	/**
	 * Sync instance.
	 *
	 * @var Sync $sync
	 */
	private $sync;

	/**
	 * Integration type.
	 *
	 * @var string $integration_type
	 */
	private $integration_type = 'INTEGRATION_TYPE_WORDPRESS';

	/**
	 * API constructor.
	 *
	 * @param Recipe $recipe Recipe instance.
	 * @param Sync   $sync Sync instance.
	 */
	public function __construct( Recipe $recipe, Sync $sync) {
		$this->recipe         = $recipe;
		$this->sync           = $sync;
		$this->token          = get_option( '_whisk_api_token' );
		$this->integration_id = get_option( '_whisk_api_integration_id' );
		$this->client_id      = get_option( '_whisk_api_client_id' );
		$this->secret         = get_option( '_whisk_api_secret' );

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
	}

	/**
	 * Hooks init.
	 */
	public function setup_hooks() {
		add_action( 'after_delete_post', array( $this, 'maybe_delete_sync' ), 10, 2 );
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		add_action( 'init', array( $this, 'schedule_event' ) );
		add_action( self::UPDATE_NUTRITION_EVENT, array( $this, 'update_nutrition' ) );

		add_action( self::IMPORT_RECIPES_EVENT, array( $this, 'import_recipe' ) );
		add_action( self::IMPORT_RECIPES_SINGLE_EVENT, array( $this, 'import_recipe' ) );
		add_action( self::CREATE_INTEGRATION_ID_EVENT, array( $this, 'maybe_create_integration_id' ) );

		//add_action( 'save_post', array( $this, 'on_recipe_save' ), 10, 3 );
		add_action( 'wp_ajax_whisk_import_recipes', array( $this, 'import_recipes_trigger' ) );

		if ( ! is_admin() && ! empty( $_GET['zalupa'] ) ) {
			add_action(
				'wp_loaded',
				function () {
					//$this->update_nutrition( 17252 ); // remote.
					//$this->update_nutrition(5); // local.
					$this->import_recipe();
					//$this->maybe_create_integration_id();
					die;
				}
			);
		}
	}

	/**
	 * Get integration type.
	 *
	 * @return string
	 */
	public function get_integration_type() {
		return $this->integration_type;
	}

	public function import_recipes_trigger() {
		if ( ! as_next_scheduled_action( self::IMPORT_RECIPES_SINGLE_EVENT, array(), Utils::get_plugin_prefix() ) ) {
			as_schedule_single_action( time(), self::IMPORT_RECIPES_SINGLE_EVENT, array(), Utils::get_plugin_prefix() );

			wp_send_json_success( 'yes' );
		}

		wp_send_json_error( 'no' );
	}

	/**
	 * Create empty recipe.
	 *
	 * @param string $post_title Post title.
	 *
	 * @return int|\WP_Error
	 */
	public function maybe_create_post( $post_title ) {
		return wp_insert_post(
			array(
				'post_title'  => wp_strip_all_tags( $post_title ),
				'post_status' => 'publish',
				'post_type'   => Recipe::get_cpt_name(),
				'post_author' => Settings::get_default_author(),
			)
		);
	}

	/**
	 * Maybe delete sync when recipe was deleted.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post WP_Post instance.
	 */
	public function maybe_delete_sync( $post_id, WP_Post $post ) {
		if ( Recipe::get_cpt_name() !== $post->post_type ) {
			return;
		}

		$this->sync->delete_sync( $post_id );
	}

	/**
	 * Maybe create Integration ID.
	 */
	public function maybe_create_integration_id() {
		if ( $this->get_integration_id() ) {
			return;
		}

		// Integration exist?
		$response = $this->get_api_request( '/integrations/v1/get' );

		if ( isset( $response['integrations'] ) && is_array( $response['integrations'] ) ) {
			foreach ( $response['integrations'] as $integration ) {
				if ( $this->get_integration_type() === $integration['type'] && 'INTEGRATION_STATUS_ENABLED' === $integration['status'] ) {
					$this->update_integration_id( $integration['id'] );

					return;
				}
			}
		}

		// Create integration.
		$args = array(
			'body' => array(
				'type' => $this->get_integration_type(),
			),
		);

		$response = $this->get_api_request( '/integrations/v1/add', 'put', $args );

		if ( isset( $response['integrations'] ) && is_array( $response['integrations'] ) ) {
			$this->update_integration_id( $response['integrations'][0]['id'] );
			return;
		}
	}

	/**
	 * Get or create term in given taxonomy.
	 *
	 * @param string $name     Term name.
	 * @param string $taxonomy Taxonomy name.
	 * @param array  $args     Arguments.
	 *
	 * @return int
	 */
	public function maybe_create_term( $name, $taxonomy, $args ) {

		$term = get_term_by( 'slug', $args['slug'], $taxonomy, ARRAY_A );

		if ( $term ) {
			return $term['term_id'];
		}

		// Create term.
		$term = wp_insert_term( $name, $taxonomy, $args );

		if ( ! is_wp_error( $term ) ) {
			return $term['term_id'];
		}

		return 0;
	}

	/**
	 * Import recipe from studio via cron
	 */
	public function import_recipe() {

		global $wpdb;

		if ( ! $this->validate_credential() ) {
			return;
		}

		$paging_after = '';

		$j = 0;

		$ingredients_cache = array();

		do {

			$args = array();

			$args['body'] = array(
				'paging.after'   => $paging_after,
				'integration_id' => $this->get_integration_id(),
				'paging.limit'   => 50, // 50 by default.
				'fields'         => array(
					'EXTRA_RECIPE_FIELD_NORMALIZED_INGREDIENTS',
					'EXTRA_RECIPE_FIELD_INSTRUCTIONS',
					'EXTRA_RECIPE_FIELD_NUTRITION',
					'EXTRA_RECIPE_FIELD_AUTO_LABELS',
				),
			);

			$response = $this->get_api_request( '/recipes/v1/get/batch', 'get', $args );

			$i = 1;

			if ( isset( $response['recipes'] ) && is_array( $response['recipes'] ) ) {
				foreach ( $response['recipes'] as $data ) {

					if ( isset( $data['deleted_recipe'] ) ) {
						$recipe    = $data['deleted_recipe'];
						$recipe_id = $recipe['id'];

						$sync_row = $this->sync->get_sync( $recipe_id );

						if ( ! $sync_row ) {
							continue;
						}

						$this->sync->disable_sync( $sync_row['post_id'], $recipe_id );
					}

					// Бывает попадается ключ `deleted_recipe`.
					if ( ! isset( $data['recipe'] ) ) {
						continue;
					}

					$post_id        = 0;
					$recipe         = $data['recipe'];
					$recipe_id      = $recipe['id'];
					$updated_at     = (int) $recipe['updated_at_time'];
					$source_url     = isset( $recipe['source']['url'] ) ? $recipe['source']['url'] : '';
					$source_domain  = wp_parse_url( $source_url, PHP_URL_HOST );
					$current_domain = wp_parse_url( home_url(), PHP_URL_HOST );
					$source_url     = str_replace( $source_domain, $current_domain, $source_url );

					// Ищем пост в таблице синхронизации.
					$sync_row = $this->sync->get_sync( $recipe_id );

					if ( $sync_row ) {
						$post_id = $sync_row['post_id'];

						// Рецепт на сйте обновлять не нужно, если его не ьрогали в студии.
						if ( $updated_at <= (int) $sync_row['sync_date'] ) {
							continue;
						}

						$this->sync->update_sync( $post_id, 0, 'running' );
					}

					// Ищем пост по урлу источника.
					if ( ! $post_id && $source_url ) {
						$_post_id = url_to_postid( $source_url );

						if ( $_post_id ) {
							$post_id = $_post_id;
							$this->sync->create_sync( $post_id, $recipe_id, 0, 'running' );
						}
					}

					// Пытаемся создать пост.
					if ( ! $post_id ) {
						$_post_id = $this->maybe_create_post( $recipe['name'] );

						if ( $_post_id ) {
							$post_id = $_post_id;
							$this->sync->create_sync( $post_id, $recipe_id, 0, 'running' );
						}
					}

					/**
					 * Meal type.
					 */
					if ( isset( $recipe['labels']['meal_type'] ) && is_array( $recipe['labels']['meal_type'] ) ) {
						$meal_types = array();

						foreach ( $recipe['labels']['meal_type'] as $meal_type ) {
							$meal_types[] = $this->maybe_create_term( $meal_type['display_name'], Meal_Type::get_default_taxonomy_name(), array( 'slug' => $meal_type['name'] ) );
						}

						wp_set_object_terms( $post_id, $meal_types, Meal_Type::get_default_taxonomy_name() );
						whisk_carbon_set_post_meta( $post_id, 'whisk_meal_types', $meal_types );
					}

					/**
					 * Cuisines.
					 */
					if ( isset( $recipe['labels']['cuisine'] ) && is_array( $recipe['labels']['cuisine'] ) ) {
						$cuisines = array();

						foreach ( $recipe['labels']['cuisine'] as $cuisine ) {
							$cuisines[] = $this->maybe_create_term( $cuisine['display_name'], Cuisine::get_default_taxonomy_name(), array( 'slug' => $cuisine['name'] ) );
						}

						wp_set_object_terms( $post_id, $cuisines, Cuisine::get_default_taxonomy_name() );
						whisk_carbon_set_post_meta( $post_id, 'whisk_cuisines', $cuisines );
					}

					/**
					 * Cuisines.
					 */
					if ( isset( $recipe['labels']['technique'] ) && is_array( $recipe['labels']['technique'] ) ) {
						$techniques = array();

						foreach ( $recipe['labels']['technique'] as $technique ) {
							$techniques[] = $this->maybe_create_term( $technique['display_name'], Cooking_Technique::get_default_taxonomy_name(), array( 'slug' => $technique['name'] ) );
						}

						wp_set_object_terms( $post_id, $techniques, Cooking_Technique::get_default_taxonomy_name() );
						whisk_carbon_set_post_meta( $post_id, 'whisk_cooking_techniques', $techniques );
					}

					/**
					 * Description/content.
					 */
					if ( isset( $recipe['description'] ) ) {
						wp_update_post(
							array(
								'ID'           => $post_id,
								'post_content' => $recipe['description'],
							)
						);
					}

					/**
					 * Status.
					 */
					if ( isset( $recipe['published_status'] ) && array_key_exists( $recipe['published_status'], $this->recipe_statuses ) ) {

						$status = $this->recipe_statuses[ $recipe['published_status'] ];

						wp_update_post(
							array(
								'ID'          => $post_id,
								'post_status' => $status,
							)
						);

						if ( 'publish' !== $status ) {
							$this->sync->update_sync( $post_id, $updated_at, 'completed' );
							continue;
						}
					}

					/**
					 * Servings.
					 */
					$servings = 1;

					if ( isset( $recipe['servings'] ) ) {
						$servings = (int) $recipe['servings'];
						whisk_carbon_set_post_meta( $post_id, 'whisk_servings', $servings );
					}

					/**
					 * Times.
					 */
					if ( isset( $recipe['durations']['prep_time'] ) ) {
						list( $hours, $minutes ) = Utils::convert_minutes_to_hours_and_minutes( $recipe['durations']['prep_time'] );
						whisk_carbon_set_post_meta( $post_id, 'whisk_prep_time_hours', $hours );
						whisk_carbon_set_post_meta( $post_id, 'whisk_prep_time_minutes', $minutes );
					}

					if ( isset( $recipe['durations']['cook_time'] ) ) {
						list( $hours, $minutes ) = Utils::convert_minutes_to_hours_and_minutes( $recipe['durations']['cook_time'] );
						whisk_carbon_set_post_meta( $post_id, 'whisk_cook_time_hours', $hours );
						whisk_carbon_set_post_meta( $post_id, 'whisk_cook_time_minutes', $minutes );
					}

					if ( isset( $recipe['durations']['total_time'] ) ) {
						list( $hours, $minutes ) = Utils::convert_minutes_to_hours_and_minutes( $recipe['durations']['total_time'] );
						whisk_carbon_set_post_meta( $post_id, 'whisk_total_time_hours', $hours );
						whisk_carbon_set_post_meta( $post_id, 'whisk_total_time_minutes', $minutes );
					}

					/**
					 * Images.
					 */
					if ( isset( $recipe['images'] ) && is_array( $recipe['images'] ) ) {
						$images = array();

						foreach ( $recipe['images'] as $image ) {
							$media_id = Utils::get_media_by( 'guid', $image['url'] );

							if ( ! $media_id ) {
								$media_id = Utils::media_sideload( $image['url'] );
							}

							$images[] = $media_id;
						}

						$images = array_filter( $images );
						$images = array_unique( $images );

						if ( count( $images ) ) {
							set_post_thumbnail( $post_id, $images[0] );
							whisk_carbon_set_post_meta( $post_id, 'whisk_images', $images );
						}
					}

					/**
					 * Videos.
					 */
					if ( isset( $recipe['videos'] ) && is_array( $recipe['videos'] ) && isset( $recipe['videos'][0]['embed_url'] ) ) {
						whisk_carbon_set_post_meta( $post_id, 'whisk_video_url', $recipe['videos'][0]['embed_url'] );
					}

					/**
					 * Ingredients.
					 */
					if ( isset( $recipe['normalized_ingredients'] ) && is_array( $recipe['normalized_ingredients'] ) ) {

						$ingredients = array();

						foreach ( $recipe['normalized_ingredients'] as $ingredient ) {
							if ( ! isset( $ingredient['analysis'] ) && ! isset( $ingredient['linked_product'] ) ) {
								continue;
							}

							// Simple ingredient.
							if ( isset( $ingredient['analysis'][0] ) ) {
								$analysis        = $ingredient['analysis'][0];
								$ingredient_name = strtolower( $analysis['product']['original_name'] );
								$image_url       = $analysis['image_url'];
								$quantity        = $analysis['quantity'];
								$unit            = $analysis['unit'];
								$comment         = $analysis['comment'];
							} else {
								// Linked custom product.
								$source_text     = $ingredient['source_text'];
								$linked_product  = $ingredient['linked_product'];
								$ingredient_name = $linked_product['name'];
								$image_url       = $linked_product['images'][0]['url'];

								list( $_tmp, $comment ) = explode( ',', $source_text );

								$quantity = '';
								$unit     = '';
								$comment  = trim( $comment );

								if ( $_tmp ) {
									list( $quantity, $unit, $foo ) = explode( ' ', $_tmp );
								}
							}

							if ( isset( $ingredients_cache[ $ingredient_name ] ) ) {
								$term = $ingredients_cache[ $ingredient_name ];
							} else {

								$term = get_term_by( 'name', $ingredient_name, Ingredient::TAXONOMY, ARRAY_A );

								if ( ! $term ) {
									$term = wp_insert_term( $ingredient_name, Ingredient::TAXONOMY );
								}

								$ingredients_cache[ $ingredient_name ] = $term;
							}

							// Если не привязана картинка - привяжем.
							if ( ! whisk_carbon_get_term_meta( $term['term_id'], 'whisk_ingredient_image' ) ) {

								$media_id = Utils::get_media_by( 'guid', $image_url );

								if ( ! $media_id ) {
									$media_id = Utils::media_sideload( $image_url, $ingredient_name );
								}

								whisk_carbon_set_term_meta( $term['term_id'], 'whisk_ingredient_image', $media_id );
							}

							$ingredients[] = array(
								'whisk_ingredient_amount' => $quantity,
								'whisk_ingredient_unit'   => $unit,
								'whisk_ingredient_id'     => $term['term_id'],
								'whisk_ingredient_note'   => isset( $comment )
									? $comment
									: '',
							);
						}

						whisk_carbon_set_post_meta( $post_id, 'whisk_ingredients', $ingredients );
					}

					/**
					 * Health Score.
					 */
					if ( isset( $recipe['nutrition']['health_score'] ) ) {
						whisk_carbon_set_post_meta( $post_id, 'whisk_health_score', $recipe['nutrition']['health_score']['value'] );
					}

					/**
					 * Glycemic index.
					 */
					if ( isset( $recipe['nutrition']['glycemic_index'] ) ) {
						whisk_carbon_set_post_meta( $post_id, 'whisk_glycemic_index', $recipe['nutrition']['glycemic_index']['value'] );
					}

					/**
					 * Glycemic load.
					 */
					if ( isset( $recipe['nutrition']['glycemic_load'] ) ) {
						whisk_carbon_set_post_meta( $post_id, 'whisk_glycemic_load', $recipe['nutrition']['glycemic_load']['value'] );
					}

					/**
					 * Nutrition.
					 */
					if ( isset( $recipe['nutrition']['total'] ) ) {
						// Get units list.
						$units = Recipe::get_nutrition_units();

						foreach ( $recipe['nutrition']['total'] as $total ) {
							// Преобразуем NUTRITION_CODE_ENERC_KCAL в enerc_kcal для совместимости с API b2b.
							$code = strtolower( str_replace( 'NUTRITION_CODE_', '', $total['code'] ) );
							// TODO: делим временно значение на количество порций,
							// TODO: так как из API отдаются результаты не для одной порции.
							$value = isset( $total['value'] ) ? $total['value'] / $servings : 0;
							whisk_carbon_set_post_meta( $post_id, 'whisk_' . $code, round( $value, 2 ) . $units[ $total['unit'] ] );
						}
					}

					/**
					 * Nutrients Influence.
					 */
					if ( isset( $recipe['nutrition']['health_score']['nutrients_influence'] ) ) {
						foreach ( $recipe['nutrition']['health_score']['nutrients_influence'] as $nutrients ) {
							// Преобразуем NUTRITION_CODE_ENERC_KCAL в enerc_kcal для совместимости с API b2b.
							$code = strtolower( str_replace( 'NUTRITION_CODE_', '', $nutrients['code'] ) );
							update_post_meta( $post_id, '_whisk_' . $code . '_influence', round( $nutrients['influence'], 2 ) );
						}
					}

					/**
					 * Instructions.
					 */
					if ( isset( $recipe['instructions']['steps'] ) ) {
						$groups = array();
						$steps  = array();

						foreach ( $recipe['instructions']['steps'] as $i => $instruction ) {

							if ( isset( $instruction['group'] ) && ! in_array( $instruction['group'], $groups, true ) ) {

								$steps[] = array(
									'_type' => 'separator',
									'whisk_step_separator_name' => $instruction['group'],
								);

								$groups[] = $instruction['group'];
							}

							$media_id = 0;

							if ( isset( $instruction['images'][0]['url'] ) ) {

								$media_id = Utils::get_media_by( 'guid', $instruction['images'][0]['url'] );

								if ( ! $media_id ) {
									$media_id = Utils::media_sideload( $instruction['images'][0]['url'] );
								}
							}

							$steps[] = array(
								'_type'                  => 'step',
								'whisk_step_summary'     => 'Step ' . ( $i + 1 ),
								'whisk_step_instruction' => $instruction['text'],
								'whisk_step_image'       => $media_id,
							);
						}

						whisk_carbon_set_post_meta( $post_id, 'whisk_instructions', $steps );
					}

					$this->sync->update_sync( $post_id, $updated_at, 'completed' );

					$i++;
				}
			}

			// Set next page.
			if ( isset( $response['paging']['after'] ) ) {
				$paging_after = $response['paging']['after'];
			} else {
				$paging_after = '';
			}// if ( $i === 0 ) {break;}
		} while ( isset( $response['paging']['after'] ) );
	}

	/**
	 * Update nutrition via cron.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return void
	 */
	public function update_nutrition( $recipe_id ) {

		if ( ! $this->validate_credential() ) {
			return;
		}

		$response = $this->get_api_request(
			'/',
			'get',
			array(
				'body' => array(
					'servings' => 1,
					'fields'   => 'normalizedIngredients,nutrition',
					'id'       => get_permalink( $recipe_id ),
				),
			)
		);

		if ( isset( $response['nutrition'] ) ) {
			$nutrition = $response['nutrition'];
			update_post_meta( $recipe_id, '_whisk_glycemic_index', $nutrition['glycemicIndex']['value'] );
			update_post_meta( $recipe_id, '_whisk_glycemic_load', $nutrition['glycemicLoad']['value'] );

			if ( is_array( $nutrition['total'] ) ) {
				foreach ( $nutrition['total'] as $total ) {
					update_post_meta( $recipe_id, '_whisk_' . strtolower( $total['code'] ), round( $total['value'], 2 ) . strtolower( $total['unit'] ) );
				}
			}

			if ( is_array( $nutrition['healthScore'] ) ) {
				update_post_meta( $recipe_id, '_whisk_health_score', $nutrition['healthScore']['value'] );

				foreach ( $nutrition['healthScore']['nutrientsInfluence'] as $nutrients ) {
					update_post_meta( $recipe_id, '_whisk_' . strtolower( $nutrients['code'] ) . '_influence', round( $nutrients['influence'], 2 ) );
				}
			}
		}

		if ( isset( $response['normalizedIngredients'] ) ) {
			foreach ( $response['normalizedIngredients'] as $ingredient ) {
				// Оригинальная строка вида "10 cup fresh ginger" или "1 salt".
				$count = count( explode( ' ', $ingredient['sourceText'] ) );

				if ( $count > 2 ) {
					// 10 cup fresh ginger.
					$pattern = '/[0-9\.\,]+[\s][\w]+[\s]/';
				} else {
					// 1 salt.
					$pattern = '/[0-9\.\,]+[\s]/';
				}

				$source = trim( preg_replace( $pattern, '', $ingredient['sourceText'] ) );

				// analysis.
				$analysis = $ingredient['analysis'];

				// Получить терм из базы по названию.
				$term = get_term_by( 'name', $source, Ingredient::TAXONOMY );

				if ( $term ) {

					// Если уже привязана картинка - ничего не делаем.
					if ( whisk_carbon_get_term_meta( $term->term_id, 'whisk_ingredient_image' ) ) {
						continue;
					}

					// Обновить терм из нормализованного названия и картинки.
					$updated = wp_update_term( $term->term_id, Ingredient::TAXONOMY, array( 'name' => $analysis['product'] ) );

					// загрузка файла во временный каталог.
					$tmp = download_url( $analysis['imageUrl'] );

					if ( is_wp_error( $tmp ) ) {
						@unlink( $tmp ); // phpcs:ignore
						continue;
					}

					$file_array = array(
						'name'     => basename( $analysis['imageUrl'] ),
						'tmp_name' => $tmp,
						'error'    => 0,
						'size'     => filesize( $tmp ),
					);

					// загружаем файл в медиатеку.
					$media_id = media_handle_sideload( $file_array, 0, $analysis['product'] );

					if ( is_wp_error( $media_id ) ) {
						@unlink( $tmp ); // phpcs:ignore
						continue;
					}

					update_term_meta( $term->term_id, '_whisk_ingredient_image', $media_id );
				}
			}
		}
	}

	/**
	 * Build query from array.
	 *
	 * @param array $data Query data.
	 *
	 * @return string
	 */
	public function http_build_query( $data ) {
		$result = array();

		foreach ( $data as $key => $val ) {
			if ( empty( $val ) ) {
				continue;
			}

			if ( is_array( $val ) ) {
				foreach ( $val as $a => $b ) {
					$result[] = sprintf( '%s=%s', $key, $b );
				}
			} else {
				$result[] = sprintf( '%s=%s', $key, $val );
			}
		}

		return implode( '&', $result );
	}

	/**
	 * Get API request.
	 *
	 * @param string $endpoint API full URL or API endpoint.
	 * @param string $method HTTP method (get, post, put).
	 * @param array  $args Request arguments.
	 *
	 * @return array|string|\WP_Error
	 */
	protected function get_api_request( $endpoint, $method = 'get', $args = array() ) {
		$url = ( false !== strpos( $endpoint, 'http' ) )
			? $endpoint
			: sprintf( '%s%s', $this->get_api_base(), $endpoint );

		$args = Utils::wp_parse_args(
			$args,
			array(
				'method'  => strtoupper( $method ),
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Accept'        => 'application/json',
				),
			)
		);

		if ( 'get' === $method ) {

			if ( isset( $args['body'] ) ) {
				$url .= '?' . $this->http_build_query( $args['body'] );
				unset( $args['body'] );
			}

			$response = wp_remote_get( $url, $args );

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return $response;
			}

			$body = wp_remote_retrieve_body( $response );

			return json_decode( $body, true );
		}

		if ( 'post' === $method || 'put' === $method ) {

			if ( isset( $args['body'] ) ) {
				$args['body'] = wp_json_encode( $args['body'] );
			}

			$response = wp_remote_post( $url, $args );

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return $response;
			}

			$body = wp_remote_retrieve_body( $response );

			return json_decode( $body, true );
		}
	}

	/**
	 * Fires once a post has been saved.
	 *
	 * The dynamic portion of the hook name, `$post->post_type`, refers to
	 * the post type slug.
	 *
	 * @since 3.7.0
	 *
	 * @param int     $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function on_recipe_save( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'publish' !== $post->post_status ) {
			return;
		}

		//wp_schedule_single_event( time() + 20, self::UPDATE_NUTRITION_EVENT, array( $post_id ) );
		as_schedule_single_action( time() + 20, self::UPDATE_NUTRITION_EVENT, array( $post_id ), Utils::get_plugin_prefix() );
	}

	/**
	 * Schedule event.
	 */
	public function schedule_event() {
		if ( ! as_next_scheduled_action( self::CREATE_INTEGRATION_ID_EVENT, array(), Utils::get_plugin_prefix() ) ) {
			as_schedule_recurring_action( time(), MINUTE_IN_SECONDS * 5, self::CREATE_INTEGRATION_ID_EVENT, array(), Utils::get_plugin_prefix() );
		}

		if ( ! as_next_scheduled_action( self::IMPORT_RECIPES_EVENT ) ) {
			as_schedule_recurring_action( time(), MINUTE_IN_SECONDS * 5, self::IMPORT_RECIPES_EVENT, array(), Utils::get_plugin_prefix() );
		}
	}

	/**
	 * Register custom REST route for authentication.
	 */
	public function register_rest_route() {
		register_rest_route(
			'whisk/v1',
			'/auth/',
			array(
				'methods'             => array( 'GET', 'POST' ),
				'permission_callback' => '__return_true',
				'callback'            => function () {
					if ( isset( $_GET['state'] ) && isset( $_GET['code'] ) ) { // phpcs:ignore

						// Get token.
						$body = array(
							'client_id'     => $this->get_client_id(),
							'client_secret' => $this->get_secret(),
							'grant_type'    => 'authorization_code',
							'redirect_uri'  => esc_url( home_url( '/wp-json/whisk/v1/auth/' ) ),
							'code'          => sanitize_text_field( $_GET['code'] ), // phpcs:ignore
						);

						$response = wp_remote_post(
							self::TOKEN,
							array(
								'timeout' => 5,
								'body'    => http_build_query( $body ),
							)
						);

						if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
							$response = json_decode( wp_remote_retrieve_body( $response ), true );

							$this->update_token( $response['access_token'] );
						}

						wp_safe_redirect( $_GET['state'] );  // phpcs:ignore
					}
				},
			)
		);
	}

	/**
	 * Get API base.
	 *
	 * @return string
	 */
	protected function get_api_base() {
		return self::BASE;
	}

	/**
	 * Get API token.
	 *
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Get API integration_id.
	 *
	 * @return string
	 */
	public function get_integration_id() {
		return $this->integration_id;
	}

	/**
	 * Get API client_id.
	 *
	 * @return string
	 */
	public function get_client_id() {
		return $this->client_id;
	}

	/**
	 * Get API secret.
	 *
	 * @return string
	 */
	public function get_secret() {
		return $this->secret;
	}

	/**
	 * Update API token.
	 *
	 * @param string $token Token.
	 */
	public function update_token( $token ) {
		update_option( '_whisk_api_token', $token, false );
	}

	/**
	 * Update API Integration ID.
	 *
	 * @param string $integration_id Integration ID.
	 */
	public function update_integration_id( $integration_id ) {
		update_option( '_whisk_api_integration_id', $integration_id, false );
	}

	/**
	 * Validate API credential.
	 *
	 * @return bool
	 */
	public function validate_credential() {
		return ( ! empty( $this->get_token() ) && ! empty( $this->get_integration_id() ) );
	}
}
