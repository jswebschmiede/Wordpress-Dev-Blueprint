(() => {
	/**
	 * FlexiHeader
	 *
	 * @param {HTMLElement} element - Header root (`.js-f-header`).
	 * @param {Object} options - User defined options.
	 * @param {string} options.openSubmenu - `"click"` or `"hover"`.
	 */
	class FlexiHeader {
		constructor(
			element,
			options = {
				openSubmenu: 'click',
			},
		) {
			this.header = element;
			this.options = { ...FlexiHeader.defaultOptions, ...options };
			this.menuTrigger = this.header.querySelector('.js-anim-menu-btn');
			this.focusMenu = false;
			this.resizingId = false;

			this.init();
		}

		static defaultOptions = {
			openSubmenu: 'click',
		};

		/**
		 * Binds dropdown, overlay, resize and keyboard behaviour.
		 *
		 * @returns {void}
		 */
		init = () => {
			this.resetFlexHeaderOffset();
			this.setAriaButtons();

			if (this.menuTrigger) {
				this.menuTrigger.addEventListener('anim-menu-btn-clicked', (event) => {
					this.toggleMenuNavigation(event.detail);
				});
			}

			this.header.addEventListener('click', (event) => {
				const control = event.target.closest('.js-f-header__dropdown-control');

				if (!control) {
					return;
				}

				const willOpen = control.getAttribute('aria-expanded') !== 'true';
				this.closeOtherDropdowns(control);
				this.setDropdownExpanded(control, willOpen);
			});

			if (this.options.openSubmenu === 'hover') {
				this.header.classList.add('f-header--open-submenu-hover');
				let hoverTimeout;

				this.header.addEventListener(
					'mouseover',
					(event) => {
						if (this.getLayout() === 'mobile') {
							return;
						}

						const control = this.getItemDropdownControl(event.target);

						if (!control) {
							return;
						}

						if (hoverTimeout) {
							clearTimeout(hoverTimeout);
							hoverTimeout = null;
						}

						this.closeOtherDropdowns(control);
						this.setDropdownExpanded(control, true);
					},
					true,
				);

				this.header.addEventListener(
					'mouseout',
					(event) => {
						if (this.getLayout() === 'mobile') {
							return;
						}

						const item = event.target.closest('.f-header__list > .f-header__item');
						const control = item?.querySelector(
							':scope > .js-f-header__dropdown-control',
						);

						if (!control) {
							return;
						}

						const relatedTarget = event.relatedTarget;
						if (relatedTarget instanceof Node && item.contains(relatedTarget)) {
							return;
						}

						hoverTimeout = setTimeout(() => {
							this.closeAllDropdowns();
						}, 150);
					},
					true,
				);
			}

			this.header.addEventListener('focusin', (event) => {
				if (this.getLayout() === 'mobile') {
					return;
				}

				this.closeOtherDropdowns(this.getItemDropdownControl(event.target));
			});

			document.addEventListener('keydown', (event) => {
				if (event.key !== 'Escape') {
					return;
				}

				if (this.header.classList.contains('f-header--expanded') && this.menuTrigger) {
					this.menuTrigger.click();
					return;
				}

				this.closeAllDropdowns();
			});

			window.addEventListener('resize', () => {
				clearTimeout(this.resizingId);
				this.resizingId = setTimeout(this.doneResizing, 500);
			});
		};

		/**
		 * Returns the disclosure button for the top-level item that contains `target`.
		 *
		 * @param {EventTarget|null} target - Event target.
		 * @returns {HTMLElement|null} Dropdown control, if any.
		 */
		getItemDropdownControl = (target) => {
			if (!(target instanceof Element)) {
				return null;
			}

			const item = target.closest('.f-header__list > .f-header__item');
			return item?.querySelector(':scope > .js-f-header__dropdown-control') ?? null;
		};

		/**
		 * Sets `aria-expanded` and the shared open class on the body.
		 *
		 * @param {HTMLElement} control - Dropdown control button.
		 * @param {boolean} expanded - Whether the submenu should be open.
		 * @returns {void}
		 */
		setDropdownExpanded = (control, expanded) => {
			control.setAttribute('aria-expanded', String(expanded));

			if (expanded) {
				document.body.classList.add('f-header-dropdown--open');
				return;
			}

			if (
				!this.header.querySelector('.js-f-header__dropdown-control[aria-expanded="true"]')
			) {
				document.body.classList.remove('f-header-dropdown--open');
			}
		};

		/**
		 * Collapses every dropdown except `exceptControl`.
		 *
		 * @param {HTMLElement|null} exceptControl - Control to leave unchanged.
		 * @returns {void}
		 */
		closeOtherDropdowns = (exceptControl) => {
			this.header.querySelectorAll('.js-f-header__dropdown-control').forEach((control) => {
				if (control !== exceptControl) {
					control.setAttribute('aria-expanded', 'false');
				}
			});

			if (
				!this.header.querySelector('.js-f-header__dropdown-control[aria-expanded="true"]')
			) {
				document.body.classList.remove('f-header-dropdown--open');
			}
		};

		/**
		 * Collapses all header dropdowns.
		 *
		 * @returns {void}
		 */
		closeAllDropdowns = () => {
			this.closeOtherDropdowns(null);
		};

		/**
		 * Ensures each disclosure button points at its submenu panel.
		 *
		 * @returns {void}
		 */
		setAriaButtons = () => {
			const btnDropdown = this.header.getElementsByClassName('js-f-header__dropdown-control');

			for (let i = 0; i < btnDropdown.length; i++) {
				let id = 'f-header-dropdown-' + i;
				const dropdown = btnDropdown[i].nextElementSibling;

				if (!dropdown) {
					continue;
				}

				if (dropdown.hasAttribute('id')) {
					id = dropdown.getAttribute('id');
				} else {
					dropdown.setAttribute('id', id);
				}
				btnDropdown[i].setAttribute('aria-controls', id);
			}
		};

		/**
		 * Updates `--f-header-offset` from the header's position (mobile overlay height).
		 *
		 * @returns {void}
		 */
		resetFlexHeaderOffset = () => {
			document.documentElement.style.setProperty(
				'--f-header-offset',
				this.header.getBoundingClientRect().top + 'px',
			);
		};

		/**
		 * Makes page content behind the mobile overlay unfocusable.
		 *
		 * @param {boolean} isMenuOpen - Whether the mobile navigation is visible.
		 * @returns {void}
		 */
		setBackgroundInert = (isMenuOpen) => {
			const shouldInert = Boolean(isMenuOpen && this.getLayout() === 'mobile');
			const targets = [
				document.querySelector('a[href="#main"]'),
				document.querySelector('.header-top'),
				document.getElementById('content'),
				document.getElementById('colophon'),
				document.getElementById('scroll-top'),
			];

			targets.forEach((element) => {
				if (!element) {
					return;
				}

				element.inert = shouldInert;
			});
		};

		/**
		 * Shows or hides the mobile navigation overlay.
		 *
		 * @param {boolean} isOpen - Whether the overlay should be visible.
		 * @returns {void}
		 */
		toggleMenuNavigation = (isOpen) => {
			const nav = document.querySelector('.f-header__nav');
			if (nav) {
				nav.classList.toggle('f-header__nav--is-visible', isOpen);
			}
			this.header.classList.toggle('f-header--expanded', isOpen);
			if (this.menuTrigger) {
				this.menuTrigger.setAttribute('aria-expanded', String(isOpen));
			}

			this.setBackgroundInert(isOpen);

			if (isOpen) {
				this.focusMenu = document.activeElement;
				const firstFocusable = this.getMenuFirstFocusable();
				if (firstFocusable) {
					firstFocusable.focus();
				}
			} else if (this.focusMenu) {
				this.focusMenu.focus();
				this.focusMenu = false;
			}
		};

		/**
		 * Whether an element currently occupies layout space.
		 *
		 * @param {HTMLElement} element - Candidate element.
		 * @returns {boolean} True if the element is visible.
		 */
		isVisible = (element) => {
			return element.offsetWidth || element.offsetHeight || element.getClientRects().length;
		};

		/**
		 * Closes the overlay when the viewport grows past the hamburger breakpoint.
		 *
		 * @returns {void}
		 */
		doneResizing = () => {
			if (
				this.menuTrigger &&
				!this.isVisible(this.menuTrigger) &&
				this.header.classList.contains('f-header--expanded')
			) {
				this.menuTrigger.click();
			}
			this.resetFlexHeaderOffset();
		};

		/**
		 * First tabbable control inside the navigation panel.
		 *
		 * @returns {HTMLElement|false} Focus target, or false if none is visible.
		 */
		getMenuFirstFocusable = () => {
			const nav = this.header.querySelector('.f-header__nav');
			if (!nav) {
				return false;
			}
			const focusableEle = nav.querySelectorAll(
				'[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex]:not([tabindex="-1"]), [contenteditable], audio[controls], video[controls], summary',
			);
			let firstFocusable = false;
			for (let i = 0; i < focusableEle.length; i++) {
				if (
					focusableEle[i].offsetWidth ||
					focusableEle[i].offsetHeight ||
					focusableEle[i].getClientRects().length
				) {
					firstFocusable = focusableEle[i];
					break;
				}
			}
			return firstFocusable;
		};

		/**
		 * Layout mode from the `::before` content switch (`mobile` / `desktop`).
		 *
		 * @returns {string} Current layout token.
		 */
		getLayout = () => {
			return getComputedStyle(this.header, ':before')
				.getPropertyValue('content')
				.replace(/'|"/g, '');
		};
	}

	const flexiHeader = document.querySelector('.f-header');
	if (flexiHeader) {
		new FlexiHeader(flexiHeader, { openSubmenu: 'hover' });
	}
})();
