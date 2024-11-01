<?php
/**
 * Recipe Schema.
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes\Models;

use Whisk\Recipes\Models\Recipe;
use WP_Post;

/**
 * Class Schema
 */
class Schema {
	/**
	 * Recipe instance.
	 *
	 * @var Recipe $recipe
	 */
	private $recipe;

	/**
	 * Rating instance.
	 *
	 * @var Rating $rating
	 */
	private $rating;

	/**
	 * Schema constructor.
	 *
	 * @param Recipe $recipe Recipe instance.
	 * @param Rating $rating Rating instance.
	 */
	public function __construct( Recipe $recipe, Rating $rating ) {
		$this->recipe = $recipe;
		$this->rating = $rating;
	}

	/**
	 * Setup recipe hooks.
	 */
	public function setup_hooks() {
		add_action( 'carbon_fields_fields_registered', array( $this, 'carbon_fields_registered' ) );
	}

	/**
	 * Enable schema.org microdata.
	 */
	public function carbon_fields_registered() {
		if ( whisk_carbon_get_theme_option( 'whisk_schema_enabled' ) ) {
			add_action( 'wp_head', array( $this, 'add_scheme' ) );
		}
	}

	/**
	 * Add scheme to page.
	 *
	 * @link https://www.php.net/manual/ru/json.constants.php
	 */
	public function add_scheme() {
		$scheme = array();

		if ( is_singular( Recipe::get_cpt_name() ) ) {
			$scheme = $this->get_single_scheme();
		} elseif ( is_post_type_archive( Recipe::get_cpt_name() ) ) {
			$scheme = $this->get_archive_scheme();
		}

		if ( $scheme ) {
			?>
			<script type="application/ld+json">
			<?php echo wp_json_encode( $scheme, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ); ?>
			</script>
			<?php
		}
	}

	/**
	 * Convert ingredients to scheme.
	 *
	 * @param array $ingredients Ingredients.
	 *
	 * @return array
	 */
	public function convert_ingredients_to_scheme( $ingredients ) {
		$results = array();

		foreach ( $ingredients as $ingredient ) {
			$result = sprintf(
				'%s %s %s',
				( $ingredient['whisk_ingredient_amount'] > 0 ) ? $ingredient['whisk_ingredient_amount'] : 1,
				$ingredient['whisk_ingredient_unit'],
				isset( $ingredient['whisk_ingredient_id'][0] ) ? get_term( $ingredient['whisk_ingredient_id'][0] )->name : ''
			);

			$results[] = str_replace( '  ', ' ', $result );
		}

		return $results;
	}

	/**
	 * Convert instructions to scheme.
	 *
	 * @param array $instructions Instructions.
	 *
	 * @return array
	 */
	public function convert_instructions_to_scheme( $instructions ) {
		$results = array();

		foreach ( $instructions as $instruction ) {

			if ( isset( $instruction['whisk_step_separator'] ) ) {
				continue;
			}

			if ( ! isset( $instruction['whisk_step_summary'] ) ) {
				continue;
			}

			$results[] = array(
				'@type' => 'HowToStep',
				'name'  => $instruction['whisk_step_summary'],
				'text'  => wp_strip_all_tags( $instruction['whisk_step_instruction'] ),
				'image' => ! empty( $instruction['whisk_step_image'] )
					? wp_get_attachment_image_src( $instruction['whisk_step_image'] )[0]
					: '',
			);
		}

		return $results;
	}

	/**
	 * Convert time for scheme.
	 *
	 * @param int $seconds Time in seconds.
	 *
	 * @return string
	 */
	public function convert_time_to_scheme( $seconds ) {
		$days     = floor( $seconds / DAY_IN_SECONDS );
		$seconds %= DAY_IN_SECONDS;

		$hours    = floor( $seconds / HOUR_IN_SECONDS );
		$seconds %= HOUR_IN_SECONDS;

		$minutes  = floor( $seconds / MINUTE_IN_SECONDS );
		$seconds %= MINUTE_IN_SECONDS;

		return sprintf( 'P%dDT%dH%dM%dS', $days, $hours, $minutes, $seconds );
	}

