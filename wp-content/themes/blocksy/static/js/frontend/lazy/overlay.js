import { scrollLockManager } from './overlay/no-bounce'
import ctEvents from 'ct-events'
import { mount as mountMobileMenu } from './overlay/mobile-menu'

import { focusLockManager } from '../helpers/focus-lock'
import { whenTransitionEnds } from '../helpers/when-transition-ends'
import { isTouchDevice } from '../helpers/is-touch-device'

const persistSettings = (settings) => {
	settings.container.__overlay_settings__ = settings
}

const getSettings = (settings) => {
	if (!settings.container) {
		throw new Error('No container provided')
	}

	return settings.container.__overlay_settings__ || {}
}

const clearSettings = (settings) => {
	settings.container.__overlay_settings__ = null
}

const showOffcanvas = (initialSettings) => {
	const settings = {
		onClose: () => {},
		container: null,
		focus: true,
		...getSettings(initialSettings),
	}
	;[
		...document.querySelectorAll(
			`[data-toggle-panel*="${settings.container.id}"]`
		),

		...document.querySelectorAll(`[href*="${settings.container.id}"]`),
	].map((trigger) => {
		trigger.setAttribute('aria-expanded', 'true')
	})

	if (settings.focus) {
		setTimeout(() => {
			settings.container.querySelector('input') &&
				settings.container.querySelector('input').focus()
		}, 200)
	}

	if (settings.container.querySelector('.ct-panel-content')) {
		settings.container
			.querySelector('.ct-panel-content')
			.addEventListener('click', (event) => {
				Array.from(settings.container.querySelectorAll('select')).map(
					(select) =>
						select.selectr && select.selectr.events.dismiss(event)
				)
			})
	}

	if (
		settings.clickOutside &&
		settings.container.querySelector('.ct-panel-content')
	) {
		settings.container.addEventListener(
			'click',
			settings.handleContainerClick
		)
	}

	const onKeyUp = (event) => {
		const { keyCode, target } = event

		if (keyCode !== 27) return
		event.preventDefault()

		document.body.hasAttribute('data-panel') && hideOffcanvas(settings)

		document.removeEventListener('keyup', onKeyUp)
	}

	document.addEventListener('keyup', onKeyUp)

	let maybeCloseButton =
		settings.container &&
		settings.container.querySelector('.ct-toggle-close')

	if (maybeCloseButton) {
		maybeCloseButton.addEventListener(
			'click',
			(event) => {
				event.preventDefault()
				hideOffcanvas(settings)
			},
			{ once: true }
		)

		if (!maybeCloseButton.hasEnterListener) {
			maybeCloseButton.hasEnterListener = true

			maybeCloseButton.addEventListener('keyup', (e) => {
				if (13 == e.keyCode) {
					e.preventDefault()
					hideOffcanvas(settings)
				}
			})
		}
	}

	if (
		settings.computeScrollContainer ||
		settings.container.querySelector('.ct-panel-content')
	) {
		scrollLockManager().disable(
			settings.computeScrollContainer
				? settings.computeScrollContainer()
				: settings.container.querySelector('.ct-panel-content')
		)

		setTimeout(() => {
			focusLockManager().focusLockOn(
				settings.container.querySelector('.ct-panel-content')
					.parentNode,
				{
					focusOnMount: !settings.focus,
				}
			)
		})
	}

	/**
	 * Add window event listener in the next frame. This allows us to freely
	 * propagate the current clck event up the chain -- without the modal
	 * getting closed.
	 */
	window.addEventListener('click', settings.handleWindowClick, {
		capture: true,
	})

	ctEvents.trigger('ct:modal:opened', settings.container)
	;[...settings.container.querySelectorAll('.ct-toggle-dropdown-mobile')].map(
		(arrow) => {
			mountMobileMenu(arrow)
		}
	)
}

const hideOffcanvas = (initialSettings, args = {}) => {
	const settings = {
		onClose: () => {},
		container: null,
		...getSettings(initialSettings),
	}

	args = {
		closeInstant: false,
		shouldFocusOriginalTrigger: true,
		...args,
	}

	if (!document.body.hasAttribute('data-panel')) {
		settings.container.classList.remove('active')
		settings.onClose()
		return
	}

	;[
		...document.querySelectorAll(
			`[data-toggle-panel*="${settings.container.id}"]`
		),

		...document.querySelectorAll(`[href*="${settings.container.id}"]`),
	].map((trigger, index) => {
		trigger.setAttribute('aria-expanded', 'false')

		if (args.shouldFocusOriginalTrigger && !isTouchDevice()) {
			if (!trigger.focusDisabled) {
				setTimeout(() => {
					if (index === 0) {
						trigger.focus()
					}
				}, 50)
			}

			trigger.focusDisabled = false
		}
	})

	settings.container.classList.remove('active')

	if (args.closeInstant) {
		document.body.removeAttribute('data-panel')

		scrollLockManager().enable(
			settings.computeScrollContainer
				? settings.computeScrollContainer()
				: settings.container.querySelector('.ct-panel-content')
		)

		clearSettings(settings)

		ctEvents.trigger('ct:modal:closed', settings.container)
	} else {
		document.body.dataset.panel = `out`

		whenTransitionEnds(settings.container, () => {
			document.body.removeAttribute('data-panel')

			scrollLockManager().enable(
				settings.computeScrollContainer
					? settings.computeScrollContainer()
					: settings.container.querySelector('.ct-panel-content')
			)

			focusLockManager().focusLockOff(
				settings.container.querySelector('.ct-panel-content').parentNode
			)

			clearSettings(settings)

			ctEvents.trigger('ct:modal:closed', settings.container)
		})
	}

	window.removeEventListener('click', settings.handleWindowClick, {
		capture: true,
	})

	settings.container.removeEventListener(
		'click',
		settings.handleContainerClick
	)

	settings.onClose()
}

