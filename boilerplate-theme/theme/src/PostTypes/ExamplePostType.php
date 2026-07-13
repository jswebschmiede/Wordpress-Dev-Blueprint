<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\PostTypes;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers an example custom post type as a scaffold for new projects.
 */
class ExamplePostType {
	/**
	 * Post type slug.
	 */
	private const POST_TYPE = 'example_item';

	/**
	 * Taxonomy slug.
	 */
	private const TAXONOMY = 'example_category';

	/**
	 * Initializes post type hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
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
			'has_archive'         => true,
			'menu_position'       => 23,
			'menu_icon'           => 'dashicons-welcome-write-blog',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
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
