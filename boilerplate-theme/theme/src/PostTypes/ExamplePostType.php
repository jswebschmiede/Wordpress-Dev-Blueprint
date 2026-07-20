<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\PostTypes;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers an example custom post type as a scaffold for new projects.
 *
 * Uses CMB2 metaboxes for custom fields and the classic editor (Gutenberg off).
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
	 * Initializes post type hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'cmb2_admin_init', array( $this, 'register_metaboxes' ) );
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
			'show_in_rest'        => false,
			'has_archive'         => true,
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
	 * Registers CMB2 metaboxes for the example post type.
	 *
	 * @return void
	 */
	public function register_metaboxes(): void {
		if ( ! function_exists( 'new_cmb2_box' ) ) {
			return;
		}

		$cmb = new_cmb2_box(
			array(
				'id'           => 'example_item_metabox',
				'title'        => esc_html__( 'Beispiel-Felder', 'boilerplate-theme' ),
				'object_types' => array( self::POST_TYPE ),
				'context'      => 'normal',
				'priority'     => 'high',
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Untertitel', 'boilerplate-theme' ),
				'desc' => esc_html__( 'Beispiel-Textfeld für diesen Post Type.', 'boilerplate-theme' ),
				'id'   => 'example_subtitle',
				'type' => 'text',
			)
		);

		$cmb->add_field(
			array(
				'name'    => esc_html__( 'Layout', 'boilerplate-theme' ),
				'desc'    => esc_html__( 'Beispiel-Radio-Buttons für die Layout-Auswahl.', 'boilerplate-theme' ),
				'id'      => 'example_layout',
				'type'    => 'radio_inline',
				'options' => array(
					'default' => esc_html__( 'Standard', 'boilerplate-theme' ),
					'wide'    => esc_html__( 'Breit', 'boilerplate-theme' ),
					'narrow'  => esc_html__( 'Schmal', 'boilerplate-theme' ),
				),
				'default' => 'default',
			)
		);

		$group_id = $cmb->add_field(
			array(
				'id'          => 'example_items',
				'type'        => 'group',
				'description' => esc_html__( 'Beispiel-Repeater mit Titel und Text pro Zeile.', 'boilerplate-theme' ),
				'options'     => array(
					'group_title'   => esc_html__( 'Element {#}', 'boilerplate-theme' ),
					'add_button'    => esc_html__( 'Element hinzufügen', 'boilerplate-theme' ),
					'remove_button' => esc_html__( 'Element entfernen', 'boilerplate-theme' ),
					'sortable'      => true,
					'closed'        => true,
				),
			)
		);

		$cmb->add_group_field(
			$group_id,
			array(
				'name' => esc_html__( 'Titel', 'boilerplate-theme' ),
				'id'   => 'example_item_title',
				'type' => 'text',
			)
		);

		$cmb->add_group_field(
			$group_id,
			array(
				'name' => esc_html__( 'Text', 'boilerplate-theme' ),
				'id'   => 'example_item_text',
				'type' => 'text',
			)
		);
	}

	/**
	 * Gets a CMB2 meta value for an example post.
	 *
	 * Group field `example_items` returns an array of rows, each with
	 * `example_item_title` and `example_item_text` keys.
	 *
	 * @param int         $post_id       Post ID.
	 * @param string|null $key           Meta key, or null for all registered keys.
	 * @param mixed       $default_value Default when the value is empty.
	 * @return mixed Meta value, all meta as array, or default.
	 */
	public static function get_meta( int $post_id, ?string $key = null, $default_value = null ) {
		$keys = array( 'example_subtitle', 'example_layout', 'example_items' );

		if ( null === $key ) {
			$all = array();
			foreach ( $keys as $meta_key ) {
				$all[ $meta_key ] = get_post_meta( $post_id, $meta_key, true );
			}

			return $all;
		}

		$value = get_post_meta( $post_id, $key, true );

		if ( '' === $value || null === $value ) {
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
