import { InspectorControls, RichText, useBlockProps, useSettings } from '@wordpress/block-editor';
import {
    PanelBody,
    RangeControl,
    SelectControl,
    TextControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import {
    ImageControl,
    PageTreeSelectControl,
    PresetColorControl,
    SpacingControl,
    getColorValue,
    getSpacingClassName,
} from '../_shared/index.js';

/**
 * Media query for the desktop image. Keep in sync with settings.viewport.tablet in theme.json
 * and HeroBlock::DESKTOP_MEDIA.
 */
const DESKTOP_MEDIA = '(min-width: 782px)';

/**
 * Overlay values. Keep in sync with HeroBlock (theme/src/Blocks/Blocks/HeroBlock.php).
 */
const OVERLAY_STYLES = ['gradient', 'solid'];
const OVERLAY_OPACITY_DEFAULT = 70;

/**
 * Variants and color presets. Keep in sync with HeroBlock; the color presets match
 * the columns block (blocks/columns/edit.js).
 */
const VARIANTS = ['image', 'split'];
const BACKGROUNDS = ['none', 'primary', 'secondary', 'dark', 'light'];
const TEXT_COLORS = ['auto', 'dark', 'light'];

/**
 * Backgrounds that get light text when the text color is `auto`.
 */
const LIGHT_TEXT_BACKGROUNDS = ['primary', 'dark'];

/**
 * Resolves the effective text color variant. Mirrors HeroBlock::resolve_text_color().
 *
 * @param {string} background Background variant.
 * @param {string} textColor Text color setting (`auto`, `dark` or `light`).
 * @returns {string} `dark` or `light`.
 */
function resolveTextColor(background, textColor) {
    if ('dark' === textColor || 'light' === textColor) {
        return textColor;
    }

    return LIGHT_TEXT_BACKGROUNDS.includes(background) ? 'light' : 'dark';
}

/**
 * Builds the classes of the split variant (image right of the text), matching HeroBlock::render().
 *
 * @param {Object} attributes Block attributes.
 * @returns {string} Wrapper classes.
 */
function getSplitClassName(attributes) {
    const background = BACKGROUNDS.includes(attributes.background) ? attributes.background : 'none';
    const textColor = TEXT_COLORS.includes(attributes.textColor) ? attributes.textColor : 'auto';

    return [
        'hero--split',
        'none' !== background ? `hero--bg-${background}` : '',
        `hero--text-${resolveTextColor(background, textColor)}`,
    ]
        .filter(Boolean)
        .join(' ');
}

/**
 * Returns the overlay opacity clamped to 0–100.
 *
 * @param {*} value Raw attribute value.
 * @returns {number} Opacity in percent.
 */
function getOverlayOpacity(value) {
    const number = parseInt(value, 10);

    return Number.isNaN(number) ? OVERLAY_OPACITY_DEFAULT : Math.min(100, Math.max(0, number));
}

/**
 * Builds the overlay classes and custom properties for the wrapper, matching HeroBlock::render().
 *
 * @param {Object} attributes Block attributes.
 * @returns {{className: string, style: Object}} Wrapper classes and React style object.
 */
function getHeroOverlay(attributes) {
    const color = getColorValue(attributes.overlayColor, attributes.customOverlayColor);

    if (!color) {
        return { className: '', style: {} };
    }

    const overlayStyle = OVERLAY_STYLES.includes(attributes.overlayStyle)
        ? attributes.overlayStyle
        : 'gradient';

    return {
        className: ['hero--overlay', 'gradient' === overlayStyle ? 'hero--overlay-gradient' : '']
            .filter(Boolean)
            .join(' '),
        style: {
            '--hero-overlay-color': color,
            '--hero-overlay-opacity': `${getOverlayOpacity(attributes.overlayOpacity)}%`,
        },
    };
}

/**
 * Editor component for the hero block.
 *
 * @param {Object} props - Block editor props.
 * @param {Object} props.attributes - Current block attributes.
 * @param {string} props.attributes.title - Heading text, may contain <br>.
 * @param {string} props.attributes.text - Intro text, may contain <br>.
 * @param {string} props.attributes.buttonLabel - Button label.
 * @param {string} props.attributes.buttonUrl - Internal page permalink.
 * @param {number} props.attributes.imageDesktopId - Desktop image attachment ID.
 * @param {number} props.attributes.imageMobileId - Mobile image attachment ID.
 * @param {string} props.attributes.overlayColor - Overlay palette slug.
 * @param {string} props.attributes.customOverlayColor - Overlay custom hex color.
 * @param {number} props.attributes.overlayOpacity - Overlay opacity in percent.
 * @param {string} props.attributes.overlayStyle - Overlay `gradient` (left to right) or `solid`.
 * @param {string} props.attributes.variant - `image` (background image) or `split` (image right of the text).
 * @param {string} props.attributes.background - Background variant of the split variant.
 * @param {string} props.attributes.textColor - Text color setting of the split variant.
 * @param {Object} props.attributes.spacing - Preset padding and margin per device.
 * @param {Function} props.setAttributes - Updates block attributes.
 * @returns {JSX.Element} Block editor markup.
 */
export default function Edit({ attributes, setAttributes }) {
    const { title, text, buttonLabel, buttonUrl, imageDesktopId, imageMobileId, spacing } =
        attributes;
    const [palette = []] = useSettings('color.palette');
    const variant = VARIANTS.includes(attributes.variant) ? attributes.variant : 'image';
    const isSplit = 'split' === variant;
    // The overlay only applies to the background image variant.
    const overlay = isSplit ? { className: '', style: {} } : getHeroOverlay(attributes);
    const blockProps = useBlockProps({
        className: [
            'hero not-prose',
            isSplit ? getSplitClassName(attributes) : overlay.className,
            getSpacingClassName(spacing),
        ]
            .filter(Boolean)
            .join(' '),
        style: overlay.style,
    });
    const { desktopMedia, mobileMedia } = useSelect(
        (select) => {
            const { getMedia } = select('core');

            return {
                desktopMedia: imageDesktopId ? getMedia(imageDesktopId) : undefined,
                mobileMedia: imageMobileId ? getMedia(imageMobileId) : undefined,
            };
        },
        [imageDesktopId, imageMobileId],
    );

    // A missing image falls back to the other one, matching HeroBlock::render().
    const desktopSrc = desktopMedia?.source_url || mobileMedia?.source_url || '';
    const mobileSrc = mobileMedia?.source_url || desktopMedia?.source_url || '';
    const media = (desktopSrc || mobileSrc) && (
        <picture className="hero__media">
            <source media={DESKTOP_MEDIA} srcSet={desktopSrc} />
            <img className="hero__image" src={mobileSrc} alt="" />
        </picture>
    );

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Darstellung', 'boilerplate-theme')}>
                    <ToggleGroupControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        isBlock
                        label={__('Bild', 'boilerplate-theme')}
                        value={variant}
                        onChange={(value) => setAttributes({ variant: value })}
                    >
                        <ToggleGroupControlOption
                            value="image"
                            label={__('Hintergrundbild', 'boilerplate-theme')}
                        />
                        <ToggleGroupControlOption
                            value="split"
                            label={__('Bild rechts', 'boilerplate-theme')}
                        />
                    </ToggleGroupControl>
                </PanelBody>
                {isSplit && (
                    <PanelBody title={__('Farben', 'boilerplate-theme')}>
                        <SelectControl
                            __next40pxDefaultSize
                            __nextHasNoMarginBottom
                            label={__('Hintergrundfarbe', 'boilerplate-theme')}
                            value={
                                BACKGROUNDS.includes(attributes.background)
                                    ? attributes.background
                                    : 'none'
                            }
                            options={[
                                { label: __('Kein', 'boilerplate-theme'), value: 'none' },
                                { label: __('Primär', 'boilerplate-theme'), value: 'primary' },
                                { label: __('Sekundär', 'boilerplate-theme'), value: 'secondary' },
                                { label: __('Dunkel', 'boilerplate-theme'), value: 'dark' },
                                { label: __('Hell', 'boilerplate-theme'), value: 'light' },
                            ]}
                            onChange={(value) => setAttributes({ background: value })}
                        />
                        <SelectControl
                            __next40pxDefaultSize
                            __nextHasNoMarginBottom
                            label={__('Textfarbe', 'boilerplate-theme')}
                            help={__(
                                'Automatisch: hell auf Primär und Dunkel, sonst dunkel.',
                                'boilerplate-theme',
                            )}
                            value={
                                TEXT_COLORS.includes(attributes.textColor)
                                    ? attributes.textColor
                                    : 'auto'
                            }
                            options={[
                                { label: __('Automatisch', 'boilerplate-theme'), value: 'auto' },
                                { label: __('Dunkel', 'boilerplate-theme'), value: 'dark' },
                                { label: __('Hell', 'boilerplate-theme'), value: 'light' },
                            ]}
                            onChange={(value) => setAttributes({ textColor: value })}
                        />
                    </PanelBody>
                )}
                <PanelBody title={__('Desktop Bild', 'boilerplate-theme')}>
                    <ImageControl
                        label={__('Desktop Bild', 'boilerplate-theme')}
                        imageId={imageDesktopId}
                        media={desktopMedia}
                        onChange={(id) => setAttributes({ imageDesktopId: id })}
                    />
                </PanelBody>
                <PanelBody title={__('Mobile Bild', 'boilerplate-theme')}>
                    <ImageControl
                        label={__('Mobile Bild', 'boilerplate-theme')}
                        imageId={imageMobileId}
                        media={mobileMedia}
                        onChange={(id) => setAttributes({ imageMobileId: id })}
                    />
                </PanelBody>
                {!isSplit && (
                    <PanelBody title={__('Overlay', 'boilerplate-theme')} initialOpen={false}>
                        <PresetColorControl
                            label={__('Farbe', 'boilerplate-theme')}
                            palette={palette}
                            slug={attributes.overlayColor}
                            custom={attributes.customOverlayColor}
                            enableAlpha={false}
                            onChange={({ slug, custom }) =>
                                setAttributes({ overlayColor: slug, customOverlayColor: custom })
                            }
                        />
                        {overlay.className && (
                            <>
                                <ToggleGroupControl
                                    __next40pxDefaultSize
                                    __nextHasNoMarginBottom
                                    isBlock
                                    label={__('Darstellung', 'boilerplate-theme')}
                                    value={
                                        OVERLAY_STYLES.includes(attributes.overlayStyle)
                                            ? attributes.overlayStyle
                                            : 'gradient'
                                    }
                                    onChange={(value) => setAttributes({ overlayStyle: value })}
                                >
                                    <ToggleGroupControlOption
                                        value="gradient"
                                        label={__('Verlauf', 'boilerplate-theme')}
                                    />
                                    <ToggleGroupControlOption
                                        value="solid"
                                        label={__('Fläche', 'boilerplate-theme')}
                                    />
                                </ToggleGroupControl>
                                <RangeControl
                                    __next40pxDefaultSize
                                    __nextHasNoMarginBottom
                                    label={__('Deckkraft (%)', 'boilerplate-theme')}
                                    value={getOverlayOpacity(attributes.overlayOpacity)}
                                    min={0}
                                    max={100}
                                    allowReset
                                    resetFallbackValue={OVERLAY_OPACITY_DEFAULT}
                                    onChange={(value) =>
                                        setAttributes({
                                            overlayOpacity: value ?? OVERLAY_OPACITY_DEFAULT,
                                        })
                                    }
                                />
                            </>
                        )}
                    </PanelBody>
                )}
                <PanelBody title={__('Button', 'boilerplate-theme')}>
                    <TextControl
                        __next40pxDefaultSize
                        label={__('Button Text', 'boilerplate-theme')}
                        value={buttonLabel}
                        onChange={(value) => setAttributes({ buttonLabel: value })}
                    />
                    <PageTreeSelectControl
                        label={__('Link Ziel', 'boilerplate-theme')}
                        noOptionLabel={__('Kein Link', 'boilerplate-theme')}
                        value={buttonUrl}
                        onChange={(value) => setAttributes({ buttonUrl: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Abstand', 'boilerplate-theme')} initialOpen={false}>
                    <SpacingControl
                        value={spacing}
                        onChange={(value) => setAttributes({ spacing: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <section {...blockProps}>
                {!isSplit && media}
                <div className="hero__inner">
                    <div className="hero__content">
                        <RichText
                            tagName="h1"
                            className="hero__title editor-outline-field"
                            value={title}
                            allowedFormats={[]}
                            placeholder={__('Überschrift…', 'boilerplate-theme')}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                        <RichText
                            tagName="p"
                            className="hero__text editor-outline-field"
                            value={text}
                            allowedFormats={[]}
                            placeholder={__('Text…', 'boilerplate-theme')}
                            onChange={(value) => setAttributes({ text: value })}
                        />
                        {buttonLabel && <span className="btn btn-primary">{buttonLabel}</span>}
                    </div>
                    {isSplit && media}
                </div>
            </section>
        </>
    );
}
