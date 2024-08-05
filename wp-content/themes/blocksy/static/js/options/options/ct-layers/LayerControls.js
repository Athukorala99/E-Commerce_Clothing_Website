import {
	createElement,
	useContext,
	useState,
	Fragment,
} from '@wordpress/element'
import { LayersContext } from '../ct-layers'
import { __ } from 'ct-i18n'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'

const LayerControls = ({
	itemIndex,
	items,
	onChange,
	value,
	parentValue,
	provided,
}) => {
	const { removeForId, addForId, option, toggleOptionsPanel } =
		useContext(LayersContext)

	const hasOptions =
		option.settings[value.id] &&
		option.settings[value.id].options &&
		(!option.settings[value.id].options_condition ||
			(option.settings[value.id].options_condition &&
				matchValuesWithCondition(
					normalizeCondition(
						option.settings[value.id].options_condition
					),
					{
						...parentValue,
						itemIndex,
					}
				)))

	let itemsOfType = items.filter(({ id }) => id === value.id)
	let relativeIndex = itemsOfType.map(({ __id }) => __id).indexOf(value.__id)

	return (
		<div className="ct-layer-controls">
			{!option.disableHiding && (
				<button
					type="button"
					className="ct-visibility"
					onClick={(e) => {
						e.stopPropagation()
						onChange(
							items.map((l) =>
								l.__id === value.__id
									? {
											...l,
											enabled: !{
												enabled: true,
												...l,
											}.enabled,
									  }
									: l
							)
						)
					}}>
					<svg
						width="13px"
						height="13px"
						fill="currentColor"
						viewBox="0 0 24 24">
						<path
							className="ct-seen"
							d="m.9 13.5 1 .5s.1-.1.1-.2c.1-.2.2-.4.5-.7.4-.6 1-1.4 1.9-2.2C6 9.2 8.5 7.6 12 7.6s6 1.6 7.7 3.2c.8.8 1.5 1.6 1.9 2.2.2.3.4.5.5.7 0 .1.1.1.1.2l.9-.5.9-.5v-.1c0-.1-.1-.1-.1-.2-.1-.2-.3-.5-.5-.8-.5-.7-1.2-1.6-2.2-2.5-1.9-1.9-5-3.7-9.1-3.7S4.9 7.5 3 9.3c-1 .9-1.7 1.9-2.2 2.5-.2.3-.4.6-.5.8-.1.1-.1.2-.1.2L0 13c0 .1 0 .1.9.5zM12 17.7c2.7 0 4.8-2.2 4.8-4.8S14.7 8 12 8s-4.8 2.2-4.8 4.8 2.1 4.9 4.8 4.9z"
						/>
						<path
							className="ct-unseen"
							d="M16.8 12.8c0 2.7-2.2 4.8-4.8 4.8-.6 0-1.2-.1-1.8-.4L15.1 9c1 1 1.7 2.3 1.7 3.8zm7.2.3c0-.1 0-.1 0 0-.1-.2-.1-.2-.2-.3-.1-.2-.3-.5-.5-.8-.5-.7-1.2-1.6-2.2-2.5-1.1-1.1-2.6-2.1-4.5-2.9l-1.1 1.8c1.7.6 3.1 1.6 4.1 2.6.8.8 1.5 1.6 1.9 2.2.2.3.4.5.5.7 0 .1.1.1.1.2l.9-.5 1-.5zM16.2 1.4l-2.5 4.3c-.5-.1-1.1-.1-1.7-.1-4.1 0-7.2 1.9-9.1 3.7-1 .9-1.7 1.9-2.2 2.5-.2.3-.4.6-.5.8-.1.1-.1.2-.1.2L0 13l.9.5 1 .5s.1-.1.1-.2c.1-.2.2-.4.5-.7.4-.6 1-1.4 1.9-2.2C6 9.2 8.5 7.6 12 7.6h.5l-.2.4H12c-2.7 0-4.8 2.2-4.8 4.8 0 .9.3 1.8.7 2.6l-3.2 5.4 1.3.7L17.5 2.1l-1.3-.7z"
						/>
					</svg>
				</button>
			)}

			<div className="ct-layer-label" {...provided.dragHandleProps}>
				<span>
					{window._.template(
						(
							option.settings[value.id] || {
								label: value.id,
							}
						).label
					)(value).replace(
						' INDEX',
						itemsOfType.length === 1 ? '' : ` ${relativeIndex + 1}`
					)}
				</span>
			</div>

			{option.settings[value.id] &&
				option.settings[value.id].clone &&
				items.filter(({ id }) => id === value.id).length <
					(parseInt(option.settings[value.id].clone) || 1) + 1 && (
					<button
						type="button"
						className="ct-clone"
						data-tooltip="top"
						onClick={() => addForId(value.id, value)}>
						<svg
							width="10px"
							height="10px"
							fill="currentColor"
							viewBox="0 0 24 24">
							<path d="M23,24H7.7c-0.6,0-1-0.4-1-1V7.7c0-0.6,0.4-1,1-1H23c0.6,0,1,0.4,1,1V23C24,23.6,23.6,24,23,24z M8.7,22H22V8.7 H8.7V22z" />
							<path d="M17.3,16.3c0,0.6-0.4,1-1,1H1c-0.6,0-1-0.4-1-1V1c0-0.6,0.4-1,1-1h15.3c0.6,0,1,0.4,1,1V16.3z" />
						</svg>

						<i className="ct-tooltip">
							{__('Clone Item', 'blocksy')}
						</i>
					</button>
				)}

			{(option.manageable ||
				(option.settings[value.id] &&
					option.settings[value.id].clone &&
					items.filter(({ id }) => id === value.id).length > 1) ||
				!option.settings[value.id]) && (
				<button
					type="button"
					className="ct-remove"
					onClick={() => removeForId(value.__id)}>
					<svg
						width="8px"
						height="8px"
						fill="currentColor"
						viewBox="0 0 24 24">
						<path d="m12 14.7 9.3 9.3 2.7-2.7-9.3-9.3L24 2.7 21.3 0 12 9.3 2.7 0 0 2.7 9.3 12 0 21.3 2.7 24l9.3-9.3z" />
					</svg>
				</button>
			)}

			{hasOptions && (
				<button
					type="button"
					className="ct-toggle"
					onMouseDown={(e) => {
						e.stopPropagation()
					}}
					onClick={(e) => {
						e.stopPropagation()
						toggleOptionsPanel(value.__id)
					}}>
					<svg
						width="9px"
						height="9px"
						fill="currentColor"
						viewBox="0 0 24 24">
						<path
							className="ct-arrow-down"
							d="M12 21.7 0 10.8l2.3-2.5 9.7 8.9 9.7-8.9 2.3 2.5z"
						/>
						<path
							className="ct-arrow-up"
							d="M12 5.3 0 16.2l2.3 2.5L12 9.8l9.7 8.9 2.3-2.5z"
						/>
					</svg>
				</button>
			)}
		</div>
	)
}

export default LayerControls
