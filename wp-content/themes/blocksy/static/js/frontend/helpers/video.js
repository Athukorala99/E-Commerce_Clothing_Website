export const handlePlayClasses = (videoOrIframe) => {
	videoOrIframe.closest(
		'.ct-media-container, .ct-dynamic-media'
	).dataset.state = 'playing'
}

export const playVideo = (videoOrIframe) => {
	if (!videoOrIframe) {
		return
	}

	if (videoOrIframe.matches('video')) {
		videoOrIframe.play()
		return
	}

	if (videoOrIframe.matches('iframe[src*="youtu"]')) {
		videoOrIframe.contentWindow.postMessage(
			JSON.stringify({
				event: 'command',
				func: 'playVideo',
			}),
			'*'
		)
		return
	}

	if (videoOrIframe.matches('iframe[src*="vimeo"]')) {
		videoOrIframe.contentWindow.postMessage(
			JSON.stringify({
				method: 'play',
			}),
			'*'
		)
		return
	}
}

export const maybePlayAutoplayedVideo = (
	videoOrIframe,
	onPlay = () => {},
	onNotPlay = () => {}
) => {
	if (!videoOrIframe) {
		return
	}

	if (
		videoOrIframe.matches('video[autoplay]') ||
		videoOrIframe.matches('iframe[src*="youtu"][src*="autoplay=1"]') ||
		videoOrIframe.matches('iframe[src*="vimeo"][src*="autoplay=1"]')
	) {
		onPlay()
		playVideo(videoOrIframe)
	} else {
		onNotPlay()
	}
}

export const handlePauseClasses = (videoOrIframe) => {
	videoOrIframe.closest(
		'.ct-media-container, .ct-dynamic-media'
	).dataset.state = 'paused'
}

export const pauseVideo = (videoOrIframe) => {
	if (!videoOrIframe) {
		return
	}

	if (videoOrIframe.matches('video')) {
		videoOrIframe.pause()
		return
	}

	if (videoOrIframe.matches('iframe[src*="youtu"]')) {
		videoOrIframe.contentWindow.postMessage(
			JSON.stringify({
				event: 'command',
				func: 'pauseVideo',
			}),
			'*'
		)
		return
	}

	if (videoOrIframe.matches('iframe[src*="vimeo"]')) {
		videoOrIframe.contentWindow.postMessage(
			JSON.stringify({
				method: 'pause',
			}),
			'*'
		)
		return
	}

	if (videoOrIframe.matches('iframe')) {
		const source = videoOrIframe.src
		videoOrIframe.src = ''
		videoOrIframe.src = source
	}
}

export const subscribeForStateChanges = (videoOrIframe, cb = () => {}) => {
	if (!videoOrIframe) {
		return
	}

	if (videoOrIframe.matches('video')) {
		cb('ready')
		videoOrIframe.addEventListener('play', () => cb('play'))
		videoOrIframe.addEventListener('pause', () => cb('pause'))
		return
	}

	if (videoOrIframe.matches('iframe[src*="youtu"]')) {
		videoOrIframe.contentWindow.postMessage(
			JSON.stringify({
				event: 'listening',
				id: 1,
				channel: 'widget',
			}),
			'*'
		)

		videoOrIframe.contentWindow.postMessage(
			JSON.stringify({
				event: 'command',
				func: 'addEventListener',
				args: ['onStateChange'],
				id: 1,
				channel: 'widget',
			}),
			'*'
		)

		window.addEventListener('message', (e) => {
			if (!e.data) {
				return
			}

			try {
				const data = JSON.parse(e.data)

				if (data.event === 'onStateChange') {
					if (data.info === 1) {
						cb('play')
					}

					if (data.info === 2) {
						cb('pause')
					}
				}
			} catch (e) {}
		})

		cb('ready')

		return
	}

	if (videoOrIframe.matches('iframe[src*="vimeo"]')) {
		window.addEventListener('message', (e) => {
			if (!e.data) {
				return
			}

			try {
				const data = JSON.parse(e.data)

				if (data.event === 'ready') {
					videoOrIframe.contentWindow.postMessage(
						JSON.stringify({
							method: 'addEventListener',
							value: 'pause',
						}),
						'*'
					)

					videoOrIframe.contentWindow.postMessage(
						JSON.stringify({
							method: 'addEventListener',
							value: 'play',
						}),
						'*'
					)

					cb(data.event)
				}

				if (data.event === 'pause' || data.event === 'play') {
					cb(data.event)
				}
			} catch (e) {}
		})

		return
	}
}
