export const bootButtonLoaderFlow = (args = {}) => {
	args = {
		el: null,
		event: null,
		chunk: null,
		loadedChunks: {},

		ensureAtLeast: 500,

		...args,
	}

	const maybeCurrentChunk = args.loadedChunks[args.chunk.id]

	const startTime = new Date().getTime()

	const { el } = args
	const initialState = el.dataset.buttonState

	const isElInsideAnotherPanel = el.closest('.ct-panel.active')

	if (
		(document.querySelector('.ct-panel.active') &&
			!isElInsideAnotherPanel) ||
		el.dataset.buttonState === 'loading'
	) {
		return
	}

	if (isElInsideAnotherPanel && args.chunk.has_loader.will_open_overlay) {
		el.dataset.buttonState = 'loading'

		const maybeCloseButton = document.querySelector(
			'.ct-panel.active .ct-toggle-close'
		)

		if (maybeCurrentChunk && maybeCurrentChunk.maybeGetPanelContent) {
			const maybeFutureContent = maybeCurrentChunk.maybeGetPanelContent(
				args.el,
				{
					event: args.event,
				}
			)

			if (maybeFutureContent) {
				el.dataset.buttonState = initialState
			}
		}

		args.loadChunkWithPayload(
			args.chunk,
			{
				event: args.event,
				completeAction: (completeArgs = {}) => {
					completeArgs = {
						hasCheckmarkWithTimeout: false,
						finalState: '',
						onCompleted: () => {},

						...completeArgs,
					}

					if (maybeCloseButton) {
						maybeCloseButton.click()
						ctEvents.once(
							'ct:modal:closed',
							completeArgs.onCompleted
						)
					}
				},
				initialState,
			},
			el
		)

		return
	}

	let shouldDisplayLoading = true

	if (maybeCurrentChunk && maybeCurrentChunk.maybeGetPanelContent) {
		const maybeFutureContent = maybeCurrentChunk.maybeGetPanelContent(
			args.el,
			{
				event: args.event,
			}
		)

		if (maybeFutureContent) {
			shouldDisplayLoading = false
		}
	}

	if (shouldDisplayLoading) {
		el.dataset.buttonState = 'loading'
	}

	args.loadChunkWithPayload(
		args.chunk,
		{
			event: args.event,
			completeAction: (completeArgs = {}) => {
				completeArgs = {
					hasCheckmarkWithTimeout: false,
					finalState: '',
					onCompleted: () => {},

					...completeArgs,
				}

				const setFinalState = () => {
					const endTime = new Date().getTime()

					if (
						endTime - startTime < args.ensureAtLeast &&
						shouldDisplayLoading
					) {
						setTimeout(() => {
							el.dataset.buttonState = completeArgs.finalState
							completeArgs.onCompleted()
						}, args.ensureAtLeast - (endTime - startTime))
					} else {
						el.dataset.buttonState = completeArgs.finalState
						completeArgs.onCompleted()
					}
				}

				if (completeArgs.hasCheckmarkWithTimeout) {
					el.dataset.buttonState = 'done'
					setTimeout(setFinalState, 1000)
				} else {
					setFinalState()
				}
			},
			initialState,
		},
		el
	)
}
