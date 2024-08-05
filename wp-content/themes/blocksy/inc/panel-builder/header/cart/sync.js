import ctEvents from 'ct-events'
import { updateAndSaveEl } from '../../../../static/js/customizer/sync'
import { responsiveClassesFor } from '../../../../static/js/customizer/sync/helpers'
import { handleBackgroundOptionFor } from '../../../../static/js/customizer/sync/variables/background'
import {
	getRootSelectorFor,
	assembleSelector,
	mutateSelector,
	setRatioFor,
} from '../../../../static/js/customizer/sync/helpers'
import { typographyOption } from '../../../../static/js/customizer/sync/variables/typography'

import $ from 'jquery'

const formatPriceLabelValue = (el, optionValue) => {
	if (typeof optionValue !== 'string') {
		optionValue = optionValue['desktop']
	}

	const parts = optionValue.split('{price}')
	const price = el.querySelector('.amount')

	el.dataset.price = optionValue.includes('{price}') ? 'yes' : 'no'

	return `${parts?.[0] || ''}${price.outerHTML}${parts?.[1] || ''}`
}

ctEvents.on(
	'ct:header:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['cart'] = ({ itemId }) => ({
			cartIconSize: {
				selector: assembleSelector(getRootSelectorFor({ itemId })),
				variable: 'theme-icon-size',
				responsive: true,
				unit: 'px',
			},

			cartHeaderIconColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-item',
						})
					),
					variable: 'theme-icon-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-item',
						})
					),
					variable: 'theme-icon-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			cartBadgeColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-item',
						})
					),
					variable: 'theme-cart-badge-background',
					type: 'color:background',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-item',
						})
					),
					variable: 'theme-cart-badge-text',
					type: 'color:text',
					responsive: true,
				},
			],

			// cart top total
			...typographyOption({
				id: 'cart_total_font',

				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.ct-label',
					})
				),
			}),

			cart_total_font_color: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-item',
						})
					),
					variable: 'theme-link-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-item',
						})
					),
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			// transparent state
			transparent_cart_total_font_color: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-transparent-row="yes"]',
						})
					),
					variable: 'theme-link-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-transparent-row="yes"]',
						})
					),
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			transparentCartHeaderIconColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-transparent-row="yes"]',
						})
					),
					variable: 'theme-icon-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-transparent-row="yes"]',
						})
					),
					variable: 'theme-icon-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			transparentCartBadgeColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-transparent-row="yes"]',
						})
					),
					variable: 'theme-cart-badge-background',
					type: 'color:background',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-transparent-row="yes"]',
						})
					),
					variable: 'theme-cart-badge-text',
					type: 'color:text',
					responsive: true,
				},
			],

			// sticky state
			sticky_cart_total_font_color: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-sticky*="yes"]',
						})
					),
					variable: 'theme-link-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-sticky*="yes"]',
						})
					),
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			stickyCartHeaderIconColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-sticky*="yes"]',
						})
					),
					variable: 'theme-icon-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-sticky*="yes"]',
						})
					),
					variable: 'theme-icon-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			stickyCartBadgeColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-sticky*="yes"]',
						})
					),
					variable: 'theme-cart-badge-background',
					type: 'color:background',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: mutateSelector({
								selector: getRootSelectorFor({ itemId }),
								operation: 'suffix',
								to_add: '.ct-cart-item',
							}),
							operation: 'between',
							to_add: '[data-sticky*="yes"]',
						})
					),
					variable: 'theme-cart-badge-text',
					type: 'color:text',
					responsive: true,
				},
			],

			cartFontColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-content',
						})
					),
					variable: 'theme-text-color',
					type: 'color:default',
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-content',
						})
					),
					variable: 'theme-link-initial-color',
					type: 'color:link_initial',
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '.ct-cart-content',
						})
					),
					variable: 'theme-link-hover-color',
					type: 'color:link_hover',
				},
			],

			cartTotalFontColor: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.ct-cart-content .total',
					})
				),
				variable: 'theme-text-color',
				type: 'color:default',
			},

			// dropdown type
			cartDropDownBackground: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.ct-cart-content',
					})
				),
				variable: 'backgroundColor',
				type: 'color:default',
			},

			cartDropdownTopOffset: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '.ct-cart-content',
					})
				),
				variable: 'dropdownTopOffset',
				unit: 'px',
			},

			// panel type
			cart_panel_width: {
				selector: '#woo-cart-panel',
				variable: 'side-panel-width',
				responsive: true,
				unit: '',
			},

			cart_panel_heading_font_color: {
				selector: '#woo-cart-panel .ct-panel-actions',
				variable: 'theme-text-color',
				type: 'color:default',
				responsive: true,
			},

			cart_panel_font_color: [
				{
					selector:
						'#woo-cart-panel .cart_list, #woo-cart-panel [class*="empty-message"]',
					variable: 'theme-text-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '#woo-cart-panel .cart_list',
					variable: 'theme-link-initial-color',
					type: 'color:link_initial',
					responsive: true,
				},

				{
					selector: '#woo-cart-panel .cart_list',
					variable: 'theme-link-hover-color',
					type: 'color:link_hover',
					responsive: true,
				},
			],

			cart_panel_total_font_color: {
				selector: '#woo-cart-panel .total',
				variable: 'theme-text-color',
				type: 'color:default',
				responsive: true,
			},

			cart_panel_shadow: {
				selector: '#woo-cart-panel',
				type: 'box-shadow',
				variable: 'theme-box-shadow',
				responsive: true,
			},

			...handleBackgroundOptionFor({
				id: 'cart_panel_background',
				selector: '#woo-cart-panel .ct-panel-inner',
				responsive: true,
			}),

			...handleBackgroundOptionFor({
				id: 'cart_panel_backdrop',
				selector: '#woo-cart-panel',
				responsive: true,
			}),

			cart_panel_close_button_color: [
				{
					selector: '#woo-cart-panel .ct-toggle-close',
					variable: 'theme-icon-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '#woo-cart-panel .ct-toggle-close:hover',
					variable: 'theme-icon-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			cart_panel_close_button_border_color: [
				{
					selector:
						'#woo-cart-panel .ct-toggle-close[data-type="type-2"]',
					variable: 'toggle-button-border-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector:
						'#woo-cart-panel .ct-toggle-close[data-type="type-2"]:hover',
					variable: 'toggle-button-border-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			cart_panel_close_button_shape_color: [
				{
					selector:
						'#woo-cart-panel .ct-toggle-close[data-type="type-3"]',
					variable: 'toggle-button-background',
					type: 'color:default',
					responsive: true,
				},

				{
					selector:
						'#woo-cart-panel .ct-toggle-close[data-type="type-3"]:hover',
					variable: 'toggle-button-background',
					type: 'color:hover',
					responsive: true,
				},
			],

			cart_panel_close_button_icon_size: {
				selector: '#woo-cart-panel .ct-toggle-close',
				variable: 'theme-icon-size',
				unit: 'px',
			},

			cart_panel_close_button_border_radius: {
				selector: '#woo-cart-panel .ct-toggle-close',
				variable: 'toggle-button-radius',
				unit: 'px',
			},

			headerCartMargin: {
				selector: assembleSelector(getRootSelectorFor({ itemId })),
				type: 'spacing',
				variable: 'margin',
				responsive: true,
				important: true,
			},
		})
	}
)

