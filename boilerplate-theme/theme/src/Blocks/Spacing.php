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
	private const CLASS_NAMES = array(
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
		'mobile:pt-none',
		'mobile:pt-xs',
		'mobile:pt-sm',
		'mobile:pt-md',
		'mobile:pt-lg',
		'mobile:pt-xl',
		'mobile:pr-none',
		'mobile:pr-xs',
		'mobile:pr-sm',
		'mobile:pr-md',
		'mobile:pr-lg',
		'mobile:pr-xl',
		'mobile:pb-none',
		'mobile:pb-xs',
		'mobile:pb-sm',
		'mobile:pb-md',
		'mobile:pb-lg',
		'mobile:pb-xl',
		'mobile:pl-none',
		'mobile:pl-xs',
		'mobile:pl-sm',
		'mobile:pl-md',
		'mobile:pl-lg',
		'mobile:pl-xl',
		'tablet:pt-none',
		'tablet:pt-xs',
		'tablet:pt-sm',
		'tablet:pt-md',
		'tablet:pt-lg',
		'tablet:pt-xl',
		'tablet:pr-none',
		'tablet:pr-xs',
		'tablet:pr-sm',
		'tablet:pr-md',
		'tablet:pr-lg',
		'tablet:pr-xl',
		'tablet:pb-none',
		'tablet:pb-xs',
		'tablet:pb-sm',
		'tablet:pb-md',
		'tablet:pb-lg',
		'tablet:pb-xl',
		'tablet:pl-none',
		'tablet:pl-xs',
		'tablet:pl-sm',
		'tablet:pl-md',
		'tablet:pl-lg',
		'tablet:pl-xl',
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
		'mobile:mt-none',
		'mobile:mt-xs',
		'mobile:mt-sm',
		'mobile:mt-md',
		'mobile:mt-lg',
		'mobile:mt-xl',
		'mobile:mr-none',
		'mobile:mr-xs',
		'mobile:mr-sm',
		'mobile:mr-md',
		'mobile:mr-lg',
		'mobile:mr-xl',
		'mobile:mb-none',
		'mobile:mb-xs',
		'mobile:mb-sm',
		'mobile:mb-md',
		'mobile:mb-lg',
		'mobile:mb-xl',
		'mobile:ml-none',
		'mobile:ml-xs',
		'mobile:ml-sm',
		'mobile:ml-md',
		'mobile:ml-lg',
		'mobile:ml-xl',
		'tablet:mt-none',
		'tablet:mt-xs',
		'tablet:mt-sm',
		'tablet:mt-md',
		'tablet:mt-lg',
		'tablet:mt-xl',
		'tablet:mr-none',
		'tablet:mr-xs',
		'tablet:mr-sm',
		'tablet:mr-md',
		'tablet:mr-lg',
		'tablet:mr-xl',
		'tablet:mb-none',
		'tablet:mb-xs',
		'tablet:mb-sm',
		'tablet:mb-md',
		'tablet:mb-lg',
		'tablet:mb-xl',
		'tablet:ml-none',
		'tablet:ml-xs',
		'tablet:ml-sm',
		'tablet:ml-md',
		'tablet:ml-lg',
		'tablet:ml-xl',
	);

	/**
	 * Side utilities keyed by spacing property.
	 *
	 * @var array<string, array<string, string>>
	 */
	private const SIDE_UTILITY = array(
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
	 * Mobile-first prefixes.
	 *
	 * Tablet starts at the mobile token. Desktop starts at the tablet token.
	 * Keep the pixel values identical to settings.viewport in theme.json.
	 *
	 * @var array<string, string>
	 */
	private const DEVICE_PREFIX = array(
		'mobile'  => '',
		'tablet'  => 'mobile:',
		'desktop' => 'tablet:',
	);

	/**
	 * Turns a spacing attribute into a class string.
	 *
	 * Empty viewports are omitted so they inherit the next smaller viewport.
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

			foreach ( self::DEVICE_PREFIX as $device => $prefix ) {
				$device_sides = $group[ $device ] ?? null;

				if ( ! is_array( $device_sides ) ) {
					continue;
				}

				foreach ( $sides as $side => $utility ) {
					$slug = $device_sides[ $side ] ?? '';

					if ( ! is_string( $slug ) || '' === $slug ) {
						continue;
					}

					$class_name = $prefix . $utility . '-' . $slug;

					if ( isset( $allowed[ $class_name ] ) ) {
						$classes[] = $class_name;
					}
				}
			}
		}

		return implode( ' ', $classes );
	}
}
