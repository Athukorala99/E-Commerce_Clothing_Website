import { createElement } from '@wordpress/element'
import _ from 'underscore'

import { clamp, round } from '../ct-slider'

import { __experimentalNumberControl as NumberControl } from '@wordpress/components'

const GutenbergNumberOption = ({
	value,
	option,
	option: { step = 1, markAsAutoFor },
	device,
	onChange,
}) => {
	const parsedValue =
		markAsAutoFor && markAsAutoFor.indexOf(device) > -1 ? 'auto' : value

	const min = !option.min && option.min !== 0 ? -Infinity : option.min
	const max = !option.max && option.max !== 0 ? -Infinity : option.max

	return (
		<NumberControl
			label={option.label}
			labelPosition="top"
			max={max}
			min={min}
			value={parsedValue}
			step={step}
			onBlur={() =>
				parseFloat(parsedValue)
					? onChange(round(clamp(min, max, parsedValue)))
					: []
			}
			onChange={(value, can_safely_parse) =>
				can_safely_parse && _.isNumber(parseFloat(value))
					? onChange(round(clamp(min, max, value)))
					: parseFloat(value)
					? onChange(round(Math.min(parseFloat(value), max)))
					: onChange(round(value))
			}
		/>
	)
}

export default GutenbergNumberOption