	/**
	 * Convert array to scheme.
	 *
	 * @param array $items Array of items.
	 *
	 * @return string
	 */
	public function convert_array_to_scheme( $items ) {
		return implode( ', ', $items );
	}

	/**
	 * Register Post Taxonomy.
	 *
	 * @link https://schema.org/Recipe
	 * @link https://developers.google.com/search/docs/data-types/recipe
	 */
	public function get_single_scheme() {
		$post     = (array) get_post();
		$calories = Recipe::get_calories( $post['ID'] );
		$votes    = $this->rating->get_votes( $post['ID'] );
		$rating   = $this->rating->get_rating( $post['ID'] );

		if ( 0 === $votes || 0 === $rating ) {
			$votes  = 1;
			$rating = 5;
		}

		$result = array(
			'@context'             => 'http://schema.org',
			'@type'                => 'Recipe',
			'author'               => array(
				'@type' => 'Person',
				'name'  => Recipe::get_author_name( $post['post_author'] ),
			),
			'name'                 => $this->recipe->get_name( $post['ID'] ),
			'datePublished'        => $this->recipe->get_date_published( $post['ID'] ),
			'dateModified'         => $this->recipe->get_date_modified( $post['ID'] ),
			'description'          => Recipe::get_recipe_excerpt( $post['ID'] ),
			'image'                => Recipe::get_recipe_thumbnail_url( $post['ID'], Recipe::IMAGE_SIZE_LARGE ),
			'cookTime'             => $this->convert_time_to_scheme(
				Recipe::get_cook_time( $post['ID'] )
			),
			'prepTime'             => $this->convert_time_to_scheme(
				Recipe::get_prep_time( $post['ID'] )
			),
			'keywords'             => $this->convert_array_to_scheme(
				$this->recipe->get_keywords( $post['ID'] )
			),
			'recipeCuisine'        => $this->convert_array_to_scheme(
				$this->recipe->get_recipe_cuisine( $post['ID'] )
			),
			'recipeCategory'       => $this->convert_array_to_scheme(
				$this->recipe->get_recipe_category( $post['ID'] )
			),
			'recipeIngredient'     => $this->convert_ingredients_to_scheme(
				$this->recipe->get_ingredients( $post['ID'] )
			),
			'interactionStatistic' => array(
				'@type'                => 'InteractionCounter',
				'interactionType'      => 'http://schema.org/Comment',
				'userInteractionCount' => '0',
			),
			'recipeInstructions'   => $this->convert_instructions_to_scheme(
				$this->recipe->get_recipe_instructions( $post['ID'] )
			),
			'recipeYield'          => Recipe::get_recipe_yield( $post['ID'] ),
			'suitableForDiet'      => 'http://schema.org/LowFatDiet',
		);

		// aggregateRating.
		if ( $rating && $votes ) {
			$result['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $rating,
				'reviewCount' => $votes,
			);
		}

		// Calories.
		if ( $calories ) {
			$result['nutrition'] = array(
				'@type'    => 'NutritionInformation',
				'calories' => $calories,
				//'fatContent' => '',
			);
		}

		return $result;
	}

	/**
	 * Get archive scheme.
	 *
	 * @return array
	 *
	 * @link https://developers.google.com/search/docs/data-types/recipe?hl=ru#item-list
	 */
	public function get_archive_scheme() {
		global $posts;

		if ( ! is_array( $posts ) ) {
			return array();
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'itemListElement' => array_map(
				static function ( WP_Post $post ) {
					return array(
						'@type'    => 'ListItem',
						'position' => (int) $post->ID,
						'url'      => esc_url( get_permalink( $post->ID ) ),
					);
				},
				$posts
			),
		);
	}
}
