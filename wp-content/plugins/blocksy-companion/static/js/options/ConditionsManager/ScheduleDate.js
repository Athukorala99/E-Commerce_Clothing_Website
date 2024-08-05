import {
	createPortal,
	useState,
	useRef,
	Fragment,
	createElement,
} from '@wordpress/element'
import classnames from 'classnames'
import { CheckboxControl } from '@wordpress/components'

import { __ } from 'ct-i18n'

import { getSettings } from '@wordpress/date'

import { TimePicker } from '@wordpress/components'

import { usePopoverMaker, OutsideClickHandler } from 'blocksy-options'

const humanReadableDays = (day) =>
	({
		monday: __('Mon', 'blocksy-companion'),
		tuesday: __('Tue', 'blocksy-companion'),
		wednesday: __('Wed', 'blocksy-companion'),
		thursday: __('Thu', 'blocksy-companion'),
		friday: __('Fri', 'blocksy-companion'),
		saturday: __('Sat', 'blocksy-companion'),
		sunday: __('Sun', 'blocksy-companion'),
	}[day])

const ScheduleDate = ({ onChange, condition }) => {
	const inputRef = useRef()
	const [isOpen, setIsOpen] = useState(false)

	const { styles, popoverProps } = usePopoverMaker({
		ref: inputRef,
		defaultHeight: 228,
		shouldCalculate: true,
	})

	const defaultValue = {
		monday: true,
		tuesday: true,
		wednesday: true,
		thursday: true,
		friday: true,
		saturday: true,
		sunday: true,
	}

	const activeDays = Object.keys(defaultValue).filter(
		(key) => (condition.payload.days || defaultValue)[key]
	)

	let preview =
		'Only ' + activeDays.map((day) => humanReadableDays(day)).join(', ')

	if (activeDays.length === 7) {
		preview = __('Every day', 'blocksy-companion')
	}

	if (
		activeDays.length === 2 &&
		activeDays.includes('saturday') &&
		activeDays.includes('sunday')
	) {
		preview = __('Only weekends', 'blocksy-companion')
	}

	if (
		activeDays.length === 5 &&
		!activeDays.includes('saturday') &&
		!activeDays.includes('sunday')
	) {
		preview = __('Only weekdays', 'blocksy-companion')
	}

	if (activeDays.length === 0) {
		preview = __('Never', 'blocksy-companion')
	}

	const defaultStart = condition.payload.time_start || '00:00'
	const defaultEnd = condition.payload.time_end || '23:59'

	const startDate = new Date()

	startDate.setHours(defaultStart.split(':')[0])
	startDate.setMinutes(defaultStart.split(':')[1], 0, 0)

	const endDate = new Date()

	endDate.setHours(defaultEnd.split(':')[0])
	endDate.setMinutes(defaultEnd.split(':')[1], 0, 0)

	const settings = getSettings()

	// To know if the current timezone is a 12 hour time with look for "a" in the time format
	// We also make sure this a is not escaped by a "/"
	const is12HourTime = /a(?!\\)/i.test(
		settings.formats.time
			.toLowerCase() // Test only the lower case a.
			.replace(/\\\\/g, '') // Replace "//" with empty strings.
			.split('')
			.reverse()
			.join('') // Reverse the string and test for "a" not followed by a slash.
	)

	return (
		<Fragment>
			<OutsideClickHandler
				className="ct-select-input"
				wrapperProps={{
					ref: inputRef,
					role: 'combobox',
					'aria-expanded': isOpen,
					'aria-haspopup': 'listbox',
					onClick: () => {
						setIsOpen(!isOpen)
					},
				}}
				onOutsideClick={(e) => {
					if (e.target.closest('.ct-select-dropdown')) {
						return
					}

					setIsOpen(false)
				}}>
				<input value={preview} onChange={() => {}} readOnly />
			</OutsideClickHandler>

			{isOpen &&
				createPortal(
					<div
						className={classnames(
							'ct-select-dropdown ct-recurring-scheduling-dropdown',
							{
								'ct-fixed': true,
							}
						)}
						{...popoverProps}
						style={styles}>
						<div className="ct-recurring-scheduling-days">
							<label className="ct-label">
								{__('Recurring Days', 'blocksy-companion')}
							</label>

							{[
								{
									key: 'monday',
									value: __('Monday', 'blocksy-companion'),
								},

								{
									key: 'tuesday',
									value: __('Tuesday', 'blocksy-companion'),
								},

								{
									key: 'wednesday',
									value: __('Wednesday', 'blocksy-companion'),
								},

								{
									key: 'thursday',
									value: __('Thursday', 'blocksy-companion'),
								},

								{
									key: 'friday',
									value: __('Friday', 'blocksy-companion'),
								},

								{
									key: 'saturday',
									value: __('Saturday', 'blocksy-companion'),
								},

								{
									key: 'sunday',
									value: __('Sunday', 'blocksy-companion'),
								},
							].map(({ key, value }) => (
								<CheckboxControl
									label={value}
									checked={
										(condition.payload.days ||
											defaultValue)[key]
									}
									onChange={() => {
										onChange({
											...condition,
											payload: {
												...condition.payload,
												days: {
													...(condition.payload
														.days || defaultValue),
													[key]: !(condition.payload
														.days || defaultValue)[
														key
													],
												},
											},
										})
									}}
								/>
							))}
						</div>

						<div className="ct-recurring-scheduling-time">
							<div className="ct-recurring-start-time">
								<label className="ct-label">
									{__('Start Time', 'blocksy-companion')}
								</label>

								<TimePicker
									is12Hour={is12HourTime}
									currentTime={startDate}
									onChange={(time) => {
										onChange({
											...condition,
											payload: {
												...condition.payload,
												time_start: wp.date.format(
													'H:i',
													time
												),
											},
										})
									}}
								/>
							</div>

							<div className="ct-recurring-stop-time">
								<label className="ct-label">
									{__('Stop Time', 'blocksy-companion')}
								</label>

								<TimePicker
									is12Hour={is12HourTime}
									currentTime={endDate}
									onChange={(time) => {
										onChange({
											...condition,
											payload: {
												...condition.payload,
												time_end: wp.date.format(
													'H:i',
													time
												),
											},
										})
									}}
								/>
							</div>
						</div>
					</div>,

					document.body
				)}
		</Fragment>
	)
}

export default ScheduleDate
