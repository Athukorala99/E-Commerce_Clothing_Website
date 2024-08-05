import { createElement, useState } from '@wordpress/element'
import { __ } from 'ct-i18n'
import cls from 'classnames'
import OutsideClickHandler from './react-outside-click-handler'

import SpacingInput from './ct-spacing/input'

export const SPACING_STATE_LINKED = 1
export const SPACING_STATE_INDEPENDENT = 2
export const SPACING_STATE_CUSTOM = 3

const backportLegacySpacing = (legacy) => {
	if (legacy === 'auto' || legacy === '') {
		return {
			value: legacy,
			unit: '',
		}
	}

	const maybeNumber = parseFloat(legacy)

	if (isNaN(maybeNumber)) {
		return {
			value: '',
			unit: '',
		}
	}

	return {
		value: maybeNumber,
		unit: legacy.toString().replace(maybeNumber.toString(), ''),
	}
}

const Spacing = ({ value: maybeLegacyValue, option, onChange }) => {
	const [isOpen, setIsOpen] = useState(false)

	const units = [
		{ unit: 'px' },
		{ unit: '%' },
		{ unit: 'em' },
		{ unit: 'rem' },
		{ unit: 'pt' },
	]

	let value = maybeLegacyValue.values
		? maybeLegacyValue
		: {
				values: [
					backportLegacySpacing(maybeLegacyValue.top),
					backportLegacySpacing(maybeLegacyValue.right),
					backportLegacySpacing(maybeLegacyValue.bottom),
					backportLegacySpacing(maybeLegacyValue.left),
				],
				custom: '',
				state: maybeLegacyValue.linked
					? SPACING_STATE_LINKED
					: SPACING_STATE_INDEPENDENT,
		  }

	const currentUnit =
		value.values.find((v) => v.value !== 'auto').unit || units[0].unit

	return (
		<div
			className={cls('ct-option-spacing', {
				linked: value.state === SPACING_STATE_LINKED,
				custom: value.state === SPACING_STATE_CUSTOM,
			})}>
			<SpacingInput
				currentUnit={currentUnit}
				value={value}
				option={option}
				onChange={onChange}
			/>

			<div
				className={cls('ct-spacing-controls ct-value-changer', {
					active: isOpen,
				})}>
				{value.state !== SPACING_STATE_CUSTOM && (
					<span
						className="ct-link-unlink-toggle"
						onClick={(e) => {
							e.preventDefault()

							if (value.state === SPACING_STATE_LINKED) {
								onChange({
									...value,
									state: SPACING_STATE_INDEPENDENT,
								})

								return
							}

							const futureValue = value.values.find((v) => {
								return v.value !== 'auto' && v.value !== ''
							}) || {
								value: '',
								unit: '',
							}

							onChange({
								...value,

								values: [
									value.values[0].value === 'auto'
										? value.values[0]
										: futureValue,

									value.values[1].value === 'auto'
										? value.values[1]
										: futureValue,

									value.values[2].value === 'auto'
										? value.values[2]
										: futureValue,

									value.values[3].value === 'auto'
										? value.values[3]
										: futureValue,
								],

								state: SPACING_STATE_LINKED,
							})
						}}>
						<svg
							width="14"
							height="14"
							viewBox="0 0 24 24"
							fill="currentColor">
							{value.state === SPACING_STATE_LINKED ? (
								<path d="M24,12c0,3.9-3.2,7.1-7.1,7.1h-2.2v-2.1h2.2c2.8,0,5.1-2.3,5.1-5.1s-2.3-5.1-5.1-5.1h-2.2V4.9h2.2 C20.8,4.9,24,8.1,24,12z M2.1,12c0-2.8,2.3-5.1,5.1-5.1h2.2V4.9H7.1C3.2,4.9,0,8.1,0,12s3.2,7.1,7.1,7.1h2.2v-2.1H7.1 C4.3,17.1,2.1,14.8,2.1,12z M8.3,13h7.3V11H8.3V13z"></path>
							) : (
								<path d="M24,12c0,3.9-3.2,7.1-7.1,7.1h-2.2v-2.1h2.2c2.8,0,5.1-2.3,5.1-5.1s-2.3-5.1-5.1-5.1h-1.8L7.5,23.4l-1.2-0.6L8,19.1H7.1 C3.2,19.1,0,15.9,0,12s3.2-7.1,7.1-7.1h2.2v2.1H7.1C4.3,6.9,2,9.2,2,12s2.3,5.1,5.1,5.1h1.8l1.8-4H7.9V11h3.8l4.7-10.3l1.2,0.6 L16,4.9h0.9C20.8,4.9,24,8.1,24,12z"></path>
							)}
						</svg>
					</span>
				)}

				<div
					onClick={() => setIsOpen(!isOpen)}
					className="ct-current-value"
					data-unit={
						value.state === SPACING_STATE_CUSTOM
							? 'custom'
							: currentUnit
					}>
					{value.state === SPACING_STATE_CUSTOM
						? __('Custom', 'blocksy')
						: currentUnit || '―'}
				</div>

				<OutsideClickHandler
					className="ct-units-list"
					onOutsideClick={() => {
						if (!isOpen) {
							return
						}

						setIsOpen(false)
					}}>
					{[
						...units,
						...(value.state === SPACING_STATE_CUSTOM
							? []
							: [{ unit: 'custom' }]),
					]
						.filter(({ unit }) => unit !== currentUnit)
						.map(({ unit }) => (
							<span
								key={unit}
								data-unit={unit}
								onClick={() => {
									if (unit === 'custom') {
										onChange({
											...value,
											state: SPACING_STATE_CUSTOM,
										})

										setIsOpen(false)

										return
									}

									let nonAutoValues = value.values
										.filter((v) => v.value !== 'auto')
										.map((v) => v.value + v.unit)

									const futureState =
										[...new Set(nonAutoValues)].length === 1
											? SPACING_STATE_LINKED
											: SPACING_STATE_INDEPENDENT

									onChange({
										...value,

										values: value.values.map((v) => ({
											...v,
											unit,
										})),

										state: futureState,
									})

									setIsOpen(false)
								}}>
								{unit === 'custom'
									? __('Custom', 'blocksy')
									: unit}
							</span>
						))}
				</OutsideClickHandler>
			</div>
		</div>
	)
}

export default Spacing
