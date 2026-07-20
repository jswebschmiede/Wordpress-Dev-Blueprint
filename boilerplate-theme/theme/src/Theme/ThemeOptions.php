<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Manages theme options with CMB2.
 */
class ThemeOptions {
	/**
	 * Primary CMB2 option key (general tab / legacy storage).
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
	 * Maps each field ID to its CMB2 option_key group suffix.
	 * Empty string = primary options key (legacy).
	 *
	 * @var array<string, string>
	 */
	private const array FIELD_GROUPS = array(
		'logo'                   => '',
		'logo_footer'            => '',
		'website_title'          => '',
		'search_page'            => '',
		'show_breadcrumb'        => '',
		'show_preloader'         => '',
		'preloader_style'        => '',
		'show_backtotop'         => '',
		'disable_password_reset' => 'security',
		'disable_comments'       => 'security',
		'facebook'               => 'social',
		'twitter'                => 'social',
		'instagram'              => 'social',
		'linkedin'               => 'social',
		'youtube'                => 'social',
		'pinterest'              => 'social',
		'error_title'            => 'error',
		'error_text'             => 'error',
		'error_btn'              => 'error',
		'custom_css'             => 'custom',
		'custom_js'              => 'custom',
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
				'menu_title'   => esc_html__( 'Theme Options', 'boilerplate-theme' ),
				'capability'   => 'manage_options',
				'icon_url'     => 'dashicons-admin-customizer',
				'position'     => 59,
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
					'url' => false,
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
				'escape_cb'    => array( $this, 'escape_file_field_value' ),
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
					'url' => false,
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
				'escape_cb'    => array( $this, 'escape_file_field_value' ),
			)
		);

		$general->add_field(
			array(
				'name' => esc_html__( 'Breadcrumb-Navigation', 'boilerplate-theme' ),
				'desc' => esc_html__( 'Breadcrumb-Navigation aktivieren oder deaktivieren', 'boilerplate-theme' ),
				'id'   => 'show_breadcrumb',
				'type' => 'checkbox',
			)
		);

		$general->add_field(
			array(
				'name' => esc_html__( 'Preloader', 'boilerplate-theme' ),
				'desc' => esc_html__( 'Preloader aktivieren oder deaktivieren', 'boilerplate-theme' ),
				'id'   => 'show_preloader',
				'type' => 'checkbox',
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
				'name' => esc_html__( 'Nach oben', 'boilerplate-theme' ),
				'desc' => esc_html__( 'Nach-oben-Schaltfläche aktivieren oder deaktivieren', 'boilerplate-theme' ),
				'id'   => 'show_backtotop',
				'type' => 'checkbox',
			)
		);

		$security = new_cmb2_box(
			array(
				'id'           => self::$options_name . '_security',
				'title'        => esc_html__( 'Sicherheit', 'boilerplate-theme' ),
				'object_types' => array( 'options-page' ),
				'option_key'   => self::get_group_option_key( 'security' ),
				'parent_slug'  => self::$options_name,
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
				'option_key'   => self::get_group_option_key( 'social' ),
				'parent_slug'  => self::$options_name,
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
				'option_key'   => self::get_group_option_key( 'error' ),
				'parent_slug'  => self::$options_name,
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
				'option_key'   => self::get_group_option_key( 'custom' ),
				'parent_slug'  => self::$options_name,
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
	 * Escapes a CMB2 file field value as a URL string.
	 *
	 * @param mixed               $value      Raw field value.
	 * @param array<string,mixed> $field_args Field arguments.
	 * @param object              $field      CMB2 field instance (unused).
	 * @return string Escaped URL or empty string.
	 */
	public function escape_file_field_value( $value, array $field_args, object $field ): string {
		unset( $field_args, $field );

		if ( is_array( $value ) ) {
			if ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
				$value = $value['url'];
			} elseif ( isset( $value['value'] ) && is_string( $value['value'] ) ) {
				$value = $value['value'];
			} else {
				$value = '';
			}
		}

		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}

		return esc_url( $value );
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
	 * Gets the primary CMB2 option key for this theme.
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
	 * Builds the option key for a tab group suffix.
	 *
	 * @param string $group Group suffix (e.g. security). Empty for primary key.
	 * @return string Full option key.
	 */
	public static function get_group_option_key( string $group ): string {
		$base = self::get_options_name();

		return '' === $group ? $base : $base . '_' . $group;
	}

	/**
	 * Resolves the option key that stores a given field.
	 *
	 * @param string $field_id Field ID.
	 * @return string Option key.
	 */
	public static function get_option_key_for_field( string $field_id ): string {
		$group = self::FIELD_GROUPS[ $field_id ] ?? '';

		return self::get_group_option_key( $group );
	}

	/**
	 * Gets a theme option value.
	 *
	 * Uses WordPress get_option() so values are readable before CMB2 helper functions load.
	 *
	 * @param string $key           Option key.
	 * @param mixed  $default_value Default value if option doesn't exist.
	 * @return mixed
	 */
	public static function get_option( string $key, $default_value = null ) {
		if ( \in_array( $key, self::MEDIA_KEYS, true ) ) {
			return self::normalize_media_option( $key, $default_value );
		}

		if ( \in_array( $key, self::CHECKBOX_KEYS, true ) ) {
			return self::normalize_checkbox_option( $key, $default_value );
		}

		$value = self::read_field_value( $key, null );

		if ( '' === $value || null === $value ) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * Reads a single field from a CMB2 options array.
	 *
	 * @param string $option_key Option name in wp_options.
	 * @param string $field_id   Field ID.
	 * @param mixed  $default_value Default when missing.
	 * @return mixed
	 */
	private static function read_from_option_array( string $option_key, string $field_id, $default_value = null ) {
		$options = get_option( $option_key, array() );

		if ( ! is_array( $options ) || ! array_key_exists( $field_id, $options ) ) {
			return $default_value;
		}

		return $options[ $field_id ];
	}

	/**
	 * Reads a field from its group option key, falling back to the legacy primary key.
	 *
	 * @param string $key           Field ID.
	 * @param mixed  $default_value Default when missing.
	 * @return mixed
	 */
	private static function read_field_value( string $key, $default_value ) {
		$option_key = self::get_option_key_for_field( $key );
		$value      = self::read_from_option_array( $option_key, $key, null );

		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}

		if ( self::get_options_name() !== $option_key ) {
			$legacy = self::read_from_option_array( self::get_options_name(), $key, null );
			if ( null !== $legacy && false !== $legacy && '' !== $legacy ) {
				return $legacy;
			}
		}

		return $default_value;
	}

	/**
	 * Normalizes a CMB2 file field into a media array with url, id, width, and height.
	 *
	 * @param string $key           Field ID.
	 * @param mixed  $default_value Default when empty.
	 * @return mixed Media array or default.
	 */
	private static function normalize_media_option( string $key, $default_value ) {
		$option_key = self::get_option_key_for_field( $key );
		$url        = self::read_from_option_array( $option_key, $key, null );
		$attachment = self::read_from_option_array( $option_key, $key . '_id', null );

		if ( ( null === $url || false === $url || '' === $url ) && self::get_options_name() !== $option_key ) {
			$url        = self::read_from_option_array( self::get_options_name(), $key, null );
			$attachment = self::read_from_option_array( self::get_options_name(), $key . '_id', null );
		}

		$id = 0;

		if ( is_array( $url ) ) {
			if ( isset( $url['id'] ) && is_numeric( $url['id'] ) ) {
				$id = (int) $url['id'];
			} elseif ( isset( $url['supporting_field_value'] ) && is_numeric( $url['supporting_field_value'] ) ) {
				$id = (int) $url['supporting_field_value'];
			}

			if ( isset( $url['url'] ) && is_string( $url['url'] ) ) {
				$url = $url['url'];
			} elseif ( isset( $url['value'] ) && is_string( $url['value'] ) ) {
				$url = $url['value'];
			} else {
				$url = '';
			}
		}

		if ( is_numeric( $attachment ) ) {
			$id = (int) $attachment;
		}

		if ( ( ! is_string( $url ) || '' === $url ) && 0 === $id ) {
			return $default_value;
		}

		$url    = is_string( $url ) ? $url : '';
		$width  = 0;
		$height = 0;

		if ( $id > 0 ) {
			$image = wp_get_attachment_image_src( $id, 'full' );
			if ( is_array( $image ) ) {
				$url    = '' !== $url ? $url : (string) $image[0];
				$width  = (int) $image[1];
				$height = (int) $image[2];
			}
		}

		return array(
			'url'    => $url,
			'id'     => $id,
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Normalizes a CMB2 checkbox to a boolean, respecting unsaved defaults.
	 *
	 * CMB2 removes unchecked checkbox keys from the options array. Once a tab's
	 * option has been saved, a missing key means "off" — never fall back to a
	 * stale value in the legacy primary option.
	 *
	 * @param string $key           Field ID.
	 * @param mixed  $default_value Default when the key was never saved.
	 * @return bool
	 */
	private static function normalize_checkbox_option( string $key, $default_value ): bool {
		$option_key = self::get_option_key_for_field( $key );
		$options    = get_option( $option_key, null );

		if ( is_array( $options ) ) {
			if ( ! array_key_exists( $key, $options ) ) {
				return false;
			}

			$value = $options[ $key ];

			if ( '0' === $value || 0 === $value || false === $value || '' === $value ) {
				return false;
			}

			return true === $value || 1 === $value || '1' === $value || 'on' === $value;
		}

		// Group option never saved: optional one-time legacy read.
		if ( self::get_options_name() !== $option_key ) {
			$legacy = get_option( self::get_options_name(), array() );

			if ( is_array( $legacy ) && array_key_exists( $key, $legacy ) ) {
				$value = $legacy[ $key ];

				if ( '0' === $value || 0 === $value || false === $value || '' === $value ) {
					return false;
				}

				return true === $value || 1 === $value || '1' === $value || 'on' === $value;
			}
		}

		return (bool) $default_value;
	}
}
