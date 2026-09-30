import { __ } from '@wordpress/i18n';

/**
 * Box sides stored on a spacing attribute, in CSS order.
 *
 * @type {readonly ['top', 'right', 'bottom', 'left']}
 */
export const SPACING_SIDES = ['top', 'right', 'bottom', 'left'];

/**
 * Fixed spacing steps. Slugs match the Tailwind tokens and theme.json presets.
 *
 * @type {Array<{ slug: string, label: string }>}
 */
export const SPACING_PRESETS = [
    { slug: 'none', label: __('None', 'boilerplate-theme') },
    { slug: 'xs', label: __('Extra Small', 'boilerplate-theme') },
    { slug: 'sm', label: __('Small', 'boilerplate-theme') },
    { slug: 'md', label: __('Medium', 'boilerplate-theme') },
    { slug: 'lg', label: __('Large', 'boilerplate-theme') },
    { slug: 'xl', label: __('Extra Large', 'boilerplate-theme') },
];

/**
 * Slider index for a stored slug. Unknown and empty values sit on the first step.
 *
 * @param {string|undefined} slug Stored preset slug.
 * @returns {number} Preset index.
 */
export function getPresetIndex(slug) {
    const index = SPACING_PRESETS.findIndex((preset) => preset.slug === slug);

    return index === -1 ? 0 : index;
}

/**
 * Preset slug for a slider index.
 *
 * @param {number} index Slider index.
 * @returns {string} Preset slug.
 */
export function getPresetSlug(index) {
    return SPACING_PRESETS[index]?.slug ?? SPACING_PRESETS[0].slug;
}

/**
 * Visible label for one side.
 *
 * @param {'top'|'right'|'bottom'|'left'} side Side key.
 * @returns {string} Translated side label.
 */
export function getSideLabel(side) {
    const labels = {
        top: __('Top', 'boilerplate-theme'),
        right: __('Right', 'boilerplate-theme'),
        bottom: __('Bottom', 'boilerplate-theme'),
        left: __('Left', 'boilerplate-theme'),
    };

    return labels[side] ?? side;
}
