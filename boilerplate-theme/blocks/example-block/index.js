import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';

/**
 * Save component for the dynamic example block.
 *
 * @returns {null} No static HTML because PHP renders the block.
 */
function Save() {
    return null;
}

registerBlockType(metadata.name, {
    edit: Edit,
    save: Save,
});
