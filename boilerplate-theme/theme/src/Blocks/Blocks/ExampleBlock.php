<?php

declare( strict_types=1 );

namespace SmartMedia24\BoilerplateTheme\Blocks\Blocks;

use SmartMedia24\BoilerplateTheme\Blocks\BlockInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Handles rendering for the example block.
 */
class ExampleBlock implements BlockInterface {
	/**
	 * Renders the block on the frontend.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner block content.
	 * @param \WP_Block|null       $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render( array $attributes, string $content, ?\WP_Block $block = null ): string {
		unset( $content, $block );

		$attributes = wp_parse_args(
			$attributes,
			array(
				'title'       => __( 'Example Block', 'boilerplate-theme' ),
				'description' => __( 'Use this block as a starting point for new dynamic blocks.', 'boilerplate-theme' ),
				'url'         => '',
				'className'   => '',
			)
		);

		$template_path = get_template_directory() . '/template-parts/blocks/example-block.php';
		$template_path = apply_filters( 'boilerplate_theme_example_block_template', $template_path, $attributes );

		if ( ! is_string( $template_path ) || ! file_exists( $template_path ) ) {
			return '';
		}

		ob_start();
		include $template_path;

		return (string) ob_get_clean();
	}
}
