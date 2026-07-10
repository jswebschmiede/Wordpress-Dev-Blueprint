<?php
/**
 * Uninstall cleanup for Boilerplate Plugin.
 *
 * @package BoilerplatePlugin
 */

declare( strict_types=1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'boilerplate_plugin_options' );
