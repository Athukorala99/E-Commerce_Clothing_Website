import { useState, createElement, useRef, Fragment } from '@wordpress/element'
import { __, sprintf } from 'ct-i18n'

const EditVariableName = ({ picker, currentPalette, onChange }) => {
	const [localValue, setLocalValue] = useState('__DEFAULT__')

	const { id, [picker.id]: currentColor, ...colors } = currentPalette

	const currentValue =
		localValue === '__DEFAULT__'
			? currentPalette[picker.id].variable || picker.variableName
			: localValue

	return (
		<input
			type="text"
			value={`var(--${currentValue})`}
			onFocus={(e) => {
				e.target.select()
			}}
			readOnly
			onChange={(e) => {
				return
				if (
					Object.keys(colors).find(
						(color) =>
							(colors[color].variable ||
								'theme-palette-color-' +
									color.replace('color', '')) ===
							e.target.value
					)
				) {
					setLocalValue(e.target.value)
					return
				}

				setLocalValue('__DEFAULT__')
				document.documentElement.style.removeProperty(
					`--${
						currentPalette[picker.id].variable ||
						picker.variableName
					}`
				)
				onChange('color', {
					...currentPalette,
					[picker.id]: {
						...currentPalette[picker.id],
						variable: e.target.value,
					},
				})
			}}
		/>
	)
}

export default EditVariableName
