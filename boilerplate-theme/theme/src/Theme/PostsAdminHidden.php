<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Removes Posts screens, menu, shortcuts, and post-only taxonomy admin.
 */
class PostsAdminHidden {
	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'register_post_type_args', array( $this, 'hide_builtin_post_type_ui' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'remove_posts_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'redirect_posts_admin_screens' ) );
		add_action( 'admin_bar_menu', array( $this, 'remove_new_post_admin_bar_node' ), 999 );
		add_filter( 'dashboard_recent_posts_query_args', array( $this, 'exclude_post_from_dashboard_recent' ) );
	}

	/**
	 * Returns post types used when the dashboard widget would otherwise query no types.
	 *
	 * @return array<int, string>
	 */
	private function get_dashboard_post_type_fallback(): array {
		$fallback = apply_filters( 'boilerplate_theme_dashboard_post_type_fallback', array( 'page' ) );

		if ( ! is_array( $fallback ) ) {
			return array( 'page' );
		}

		return array_values(
			array_filter(
				array_map( 'strval', $fallback ),
				'post_type_exists'
			)
		);
	}

	/**
	 * Disables admin UI and nav menu entries for the built-in post type.
	 *
	 * @param array<string, mixed> $args      Post type registration arguments.
	 * @param string               $post_type Post type name.
	 * @return array<string, mixed>
	 */
	public function hide_builtin_post_type_ui( array $args, string $post_type ): array {
		if ( 'post' !== $post_type ) {
			return $args;
		}

		$args['show_ui']           = false;
		$args['show_in_nav_menus'] = false;

		return $args;
	}

	/**
	 * Removes the Posts top-level admin menu (and attached submenus).
	 *
	 * @return void
	 */
	public function remove_posts_admin_menu(): void {
		remove_menu_page( 'edit.php' );
	}

	/**
	 * Redirects direct access to post list, new post, post editor, and category/tag screens.
	 *
	 * @return void
	 */
	public function redirect_posts_admin_screens(): void {
		global $pagenow;

		if ( 'edit-tags.php' === $pagenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing.
			$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
			if ( in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
		}

		if ( 'edit.php' === $pagenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing.
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
			if ( '' === $post_type || 'post' === $post_type ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
		}

		if ( 'post-new.php' === $pagenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing.
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
			if ( '' === $post_type || 'post' === $post_type ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
		}

		if ( 'post.php' === $pagenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing.
			$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
			if ( $post_id > 0 && 'post' === get_post_type( $post_id ) ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
		}
	}

	/**
	 * Removes the "Post" item from the admin bar "New" menu.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function remove_new_post_admin_bar_node( \WP_Admin_Bar $wp_admin_bar ): void {
		$wp_admin_bar->remove_node( 'new-post' );
	}

	/**
	 * Drops built-in posts from the dashboard "Recent posts" widget and falls back to theme CPTs.
	 *
	 * @param array<string, mixed> $args Query arguments for the widget.
	 * @return array<string, mixed>
	 */
	public function exclude_post_from_dashboard_recent( array $args ): array {
		$post_types = isset( $args['post_type'] ) ? (array) $args['post_type'] : array( 'post' );
		$post_types = array_values( array_diff( $post_types, array( 'post' ) ) );

		if ( array() === $post_types ) {
			$post_types = $this->get_dashboard_post_type_fallback();
		}

		if ( array() === $post_types && post_type_exists( 'page' ) ) {
			$post_types = array( 'page' );
		}

		$args['post_type'] = $post_types;

		return $args;
	}
}
