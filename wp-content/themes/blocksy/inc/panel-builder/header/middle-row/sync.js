import { handleBackgroundOptionFor } from '../../../../static/js/customizer/sync/variables/background'
import ctEvents from 'ct-events'
import { updateAndSaveEl } from '../../../../static/js/customizer/sync'
import {
	withKeys,
	disableTransitionsStart,
	disableTransitionsEnd,
	responsiveClassesFor,
	getRootSelectorFor,
	assembleSelector,
	mutateSelector,
} from '../../../../static/js/customizer/sync/helpers'

import { getRowBackgroundVariables } from './sync/background'

export const handleRowVariables = ({ itemId }) => ({
	...getRowBackgroundVariables({ itemId }),

	headerRowHeight: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		variable: 'height',
		responsive: true,
		unit: 'px',
	},

	headerRowShadow: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		type: 'box-shadow',
		variable: 'theme-box-shadow',
		forceOutput: true,
		responsive: true,
	},

	...withKeys(
		[
			'headerRowTopBorder',
			'transparentHeaderRowTopBorder',
			'stickyHeaderRowTopBorder',
			'headerRowTopBorderFullWidth',
		],
		[
			{
				selector: assembleSelector(getRootSelectorFor({ itemId })),
				variable: 'theme-border-top',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					headerRowTopBorder,
					headerRowTopBorderFullWidth,
				}) =>
					headerRowTopBorderFullWidth === 'yes'
						? headerRowTopBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> div',
					})
				),
				variable: 'theme-border-top',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					headerRowTopBorder,
					headerRowTopBorderFullWidth,
				}) =>
					headerRowTopBorderFullWidth !== 'yes'
						? headerRowTopBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'el-prefix',
						to_add: '[data-transparent-row="yes"]',
					})
				),

				variable: 'theme-border-top',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					transparentHeaderRowTopBorder,
					headerRowTopBorderFullWidth,
				}) =>
					headerRowTopBorderFullWidth === 'yes'
						? transparentHeaderRowTopBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '> div',
						}),
						operation: 'el-prefix',
						to_add: '[data-transparent-row="yes"]',
					})
				),

				variable: 'theme-border-top',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					transparentHeaderRowTopBorder,
					headerRowTopBorderFullWidth,
				}) =>
					headerRowTopBorderFullWidth !== 'yes'
						? transparentHeaderRowTopBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						to_add: '[data-sticky*="yes"]',
					})
				),

				variable: 'theme-border-top',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					stickyHeaderRowTopBorder,
					headerRowTopBorderFullWidth,
				}) =>
					headerRowTopBorderFullWidth === 'yes'
						? stickyHeaderRowTopBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '> div',
						}),
						to_add: '[data-sticky*="yes"]',
					})
				),

				variable: 'theme-border-top',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					stickyHeaderRowTopBorder,
					headerRowTopBorderFullWidth,
				}) =>
					headerRowTopBorderFullWidth !== 'yes'
						? stickyHeaderRowTopBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},
		]
	),

	...withKeys(
		[
			'headerRowBottomBorder',
			'transparentHeaderRowBottomBorder',
			'stickyHeaderRowBottomBorder',
			'headerRowBottomBorderFullWidth',
		],
		[
			{
				selector: assembleSelector(getRootSelectorFor({ itemId })),
				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					headerRowBottomBorder,
					headerRowBottomBorderFullWidth,
				}) =>
					headerRowBottomBorderFullWidth === 'yes'
						? headerRowBottomBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'suffix',
						to_add: '> div',
					})
				),
				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					headerRowBottomBorder,
					headerRowBottomBorderFullWidth,
				}) =>
					headerRowBottomBorderFullWidth !== 'yes'
						? headerRowBottomBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						operation: 'el-prefix',
						to_add: '[data-transparent-row="yes"]',
					})
				),

				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					transparentHeaderRowBottomBorder,
					headerRowBottomBorderFullWidth,
				}) =>
					headerRowBottomBorderFullWidth === 'yes'
						? transparentHeaderRowBottomBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '> div',
						}),
						operation: 'el-prefix',
						to_add: '[data-transparent-row="yes"]',
					})
				),

				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					transparentHeaderRowBottomBorder,
					headerRowBottomBorderFullWidth,
				}) =>
					headerRowBottomBorderFullWidth !== 'yes'
						? transparentHeaderRowBottomBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ itemId }),
						to_add: '[data-sticky*="yes"]',
					})
				),

				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					stickyHeaderRowBottomBorder,
					headerRowBottomBorderFullWidth,
				}) =>
					headerRowBottomBorderFullWidth === 'yes'
						? stickyHeaderRowBottomBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: mutateSelector({
							selector: getRootSelectorFor({ itemId }),
							operation: 'suffix',
							to_add: '> div',
						}),
						to_add: '[data-sticky*="yes"]',
					})
				),

				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					stickyHeaderRowBottomBorder,
					headerRowBottomBorderFullWidth,
				}) =>
					headerRowBottomBorderFullWidth !== 'yes'
						? stickyHeaderRowBottomBorder
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},
		]
	),

	// Transparent
	transparentHeaderRowShadow: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId }),
				operation: 'el-prefix',
				to_add: '[data-transparent-row="yes"]',
			})
		),

		type: 'box-shadow',
		variable: 'theme-box-shadow',
		forceOutput: true,
		responsive: true,
	},

	// Sticky
	stickyHeaderRowShadow: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId }),
				to_add: '[data-sticky*="yes"]',
			})
		),

		type: 'box-shadow',
		variable: 'theme-box-shadow',
		forceOutput: true,
		responsive: true,
	},

	header_row_border_radius: {
		selector: assembleSelector(getRootSelectorFor({ itemId })),
		type: 'spacing',
		variable: 'row-border-radius',
		responsive: true,
	},
})

