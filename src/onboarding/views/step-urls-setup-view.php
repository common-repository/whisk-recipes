<?php
/**
 * Onboarding urls setup step view
 *
 * @package whisk-recipes
 **/

?>
<h2 class="whisk-h2"><?php esc_html_e( 'Setup recipe URLs', 'whisk-recipes' ); ?></h2>
<p><?php esc_html_e( 'Here you can choose URL structure for your recipes. If you are creating recipes collection from scratch, select Semantic URLs.', 'whisk-recipes' ); ?></p>
<p><?php esc_html_e( 'Otherwise, if you already have recipes inside WordPress posts, use Native WordPress URLs.', 'whisk-recipes' ); ?></p>

<form method="post" class="address-step">
	<input type="hidden" name="save_step" value="urls_setup" />
	<div class="semantic-urls-group">
		<div class="input-group">
			<input id="semanticType" type="radio" name="type" value="semantic" checked>
			<label for="semanticType"><?php esc_html_e( 'Semantic URLs', 'whisk-recipes' ); ?></label>
		</div>
		<label class="select-label" for="urlsBase"><?php esc_html_e( 'Recipe base', 'whisk-recipes' ); ?></label>
		<span class="select-container">
			<svg viewBox="0 0 1024 1024"><path d="M511.976 768.002c-10.236 0-20.47-3.904-28.282-11.712l-344.154-344.098c-15.624-15.62-15.624-40.946-0.006-56.57 15.622-15.622 40.948-15.624 56.568-0.004l315.876 315.82 315.922-315.868c15.624-15.618 40.952-15.616 56.568 0.004 15.62 15.624 15.618 40.95-0.006 56.57l-344.204 344.144c-7.81 7.81-18.046 11.714-28.282 11.714z" class="css-kqzqgg"></path></svg>
			<select id="urlsBase" name="urls_base">
				<option value="meal_type" selected><?php esc_html_e( 'Meal Type', 'whisk-recipes' ); ?></option>
				<option value="avoidance"><?php esc_html_e( 'Avoidance', 'whisk-recipes' ); ?></option>
				<option value="cooking_technique"><?php esc_html_e( 'Cooking Technique', 'whisk-recipes' ); ?></option>
				<option value="cuisine"><?php esc_html_e( 'Cuisine', 'whisk-recipes' ); ?></option>
				<option value="diet"><?php esc_html_e( 'Diet', 'whisk-recipes' ); ?></option>
				<option value="ingredient"><?php esc_html_e( 'Ingredient', 'whisk-recipes' ); ?></option>
				<option value="nutrition"><?php esc_html_e( 'Nutrition', 'whisk-recipes' ); ?></option>
			</select>
		</span>
	</div>
	<div class="divider"></div>
	<div class="native-urls-group">
		<div class="input-group">
			<input id="nativeType" type="radio" name="type" value="native">
			<label for="nativeType"><?php esc_html_e( 'Native WordPress URLs', 'whisk-recipes' ); ?></label>
		</div>
	</div>
	<p class="recipe-example"><?php esc_html_e( 'Example of how your URLs will look like: ', 'whisk-recipes' ); ?><strong><?php echo esc_url( get_site_url() ); ?><span id="whisk-recipe-url" data-structure="<?php echo esc_html( get_option( 'permalink_structure' ) ); ?>">/dinner/your-awesome-recipe</span></strong></p>
	<a id="back-button" class="whisk-go-button whisk-go-button__secondary whisk-go-button__back" href="<?php echo esc_url( $this->get_previous_step_url( 'urls_setup' ) ); ?>">
		<?php esc_html_e( 'Back', 'whisk-recipes' ); ?>
	</a>
	<button type="submit" class="whisk-go-button"><?php esc_html_e( 'Continue', 'whisk-recipes' ); ?></button>
</form>
