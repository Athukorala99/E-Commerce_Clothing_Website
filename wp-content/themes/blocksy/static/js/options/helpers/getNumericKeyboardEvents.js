const round = (value, decimalPlaces = 1) => {
	const multiplier = Math.pow(10, decimalPlaces)

	const rounded = Math.round(value * multiplier + Number.EPSILON) / multiplier

	return rounded
}

export const getNumericKeyboardEvents = ({
	value,
	onChange,
	blockDecimal = false,
	handleHorizontal = false,
}) => {
	return {
		onKeyDown: (e) => {
			if (blockDecimal) {
				if (e.key === '.' || e.key === ',') {
					e.preventDefault()
				}
			}

			let step = 1
			let decimalPlaces = 0

			step = e.shiftKey ? step * 10 : step

			/**
			 * Arrow up or right
			 */
			if (e.keyCode === 38 || (handleHorizontal && e.keyCode === 39)) {
				e.preventDefault()
				onChange(parseFloat(value) + step)
			}

			/**
			 * Arrow down or left
			 */
			if (e.keyCode === 40 || (handleHorizontal && e.keyCode === 37)) {
				e.preventDefault()
				onChange(parseFloat(value) - step)
			}
		},
	}
}
