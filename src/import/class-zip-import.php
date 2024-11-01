<?php
namespace Whisk\Recipes\Import;

use Whisk\Recipes\Models\Recipe;

/**
 * Class Zip_Import
 *
 * @package whisk-recipes
 */
class Zip_Import extends Importer {

	/**
	 * Zip recipes table name
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Wpdb instance
	 *
	 * @var \QM_DB|\wpdb
	 */
	private $wpdb;

	/**
	 * Zip_Import constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'amd_zlrecipe_recipes';
	}

	/**
	 * Count recipes for import
	 *
	 * @return int
	 */
	public function count_posts() {
		$all_posts_ids = $this->get_all_posts_ids();

		return $all_posts_ids ? count( $all_posts_ids ) : 0;
	}

	/**
	 * Select ids of posts for import
	 *
	 * @return array|null
	 */
	public function get_all_posts_ids() {
		// phpcs:disable WordPress.DB.PreparedSQL
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( $this->wpdb->get_var( "SHOW TABLES LIKE '{$this->table}'" ) !== $this->table ) {
			return null;
		}

		if ( ! $this->check_imported_col() ) {
			return $this->wpdb->get_results( "SELECT recipe_id FROM {$this->table}" );
		} else {
			return $this->wpdb->get_results( "SELECT recipe_id FROM {$this->table} WHERE imported = 0" );
		}
		// phpcs:enable WordPress.DB.PreparedSQL
		// phpcs:enable WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Get recipe with the specified ID as array.
	 *
	 * @param mixed $id ID of the recipe we want to import.
	 *
	 * @return array
	 */
	private function get_recipe( $id ) {
		$recipe = array(
			'import_id' => $id,
		);
		// phpcs:disable WordPress.DB.PreparedSQL
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$sql      = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE recipe_id=%s", $id );
		$z_recipe = $this->wpdb->get_row( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		// Featured Image, video.
		$recipe['image_id']  = $z_recipe->recipe_image_id;
		$recipe['video_url'] = $z_recipe->video_url;

		// Name, summary, notes.
		$recipe['name']    = $z_recipe->recipe_title;
		$recipe['summary'] = $z_recipe->summary;
		$recipe['notes']   = $z_recipe->notes;

		// Servings.
		$match = preg_match( '/^\s*\d+/', $z_recipe->yield, $servings_array );
		if ( 1 === $match ) {
			$servings = str_replace( ' ', '', $servings_array[0] );
		} else {
			$servings = '';
		}

		$servings_unit = preg_replace( '/^\s*\d+\s*/', '', $z_recipe->yield );

		$recipe['servings']      = $servings;
		$recipe['servings_unit'] = $servings_unit;

		// Recipe times in minutes.
		$recipe['prep_time'] = $z_recipe->prep_time ? $this->time_to_minutes( $z_recipe->prep_time ) : 0;
		$recipe['cook_time'] = $z_recipe->cook_time ? $this->time_to_minutes( $z_recipe->cook_time ) : 0;

		// Recipe tags.
		$ziplist_courses = isset( $z_recipe->category ) && $z_recipe->category ? $z_recipe->category : '';
		$raw_field       = str_replace( ';', ',', $ziplist_courses );
		$courses         = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $raw_field );
		$recipe['tags']  = '' === $courses[0] ? [] : $courses;

		$ziplist_cuisines  = isset( $z_recipe->cuisine ) && $z_recipe->cuisine ? $z_recipe->cuisine : '';
		$raw_field         = str_replace( ';', ',', $ziplist_cuisines );
		$cuisines          = preg_split( '/[\s*,\s*]*,+[\s*,\s*]*/', $raw_field );
		$recipe['cuisine'] = '' === $cuisines[0] ? [] : $cuisines;

		// Ingredients.
		$recipe['ingredients'] = $this->process_multiline( $z_recipe->ingredients );

		// Instructions.
		$recipe['instructions'] = $this->process_multiline( $z_recipe->instructions );

		// Serving size.
		$recipe['serving_size'] = $z_recipe->serving_size;

		// Nutrition.
		$recipe['nutrition'] = [];
		$nutrition_mapping   = [
			'calories'      => 'whisk_enerc_kcal',
			'carbs'         => 'whisk_chocdf',
			'protein'       => 'whisk_procnt',
			'fat'           => 'whisk_fat',
			'saturated_fat' => 'whisk_fasat',
			'trans_fat'     => 'whisk_fatrn',
			'cholesterol'   => 'whisk_chole',
			'sodium'        => 'whisk_na',
			'fiber'         => 'whisk_fibtg',
			'sugar'         => 'whisk_sugar',
			'vitamin_a'     => 'whisk_vita_rae',
			'vitamin_c'     => 'whisk_vitc',
			'iron'          => 'whisk_fe',
			'calcium'       => 'whisk_ca',
		];
		foreach ( $nutrition_mapping as $z_field => $whisk_field ) {
			$value                               = $z_recipe->$z_field;
			$recipe['nutrition'][ $whisk_field ] = filter_var(
				$value,
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
		$post   = array(
			'post_type'    => Recipe::get_cpt_name(),
			'post_status'  => 'publish',
			'post_title'   => wp_strip_all_tags( $recipe['name'] ),
			'post_content' => html_entity_decode( wp_kses_post( $recipe['summary'] ) ),
		);
		$id     = wp_insert_post( $post );

		// Post thumbnail.
		if ( isset( $recipe['image_id'] ) && $recipe['image_id'] ) {
			set_post_thumbnail( $id, $recipe['image_id'] );
		}

		// Tags.
		wp_set_post_terms( $id, $recipe['tags'], 'whisk_tag', true );
		$this->set_tags_fields( $id, 'whisk_tag', 'whisk_tags' );
		wp_set_post_terms( $id, $recipe['cuisine'], 'whisk_cuisine', true );
		$this->set_tags_fields( $id, 'whisk_cuisine', 'whisk_cuisines' );

		// Simple meta fields.
		update_post_meta( $id, '_whisk_servings', intval( $recipe['servings'] ) );
		update_post_meta( $id, '_whisk_imported_id', intval( $recipe['import_id'] ) );
		update_post_meta( $id, '_whisk_video_url', esc_url( $recipe['video_url'] ) );

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
		if ( ! $this->check_imported_col() ) {
			$this->create_imported_col();
		}

		$this->wpdb->update(
			$this->table,
			array( 'imported' => 1 ),
			array( 'recipe_id' => $import_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Check if imported column is present in zip recipes table
	 *
	 * @return bool
	 */
	private function check_imported_col() {
		// phpcs:disable WordPress.DB.PreparedSQL
		if ( $this->table !== $this->wpdb->get_var( "SHOW TABLES LIKE '$this->table'" ) ) {
			return false;
		}

		if ( ! $this->wpdb->get_results(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$this->table}' AND column_name = 'imported'"
		) ) {
			return false;
		}

		return true;
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 *  Create imported column in zip recipes table
	 */
	private function create_imported_col() {
		// phpcs:disable WordPress.DB.PreparedSQL
		$this->wpdb->query(
			"ALTER TABLE {$this->table} ADD COLUMN `imported` TINYINT(1) DEFAULT 0"
		);
		// phpcs:enable WordPress.DB.PreparedSQL
	}

	/**
	 * Convert time metadata to minutes.
	 *
	 * @param mixed $duration Time to convert.
	 *
	 * @return int
	 */
	private function time_to_minutes( $duration = 'PT' ) {
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

	/**
	 * Converts special Zip Recipes Markup to html.
	 *
	 * @param string $text String with Zip Recipes special markup.
	 *
	 * @return string
	 */
	private function process_multiline( $text ) {
		$array = preg_split( '/$\R?^/m', $text, -1, PREG_SPLIT_NO_EMPTY );
		$html  = '';
		if ( is_array( $array ) ) {
			$ul = false;
			foreach ( $array as $line ) {
				$line = trim( str_replace( array( "\n", "\t", "\r" ), '', $line ) );
				if ( '!' === substr( $line, 0, 1 ) ) {
					$line = ltrim( $line, '!' );
					$line = "<h3 class='whisk-h3'>{$line}</h3>";
					if ( $ul ) {
						$html .= '</ul>';
						$ul    = false;
					}
					$html .= $line;
					continue;
				}
				if ( '%' === substr( $line, 0, 1 ) ) {
					$line          = ltrim( $line, '%' );
					$attachment_id = attachment_url_to_postid( $line );
					if ( $attachment_id ) {
						if ( $ul ) {
							$html .= '</ul>';
							$ul    = false;
						}
						$html .= sprintf(
							'<img class="whisk-image" src="%s" srcset="%s">',
							wp_get_attachment_image_url( $attachment_id, 'full' ),
							wp_get_attachment_image_srcset( $attachment_id, 'full' )
						);
						continue;
					}
				}
				$line = preg_replace( '/(^|\s)\*([^\s\*][^\*]*[^\s\*]|[^\s\*])\*(\W|$)/', '\\1<strong>\\2</strong>\\3', $line );
				$line = preg_replace( '/(^|\s)_([^\s_][^_]*[^\s_]|[^\s_])_(\W|$)/', '\\1<em>\\2</em>\\3', $line );
				$line = preg_replace( '/\[([^\]\|\[]*)\|([^\]\|\[]*)\]/', '<a href="\\2" target="_blank">\\1</a>', $line );
				if ( ! $ul ) {
					$html .= '<ul>';
					$ul    = true;
				}
				$html .= "<li>{$line}</li>";
			}
		}
		if ( $ul ) {
			$html .= '</ul>';
		}
		return $html;
	}
}
