<?php
/**
 * The main template file
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

Timber::render( 'templates/index.twig', Timber::context() );
