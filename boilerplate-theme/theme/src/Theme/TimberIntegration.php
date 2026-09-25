<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

use CompanyName\BoilerplateTheme\Timber\Timber;
use CompanyName\BoilerplateTheme\Twig\Environment;
use CompanyName\BoilerplateTheme\Twig\TwigFunction;

\defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps Timber and enriches the global Twig context.
 */
class TimberIntegration {
	/**
	 * Menu locations registered in ThemeSetup mapped to their max depth (0 = unlimited).
	 *
	 * @var array<string, int>
	 */
	private const array MENU_LOCATIONS = array(
		'header-menu'   => 0,
		'footer-menu-1' => 1,
		'footer-menu-2' => 1,
		'footer-menu-3' => 1,
	);

	/**
	 * Checks whether the Strauss-prefixed Timber class can be autoloaded.
	 *
	 * @return bool True if Timber is available in vendor-prefixed, false otherwise.
	 */
	public static function is_available(): bool {
		return class_exists( Timber::class );
	}

	/**
	 * Initializes Timber and registers context filters.
	 *
	 * @return void
	 */
	public function init(): void {
		Timber::init();

		Timber::$dirname = array( 'views' );

		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
	}

	/**
	 * Registers theme-specific Twig functions.
	 *
	 * @param Environment $twig Twig environment provided by Timber.
	 * @return Environment
	 */
	public function add_to_twig( Environment $twig ): Environment {
		$twig->addFunction(
			new TwigFunction(
				'breadcrumb_items',
				static fn ( bool $show_on_home = false, bool $show_current = true ): array => ( new Breadcrumb( $show_on_home, $show_current ) )->get_crumbs()
			)
		);

		$twig->addFunction( new TwigFunction( 'the_content', array( $this, 'get_the_content_html' ) ) );

		return $twig;
	}

	/**
	 * Returns the filtered content of the current post, equivalent to WordPress' the_content().
	 *
	 * Unlike Timber's `post.content`, this honours `<!--more-->` (teaser + more link in lists,
	 * `#more-{ID}` anchor on singular views). Relies on the global post that Timber sets up for
	 * singular contexts and while looping over posts.
	 *
	 * @param string|null $more_link_text Optional "read more" link text for teasers.
	 * @return string Filtered post content HTML.
	 */
	public function get_the_content_html( ?string $more_link_text = null ): string {
		$content = apply_filters( 'the_content', get_the_content( $more_link_text ) );

		return str_replace( ']]>', ']]&gt;', (string) $content );
	}

	/**
	 * Adds curated theme options and menus to the global Timber context.
	 *
	 * @param array<string, mixed> $context Global Timber context.
	 * @return array<string, mixed>
	 */
	public function add_to_context( array $context ): array {
		$context['options']                   = $this->get_theme_options_for_context();
		$context['search_url']                = $this->get_search_url();
		$context['typography_classes']        = \defined( 'BOILERPLATE_THEME_TYPOGRAPHY_CLASSES' ) ? BOILERPLATE_THEME_TYPOGRAPHY_CLASSES : '';
		$context['strip_header_footer_links'] = (bool) apply_filters( 'boilerplate_theme_strip_header_footer_links', false );

		foreach ( self::MENU_LOCATIONS as $location => $depth ) {
			$context_key = str_replace( '-', '_', $location );

			// get_menu_by() avoids Timber::get_menu()'s slug/name fallback for unassigned locations.
			$context[ $context_key ] = Timber::get_menu_by( 'location', $location, array( 'depth' => $depth ) );
		}

		return $context;
	}

	/**
	 * Builds a curated options array for Twig (defaults work without Redux).
	 *
	 * Security and asset injection keys stay out of the frontend context.
	 *
	 * @return array<string, mixed>
	 */
	private function get_theme_options_for_context(): array {
		$empty_media = array(
			'url' => '',
		);

		return array(
			'logo'            => ThemeOptions::get_option( 'logo', $empty_media ),
			'logo_footer'     => ThemeOptions::get_option( 'logo_footer', $empty_media ),
			'website_title'   => ThemeOptions::get_option( 'website_title', get_bloginfo( 'name' ) ),
			'show_breadcrumb' => $this->theme_option_flag( 'show_breadcrumb', true ),
			'show_preloader'  => $this->theme_option_flag( 'show_preloader', true ),
			'preloader_style' => (string) ThemeOptions::get_option( 'preloader_style', 'v1' ),
			'show_backtotop'  => $this->theme_option_flag( 'show_backtotop', true ),
			'social'          => array(
				'facebook'  => (string) ThemeOptions::get_option( 'facebook', '' ),
				'twitter'   => (string) ThemeOptions::get_option( 'twitter', '' ),
				'instagram' => (string) ThemeOptions::get_option( 'instagram', '' ),
				'linkedin'  => (string) ThemeOptions::get_option( 'linkedin', '' ),
				'youtube'   => (string) ThemeOptions::get_option( 'youtube', '' ),
				'pinterest' => (string) ThemeOptions::get_option( 'pinterest', '' ),
			),
			'error_title'     => (string) ThemeOptions::get_option(
				'error_title',
				esc_html__( 'Seite nicht gefunden', 'boilerplate-theme' )
			),
			'error_text'      => (string) ThemeOptions::get_option(
				'error_text',
				esc_html__(
					'Diese Seite konnte nicht gefunden werden. Sie wurde möglicherweise entfernt oder umbenannt, oder sie hat möglicherweise nie existiert.',
					'boilerplate-theme'
				)
			),
			'error_btn'       => (string) ThemeOptions::get_option(
				'error_btn',
				esc_html__( 'Zur Startseite', 'boilerplate-theme' )
			),
		);
	}

	/**
	 * Reads a Redux switch into a real boolean for Twig `{% if %}` checks.
	 *
	 * @param string $key           Option key.
	 * @param bool   $default_value Value when Redux is unavailable or the option is blank.
	 * @return bool Whether the switch is on.
	 */
	private function theme_option_flag( string $key, bool $default_value ): bool {
		return ThemeOptions::cast_switch( ThemeOptions::get_option( $key, $default_value ) );
	}

	/**
	 * Resolves the themed search page URL when the helper is available.
	 *
	 * @return string Search form action URL.
	 */
	private function get_search_url(): string {
		if ( \function_exists( 'boilerplate_theme_get_search_page_url' ) ) {
			return boilerplate_theme_get_search_page_url();
		}

		return home_url( '/' );
	}
}
