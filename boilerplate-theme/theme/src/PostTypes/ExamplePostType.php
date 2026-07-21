<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\PostTypes;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers an example custom post type as a scaffold for new projects.
 *
 * Uses Secure Custom Fields (SCF) field groups and the classic editor (Gutenberg off).
 */
class ExamplePostType {
	/**
	 * Post type slug.
	 */
	private const string POST_TYPE = 'example_item';

	/**
	 * Taxonomy slug.
	 */
	private const string TAXONOMY = 'example_category';

	/**
	 * Known SCF field names for this post type (all field types for testing).
	 *
	 * @var list<string>
	 */
	private const array META_KEYS = array(
		'example_text',
		'example_textarea',
		'example_number',
		'example_range',
		'example_email',
		'example_url',
		'example_password',
		'example_select',
		'example_checkbox',
		'example_radio',
		'example_button_group',
		'example_true_false',
		'example_date_picker',
		'example_time_picker',
		'example_date_time_picker',
		'example_color_picker',
		'example_icon_picker',
		'example_wysiwyg',
		'example_oembed',
		'example_image',
		'example_file',
		'example_gallery',
		'example_link',
		'example_page_link',
		'example_post_object',
		'example_relationship',
		'example_taxonomy',
		'example_user',
		'example_nav_menu',
		'example_google_map',
		'example_group',
		'example_repeater',
		'example_flexible_content',
		'example_clone',
	);

	/**
	 * Initializes post type hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'acf/init', array( $this, 'register_scf_fields' ) );
		add_action( 'admin_notices', array( $this, 'render_missing_scf_notice' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
		add_filter( 'dashboard_recent_posts_query_args', array( $this, 'add_to_dashboard' ) );
	}

	/**
	 * Registers the example custom post type.
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'                  => __( 'Beispiele', 'boilerplate-theme' ),
			'singular_name'         => __( 'Beispiel', 'boilerplate-theme' ),
			'menu_name'             => __( 'Beispiele', 'boilerplate-theme' ),
			'name_admin_bar'        => __( 'Beispiel', 'boilerplate-theme' ),
			'add_new'               => __( 'Neu hinzufügen', 'boilerplate-theme' ),
			'add_new_item'          => __( 'Neues Beispiel hinzufügen', 'boilerplate-theme' ),
			'new_item'              => __( 'Neues Beispiel', 'boilerplate-theme' ),
			'edit_item'             => __( 'Beispiel bearbeiten', 'boilerplate-theme' ),
			'view_item'             => __( 'Beispiel ansehen', 'boilerplate-theme' ),
			'all_items'             => __( 'Alle Beispiele', 'boilerplate-theme' ),
			'search_items'          => __( 'Beispiele durchsuchen', 'boilerplate-theme' ),
			'parent_item_colon'     => __( 'Übergeordnetes Beispiel:', 'boilerplate-theme' ),
			'not_found'             => __( 'Keine Beispiele gefunden.', 'boilerplate-theme' ),
			'not_found_in_trash'    => __( 'Keine Beispiele im Papierkorb gefunden.', 'boilerplate-theme' ),
			'featured_image'        => __( 'Beitragsbild', 'boilerplate-theme' ),
			'set_featured_image'    => __( 'Beitragsbild festlegen', 'boilerplate-theme' ),
			'remove_featured_image' => __( 'Beitragsbild entfernen', 'boilerplate-theme' ),
			'use_featured_image'    => __( 'Als Beitragsbild verwenden', 'boilerplate-theme' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'menu_position'       => 23,
			'menu_icon'           => 'dashicons-welcome-write-blog',
			'supports'            => array( 'title', 'editor', 'revisions' ),
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'examples',
				'with_front' => false,
			),
		);

		register_post_type( self::POST_TYPE, $args );
		$this->register_taxonomy();
	}

	/**
	 * Disables the block editor for this post type (classic editor only).
	 *
	 * @param bool   $use_block_editor Whether the post type uses the block editor.
	 * @param string $post_type        Post type slug.
	 * @return bool
	 */
	public function disable_block_editor( bool $use_block_editor, string $post_type ): bool {
		if ( self::POST_TYPE === $post_type ) {
			return false;
		}

		return $use_block_editor;
	}

