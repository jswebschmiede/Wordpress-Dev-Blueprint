<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Manages initialization of all theme options with Redux Framework.
 */
class ThemeOptions {
	/**
	 * Redux options name.
	 *
	 * @var string
	 */
	private static string $options_name;

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Current theme object.
	 *
	 * @var \WP_Theme
	 */
	private \WP_Theme $theme;

	/**
	 * Redux arguments.
	 *
	 * @var array<string, mixed>
	 */
	private array $args = array();

	/**
	 * Constructor - Initialize Redux Framework.
	 */
	public function __construct() {
		$this->theme        = wp_get_theme();
		$this->text_domain  = $this->theme->get( 'TextDomain' );
		self::$options_name = $this->text_domain . '_options';

		$this->setup_redux_args();
		$this->init_redux();
	}

	/**
	 * Initialize theme options.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'redux/loaded', array( $this, 'register_settings' ) );
		add_action( 'init', array( $this, 'register_settings' ), 1 );
	}

	/**
	 * Setup Redux Framework arguments.
	 *
	 * @return void
	 */
	private function setup_redux_args(): void {
		$this->args = array(
			'opt_name'            => self::$options_name,
			'display_name'        => $this->theme->get( 'Name' ),
			'display_version'     => $this->theme->get( 'Version' ),
			'display_description' => $this->theme->get( 'Description' ),
			'menu_icon'           => 'dashicons-portfolio',
			'menu_title'          => esc_html__( 'Theme Options', 'boilerplate-theme' ),
			'page_title'          => esc_html__( 'Theme Options', 'boilerplate-theme' ),
			'page_slug'           => 'theme_options',
			'page_icon'           => 'icon-themes',
			'page_parent'         => 'themes.php',
			'page_permissions'    => 'manage_options',
			'customizer'          => true,
			'admin_bar'           => false,
			'dev_mode'            => false,
			'page_priority'       => 90,
			'search'              => true,
		);
	}

	/**
	 * Initialize Redux Framework.
	 *
	 * @return void
	 */
	private function init_redux(): void {
		if ( class_exists( 'Redux' ) ) {
			\Redux::set_args( self::$options_name, $this->args );
		}
	}

