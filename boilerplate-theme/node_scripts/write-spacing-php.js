import { readFileSync, writeFileSync, unlinkSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const rootDir = join(dirname(fileURLToPath(import.meta.url)), '..');
const names = readFileSync(join(rootDir, 'spacing-class-names.php-snippet.txt'), 'utf8').trimEnd();

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
	private const CLASS_NAMES = array(
${names}
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
`;

writeFileSync(join(rootDir, 'theme/src/Blocks/Spacing.php'), php);
unlinkSync(join(rootDir, 'spacing-class-names.php-snippet.txt'));
unlinkSync(fileURLToPath(import.meta.url));
