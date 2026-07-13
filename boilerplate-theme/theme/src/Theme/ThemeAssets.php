<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles enqueuing of theme scripts and styles.
 */
class ThemeAssets {
	/**
	 * Initialize asset enqueuing.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_editor_script' ) );
	}

	/**
	 * Enqueue scripts and styles for frontend.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_style(
			'boilerplate-theme-style',
			get_stylesheet_uri(),
			array(),
			BOILERPLATE_THEME_VERSION,
		);

		$script_path    = get_template_directory() . '/js/script.min.js';
		$script_version = file_exists( $script_path )
			? (string) filemtime( $script_path )
			: BOILERPLATE_THEME_VERSION;

		if ( file_exists( $script_path ) ) {
			wp_enqueue_script(
				'boilerplate-theme-script',
				get_template_directory_uri() . '/js/script.min.js',
				array(),
				$script_version,
				true,
			);
		}

		$custom_css = ThemeOptions::get_option( 'custom_css', '' );

		if ( ! empty( $custom_css ) ) {
			wp_add_inline_style(
				'boilerplate-theme-style',
				wp_kses(
					$custom_css,
					array(),
				),
			);
		}

		$custom_js = ThemeOptions::get_option( 'custom_js', '' );

		if ( ! empty( $custom_js ) && file_exists( $script_path ) ) {
			wp_add_inline_script(
				'boilerplate-theme-script',
				$custom_js,
			);
		}
	}

	/**
	 * Enqueue the block editor script.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_script(): void {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if (
			$current_screen &&
			$current_screen->is_block_editor() &&
			'widgets' !== $current_screen->id
		) {
			$editor_script = get_template_directory() . '/js/block-editor.min.js';

			if ( ! file_exists( $editor_script ) ) {
				return;
			}

			wp_enqueue_script(
				'boilerplate-theme-editor',
				get_template_directory_uri() . '/js/block-editor.min.js',
				array(
					'wp-blocks',
					'wp-edit-post',
					'wp-hooks',
					'wp-compose',
					'wp-block-editor',
					'wp-components',
					'wp-element',
				),
				BOILERPLATE_THEME_VERSION,
				true,
			);

			wp_add_inline_script(
				'boilerplate-theme-editor',
				"tailwindTypographyClasses = '" . esc_attr( BOILERPLATE_THEME_TYPOGRAPHY_CLASSES ) . "'.split(' ');",
				'before'
			);
		}
	}
}
