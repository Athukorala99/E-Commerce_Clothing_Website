import {
	createContext,
	useContext,
	createElement,
	createPortal,
	useRef,
	Fragment,
	useState,
	useEffect,
} from '@wordpress/element'
import OptionsPanel from '../OptionsPanel'
import { __ } from 'ct-i18n'
import classnames from 'classnames'
import PalettePreview from './color-palettes/PalettePreview'
import ColorPalettesModal from './color-palettes/ColorPalettesModal'

import usePopoverMaker from '../helpers/usePopoverMaker'
import OutsideClickHandler from './react-outside-click-handler'

import { Transition } from '@react-spring/web'
import bezierEasing from 'bezier-easing'

import Overlay from '../../customizer/components/Overlay'

import { Dropdown } from '@wordpress/components'

export const ColorPalettesContext = createContext({
	isEditingPalettes: false,
})

const ColorPalettes = ({ option, value, onChange }) => {
	const { isEditingPalettes, setIsEditingPalettes, customPalettes } =
		useContext(ColorPalettesContext)
	const colorPalettesWrapper = useRef()

	// Dont persist the palettes in the database.
	const { palettes, current_palette, ...properValue } = value

	return (
		<div className="ct-color-palette-preview">
			<PalettePreview
				currentPalette={properValue}
				option={option}
				onChange={(optionId, optionValue) => {
					onChange(optionValue)
				}}
			/>

			<Overlay
				items={isEditingPalettes}
				className={classnames(
					'ct-admin-modal ct-color-palettes-modal',
					{
						'ct-no-tabs': (customPalettes || []).length === 0,
						'ct-has-tabs': (customPalettes || []).length > 0,
					}
				)}
				onDismiss={() => setIsEditingPalettes(false)}
				render={() => (
					<ColorPalettesModal
						onChange={(value) => {
							onChange(value)
						}}
						setIsEditingPalettes={setIsEditingPalettes}
						value={properValue}
						option={option}
					/>
				)}
			/>
		</div>
	)
}

ColorPalettes.MetaWrapper = ({ getActualOption }) => {
	const [isEditingPalettes, setIsEditingPalettes] = useState(false)
	const [customPalettes, setCustomPalettes] = useState([])

	useEffect(() => {
		fetch(
			`${window.ajaxurl}?action=blocksy_get_custom_palettes`,

			{
				method: 'POST',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({}),
			}
		)
			.then((response) => response.json())
			.then((data) => {
				if (data.data.palettes) {
					setCustomPalettes(data.data.palettes)
				}
			})
	}, [])

	return (
		<ColorPalettesContext.Provider
			value={{
				customPalettes,
				setCustomPalettes: (palettes) => {
					setCustomPalettes(palettes)
					fetch(
						`${window.ajaxurl}?action=blocksy_sync_custom_palettes`,

						{
							method: 'POST',
							headers: {
								Accept: 'application/json',
								'Content-Type': 'application/json',
							},
							body: JSON.stringify({
								palettes,
							}),
						}
					)
						.then((response) => response.json())
						.then((data) => {})
				},
				isEditingPalettes,
				setIsEditingPalettes,
			}}>
			{getActualOption()}
		</ColorPalettesContext.Provider>
	)
}

ColorPalettes.LabelToolbar = ({ option, value, onChange }) => {
	const { setIsEditingPalettes, customPalettes, setCustomPalettes } =
		useContext(ColorPalettesContext)

	const canSave = ![...option.palettes, ...(customPalettes || [])].find(
		(palette) => {
			const actualColors = Object.keys(value).reduce(
				(finalValue, currentId) => ({
					...finalValue,
					...(currentId.indexOf('color') === 0
						? {
								[currentId]: value[currentId].color,
						  }
						: {}),
				}),
				{}
			)

			const paletteColors = Object.keys(palette).reduce(
				(finalValue, currentId) => ({
					...finalValue,
					...(currentId.indexOf('color') === 0
						? {
								[currentId]: palette[currentId].color,
						  }
						: {}),
				}),
				{}
			)

			if (
				Object.keys(actualColors).length !==
				Object.keys(paletteColors).length
			) {
				return false
			}

			return Object.keys(actualColors).every((key) => {
				return actualColors[key] === paletteColors[key]
			})
		}
	)

	return (
		<Fragment>
			<Dropdown
				contentClassName="ct-options-popover"
				popoverProps={{ placement: 'bottom-start', offset: 3 }}
				renderToggle={({ isOpen, onToggle }) => (
					<span
						className="ct-more-options-trigger"
						data-tooltip="top">
						<button
							className="components-button components-dropdown-menu__toggle is-small has-icon"
							onClick={(e) => {
								e.preventDefault()
								onToggle()
							}}>
							<svg
								viewBox="0 0 24 24"
								width="24"
								height="24"
								fill="currentColor">
								<path d="M13 19h-2v-2h2v2zm0-6h-2v-2h2v2zm0-6h-2V5h2v2z"></path>
							</svg>
						</button>

						<i className="ct-tooltip">
							{__('Advanced', 'blocksy')}
						</i>
					</span>
				)}
				renderContent={({ onClose }) => (
					<div className="components-dropdown-menu__menu">
						<div className="components-menu-group">
							<button
								className="components-button components-menu-item__button"
								onClick={(e) => {
									e.preventDefault()
									setIsEditingPalettes(true)
									onClose()
								}}>
								<span className="components-menu-item__item">
									{__('Color Palettes', 'blocksy')}
								</span>
							</button>

							<button
								className="components-button components-menu-item__button"
								disabled={!canSave}
								onClick={(e) => {
									e.preventDefault()
									onClose()

									// Dont persist the palettes in the database.
									const {
										palettes,
										current_palette,
										...properValue
									} = value

									setCustomPalettes([
										...customPalettes,
										properValue,
									])
								}}>
								<span className="components-menu-item__item">
									{__('Save Palette', 'blocksy')}
								</span>
							</button>
						</div>

						<div className="components-menu-group">
							<button
								className="components-button components-menu-item__button"
								onClick={(e) => {
									e.preventDefault()

									onClose()

									// Dont persist the palettes in the database.
									const {
										palettes,
										current_palette,
										...properValue
									} = value

									const allColors = Object.keys(properValue)
										.filter(
											(key) => key.indexOf('color') > -1
										)
										.map((key) =>
											parseFloat(key.replace('color', ''))
										)
										.sort((a, b) => a - b)

									onChange({
										...properValue,
										[`color${
											allColors[allColors.length - 1] + 1
										}`]: {
											color: 'CT_CSS_SKIP_RULE',
										},
									})
								}}>
								<span className="components-menu-item__item">
									{__('Add New Color', 'blocksy')}
								</span>
							</button>
						</div>
					</div>
				)}
			/>
		</Fragment>
	)
}

export default ColorPalettes
