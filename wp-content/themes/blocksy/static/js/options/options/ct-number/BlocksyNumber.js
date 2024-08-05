import { createElement } from '@wordpress/element'
import _ from 'underscore'
import classnames from 'classnames'

import { clamp, round } from '../ct-slider'
import { getNumericKeyboardEvents } from '../../helpers/getNumericKeyboardEvents'

const BlocksyNumberOption = ({
	value,
	option,
	option: { attr, step = 1, markAsAutoFor },
	device,
	onChange,
	liftedOptionStateDescriptor,
}) => {
	const { liftedOptionState, setLiftedOptionState } =
		liftedOptionStateDescriptor

	const parsedValue =
		markAsAutoFor && markAsAutoFor.indexOf(device) > -1 ? 'auto' : value

	const min = !option.min && option.min !== 0 ? -Infinity : option.min
	const max = !option.max && option.max !== 0 ? Infinity : option.max

	return (
		<div
			className={classnames('ct-option-number', {
				[`ct-reached-limits`]:
					parseFloat(parsedValue) === parseInt(min) ||
					parseFloat(parsedValue) === parseInt(max),
			})}
			{...(attr || {})}>
			<a
				className={classnames('ct-minus', {
					['ct-disabled']: parseFloat(parsedValue) === parseInt(min),
				})}
				onClick={() =>
					onChange(
						round(
							clamp(
								min,
								max,
								parseFloat(parsedValue) - parseFloat(step)
							)
						)
					)
				}
			/>

			<a
				className={classnames('ct-plus', {
					['ct-disabled']: parseFloat(parsedValue) === parseInt(max),
				})}
				onClick={() =>
					onChange(
						round(
							clamp(
								min,
								max,
								parseFloat(parsedValue) + parseFloat(step)
							)
						)
					)
				}
			/>

			<input
				type="number"
				step={1}
				value={
					liftedOptionState && liftedOptionState.isEmptyInput
						? ''
						: parsedValue
				}
				onBlur={() => {
					setLiftedOptionState({
						isEmptyInput: false,
					})

					if (parseFloat(parsedValue)) {
						onChange(round(clamp(min, max, parsedValue)))
					}
				}}
				onChange={({ target: { value } }) => {
					if (value.toString().trim() === '') {
						setLiftedOptionState({
							isEmptyInput: true,
						})
						return
					}

					setLiftedOptionState({
						isEmptyInput: false,
					})

					_.isNumber(parseFloat(value))
						? onChange(round(value))
						: parseFloat(value)
						? onChange(round(Math.min(parseFloat(value), max)))
						: onChange(round(value))
				}}
				{...getNumericKeyboardEvents({
					blockDecimal: true,
					value: parsedValue,
					onChange: (value) => {
						onChange(round(clamp(min, max, value)))
					},
				})}
			/>
		</div>
	)
}

export default BlocksyNumberOption
