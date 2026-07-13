<?php
/**
 * Template part for displaying post archives and search results
 *
 * @package BoilerplateTheme
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div <?php boilerplate_theme_content_class( 'entry-content' ); ?>>
		<header>
			<?php
			if ( is_sticky() && is_home() && ! is_paged() ) {
				printf( '%s', esc_html_x( 'Hervorgehoben', 'post', 'boilerplate-theme' ) );
			}
			the_title( sprintf( '<h2><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			?>
		</header>
		<?php boilerplate_theme_post_thumbnail(); ?>
		<?php the_excerpt(); ?>
	</div>

	<footer class="entry-footer">
		<?php boilerplate_theme_entry_meta(); ?>
	</footer>

</article>
