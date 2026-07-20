<?php

declare( strict_types=1 );

/**
 * Template for the CMB2 demo shortcode.
 *
 * @var string $headline Headline from CMB2 options.
 * @var string $intro    Intro text from CMB2 options.
 *
 * @package BoilerplatePlugin
 */

\defined( 'ABSPATH' ) || exit;

$headline = isset( $headline ) && is_string( $headline ) ? $headline : '';
$intro    = isset( $intro ) && is_string( $intro ) ? $intro : '';
?>

<div class="boilerplate-plugin-shortcode">
	<?php if ( '' !== $headline ) : ?>
		<h2 class="boilerplate-plugin-shortcode__title"><?php echo esc_html( $headline ); ?></h2>
	<?php endif; ?>

	<?php if ( '' !== $intro ) : ?>
		<p class="boilerplate-plugin-shortcode__intro"><?php echo esc_html( $intro ); ?></p>
	<?php endif; ?>
</div>
