import { Button, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { EditorDeviceSwitcher } from '../editor-device/editor-device-switcher.js';
import { LinkIcon, LinkOffIcon, ResetIcon } from '../editor-device/icons.js';
import { useEditorDevice } from '../editor-device/use-editor-device.js';
import {
    SPACING_PRESETS,
    SPACING_SIDES,
    getPresetIndex,
    getPresetSlug,
    getSideLabel,
} from './spacing-presets.js';

/**
 * Returns whether a side map contains a stored slug.
 *
 * @param {Object|undefined} sides Side values for one viewport.
 * @returns {boolean} True when any side is set.
 */
function hasSideValue(sides) {
    if (!sides || typeof sides !== 'object') {
        return false;
    }

    return SPACING_SIDES.some((side) => typeof sides[side] === 'string' && sides[side] !== '');
}

/**
 * Slug shown by the linked slider.
 *
 * @param {Object|undefined} sides Side values for the active viewport.
 * @returns {string} Shared slug, or an empty string when unset.
 */
function getLinkedSlug(sides) {
    if (!hasSideValue(sides)) {
        return '';
    }

    const values = SPACING_SIDES.map((side) => sides[side]).filter(
        (value) => typeof value === 'string' && value !== '',
    );
    const first = values[0];

    if (values.every((value) => value === first)) {
        return first;
    }

    return typeof sides.top === 'string' && sides.top !== '' ? sides.top : first;
}

/**
 * Copies a slug onto every side of the active viewport.
 *
 * @param {Object} group Property group.
 * @param {'mobile'|'tablet'|'desktop'} deviceKey Active viewport.
 * @param {string} slug Preset slug.
 * @returns {Object} Next property group.
 */
function setLinkedSlug(group, deviceKey, slug) {
    return {
        ...group,
        [deviceKey]: {
            top: slug,
            right: slug,
            bottom: slug,
            left: slug,
        },
    };
}

/**
 * Sets one side of the active viewport.
 *
 * @param {Object} group Property group.
 * @param {'mobile'|'tablet'|'desktop'} deviceKey Active viewport.
 * @param {'top'|'right'|'bottom'|'left'} side Box side.
 * @param {string} slug Preset slug.
 * @returns {Object} Next property group.
 */
function setSideSlug(group, deviceKey, side, slug) {
    return {
        ...group,
        [deviceKey]: {
            ...(group[deviceKey] || {}),
            [side]: slug,
        },
    };
}

/**
 * Preset slider for one spacing value.
 *
 * @param {Object} props Component props.
 * @param {string} props.label Accessible label. Pass an already translated string.
 * @param {string} props.slug Stored preset slug, or an empty string.
 * @param {Function} props.onChange Called with the next preset slug.
 * @returns {JSX.Element} Range control.
 */
function SpacingSlider({ label, slug, onChange }) {
    return (
        <RangeControl
            __next40pxDefaultSize
            __nextHasNoMarginBottom
            label={label}
            hideLabelFromVision
            min={0}
            max={SPACING_PRESETS.length - 1}
            step={1}
            withInputField={false}
            showTooltip
            value={slug ? getPresetIndex(slug) : null}
            renderTooltipContent={(index) => SPACING_PRESETS[index]?.label ?? ''}
            onChange={(index) => {
                if (typeof index !== 'number') {
                    return;
                }

                onChange(getPresetSlug(index));
            }}
        />
    );
}

/**
 * Padding or margin section for the active editor device.
 *
 * @param {Object} props Component props.
 * @param {string} props.label Section title. Pass an already translated string.
 * @param {Object|undefined} props.value Stored property group.
 * @param {Function} props.onChange Called with the next property group.
 * @returns {JSX.Element} Spacing section.
 */
function SpacingSection({ label, value, onChange }) {
    const { deviceKey } = useEditorDevice();
    const group = value && typeof value === 'object' ? value : {};
    const linked = group.linked !== false;
    const sides = group[deviceKey];
    const linkedSlug = getLinkedSlug(sides);

    /**
     * Replaces the property group.
     *
     * @param {Object} nextGroup Next property group.
     * @returns {void}
     */
    const updateGroup = (nextGroup) => {
        onChange(nextGroup);
    };

    return (
        <div className="boilerplate-spacing__section">
            <div className="boilerplate-spacing__header">
                <span className="boilerplate-spacing__title">{label}</span>
                <EditorDeviceSwitcher />
                <Button
                    className="boilerplate-spacing__link"
                    icon={linked ? <LinkIcon /> : <LinkOffIcon />}
                    label={
                        linked
                            ? __('Unlink sides', 'boilerplate-theme')
                            : __('Link sides', 'boilerplate-theme')
                    }
                    showTooltip
                    size="small"
                    isPressed={!linked}
                    onClick={() => {
                        if (linked) {
                            updateGroup({ ...group, linked: false });
                            return;
                        }

                        const nextGroup = { ...group, linked: true };

                        if (linkedSlug) {
                            updateGroup(setLinkedSlug(nextGroup, deviceKey, linkedSlug));
                            return;
                        }

                        updateGroup(nextGroup);
                    }}
                />
                {hasSideValue(sides) && (
                    <Button
                        className="boilerplate-spacing__reset"
                        icon={<ResetIcon />}
                        label={__('Reset this device', 'boilerplate-theme')}
                        showTooltip
                        size="small"
                        onClick={() => {
                            const nextGroup = { ...group };
                            delete nextGroup[deviceKey];
                            updateGroup(nextGroup);
                        }}
                    />
                )}
            </div>

            {linked ? (
                <SpacingSlider
                    label={label}
                    slug={linkedSlug}
                    onChange={(slug) => updateGroup(setLinkedSlug(group, deviceKey, slug))}
                />
            ) : (
                SPACING_SIDES.map((side) => (
                    <div className="boilerplate-spacing__row" key={side}>
                        <span
                            className={`boilerplate-spacing__side boilerplate-spacing__side--${side}`}
                            aria-hidden="true"
                        />
                        <SpacingSlider
                            label={getSideLabel(side)}
                            slug={typeof sides?.[side] === 'string' ? sides[side] : ''}
                            onChange={(slug) => updateGroup(setSideSlug(group, deviceKey, side, slug))}
                        />
                    </div>
                ))
            )}
        </div>
    );
}

/**
 * Inspector control for preset padding and margin on each editor device.
 *
 * @param {Object} props Component props.
 * @param {Object|undefined} props.value Stored spacing attribute.
 * @param {Function} props.onChange Called with the next spacing attribute.
 * @returns {JSX.Element} Padding and margin controls.
 */
export function SpacingControl({ value, onChange }) {
    const spacing = value && typeof value === 'object' ? value : {};

    return (
        <div className="boilerplate-spacing">
            <SpacingSection
                label={__('Paddings', 'boilerplate-theme')}
                value={spacing.padding}
                onChange={(padding) => onChange({ ...spacing, padding })}
            />
            <SpacingSection
                label={__('Margins', 'boilerplate-theme')}
                value={spacing.margin}
                onChange={(margin) => onChange({ ...spacing, margin })}
            />
        </div>
    );
}
