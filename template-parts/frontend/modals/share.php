<?php
/**
 * Share list modal.
 *
 * @package whisk-recipes
 */

?>
<div class="whisk-template">
	<div class="whisk-modal" id="whisk-modal-share">
		<div class="modal2-close"></div>
		<h2 class="whisk-h2">Share List</h2>
		<p>Heat a tablespoon of butter and a tablespoon of olive oil in a large pan. Wait until the pan is hot and the butter has fully melted.</p>
		<div class="likely likely-light likely-circle">
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_twitter' ) ) : ?>
				<div class="twitter">Twitter</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_facebook' ) ) : ?>
				<div class="facebook">Facebook</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_vkontakte' ) ) : ?>
				<div class="vkontakte">Vkontakte</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_linkedin' ) ) : ?>
				<div class="linkedin">Linkedin</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_pinterest' ) ) : ?>
				<div class="pinterest">Pinterest</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_whatsapp' ) ) : ?>
				<div class="whatsapp">WhatsApp</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_telegram' ) ) : ?>
				<div class="telegram">Telegram</div>
			<?php endif; ?>
			<?php if ( whisk_carbon_get_theme_option( 'whisk_share_viber' ) ) : ?>
				<div class="viber">Viber</div>
			<?php endif; ?>
		</div>
	</div>
</div>
