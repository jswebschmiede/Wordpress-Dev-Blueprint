/**
 * Inline text color format for the block editor.
 *
 * Adds a dedicated toolbar button to every rich text field that colors the
 * selected text. Palette colors are stored as `has-{slug}-color` classes so
 * the global styles from theme.json apply on the frontend; custom colors fall
 * back to an inline `color` style.
 */

const { __ } = wp.i18n;
const { registerFormatType, applyFormat, removeFormat, getActiveFormat } = wp.richText;
const { BlockControls, ColorPalette, useSettings, getColorClassName, getColorObjectByColorValue } =
	wp.blockEditor;
const { Dropdown, ToolbarGroup, ToolbarButton } = wp.components;
const { useMemo } = wp.element;

/**
 * Format name.
 *
 * @type {string}
 */
const FORMAT_NAME = 'boilerplate/text-color';

/**
 * Class prefix used by palette color classes.
 *
 * @type {RegExp}
 */
const COLOR_CLASS_PATTERN = /^has-(.+)-color$/;

/**
 * Fallback when no palette is configured.
 *
 * @type {Array}
 */
const EMPTY_ARRAY = [];

/**
 * Resolve the active text color of the current selection.
 *
 * @param {Object} value - Rich text value.
 * @param {Array<{ slug: string, color: string }>} palette - Theme color palette.
 * @returns {string|undefined} Active color value.
 */
function getActiveColor(value, palette) {
	const format = getActiveFormat(value, FORMAT_NAME);

	if (!format) {
		return undefined;
	}

	const { class: className = '', style = '' } = format.attributes ?? {};

	for (const name of className.split(' ')) {
		const match = name.match(COLOR_CLASS_PATTERN);
		const colorObject = match && palette.find(({ slug }) => slug === match[1]);

		if (colorObject) {
			return colorObject.color;
		}
	}

	const styleMatch = style.match(/(?:^|;)\s*color:\s*([^;]+)/);

	return styleMatch ? styleMatch[1].trim() : undefined;
}

/**
 * Apply or remove the text color on the current selection.
 *
 * @param {Object} value - Rich text value.
 * @param {string|undefined} color - Selected color, empty to remove.
 * @param {Array<{ slug: string, color: string }>} palette - Theme color palette.
 * @returns {Object} Updated rich text value.
 */
function setColor(value, color, palette) {
	if (!color) {
		return removeFormat(value, FORMAT_NAME);
	}

	const colorObject = getColorObjectByColorValue(palette, color);
	const attributes = colorObject?.slug
		? { class: getColorClassName('color', colorObject.slug) }
		: { style: `color:${color}` };

	return applyFormat(value, { type: FORMAT_NAME, attributes });
}

/**
 * Toolbar icon: letter with a color indicator bar.
 *
 * @param {Object} props - Icon props.
 * @param {string|undefined} props.color - Indicator color.
 * @returns {JSX.Element} SVG icon.
 */
function TextColorIcon({ color }) {
	return (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			width="24"
			height="24"
			aria-hidden="true"
		>
			<path d="M12.9 4h-1.8L6.5 16h1.7l1.2-3.2h5.2l1.2 3.2h1.7L12.9 4Zm-2.9 7.3L12 5.9l2 5.4h-4Z" />
			<rect x="5" y="18" width="14" height="2.5" fill={color || 'currentColor'} />
		</svg>
	);
}

/**
 * Format edit component rendering the toolbar button and color popover.
 *
 * @param {Object} props - Format edit props.
 * @param {Object} props.value - Rich text value.
 * @param {Function} props.onChange - Rich text change handler.
 * @param {boolean} props.isActive - Whether the format is active.
 * @returns {JSX.Element|null} Toolbar controls.
 */
function TextColorEdit({ value, onChange, isActive }) {
	const [allowCustom, palette = EMPTY_ARRAY] = useSettings('color.custom', 'color.palette');
	const activeColor = useMemo(() => getActiveColor(value, palette), [value, palette]);

	if (!palette.length && !allowCustom && !isActive) {
		return null;
	}

	return (
		<BlockControls>
			<ToolbarGroup>
				<Dropdown
					popoverProps={{ placement: 'bottom-start' }}
					renderToggle={({ isOpen, onToggle }) => (
						<ToolbarButton
							icon={<TextColorIcon color={activeColor} />}
							label={__('Textfarbe', 'boilerplate-theme')}
							isPressed={isActive}
							aria-expanded={isOpen}
							onClick={onToggle}
						/>
					)}
					renderContent={() => (
						<div
							className="boilerplate-theme-text-color-popover"
							style={{ padding: '16px', width: '260px' }}
						>
							<ColorPalette
								colors={palette}
								value={activeColor}
								disableCustomColors={!allowCustom}
								onChange={(color) => onChange(setColor(value, color, palette))}
							/>
						</div>
					)}
				/>
			</ToolbarGroup>
		</BlockControls>
	);
}

registerFormatType(FORMAT_NAME, {
	title: __('Textfarbe', 'boilerplate-theme'),
	tagName: 'span',
	className: 'boilerplate-theme-text-color',
	attributes: {
		class: 'class',
		style: 'style',
	},
	edit: TextColorEdit,
});
