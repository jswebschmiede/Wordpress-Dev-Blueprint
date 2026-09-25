<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use CompanyName\BoilerplateTheme\Timber\Timber;

if ( boilerplate_theme_bail_if_timber_unavailable() ) {
	return;
}

$context = Timber::context();

Timber::render(
	array(
		'templates/single-' . $context['post']->post_type . '.twig',
		'templates/single.twig',
	),
	$context
);
