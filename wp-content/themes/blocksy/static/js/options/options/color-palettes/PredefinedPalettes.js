import { Fragment, createElement } from '@wordpress/element'
import { __, sprintf } from 'ct-i18n'

import PalettePreview from './PalettePreview'

const PredefinedPalettes = ({
	setIsEditingPalettes,
	option,
	onChange,
	value,
}) => {
	return (
		<div className="ct-color-palettes-container">
			{option.palettes.map((palette, index) => {
				const { id, ...colors } = palette

				const isActive = Object.keys(colors).every(
					(key) => colors[key].color === value[key].color
				)

				return (
					<PalettePreview
						key={palette.id}
						option={option}
						currentPalette={palette}
						isActive={isActive}
						renderBefore={() => (
							<label>
								{sprintf(
									__('Palette #%s', 'blocksy'),
									index + 1
								)}
							</label>
						)}
						onClick={() => {
							setIsEditingPalettes(false)
							onChange({
								...value,
								...colors,
							})
						}}
					/>
				)
			})}
		</div>
	)
}

export default PredefinedPalettes
