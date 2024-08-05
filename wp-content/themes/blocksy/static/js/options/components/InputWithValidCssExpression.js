import { createElement, useState, useEffect, useRef } from '@wordpress/element'
import _ from 'underscore'
import { __ } from 'ct-i18n'

// TODO: maybe extend to also support validating min max values + also non
// numeric values
const InputWithValidCssExpression = ({
	value,
	placeholder = __('Default', 'blocksy'),
	onChange,

	propertyToCheckAgainst = 'margin',

	// TODO: drop this flag when all options are refactored like spacing.
	// This component should always propagate empty values.
	shouldPropagateEmptyValue = false,

	inputProps = {},
	...restProps
}) => {
	const inputRef = useRef()
	const [localValue, setLocalValue] = useState('__DEFAULT__')

	const { ref, actualInputProps = {} } = inputProps

	useEffect(() => {
		const needToFocusOn = localValue.indexOf('()')

		if (needToFocusOn > -1) {
			inputRef.current.setSelectionRange(
				needToFocusOn + 1,
				needToFocusOn + 1
			)
		}
	}, [localValue])

	return (
		<input
			value={localValue === '__DEFAULT__' ? value : localValue}
			type="text"
			placeholder={placeholder}
			onChange={({ target: { value } }) => {
				if (value === '' && shouldPropagateEmptyValue) {
					setLocalValue('__DEFAULT__')
					onChange('')
				}

				setLocalValue(value)

				if (
					CSS.supports(propertyToCheckAgainst, value) &&
					value.toString().length -
						value.toString().replaceAll('(', '').length ===
						value.toString().length -
							value.toString().replaceAll(')', '').length
				) {
					onChange(value)
					setLocalValue('__DEFAULT__')
				}
			}}
			ref={(el) => {
				inputRef.current = el

				if (ref && typeof ref === 'function') {
					ref(el)
				}
			}}
			{...actualInputProps}
			{...restProps}
		/>
	)
}

export default InputWithValidCssExpression
