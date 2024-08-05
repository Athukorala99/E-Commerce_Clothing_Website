import { createElement, useRef } from '@wordpress/element'
import { __ } from 'ct-i18n'

import deepEqual from 'deep-equal'

import {
	PanelBody,
	__experimentalBoxControl as BoxControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components'

import OptionsPanel from '../../options/OptionsPanel'

import { getFirstLevelOptions } from '../../options/helpers/get-value-from-input'

import { optionWithDefault } from '../../options/GenericOptionType'

const ToolsWithOptionsPanel = ({
	label,
	options,
	attributes,
	setAttributes,
}) => {
	const panelItems = getFirstLevelOptions(options)

	return (
		<ToolsPanel
			label={label}
			resetAll={() => {
				const defaultValue = Object.keys(panelItems).reduce(
					(acc, item) => {
						return {
							...acc,
							[item]: panelItems[item].value,
						}
					},
					{}
				)

				setAttributes(defaultValue)
			}}>
			{Object.keys(panelItems).map((optionId, index) => {
				const option = panelItems[optionId]

				return (
					<ToolsPanelItem
						isShownByDefault
						onDeselect={() => {
							setAttributes({
								[optionId]: option.value,
							})
						}}
						hasValue={() =>
							!deepEqual(
								option.value,
								optionWithDefault({
									value: attributes[optionId],
									option,
								})
							)
						}
						key={optionId}
						label={option.label}>
						{index === 0 && (
							<OptionsPanel
								purpose="gutenberg"
								onChange={(optionId, optionValue) => {
									setAttributes({
										[optionId]: optionValue,
									})
								}}
								options={options}
								value={attributes}
								hasRevertButton={false}
							/>
						)}
					</ToolsPanelItem>
				)
			})}
		</ToolsPanel>
	)
}

export default ToolsWithOptionsPanel
