/**
 * Block Animation Handler for Frontend
 *
 * Handles scroll-triggered animations for blocks using GSAP ScrollTrigger.
 * Reads animation configuration from data attributes.
 */

import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Animation configurations for each preset.
 */
const ANIMATION_CONFIGS = {
    'fade-in': {
        from: { opacity: 0 },
    },
    'fade-in-up': {
        from: { opacity: 0, y: 'distance' },
    },
    'fade-in-down': {
        from: { opacity: 0, y: '-distance' },
    },
    'fade-in-left': {
        from: { opacity: 0, x: '-distance' },
    },
    'fade-in-right': {
        from: { opacity: 0, x: 'distance' },
    },
    'slide-in-up': {
        from: { y: 'distance' },
    },
    'slide-in-down': {
        from: { y: '-distance' },
    },
    'slide-in-left': {
        from: { x: '-distance' },
    },
    'slide-in-right': {
        from: { x: 'distance' },
    },
    'zoom-in': {
        from: { opacity: 0, scale: 0.8 },
    },
    'zoom-out': {
        from: { opacity: 0, scale: 1.2 },
    },
    'rotate-in': {
        from: { opacity: 0, rotation: -15 },
    },
    'flip-in-x': {
        from: { opacity: 0, rotationX: 90 },
    },
    'flip-in-y': {
        from: { opacity: 0, rotationY: 90 },
    },
    'bounce-in': {
        from: { opacity: 0, scale: 0.3 },
        ease: 'elastic.out(1, 0.5)',
    },
};

/**
 * Scroll-triggered animations for blocks marked with `data-ei-animation`.
 */
class BlockAnimationHandler {
    /**
     * Create a new BlockAnimationHandler instance.
     *
     * @param {Object} [options] - Animation handler options.
     * @param {boolean} [options.disableOnMobile=false] - Disable animations on mobile viewports.
     * @param {number} [options.mobileBreakpoint=640] - Viewport width threshold for mobile.
     * @param {boolean} [options.forceEnable=false] - Force animations even when mobile is disabled.
     */
    constructor(options = {}) {
        this.options = {
            disableOnMobile: false,
            mobileBreakpoint: 640,
            forceEnable: false,
            ...options,
        };
        this.animatedBlocks = [];
        this.init();
    }

    /**
     * Initialize the animation handler.
     *
     * @returns {void}
     */
    init() {
        this.findAnimatedBlocks();

        if (this.animatedBlocks.length === 0) {
            return;
        }

        if (this.shouldDisableForReducedMotion() || this.shouldDisableForMobile()) {
            this.revealBlocks();
            return;
        }

        this.setupAnimations();
        this.setupRefresh();
    }

    /**
     * Determine if animations should be skipped for reduced motion.
     *
     * @returns {boolean} Whether the user prefers reduced motion.
     */
    shouldDisableForReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    /**
     * Determine if animations should be disabled for the current viewport.
     *
     * @returns {boolean} Whether animations should be skipped.
     */
    shouldDisableForMobile() {
        const { disableOnMobile, mobileBreakpoint, forceEnable } = this.options;

        if (forceEnable || !disableOnMobile) {
            return false;
        }

        return window.innerWidth < mobileBreakpoint;
    }

    /**
     * Show animated blocks without starting a timeline.
     *
     * CSS hides `[data-ei-animation]` until GSAP reveals it. Skipping the
     * timeline must still clear that hidden state.
     *
     * @returns {void}
     */
    revealBlocks() {
        this.animatedBlocks.forEach((block) => {
            block.style.visibility = 'visible';
        });
    }

    /**
     * Find all blocks with animation data attributes.
     *
     * @returns {void}
     */
    findAnimatedBlocks() {
        const blocks = document.querySelectorAll('[data-ei-animation]');
        this.animatedBlocks = Array.from(blocks);
    }

    /**
     * Parse animation configuration from data attributes.
     *
     * @param {HTMLElement} element - The element to parse.
     * @returns {Object} Animation configuration.
     */
    parseConfig(element) {
        return {
            animation: element.dataset.eiAnimation || '',
            duration: parseFloat(element.dataset.eiAnimationDuration) || 0.8,
            delay: parseFloat(element.dataset.eiAnimationDelay) || 0,
            easing: element.dataset.eiAnimationEasing || 'power2.out',
            distance: parseInt(element.dataset.eiAnimationDistance, 10) || 50,
            once: element.dataset.eiAnimationOnce !== 'false',
            threshold: parseInt(element.dataset.eiAnimationThreshold, 10) || 20,
        };
    }

    /**
     * Get the starting state for an animation.
     *
     * @param {string} animationType - The animation type.
     * @param {number} distance - The distance for movement animations.
     * @returns {Object} The from state.
     */
    getFromState(animationType, distance) {
        const config = ANIMATION_CONFIGS[animationType];

        if (!config) {
            return { opacity: 0 };
        }

        const fromState = { ...config.from };

        Object.keys(fromState).forEach((key) => {
            if (fromState[key] === 'distance') {
                fromState[key] = distance;
            } else if (fromState[key] === '-distance') {
                fromState[key] = -distance;
            }
        });

        return fromState;
    }

    /**
     * Setup animations for all found blocks.
     *
     * @returns {void}
     */
    setupAnimations() {
        this.animatedBlocks.forEach((block) => {
            const config = this.parseConfig(block);

            if (!config.animation) {
                return;
            }

            const fromState = this.getFromState(config.animation, config.distance);
            const animConfig = ANIMATION_CONFIGS[config.animation] || {};

            gsap.set(block, {
                ...fromState,
                visibility: 'visible',
            });

            const timeline = gsap.timeline({
                paused: true,
                scrollTrigger: {
                    trigger: block,
                    start: `top ${100 - config.threshold}%`,
                    toggleActions: config.once ? 'play none none none' : 'play none none reverse',
                    once: config.once,
                },
            });

            timeline.to(block, {
                opacity: 1,
                x: 0,
                y: 0,
                scale: 1,
                rotation: 0,
                rotationX: 0,
                rotationY: 0,
                duration: config.duration,
                delay: config.delay,
                ease: animConfig.ease || config.easing,
            });

            block._eiAnimation = timeline;
        });
    }

    /**
     * Setup refresh on window resize and after the window load event.
     *
     * @returns {void}
     */
    setupRefresh() {
        let resizeTimeout;

        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                ScrollTrigger.refresh();
            }, 250);
        });

        window.addEventListener('load', () => {
            ScrollTrigger.refresh();
        });
    }

    /**
     * Manually refresh ScrollTrigger instances.
     *
     * @returns {void}
     */
    refresh() {
        ScrollTrigger.refresh();
    }

    /**
     * Destroy all animations.
     *
     * @returns {void}
     */
    destroy() {
        this.animatedBlocks.forEach((block) => {
            if (block._eiAnimation) {
                block._eiAnimation.kill();
                delete block._eiAnimation;
            }
        });
        ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
    }
}

export default BlockAnimationHandler;