export const handleClick = (e, settings) => {
	if (e && e.preventDefault) {
		e.preventDefault()
	}

	settings = {
		onClose: () => {},
		container: null,
		focus: false,
		clickOutside: true,
		isModal: false,
		computeScrollContainer: null,
		closeWhenLinkInside: false,
		handleContainerClick: (event) => {
			const isPanelHeadContent = event.target.closest('.ct-panel-actions')
			let isInsidePanelContent = event.target.closest('.ct-panel-content')
			let isPanelContentItself =
				[
					...settings.container.querySelectorAll('.ct-panel-content'),
				].indexOf(event.target) > -1

			let maybeTarget = null

			if (event.target.matches('[data-toggle-panel],[href*="modal"]')) {
				maybeTarget = event.target
			}

			if (
				!maybeTarget &&
				event.target.closest('[data-toggle-panel],[href*="modal"]')
			) {
				maybeTarget = event.target.closest(
					'[data-toggle-panel],[href*="modal"]'
				)
			}

			// If target has the click listener, its likely that it will
			// trigger an overlay. We should close the panel in this case.
			if (
				maybeTarget &&
				maybeTarget.hasLazyLoadClickListener &&
				// This flow is not compatible with action buttons.
				!maybeTarget.matches('[data-button-state]')
			) {
				hideOffcanvas(settings)

				setTimeout(() => {
					maybeTarget.click()
				}, 650)
				return
			}

			if (
				(settings.isModal &&
					!isPanelContentItself &&
					isInsidePanelContent) ||
				(!settings.isModal &&
					(isPanelContentItself ||
						isInsidePanelContent ||
						isPanelHeadContent)) ||
				event.target.closest('[class*="select2-container"]') ||
				// Element was clicked upon but suddenly got removed from the DOM
				!event.target.closest('body') ||
				!event.target.closest('.ct-panel')
			) {
				return
			}

			if (window.getSelection().toString().length > 0) {
				return
			}

			document.body.hasAttribute('data-panel') && hideOffcanvas(settings)
		},
		handleWindowClick: (e) => {
			setTimeout(() => {
				if (
					settings.container.contains(e.target) ||
					e.target === document.body ||
					e.target.closest('[class*="select2-container"]') ||
					!e.target.closest('body')
				) {
					return
				}

				if (!document.body.hasAttribute('data-panel')) {
					return
				}

				hideOffcanvas(settings)
			})
		},
		...settings,
	}

	persistSettings(settings)

	showOffcanvas(settings)

	if (settings.closeWhenLinkInside) {
		if (!settings.container.hasListener) {
			settings.container.hasListener = true

			settings.container.addEventListener('click', (event) => {
				if (!event.target) {
					return
				}

				let maybeA = event.target

				if (event.target.closest('a')) {
					maybeA = event.target.closest('a')
				}

				if (!maybeA.closest('.ct-panel')) {
					return
				}

				if (!maybeA.closest('.ct-panel').classList.contains('active')) {
					return
				}

				if (!maybeA.matches('a')) {
					return
				}

				if (maybeA.classList.contains('ct-overlay-skip')) {
					return
				}

				if (
					!maybeA.closest('nav[data-id*="menu"]') &&
					!maybeA.closest('[data-id*="text"]') &&
					!maybeA.closest('[data-id*="button"]') &&
					!maybeA.matches('.ct-offcanvas-trigger') &&
					!maybeA.matches('.ct-header-account') &&
					!maybeA.closest('.widget_nav_menu')
				) {
					return
				}

				hideOffcanvas(settings, {
					closeInstant: maybeA.getAttribute('href')[0] !== '#',
					shouldFocusOriginalTrigger: false,
				})

				setTimeout(() => {
					if (
						maybeA.matches('.ct-offcanvas-trigger') ||
						maybeA.matches('.ct-header-account')
					) {
						maybeA.click()
					}
				}, 500)
			})
		}
	}
}

ctEvents.on('ct:offcanvas:force-close', (settings) => hideOffcanvas(settings))

export const mount = (el, { event, focus = false }) => {
	handleClick(event, {
		isModal: true,
		container: document.querySelector(el.dataset.togglePanel || el.hash),
		clickOutside: true,
		focus,
	})
}
