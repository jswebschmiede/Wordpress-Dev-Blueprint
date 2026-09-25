<?php
/**
 * The template for displaying search results pages
 *
 * Template Name: Search-Template
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use CompanyName\BoilerplateTheme\Timber\Timber;

if ( boilerplate_theme_bail_if_timber_unavailable() ) {
	return;
}

$context     = Timber::context();
$search_term = boilerplate_theme_get_current_search_term();

$current_page = max(
	1,
	absint( get_query_var( 'paged' ) ),
	absint( get_query_var( 'page' ) ),
	isset( $_GET['paged'] ) ? absint( wp_unslash( (string) $_GET['paged'] ) ) : 0
);

$context['search_term']       = $search_term;
$context['search_results']    = null;
$context['search_pagination'] = null;

if ( '' !== $search_term ) {
	$search_results = Timber::get_posts(
		array(
			's'              => $search_term,
			'paged'          => $current_page,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
		)
	);

	// Timber\Pagination double-encodes placeholders in the base query string, so the page
	// number goes into `format` and all other query args into `add_args`.
	$search_url_parts = explode( '?', (string) $context['search_url'], 2 );
	$add_args         = array();

	if ( isset( $search_url_parts[1] ) ) {
		wp_parse_str( $search_url_parts[1], $add_args );
	}

	$add_args['search_term'] = $search_term;

	$context['search_results']    = $search_results;
	$context['search_pagination'] = $search_results->pagination(
		array(
			'base'         => $search_url_parts[0] . '%_%',
			'format'       => '?paged=%#%',
			'add_args'     => $add_args,
			'add_fragment' => '#search-results',
			'mid_size'     => 2,
		)
	);
}

Timber::render( 'templates/search.twig', $context );
