import './public-path'
import './events'

import ctEvents from 'ct-events'

import { watchLayoutContainerForReveal } from './frontend/animated-element'
import { onDocumentLoaded, handleEntryPoints, loadStyle } from './helpers'

import { getCurrentScreen } from './frontend/helpers/current-screen'
import { mountDynamicChunks } from './dynamic-chunks'

import { menuEntryPoints } from './frontend/entry-points/menus'
import { liveSearchEntryPoints } from './frontend/entry-points/live-search'

import { mountElementorIntegration } from './frontend/integration/elementor'

import { preloadClickHandlers } from './frontend/dynamic-chunks/click-trigger'
import { isTouchDevice } from './frontend/helpers/is-touch-device'

export const areWeDealingWithSafari = /apple/i.test(navigator.vendor)

/**
 * iOS hover fix
 */
document.addEventListener('click', (x) => 0)

import {
	fastOverlayHandleClick,
	fastOverlayMount,
} from './frontend/fast-overlay'

let allFrontendEntryPoints = [
	...menuEntryPoints,
	...liveSearchEntryPoints,

	{
		els: '[data-parallax]',
		load: () => import('./frontend/parallax/register-listener'),
		events: ['blocksy:parallax:init'],
	},

	{
		els: '.flexy-container[data-flexy*="no"]',
		load: () => import('./frontend/flexy'),
		events: ['ct:flexy:update'],
		trigger: ['hover-with-touch'],
	},

	{
		els: '.ct-share-box [data-network="pinterest"]',
		load: () => import('./frontend/social-buttons'),
		trigger: ['click'],
	},

	{
		els: '.ct-media-container[data-media-id], .ct-dynamic-media[data-media-id]',
		load: () => import('./frontend/lazy/video-on-click.js'),
		trigger: ['click', 'slight-mousemove'],
	},

	{
		els: '.ct-share-box [data-network]:not([data-network="pinterest"]):not([data-network="email"])',
		load: () => import('./frontend/social-buttons'),
		trigger: ['click'],
		condition: () =>
			!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
				navigator.userAgent
			),
	},

	{
		els: [
			...(document.querySelector('.ct-header-cart > .ct-cart-content')
				? ['.ct-header-cart > .ct-cart-item']
				: []),
			'.ct-language-switcher > .ct-active-language',
			'.ct-header-account[data-interaction="dropdown"] > .ct-account-item',
		],
		load: () => import('./frontend/popper-elements'),
		trigger: ['hover-with-click'],
		events: ['ct:popper-elements:update'],
	},

	{
		els: '.ct-back-to-top, .ct-shortcuts-bar [data-shortcut*="scroll_top"]',
		load: () => import('./frontend/back-to-top-link'),
		events: ['ct:back-to-top:mount'],
		trigger: ['scroll'],
	},

	{
		els: '.ct-pagination:not([data-pagination="simple"])',
		load: () => import('./frontend/layouts/infinite-scroll'),
		trigger: ['scroll'],
	},

	{
		els: ['.entries[data-layout]', '[data-products].products'],
		load: () =>
			new Promise((r) => r({ mount: watchLayoutContainerForReveal })),
	},

	{
		els: ['.ct-modal-action'],
		load: () => new Promise((r) => r({ mount: fastOverlayMount })),
		events: ['ct:header:update'],
		trigger: ['click'],
	},

	{
		els: ['.ct-expandable-trigger'],
		load: () => import('./frontend/generic-accordion'),
		trigger: ['click'],
	},

	{
		els: ['.ct-header-search'],
		load: () => new Promise((r) => r({ mount: fastOverlayMount })),
		mount: ({ mount, el, ...rest }) => {
			mount(el, {
				...rest,
				focus: true,
			})
		},
		events: [],
		trigger: ['click'],
	},
]

