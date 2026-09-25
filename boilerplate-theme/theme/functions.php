<?php
/**
 * Boilerplate Theme functions and definitions.
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use CompanyName\BoilerplateTheme\Theme\ThemeManager;

\defined( 'ABSPATH' ) || exit;

/**
 * Loads TimberIntegration when Composer autoload has not registered it.
 *
 * @return void
 */
function boilerplate_theme_load_timber_integration(): void {
	if ( class_exists( \CompanyName\BoilerplateTheme\Theme\TimberIntegration::class, false ) ) {
		return;
	}

	$integration_file = get_template_directory() . '/src/Theme/TimberIntegration.php';

	if ( is_readable( $integration_file ) ) {
		require_once $integration_file;
	}
}

/**
 * Stops a template stub when prefixed Timber cannot render Twig.
 *
 * Logged-in administrators see setup guidance. Visitors see a generic message.
 *
 * @return bool True when the stub must return without calling Timber.
 */
function boilerplate_theme_bail_if_timber_unavailable(): bool {
	boilerplate_theme_load_timber_integration();

	if ( class_exists( \CompanyName\BoilerplateTheme\Theme\TimberIntegration::class, false ) ) {
		return \CompanyName\BoilerplateTheme\Theme\TimberIntegration::bail_if_unavailable();
	}

	if ( class_exists( \CompanyName\BoilerplateTheme\Timber\Timber::class ) ) {
		return false;
	}

	if ( ! headers_sent() && \function_exists( 'status_header' ) ) {
		status_header( 503 );
	}

	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>';
	echo esc_html__( 'Vorübergehend nicht verfügbar', 'boilerplate-theme' );
	echo '</title></head><body><p>';
	echo esc_html__( 'Diese Website ist vorübergehend nicht verfügbar.', 'boilerplate-theme' );
	echo '</p></body></html>';

	return true;
}

$boilerplate_theme_autoload = get_template_directory() . '/vendor-prefixed/autoload.php';

if ( ! file_exists( $boilerplate_theme_autoload ) ) {
	boilerplate_theme_load_timber_integration();

	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$message = class_exists( \CompanyName\BoilerplateTheme\Theme\TimberIntegration::class, false )
				? \CompanyName\BoilerplateTheme\Theme\TimberIntegration::get_missing_dependency_message()
				: \__(
					'vendor-prefixed fehlt. Bitte führen Sie „composer install“ im Theme-Verzeichnis aus, um die Abhängigkeiten (inkl. Timber) zu generieren.',
					'boilerplate-theme'
				);

			echo '<div class="notice notice-error"><p><strong>Boilerplate Theme:</strong> ';
			echo esc_html( $message );
			echo '</p></div>';
		}
	);
	return;
}

require_once $boilerplate_theme_autoload;

/**
 * ThemeManager bootstraps Timber (via TimberIntegration) and the rest of the theme
 * on after_setup_theme. Templates render Twig views from views/.
 */
if ( class_exists( ThemeManager::class ) ) {
	add_action(
		'after_setup_theme',
		static function (): void {
			ThemeManager::get_instance()->init();
		},
		1
	);
}

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';
