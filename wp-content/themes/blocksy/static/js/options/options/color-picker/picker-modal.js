import {
	createElement,
	Component,
	useRef,
	useCallback,
	useMemo,
	useEffect,
	createRef,
	Fragment,
} from '@wordpress/element'
import ColorPickerIris from './color-picker-iris.js'
import classnames from 'classnames'
import { sprintf, __ } from 'ct-i18n'

import { nullifyTransforms } from '../../helpers/usePopoverMaker'
import { getComputedStyleValue } from './utils.js'

export const getNoColorPropFor = (option) =>
	option.noColorTransparent ? 'transparent' : `CT_CSS_SKIP_RULE`

const focusOrOpenCustomizerSectionProps = (section) => ({
	target: '_blank',
	href: `${
		window.ct_localizations ? window.ct_localizations.customizer_url : ''
	}${encodeURIComponent(`[section]=${section}`)}`,
	...(wp && wp.customize && wp.customize.section
		? {
				onClick: (e) => {
					e.preventDefault()
					wp.customize.section(section).expand()
				},
		  }
		: {}),
})

const getLeftForEl = (modal, el) => {
	if (!modal) return
	if (!el) return

	let style = getComputedStyle(modal)

	let wrapperLeft = parseFloat(style.left)

	el = el.firstElementChild.getBoundingClientRect()

	return {
		'--option-modal-arrow-position': `${
			el.left + el.width / 2 - wrapperLeft - 6
		}px`,
	}
}

const getComputedColorValue = (color) => {
	const maybeRootVar = getComputedStyle(document.documentElement)
		.getPropertyValue(color.replace(/var\(/, '').replace(/\)/, ''))
		.trim()
		.replace(/\s/g, '')

	if (maybeRootVar) {
		return maybeRootVar
	}
}

const PickerModal = ({
	containerRef,
	el,
	value,
	picker,
	onChange,
	option,
	style,
	wrapperProps = {},
	inline_modal,
	appendToBody,
	inheritValue,
}) => {
	const palettesRef = useRef()

	useEffect(() => {
		if (!palettesRef.current) {
			palettesRef.current = (
				window.ct_customizer_localizations || window.ct_localizations
			).current_palette.map((c) =>
				c.replace('color', 'theme-palette-color-')
			)

			const styles = Object.values({ ...document.documentElement.style })

			if (styles.includes('--theme-palette-color-1')) {
				palettesRef.current = styles
					.filter((key) => key.includes('--theme-palette-color'))
					.map((key) => key.replace('--', ''))
			}
		}
	}, [])

	const getValueForPicker = useMemo(() => {
		if (value.color === getNoColorPropFor(option)) {
			return { color: '', key: 'empty' }
		}

		if ((value.color || '').indexOf(getNoColorPropFor(option)) > -1) {
			return {
				key: '',
				color: '',
			}
		}

		if (
			(value.color || '').indexOf(getNoColorPropFor(option)) > -1 &&
			picker.inherit
		) {
			return {
				key: 'picker' + inheritValue,
				color: getComputedStyleValue(inheritValue),
			}
		}

		if ((value.color || '').indexOf('var') > -1) {
			return {
				key: 'var' + value.color,
				color: value.color,
			}
		}

		return { key: 'color', color: value.color }
	}, [value, option, picker, inheritValue])

	let valueToCheck = value.color

	if (
		(value.color || '').indexOf(getNoColorPropFor(option)) > -1 &&
		picker.inherit
	) {
		valueToCheck = inheritValue
	}

	const arrowLeft = useMemo(
		() =>
			wrapperProps.ref &&
			wrapperProps.ref.current &&
			el &&
			getLeftForEl(wrapperProps.ref.current, el.current),
		[wrapperProps.ref && wrapperProps.ref.current, el && el.current]
	)

	return (
		<Fragment>
			<div
				tabIndex="0"
				className={classnames(
					`ct-color-picker-modal`,
					{
						'ct-option-modal': !inline_modal && appendToBody,
					},
					option.modalClassName
				)}
				style={{
					...arrowLeft,
					...(style ? style : {}),
				}}
				{...wrapperProps}>
				<ColorPickerIris
					onChange={(v) => onChange(v)}
					option={option}
					picker={picker}
					value={{
						...value,
						color: getValueForPicker.color,
					}}
				/>

				{!option.predefined && (
					<div className="ct-color-picker-palette">
						{(palettesRef.current || []).map((color) => (
							<span
								key={color}
								style={{
									background: `var(--${color})`,
								}}
								className={classnames({
									active: valueToCheck === `var(--${color})`,
								})}
								data-tooltip="top"
								onClick={() =>
									onChange({
										...value,
										color: `var(--${color})`,
									})
								}>
								<i className="ct-tooltip">
									{sprintf(
										__('Color %s', 'blocksy'),
										color.replace(
											'theme-palette-color-',
											''
										)
									)}
								</i>
							</span>
						))}
					</div>
				)}
			</div>
		</Fragment>
	)
}

export default PickerModal
