<?php
/**
 * Template part for displaying a single custom search result.
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

$search_term      = isset( $args['search_term'] ) ? (string) $args['search_term'] : '';
$post_type        = get_post_type();
$post_type_object = is_string( $post_type ) ? get_post_type_object( $post_type ) : null;
$post_type_label  = $post_type_object ? $post_type_object->labels->singular_name : __( 'Inhalt', 'boilerplate-theme' );
$excerpt          = wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 40 );

if ( '' === $excerpt ) {
	$excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 40 );
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'search-result-item' ); ?>>
	<a class="search-result-item__link flex flex-col card no-underline! hover:shadow-lg text-foreground! font-normal! transition-shadow duration-300"
		title="<?php esc_attr_e( 'Zum Inhalt wechseln', 'boilerplate-theme' ); ?>"
		href="<?php the_permalink(); ?>">
		<div class="search-result-item__header">
			<p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">
				<?php echo esc_html( $post_type_label ); ?>
			</p>
			<h3 class="search-result-item__title mb-0!">
				<?php echo wp_kses_post( boilerplate_theme_highlight_search_term( get_the_title(), $search_term ) ); ?>
			</h3>
		</div>

		<div class="search-result-item__excerpt">
			<p class="mb-0! text-foreground!">
				<?php echo wp_kses_post( boilerplate_theme_highlight_search_term( $excerpt, $search_term ) ); ?>
			</p>
		</div>
	</a>
</article>
