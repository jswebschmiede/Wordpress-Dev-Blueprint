import { Button, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { ALL_LAYOUTS, DEFAULT_LAYOUT } from './layouts.js';

/**
 * Block icon shown in the placeholder header (dashicon, same as block.json).
 */
const PLACEHOLDER_ICON = 'columns';

/**
 * Start screen of a new columns block: picks one of the fixed layouts.
 *
 * Uses the stable Placeholder with the core variation picker class names, so
 * it looks like the core/columns picker without the experimental component.
 *
 * @param {Object} props Component props.
 * @param {Function} props.onSelect Called with the chosen layout; skipping passes DEFAULT_LAYOUT.
 * @returns {JSX.Element} Picker markup.
 */
export default function LayoutPicker({ onSelect }) {
	return (
		<Placeholder
			icon={PLACEHOLDER_ICON}
			label={__('Spalten', 'boilerplate-theme')}
			instructions={__('Wähle ein Layout als Ausgangspunkt.', 'boilerplate-theme')}
			className="block-editor-block-variation-picker has-many-variations"
		>
			<ul
				className="block-editor-block-variation-picker__variations"
				role="list"
				aria-label={__('Layouts', 'boilerplate-theme')}
			>
				{ALL_LAYOUTS.map((layout) => (
					<li key={layout.name}>
						<Button
							__next40pxDefaultSize
							variant="tertiary"
							icon={layout.icon}
							iconSize={48}
							label={layout.label}
							className="block-editor-block-variation-picker__variation"
							onClick={() => onSelect(layout)}
						/>
						<span
							className="block-editor-block-variation-picker__variation-label"
							aria-hidden="true"
						>
							{layout.label}
						</span>
					</li>
				))}
			</ul>
			<div className="block-editor-block-variation-picker__skip">
				<Button
					__next40pxDefaultSize
					variant="link"
					onClick={() => onSelect(DEFAULT_LAYOUT)}
				>
					{__('Überspringen', 'boilerplate-theme')}
				</Button>
			</div>
		</Placeholder>
	);
}
