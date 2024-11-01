<?php
/**
 * Share template part.
 *
 * @package whisk-recipes
 */
?>
<div class="likely likely-light likely-circle likely-medium likely-textless">
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
