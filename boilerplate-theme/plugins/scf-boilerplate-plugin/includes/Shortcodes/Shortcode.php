<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Shortcodes;

use CompanyName\BoilerplatePlugin\Assets\Assets;
use CompanyName\BoilerplatePlugin\Backend\PluginOptions;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the demo shortcode that outputs SCF options.
 */
class Shortcode {
	public const string TAG = 'boilerplate_plugin';

	/**
	 * Initializes shortcode hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Renders shortcode markup from SCF options.
	 *
	 * @param array<string, mixed> $atts    Shortcode attributes.
	 * @param string|null          $content Shortcode content.
	 * @param string               $tag     Shortcode tag.
	 * @return string Rendered shortcode markup.
	 */
	public function render( array $atts = array(), ?string $content = null, string $tag = '' ): string {
		unset( $atts, $content, $tag );

		( new Assets() )->enqueue_frontend();

		$headline = PluginOptions::get_option(
			'demo_headline',
			__( 'Boilerplate Plugin Demo', 'boilerplate-plugin' )
		);
		$intro    = PluginOptions::get_option(
			'demo_intro',
			__(
				'This content comes from Secure Custom Fields plugin options. Edit it under Boilerplate Plugin in the admin menu.',
				'boilerplate-plugin'
			)
		);

		$headline = is_scalar( $headline ) ? (string) $headline : '';
		$intro    = is_scalar( $intro ) ? (string) $intro : '';

		ob_start();
		require $this->get_template_path();

		return (string) ob_get_clean();
	}

	/**
	 * Gets the shortcode view path.
	 *
	 * @return string Absolute template path.
	 */
	private function get_template_path(): string {
		if ( \defined( 'BOILERPLATE_PLUGIN_FILE' ) ) {
			return plugin_dir_path( BOILERPLATE_PLUGIN_FILE ) . 'views/shortcode-view.php';
		}

		return dirname( __DIR__, 2 ) . '/views/shortcode-view.php';
	}
}
