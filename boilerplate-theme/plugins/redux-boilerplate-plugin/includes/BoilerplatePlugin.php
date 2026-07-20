<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin;

use CompanyName\BoilerplatePlugin\Assets\Assets;
use CompanyName\BoilerplatePlugin\Backend\PluginOptions;
use CompanyName\BoilerplatePlugin\Shortcodes\Shortcode;

\defined( 'ABSPATH' ) || exit;

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
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		( new PluginOptions() )->init();
		( new Assets() )->init();
		( new Shortcode() )->init();
	}

	/**
	 * Loads the plugin text domain on init (required since WordPress 6.7).
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		if ( ! \defined( 'BOILERPLATE_PLUGIN_FILE' ) ) {
			return;
		}

		load_plugin_textdomain(
			'boilerplate-plugin',
			false,
			dirname( plugin_basename( BOILERPLATE_PLUGIN_FILE ) ) . '/languages'
		);
	}
}
