import imageControlCss from './image-control.css';

const STYLE_ELEMENT_ID = 'boilerplate-theme-image-control';

/**
 * Injects image control inspector styles into the editor document.
 *
 * The block editor script runs outside the canvas iframe, so the style
 * element lands in the editor chrome.
 *
 * @returns {void}
 */
function injectImageControlStyle() {
	if (typeof document === 'undefined' || document.getElementById(STYLE_ELEMENT_ID)) {
		return;
	}

	const style = document.createElement('style');
	style.id = STYLE_ELEMENT_ID;
	style.textContent = imageControlCss;
	document.head.appendChild(style);
}

injectImageControlStyle();
