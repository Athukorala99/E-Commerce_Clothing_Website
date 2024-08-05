import { handleBackgroundOptionFor } from '../../../static/js/customizer/sync/variables/background'
import { maybePromoteScalarValueIntoResponsive } from 'customizer-sync-helpers/dist/promote-into-responsive'
import {
	withKeys,
	handleResponsiveSwitch,
} from '../../../static/js/customizer/sync/helpers'
import ctEvents from 'ct-events'

import {
	getRootSelectorFor,
	assembleSelector,
	mutateSelector,
} from '../../../static/js/customizer/sync/helpers'

ctEvents.on(
	'ct:footer:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['global'] = () => ({
			...handleBackgroundOptionFor({
				id: 'footerBackground',
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ panelType: 'footer' }),
						operation: 'suffix',
						to_add: '.ct-footer',
					})
				),
				responsive: true,
			}),

			footer_spacing: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ panelType: 'footer' }),
						operation: 'suffix',
						to_add: '.ct-footer',
					})
				),
				type: 'spacing',
				variable: 'footer-container-padding',
				responsive: true,
			},

			footer_boxed_offset: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ panelType: 'footer' }),
						operation: 'suffix',
						to_add: 'footer.ct-container',
					})
				),
				variable: 'footer-container-bottom-offset',
				responsive: true,
				unit: 'px',
			},

			footer_boxed_spacing: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ panelType: 'footer' }),
						operation: 'suffix',
						to_add: 'footer.ct-container',
					})
				),
				type: 'spacing',
				variable: 'footer-container-padding',
				responsive: true,
			},

			footer_container_border_radius: {
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({ panelType: 'footer' }),
						operation: 'suffix',
						to_add: '.ct-container',
					})
				),
				type: 'spacing',
				variable: 'footer-container-border-radius',
				responsive: true,
			},

			...withKeys(
				['has_reveal_effect', 'footerShadow'],
				[
					handleResponsiveSwitch({
						selector: assembleSelector(
							mutateSelector({
								selector: mutateSelector({
									selector: getRootSelectorFor({
										panelType: 'footer',
									}),
									operation: 'suffix',
									to_add: '.ct-footer',
								}),
								operation: 'container-suffix',
								to_add: '[data-footer*="reveal"]',
							})
						),
						variable: 'position',
						on: 'sticky',
						off: 'static',
						fullValue: true,
						extractValue: ({
							has_reveal_effect = {
								desktop: false,
								tablet: false,
								mobile: false,
							},
						}) => has_reveal_effect,
					}),

					{
						selector: assembleSelector(
							mutateSelector({
								selector: mutateSelector({
									selector: getRootSelectorFor({
										panelType: 'footer',
									}),
									operation: 'suffix',
									to_add: '.site-main',
								}),
								operation: 'container-suffix',
								to_add: '[data-footer*="reveal"]',
							})
						),
						type: 'box-shadow',
						variable: 'footer-box-shadow',
						responsive: true,
						fullValue: true,
						forcedOutput: true,
						extractValue: ({
							has_reveal_effect = {
								desktop: false,
								tablet: false,
								mobile: false,
							},

							footerShadow = {
								enable: true,
								h_offset: 0,
								v_offset: 30,
								blur: 50,
								spread: 0,
								inset: false,
								color: { color: 'rgba(0, 0, 0, 0.1)' },
							},
						}) => {
							let value =
								maybePromoteScalarValueIntoResponsive(
									footerShadow
								)

							if (
								!has_reveal_effect.desktop &&
								!has_reveal_effect.tablet &&
								!has_reveal_effect.mobile
							) {
								return 'CT_CSS_SKIP_RULE'
							}

							if (!has_reveal_effect.desktop) {
								value.desktop = 'none'
							}

							if (!has_reveal_effect.tablet) {
								value.tablet = 'none'
							}

							if (!has_reveal_effect.mobile) {
								value.mobile = 'none'
							}

							return value
						},
					},
				]
			),
		})
	}
)

ctEvents.on('ct:footer:sync:item:global', (changeDescriptor) => {
	const footer = document.querySelector('.ct-footer')

	if (changeDescriptor.optionId === 'has_reveal_effect') {
		let revealComponents = []

		if (changeDescriptor.optionValue.desktop) {
			revealComponents.push('desktop')
		}

		if (changeDescriptor.optionValue.tablet) {
			revealComponents.push('tablet')
		}

		if (changeDescriptor.optionValue.mobile) {
			revealComponents.push('mobile')
		}

		document.body.dataset.footer.replace(':reveal', '')

		if (revealComponents.length > 0) {
			document.body.dataset.footer += ':reveal'
		}
	}

	if (changeDescriptor.optionId === 'footer_container_structure') {
		const rows = footer.querySelectorAll('[data-row] > div')

		rows.forEach((row) => {
			row.classList.remove('ct-container-auto')
			row.classList.remove('ct-container-fluid')
			row.classList.add('ct-container')
		})

		if (changeDescriptor.optionValue === 'boxed') {
			footer.classList.add('ct-container')

			rows.forEach((row) => {
				row.classList.remove('ct-container')
				row.classList.add('ct-container-auto')
			})
		} else {
			footer.classList.remove('ct-container')
		}

		if (changeDescriptor.optionValue === 'fluid') {
			rows.forEach((row) => {
				row.classList.remove('ct-container')
				row.classList.add('ct-container-fluid')
			})
		}
	}
})
