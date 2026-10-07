/**
 * Scroll-to-top button with a circular progress ring.
 */
class ProgressToTopV2 {
    /**
     * @param {HTMLElement|null} buttonElement - Scroll-to-top control.
     */
    constructor(buttonElement) {
        this.button = buttonElement || document.getElementById('scroll-top');
        if (!this.button) return null;

        this.progressIndicator = document.querySelector('#progress-indicator');
        this.init();
    }

    /**
     * Binds scroll and click listeners and paints the initial progress state.
     *
     * @returns {void}
     */
    init() {
        this.setupEventListeners();
        this.updateProgress();
    }

    /**
     * Binds scroll and click listeners.
     *
     * @returns {void}
     */
    setupEventListeners() {
        window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
        this.button.addEventListener('click', this.handleScrollToTop.bind(this));
    }

    /**
     * Toggles visibility after a scroll threshold and updates the ring progress.
     *
     * @returns {void}
     */
    updateProgress() {
        const scrollTop = window.scrollY;

        if (scrollTop > 200) {
            this.button.classList.add('show');

            const documentHeight = document.documentElement.scrollHeight;
            const windowHeight = window.innerHeight;

            const progress = (scrollTop / (documentHeight - windowHeight)) * 214;

            if (this.progressIndicator) {
                this.progressIndicator.style.strokeDasharray = `${progress}, 400`;
            }
        } else {
            this.button.classList.remove('show');
        }
    }

    /**
     * Updates the ring while the page scrolls.
     *
     * @returns {void}
     */
    handleScroll() {
        this.updateProgress();
    }

    /**
     * Scrolls to the top of the page, respecting reduced-motion preferences.
     *
     * @returns {void}
     */
    handleScrollToTop() {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        window.scrollTo({
            top: 0,
            behavior: reduceMotion ? 'auto' : 'smooth',
        });
    }
}

export default ProgressToTopV2;
