import {
	InspectorControls,
	store as blockEditorStore,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';
import {
	BaseControl,
	Button,
	Flex,
	PanelBody,
	RangeControl,
	SelectControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { Fragment, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { SpacingControl, getSpacingClassName } from '../_shared/index.js';
import GapControl, { getGapStyle } from './gap-control.js';
import LayoutPicker from './layout-picker.js';
import { LAYOUTS, resolveLayout } from './layouts.js';

/**
 * Allowed values. Keep in sync with ColumnsBlock (theme/src/Blocks/Blocks/ColumnsBlock.php).
 */
const MAX_COLUMNS = 4;
const MAX_ROWS = 6;
const BACKGROUNDS = ['none', 'primary', 'secondary', 'dark', 'light'];
const TEXT_COLORS = ['auto', 'dark', 'light'];

/**
 * Backgrounds that get light text when the text color is `auto`.
 */
const LIGHT_TEXT_BACKGROUNDS = ['primary', 'dark'];

const ALLOWED_BLOCKS = ['boilerplate/column'];

/**
 * Resolves the effective text color variant. Mirrors ColumnsBlock::resolve_text_color().
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
 * Clamps the column count to 1..MAX_COLUMNS.
 *
 * @param {number} value Raw column count.
 * @returns {number} Valid column count.
 */
function clampColumns(value) {
	const count = parseInt(value, 10);

	return Number.isNaN(count) ? 2 : Math.max(1, Math.min(MAX_COLUMNS, count));
}

/**
 * Clamps the row count to 1..MAX_ROWS.
 *
 * @param {number} value Raw row count.
 * @returns {number} Valid row count.
 */
function clampRows(value) {
	const count = parseInt(value, 10);

	return Number.isNaN(count) ? 1 : Math.max(1, Math.min(MAX_ROWS, count));
}

/**
 * Start screen of a new columns block without columns.
 *
 * @param {Object} props - Component props.
 * @param {Function} props.setAttributes - Updates block attributes.
 * @param {string} props.clientId - Block client ID.
 * @returns {JSX.Element} Layout picker markup.
 */
function ColumnsPlaceholder({ setAttributes, clientId }) {
	const { replaceInnerBlocks } = useDispatch(blockEditorStore);
	const blockProps = useBlockProps();

	/**
	 * Applies a layout from the picker and creates its empty columns.
	 *
	 * @param {Object} selected - Layout from layouts.js.
	 * @param {number} selected.columns - Column count.
	 * @param {string} selected.name - Layout name.
	 */
	const onSelect = ({ columns, name }) => {
		setAttributes({ columns, rows: 1, layout: name });
		replaceInnerBlocks(
			clientId,
			Array.from({ length: columns }, () => createBlock('boilerplate/column')),
			true,
		);
	};

	return (
		<div {...blockProps}>
			<LayoutPicker onSelect={onSelect} />
		</div>
	);
}

/**
 * Editor component for a columns block with columns.
 *
 * @param {Object} props - Block editor props.
 * @param {Object} props.attributes - Current block attributes.
 * @param {string} props.attributes.heading - Optional heading above the columns, line breaks are kept.
 * @param {number} props.attributes.columns - Column count per row (1-4).
 * @param {number} props.attributes.rows - Row count (1-6); the block holds columns × rows column blocks.
 * @param {string} props.attributes.layout - Fixed column widths, see layouts.js.
 * @param {number} [props.attributes.gap] - Mobile gap in pixels, inherits when unset (see gap-control.js).
 * @param {number} [props.attributes.gapTablet] - Gap from the `mobile` breakpoint, inherits when unset.
 * @param {number} [props.attributes.gapDesktop] - Gap from the `tablet` breakpoint, inherits when unset.
 * @param {number} [props.attributes.rowGap] - Mobile row gap in pixels, falls back to the column gap.
 * @param {number} [props.attributes.rowGapTablet] - Row gap from the `mobile` breakpoint.
 * @param {number} [props.attributes.rowGapDesktop] - Row gap from the `tablet` breakpoint.
 * @param {boolean} props.attributes.verticalCenter - Centers the column contents vertically.
 * @param {string} props.attributes.background - Background variant.
 * @param {string} props.attributes.textColor - Text color setting.
 * @param {Object} props.attributes.spacing - Preset padding and margin per device.
 * @param {Function} props.setAttributes - Updates block attributes.
 * @param {string} props.clientId - Block client ID.
 * @returns {JSX.Element} Block editor markup.
 */
function ColumnsEdit({ attributes, setAttributes, clientId }) {
	const { heading, verticalCenter, spacing } = attributes;
	const columns = clampColumns(attributes.columns);
	const rows = clampRows(attributes.rows);
	const columnCount = columns * rows;
	const layout = resolveLayout(columns, attributes.layout);
	const columnLayouts = LAYOUTS[columns];
	const background = BACKGROUNDS.includes(attributes.background) ? attributes.background : 'none';
	const textColor = TEXT_COLORS.includes(attributes.textColor) ? attributes.textColor : 'auto';

	// Same output as `heading|esc_html|nl2br` in columns.twig.
	const headingLines = heading?.trim() ? heading.trim().split('\n') : [];

	const innerBlocks = useSelect(
		(select) => select(blockEditorStore).getBlocks(clientId),
		[clientId],
	);
	const { replaceInnerBlocks } = useDispatch(blockEditorStore);

	// Keeps the number of column blocks in sync with columns × rows: appends empty
	// columns or removes trailing ones (their content is lost, like core/columns).
	// The grid wraps the column blocks into rows.
	useEffect(() => {
		if (innerBlocks.length === columnCount) {
			return;
		}

		const nextBlocks =
			innerBlocks.length > columnCount
				? innerBlocks.slice(0, columnCount)
				: [
						...innerBlocks,
						...Array.from({ length: columnCount - innerBlocks.length }, () =>
							createBlock('boilerplate/column'),
						),
					];

		replaceInnerBlocks(clientId, nextBlocks, false);
	}, [columnCount, innerBlocks, clientId, replaceInnerBlocks]);

	const blockProps = useBlockProps({
		className: [
			'columns',
			'editor-outline-children',
			`columns--count-${columns}`,
			layout.isEqual ? '' : `columns--layout-${layout.name}`,
			verticalCenter ? 'columns--align-center' : '',
			'none' !== background ? `columns--bg-${background}` : '',
			`columns--text-${resolveTextColor(background, textColor)}`,
			getSpacingClassName(spacing),
		]
			.filter(Boolean)
			.join(' '),
		style: getGapStyle(attributes),
	});
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'columns__grid' },
		{
			allowedBlocks: ALLOWED_BLOCKS,
			orientation: 'horizontal',
			renderAppender: false,
			templateLock: 'insert',
		},
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Spalten', 'boilerplate-theme')}>
					<TextareaControl
						__nextHasNoMarginBottom
						label={__('Überschrift', 'boilerplate-theme')}
						rows={2}
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
					/>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={__('Anzahl Spalten', 'boilerplate-theme')}
						help={__(
							'Pro Zeile. Bei mehreren Zeilen werden die Spalten neu verteilt; beim Reduzieren wird der Inhalt der letzten Spalten gelöscht.',
							'boilerplate-theme',
						)}
						value={columns}
						min={1}
						max={MAX_COLUMNS}
						onChange={(value) =>
							setAttributes({ columns: clampColumns(value), layout: '' })
						}
					/>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={__('Anzahl Zeilen', 'boilerplate-theme')}
						help={__(
							'Beim Reduzieren wird der Inhalt der letzten Spalten gelöscht.',
							'boilerplate-theme',
						)}
						value={rows}
						min={1}
						max={MAX_ROWS}
						onChange={(value) => setAttributes({ rows: clampRows(value) })}
					/>
					{columnLayouts.length > 1 && (
						<BaseControl __nextHasNoMarginBottom>
							<BaseControl.VisualLabel>
								{__('Spaltenbreiten', 'boilerplate-theme')}
							</BaseControl.VisualLabel>
							<Flex
								justify="flex-start"
								gap={2}
								wrap
								role="group"
								aria-label={__('Spaltenbreiten', 'boilerplate-theme')}
							>
								{columnLayouts.map((option) => (
									<Button
										key={option.name}
										icon={option.icon}
										iconSize={48}
										label={option.label}
										showTooltip
										isPressed={option.name === layout.name}
										onClick={() => setAttributes({ layout: option.name })}
									/>
								))}
							</Flex>
						</BaseControl>
					)}
					<GapControl attributes={attributes} setAttributes={setAttributes} />
					<GapControl attributes={attributes} setAttributes={setAttributes} isRowGap />
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Vertikal zentrieren', 'boilerplate-theme')}
						checked={!!verticalCenter}
						onChange={(value) => setAttributes({ verticalCenter: value })}
					/>
				</PanelBody>
				<PanelBody title={__('Farben', 'boilerplate-theme')}>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={__('Hintergrundfarbe', 'boilerplate-theme')}
						value={background}
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
						value={textColor}
						options={[
							{ label: __('Automatisch', 'boilerplate-theme'), value: 'auto' },
							{ label: __('Dunkel', 'boilerplate-theme'), value: 'dark' },
							{ label: __('Hell', 'boilerplate-theme'), value: 'light' },
						]}
						onChange={(value) => setAttributes({ textColor: value })}
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
				{headingLines.length > 0 && (
					<div className="columns__heading-wrapper">
						<h2 className="columns__heading">
							{headingLines.map((line, index) => (
								<Fragment key={index}>
									{index > 0 && <br />}
									{line}
								</Fragment>
							))}
						</h2>
					</div>
				)}
				<div {...innerBlocksProps} />
			</section>
		</>
	);
}

/**
 * Editor component for the columns block. A new block has no columns yet and
 * shows the layout picker; existing blocks render the columns editor.
 *
 * @param {Object} props - Block editor props.
 * @param {string} props.clientId - Block client ID.
 * @returns {JSX.Element} Block editor markup.
 */
export default function Edit(props) {
	const { clientId } = props;
	const hasColumns = useSelect(
		(select) => select(blockEditorStore).getBlockCount(clientId) > 0,
		[clientId],
	);

	return hasColumns ? <ColumnsEdit {...props} /> : <ColumnsPlaceholder {...props} />;
}
