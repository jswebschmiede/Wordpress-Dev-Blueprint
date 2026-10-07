<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks\Blocks;

use CompanyName\BoilerplateTheme\Blocks\BlockInterface;
use CompanyName\BoilerplateTheme\Blocks\Spacing;
use CompanyName\BoilerplateTheme\Theme\TimberIntegration;
use CompanyName\BoilerplateTheme\Timber\Timber;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles rendering for the columns block. Columns are `boilerplate/column` inner blocks.
 */
class ColumnsBlock implements BlockInterface {
	/**
	 * Maximum column count. Keep in sync with MAX_COLUMNS in blocks/columns/edit.js.
	 *
	 * @var int
	 */
	private const int MAX_COLUMNS = 4;

	/**
	 * Fixed column width layouts per column count. The first layout is the default
	 * with equal widths. Keep in sync with LAYOUT_NAMES in blocks/columns/layouts.js.
	 *
	 * @var array<int, string[]>
	 */
	private const array LAYOUTS = array(
		1 => array( '100' ),
		2 => array( '50-50', '30-70', '70-30' ),
		3 => array( '33-33-33', '25-50-25' ),
		4 => array( '25-25-25-25' ),
	);

	/**
	 * Default gap in pixels. Keep in sync with blocks/columns/gap-control.js.
	 *
	 * @var int
	 */
	private const int DEFAULT_GAP = 24;

	/**
	 * Maximum gap in pixels. Keep in sync with blocks/columns/gap-control.js.
	 *
	 * @var int
	 */
	private const int MAX_GAP = 96;

	/**
	 * Allowed background variants. Keep in sync with blocks/columns/edit.js.
	 *
	 * @var string[]
	 */
	private const array BACKGROUNDS = array( 'none', 'primary', 'secondary', 'dark', 'light' );

	/**
	 * Allowed text color settings. Keep in sync with blocks/columns/edit.js.
	 *
	 * @var string[]
	 */
	private const array TEXT_COLORS = array( 'auto', 'dark', 'light' );

	/**
	 * Backgrounds that get light text when the text color is `auto`.
	 *
	 * @var string[]
	 */
	private const array LIGHT_TEXT_BACKGROUNDS = array( 'primary', 'dark' );

	/**
	 * Renders the block on the frontend.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Rendered inner blocks (columns).
	 * @param \WP_Block|null       $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render( array $attributes, string $content, ?\WP_Block $block = null ): string {
		unset( $block );

		if ( ! TimberIntegration::is_available() ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return '';
			}

			return '<p><strong>Boilerplate Theme:</strong> '
				. esc_html( TimberIntegration::get_missing_dependency_message() )
				. '</p>';
		}

		if ( '' === trim( $content ) ) {
			return '';
		}

		$attributes = wp_parse_args(
			$attributes,
			array(
				'heading'        => '',
				'columns'        => 2,
				'layout'         => '',
				'gap'            => null,
				'gapTablet'      => null,
				'gapDesktop'     => null,
				'rowGap'         => null,
				'rowGapTablet'   => null,
				'rowGapDesktop'  => null,
				'verticalCenter' => false,
				'background'     => 'none',
				'textColor'      => 'auto',
				'className'      => '',
				'spacing'        => array(),
			)
		);

		$columns    = is_numeric( $attributes['columns'] ) ? (int) $attributes['columns'] : 2;
		$columns    = max( 1, min( self::MAX_COLUMNS, $columns ) );
		$background = $this->get_enum_attribute( $attributes, 'background', self::BACKGROUNDS, 'none' );
		$text_color = $this->resolve_text_color(
			$background,
			$this->get_enum_attribute( $attributes, 'textColor', self::TEXT_COLORS, 'auto' )
		);

		$wrapper_classes = array(
			'columns',
			'columns--count-' . $columns,
		);

		$layout = $this->resolve_layout( $columns, $this->get_string_attribute( $attributes, 'layout' ) );

		if ( self::LAYOUTS[ $columns ][0] !== $layout ) {
			$wrapper_classes[] = 'columns--layout-' . $layout;
		}

		if ( true === $attributes['verticalCenter'] ) {
			$wrapper_classes[] = 'columns--align-center';
		}

		if ( 'none' !== $background ) {
			$wrapper_classes[] = 'columns--bg-' . $background;
		}

		$wrapper_classes[] = 'columns--text-' . $text_color;

		$class_name      = $this->get_string_attribute( $attributes, 'className' );
		$spacing         = is_array( $attributes['spacing'] ) ? $attributes['spacing'] : array();
		$spacing_classes = Spacing::classes( $spacing );

		if ( '' !== $class_name ) {
			$wrapper_classes[] = $class_name;
		}

		if ( '' !== $spacing_classes ) {
			$wrapper_classes[] = $spacing_classes;
		}

		$context = array(
			'attributes'         => $attributes,
			'heading'            => trim( $this->get_string_attribute( $attributes, 'heading' ) ),
			'content'            => $content,
			'wrapper_attributes' => get_block_wrapper_attributes(
				array(
					'class' => implode( ' ', $wrapper_classes ),
					'style' => $this->get_gap_style( $attributes ),
				)
			),
		);

		/**
		 * Filters the Twig template(s) used to render the columns block (relative to `views/`).
		 *
		 * @param string|string[]      $template   Twig template name or fallback list.
		 * @param array<string, mixed> $attributes Parsed block attributes.
		 */
		$template = apply_filters( 'boilerplate_theme_columns_block_template', 'blocks/columns.twig', $attributes );

