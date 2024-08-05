import {
	createElement,
	useMemo,
	useRef,
	useEffect,
	useContext,
} from '@wordpress/element'
import OptionsPanel from '../OptionsPanel'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'
import {
	useDeviceManagerState,
	useDeviceManagerActions,
} from '../../customizer/components/useDeviceManager'
import { capitalizeFirstLetter, optionWithDefault } from '../GenericOptionType'
import deepEqual from 'deep-equal'

import ResponsiveControls from '../../customizer/components/responsive-controls'

import { getOptionLabelFor } from '../helpers/get-label'

const SingleChoice = ({
	singleChoice,
	groupOption,
	purpose,
	onChange,
	value,
	hasRevertButton,
	parentValue,
}) => {
	return (
		<div key={singleChoice.id} className="ct-labeled-group-item">
			{singleChoice.label && <label>{singleChoice.label}</label>}

			<OptionsPanel
				purpose={purpose}
				key={groupOption.id}
				onChange={onChange}
				options={{
					[singleChoice.id]: {
						...groupOption.options[singleChoice.id],
						design: 'none',
					},
				}}
				value={value}
				hasRevertButton={hasRevertButton}
				parentValue={parentValue}
			/>
		</div>
	)
}

const LabeledGroup = ({
	renderingChunk,
	value,
	onChange,
	purpose,
	parentValue,
	hasRevertButton,
}) => {
	const { currentView } = useDeviceManagerState()
	const { setDevice } = useDeviceManagerActions()

	return renderingChunk.map((groupOption) => {
		let valueForCondition = null

		if (!valueForCondition) {
			valueForCondition = {
				...value,
				wp_customizer_current_view: currentView,
			}
		}

		const totalAmountofMatched = groupOption.choices.filter(
			(singleChoice) =>
				singleChoice.condition
					? matchValuesWithCondition(
							normalizeCondition(singleChoice.condition),
							valueForCondition
					  )
					: true
		)

		let maybeLabel = getOptionLabelFor({
			id: groupOption.id,
			option: groupOption,
			values: value,
		})

		if (totalAmountofMatched.length === 0) {
			return null
		}

		if (totalAmountofMatched.length === 1) {
			return (
				<OptionsPanel
					purpose={purpose}
					onChange={onChange}
					key={groupOption.id}
					options={{
						[groupOption.choices[0].id]: {
							...groupOption.options[groupOption.choices[0].id],

							...(groupOption.divider
								? { divider: groupOption.divider }
								: {}),
						},
					}}
					value={value}
					hasRevertButton={hasRevertButton}
					parentValue={parentValue}
				/>
			)
		}

		return (
			<div
				className="ct-control"
				data-design="block"
				{...(groupOption.divider
					? { 'data-divider': groupOption.divider }
					: {})}>
				<header>
					{maybeLabel && <label>{maybeLabel}</label>}

					<button
						type="button"
						disabled={groupOption.choices.every(({ id }) =>
							deepEqual(
								groupOption.options[id].value,
								optionWithDefault({
									value: value[id],
									option: groupOption.options[id],
								})
							)
						)}
						className="ct-revert"
						onClick={() => {
							groupOption.choices.reduce(
								(previousPromise, nextChoice) => {
									return previousPromise.then(() => {
										return new Promise((r) => {
											setTimeout(() => {
												onChange(
													nextChoice.id,
													groupOption.options[
														nextChoice.id
													].value
												)
												r()
											})
										})
									})
								},
								Promise.resolve()
							)
						}}
					>
						<svg fill="currentColor" viewBox="0 0 35 35">
							<path d="M17.5,26L17.5,26C12.8,26,9,22.2,9,17.5v0C9,12.8,12.8,9,17.5,9h0c4.7,0,8.5,3.8,8.5,8.5v0C26,22.2,22.2,26,17.5,26z"/>
							<polygon points="34.5,30.2 21.7,17.5 34.5,4.8 30.2,0.5 17.5,13.3 4.8,0.5 0.5,4.8 13.3,17.5 0.5,30.2 4.8,34.5 17.5,21.7 30.2,34.5 "/>
						</svg>
					</button>

					{groupOption.responsive && (
						<ResponsiveControls
							device={currentView}
							responsiveDescriptor={groupOption.responsive}
							setDevice={setDevice}
						/>
					)}
				</header>
				<section className="ct-labeled-group">
					{totalAmountofMatched.map((singleChoice) => (
						<SingleChoice
							key={singleChoice.id}
							singleChoice={singleChoice}
							groupOption={groupOption}
							purpose={purpose}
							onChange={onChange}
							value={value}
							hasRevertButton={hasRevertButton}
							parentValue={parentValue}
						/>
					))}
				</section>
			</div>
		)
	})
}

export default LabeledGroup
