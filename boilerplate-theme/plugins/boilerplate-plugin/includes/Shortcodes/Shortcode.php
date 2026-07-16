<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplatePlugin\Shortcodes;

use CompanyName\BoilerplatePlugin\Assets\Assets;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the example shortcode.
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
	 * Renders shortcode markup.
	 *
	 * @param array<string, mixed> $atts    Shortcode attributes.
	 * @param string|null          $content Shortcode content.
	 * @param string               $tag     Shortcode tag.
	 * @return string Rendered shortcode markup.
	 */
	public function render( array $atts = array(), ?string $content = null, string $tag = '' ): string {
		unset( $content, $tag );

		$atts = shortcode_atts(
			array(
				'title' => __( 'Boilerplate Plugin', 'boilerplate-plugin' ),
			),
			$atts,
			self::TAG
		);

		( new Assets() )->enqueue_frontend();

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
