<?php
namespace Whisk\Recipes\Import;

use Whisk\Recipes\Models\Recipe;

/**
 * Class Ultimate_Import
 *
 * @package whisk-recipes
 */
class Ultimate_Import extends Importer {
	/**
	 * Post type of imported cpt
	 *
	 * @var string
	 */
	protected $post_type = 'recipe';

	/**
	 * Get recipe with the specified ID as array.
	 *
	 * @param mixed $id ID of the recipe we want to import.
	 *
	 * @return array
	 */
	public function get_recipe( $id ) {
		$recipe = array(
			'import_id' => $id,
		);

		$post = get_post( $id );

		// Featured Image.
		$recipe['image_id'] = get_post_meta( $id, 'recipe_alternate_image', true ) ? (int) get_post_meta( $id, 'recipe_alternate_image', true ) : get_post_thumbnail_id( $id );

		// Simple Matching.
		$recipe['name']     = get_post_meta( $id, 'recipe_title', true ) ? get_post_meta( $id, 'recipe_title', true ) : $post->post_title;
		$recipe['summary']  = get_post_meta( $id, 'recipe_description', true );
		$recipe['notes']    = get_post_meta( $id, 'recipe_notes', true );
		$recipe['video_id'] = get_post_meta( $id, 'recipe_video_id', true );

		// Servings.
		$recipe['servings']      = get_post_meta( $id, 'recipe_servings', true );
		$recipe['servings_unit'] = get_post_meta( $id, 'recipe_servings_type', true );

		// Recipe times in minutes.
		$prep_time           = get_post_meta( $id, 'recipe_prep_time', true );
		$prep_time_unit      = get_post_meta( $id, 'recipe_prep_time_text', true );
		$recipe['prep_time'] = $prep_time ? $this->get_time_in_minutes( $prep_time, $prep_time_unit ) : '';

		$cook_time           = get_post_meta( $id, 'recipe_cook_time', true );
		$cook_time_unit      = get_post_meta( $id, 'recipe_cook_time_text', true );
		$recipe['cook_time'] = $cook_time ? $this->get_time_in_minutes( $cook_time, $cook_time_unit ) : '';

		$passive_time           = get_post_meta( $id, 'recipe_passive_time', true );
		$passive_time_unit      = get_post_meta( $id, 'recipe_passive_time_text', true );
		$recipe['resting_time'] = $passive_time ? $this->get_time_in_minutes( $passive_time, $passive_time_unit ) : '';

		// Recipe tags.
		$course_terms      = get_the_terms( $id, 'course' );
		$recipe['course']  = $course_terms ? wp_list_pluck( $course_terms, 'name' ) : [];
		$cuisine_terms     = get_the_terms( $id, 'cuisine' );
		$recipe['cuisine'] = $cuisine_terms ? wp_list_pluck( $cuisine_terms, 'name' ) : [];
		$keyword_terms     = get_the_terms( $id, 'wpurp_keyword' );
		$recipe['keyword'] = $keyword_terms ? wp_list_pluck( $keyword_terms, 'name' ) : [];

		// Ingredients.
		$recipe['ingredients'] = get_post_meta( $id, 'recipe_ingredients', true );

		// Instructions.
		$recipe['instructions'] = get_post_meta( $id, 'recipe_instructions', true );

		// Nutrition Facts.
		$recipe['nutrition'] = array();
		$nutrition           = get_post_meta( $id, 'recipe_nutritional', true );

		if ( $nutrition ) {
			$nutrition_fields = [
				'serving_size',
				'calories',
				'fat',
				'saturated_fat',
				'polyunsaturated_fat',
				'monounsaturated_fat',
				'trans_fat',
				'carbohydrate',
				'sugar',
				'fiber',
				'protein',
				'cholesterol',
				'sodium',
				'potassium',
				'vitamin_a',
				'vitamin_c',
				'calcium',
				'iron',
			];
			foreach ( $nutrition_fields as $field ) {
				$value                         = isset( $nutrition[ $field ] ) ? $nutrition[ $field ] : '';
				$recipe['nutrition'][ $field ] = filter_var(
					$value,
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
				);
			}
		}

		return $recipe;
	}

