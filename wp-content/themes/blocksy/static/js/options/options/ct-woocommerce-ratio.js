import { createElement, Component, useState } from '@wordpress/element'
import cls from 'classnames'
import { __, sprintf } from 'ct-i18n'
import Ratio from './ct-ratio'

import { useCustomizerValues } from '../../customizer/controls/customizer-values-context'

const WooCommerceRatio = ({
	value,
	onChange,
	onChangeFor,
	option,

	option: {
		croppingKey = 'woocommerce_archive_thumbnail_cropping',
		customWidthKey = 'woocommerce_archive_thumbnail_cropping_custom_width',
		customHeightKey = 'woocommerce_archive_thumbnail_cropping_custom_height',
	},

	...props
}) => {
	const [values, onChangeGlobalFor] = useCustomizerValues()

	return (
		<Ratio
			onChange={(val) => {
				let isCustom = val.indexOf('/') === -1
				let [width, height] = val.split(isCustom ? ':' : '/')

				if (val === 'original') {
					onChangeGlobalFor(croppingKey, 'uncropped')
					onChange('uncropped')
					return
				}

				onChange(isCustom ? 'custom' : 'predefined')
				onChangeGlobalFor(croppingKey, 'custom')

				onChangeGlobalFor(
					customHeightKey,
					parseFloat(height || '0') || 0
				)

				onChangeGlobalFor(customWidthKey, parseFloat(width || '0') || 0)
			}}
			value={
				value === 'uncropped'
					? 'original'
					: value === '1:1'
					? `1/1`
					: `${values[customWidthKey]}${
							value === 'custom' ? ':' : '/'
					  }${values[customHeightKey]}`
			}
			option={{
				...option,
				value: '1/1',
			}}
			onChangeFor={onChangeGlobalFor}
			{...props}
			values={values}
		/>
	)
}

WooCommerceRatio.ControlEnd = () => (
	<div
		className="ct-color-modal-wrapper"
		onMouseDown={(e) => e.stopPropagation()}
		onMouseUp={(e) => e.stopPropagation()}
	/>
)

export default WooCommerceRatio
