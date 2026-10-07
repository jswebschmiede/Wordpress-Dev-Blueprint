import { TreeSelect, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

import { buildPostTree, getSelectedPageId } from './page-tree-utils.js';

/**
 * Post types offered by default. Pages only; pass `postTypes` to include others.
 */
const DEFAULT_POST_TYPES = ['page'];

const RECORD_QUERY = {
    per_page: -1,
    status: 'publish',
    orderby: 'menu_order',
    order: 'asc',
    _fields: 'id,parent,title,link,menu_order',
};

/**
 * Page picker as a hierarchical TreeSelect backed by published pages and custom post types.
 *
 * @param {Object} props Component props.
 * @param {string} props.value Stored permalink URL, or an empty string.
 * @param {Function} props.onChange Called with the new URL string or an empty string.
 * @param {string} props.label Control label. Pass an already translated string.
 * @param {string} props.noOptionLabel Label for clearing the selection. Pass an already translated string.
 * @param {Array<string>} [props.postTypes] Post type slugs to list. Defaults to pages.
 * @returns {JSX.Element} Tree select or a loading spinner.
 */
export function PageTreeSelectControl({
    value,
    onChange,
    label,
    noOptionLabel,
    postTypes = DEFAULT_POST_TYPES,
}) {
    const postTypesKey = postTypes.join(',');
    const { recordsByType, labelsByType, isLoading } = useSelect(
        (select) => {
            const core = select('core');
            const records = {};
            const labels = {};
            let loading = false;

            postTypes.forEach((postType) => {
                const args = ['postType', postType, RECORD_QUERY];
                records[postType] = core.getEntityRecords(...args);
                labels[postType] = core.getPostType(postType)?.labels?.singular_name || '';

                if (!core.hasFinishedResolution('getEntityRecords', args)) {
                    loading = true;
                }
            });

            return { recordsByType: records, labelsByType: labels, isLoading: loading };
        },
        [postTypesKey],
    );

    const availablePosts = useMemo(
        () =>
            postTypes.flatMap((postType) =>
                Array.isArray(recordsByType[postType]) ? recordsByType[postType] : [],
            ),
        [postTypesKey, recordsByType],
    );
    const postTree = useMemo(
        () => buildPostTree(postTypes, recordsByType, labelsByType),
        [postTypesKey, recordsByType, labelsByType],
    );
    const selectedPageId = useMemo(
        () => getSelectedPageId(availablePosts, value),
        [availablePosts, value],
    );
    const linksById = useMemo(
        () =>
            new Map(
                availablePosts.map((post) => [
                    String(post.id),
                    typeof post?.link === 'string' ? post.link : '',
                ]),
            ),
        [availablePosts],
    );

    if (isLoading) {
        return <Spinner />;
    }

    return (
        <TreeSelect
            __next40pxDefaultSize
            label={label}
            noOptionLabel={noOptionLabel}
            selectedId={selectedPageId}
            tree={postTree}
            onChange={(newPostId) => {
                if (!newPostId) {
                    onChange('');
                    return;
                }

                onChange(linksById.get(String(newPostId)) || '');
            }}
        />
    );
}
