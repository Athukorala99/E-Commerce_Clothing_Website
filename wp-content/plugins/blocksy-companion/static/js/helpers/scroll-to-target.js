import { areWeDealingWithSafari } from 'blocksy-frontend'

export const scrollToTarget = (target) => {
	if (!target) {
		return
	}

	var start = window.scrollY
	var currentTime = null

	const animateScroll = (timestamp) => {
		if (!currentTime) currentTime = timestamp
		var progress = timestamp - currentTime

		const easeInOutQuad = (t, b, c, d) => {
			t /= d / 2
			if (t < 1) return (c / 2) * t * t + b
			t--
			return (-c / 2) * (t * (t - 2) - 1) + b
		}

		const summary = target.getBoundingClientRect()

		let offset = 0
		const maybeStickyHeader = document.querySelector(
			'[data-sticky*=yes], [data-sticky*=fixed]'
		)

		if (maybeStickyHeader) {
			offset = parseFloat(getComputedStyle(maybeStickyHeader).top) || 0
		}

		const destination =
			window.scrollY +
			summary.top -
			(parseFloat(
				getComputedStyle(document.body).getPropertyValue(
					'--header-sticky-height'
				)
			) || 0) -
			20 -
			offset

		var val = Math.max(
			easeInOutQuad(progress, start, -start, 700),
			destination
		)

		if (areWeDealingWithSafari) {
			scrollTo(0, val)

			if (progress < 700) {
				requestAnimationFrame(animateScroll)
			}
		} else {
			scrollTo(0, destination)
		}
	}

	if (areWeDealingWithSafari) {
		requestAnimationFrame(animateScroll)
	} else {
		animateScroll(0)
	}
}
