<?php
namespace Whisk\Recipes\Import;

use Whisk\Recipes\Models\Recipe;

/**
 * Class Recipemaker_Import
 *
 * @package whisk-recipes
 */
class Recipemaker_Import extends Importer {
	/**
	 * Post type of imported cpt
	 *
	 * @var string
	 */
	protected $post_type = 'wprm_recipe';

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
		$recipe['image_id'] = get_post_thumbnail_id( $id );

		// Simple Matching.
		$recipe['name']           = $post->post_title;
		$recipe['summary']        = $post->post_content;
		$recipe['notes']          = get_post_meta( $id, 'wprm_notes', true );
		$recipe['author_name']    = get_post_meta( $id, 'wprm_author_name', true );
		$recipe['video_url']      = get_post_meta( $id, 'wprm_video_embed', true );
		$recipe['video_id']       = get_post_meta( $id, 'wprm_video_id', true );
		$recipe['estimated_cost'] = filter_var(
			get_post_meta( $id, 'wprm_cost', true ),
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
		);

		// Servings.
		$recipe['servings']      = get_post_meta( $id, 'wprm_servings', true );
		$recipe['servings_unit'] = get_post_meta( $id, 'wprm_servings_unit', true );

		// Recipe times in minutes.
		$recipe['prep_time']  = get_post_meta( $id, 'wprm_prep_time', true );
		$recipe['cook_time']  = get_post_meta( $id, 'wprm_cook_time', true );
		$recipe['total_time'] = get_post_meta( $id, 'wprm_total_time', true );

		// Recipe tags.
		$course_terms        = get_the_terms( $id, 'wprm_course' );
		$recipe['course']    = $course_terms ? wp_list_pluck( $course_terms, 'name' ) : [];
		$cuisine_terms       = get_the_terms( $id, 'wprm_cuisine' );
		$recipe['cuisine']   = $cuisine_terms ? wp_list_pluck( $cuisine_terms, 'name' ) : [];
		$equipment_terms     = get_the_terms( $id, 'wprm_equipment' );
		$recipe['equipment'] = $equipment_terms ? wp_list_pluck( $equipment_terms, 'name' ) : [];
		$keyword_terms       = get_the_terms( $id, 'wprm_keyword' );
		$recipe['keyword']   = $keyword_terms ? wp_list_pluck( $keyword_terms, 'name' ) : [];

		// Ingredients.
		$raw_ingredients = get_post_meta( $id, 'wprm_ingredients', true );
		if ( $raw_ingredients ) {
			$ingredients = wp_list_pluck( $raw_ingredients, 'ingredients' );
			$processed   = [];
			foreach ( $ingredients as $group ) {
				$processed = array_merge( $processed, $group );
			}
			$recipe['ingredients'] = $processed;
		}

		// Instructions.
		$recipe['instructions'] = get_post_meta( $id, 'wprm_instructions', true );

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		$recipe['nutrition']['serving_size'] = get_post_meta( $id, 'wprm_servings', true );
		$recipe['nutrition']['serving_unit'] = get_post_meta( $id, 'wprm_servings_unit', true );

		$nutrition_fields = [
			'calories',
			'fat',
			'saturated_fat',
			'unsaturated_fat',
			'trans_fat',
			'carbohydrates',
			'sugar',
			'fiber',
			'protein',
			'cholesterol',
			'sodium',
		];
		foreach ( $nutrition_fields as $field ) {
			$recipe['nutrition'][ $field ] = filter_var(
				get_post_meta( $id, 'wprm_nutrition_' . $field, true ),
				FILTER_SANITIZE_NUMBER_FLOAT,
				FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
			);
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
			set_post_thumbnail( $id, intval( $recipe['image_id'] ) );
		}

		// Tags.
		wp_set_post_terms( $id, $recipe['course'], 'whisk_meal_type', true );
		$this->set_tags_fields( $id, 'whisk_meal_type', 'whisk_meal_types' );
		wp_set_post_terms( $id, $recipe['cuisine'], 'whisk_cuisine', true );
		$this->set_tags_fields( $id, 'whisk_cuisine', 'whisk_cuisines' );
		wp_set_post_terms( $id, $recipe['equipment'], 'whisk_equipment', true );
		$this->set_tags_fields( $id, 'whisk_equipment', 'whisk_equipments' );