	/**
	 * Create Whisks post and fill in data.
	 *
	 * @param int $import_id id of post being imported.
	 */
	public function process_recipe( $import_id ) {
		$recipe = $this->get_recipe( $import_id );

		$post = array(
			'post_type'    => Recipe::get_cpt_name(),
			'post_status'  => 'publish',
			'post_title'   => wp_strip_all_tags( $recipe['name'] ),
			'post_content' => wp_kses_post( $recipe['summary'] ),
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
		update_post_meta( $id, '_whisk_video', intval( $recipe['video_id'] ) );
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

		$times = $this->minutes_to_time( $recipe['resting_time'] );
		if ( $times['days'] ) {
			update_post_meta( $id, '_whisk_resting_time_days', $times['days'] );
		}
		if ( $times['hours'] ) {
			update_post_meta( $id, '_whisk_resting_time_hours', $times['hours'] );
		}
		if ( $times['minutes'] ) {
			update_post_meta( $id, '_whisk_resting_time_minutes', $times['minutes'] );
		}

		// Nutrition.
		update_post_meta( $id, '_whisk_enerc_kcal', $recipe['nutrition']['calories'] );
		update_post_meta( $id, '_whisk_sugar', $recipe['nutrition']['sugar'] );
		update_post_meta( $id, '_whisk_na', $recipe['nutrition']['sodium'] );
		update_post_meta( $id, '_whisk_fat', $recipe['nutrition']['fat'] );
		update_post_meta( $id, '_whisk_fasat', $recipe['nutrition']['saturated_fat'] );
		update_post_meta( $id, '_whisk_fapu', $recipe['nutrition']['polyunsaturated_fat'] );
		update_post_meta( $id, '_whisk_fams', $recipe['nutrition']['monounsaturated_fat'] );
		update_post_meta( $id, '_whisk_fatrn', $recipe['nutrition']['trans_fat'] );
		update_post_meta( $id, '_whisk_chocdf', $recipe['nutrition']['carbohydrate'] );
		update_post_meta( $id, '_whisk_fibtg', $recipe['nutrition']['fiber'] );
		update_post_meta( $id, '_whisk_procnt', $recipe['nutrition']['protein'] );
		update_post_meta( $id, '_whisk_chole', $recipe['nutrition']['cholesterol'] );
		update_post_meta( $id, '_whisk_na', $recipe['nutrition']['sodium'] );
		update_post_meta( $id, '_whisk_k', $recipe['nutrition']['potassium'] );
		update_post_meta( $id, '_whisk_vita_rae', $recipe['nutrition']['vitamin_a'] );
		update_post_meta( $id, '_whisk_vitc', $recipe['nutrition']['vitamin_с'] );
		update_post_meta( $id, '_whisk_ca', $recipe['nutrition']['calcium'] );
		update_post_meta( $id, '_whisk_fe', $recipe['nutrition']['iron'] );

		// Ingredients.
		if ( isset( $recipe['ingredients'] ) && is_array( $recipe['ingredients'] ) ) {
			$term_names = wp_list_pluck( $recipe['ingredients'], 'ingredient' );
			wp_set_post_terms( $id, $term_names, 'whisk_ingredient', true );
			$carbon_set_array = [];
			foreach ( $recipe['ingredients'] as $item ) {
				$term               = get_term_by( 'name', $item['ingredient'], 'whisk_ingredient' );
				$carbon_set_array[] = [
					'whisk_ingredient_amount' => sanitize_text_field( $item['amount'] ),
					'whisk_ingredient_unit'   => sanitize_text_field( $item['unit'] ),
					'whisk_ingredient_id'     => isset( $term ) && is_a( $term, 'WP_Term' ) ? $term->term_id : null,
					'whisk_ingredient_note'   => sanitize_text_field( $item['notes'] ),
				];
			}
			whisk_carbon_set_post_meta( $id, 'whisk_ingredients', $carbon_set_array );
		}

		// Instructions.
		if ( isset( $recipe['instructions'] ) && is_array( $recipe['instructions'] ) ) {
			$group = '';
			foreach ( $recipe['instructions'] as $row ) {
				if ( $row['group'] && $row['group'] !== $group ) {
					$group              = $row['group'];
					$carbon_set_array[] = [
						'_type'                     => 'separator',
						'whisk_step_separator_name' => sanitize_text_field( $row['group'] ),
					];
				}
				$carbon_set_array[] = [
					'_type'                  => 'step',
					'whisk_step_image'       => intval( $row['image'] ),
					'whisk_step_instruction' => wp_kses_post( $row['description'] ),
				];

			}
			whisk_carbon_set_post_meta( $id, 'whisk_instructions', $carbon_set_array );
		}

		// Notes.
		update_post_meta( $id, '_whisk_simple_notes_text', wp_kses_post( $recipe['notes'] ) );
		update_post_meta( $id, '_whisk_simple_notes', '1' );

		// Imported flag.
		update_post_meta( $import_id, 'imported', 1 );
	}

	/**
	 * Convert time string to minutes.
	 *
	 * @param mixed $time Time string to convert to minutes.
	 * @param mixed $unit Unit of the time string.
	 */
	private function get_time_in_minutes( $time, $unit ) {
		$minutes = intval( $time );

		if ( strtolower( $unit ) === strtolower( __( 'hour', 'whisk-recipes' ) )
			|| strtolower( $unit ) === strtolower( __( 'hours', 'whisk-recipes' ) )
			|| strtolower( $unit ) === _x( 'h', 'hour abbreviation', 'whisk-recipes' )
			|| strtolower( $unit ) === _x( 'hr', 'hour abbreviation', 'whisk-recipes' )
			|| strtolower( $unit ) === _x( 'hr', 'hours abbreviation', 'whisk-recipes' ) ) {
			$minutes = $minutes * 60;
		}

		return $minutes;
	}
}
