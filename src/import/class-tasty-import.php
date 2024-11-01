<?php
namespace Whisk\Recipes\Import;

use DiDom\Document;
use DiDom\Element;
use Whisk\Recipes\Models\Recipe;

/**
 * Class Tasty_Import
 *
 * @package whisk-recipes
 */
class Tasty_Import extends Importer {
	/**
	 * Post type of imported cpt
	 *
	 * @var string
	 */
	protected $post_type = 'tasty_recipe';

	/**
	 * DiDOM class object
	 *
	 * @var Document
	 */
	private $document;

	/**
	 * Tasty_Import constructor.
	 */
	public function __construct() {
		libxml_use_internal_errors( true );
		$this->document = new Document();
	}

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
		$recipe['name']        = $post->post_title;
		$summary               = get_post_meta( $id, 'description', true );
		$recipe['summary']     = $this->do_html_replacements( $summary );
		$notes                 = get_post_meta( $id, 'notes', true );
		$recipe['notes']       = $this->do_html_replacements( $notes );
		$recipe['author_name'] = get_post_meta( $id, 'author_name', true );
		$recipe['video_url']   = get_post_meta( $id, 'video_url', true );

		// Servings.
		$tasty_yield = get_post_meta( $id, 'yield', true );
		$match       = preg_match( '/^\s*\d+/', $tasty_yield, $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ', '', $servings_array[0] );
		} else {
			$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $tasty_yield );

		$recipe['servings']      = (int) $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe times.
		$recipe['prep_time']  = $this->get_minutes_for_time( get_post_meta( $id, 'prep_time', true ) );
		$recipe['cook_time']  = $this->get_minutes_for_time( get_post_meta( $id, 'cook_time', true ) );
		$recipe['total_time'] = $this->get_minutes_for_time( get_post_meta( $id, 'total_time', true ) );

		// Recipe tags.
		$recipe['category'] = array_map( 'trim', explode( ',', get_post_meta( $id, 'category', true ) ) );
		$recipe['cuisine']  = array_map( 'trim', explode( ',', get_post_meta( $id, 'cuisine', true ) ) );
		$recipe['method']   = array_map( 'trim', explode( ',', get_post_meta( $id, 'method', true ) ) );
		$recipe['keyword']  = array_map( 'trim', explode( ',', get_post_meta( $id, 'keywords', true ) ) );

		// Ingredients.
		$ingredients           = get_post_meta( $id, 'ingredients', true );
		$recipe['ingredients'] = $this->do_html_replacements( $ingredients );

		// Instructions.
		$instructions           = get_post_meta( $id, 'instructions', true );
		$recipe['instructions'] = $this->do_html_replacements( $instructions );

		// Nutrition Facts.
		$recipe['nutrition'] = array();

		// Serving size.
		$tasty_serving_size = get_post_meta( $id, 'serving_size', true );
		$match              = preg_match( '/^\s*\d+/', $tasty_serving_size, $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ', '', $servings_array[0] );
		} else {
			$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $tasty_serving_size );

		$recipe['nutrition']['serving_size'] = $servings;
		$recipe['nutrition']['serving_unit'] = $servings_unit;

		$recipe['nutrition']['calories']            = (int) get_post_meta( $id, 'calories', true );
		$recipe['nutrition']['sugar']               = get_post_meta( $id, 'sugar', true );
		$recipe['nutrition']['sodium']              = get_post_meta( $id, 'sodium', true );
		$recipe['nutrition']['fat']                 = get_post_meta( $id, 'fat', true );
		$recipe['nutrition']['saturated_fat']       = get_post_meta( $id, 'saturated_fat', true );
		$recipe['nutrition']['polyunsaturated_fat'] = get_post_meta( $id, 'unsaturated_fat', true );
		$recipe['nutrition']['trans_fat']           = get_post_meta( $id, 'trans_fat', true );
		$recipe['nutrition']['carbohydrates']       = get_post_meta( $id, 'carbohydrates', true );
		$recipe['nutrition']['fiber']               = get_post_meta( $id, 'fiber', true );
		$recipe['nutrition']['protein']             = get_post_meta( $id, 'protein', true );
		$recipe['nutrition']['cholesterol']         = get_post_meta( $id, 'cholesterol', true );

		foreach ( $recipe['nutrition'] as $name => $value ) {
			if ( 'serving_unit' === $name ) {
				continue;
			}
			$recipe['nutrition'][ $name ] = filter_var(
				$value,
				FILTER_SANITIZE_NUMBER_FLOAT,
				FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
			);
		}

