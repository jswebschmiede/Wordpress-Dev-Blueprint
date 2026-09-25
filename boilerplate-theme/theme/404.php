<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use CompanyName\BoilerplateTheme\Timber\Timber;

if ( boilerplate_theme_bail_if_timber_unavailable() ) {
	return;
}

Timber::render( 'templates/404.twig', Timber::context() );
