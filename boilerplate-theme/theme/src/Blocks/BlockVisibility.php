<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks;

use WP_HTML_Tag_Processor;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers viewport-visibility attributes and prints hide classes on rendered blocks.
 */
class BlockVisibility {
	/**
	 * Blocks that must not receive visibility attributes.
	 *
	 * @var array<int, string>
	 */
	private const EXCLUDED_BLOCKS = array(
		'core/freeform',
		'core/html',
		'core/shortcode',
		'core/block',
	);

	/**
	 * Boolean attributes and the utility class each one prints.
	 *
	 * Class names match the tokens in tailwind-theme.css and settings.viewport in theme.json.
	 *
	 * @var array<string, string>
	 */
	private const HIDE_CLASSES = array(
		'eiHideMobile'  => 'ei-hide-mobile',
		'eiHideTablet'  => 'ei-hide-tablet',
		'eiHideDesktop' => 'ei-hide-desktop',
	);

	/**
	 * Registers block visibility hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'register_block_type_args', array( $this, 'register_visibility_attributes' ), 10, 2 );
		add_filter( 'render_block', array( $this, 'add_visibility_classes' ), 10, 2 );
	}

	/**
	 * Register visibility attributes on all eligible blocks server-side.
	 *
	 * @param array<string, mixed> $args       Block type arguments.
	 * @param string               $block_type Block type name.
	 * @return array<string, mixed> Modified block type arguments.
	 */
	public function register_visibility_attributes( array $args, string $block_type ): array {
		if ( in_array( $block_type, self::EXCLUDED_BLOCKS, true ) ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		foreach ( array_keys( self::HIDE_CLASSES ) as $attr_name ) {
			if ( ! isset( $args['attributes'][ $attr_name ] ) ) {
				$args['attributes'][ $attr_name ] = array(
					'type'    => 'boolean',
					'default' => false,
				);
			}
		}

		return $args;
	}

	/**
	 * Add hide classes to the first rendered tag.
	 *
	 * @param string               $block_content The block content.
	 * @param array<string, mixed> $block         The full block, including name and attributes.
	 * @return string Updated block HTML.
	 */
	public function add_visibility_classes( string $block_content, array $block ): string {
		if ( '' === $block_content || is_admin() ) {
			return $block_content;
		}

		$block_name = $block['blockName'] ?? '';

		if ( ! is_string( $block_name ) || in_array( $block_name, self::EXCLUDED_BLOCKS, true ) ) {
			return $block_content;
		}

		$attributes = $block['attrs'] ?? array();

		if ( ! is_array( $attributes ) ) {
			return $block_content;
		}

		$classes = $this->hide_classes_for( $attributes );

		if ( array() === $classes ) {
			return $block_content;
		}

		$processor = new WP_HTML_Tag_Processor( $block_content );

		if ( ! $processor->next_tag() ) {
			return $block_content;
		}

		foreach ( $classes as $class_name ) {
			$processor->add_class( $class_name );
		}

		return $processor->get_updated_html();
	}

	/**
	 * Collect hide classes for attributes that are enabled.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return array<int, string> Utility class names.
	 */
	private function hide_classes_for( array $attributes ): array {
		$classes = array();

		foreach ( self::HIDE_CLASSES as $attr_name => $class_name ) {
			if ( ! empty( $attributes[ $attr_name ] ) ) {
				$classes[] = $class_name;
			}
		}

		return $classes;
	}
}