		if ( ! is_string( $template ) && ! is_array( $template ) ) {
			return '';
		}

		return (string) Timber::compile( $template, $context );
	}

	/**
	 * Clamps a gap to 0..MAX_GAP pixels.
	 *
	 * @param mixed $value Raw gap in pixels.
	 * @return int|null Valid gap in pixels, or null for a missing or invalid value.
	 */
	private function normalize_gap( mixed $value ): ?int {
		if ( ! is_numeric( $value ) ) {
			return null;
		}

		return max( 0, min( self::MAX_GAP, (int) $value ) );
	}

	/**
	 * Resolves the effective gap per device for one attribute set. An unset device takes
	 * the next larger set device (Spacing::resolve_device_value()), else its fallback.
	 * Mirrors resolveDeviceGaps() in blocks/columns/gap-control.js.
	 *
	 * @param array<string, mixed>                                   $attributes Block attributes.
	 * @param array{mobile: string, tablet: string, desktop: string} $keys       Attribute key per device.
	 * @param array{mobile: int, tablet: int, desktop: int}          $fallback   Gap per device when nothing is set.
	 * @return array{mobile: int, tablet: int, desktop: int} Gaps in pixels.
	 */
	private function resolve_gaps( array $attributes, array $keys, array $fallback ): array {
		$values = array();

		foreach ( $keys as $device => $key ) {
			$values[ $device ] = $this->normalize_gap( $attributes[ $key ] ?? null );
		}

		$gaps = array();

		foreach ( array_keys( $values ) as $device ) {
			$gap             = Spacing::resolve_device_value( $values, $device );
			$gaps[ $device ] = is_int( $gap ) ? $gap : $fallback[ $device ];
		}

		return $gaps;
	}

	/**
	 * Returns the inline column and row gap custom properties. All are always set so
	 * nested columns blocks do not inherit them. Rows fall back to the column gap per device.
	 * Mirrors getGapStyle() in blocks/columns/gap-control.js.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string Inline style declarations.
	 */
	private function get_gap_style( array $attributes ): string {
		$gaps     = $this->resolve_gaps(
			$attributes,
			array(
				'mobile'  => 'gap',
				'tablet'  => 'gapTablet',
				'desktop' => 'gapDesktop',
			),
			array(
				'mobile'  => self::DEFAULT_GAP,
				'tablet'  => self::DEFAULT_GAP,
				'desktop' => self::DEFAULT_GAP,
			)
		);
		$row_gaps = $this->resolve_gaps(
			$attributes,
			array(
				'mobile'  => 'rowGap',
				'tablet'  => 'rowGapTablet',
				'desktop' => 'rowGapDesktop',
			),
			$gaps
		);

		return sprintf(
			'--columns-gap:%dpx;--columns-gap-tablet:%dpx;--columns-gap-desktop:%dpx;'
			. '--columns-row-gap:%dpx;--columns-row-gap-tablet:%dpx;--columns-row-gap-desktop:%dpx;',
			$gaps['mobile'],
			$gaps['tablet'],
			$gaps['desktop'],
			$row_gaps['mobile'],
			$row_gaps['tablet'],
			$row_gaps['desktop']
		);
	}

	/**
	 * Returns the layout for a column count, falling back to equal widths.
	 * Mirrors resolveLayout() in blocks/columns/layouts.js.
	 *
	 * @param int    $columns Column count (1-4).
	 * @param string $layout  Stored layout name.
	 * @return string Allowed layout name.
	 */
	private function resolve_layout( int $columns, string $layout ): string {
		$layouts = self::LAYOUTS[ $columns ] ?? self::LAYOUTS[2];

		return in_array( $layout, $layouts, true ) ? $layout : $layouts[0];
	}

	/**
	 * Resolves the effective text color variant. Mirrors resolveTextColor() in blocks/columns/edit.js.
	 *
	 * @param string $background Background variant.
	 * @param string $text_color Text color setting (`auto`, `dark` or `light`).
	 * @return string `dark` or `light`.
	 */
	private function resolve_text_color( string $background, string $text_color ): string {
		if ( 'auto' !== $text_color ) {
			return $text_color;
		}

		return in_array( $background, self::LIGHT_TEXT_BACKGROUNDS, true ) ? 'light' : 'dark';
	}

	/**
	 * Returns a string attribute if it is one of the allowed values.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $key        Attribute key.
	 * @param string[]             $allowed    Allowed values.
	 * @param string               $fallback   Value for missing or invalid input.
	 * @return string Allowed value.
	 */
	private function get_enum_attribute( array $attributes, string $key, array $allowed, string $fallback ): string {
		$value = $this->get_string_attribute( $attributes, $key );

		return in_array( $value, $allowed, true ) ? $value : $fallback;
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
