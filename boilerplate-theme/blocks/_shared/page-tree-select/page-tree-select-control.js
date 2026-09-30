import { TreeSelect, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

import { buildPageTree, getSelectedPageId } from './page-tree-utils.js';

/**
 * Page picker as a hierarchical TreeSelect backed by published pages.
 *
 * @param {Object} props Component props.
 * @param {string} props.value Stored page permalink URL, or an empty string.
 * @param {Function} props.onChange Called with the new URL string or an empty string.
 * @param {string} props.label Control label. Pass an already translated string.
 * @param {string} props.noOptionLabel Label for clearing the selection. Pass an already translated string.
 * @returns {JSX.Element} Tree select or a loading spinner.
 */
export function PageTreeSelectControl({ value, onChange, label, noOptionLabel }) {
    const pages = useSelect(
        (select) =>
            select('core').getEntityRecords('postType', 'page', {
                per_page: -1,
                status: 'publish',
                orderby: 'menu_order',
                order: 'asc',
                _fields: 'id,parent,title,link,menu_order',
            }),
        [],
    );
    const isLoadingPages = pages === undefined;
    const availablePages = Array.isArray(pages) ? pages : [];
    const pageTree = useMemo(() => buildPageTree(availablePages), [availablePages]);
    const selectedPageId = useMemo(
        () => getSelectedPageId(availablePages, value),
        [availablePages, value],
    );
    const pagesById = useMemo(
        () =>
            new Map(
                availablePages.map((page) => [
                    String(page.id),
                    typeof page?.link === 'string' ? page.link : '',
                ]),
            ),
        [availablePages],
    );

    if (isLoadingPages) {
        return <Spinner />;
    }

    return (
        <TreeSelect
            __next40pxDefaultSize
            label={label}
            noOptionLabel={noOptionLabel}
            selectedId={selectedPageId}
            tree={pageTree}
            onChange={(newPageId) => {
                if (!newPageId) {
                    onChange('');
                    return;
                }

                onChange(pagesById.get(String(newPageId)) || '');
            }}
        />
    );
}
