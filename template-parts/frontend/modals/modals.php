<?php
/**
 * Modals.
 */

use Whisk\Recipes\Models\Recipe;

$recipe_id = get_the_ID();
?>
<div class="whisk-template">
	<div class="whisk-modal" id="whisk-modal-photo">
		<div class="modal2-close"></div>
		<img width="100%" />
	</div>
</div>

<div class="whisk-template">
	<div class="whisk-modal" id="whisk-modal-video">
		<div class="modal2-close"></div>
		<div class="whisk-oembed">
			<?php Recipe::the_recipe_video( $recipe_id ); ?>
		</div>
	</div>
</div>

<div class="whisk-template">
	<div class="whisk-modal" id="whisk-modal-ingredients">
		<div class="modal2-close"></div>
		<h2 class="whisk-h2">Ingredients</h2>
		<p>Ingredients was where</p>
	</div>
</div>
