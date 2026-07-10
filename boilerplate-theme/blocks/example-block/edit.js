import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Editor component for the example block.
 *
 * @param {Object} props - Block editor props.
 * @param {Object} props.attributes - Current block attributes.
 * @param {string} props.attributes.title - Block title.
 * @param {string} props.attributes.description - Block description.
 * @param {string} props.attributes.url - Optional link URL.
 * @param {Function} props.setAttributes - Updates block attributes.
 * @returns {JSX.Element} Block editor markup.
 */
export default function Edit({ attributes, setAttributes }) {
    const { title, description, url } = attributes;
    const blockProps = useBlockProps({
        className: 'example-block not-prose',
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
