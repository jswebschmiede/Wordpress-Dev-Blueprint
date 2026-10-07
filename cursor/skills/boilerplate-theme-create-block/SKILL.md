---
name: boilerplate-theme-create-block
description: Scaffolds and registers new Gutenberg blocks in the Boilerplate Theme. Use when creating a new block, adding block.json, implementing Inner Blocks, or registering blocks in the theme. Covers the project's dual-path workflow (blocks/ source → theme/blocks/ via copy), BlockManager, and WordPress 6.9+ standards (apiVersion 3).
---

# Boilerplate Theme – Blöcke erstellen

## When to use

Use this skill when:

- Creating a new Gutenberg block in the Boilerplate Theme
- Adding or modifying `block.json` for theme blocks
- Implementing Inner Blocks (container blocks)
- Registering blocks in BlockManager or wiring PHP render callbacks

## Project structure

All paths are relative to `boilerplate-theme/` (the package directory inside the repository root):

| Purpose                       | Path                                      |
| ----------------------------- | ----------------------------------------- |
| Block source (JS, block.json) | `blocks/<slug>/`                          |
| Shared editor controls        | `blocks/_shared/`                         |
| block.json copy target        | `theme/blocks/<slug>/block.json`          |
| PHP block class               | `theme/src/Blocks/Blocks/<Name>Block.php` |
| Twig template (Timber)        | `theme/views/blocks/<slug>.twig`          |
| JS entry (import all blocks)  | `javascript/blocks.js`                    |
| Block registration            | `theme/src/Blocks/BlockManager.php`       |
| Styles                        | `tailwind/custom/components/<slug>.css`   |

**Build flow:** `blocks/<slug>/block.json` is copied to `theme/blocks/<slug>/` by `development:copy-blocks`. All block JS is bundled into `theme/js/blocks.min.js` via `development:esbuild:blocks`. Do not run pnpm scripts; the user manages builds.

## Checklist: New block

Copy and track progress:

```
- [ ] 1. Create blocks/<slug>/ with block.json, index.js, edit.js
- [ ] 2. Add import to javascript/blocks.js
- [ ] 3. Create theme/src/Blocks/Blocks/<Name>Block.php
- [ ] 4. Add register_block() call in BlockManager::register_blocks()
- [ ] 5. Create theme/views/blocks/<slug>.twig
- [ ] 6. Run development:copy-blocks (user) after block.json changes
```

### 1. block.json

Required fields:

- `"$schema": "https://schemas.wp.org/trunk/block.json"`
- `"apiVersion": 3` (WordPress 6.9+)
- `"name": "boilerplate/<slug>"`
- `"category": "boilerplate-theme"`
- `"textdomain": "boilerplate-theme"`
- `"editorScript": "boilerplate-theme-blocks-editor"` (shared handle; do not use file paths)

Treat `name` as stable API; renaming breaks existing content. For markup changes, add `deprecated` versions.

### 2. index.js and edit.js

- Import `metadata` from `./block.json`, register with `registerBlockType(metadata.name, { edit, save })`
- Use `useBlockProps()` in edit; use `useBlockProps.save()` in save when wrapper props are needed
- For dynamic blocks: `save` returns `null` or `<InnerBlocks.Content />`; rendering happens in PHP

### 3. Inner Blocks (container blocks)

- Editor: `useInnerBlocksProps( useBlockProps(), { allowedBlocks, template, ... } )` or wrap `InnerBlocks` inside a `useBlockProps` wrapper
- Save: `useInnerBlocksProps.save( useBlockProps.save(), { ... } )` or `<InnerBlocks.Content />` when appropriate
- Only one `InnerBlocks` per block
- Changing wrapper structure can invalidate content; consider deprecations

### 4. PHP block class

- Implement `BlockInterface` with `render( array $attributes, string $content, ?\WP_Block $block = null ): string`
- Use `wp_parse_args()` for attribute defaults and prepare all view data in PHP (sanitized strings, `get_block_wrapper_attributes()`)
- Render with `Timber::compile( 'blocks/<slug>.twig', $context )`; import the Strauss-prefixed class `CompanyName\BoilerplateTheme\Timber\Timber`
- Optionally expose the template name through a filter (see `boilerplate_theme_example_block_template` in `ExampleBlock`)

### 5. Twig template

- Lives in `theme/views/blocks/<slug>.twig` (Timber view root is `theme/views/`)
- Markup only, no data lookups; Timber autoescape is off, so escape explicitly: `{{ value|esc_html }}`, `{{ value|esc_attr }}`, `{{ url|esc_url }}`
- Output `wrapper_attributes` from `get_block_wrapper_attributes()` unescaped (already escaped by WordPress)
- Translations: `{{ __('Text', 'boilerplate-theme') }}`

### 6. BlockManager registration

Add in `register_blocks()`:

```php
$this->register_block( '<slug>', new Blocks\<Name>Block() );
```

### 7. New @wordpress/\* imports

If the block uses a package not yet in the bundle, add it to `wpGlobals` in `node_scripts/build-blocks.js`.

### 8. Styles

- Create a new file in `tailwind/custom/components/<slug>.css`
- Add the styles to the file
- use tailwind 4 classes in the css file with @apply

## Shared editor controls

Reusable Inspector controls belong in `blocks/_shared/<name>/`, not inside a single block.

