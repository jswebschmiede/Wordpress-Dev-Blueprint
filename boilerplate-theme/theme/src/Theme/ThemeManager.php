<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

use SmartMedia24\BoilerplateTheme\Blocks\BlockManager;

defined( 'ABSPATH' ) || exit;

/**
 * Manages initialization of theme functionality.
 */
class ThemeManager {
	private static ?self $instance = null;

	/**
	 * Prevents direct construction.
	 */
	private function __construct() {
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return self Theme manager instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevents cloning the singleton instance.
	 */
	private function __clone(): void {
	}

	/**
	 * Prevents unserializing the singleton instance.
	 *
	 * @throws \Exception When unserializing is attempted.
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Initializes all theme functionality.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->set_constants();
		$this->setup_theme_support();

		$block_manager = new BlockManager();
		$block_manager->init();
	}

	/**
	 * Defines theme constants.
	 *
	 * @return void
	 */
	private function set_constants(): void {
		if ( ! defined( 'BOILERPLATE_THEME_VERSION' ) ) {
			define( 'BOILERPLATE_THEME_VERSION', '1.0.0' );
		}
	}

	/**
	 * Registers minimal WordPress theme support features.
	 *
	 * @return void
	 */
	private function setup_theme_support(): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'style-editor.css' );
	}
}
