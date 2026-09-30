/**
 * Block Animations Extension for WordPress Block Editor
 *
 * Adds animation controls to all Gutenberg blocks via the Inspector Panel.
 * Uses WordPress hooks system to extend block functionality.
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl, ToggleControl } = wp.components;
const { Fragment } = wp.element;

/**
 * Blocks that must not receive animation controls.
 *
 * @type {string[]}
 */
const EXCLUDED_BLOCKS = ['core/freeform', 'core/html', 'core/shortcode', 'core/block'];

/**
 * Animation presets configuration
 */
const ANIMATION_PRESETS = [
    { label: __('— Keine Animation —', 'boilerplate-theme'), value: '' },
    { label: __('Fade In', 'boilerplate-theme'), value: 'fade-in' },
    { label: __('Fade In Up', 'boilerplate-theme'), value: 'fade-in-up' },
    { label: __('Fade In Down', 'boilerplate-theme'), value: 'fade-in-down' },
    { label: __('Fade In Left', 'boilerplate-theme'), value: 'fade-in-left' },
    { label: __('Fade In Right', 'boilerplate-theme'), value: 'fade-in-right' },
    { label: __('Slide In Up', 'boilerplate-theme'), value: 'slide-in-up' },
    { label: __('Slide In Down', 'boilerplate-theme'), value: 'slide-in-down' },
    { label: __('Slide In Left', 'boilerplate-theme'), value: 'slide-in-left' },
    { label: __('Slide In Right', 'boilerplate-theme'), value: 'slide-in-right' },
    { label: __('Zoom In', 'boilerplate-theme'), value: 'zoom-in' },
    { label: __('Zoom Out', 'boilerplate-theme'), value: 'zoom-out' },
    { label: __('Rotate In', 'boilerplate-theme'), value: 'rotate-in' },
    { label: __('Flip In X', 'boilerplate-theme'), value: 'flip-in-x' },
    { label: __('Flip In Y', 'boilerplate-theme'), value: 'flip-in-y' },
    { label: __('Bounce In', 'boilerplate-theme'), value: 'bounce-in' },
];

/**
 * Easing presets configuration
 */
const EASING_PRESETS = [
    { label: __('Power1 Out (Smooth)', 'boilerplate-theme'), value: 'power1.out' },
    { label: __('Power2 Out (Medium)', 'boilerplate-theme'), value: 'power2.out' },
    { label: __('Power3 Out (Strong)', 'boilerplate-theme'), value: 'power3.out' },
    { label: __('Power4 Out (Extra Strong)', 'boilerplate-theme'), value: 'power4.out' },
    { label: __('Back Out (Overshoot)', 'boilerplate-theme'), value: 'back.out' },
    { label: __('Elastic Out (Bouncy)', 'boilerplate-theme'), value: 'elastic.out(1, 0.5)' },
    { label: __('Bounce Out', 'boilerplate-theme'), value: 'bounce.out' },
    { label: __('Expo Out (Dramatic)', 'boilerplate-theme'), value: 'expo.out' },
    { label: __('Circ Out (Circular)', 'boilerplate-theme'), value: 'circ.out' },
    { label: __('Linear', 'boilerplate-theme'), value: 'none' },
];

/**
 * Add animation attributes to all blocks.
 *
 * @param {Object} settings - Block settings.
 * @param {string} name - Block name.
 * @returns {Object} Modified settings.
 */
function addAnimationAttributes(settings, name) {
    if (EXCLUDED_BLOCKS.includes(name)) {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            eiAnimation: {
                type: 'string',
                default: '',
            },
            eiAnimationDuration: {
                type: 'number',
                default: 0.8,
            },
            eiAnimationDelay: {
                type: 'number',
                default: 0,
            },
            eiAnimationEasing: {
                type: 'string',
                default: 'power2.out',
            },
            eiAnimationDistance: {
                type: 'number',
                default: 50,
            },
            eiAnimationOnce: {
                type: 'boolean',
                default: true,
            },
            eiAnimationThreshold: {
                type: 'number',
                default: 20,
            },
        },
    };
}

addFilter('blocks.registerBlockType', 'boilerplate-theme/animation-attributes', addAnimationAttributes);

/**
 * Add animation controls to the block inspector.
 */
