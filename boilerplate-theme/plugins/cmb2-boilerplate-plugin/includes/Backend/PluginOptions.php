<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Backend;

use CompanyName\BoilerplatePlugin\Shortcodes\Shortcode;

\defined( 'ABSPATH' ) || exit;

/**
 * Manages plugin options with CMB2.
 */
class PluginOptions {
	/**
	 * CMB2 option key (stored in wp_options).
	 *
	 * @var string
	 */
	public const string OPTIONS_NAME = 'boilerplate_plugin_options';

	/**
	 * Initializes plugin options hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		// CMB2 defines CMB2_LOADED when the plugin boots; new_cmb2_box() only exists after late init.
		if ( ! defined( 'CMB2_LOADED' ) ) {
			add_action( 'admin_notices', array( $this, 'render_missing_cmb2_notice' ) );
			return;
		}

		add_action( 'cmb2_admin_init', array( $this, 'register_options' ) );
	}

	/**
	 * Renders an admin notice when CMB2 is missing.
	 *
	 * @return void
	 */
	public function render_missing_cmb2_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__(
			'CMB2 is not installed. Please install it to use Boilerplate Plugin options.',
			'boilerplate-plugin'
		);
		echo ' <a href="https://wordpress.org/plugins/cmb2/" target="_blank" rel="noopener noreferrer">CMB2</a>';
		echo '</p></div>';
	}

	/**
	 * Registers CMB2 options-page boxes and fields.
	 *
	 * @return void
	 */
	public function register_options(): void {
		if ( ! function_exists( 'new_cmb2_box' ) ) {
			return;
		}

		$cmb = new_cmb2_box(
			array(
				'id'           => self::OPTIONS_NAME . '_page',
				'title'        => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::OPTIONS_NAME,
				'menu_title'   => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
				'capability'   => 'manage_options',
				'icon_url'     => 'dashicons-admin-generic',
				'position'     => 90,
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Content', 'boilerplate-plugin' ),
				'desc' => esc_html__( 'Text fields used by the demo shortcode output.', 'boilerplate-plugin' ),
				'id'   => 'content_title',
				'type' => 'title',
			)
		);

		$cmb->add_field(
			array(
				'name'    => esc_html__( 'Headline', 'boilerplate-plugin' ),
				'desc'    => esc_html__( 'Main headline shown by the shortcode.', 'boilerplate-plugin' ),
				'id'      => 'demo_headline',
				'type'    => 'text',
				'default' => esc_html__( 'Boilerplate Plugin Demo', 'boilerplate-plugin' ),
			)
		);

		$cmb->add_field(
			array(
				'name'    => esc_html__( 'Intro text', 'boilerplate-plugin' ),
				'desc'    => esc_html__( 'Supporting text shown below the headline.', 'boilerplate-plugin' ),
				'id'      => 'demo_intro',
				'type'    => 'textarea',
				'default' => esc_html__(
					'This content comes from CMB2 plugin options. Edit it under Boilerplate Plugin in the admin menu.',
					'boilerplate-plugin'
				),
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Example Shortcode', 'boilerplate-plugin' ),
				'desc' => esc_html__( 'Insert this shortcode into a page or post to render the example output.', 'boilerplate-plugin' ),
				'id'   => 'shortcode_title',
				'type' => 'title',
			)
		);

		$cmb->add_field(
			array(
				'name'       => esc_html__( 'Shortcode', 'boilerplate-plugin' ),
				'desc'       => esc_html__( 'Copy and paste this shortcode into any page or post.', 'boilerplate-plugin' ),
				'id'         => 'shortcode_usage',
				'type'       => 'text',
				'default'    => sprintf( '[%s]', Shortcode::TAG ),
				'attributes' => array(
					'readonly' => 'readonly',
					'onclick'  => 'this.select();',
					'class'    => 'regular-text code',
				),
				'save_field' => false,
			)
		);
	}

	/**
	 * Gets a plugin option value from CMB2.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if the option is empty or CMB2 is missing.
	 * @return mixed Option value or default.
	 */
	public static function get_option( string $key, $default_value = null ) {
		if ( ! function_exists( 'cmb2_get_option' ) ) {
			return $default_value;
		}

		$value = cmb2_get_option( self::OPTIONS_NAME, $key, $default_value );

		if ( '' === $value || null === $value ) {
			return $default_value;
		}

		return $value;
	}
}
