<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks;

\defined( 'ABSPATH' ) || exit;

/**
 * Builds whitelist spacing utility classes from a block spacing attribute.
 */
class Spacing {
	/**
	 * Allowed utility classes.
	 *
	 * Keep in sync with blocks/_shared/spacing/spacing-class-names.js.
	 *
	 * @var array<int, string>
	 */
	private const array CLASS_NAMES = array(
		'pt-none',
		'pt-xs',
		'pt-sm',
		'pt-md',
		'pt-lg',
		'pt-xl',
		'pr-none',
		'pr-xs',
		'pr-sm',
		'pr-md',
		'pr-lg',
		'pr-xl',
		'pb-none',
		'pb-xs',
		'pb-sm',
		'pb-md',
		'pb-lg',
		'pb-xl',
		'pl-none',
		'pl-xs',
		'pl-sm',
		'pl-md',
		'pl-lg',
		'pl-xl',
		'max-tablet:pt-none',
		'max-tablet:pt-xs',
		'max-tablet:pt-sm',
		'max-tablet:pt-md',
		'max-tablet:pt-lg',
		'max-tablet:pt-xl',
		'max-tablet:pr-none',
		'max-tablet:pr-xs',
		'max-tablet:pr-sm',
		'max-tablet:pr-md',
		'max-tablet:pr-lg',
		'max-tablet:pr-xl',
		'max-tablet:pb-none',
		'max-tablet:pb-xs',
		'max-tablet:pb-sm',
		'max-tablet:pb-md',
		'max-tablet:pb-lg',
		'max-tablet:pb-xl',
		'max-tablet:pl-none',
		'max-tablet:pl-xs',
		'max-tablet:pl-sm',
		'max-tablet:pl-md',
		'max-tablet:pl-lg',
		'max-tablet:pl-xl',
		'max-mobile:pt-none',
		'max-mobile:pt-xs',
		'max-mobile:pt-sm',
		'max-mobile:pt-md',
		'max-mobile:pt-lg',
		'max-mobile:pt-xl',
		'max-mobile:pr-none',
		'max-mobile:pr-xs',
		'max-mobile:pr-sm',
		'max-mobile:pr-md',
		'max-mobile:pr-lg',
		'max-mobile:pr-xl',
		'max-mobile:pb-none',
		'max-mobile:pb-xs',
		'max-mobile:pb-sm',
		'max-mobile:pb-md',
		'max-mobile:pb-lg',
		'max-mobile:pb-xl',
		'max-mobile:pl-none',
		'max-mobile:pl-xs',
		'max-mobile:pl-sm',
		'max-mobile:pl-md',
		'max-mobile:pl-lg',
		'max-mobile:pl-xl',
		'mt-none',
		'mt-xs',
		'mt-sm',
		'mt-md',
		'mt-lg',
		'mt-xl',
		'mr-none',
		'mr-xs',
		'mr-sm',
		'mr-md',
		'mr-lg',
		'mr-xl',
		'mb-none',
		'mb-xs',
		'mb-sm',
		'mb-md',
		'mb-lg',
		'mb-xl',
		'ml-none',
		'ml-xs',
		'ml-sm',
		'ml-md',
		'ml-lg',
		'ml-xl',
		'max-tablet:mt-none',
		'max-tablet:mt-xs',
		'max-tablet:mt-sm',
		'max-tablet:mt-md',
		'max-tablet:mt-lg',
		'max-tablet:mt-xl',
		'max-tablet:mr-none',
		'max-tablet:mr-xs',
		'max-tablet:mr-sm',
		'max-tablet:mr-md',
		'max-tablet:mr-lg',
		'max-tablet:mr-xl',
		'max-tablet:mb-none',
		'max-tablet:mb-xs',
		'max-tablet:mb-sm',
		'max-tablet:mb-md',
		'max-tablet:mb-lg',
		'max-tablet:mb-xl',
		'max-tablet:ml-none',
		'max-tablet:ml-xs',
		'max-tablet:ml-sm',
		'max-tablet:ml-md',
		'max-tablet:ml-lg',
		'max-tablet:ml-xl',
		'max-mobile:mt-none',
		'max-mobile:mt-xs',
		'max-mobile:mt-sm',
		'max-mobile:mt-md',
		'max-mobile:mt-lg',
		'max-mobile:mt-xl',
		'max-mobile:mr-none',
		'max-mobile:mr-xs',
		'max-mobile:mr-sm',
		'max-mobile:mr-md',
		'max-mobile:mr-lg',
		'max-mobile:mr-xl',
		'max-mobile:mb-none',
		'max-mobile:mb-xs',
		'max-mobile:mb-sm',
		'max-mobile:mb-md',
		'max-mobile:mb-lg',
		'max-mobile:mb-xl',
		'max-mobile:ml-none',
		'max-mobile:ml-xs',
		'max-mobile:ml-sm',
		'max-mobile:ml-md',
		'max-mobile:ml-lg',
		'max-mobile:ml-xl',
	);

