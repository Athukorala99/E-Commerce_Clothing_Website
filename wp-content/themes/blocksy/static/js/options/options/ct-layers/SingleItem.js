import {
	createElement,
	Component,
	createContext,
	useState,
	Fragment,
	useContext,
} from '@wordpress/element'
import { LayersContext } from '../ct-layers'
import classnames from 'classnames'

import LayerControls from './LayerControls'

import OptionsPanel from '../../OptionsPanel'
import { getValueFromInput } from '../../helpers/get-value-from-input'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'

const SingleItem = ({
	value,
	items,
	onChange,
	index,
	provided,
	snapshot,
	className,

	values,
	parentValues,
}) => {
	const itemIndex = items
		.map(({ __id }) => __id)
		.indexOf(value.__id)
		.toString()

	let { option, isDragging, isOpen, parentValue } = useContext(LayersContext)

	let finalProps = {
		...provided.draggableProps,
		style: {
			...(provided.draggableProps.style || {}),

			...(provided.draggableProps.style.transform
				? {
						transform:
							'translate(0px' +
							provided.draggableProps.style.transform.slice(
								provided.draggableProps.style.transform.indexOf(
									','
								),
								provided.draggableProps.style.transform.length
							),
				  }
				: {}),
		},
	}

	let layerHasContent =
		option.settings[value.id] &&
		option.settings[value.id].options &&
		Object.keys(option.settings[value.id].options).length > 0 &&
		isOpen === value.__id &&
		(!isDragging || (isDragging && isDragging !== isOpen))

	return (
		<li
			className={classnames('ct-layer', option.itemClass, className, {
				[`ct-disabled`]: !{ enabled: true, ...value }.enabled,
				[`ct-active`]: layerHasContent,
			})}
			ref={provided.innerRef}
			{...finalProps}>
			<LayerControls
				items={items}
				onChange={onChange}
				value={value}
				parentValue={parentValue}
				itemIndex={itemIndex}
				provided={provided}
			/>

			{layerHasContent && (
				<div className="ct-layer-content">
					<OptionsPanel
						parentValue={parentValue}
						onChange={(key, newValue) => {
							if (
								option.settings[value.id].sync &&
								option.settings[value.id].clone
							) {
								let totalItems = items.filter(
									({ id }) => id === value.id
								).length

								let idForSync = `${
									option.settings[value.id].sync.id
								}_first`

								if (
									totalItems > 1 &&
									items
										.filter(({ id }) => id === value.id)
										.map(({ __id }) => __id)
										.indexOf(value.__id) > 0
								) {
									idForSync = `${
										option.settings[value.id].sync.id
									}_second`
								}

								wp.customize &&
									wp.customize.previewer &&
									wp.customize.previewer.send(
										'ct:sync:refresh_partial',
										{
											id: idForSync,
										}
									)
							}

							onChange(
								items.map((l) =>
									l.__id === value.__id
										? {
												...l,
												[key]: newValue,
										  }
										: l
								)
							)
						}}
						value={getValueFromInput(
							option.settings[value.id].options,
							{
								...(option.value.filter(
									({ id }) => id === value.id
								).length > 1
									? option.value.filter(
											({ id }) => value.id === id
									  )[
											items
												.filter(
													({ id }) => id === value.id
												)
												.map(({ __id }) => __id)
												.indexOf(value.__id)
									  ]
									: {}),
								...value,
								itemIndex,
							}
						)}
						options={option.settings[value.id].options}
					/>
				</div>
			)}
		</li>
	)
}

export default SingleItem
