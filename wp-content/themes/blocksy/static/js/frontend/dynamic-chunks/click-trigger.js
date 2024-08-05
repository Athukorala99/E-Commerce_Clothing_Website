import { fastOverlayHandleClick } from '../fast-overlay'

export const preloadClickHandlers = () => {
	import('./button-loader-flow')
	import('./modal-loader-flow')
}

export const handleClickTrigger = (
	trigger,
	chunk,
	loadChunkWithPayload,
	loadedChunks
) =>
	[...document.querySelectorAll(trigger.selector)].map((el) => {
		if (el.hasLazyLoadClickListener) {
			return
		}

		el.hasLazyLoadClickListener = true

		const cb = (event) => {
			if (
				chunk.ignore_click &&
				(event.target.matches(chunk.ignore_click) ||
					event.target.closest(chunk.ignore_click))
			) {
				return
			}

			event.preventDefault()

			if (
				el.closest('.ct-panel.active') &&
				el.matches('.ct-header-account[href*="account-modal"]')
			) {
				return
			}

			if (chunk.has_loader) {
				if (chunk.has_loader.type === 'button') {
					import('./button-loader-flow').then(
						({ bootButtonLoaderFlow }) => {
							bootButtonLoaderFlow({
								el,
								chunk,
								event,
								loadedChunks,
								loadChunkWithPayload,
							})
						}
					)
				} else {
					import('./modal-loader-flow').then(
						({ bootModalLoaderFlow }) => {
							bootModalLoaderFlow({
								el,
								event,
								chunk,
								loadedChunks,
								loadChunkWithPayload,
							})
						}
					)
				}
			} else {
				loadChunkWithPayload(chunk, { event }, el)
			}
		}

		el.dynamicJsChunkStop = () => {
			el.removeEventListener('click', cb)
		}

		el.addEventListener('click', cb)
	})
