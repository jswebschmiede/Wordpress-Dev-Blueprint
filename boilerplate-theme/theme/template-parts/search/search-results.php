<?php
/**
 * Template part for displaying custom search results.
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

$search_query = $args['search_query'] ?? null;
$search_term  = isset( $args['search_term'] ) ? (string) $args['search_term'] : '';
$current_page = isset( $args['current_page'] ) ? absint( (string) $args['current_page'] ) : 1;
$search_url   = isset( $args['search_url'] ) ? (string) $args['search_url'] : home_url( '/' );

if ( ! ( $search_query instanceof \WP_Query ) ) {
	return;
}

$found_posts = (int) $search_query->found_posts;
$pagination  = paginate_links(
	array(
		'base'         => add_query_arg(
			array(
				'search_term' => $search_term,
				'paged'       => '%#%',
			),
			$search_url
		),
		'format'       => '',
		'current'      => max( 1, $current_page ),
		'total'        => max( 1, (int) $search_query->max_num_pages ),
		'type'         => 'list',
		'add_fragment' => '#search-results',
		'prev_text'    => __( 'Zurück', 'boilerplate-theme' ),
		'next_text'    => __( 'Weiter', 'boilerplate-theme' ),
	)
);
?>

<div id="search-results" class="search-results flex flex-col lg:gap-12 gap-6">
	<header class="search-results__header flex flex-col gap-3">
		<h2 class="not-prose text-primary text-3xl md:text-5xl font-bold">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: Number of search results. */
					_n( '%s Treffer', '%s Treffer', $found_posts, 'boilerplate-theme' ),
					number_format_i18n( $found_posts )
				)
			);
			?>
		</h2>
		<p class="search-results__description">
			<?php
			printf(
				esc_html(
					/* translators: 1: Search term, 2: Number of results. */
					_n(
						'Für Ihren Suchbegriff "%1$s" wurde "%2$s" Ergebnis gefunden.',
						'Für Ihren Suchbegriff "%1$s" wurden "%2$s" Ergebnisse gefunden.',
						$found_posts,
						'boilerplate-theme'
					)
				),
				esc_html( $search_term ),
				number_format_i18n( $found_posts )
			);
			?>
		</p>
	</header>

	<?php if ( $search_query->have_posts() ) : ?>
		<div class="search-results__list flex flex-col gap-6">
			<?php
			while ( $search_query->have_posts() ) :
				$search_query->the_post();

				get_template_part(
					'template-parts/search/search-result-item',
					null,
					array(
						'search_term' => $search_term,
					)
				);
			endwhile;
			?>
		</div>

		<?php if ( is_string( $pagination ) && '' !== $pagination ) : ?>
			<nav class="search-results__pagination" aria-label="<?php esc_attr_e( 'Suchergebnisse Navigation', 'boilerplate-theme' ); ?>">
				<?php echo wp_kses_post( $pagination ); ?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<div class="search-results__no-results card">
			<h3>
				<?php esc_html_e( 'Keine passenden Ergebnisse gefunden', 'boilerplate-theme' ); ?>
			</h3>
			<p class="mb-0!">
				<?php
				printf(
					/* translators: %s: Search term. */
					esc_html__( 'Für "%s" konnten wir aktuell keine Inhalte finden. Versuchen Sie es mit einem allgemeineren Begriff oder einer anderen Schreibweise.', 'boilerplate-theme' ),
					esc_html( $search_term )
				);
				?>
			</p>
		</div>
	<?php endif; ?>
</div>
