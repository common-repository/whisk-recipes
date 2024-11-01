<?php
/**
 * Sync Model.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use wpdb;

/**
 * Class Sync
 *
 * @package whisk-recipes
 */
class Sync {
	/**
	 * Sync table name.
	 *
	 * @var string $table_name
	 */
	protected $table_name;

	/**
	 * Sync constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name = $wpdb->prefix . 'whisk_sync';
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name() {
		return $this->table_name;
	}

	/**
	 * Setup hooks.
	 */
	public function setup_hooks() {}

	/**
	 * Get Post ID from database via Recipe ID.
	 *
	 * @param string $recipe_id Recipe ID.
	 *
	 * @return array
	 */
	public function get_sync( $recipe_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->get_table_name()} WHERE recipe_id=%s",
				$recipe_id
			),
			ARRAY_A
		);
	}

	/**
	 * Get sync row from database via Post ID.
	 *
	 * @param int $post_id Recipe ID.
	 *
	 * @return string
	 */
	public function get_recipe_id( $post_id ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT recipe_id FROM {$this->get_table_name()} WHERE post_id=%d",
				$post_id
			)
		);
	}

	/**
	 * Create sync record for recipe.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $recipe_id Recipe ID.
	 * @param int    $sync_at   Last sync date.
	 * @param string $status    Status of sync.
	 *
	 * @return int
	 */
	public function create_sync( $post_id, $recipe_id, $sync_at, $status ) {
		global $wpdb;

		return (int) $wpdb->insert(
			$this->get_table_name(),
			array(
				'recipe_id' => $recipe_id,
				'post_id'   => $post_id,
				'sync_date' => $sync_at,
				'status'    => $status,
			)
		);
	}

	/**
	 * Create sync record for recipe.
	 *
	 * @param int    $post_id   Post ID.
	 * @param int    $sync_at   Last sync date.
	 * @param string $status    Status of sync.
	 *
	 * @return int
	 */
	public function update_sync( $post_id, $sync_at, $status ) {
		global $wpdb;

		$data = array();

		if ( ! empty( $sync_at ) ) {
			$data['sync_date'] = $sync_at;
		}

		if ( ! empty( $status ) ) {
			$data['status'] = $status;
		}

		return (int) $wpdb->update(
			$this->get_table_name(),
			$data,
			array(
				'post_id' => $post_id,
			)
		);
	}

	/**
	 * Disable post synchronization.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $recipe_id Recipe ID.
	 */
	public function disable_sync( $post_id, $recipe_id ) {
		global $wpdb;

		// Mark recipe as deleted.
		$wpdb->update(
			$this->get_table_name(),
			array(
				'status' => 'deleted',
			),
			array(
				'post_id' => $post_id,
			)
		);

		// Mark post as draft.
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_status' => 'draft',
			),
			array(
				'ID' => $post_id,
			)
		);
	}

	/**
	 * Delete post synchronization.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_sync( $post_id ) {
		global $wpdb;

		$wpdb->delete(
			$this->get_table_name(),
			array(
				'post_id' => $post_id,
			)
		);
	}
}
