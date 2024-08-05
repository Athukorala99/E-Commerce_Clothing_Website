const handledChunks = {}

export const handleScrollTrigger = (
	trigger,
	chunk,
	loadChunkWithPayload,
	loadedChunks
) => {
	if (handledChunks[chunk.id]) {
		return
	}

	handledChunks[chunk.id] = true

	setTimeout(() => {
		let prevScroll = scrollY

		let cb = (e) => {
			if (
				Math.abs(scrollY - prevScroll) > 30 ||
				// are we at the bottom of the page?
				window.innerHeight + Math.round(scrollY) >=
					document.body.offsetHeight
			) {
				document.removeEventListener('scroll', cb)
				loadChunkWithPayload(chunk)
				return
			}
		}

		cb()
		document.addEventListener('scroll', cb, { passive: true })
	}, 500)
}
