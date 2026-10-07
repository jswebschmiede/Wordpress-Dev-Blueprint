import { resolveDeviceValue } from '../editor-device/resolve-device-value.js';
import { SPACING_CLASS_NAMES } from './spacing-class-names.js';

/**
 * Utility prefix for each box side.
 *
 * @type {Record<'padding'|'margin', Record<'top'|'right'|'bottom'|'left', string>>}
 */
const SIDE_UTILITY = {
	padding: { top: 'pt', right: 'pr', bottom: 'pb', left: 'pl' },
	margin: { top: 'mt', right: 'mr', bottom: 'mb', left: 'ml' },
};

/**
 * Desktop-first prefixes, so an unset device leaves larger devices untouched.
 *
 * Tablet ends below the `tablet` token (`settings.viewport.tablet`).
 * Mobile ends below the `mobile` token (`settings.viewport.mobile`).
 *
 * @type {Record<'mobile'|'tablet'|'desktop', string>}
 */
const DEVICE_PREFIX = {
	desktop: '',
	tablet: 'max-tablet:',
	mobile: 'max-mobile:',
};

const SPACING_CLASS_SET = new Set(SPACING_CLASS_NAMES);

/**
 * Returns a whitelist class name, or an empty string when the candidate is unknown.
 *
 * @param {string} className Candidate utility class.
 * @returns {string} Allowed class name.
 */
function matchSpacingClass(className) {
	return SPACING_CLASS_SET.has(className) ? className : '';
}

/**
 * Builds the utility class for one stored side.
 *
 * @param {'padding'|'margin'} property Spacing property.
 * @param {'mobile'|'tablet'|'desktop'} device Viewport key.
 * @param {'top'|'right'|'bottom'|'left'} side Box side.
 * @param {string} slug Preset slug.
 * @returns {string} Utility class, or an empty string.
 */
function classForSide(property, device, side, slug) {
	const utility = SIDE_UTILITY[property]?.[side];
	const prefix = DEVICE_PREFIX[device];

	if (!utility || prefix === undefined || typeof slug !== 'string' || slug === '') {
		return '';
	}

	return matchSpacingClass(`${prefix}${utility}-${slug}`);
}

/**
 * Returns whether a side value is a stored slug.
 *
 * @param {*} slug Candidate value.
 * @returns {boolean} True for a non-empty string.
 */
function isSlug(slug) {
	return typeof slug === 'string' && slug !== '';
}

/**
 * Turns a spacing attribute into a class string for the block wrapper.
 *
 * Each side resolves per viewport with resolveDeviceValue(): an empty viewport
 * takes the next larger set viewport. A viewport class is only printed when it
 * differs from the next larger viewport.
 *
 * @param {Object|undefined} spacing Spacing attribute.
 * @returns {string} Space-separated utility classes.
 */
export function getSpacingClassName(spacing) {
	if (!spacing || typeof spacing !== 'object') {
		return '';
	}

	const classes = [];

	for (const property of ['padding', 'margin']) {
		const group = spacing[property];

		if (!group || typeof group !== 'object') {
			continue;
		}

		for (const side of ['top', 'right', 'bottom', 'left']) {
			const values = {};

			for (const device of ['mobile', 'tablet', 'desktop']) {
				const sides = group[device];

				values[device] = sides && typeof sides === 'object' ? sides[side] : undefined;
			}

			let previous = '';

			for (const device of ['desktop', 'tablet', 'mobile']) {
				const slug = resolveDeviceValue(values, device, isSlug) ?? '';

				if (slug !== previous) {
					const className = classForSide(property, device, side, slug);

					if (className) {
						classes.push(className);
					}
				}

				previous = slug;
			}
		}
	}

	return classes.join(' ');
}
