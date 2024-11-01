<?php
/**
 * Onboarding studio connect step view
 *
 * @package whisk-recipes
 **/
use Whisk\Recipes\Controllers\API;
use Whisk\Recipes\Utils;
?>
<h2 class="whisk-h2"><?php esc_html_e( 'Connect to Whisk Studio', 'whisk-recipes' ); ?></h2>
<?php // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment  ?>
<p><?php echo sprintf( esc_html__( 'In order to have Nutrition Data, Auto-tagging and full Ingredient Data you must have working connection with Whisk Studio API. Create business account at %1$s and get API keys from this page %2$s. After that, copy and paste them into fields below and click Continue. After sucessfull integration all new recipes should start getting enhanced data.', 'whisk-recipes' ), '<a class="whisk-link" href="https://studio.whisk.com">https://studio.whisk.com</a>', '<a class="whisk-link" href="https://studio.whisk.com/api">https://studio.whisk.com/api</a>' ); ?></p>
<div class="row">
	<div class="column">
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>
		<img src="<?php echo Utils::get_plugin_file_uri( 'assets/images/onboarding/studio_1.png' ); ?>" alt="">
		<h3 class="whisk-h3">
			<?php esc_html_e( 'Nutrition Data', 'whisk-recipes' ); ?>
		</h3>
	</div>
	<div class="column">
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>
		<img src="<?php echo Utils::get_plugin_file_uri( 'assets/images/onboarding/studio_2.png' ); ?>" alt="">
		<h3 class="whisk-h3">
			<?php esc_html_e( 'Auto-tagging recipes', 'whisk-recipes' ); ?>
		</h3>
	</div>
</div>
<div class="divider"></div>
<form id="studioLoginForm" method="post" action="<?php echo esc_url( $this->get_step_url( 'ready' ) ); ?>">
	<div id="errorMessage" class="error-message" style="display: none"></div>
	<div id="successMessage" class="success-message" style="display: none"></div>
	<div class="row">
		<p>Copy & paste the Key from <a class="whisk-link" href="https://studio.whisk.com/api-keys/key-management" target="_blank"><u>Recipe Content Platform API backend</u></a></p>
	</div>
	<div class="form-row">
		<div class="form-group">
			<label for="studio_api_key"><?php esc_html_e( 'Key', 'whisk-recipes' ); ?></label>
			<input id="studio_api_key" type="text" name="studio_api_key" />
		</div>
		<div class="form-group">
			<label for="studio_integration_id"><?php esc_html_e( 'Integration ID', 'whisk-recipes' ); ?></label>
			<input id="studio_integration_id" type="text" name="studio_integration_id" />
		</div>
	</div>
	<div class="row">
		<a id="back-button" class="whisk-go-button whisk-go-button__secondary whisk-go-button__back" href="<?php echo esc_url( $this->get_previous_step_url( 'studio_connect' ) ); ?>">
			<?php esc_html_e( 'Back', 'whisk-recipes' ); ?>
		</a>
		<input id="continue-button" type="submit" value="<?php esc_html_e( 'Continue', 'whisk-recipes' ); ?>" class="whisk-go-button" />
		<a id="skip-button" class="whisk-go-button whisk-go-button__secondary whisk-go-button__skip" href="<?php echo esc_url( $this->get_next_step_url( 'studio_connect' ) ); ?>">
			<?php esc_html_e( 'Skip this step', 'whisk-recipes' ); ?>
		</a>
	</div>
</form>