const withAnimationControls = createHigherOrderComponent((BlockEdit) => {
    /**
     * Renders the block editor with animation inspector controls.
     *
     * @param {Object} props - Block edit props.
     * @returns {JSX.Element} Block edit element with inspector controls.
     */
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (EXCLUDED_BLOCKS.includes(name)) {
            return <BlockEdit {...props} />;
        }

        const {
            eiAnimation = '',
            eiAnimationDuration = 0.8,
            eiAnimationDelay = 0,
            eiAnimationEasing = 'power2.out',
            eiAnimationDistance = 50,
            eiAnimationOnce = true,
            eiAnimationThreshold = 20,
        } = attributes;

        const hasAnimation = eiAnimation !== '';

        return (
            <Fragment>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody title={__('Animation', 'boilerplate-theme')} initialOpen={false}>
                        <SelectControl
                            label={__('Animations-Typ', 'boilerplate-theme')}
                            value={eiAnimation}
                            options={ANIMATION_PRESETS}
                            onChange={(value) => setAttributes({ eiAnimation: value })}
                            help={__('Wähle eine Animation beim Scrollen', 'boilerplate-theme')}
                            __next40pxDefaultSize
                            __nextHasNoMarginBottom
                        />

                        {hasAnimation && (
                            <>
                                <RangeControl
                                    label={__('Dauer (Sekunden)', 'boilerplate-theme')}
                                    value={eiAnimationDuration}
                                    onChange={(value) =>
                                        setAttributes({
                                            eiAnimationDuration: value,
                                        })
                                    }
                                    min={0.1}
                                    max={3}
                                    step={0.1}
                                />

                                <RangeControl
                                    label={__('Verzögerung (Sekunden)', 'boilerplate-theme')}
                                    value={eiAnimationDelay}
                                    onChange={(value) =>
                                        setAttributes({
                                            eiAnimationDelay: value,
                                        })
                                    }
                                    min={0}
                                    max={2}
                                    step={0.1}
                                />

                                <SelectControl
                                    label={__('Easing', 'boilerplate-theme')}
                                    __next40pxDefaultSize
                                    __nextHasNoMarginBottom
                                    value={eiAnimationEasing}
                                    options={EASING_PRESETS}
                                    onChange={(value) =>
                                        setAttributes({
                                            eiAnimationEasing: value,
                                        })
                                    }
                                />

                                <RangeControl
                                    label={__('Distanz (px)', 'boilerplate-theme')}
                                    value={eiAnimationDistance}
                                    onChange={(value) =>
                                        setAttributes({
                                            eiAnimationDistance: value,
                                        })
                                    }
                                    min={10}
                                    max={200}
                                    step={10}
                                    help={__(
                                        'Bewegungsdistanz für Slide/Fade-Animationen',
                                        'boilerplate-theme',
                                    )}
                                />

                                <RangeControl
                                    label={__('Start-Position (%)', 'boilerplate-theme')}
                                    value={eiAnimationThreshold}
                                    onChange={(value) =>
                                        setAttributes({
                                            eiAnimationThreshold: value,
                                        })
                                    }
                                    min={0}
                                    max={100}
                                    step={5}
                                    help={__(
                                        'Prozent vom Viewport, bei dem die Animation startet',
                                        'boilerplate-theme',
                                    )}
                                />

                                <ToggleControl
                                    label={__('Nur einmal abspielen', 'boilerplate-theme')}
                                    checked={eiAnimationOnce}
                                    onChange={(value) => setAttributes({ eiAnimationOnce: value })}
                                    help={__(
                                        'Animation nur beim ersten Scrollen abspielen',
                                        'boilerplate-theme',
                                    )}
                                />
                            </>
                        )}
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withAnimationControls');

addFilter('editor.BlockEdit', 'boilerplate-theme/with-animation-controls', withAnimationControls);

/**
 * Add animation data attributes to the block wrapper in the editor.
 */
const withAnimationDataAttributes = createHigherOrderComponent((BlockListBlock) => {
    /**
     * Renders the block list item with an animation data attribute.
     *
     * @param {Object} props - Block list block props.
     * @returns {JSX.Element} Block list element.
     */
    return (props) => {
        const { attributes } = props;
        const { eiAnimation } = attributes;

        if (!eiAnimation) {
            return <BlockListBlock {...props} />;
        }

        const wrapperProps = {
            ...props.wrapperProps,
            'data-ei-animation': eiAnimation,
        };

        return <BlockListBlock {...props} wrapperProps={wrapperProps} />;
    };
}, 'withAnimationDataAttributes');

addFilter(
    'editor.BlockListBlock',
    'boilerplate-theme/with-animation-data-attributes',
    withAnimationDataAttributes,
);

export { ANIMATION_PRESETS, EASING_PRESETS };
