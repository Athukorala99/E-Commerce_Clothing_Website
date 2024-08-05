import { whenTransitionEnds } from './helpers/when-transition-ends'

const showContent = (childrenWrap) => {
	const actualHeight = childrenWrap.getBoundingClientRect().height

	childrenWrap.style.height = '0px'
	childrenWrap.style.opacity = '0'

	requestAnimationFrame(() => {
		childrenWrap.classList.add('is-animating')

		requestAnimationFrame(() => {
			childrenWrap.style.height = `${actualHeight}px`
			childrenWrap.style.opacity = '1'

			whenTransitionEnds(childrenWrap, () => {
				childrenWrap.classList.remove('is-animating')
				childrenWrap.removeAttribute('style')
			})
		})
	})
}

const hideContent = (childrenWrap, cb) => {
	const actualHeight = childrenWrap.getBoundingClientRect().height

	childrenWrap.style.height = `${actualHeight}px`
	childrenWrap.style.opacity = '1'
	childrenWrap.classList.add('is-animating')

	requestAnimationFrame(() => {
		childrenWrap.style.height = '0px'
		childrenWrap.style.opacity = '0'

		whenTransitionEnds(childrenWrap, () => {
			childrenWrap.classList.remove('is-animating')
			childrenWrap.removeAttribute('style')

			cb()
		})
	})
}

export const mount = (el, { event }) => {
	event.stopPropagation()
	event.preventDefault()

	const childrenWrap = document.querySelector(el.dataset.target)

	if (!childrenWrap) {
		return
	}

	const isExpanded = childrenWrap.getAttribute('aria-hidden') === 'false'

	if (isExpanded) {
		hideContent(childrenWrap, () => {
			el.setAttribute('aria-expanded', 'false')
			childrenWrap.setAttribute('aria-hidden', 'true')
		})
		return
	}

	if (typeof el.dataset.closeOthers !== 'undefined') {
		const parent = el.closest('.ct-accordion-tab').parentNode
		const toggles = parent.querySelectorAll(
			'.ct-expandable-trigger[aria-expanded="true"]'
		)

		toggles.forEach((toggle) => {
			const target = document.querySelector(toggle.dataset.target)

			if (target) {
				hideContent(target, () => {
					toggle.setAttribute('aria-expanded', 'false')
					target.setAttribute('aria-hidden', 'true')
				})
			}
		})
	}

	el.setAttribute('aria-expanded', 'true')
	childrenWrap.setAttribute('aria-hidden', 'false')
	showContent(childrenWrap)
}
