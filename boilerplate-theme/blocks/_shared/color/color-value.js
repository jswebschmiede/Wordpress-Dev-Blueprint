/**
 * Hex colors as produced by ColorPalette, with optional alpha.
 * WordPress strips rgb()/hsl() from style attributes, so only hex is supported.
 * Keep in sync with HEX_COLOR_PATTERN in the PHP block classes (IconBoxBlock, HeroBlock).
 */
const HEX_COLOR = /^#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i;

/**
 * Resolves a preset slug or custom color to a CSS value.
 *
 * @param {string|undefined} slug Palette slug.
 * @param {string|undefined} custom Custom hex color.
 * @returns {string|undefined} `var(--wp--preset--color--<slug>)`, the hex color, or undefined.
 */
export function getColorValue(slug, custom) {
	if (slug) {
		return `var(--wp--preset--color--${slug})`;
	}

	return custom && HEX_COLOR.test(custom) ? custom : undefined;
}
