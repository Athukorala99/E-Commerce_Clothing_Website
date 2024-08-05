import { typographyOption } from '../../../../static/js/customizer/sync/variables/typography'
import ctEvents from 'ct-events'

import {
	responsiveClassesFor,
	getRootSelectorFor,
	assembleSelector,
	mutateSelector,
} from '../../../../static/js/customizer/sync/helpers'

const handleMenuVariables = ({ itemId, panelType }) => ({
	// off canvas menu styles
	mobile_menu_items_spacing: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		variable: 'items-vertical-spacing',
		responsive: true,
		unit: 'px',
	},

	...typographyOption({
		id: 'mobileMenuFont',
		selector: assembleSelector(getRootSelectorFor({ itemId })),
	}),

	mobileMenuColor: [
		{
			selector: assembleSelector(getRootSelectorFor({ itemId })),
			variable: 'theme-link-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: assembleSelector(getRootSelectorFor({ itemId })),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
			responsive: true,
		},

		{
			selector: assembleSelector(getRootSelectorFor({ itemId })),
			variable: 'theme-link-active-color',
			type: 'color:active',
			responsive: true,
		},
	],

	...typographyOption({
		id: 'mobileMenuDropdownFont',
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId }),
				operation: 'suffix',
				to_add: '.sub-menu',
			})
		),
	}),

	mobileMenuDropdownColor: [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
			responsive: true,
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '.sub-menu',
				})
			),
			variable: 'theme-link-active-color',
			type: 'color:active',
			responsive: true,
		},
	],

	mobile_menu_items_divider: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		variable: 'mobile-menu-divider',
		type: 'border',
	},

	mobileMenuMargin: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		type: 'spacing',
		variable: 'margin',
		responsive: true,
	},

	// inline menu styles
	inline_menu_items_spacing: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		variable: 'menu-items-spacing',
		responsive: true,
		extractValue: (val) => {
			return val
		},
		unit: 'px',
	},

	inline_menu_horizontal_alignment: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		variable: 'horizontal-alignment',
		responsive: true,
		unit: '',
	},

	...typographyOption({
		id: 'inline_mobile_menu_font',
		selector: assembleSelector(getRootSelectorFor({ itemId })),
	}),

	// default state
	inline_menu_font_color: [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({ itemId }),
					operation: 'suffix',
					to_add: '> ul > li > a',
				})
			),
			variable: 'theme-link-active-color',
			type: 'color:active',
		},
	],

	// transparent state
	transparent_inline_menu_font_color: [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-transparent-row="yes"]',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-transparent-row="yes"]',
				})
			),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-transparent-row="yes"]',
				})
			),
			variable: 'theme-link-active-color',
			type: 'color:active',
		},
	],

	// sticky state
	sticky_inline_menu_font_color: [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> ul > li > a',
					}),
					operation: 'between',
					to_add: '[data-sticky*="yes"]',
				})
			),
			variable: 'theme-link-active-color',
			type: 'color:active',
		},
	],

	inline_menu_margin: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		type: 'spacing',
		variable: 'margin',
		important: true,
	},
})

ctEvents.on(
	'ct:header:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['mobile-menu'] = ({ itemId, panelType }) =>
			handleMenuVariables({
				itemId,
			})

		variableDescriptors['mobile-menu-secondary'] = ({
			itemId,
			panelType,
		}) =>
			handleMenuVariables({
				itemId,
				panelType,
			})
	}
)

export const handleMenuOptions = ({
	selector,
	changeDescriptor: { optionId, optionValue, values },
}) => {
	const el = document.querySelector(selector)

	if (optionId === 'inline_menu_stretch_menu') {
		el.removeAttribute('data-stretch')

		el.classList.add('ct-disable-transitions')

		if (values.inline_menu_stretch_menu === 'yes') {
			el.dataset.stretch = ''
		}

		setTimeout(() => {
			el.classList.remove('ct-disable-transitions')
		}, 500)
	}

	if (optionId === 'inline_menu_visibility') {
		responsiveClassesFor(optionValue, el)
	}
}

ctEvents.on('ct:header:sync:item:mobile-menu', (changeDescriptor) => {
	const selector = '[data-id="mobile-menu"]'
	handleMenuOptions({ selector, changeDescriptor })
})

ctEvents.on('ct:header:sync:item:mobile-menu-secondary', (changeDescriptor) => {
	const selector = '[data-id="mobile-menu-secondary"]'
	handleMenuOptions({ selector, changeDescriptor })
})
