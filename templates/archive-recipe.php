<?php
/**
 * Archive Template
 *
 * @package whisk-recipes
 */

use Whisk\Recipes\Models\Recipe;

?>
<?php get_header(); ?>
<main class="whisk-container">
	<article class="whisk-archive">
		<h1 class="whisk-h1"><?php echo get_the_archive_title(); ?></h1>
		<div class="whisk-row whisk-row-responsive whisk-loop">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				$recipe_id       = get_the_ID();
				$recipe_cover    = Recipe::get_recipe_thumbnail_url( $recipe_id, Recipe::IMAGE_SIZE_THUMBNAIL );
				$recipe_author   = Recipe::get_author_name( get_the_author_meta( 'ID' ) );
				$recipe_calories = Recipe::get_calories( $recipe_id );
				$cock_time       = Recipe::get_calories( $recipe_id );
				?>
				<div class="whisk-column whisk-column-sm-33 whisk-column-md-33 whisk-column-lg-33">
					<a href="<?php the_permalink(); ?>" class="whisk-loop__item" style="background-image: url(<?php echo esc_url( $recipe_cover ); ?>);">
						<div class="whisk-loop__inner">
							<h2 class="whisk-h2 whisk-loop__h"><?php the_title(); ?></h2>
							<!--div class="whisk-loop__author"><?php echo esc_html( $recipe_author ); ?></div-->
							<div class="whisk-loop__rating">
								<?php do_action( 'whisk_recipes_rating', $recipe_id ); ?>
							</div>
							<div class="whisk-loop__footer">
								<div class="whisk-loop__time">
									<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="whisk-icon" fill="#a1a1a1" version="1.1" x="0" y="0" viewBox="0 0 96.3 95.5" xml:space="preserve">
										<path d="M5271.2 2398.1v-2c0-2.8-5-4-9.7-4s-9.7 1.3-9.7 4v2c0 1.8 0.7 3.6 2 4.9l5 4.9c0.3 0.3 0.4 0.6 0.4 1v6.4c0 0.4 0.2 0.7 0.6 0.8l2.9 0.9c0.5 0.1 1-0.2 1-0.8v-7.2c0-0.4 0.2-0.7 0.4-1l5.1-5C5270.5 2401.7 5271.2 2399.9 5271.2 2398.1zM5261.5 2398c-4.8 0-7.4-1.3-7.5-1.8l0 0c0.1-0.5 2.7-1.8 7.5-1.8s7.3 1.3 7.5 1.8C5268.8 2396.7 5266.3 2398 5261.5 2398zM5266.5 2408.3c-0.6 0-1 0.4-1 1s0.4 1 1 1h4.3c0.6 0 1-0.4 1-1s-0.4-1-1-1H5266.5zM5270.8 2411.7h-4.3c-0.6 0-1 0.4-1 1s0.4 1 1 1h4.3c0.6 0 1-0.4 1-1C5271.8 2412.1 5271.4 2411.7 5270.8 2411.7zM5270.8 2415h-4.3c-0.6 0-1 0.4-1 1s0.4 1 1 1h4.3c0.6 0 1-0.4 1-1C5271.8 2415.5 5271.4 2415 5270.8 2415z"></path>
										<path d="M48.1 0.5C21.9 0.5 0.6 21.8 0.6 48s21.3 47.5 47.5 47.5S95.6 74.2 95.6 48 74.3 0.5 48.1 0.5zM48.1 87c-21.5 0-39-17.5-39-39s17.5-39 39-39 39 17.5 39 39S69.6 87 48.1 87zM52.3 45.8V25.1c0-2.3-1.9-4.2-4.2-4.2s-4.2 1.9-4.2 4.2V48c0 1.4 0.7 2.7 1.8 3.5L59.2 61c0.7 0.5 1.6 0.8 2.4 0.8 1.3 0 2.6-0.6 3.5-1.8 1.3-1.9 0.9-4.6-1-5.9L52.3 45.8z"></path>
									</svg>
									Total: 2h
								</div>
								<?php if ( $recipe_calories ) { ?>
									<div class="whisk-loop__calories">
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="whisk-icon" fill="#a1a1a1" x="0" y="0" viewBox="0 0 113.4 113.4" xml:space="preserve">
										<path d="M56.5 62.3l8.9 10.6 0.1 0.1 0.1 0.1c2.4 2.4 3.7 5.7 3.7 9.1 0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9c0-3.4 1.3-6.7 3.7-9.1l0.1-0.1 0.1-0.1L56.5 62.3M56.5 55.8c-1.1 0-2.3 0.4-3.1 1.4L43.2 69.4c-3.3 3.4-5.4 7.9-5.4 13.1 0 10.2 8.4 18.6 18.6 18.6S75 92.7 75 82.4c0-5.1-2-9.6-5.4-13.1L59.4 57.2C58.8 56.2 57.7 55.8 56.5 55.8L56.5 55.8zM52.3 11.9c8.8 8.2 12.9 18.9 13.1 33.6 0 4 2.8 7.4 6.7 7.9 0.4 0.1 0.9 0.1 1.3 0.1 4.4 0 7.8-3.4 7.9-7.8 0-0.4 0-0.7 0-1.1 0-0.6 0-1.8 0.1-3.3 0-1.3 1.6-1.8 2.4-1 7.5 8.2 11.9 18.9 11.9 29.7 0 9.2-4.4 18.7-11.9 26 -7.7 7.4-17.6 11.5-27.4 11.5 -9.8 0-19.7-4.3-27.4-11.5 -7.5-7.2-11.9-16.7-11.9-26 0-14.6 8.9-24.8 16.7-33.6 8.9-10.1 13.6-18.3 14.5-25 0.1-1.1 1.4-1.6 2.3-0.9L52.3 11.9zM43.6 0c-1 0-1.8 0.4-2.4 1.3 -0.6 1 0.3 3.1 1.3 5.8 2 6.1-4.1 15.9-12.8 25.7C21.6 42 11.6 53.4 11.6 70.2c0 22.7 21.4 43.3 45 43.3s45-20.6 45-43.3c0-14.3-6.8-28.5-18.2-38l-1.4-1.1c-0.7-0.6-1.4-0.9-2.3-0.9 -1.3 0-2.6 0.7-3.1 2 -0.4 1-0.9 10.9-0.9 12.6 0 0.3 0 0.9 0 1 0 1.3-1 2.3-2.3 2.3 -0.1 0-0.3 0-0.4 0 -1.1-0.1-2-1.3-2-2.4 -0.1-17-5.3-28.9-14.9-37.9C51.7 3.7 46.7 0 43.6 0L43.6 0z"/>
									</svg>
										<?php echo esc_html( $recipe_calories ); ?>
									</div>
								<?php } ?>
							</div>
						</div>
					</a>
				</div>
			<?php endwhile; ?>
		</div>
		<div class="whisk-row">
			<div class="whisk-column">
				<div class="whisk-pagination">
					<?php
					$args = [
						'prev_text' => '<svg fill="#333333" width="8" height="8" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 256 256" xml:space="preserve"><polygon points="207.1 30.2 176.9 0 48.9 128 176.9 256 207.1 225.8 109.3 128 "/></svg>',
						'next_text' => '<svg fill="#333333" width="8" height="8" xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 256 256" xml:space="preserve"><polygon points="79.1 0 48.9 30.2 146.7 128 48.9 225.8 79.1 256 207.1 128 "/></svg>',
						'end_size'  => 1,
						'mid_size'  => 2,
					];
					the_posts_pagination( $args );
					?>
				</div>
			</div>
		</div>
	</article>
</main>
<?php get_footer(); ?>
