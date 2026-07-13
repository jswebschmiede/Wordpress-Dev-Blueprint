<?php
/**
 * Template part for displaying posts
 *
 * @package BoilerplateTheme
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php
		if ( is_sticky() && is_home() && ! is_paged() ) {
			printf( '<span>%s</span>', esc_html_x( 'Hervorgehoben', 'post', 'boilerplate-theme' ) );
		}
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
		endif;
		?>
	</header>

	<?php boilerplate_theme_post_thumbnail(); ?>

	<div <?php boilerplate_theme_content_class( 'entry-content' ); ?>>
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<div>' . __( 'Seiten:', 'boilerplate-theme' ),
				'after'  => '</div>',
			)
		);
		?>
	</div>

</article>
