<?php
/**
 * Template part for displaying the preloader style v6.
 *
 * @package BoilerplateTheme
 */
?>

<div class="visible z-50 fixed inset-0 flex justify-center items-center bg-white transition spinner-wrapper"
	id="loader">
	<div class="circle-loader circle-loader--v6" role="alert">
		<p class="circle-loader__label"><?php echo esc_html( (string) get_query_var( 'preloader_label' ) ); ?></p>
		<div aria-hidden="true">
			<svg class="circle-loader__svg" width="48" height="48" viewBox="0 0 48 48">
				<line class="circle-loader__fill" x1="6" y1="24" x2="42" y2="24" fill="none" stroke="currentColor"
					stroke-linecap="round" stroke-miterlimit="10" stroke-width="12" />
			</svg>
		</div>
	</div>
</div>
