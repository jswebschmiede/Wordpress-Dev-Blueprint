<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks\Blocks;

use CompanyName\BoilerplateTheme\Blocks\BlockInterface;
use CompanyName\BoilerplateTheme\Blocks\Spacing;
use CompanyName\BoilerplateTheme\Theme\TimberIntegration;
use CompanyName\BoilerplateTheme\Timber\Timber;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles rendering for the hero block.
 */
class HeroBlock implements BlockInterface {
	/**
	 * Media query for the desktop image.
	 *
	 * Keep in sync with settings.viewport.tablet in theme.json and DESKTOP_MEDIA in blocks/hero/edit.js.
	 * Media queries cannot read CSS variables.
	 */
	private const string DESKTOP_MEDIA = '(min-width: 782px)';

	/**
	 * Allowed HTML in heading and text. RichText stores manual line breaks as <br>.
	 *
	 * @var array<string, array<string, bool>>
	 */
	private const array ALLOWED_TEXT_HTML = array( 'br' => array() );

	/**
	 * Overlay styles: left-to-right fade or even fill. Keep in sync with blocks/hero/edit.js.
	 *
	 * @var string[]
	 */
	private const array OVERLAY_STYLES = array( 'gradient', 'solid' );

	/**
	 * Default overlay opacity in percent.
	 */
	private const int OVERLAY_OPACITY_DEFAULT = 70;

	/**
	 * Variants: full background image or image right of the text. Keep in sync with blocks/hero/edit.js.
	 *
	 * @var string[]
	 */
	private const array VARIANTS = array( 'image', 'split' );

	/**
	 * Background variants of the split variant, same as ColumnsBlock. Keep in sync with blocks/hero/edit.js.
	 *
	 * @var string[]
	 */
	private const array BACKGROUNDS = array( 'none', 'primary', 'secondary', 'dark', 'light' );

