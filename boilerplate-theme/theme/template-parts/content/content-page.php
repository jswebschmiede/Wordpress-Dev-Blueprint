<?php
/**
 * Template part for displaying pages
 *
 * @package BoilerplateTheme
 */

use CompanyName\BoilerplateTheme\Theme\ThemeOptions;

$show_breadcrumb          = ThemeOptions::get_option( 'show_breadcrumb', false );
$breadcrumb_visible_class = ! $show_breadcrumb ? 'mb-12' : '';

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( ! is_front_page() ) : ?>
		<div class="entry-header <?php echo esc_attr( $breadcrumb_visible_class ); ?>">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</div>
	<?php endif; ?>

	<?php if ( $show_breadcrumb ) : ?>
		<?php boilerplate_theme_breadcrumb(); ?>
	<?php endif; ?>

	<div <?php boilerplate_theme_content_class( 'entry-content' ); ?>>
		<?php the_content(); ?>
	</div>

</article>
