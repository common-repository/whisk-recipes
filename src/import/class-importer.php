<?php
namespace Whisk\Recipes\Import;

/**
 * Class Importer
 *
 * @package whisk-recipes
 */
abstract class Importer {
	/**
	 * Post type of imported cpt
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Count recipes for import
	 *
	 * @return int
	 */
	public function count_posts() {
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => 'imported',
			'meta_compare'   => 'NOT EXISTS',
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

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", $this->post_type );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Updates multiselect terms field with terms attached to post
	 *
	 * @param int    $post_id id of post in question.
	 * @param string $tax taxonomy to get terms.
	 * @param string $field carbon field name.
	 */
	protected function set_tags_fields( $post_id, $tax, $field ) {
		$terms = get_the_terms( $post_id, $tax );
		if ( ! $terms ) {
			return;
		}
		$terms_ids = wp_list_pluck( $terms, 'term_id' );
		whisk_carbon_set_post_meta( $post_id, $field, $terms_ids );
	}

	/**
	 * Create Whisks post and fill in data.
	 *
	 * @param int $import_id id of post being imported.
	 */
	abstract public function process_recipe( $import_id );

	/**
	 * Converts minutes to days-hours-minutes
	 *
	 * @param int $input_minutes input.
	 *
	 * @return int[]
	 */
	protected function minutes_to_time( $input_minutes ) {
		$minutes_in_an_hour = 60;
		$minutes_in_a_day   = 24 * $minutes_in_an_hour;

		// Extract days.
		$days = floor( $input_minutes / $minutes_in_a_day );

		// Extract hours.
		$hours = floor( $input_minutes / $minutes_in_an_hour );

		// Extract the remaining seconds.
		$remaining_minutes = $input_minutes % $minutes_in_an_hour;
		$minutes           = ceil( $remaining_minutes );

		// Format and return.
		return [
			'days'    => (int) $days,
			'hours'   => (int) $hours,
			'minutes' => (int) $minutes,
		];
	}
}
