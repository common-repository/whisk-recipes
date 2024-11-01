<?php
namespace Whisk\Recipes\Import;

use DiDom\Document;
use Whisk\Recipes\Models\Recipe;

/**
 * Class Easyrecipe_Import
 *
 * @package whisk-recipes
 */
class Easyrecipe_Import extends Importer {

	/**
	 * DiDOM class object
	 *
	 * @var Document
	 */
	private $document;

	/**
	 * Easyrecipe_Import constructor.
	 */
	public function __construct() {
		libxml_use_internal_errors( true );
		$this->document = new Document();
	}

	/**
	 * Count recipes for import
	 *
	 * @return int
	 */
	public function count_posts() {
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args = array(
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'any',
			'meta_key'       => 'imported',
			'meta_compare'   => 'NOT EXISTS',
			'posts_per_page' => 1,
			's'              => 'item ERName',
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key

		$query = new \WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Select ids of posts for import
	 *
	 * @return array|null
	 */
	public function get_all_posts_ids() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type IN('post', 'page')" );
	}

	/**
	 * Get recipe with the specified ID as array.
	 *
	 * @param mixed $id ID of the recipe we want to import.
	 *
	 * @return array
	 * @throws \DiDom\Exceptions\InvalidSelectorException Error report.
	 */
	public function get_recipes_html( $id ) {
		$post = get_post( $id );
		$html = $post->post_content;
		$this->document->loadHtml( $html );
		$recipes = $this->document->find( '.easyrecipe' );

		return $recipes;
	}

	/**
	 * Create Whisks post and fill in data.
	 *
	 * @param int $import_id id of post being imported.
	 */
	public function process_recipe( $import_id ) {
		$recipes = $this->get_recipes_html( $import_id );
		if ( ! $recipes ) {
			return;
		}
		foreach ( $recipes as $recipe_html ) {
			$recipe = $this->get_recipe( $recipe_html, $import_id );

			$post = array(
				'post_type'    => Recipe::get_cpt_name(),
				'post_status'  => 'publish',
				'post_title'   => wp_strip_all_tags( $recipe['name'] ),
				'post_content' => html_entity_decode( wp_kses_post( $recipe['summary'] ) ),
			);
			$id   = wp_insert_post( $post );

			// Post thumbnail.
			if ( isset( $recipe['image_id'] ) && $recipe['image_id'] ) {
				set_post_thumbnail( $id, $recipe['image_id'] );
			}

			// Tags.
			wp_set_post_terms( $id, $recipe['course'], 'whisk_meal_type', true );
			$this->set_tags_fields( $id, 'whisk_meal_type', 'whisk_meal_types' );
			wp_set_post_terms( $id, $recipe['cuisine'], 'whisk_cuisine', true );
			$this->set_tags_fields( $id, 'whisk_cuisine', 'whisk_cuisines' );

			// Simple meta fields.
			update_post_meta( $id, '_whisk_servings', intval( $recipe['servings'] ) );
			update_post_meta( $id, '_whisk_imported_id', intval( $recipe['import_id'] ) );

			// Times.
			$times = $this->minutes_to_time( $recipe['prep_time'] );
			if ( $times['days'] ) {
				update_post_meta( $id, '_whisk_prep_time_days', $times['days'] );
			}
			if ( $times['hours'] ) {
				update_post_meta( $id, '_whisk_prep_time_hours', $times['hours'] );
			}
			if ( $times['minutes'] ) {
				update_post_meta( $id, '_whisk_prep_time_minutes', $times['minutes'] );
			}

			$times = $this->minutes_to_time( $recipe['cook_time'] );
			if ( $times['days'] ) {
				update_post_meta( $id, '_whisk_cook_time_days', $times['days'] );
			}
			if ( $times['hours'] ) {
				update_post_meta( $id, '_whisk_cook_time_hours', $times['hours'] );
			}
			if ( $times['minutes'] ) {
				update_post_meta( $id, '_whisk_cook_time_minutes', $times['minutes'] );
			}

			// Nutrition.
			foreach ( $recipe['nutrition'] as $field_name => $field_value ) {
				update_post_meta( $id, $field_name, $field_value );
			}

			// Notes.
			update_post_meta( $id, '_whisk_simple_notes_text', wp_kses_post( $recipe['notes'] ) );
			update_post_meta( $id, '_whisk_simple_notes', '1' );

			// Ingredients.
			update_post_meta( $id, '_whisk_simple_ingredients_text', wp_kses_post( $recipe['ingredients'] ) );
			update_post_meta( $id, '_whisk_simple_ingredients', '1' );

			// Instructions.
			update_post_meta( $id, '_whisk_simple_instructions_text', wp_kses_post( $recipe['instructions'] ) );
			update_post_meta( $id, '_whisk_simple_instructions', '1' );

			// Imported flag.
			update_post_meta( $import_id, 'imported', 1 );
		}
	}

	/**
	 * Extract data from recipe object to array
	 *
	 * @param \DiDom\Element $recipe_html recipe DiDOM object.
	 * @param int            $import_id id of recipe source.
	 *
	 * @return array
	 */
	private function get_recipe( $recipe_html, $import_id ) {
		$recipe = array(
			'import_id' => $import_id,
		);

		// Featured image.
		$recipe['image_id'] = '';
		if ( $recipe_html->has( 'link[itemprop=image]' ) ) {
			$easyrecipe_field   = $recipe_html->find( 'link[itemprop=image]' )[0];
			$image_url          = $easyrecipe_field->getAttribute( 'href' );
			$recipe['image_id'] = $image_url ? attachment_url_to_postid( $image_url ) : '';
		} else {
			// Use first image added to recipe.
			$images = $this->get_easyrecipe_images( $recipe_html->html() );
			if ( isset( $images[0] ) ) {
				$recipe['image_id'] = $images[0]['id'];
			}
		}

		// Name.
		$recipe['name'] = '';
		if ( $recipe_html->has( '.ERName' ) ) {
			$easyrecipe_field = $recipe_html->find( '.ERName' )[0];
			$recipe['name']   = $easyrecipe_field->text();
		}

		// Summary.
		$recipe['summary'] = '';
		if ( $recipe_html->has( '.ERSummary' ) ) {
			$easyrecipe_field  = $recipe_html->find( '.ERSummary' )[0];
			$recipe['summary'] = $easyrecipe_field->innerHtml();
		}

		// Notes.
		$recipe['notes'] = '';
		if ( $recipe_html->has( '.ERNotes' ) ) {
			$easyrecipe_field = $recipe_html->find( '.ERNotes' )[0];
			$recipe['notes']  = $easyrecipe_field->innerHtml();
		}

		// Servings, servings unit.
		$recipe['servings'] = '';
		if ( $recipe_html->has( '.yield' ) ) {
			$easyrecipe_field   = $recipe_html->find( '.yield' )[0];
			$recipe['servings'] = trim( wp_strip_all_tags( $easyrecipe_field->text() ) );
		}
		$recipe['servings_unit'] = preg_replace( '/^\s*\d+\s*/', '', $easyrecipe_field );

		// Cook times.
		$recipe['prep_time']  = '';
		$recipe['cook_time']  = '';
		$recipe['total_time'] = '';
		if ( $recipe_html->has( 'time' ) ) {
			$easyrecipe_field = $recipe_html->find( 'time' );
			foreach ( $easyrecipe_field as $item ) {
				$minutes = $this->er_time_to_minutes( $item->getAttribute( 'datetime' ) );
				switch ( $item->getAttribute( 'itemprop' ) ) {
					case 'prepTime':
						$recipe['prep_time'] = $minutes;
						break;
					case 'cookTime':
						$recipe['cook_time'] = $minutes;
						break;
					case 'totalTime':
						$recipe['total_time'] = $minutes;
				}
			}
		}

		// Recipe Tags.
		$recipe['course'] = [];
		if ( $recipe_html->has( '.type' ) ) {
			$easyrecipe_field = $recipe_html->find( '.type' )[0];
			$easyrecipe_field = str_replace( ';', ',', $easyrecipe_field->text() );
			$recipe['course'] = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $easyrecipe_field );
		}
		$recipe['cuisine'] = [];
		if ( $recipe_html->has( '.cuisine' ) ) {
			$easyrecipe_field  = $recipe_html->find( '.cuisine' )[0];
			$easyrecipe_field  = str_replace( ';', ',', $easyrecipe_field->text() );
			$recipe['cuisine'] = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $easyrecipe_field );
		}
		// Ingredients.
		$recipe['ingredients'] = '';
		if ( $recipe_html->has( '.ingredients' ) ) {
			$block = $recipe_html->find( '.ingredients' )[0];
			if ( $block->has( '.ERSeparator' ) ) {
				foreach ( $block->find( '.ERSeparator' ) as $heading ) {
					$recipe['ingredients'] .= "<h3>{$heading->text()}</h3>";
				}
			}
			if ( $block->has( '.ingredient' ) ) {
				$recipe['ingredients'] .= '<ul>';
				foreach ( $block->find( '.ingredient' ) as $li ) {
					$recipe['ingredients'] .= "<li>{$li->text()}</li>";
				}
				$recipe['ingredients'] .= '</ul>';
			}
		}
		// Instructions.
		$recipe['instructions'] = '';
		if ( $recipe_html->has( '.instructions' ) ) {
			$block = $recipe_html->find( '.instructions' )[0];
			if ( $block->has( '.ERSeparator' ) ) {
				foreach ( $block->find( '.ERSeparator' ) as $heading ) {
					$recipe['instructions'] .= "<h3>{$heading->text()}</h3>";
				}
			}
			if ( $block->has( '.instruction' ) ) {
				$recipe['instructions'] .= '<ul>';
				foreach ( $block->find( '.instruction' ) as $li ) {
					$recipe['instructions'] .= "<li>{$li->text()}</li>";
				}
				$recipe['instructions'] .= '</ul>';
			}
		}

