<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package BoilerplateTheme
 */

use SmartMedia24\BoilerplateTheme\Theme\ThemeOptions;

$show_breadcrumb          = ThemeOptions::get_option( 'show_breadcrumb', false );
$breadcrumb_visible_class = ! $show_breadcrumb ? 'mb-12' : '';

get_header();

?>

	<section id="primary">
		<main id="main">

			<div class="entry-header <?php echo esc_attr( $breadcrumb_visible_class ); ?>">
				<h1 class="entry-title">
					<?php
					if ( is_category() ) {
						echo esc_html( single_cat_title( '', false ) );
					} else {
						the_archive_title();
					}
					?>
				</h1>
			</div>

			<?php if ( $show_breadcrumb ) : ?>
				<?php boilerplate_theme_breadcrumb(); ?>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content', 'excerpt' ); ?>
				<?php endwhile; ?>

				<?php boilerplate_theme_the_posts_navigation(); ?>

			<?php else : ?>

				<?php get_template_part( 'template-parts/content/content', 'none' ); ?>

			<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
