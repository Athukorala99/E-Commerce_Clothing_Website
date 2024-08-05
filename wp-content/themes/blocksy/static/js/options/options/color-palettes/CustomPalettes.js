import { useContext, Fragment, createElement } from '@wordpress/element'
import { __, sprintf } from 'ct-i18n'

import PalettePreview from './PalettePreview'

import { ColorPalettesContext } from '../ct-color-palettes-picker'

const CustomPalettes = ({ setIsEditingPalettes, option, onChange, value }) => {
	const { customPalettes, setCustomPalettes } =
		useContext(ColorPalettesContext)

	return (
		<div className="ct-color-palettes-container">
			{(customPalettes || []).map((palette, index) => {
				const { id, ...colors } = palette

				return (
					<PalettePreview
						key={index}
						option={option}
						currentPalette={palette}
						hasColorRemove={false}
						onClick={() => {
							onChange({
								...value,
								...colors,
							})

							setIsEditingPalettes(false)
						}}
						renderBefore={() => (
							<Fragment>
								<label>
									{sprintf(
										__('Custom Palette #%s', 'blocksy'),
										index + 1
									)}

									<span
										data-tooltip="top"
										onClick={(e) => {
											e.preventDefault()
											e.stopPropagation()

											setCustomPalettes(
												customPalettes.filter(
													(item, i) => i !== index
												)
											)
										}}>
										<svg
											width="14"
											height="14"
											fill="currentColor"
											viewBox="0 0 24 24">
											<path d="M21.8 4.4h-4.4V3.3C17.5 1.5 16 0 14.2 0H9.8C8 0 6.5 1.5 6.5 3.3v1.1H2.2c-.6 0-1.1.5-1.1 1.1s.5 1.1 1.1 1.1h1.1v14.2c0 1.8 1.5 3.3 3.3 3.3h10.9c1.8 0 3.3-1.5 3.3-3.3V6.5h1.1c.6 0 1.1-.5 1.1-1.1s-.6-1-1.2-1zM8.7 3.3c0-.6.5-1.1 1.1-1.1h4.4c.6 0 1.1.5 1.1 1.1v1.1H8.7V3.3zm9.8 17.4c0 .6-.5 1.1-1.1 1.1H6.5c-.6 0-1.1-.5-1.1-1.1V6.5h13.1v14.2z" />
										</svg>
										<i className="ct-tooltip">
											{__('Remove', 'blocksy')}
										</i>
									</span>
								</label>
							</Fragment>
						)}
					/>
				)
			})}

			{(customPalettes || []).length === 0 && (
				<div className="ct-no-custom-palettes">
					{__('No custom palettes yet.', 'blocksy')}
				</div>
			)}
		</div>
	)
}

export default CustomPalettes
