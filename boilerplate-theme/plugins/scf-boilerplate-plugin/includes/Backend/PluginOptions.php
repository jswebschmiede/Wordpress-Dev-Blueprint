<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Backend;

use CompanyName\BoilerplatePlugin\Shortcodes\Shortcode;

\defined( 'ABSPATH' ) || exit;

/**
 * Manages plugin options with Secure Custom Fields (SCF).
 */
class PluginOptions {
	/**
	 * SCF options page menu slug.
	 */
	public const string MENU_SLUG = 'boilerplate-plugin';

	/**
	 * Initializes plugin options hooks.
	 *
	 * Translations and SCF setup run on acf/init so the text domain is available
	 * and SCF APIs are loaded.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			add_action( 'admin_notices', array( $this, 'render_missing_scf_notice' ) );
			return;
		}

		add_action( 'acf/init', array( $this, 'bootstrap_scf' ) );
	}

	/**
	 * Registers the options page and local field group.
	 *
	 * @return void
	 */
	public function bootstrap_scf(): void {
		if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
				'menu_title' => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
				'menu_slug'  => self::MENU_SLUG,
				'capability' => 'manage_options',
				'redirect'   => false,
				'icon_url'   => 'dashicons-admin-generic',
				'position'   => 90,
			)
		);

		acf_add_local_field_group(
			array(
				'key'        => 'group_boilerplate_plugin_options',
				'title'      => esc_html__( 'Content', 'boilerplate-plugin' ),
				'fields'     => array(
					array(
						'key'           => 'field_demo_headline',
						'label'         => esc_html__( 'Headline', 'boilerplate-plugin' ),
						'name'          => 'demo_headline',
						'type'          => 'text',
						'instructions'  => esc_html__( 'Main headline shown by the shortcode.', 'boilerplate-plugin' ),
						'required'      => 0,
						'default_value' => esc_html__( 'Boilerplate Plugin Demo', 'boilerplate-plugin' ),
					),
					array(
						'key'           => 'field_demo_intro',
						'label'         => esc_html__( 'Intro text', 'boilerplate-plugin' ),
						'name'          => 'demo_intro',
						'type'          => 'textarea',
						'instructions'  => esc_html__( 'Supporting text shown below the headline.', 'boilerplate-plugin' ),
						'required'      => 0,
						'rows'          => 4,
						'default_value' => esc_html__(
							'This content comes from Secure Custom Fields plugin options. Edit it under Boilerplate Plugin in the admin menu.',
							'boilerplate-plugin'
						),
					),
					array(
						'key'     => 'field_shortcode_usage',
						'label'   => esc_html__( 'Shortcode', 'boilerplate-plugin' ),
						'name'    => 'shortcode_usage',
						'type'    => 'message',
						'message' => $this->get_shortcode_usage_html(),
					),
				),
				'location'   => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => self::MENU_SLUG,
						),
					),
				),
				'menu_order' => 0,
				'position'   => 'normal',
				'style'      => 'default',
			)
		);
	}

	/**
	 * Renders an admin notice when Secure Custom Fields is missing.
	 *
	 * @return void
	 */
	public function render_missing_scf_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__(
			'Secure Custom Fields is not installed. Please install it to use Boilerplate Plugin options.',
			'boilerplate-plugin'
		);
		echo ' <a href="https://wordpress.org/plugins/secure-custom-fields/" target="_blank" rel="noopener noreferrer">Secure Custom Fields</a>';
		echo '</p></div>';
	}

	/**
	 * Gets a plugin option value from SCF.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if the option is empty or SCF is missing.
	 * @return mixed Option value or default.
	 */
	public static function get_option( string $key, $default_value = null ) {
		if ( ! function_exists( 'get_field' ) ) {
			return $default_value;
		}

		$value = get_field( $key, 'option' );

		if ( '' === $value || null === $value || false === $value ) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * Builds HTML explaining how to use the demo shortcode.
	 *
	 * @return string Escaped markup for the SCF message field.
	 */
	private function get_shortcode_usage_html(): string {
		$shortcode = $this->get_example_shortcode();

		ob_start();
		?>
		<input
			type="text"
			value="<?php echo esc_attr( $shortcode ); ?>"
			class="regular-text code"
			readonly
			onclick="this.select();"
		/>
		<p class="description">
			<?php esc_html_e( 'Copy and paste this shortcode into any page or post.', 'boilerplate-plugin' ); ?>
		</p>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Gets the example shortcode tag for copy.
	 *
	 * @return string Shortcode tag wrapped in brackets.
	 */
	private function get_example_shortcode(): string {
		return sprintf( '[%s]', Shortcode::TAG );
	}
}
