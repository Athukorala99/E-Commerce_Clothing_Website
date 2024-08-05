import ctEvents from 'ct-events'
import { loadStyle, registerDynamicChunk } from 'blocksy-frontend'
import { handleAccountModal, activateScreen } from './frontend/account'

let maybeTemplate = ''

registerDynamicChunk('blocksy_account', {
	mount: (el, { event }) => {
		// Don't do anything if there's a panel opened already.
		// This means the account is placed in another panel and is opened from
		// it. Soon another click event will be fired on the same element
		// by the panel logic itself.
		if (document.body.dataset.panel) {
			return
		}

		if (!maybeTemplate) {
			let maybeAccount = document.querySelector('#account-modal')

			if (!maybeAccount) {
				location = document.querySelector(
					'[data-id="account"] .ct-account-item'
				)
					? document.querySelector(
							'[data-id="account"] .ct-account-item'
					  ).href
					: el.href

				return
			}

			maybeTemplate = maybeAccount.outerHTML
			maybeAccount.remove()
		}

		let panel = document.querySelector('#account-modal')
		if (!panel) {
			document
				.querySelector('.ct-drawer-canvas')
				.insertAdjacentHTML('beforeend', maybeTemplate)
			panel = document.querySelector('.ct-drawer-canvas').lastChild
		}

		const maybeMatchingContainer =
			ct_localizations.dynamic_styles_selectors.find((descriptor) =>
				panel.matches(descriptor.selector)
			)

		const actuallyOpen = () => {
			handleAccountModal(panel)

			activateScreen(panel, {
				screen: el.dataset.view || 'login',
			})

			if (window.anr_onloadCallback) {
				window.anr_onloadCallback()
			}

			if (window.Dokan_Vendor_Registration) {
				window.Dokan_Vendor_Registration.init()
			}

			ctEvents.trigger('ct:overlay:handle-click', {
				e: event,
				options: {
					openStrategy: 'fast',
					container: panel,
				},
			})

			ctEvents.trigger('ct:overlay:handle-click', {
				e: event,
				href: '#account-modal',
				options: {
					openStrategy: 'skip',
					isModal: true,
					computeScrollContainer: () => {
						if (!panel.closest('body')) {
							return
						}

						return panel.querySelector('.ct-account-forms')
					},
				},
			})
		}

		if (!maybeMatchingContainer) {
			actuallyOpen()
		} else {
			loadStyle(maybeMatchingContainer.url).then(() => {
				actuallyOpen()
			})
		}
	},
})

ctEvents.on('ct:modal:closed', (modalContainer) => {
	if (!modalContainer.closest('#account-modal')) {
		return
	}

	modalContainer.remove()
})
