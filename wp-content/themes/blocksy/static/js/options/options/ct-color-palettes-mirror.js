import {
	createContext,
	useContext,
	createElement,
	createPortal,
	useRef,
	Fragment,
	useState,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import PalettePreview from './color-palettes/PalettePreview'

const ColorPalettesMirror = ({ option, value, values, onChange }) => {
	const colorPalettesWrapper = useRef()

	// Dont persist the palettes in the database.
	const { palettes, current_palette, ...properValue } = value

	const computedValue = Object.keys(values.colorPalette).reduce(
		(finalValue, currentId) => ({
			...finalValue,
			...(currentId.indexOf('color') === 0
				? {
						[currentId]: value[currentId]
							? value[currentId]
							: values.colorPalette[currentId],
				  }
				: {}),
		}),
		{}
	)

	return (
		<div className="ct-color-palette-preview">
			<PalettePreview
				currentPalette={computedValue}
				option={option}
				onChange={(optionId, optionValue) => {
					onChange(optionValue)
				}}
			/>
		</div>
	)
}

export default ColorPalettesMirror
