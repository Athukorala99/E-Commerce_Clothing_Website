import {
	createElement,
	Component,
	createRef,
	Fragment,
} from '@wordpress/element'
import classnames from 'classnames'
import linearScale from 'simple-linear-scale'

import OutsideClickHandler from './react-outside-click-handler'

import { __ } from 'ct-i18n'

import InputWithValidCssExpression from '../components/InputWithValidCssExpression'
import { getNumericKeyboardEvents } from '../helpers/getNumericKeyboardEvents'

export const clamp = (min, max, value) => Math.max(min, Math.min(max, value))

export const round = (value, decimalPlaces = 1) => {
	const multiplier = Math.pow(10, decimalPlaces)

	const rounded = Math.round(value * multiplier + Number.EPSILON) / multiplier

	return rounded
}

var roundWholeNumbers = function (num, precision) {
	num = parseFloat(num)
	if (!precision) return num
	return Math.round(num / precision) * precision
}

const UnitsList = ({
	option,
	value,
	onChange,
	is_open,
	toggleOpen,
	currentUnit,
	getNumericValue,

	forced_current_unit,
	setForcedCurrentUnit,
}) => {
	const pickUnit = (unit) => {
		const numericValue = getNumericValue()

		let futureUnitDescriptor = option.units.find(
			({ unit: u }) => u === unit
		)

		if (Object.keys(futureUnitDescriptor).includes('min')) {
			onChange(
				`${clamp(
					option.units.find(({ unit: u }) => u === unit).min,
					option.units.find(({ unit: u }) => u === unit).max,
					numericValue === '' ? -Infinity : numericValue
				)}${unit}`
			)
		} else {
			onChange(`${numericValue}${currentUnit}`)
		}

		if (
			futureUnitDescriptor.unit === '' &&
			futureUnitDescriptor.type === 'custom'
		) {
			setForcedCurrentUnit('')
		} else {
			setForcedCurrentUnit('__DEFAULT__')
		}
	}

	let futureUnitDescriptor = option.units.find(
		({ unit: u }) => u === currentUnit
	)

	return (
		<Fragment>
			<span
				onClick={() => toggleOpen()}
				className="ct-current-value"
				data-unit={
					currentUnit ||
					(futureUnitDescriptor &&
					futureUnitDescriptor.type === 'custom'
						? __('custom', 'blocksy')
						: '')
				}>
				{currentUnit ||
					(futureUnitDescriptor &&
					futureUnitDescriptor.type === 'custom'
						? __('Custom', 'blocksy')
						: '―')}
			</span>

			<OutsideClickHandler
				className="ct-units-list"
				onOutsideClick={() => {
					if (!is_open) {
						return
					}

					toggleOpen()
				}}>
				{option.units
					.filter(({ unit }) => unit !== currentUnit)
					.map(({ unit, type }) => (
						<span
							key={unit}
							data-unit={type === 'custom' ? 'custom' : unit}
							onClick={() => {
								pickUnit(unit)
								toggleOpen()
							}}>
							{unit ||
								(type === 'custom'
									? __('Custom', 'blocksy')
									: '―')}
						</span>
					))}
			</OutsideClickHandler>
		</Fragment>
	)
}

export default class Slider extends Component {
	state = {
		is_dragging: false,
		is_open: false,
		is_empty_input: false,
		forced_current_unit: '__DEFAULT__',
	}

	el = createRef()

	hasUnitsList = () =>
		this.props.option.units && this.props.option.units.length > 1

	withDefault = (currentUnit, defaultUnit) =>
		this.props.option.units
			? this.props.option.units.find(({ unit }) => unit === currentUnit)
				? currentUnit
				: currentUnit || defaultUnit
			: currentUnit || defaultUnit

	getCurrentUnit = () => {
		if (this.state.forced_current_unit !== '__DEFAULT__') {
			return this.state.forced_current_unit
		}

		if (!this.props.option.units) {
			return ''
		}

		let defaultUnit = this.props.option.units
			? this.props.option.units[0].unit
			: ''

		if (
			this.props.value === 'NaN' ||
			this.props.value === '' ||
			this.props.value === 'CT_CSS_SKIP_RULE'
		) {
			return defaultUnit
		}

		let computedUnit = this.props.value
			.toString()
			.replace(/[0-9]/g, '')
			.replace(/\-/g, '')
			.replace(/\./g, '')
			.replace('CT_CSS_SKIP_RULE', '')

		let maybeActualUnit = this.props.option.units.find(
			({ unit }) => unit === computedUnit
		)

		if (
			computedUnit === '' &&
			this.props.value.toString() ===
				parseFloat(this.props.value).toString() &&
			!this.props.option.units.find(
				({ unit, type }) => unit === '' && !type
			)
		) {
			return defaultUnit
		}

		if (maybeActualUnit) {
			return computedUnit
		}

		return ''
	}

