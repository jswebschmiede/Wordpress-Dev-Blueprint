<?php
/**
 * Template part for displaying a message when posts are not found
 *
 * @package BoilerplateTheme
 */

?>

<section>

	<header class="page-header">
		<?php if ( is_search() ) : ?>
			<h1 class="page-title">
				<?php
				printf(
					/* translators: %s: search term */
					esc_html__( 'Suchergebnisse für: %s', 'boilerplate-theme' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		<?php else : ?>
			<h1 class="page-title"><?php esc_html_e( 'Nichts gefunden', 'boilerplate-theme' ); ?></h1>
		<?php endif; ?>
	</header>

	<div <?php boilerplate_theme_content_class( 'page-content' ); ?>>
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) :
			?>
			<p>
				<?php esc_html_e( 'Ihre Website ist so eingestellt, dass die neuesten Beiträge auf der Startseite angezeigt werden, aber es wurden noch keine Beiträge veröffentlicht.', 'boilerplate-theme' ); ?>
			</p>
			<?php
		elseif ( is_search() ) :
			?>
			<p>
				<?php esc_html_e( 'Ihre Suche ergab keine Ergebnisse. Bitte versuchen Sie es mit einem anderen Suchbegriff.', 'boilerplate-theme' ); ?>
			</p>
			<?php
			get_search_form();
		else :
			?>
			<p>
				<?php esc_html_e( 'Es wurden keine passenden Inhalte gefunden.', 'boilerplate-theme' ); ?>
			</p>
			<?php
			get_search_form();
		endif;
		?>
	</div>

</section>
