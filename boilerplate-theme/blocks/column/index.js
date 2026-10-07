import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import Edit from './edit';
import metadata from './block.json';

/**
 * Save component for the dynamic column block.
 *
 * @returns {JSX.Element} Inner blocks only; ColumnBlock renders the wrapper in PHP.
 */
function Save() {
	return <InnerBlocks.Content />;
}

registerBlockType(metadata.name, {
	edit: Edit,
	save: Save,
});
