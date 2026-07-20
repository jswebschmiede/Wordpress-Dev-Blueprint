<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Manages theme options with CMB2.
 */
class ThemeOptions {
	/**
	 * CMB2 option key (stored in wp_options).
	 *
	 * @var string
	 */
	private static string $options_name;

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private readonly string $text_domain;

	/**
	 * Current theme object.
	 *
	 * @var \WP_Theme
	 */
	private readonly \WP_Theme $theme;

	/**
	 * Field IDs that use the CMB2 file type.
	 *
	 * @var list<string>
	 */
	private const array MEDIA_KEYS = array( 'logo', 'logo_footer' );

	/**
	 * Field IDs that use the CMB2 checkbox type.
	 *
	 * @var list<string>
	 */
	private const array CHECKBOX_KEYS = array(
		'show_breadcrumb',
		'show_preloader',
		'show_backtotop',
		'disable_password_reset',
		'disable_comments',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->theme        = wp_get_theme();
		$this->text_domain  = $this->theme->get( 'TextDomain' );
		self::$options_name = $this->text_domain . '_options';
	}

	/**
	 * Registers CMB2 options page hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'cmb2_admin_init', array( $this, 'register_options' ) );
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

		$tab_group = self::$options_name;

		$general = new_cmb2_box(
			array(
				'id'           => self::$options_name . '_general',
				'title'        => esc_html__( 'Theme Options', 'boilerplate-theme' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::$options_name,
				'parent_slug'  => 'themes.php',
				'menu_title'   => esc_html__( 'Theme Options', 'boilerplate-theme' ),
				'menu_slug'    => 'theme_options',
				'capability'   => 'manage_options',
				'tab_group'    => $tab_group,
				'tab_title'    => esc_html__( 'Allgemein', 'boilerplate-theme' ),
			)
		);

		$general->add_field(
			array(
				'name'         => esc_html__( 'Logo', 'boilerplate-theme' ),
				'desc'         => esc_html__( 'Laden Sie ein Logo für Ihre Website hoch', 'boilerplate-theme' ),
				'id'           => 'logo',
				'type'         => 'file',
				'options'      => array(
					'url' => true,
				),
				'text'         => array(
					'add_upload_file_text' => esc_html__( 'Logo hochladen', 'boilerplate-theme' ),
				),
				'query_args'   => array(
					'type' => array(
						'image/gif',
						'image/jpeg',
						'image/png',
						'image/svg+xml',
						'image/webp',
					),
				),
				'preview_size' => 'medium',
			)
		);

		$general->add_field(
			array(
				'name'    => esc_html__( 'Titel', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Geben Sie einen Titel für Ihre Website ein. Dieser wird angezeigt, wenn kein Logo hochgeladen wurde.', 'boilerplate-theme' ),
				'id'      => 'website_title',
				'type'    => 'text',
				'default' => $this->theme->get( 'Name' ),
			)
		);

		$general->add_field(
			array(
				'name'             => esc_html__( 'Suchseite', 'boilerplate-theme' ),
				'desc'             => esc_html__( 'Wählen Sie die Seite aus, auf der die Suchseite angezeigt wird. Alle Suchformulare im Theme verwenden diese Seite als Ziel.', 'boilerplate-theme' ),
				'id'               => 'search_page',
				'type'             => 'select',
				'show_option_none' => esc_html__( '— Auswählen —', 'boilerplate-theme' ),
				'options_cb'       => array( $this, 'get_page_options' ),
			)
		);

		$general->add_field(
			array(
				'name'         => esc_html__( 'Logo Footer', 'boilerplate-theme' ),
				'desc'         => esc_html__( 'Laden Sie ein Logo für Ihren Footer hoch', 'boilerplate-theme' ),
				'id'           => 'logo_footer',
				'type'         => 'file',
				'options'      => array(
					'url' => true,
				),
				'text'         => array(
					'add_upload_file_text' => esc_html__( 'Footer-Logo hochladen', 'boilerplate-theme' ),
				),
				'query_args'   => array(
					'type' => array(
						'image/gif',
						'image/jpeg',
						'image/png',
						'image/svg+xml',
						'image/webp',
					),
				),
				'preview_size' => 'medium',
			)
		);

		$general->add_field(
			array(
				'name'    => esc_html__( 'Breadcrumb-Navigation', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Breadcrumb-Navigation aktivieren oder deaktivieren', 'boilerplate-theme' ),
				'id'      => 'show_breadcrumb',
				'type'    => 'checkbox',
				'default' => $this->cmb2_set_checkbox_default_true( 'show_breadcrumb' ),
			)
		);

		$general->add_field(
			array(
				'name'    => esc_html__( 'Preloader', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Preloader aktivieren oder deaktivieren', 'boilerplate-theme' ),
				'id'      => 'show_preloader',
				'type'    => 'checkbox',
				'default' => $this->cmb2_set_checkbox_default_true( 'show_preloader' ),
			)
		);

		$general->add_field(
			array(
				'name'    => esc_html__( 'Preloader-Stil', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Wählen Sie den Preloader-Stil', 'boilerplate-theme' ),
				'id'      => 'preloader_style',
				'type'    => 'select',
				'default' => 'v1',
				'options' => array(
					'v1' => esc_html__( 'Stil 1', 'boilerplate-theme' ),
					'v2' => esc_html__( 'Stil 2', 'boilerplate-theme' ),
					'v3' => esc_html__( 'Stil 3', 'boilerplate-theme' ),
					'v4' => esc_html__( 'Stil 4', 'boilerplate-theme' ),
					'v5' => esc_html__( 'Stil 5', 'boilerplate-theme' ),
					'v6' => esc_html__( 'Stil 6', 'boilerplate-theme' ),
				),
			)
		);

		$general->add_field(
			array(
				'name'    => esc_html__( 'Nach oben', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Nach-oben-Schaltfläche aktivieren oder deaktivieren', 'boilerplate-theme' ),
				'id'      => 'show_backtotop',
				'type'    => 'checkbox',
				'default' => $this->cmb2_set_checkbox_default_true( 'show_backtotop' ),
			)
		);

		$security = new_cmb2_box(
			array(
				'id'           => self::$options_name . '_security',
				'title'        => esc_html__( 'Sicherheit', 'boilerplate-theme' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::$options_name,
				'parent_slug'  => 'themes.php',
				'tab_group'    => $tab_group,
				'tab_title'    => esc_html__( 'Sicherheit', 'boilerplate-theme' ),
			)
		);

		$security->add_field(
			array(
				'name' => esc_html__( 'Passwort-Zurücksetzen deaktivieren', 'boilerplate-theme' ),
				'desc' => esc_html__(
					'Blendet „Passwort vergessen“ aus und blockiert den WordPress-Passwort-Reset (Links und E-Mails). Verringert Missbrauch.',
					'boilerplate-theme'
				),
				'id'   => 'disable_password_reset',
				'type' => 'checkbox',
			)
		);

		$security->add_field(
			array(
				'name' => esc_html__( 'Kommentare deaktivieren', 'boilerplate-theme' ),
				'desc' => esc_html__(
					'Entfernt Kommentar-UI im Backend und der Admin-Bar, deaktiviert Kommentar-Support bei allen Beitragstypen und blockiert neue Kommentar-Einreichungen.',
					'boilerplate-theme'
				),
				'id'   => 'disable_comments',
				'type' => 'checkbox',
			)
		);

		$social = new_cmb2_box(
			array(
				'id'           => self::$options_name . '_social',
				'title'        => esc_html__( 'Social Media', 'boilerplate-theme' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::$options_name,
				'parent_slug'  => 'themes.php',
				'tab_group'    => $tab_group,
				'tab_title'    => esc_html__( 'Social Media', 'boilerplate-theme' ),
			)
		);

		$social->add_field(
			array(
				'name' => esc_html__( 'Hinweis', 'boilerplate-theme' ),
				'desc' => __( 'Geben Sie Ihre Social-Media-Links ein. Lassen Sie das Feld leer, um das Icon auszublenden.', 'boilerplate-theme' ),
				'id'   => 'social_media_info',
				'type' => 'title',
			)
		);

		foreach ( array(
			'facebook'  => esc_html__( 'Facebook', 'boilerplate-theme' ),
			'twitter'   => esc_html__( 'Twitter / X', 'boilerplate-theme' ),
			'instagram' => esc_html__( 'Instagram', 'boilerplate-theme' ),
			'linkedin'  => esc_html__( 'LinkedIn', 'boilerplate-theme' ),
			'youtube'   => esc_html__( 'YouTube', 'boilerplate-theme' ),
			'pinterest' => esc_html__( 'Pinterest', 'boilerplate-theme' ),
		) as $field_id => $label ) {
			$social->add_field(
				array(
					'name' => $label,
					'id'   => $field_id,
					'type' => 'text_url',
				)
			);
		}

		$error = new_cmb2_box(
			array(
				'id'           => self::$options_name . '_error',
				'title'        => esc_html__( '404-Einstellungen', 'boilerplate-theme' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::$options_name,
				'parent_slug'  => 'themes.php',
				'tab_group'    => $tab_group,
				'tab_title'    => esc_html__( '404', 'boilerplate-theme' ),
			)
		);

		$error->add_field(
			array(
				'name'    => esc_html__( 'Fehlertitel', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Geben Sie den erforderlichen Titel ein', 'boilerplate-theme' ),
				'id'      => 'error_title',
				'type'    => 'text',
				'default' => 'Entschuldigung! Die Seite wurde nicht gefunden',
			)
		);

		$error->add_field(
			array(
				'name'    => esc_html__( 'Fehlertext', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Geben Sie den erforderlichen Text ein', 'boilerplate-theme' ),
				'id'      => 'error_text',
				'type'    => 'textarea',
				'default' => 'Diese Seite konnte nicht gefunden werden. Sie wurde möglicherweise entfernt oder umbenannt, oder sie hat möglicherweise nie existiert.',
			)
		);

		$error->add_field(
			array(
				'name'    => esc_html__( 'Button-Text', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Geben Sie den Button-Text ein', 'boilerplate-theme' ),
				'id'      => 'error_btn',
				'type'    => 'text',
				'default' => 'Zurück zur Startseite',
			)
		);

		$custom = new_cmb2_box(
			array(
				'id'           => self::$options_name . '_custom',
				'title'        => esc_html__( 'Custom CSS / JS', 'boilerplate-theme' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::$options_name,
				'parent_slug'  => 'themes.php',
				'tab_group'    => $tab_group,
				'tab_title'    => esc_html__( 'Custom CSS / JS', 'boilerplate-theme' ),
			)
		);

		$custom->add_field(
			array(
				'name'       => esc_html__( 'Benutzerdefiniertes CSS', 'boilerplate-theme' ),
				'desc'       => esc_html__( 'Fügen Sie hier Ihren benutzerdefinierten CSS-Code ein.', 'boilerplate-theme' ),
				'id'         => 'custom_css',
				'type'       => 'textarea_code',
				'attributes' => array(
					'data-codeeditor' => wp_json_encode(
						array(
							'codemirror' => array(
								'mode' => 'css',
							),
						)
					),
				),
			)
		);

		$custom->add_field(
			array(
				'name'       => esc_html__( 'Benutzerdefiniertes JS', 'boilerplate-theme' ),
				'desc'       => esc_html__( 'Fügen Sie hier Ihren JS-Code ein.', 'boilerplate-theme' ),
				'id'         => 'custom_js',
				'type'       => 'textarea_code',
				'attributes' => array(
					'data-codeeditor' => wp_json_encode(
						array(
							'codemirror' => array(
								'mode' => 'javascript',
							),
						)
					),
				),
			)
		);
	}

	/**
	 * Returns published pages as select options (ID => title).
	 *
	 * @return array<string, string>
	 */
	public function get_page_options(): array {
		$pages = get_pages(
			array(
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC',
			)
		);

		$options = array();

		if ( empty( $pages ) || ! is_array( $pages ) ) {
			return $options;
		}

		foreach ( $pages as $page ) {
			$options[ (string) $page->ID ] = $page->post_title;
		}

		return $options;
	}

