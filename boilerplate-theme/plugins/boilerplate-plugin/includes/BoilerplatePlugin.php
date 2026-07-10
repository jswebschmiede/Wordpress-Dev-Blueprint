<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplatePlugin;

use SmartMedia24\BoilerplatePlugin\Ajax\AjaxHandler;
use SmartMedia24\BoilerplatePlugin\Assets\Assets;
use SmartMedia24\BoilerplatePlugin\Backend\PluginOptions;
use SmartMedia24\BoilerplatePlugin\Shortcodes\Shortcode;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin bootstrap.
 */
class BoilerplatePlugin {
	private static ?self $instance = null;

	/**
	 * Prevents direct construction.
	 */
	private function __construct() {
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
	 * Gets the singleton instance.
	 *
	 * @return self Plugin instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Bootstraps the plugin modules.
	 *
	 * @return void
	 */
	public function run(): void {
		if ( defined( 'BOILERPLATE_PLUGIN_FILE' ) ) {
			load_plugin_textdomain(
				'boilerplate-plugin',
				false,
				dirname( plugin_basename( BOILERPLATE_PLUGIN_FILE ) ) . '/languages'
			);
		}

		( new PluginOptions() )->init();
		( new Assets() )->init();
		( new Shortcode() )->init();
		( new AjaxHandler() )->init();
	}
}