ctEvents.on('ct:header:sync:item:cart', ({ optionId, optionValue, values }) => {
	const selector = '[data-id="cart"]'

	if (optionId === 'thumb_ratio') {
		updateAndSaveEl(selector, (el) => {
			;[...el.querySelectorAll('.ct-media-container')].map((el) => {
				setRatioFor(optionValue, el)
			})
		})
	}

	if (optionId === 'cart_subtotal_visibility') {
		updateAndSaveEl(selector, (el) => {
			;[...el.querySelectorAll('.ct-label')].map((el) => {
				responsiveClassesFor(optionValue, el)
			})
		})
	}

	if (optionId === 'cart_total_label') {
		updateAndSaveEl(selector, (el) => {
			;[...el.querySelectorAll('.ct-label')].map((label) => {
				label.innerHTML = formatPriceLabelValue(label, optionValue)
			})
		})

		updateAndSaveEl(
			selector,
			(el) => {
				if (!optionValue.desktop) {
					optionValue = {
						desktop: optionValue,
						mobile: optionValue,
					}
				}

				;[...el.querySelectorAll('.ct-label')].map((label) => {
					label.innerHTML = formatPriceLabelValue(
						label,
						optionValue.desktop
					)
				})
			},
			{ onlyView: 'desktop' }
		)

		updateAndSaveEl(
			selector,
			(el) => {
				if (!optionValue.desktop) {
					optionValue = {
						desktop: optionValue,
						mobile: optionValue,
					}
				}

				;[...el.querySelectorAll('.ct-label')].map((label) => {
					label.innerHTML = formatPriceLabelValue(
						label,
						optionValue.mobile
					)
				})
			},
			{ onlyView: 'mobile' }
		)
	}

	if (optionId === 'cart_total_position') {
		updateAndSaveEl(
			selector,
			(el) => {
				if (!optionValue.desktop) {
					optionValue = {
						desktop: optionValue,
						mobile: optionValue,
					}
				}

				el.firstElementChild.dataset.label = optionValue.desktop
			},
			{ onlyView: 'desktop' }
		)

		updateAndSaveEl(
			selector,
			(el) => {
				if (!optionValue.desktop) {
					optionValue = {
						desktop: optionValue,
						mobile: optionValue,
					}
				}

				el.firstElementChild.dataset.label = optionValue.mobile
			},
			{ onlyView: 'mobile' }
		)
	}

	if (optionId === 'header_cart_visibility') {
		updateAndSaveEl(selector, (el) =>
			responsiveClassesFor({ ...optionValue, desktop: true }, el)
		)
	}

	if (optionId === 'has_cart_badge') {
		updateAndSaveEl(selector, (el) => {
			const targetCounter = el.getElementsByClassName(
				'ct-dynamic-count-cart'
			)[0]
			targetCounter.dataset.count = targetCounter.innerText

			if (optionValue === 'yes') return
			targetCounter.dataset.count = 0
		})
	}

	if (optionId === 'auto_open_cart') {
		updateAndSaveEl(selector, (el) => {
			el.querySelector('a').removeAttribute('data-auto-open')

			let components = []

			if (optionValue.archive) {
				components.push('archive')
			}

			if (optionValue.product) {
				components.push('product')
			}

			if (components.length > 0) {
				el.querySelector('a').dataset.autoOpen = components.join(':')
			}
		})
	}

	if (optionId === 'cart_panel_close_button_type') {
		let offcanvasModalClose = document.querySelector(
			'#woo-cart-panel .ct-toggle-close'
		)

		setTimeout(() => {
			offcanvasModalClose.classList.add('ct-disable-transitions')

			requestAnimationFrame(() => {
				if (offcanvasModalClose) {
					offcanvasModalClose.dataset.type = optionValue
				}

				setTimeout(() => {
					offcanvasModalClose.classList.remove(
						'ct-disable-transitions'
					)
				})
			})
		}, 300)
	}
})
