<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks\Blocks;

use CompanyName\BoilerplateTheme\Blocks\BlockInterface;
use CompanyName\BoilerplateTheme\Theme\TimberIntegration;
use CompanyName\BoilerplateTheme\Timber\Timber;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles rendering for a single column inside the columns block.
 */
class ColumnBlock implements BlockInterface {
	/**
	 * Renders the block on the frontend.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Rendered inner blocks.
	 * @param \WP_Block|null       $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render( array $attributes, string $content, ?\WP_Block $block = null ): string {
		unset( $block );

		// The parent ColumnsBlock shows the missing dependency notice.
		if ( ! TimberIntegration::is_available() ) {
			return '';
		}

		$wrapper_classes = array( 'columns__column' );
		$class_name      = isset( $attributes['className'] ) && is_scalar( $attributes['className'] ) ? (string) $attributes['className'] : '';

		if ( '' !== $class_name ) {
			$wrapper_classes[] = $class_name;
		}

		// Empty columns still render, so the grid keeps its column positions.
		$context = array(
			'attributes'         => $attributes,
			'content'            => $content,
			'wrapper_attributes' => get_block_wrapper_attributes(
				array(
					'class' => implode( ' ', $wrapper_classes ),
				)
			),
		);

		/**
		 * Filters the Twig template(s) used to render a column (relative to `views/`).
		 *
		 * @param string|string[]      $template   Twig template name or fallback list.
		 * @param array<string, mixed> $attributes Block attributes.
		 */
		$template = apply_filters( 'boilerplate_theme_column_block_template', 'blocks/column.twig', $attributes );

		if ( ! is_string( $template ) && ! is_array( $template ) ) {
			return '';
		}

		return (string) Timber::compile( $template, $context );
	}
}
