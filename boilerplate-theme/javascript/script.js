/**
 * Front-end JavaScript
 *
 * The JavaScript code you place here will be processed by esbuild. The output
 * file will be created at `../theme/js/script.min.js` and enqueued in
 * `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */
/**
 * Front-end JavaScript
 *
 * The JavaScript code you place here will be processed by esbuild. The output
 * file will be created at `../theme/js/script.min.js` and enqueued in
 * `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */


import './components/anim-menu-btn';
import './components/flexi-header';
import BlockAnimationHandler from './components/block-animation-handler';
import ProgressToTopV2 from './components/progress-to-top';

document.addEventListener('DOMContentLoaded', () => {
	init();
});

/**
 * Initialize the script
 * @returns {void}
 */
function init() {
	to_top_button();
	preloader();
	block_animations();

	console.log('%cWe ❤️ WordPress!', 'color: #059669; font-weight: bold;');
}

/**
 * Block animations functionality
 *
 * @param {Object} [options] - Block animation handler options
 * @param {boolean} [options.disableOnMobile] - Disable animations on mobile viewports
 * @param {number} [options.mobileBreakpoint] - Viewport width threshold for mobile
 * @param {boolean} [options.forceEnable] - Force animations even when mobile is disabled
 * @returns {void}
 */
function block_animations(options = {}) {
	const animatedBlocks = document.querySelectorAll('[data-ei-animation]');
	if (animatedBlocks.length > 0) {
		const defaultOptions = {
			disableOnMobile: true,
			mobileBreakpoint: 640,
		};
		new BlockAnimationHandler({
			...defaultOptions,
			...options,
		});
	}
}

/**
 * To-top button functionality
 * @returns {void}
 */
function to_top_button() {
	const progressToTop = document.querySelector('#scroll-top');
	if (progressToTop) {
		new ProgressToTopV2(progressToTop);
	}
}

/**
 * Preloader functionality
 * @param {Function} [onComplete] - Callback when preloader starts to fade out
 * @returns {void}
 */
function preloader(onComplete) {
	const loaderElement = document.getElementById('loader');

	// If no preloader exists, run callback immediately
	if (!loaderElement) {
		onComplete?.();
		return;
	}

	setTimeout(() => {
		loaderElement.classList.add('done');

		// Remove preloader after fade-out animation
		setTimeout(() => {
			loaderElement.remove();
			onComplete?.();
		}, 300);

	}, 1500);
}
