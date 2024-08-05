import { createElement, Component } from '@wordpress/element'
import { DateTimePicker } from '@wordpress/components'

import { __experimentalGetSettings } from '@wordpress/date'

const LocalDateTimePicker = ({ value, onChange }) => {
	const settings = __experimentalGetSettings()

	const is12HourTime = /a(?!\\)/i.test(
		settings.formats.time
			.toLowerCase() // Test only the lower case a
			.replace(/\\\\/g, '') // Replace "//" with empty strings
			.split('')
			.reverse()
			.join('') // Reverse the string and test for "a" not followed by a slash
	)

	return (
		<div className="ct-date-time-picker">
			<DateTimePicker
				currentDate={value ? value : new Date()}
				onChange={(date) => {
					onChange(date)
				}}
				is12Hour={is12HourTime}
			/>
		</div>
	)
}

export default LocalDateTimePicker
