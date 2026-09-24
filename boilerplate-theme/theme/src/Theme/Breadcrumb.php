<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Theme;

\defined( 'ABSPATH' ) || exit;

/**
 * Builds breadcrumb items for the current request (markup lives in views/partials/breadcrumb.twig).
 * Supports all standard WordPress page types: singular posts/pages, categories,
 * tags, taxonomies, archives, search, author and 404 pages.
 */
class Breadcrumb {
	/**
	 * @param bool $show_on_home Whether to return breadcrumbs on the front page.
	 * @param bool $show_current Whether to include the current page title as the last crumb.
	 */
	public function __construct(
		/**
		 * Whether to show the breadcrumb on the home page.
		 */
		private readonly bool $show_on_home = false,
		/**
		 * Whether to show the current post/page title as the last crumb.
		 */
		private readonly bool $show_current = true
	) {
	}

	/**
	 * Returns the CPT-to-page breadcrumb map.
	 *
	 * Register the map via the {@see 'boilerplate_theme_breadcrumb_cpt_page_map'} filter
	 * (e.g. in the child theme's `functions.php`). Paths are WordPress page slugs/paths
	 * resolved with `get_page_by_path()`. Optional `*_label` keys are fallbacks when the
	 * page does not exist.
	 *
	 * Example:
	 *
	 *     add_filter( 'boilerplate_theme_breadcrumb_cpt_page_map', function ( array $map ): array {
	 *         $map['example_item'] = array(
	 *             'parent_path'        => 'about',
	 *             'parent_label'       => 'About us',
	 *             'intermediate_path'  => 'about/news',
	 *             'intermediate_label' => 'News',
	 *             'list_path'          => 'about/news/examples',
	 *         );
	 *         return $map;
	 *     } );
	 *
	 * @return array<string, array<string, string>>
	 */
	private function get_cpt_page_map(): array {
		$map = apply_filters( 'boilerplate_theme_breadcrumb_cpt_page_map', array() );

		return is_array( $map ) ? $map : array();
	}

	/**
	 * Returns the ordered breadcrumb items for the current request.
	 *
	 * Each item has a `label` and an optional `url` (null for the current page).
	 *
	 * @return array<int, array<string, string|null>> Empty when breadcrumbs are hidden on the front page.
	 */
	public function get_crumbs(): array {
		if ( ( is_home() || is_front_page() ) && ! $this->show_on_home ) {
			return array();
		}

		return $this->build_crumbs();
	}

