<?php
/**
 * Template part for displaying single posts
 *
 * @package BoilerplateTheme
 */

use CompanyName\BoilerplateTheme\Theme\ThemeOptions;

$show_breadcrumb          = ThemeOptions::get_option( 'show_breadcrumb', false );
$breadcrumb_visible_class = ! $show_breadcrumb ? 'mb-12' : '';

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-header <?php echo esc_attr( $breadcrumb_visible_class ); ?>">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</div>

	<?php if ( $show_breadcrumb ) : ?>
		<?php boilerplate_theme_breadcrumb(); ?>
	<?php endif; ?>

	<div <?php boilerplate_theme_content_class( 'entry-content' ); ?>>
		<?php boilerplate_theme_entry_meta(); ?>

		<?php
		the_content(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers. */
					__( 'Weiterlesen<span class="sr-only"> "%s"</span>', 'boilerplate-theme' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			)
		);
		?>
	</div>

</article>
