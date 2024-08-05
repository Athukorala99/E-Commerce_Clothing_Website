import {
	createElement,
	Component,
	createContext,
	useState,
	Fragment,
	useMemo,
} from '@wordpress/element'
import classnames from 'classnames'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'

import arrayMove from 'array-move'

import { getValueFromInput } from '../helpers/get-value-from-input'
import nanoid from 'nanoid'

import SelectThatAddsItems from './ct-layers/SelectThatAddsItems'
import SingleItem from './ct-layers/SingleItem'

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd'

export const reorder = (list, startIndex, endIndex) => {
	const result = Array.from(list)
	const [removed] = result.splice(startIndex, 1)
	result.splice(endIndex, 0, removed)

	return result
}

const valueWithUniqueIds = (value) =>
	value
		.filter((singleItem) => singleItem)
		.map((singleItem) => ({
			...singleItem,

			...(singleItem.__id
				? {}
				: {
						__id: nanoid(),
				  }),
		}))

export const itemsThatAreNotAdded = (value, option) =>
	Object.keys(option.settings).filter(
		(optionId) => !value.find(({ id }) => id === optionId)
	)

const getDefaultState = () => ({
	currentlyPickedItem: null,
	isDragging: false,
	isOpen: false,
})

export const LayersContext = createContext(getDefaultState())

const { Provider, Consumer } = LayersContext

const Layers = ({ value, option, onChange, values }) => {
	const [state, setState] = useState(getDefaultState())

	const addForId = (idToAdd, val = {}) => {
		onChange([
			...(value || []),
			{
				id: idToAdd,
				enabled: true,
				...getValueFromInput(
					option.settings[idToAdd].options || {},
					{}
				),
				...val,
				__id: nanoid(),
			},
		])
	}

	const computedValue = (
		option.manageable || option.grouped
			? valueWithUniqueIds(value)
			: [
					...valueWithUniqueIds(value),
					...option.value
						.filter(
							({ id }) =>
								value.map(({ id }) => id).indexOf(id) === -1
						)
						.map((item) => ({
							...item,
							__id: nanoid(),
							enabled: item?.enabled || false,
						})),
			  ]
	).filter((item) => !!option.settings[item.id])

	let withoutDragDropContext = (
		<Provider
			value={{
				...state,
				parentValue: values,
				addCurrentlySelectedItem: () => {
					const idToAdd =
						state.currentlyPickedItem ||
						itemsThatAreNotAdded(
							valueWithUniqueIds(value),
							option
						)[0]

					setState((state) => ({
						...state,
						currentlyPickedItem: null,
					}))
					addForId(idToAdd)
				},
				addForId: (id, value) => addForId(id, value),
				option: option,
				setCurrentItem: (currentlyPickedItem) =>
					setState((state) => ({
						...state,
						currentlyPickedItem,
					})),
				removeForId: (idToRemove) =>
					onChange(
						valueWithUniqueIds(value).filter(
							({ __id: id }) => id !== idToRemove
						)
					),

				toggleOptionsPanel: (idToAdd) => {
					const completeValue = [
						...value,
						...option.value.filter(
							({ id }) =>
								value.map(({ id }) => id).indexOf(id) === -1
						),
					]

					if (
						value.length > 0 &&
						completeValue.find((item) => !item.__id)
					) {
						wp.customize &&
							wp.customize.previewer &&
							wp.customize.previewer.send(
								'ct:sync:refresh_partial',
								{
									shouldSkip: true,
								}
							)

						onChange(computedValue)
					}

					setState((state) => ({
						...state,
						isOpen: state.isOpen === idToAdd ? false : idToAdd,
					}))
				},
			}}>
			{option.manageable && (
				<SelectThatAddsItems
					{...{
						value: computedValue,
						option,
					}}
				/>
			)}

			<Droppable droppableId={option.id}>
				{(provided, snapshot) => (
					<ul
						className="ct-layers"
						{...provided.droppableProps}
						ref={provided.innerRef}>
						{computedValue.map((value, index) => {
							return (
								<Draggable
									key={value.__id}
									draggableId={value.__id}
									isDragDisabled={!!option.disableDrag}
									index={index}>
									{(provided, snapshot) => (
										<SingleItem
											onChange={onChange}
											value={value}
											items={computedValue}
											provided={provided}
											snapshot={snapshot}
											className={
												option.settings[value.id]
													.condition &&
												!matchValuesWithCondition(
													normalizeCondition(
														option.settings[
															value.id
														].condition
													),
													values
												)
													? 'ct-hidden'
													: ''
											}
										/>
									)}
								</Draggable>
							)
						})}

						{provided.placeholder}
					</ul>
				)}
			</Droppable>
		</Provider>
	)

	if (option.grouped) {
		return withoutDragDropContext
	}

	return (
		<DragDropContext
			onDragEnd={(result) => {
				if (!result.destination) {
					return
				}

				onChange(
					reorder(
						computedValue,
						result.source.index,
						result.destination.index
					)
				)
			}}>
			{withoutDragDropContext}
		</DragDropContext>
	)
}

export default Layers
