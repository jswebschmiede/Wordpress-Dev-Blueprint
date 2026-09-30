<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks;

use WP_HTML_Tag_Processor;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers scroll-animation attributes and prints them on rendered blocks.
 */
class BlockAnimations {
	/**
	 * Script handle for the frontend animation runtime.
	 */
	private const SCRIPT_HANDLE = 'boilerplate-theme-block-animations';

	/**
	 * Blocks that must not receive animation attributes.
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
	 * Animation attributes added to every eligible block type.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private const ANIMATION_ATTRIBUTES = array(
		'eiAnimation'          => array(
			'type'    => 'string',
			'default' => '',
		),
		'eiAnimationDuration'  => array(
			'type'    => 'number',
			'default' => 0.8,
		),
		'eiAnimationDelay'     => array(
			'type'    => 'number',
			'default' => 0,
		),
		'eiAnimationEasing'    => array(
			'type'    => 'string',
			'default' => 'power2.out',
		),
		'eiAnimationDistance'  => array(
			'type'    => 'number',
			'default' => 50,
		),
		'eiAnimationOnce'      => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'eiAnimationThreshold' => array(
			'type'    => 'number',
			'default' => 20,
		),
	);

	/**
	 * Whether the runtime script has already been enqueued.
	 */
	private bool $runtime_enqueued = false;

	/**
	 * Registers block animation hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'register_block_type_args', array( $this, 'register_animation_attributes' ), 10, 2 );
		add_filter( 'render_block', array( $this, 'add_animation_attributes' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_runtime_script' ) );
	}

	/**
	 * Register animation attributes on all blocks server-side.
	 *
	 * @param array<string, mixed> $args       Block type arguments.
	 * @param string               $block_type Block type name.
	 * @return array<string, mixed> Modified block type arguments.
	 */
	public function register_animation_attributes( array $args, string $block_type ): array {
		if ( in_array( $block_type, self::EXCLUDED_BLOCKS, true ) ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		foreach ( self::ANIMATION_ATTRIBUTES as $attr_name => $attr_config ) {
			if ( ! isset( $args['attributes'][ $attr_name ] ) ) {
				$args['attributes'][ $attr_name ] = $attr_config;
			}
		}

		return $args;
	}

	/**
	 * Add animation data attributes to the first rendered tag.
	 *
	 * @param string               $block_content The block content.
	 * @param array<string, mixed> $block         The full block, including name and attributes.
	 * @return string Updated block HTML.
	 */
	public function add_animation_attributes( string $block_content, array $block ): string {
		if ( '' === $block_content || is_admin() ) {
			return $block_content;
		}

		$attributes = $block['attrs'] ?? array();

		if ( ! is_array( $attributes ) || empty( $attributes['eiAnimation'] ) ) {
			return $block_content;
		}

		$animation = $attributes['eiAnimation'];

		if ( ! is_string( $animation ) || '' === $animation ) {
			return $block_content;
		}

		$processor = new WP_HTML_Tag_Processor( $block_content );

		if ( ! $processor->next_tag() ) {
			return $block_content;
		}

		$processor->set_attribute( 'data-ei-animation', $animation );
		$this->set_optional_attributes( $processor, $attributes );
		$this->enqueue_runtime_script();

		return $processor->get_updated_html();
	}

	/**
	 * Register the frontend runtime without printing it.
	 *
	 * The script is enqueued from render_block only when a block uses an animation.
	 *
	 * @return void
	 */
	public function register_runtime_script(): void {
		$script_path = get_template_directory() . '/js/block-animations.min.js';

		if ( ! file_exists( $script_path ) ) {
			return;
		}

		wp_register_script(
			self::SCRIPT_HANDLE,
			get_template_directory_uri() . '/js/block-animations.min.js',
			array(),
			(string) filemtime( $script_path ),
			true,
		);
	}

	/**
	 * Write optional animation settings onto the current tag.
	 *
	 * @param WP_HTML_Tag_Processor $processor  HTML processor positioned on the block tag.
	 * @param array<string, mixed>  $attributes Block attributes.
	 * @return void
	 */
	private function set_optional_attributes( WP_HTML_Tag_Processor $processor, array $attributes ): void {
		if ( isset( $attributes['eiAnimationDuration'] ) && is_numeric( $attributes['eiAnimationDuration'] ) ) {
			$processor->set_attribute( 'data-ei-animation-duration', (string) $attributes['eiAnimationDuration'] );
		}

		if ( isset( $attributes['eiAnimationDelay'] ) && is_numeric( $attributes['eiAnimationDelay'] ) ) {
			$processor->set_attribute( 'data-ei-animation-delay', (string) $attributes['eiAnimationDelay'] );
		}

		if ( isset( $attributes['eiAnimationEasing'] ) && is_string( $attributes['eiAnimationEasing'] ) ) {
			$processor->set_attribute( 'data-ei-animation-easing', $attributes['eiAnimationEasing'] );
		}

		if ( isset( $attributes['eiAnimationDistance'] ) && is_numeric( $attributes['eiAnimationDistance'] ) ) {
			$processor->set_attribute( 'data-ei-animation-distance', (string) $attributes['eiAnimationDistance'] );
		}

		if ( isset( $attributes['eiAnimationOnce'] ) ) {
			$processor->set_attribute(
				'data-ei-animation-once',
				$attributes['eiAnimationOnce'] ? 'true' : 'false',
			);
		}

		if ( isset( $attributes['eiAnimationThreshold'] ) && is_numeric( $attributes['eiAnimationThreshold'] ) ) {
			$processor->set_attribute( 'data-ei-animation-threshold', (string) $attributes['eiAnimationThreshold'] );
		}
	}

	/**
	 * Enqueue the animation runtime once for the current request.
	 *
	 * @return void
	 */
	private function enqueue_runtime_script(): void {
		if ( $this->runtime_enqueued ) {
			return;
		}

		$this->runtime_enqueued = true;
		wp_enqueue_script( self::SCRIPT_HANDLE );
	}
}