	/**
	 * Register Redux settings sections.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		if ( ! class_exists( 'Redux' ) ) {
			return;
		}

		\Redux::set_section(
			self::$options_name,
			array(
				'title'  => esc_html__( 'Allgemein', 'boilerplate-theme' ),
				'id'     => 'general',
				'desc'   => esc_html__( 'Alle allgemeinen Einstellungen sind hier aufgelistet', 'boilerplate-theme' ),
				'icon'   => 'el el-home',
				'fields' => array(
					array(
						'id'          => 'logo',
						'type'        => 'media',
						'url'         => true,
						'preview'     => false,
						'title'       => esc_html__( 'Logo', 'boilerplate-theme' ),
						'desc'        => esc_html__( 'Laden Sie ein Logo für Ihre Website hoch', 'boilerplate-theme' ),
						'placeholder' => esc_html__( 'Keine Medien ausgewählt', 'boilerplate-theme' ),
						'default'     => array(
							'url' => '',
						),
					),
					array(
						'id'      => 'website_title',
						'type'    => 'text',
						'title'   => esc_html__( 'Titel', 'boilerplate-theme' ),
						'desc'    => esc_html__( 'Geben Sie einen Titel für Ihre Website ein. Dieser wird angezeigt, wenn kein Logo hochgeladen wurde.', 'boilerplate-theme' ),
						'default' => $this->theme->get( 'Name' ),
					),
					array(
						'id'       => 'search_page',
						'type'     => 'select',
						'title'    => esc_html__( 'Suchseite', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Wählen Sie die Seite aus, auf der die Suchseite angezeigt wird. Alle Suchformulare im Theme verwenden diese Seite als Ziel.', 'boilerplate-theme' ),
						'data'     => 'pages',
						'default'  => 0,
						'select2'  => array(
							'allowClear' => true,
						),
					),
					array(
						'id'          => 'logo_footer',
						'type'        => 'media',
						'url'         => true,
						'preview'     => false,
						'title'       => esc_html__( 'Logo Footer', 'boilerplate-theme' ),
						'desc'        => esc_html__( 'Laden Sie ein Logo für Ihren Footer hoch', 'boilerplate-theme' ),
						'placeholder' => esc_html__( 'Keine Medien ausgewählt', 'boilerplate-theme' ),
						'default'     => array(
							'url' => '',
						),
					),
					array(
						'id'       => 'show_breadcrumb',
						'type'     => 'switch',
						'title'    => esc_html__( 'Breadcrumb-Navigation', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Breadcrumb-Navigation aktivieren oder deaktivieren', 'boilerplate-theme' ),
						'default'  => true,
						'on'       => esc_html__( 'Aktivieren', 'boilerplate-theme' ),
						'off'      => esc_html__( 'Deaktivieren', 'boilerplate-theme' ),
					),
					array(
						'id'       => 'show_preloader',
						'type'     => 'switch',
						'title'    => esc_html__( 'Preloader', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Preloader aktivieren oder deaktivieren', 'boilerplate-theme' ),
						'default'  => true,
						'on'       => esc_html__( 'Aktivieren', 'boilerplate-theme' ),
						'off'      => esc_html__( 'Deaktivieren', 'boilerplate-theme' ),
					),
					array(
						'id'       => 'preloader_style',
						'type'     => 'select',
						'title'    => esc_html__( 'Preloader-Stil', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Wählen Sie den Preloader-Stil', 'boilerplate-theme' ),
						'default'  => 'v1',
						'options'  => array(
							'v1' => esc_html__( 'Stil 1', 'boilerplate-theme' ),
							'v2' => esc_html__( 'Stil 2', 'boilerplate-theme' ),
							'v3' => esc_html__( 'Stil 3', 'boilerplate-theme' ),
							'v4' => esc_html__( 'Stil 4', 'boilerplate-theme' ),
							'v5' => esc_html__( 'Stil 5', 'boilerplate-theme' ),
							'v6' => esc_html__( 'Stil 6', 'boilerplate-theme' ),
						),
						'select2'  => array(
							'allowClear' => false,
						),
						'required' => array(
							array(
								'show_preloader',
								'equals',
								'1',
							),
						),
					),
					array(
						'id'       => 'show_backtotop',
						'type'     => 'switch',
						'title'    => esc_html__( 'Nach oben', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Nach-oben-Schaltfläche aktivieren oder deaktivieren', 'boilerplate-theme' ),
						'default'  => true,
						'on'       => esc_html__( 'Aktivieren', 'boilerplate-theme' ),
						'off'      => esc_html__( 'Deaktivieren', 'boilerplate-theme' ),
					),
				),
			),
		);

		\Redux::set_section(
			self::$options_name,
			array(
				'id'     => 'security',
				'title'  => esc_html__( 'Sicherheit', 'boilerplate-theme' ),
				'desc'   => esc_html__( 'Einstellungen zur Anmeldung und zum Schutz vor Missbrauch', 'boilerplate-theme' ),
				'icon'   => 'el el-lock',
				'fields' => array(
					array(
						'id'       => 'disable_password_reset',
						'type'     => 'switch',
						'title'    => esc_html__( 'Passwort-Zurücksetzen deaktivieren', 'boilerplate-theme' ),
						'subtitle' => esc_html__(
							'Blendet „Passwort vergessen“ aus und blockiert den WordPress-Passwort-Reset (Links und E-Mails). Verringert Missbrauch.',
							'boilerplate-theme'
						),
						'default'  => false,
						'on'       => esc_html__( 'Aktivieren', 'boilerplate-theme' ),
						'off'      => esc_html__( 'Deaktivieren', 'boilerplate-theme' ),
					),
				),
			),
		);

		\Redux::set_section(
			self::$options_name,
			array(
				'id'     => 'social_media',
				'type'   => 'section',
				'title'  => esc_html__( 'Social Media', 'boilerplate-theme' ),
				'desc'   => __( 'Geben Sie Ihre Social-Media-Links ein. Lassen Sie das Feld leer, um das Icon auszublenden. <br> <br> <strong>Hinweis:</strong> Die Icons werden nicht angezeigt, wenn die URL leer ist.', 'boilerplate-theme' ),
				'icon'   => 'el el-facebook',
				'fields' => array(
					array(
						'id'       => 'facebook',
						'type'     => 'text',
						'title'    => esc_html__( 'Facebook', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie Ihre Facebook-URL ein.', 'boilerplate-theme' ),
						'default'  => '',
						'validate' => array( 'url' ),
					),
					array(
						'id'       => 'twitter',
						'type'     => 'text',
						'title'    => esc_html__( 'Twitter / X', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie Ihre Twitter / X-URL ein.', 'boilerplate-theme' ),
						'default'  => '',
						'validate' => array( 'url' ),
					),
					array(
						'id'       => 'instagram',
						'type'     => 'text',
						'title'    => esc_html__( 'Instagram', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie Ihre Instagram-URL ein.', 'boilerplate-theme' ),
						'default'  => '',
						'validate' => array( 'url' ),
					),
					array(
						'id'       => 'linkedin',
						'type'     => 'text',
						'title'    => esc_html__( 'LinkedIn', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie Ihre LinkedIn-URL ein.', 'boilerplate-theme' ),
						'default'  => '',
						'validate' => array( 'url' ),
					),
					array(
						'id'       => 'youtube',
						'type'     => 'text',
						'title'    => esc_html__( 'YouTube', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie Ihre YouTube-URL ein.', 'boilerplate-theme' ),
						'default'  => '',
						'validate' => array( 'url' ),
					),
					array(
						'id'       => 'pinterest',
						'type'     => 'text',
						'title'    => esc_html__( 'Pinterest', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie Ihre Pinterest-URL ein.', 'boilerplate-theme' ),
						'default'  => '',
						'validate' => array( 'url' ),
					),
				),
			),
		);

		\Redux::set_section(
			self::$options_name,
			array(
				'id'     => 'error_tab',
				'title'  => esc_html__( '404-Einstellungen', 'boilerplate-theme' ),
				'desc'   => esc_html__( 'Alle 404-bezogenen Optionen sind hier aufgelistet', 'boilerplate-theme' ),
				'icon'   => 'el el-website',
				'fields' => array(
					array(
						'id'       => 'error_title',
						'type'     => 'text',
						'title'    => esc_html__( 'Fehlertitel', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie den erforderlichen Titel ein', 'boilerplate-theme' ),
						'default'  => 'Entschuldigung! Die Seite wurde nicht gefunden',
					),
					array(
						'id'       => 'error_text',
						'type'     => 'textarea',
						'title'    => esc_html__( 'Fehlertext', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie den erforderlichen Text ein', 'boilerplate-theme' ),
						'default'  => 'Diese Seite konnte nicht gefunden werden. Sie wurde möglicherweise entfernt oder umbenannt, oder sie hat möglicherweise nie existiert.',
					),
					array(
						'id'       => 'error_btn',
						'type'     => 'text',
						'title'    => esc_html__( 'Button-Text', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Geben Sie den Button-Text ein', 'boilerplate-theme' ),
						'default'  => 'Zurück zur Startseite',
					),
				),
			),
		);

		\Redux::set_section(
			self::$options_name,
			array(
				'title'  => esc_html__( 'Custom CSS / JS', 'boilerplate-theme' ),
				'id'     => 'custom-css-js',
				'icon'   => 'el el-css',
				'fields' => array(
					array(
						'id'       => 'custom_css',
						'type'     => 'ace_editor',
						'title'    => esc_html__( 'Benutzerdefiniertes CSS', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Fügen Sie hier Ihren benutzerdefinierten CSS-Code ein.', 'boilerplate-theme' ),
						'mode'     => 'css',
						'theme'    => 'monokai',
					),
					array(
						'id'       => 'custom_js',
						'type'     => 'ace_editor',
						'title'    => esc_html__( 'Benutzerdefiniertes JS', 'boilerplate-theme' ),
						'subtitle' => esc_html__( 'Fügen Sie hier Ihren JS-Code ein.', 'boilerplate-theme' ),
						'mode'     => 'javascript',
						'theme'    => 'chrome',
					),
				),
			),
		);
	}

	/**
	 * Get theme option value.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if option doesn't exist.
	 * @return mixed
	 */
	public static function get_option( string $key, $default_value = null ) {
		if ( ! class_exists( 'Redux' ) ) {
			return $default_value;
		}

		$value = \Redux::get_option( self::$options_name, $key, $default_value );

		if ( empty( $value ) ) {
			return $default_value;
		}

		return $value;
	}
}
