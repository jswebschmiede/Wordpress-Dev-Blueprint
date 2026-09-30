import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { SpacingControl, getSpacingClassName } from '../_shared/index.js';

/**
 * Editor component for the example block.
 *
 * @param {Object} props - Block editor props.
 * @param {Object} props.attributes - Current block attributes.
 * @param {string} props.attributes.title - Block title.
 * @param {string} props.attributes.description - Block description.
 * @param {string} props.attributes.url - Optional link URL.
 * @param {Object} props.attributes.spacing - Preset padding and margin per device.
 * @param {Function} props.setAttributes - Updates block attributes.
 * @returns {JSX.Element} Block editor markup.
 */
export default function Edit({ attributes, setAttributes }) {
    const { title, description, url, spacing } = attributes;
    const blockProps = useBlockProps({
        className: ['example-block not-prose', getSpacingClassName(spacing)].filter(Boolean).join(' '),
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Example Block Settings', 'boilerplate-theme')}>
                    <TextControl
                        label={__('Title', 'boilerplate-theme')}
                        value={title}
                        onChange={(value) => setAttributes({ title: value })}
                    />
                    <TextareaControl
                        label={__('Description', 'boilerplate-theme')}
                        value={description}
                        onChange={(value) => setAttributes({ description: value })}
                    />
                    <TextControl
                        label={__('URL', 'boilerplate-theme')}
                        type="url"
                        value={url}
                        onChange={(value) => setAttributes({ url: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Spacing', 'boilerplate-theme')}>
                    <SpacingControl
                        value={spacing}
                        onChange={(value) => setAttributes({ spacing: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <section {...blockProps}>
                <div className="example-block__inner">
                    {title && <h2 className="example-block__title">{title}</h2>}
                    {description && (
                        <p className="example-block__description">{description}</p>
                    )}
                    {url && (
                        <span className="example-block__link">
                            {__('Read more', 'boilerplate-theme')}
                        </span>
                    )}
                </div>
            </section>
        </>
    );
}
