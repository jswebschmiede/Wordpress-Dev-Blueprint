<?php
/**
 * Plugin Name: Boilerplate Plugin
 * Description: Demo WordPress plugin using CMB2 for options (text fields) with shortcode output.
 * Version: 1.0.0
 * Author: companyname
 * Author URI: https://companyname.example
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: boilerplate-plugin
 * Domain Path: /languages
 */

declare( strict_types=1 );

\defined( 'ABSPATH' ) || exit;

define( 'BOILERPLATE_PLUGIN_FILE', __FILE__ );
define( 'BOILERPLATE_PLUGIN_VERSION', '1.0.0' );

use CompanyName\BoilerplatePlugin\BoilerplatePlugin;

$boilerplate_plugin_autoload = plugin_dir_path( __FILE__ ) . 'vendor-prefixed/autoload.php';

if ( ! file_exists( $boilerplate_plugin_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p><strong>Boilerplate Plugin:</strong> ';
			echo esc_html__( 'Run composer install in the plugin directory to generate vendor-prefixed dependencies.', 'boilerplate-plugin' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $boilerplate_plugin_autoload;

register_activation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		BoilerplatePlugin::get_instance()->run();
	}
);
