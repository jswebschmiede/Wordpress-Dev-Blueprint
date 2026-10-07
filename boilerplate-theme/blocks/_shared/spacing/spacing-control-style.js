import spacingControlCss from './spacing-control.css';

const STYLE_ELEMENT_ID = 'sp-spacing';

/**
 * Injects spacing inspector styles into the editor document.
 *
 * The block editor script runs outside the canvas iframe, so the style
 * element lands in the editor chrome.
 *
 * @returns {void}
 */
function injectSpacingControlStyle() {
	if (typeof document === 'undefined' || document.getElementById(STYLE_ELEMENT_ID)) {
		return;
	}

	const style = document.createElement('style');
	style.id = STYLE_ELEMENT_ID;
	style.textContent = spacingControlCss;
	document.head.appendChild(style);
}

injectSpacingControlStyle();
