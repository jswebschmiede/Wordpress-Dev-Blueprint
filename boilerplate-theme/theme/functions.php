<?php
/**
 * Boilerplate Theme functions and definitions.
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use CompanyName\BoilerplateTheme\Theme\ThemeManager;

\defined( 'ABSPATH' ) || exit;

$boilerplate_theme_autoload = get_template_directory() . '/vendor-prefixed/autoload.php';

if ( ! file_exists( $boilerplate_theme_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p><strong>Boilerplate Theme:</strong> ';
			echo esc_html__(
				'vendor-prefixed fehlt. Bitte führen Sie „composer install“ im Theme-Verzeichnis aus, um die Abhängigkeiten zu generieren.',
				'boilerplate-theme'
			);
			echo '</p></div>';
		}
	);
	return;
}

require_once $boilerplate_theme_autoload;

/**
 * ThemeManager bootstraps Timber (via TimberIntegration) and the rest of the theme
 * on after_setup_theme. Existing classic PHP templates keep working unchanged in Phase 0.
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
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';
