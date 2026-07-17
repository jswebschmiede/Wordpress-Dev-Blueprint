<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Backend;

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
	 * Plugin text domain.
	 */
	private const string TEXT_DOMAIN = 'boilerplate-plugin';

	/**
	 * Redux arguments.
	 *
	 * @var array<string, mixed>
	 */
	private array $args = array();

	/**
	 * Constructor — prepare Redux args when the framework is available.
	 */
	public function __construct() {
		if ( ! class_exists( 'Redux' ) ) {
			return;
		}

		$this->setup_redux_args();
		$this->init_redux();
	}

	/**
	 * Initializes plugin options hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( ! class_exists( 'Redux' ) ) {
			add_action( 'admin_notices', array( $this, 'render_missing_redux_notice' ) );
			return;
		}

		add_action( 'redux/loaded', array( $this, 'register_settings' ) );
		add_action( 'init', array( $this, 'register_settings' ), 1 );
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
			self::TEXT_DOMAIN
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
			'display_name'        => esc_html__( 'Boilerplate Plugin', self::TEXT_DOMAIN ),
			'display_version'     => \defined( 'BOILERPLATE_PLUGIN_VERSION' ) ? BOILERPLATE_PLUGIN_VERSION : '1.0.0',
			'display_description' => esc_html__(
				'Demo options panel powered by Redux Framework (text fields and image repeater).',
				self::TEXT_DOMAIN
			),
			'menu_icon'           => 'dashicons-images-alt2',
			'menu_title'          => esc_html__( 'Boilerplate Plugin', self::TEXT_DOMAIN ),
			'page_title'          => esc_html__( 'Boilerplate Plugin', self::TEXT_DOMAIN ),
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
				'title'  => esc_html__( 'Content', self::TEXT_DOMAIN ),
				'id'     => 'content',
				'desc'   => esc_html__( 'Text fields used by the demo shortcode output.', self::TEXT_DOMAIN ),
				'icon'   => 'el el-edit',
				'fields' => array(
					array(
						'id'       => 'demo_headline',
						'type'     => 'text',
						'title'    => esc_html__( 'Headline', self::TEXT_DOMAIN ),
						'subtitle' => esc_html__( 'Main headline shown by the shortcode.', self::TEXT_DOMAIN ),
						'default'  => esc_html__( 'Boilerplate Plugin Demo', self::TEXT_DOMAIN ),
					),
					array(
						'id'       => 'demo_intro',
						'type'     => 'textarea',
						'title'    => esc_html__( 'Intro text', self::TEXT_DOMAIN ),
						'subtitle' => esc_html__( 'Supporting text shown below the headline.', self::TEXT_DOMAIN ),
						'default'  => esc_html__(
							'This content comes from Redux plugin options. Edit it under Boilerplate Plugin in the admin menu.',
							self::TEXT_DOMAIN
						),
					),
				),
			)
		);

		\Redux::set_section(
			self::OPTIONS_NAME,
			array(
				'title'  => esc_html__( 'Gallery', self::TEXT_DOMAIN ),
				'id'     => 'gallery',
				'desc'   => esc_html__( 'Repeater of images with optional captions.', self::TEXT_DOMAIN ),
				'icon'   => 'el el-picture',
				'fields' => array(
					array(
						'id'           => 'demo_gallery',
						'type'         => 'repeater',
						'title'        => esc_html__( 'Images', self::TEXT_DOMAIN ),
						'subtitle'     => esc_html__( 'Add, sort, and remove gallery items.', self::TEXT_DOMAIN ),
						'group_values' => true,
						'item_name'    => esc_html__( 'Image', self::TEXT_DOMAIN ),
						'bind_title'   => 'caption',
						'sortable'     => true,
						'limit'        => 12,
						'fields'       => array(
							array(
								'id'          => 'image',
								'type'        => 'media',
								'url'         => true,
								'title'       => esc_html__( 'Image', self::TEXT_DOMAIN ),
								'placeholder' => esc_html__( 'No media selected', self::TEXT_DOMAIN ),
							),
							array(
								'id'          => 'caption',
								'type'        => 'text',
								'title'       => esc_html__( 'Caption', self::TEXT_DOMAIN ),
								'placeholder' => esc_html__( 'Optional caption', self::TEXT_DOMAIN ),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Gets a plugin option value from Redux.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if the option is empty or Redux is missing.
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
	 * Normalizes the grouped gallery repeater into a list of items.
	 *
	 * @return array<int, array{attachment_id: int, url: string, caption: string}>
	 */
	public static function get_gallery_items(): array {
		$gallery = self::get_option( 'demo_gallery', array() );

		if ( ! is_array( $gallery ) ) {
			return array();
		}

		$images   = isset( $gallery['image'] ) && is_array( $gallery['image'] ) ? $gallery['image'] : array();
		$captions = isset( $gallery['caption'] ) && is_array( $gallery['caption'] ) ? $gallery['caption'] : array();
		$count    = max( count( $images ), count( $captions ) );
		$items    = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$media   = isset( $images[ $i ] ) && is_array( $images[ $i ] ) ? $images[ $i ] : array();
			$url     = isset( $media['url'] ) && is_string( $media['url'] ) ? $media['url'] : '';
			$id      = isset( $media['id'] ) ? absint( $media['id'] ) : 0;
			$caption = isset( $captions[ $i ] ) && is_scalar( $captions[ $i ] ) ? (string) $captions[ $i ] : '';

			if ( '' === $url && 0 === $id ) {
				continue;
			}

			$items[] = array(
				'attachment_id' => $id,
				'url'           => $url,
				'caption'       => $caption,
			);
		}

		return $items;
	}
}
