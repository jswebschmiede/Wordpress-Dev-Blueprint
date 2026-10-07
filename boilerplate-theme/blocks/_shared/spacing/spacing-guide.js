import { useBlockEditContext } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';

const GUIDE_STYLE_ID = 'sp-spacing-guide';

/**
 * Overlay colors, matching the core spacing visualizer tones.
 *
 * @type {Record<'padding'|'margin', string>}
 */
const GUIDE_COLORS = {
	padding: 'rgba(162, 201, 68, 0.5)',
	margin: 'rgba(220, 158, 93, 0.5)',
};

/**
 * Returns the document that renders the blocks.
 *
 * The post editor renders blocks inside the `editor-canvas` iframe. Without
 * the iframe, blocks render in the editor document itself.
 *
 * @returns {Document} Canvas document.
 */
function getCanvasDocument() {
	const iframe = document.querySelector('iframe[name="editor-canvas"]');

	return iframe?.contentDocument ?? document;
}

/**
 * Builds the overlay CSS for one block wrapper.
 *
 * The overlay always uses the computed values, so it shows the spacing of the
 * previewed device.
 *
 * Padding: a `::after` box whose border widths equal the computed padding.
 *
 * Margin: four offset box shadows on the wrapper itself, one band per side.
 * A `::after` box would have to extend beyond the wrapper and gets clipped
 * when the block uses `overflow: hidden`. The element's own box shadow is not
 * clipped by its overflow. The corners stay empty.
 *
 * @param {string} selector Block wrapper selector.
 * @param {'padding'|'margin'} property Highlighted property.
 * @param {CSSStyleDeclaration} computed Computed style of the block wrapper.
 * @returns {string} CSS text.
 */
function buildGuideCss(selector, property, computed) {
	const top = computed.getPropertyValue(`${property}-top`);
	const right = computed.getPropertyValue(`${property}-right`);
	const bottom = computed.getPropertyValue(`${property}-bottom`);
	const left = computed.getPropertyValue(`${property}-left`);
	const color = GUIDE_COLORS[property];

	if (property === 'margin') {
		return `${selector} {
    box-shadow:
        0 calc(${top} * -1) 0 0 ${color},
        ${right} 0 0 0 ${color},
        0 ${bottom} 0 0 ${color},
        calc(${left} * -1) 0 0 0 ${color} !important;
}`;
	}

	const position = computed.position === 'static' ? `${selector} { position: relative; }` : '';

	return `${position}
${selector}::after {
    content: '' !important;
    position: absolute;
    inset: 0;
    z-index: 2;
    box-sizing: border-box;
    border-style: solid;
    border-color: ${color};
    border-width: ${top} ${right} ${bottom} ${left};
    background: transparent !important;
    outline: none;
    box-shadow: none;
    pointer-events: none;
}`;
}

/**
 * Highlights the padding or margin of the current block in the editor canvas.
 *
 * Pass `null` to remove the overlay. The overlay is re-measured whenever the
 * stored group or the previewed device changes.
 *
 * @param {'padding'|'margin'|null} property Property to highlight.
 * @param {Object|undefined} group Stored property group.
 * @param {'mobile'|'tablet'|'desktop'} deviceKey Previewed device.
 * @param {string} [targetSelector] Element inside the block wrapper that carries the spacing.
 * @returns {void}
 */
export function useSpacingGuide(property, group, deviceKey, targetSelector) {
	const { clientId } = useBlockEditContext();

	useEffect(() => {
		if (!property || !clientId) {
			return undefined;
		}

		const canvas = getCanvasDocument();
		const view = canvas.defaultView ?? window;
		let style = null;

		// Measure after the canvas has applied the new spacing classes.
		const frame = view.requestAnimationFrame(() => {
			const selector = targetSelector
				? `#block-${clientId} ${targetSelector}`
				: `#block-${clientId}`;
			const element = canvas.querySelector(selector);

			if (!element) {
				return;
			}

			style = canvas.createElement('style');
			style.id = GUIDE_STYLE_ID;
			style.textContent = buildGuideCss(selector, property, view.getComputedStyle(element));
			canvas.head.appendChild(style);
		});

		return () => {
			view.cancelAnimationFrame(frame);
			style?.remove();
		};
	}, [property, clientId, group, deviceKey, targetSelector]);
}
