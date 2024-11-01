<?php
/**
 * @var int    $id       Recipe ID.
 * @var string $comments Comments visibility.
 * @var string $embedded Comments visibility.
 * @var string $author   Comments visibility.
 * @var string $share    Comments visibility.
 */

use Whisk\Recipes\Models\Equipment;
use Whisk\Recipes\Models\Recipe;
use Whisk\Recipes\Models\Ingredient;

global $wp_embed;

$recipe_id = $id;
$classes   = array();

if ( 'yes' === $comments ) {
	$classes[] = 'whisk-container--comments';
}

if ( 'yes' === $embedded ) {
	$classes[] = 'whisk-container--embedded';
}

if ( 'yes' === $author ) {
	$classes[] = 'whisk-container--author';
}

if ( 'yes' === $share ) {
	$classes[] = 'whisk-container--share';
}
?>
<main class="whisk-container <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<article class="whisk-single">
		<header class="whisk-header">
			<div class="whisk-row whisk-row-responsive">
				<!--header-left-column-->
				<div class="whisk-column whisk-column-sm-34 whisk-column-md-34 whisk-column-lg-34 whisk-column--cover">
					<div class="whisk-cover">
						<a href="<?php echo esc_url( Recipe::get_recipe_thumbnail_url( $recipe_id, Recipe::IMAGE_SIZE_LARGE ) ); ?>">
							<?php echo Recipe::get_recipe_thumbnail( $recipe_id, Recipe::IMAGE_SIZE_THUMBNAIL, [ 'class' => 'whisk-image' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
					<?php $video = Recipe::get_recipe_video( $recipe_id ); ?>
					<?php if ( $video ) : ?>
						<a class="whisk-play-video" data-toggle="modal" data-target="#whisk-modal-video" data-width="1000">
							<svg aria-hidden="true" class="whisk-icon" width="40" height="40" fill="#3dc795" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 477.9 477.9" xml:space="preserve"><path d="M238.9 0C107 0 0 107 0 238.9s107 238.9 238.9 238.9 238.9-107 238.9-238.9C477.7 107 370.8 0.1 238.9 0zM339.6 246.5c-1.7 3.3-4.3 6-7.7 7.7v0.1L195.4 322.6c-8.4 4.2-18.7 0.8-22.9-7.6 -1.2-2.4-1.8-5-1.8-7.7V170.7c0-9.4 7.6-17.1 17.1-17.1 2.7 0 5.3 0.6 7.6 1.8l136.5 68.3C340.3 227.9 343.8 238.1 339.6 246.5z"/></svg>
							<span class="whisk-play-video__value">Play video</span>
						</a>
					<?php endif; ?>
				</div>
				<!--/header-left-column-->
				<!--header-right-column-->
				<div class="whisk-column whisk-column-sm-66 whisk-column-md-66 whisk-column-lg-66 whisk-column--meta">
					<div class="whisk-row whisk-row-responsive">
						<div class="whisk-column whisk-column-md-40 whisk-column-lg-40 whisk-column--title">
							<h2 class="whisk-h2"><?php echo esc_html( get_the_title( $recipe_id ) ); ?></h2>
						</div>
						<div class="whisk-column whisk-column-md-60 whisk-column-lg-60 whisk-align__right whisk-actions whisk-column--actions">
							<button data-url="<?php echo esc_url( get_permalink( $recipe_id ) ); ?>" id="whisk-collections-trigger" type="button" class="whisk-btn whisk-btn-primary whisk-btn-spin" title="<?php esc_html_e( 'Save recipe to Whisk Studio', 'whisk-recipes' ); ?>">
								<?php esc_html_e( 'Save', 'whisk-recipes' ); ?>
							</button>
							<button type="button" class="whisk-btn" onclick="window.print()">
								<svg class="whisk-icon" enable-background="new 0 0 512 512" height="16" viewBox="0 0 512 512" width="16" xmlns="http://www.w3.org/2000/svg"><g><path d="m422.5 99v-24c0-41.355-33.645-75-75-75h-184c-41.355 0-75 33.645-75 75v24z"/><path d="m118.5 319v122 26 15c0 16.568 13.431 30 30 30h214c16.569 0 30-13.432 30-30v-15-26-122zm177 128h-80c-8.284 0-15-6.716-15-15s6.716-15 15-15h80c8.284 0 15 6.716 15 15s-6.716 15-15 15zm0-64h-80c-8.284 0-15-6.716-15-15s6.716-15 15-15h80c8.284 0 15 6.716 15 15s-6.716 15-15 15z"/><path d="m436.5 129h-361c-41.355 0-75 33.645-75 75v120c0 41.355 33.645 75 75 75h13v-80h-9c-8.284 0-15-6.716-15-15s6.716-15 15-15h24 304 24c8.284 0 15 6.716 15 15s-6.716 15-15 15h-9v80h14c41.355 0 75-33.645 75-75v-120c0-41.355-33.645-75-75-75zm-309 94h-48c-8.284 0-15-6.716-15-15s6.716-15 15-15h48c8.284 0 15 6.716 15 15s-6.716 15-15 15z"/></g></svg> Print
							</button>
							<?php if ( whisk_carbon_get_theme_option( 'whisk_share_list' ) ) : ?>
								<button title="Share this recipe" class="whisk-btn" data-toggle="modal" data-target="#whisk-modal-share" data-width="720">
									<svg class="whisk-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
										<path d="M11 5.83L8.41 8.41L7 7L12 2L17 7L15.59 8.41L13 5.83L13 15.5L11 15.5L11 5.83Z" />
										<path d="M19 20V13H21V20C21 21.1 20.1 22 19 22H5C3.9 22 3 21.1 3 20V13H5V20H19Z" />
									</svg>
									Share
								</button>
							<?php else : ?>
								<a href="http://pinterest.com/pin/create/button/?url=<?php echo rawurlencode( get_the_post_thumbnail_url() ); ?>&description=<?php echo rawurlencode( get_the_title() ); ?>" target="_blank" class="whisk-btn">
									<svg class="whisk-icon" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" width="16" height="16" viewBox="0 0 438.5 438.5" xml:space="preserve"><path d="M409.1 109.2c-19.6-33.6-46.2-60.2-79.8-79.8C295.8 9.8 259.1 0 219.3 0 179.5 0 142.8 9.8 109.2 29.4c-33.6 19.6-60.2 46.2-79.8 79.8C9.8 142.8 0 179.5 0 219.3c0 44.4 12.1 84.6 36.3 120.8 24.2 36.2 55.9 62.9 95.1 80.2 -0.8-20.4 0.5-37.2 3.7-50.5l28.3-119.3c-4.8-9.3-7.1-20.9-7.1-34.8 0-16.2 4.1-29.7 12.3-40.5 8.2-10.8 18.2-16.3 30-16.3 9.5 0 16.8 3.1 22 9.4 5.1 6.3 7.7 14.2 7.7 23.7 0 5.9-1.1 13.1-3.3 21.6 -2.2 8.5-5 18.3-8.6 29.4 -3.5 11.1-6 20-7.6 26.7 -2.7 11.6-0.5 21.6 6.6 29.8 7 8.3 16.4 12.4 28 12.4 20.4 0 37.1-11.3 50.1-34 13-22.7 19.6-50.2 19.6-82.5 0-24.9-8-45.2-24.1-60.8 -16.1-15.6-38.5-23.4-67.2-23.4 -32.2 0-58.2 10.3-78.1 31 -19.9 20.7-29.8 45.4-29.8 74.1 0 17.1 4.9 31.5 14.6 43.1 3.2 3.8 4.3 7.9 3.1 12.3 -0.4 1.1-1.1 4-2.3 8.6 -1.1 4.6-1.9 7.5-2.3 8.9 -1.5 6.1-5.1 8-10.8 5.7 -14.7-6.1-25.8-16.7-33.4-31.7 -7.6-15-11.4-32.5-11.4-52.2 0-12.8 2-25.5 6.1-38.3 4.1-12.8 10.5-25.1 19.1-37 8.7-11.9 19-22.4 31.1-31.5 12.1-9.1 26.8-16.5 44.1-22s36-8.3 56-8.3c27 0 51.3 6 72.8 18 21.5 12 37.9 27.5 49.3 46.5 11.3 19 17 39.4 17 61.1 0 28.5-4.9 54.2-14.8 77.1 -9.9 22.8-23.9 40.8-42 53.8 -18.1 13-38.6 19.6-61.7 19.6 -11.6 0-22.5-2.7-32.5-8.1 -10.1-5.4-17-11.8-20.8-19.3 -8.6 33.7-13.7 53.8-15.4 60.2 -3.6 13.5-11 29.1-22.3 46.8 20.4 6.1 41.1 9.1 62.2 9.1 39.8 0 76.5-9.8 110.1-29.4 33.6-19.6 60.2-46.2 79.8-79.8 19.6-33.6 29.4-70.3 29.4-110.1C438.5 179.5 428.7 142.8 409.1 109.2z"/></svg>
									Pin
								</a>
							<?php endif; ?>
						</div>
					</div>
					<div class="whisk-row">
						<div class="whisk-column">
							<div class="whisk-author">
								<img src="<?php echo esc_url( get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 40 ) ) ); ?>" alt="" width="40" height="40" class="whisk-author__avatar" />
								<span class="whisk-author__name">By: <b><?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?></b></span>
								<span class="whisk-author__date"><?php echo esc_html( get_post_time( 'F d, Y' ) ); ?></span>
							</div>
						</div>
					</div>

					<?php $description = Recipe::get_recipe_excerpt( $recipe_id ); ?>
					<?php if ( ! empty( $description ) ) : ?>
						<div class="whisk-row">
							<div class="whisk-column">
								<div class="whisk-description"><?php echo esc_html( $description ); ?></div>
							</div>
						</div>
					<?php endif; ?>
					<?php
					$prep_time    = Recipe::get_prep_time( $recipe_id );
					$cook_time    = Recipe::get_cook_time( $recipe_id );
					$resting_time = Recipe::get_resting_time( $recipe_id );
					$total_time   = Recipe::get_total_time( $recipe_id );
					?>
					<div class="whisk-row whisk-row-responsive">
						<?php if ( $total_time ) : ?>
							<div class="whisk-column whisk-column-sm-60 whisk-column-md-60 whisk-column-lg-60">
								<div class="whisk-times">
									<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="whisk-icon" fill="#a1a1a1" version="1.1" x="0" y="0" viewBox="0 0 96.3 95.5" xml:space="preserve"><path d="M5271.2 2398.1v-2c0-2.8-5-4-9.7-4s-9.7 1.3-9.7 4v2c0 1.8 0.7 3.6 2 4.9l5 4.9c0.3 0.3 0.4 0.6 0.4 1v6.4c0 0.4 0.2 0.7 0.6 0.8l2.9 0.9c0.5 0.1 1-0.2 1-0.8v-7.2c0-0.4 0.2-0.7 0.4-1l5.1-5C5270.5 2401.7 5271.2 2399.9 5271.2 2398.1zM5261.5 2398c-4.8 0-7.4-1.3-7.5-1.8l0 0c0.1-0.5 2.7-1.8 7.5-1.8s7.3 1.3 7.5 1.8C5268.8 2396.7 5266.3 2398 5261.5 2398zM5266.5 2408.3c-0.6 0-1 0.4-1 1s0.4 1 1 1h4.3c0.6 0 1-0.4 1-1s-0.4-1-1-1H5266.5zM5270.8 2411.7h-4.3c-0.6 0-1 0.4-1 1s0.4 1 1 1h4.3c0.6 0 1-0.4 1-1C5271.8 2412.1 5271.4 2411.7 5270.8 2411.7zM5270.8 2415h-4.3c-0.6 0-1 0.4-1 1s0.4 1 1 1h4.3c0.6 0 1-0.4 1-1C5271.8 2415.5 5271.4 2415 5270.8 2415z"/><path d="M48.1 0.5C21.9 0.5 0.6 21.8 0.6 48s21.3 47.5 47.5 47.5S95.6 74.2 95.6 48 74.3 0.5 48.1 0.5zM48.1 87c-21.5 0-39-17.5-39-39s17.5-39 39-39 39 17.5 39 39S69.6 87 48.1 87zM52.3 45.8V25.1c0-2.3-1.9-4.2-4.2-4.2s-4.2 1.9-4.2 4.2V48c0 1.4 0.7 2.7 1.8 3.5L59.2 61c0.7 0.5 1.6 0.8 2.4 0.8 1.3 0 2.6-0.6 3.5-1.8 1.3-1.9 0.9-4.6-1-5.9L52.3 45.8z"/></svg>
									<?php if ( $prep_time ) : ?>
										<span class="whisk-times__item"><span class="whisk-muted">Prep:</span> <?php echo absint( $prep_time / 60 ); ?>m</span>
									<?php endif; ?>
									<?php if ( $cook_time ) : ?>
										<span class="whisk-times__item"><span class="whisk-muted">Cook:</span> <?php echo absint( $cook_time / 60 ); ?>m</span>
									<?php endif; ?>
									<?php if ( $resting_time ) : ?>
										<span class="whisk-times__item"><span class="whisk-muted">Resting:</span> <?php echo absint( $resting_time / 60 ); ?>m</span>
									<?php endif; ?>
									<?php if ( $total_time ) : ?>
										<span class="whisk-times__item"><span class="whisk-muted">Total:</span> <?php echo absint( $total_time / 60 ); ?>m</span>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="whisk-column whisk-align__right whisk-recipe-rating">
							<?php do_action( 'whisk_recipes_rating', $recipe_id ); ?>
						</div>
					</div>
					<div class="whisk-row whisk-row-responsive whisk-taxonomies">
						<?php $meal_types = Recipe::get_recipe_terms( $recipe_id, 'whisk_meal_type' ); ?>
						<?php if ( $meal_types ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_mealtypes">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 82.2 95.8"><path d="M45.6 46v-1.3c0-2.5-2-4.5-4.5-4.5s-4.5 2-4.5 4.5V46C19.5 48.2 6.3 62.8 6.3 80.5H76C75.9 62.8 62.7 48.2 45.6 46z"/><path d="M17.9 27.1l1.4 3.3c0.6 1.4 1.9 2.3 3.4 2.3 0.5 0 0.9-0.1 1.4-0.3 1.9-0.8 2.7-2.9 2-4.8l-1.4-3.3c-0.7-1.8-0.4-3.8 0.8-5.3 2.9-3.6 3.7-8.4 1.9-12.7L26 3c-0.8-1.9-2.9-2.7-4.8-2 -1.9 0.8-2.7 2.9-2 4.8l1.4 3.3c0.7 1.8 0.4 3.8-0.8 5.3C16.9 17.9 16.2 22.8 17.9 27.1z"/><path d="M36.4 27.1l1.4 3.3c0.6 1.4 1.9 2.3 3.4 2.3 0.5 0 0.9-0.1 1.4-0.3 1.9-0.8 2.7-2.9 2-4.8l-1.4-3.3c-0.7-1.8-0.4-3.8 0.8-5.3 2.9-3.6 3.7-8.4 1.9-12.7L44.5 3c-0.8-1.9-2.9-2.7-4.8-2 -1.9 0.8-2.7 2.9-2 4.8l1.4 3.3c0.7 1.8 0.4 3.8-0.8 5.3C35.4 17.9 34.7 22.8 36.4 27.1z"/><path d="M54.9 27.1l1.4 3.3c0.6 1.4 1.9 2.3 3.4 2.3 0.5 0 0.9-0.1 1.4-0.3 1.9-0.8 2.7-2.9 2-4.8l-1.5-3.3c-0.7-1.8-0.4-3.8 0.8-5.3 2.9-3.6 3.7-8.4 1.9-12.7L62.9 3c-0.8-1.9-2.9-2.7-4.8-2 -1.9 0.8-2.7 2.9-2 4.8l1.4 3.3c0.7 1.8 0.4 3.8-0.8 5.3C53.9 17.9 53.2 22.8 54.9 27.1z"/><path d="M79 86.3H3.2c-1.8 0-3.2 1.4-3.2 3.2v3.1c0 1.8 1.4 3.2 3.2 3.2H79c1.8 0 3.2-1.4 3.2-3.2v-3.1C82.2 87.8 80.8 86.3 79 86.3z"/></svg>
									Meal Type:
									<?php foreach ( $meal_types as $meal_type ) : ?>
										<a href="<?php echo esc_url( get_term_link( $meal_type->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $meal_type->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $cuisines = Recipe::get_recipe_terms( $recipe_id, 'whisk_cuisine' ); ?>
						<?php if ( $cuisines ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_cuisines">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 69 95.3"><path d="M69 26.7c0-12.5-5.9-26.4-16-26.4S37 14.2 37 26.7c0 10 4.6 16.1 10.9 18.1l-3.2 41.5c-0.4 4.9 3.5 9 8.4 9s8.7-4.2 8.4-9l-3.4-41.5C64.4 42.7 69 36.6 69 26.7z"/><path d="M26.5 3.1h-0.3c-1.6 0-2.8 1.3-2.8 2.9l0.7 22c0.1 1.6-1.2 2.9-2.8 2.9 -1.5 0-2.8-1.2-2.8-2.7L18.3 5.8c0-1.5-1.3-2.7-2.8-2.7h-0.4c-1.5 0-2.8 1.2-2.8 2.7L12 28.1c0 1.5-1.3 2.7-2.8 2.7 -1.6 0-2.8-1.3-2.8-2.9l0.7-22C7.2 4.3 5.9 3 4.3 3H4C2.5 3 1.3 4.2 1.2 5.7L0.3 28.5C0 35.2 4.2 40.9 10.2 43.1L6.8 86.2c-0.4 4.9 3.5 9 8.4 9s8.7-4.2 8.4-9l-3.4-43.1c5.9-2.1 10.1-7.9 9.9-14.6L29.3 5.8C29.2 4.3 28 3.1 26.5 3.1z"/></svg>
									Cousines:
									<?php foreach ( $cuisines as $cuisine ) : ?>
										<a href="<?php echo esc_url( get_term_link( $cuisine->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $cuisine->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $cooking_techniques = Recipe::get_recipe_terms( $recipe_id, 'whisk_cooking_technique' ); ?>
						<?php if ( $cooking_techniques ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_techniques">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 71.5 95.2"><path d="M13.9 25l1.3 3.1c0.5 1.3 1.8 2.1 3.2 2.1 0.4 0 0.9-0.1 1.3-0.3 1.8-0.7 2.6-2.7 1.9-4.5l-1.3-3.1c-0.7-1.7-0.4-3.6 0.8-5 2.8-3.4 3.5-8 1.8-12l-1.3-3.1c-0.8-1.6-2.8-2.5-4.6-1.7 -1.8 0.7-2.6 2.7-1.9 4.5l1.3 3.1c0.7 1.7 0.4 3.6-0.7 5C12.9 16.4 12.2 21 13.9 25z"/><path d="M31.3 25l1.3 3.1c0.5 1.3 1.8 2.1 3.2 2.1 0.4 0 0.9-0.1 1.3-0.3 1.8-0.7 2.6-2.7 1.9-4.5l-1.3-3.1c-0.7-1.7-0.4-3.6 0.7-5 2.8-3.4 3.5-8 1.8-12l-1.3-3.1c-0.7-1.8-2.7-2.6-4.5-1.9C32.6 1 31.8 3 32.5 4.8l1.3 3.1c0.7 1.7 0.4 3.6-0.7 5C30.4 16.4 29.7 21 31.3 25z"/><path d="M48.8 25l1.3 3.1c0.5 1.3 1.8 2.1 3.2 2.1 0.4 0 0.9-0.1 1.3-0.3 1.8-0.7 2.6-2.7 1.9-4.5l-1.3-3.1c-0.7-1.7-0.4-3.6 0.7-5 2.8-3.4 3.5-8 1.8-12l-1.3-3.1c-0.7-1.8-2.7-2.6-4.5-1.9S49.3 3 50 4.8l1.3 3.1c0.7 1.7 0.4 3.6-0.8 5C47.8 16.4 47.1 21 48.8 25z"/><path d="M6.9 51.4h57.8c1.7 0 3.1-1.4 3.1-3.1v-1.9c0-1.7-1.4-3.1-3.1-3.1H41.2l0.2-2.9c0.2-2.8-2-5.2-4.8-5.2h-1.5c-2.8 0-5 2.4-4.8 5.2l0.2 2.9H6.9c-1.7 0-3.1 1.4-3.1 3.1v1.9C3.8 50 5.2 51.4 6.9 51.4z"/><path d="M68.6 62.3l-4.1 0.1v-4.7c0-1.2-0.9-2.1-2.1-2.1H9.2c-1.2 0-2.1 0.9-2.1 2.1v4.7L3 62.3c-1.7 0-3 1.3-3 3v3.4c0 1.7 1.4 3 3 3l4.1-0.1v11.6c0 6.6 5.4 12 12 12h33.3c6.6 0 12-5.4 12-12V71.6l4.1 0.1c1.7 0 3-1.3 3-3v-3.4C71.6 63.6 70.2 62.3 68.6 62.3z"/></svg>
									Cooking Technique:
									<?php foreach ( $cooking_techniques as $cooking_technique ) : ?>
										<a href="<?php echo esc_url( get_term_link( $cooking_technique->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $cooking_technique->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $diets = Recipe::get_recipe_terms( $recipe_id, 'whisk_diet' ); ?>
						<?php if ( $diets ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_techniques">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 71.5 95.2" xml:space="preserve"><path d="M50.3 27.3c-3.9 0.3-7.7 1.3-11.1 2.8 -1.6-9.6-5.3-19.9-13.9-21 -6.3-0.8-7.3 4.8-3.5 5.8 7.1 1.9 10.5 8.8 11.9 15.8 -3.9-1.9-8.2-3.1-12.5-3.5C4.7 26.1-4.2 40.8 2.6 61.7c5.1 15.8 14.9 27.5 27.7 24.9 1.9-0.4 3.7-1.2 5.5-2.1 1.7 0.9 3.5 1.7 5.5 2.1 12.8 2.6 22.6-9.2 27.7-24.9C75.6 40.8 66.8 26 50.3 27.3zM16.3 64.5c-5.8-0.2-10.3-7.3-10.1-15.8 0.3-8.6 5.2-15.4 11-15.2 5.8 0.2 10.3 7.3 10.1 15.8C27 57.9 22.1 64.7 16.3 64.5zM52.3 18.6c-2.1 3.2-5.7 4.8-8.2 5.6 -1.4 0.4-2.8-0.4-3-1.9 -0.3-2.5-0.1-6.5 2-9.6 2.1-3.2 5.7-4.8 8.2-5.5 1.5-0.5 2.9 0.4 2.9 1.9C54.5 11.6 54.4 15.5 52.3 18.6z"/></svg>
									Diets:
									<?php foreach ( $diets as $diet ) : ?>
										<a href="<?php echo esc_url( get_term_link( $diet->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $diet->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $avoidance = Recipe::get_recipe_terms( $recipe_id, 'whisk_avoidance' ); ?>
						<?php if ( $avoidance ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_techniques">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 71.5 95.2" xml:space="preserve"><path d="M10.5 22.3c-14 14-14 36.6 0 50.6s36.6 14 50.6 0 14-36.6 0-50.6S24.4 8.4 10.5 22.3zM18.5 30.4c4.6-4.5 10.8-7.1 17.2-7 4.5 0 8.8 1.2 12.6 3.5L15 60.2C9.2 50.7 10.7 38.4 18.5 30.4L18.5 30.4zM52.9 64.7c-7.9 7.9-20.2 9.4-29.8 3.6L56.5 35C62.3 44.5 60.8 56.8 52.9 64.7z"/></svg>
									Avoidance:
									<?php foreach ( $avoidance as $item ) : ?>
										<a href="<?php echo esc_url( get_term_link( $item->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $item->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $nutrition = Recipe::get_recipe_terms( $recipe_id, 'whisk_nutrition' ); ?>
						<?php if ( $nutrition ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_techniques">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 71.5 95.2" xml:space="preserve"><path d="M67.9 43.9h-1.9l3.3-9.3c2-5.8-0.5-12.3-5.6-14.7 -5-2.3-10.8 0.5-12.8 6.2l-6.3 17.7h-3.3l1.6-28.2c0.1-2.2-1.4-4.2-3.4-4.3 -0.1 0-0.1 0-0.2 0H3.6c-2 0-3.6 1.8-3.6 4.1v0.2l3.6 65.1c0.1 2.1 1.7 3.8 3.6 3.8h28.6c0.9 0 1.6-0.3 2.3-1 14.3 4.3 28.9-5.5 32.6-21.7 0.6-2.5 0.9-5.2 0.9-7.8V48C71.5 45.7 69.9 43.9 67.9 43.9zM57.6 29.2c0.6-1.6 2.2-2.4 3.6-1.7 0.2 0.1 0.4 0.2 0.5 0.3 1.1 0.8 1.5 2.4 1 3.8l-4.4 12.3h-6L57.6 29.2zM7.4 19.5h28.2l-0.4 6.6 -3.4 2.6c-2 1.5-4.6 1.5-6.6 0 -4.3-3.4-10-3.5-14.5-0.2L8 30.3 7.4 19.5zM10.5 76.5l-2-37.1 6.1-4.1c2-1.5 4.6-1.5 6.6 0 4 3.2 9.2 3.5 13.4 0.9l-0.4 7.7H21.5c-2 0-3.6 1.8-3.6 4.1v6.1c0 1.1 0 2.1 0.1 3.2 0 0.4 0.1 0.9 0.2 1.4 0.1 0.6 0.1 1.1 0.3 1.7 0.1 0.7 0.3 1.3 0.4 1.9 0.1 0.3 0.2 0.7 0.3 1.1 0.2 0.7 0.5 1.5 0.7 2.2 0.1 0.2 0.1 0.4 0.2 0.7 0.3 0.8 0.6 1.5 1 2.3 0.1 0.1 0.1 0.2 0.1 0.4 0.4 0.8 0.8 1.5 1.2 2.2 0.1 0.1 0.1 0.2 0.1 0.3 0.4 0.7 0.9 1.4 1.4 2.1 0 0.1 0.1 0.2 0.2 0.3 0.5 0.7 1 1.3 1.5 1.8 0.1 0.1 0.2 0.3 0.4 0.4 0.2 0.2 0.4 0.4 0.6 0.7H10.5z"/></svg>
									Nutrition:
									<?php foreach ( $nutrition as $item ) : ?>
										<a href="<?php echo esc_url( get_term_link( $item->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $item->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $tags = Recipe::get_recipe_terms( $recipe_id, 'whisk_tag' ); ?>
						<?php if ( $tags ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item whisk-taxonomies__item_techniques">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 71.5 95.2" xml:space="preserve"><path d="M24.5 32.6c-1.2 0-2.5 0.5-3.3 1.4 -1.8 1.8-1.8 4.8 0 6.8 1.9 1.8 4.8 1.8 6.8 0 1.8-1.8 1.8-4.8 0-6.8C27 33.1 25.8 32.6 24.5 32.6zM33.7 12.8L6.5 13.5c-3.2 0.1-5.7 2.7-5.8 5.8L0 46.5c0 1.6 0.6 3.2 1.7 4.4l29.6 29.6c2.5 2.5 5.8 3.9 9.3 3.9 3.5 0 6.8-1.3 9.3-3.9l17.7-17.6c2.5-2.5 3.9-5.8 3.9-9.3s-1.3-6.8-3.9-9.3L38 14.5C36.9 13.4 35.3 12.8 33.7 12.8zM31.9 44.8c-2 2-4.8 3-7.4 3s-5.3-1-7.4-3C13 40.7 13 34 17.1 29.9s10.7-4.1 14.8 0S36 40.7 31.9 44.8z"/></svg>
									Tags:
									<?php foreach ( $tags as $item ) : ?>
										<a href="<?php echo esc_url( get_term_link( $item->term_id ) ); ?>" class="whisk-link whisk-taxonomies__link"><?php echo esc_html( $item->name ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php $complexity = Recipe::get_recipe_complexity( $recipe_id ); ?>
						<?php if ( $complexity > 0 ) : ?>
							<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item">
								<div class="whisk-taxonomies__data">
									<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 71.5 95.2" xml:space="preserve"><path d="M68.9 9.5H56.8c-1.5 0-2.6 1.1-2.6 2.6v72.4c0 1.5 1.1 2.6 2.6 2.6h12.1c1.5 0 2.6-1.1 2.6-2.6V12.1C71.5 10.7 70.4 9.5 68.9 9.5zM41.8 25.2H29.7c-1.5 0-2.6 1.1-2.6 2.6v56.9c0 1.5 1.1 2.6 2.6 2.6h12c1.5 0 2.6-1.1 2.6-2.6V27.8C44.4 26.4 43.3 25.2 41.8 25.2zM14.7 40.9H2.6C1.1 40.9 0 42 0 43.5v41.2c0 1.5 1.1 2.6 2.6 2.6h12c1.5 0 2.6-1.1 2.6-2.6V43.5C17.3 42 16.2 40.9 14.7 40.9z"/></svg>
									Complexity: <?php echo esc_html( Recipe::get_recipe_complexity_label( $complexity ) ); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="whisk-column whisk-column-sm-50 whisk-column-md-50 whisk-column-lg-50 whisk-taxonomies__item">
							<div class="whisk-taxonomies__data">
								<svg aria-hidden="true" class="whisk-icon" fill="#a1a1a1" width="17" height="20" version="1.1" x="0" y="0" viewBox="0 0 71.5 95.2" xml:space="preserve"><path d="M35.9 82.5c-10.2 0-20.5 0-30.7 0 -2.9 0-4.8-1.8-4.9-4.7 0-2.1 0-4.1 0-6.2 0.1-2.5 1.3-4.3 3.4-5.5 7-3.8 14-7.5 21-11.3 0.5-0.3 0.8 0 1.2 0.2 6 3.1 12.1 3.4 18.3 0.8 0.2-0.1 0.4-0.1 0.5-0.2 1.3-1.1 2.5-0.7 3.8 0.1 6.3 3.5 12.6 6.8 18.9 10.2 2.7 1.5 4.1 3.6 4 6.7 0 1.8 0 3.5 0 5.2 0 2.9-1.9 4.8-4.8 4.8C56.4 82.5 46.1 82.5 35.9 82.5zM35.4 53.3c-5.8-0.2-10.5-3-14.1-8.1 -5.8-8.3-5.1-20.1 1.5-27.6 7.2-8.2 19-8.1 26.2 0.1 7.5 8.8 7.1 22.6-1.1 30.6C44.6 51.4 40.6 53.2 35.4 53.3z"/><line class="st0" x1="189.9" y1="32.3" x2="189.9" y2="29.7"/></svg>
								<?php echo esc_html( Recipe::get_recipe_yield_label( $recipe_id ) ); ?>: <?php echo esc_html( Recipe::get_recipe_yield( $recipe_id ) ); ?>
							</div>
						</div>
					</div>
				</div>
				<!--/header-right-column-->
			</div>
		</header>
		<div class="whisk-row whisk-article">
			<div class="whisk-column whisk-column-66__ whisk-main">
				<section class="whisk-section whisk-section_ingredients">
					<?php do_action( 'whisk_ingredients', $recipe_id ); ?>
				</section>

				<?php if ( true === get_theme_mod( 'whisk_use_nutrition_block', true ) ) : ?>
					<?php $nutrition = Recipe::get_recipe_nutrition( $recipe_id ); ?>
					<?php if ( $nutrition ) : ?>
						<section class="whisk-section whisk-section--nutrition">
							<div class="whisk-row whisk-row-responsive">
								<div class="whisk-column whisk-column-66 whisk-column--nutrition">
									<?php do_action( 'whisk_nutrition', $recipe_id ); ?>
								</div>
								<div class="whisk-column whisk-column-34 whisk-column--usefulness">
									<?php do_action( 'whisk_health_score', $recipe_id ); ?>
									<?php do_action( 'whisk_glycemic_index', $recipe_id ); ?>
								</div>
							</div>
						</section>
					<?php endif; ?>
				<?php endif; ?>

				<?php $content = trim( get_the_content( null, false, $recipe_id ) ); ?>
				<?php if ( $content ) : ?>
					<section class="whisk-section whisk-content">
						<?php echo wp_kses_post( $content ); ?>
					</section>
				<?php endif; ?>

				<section class="whisk-section whisk-instructions">
					<?php do_action( 'whisk_instructions', $recipe_id ); ?>
				</section>

				<?php $simple_notes = whisk_carbon_get_post_meta( $recipe_id, 'whisk_simple_notes' ); ?>
				<?php if ( $simple_notes ) : ?>
					<section class="whisk-section whisk-notes">
						<h2 class="whisk-h2">Tips</h2>
						<div class="whisk-notes__item"><?php echo wp_kses_post( whisk_carbon_get_post_meta( $recipe_id, 'whisk_simple_notes_text' ) ); ?></div>
					</section>
				<?php endif; ?>

				<?php $notes = whisk_carbon_get_post_meta( $recipe_id, 'whisk_notes' ); ?>
				<?php if ( $notes && ! $simple_notes ) : ?>
					<section class="whisk-section whisk-notes">
						<h2 class="whisk-h2">Tips</h2>
						<?php foreach ( $notes as $note ) : ?>
							<div class="whisk-notes__item"><?php echo wp_kses_post( $note['whisk_note'] ); ?></div>
						<?php endforeach; ?>
					</section>
				<?php endif; ?>

				<?php $equipments = whisk_carbon_get_post_meta( $recipe_id, 'whisk_equipments' ); ?>
				<?php if ( $equipments ) : ?>
					<section class="whisk-section whisk-equipment">
						<h2 class="whisk-h2">Equipment</h2>
						<div class="whisk-row whisk-row-responsive whisk-equipment__row">
							<?php foreach ( $equipments as $item ) : ?>
								<?php
								$equipment          = get_term( $item );
								$equipment_image    = whisk_carbon_get_term_meta( $equipment->term_id, 'whisk_equipment_image' );
								$equipment_ref_link = whisk_carbon_get_term_meta( $equipment->term_id, 'whisk_equipment_ref_link' );
								?>
								<div class="whisk-equipment__item whisk-column whisk-column-xs-50 whisk-column-sm-50 whisk-column-md-33 whisk-column-lg-33">
									<a href="<?php echo esc_url( wp_get_attachment_image_url( $equipment_image, 'large' ) ); ?>" target="_blank" class="whisk-magnifier whisk-equipment__link" data-width="800">
										<img src="<?php echo esc_url( wp_get_attachment_image_url( $equipment_image, Equipment::IMAGE_SIZE ) ); ?>" alt="" class="whisk-equipment__image" width="160" height="160" />
									</a>
									<div class="whisk-equipment__name">
										<?php echo esc_html( $equipment->name ); ?>
										<?php if ( ! empty( $equipment_ref_link ) ) : ?>
											<br><a class="whisk-link" href="<?php echo esc_url( $equipment_ref_link ); ?>" target="_blank">Buy now</a>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( 'yes' === $share && true === get_theme_mod( 'whisk_use_share_block', true ) ) : ?>
					<section class="whisk-section whisk-share">
						<div class="whisk-row">
							<div class="whisk-column whisk-column-50">
								<h2 class="whisk-h2">Did you make this recipe?</h2>
								<p>Tag <a href="https://www.instagram.com/<?php echo esc_attr( whisk_carbon_get_theme_option( 'whisk_instagram_username' ) ) ;?>/" class="whisk-link" target="_blank"><?php echo esc_attr( whisk_carbon_get_theme_option( 'whisk_instagram_username' ) ) ;?></a> on Instagram<br>and hashtag <a href="#" class="whisk-link"><?php echo esc_attr( whisk_carbon_get_theme_option( 'whisk_instagram_hashtag' ) ) ;?></a></p>
							</div>
							<div class="whisk-column whisk-column-50">
								<h2 class="whisk-h2">Share this recipe</h2>
								<?php if ( whisk_carbon_get_theme_option( 'whisk_share_list' ) ) : ?>
									<?php require_once WHISK_RECIPES_PATH . '/template-parts/frontend/share.php'; ?>
								<?php else : ?>
									<p>
										<a class="whisk-share__link" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( get_permalink() ); ?>" target="_blank" title="Share on Facebook">
											<svg aria-hidden="true" class="whisk-icon" fill="#bebebe" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 512 512" height="24" viewBox="0 0 512 512" width="24"><path d="m512 256c0-141.4-114.6-256-256-256s-256 114.6-256 256 114.6 256 256 256c1.5 0 3 0 4.5-0.1v-199.2h-55v-64.1h55v-47.2c0-54.7 33.4-84.5 82.2-84.5 23.4 0 43.5 1.7 49.3 2.5v57.2h-33.6c-26.5 0-31.7 12.6-31.7 31.1v40.8h63.5l-8.3 64.1h-55.2v189.5c107-30.7 185.3-129.2 185.3-246.1z"/></svg>
										</a>
										<a class="whisk-share__link" href="https://twitter.com/intent/tweet?text=<?php echo esc_attr( get_the_title() ); ?> - <?php echo esc_url( get_permalink() ); ?>" target="_blank" title="Share on Twitter">
											<svg aria-hidden="true" class="whisk-icon" fill="#bebebe" height="24" viewBox="0 0 512 512" width="24" xmlns="http://www.w3.org/2000/svg"><path d="m256 0c-141.363281 0-256 114.636719-256 256s114.636719 256 256 256 256-114.636719 256-256-114.636719-256-256-256zm116.886719 199.601562c.113281 2.519532.167969 5.050782.167969 7.59375 0 77.644532-59.101563 167.179688-167.183594 167.183594h.003906-.003906c-33.183594 0-64.0625-9.726562-90.066406-26.394531 4.597656.542969 9.277343.8125 14.015624.8125 27.53125 0 52.867188-9.390625 72.980469-25.152344-25.722656-.476562-47.410156-17.464843-54.894531-40.8125 3.582031.6875 7.265625 1.0625 11.042969 1.0625 5.363281 0 10.558593-.722656 15.496093-2.070312-26.886718-5.382813-47.140624-29.144531-47.140624-57.597657 0-.265624 0-.503906.007812-.75 7.917969 4.402344 16.972656 7.050782 26.613281 7.347657-15.777343-10.527344-26.148437-28.523438-26.148437-48.910157 0-10.765624 2.910156-20.851562 7.957031-29.535156 28.976563 35.554688 72.28125 58.9375 121.117187 61.394532-1.007812-4.304688-1.527343-8.789063-1.527343-13.398438 0-32.4375 26.316406-58.753906 58.765625-58.753906 16.902344 0 32.167968 7.144531 42.890625 18.566406 13.386719-2.640625 25.957031-7.53125 37.3125-14.261719-4.394531 13.714844-13.707031 25.222657-25.839844 32.5 11.886719-1.421875 23.214844-4.574219 33.742187-9.253906-7.863281 11.785156-17.835937 22.136719-29.308593 30.429687zm0 0"/></svg>
										</a>
									</p>
								<?php endif; ?>
							</div>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( 'yes' === $author && true === get_theme_mod( 'whisk_use_author_block', true ) ) : ?>
					<section class="whisk-section whisk-publisher">
						<div class="whisk-publisher__avatar">
							<img width="80" height="80" src="<?php echo esc_url( get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 80 ) ) ); ?>" alt="">
						</div>
						<div class="whisk-publisher__name"><?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?></div>
						<div class="whisk-publisher__description"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></div>
					</section>
				<?php endif; ?>
				<?php if ( 'yes' === $comments && true === get_theme_mod( 'whisk_use_comments_block', true ) ) : ?>
					<section class="whisk-section whisk-comments">
						<?php comments_template(); ?>
					</section>
				<?php endif; ?>
			</div>
		</div>
	</article>
</main>
