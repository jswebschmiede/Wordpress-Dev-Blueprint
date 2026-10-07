import { Button, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { EditorDeviceSwitcher, resolveDeviceValue, useEditorDevice } from '../_shared/index.js';
import { ResetIcon } from '../_shared/editor-device/icons.js';

/**
 * Gap limits in pixels. Keep in sync with ColumnsBlock (theme/src/Blocks/Blocks/ColumnsBlock.php).
 */
export const DEFAULT_GAP = 24;
export const MAX_GAP = 96;

/**
 * Column gap attribute per editor device. All are optional; an unset device
 * inherits via resolveDeviceValue() (larger devices only).
 *
 * @type {Record<'mobile'|'tablet'|'desktop', string>}
 */
const GAP_ATTRIBUTES = {
	mobile: 'gap',
	tablet: 'gapTablet',
	desktop: 'gapDesktop',
};

/**
 * Row gap attribute per editor device. Inherits like GAP_ATTRIBUTES; without
 * any row gap set, each device uses its resolved column gap.
 *
 * @type {Record<'mobile'|'tablet'|'desktop', string>}
 */
const ROW_GAP_ATTRIBUTES = {
	mobile: 'rowGap',
	tablet: 'rowGapTablet',
	desktop: 'rowGapDesktop',
};

/**
 * Clamps the gap to 0..MAX_GAP pixels.
 *
 * @param {number} value Raw gap in pixels.
 * @returns {number|null} Valid gap in pixels, or null for a missing or invalid value.
 */
function normalizeGap(value) {
	const gap = parseInt(value, 10);

	return Number.isNaN(gap) ? null : Math.max(0, Math.min(MAX_GAP, gap));
}

/**
 * Resolves the effective gap per device for one attribute set. An unset device
 * takes the next larger set device, else its fallback.
 * Mirrors ColumnsBlock::resolve_gaps().
 *
 * @param {Object} attributes Block attributes.
 * @param {Record<'mobile'|'tablet'|'desktop', string>} attributeMap Attribute name per device.
 * @param {{mobile: number, tablet: number, desktop: number}} fallback Gap per device when nothing is set.
 * @returns {{mobile: number, tablet: number, desktop: number}} Gaps in pixels.
 */
function resolveDeviceGaps(attributes, attributeMap, fallback) {
	const values = Object.fromEntries(
		Object.entries(attributeMap).map(([device, attribute]) => [
			device,
			normalizeGap(attributes[attribute]),
		]),
	);

	return {
		mobile: resolveDeviceValue(values, 'mobile') ?? fallback.mobile,
		tablet: resolveDeviceValue(values, 'tablet') ?? fallback.tablet,
		desktop: resolveDeviceValue(values, 'desktop') ?? fallback.desktop,
	};
}

/**
 * Resolves the effective column gap per device, defaulting to DEFAULT_GAP.
 *
 * @param {Object} attributes Block attributes.
 * @returns {{mobile: number, tablet: number, desktop: number}} Gaps in pixels.
 */
export function resolveGaps(attributes) {
	return resolveDeviceGaps(attributes, GAP_ATTRIBUTES, {
		mobile: DEFAULT_GAP,
		tablet: DEFAULT_GAP,
		desktop: DEFAULT_GAP,
	});
}

/**
 * Resolves the effective row gap per device, defaulting to the column gaps.
 *
 * @param {Object} attributes Block attributes.
 * @returns {{mobile: number, tablet: number, desktop: number}} Gaps in pixels.
 */
export function resolveRowGaps(attributes) {
	return resolveDeviceGaps(attributes, ROW_GAP_ATTRIBUTES, resolveGaps(attributes));
}

/**
 * Returns the inline custom properties for the resolved column and row gaps.
 *
 * All are always set so nested columns blocks do not inherit them.
 *
 * @param {Object} attributes Block attributes.
 * @returns {Object} Style object for the block wrapper.
 */
export function getGapStyle(attributes) {
	const gaps = resolveGaps(attributes);
	const rowGaps = resolveRowGaps(attributes);

	return {
		'--columns-gap': `${gaps.mobile}px`,
		'--columns-gap-tablet': `${gaps.tablet}px`,
		'--columns-gap-desktop': `${gaps.desktop}px`,
		'--columns-row-gap': `${rowGaps.mobile}px`,
		'--columns-row-gap-tablet': `${rowGaps.tablet}px`,
		'--columns-row-gap-desktop': `${rowGaps.desktop}px`,
	};
}

/**
 * Gap slider for the active editor device, with the shared device switcher.
 *
 * Uses the spacing control header styles (`sp__*`), injected by SpacingControl.
 *
 * @param {Object} props Component props.
 * @param {Object} props.attributes Block attributes.
 * @param {Function} props.setAttributes Updates block attributes.
 * @param {boolean} [props.isRowGap] Edits the row gap instead of the column gap.
 * @param {string} [props.label] Overrides the default label. Pass an already translated string.
 * @returns {JSX.Element} Gap control.
 */
export default function GapControl({
	attributes,
	setAttributes,
	isRowGap = false,
	label: customLabel,
}) {
	const { deviceKey } = useEditorDevice();
	const attribute = (isRowGap ? ROW_GAP_ATTRIBUTES : GAP_ATTRIBUTES)[deviceKey];
	const hasOwnValue = null !== normalizeGap(attributes[attribute]);
	const gaps = isRowGap ? resolveRowGaps(attributes) : resolveGaps(attributes);
	const label =
		customLabel ||
		(isRowGap
			? __('Abstand zwischen Zeilen (px)', 'boilerplate-theme')
			: __('Abstand zwischen Spalten (px)', 'boilerplate-theme'));

	return (
		<div className="sp__section">
			<div className="sp__header">
				<span className="sp__title">{label}</span>
				<EditorDeviceSwitcher />
				{hasOwnValue && (
					<Button
						className="sp__reset"
						icon={<ResetIcon />}
						label={__('Reset this device', 'boilerplate-theme')}
						showTooltip
						size="small"
						onClick={() => setAttributes({ [attribute]: undefined })}
					/>
				)}
			</div>
			<RangeControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={label}
				hideLabelFromVision
				value={gaps[deviceKey]}
				min={0}
				max={MAX_GAP}
				step={4}
				onChange={(value) => {
					const gap = normalizeGap(value);

					if (null !== gap) {
						setAttributes({ [attribute]: gap });
					}
				}}
			/>
		</div>
	);
}
