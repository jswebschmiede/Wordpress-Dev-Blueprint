<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package BoilerplateTheme
 */

use CompanyName\BoilerplateTheme\Theme\ThemeOptions;

get_header();

$error_title = ThemeOptions::get_option(
	'error_title',
	esc_html__( 'Seite nicht gefunden', 'boilerplate-theme' ),
);

$error_text = ThemeOptions::get_option(
	'error_text',
	esc_html__( 'Diese Seite konnte nicht gefunden werden. Sie wurde möglicherweise entfernt oder umbenannt, oder sie hat möglicherweise nie existiert.', 'boilerplate-theme' ),
);

$error_btn = ThemeOptions::get_option(
	'error_btn',
	esc_html__( 'Zur Startseite', 'boilerplate-theme' ),
);
?>

<section id="primary">
	<main id="main">

		<section class="max-w-content w-p-1 lg:w-p-2 mx-auto text-center">
			<div <?php boilerplate_theme_content_class( 'page-content pt-10 md:pt-16' ); ?>>
				<header class="page-header">
					<h1 class="page-title">
						<?php echo esc_html( $error_title ); ?>
					</h1>
				</header>

				<div>
					<p><?php echo esc_html( $error_text ); ?></p>

					<p>
						<a href="<?php echo esc_url( home_url() ); ?>"
							class="btn btn-primary"><?php echo esc_html( $error_btn ); ?>
						</a>
					</p>
				</div>
			</div>
		</section>
	</main><!-- #main -->
</section><!-- #primary -->

<?php
get_footer();
