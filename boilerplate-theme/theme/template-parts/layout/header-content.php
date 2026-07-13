<?php
/**
 * Template part for displaying the header content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package BoilerplateTheme
 */

use SmartMedia24\BoilerplateTheme\Theme\NavWalker;

$hide_header_nav = (bool) apply_filters( 'boilerplate_theme_strip_header_footer_links', false );

?>

<header id="masthead" class="bg-white">
	<div class="f-header relative z-5 flex items-center js-f-header w-full h-(--f-header-height)"
		data-element="header">

		<div class="mx-auto w-p-1 max-w-7xl f-header__mobile-content">

			<?php boilerplate_theme_render_site_branding(); ?>

			<?php if ( ! $hide_header_nav ) : ?>
				<div class="f-header__extra-links-wrapper lg:hidden flex">
					<?php get_template_part( 'template-parts/layout/header-extra-links' ); ?>

					<button
						class="anim-menu-btn js-anim-menu-btn f-header__nav-control js-tab-focus text-primary"
						aria-label="<?php esc_attr_e( 'Menü umschalten', 'boilerplate-theme' ); ?>">
						<i class="anim-menu-btn__icon anim-menu-btn__icon--close" aria-hidden="true"></i>
					</button>
				</div>
			<?php endif; ?>
		</div>

		<div class="f-header__nav" role="navigation">
			<div
				class="justify-between f-header__nav-grid mx-auto xl:w-[calc(100%-12rem)] w-p-1">
				<div class="f-header__nav-logo-wrapper grow">
					<?php boilerplate_theme_render_site_branding(); ?>
				</div>

				<?php if ( ! $hide_header_nav && has_nav_menu( 'header-menu' ) ) : ?>
					<nav id="site-navigation"
						aria-label="<?php esc_attr_e( 'Hauptnavigation', 'boilerplate-theme' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'header-menu',
								'menu_id'        => 'primary-menu',
								'container'      => '',
								'items_wrap'     => '<ul id="%1$s" class="flex lg:flex-row flex-col justify-end gap-1 lg:gap-8 xl:gap-20 %2$s f-header__list grow" aria-label="submenu">%3$s</ul>',
								'walker'         => new NavWalker(),
								'fallback_cb'    => array( NavWalker::class, 'fallback' ),
							),
						);
						?>
					</nav>
				<?php endif; ?>

				<?php if ( ! $hide_header_nav ) : ?>
					<div class="f-header__right-wrapper hidden lg:flex ml-12 xl:ml-28 -mt-1.5">
						<?php get_template_part( 'template-parts/layout/header-extra-links' ); ?>
					</div>
				<?php endif; ?>

				<div class="f-header__social-media-wrapper lg:hidden block w-full">
					<?php get_template_part( 'template-parts/layout/social-media' ); ?>
				</div>
			</div>
		</div>
	</div>
</header>
