import { __ } from '@wordpress/i18n';

/**
 * Decodes HTML entities from a string value.
 *
 * @param {string} value Input text that may include HTML entities.
 * @returns {string} Decoded text value.
 */
function decodeHtmlEntities(value) {
    if (!value || typeof document === 'undefined') {
        return String(value || '');
    }

    const textArea = document.createElement('textarea');
    textArea.innerHTML = value;

    return textArea.value;
}

/**
 * Normalizes a URL string for stable comparison.
 *
 * @param {string} value URL value to normalize.
 * @returns {string} Normalized URL without a trailing slash.
 */
function normalizeUrl(value) {
    const normalizedValue = String(value || '').trim();

    if (!normalizedValue) {
        return '';
    }

    return normalizedValue.replace(/\/+$/, '');
}

/**
 * Normalizes a page title from the REST entity.
 *
 * @param {Object} page Page entity.
 * @returns {string} Decoded page title.
 */
function getPageTitle(page) {
    const untitled = __('Untitled', 'boilerplate-theme');

    if (!page?.title) {
        return untitled;
    }

    if (typeof page.title === 'string') {
        return decodeHtmlEntities(page.title);
    }

    return decodeHtmlEntities(page.title.rendered || untitled);
}

/**
 * Sorts pages by menu order, then by title.
 *
 * @param {Object} firstPage First page entity.
 * @param {Object} secondPage Second page entity.
 * @returns {number} Sorting result.
 */
function sortPages(firstPage, secondPage) {
    const firstMenuOrder = Number(firstPage?.menu_order) || 0;
    const secondMenuOrder = Number(secondPage?.menu_order) || 0;

    if (firstMenuOrder !== secondMenuOrder) {
        return firstMenuOrder - secondMenuOrder;
    }

    return getPageTitle(firstPage).localeCompare(getPageTitle(secondPage));
}

/**
 * Builds the TreeSelect structure from flat page entities.
 *
 * @param {Array<Object>} pages Flat page entities from the REST API.
 * @returns {Array<Object>} TreeSelect tree nodes.
 */
export function buildPageTree(pages) {
    const pagesByParent = new Map();

    pages.forEach((page) => {
        const parentId = Number(page?.parent) || 0;
        const siblings = pagesByParent.get(parentId) || [];
        siblings.push(page);
        pagesByParent.set(parentId, siblings);
    });

    /**
     * Builds tree nodes recursively for one parent.
     *
     * @param {number} parentId Parent page ID.
     * @returns {Array<Object>} Tree nodes for the parent.
     */
    function getNodes(parentId) {
        const siblingPages = [...(pagesByParent.get(Number(parentId)) || [])].sort(sortPages);

        return siblingPages.map((page) => {
            const children = getNodes(page.id);
            const node = {
                id: String(page.id),
                name: getPageTitle(page),
            };

            if (children.length > 0) {
                node.children = children;
            }

            return node;
        });
    }

    return getNodes(0);
}

/**
 * Resolves the selected page ID for a stored URL value.
 *
 * @param {Array<Object>} pages Flat page entities from the REST API.
 * @param {string} urlValue Stored URL attribute.
 * @returns {string|undefined} Selected page ID for TreeSelect.
 */
export function getSelectedPageId(pages, urlValue) {
    const normalizedTargetUrl = normalizeUrl(urlValue);

    if (!normalizedTargetUrl) {
        return undefined;
    }

    const selectedPage = pages.find((page) => normalizeUrl(page?.link) === normalizedTargetUrl);

    return selectedPage ? String(selectedPage.id) : undefined;
}
