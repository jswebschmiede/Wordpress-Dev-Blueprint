<?php
/**
 * Template part for displaying the preloader style v5.
 *
 * @package BoilerplateTheme
 */
?>

<div class="visible z-50 fixed inset-0 flex justify-center items-center bg-white transition spinner-wrapper"
	id="loader">
	<div class="circle-loader circle-loader--v5" role="alert">
		<p class="circle-loader__label"><?php echo esc_html( (string) get_query_var( 'preloader_label' ) ); ?></p>
		<div aria-hidden="true">
			<div class="circle-loader__shadow"></div>
			<div class="circle-loader__ball"></div>
		</div>
	</div>
</div>
