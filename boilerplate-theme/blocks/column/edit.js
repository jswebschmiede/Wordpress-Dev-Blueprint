import { InnerBlocks, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

/**
 * Editor component for a single column.
 *
 * Empty columns show the "+" button appender like core/column; columns with
 * content use the default inline appender.
 *
 * @param {Object} props - Block editor props.
 * @param {string} props.clientId - Block client ID.
 * @returns {JSX.Element} Block editor markup.
 */
export default function Edit({ clientId }) {
	const hasChildBlocks = useSelect(
		(select) => select('core/block-editor').getBlockOrder(clientId).length > 0,
		[clientId],
	);
	const innerBlocksProps = useInnerBlocksProps(
		useBlockProps({ className: 'columns__column editor-outline-skip' }),
		{
			templateLock: false,
			renderAppender: hasChildBlocks ? undefined : InnerBlocks.ButtonBlockAppender,
		},
	);

	return <div {...innerBlocksProps} />;
}
