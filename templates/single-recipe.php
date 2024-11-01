<?php
/**
 * Single recipe template.
 *
 * @package whisk-recipes
 */
?>
<?php get_header(); ?>
<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>
	<?php echo do_shortcode( sprintf( '[whisk-recipe id="%d" embedded="no" sidebar="yes" comments="yes"]', get_the_ID() ) ); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
<?php
