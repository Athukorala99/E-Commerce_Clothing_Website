import { Fragment, createElement, Component } from '@wordpress/element'
import OptionsPanel from '../OptionsPanel'
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd'
import { reorder } from '../options/ct-layers'
import nanoid from 'nanoid'

import { getValueFromInput } from '../helpers/get-value-from-input'

const valueWithUniqueIds = (value) =>
	value.map((singleItem) => ({
		...singleItem,

		...(singleItem.__id
			? {}
			: {
					__id: nanoid(),
			  }),
	}))

const LayersCombined = ({ option, value, onChange }) => {
	let computedValue = Object.keys(value).reduce((acc, key) => {
		return {
			...acc,
			[key]: valueWithUniqueIds(value[key]),
		}
	}, {})

	const fullValue = Object.values(computedValue).reduce(
		(acc, value) => [...acc, ...value],
		[]
	)

	const defaultValue = Object.values(
		getValueFromInput(option['inner-options'], {})
	).reduce((acc, value) => [...acc, ...value])

	const missingItemsForRightColumn = valueWithUniqueIds([
		...defaultValue.filter(
			({ id }) => !fullValue.find(({ id: id2 }) => id === id2)
		),
	])

	computedValue.right = [
		...computedValue.right,
		...missingItemsForRightColumn,
	]

	return (
		<DragDropContext
			onDragEnd={(result) => {
				if (!result.destination) {
					return
				}

				if (
					result.destination.droppableId === result.source.droppableId
				) {
					onChange({
						...computedValue,

						[result.destination.droppableId]: reorder(
							computedValue[result.destination.droppableId],
							result.source.index,
							result.destination.index
						),
					})
					return
				}

				const current = computedValue[result.source.droppableId]
				const next = computedValue[result.destination.droppableId]
				const target = current[result.source.index]

				current.splice(result.source.index, 1)
				next.splice(result.destination.index, 0, target)

				onChange({
					...computedValue,
					[result.source.droppableId]: current,
					[result.destination.droppableId]: next,
				})
			}}>
			<OptionsPanel
				onChange={(key, optionValue) => {
					onChange({
						...computedValue,
						[key]: optionValue,
					})
				}}
				options={option['inner-options']}
				value={computedValue}
			/>
		</DragDropContext>
	)
}

export default LayersCombined