	getMax = () =>
		this.props.option.units
			? this.props.option.units.find(
					({ unit }) => unit === this.getCurrentUnit()
			  )?.max || 0
			: this.props.option.max

	getMin = () => {
		return this.props.option.units
			? this.props.option.units.find(
					({ unit }) => unit === this.getCurrentUnit()
			  )?.min || 0
			: this.props.option.min
	}

	getNumericValue = ({ forPosition = false } = {}) => {
		const maybeValue = parseFloat(this.props.value, 10)

		if (maybeValue === 0) {
			return maybeValue
		}

		if (!maybeValue) {
			if (forPosition) {
				if (
					this.props.option.defaultPosition &&
					this.props.option.defaultPosition === 'center'
				) {
					let min = parseFloat(this.getMin(), 10)
					let max = parseFloat(this.getMax(), 10)

					return (max - min) / 2 + min
				}

				return parseFloat(this.getMin(), 10)
			}

			return ''
		}

		return maybeValue
	}

	computeAndSendNewValue({ pageX, shiftKey }) {
		let { top, left, right, width } =
			this.el.current.getBoundingClientRect()

		let elLeftOffset = pageX - left - pageXOffset

		this.props.onChange(
			`${roundWholeNumbers(
				linearScale(
					[0, width],
					[
						parseFloat(this.getMin(), 10),
						parseFloat(this.getMax(), 10),
					],
					true
				)(
					document.body.classList.contains('rtl')
						? width - elLeftOffset
						: elLeftOffset
				),

				shiftKey ? 10 : 1
			)}${this.getCurrentUnit()}`
		)
	}

	handleMove = (event) => {
		if (!this.state.is_dragging) return
		this.computeAndSendNewValue(event)
	}

	handleUp = () => {
		this.setState({
			is_dragging: false,
		})

		this.detachEvents()
	}

	handleFocus = () => {
		if (this.isCustomValueInput()) {
			this.setState({
				forced_current_unit: this.getCurrentUnit(),
			})
		}
	}

	handleOptionRevert = () => {
		this.setState({
			forced_current_unit: '__DEFAULT__',
		})
	}

	handleBlur = () => {
		this.setState({ is_empty_input: false })

		if (this.props.option.value === 'CT_CSS_SKIP_RULE') {
			if (this.props.value === 'CT_CSS_SKIP_RULE') {
				return
			}

			if (this.getNumericValue() === '') {
				this.props.onChange('CT_CSS_SKIP_RULE')
				return
			}
		}

		if (this.props.value.toString().trim() === '') {
			this.props.onChange(this.props.option.value)
			return
		}

		this.props.onChange(
			`${clamp(
				parseFloat(this.getMin(), 10),
				parseFloat(this.getMax(), 10),
				parseFloat(this.getNumericValue(), 10)
			)}${this.getCurrentUnit()}`
		)
	}

	handleChange = (value, shouldClamp = true) => {
		if (this.props.option.value === 'CT_CSS_SKIP_RULE') {
			if (value.toString().trim() === '') {
				this.props.onChange('CT_CSS_SKIP_RULE')
				return
			}
		}

		if (this.isCustomValueInput()) {
			this.props.onChange(value)
			return
		}

		if (this.props.option.value !== '' && value.toString().trim() === '') {
			this.setState({ is_empty_input: true })
			return
		}

		this.setState({ is_empty_input: false })

		this.props.onChange(
			`${
				shouldClamp
					? clamp(
							parseFloat(this.getMin(), 10),
							parseFloat(this.getMax(), 10),
							value
					  )
					: value
			}${this.getCurrentUnit()}`
		)
	}

	attachEvents() {
		document.documentElement.addEventListener(
			'mousemove',
			this.handleMove,
			true
		)

		document.documentElement.addEventListener(
			'mouseup',
			this.handleUp,
			true
		)
	}

	detachEvents() {
		document.documentElement.removeEventListener(
			'mousemove',
			this.handleMove,
			true
		)

		document.documentElement.removeEventListener(
			'mouseup',
			this.handleUp,
			true
		)
	}

