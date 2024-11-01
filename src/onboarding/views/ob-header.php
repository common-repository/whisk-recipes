<?php
/**
 * Onboarding pages header
 *
 * @package whisk-recipes
 **/

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php esc_html_e( 'Welcome to Whisk Recipes Plugin', 'whisk-recipes' ); ?></title>
	<?php do_action( 'admin_enqueue_scripts' ); ?>
	<?php do_action( 'admin_print_styles' ); ?>
	<?php do_action( 'admin_head' ); ?>
</head>
<body class="whisk-setup wp-core-ui <?php echo esc_attr( 'whisk-setup-step__' . $this->step ); ?> <?php echo esc_attr( $data['wp_version_class'] ); ?>">
<div class="whisk-setup-wrapper">
	<h1 class="whisk-logo"><img src="<?php echo esc_url( $data['plugin_url'] ); ?>/assets/images/onboarding/whisk_logo.png" alt="<?php esc_attr( 'Whisk' ); ?>" /></h1>
