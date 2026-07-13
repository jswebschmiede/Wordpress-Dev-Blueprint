<?php

/**
 * Custom template tags for this theme
 *
 * @package BoilerplateTheme
 */

use SmartMedia24\BoilerplateTheme\Theme\Breadcrumb;
use SmartMedia24\BoilerplateTheme\Theme\ThemeOptions;

if ( ! function_exists( 'boilerplate_theme_breadcrumb' ) ) :
	/**
	 * Renders the breadcrumb navigation for the current page.
	 *
	 * @param bool $show_on_home Whether to render breadcrumbs on the front page. Default false.
	 * @param bool $show_current Whether to show the current page title as the last crumb. Default true.
	 * @return void
	 */
	function boilerplate_theme_breadcrumb( bool $show_on_home = false, bool $show_current = true ): void {
		$breadcrumb = new Breadcrumb( $show_on_home, $show_current );
		$breadcrumb->render();
	}
endif;

if ( ! function_exists( 'boilerplate_theme_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 *
	 * @return void
	 */
	function boilerplate_theme_posted_on(): void {
		$time_string = '<time class="published" datetime="%1$s">%2$s</time>';

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
		);

		printf(
			'<span class="badge"><span class="mr-1.5">%1$s: </span>%2$s</span>',
			esc_html__( 'Veröffentlicht am', 'boilerplate-theme' ),
			$time_string // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
endif;

if ( ! function_exists( 'boilerplate_theme_posted_by' ) ) :
	/**
	 * Prints HTML with meta information about theme author.
	 *
	 * @return void
	 */
	function boilerplate_theme_posted_by(): void {
		printf(
			/* translators: 1: posted by label, only visible to screen readers. 2: post author. */
			'<span class="">%1$s: <span class="author vcard">%2$s</span></span>',
			esc_html__( 'Erstellt von', 'boilerplate-theme' ),
			esc_html( get_the_author() )
		);
	}
endif;

if ( ! function_exists( 'boilerplate_theme_entry_tags' ) ) :
	/**
	 * Prints plain term names (no archive links) for the current post.
	 *
	 * @return void
	 */
	function boilerplate_theme_entry_tags(): void {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$terms = get_the_terms( $post_id, 'post_tag' );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		printf(
			'<span class="tags inline-flex text-lg md:text-xl">%s</span>',
			esc_html( implode( ' / ', wp_list_pluck( $terms, 'name' ) ) )
		);
	}
endif;

if ( ! function_exists( 'boilerplate_theme_entry_meta' ) ) :
	/**
	 * Prints HTML with meta information for the current post (e.g. date).
	 *
	 * @return void
	 */
	function boilerplate_theme_entry_meta(): void {
		if ( 'post' === get_post_type() || 'example_item' === get_post_type() ) :
			?>
			<div class="entry-meta mb-8">
				<?php boilerplate_theme_posted_on(); ?>
			</div><!-- .entry-meta -->
			<?php
		endif;
	}
endif;

if ( ! function_exists( 'boilerplate_theme_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * @return void
	 */
	function boilerplate_theme_post_thumbnail(): void {
		if ( ! boilerplate_theme_can_show_post_thumbnail() ) {
			return;
		}
		?>
		<figure>
			<?php the_post_thumbnail(); ?>
		</figure><!-- .post-thumbnail -->
		<?php
	}
endif;

if ( ! function_exists( 'boilerplate_theme_the_posts_navigation' ) ) :
	/**
	 * Wraps `the_posts_pagination` for use throughout the theme.
	 *
	 * @return void
	 */
	function boilerplate_theme_the_posts_navigation(): void {
		the_posts_pagination(
			array(
				'mid_size'  => 2,
				'prev_text' => __( 'Neuere Beiträge', 'boilerplate-theme' ),
				'next_text' => __( 'Ältere Beiträge', 'boilerplate-theme' ),
			)
		);
	}
endif;

if ( ! function_exists( 'boilerplate_theme_content_class' ) ) :
	/**
	 * Displays the class names for the post content wrapper.
	 *
	 * @param string|string[] $classes Space-separated string or array of class names.
	 * @return void
	 */
	function boilerplate_theme_content_class( $classes = '' ): void {
		$all_classes = array( $classes, BOILERPLATE_THEME_TYPOGRAPHY_CLASSES );

		foreach ( $all_classes as &$class_groups ) {
			if ( ! empty( $class_groups ) ) {
				if ( ! is_array( $class_groups ) ) {
					$class_groups = preg_split( '#\s+#', $class_groups );
				}
			} else {
				$class_groups = array();
			}
		}

		$combined_classes = array_merge( $all_classes[0], $all_classes[1] );
		$combined_classes = array_map( 'esc_attr', $combined_classes );

		echo 'class="' . esc_attr( implode( ' ', $combined_classes ) ) . '"';
	}
endif;

if ( ! function_exists( 'boilerplate_theme_render_site_branding' ) ) :
	/**
	 * Renders the site branding (logo or title) with improved accessibility.
	 *
	 * @return void
	 */
	function boilerplate_theme_render_site_branding(): void {
		$logo          = ThemeOptions::get_option( 'logo' );
		$website_title = ThemeOptions::get_option( 'website_title', get_bloginfo( 'name' ) );
		$site_name     = get_bloginfo( 'name' );
		$home_url      = esc_url( home_url( '/' ) );
		$is_front_page = is_front_page();

		$strip_links = (bool) apply_filters( 'boilerplate_theme_strip_header_footer_links', false );

		$sr_home_text = __( 'Zur Startseite', 'boilerplate-theme' );
		$sr_logo_text = sprintf(
			/* translators: %s: Site name */
			__( '%s Logo', 'boilerplate-theme' ),
			$site_name
		);
		?>

		<div class="site-branding -ml-8 xs:ml-0" role="banner" aria-label="<?php esc_attr_e( 'Website Slogan', 'boilerplate-theme' ); ?>">
			<?php if ( ! empty( $logo['url'] ) ) : ?>
				<?php
				$logo_img = sprintf(
					'<img src="%s" alt="%s" width="%s" height="%s" class="w-auto max-h-full site-logo" role="img">',
					esc_url( $logo['url'] ),
					esc_attr( $sr_logo_text ),
					esc_attr( $logo['width'] ),
					esc_attr( $logo['height'] ),
				);
				?>

				<?php if ( $strip_links || $is_front_page ) : ?>
					<div class="f-header__logo" aria-current="page">
						<?php echo $logo_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="sr-only"><?php esc_html_e( 'Aktuelle Seite: Startseite', 'boilerplate-theme' ); ?></span>
					</div>
				<?php else : ?>
					<a href="<?php echo $home_url; ?>" rel="home" class="f-header__logo"
						aria-label="<?php echo esc_attr( $sr_home_text ); ?>" title="<?php echo esc_attr( $sr_home_text ); ?>">
						<?php echo $logo_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			<?php else : ?>
				<?php if ( $strip_links ) : ?>
					<p class="site-title">
						<span class="site-title-text"><?php echo esc_html( $website_title ); ?></span>
					</p>
				<?php elseif ( $is_front_page ) : ?>
					<h1 class="site-title" aria-current="page">
						<span class="site-title-text"><?php echo esc_html( $website_title ); ?></span>
						<span class="sr-only"><?php esc_html_e( ' - Aktuelle Seite: Startseite', 'boilerplate-theme' ); ?></span>
					</h1>
				<?php else : ?>
					<p class="site-title">
						<a href="<?php echo $home_url; ?>" rel="home" class="site-title-link"
							aria-label="<?php echo esc_attr( $sr_home_text ); ?>" title="<?php echo esc_attr( $sr_home_text ); ?>">
							<span class="site-title-text"><?php echo esc_html( $website_title ); ?></span>
						</a>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php
	}
endif;

if ( ! function_exists( 'boilerplate_theme_skip_link' ) ) :
	/**
	 * Displays a skip link for the content.
	 *
	 * @return void
	 */
	function boilerplate_theme_skip_link(): void {
		?>
		<a href="#content"
			class="sr-only focus:not-sr-only focus:top-0 focus:left-0 focus:z-50 focus:absolute focus:bg-white focus:shadow-lg focus:p-4 focus:rounded-b-lg focus:text-black"><?php esc_html_e( 'Inhalte überspringen', 'boilerplate-theme' ); ?></a>
		<?php
	}
endif;

if ( ! function_exists( 'boilerplate_theme_get_website_logo' ) ) :
	/**
	 * Get the website logo.
	 *
	 * @return string
	 */
	function boilerplate_theme_get_website_logo(): string {
		$logo = ThemeOptions::get_option( 'logo' );

		$sr_logo_text = sprintf(
			/* translators: %s: Site name */
			__( '%s Logo', 'boilerplate-theme' ),
			get_bloginfo( 'name' )
		);

		return sprintf(
			'<img src="%s" alt="%s" width="%s" height="%s" class="w-auto max-h-full site-logo" role="img">',
			esc_url( $logo['url'] ),
			esc_attr( $sr_logo_text ),
			esc_attr( $logo['width'] ),
			esc_attr( $logo['height'] ),
		);
	}
endif;
