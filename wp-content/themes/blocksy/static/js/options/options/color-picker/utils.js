export const getComputedStyleValue = (color) => {
	if (color.indexOf('var(--') === -1) {
		return color
	}

	const varColor = color.replace(/var\(/, '').replace(/\)/, '').trim()
	const computedStyle = getComputedStyle(document.documentElement)
	const computedValue = computedStyle.getPropertyValue(varColor)

	if (computedValue) {
		return computedValue.trim().replace(/\s/g, '')
	}

	const iframe = document.querySelector('#customize-preview iframe')

	if (iframe && iframe.contentDocument.querySelector('body')) {
		const div = iframe.contentDocument.createElement('div')
		div.style.borderColor = color
		iframe.contentDocument.querySelector('body').append(div)

		const compStyles = iframe.contentWindow.getComputedStyle(div)
		const maybeCalculatedValue = compStyles.getPropertyValue('border-color')

		if (
			maybeCalculatedValue &&
			maybeCalculatedValue !== compStyles.getPropertyValue('color')
		) {
			div.remove()
			return maybeCalculatedValue.trim().replace(/\s/g, '')
		}

		div.remove()
	}

	return color
}
