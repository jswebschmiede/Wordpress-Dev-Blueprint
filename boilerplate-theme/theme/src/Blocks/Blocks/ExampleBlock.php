<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks\Blocks;

use CompanyName\BoilerplateTheme\Blocks\BlockInterface;
use CompanyName\BoilerplateTheme\Theme\TimberIntegration;
use CompanyName\BoilerplateTheme\Timber\Timber;

\defined( 'ABSPATH' ) || exit;

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

		if ( ! TimberIntegration::is_available() ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return '';
			}

			return '<p><strong>Boilerplate Theme:</strong> '
				. esc_html( TimberIntegration::get_missing_dependency_message() )
				. '</p>';
		}

		$attributes = wp_parse_args(
			$attributes,
			array(
				'title'       => __( 'Example Block', 'boilerplate-theme' ),
				'description' => __( 'Use this block as a starting point for new dynamic blocks.', 'boilerplate-theme' ),
				'url'         => '',
				'className'   => '',
			)
		);

		$wrapper_classes = array( 'example-block not-prose' );
		$class_name      = $this->get_string_attribute( $attributes, 'className' );

		if ( '' !== $class_name ) {
			$wrapper_classes[] = $class_name;
		}

		$context = array(
			'attributes'         => $attributes,
			'title'              => $this->get_string_attribute( $attributes, 'title' ),
			'description'        => $this->get_string_attribute( $attributes, 'description' ),
			'url'                => trim( $this->get_string_attribute( $attributes, 'url' ) ),
			'wrapper_attributes' => get_block_wrapper_attributes(
				array(
					'class' => implode( ' ', $wrapper_classes ),
				)
			),
		);

		/**
		 * Filters the Twig template(s) used to render the example block (relative to `views/`).
		 *
		 * @param string|string[]      $template   Twig template name or fallback list.
		 * @param array<string, mixed> $attributes Parsed block attributes.
		 */
		$template = apply_filters( 'boilerplate_theme_example_block_template', 'blocks/example-block.twig', $attributes );

		if ( ! is_string( $template ) && ! is_array( $template ) ) {
			return '';
		}

		return (string) Timber::compile( $template, $context );
	}

	/**
	 * Returns a scalar block attribute as string.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $key        Attribute key.
	 * @return string Attribute value, or an empty string for missing/non-scalar values.
	 */
	private function get_string_attribute( array $attributes, string $key ): string {
		return isset( $attributes[ $key ] ) && is_scalar( $attributes[ $key ] ) ? (string) $attributes[ $key ] : '';
	}
}
