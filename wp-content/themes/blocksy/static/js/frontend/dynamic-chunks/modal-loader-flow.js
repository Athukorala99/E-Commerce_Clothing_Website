import { fastOverlayHandleClick } from '../fast-overlay'

const ajaxLoader = `<span class="ct-ajax-loader" data-type="boxed"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" opacity="0.2" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="2"/><path d="m12,2c5.52,0,10,4.48,10,10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="0.5s" from="0 12 12" to="360 12 12" repeatCount="indefinite" /></path></svg></span>`

const getModalContent = (args) => {
	return new Promise((resolve) => {
		const maybeCurrentChunk = args.loadedChunks[args.chunk.id]

		const loader = document.createElement('div')

		if (maybeCurrentChunk && maybeCurrentChunk.maybeGetPanelContent) {
			const maybeFutureContent = maybeCurrentChunk.maybeGetPanelContent(
				args.el,
				{
					event: args.event,
				}
			)

			if (maybeFutureContent) {
				maybeFutureContent.then((content) => {
					loader.appendChild(content)
					resolve(loader.firstElementChild)
				})

				return
			}
		}

		loader.innerHTML = ajaxLoader

		loader.dataset.behaviour = 'modal'
		loader.classList.add('ct-panel')
		loader.classList.add('loading')

		if (args.chunk.has_loader.class) {
			loader.classList.add(args.chunk.has_loader.class)
		}

		if (args.chunk.has_loader.id) {
			loader.id = args.chunk.has_loader.id
		} else {
			loader.id = (args.el.hash || '').replace('#', '')
		}

		resolve(loader)
	})
}

const openLoader = (args) => {
	const startTime = new Date().getTime()

	if (document.querySelector('.ct-panel.active')) {
		return
	}

	getModalContent(args).then((content) => {
		document.querySelector('.ct-drawer-canvas').appendChild(content)

		fastOverlayHandleClick(args.event, {
			openStrategy: 'fast',
			container: content,
		})

		args.loadChunkWithPayload(
			args.chunk,
			{
				event: args.event,
				ajaxLoader,
				panel: content,
				completeAction: (completeArgs = {}) => {
					completeArgs = {
						onCompleted: () => {},

						...completeArgs,
					}

					const setFinalState = () => {
						const endTime = new Date().getTime()

						if (endTime - startTime < args.ensureAtLeast) {
							setTimeout(() => {
								content.classList.remove('loading')
								completeArgs.onCompleted()
							}, args.ensureAtLeast - (endTime - startTime))
						} else {
							content.classList.remove('loading')
							completeArgs.onCompleted()
						}
					}

					setFinalState()
				},
			},
			args.el
		)
	})
}

export const bootModalLoaderFlow = (args = {}) => {
	args = {
		el: null,
		event: null,
		chunk: null,
		loadedChunks: {},
		ensureAtLeast: 0, // TODO: maybe will be used in the future

		...args,
	}

	openLoader(args)
}
