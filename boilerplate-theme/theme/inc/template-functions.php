<?php

declare( strict_types=1 );

use SmartMedia24\BoilerplateTheme\Theme\ThemeOptions;

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package BoilerplateTheme
 */

/**
 * Suppresses rendered output for empty core/paragraph blocks on the front end.
 *
 * @param string $block_content Rendered HTML for the current block.
 * @param array  $block         Parsed block data (uses `blockName`).
 * @return string Rendered HTML, or empty string when the paragraph is empty.
 */
function boilerplate_theme_remove_empty_paragraphs( string $block_content, array $block ): string {
	if ( 'core/paragraph' === $block['blockName'] && trim( wp_strip_all_tags( $block_content ) ) === '' ) {
		return '';
	}
	return $block_content;
}
add_filter( 'render_block', 'boilerplate_theme_remove_empty_paragraphs', 10, 2 );

/**
 * Gets the configured search results page ID.
 *
 * @return int Search results page ID.
 */
function boilerplate_theme_get_search_page_id(): int {
	$configured_page_id = absint( (string) ThemeOptions::get_option( 'search_page', 0 ) );

	if ( $configured_page_id > 0 && 'publish' === get_post_status( $configured_page_id ) ) {
		return $configured_page_id;
	}

	if ( is_page_template( 'templates/template-search.php' ) ) {
		return get_queried_object_id();
	}

	$search_pages = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'templates/template-search.php',
			'orderby'                => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $search_pages ) ) {
		return 0;
	}

	return (int) $search_pages[0];
}

/**
 * Gets the URL used for all theme search forms.
 *
 * @return string Search results page URL.
 */
function boilerplate_theme_get_search_page_url(): string {
	$search_page_id = boilerplate_theme_get_search_page_id();

	if ( $search_page_id > 0 ) {
		$search_page_url = get_permalink( $search_page_id );

		if ( is_string( $search_page_url ) && '' !== $search_page_url ) {
			return $search_page_url;
		}
	}

	return home_url( '/' );
}

/**
 * Registers public query vars used by theme templates.
 *
 * @param array<int, string> $query_vars Existing public query vars.
 * @return array<int, string> Updated query vars.
 */
function boilerplate_theme_register_query_vars( array $query_vars ): array {
	$query_vars[] = 'search_term';

	return $query_vars;
}
add_filter( 'query_vars', 'boilerplate_theme_register_query_vars' );

/**
 * Gets the current custom search term from the request.
 *
 * @return string Sanitized search term.
 */
function boilerplate_theme_get_current_search_term(): string {
	$search_term = get_query_var( 'search_term' );
	if ( is_string( $search_term ) && '' !== $search_term ) {
		return sanitize_text_field( $search_term );
	}

	$search_query = get_search_query( false );
	if ( is_string( $search_query ) && '' !== $search_query ) {
		return sanitize_text_field( $search_query );
	}

	return '';
}

/**
 * Highlights matching search terms in plain text.
 *
 * @param string $text        Plain text content.
 * @param string $search_term Search term from the request.
 * @return string Safe highlighted HTML string.
 */
