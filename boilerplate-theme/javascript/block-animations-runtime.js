/**
 * Frontend entry for scroll-triggered block animations.
 *
 * Built to `theme/js/block-animations.min.js` and enqueued only when a
 * rendered block has animation attributes.
 */

import BlockAnimationHandler from './components/block-animation-handler';

/**
 * Starts scroll animations when animated blocks are present.
 *
 * Animations are disabled below 640px. The handler still reveals those
 * blocks so the initial hidden state does not stick.
 *
 * @returns {void}
 */
function initBlockAnimations() {
    const animatedBlocks = document.querySelectorAll('[data-ei-animation]');

    if (animatedBlocks.length === 0) {
        return;
    }

    new BlockAnimationHandler({
        disableOnMobile: true,
        mobileBreakpoint: 640,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBlockAnimations);
} else {
    initBlockAnimations();
}
