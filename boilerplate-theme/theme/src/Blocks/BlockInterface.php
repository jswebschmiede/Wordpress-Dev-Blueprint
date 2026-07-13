<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks;

\defined( 'ABSPATH' ) || exit;

/**
 * Defines the contract for custom Gutenberg blocks.
 */
interface BlockInterface {
	/**
	 * Renders the block on the frontend.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner block content.
	 * @param \WP_Block|null       $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render( array $attributes, string $content, ?\WP_Block $block = null ): string;
}
