import ctEvents from 'ct-events'
import {
	withKeys,
	handleBackgroundOptionFor,
	assembleSelector,
	mutateSelector,
	getRootSelectorFor,
} from 'blocksy-customizer-sync'

ctEvents.on(
	'ct:header:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['global'] = () => ({
			transparent_behaviour: {
				selector: assembleSelector(getRootSelectorFor()),
				variable: 'has-transparent-header',
				responsive: true,
				extractValue: (value) => ({
					desktop: value.desktop ? 'var(--true)' : 'var(--false)',
					tablet: value.mobile ? 'var(--true)' : 'var(--false)',
					mobile: value.mobile ? 'var(--true)' : 'var(--false)',
				}),
				unit: '',
			},

			...handleBackgroundOptionFor({
				id: 'headerBackground',
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor(),
						operation: 'suffix',
						to_add: '.ct-header',
					})
				),
				responsive: true,
				forced_background_image: true,
			}),

			...handleBackgroundOptionFor({
				id: 'transparentHeaderBackground',
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor(),
						operation: 'suffix',
						to_add: '[data-transparent]',
					})
				),
				responsive: true,
				forced_background_image: true,
			}),

			...handleBackgroundOptionFor({
				id: 'stickyHeaderBackground',
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor(),
						operation: 'suffix',
						to_add: '[data-sticky*="yes"]',
					})
				),
				responsive: true,
				forced_background_image: true,
			}),
		})
	}
)

ctEvents.on(
	'ct:header:sync:item:global',
	({ optionId, optionValue, values }) => {
		if (
			optionId === 'has_sticky_header' ||
			optionId === 'sticky_rows' ||
			optionId === 'sticky_behaviour'
		) {
			const { has_sticky_header, sticky_rows, sticky_behaviour } = values

			Array.from(document.querySelectorAll('[data-sticky]')).map(
				(row) => {
					row.removeAttribute('data-sticky')
				}
			)

			if (has_sticky_header === 'yes') {
				Array.from(document.querySelectorAll('[data-row]')).map(
					(row) => {
						let rowType = row.dataset.row

						if (!sticky_rows[rowType]) {
							return
						}

						let stickyResult = []

						if (sticky_behaviour.desktop) {
							stickyResult.push('desktop')
						}

						if (sticky_behaviour.mobile) {
							stickyResult.push('mobile')
						}

						row.dataset.sticky = stickyResult.join(':')
					}
				)
			}

			ctEvents.trigger('blocksy:frontend:init')
		}

		if (optionId === 'transparent_behaviour') {
			if (!document.querySelector('[data-transparent]')) {
				return
			}

			Array.from(document.querySelectorAll('[data-device]')).map(
				(device) => {
					device.removeAttribute('data-transparent')
					Array.from(device.querySelectorAll('[data-row]')).map(
						(el) => el.removeAttribute('data-transparent-row')
					)

					if (optionValue[device.dataset.device]) {
						device.dataset.transparent = ''

						Array.from(device.querySelectorAll('[data-row]')).map(
							(el) => (el.dataset.transparentRow = 'yes')
						)
					}

					ctEvents.trigger('blocksy:frontend:init')
				}
			)
		}
	}
)
