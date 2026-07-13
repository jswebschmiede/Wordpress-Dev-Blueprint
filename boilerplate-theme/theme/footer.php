<?php
/**
 * The template for displaying the footer
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package BoilerplateTheme
 */

use CompanyName\BoilerplateTheme\Theme\ThemeOptions;

?>

	</div><!-- #content -->

	<?php get_template_part( 'template-parts/layout/footer', 'content' ); ?>

</div><!-- #page -->

<?php if ( ThemeOptions::get_option( 'show_preloader' ) ) : ?>
	<?php get_template_part( 'template-parts/layout/preloader' ); ?>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>
