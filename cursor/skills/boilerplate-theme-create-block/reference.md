# Reference: Block Skeletons

Minimal templates for new blocks. Replace `<slug>`, `<Name>`, and placeholders as needed.

## block.json (minimal)

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "boilerplate-theme/<slug>",
    "version": "1.0.0",
    "title": "Block Title",
    "category": "boilerplate-theme",
    "icon": "block-default",
    "description": "Block description.",
    "keywords": ["keyword1", "keyword2"],
    "textdomain": "boilerplate-theme",
    "supports": {
        "html": false,
        "anchor": true,
        "className": true
    },
    "attributes": {},
    "editorScript": "boilerplate-theme-blocks-editor"
}
```

## index.js (static block)

```javascript
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

function Save() {
    return null;
}

registerBlockType(metadata.name, {
    edit: Edit,
    save: Save,
});
```

## index.js (Inner Blocks container)

```javascript
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import Edit from './edit';
import metadata from './block.json';

function Save() {
    return <InnerBlocks.Content />;
}

registerBlockType(metadata.name, {
    edit: Edit,
    save: Save,
});
```

## edit.js (simple)

```javascript
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
    const blockProps = useBlockProps();
    return <div {...blockProps}>Block content</div>;
}
```

## edit.js (with InspectorControls)

```javascript
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Settings', 'boilerplate-theme')}>{/* Controls */}</PanelBody>
            </InspectorControls>
            <div {...blockProps}>Block content</div>
        </>
    );
}
```

## PHP block class

```php
<?php

declare( strict_types=1 );

namespace CompanyName\BoilerplateTheme\Blocks\Blocks;

use CompanyName\BoilerplateTheme\Blocks\BlockInterface;
use CompanyName\BoilerplateTheme\Timber\Timber;

defined( 'ABSPATH' ) || exit;

class <Name>Block implements BlockInterface {
	public function render( array $attributes, string $content, ?\WP_Block $block = null ): string {
		$attributes = wp_parse_args( $attributes, array( /* defaults */ ) );

		$context = array(
			'attributes'         => $attributes,
			'wrapper_attributes' => get_block_wrapper_attributes(
				array(
					'class' => '<slug>',
				)
			),
		);

		return (string) Timber::compile( 'blocks/<slug>.twig', $context );
	}
}
```

## Twig template (views/blocks/<slug>.twig)

```twig
{#
 # <Name> block. Data is prepared in Blocks\Blocks\<Name>Block::render().
 #}
<section {{ wrapper_attributes }}>
	{# Block markup, escape values explicitly: {{ attributes.title|esc_html }} #}
</section>
```

## javascript/blocks.js (add import)

```javascript
import '../blocks/<slug>/index.js';
```

## BlockManager::register_blocks() (add line)

```php
$this->register_block( '<slug>', new Blocks\<Name>Block() );
```

## Spacing attribute (block.json)

```json
"spacing": {
    "type": "object",
    "default": {}
}
```

Stored shape: `padding.mobile.top = "sm"`. Viewport keys are `mobile`, `tablet`, and `desktop`. Side keys are `top`, `right`, `bottom`, and `left`. Slugs are `none`, `xs`, `sm`, `md`, `lg`, and `xl`.

## edit.js (spacing)

```javascript
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { SpacingControl, getSpacingClassName } from '../_shared/index.js';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: ['<slug>', getSpacingClassName(attributes.spacing)].filter(Boolean).join(' '),
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Spacing', 'boilerplate-theme')}>
                    <SpacingControl
                        value={attributes.spacing}
                        onChange={(spacing) => setAttributes({ spacing })}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>Block content</div>
        </>
    );
}
```

## PHP wrapper classes (spacing)

Append the result to the classes passed into `get_block_wrapper_attributes()`. The Twig view stays `{{ wrapper_attributes }}`.

```php
use CompanyName\BoilerplateTheme\Blocks\Spacing;

$spacing = isset( $attributes['spacing'] ) && is_array( $attributes['spacing'] ) ? $attributes['spacing'] : array();
$spacing_classes = Spacing::classes( $spacing );

if ( '' !== $spacing_classes ) {
    $wrapper_classes[] = $spacing_classes;
}
```

## edit.js (device switcher only)

The switcher resizes the editor canvas. It does not add frontend classes. Read `deviceKey` and store this block's own values.

```javascript
import { EditorDeviceSwitcher, useEditorDevice } from '../_shared/index.js';

export default function Edit() {
    const { deviceKey } = useEditorDevice();

    return <EditorDeviceSwitcher />;
}
```

`deviceKey` is `desktop`, `tablet`, or `mobile`.

## Official documentation

- [Timber v2](https://timber.github.io/docs/v2/) (compile a block view with the prefixed `Timber` class; [escaping](https://timber.github.io/docs/v2/guides/escaping/))
- [Block metadata (block.json)](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/)
- [Block API versions / Iframe migration](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/)
- [Nested blocks (Inner Blocks)](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/nested-blocks-inner-blocks/)
- [Block supports](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/)
- [Block variations](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/)
