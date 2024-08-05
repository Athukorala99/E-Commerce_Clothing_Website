import { createElement } from '@wordpress/element'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'

import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components'

const GutenbergRadio = ({ option, values, value, onChange }) => {
	let matchingChoices = Object.keys(option.choices).filter((choice) => {
		if (!option.conditions) {
			return true
		}

		if (!option.conditions[choice]) {
			return true
		}

		return matchValuesWithCondition(
			normalizeCondition(option.conditions[choice]),
			values
		)
	})

	let normalizedValue = matchingChoices.includes(value) ? value : option.value

	return (
		<ToggleGroupControl
			label={option.label}
			value={normalizedValue}
			isBlock
			onChange={onChange}>
			{matchingChoices.map((choice) => (
				<>
					<ToggleGroupControlOption
						key={choice}
						value={choice}
						label={
							<span
								dangerouslySetInnerHTML={{
									__html: option.choices[choice],
								}}
							/>
						}
					/>
				</>
			))}
		</ToggleGroupControl>
	)
}

export default GutenbergRadio
