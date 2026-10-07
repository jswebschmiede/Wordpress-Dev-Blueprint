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

    console.log('%cWe ❤️ WordPress!', 'color: #059669; font-weight: bold;');
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
