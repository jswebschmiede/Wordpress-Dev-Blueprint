<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Blocks lost-password flows when the Redux option is enabled.
 */
class ThemeLoginSecurity {
	/**
	 * Prevents duplicate hook registration.
	 *
	 * @var bool
	 */
	private static bool $hooks_registered = false;

	/**
	 * Query argument indicating the user was redirected from a blocked password-reset flow.
	 */
	private const PWRESET_QUERY_ARG = 'pwreset';

	/**
	 * Query value paired with self::PWRESET_QUERY_ARG.
	 */
	private const PWRESET_QUERY_VALUE = 'disabled';

	/**
	 * Registers login security hooks when the option is enabled.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register();
		add_action( 'after_setup_theme', array( $this, 'register' ), 0 );
	}

	/**
	 * Registers login-related hooks when password reset is disabled in theme options.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( self::$hooks_registered || ! $this->is_password_reset_disabled() ) {
			return;
		}

		self::$hooks_registered = true;

		add_action( 'login_init', array( $this, 'block_password_reset_actions' ), -1000 );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_hiding_styles' ), 20 );
		add_filter( 'lost_password_html_link', array( $this, 'remove_lost_password_link' ), 999, 1 );
		add_filter( 'login_link_separator', array( $this, 'remove_login_link_separator' ), 999, 1 );
		add_filter( 'wp_login_errors', array( $this, 'filter_login_errors' ), 10, 2 );
		add_filter( 'allow_password_reset', '__return_false', 999 );
	}

	/**
	 * Redirects away from password-reset actions to the normal login screen.
	 *
	 * @return void
	 */
	public function block_password_reset_actions(): void {
		$action  = isset( $_REQUEST['action'] ) ? (string) $_REQUEST['action'] : 'login';
		$blocked = array( 'lostpassword', 'retrievepassword', 'resetpass', 'rp' );
		if ( ! in_array( $action, $blocked, true ) ) {
			return;
		}

		$url = add_query_arg(
			self::PWRESET_QUERY_ARG,
			self::PWRESET_QUERY_VALUE,
			wp_login_url()
		);
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Hides lost-password links when markup or filters differ from core defaults.
	 *
	 * @return void
	 */
	public function enqueue_login_hiding_styles(): void {
		wp_add_inline_style(
			'login',
			'#nav .wp-login-lost-password,#nav a[href*="action=lostpassword"],#nav a[href*="action=retrievepassword"]{display:none!important}'
		);
	}

	/**
	 * Removes the lost-password HTML link from the login navigation.
	 *
	 * @param mixed $html_link Original HTML link markup.
	 * @return string Empty string to remove the link.
	 */
	public function remove_lost_password_link( $html_link ): string {
		return '';
	}

	/**
	 * Removes the separator between login nav links when the lost-password link is hidden.
	 *
	 * @param mixed $separator Default separator (typically " | ").
	 * @return string Empty string to avoid a trailing separator.
	 */
	public function remove_login_link_separator( $separator ): string {
		return '';
	}

	/**
	 * Shows an informational notice after redirect from a blocked password-reset URL.
	 *
	 * @param \WP_Error $errors       Login errors object.
	 * @param mixed     $redirect_to Redirect destination URL.
	 * @return \WP_Error
	 */
	public function filter_login_errors( \WP_Error $errors, $redirect_to ): \WP_Error {
		if ( ! isset( $_GET[ self::PWRESET_QUERY_ARG ] ) ) {
			return $errors;
		}

		$value = sanitize_text_field( wp_unslash( (string) $_GET[ self::PWRESET_QUERY_ARG ] ) );
		if ( self::PWRESET_QUERY_VALUE !== $value ) {
			return $errors;
		}

		$errors->add(
			'pwreset_disabled',
			__( 'Passwort-Zurücksetzen ist auf dieser Website deaktiviert.', 'boilerplate-theme' ),
			'message'
		);

		return $errors;
	}

	/**
	 * Whether password reset via WordPress should be blocked.
	 *
	 * @return bool True when the lost-password flow should be blocked.
	 */
	private function is_password_reset_disabled(): bool {
		if ( ! class_exists( 'Redux' ) ) {
			return (bool) apply_filters( 'boilerplate_theme_password_reset_disabled', false );
		}

		$value   = ThemeOptions::get_option( 'disable_password_reset', false );
		$enabled = ( true === $value || 1 === $value || '1' === $value );

		return (bool) apply_filters( 'boilerplate_theme_password_reset_disabled', $enabled );
	}
}