if (document.body.className.indexOf('woocommerce') > -1) {
	import('./frontend/woocommerce/main').then(({ wooEntryPoints }) => {
		allFrontendEntryPoints = [...allFrontendEntryPoints, ...wooEntryPoints]

		handleEntryPoints(allFrontendEntryPoints, {
			immediate: true,
			skipEvents: true,
		})
	})
}

handleEntryPoints(allFrontendEntryPoints, {
	immediate: /comp|inter|loaded/.test(document.readyState),
})

const initOverlayTrigger = () => {
	;[
		...document.querySelectorAll('.ct-header-trigger'),
		...document.querySelectorAll('.ct-offcanvas-trigger'),
	].map((menuToggle) => {
		if (menuToggle && !menuToggle.hasListener) {
			menuToggle.hasListener = true

			menuToggle.addEventListener('click', (event) => {
				event.preventDefault()

				if (!menuToggle.dataset.togglePanel && !menuToggle.hash) {
					return
				}

				let offcanvas = document.querySelector(
					menuToggle.dataset.togglePanel || menuToggle.hash
				)

				if (!offcanvas) {
					return
				}

				fastOverlayHandleClick(event, {
					container: offcanvas,
					closeWhenLinkInside: !menuToggle.closest('.ct-header-cart'),
					computeScrollContainer: () => {
						if (
							offcanvas.querySelector('.cart_list') &&
							!offcanvas.querySelector(
								'[data-id="cart"] .cart_list'
							)
						) {
							return offcanvas.querySelector('.cart_list')
						}

						if (
							getCurrentScreen() === 'mobile' &&
							offcanvas.querySelector(
								'[data-device="mobile"] > .ct-panel-content-inner'
							)
						) {
							return offcanvas.querySelector(
								'[data-device="mobile"] > .ct-panel-content-inner'
							)
						}

						return offcanvas.querySelector(
							'.ct-panel-content > .ct-panel-content-inner'
						)
					},
				})
			})
		}
	})
}

onDocumentLoaded(() => {
	document.body.addEventListener(
		'mouseover',
		() => {
			loadStyle(ct_localizations.dynamic_styles.lazy_load)
			preloadClickHandlers()
			import('./frontend/handle-3rd-party-events.js')
		},
		{ once: true, passive: true }
	)

	let inputs = [
		...document.querySelectorAll(
			'.comment-form [class*="comment-form-field"]'
		),
	]
		.reduce(
			(result, parent) => [
				...result,
				parent.querySelector('input,textarea'),
			],
			[]
		)
		.filter((input) => input.type !== 'hidden' && input.type !== 'checkbox')

	const renderEmptiness = () => {
		inputs.map((input) => {
			input.parentNode.classList.remove('ct-not-empty')

			if (!input.value) {
				return
			}

			if (input.value.trim().length > 0) {
				input.parentNode.classList.add('ct-not-empty')
			}
		})
	}

	setTimeout(() => {
		renderEmptiness()
	}, 10)

	inputs.map((input) => input.addEventListener('input', renderEmptiness))

	mountDynamicChunks()

	setTimeout(() => {
		initOverlayTrigger()
	})

	mountElementorIntegration()
})

let isPageLoad = true

ctEvents.on('blocksy:frontend:init', () => {
	handleEntryPoints(allFrontendEntryPoints, {
		immediate: true,
		skipEvents: true,
	})

	mountDynamicChunks()

	initOverlayTrigger()

	if (isPageLoad) {
		isPageLoad = false
	} else {
		import('./frontend/integration/stackable').then(
			({ mountStackableIntegration }) => mountStackableIntegration()
		)
	}
})

ctEvents.on(
	'ct:overlay:handle-click',
	({ e, href, container, options = {} }) => {
		fastOverlayHandleClick(e, {
			...(href
				? {
						container: document.querySelector(href),
				  }
				: {}),

			...(container ? { container } : {}),
			...options,
		})
	}
)

export { loadStyle, handleEntryPoints, onDocumentLoaded } from './helpers'
export { registerDynamicChunk } from './dynamic-chunks'
export { getCurrentScreen } from './frontend/helpers/current-screen'
