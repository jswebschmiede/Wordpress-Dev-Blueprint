/**
 * Block editor modifications
 *
 * This file is loaded only by the block editor. Use it to modify the block
 * editor via its APIs.
 *
 * The JavaScript code you place here will be processed by esbuild, and the
 * output file will be created at `../theme/js/block-editor.min.js` and
 * enqueued in `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */

/**
 * This import adds your front-end post title and Tailwind Typography classes
 * to the block editor. It also adds some helper classes so you can access the
 * post type when modifying the block editor’s appearance.
 */
import '@_tw/typography/block-editor-classes';

wp.domReady(() => {
	/**
	 * Block styles to register
	 */
	const blockStyles = [
		{ blockType: 'core/paragraph', name: 'lead', label: 'Lead' },
		{ blockType: 'core/image', name: 'rounded', label: 'Rounded' },
		{ blockType: 'core/media-text', name: 'rounded', label: 'Rounded' },
	];

	/**
	 * Register all block styles
	 */
	blockStyles.forEach(style => {
		wp.blocks.registerBlockStyle(style.blockType, {
			name: style.name,
			label: style.label,
		});
	});
});
