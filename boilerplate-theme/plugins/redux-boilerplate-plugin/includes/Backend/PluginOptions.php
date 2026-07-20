<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Backend;

use CompanyName\BoilerplatePlugin\Shortcodes\Shortcode;

\defined( 'ABSPATH' ) || exit;

/**
 * Manages plugin options with Redux Framework.
 */
class PluginOptions {
	/**
	 * Redux options name.
	 *
	 * @var string
	 */
	public const string OPTIONS_NAME = 'boilerplate_plugin_options';

	/**
	 * Redux arguments.
	 *
	 * @var array<string, mixed>
	 */
	private array $args = array();

	/**
	 * Initializes plugin options hooks.
	 *
	 * Translations and Redux setup run on init so the text domain is not
	 * loaded before WordPress 6.7 allows it.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! class_exists( 'Redux' ) ) {
			add_action( 'admin_notices', array( $this, 'render_missing_redux_notice' ) );
			return;
		}

		add_action( 'init', array( $this, 'bootstrap_redux' ), 1 );
	}

	/**
	 * Sets Redux args and registers sections after translations are available.
	 *
	 * @return void
	 */
	public function bootstrap_redux(): void {
		if ( ! class_exists( 'Redux' ) ) {
			return;
		}

		$this->setup_redux_args();
		$this->init_redux();
		$this->register_settings();
	}

	/**
	 * Renders an admin notice when Redux Framework is missing.
	 *
	 * @return void
	 */
	public function render_missing_redux_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__(
			'Redux Framework is not installed. Please install it to use Boilerplate Plugin options.',
			'boilerplate-plugin'
		);
		echo ' <a href="https://wordpress.org/plugins/redux-framework/" target="_blank" rel="noopener noreferrer">Redux Framework</a>';
		echo '</p></div>';
	}

	/**
	 * Sets up Redux Framework arguments.
	 *
	 * @return void
	 */
	private function setup_redux_args(): void {
		$this->args = array(
			'opt_name'            => self::OPTIONS_NAME,
			'display_name'        => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
			'display_version'     => \defined( 'BOILERPLATE_PLUGIN_VERSION' ) ? BOILERPLATE_PLUGIN_VERSION : '1.0.0',
			'display_description' => esc_html__(
				'Demo options panel powered by Redux Framework (text fields with shortcode output).',
				'boilerplate-plugin'
			),
			'menu_icon'           => 'dashicons-admin-generic',
			'menu_title'          => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
			'page_title'          => esc_html__( 'Boilerplate Plugin', 'boilerplate-plugin' ),
			'page_slug'           => 'boilerplate-plugin',
			'page_icon'           => 'icon-themes',
			'page_parent'         => '',
			'menu_type'           => 'menu',
			'page_permissions'    => 'manage_options',
			'customizer'          => false,
			'admin_bar'           => false,
			'dev_mode'            => false,
			'page_priority'       => 90,
			'search'              => true,
		);
	}

	/**
	 * Initializes Redux Framework args.
	 *
	 * @return void
	 */
	private function init_redux(): void {
		\Redux::set_args( self::OPTIONS_NAME, $this->args );
	}

	/**
	 * Registers Redux settings sections and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		if ( ! class_exists( 'Redux' ) ) {
			return;
		}

		\Redux::set_section(
			self::OPTIONS_NAME,
			array(
				'title'  => esc_html__( 'Content', 'boilerplate-plugin' ),
				'id'     => 'content',
				'desc'   => esc_html__( 'Text fields used by the demo shortcode output.', 'boilerplate-plugin' ),
				'icon'   => 'el el-edit',
				'fields' => array(
					array(
						'id'       => 'demo_headline',
						'type'     => 'text',
						'title'    => esc_html__( 'Headline', 'boilerplate-plugin' ),
						'subtitle' => esc_html__( 'Main headline shown by the shortcode.', 'boilerplate-plugin' ),
						'default'  => esc_html__( 'Boilerplate Plugin Demo', 'boilerplate-plugin' ),
					),
					array(
						'id'       => 'demo_intro',
						'type'     => 'textarea',
						'title'    => esc_html__( 'Intro text', 'boilerplate-plugin' ),
						'subtitle' => esc_html__( 'Supporting text shown below the headline.', 'boilerplate-plugin' ),
						'default'  => esc_html__(
							'This content comes from Redux plugin options. Edit it under Boilerplate Plugin in the admin menu.',
							'boilerplate-plugin'
						),
					),
				),
			)
		);

		\Redux::set_section(
			self::OPTIONS_NAME,
			array(
				'title'  => esc_html__( 'Example Shortcode', 'boilerplate-plugin' ),
				'id'     => 'shortcode',
				'desc'   => esc_html__( 'Insert this shortcode into a page or post to render the example output.', 'boilerplate-plugin' ),
				'icon'   => 'el el-info-circle',
				'fields' => array(
					array(
						'id'      => 'shortcode_usage',
						'type'    => 'raw',
						'title'   => esc_html__( 'Shortcode', 'boilerplate-plugin' ),
						'content' => $this->get_shortcode_usage_html(),
					),
				),
			)
		);
	}

	/**
	 * Gets a plugin option value from Redux.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if the option is empty or "Redux Framework" is missing.
	 * @return mixed Option value or default.
	 */
	public static function get_option( string $key, $default_value = null ) {
		if ( ! class_exists( 'Redux' ) ) {
			return $default_value;
		}

		$value = \Redux::get_option( self::OPTIONS_NAME, $key, $default_value );

		if ( empty( $value ) ) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * Builds HTML explaining how to use the demo shortcode.
	 *
	 * @return string Escaped markup for the Redux raw field.
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
