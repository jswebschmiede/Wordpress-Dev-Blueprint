<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles basic theme setup and WordPress features.
 */
class ThemeSetup {
	/**
	 * Initialize theme setup.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );
		add_action( 'admin_menu', array( $this, 'add_gutenberg_template_menu' ) );
	}

	/**
	 * Add a submenu page to the themes menu to manage Gutenberg templates.
	 *
	 * @return void
	 */
	public function add_gutenberg_template_menu(): void {
		add_submenu_page(
			'themes.php',
			__( 'Gutenberg Vorlagen', 'boilerplate-theme' ),
			__( 'Gutenberg Vorlagen', 'boilerplate-theme' ),
			'edit_posts',
			'edit.php?post_type=wp_block'
		);
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @return void
	 */
	public function setup_theme(): void {
		load_theme_textdomain( 'boilerplate-theme', get_template_directory() . '/languages' );

		register_nav_menus(
			array(
				'header-menu'   => __( 'Header Menu', 'boilerplate-theme' ),
				'footer-menu-1' => __( 'Footer Menu 1', 'boilerplate-theme' ),
				'footer-menu-2' => __( 'Footer Menu 2', 'boilerplate-theme' ),
				'footer-menu-3' => __( 'Footer Menu 3', 'boilerplate-theme' ),
			),
		);

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'widgets' );
		add_theme_support( 'widgets-block-editor' );
		add_theme_support(
			'html5',
			array(
				'gallery',
				'caption',
				'style',
				'script',
			),
		);

		remove_theme_support( 'block-templates' );
		remove_theme_support( 'core-block-patterns' );

		add_editor_style( 'style-editor.css' );

		if ( file_exists( get_template_directory() . '/style-editor-extra.css' ) ) {
			add_editor_style( 'style-editor-extra.css' );
		}

		$this->disable_wp_emoji();
	}

	/**
	 * Removes WordPress core emoji detection script and inline styles from the front end.
	 *
	 * @return void
	 */
	private function disable_wp_emoji(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}
}