	/**
	 * Returns true so a checkbox defaults to checked when the key was never saved.
	 *
	 * @param string $field_id Field ID.
	 * @return bool True when the field should default to checked.
	 */
	public function cmb2_set_checkbox_default_true( string $field_id ): bool {
		$options = get_option( self::get_options_name(), array() );

		return ! is_array( $options ) || ! array_key_exists( $field_id, $options );
	}

	/**
	 * Gets the CMB2 option key for this theme.
	 *
	 * @return string Option key.
	 */
	public static function get_options_name(): string {
		if ( isset( self::$options_name ) ) {
			return self::$options_name;
		}

		return wp_get_theme()->get( 'TextDomain' ) . '_options';
	}

	/**
	 * Gets a theme option value.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if option doesn't exist.
	 * @return mixed
	 */
	public static function get_option( string $key, $default_value = null ) {
		if ( ! function_exists( 'cmb2_get_option' ) ) {
			return $default_value;
		}

		if ( \in_array( $key, self::MEDIA_KEYS, true ) ) {
			return self::normalize_media_option( $key, $default_value );
		}

		if ( \in_array( $key, self::CHECKBOX_KEYS, true ) ) {
			return self::normalize_checkbox_option( $key, $default_value );
		}

		$value = cmb2_get_option( self::get_options_name(), $key, $default_value );

		if ( '' === $value || null === $value ) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * Normalizes a CMB2 file field into a media array with url, id, width, and height.
	 *
	 * @param string $key           Field ID.
	 * @param mixed  $default_value Default when empty.
	 * @return mixed Media array or default.
	 */
	private static function normalize_media_option( string $key, $default_value ) {
		$option_key = self::get_options_name();
		$url        = cmb2_get_option( $option_key, $key, '' );
		$attachment = cmb2_get_option( $option_key, $key . '_id', 0 );
		$id         = is_numeric( $attachment ) ? (int) $attachment : 0;

		if ( ( '' === $url || null === $url ) && 0 === $id ) {
			return $default_value;
		}

		$width  = 0;
		$height = 0;

		if ( $id > 0 ) {
			$image = wp_get_attachment_image_src( $id, 'full' );
			if ( is_array( $image ) ) {
				$url    = $url ? (string) $url : (string) $image[0];
				$width  = (int) $image[1];
				$height = (int) $image[2];
			}
		}

		return array(
			'url'    => (string) $url,
			'id'     => $id,
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Normalizes a CMB2 checkbox to a boolean, respecting unsaved defaults.
	 *
	 * @param string $key           Field ID.
	 * @param mixed  $default_value Default when the key was never saved.
	 * @return bool
	 */
	private static function normalize_checkbox_option( string $key, $default_value ): bool {
		$options = get_option( self::get_options_name(), array() );

		if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) ) {
			return (bool) $default_value;
		}

		$value = $options[ $key ];

		return true === $value || 1 === $value || '1' === $value || 'on' === $value;
	}
}