export const handleRowOptions = ({
	selector,
	changeDescriptor: { optionId, optionValue, values },
}) => {
	if (optionId === 'headerRowVisibility') {
		updateAndSaveEl(selector, (el) => {
			responsiveClassesFor(optionValue, el)
		})
	}

	if (optionId === 'headerRowHeight') {
		ctEvents.trigger('blocksy:sticky:compute')
	}

	if (optionId === 'headerRowWidth') {
		updateAndSaveEl(
			selector,
			(el) => {
				el.classList.add('ct-disable-transitions')

				if (!optionValue.desktop) {
					optionValue = {
						desktop: optionValue,
						mobile: optionValue,
					}
				}

				el.firstElementChild.classList.remove(
					'ct-container',
					'ct-container-fluid'
				)

				el.dataset.row = `${el.dataset.row.split(':')[0]}${
					optionValue.desktop === 'boxed' ? ':boxed' : ''
				}`

				el.firstElementChild.classList.add(
					optionValue.desktop === 'fluid'
						? 'ct-container-fluid'
						: 'ct-container'
				)

				setTimeout(() => {
					el.classList.remove('ct-disable-transitions')
				}, 100)
			},
			{ onlyView: 'desktop' }
		)

		updateAndSaveEl(
			selector,
			(el) => {
				el.classList.add('ct-disable-transitions')
				if (!optionValue.desktop) {
					optionValue = {
						desktop: optionValue,
						mobile: optionValue,
					}
				}

				el.firstElementChild.classList.remove(
					'ct-container',
					'ct-container-fluid'
				)

				el.dataset.row = `${el.dataset.row.split(':')[0]}${
					optionValue.mobile === 'boxed' ? ':boxed' : ''
				}`

				el.firstElementChild.classList.add(
					optionValue.mobile === 'fluid'
						? 'ct-container-fluid'
						: 'ct-container'
				)

				setTimeout(() => {
					el.classList.remove('ct-disable-transitions')
				}, 100)
			},
			{ onlyView: 'mobile' }
		)
	}
}

ctEvents.on(
	'ct:header:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['middle-row'] = handleRowVariables
	}
)

ctEvents.on('ct:header:sync:item:middle-row', (changeDescriptor) =>
	handleRowOptions({
		selector: '[data-row*="middle"]',
		changeDescriptor,
	})
)
