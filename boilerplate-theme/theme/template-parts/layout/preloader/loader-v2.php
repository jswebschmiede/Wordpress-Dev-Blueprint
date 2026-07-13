<?php
/**
 * Template part for displaying the preloader style v2.
 *
 * @package BoilerplateTheme
 */
?>

<div class="visible z-50 fixed inset-0 flex justify-center items-center bg-white transition spinner-wrapper"
	id="loader">
	<div class="circle-loader circle-loader--v2" role="alert">
		<p class="circle-loader__label"><?php echo esc_html( (string) get_query_var( 'preloader_label' ) ); ?></p>
		<div aria-hidden="true">
			<svg class="circle-loader__svg" width="48" height="48" viewBox="0 0 48 48">
				<circle class="circle-loader__base" cx="24" cy="24" r="19" fill="none" stroke="currentColor"
					stroke-width="2" />
				<circle class="circle-loader__fill" cx="24" cy="24" r="19" fill="none" stroke="currentColor"
					stroke-width="2" />
			</svg>
		</div>
	</div>
</div>
