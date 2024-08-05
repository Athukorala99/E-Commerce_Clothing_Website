import {
	createElement,
	Fragment,
	useEffect,
	useState,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import { Switch } from 'blocksy-options'
import ConditionsManager from './ConditionsManager'
import { Overlay } from 'blocksy-options'

const DisplayCondition = ({
	option: {
		// inline | modal
		display = 'inline',

		filter = 'all',

		modalTitle = __(
			'Transparent Header Display Conditions',
			'blocksy-companion'
		),
		modalDescription = __(
			'Add one or more conditions to display the transparent header.',
			'blocksy-companion'
		),

		addConditionButtonLabel = __(
			'Add Display Condition',
			'blocksy-companion'
		),
	},
	value,
	onChange,
}) => {
	const [isEditing, setIsEditing] = useState(false)
	const [localValue, setLocalValue] = useState(null)

	if (display === 'inline') {
		return (
			<ConditionsManager
				filter={filter}
				value={value}
				onChange={onChange}
				addConditionButtonLabel={addConditionButtonLabel}
			/>
		)
	}

	return (
		<Fragment>
			<button
				className="button-primary"
				style={{ width: '100%' }}
				onClick={(e) => {
					e.preventDefault()
					setIsEditing(true)
					setLocalValue(null)
				}}>
				{Object.keys(value).length > 0
					? __('Edit Conditions', 'blocksy-companion')
					: __('Add Conditions', 'blocksy-companion')}
			</button>

			<Overlay
				items={isEditing}
				className="ct-admin-modal ct-builder-conditions-modal"
				onDismiss={() => {
					setIsEditing(false)
					setLocalValue(null)
				}}
				render={() => (
					<div className="ct-modal-content">
						<h2>{modalTitle}</h2>
						<p>{modalDescription}</p>

						<div className="ct-modal-scroll">
							<ConditionsManager
								filter={filter}
								value={localValue || value}
								onChange={(value) => {
									setLocalValue(value)
								}}
								addConditionButtonLabel={
									addConditionButtonLabel
								}
							/>
						</div>

						<div className="ct-modal-actions has-divider">
							<button
								className="button-primary"
								disabled={!localValue}
								onClick={() => {
									onChange(localValue)
									setIsEditing(false)
								}}>
								{__('Save Conditions', 'blocksy-companion')}
							</button>
						</div>
					</div>
				)}
			/>
		</Fragment>
	)
}

export default DisplayCondition
