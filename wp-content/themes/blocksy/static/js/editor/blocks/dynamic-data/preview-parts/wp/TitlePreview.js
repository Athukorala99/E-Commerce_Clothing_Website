import { createElement } from '@wordpress/element'
import { withSelect } from '@wordpress/data'

import { __ } from 'ct-i18n'

import { useEntityProp } from '@wordpress/core-data'

const TitlePreview = ({ attributes: { has_field_link }, postId, postType }) => {
	const [rawTitle = '', setTitle, fullTitle] = useEntityProp(
		'postType',
		postType,
		'title',
		postId
	)

	if (!rawTitle) {
		return null
	}

	if (has_field_link === 'yes') {
		return (
			<a href="#" rel="noopener noreferrer">
				{rawTitle}
			</a>
		)
	}

	return rawTitle
}

export default TitlePreview