- Export the public control, hook, or class helper from `blocks/_shared/index.js`
- Import it from a block with `import { PageTreeSelectControl, ImageControl, PresetColorControl, getColorValue, SpacingControl, getSpacingClassName, EditorDeviceSwitcher, useEditorDevice, resolveDeviceValue } from '../_shared/index.js';`
- Keep other helpers in the control folder. Do not re-export them from the barrel
- `_shared` is editor-only. Do not import it from `view.js`
- Directories starting with `_` are ignored by `copy-blocks` and `build-block-views`

## Editor device and spacing

`EditorDeviceSwitcher` and `useEditorDevice` switch the global editor preview (`Desktop`, `Tablet`, `Mobile`) and resize the canvas. They do not print frontend CSS. Every control that uses them stays in sync with the toolbar view menu.

`SpacingControl` uses that switcher. It stores preset slugs only: `none`, `xs`, `sm`, `md`, `lg`, `xl`. No free pixel values. `none` is an explicit `0` so a larger viewport can override a smaller one. Reset clears only the active viewport, which then inherits the smaller one.

Spacing classes are desktop-first. Breakpoint literals in `tailwind/tailwind-theme.css` (`--breakpoint-mobile`, `--breakpoint-tablet`) must match `settings.viewport` in `theme.json`. A CSS variable cannot stand in for them, because media queries do not read `var()`.

- desktop: no prefix
- tablet: `max-tablet:` (below `settings.viewport.tablet`, default `782px`)
- mobile: `max-mobile:` (below `settings.viewport.mobile`, default `480px`)

An unset viewport inherits the next larger one. A class is printed only when that viewport differs from the larger one. `resolveDeviceValue()` and `Spacing::resolve_device_value()` stay in sync.

Copy-paste samples are in [reference.md](reference.md).

### Spacing in a block

1. Add the `spacing` object attribute to `block.json`.
2. In `edit.js`, render `SpacingControl` and pass `getSpacingClassName(attributes.spacing)` to `useBlockProps`.
3. In the PHP block class, append `Spacing::classes()` to the wrapper classes passed to `get_block_wrapper_attributes()`.

### View

Twig keeps `{{ wrapper_attributes }}`. Do not import `_shared` and do not add a second spacing element. Classes such as `pt-sm` and `max-tablet:pt-md` are already on the wrapper. `_shared` is editor-only.

New preset slugs must be added as complete class strings in `blocks/_shared/spacing/spacing-class-names.js` and in `theme/src/Blocks/Spacing.php` (or regenerate with `node node_scripts/write-spacing-php.js`), plus one `@utility` per side in `tailwind/custom/utilities.css` and the `theme.json` spacing preset. Tailwind does not see concatenated class names.

Do not add named `--spacing-*` tokens to `tailwind/tailwind-theme.css`. Tailwind resolves `w-*`, `min-w-*` and `max-w-*` from `--spacing-*` before `--container-*`, so a token such as `--spacing-xl` would turn the built-in `max-w-xl` (36rem) into the spacing value.

### Device switcher only

Import `useEditorDevice` and `EditorDeviceSwitcher`. Store that block's own values under `deviceKey` (`desktop`, `tablet`, `mobile`). The switcher does not emit CSS. The block's PHP or Twig outputs its own responsive markup.


## Editor hover outline

Content edited directly in the canvas gets a dotted outline on hover, so editors see which parts are editable. The styles live in `tailwind/tailwind-editor.css` (editor stylesheet only). Do not copy them into a block stylesheet.

| Class                     | Set on                                       | Effect                                                    |
| ------------------------- | -------------------------------------------- | --------------------------------------------------------- |
| `editor-outline-field`    | `RichText` / `PlainText` element             | Outline around the field                                  |
| `editor-outline-children` | Container block wrapper (`useBlockProps`)    | Outline around each inner block (drawn with `::after`)    |
| `editor-outline-skip`     | Structural inner block wrapper (e.g. column) | No outline for this block; its own children still get one |

- Add the classes in `edit.js` only. PHP and Twig must not output them.
- Focused elements are excluded; they keep the editor's selection outline.
- New blocks with inline fields or Inner Blocks use these classes instead of their own hover rules.

Samples are in [reference.md](reference.md).

## Viewport visibility

Viewport hide controls are global. New blocks do not add their own panel or attributes. `javascript/block-visibility.js` and `BlockVisibility` register `eiHideMobile`, `eiHideTablet`, and `eiHideDesktop` on every block except `core/freeform`, `core/html`, `core/shortcode`, and `core/block`.

Changing a width means the same literal in `settings.viewport` (`theme.json`) and in `--breakpoint-mobile` / `--breakpoint-tablet` (`tailwind/tailwind-theme.css`). A CSS variable is not enough, because media queries do not read `var()`.

## WordPress block standards (summary)

- **apiVersion 3:** Required for WP 6.9+; enables iframe editor compatibility
- **block.json schema:** [Block metadata](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/), [apiVersion / Iframe](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/)
- **Inner Blocks:** [Nested blocks guide](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/nested-blocks-inner-blocks/)
- **Block variations:** [Block variations](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/)

## Additional resources

- Minimal skeletons and templates: [reference.md](reference.md)
- [Timber v2](https://timber.github.io/docs/v2/) — theme views are Twig; import the Strauss-prefixed `CompanyName\BoilerplateTheme\Timber\Timber`. See also [template inheritance](https://timber.github.io/docs/v2/getting-started/template-inheritance-and-includes/) and [escaping](https://timber.github.io/docs/v2/guides/escaping/)