	/**
	 * Builds the ordered list of breadcrumb crumbs for the current page.
	 *
	 * @return array<int, array<string, string|null>>
	 */
	private function build_crumbs(): array {
		$home_label = __( 'Startseite', 'boilerplate-theme' );
		$crumbs     = array(
			array(
				'label' => $home_label,
				'url'   => home_url( '/' ),
			),
		);

		if ( is_home() || is_front_page() ) {
			return $crumbs;
		}

		if ( is_category() ) {
			$crumbs   = array_merge( $crumbs, $this->get_category_crumbs() );
			$crumbs[] = array(
				'label' => single_cat_title( '', false ),
				'url'   => null,
			);
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof \WP_Term ) {
				$crumbs[] = array(
					'label' => $term->name,
					'url'   => null,
				);
			}
		} elseif ( is_tag() ) {
			$crumbs[] = array(
				'label' => single_tag_title( '', false ),
				'url'   => null,
			);
		} elseif ( is_author() ) {
			$crumbs[] = array(
				'label' => sprintf(
					/* translators: %s: author display name */
					__( 'Beiträge von %s', 'boilerplate-theme' ),
					get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) )
				),
				'url'   => null,
			);
		} elseif ( is_search() ) {
			$crumbs[] = array(
				'label' => sprintf(
					/* translators: %s: search query */
					__( 'Suchergebnisse für „%s“', 'boilerplate-theme' ),
					get_search_query()
				),
				'url'   => null,
			);
		} elseif ( is_year() ) {
			$crumbs[] = array(
				'label' => get_the_date( 'Y' ),
				'url'   => null,
			);
		} elseif ( is_month() ) {
			$crumbs[] = array(
				'label' => get_the_date( 'Y' ),
				'url'   => get_year_link( (int) get_the_date( 'Y' ) ),
			);
			$crumbs[] = array(
				'label' => get_the_date( 'F Y' ),
				'url'   => null,
			);
		} elseif ( is_day() ) {
			$crumbs[] = array(
				'label' => get_the_date( 'Y' ),
				'url'   => get_year_link( (int) get_the_date( 'Y' ) ),
			);
			$crumbs[] = array(
				'label' => get_the_date( 'F Y' ),
				'url'   => get_month_link( (int) get_the_date( 'Y' ), (int) get_the_date( 'm' ) ),
			);
			$crumbs[] = array(
				'label' => get_the_date( 'd. F Y' ),
				'url'   => null,
			);
		} elseif ( is_404() ) {
			$crumbs[] = array(
				'label' => __( 'Fehler 404', 'boilerplate-theme' ),
				'url'   => null,
			);
		} elseif ( is_attachment() ) {
			$crumbs = array_merge( $crumbs, $this->get_attachment_crumbs() );
		} elseif ( is_single() ) {
			$crumbs = array_merge( $crumbs, $this->get_single_crumbs() );
		} elseif ( is_page() ) {
			$crumbs = array_merge( $crumbs, $this->get_page_crumbs() );
		} elseif ( is_post_type_archive() ) {
			$post_type = get_queried_object();
			if ( $post_type instanceof \WP_Post_Type ) {
				$crumbs[] = array(
					'label' => $post_type->labels->name,
					'url'   => null,
				);
			}
		}

		$paged = (int) get_query_var( 'paged' );
		if ( 1 < $paged ) {
			$last_key = array_key_last( $crumbs );
			if ( null !== $last_key ) {
				$crumbs[ $last_key ]['label'] .= sprintf(
					/* translators: %d: page number */
					' — ' . __( 'Seite %d', 'boilerplate-theme' ),
					$paged
				);
			}
		}

		return $crumbs;
	}

	/**
	 * Returns crumbs for a category archive, including ancestor categories.
	 *
	 * @return array<int, array<string, string|null>>
	 */
	private function get_category_crumbs(): array {
		$crumbs = array();
		$cat    = get_category( get_query_var( 'cat' ) );

		if ( ! $cat instanceof \WP_Term || 0 === $cat->parent ) {
			return $crumbs;
		}

		$ancestors = get_ancestors( $cat->term_id, 'category', 'taxonomy' );

		foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
			$ancestor = get_category( $ancestor_id );
			if ( $ancestor instanceof \WP_Term ) {
				$crumbs[] = array(
					'label' => $ancestor->name,
					'url'   => get_category_link( $ancestor->term_id ),
				);
			}
		}

		return $crumbs;
	}

	/**
	 * Returns crumbs for a single post (standard post or custom post type).
	 *
	 * @return array<int, array<string, string|null>>
	 */
	private function get_single_crumbs(): array {
		$crumbs       = array();
		$post_type    = get_post_type();
		$cpt_page_map = $this->get_cpt_page_map();

		if ( 'post' === $post_type ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$category  = $categories[0];
				$ancestors = get_ancestors( $category->term_id, 'category', 'taxonomy' );

				foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
					$ancestor = get_category( $ancestor_id );
					if ( $ancestor instanceof \WP_Term ) {
						$crumbs[] = array(
							'label' => $ancestor->name,
							'url'   => get_category_link( $ancestor->term_id ),
						);
					}
				}

				$crumbs[] = array(
					'label' => $category->name,
					'url'   => get_category_link( $category->term_id ),
				);
			}
		} else {
			if ( isset( $cpt_page_map[ $post_type ] ) ) {
				$cpt_map = $cpt_page_map[ $post_type ];

				if ( ! empty( $cpt_map['parent_path'] ) ) {
					$crumbs[] = $this->get_page_path_crumb(
						$cpt_map['parent_path'],
						$cpt_map['parent_label'] ?? ''
					);
				}

				if ( ! empty( $cpt_map['intermediate_path'] ) ) {
					$crumbs[] = $this->get_page_path_crumb(
						$cpt_map['intermediate_path'],
						$cpt_map['intermediate_label'] ?? ''
					);
				}
			}

			$post_type_object = get_post_type_object( $post_type );
			if ( $post_type_object instanceof \WP_Post_Type ) {
				$archive_url = get_post_type_archive_link( $post_type );
				$cpt_label   = $post_type_object->labels->name;

				if ( isset( $cpt_page_map[ $post_type ]['list_path'] ) ) {
					$list_crumb = $this->get_page_path_crumb(
						$cpt_page_map[ $post_type ]['list_path'],
						$cpt_label
					);

					if ( null === $list_crumb['url'] && $archive_url ) {
						$list_crumb['url'] = $archive_url;
					}

					$crumbs[] = $list_crumb;
				} else {
					$crumbs[] = array(
						'label' => $cpt_label,
						'url'   => $archive_url ?: null,
					);
				}
			}
		}

		if ( $this->show_current ) {
			$crumbs[] = array(
				'label' => get_the_title(),
				'url'   => null,
			);
		}

		return $crumbs;
	}

	/**
	 * Builds a breadcrumb crumb from a page path with a text fallback.
	 *
	 * @param string $path           Page path used for lookup.
	 * @param string $fallback_label Label used when the page cannot be found.
	 * @return array<string, string|null>
	 */
	private function get_page_path_crumb( string $path, string $fallback_label ): array {
		$page = get_page_by_path( $path );

		if ( $page instanceof \WP_Post ) {
			return array(
				'label' => get_the_title( $page->ID ),
				'url'   => get_permalink( $page->ID ),
			);
		}

		return array(
			'label' => $fallback_label,
			'url'   => null,
		);
	}

	/**
	 * Returns crumbs for a page, including ancestor pages.
	 *
	 * @return array<int, array<string, string|null>>
	 */
	private function get_page_crumbs(): array {
		$crumbs = array();
		$post   = get_post();

		if ( ! $post instanceof \WP_Post ) {
			return $crumbs;
		}

		if ( $post->post_parent ) {
			$ancestors = get_ancestors( $post->ID, 'page', 'post_type' );

			foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
				$ancestor = get_post( $ancestor_id );
				if ( $ancestor instanceof \WP_Post ) {
					$crumbs[] = array(
						'label' => get_the_title( $ancestor->ID ),
						'url'   => get_permalink( $ancestor->ID ),
					);
				}
			}
		}

		if ( $this->show_current ) {
			$crumbs[] = array(
				'label' => get_the_title(),
				'url'   => null,
			);
		}

		return $crumbs;
	}

	/**
	 * Returns crumbs for a media attachment page.
	 *
	 * @return array<int, array<string, string|null>>
	 */
	private function get_attachment_crumbs(): array {
		$crumbs = array();
		$post   = get_post();

		if ( ! $post instanceof \WP_Post || ! $post->post_parent ) {
			if ( $this->show_current ) {
				$crumbs[] = array(
					'label' => get_the_title(),
					'url'   => null,
				);
			}
			return $crumbs;
		}

		$parent = get_post( $post->post_parent );

		if ( $parent instanceof \WP_Post ) {
			$categories = get_the_category( $parent->ID );
			if ( ! empty( $categories ) ) {
				$crumbs[] = array(
					'label' => $categories[0]->name,
					'url'   => get_category_link( $categories[0]->term_id ),
				);
			}

			$crumbs[] = array(
				'label' => get_the_title( $parent->ID ),
				'url'   => get_permalink( $parent->ID ),
			);
		}

		if ( $this->show_current ) {
			$crumbs[] = array(
				'label' => get_the_title(),
				'url'   => null,
			);
		}

		return $crumbs;
	}
}
