let mounted = false

export const mount = (reference) => {
	if (mounted) {
		return
	}

	mounted = true

	if (!reference.nextElementSibling) {
		return
	}

	const target = reference.nextElementSibling

	let initialPlacement =
		reference.getBoundingClientRect().left > innerWidth / 2
			? 'left'
			: 'right'

	const referenceRect = reference.getBoundingClientRect()
	const targetRect = target.getBoundingClientRect()

	let placement = initialPlacement

	if (referenceRect.left + targetRect.width > innerWidth) {
		placement = 'left'
	}

	if (referenceRect.left - targetRect.width < 0) {
		placement = 'right'
	}

	if (
		referenceRect.left + targetRect.width > innerWidth &&
		referenceRect.right - targetRect.width < 0
	) {
		placement = initialPlacement

		let offset = 0
		let edgeOffset = 20

		if (placement === 'left') {
			offset = innerWidth - referenceRect.right - edgeOffset
		}

		if (placement === 'right') {
			offset = referenceRect.left - edgeOffset
		}

		offset = Math.round(offset)

		target.style.setProperty(
			'--theme-submenu-inline-offset',
			`${offset * -1}px`
		)
	}

	target.dataset.placement = placement
}
