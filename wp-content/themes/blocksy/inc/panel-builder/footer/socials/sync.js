import ctEvents from 'ct-events'
import { handleResponsiveSwitch } from '../../../../static/js/customizer/sync/helpers'
import {
	responsiveClassesFor,
	getRootSelectorFor,
	assembleSelector,
	mutateSelector,
	getColumnSelectorFor,
} from '../../../../static/js/customizer/sync/helpers'
import { typographyOption } from '../../../../static/js/customizer/sync/variables/typography'

ctEvents.on(
	'ct:footer:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['socials'] = ({ fullItemId, itemId }) => ({
			socialsIconSize: {
				selector: assembleSelector(
					getRootSelectorFor({ itemId, panelType: 'footer' })
				),
				variable: 'theme-icon-size',
				responsive: true,
				unit: 'px',
			},

			socialsIconSpacing: {
				selector: assembleSelector(
					getRootSelectorFor({ itemId, panelType: 'footer' })
				),
				variable: 'items-spacing',
				responsive: true,
				unit: 'px',
			},

			footerSocialsAlignment: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({
							itemId,
							panelType: 'footer',
						}),
						operation: 'replace-last',
						to_add: getColumnSelectorFor({
							itemId: fullItemId,
						}),
					})
				),
				variable: 'horizontal-alignment',
				responsive: true,
				unit: '',
			},

			footerSocialsVerticalAlignment: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({
							itemId,
							panelType: 'footer',
						}),
						operation: 'replace-last',
						to_add: getColumnSelectorFor({
							itemId: fullItemId,
						}),
					})
				),
				variable: 'vertical-alignment',
				responsive: true,
				unit: '',
			},

			...typographyOption({
				id: 'footer_socials_label_font',

				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({
							itemId,
							panelType: 'footer',
						}),
						operation: 'suffix',
						to_add: '.ct-label',
					})
				),
			}),

			footer_socials_font_color: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({
								itemId,
								panelType: 'footer',
							}),
							operation: 'suffix',
							to_add: 'a',
						})
					),
					variable: 'theme-link-initial-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({
								itemId,
								panelType: 'footer',
							}),
							operation: 'suffix',
							to_add: 'a',
						})
					),
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			footerSocialsIconColor: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({
								itemId,
								panelType: 'footer',
							}),
							operation: 'suffix',
							to_add: '[data-color="custom"]',
						})
					),
					variable: 'theme-icon-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({
								itemId,
								panelType: 'footer',
							}),
							operation: 'suffix',
							to_add: '[data-color="custom"]',
						})
					),
					variable: 'theme-icon-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			footerSocialsIconBackground: [
				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({
								itemId,
								panelType: 'footer',
							}),
							operation: 'suffix',
							to_add: '[data-color="custom"]',
						})
					),
					variable: 'background-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: assembleSelector(
						mutateSelector({
							selector: getRootSelectorFor({
								itemId,
								panelType: 'footer',
							}),
							operation: 'suffix',
							to_add: '[data-color="custom"]',
						})
					),
					variable: 'background-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			footerSocialsMargin: {
				selector: assembleSelector(
					getRootSelectorFor({ itemId, panelType: 'footer' })
				),
				type: 'spacing',
				variable: 'margin',
				responsive: true,
				// important: true
			},

			footer_socials_direction: {
				selector: assembleSelector(
					getRootSelectorFor({ itemId, panelType: 'footer' })
				),
				variable: 'items-direction',
				responsive: true,
				unit: '',
			},
		})
	}
)

ctEvents.on(
	'ct:footer:sync:item:socials',
	({ itemId, optionId, optionValue, values }) => {
		const el = document.querySelector(`.ct-footer [data-id="${itemId}"]`)

		if (optionId === 'socialsType' || optionId === 'socialsFillType') {
			const box = el.querySelector('.ct-social-box')

			box.dataset.iconsType = `${values.socialsType}${
				values.socialsType === 'simple'
					? ''
					: `:${values.socialsFillType || 'solid'}`
			}`
		}

		if (optionId === 'socialsIconSize') {
			el.querySelector('.ct-social-box').dataset.size =
				values.socialsIconSize
		}

		if (optionId === 'socialsLabelVisibility') {
			;[...el.querySelectorAll('.ct-label')].map((label) => {
				responsiveClassesFor(optionValue, label)
			})
		}

		if (optionId === 'footer_socials_visibility') {
			responsiveClassesFor(optionValue, el)
		}
	}
)
