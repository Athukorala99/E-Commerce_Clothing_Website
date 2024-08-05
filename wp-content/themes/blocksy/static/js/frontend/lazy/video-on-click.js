import {
	handlePauseClasses,
	handlePlayClasses,
	pauseVideo,
	playVideo,
	maybePlayAutoplayedVideo,
	subscribeForStateChanges,
} from '../helpers/video'

import { loadStyle } from '../../helpers'

const store = {}

const cachedFetch = (url) =>
	store[url]
		? new Promise((resolve) => {
				resolve(store[url])
				store[url] = store[url].clone()
		  })
		: new Promise((resolve) =>
				fetch(url).then((response) => {
					resolve(response)
					store[url] = response.clone()
				})
		  )

export const fetchVideoBy = (mediaId, args = {}) => {
	args = {
		ignoreVideoOptions: false,
		...args,
	}

	let url =
		ct_localizations.ajax_url +
		'?action=blocksy_get_image_video_component&media=' +
		mediaId

	if (args.ignoreVideoOptions) {
		url += '&ignore_video_options=true'
	}

	return new Promise((resolve) => {
		cachedFetch(url).then((r) => {
			if (r.status === 200) {
				r.json().then(({ success, data }) => {
					if (!success) {
						return
					}

					resolve(data)
				})
			}
		})
	})
}

const listenForStateChanges = (videoOrIframe, args = {}) => {
	args = {
		onPause: () => {},
		onPlay: () => {},

		onReady: () => {},

		...args,
	}

	if (videoOrIframe.isListeningForStateChanges) {
		return
	}

	videoOrIframe.isListeningForStateChanges = true

	subscribeForStateChanges(videoOrIframe, (e) => {
		if (e === 'pause') {
			handlePauseClasses(videoOrIframe)
			args.onPause()
		}

		if (e === 'play') {
			args.onPlay()
			handlePlayClasses(videoOrIframe)
		}

		if (e === 'ready') {
			args.onReady()
		}
	})
}

const loadVideoOrIframeViaAjax = (el) => {
	el.querySelector('.ct-video-indicator').classList.add('loading')

	fetchVideoBy(el.dataset.mediaId).then((data) => {
		const div = document.createElement('div')
		div.innerHTML = data.html
		const insertVideo = div.firstChild
		el.insertAdjacentElement('beforeend', insertVideo)

		const videoOrIframe = el.querySelector('video,iframe')

		const subscriber = () => {
			listenForStateChanges(videoOrIframe, {
				onPlay: () => {
					setTimeout(() => {
						el.querySelector(
							'.ct-video-indicator'
						).classList.remove('loading')
					}, 120)
				},

				onReady: () => {
					playVideo(videoOrIframe)
				},
			})
		}

		if (videoOrIframe.matches('video')) {
			videoOrIframe.onloadeddata = subscriber
			return
		}

		videoOrIframe.onload = subscriber
	})
}

const loadVideoWithStyles = (el) => {
	const maybeMatchingContainer =
		ct_localizations.dynamic_styles_selectors.find(
			(descriptor) =>
				'.ct-media-container[data-media-id], .ct-dynamic-media[data-media-id]' ===
				descriptor.selector
		)

	loadStyle(maybeMatchingContainer.url).then(() => {
		loadVideoOrIframeViaAjax(el)
	})
}

ctEvents.on('blocksy:frontend:flexy:slide-change', ({ instance, payload }) => {
	;[...instance.sliderContainer.querySelectorAll('video,iframe')].map(
		(videoOrIframe) => pauseVideo(videoOrIframe)
	)
	const currentSlide = instance.sliderContainer.children[
		payload.currentIndex
	].querySelector(
		'.ct-media-container[data-media-id], .ct-dynamic-media[data-media-id]'
	)

	if (!currentSlide) {
		return
	}

	const maybeVideoOrIframeFromCurrentSlide =
		currentSlide.querySelector('video,iframe')

	if (maybeVideoOrIframeFromCurrentSlide) {
		maybePlayAutoplayedVideo(maybeVideoOrIframeFromCurrentSlide)
		return
	}

	processInitialAutoplayFor(currentSlide, {
		performVisibilityCheck: false,
	})
})

const processInitialAutoplayFor = (el, args = {}) => {
	args = {
		performVisibilityCheck: true,
		...args,
	}

	if (!el.matches('[data-state*="autoplay"]')) {
		return
	}

	let isVisible = true

	if (args.performVisibilityCheck && el.closest('.flexy-items')) {
		const box = el.getBoundingClientRect()
		const parentBox = el.closest('.flexy-items').getBoundingClientRect()

		isVisible =
			box.left >= parentBox.left &&
			box.left <= parentBox.left + parentBox.width &&
			box.top >= parentBox.top &&
			box.top <= parentBox.top + parentBox.height
	}

	if (isVisible) {
		el.removeAttribute('data-state')
		loadVideoWithStyles(el)
	}
}

let mounted = false

export const mount = (el, { event }) => {
	if (!event || event.type === 'scroll') {
		if (mounted) {
			return
		}

		mounted = true
		;[
			...document.querySelectorAll('.ct-media-container[data-media-id]'),
		].map((el) => {
			processInitialAutoplayFor(el)
		})

		return
	}

	const videoOrIframe = el.querySelector('video,iframe')

	if (videoOrIframe) {
		if (el.matches('[data-state="playing"]')) {
			pauseVideo(videoOrIframe)
		} else {
			playVideo(videoOrIframe)
		}

		return
	}

	loadVideoWithStyles(el)
}
