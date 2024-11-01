<?php
/**
 * Onboarding steps representation
 *
 * @package whisk-recipes
 **/

?>
<ul class="whisk-ob-steps">
	<?php
	foreach ( $data['steps'] as $step_key => $step ) {
		if ( $step_key === $this->step ) {
			?>
			<li class="active"><?php echo esc_html( $step['name'] ); ?></li>
			<?php
		} else {
			?>
			<li><a class="whisk-link" href="<?php echo esc_url( $step['url'] ); ?>"><?php echo esc_html( $step['name'] ); ?></a></li>
			<?php
		}
	}
	?>
</ul>
