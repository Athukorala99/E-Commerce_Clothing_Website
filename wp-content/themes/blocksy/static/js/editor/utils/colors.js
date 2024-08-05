export const getColorsDefaults = (colors) =>
	Object.keys(colors).reduce((acc, key) => {
		acc[key] = {
			type: 'string',
			default: colors[key],
		}
		return acc
	}, {})

export const getColorsContexts = (colors) =>
	Object.keys(colors)
		.filter((key) => key.includes('custom'))
		.reduce((acc, key) => {
			acc[key] = key
			return acc
		}, {})
