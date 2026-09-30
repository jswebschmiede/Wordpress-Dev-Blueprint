/**
 * Block visibility extension for the WordPress block editor.
 *
 * Adds viewport hide controls to every eligible block. Frontend classes are
 * printed in PHP; this file only stores the attributes and mirrors them on
 * the editor wrapper so the canvas media queries apply.
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { Fragment } = wp.element;

/**
 * Blocks that must not receive visibility controls.
 *
 * @type {string[]}
 */
const EXCLUDED_BLOCKS = ['core/freeform', 'core/html', 'core/shortcode', 'core/block'];

/**
 * Hide flags and the utility class each one prints.
 *
 * @type {Array<{ attribute: 'eiHideDesktop' | 'eiHideTablet' | 'eiHideMobile', className: string, label: string }>}
 */
const HIDE_OPTIONS = [
    {
        attribute: 'eiHideDesktop',
        className: 'ei-hide-desktop',
        label: __('Auf Desktop ausblenden', 'boilerplate-theme'),
    },
    {
        attribute: 'eiHideTablet',
        className: 'ei-hide-tablet',
        label: __('Auf Tablet ausblenden', 'boilerplate-theme'),
    },
    {
        attribute: 'eiHideMobile',
        className: 'ei-hide-mobile',
        label: __('Auf Mobilgeräten ausblenden', 'boilerplate-theme'),
    },
];

/**
 * Add visibility attributes to all eligible blocks.
 *
 * @param {Object} settings - Block settings.
 * @param {string} name - Block name.
 * @returns {Object} Modified settings.
 */
function addVisibilityAttributes(settings, name) {
    if (EXCLUDED_BLOCKS.includes(name)) {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            eiHideMobile: {
                type: 'boolean',
                default: false,
            },
            eiHideTablet: {
                type: 'boolean',
                default: false,
            },
            eiHideDesktop: {
                type: 'boolean',
                default: false,
            },
        },
    };
}

addFilter('blocks.registerBlockType', 'boilerplate-theme/visibility-attributes', addVisibilityAttributes);

/**
 * Add visibility controls to the block inspector.
 */
const withVisibilityControls = createHigherOrderComponent((BlockEdit) => {
    /**
     * Renders the block editor with viewport visibility controls.
     *
     * @param {Object} props - Block edit props.
     * @returns {JSX.Element} Block edit element with inspector controls.
     */
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (EXCLUDED_BLOCKS.includes(name)) {
            return <BlockEdit {...props} />;
        }

        return (
            <Fragment>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody title={__('Sichtbarkeit', 'boilerplate-theme')} initialOpen={false}>
                        {HIDE_OPTIONS.map(({ attribute, label }) => (
                            <ToggleControl
                                key={attribute}
                                label={label}
                                checked={Boolean(attributes[attribute])}
                                onChange={(value) => setAttributes({ [attribute]: value })}
                            />
                        ))}
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withVisibilityControls');

addFilter('editor.BlockEdit', 'boilerplate-theme/with-visibility-controls', withVisibilityControls);

/**
 * Add hide classes to the block wrapper in the editor.
 */
const withVisibilityClasses = createHigherOrderComponent((BlockListBlock) => {
    /**
     * Renders the block list item with viewport hide classes.
     *
     * @param {Object} props - Block list block props.
     * @returns {JSX.Element} Block list element.
     */
    return (props) => {
        if (EXCLUDED_BLOCKS.includes(props.name)) {
            return <BlockListBlock {...props} />;
        }

        const className = HIDE_OPTIONS.filter(({ attribute }) => props.attributes?.[attribute])
            .map(({ className: hideClass }) => hideClass)
            .join(' ');

        if (!className) {
            return <BlockListBlock {...props} />;
        }

        const wrapperProps = {
            ...props.wrapperProps,
            className: [props.wrapperProps?.className, className].filter(Boolean).join(' '),
        };

        return <BlockListBlock {...props} wrapperProps={wrapperProps} />;
    };
}, 'withVisibilityClasses');

addFilter('editor.BlockListBlock', 'boilerplate-theme/with-visibility-classes', withVisibilityClasses);