	/**
	 * Registers SCF field groups for the example post type.
	 *
	 * @return void
	 */
	public function register_scf_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_example_item_fields',
				'title'                 => esc_html__( 'Beispiel-Felder (alle SCF-Typen)', 'boilerplate-theme' ),
				'fields'                => $this->get_all_scf_test_fields(),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => self::POST_TYPE,
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'show_in_rest'          => false,
			)
		);
	}

	/**
	 * Builds SCF field definitions covering every available field type for testing.
	 *
	 * @return list<array<string, mixed>>
	 */
	private function get_all_scf_test_fields(): array {
		$choices = array(
			'option_1' => esc_html__( 'Option 1', 'boilerplate-theme' ),
			'option_2' => esc_html__( 'Option 2', 'boilerplate-theme' ),
			'option_3' => esc_html__( 'Option 3', 'boilerplate-theme' ),
		);

		return array(
			// Layout helpers.
			array(
				'key'       => 'field_example_tab_basic',
				'label'     => esc_html__( 'Basic', 'boilerplate-theme' ),
				'name'      => 'example_tab_basic',
				'type'      => 'tab',
				'placement' => 'top',
			),
			array(
				'key'       => 'field_example_message',
				'label'     => esc_html__( 'Message', 'boilerplate-theme' ),
				'name'      => 'example_message',
				'type'      => 'message',
				'message'   => esc_html__( 'Test field group with all Secure Custom Fields types.', 'boilerplate-theme' ),
				'new_lines' => 'wpautop',
				'esc_html'  => 0,
			),
			array(
				'key'   => 'field_example_separator',
				'label' => esc_html__( 'Separator', 'boilerplate-theme' ),
				'name'  => 'example_separator',
				'type'  => 'separator',
			),
			array(
				'key'          => 'field_example_accordion_open',
				'label'        => esc_html__( 'Accordion', 'boilerplate-theme' ),
				'name'         => 'example_accordion_open',
				'type'         => 'accordion',
				'open'         => 1,
				'multi_expand' => 0,
				'endpoint'     => 0,
			),

			// Text-based.
			array(
				'key'      => 'field_example_text',
				'label'    => esc_html__( 'Text', 'boilerplate-theme' ),
				'name'     => 'example_text',
				'type'     => 'text',
				'required' => 1,
			),
			array(
				'key'   => 'field_example_textarea',
				'label' => esc_html__( 'Textarea', 'boilerplate-theme' ),
				'name'  => 'example_textarea',
				'type'  => 'textarea',
				'rows'  => 3,
			),
			array(
				'key'   => 'field_example_number',
				'label' => esc_html__( 'Number', 'boilerplate-theme' ),
				'name'  => 'example_number',
				'type'  => 'number',
			),
			array(
				'key'           => 'field_example_range',
				'label'         => esc_html__( 'Range', 'boilerplate-theme' ),
				'name'          => 'example_range',
				'type'          => 'range',
				'min'           => 0,
				'max'           => 100,
				'default_value' => 50,
			),
			array(
				'key'           => 'field_example_email',
				'label'         => esc_html__( 'Email', 'boilerplate-theme' ),
				'name'          => 'example_email',
				'type'          => 'email',
				'default_value' => 'test@example.com',
			),
			array(
				'key'   => 'field_example_url',
				'label' => esc_html__( 'URL', 'boilerplate-theme' ),
				'name'  => 'example_url',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_example_password',
				'label' => esc_html__( 'Password', 'boilerplate-theme' ),
				'name'  => 'example_password',
				'type'  => 'password',
			),

			// Choice.
			array(
				'key'       => 'field_example_tab_choice',
				'label'     => esc_html__( 'Choice', 'boilerplate-theme' ),
				'name'      => 'example_tab_choice',
				'type'      => 'tab',
				'placement' => 'top',
			),
			array(
				'key'     => 'field_example_select',
				'label'   => esc_html__( 'Select', 'boilerplate-theme' ),
				'name'    => 'example_select',
				'type'    => 'select',
				'choices' => $choices,
			),
			array(
				'key'     => 'field_example_checkbox',
				'label'   => esc_html__( 'Checkbox', 'boilerplate-theme' ),
				'name'    => 'example_checkbox',
				'type'    => 'checkbox',
				'choices' => $choices,
			),
			array(
				'key'     => 'field_example_radio',
				'label'   => esc_html__( 'Radio', 'boilerplate-theme' ),
				'name'    => 'example_radio',
				'type'    => 'radio',
				'choices' => $choices,
				'layout'  => 'vertical',
			),
			array(
				'key'     => 'field_example_button_group',
				'label'   => esc_html__( 'Button Group', 'boilerplate-theme' ),
				'name'    => 'example_button_group',
				'type'    => 'button_group',
				'choices' => $choices,
			),
			array(
				'key'   => 'field_example_true_false',
				'label' => esc_html__( 'True / False', 'boilerplate-theme' ),
				'name'  => 'example_true_false',
				'type'  => 'true_false',
				'ui'    => 1,
			),
			array(
				'key'         => 'field_example_nav_menu',
				'label'       => esc_html__( 'Nav Menu', 'boilerplate-theme' ),
				'name'        => 'example_nav_menu',
				'type'        => 'nav_menu',
				'save_format' => 'id',
				'allow_null'  => 1,
			),

			// Date / time & color.
			array(
				'key'       => 'field_example_tab_datetime',
				'label'     => esc_html__( 'Date / Color', 'boilerplate-theme' ),
				'name'      => 'example_tab_datetime',
				'type'      => 'tab',
				'placement' => 'top',
			),
			array(
				'key'            => 'field_example_date_picker',
				'label'          => esc_html__( 'Date Picker', 'boilerplate-theme' ),
				'name'           => 'example_date_picker',
				'type'           => 'date_picker',
				'display_format' => 'd.m.Y',
				'return_format'  => 'Y-m-d',
			),
			array(
				'key'            => 'field_example_time_picker',
				'label'          => esc_html__( 'Time Picker', 'boilerplate-theme' ),
				'name'           => 'example_time_picker',
				'type'           => 'time_picker',
				'display_format' => 'H:i',
				'return_format'  => 'H:i:s',
			),
			array(
				'key'            => 'field_example_date_time_picker',
				'label'          => esc_html__( 'Date Time Picker', 'boilerplate-theme' ),
				'name'           => 'example_date_time_picker',
				'type'           => 'date_time_picker',
				'display_format' => 'd.m.Y H:i',
				'return_format'  => 'Y-m-d H:i:s',
			),
			array(
				'key'   => 'field_example_color_picker',
				'label' => esc_html__( 'Color Picker', 'boilerplate-theme' ),
				'name'  => 'example_color_picker',
				'type'  => 'color_picker',
			),
			array(
				'key'           => 'field_example_icon_picker',
				'label'         => esc_html__( 'Icon Picker', 'boilerplate-theme' ),
				'name'          => 'example_icon_picker',
				'type'          => 'icon_picker',
				'return_format' => 'string',
			),

			// Content & media.
			array(
				'key'       => 'field_example_tab_content',
				'label'     => esc_html__( 'Content / Media', 'boilerplate-theme' ),
				'name'      => 'example_tab_content',
				'type'      => 'tab',
				'placement' => 'top',
			),
			array(
				'key'     => 'field_example_wysiwyg',
				'label'   => esc_html__( 'WYSIWYG', 'boilerplate-theme' ),
				'name'    => 'example_wysiwyg',
				'type'    => 'wysiwyg',
				'tabs'    => 'all',
				'toolbar' => 'full',
			),
			array(
				'key'   => 'field_example_oembed',
				'label' => esc_html__( 'oEmbed', 'boilerplate-theme' ),
				'name'  => 'example_oembed',
				'type'  => 'oembed',
			),
			array(
				'key'           => 'field_example_image',
				'label'         => esc_html__( 'Image', 'boilerplate-theme' ),
				'name'          => 'example_image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'thumbnail',
			),
			array(
				'key'           => 'field_example_file',
				'label'         => esc_html__( 'File', 'boilerplate-theme' ),
				'name'          => 'example_file',
				'type'          => 'file',
				'return_format' => 'array',
			),
			array(
				'key'           => 'field_example_gallery',
				'label'         => esc_html__( 'Gallery', 'boilerplate-theme' ),
				'name'          => 'example_gallery',
				'type'          => 'gallery',
				'return_format' => 'array',
				'preview_size'  => 'thumbnail',
			),
			array(
				'key'           => 'field_example_link',
				'label'         => esc_html__( 'Link', 'boilerplate-theme' ),
				'name'          => 'example_link',
				'type'          => 'link',
				'return_format' => 'array',
			),

			// Relational.
			array(
				'key'       => 'field_example_tab_relational',
				'label'     => esc_html__( 'Relational', 'boilerplate-theme' ),
				'name'      => 'example_tab_relational',
				'type'      => 'tab',
				'placement' => 'top',
			),
			array(
				'key'        => 'field_example_page_link',
				'label'      => esc_html__( 'Page Link', 'boilerplate-theme' ),
				'name'       => 'example_page_link',
				'type'       => 'page_link',
				'post_type'  => array( 'page', 'post' ),
				'allow_null' => 1,
				'multiple'   => 0,
			),
			array(
				'key'           => 'field_example_post_object',
				'label'         => esc_html__( 'Post Object', 'boilerplate-theme' ),
				'name'          => 'example_post_object',
				'type'          => 'post_object',
				'post_type'     => array( 'post', 'page' ),
				'return_format' => 'object',
				'allow_null'    => 1,
				'multiple'      => 0,
			),
			array(
				'key'           => 'field_example_relationship',
				'label'         => esc_html__( 'Relationship', 'boilerplate-theme' ),
				'name'          => 'example_relationship',
				'type'          => 'relationship',
				'post_type'     => array( 'post', 'page' ),
				'filters'       => array( 'search' ),
				'return_format' => 'object',
			),
			array(
				'key'           => 'field_example_taxonomy',
				'label'         => esc_html__( 'Taxonomy', 'boilerplate-theme' ),
				'name'          => 'example_taxonomy',
				'type'          => 'taxonomy',
				'taxonomy'      => self::TAXONOMY,
				'field_type'    => 'select',
				'return_format' => 'object',
				'allow_null'    => 1,
			),
			array(
				'key'           => 'field_example_user',
				'label'         => esc_html__( 'User', 'boilerplate-theme' ),
				'name'          => 'example_user',
				'type'          => 'user',
				'return_format' => 'array',
				'allow_null'    => 1,
				'multiple'      => 0,
			),
			array(
				'key'        => 'field_example_google_map',
				'label'      => esc_html__( 'Google Map', 'boilerplate-theme' ),
				'name'       => 'example_google_map',
				'type'       => 'google_map',
				'center_lat' => '52.5200',
				'center_lng' => '13.4050',
				'zoom'       => 12,
			),

			// Layout containers.
			array(
				'key'       => 'field_example_tab_layout',
				'label'     => esc_html__( 'Layout', 'boilerplate-theme' ),
				'name'      => 'example_tab_layout',
				'type'      => 'tab',
				'placement' => 'top',
			),
			array(
				'key'        => 'field_example_group',
				'label'      => esc_html__( 'Group', 'boilerplate-theme' ),
				'name'       => 'example_group',
				'type'       => 'group',
				'layout'     => 'block',
				'sub_fields' => array(
					array(
						'key'   => 'field_example_group_text',
						'label' => esc_html__( 'Group Text', 'boilerplate-theme' ),
						'name'  => 'group_text',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_example_group_number',
						'label' => esc_html__( 'Group Number', 'boilerplate-theme' ),
						'name'  => 'group_number',
						'type'  => 'number',
					),
				),
			),
			array(
				'key'          => 'field_example_repeater',
				'label'        => esc_html__( 'Repeater', 'boilerplate-theme' ),
				'name'         => 'example_repeater',
				'type'         => 'repeater',
				'layout'       => 'block', // table, block, row
				'button_label' => esc_html__( 'Add Row', 'boilerplate-theme' ),
				'min'          => 0,
				'max'          => 5,
				'sub_fields'   => array(
					array(
						'key'   => 'field_example_repeater_text',
						'label' => esc_html__( 'Repeater Text', 'boilerplate-theme' ),
						'name'  => 'repeater_text',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_example_repeater_number',
						'label' => esc_html__( 'Repeater Number', 'boilerplate-theme' ),
						'name'  => 'repeater_number',
						'type'  => 'number',
					),
				),
			),
			array(
				'key'          => 'field_example_flexible_content',
				'label'        => esc_html__( 'Flexible Content', 'boilerplate-theme' ),
				'name'         => 'example_flexible_content',
				'type'         => 'flexible_content',
				'button_label' => esc_html__( 'Add Layout', 'boilerplate-theme' ),
				'layouts'      => array(
					'layout_example_text_block'  => array(
						'key'        => 'layout_example_text_block',
						'name'       => 'text_block',
						'label'      => esc_html__( 'Text Block', 'boilerplate-theme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'   => 'field_example_fc_heading',
								'label' => esc_html__( 'Heading', 'boilerplate-theme' ),
								'name'  => 'heading',
								'type'  => 'text',
							),
							array(
								'key'   => 'field_example_fc_body',
								'label' => esc_html__( 'Body', 'boilerplate-theme' ),
								'name'  => 'body',
								'type'  => 'textarea',
								'rows'  => 3,
							),
						),
					),
					'layout_example_media_block' => array(
						'key'        => 'layout_example_media_block',
						'name'       => 'media_block',
						'label'      => esc_html__( 'Media Block', 'boilerplate-theme' ),
						'display'    => 'block',
						'sub_fields' => array(
							array(
								'key'           => 'field_example_fc_image',
								'label'         => esc_html__( 'Image', 'boilerplate-theme' ),
								'name'          => 'image',
								'type'          => 'image',
								'return_format' => 'array',
							),
							array(
								'key'   => 'field_example_fc_caption',
								'label' => esc_html__( 'Caption', 'boilerplate-theme' ),
								'name'  => 'caption',
								'type'  => 'text',
							),
						),
					),
				),
			),
			array(
				'key'      => 'field_example_accordion_end',
				'label'    => esc_html__( 'Accordion End', 'boilerplate-theme' ),
				'name'     => 'example_accordion_end',
				'type'     => 'accordion',
				'endpoint' => 1,
			),
		);
	}

	/**
	 * Renders an admin notice when Secure Custom Fields is missing.
	 *
	 * @return void
	 */
	public function render_missing_scf_notice(): void {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__(
			'Secure Custom Fields is not installed. Please install it to use example post type fields.',
			'boilerplate-theme'
		);
		echo ' <a href="https://wordpress.org/plugins/secure-custom-fields/" target="_blank" rel="noopener noreferrer">Secure Custom Fields</a>';
		echo '</p></div>';
	}

	/**
	 * Gets an SCF field value for an example post.
	 *
	 * @param int         $post_id       Post ID.
	 * @param string|null $key           Meta key, or null for all known meta.
	 * @param mixed       $default_value Default when the value is empty.
	 * @return mixed Meta value, all meta as array, or default.
	 */
	public static function get_meta( int $post_id, ?string $key = null, $default_value = null ) {
		if ( ! function_exists( 'get_field' ) ) {
			return $default_value;
		}

		if ( null === $key ) {
			$values = array();

			foreach ( self::META_KEYS as $meta_key ) {
				$values[ $meta_key ] = get_field( $meta_key, $post_id );
			}

			return $values;
		}

		$value = get_field( $key, $post_id );

		if ( '' === $value || null === $value || false === $value ) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * Registers the example category taxonomy.
	 *
	 * @return void
	 */
	private function register_taxonomy(): void {
		$labels = array(
			'name'              => __( 'Beispiel-Kategorien', 'boilerplate-theme' ),
			'singular_name'     => __( 'Beispiel-Kategorie', 'boilerplate-theme' ),
			'search_items'      => __( 'Kategorien durchsuchen', 'boilerplate-theme' ),
			'all_items'         => __( 'Alle Kategorien', 'boilerplate-theme' ),
			'parent_item'       => __( 'Übergeordnete Kategorie', 'boilerplate-theme' ),
			'parent_item_colon' => __( 'Übergeordnete Kategorie:', 'boilerplate-theme' ),
			'edit_item'         => __( 'Kategorie bearbeiten', 'boilerplate-theme' ),
			'update_item'       => __( 'Kategorie aktualisieren', 'boilerplate-theme' ),
			'add_new_item'      => __( 'Neue Kategorie hinzufügen', 'boilerplate-theme' ),
			'new_item_name'     => __( 'Neuer Kategoriename', 'boilerplate-theme' ),
			'menu_name'         => __( 'Kategorien', 'boilerplate-theme' ),
		);

		register_taxonomy(
			self::TAXONOMY,
			array( self::POST_TYPE ),
			array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'example-category',
					'with_front' => false,
				),
			),
		);
	}

	/**
	 * Adds the example post type to the dashboard recent posts widget.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 * @return array<string, mixed>
	 */
	public function add_to_dashboard( array $args ): array {
		$post_types = isset( $args['post_type'] ) ? (array) $args['post_type'] : array( 'post' );
		if ( ! in_array( self::POST_TYPE, $post_types, true ) ) {
			$post_types[] = self::POST_TYPE;
		}
		$args['post_type'] = $post_types;
		return $args;
	}
}
