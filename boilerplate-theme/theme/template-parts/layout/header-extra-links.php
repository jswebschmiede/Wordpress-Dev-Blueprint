<?php
/**
 * Template part for displaying header utility links.
 *
 * @package BoilerplateTheme
 */
?>

<div class="f-header__nav-extra-links flex xl:gap-8 xs:gap-6 gap-4 items-center mr-4 xs:mr-8 lg:mr-0">
	<a
		href="<?php echo esc_url( boilerplate_theme_get_search_page_url() ); ?>"
		class="f-header__nav-search-button js-tooltip-trigger cursor-pointer text-primary text-2xl lg:text-3xl leading-none hover:text-secondary transition-colors duration-300"
		aria-label="<?php esc_attr_e( 'Suche öffnen', 'boilerplate-theme' ); ?>"
		data-tooltip-position="left"
		title="<?php esc_attr_e( 'Suche öffnen', 'boilerplate-theme' ); ?>">
		<i class="f-header__nav-search-icon fas fa-search" aria-hidden="true"></i>
		<span class="sr-only"><?php esc_html_e( 'Suche öffnen', 'boilerplate-theme' ); ?></span>
	</a>
</div>
