import { createElement } from '@wordpress/element'
import { format, getSettings } from '@wordpress/date'
import { withSelect } from '@wordpress/data'

import { __ } from 'ct-i18n'

import { useEntityProp } from '@wordpress/core-data'

const DatePreview = ({
	postId,
	postType,

	attributes: {
		date_type,
		default_format,
		date_format,
		custom_date_format,
		has_field_link,
	},
}) => {
	const [date] = useEntityProp(
		'postType',
		postType,
		date_type === 'published' ? 'date' : 'modified',
		postId
	)

	const dateFormat =
		default_format === 'yes'
			? getSettings().formats.date
			: date_format !== 'custom'
			? date_format
			: custom_date_format

	let content = <span>{format(dateFormat, date)}</span>

	if (has_field_link) {
		content = <a href="#">{content}</a>
	}

	return content
}

export default DatePreview
