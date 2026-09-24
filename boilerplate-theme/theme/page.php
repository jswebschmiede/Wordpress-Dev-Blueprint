<?php
/**
 * The template for displaying all pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package BoilerplateTheme
 */

declare( strict_types=1 );

use CompanyName\BoilerplateTheme\Timber\Timber;

$context = Timber::context();

Timber::render(
	array(
		'templates/page-' . $context['post']->slug . '.twig',
		'templates/page.twig',
	),
	$context
);