		return $recipe;
	}

	/**
	 * Replace h4 tag with h3. Add whisk classes to html.
	 *
	 * @param string $html Html for replacements.
	 *
	 * @return string
	 * @throws \DiDom\Exceptions\InvalidSelectorException DOM parser exception.
	 */
	private function do_html_replacements( $html ) {
		if ( $html ) {
			$this->document->loadHtml( $html );
			$headings = $this->document->find( 'h4' );
			foreach ( $headings as $heading ) {
				$new_heading = new Element( 'h3', $heading->text() );
				$new_heading->setAttribute( 'class', 'whisk-h3' );
				$heading->replace( $new_heading );
			}
			$images = $this->document->find( 'img' );
			foreach ( $images as $image ) {
				$classes     = $image->getAttribute( 'class' );
				$new_classes = $classes ? $classes . ' whisk-image' : 'whisk-image';
				$image->setAttribute( 'class', $new_classes );
			}
			return $this->document->toElement()->innerHtml();
		}

		return '';
	}

	/**
	 * Create Whisks post and fill in data.
	 *
	 * @param int $import_id id of post being imported.
	 */
	public function process_recipe( $import_id ) {
		$recipe = $this->get_recipe( $import_id );
		update_post_meta( $import_id, 'imported', 1 );

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
		wp_set_post_terms( $id, $recipe['category'], 'whisk_tag', true );
		$this->set_tags_fields( $id, 'whisk_tag', 'whisk_tags' );
		wp_set_post_terms( $id, $recipe['cuisine'], 'whisk_cuisine', true );
		$this->set_tags_fields( $id, 'whisk_cuisine', 'whisk_cuisines' );
		wp_set_post_terms( $id, $recipe['method'], 'whisk_cooking_technique', true );
		$this->set_tags_fields( $id, 'whisk_cooking_technique', 'whisk_cooking_techniques' );

		// Simple meta fields.
		update_post_meta( $id, '_whisk_servings', intval( $recipe['servings'] ) );
		update_post_meta( $id, '_whisk_prep_time_minutes', $recipe['prep_time'] );
		update_post_meta( $id, '_whisk_cook_time_minutes', $recipe['cook_time'] );
		update_post_meta( $id, '_whisk_video_url', esc_url( $recipe['video_url'] ) );
		update_post_meta( $id, '_whisk_imported_id', intval( $recipe['import_id'] ) );
		update_post_meta( $id, '_whisk_author_name', sanitize_text_field( $recipe['author_name'] ) );

		// Nutrition.
		update_post_meta( $id, '_whisk_enerc_kcal', $recipe['nutrition']['calories'] );
		update_post_meta( $id, '_whisk_sugar', $recipe['nutrition']['sugar'] );
		update_post_meta( $id, '_whisk_na', $recipe['nutrition']['sodium'] );
		update_post_meta( $id, '_whisk_fat', $recipe['nutrition']['fat'] );
		update_post_meta( $id, '_whisk_fasat', $recipe['nutrition']['saturated_fat'] );
		update_post_meta( $id, '_whisk_fapu', $recipe['nutrition']['polyunsaturated_fat'] );
		update_post_meta( $id, '_whisk_fatrn', $recipe['nutrition']['trans_fat'] );
		update_post_meta( $id, '_whisk_chocdf', $recipe['nutrition']['carbohydrates'] );
		update_post_meta( $id, '_whisk_fibtg', $recipe['nutrition']['fiber'] );
		update_post_meta( $id, '_whisk_procnt', $recipe['nutrition']['protein'] );
		update_post_meta( $id, '_whisk_chole', $recipe['nutrition']['cholesterol'] );

		// Ingredients, instructions, notes.
		update_post_meta( $id, '_whisk_simple_ingredients_text', wp_kses_post( $recipe['ingredients'] ) );
		update_post_meta( $id, '_whisk_simple_instructions_text', wp_kses_post( $recipe['instructions'] ) );
		update_post_meta( $id, '_whisk_simple_notes_text', wp_kses_post( $recipe['notes'] ) );
		$checkboxes = [ '_whisk_simple_ingredients', '_whisk_simple_instructions', '_whisk_simple_notes' ];
		foreach ( $checkboxes as $field ) {
			update_post_meta( $id, $field, '1' );
		}
	}

	/**
	 * Get the time in minutes.
	 *
	 * @param mixed $time Time to get in minutes.
	 *
	 * @return int
	 */
	private function get_minutes_for_time( $time ) {
		if ( ! $time ) {
			return 0;
		}

		// Assume a number is minutes.
		if ( is_numeric( $time ) ) {
			return $time;
		}
		$now  = time();
		$time = $this->strtotime( $time, $now );

		return ( $time - $now ) / 60;
	}

	/**
	 * Custom strtotime function for Tasty format.
	 *
	 * @param mixed $time Time to get in minutes.
	 * @param mixed $now Time now.
	 *
	 * @return int|false
	 */
	public static function strtotime( $time, $now = null ) {
		// TODO: this is not suitable for localization and should be reworked.
		if ( null === $now ) {
			$now = time();
		}
		// Parse string to remove any info in parentheses.
		$time = preg_replace( '/\([^)]+\)/', '', $time );

		return strtotime( $time );
	}
}
