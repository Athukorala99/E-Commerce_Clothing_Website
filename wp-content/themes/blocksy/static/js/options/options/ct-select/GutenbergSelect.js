import { createElement, useEffect } from '@wordpress/element'
import { maybeTransformUnorderedChoices } from '../../helpers/parse-choices'
import { __ } from 'ct-i18n'

import { CustomSelectControl, BaseControl } from '@wordpress/components'

const GutenbergSelect = ({
	value,
	option,
	option: {
		choices,
		tabletChoices,
		mobileChoices,
		placeholder,
		searchPlaceholder,
		defaultToFirstItem = true,
		search = false,
		inputClassName = '',
		selectInputStart,
		appendToBody = false,
	},
	onInputValueChange = () => {},
	renderItemFor = (item) => item.value,
	onChange,
	device = 'desktop',
}) => {
	let deviceChoices = choices

	if (device === 'tablet' && tabletChoices) {
		deviceChoices = tabletChoices
	}

	if (device === 'mobile' && mobileChoices) {
		deviceChoices = mobileChoices
	}

	const orderedChoices = maybeTransformUnorderedChoices(deviceChoices)

	let potentialValue =
		value || !defaultToFirstItem
			? value
			: parseInt(value, 10) === 0
			? value
			: (orderedChoices[0] || {}).key

	useEffect(() => {
		if (!appendToBody) {
			return
		}

		setTimeout(() => {
			setTempState(Math.round())
		}, 50)
	}, [])

	let maybeSelectedItem = orderedChoices.find(
		({ key }) => key === potentialValue
	)

	if (!maybeSelectedItem) {
		maybeSelectedItem = orderedChoices.find(
			({ key }) => parseInt(key) === parseInt(potentialValue)
		)
	}

	const formatedChoices = orderedChoices.map((choice) => ({
		...choice,
		name: choice.value,
	}))

	return (
		<BaseControl>
			<CustomSelectControl
				isBlock
				label={option.label}
				options={formatedChoices}
				onChange={({ selectedItem }) => onChange(selectedItem.key)}
				value={formatedChoices.find(
					(option) =>
						option.key ===
						(maybeSelectedItem || !defaultToFirstItem
							? potentialValue
							: (orderedChoices[0] || {}).key)
				)}
			/>
		</BaseControl>
	)
}

export default GutenbergSelect
