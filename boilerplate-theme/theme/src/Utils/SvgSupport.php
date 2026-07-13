<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Handles SVG file support in WordPress media library.
 */
class SvgSupport {
	/**
	 * Initialize SVG support hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'upload_mimes', array( $this, 'add_svg_mime_type' ) );
	}

	/**
	 * Add SVG support to WordPress media library.
	 *
	 * @param array<string, string> $mimes Array of allowed MIME types.
	 * @return array<string, string> Modified array with SVG support.
	 */
	public function add_svg_mime_type( array $mimes ): array {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}
}
