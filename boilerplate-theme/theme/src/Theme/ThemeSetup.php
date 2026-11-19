<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

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
		add_action( 'admin_init', array( $this, 'disable_admin_emoji' ) );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_dns_prefetch' ), 10, 2 );
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
	 * Removes WordPress emoji scripts, styles, and conversion filters outside wp-admin.
	 *
	 * Core still prints the detection script unless it is unhooked from wp_head
	 * at priority 7. Unhooking print_emoji_styles also stops wp_enqueue_emoji_styles.
	 *
	 * @return void
	 */
	private function disable_wp_emoji(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'embed_head', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	/**
	 * Removes WordPress emoji scripts and styles from wp-admin.
	 *
	 * Core registers these in wp-admin/includes/admin-filters.php, which loads after
	 * after_setup_theme, so they can only be unhooked from admin_init onwards.
	 *
	 * @return void
	 */
	public function disable_admin_emoji(): void {
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	}

	/**
	 * Removes the TinyMCE wpemoji plugin.
	 *
	 * @param array<int, string> $plugins TinyMCE plugin slugs.
	 * @return array<int, string> Plugin slugs without wpemoji.
	 */
	public function disable_emojis_tinymce( array $plugins ): array {
		return array_values( array_diff( $plugins, array( 'wpemoji' ) ) );
	}

	/**
	 * Drops s.w.org emoji CDN hosts from resource hints.
	 *
	 * @param array<int|string, mixed> $urls          Resource hint URLs.
	 * @param string                   $relation_type Hint relation type.
	 * @return array<int|string, mixed> Filtered URLs.
	 */
	public function disable_emojis_dns_prefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' !== $relation_type && 'preconnect' !== $relation_type ) {
			return $urls;
		}

		return array_values(
			array_filter(
				$urls,
				/**
				 * Keeps resource hints that do not point at the emoji CDN.
				 *
				 * @param mixed $url Resource hint URL or hint array.
				 * @return bool True when the hint should remain.
				 */
				static function ( $url ): bool {
					$href = is_array( $url ) ? (string) ( $url['href'] ?? '' ) : (string) $url;

					return false === strpos( $href, 's.w.org' );
				}
			)
		);
	}
}
