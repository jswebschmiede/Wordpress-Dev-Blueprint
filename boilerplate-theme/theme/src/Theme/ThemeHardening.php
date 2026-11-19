<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Reduces public user enumeration via REST, author archives, sitemaps, and oEmbed.
 */
class ThemeHardening {
	/**
	 * Registers hardening hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'rest_endpoints', array( $this, 'remove_rest_user_endpoints' ) );
		add_action( 'template_redirect', array( $this, 'redirect_author_archive' ), 1 );
		add_filter( 'oembed_response_data', array( $this, 'remove_oembed_author_data' ) );
		add_filter( 'wp_sitemaps_add_provider', array( $this, 'remove_users_sitemap_provider' ), 10, 2 );
	}

	/**
	 * Removes REST user endpoints for visitors who are not logged in.
	 *
	 * Logged-in users keep the routes so the block editor author dropdown still works.
	 *
	 * @param array<string, mixed> $endpoints Registered REST endpoints.
	 * @return array<string, mixed> Endpoints without public user routes.
	 */
	public function remove_rest_user_endpoints( array $endpoints ): array {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}

		return $endpoints;
	}

	/**
	 * Redirects author archives to the home page.
	 *
	 * Runs at priority 1 so `/?author=N` never reaches `redirect_canonical`.
	 *
	 * @return void
	 */
	public function redirect_author_archive(): void {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}

	/**
	 * Removes the author name and URL from oEmbed responses.
	 *
	 * @param array<string, mixed> $data oEmbed response data.
	 * @return array<string, mixed> Response data without author fields.
	 */
	public function remove_oembed_author_data( array $data ): array {
		unset( $data['author_name'], $data['author_url'] );

		return $data;
	}

	/**
	 * Drops the users sitemap so nicenames are not listed at `/wp-sitemap-users-1.xml`.
	 *
	 * @param mixed  $provider Sitemap provider instance.
	 * @param string $name     Provider name.
	 * @return mixed Provider, or false to skip the users sitemap.
	 */
	public function remove_users_sitemap_provider( mixed $provider, string $name ): mixed {
		if ( 'users' === $name ) {
			return false;
		}

		return $provider;
	}
}
