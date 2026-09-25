<?php
/**
 * The template for displaying archive pages
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

$context   = Timber::context();
$templates = array( 'templates/archive.twig', 'templates/index.twig' );
$queried   = get_queried_object();

if ( $queried instanceof \WP_Post_Type ) {
	array_unshift( $templates, 'templates/archive-' . $queried->name . '.twig' );
}

Timber::render( $templates, $context );
