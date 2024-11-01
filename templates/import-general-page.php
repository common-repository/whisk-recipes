<?php
/**
 * Template for recipe import page.
 *
 * @package whisk-recipes
 */
?>
<div class="wrap wsk-wrap">
	<h2><?php esc_html_e( 'Import Recipes from other plugins', 'whisk-recipes' ); ?></h2>
	<div class="whisk_message"></div>
	<?php foreach ( $template_data as $item ) : ?>
		<div class="<?php echo esc_html( $item['slug'] ); ?>-wrap">
			<h4 style="margin-bottom: 0"><?php echo esc_html( $item['name'] ); ?></h4>
			<?php if ( $item['found_posts'] ) : ?>
				<span><?php echo esc_html( sprintf( '%s %s', __( 'Found posts:', 'whisk-recipes' ), $item['found_posts'] ) ); ?></span>
				<br>
				<button class="wsk-import" id="<?php echo esc_html( $item['slug'] ); ?>"><?php esc_html_e( 'Import', 'whisk-recipes' ); ?></button>
			<?php else : ?>
				<p><?php esc_html_e( 'No recipes found', 'whisk-recipes' ); ?></p>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>

