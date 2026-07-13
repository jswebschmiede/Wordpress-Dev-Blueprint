<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks;

use CompanyName\BoilerplateTheme\Blocks\Blocks\ExampleBlock;

\defined( 'ABSPATH' ) || exit;

/**
 * Manages registration and rendering of custom Gutenberg blocks.
 */
class BlockManager {
	/**
	 * Path to the deployable blocks directory.
	 *
	 * @var string
	 */
	private string $blocks_path = '';

	/**
	 * Registered block instances.
	 *
	 * @var array<string, BlockInterface>
	 */
	private array $blocks = array();

	/**
	 * Initializes block hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->blocks_path = get_template_directory() . '/blocks';

		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Registers the custom block category.
	 *
	 * @param array<int, array<string, mixed>> $block_categories     Block categories.
	 * @param \WP_Block_Editor_Context        $block_editor_context Current editor context.
	 * @return array<int, array<string, mixed>> Updated block categories.
	 */
	public function register_block_category( array $block_categories, \WP_Block_Editor_Context $block_editor_context ): array {
		unset( $block_editor_context );

		return array_merge(
			array(
				array(
					'slug'  => 'boilerplate-theme',
					'title' => __( 'Boilerplate Theme', 'boilerplate-theme' ),
					'icon'  => null,
				),
			),
			$block_categories
		);
	}

	/**
	 * Registers all custom blocks.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		$this->register_block( 'example-block', new ExampleBlock() );
	}

	/**
	 * Registers a single block from metadata.
	 *
	 * @param string         $name  Block directory name.
	 * @param BlockInterface $block Block renderer.
	 * @return void
	 */
	private function register_block( string $name, BlockInterface $block ): void {
		$block_path = $this->blocks_path . '/' . $name;

		if ( ! file_exists( $block_path . '/block.json' ) ) {
			return;
		}

		$this->blocks[ $name ] = $block;

		register_block_type(
			$block_path,
			array(
				'render_callback' => array( $block, 'render' ),
			)
		);
	}

	/**
	 * Enqueues the custom block editor bundle.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		$asset_file = get_template_directory() . '/js/blocks.min.js';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		wp_enqueue_script(
			'boilerplate-theme-blocks-editor',
			get_template_directory_uri() . '/js/blocks.min.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-i18n',
				'wp-block-editor',
			),
			\defined( 'BOILERPLATE_THEME_VERSION' ) ? BOILERPLATE_THEME_VERSION : (string) filemtime( $asset_file ),
			true
		);
	}
}
