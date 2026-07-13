<?php
/**
 * The template for displaying search results pages
 *
 * Template Name: Search-Template
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use SmartMedia24\BoilerplateTheme\Theme\ThemeOptions;

if ( have_posts() ) {
	the_post();
}

$search_term              = boilerplate_theme_get_current_search_term();
$show_breadcrumb          = ThemeOptions::get_option( 'show_breadcrumb', false );
$breadcrumb_visible_class = ! $show_breadcrumb ? 'mb-12' : '';

$current_page = max(
	1,
	absint( get_query_var( 'paged' ) ),
	absint( get_query_var( 'page' ) ),
	isset( $_GET['paged'] ) ? absint( wp_unslash( (string) $_GET['paged'] ) ) : 0
);
$search_query = null;

if ( '' !== $search_term ) {
	$search_query = new \WP_Query(
		array(
			's'              => $search_term,
			'paged'          => $current_page,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		)
	);
}

get_header();
?>
<section id="primary">
	<main id="main">

		<div class="entry-header <?php echo esc_attr( $breadcrumb_visible_class ); ?>">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</div>

		<?php if ( $show_breadcrumb ) : ?>
			<?php boilerplate_theme_breadcrumb(); ?>
		<?php endif; ?>

		<section class="bg-light-gray py-6">
			<div class="mx-auto flex max-w-wide w-p-1 flex-col gap-4 lg:w-p-2">
				<p class="sr-only">
					<?php esc_html_e( 'Ihre Suchbegriffe', 'boilerplate-theme' ); ?>
				</p>

				<?php get_search_form(); ?>
			</div>
		</section>

		<div <?php boilerplate_theme_content_class( 'entry-content pt-14 max-w-wide w-full mx-auto lg:w-p-2' ); ?>>
			<?php if ( $search_query instanceof \WP_Query ) : ?>
				<?php
				get_template_part(
					'template-parts/search/search-results',
					null,
					array(
						'search_query' => $search_query,
						'search_term'  => $search_term,
						'current_page' => $current_page,
						'search_url'   => boilerplate_theme_get_search_page_url(),
					)
				);
				?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="card">
					<h2 class="text-primary">
						<?php esc_html_e( 'Starten Sie Ihre Suche', 'boilerplate-theme' ); ?>
					</h2>
					<p class="mb-0!">
						<?php esc_html_e( 'Geben Sie einen Suchbegriff ein, um Inhalte, Seiten und Beiträge zu durchsuchen.', 'boilerplate-theme' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</main><!-- #main -->
</section><!-- #primary -->
<?php
get_footer();