	getLeftValue() {
		return `${linearScale(
			[parseFloat(this.getMin(), 10), parseFloat(this.getMax(), 10)],
			[0, 100]
		)(
			clamp(
				parseFloat(this.getMin(), 10),
				parseFloat(this.getMax(), 10),
				parseFloat(this.getNumericValue({ forPosition: true }), 10) ===
					0
					? 0
					: parseFloat(
							this.getNumericValue({ forPosition: true }),
							10
					  )
					? parseFloat(
							this.getNumericValue({ forPosition: true }),
							10
					  )
					: parseFloat(this.getMin(), 10)
			)
		)}`
	}

	isCustomValueInput() {
		if (!this.hasUnitsList()) return false

		let maybeUnit = this.props.option.units.find(({ unit: u }) => u === '')

		if (!maybeUnit) {
			return false
		}

		return (
			this.getCurrentUnit() === '' &&
			maybeUnit.unit === '' &&
			maybeUnit.type === 'custom'
		)
	}

	render() {
		return (
			<div className="ct-option-slider">
				{this.props.beforeOption && this.props.beforeOption()}

				{this.isCustomValueInput() ? (
					<InputWithValidCssExpression
						value={
							this.state.is_empty_input ||
							this.props.value === 'NaN' ||
							(this.props.value || '')
								.toString()
								.indexOf('CT_CSS_SKIP_RULE') > -1
								? ''
								: this.props.value
						}
						inputProps={{
							...(this.props.option.ref
								? { ref: this.props.option.ref }
								: {}),
						}}
						onFocus={() => this.handleFocus()}
						onChange={(value) => {
							this.handleChange(value)
						}}
					/>
				) : (
					<div
						onMouseDown={({ pageX, pageY }) => {
							this.attachEvents()
							this.setState({ is_dragging: true })
						}}
						onClick={(e) => this.computeAndSendNewValue(e)}
						ref={this.el}
						className="ct-slider"
						{...(this.props.option.steps
							? { ['data-steps']: '' }
							: {})}>
						<div style={{ width: `${this.getLeftValue()}%` }} />
						<span
							tabIndex="0"
							{...getNumericKeyboardEvents({
								handleHorizontal: true,
								value: this.state.is_empty_input
									? 0
									: this.getNumericValue({
											forPosition: true,
									  }),
								onChange: (value) => {
									this.props.onChange(
										`${clamp(
											parseFloat(this.getMin(), 10),
											parseFloat(this.getMax(), 10),
											value
										)}${this.getCurrentUnit()}`
									)
								},
							})}
							style={{
								'--position': `${this.getLeftValue()}%`,
							}}
						/>

						{this.props.option.steps && (
							<section className={this.props.option.steps}>
								<i className="minus"></i>
								<i className="zero"></i>
								<i className="plus"></i>
							</section>
						)}
					</div>
				)}

				{!this.props.option.skipInput && (
					<div
						className={classnames('ct-slider-input', {
							// ['ct-unit-changer']: !!this.props.option.units,
							['ct-value-changer']: true,
							'no-unit-list': !this.hasUnitsList(),
							active: this.state.is_open,
						})}>
						{!this.isCustomValueInput() && (
							<input
								type="number"
								{...(this.props.option.ref
									? { ref: this.props.option.ref }
									: {})}
								step={1}
								value={
									this.state.is_empty_input
										? ''
										: this.getNumericValue()
								}
								onFocus={() => this.handleFocus()}
								onBlur={() => this.handleBlur()}
								onChange={({ target: { value } }) => {
									this.handleChange(value, false)
								}}
								{...getNumericKeyboardEvents({
									value: this.state.is_empty_input
										? 0
										: this.getNumericValue({
												forPosition: true,
										  }),
									onChange: (value) => {
										this.handleChange(value)
									},
								})}
							/>
						)}

						{!this.hasUnitsList() && (
							<span className="ct-current-value">
								{this.withDefault(
									this.getCurrentUnit(),
									this.props.option.defaultUnit || 'px'
								)}
							</span>
						)}

						{this.hasUnitsList() && (
							<UnitsList
								option={this.props.option}
								value={this.props.value}
								onChange={this.props.onChange}
								is_open={this.state.is_open}
								forced_current_unit={
									this.state.forced_current_unit
								}
								setForcedCurrentUnit={(unit) => {
									this.setState({ forced_current_unit: unit })
								}}
								toggleOpen={() =>
									this.setState({
										is_open: !this.state.is_open,
									})
								}
								currentUnit={this.getCurrentUnit()}
								getNumericValue={this.getNumericValue}
							/>
						)}
					</div>
				)}
			</div>
		)
	}
}
