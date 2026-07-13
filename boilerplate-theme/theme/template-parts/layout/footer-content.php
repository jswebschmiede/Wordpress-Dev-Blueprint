<?php
/**
 * Template part for displaying the footer content
 *
 * @package BoilerplateTheme
 */

use SmartMedia24\BoilerplateTheme\Theme\ThemeOptions;

$hide_footer_nav = (bool) apply_filters( 'boilerplate_theme_strip_header_footer_links', false );

$logo_footer = ThemeOptions::get_option( 'logo_footer', null );

$logo_footer_alt = sprintf(
	/* translators: %s: Site name */
	__( '%s Logo', 'boilerplate-theme' ),
	get_bloginfo( 'name' )
);
?>

<footer id="colophon" class="footer bg-primary text-white py-20 mt-14">
	<div
		class="flex flex-col md:gap-16 gap-8 justify-center items-center max-w-wide mx-auto w-p-1 lg:w-p-2">

		<div class="flex flex-col gap-12 items-center">

			<?php if ( $logo_footer ) : ?>
				<img class="xs:w-[240px] w-[120px] h-auto" src="<?php echo esc_url( $logo_footer['url'] ); ?>"
					alt="<?php echo esc_attr( $logo_footer_alt ); ?>">
			<?php endif; ?>

			<?php if ( ! $hide_footer_nav && has_nav_menu( 'footer-menu-1' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Footer-Menü', 'boilerplate-theme' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-menu-1',
							'menu_class'     => 'footer-menu',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>

		<div class="flex flex-col gap-3">
			<?php if ( ! $hide_footer_nav && has_nav_menu( 'footer-menu-2' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Footer-Menü', 'boilerplate-theme' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-menu-2',
							'menu_class'     => 'footer-menu',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( ! $hide_footer_nav && has_nav_menu( 'footer-menu-3' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Footer-Menü', 'boilerplate-theme' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-menu-3',
							'menu_class'     => 'footer-menu',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/layout/social-media' ); ?>
		</div>

		<?php if ( ThemeOptions::get_option( 'show_backtotop' ) ) : ?>
			<?php get_template_part( 'template-parts/layout/backtotop' ); ?>
		<?php endif; ?>
	</div>
</footer>