	/**
	 * Text color settings of the split variant. Keep in sync with blocks/hero/edit.js.
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
	 * Hex colors with optional alpha. WordPress strips rgb()/hsl() from style attributes.
	 */
	private const string HEX_COLOR_PATTERN = '/^#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i';

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
				'title'              => '',
				'text'               => '',
				'buttonLabel'        => __( 'Mehr erfahren', 'boilerplate-theme' ),
				'buttonUrl'          => '',
				'imageDesktopId'     => 0,
				'imageMobileId'      => 0,
				'overlayColor'       => '',
				'customOverlayColor' => '',
				'overlayOpacity'     => self::OVERLAY_OPACITY_DEFAULT,
				'overlayStyle'       => 'gradient',
				'variant'            => 'image',
				'background'         => 'none',
				'textColor'          => 'auto',
				'className'          => '',
				'spacing'            => array(),
			)
		);

		$wrapper_classes = array( 'hero not-prose' );
		$class_name      = $this->get_string_attribute( $attributes, 'className' );
		$spacing         = isset( $attributes['spacing'] ) && is_array( $attributes['spacing'] ) ? $attributes['spacing'] : array();
		$spacing_classes = Spacing::classes( $spacing );

		if ( '' !== $class_name ) {
			$wrapper_classes[] = $class_name;
		}

		if ( '' !== $spacing_classes ) {
			$wrapper_classes[] = $spacing_classes;
		}

		$wrapper_attributes = array();
		$variant            = $this->get_enum_attribute( $attributes, 'variant', self::VARIANTS, 'image' );
		$is_split           = 'split' === $variant;

		// The overlay only applies to the background image variant.
		$overlay_color = $is_split ? '' : $this->get_color_value(
			$this->get_string_attribute( $attributes, 'overlayColor' ),
			$this->get_string_attribute( $attributes, 'customOverlayColor' )
		);

		if ( $is_split ) {
			$background = $this->get_enum_attribute( $attributes, 'background', self::BACKGROUNDS, 'none' );
			$text_color = $this->resolve_text_color(
				$background,
				$this->get_enum_attribute( $attributes, 'textColor', self::TEXT_COLORS, 'auto' )
			);

			$wrapper_classes[] = 'hero--split';

			if ( 'none' !== $background ) {
				$wrapper_classes[] = 'hero--bg-' . $background;
			}

			$wrapper_classes[] = 'hero--text-' . $text_color;
		}

		if ( '' !== $overlay_color ) {
			$overlay_style   = $this->get_string_attribute( $attributes, 'overlayStyle' );
			$overlay_style   = in_array( $overlay_style, self::OVERLAY_STYLES, true ) ? $overlay_style : 'gradient';
			$overlay_opacity = $this->clamp( $attributes['overlayOpacity'], 0, 100, self::OVERLAY_OPACITY_DEFAULT );

			$wrapper_classes[] = 'hero--overlay';

			if ( 'gradient' === $overlay_style ) {
				$wrapper_classes[] = 'hero--overlay-gradient';
			}

			$wrapper_attributes['style'] = '--hero-overlay-color:' . $overlay_color . ';--hero-overlay-opacity:' . $overlay_opacity . '%';
		}

		$wrapper_attributes['class'] = implode( ' ', $wrapper_classes );

		$image_desktop = $this->get_image_data( $this->get_int_attribute( $attributes, 'imageDesktopId' ) );
		$image_mobile  = $this->get_image_data( $this->get_int_attribute( $attributes, 'imageMobileId' ) );

		// A missing image falls back to the other one, so both viewports always show an image.
		$image_desktop ??= $image_mobile;
		$image_mobile  ??= $image_desktop;

		$context = array(
			'attributes'         => $attributes,
			'variant'            => $variant,
			'title'              => wp_kses( $this->get_string_attribute( $attributes, 'title' ), self::ALLOWED_TEXT_HTML ),
			'text'               => wp_kses( $this->get_string_attribute( $attributes, 'text' ), self::ALLOWED_TEXT_HTML ),
			'button_label'       => trim( $this->get_string_attribute( $attributes, 'buttonLabel' ) ),
			'button_url'         => trim( $this->get_string_attribute( $attributes, 'buttonUrl' ) ),
			'image_desktop'      => $image_desktop,
			'image_mobile'       => $image_mobile,
			'desktop_media'      => self::DESKTOP_MEDIA,
			'wrapper_attributes' => get_block_wrapper_attributes( $wrapper_attributes ),
		);

		/**
		 * Filters the Twig template(s) used to render the hero block (relative to `views/`).
		 *
		 * @param string|string[]      $template   Twig template name or fallback list.
		 * @param array<string, mixed> $attributes Parsed block attributes.
		 */
		$template = apply_filters( 'boilerplate_theme_hero_block_template', 'blocks/hero.twig', $attributes );

		if ( ! is_string( $template ) && ! is_array( $template ) ) {
			return '';
		}

		return (string) Timber::compile( $template, $context );
	}

	/**
	 * Resolves the view data for an image attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array{src: string, srcset: string, width: int, height: int, alt: string}|null Image data, or null if the attachment is not a valid image.
	 */
	private function get_image_data( int $attachment_id ): ?array {
		if ( $attachment_id <= 0 ) {
			return null;
		}

		$image = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! is_array( $image ) ) {
			return null;
		}

		$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
		$alt    = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		return array(
			'src'    => (string) $image[0],
			'srcset' => is_string( $srcset ) ? $srcset : '',
			'width'  => (int) $image[1],
			'height' => (int) $image[2],
			'alt'    => is_string( $alt ) ? trim( $alt ) : '',
		);
	}

	/**
	 * Resolves a preset slug or custom color to a CSS value.
	 *
	 * @param string $slug   Palette slug.
	 * @param string $custom Custom color.
	 * @return string `var(--wp--preset--color--<slug>)`, a hex color, or an empty string.
	 */
	private function get_color_value( string $slug, string $custom ): string {
		$slug = sanitize_key( $slug );

		if ( '' !== $slug ) {
			return 'var(--wp--preset--color--' . $slug . ')';
		}

		return 1 === preg_match( self::HEX_COLOR_PATTERN, $custom ) ? $custom : '';
	}

	/**
	 * Clamps a numeric attribute into a range.
	 *
	 * @param mixed $value    Raw value.
	 * @param int   $min      Lower bound.
	 * @param int   $max      Upper bound.
	 * @param int   $fallback Value for non-numeric input.
	 * @return int Clamped value.
	 */
	private function clamp( mixed $value, int $min, int $max, int $fallback ): int {
		if ( ! is_numeric( $value ) ) {
			return $fallback;
		}

		return max( $min, min( $max, (int) $value ) );
	}

	/**
	 * Resolves the effective text color variant. Mirrors resolveTextColor() in blocks/hero/edit.js.
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
	 * @return string Allowed value or fallback.
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

	/**
	 * Returns a numeric block attribute as integer.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $key        Attribute key.
	 * @return int Attribute value, or 0 for missing/non-numeric values.
	 */
	private function get_int_attribute( array $attributes, string $key ): int {
		return isset( $attributes[ $key ] ) && is_numeric( $attributes[ $key ] ) ? (int) $attributes[ $key ] : 0;
	}
}
