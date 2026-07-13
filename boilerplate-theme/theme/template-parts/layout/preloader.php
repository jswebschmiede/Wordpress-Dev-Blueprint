<?php
/**
 * Template part for displaying the preloader.
 *
 * @package BoilerplateTheme
 */

use CompanyName\BoilerplateTheme\Theme\ThemeOptions;

$preloader_style = ThemeOptions::get_option( 'preloader_style', 'v1' );
$preloader_label = esc_html__( 'Seite wird geladen...', 'boilerplate-theme' );
$valid_styles    = array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v6' );
set_query_var( 'preloader_label', $preloader_label );

$style = in_array( $preloader_style, $valid_styles, true ) ? $preloader_style : 'v1';

get_template_part( 'template-parts/layout/preloader/loader-' . $style );
