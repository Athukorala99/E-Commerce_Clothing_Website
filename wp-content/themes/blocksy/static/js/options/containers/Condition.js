import { createElement, useMemo, useEffect } from '@wordpress/element'
import OptionsPanel from '../OptionsPanel'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'
import { useDeviceManagerState } from '../../customizer/components/useDeviceManager'

import useForceUpdate from './use-force-update'

const pendingRequests = []

const Condition = ({
	renderingChunk,
	value,
	onChange,
	purpose,
	parentValue,
	hasRevertButton,
}) => {
	const forceUpdate = useForceUpdate()
	const { currentView } = useDeviceManagerState()

	useEffect(() => {
		renderingChunk.map(
			(conditionOption) =>
				conditionOption.global &&
				Object.keys(conditionOption.condition).map((key) =>
					wp.customize(key, (val) =>
						val.bind((to) => setTimeout(() => forceUpdate()))
					)
				)
		)
	}, [])

	return renderingChunk.map((conditionOption) => {
		let valueForCondition = null

		if (conditionOption.values_source === 'global') {
			let allReplaces = Array.isArray(conditionOption.perform_replace)
				? conditionOption.perform_replace
				: [conditionOption.perform_replace]

			let conditionToWatch = {
				...conditionOption.condition,
				...(conditionOption.perform_replace
					? (Array.isArray(conditionOption.perform_replace)
							? conditionOption.perform_replace
							: [conditionOption.perform_replace]
					  ).reduce((res, singleReplace) => {
							return {
								...res,
								...conditionOption.perform_replace.condition,
							}
					  }, {})
					: {}),
			}

			valueForCondition = Object.keys(conditionToWatch).reduce(
				(current, key) => ({
					...current,
					[key.split(':')[0]]: wp.customize(key.split(':')[0])(),
				}),
				{}
			)
		}

		if (conditionOption.values_source === 'parent') {
			valueForCondition = parentValue
		}

		if (!valueForCondition) {
			valueForCondition = {
				...value,
				wp_customizer_current_view: currentView,
			}
		}

		if (conditionOption.perform_replace) {
			let allReplaces = Array.isArray(conditionOption.perform_replace)
				? conditionOption.perform_replace
				: [conditionOption.perform_replace]

			allReplaces.map((singleReplace) => {
				let conditionReplaceMatches = matchValuesWithCondition(
					normalizeCondition(singleReplace.condition),
					valueForCondition
				)

				if (
					conditionReplaceMatches &&
					valueForCondition[singleReplace.key] &&
					valueForCondition[singleReplace.key] === singleReplace.from
				) {
					valueForCondition[singleReplace.key] = singleReplace.to
				}
			})
		}

		valueForCondition = {
			...valueForCondition,

			...(window.ct_customizer_localizations
				? ct_customizer_localizations.conditions_override
				: {}),

			...(window.ct_localizations
				? ct_localizations.conditions_override
				: {}),
		}

		if (conditionOption.computed_fields) {
			conditionOption.computed_fields.map((computedField) => {
				if (
					computedField === 'woo_single_layout' &&
					(valueForCondition.product_view_type ===
						'columns-top-gallery' ||
						valueForCondition.product_view_type === 'top-gallery')
				) {
					valueForCondition[computedField] = [
						...(valueForCondition.woo_single_split_layout.left ||
							[]),
						...(valueForCondition.woo_single_split_layout.right ||
							[]),
					]
				}

				if (computedField === 'has_svg_logo') {
					const ids = ['custom_logo']

					if (
						valueForCondition.builderSettings &&
						valueForCondition.builderSettings
							.has_transparent_header === 'yes'
					) {
						ids.push('transparent_logo')
					}

					if (
						valueForCondition.builderSettings &&
						valueForCondition.builderSettings.has_sticky_header ===
							'yes'
					) {
						ids.push('sticky_logo')
					}

					const hasSvg = ids.some((id) => {
						const attachmentIds = [
							...new Set(
								Object.values(
									valueForCondition[id] || {}
								).filter((value) => typeof value === 'number')
							),
						]

						return attachmentIds.some((attachmentId) => {
							const attachment = wp.media
								.attachment(attachmentId)
								.toJSON()

							if (attachment && attachment.url) {
								return attachment.url.match(/\.svg$/)
							}

							if (pendingRequests.includes(attachmentId)) {
								return false
							}

							if (!pendingRequests.includes(attachmentId)) {
								pendingRequests.push(attachmentId)

								wp.media
									.attachment(attachmentId)
									.fetch()
									.then(() => {
										pendingRequests =
											pendingRequests.filter(
												(id) => id !== attachmentId
											)

										forceUpdate()
									})
							}

							return false
						})
					})

					valueForCondition['has_svg_logo'] = hasSvg ? 'yes' : 'no'
				}
			})
		}

		let conditionMatches = matchValuesWithCondition(
			normalizeCondition(conditionOption.condition),
			valueForCondition
		)

		return conditionMatches ? (
			<OptionsPanel
				purpose={purpose}
				key={conditionOption.id}
				onChange={onChange}
				options={conditionOption.options}
				value={value}
				hasRevertButton={hasRevertButton}
				parentValue={parentValue}
			/>
		) : (
			[]
		)
	})
}

export default Condition
