import { getColorObjectByColorValue } from '@wordpress/block-editor';
import { BaseControl, ColorPalette } from '@wordpress/components';

/**
 * Color palette control that stores a palette slug or a custom hex color.
 *
 * @param {Object} props Component props.
 * @param {string} props.label Control label. Pass an already translated string.
 * @param {Array<Object>} props.palette Theme color palette.
 * @param {string|undefined} props.slug Stored palette slug.
 * @param {string|undefined} props.custom Stored custom color.
 * @param {boolean} [props.enableAlpha=true] Whether custom colors may have an alpha channel.
 * @param {Function} props.onChange Called with `{ slug, custom }`.
 * @returns {JSX.Element} Color control.
 */
export function PresetColorControl({ label, palette, slug, custom, enableAlpha = true, onChange }) {
	const value = slug ? palette.find((color) => color.slug === slug)?.color : custom;

	return (
		<BaseControl __nextHasNoMarginBottom label={label}>
			<ColorPalette
				colors={palette}
				value={value}
				enableAlpha={enableAlpha}
				onChange={(color) => {
					const preset = color ? getColorObjectByColorValue(palette, color) : undefined;

					onChange({
						slug: preset?.slug,
						custom: preset ? undefined : color,
					});
				}}
			/>
		</BaseControl>
	);
}
