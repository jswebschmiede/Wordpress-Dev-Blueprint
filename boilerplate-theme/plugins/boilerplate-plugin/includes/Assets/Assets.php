<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplatePlugin\Assets;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers and enqueues plugin assets.
 */
class Assets {
	/**
	 * Initializes asset hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ) );
	}

	/**
	 * Registers frontend assets.
	 *
	 * @return void
	 */
	public function register_frontend_assets(): void {
		$script_path = $this->get_plugin_dir_path() . 'build/frontend.js';
		$style_path  = $this->get_plugin_dir_path() . 'build/frontend.css';
		$version     = $this->get_file_version( $script_path );

		if ( false === $version ) {
			return;
		}

		wp_register_script(
			'boilerplate-plugin-frontend',
			$this->get_plugin_dir_url() . 'build/frontend.js',
			array( 'jquery' ),
			$version,
			true
		);

		$style_version = $this->get_file_version( $style_path );
		if ( false !== $style_version ) {
			wp_register_style(
				'boilerplate-plugin-frontend',
				$this->get_plugin_dir_url() . 'build/frontend.css',
				array(),
				$style_version
			);
		}
	}

	/**
	 * Registers admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function register_admin_assets( string $hook ): void {
		if ( 'settings_page_boilerplate-plugin' !== $hook ) {
			return;
		}

		$script_path = $this->get_plugin_dir_path() . 'build/dashboard.js';
		$style_path  = $this->get_plugin_dir_path() . 'build/dashboard.css';
		$version     = $this->get_file_version( $script_path );

		if ( false === $version ) {
			return;
		}

		wp_enqueue_script(
			'boilerplate-plugin-admin',
			$this->get_plugin_dir_url() . 'build/dashboard.js',
			array( 'jquery' ),
			$version,
			true
		);

		$style_version = $this->get_file_version( $style_path );
		if ( false !== $style_version ) {
			wp_enqueue_style(
				'boilerplate-plugin-admin',
				$this->get_plugin_dir_url() . 'build/dashboard.css',
				array(),
				$style_version
			);
		}
	}

	/**
	 * Enqueues frontend assets after registration.
	 *
	 * @return void
	 */
	public function enqueue_frontend(): void {
		$this->register_frontend_assets();

		if ( wp_script_is( 'boilerplate-plugin-frontend', 'registered' ) ) {
			wp_enqueue_script( 'boilerplate-plugin-frontend' );
		}

		if ( wp_style_is( 'boilerplate-plugin-frontend', 'registered' ) ) {
			wp_enqueue_style( 'boilerplate-plugin-frontend' );
		}
	}

	/**
	 * Gets the file version based on modification time.
	 *
	 * @param string $file_path Absolute path to the file.
	 * @return string|false File modification time or false if the file is missing.
	 */
	private function get_file_version( string $file_path ): string|false {
		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		return (string) filemtime( $file_path );
	}

	/**
	 * Gets the plugin root path.
	 *
	 * @return string Plugin root path with trailing slash.
	 */
	private function get_plugin_dir_path(): string {
		if ( \defined( 'BOILERPLATE_PLUGIN_FILE' ) ) {
			return plugin_dir_path( BOILERPLATE_PLUGIN_FILE );
		}

		return dirname( __DIR__, 2 ) . '/';
	}

	/**
	 * Gets the plugin root URL.
	 *
	 * @return string Plugin root URL with trailing slash.
	 */
	private function get_plugin_dir_url(): string {
		if ( \defined( 'BOILERPLATE_PLUGIN_FILE' ) ) {
			return plugin_dir_url( BOILERPLATE_PLUGIN_FILE );
		}

		return plugin_dir_url( dirname( __DIR__, 2 ) . '/boilerplate-plugin.php' );
	}
}