	/**
	 * Side utilities keyed by spacing property.
	 *
	 * @var array<string, array<string, string>>
	 */
	private const array SIDE_UTILITY = array(
		'padding' => array(
			'top'    => 'pt',
			'right'  => 'pr',
			'bottom' => 'pb',
			'left'   => 'pl',
		),
		'margin'  => array(
			'top'    => 'mt',
			'right'  => 'mr',
			'bottom' => 'mb',
			'left'   => 'ml',
		),
	);

	/**
	 * Desktop-first prefixes, so an unset device leaves larger devices untouched.
	 *
	 * Tablet ends below the tablet token. Mobile ends below the mobile token.
	 * Keep the pixel values identical to settings.viewport in theme.json.
	 *
	 * @var array<string, string>
	 */
	private const array DEVICE_PREFIX = array(
		'desktop' => '',
		'tablet'  => 'max-tablet:',
		'mobile'  => 'max-mobile:',
	);

	/**
	 * Lookup order per device: the own value first, then larger devices only.
	 *
	 * Keep in sync with blocks/_shared/editor-device/resolve-device-value.js.
	 *
	 * @var array<string, array<int, string>>
	 */
	private const array DEVICE_LOOKUP_ORDER = array(
		'desktop' => array( 'desktop' ),
		'tablet'  => array( 'tablet', 'desktop' ),
		'mobile'  => array( 'mobile', 'tablet', 'desktop' ),
	);

	/**
	 * Returns the effective value of a device from per-device values.
	 *
	 * A value set on desktop applies to every device without its own value, a value
	 * set on tablet or mobile never applies upward. `null` counts as not set.
	 *
	 * @param array<string, mixed> $values Values keyed by `mobile`, `tablet` and `desktop`.
	 * @param string               $device Device to resolve.
	 * @return mixed First set value in lookup order, or null.
	 */
	public static function resolve_device_value( array $values, string $device ): mixed {
		foreach ( self::DEVICE_LOOKUP_ORDER[ $device ] ?? array() as $candidate ) {
			if ( isset( $values[ $candidate ] ) ) {
				return $values[ $candidate ];
			}
		}

		return null;
	}

	/**
	 * Turns a spacing attribute into a class string.
	 *
	 * Each side resolves per viewport with resolve_device_value(). A viewport class
	 * is only printed when it differs from the next larger viewport.
	 * Mirrors getSpacingClassName() in blocks/_shared/spacing/spacing-classes.js.
	 *
	 * @param array<string, mixed> $spacing Spacing attribute.
	 * @return string Space-separated utility classes.
	 */
	public static function classes( array $spacing ): string {
		$allowed = array_fill_keys( self::CLASS_NAMES, true );
		$classes = array();

		foreach ( self::SIDE_UTILITY as $property => $sides ) {
			$group = $spacing[ $property ] ?? null;

			if ( ! is_array( $group ) ) {
				continue;
			}

			foreach ( $sides as $side => $utility ) {
				$values = array();

				foreach ( array_keys( self::DEVICE_PREFIX ) as $device ) {
					$slug = is_array( $group[ $device ] ?? null ) ? ( $group[ $device ][ $side ] ?? null ) : null;

					$values[ $device ] = is_string( $slug ) && '' !== $slug ? $slug : null;
				}

				$previous = '';

				foreach ( self::DEVICE_PREFIX as $device => $prefix ) {
					$slug = self::resolve_device_value( $values, $device );
					$slug = is_string( $slug ) ? $slug : '';

					if ( '' !== $slug && $slug !== $previous ) {
						$class_name = $prefix . $utility . '-' . $slug;

						if ( isset( $allowed[ $class_name ] ) ) {
							$classes[] = $class_name;
						}
					}

					$previous = $slug;
				}
			}
		}

		return implode( ' ', $classes );
	}
}
