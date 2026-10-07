/**
 * Rewrites theme/src/Blocks/Spacing.php from the shared spacing class whitelist.
 *
 * Class names come from blocks/_shared/spacing/spacing-class-names.js.
 * Resolution stays desktop-first and must match spacing-classes.js.
 */

import { readFileSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const rootDir = join(dirname(fileURLToPath(import.meta.url)), '..');
const source = readFileSync(join(rootDir, 'blocks/_shared/spacing/spacing-class-names.js'), 'utf8');
const match = source.match(/export const SPACING_CLASS_NAMES = \[([\s\S]*?)\];/);

if (!match) {
	throw new Error('Could not read SPACING_CLASS_NAMES.');
}

const names = [...match[1].matchAll(/'([^']+)'/g)].map((item) => item[1]);

if (names.length === 0) {
	throw new Error('SPACING_CLASS_NAMES is empty.');
}

const classNames = names.map((name) => `\t\t'${name}',`).join('\n');

const php = `<?php

declare( strict_types=1 );

namespace CompanyName\\BoilerplateTheme\\Blocks;

\\defined( 'ABSPATH' ) || exit;

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
${classNames}
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
	 * set on tablet or mobile never applies upward. \`null\` counts as not set.
	 *
	 * @param array<string, mixed> $values Values keyed by \`mobile\`, \`tablet\` and \`desktop\`.
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
`;

writeFileSync(join(rootDir, 'theme/src/Blocks/Spacing.php'), php);
console.log(`Wrote Spacing.php with ${names.length} class names.`);
