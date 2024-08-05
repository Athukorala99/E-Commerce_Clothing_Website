import { isString, isObject, isNumber } from './primitive-types'

export const getFirstLevelOptions = (options, hasInnerOptions = true) => {
	const { __CT_KEYS_ORDER__, ...rest } = options

	return Object.keys(rest).reduce((currentOptions, currentOptionId) => {
		if (!options[currentOptionId].type) {
			return {
				...currentOptions,
				...getFirstLevelOptions(
					options[currentOptionId],
					hasInnerOptions
				),
			}
		}

		if (options[currentOptionId].options) {
			return {
				...currentOptions,
				...getFirstLevelOptions(
					options[currentOptionId].options,
					hasInnerOptions
				),
			}
		}

		if (options[currentOptionId]['inner-options'] && hasInnerOptions) {
			return {
				...currentOptions,
				[currentOptionId]: options[currentOptionId],
				...getFirstLevelOptions(
					options[currentOptionId]['inner-options'],
					hasInnerOptions
				),
			}
		}

		return {
			...currentOptions,
			[currentOptionId]: options[currentOptionId],
		}
	}, {})
}

export const flattenOptions = (options) =>
	Object.keys(options).reduce(
		(result, currentId) => ({
			...result,

			...(options[currentId].type
				? { [currentId]: options[currentId] }
				: currentId === '__CT_KEYS_ORDER__'
				? { [currentId]: options[currentId] }
				: flattenOptions(options[currentId])),
		}),
		{}
	)

export const getValueFromInput = (
	options,
	values,
	valueGetter = null,
	hasInnerOptions = true
) => {
	let firstLevelOptions = getFirstLevelOptions(options, hasInnerOptions)

	return {
		...values,
		...Object.keys(firstLevelOptions).reduce(
			(currentValues, currentOptionId) => {
				let actualValue = null

				if (Object.keys(values).indexOf(currentOptionId) > -1) {
					if (
						isString(values[currentOptionId]) ||
						isNumber(values[currentOptionId])
					) {
						actualValue = values[currentOptionId]
					}

					if (
						isObject(values[currentOptionId]) &&
						!Array.isArray(values[currentOptionId])
					) {
						actualValue = {
							...(firstLevelOptions[currentOptionId].value || {}),
							...values[currentOptionId],
						}
					}

					if (Array.isArray(values[currentOptionId])) {
						actualValue = values[currentOptionId]
							? values[currentOptionId]
							: [
									...(firstLevelOptions[currentOptionId]
										.value || []),
									// ...values[currentOptionId],
							  ]
					}
				} else if (valueGetter) {
					return {
						...currentValues,
						...valueGetter(
							currentOptionId,
							firstLevelOptions[currentOptionId]
						),
					}
				} else {
					if (
						Object.keys(firstLevelOptions[currentOptionId]).indexOf(
							'value'
						) > -1
					) {
						actualValue = firstLevelOptions[currentOptionId].value
					} else {
						actualValue = ''
					}
				}

				return {
					...currentValues,
					[currentOptionId]: actualValue,
				}
			},
			{}
		),
	}
}