function boilerplate_theme_highlight_search_term( string $text, string $search_term ): string {
	$keywords = preg_split( '/\s+/u', trim( $search_term ) );
	$keywords = array_filter(
		array_map( 'trim', is_array( $keywords ) ? $keywords : array() ),
		static fn( string $keyword ): bool => '' !== $keyword
	);

	if ( empty( $keywords ) ) {
		return esc_html( $text );
	}

	usort(
		$keywords,
		static fn( string $left, string $right ): int => strlen( $right ) <=> strlen( $left )
	);

	$pattern = '/(' . implode( '|', array_map( static fn( string $keyword ): string => preg_quote( $keyword, '/' ), $keywords ) ) . ')/iu';
	$parts   = preg_split( $pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( false === $parts ) {
		return esc_html( $text );
	}

	$highlighted = '';

	foreach ( $parts as $part ) {
		if ( '' === $part ) {
			continue;
		}

		if ( 1 === preg_match( $pattern, $part ) ) {
			$highlighted .= '<mark class="bg-primary/15 px-1 text-secondary rounded-sm">' . esc_html( $part ) . '</mark>';
			continue;
		}

		$highlighted .= esc_html( $part );
	}

	return $highlighted;
}

/**
 * Filters the default search form output.
 *
 * @param string               $form Existing search form markup.
 * @param array<string, mixed> $args Search form arguments.
 * @return string Custom search form markup.
 */
function boilerplate_theme_filter_search_form( string $form, array $args = array() ): string {
	$action_url   = boilerplate_theme_get_search_page_url();
	$input_id     = wp_unique_id( 'search-form-' );
	$search_term  = boilerplate_theme_get_current_search_term();
	$label        = $args['label'] ?? __( 'Suche nach', 'boilerplate-theme' );
	$placeholder  = $args['placeholder'] ?? __( 'Suchbegriff...', 'boilerplate-theme' );
	$button_label = $args['button_label'] ?? __( 'Webseite durchsuchen', 'boilerplate-theme' );

	ob_start();
	?>
	<form role="search" method="get" class="boilerplate-theme-form search-form-custom flex gap-3 rounded-form bg-white p-2 shadow-sm flex-row items-center" action="<?php echo esc_url( $action_url . '#search-results' ); ?>">
		<label class="sr-only" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
		<input
			id="<?php echo esc_attr( $input_id ); ?>"
			type="search"
			class="search-field min-w-0 min-h-0! sm:min-h-13! flex-1 rounded-form border border-slate-200 bg-slate-50 px-4 py-1! sm:py-3! text-base text-foreground outline-none transition-all focus:border-primary focus:ring-1 focus:ring-primary"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			value="<?php echo esc_attr( $search_term ); ?>"
			name="search_term"
		/>
		<button type="submit" class="search-submit btn btn-primary font-normal rounded-full aspect-square px-3 js-tooltip-trigger" aria-label="<?php echo esc_attr( $button_label ); ?>" title="<?php echo esc_attr( $button_label ); ?>" data-tooltip-position="left" >
			<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
			<span class="sr-only"><?php echo esc_html( $button_label ); ?></span>
		</button>
	</form>
	<?php

	return (string) ob_get_clean();
}
add_filter( 'get_search_form', 'boilerplate_theme_filter_search_form', 10, 2 );

/**
 * Filters the default archive titles.
 *
 * @return string Archive title HTML.
 */
function boilerplate_theme_get_the_archive_title(): string {
	if ( is_category() ) {
		$title = __( 'Kategorie-Archive: ', 'boilerplate-theme' ) . '<span>' . single_term_title( '', false ) . '</span>';
	} elseif ( is_tag() ) {
		$title = __( 'Schlagwort-Archive: ', 'boilerplate-theme' ) . '<span>' . single_term_title( '', false ) . '</span>';
	} elseif ( is_author() ) {
		$title = __( 'Autor-Archive: ', 'boilerplate-theme' ) . '<span>' . get_the_author_meta( 'display_name' ) . '</span>';
	} elseif ( is_year() ) {
		$title = __( 'Jahres-Archive: ', 'boilerplate-theme' ) . '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'boilerplate-theme' ) ) . '</span>';
	} elseif ( is_month() ) {
		$title = __( 'Monats-Archive: ', 'boilerplate-theme' ) . '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'boilerplate-theme' ) ) . '</span>';
	} elseif ( is_day() ) {
		$title = __( 'Tages-Archive: ', 'boilerplate-theme' ) . '<span>' . get_the_date() . '</span>';
	} elseif ( is_post_type_archive() ) {
		$cpt   = get_post_type_object( get_queried_object()->name );
		$title = sprintf(
			/* translators: %s: Post type singular name */
			esc_html__( '%s-Archive', 'boilerplate-theme' ),
			$cpt->labels->singular_name
		);
	} elseif ( is_tax() ) {
		$tax   = get_taxonomy( get_queried_object()->taxonomy );
		$title = sprintf(
			/* translators: %s: Taxonomy singular name */
			esc_html__( '%s-Archive', 'boilerplate-theme' ),
			$tax->labels->singular_name
		);
	} else {
		$title = __( 'Archive:', 'boilerplate-theme' );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'boilerplate_theme_get_the_archive_title' );

/**
 * Determines whether the post thumbnail can be displayed.
 *
 * @return bool
 */
function boilerplate_theme_can_show_post_thumbnail(): bool {
	return apply_filters( 'boilerplate_theme_can_show_post_thumbnail', ! post_password_required() && ! is_attachment() && has_post_thumbnail() );
}

/**
 * Create the continue reading link.
 *
 * @param string $more_string The string shown within the more link.
 * @return string
 */
function boilerplate_theme_continue_reading_link( $more_string ) {
	if ( ! is_admin() ) {
		$continue_reading = sprintf(
			/* translators: %s: Name of current post. */
			wp_kses( __( 'Weiterlesen: %s', 'boilerplate-theme' ), array( 'span' => array( 'class' => array() ) ) ),
			the_title( '<span class="sr-only">"', '"</span>', false )
		);

		$more_string = '<a href="' . esc_url( get_permalink() ) . '">' . $continue_reading . '</a>';
	}

	return $more_string;
}
add_filter( 'excerpt_more', 'boilerplate_theme_continue_reading_link' );
add_filter( 'the_content_more_link', 'boilerplate_theme_continue_reading_link' );

/**
 * Format a phone number.
 *
 * @param string $number The number to format, will be cleaned and converted to international format.
 * @return string The formatted number.
 */
function boilerplate_theme_format_number( string $number ): string {
	if ( empty( $number ) ) {
		return '';
	}

	$cleaned = preg_replace( '/[^0-9+]/', '', esc_attr( $number ) );

	if ( str_starts_with( $cleaned, '0' ) && ! str_starts_with( $cleaned, '00' ) ) {
		$cleaned = '+49' . substr( $cleaned, 1 );
	}

	return $cleaned;
}

/**
 * Disables automatic resizing of the TinyMCE editor and sets a fixed height.
 *
 * @param array<string, mixed> $init TinyMCE init array.
 * @return array<string, mixed>
 */
function boilerplate_theme_disable_tiny_mce_autoresize( $init ) {
	$init['wp_autoresize_on'] = false;
	$init['height']           = 400;
	return $init;
}
add_filter( 'tiny_mce_before_init', 'boilerplate_theme_disable_tiny_mce_autoresize' );

/**
 * Add the Tailwind Typography classes to TinyMCE.
 *
 * @param array<string, mixed> $settings TinyMCE settings.
 * @return array<string, mixed>
 */
function boilerplate_theme_tinymce_add_class( array $settings ): array {
	$settings['body_class'] = BOILERPLATE_THEME_TYPOGRAPHY_CLASSES;
	return $settings;
}
add_filter( 'tiny_mce_before_init', 'boilerplate_theme_tinymce_add_class' );

/**
 * Limit the block editor to heading levels supported by Tailwind Typography.
 *
 * @param array<string, mixed> $args       Array of arguments for registering a block type.
 * @param string               $block_type Block type name including namespace.
 * @return array<string, mixed>
 */
function boilerplate_theme_modify_heading_levels( array $args, string $block_type ): array {
	if ( 'core/heading' !== $block_type ) {
		return $args;
	}

	$args['attributes']['levelOptions']['default'] = array( 1, 2, 3, 4 );

	return $args;
}
add_filter( 'register_block_type_args', 'boilerplate_theme_modify_heading_levels', 10, 2 );

/*
 * -------------------------------------------------------------------------
 * Project-specific examples (uncomment and adapt when needed)
 * -------------------------------------------------------------------------
 *
 * Breadcrumb CPT-to-page mapping for custom post types.
 * @see also: theme/src/Theme/Breadcrumb.php::get_cpt_page_map()
 *
 * add_filter(
 *     'boilerplate_theme_breadcrumb_cpt_page_map',
 *     function ( array $map ): array {
 *         $map['example_item'] = array(
 *             'parent_path'        => 'about',
 *             'parent_label'       => 'Über uns',
 *             'intermediate_path'  => 'about/news',
 *             'intermediate_label' => 'News',
 *             'list_path'          => 'about/news/examples',
 *         );
 *
 *         return $map;
 *     }
 * );
 */
