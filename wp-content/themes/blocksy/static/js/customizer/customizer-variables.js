import { handleVariablesFor } from 'customizer-sync-helpers/dist/simplified'

export const listenToVariables = () => {
	handleVariablesFor({
		colorPalette: (value) =>
			Object.keys(value).reduce(
				(acc, key) => [
					...acc,
					{
						variable: value[key].variable
							? value[key].variable
							: `theme-palette-color-${key.replace('color', '')}`,
						type: `color:${key}`,
					},
				],
				[]
			),

		fontColor: {
			selector: ':root',
			variable: 'theme-text-color',
			type: 'color',
		},

		linkColor: [
			{
				selector: ':root',
				variable: 'theme-link-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'theme-link-hover-color',
				type: 'color:hover',
			},
		],

		formTextColor: [
			{
				selector: ':root',
				variable: 'theme-form-text-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'theme-form-text-focus-color',
				type: 'color:focus',
			},
		],

		formBorderColor: [
			{
				selector: ':root',
				variable: 'theme-form-field-border-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'theme-form-field-border-focus-color',
				type: 'color:focus',
			},
		],

		formBackgroundColor: [
			{
				selector: ':root',
				variable: 'theme-form-field-background-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'theme-form-field-background-focus-color',
				type: 'color:focus',
			},
		],

		border_color: {
			selector: ':root',
			variable: 'theme-border-color',
			type: 'color',
		},

		headingColor: {
			selector: ':root',
			variable: 'theme-headings-color',
			type: 'color',
		},

		heading_1_color: {
			selector: ':root',
			variable: 'theme-heading-1-color',
			type: 'color',
		},

		heading_2_color: {
			selector: ':root',
			variable: 'theme-heading-2-color',
			type: 'color',
		},

		heading_3_color: {
			selector: ':root',
			variable: 'theme-heading-3-color',
			type: 'color',
		},

		heading_4_color: {
			selector: ':root',
			variable: 'theme-heading-4-color',
			type: 'color',
		},

		heading_5_color: {
			selector: ':root',
			variable: 'theme-heading-5-color',
			type: 'color',
		},

		heading_6_color: {
			selector: ':root',
			variable: 'theme-heading-6-color',
			type: 'color',
		},

		buttonTextColor: [
			{
				selector: ':root',
				variable: 'theme-button-text-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'theme-button-text-hover-color',
				type: 'color:hover',
			},
		],

		buttonColor: [
			{
				selector: ':root',
				variable: 'theme-button-background-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'theme-button-background-hover-color',
				type: 'color:hover',
			},
		],

		global_quantity_color: [
			{
				selector: ':root',
				variable: 'quantity-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'quantity-hover-color',
				type: 'color:hover',
			},
		],

		global_quantity_arrows: [
			{
				selector: ':root',
				variable: 'quantity-arrows-initial-color',
				type: 'color:default',
			},

			{
				selector: ':root',
				variable: 'quantity-arrows-hover-color',
				type: 'color:hover',
			},
		],
	})
}