		// Serving size.
		if ( $recipe_html->has( '.servingSize' ) ) {
			$recipe['serving_size'] = $recipe_html->find( '.servingSize' )[0]->text();
		}

		// Nutrition.
		$recipe['nutrition'] = [];
		$nutrition_mapping   = [
			'calories'       => 'whisk_enerc_kcal',
			'carbohydrates'  => 'whisk_chocdf',
			'protein'        => 'whisk_procnt',
			'fat'            => 'whisk_fat',
			'saturatedFat'   => 'whisk_fasat',
			'unsaturatedFat' => 'whisk_fapu',
			'transFat'       => 'whisk_fatrn',
			'cholesterol'    => 'whisk_chole',
			'sodium'         => 'whisk_na',
			'fiber'          => 'whisk_fibtg',
			'sugar'          => 'whisk_sugar',
		];
		foreach ( $nutrition_mapping as $er_field => $whisk_field ) {
			if ( $recipe_html->has( '.' . $er_field ) ) {
				$value                               = $recipe_html->find( '.' . $er_field )[0]->text();
				$recipe['nutrition'][ $whisk_field ] = filter_var(
					$value,
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
				);
			}
		}

		return $recipe;
	}



	/**
	 * Get images that are used in the ER recipe.
	 *
	 * @param mixed $text Text to find images in.
	 *
	 * @return array
	 */
	private function get_easyrecipe_images( $text ) {
		$images = array();

		preg_match_all( '/\[img[^\]]*]/i', $text, $easyrecipe_images );

		if ( isset( $easyrecipe_images[0] ) ) {
			foreach ( $easyrecipe_images[0] as $easyrecipe_image ) {
				preg_match( '/src=\"([^\"]*)\"/i', $easyrecipe_image, $image );

				if ( isset( $image[1] ) ) {
					$id    = attachment_url_to_postid( $image[1] );
					$image = wp_get_attachment_image_src( $id, array( 9999, 150 ) );

					$images[] = array(
						'id'  => $id,
						'img' => $image[0],
					);
				}
			}
		}

		return $images;
	}

	/**
	 * Get time in minutes from ER time string.
	 *
	 * @param mixed $duration ER time string.
	 *
	 * @return int
	 */
	private function er_time_to_minutes( $duration = 'PT' ) {
		$date_abbr = array(
			'd' => 60 * 24,
			'h' => 60,
			'i' => 1,
		);
		$result    = 0;

		$arr = explode( 'T', $duration );
		if ( isset( $arr[1] ) ) {
			$arr[1] = str_replace( 'M', 'I', $arr[1] );
		}
		$duration = implode( 'T', $arr );

		foreach ( $date_abbr as $abbr => $time ) {
			if ( preg_match( '/(\d+)' . $abbr . '/i', $duration, $val ) ) {
				$result += intval( $val[1] ) * $time;
			}
		}

		return $result;
	}
}
