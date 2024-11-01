<?php
/**
 * Rating Model.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Utils;
use wpdb;
use stdClass;

/**
 * Class Rating
 *
 * @package whisk-recipes
 */
class Rating {

	/**
	 * Table for ratings.
	 *
	 * @var string $table_name
	 */
	private $table_name;

	/**
	 * Instance of wpdb.
	 *
	 * @var wpdb $wpdb
	 */
	private $wpdb;

	/**
	 * Rating constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb       = $wpdb;
		$this->table_name = $this->wpdb->prefix . 'whisk_ratings';
	}

	/**
	 * Init hooks.
	 */
	public function setup_hooks() {
		add_action( 'whisk_recipes_rating', array( $this, 'get_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 101 );
		add_action( 'wp_ajax_whisk_recipes_rating', array( $this, 'set_rating' ) );
		add_action( 'wp_ajax_nopriv_whisk_recipes_rating', array( $this, 'set_rating' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {

		$recipe_id = get_the_ID();

		$object = array(
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'recipe_' . $recipe_id ),
			'recipe_id' => $recipe_id,
			'permalink' => get_permalink( $recipe_id ),
		);

		wp_localize_script( Utils::get_plugin_prefix(), Utils::get_plugin_slug(), $object );
	}

	/**
	 * Get rating for recipe.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return string|null
	 */
	public function get_rating( $recipe_id ) {
		global $wpdb;

		$sql = "SELECT ROUND( AVG( rating ), 1 ) AS total
				FROM {$this->table_name}
				WHERE recipe_id=%d";

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare( $sql, $recipe_id );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->get_var( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		return absint( $result );
	}

	/**
	 * Get number of votes for recipe.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return int
	 */
	public function get_votes( $recipe_id ) {

		$sql = "SELECT COUNT(rating) AS total
				FROM {$this->table_name}
				WHERE recipe_id=%d";

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$sql    = $this->wpdb->prepare( $sql, $recipe_id );
		$result = $this->wpdb->get_var( $sql );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		return absint( $result );
	}

	/**
	 * Render rating form.
	 *
	 * @param int $recipe_id Recipe ID.
	 */
	public function get_form( $recipe_id ) {
		$votes  = $this->get_votes( $recipe_id );
		$rating = $this->get_rating( $recipe_id );
		?>
		<form>
			<fieldset class="whisk-rating">
				<legend class="whisk-rating__caption">Rating</legend>
				<div class="whisk-rating__stars">
					<input type="hidden" class="whisk-rating__nonce" value="<?php echo esc_attr( wp_create_nonce( 'recipe_' . $recipe_id ) ); ?>">
					<input data-recipe-id="<?php echo absint( $recipe_id ); ?>" id="whisk-rating-<?php echo absint( $recipe_id ); ?>-1" class="whisk-rating__input" type="radio" name="whisk-rating" value="1" <?php checked( $rating, 1 ); ?> />
					<label for="whisk-rating-<?php echo absint( $recipe_id ); ?>-1" class="whisk-rating__star" aria-label="1"></label>

					<input data-recipe-id="<?php echo absint( $recipe_id ); ?>" id="whisk-rating-<?php echo absint( $recipe_id ); ?>-2" class="whisk-rating__input" type="radio" name="whisk-rating" value="2" <?php checked( $rating, 2 ); ?> />
					<label for="whisk-rating-<?php echo absint( $recipe_id ); ?>-2" class="whisk-rating__star" aria-label="2"></label>

					<input data-recipe-id="<?php echo absint( $recipe_id ); ?>" id="whisk-rating-<?php echo absint( $recipe_id ); ?>-3" class="whisk-rating__input" type="radio" name="whisk-rating" value="3" <?php checked( $rating, 3 ); ?> />
					<label for="whisk-rating-<?php echo absint( $recipe_id ); ?>-3" class="whisk-rating__star" aria-label="3"></label>

					<input data-recipe-id="<?php echo absint( $recipe_id ); ?>" id="whisk-rating-<?php echo absint( $recipe_id ); ?>-4" class="whisk-rating__input" type="radio" name="whisk-rating" value="4" <?php checked( $rating, 4 ); ?> />
					<label for="whisk-rating-<?php echo absint( $recipe_id ); ?>-4" class="whisk-rating__star" aria-label="4"></label>

					<input data-recipe-id="<?php echo absint( $recipe_id ); ?>" id="whisk-rating-<?php echo absint( $recipe_id ); ?>-5" class="whisk-rating__input" type="radio" name="whisk-rating" value="5" <?php checked( $rating, 5 ); ?> />
					<label for="whisk-rating-<?php echo absint( $recipe_id ); ?>-5" class="whisk-rating__star" aria-label="5"></label>
				</div>
				<div class="whisk-rating__results"><span class="whisk-rating__value"><?php echo absint( $rating ); ?></span> <span class="whisk-muted">from <span class="whisk-rating__votes"><?php echo absint( $votes ); ?></span> votes</span></div>
			</fieldset>
		</form>
		<?php
	}

	/**
	 * Check if rating all ready exist.
	 *
	 * @param int $recipe_id Recipe ID.
	 *
	 * @return int
	 */
	public function rating_exist( $recipe_id ) {
		global $wpdb;

		$recipe_id  = absint( $recipe_id );
		$user_id    = get_current_user_id();
		$user_ip    = Utils::get_user_ip();
		$user_agent = Utils::get_user_agent();

		$return = 0;

		if ( $recipe_id ) {
			if ( $user_id ) {
				$sql = $wpdb->prepare(
						"SELECT COUNT(*) AS total
					FROM {$this->table_name}
					WHERE
						recipe_id = %d AND
				        user_id = %d
				    ",
					$recipe_id,
					$user_id
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT COUNT(*) AS total
					FROM {$this->table_name}
					WHERE
						recipe_id = %d AND
				        user_id = %d AND
				        ip = %s AND
				        user_agent = %s
				    ",
					$recipe_id,
					$user_id,
					$user_ip,
					$user_agent
				);
			}

			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			return absint( $wpdb->get_var( $sql ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		return $return;

	}

	/**
	 * Set rating.
	 */
	public function set_rating() {
		global $wpdb;

		$user_id    = get_current_user_id();
		$recipe_id  = ( ! empty( $_REQUEST['recipe_id'] ) ) ? absint( $_REQUEST['recipe_id'] ) : 0;
		$rating     = ( ! empty( $_REQUEST['rating'] ) && $_REQUEST['rating'] > 0 && $_REQUEST['rating'] < 6 ) ? absint( $_REQUEST['rating'] ) : 0;
		$user_ip    = Utils::get_user_ip();
		$user_agent = Utils::get_user_agent();

		if ( is_user_logged_in() ) {
			check_ajax_referer( 'recipe_' . $recipe_id );
		}

		if ( $recipe_id && $rating ) {

			$model = array();

			$model['recipe_id'] = $recipe_id;
			$model['user_id']   = $user_id;

			if ( $this->rating_exist( $recipe_id ) ) {

				if ( ! $user_id ) {
					$model['ip']         = $user_ip;
					$model['user_agent'] = $user_agent;
				}

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				$wpdb->delete( $this->table_name, $model );
				// phpcs:enable WordPress.DB.DirectDatabaseQuery
			} else {
				$model['ip']         = $user_ip;
				$model['rating']     = $rating;
				$model['user_agent'] = $user_agent;

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				$result = $wpdb->insert( $this->table_name, $model );
				// phpcs:enable WordPress.DB.DirectDatabaseQuery
			}

			$votes  = $this->get_votes( $recipe_id );
			$rating = $this->get_rating( $recipe_id );

			wp_send_json_success(
				array(
					'votes'  => $votes,
					'rating' => $rating,
					'model'  => $model,
				)
			);
		} else {
			wp_send_json_error( 'Empty' );
		}
	}
}
