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
 * Mobile-first prefixes.
 *
 * Tablet starts at the `mobile` token (`settings.viewport.mobile`).
 * Desktop starts at the `tablet` token (`settings.viewport.tablet`).
 *
 * @type {Record<'mobile'|'tablet'|'desktop', string>}
 */
const DEVICE_PREFIX = {
    mobile: '',
    tablet: 'mobile:',
    desktop: 'tablet:',
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
 * Turns a spacing attribute into a class string for the block wrapper.
 *
 * Empty viewports are omitted so they inherit the next smaller viewport.
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

        for (const device of ['mobile', 'tablet', 'desktop']) {
            const sides = group[device];

            if (!sides || typeof sides !== 'object') {
                continue;
            }

            for (const side of ['top', 'right', 'bottom', 'left']) {
                const className = classForSide(property, device, side, sides[side]);

                if (className) {
                    classes.push(className);
                }
            }
        }
    }

    return classes.join(' ');
}
