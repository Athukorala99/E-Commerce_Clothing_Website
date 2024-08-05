import { createElement, useState, useContext } from '@wordpress/element'
import classnames from 'classnames'
import { __ } from 'ct-i18n'

import PredefinedPalettes from './PredefinedPalettes'
import CustomPalettes from './CustomPalettes'
import { ColorPalettesContext } from '../ct-color-palettes-picker'

const ColorPalettesModal = ({
	setIsEditingPalettes,
	option,
	value,
	onChange,
	wrapperProps = {},
}) => {
	// predefined | custom
	const [currentTabInternal, setCurrentTab] = useState('predefined')

	const { customPalettes } = useContext(ColorPalettesContext)

	const currentTab =
		(customPalettes || []).length === 0 ? 'predefined' : currentTabInternal

	return (
		<div className="ct-modal-content" {...wrapperProps}>
			<h2 className="ct-modal-title">
				{__('Color Palette Presets', 'blocksy')}
			</h2>

			<div className="ct-tabs-scroll">
				<div className="ct-tabs">
					{(customPalettes || []).length > 0 && (
						<ul>
							{[
								{
									id: 'predefined',
									title: __('Predefined', 'blocksy'),
								},
								{
									id: 'custom',
									title: __('Custom', 'blocksy'),
								},
							].map(({ id, title }) => (
								<li
									className={classnames({
										active: currentTab === id,
									})}
									onClick={() => setCurrentTab(id)}>
									{title}
								</li>
							))}
						</ul>
					)}

					<div className="ct-current-tab">
						{currentTab === 'predefined' && (
							<PredefinedPalettes
								setIsEditingPalettes={setIsEditingPalettes}
								option={option}
								onChange={onChange}
								value={value}
							/>
						)}
						{currentTab === 'custom' && (
							<CustomPalettes
								setIsEditingPalettes={setIsEditingPalettes}
								option={option}
								onChange={onChange}
								value={value}
							/>
						)}
					</div>
				</div>
			</div>
		</div>
	)
}

export default ColorPalettesModal
