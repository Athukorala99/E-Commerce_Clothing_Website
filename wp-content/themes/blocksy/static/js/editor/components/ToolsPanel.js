import { useState, createElement } from '@wordpress/element'
import classnames from 'classnames'

import { __experimentalGrid as Grid } from '@wordpress/components'

import ToolsPanelHeader from './ToolsPanel/ToolsPanelHeader'

const ToolsPanel = ({
	className,
	attributes,
	setAttributes,
	resetAll,
	items,
	label,
}) => {
	const idsWithValue = items.reduce(
		(result, group) => {
			return [
				...result,
				...group.items
					.filter((item) => item.hasValue())
					.map((item) => item.label),
			]
		},

		[]
	)

	const [temporarilyOpenedItems, setTemporarilyOpenedItems] = useState([])

	const selectedItems = [
		...new Set([...temporarilyOpenedItems, ...idsWithValue]),
	]

	return (
		<div className={classnames('ct-tools-panel', className)}>

			<ToolsPanelHeader
				label={label}
				resetAll={() => {
					setTemporarilyOpenedItems([])
					resetAll()
				}}
				items={items}
				selectedItems={selectedItems}
				attributes={attributes}
				setAttributes={setAttributes}
				onItemClick={(itemLabel) => {
					if (!selectedItems.includes(itemLabel)) {
						setTemporarilyOpenedItems([
							...temporarilyOpenedItems,
							itemLabel,
						])

						return
					}

					const item = items
						.reduce((acc, group) => [...acc, ...group.items], [])
						.find((item) => item.label === itemLabel)

					setTemporarilyOpenedItems(
						temporarilyOpenedItems.filter(
							(item) => item !== itemLabel
						)
					)

					item.reset()
				}}
			/>

			<div className="ct-tools-panel-items">
				{items
					.reduce((acc, group) => [...acc, ...group.items], [])
					.filter((item) => selectedItems.includes(item.label))
					.map((item) => {
						return item.render()
					})}
			</div>
		</div>
	)
}

export default ToolsPanel
