<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Ajax;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers example AJAX endpoints.
 */
class AjaxHandler {
	/**
	 * Initializes AJAX hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_ajax_boilerplate_plugin_ping', array( $this, 'handle_ping' ) );
		add_action( 'wp_ajax_nopriv_boilerplate_plugin_ping', array( $this, 'handle_ping' ) );
	}

	/**
	 * Handles the example ping request.
	 *
	 * @return void
	 */
	public function handle_ping(): void {
		if ( ! check_ajax_referer( 'boilerplate_plugin_ping', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid security token.', 'boilerplate-plugin' ),
				),
				403
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'AJAX endpoint is ready.', 'boilerplate-plugin' ),
			)
		);
	}
}
