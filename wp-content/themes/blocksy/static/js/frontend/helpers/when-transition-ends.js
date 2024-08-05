export function whenTransitionEnds(el, cb) {
	const end = () => {
		el.removeEventListener('transitionend', onEnd)
		cb()
	}

	const onEnd = (e) => {
		// Very important check.
		//
		// Sometimes transitionend event is propagated from children to parent
		// and the children transition might be shorter than the parent's one
		// and thus the parent's transitionend event is triggered too early.
		if (e.target === el) {
			end()
		}
	}

	el.addEventListener('transitionend', onEnd)
}
