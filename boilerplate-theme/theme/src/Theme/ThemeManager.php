<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

use CompanyName\BoilerplateTheme\Blocks\BlockManager;
use CompanyName\BoilerplateTheme\PostTypes\ExamplePostType;
use CompanyName\BoilerplateTheme\Utils\SvgSupport;

\defined( 'ABSPATH' ) || exit;

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

		$timber_integration = new TimberIntegration();
		$timber_integration->init();

		$theme_setup = new ThemeSetup();
		$theme_setup->init();

		$comments_disabled = new CommentsDisabled();
		$comments_disabled->init();

		$example_post_type = new ExamplePostType();
		$example_post_type->init();

		$svg_support = new SvgSupport();
		$svg_support->init();

		$theme_assets = new ThemeAssets();
		$theme_assets->init();

		$block_manager = new BlockManager();
		$block_manager->init();

		if ( class_exists( 'Redux' ) ) {
			$theme_options = new ThemeOptions();
			$theme_options->init();
		} else {
			add_action(
				'admin_notices',
				static function (): void {
					echo '<div class="notice notice-error"><p>'
						. esc_html__( 'Redux Framework ist nicht installiert. Bitte installieren Sie es, um die Theme-Optionen zu nutzen.', 'boilerplate-theme' )
						. ' <a href="https://wordpress.org/plugins/redux-framework/" target="_blank" rel="noopener noreferrer">Redux Framework</a></p></div>';
				},
			);
		}

		$theme_login_security = new ThemeLoginSecurity();
		$theme_login_security->init();
	}

	/**
	 * Defines theme constants.
	 *
	 * @return void
	 */
	private function set_constants(): void {
		if ( ! \defined( 'BOILERPLATE_THEME_VERSION' ) ) {
			\define( 'BOILERPLATE_THEME_VERSION', '1.0.0' );
		}

		if ( ! \defined( 'BOILERPLATE_THEME_TYPOGRAPHY_CLASSES' ) ) {
			\define(
				'BOILERPLATE_THEME_TYPOGRAPHY_CLASSES',
				'prose md:prose-lg lg:prose-xl lg:leading-7 leading-6 prose-boilerplate-theme max-w-none prose-a:no-underline prose-a:hover:underline prose-ul:leading-relaxed prose-ol:leading-relaxed prose-p:last:mb-0',
			);
		}
	}
}
