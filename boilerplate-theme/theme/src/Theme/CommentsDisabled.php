<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Central place for no-comments UI and legacy comment submission blocking.
 * Does not unregister REST comment routes (editor compatibility).
 */
class CommentsDisabled {
	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'remove_comments_admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'remove_comments_admin_bar_node' ), 999 );
		add_action( 'init', array( $this, 'remove_comments_support' ) );
		add_action( 'admin_init', array( $this, 'redirect_comments_admin_screen' ) );
		add_action( 'pre_comment_on_post', array( $this, 'block_comment_post' ), 0 );
	}

	/**
	 * Removes the Comments admin menu item.
	 *
	 * @return void
	 */
	public function remove_comments_admin_menu(): void {
		remove_menu_page( 'edit-comments.php' );
	}

	/**
	 * Removes the comments shortcut from the admin bar (front and back end).
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function remove_comments_admin_bar_node( \WP_Admin_Bar $wp_admin_bar ): void {
		$wp_admin_bar->remove_node( 'comments' );
	}

	/**
	 * Redirects direct access to the comments admin screen.
	 *
	 * @return void
	 */
	public function redirect_comments_admin_screen(): void {
		global $pagenow;

		if ( 'edit-comments.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}

	/**
	 * Removes comment and trackback support from all post types that declare it.
	 *
	 * @return void
	 */
	public function remove_comments_support(): void {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	/**
	 * Stops comment submission before WordPress validates or stores the comment.
	 *
	 * @return void
	 */
	public function block_comment_post(): void {
		wp_die(
			esc_html__( 'Kommentare sind deaktiviert.', 'boilerplate-theme' ),
			esc_html__( 'Verboten', 'boilerplate-theme' ),
			array( 'response' => 403 )
		);
	}
}
