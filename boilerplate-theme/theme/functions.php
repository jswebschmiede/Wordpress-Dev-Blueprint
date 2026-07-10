<?php
/**
 * Boilerplate Theme functions and definitions.
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use SmartMedia24\BoilerplateTheme\Theme\ThemeManager;

defined( 'ABSPATH' ) || exit;

if ( file_exists( get_template_directory() . '/vendor/autoload.php' ) ) {
	require_once get_template_directory() . '/vendor/autoload.php';
}

if ( class_exists( ThemeManager::class ) ) {
	add_action(
		'after_setup_theme',
		static function (): void {
			ThemeManager::get_instance()->init();
		},
		1
	);
}