		// Simple meta fields.
		update_post_meta( $id, '_whisk_servings', intval( $recipe['servings'] ) );
		update_post_meta( $id, '_whisk_video_url', esc_url( $recipe['video_url'] ) );
		update_post_meta( $id, '_whisk_video', intval( $recipe['video_id'] ) );
		update_post_meta( $id, '_whisk_imported_id', intval( $recipe['import_id'] ) );
		update_post_meta( $id, '_whisk_ingredients_estimated_cost', sanitize_text_field( $recipe['estimated_cost'] ) );

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
		update_post_meta( $id, '_whisk_enerc_kcal', $recipe['nutrition']['calories'] );
		update_post_meta( $id, '_whisk_sugar', $recipe['nutrition']['sugar'] );
		update_post_meta( $id, '_whisk_na', $recipe['nutrition']['sodium'] );
		update_post_meta( $id, '_whisk_fat', $recipe['nutrition']['fat'] );
		update_post_meta( $id, '_whisk_fasat', $recipe['nutrition']['saturated_fat'] );
		update_post_meta( $id, '_whisk_fapu', $recipe['nutrition']['unsaturated_fat'] );
		update_post_meta( $id, '_whisk_fatrn', $recipe['nutrition']['trans_fat'] );
		update_post_meta( $id, '_whisk_chocdf', $recipe['nutrition']['carbohydrates'] );
		update_post_meta( $id, '_whisk_fibtg', $recipe['nutrition']['fiber'] );
		update_post_meta( $id, '_whisk_procnt', $recipe['nutrition']['protein'] );
		update_post_meta( $id, '_whisk_chole', $recipe['nutrition']['cholesterol'] );

		// Ingredients.
		if ( isset( $recipe['ingredients'] ) && is_array( $recipe['ingredients'] ) ) {
			$term_names = wp_list_pluck( $recipe['ingredients'], 'name' );
			wp_set_post_terms( $id, $term_names, 'whisk_ingredient', true );
			$carbon_set_array = [];
			foreach ( $recipe['ingredients'] as $row ) {
				$carbon_row = [];
				foreach ( $row as $key => $value ) {
					switch ( $key ) {
						case 'amount':
							$carbon_row['whisk_ingredient_amount'] = sanitize_text_field( $value );
							break;
						case 'unit':
							$carbon_row['whisk_ingredient_unit'] = sanitize_text_field( $value );
							break;
						case 'name':
							$term                              = get_term_by( 'name', $value, 'whisk_ingredient' );
							$carbon_row['whisk_ingredient_id'] = isset( $term ) && is_a( $term, 'WP_Term' ) ? $term->term_id : null;
							break;
						case 'notes':
							$carbon_row['whisk_ingredient_note'] = sanitize_text_field( $value );
							break;
					}
				}
				$carbon_set_array[] = $carbon_row;
			}
			whisk_carbon_set_post_meta( $id, 'whisk_ingredients', $carbon_set_array );
		}

		// Instructions.
		if ( isset( $recipe['instructions'] ) && is_array( $recipe['instructions'] ) ) {
			$carbon_set_array = [];
			foreach ( $recipe['instructions'] as $group ) {
				if ( $group['name'] ) {
					$carbon_set_array[] = [
						'_type'                     => 'separator',
						'whisk_step_separator_name' => sanitize_text_field( $group['name'] ),
					];
				}
				foreach ( $group['instructions'] as $row ) {
					$carbon_row                         = [
						'_type'                  => 'step',
						'whisk_step_summary'     => sanitize_text_field( $row['name'] ),
						'whisk_step_image'       => intval( $row['image'] ),
						'whisk_step_instruction' => sanitize_text_field( $row['text'] ),
					];
					$carbon_row['whisk_step_video_url'] = 'embed' === $row['video']['type'] ? esc_url( $row['video']['embed'] ) : '';
					$carbon_row['whisk_step_video']     = 'upload' === $row['video']['type'] ? intval( $row['video']['id'] ) : '';
					$carbon_set_array[]                 = $carbon_row;
				}
			}
			whisk_carbon_set_post_meta( $id, 'whisk_instructions', $carbon_set_array );
		}

		// Notes.
		update_post_meta( $id, '_whisk_simple_notes_text', wp_kses_post( $recipe['notes'] ) );
		update_post_meta( $id, '_whisk_simple_notes', '1' );

		// Imported flag.
		update_post_meta( $import_id, 'imported', 1 );
	}
}
