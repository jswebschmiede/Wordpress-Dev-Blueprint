<?php

declare( strict_types=1 );

/**
 * Template for the example shortcode.
 *
 * @var array<string, mixed> $atts Shortcode attributes.
 *
 * @package BoilerplatePlugin
 */

\defined( 'ABSPATH' ) || exit;

$title = isset( $atts['title'] ) && is_scalar( $atts['title'] ) ? (string) $atts['title'] : '';
?>

<div class="boilerplate-plugin-shortcode">
	<h2 class="boilerplate-plugin-shortcode__title"><?php echo esc_html( $title ); ?></h2>
	<p class="boilerplate-plugin-shortcode__text">
		<?php echo esc_html__( 'This shortcode output comes from the boilerplate plugin.', 'boilerplate-plugin' ); ?>
	</p>
</div>
