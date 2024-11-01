<?php
/**
 * Onboarding ready step view.
 *
 * @package whisk-recipes
 **/

?>
<h2 class="whisk-h2"><?php esc_html_e( 'Ready!', 'whisk-recipes' ); ?></h2>
<div class="content-wrapper">
	<p><?php esc_html_e( "Congratulations, you're all set! You can now start adding your awesome recipes and share them with the world!", 'whisk-recipes' ); ?></p>
</div>
<div class="divider"></div>
<a class="whisk-go-button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=whisk_recipe' ) ); ?>">
	<?php esc_html_e( 'Add first recipe', 'whisk-recipes' ); ?>
</a>
<a class="whisk-go-button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=whisk_recipe&page=wsk_import' ) ); ?>">
	<?php esc_html_e( 'Import Recipes', 'whisk-recipes' ); ?>
</a>
<a id="back-button" class="whisk-go-button whisk-go-button__secondary whisk-go-button__back" href="<?php echo esc_url( $this->get_previous_step_url( 'ready' ) ); ?>">
	<?php esc_html_e( 'Back', 'whisk-recipes' ); ?>
</a>
