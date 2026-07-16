<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Backend;

use CompanyName\BoilerplatePlugin\Shortcodes\Shortcode;
use CompanyName\BoilerplatePlugin\Support\StraussDemo;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers a small Settings API example page.
 */
class PluginOptions {
	private const string OPTION_NAME = 'boilerplate_plugin_options';
	private const string PAGE_SLUG   = 'boilerplate-plugin';

	/**
	 * Initializes admin settings hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_options_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Registers the plugin options page.
	 *
	 * @return void
	 */
	public function register_options_page(): void {
		add_options_page(
			__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
			__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_options_page' )
		);
	}

	/**
	 * Registers settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::PAGE_SLUG,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => $this->get_default_options(),
			)
		);

		add_settings_section(
			'boilerplate_plugin_general',
			__( 'General Settings', 'boilerplate-plugin' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Use this page as a starting point for plugin options.', 'boilerplate-plugin' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_field(
			'example_text',
			__( 'Example Text', 'boilerplate-plugin' ),
			array( $this, 'render_example_text_field' ),
			self::PAGE_SLUG,
			'boilerplate_plugin_general'
		);

		add_settings_field(
			'strauss_demo',
			__( 'Strauss test (UUID)', 'boilerplate-plugin' ),
			array( $this, 'render_strauss_demo_field' ),
			self::PAGE_SLUG,
			'boilerplate_plugin_general'
		);

		add_settings_section(
			'boilerplate_plugin_shortcode',
			__( 'Example Shortcode', 'boilerplate-plugin' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Insert this shortcode into a page or post to render the example output.', 'boilerplate-plugin' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_field(
			'shortcode_tag',
			__( 'Shortcode', 'boilerplate-plugin' ),
			array( $this, 'render_shortcode_tag_field' ),
			self::PAGE_SLUG,
			'boilerplate_plugin_shortcode'
		);

		add_settings_field(
			'shortcode_preview',
			__( 'Preview', 'boilerplate-plugin' ),
			array( $this, 'render_shortcode_preview_field' ),
			self::PAGE_SLUG,
			'boilerplate_plugin_shortcode'
		);
	}

	/**
	 * Sanitizes plugin options.
	 *
	 * @param mixed $options Raw options value.
	 * @return array<string, string> Sanitized options.
	 */
	public function sanitize_options( mixed $options ): array {
		$defaults = $this->get_default_options();

		if ( ! is_array( $options ) ) {
			return $defaults;
		}

		return array(
			'example_text' => isset( $options['example_text'] ) && is_scalar( $options['example_text'] )
				? sanitize_text_field( (string) $options['example_text'] )
				: $defaults['example_text'],
		);
	}

	/**
	 * Renders the example text field.
	 *
	 * @return void
	 */
	public function render_example_text_field(): void {
		$options = $this->get_options();
		?>
		<input
			type="text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[example_text]"
			value="<?php echo esc_attr( $options['example_text'] ); ?>"
			class="regular-text"
		/>
		<?php
	}

	/**
	 * Renders the Strauss demo read-only field.
	 *
	 * @return void
	 */
	public function render_strauss_demo_field(): void {
		$sample_uuid = ( new StraussDemo() )->get_sample_uuid();
		?>
		<input
			type="text"
			value="<?php echo esc_attr( $sample_uuid ); ?>"
			class="regular-text"
			readonly
		/>
		<p class="description">
			<?php esc_html_e( 'Random UUID via prefixed ramsey/uuid (Strauss).', 'boilerplate-plugin' ); ?>
		</p>
		<?php
	}

	/**
	 * Renders the example shortcode tag field.
	 *
	 * @return void
	 */
	public function render_shortcode_tag_field(): void {
		$shortcode = $this->get_example_shortcode();
		?>
		<input
			type="text"
			value="<?php echo esc_attr( $shortcode ); ?>"
			class="regular-text code"
			readonly
			onclick="this.select();"
		/>
		<p class="description">
			<?php esc_html_e( 'Optional attribute example:', 'boilerplate-plugin' ); ?>
			<code>[<?php echo esc_html( Shortcode::TAG ); ?> title="<?php echo esc_attr__( 'Custom Title', 'boilerplate-plugin' ); ?>"]</code>
		</p>
		<?php
	}

	/**
	 * Renders a live preview of the example shortcode output.
	 *
	 * @return void
	 */
	public function render_shortcode_preview_field(): void {
		?>
		<div class="boilerplate-plugin-shortcode-preview" style="max-width: 32rem; padding: 1rem; border: 1px solid #c3c4c7; background: #fff;">
			<?php echo do_shortcode( $this->get_example_shortcode() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode template escapes output. ?>
		</div>
		<p class="description">
			<?php esc_html_e( 'This is how the shortcode renders on the frontend.', 'boilerplate-plugin' ); ?>
		</p>
		<?php
	}

	/**
	 * Gets the example shortcode tag for copy and preview.
	 *
	 * @return string Shortcode tag wrapped in brackets.
	 */
	private function get_example_shortcode(): string {
		return sprintf( '[%s]', Shortcode::TAG );
	}

	/**
	 * Renders the options page.
	 *
	 * @return void
	 */
	public function render_options_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Gets saved options merged with defaults.
	 *
	 * @return array<string, string> Plugin options.
	 */
	public function get_options(): array {
		$options = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return wp_parse_args( $options, $this->get_default_options() );
	}

	/**
	 * Gets default options.
	 *
	 * @return array<string, string> Default options.
	 */
	private function get_default_options(): array {
		return array(
			'example_text' => __( 'Hello from Boilerplate Plugin.', 'boilerplate-plugin' ),
		);
	}
}
