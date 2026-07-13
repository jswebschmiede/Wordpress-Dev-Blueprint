<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Backend;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers a small Settings API example page.
 */
class PluginOptions {
	private const OPTION_NAME = 'boilerplate_plugin_options';
	private const PAGE_SLUG   = 'boilerplate-plugin';

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
