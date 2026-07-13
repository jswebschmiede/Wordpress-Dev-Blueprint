<?php
/**
 * Template part for displaying the social media links.
 *
 * @package BoilerplateTheme
 */

use SmartMedia24\BoilerplateTheme\Theme\ThemeOptions;

$linkedin  = esc_url( ThemeOptions::get_option( 'linkedin', '' ) );
$facebook  = esc_url( ThemeOptions::get_option( 'facebook', '' ) );
$instagram = esc_url( ThemeOptions::get_option( 'instagram', '' ) );
$twitter   = esc_url( ThemeOptions::get_option( 'twitter', '' ) );
$youtube   = esc_url( ThemeOptions::get_option( 'youtube', '' ) );
$pinterest = esc_url( ThemeOptions::get_option( 'pinterest', '' ) );

?>

<div class="social-media__wrapper flex gap-4 text-white justify-center pt-4">
	<?php if ( $instagram ) : ?>
		<a title="Instagram" aria-label="<?php echo esc_attr( __( 'Folgen Sie uns auf Instagram', 'boilerplate-theme' ) ); ?>"
			target="_blank" rel="nofollow" href="<?php echo esc_url( $instagram ); ?>"
			class="text-4xl js-tooltip-trigger" data-tooltip-position="top">
			<i class="fa-brands fa-instagram" aria-hidden="true"></i>
		</a>
	<?php endif; ?>

	<?php if ( $facebook ) : ?>
		<a title="Facebook" aria-label="<?php echo esc_attr( __( 'Folgen Sie uns auf Facebook', 'boilerplate-theme' ) ); ?>"
			target="_blank" rel="nofollow" href="<?php echo esc_url( $facebook ); ?>"
			class="text-4xl js-tooltip-trigger" data-tooltip-position="top">
			<i class="fa-brands fa-square-facebook" aria-hidden="true"></i>
		</a>
	<?php endif; ?>

	<?php if ( $linkedin ) : ?>
		<a title="LinkedIn" aria-label="<?php echo esc_attr( __( 'Folgen Sie uns auf LinkedIn', 'boilerplate-theme' ) ); ?>"
			target="_blank" rel="nofollow" href="<?php echo esc_url( $linkedin ); ?>"
			class="text-4xl js-tooltip-trigger" data-tooltip-position="top">
			<i class="fa-brands fa-square-linkedin" aria-hidden="true"></i>
		</a>
	<?php endif; ?>

	<?php if ( $twitter ) : ?>
		<a title="Twitter / X" aria-label="<?php echo esc_attr( __( 'Folgen Sie uns auf Twitter / X', 'boilerplate-theme' ) ); ?>"
			target="_blank" rel="nofollow" href="<?php echo esc_url( $twitter ); ?>"
			class="text-4xl js-tooltip-trigger" data-tooltip-position="top">
			<i class="fa-brands fa-square-x-twitter" aria-hidden="true"></i>
		</a>
	<?php endif; ?>

	<?php if ( $youtube ) : ?>
		<a title="YouTube" aria-label="<?php echo esc_attr( __( 'Folgen Sie uns auf YouTube', 'boilerplate-theme' ) ); ?>"
			target="_blank" rel="nofollow" href="<?php echo esc_url( $youtube ); ?>"
			class="text-4xl js-tooltip-trigger" data-tooltip-position="top">
			<i class="fa-brands fa-youtube" aria-hidden="true"></i>
		</a>
	<?php endif; ?>

	<?php if ( $pinterest ) : ?>
		<a title="Pinterest" aria-label="<?php echo esc_attr( __( 'Folgen Sie uns auf Pinterest', 'boilerplate-theme' ) ); ?>"
			target="_blank" rel="nofollow" href="<?php echo esc_url( $pinterest ); ?>"
			class="text-4xl js-tooltip-trigger" data-tooltip-position="top">
			<i class="fa-brands fa-pinterest" aria-hidden="true"></i>
		</a>
	<?php endif; ?>
</div>
