<?php
/**
 * Shortcode
 *
 * @package whisk-recipes
 */

namespace Whisk\Recipes;

use Whisk\Recipes\Models\Recipe;
use Whisk\Recipes\Models\Ingredient;

/**
 * Class Shortcode
 *
 * @package whisk-recipes
 */
class Shortcode {
	const NAME = 'whisk-recipe';
	const NUTRITION = 'whisk-nutrition';
	const HEALTH_SCORE = 'whisk-health-score';
	const GLYCEMIC_INDEX = 'whisk-glycemic-index';
	const INGREDIENTS = 'whisk-ingredients';
	const INSTRUCTIONS = 'whisk-instructions';

	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
	}

	/**
	 * Hooks setup.
	 */
	public function setup_hooks() {
		add_shortcode( self::NAME, array ( $this, 'register_recipe_shortcode' ) );
		add_shortcode( self::NUTRITION, array ( $this, 'register_nutrition_shortcode' ) );
		add_shortcode( self::HEALTH_SCORE, array ( $this, 'register_health_score_shortcode' ) );
		add_shortcode( self::GLYCEMIC_INDEX, array ( $this, 'register_glycemic_index_shortcode' ) );
		add_shortcode( self::INGREDIENTS, array ( $this, 'register_ingredients_shortcode' ) );
		add_shortcode( self::INSTRUCTIONS, array ( $this, 'register_instructions_shortcode' ) );

		add_action( 'whisk_recipe', array ( $this, 'callback_recipe' ) );
		add_action( 'whisk_nutrition', array ( $this, 'callback_nutrition' ) );
		add_action( 'whisk_health_score', array ( $this, 'callback_health_score' ) );
		add_action( 'whisk_glycemic_index', array ( $this, 'callback_glycemic_index' ) );
		add_action( 'whisk_ingredients', array ( $this, 'callback_ingredients' ) );
		add_action( 'whisk_instructions', array ( $this, 'callback_instructions' ) );
	}

	/**
	 * Add shortcode.
	 *
	 * @param array $attrs
	 * @param       $content
	 * @param       $tag
	 *
	 * @return string
	 */
	public function register_recipe_shortcode( $attrs, $content, $tag ) {
		$attrs = shortcode_atts(
			array (
				'id'       => 0,
				'comments' => 'no',
				'author'   => 'no',
				'share'    => 'no',
				'embedded' => 'yes',
			),
			$attrs
		);

		return Utils::get_template_parts( 'template-parts/frontend/recipe', null, $attrs );
	}

	/**
	 * Register shortcode for recipe nutrition block.
	 *
	 * @param array  $attrs   Array of shortcode attribute.
	 * @param string $content Shortcode content.
	 * @param static $tag     Shortcode name.
	 *
	 * @return string|void
	 */
	public function register_nutrition_shortcode( $attrs, $content, $tag ) {
		$recipe_id = ! empty( $attrs['id'] ) ? absint( $attrs['id'] ) : 0;

		if ( ! $recipe_id ) {
			return;
		}

		return $this->callback_nutrition( $recipe_id, $attrs, false );
	}

	/**
	 * Register shortcode for recipe health score block.
	 *
	 * @param array  $attrs   Array of shortcode attribute.
	 * @param string $content Shortcode content.
	 * @param static $tag     Shortcode name.
	 *
	 * @return string|void
	 */
	public function register_health_score_shortcode( $attrs, $content, $tag ) {
		$recipe_id = ! empty( $attrs['id'] ) ? absint( $attrs['id'] ) : 0;

		if ( ! $recipe_id ) {
			return;
		}

		return $this->callback_health_score( $recipe_id, $attrs, false );
	}

	/**
	 * Register shortcode for recipe glycemic index block.
	 *
	 * @param array  $attrs   Array of shortcode attribute.
	 * @param string $content Shortcode content.
	 * @param static $tag     Shortcode name.
	 *
	 * @return string|void
	 */
	public function register_glycemic_index_shortcode( $attrs, $content, $tag ) {
		$recipe_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : 0;

		if ( ! $recipe_id ) {
			return;
		}

		return $this->callback_glycemic_index( $recipe_id, $attrs, false );
	}

	/**
	 * Register shortcode for recipe ingredients block.
	 *
	 * @param array  $attrs   Array of shortcode attribute.
	 * @param string $content Shortcode content.
	 * @param static $tag     Shortcode name.
	 *
	 * @return string|void
	 */
	public function register_ingredients_shortcode( $attrs, $content, $tag ) {
		$recipe_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : 0;

		if ( ! $recipe_id ) {
			return;
		}

		return $this->callback_ingredients( $recipe_id, $attrs, false );
	}

	/**
	 * Register shortcode for recipe instructions block.
	 *
	 * @param array  $attrs   Array of shortcode attribute.
	 * @param string $content Shortcode content.
	 * @param static $tag     Shortcode name.
	 *
	 * @return string|void
	 */
	public function register_instructions_shortcode( $attrs, $content, $tag ) {
		$recipe_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : 0;

		if ( ! $recipe_id ) {
			return;
		}

		return $this->callback_instructions( $recipe_id, $attrs, false );
	}

	/**
	 * Callback for recipe shortcode.
	 *
	 * @param int  $recipe_id Recipe ID.
	 * @param bool $echo      Disaply or return.
	 *
	 * @return false|string
	 */
	public function callback_recipe( $recipe_id, $echo = true ) {

		if ( ! $echo ) {
			return 'callback_nutrition_' . $recipe_id;
		}

		echo 'callback_nutrition_' . $recipe_id;
	}

	/**
	 * Callback for nutrition shortcode.
	 *
	 * @param int   $recipe_id  Recipe ID.
	 * @param array $attributes Array of attributes.
	 * @param bool  $echo       Disaply or return.
	 *
	 * @return false|string
	 */
	public function callback_nutrition( $recipe_id, $attributes = [], $echo = true ) {

		ob_start();

		$attributes = shortcode_atts(
			array (
				'header' => __( 'Nutrition per serving', 'whisk-recipes' ),
				'footer' => __( '* Percent Daily Values based on a 2,000 calorie diet.', 'whisk-recipes' ),
				'link'   => __( 'View all nutrients', 'whisk-recipes' ),
			),
			$attributes
		);

		$attributes = apply_filters( 'whisk_recipes_nutrition_shortcode_attributes', $attributes, $recipe_id );

		$needed = [
			'whisk_enerc_kcal',
			'whisk_fat',
			'whisk_chocdf',
			'whisk_sugar',
			'whisk_procnt',
			'whisk_na',
			'whisk_fibtg',
		];

		$nutrition = Recipe::get_recipe_nutrition( $recipe_id );

		?>
		<div class="whisk-shortcode whisk-shortcode--nutrition">
			<h2 class="whisk-h2"><?php echo esc_html( $attributes['header'] ); ?></h2>
			<table class="whisk-table whisk-table--stripe whisk-table--small">
				<?php foreach ( $needed as $nutrition_id ) : ?>
					<?php
					if ( ! isset( $nutrition[ $nutrition_id ] ) ) {
						continue;
					}

					$item = $nutrition[ $nutrition_id ];
					?>
					<tr>
						<th><?php echo esc_html( $item['title'] ); ?></th>
						<td style="white-space: nowrap"><?php echo esc_html( $item['value'] ); ?></td>
						<td class="whisk-muted"><?php echo esc_html( $item['daily'] ); ?>%</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p class="whisk-muted"><?php echo esc_html( $attributes['footer'] ); ?> <a data-width="800" data-toggle="modal" href="#whisk-nutrition-per-serving" class="whisk-link"><?php echo esc_html( $attributes['link'] ); ?></a></p>
		</div>
		<div class="whisk-template">
			<div class="whisk-modal" id="whisk-nutrition-per-serving">
				<div class="modal2-close"></div>
				<h2 class="whisk-h2"><?php echo esc_html( $attributes['header'] ); ?></h2>
				<?php
				$nutrition_total = count( $nutrition );
				$nutrition_left  = array_slice( $nutrition, 0, $nutrition_total / 2 );
				$nutrition_right = array_slice( $nutrition, $nutrition_total / 2 );
				?>
				<div class="whisk-row">
					<?php if ( $nutrition_left ) : ?>
						<div class="whisk-column whisk-column-50">
							<p class="whisk-muted">Amount per serving</p>
							<table cellpadding="5" cellspacing="0" class="whisk-table whisk-table--stripe">
								<?php foreach ( $nutrition_left as $item ) : ?>
									<tr>
										<th><?php echo esc_html( $item['title'] ); ?></th>
										<td style="white-space: nowrap"><?php echo esc_html( $item['value'] ); ?></td>
										<td class="whisk-muted"><?php echo esc_html( $item['daily'] ); ?>%</td>
									</tr>
								<?php endforeach; ?>
							</table>
						</div>
					<?php endif; ?>
					<?php if ( $nutrition_right ) : ?>
						<div class="whisk-column whisk-column-50">
							<p class="whisk-muted">% Daily Value*</p>
							<table cellpadding="5" cellspacing="0" class="whisk-table whisk-table--stripe">
								<?php foreach ( $nutrition_right as $item ) : ?>
									<tr>
										<th><?php echo esc_html( $item['title'] ); ?></th>
										<td style="white-space: nowrap"><?php echo esc_html( $item['value'] ); ?></td>
										<td class="whisk-muted"><?php echo esc_html( $item['daily'] ); ?>%</td>
									</tr>
								<?php endforeach; ?>
							</table>
						</div>
					<?php endif; ?>
				</div>
				<p class="whisk-muted"><?php echo esc_html( $attributes['footer'] ); ?></p>
			</div>
		</div>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Callback for health score shortcode.
	 *
	 * @param int   $recipe_id  Recipe ID.
	 * @param array $attributes Array of attributes.
	 * @param bool  $echo       Disaply or return.
	 *
	 * @return false|string
	 */
	public function callback_health_score( $recipe_id, $attributes = [], $echo = true ) {

		ob_start();

		$attributes = shortcode_atts(
			array (),
			$attributes
		);

		$attributes = apply_filters( 'whisk_recipes_health_score_shortcode_attributes', $attributes, $recipe_id );

		$health_score       = Recipe::get_health_score( $recipe_id );
		$health_score_color = Recipe::get_health_score_color( $health_score );
		$nutrition          = Recipe::get_recipe_nutrition( $recipe_id );
		?>
		<div class="whisk-shortcode whisk-shortcode--health-score">
			<h2 class="whisk-h2">Health score</h2>
			<div class="whisk-health-score__item">
				<div class="whisk-vprogress whisk-vprogress_<?php echo esc_attr( $health_score_color ); ?>">
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__item"></div>
					<div class="whisk-vprogress__value" data-height="<?php echo esc_attr( $health_score * 7 ); ?>"></div>
					<div class="whisk-vprogress__labels">
						<div class="whisk-vprogress__label whisk-vprogress__label_high">High</div>
						<div class="whisk-vprogress__label whisk-vprogress__label_medium">Medium</div>
						<div class="whisk-vprogress__label whisk-vprogress__label_low">Low</div>
					</div>
					<script>
					jQuery(function ($) {
						var $vprogress = $('.whisk-vprogress__value'),
							value = $vprogress.data('height');

						$vprogress.animate(
							{
								height: value
							},
							2500
						);
					});
					</script>
				</div>
				<div class="whisk-health-score__value whisk-health-score__value_<?php echo esc_attr( $health_score_color ); ?>">
					<mark><?php echo esc_html( round( $health_score, 1 ) ); ?></mark>
					/10
				</div>
				<div class="whisk-health-score__toggler" data-toggle="modal" data-target="#whisk-health-score">
					<svg viewBox="0 0 24 24">
						<path d="M11 8C11 7.44772 11.4477 7 12 7C12.5523 7 13 7.44772 13 8C13 8.55228 12.5523 9 12 9C11.4477 9 11 8.55228 11 8ZM11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12V16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16V12ZM12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z"></path>
					</svg>
				</div>
			</div>
		</div>
		<div class="whisk-template">
			<div class="whisk-modal" id="whisk-health-score">
				<div class="modal2-close"></div>
				<h2 class="whisk-h2">Health score</h2>
				<p>Health Scores are calculated on a 1-10 scale based on nutrient density and USDA recommendations for a healthy diet. A higher Health Score means healthier food. The value based on impact of more than 28 nutrients, here are the main ones:</p>
				<?php if ( $nutrition ) : ?>
					<p class="whisk-muted">Nutrients with positive impact</p>
					<table cellpadding="5" cellspacing="0" class="whisk-table whisk-table--stripe">
						<?php foreach ( $nutrition as $item ) : ?>
							<?php
							if ( empty( $item['influence'] ) || 'negative' === $item['impact'] ) {
								continue;
							}
							?>
							<tr>
								<td width="40%"><?php echo esc_html( $item['title'] ); ?> <span class="whisk-muted"><?php echo esc_html( $item['value'] ); ?></span></td>
								<td width="60%">
									<span class="whisk-progress whisk-progress_positive" style="width: <?php echo esc_html( $item['daily'] ); ?>%">&nbsp;</span> <?php echo esc_html( $item['daily'] ); ?>% DV
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				<?php endif; ?>
				<?php if ( $nutrition ) : ?>
					<p class="whisk-muted">Nutrients with negative impact</p>
					<table cellpadding="5" cellspacing="0" class="whisk-table whisk-table--stripe">
						<?php foreach ( $nutrition as $item ) : ?>
							<?php
							if ( empty( $item['influence'] ) || 'positive' === $item['impact'] ) {
								continue;
							}
							?>
							<tr>
								<td width="40%"><?php echo esc_html( $item['title'] ); ?> <span class="whisk-muted"><?php echo esc_html( $item['value'] ); ?></span></td>
								<td width="60%">
									<span class="whisk-progress whisk-progress_negative" style="width: <?php echo esc_html( $item['daily'] ); ?>%">&nbsp;</span> <?php echo esc_html( $item['daily'] ); ?>% DV
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php

		if ( ! $echo ) {
			return ob_get_clean();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Callback for glycemic index shortcode.
	 *
	 * @param int   $recipe_id  Recipe ID.
	 * @param array $attributes Array of attributes.
	 * @param bool  $echo       Disaply or return.
	 *
	 * @return false|string
	 */
	public function callback_glycemic_index( $recipe_id, $attributes = array (), $echo = true ) {

		$attributes = shortcode_atts(
			array (),
			$attributes
		);

		$attributes = apply_filters( 'whisk_recipes_glycemic_index_shortcode_attributes', $attributes, $recipe_id );

		$glycemic_index       = Recipe::get_glycemic_index( $recipe_id );
		$glycemic_index_label = Recipe::get_glycemic_index_label( $glycemic_index );
		$glycemic_load        = Recipe::get_glycemic_load( $recipe_id );
		$glycemic_load_label  = Recipe::get_glycemic_load_label( $glycemic_load );

		if ( ! $glycemic_load || ! $glycemic_index ) {
			return '';
		}

		ob_start();
		?>
		<div class="whisk-shortcode whisk-shortcode--glycemic-index">
			<div class="whisk-glycemic__item">
				<table>
					<tr>
						<td>Glycemic Index</td>
						<td>
							<span class="whisk-label whisk-label--<?php echo esc_html( strtolower( $glycemic_index_label ) ); ?>"><?php echo absint( round( $glycemic_index ) ); ?></span>
						</td>
						<th><?php echo esc_html( $glycemic_index_label ); ?></th>
					</tr>
					<tr>
						<td>Glycemic Load</td>
						<td>
							<span class="whisk-label whisk-label--<?php echo esc_html( strtolower( $glycemic_load_label ) ); ?>"><?php echo absint( round( $glycemic_load ) ); ?></span>
						</td>
						<th><?php echo esc_html( $glycemic_load_label ); ?></th>
					</tr>
				</table>
				<div class="whisk-glycemic__toggler" data-toggle="modal" data-target="#whisk-modal-glycemic-index">
					<svg viewBox="0 0 24 24">
						<path d="M11 8C11 7.44772 11.4477 7 12 7C12.5523 7 13 7.44772 13 8C13 8.55228 12.5523 9 12 9C11.4477 9 11 8.55228 11 8ZM11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12V16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16V12ZM12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z"></path>
					</svg>
				</div>
			</div>
		</div>
		<div class="whisk-template">
			<div class="whisk-modal" id="whisk-modal-glycemic-index">
				<div class="modal2-close"></div>
				<h2 class="whisk-h2">About Glycemic Index</h2>
				<p>The Glycemic Index <b>(GI)</b> measures how carbohydrates affect your blood glucose levels, helping you choose foods for good health. You need carbs as they break down into glucose and provide fuel for most organs, our brain, and muscles during exercise.</p>
				<p>High <b>GI</b> carbs cause blood glucose levels to spike and crash.</p>
				<p>Low <b>GI</b> carbs are digested and released slowly for sustained energy.</p>
				<p><img src="<?php echo esc_url( Utils::get_plugin_file_uri( 'assets/images/glycemic.png' ) ); ?>" width="340" alt=""></p>
				<p><b>Glycemic Load (GL)</b></p>
				<p>Glycemic Load combines the quantity and quality <b>(GI)</b> of carbohydrates providing a way to compare blood glucose values of different types and amounts of foods. How high your blood glucose rises and how long it stays high depends on GL. There are three classifications for GL: <b>Low</b> (10 or less), <b>Moderate</b> (11 – 19), and <b>High</b> (20 or more).</p>
			</div>
		</div>
		<?php

		if ( ! $echo ) {
			return ob_get_clean();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Callback for ingredients shortcode.
	 *
	 * @param int   $recipe_id  Recipe ID.
	 * @param array $attributes Array of attributes.
	 * @param bool  $echo       Disaply or return.
	 *
	 * @return false|string
	 */
	public function callback_ingredients( $recipe_id, $attributes = [], $echo = true ) {

		ob_start();

		$attributes = shortcode_atts(
			array (),
			$attributes
		);

		$attributes         = apply_filters( 'whisk_recipes_ingredients_shortcode_attributes', $attributes, $recipe_id );
		$simple_ingredients = whisk_carbon_get_post_meta( $recipe_id, 'whisk_simple_ingredients' );
		$ingredients        = whisk_carbon_get_post_meta( $recipe_id, 'whisk_ingredients' );
		$servings           = Recipe::get_recipe_yield( $recipe_id );

		if ( ! $simple_ingredients && ! $ingredients ) {
			return '';
		}
		?>
		<div class="whisk-shortcode whisk-shortcode--ingredients">
			<h2 class="whisk-h2">Ingredients</h2>
			<?php if ( $simple_ingredients ) : ?>
				<?php echo wp_kses_post( whisk_carbon_get_post_meta( $recipe_id, 'whisk_simple_ingredients_text' ) ); ?>
			<?php endif; ?>
			<?php if ( $ingredients && ! $simple_ingredients ) : ?>
				<!--dl class="whisk-tabs">
					<dt class="whisk-tabs__item whisk-tabs__item_selected" data-type="metrics">Metric</dt>
					<dt class="whisk-tabs__item" data-type="us">US</dt>
				</dl-->
				<div class="whisk-ingredients">
					<?php foreach ( $ingredients as $ingredient ) : ?>
						<?php
						$ingredient_image     = whisk_carbon_get_term_meta( $ingredient['whisk_ingredient_id'][0], 'whisk_ingredient_image' );
						$ingredient_term      = get_term( $ingredient['whisk_ingredient_id'][0] );
						$ingredient_image_src = wp_get_attachment_image_url( $ingredient_image, Ingredient::IMAGE_SIZE );
						$ingredient_amount    = $ingredient['whisk_ingredient_amount'];
						$ingredient_unit      = ( ! empty( $ingredient['whisk_ingredient_unit'] ) )
							? $ingredient['whisk_ingredient_unit']
							: '';
						?>
						<div class="whisk-ingredients__item">
							<div class="whisk-ingredients__photo" style="background-image: url(<?php echo esc_url( $ingredient_image_src ); ?>)"></div>
							<div class="whisk-ingredients__row">
								<div class="whisk-ingredients__cell">

									<?php if ( $ingredient_amount === '' || $ingredient_amount === 0 ) { ?>

										<span class="whisk-ingredients__unit" data-original-unit="<?php echo esc_html( $ingredient_unit ?: 'cup' ); ?>"><?php echo esc_html( $ingredient_unit ); ?></span>

									<?php } else if ( is_numeric( $ingredient_amount ) && $ingredient_amount > 0 ) { ?>

										<span class="whisk-ingredients__amount" data-original-amount="<?php echo esc_html( $ingredient_amount / $servings ); ?>"><?php echo esc_html( Utils::convert_decimal_to_html_entity( $ingredient_amount ) ); ?></span>
										<span class="whisk-ingredients__unit" data-original-unit="<?php echo esc_html( $ingredient_unit ?: 'cup' ); ?>"><?php echo esc_html( $ingredient_unit ); ?></span>

									<?php } else { ?>

										<span class="whisk-ingredients__amount" data-original-amount="<?php echo esc_html( $ingredient_amount ); ?>"><?php echo esc_html( $ingredient_amount ) ?></span>
										<span class="whisk-ingredients__unit" data-original-unit="<?php echo esc_html( $ingredient_unit ?: 'cup' ); ?>"><?php echo esc_html( $ingredient_unit ); ?></span>

									<?php } ?>

									<span class="whisk-ingredients__name"><?php echo esc_html( $ingredient_term->name ); ?></span>
									<span class="whisk-ingredients__note"><br><?php echo esc_html( $ingredient['whisk_ingredient_note'] ); ?></span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<form class="whisk-calculator">
					<?php $estimated_cost = whisk_carbon_get_post_meta( $recipe_id, 'whisk_ingredients_estimated_cost' ); ?>
					<div class="whisk-calculator__item whisk-calculator__item_scale">
						<input id="whisk-shopping-list-scale" class="whisk-input whisk-input_number" size="3" type="tel" data-original-scale="<?php echo (int) $servings; ?>" value="<?php echo (int) $servings; ?>" min="1" max="999"/>
					</div>
					<div class="whisk-calculator__item whisk-calculator__item_unit whisk-hid">
						<?php echo esc_html( Recipe::get_recipe_yield_label( $recipe_id ) ); ?>
					</div>
					<div class="whisk-calculator__item whisk-calculator__item_add">
						<button type="button" id="whisk-shopping-list-trigger" data-url="<?php echo esc_url( get_permalink( $recipe_id ) ); ?>" class="whisk-btn whisk-btn-primary whisk-btn-spin">Add to shopping list</button>
					</div>
					<div class="whisk-calculator__item whisk-calculator__item_view">
						<input type="button" id="whisk-shopping-list-view" class="whisk-btn whisk-btn-link" value="View list"/>
					</div>
				</form>
				<?php if ( 1 === 2 && $estimated_cost ) : ?>
					<div class="whisk-ingredients__cost">
						<span class="whisk-muted">Cost:</span> <b><?php echo esc_html( $estimated_cost ); ?></b>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php

		if ( ! $echo ) {
			return ob_get_clean();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Callback for instructions shortcode.
	 *
	 * @param int   $recipe_id  Recipe ID.
	 * @param array $attributes Array of attributes.
	 * @param bool  $echo       Disaply or return.
	 *
	 * @return false|string
	 */
	public function callback_instructions( $recipe_id, $attributes = [], $echo = true ) {
		global $wp_embed;

		ob_start();

		$attributes = shortcode_atts(
			array (),
			$attributes
		);

		$attributes          = apply_filters( 'whisk_recipes_instructions_shortcode_attributes', $attributes, $recipe_id );
		$simple_instructions = whisk_carbon_get_post_meta( $recipe_id, 'whisk_simple_instructions' );
		$instructions        = whisk_carbon_get_post_meta( $recipe_id, 'whisk_instructions' );

		if ( ! $simple_instructions && ! $instructions ) {
			return '';
		}
		?>
		<div class="whisk-shortcode whisk-shortcode--instructions">
			<?php if ( $simple_instructions ) : ?>
				<h2 class="whisk-h2">Instructions</h2>
				<div class="whisk-shortcode--instructions__item_">
					<?php echo wp_kses_post( whisk_carbon_get_post_meta( $recipe_id, 'whisk_simple_instructions_text' ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $instructions && ! $simple_instructions ) : ?>
				<?php $show_video_toggler = false; ?>
				<h2 class="whisk-h2">Instructions</h2>
				<?php foreach ( $instructions as $instruction ) : ?>
					<?php if ( 'separator' === $instruction['_type'] ) : ?>
						<h3 class="whisk-h3"><?php echo esc_html( $instruction['whisk_step_separator_name'] ); ?></h3>
					<?php else : ?>
						<div class="whisk-shortcode--instructions__item">
							<div class="whisk-shortcode--instructions__summary"><?php echo esc_html( $instruction['whisk_step_summary'] ); ?></div>
							<?php if ( $instruction['whisk_step_instruction'] ) : ?>
								<div class="whisk-shortcode--instructions__instruction"><?php echo wp_kses_post( $instruction['whisk_step_instruction'] ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $instruction['whisk_step_image'] ) ) : ?>
								<div class="whisk-shortcode--instructions__image">
									<a href="<?php echo esc_url( wp_get_attachment_image_url( $instruction['whisk_step_image'], 'full' ) ); ?>" target="_blank">
										<img src="<?php echo esc_url( wp_get_attachment_image_url( $instruction['whisk_step_image'], 'large' ) ); ?>" width="100%" alt="">
									</a>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $instruction['whisk_step_video_url'] ) ) : ?>
								<div class="whisk-shortcode--instructions__video whisk-hidden">
									<?php
									$show_video_toggler = true;
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $wp_embed->autoembed( $instruction['whisk_step_video_url'] );
									?>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $instruction['whisk_step_video'] ) ) : ?>
								<div class="whisk-shortcode--instructions__video whisk-hidden">
									<?php
									$show_video_toggler = true;
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo wp_video_shortcode(
										array (
											'src'    => wp_get_attachment_url( $instruction['whisk_step_video'] ),
											'width'  => 16,
											'height' => 9,
											'class'  => 'whisk-video',
										)
									);
									?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ( $show_video_toggler ) : ?>
					<dl class="whisk-tabs">
						<dt class="whisk-tabs__item whisk-tabs__item_disabled">Video</dt>
						<dt class="whisk-tabs__item" data-type="on" title="Video on">On</dt>
						<dt class="whisk-tabs__item whisk-tabs__item_selected" data-type="off" title="Video off">Off</dt>
					</dl>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php

		if ( ! $echo ) {
			return ob_get_clean();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}
}
