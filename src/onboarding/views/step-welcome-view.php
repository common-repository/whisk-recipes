<?php
/**
 * Onboarding welcome step view.
 *
 * @package whisk-recipes
 **/

?>
<h2 class="whisk-h2"><?php esc_html_e( 'Welcome to Whisk Recipes', 'whisk-recipes' ); ?></h2>
<div class="content-wrapper">
	<p><?php esc_html_e( 'Setup process is designed to configure Whisk Recipes WordPress plugin to better suit your website. You can skip all steps now or change everything later.', 'whisk-recipes' ); ?></p>
</div>

<a class="whisk-go-button" href="<?php echo esc_url( $this->get_next_step_url( 'welcome' ) ); ?>">
	<?php esc_html_e( 'Let’s Go!', 'whisk-recipes' ); ?>
</a>
