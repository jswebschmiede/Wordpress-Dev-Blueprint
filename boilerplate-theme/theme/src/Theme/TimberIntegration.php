<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

use CompanyName\BoilerplateTheme\Timber\Timber;
use CompanyName\BoilerplateTheme\Twig\Environment;
use CompanyName\BoilerplateTheme\Twig\TwigFunction;

\defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps Timber, enriches the global Twig context, and renders a safe frontend fallback when Timber is missing.
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
	 * Returns the admin-facing explanation when prefixed Timber cannot be loaded.
	 *
	 * @return string Plain-text guidance. Callers must escape before printing.
	 */
	public static function get_missing_dependency_message(): string {
		$autoload = get_template_directory() . '/vendor-prefixed/autoload.php';

		if ( ! file_exists( $autoload ) ) {
			return \__(
				'vendor-prefixed fehlt. Bitte führen Sie „composer install“ im Theme-Verzeichnis aus, um die Abhängigkeiten (inkl. Timber) zu generieren.',
				'boilerplate-theme'
			);
		}

		return \__(
			'Timber wurde in vendor-prefixed nicht gefunden. Bitte führen Sie „composer install“ im Theme-Verzeichnis aus, damit Strauss Timber prefixiert. Ohne Timber können die Twig-Templates nicht gerendert werden.',
			'boilerplate-theme'
		);
	}

	/**
	 * Prints a minimal HTML document when Twig cannot be rendered.
	 *
	 * Administrators see the same guidance as the admin notice. Visitors see a generic message.
	 *
	 * @return void
	 */
	public static function render_unavailable_fallback(): void {
		$show_details = \function_exists( 'current_user_can' ) && current_user_can( 'manage_options' );
		$message      = $show_details
			? self::get_missing_dependency_message()
			: \__( 'Diese Website ist vorübergehend nicht verfügbar.', 'boilerplate-theme' );
		$title        = $show_details
			? \__( 'Theme-Abhängigkeit fehlt', 'boilerplate-theme' )
			: \__( 'Vorübergehend nicht verfügbar', 'boilerplate-theme' );
		$language     = \function_exists( 'get_bloginfo' ) ? (string) get_bloginfo( 'language' ) : '';

		if ( '' === $language ) {
			$language = 'en';
		}

		if ( ! headers_sent() ) {
			if ( \function_exists( 'status_header' ) ) {
				status_header( 503 );
			}

			if ( \function_exists( 'nocache_headers' ) ) {
				nocache_headers();
			}
		}

		echo '<!DOCTYPE html><html lang="' . esc_attr( $language ) . '"><head><meta charset="utf-8">';
		echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
		echo '<meta name="robots" content="noindex">';
		echo '<title>' . esc_html( $title ) . '</title></head><body>';

		if ( $show_details ) {
			echo '<p><strong>Boilerplate Theme:</strong> ' . esc_html( $message ) . '</p>';
		} else {
			echo '<p>' . esc_html( $message ) . '</p>';
		}

		echo '</body></html>';
	}

	/**
	 * Renders the frontend fallback and signals template stubs to stop.
	 *
	 * @return bool True when Timber is unavailable and the fallback was printed.
	 */
	public static function bail_if_unavailable(): bool {
		if ( self::is_available() ) {
			return false;
		}

		self::render_unavailable_fallback();

		return true;
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
