import { getColorsDefaults } from '../../utils/colors'

export const colors = {
	initialColor: '',
	customInitialColor: '',
	hoverColor: '',
	customHoverColor: '',

	borderColor: 'rgba(218, 222, 228, 0.5)',
	customBorderColor: 'rgba(218, 222, 228, 0.5)',
	borderHoverColor: 'rgba(218, 222, 228, 0.7)',
	customBorderHoverColor: 'rgba(218, 222, 228, 0.7)',

	backgroundColor: 'rgba(218, 222, 228, 0.5)',
	customBackgroundColor: 'rgba(218, 222, 228, 0.5)',
	backgroundHoverColor: 'rgba(218, 222, 228, 0.7)',
	customBackgroundHoverColor: 'rgba(218, 222, 228, 0.7)',
}

export const colorsDefaults = getColorsDefaults(colors)
