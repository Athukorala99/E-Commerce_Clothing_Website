import { createElement } from '@wordpress/element'

import { __ } from 'ct-i18n'

import useCommentsData from '../../hooks/use-comments-data'

import { useEntityRecords } from '@wordpress/core-data'

const CommentsPreview = ({
	postId,
	postType,
	attributes: { has_field_link, zero_text, single_text, multiple_text },
}) => {
	const { commentsData } = useCommentsData({ postId })

	const comments_num =
		commentsData && commentsData.total ? commentsData.total : 0

	const commentsText =
		comments_num === 0
			? zero_text
			: comments_num === 1
			? single_text
			: multiple_text

	if (has_field_link === 'yes') {
		return (
			<a href="#" rel="noopener noreferrer">
				{commentsText.replace('%', comments_num)}
			</a>
		)
	}

	return commentsText.replace('%', comments_num)
}

export default CommentsPreview
