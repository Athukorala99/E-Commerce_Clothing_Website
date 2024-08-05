import { handleClickTrigger } from './click-trigger'
import { handleScrollTrigger } from './scroll-trigger'
import $ from 'jquery'

// trigger: { id: 'hover', selector: '.js-lazy-load-on-hover' }
const triggersList = {
	click: handleClickTrigger,
	scroll: handleScrollTrigger,

	change: (trigger, chunk, loadChunkWithPayload) => {
		;[...document.querySelectorAll(trigger.selector)].map((el) => {
			if (el.hasLazyLoadChangeListener) {
				return
			}

			el.hasLazyLoadChangeListener = true

			el.addEventListener('change', (event) => {
				event.preventDefault()
				loadChunkWithPayload(chunk, { event }, el)
			})
		})
	},

	submit: (trigger, chunk, loadChunkWithPayload) => {
		;[...document.querySelectorAll(trigger.selector)].map((el) => {
			if (el.hasLazyLoadSubmitListener) {
				return
			}

			el.hasLazyLoadSubmitListener = true

			if ($) {
				$(el).on('submit', (event) => {
					event.preventDefault()
					loadChunkWithPayload(chunk, { event }, el)
				})
			} else {
				el.addEventListener('submit', (event) => {
					event.preventDefault()
					loadChunkWithPayload(chunk, { event }, el)
				})
			}
		})
	},

	hover: (trigger, chunk, loadChunkWithPayload) => {
		;[...document.querySelectorAll(trigger.selector)].map((el) => {
			if (el.hasLazyLoadHoverListener) {
				return
			}

			el.hasLazyLoadHoverListener = true

			el.addEventListener('mouseover', (event) => {
				event.preventDefault()
				loadChunkWithPayload(chunk, { event }, el)
			})
		})
	},

	'slight-mousemove': (trigger, chunk, loadChunkWithPayload) => {
		const maybeEl = document.querySelector(trigger.selector)

		if (!document.body.hasSlightMousemoveListener && maybeEl) {
			document.body.hasSlightMousemoveListener = true

			const cb = (event) => {
				document.removeEventListener('mousemove', cb)
				loadChunkWithPayload(chunk, { event }, [
					...document.querySelectorAll(trigger.selector),
				])
			}

			document.addEventListener('mousemove', cb)
		}
	},

	'variable-product-update': (trigger, chunk, loadChunkWithPayload) => {
		;[...document.querySelectorAll(trigger.selector)].map((el) => {
			if (el.hasVariableProductUpdateListener) {
				return
			}

			el.hasVariableProductUpdateListener = true
			;['found_variation', 'reset_data'].map((eventName) => {
				$(document.body).on(eventName, (event, eventData) => {
					loadChunkWithPayload(chunk, { event, eventData }, el)
				})
			})
		})
	},
}

export const handleTrigger = (
	trigger,
	chunk,
	loadChunkWithPayload,
	loadedChunks
) => {
	if (!trigger.trigger && !triggersList[trigger.trigger]) {
		return
	}

	triggersList[trigger.trigger](
		trigger,
		chunk,
		loadChunkWithPayload,
		loadedChunks
	)
}
