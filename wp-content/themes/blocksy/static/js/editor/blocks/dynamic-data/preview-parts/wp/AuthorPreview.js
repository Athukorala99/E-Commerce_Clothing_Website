import { createElement } from '@wordpress/element'

import { useSelect } from '@wordpress/data'
import { store as coreStore } from '@wordpress/core-data'

import { __ } from 'ct-i18n'

const getAuthorFiledValue = (author, author_field) => {
	switch (author_field) {
		case 'email':
			return author?.email || ''
		case 'nicename':
			return author?.nickname || ''
		case 'display_name':
			return author?.nickname || ''
		case 'first_name':
			return author?.first_name || ''
		case 'last_name':
			return author?.last_name || ''
		case 'description':
			return author?.description || ''
		default:
			break
	}
}

const AuthorPreview = ({
	postId,
	postType,
	attributes: { has_field_link, author_field },
	fallback,
}) => {
	const { authorId, authorDetails } = useSelect(
		(select) => {
			const { getEditedEntityRecord, getUser, getUsers } =
				select(coreStore)

			const _authorId = getEditedEntityRecord(
				'postType',
				postType,
				postId
			)?.author

			return {
				authorId: _authorId,
				authorDetails: _authorId ? getUser(_authorId) : null,
			}
		},
		[postType, postId]
	)

	if (!authorDetails) {
		return null
	}

	if (has_field_link === 'yes') {
		return (
			<a href="#" rel="noopener noreferrer">
				{getAuthorFiledValue(authorDetails, author_field) || fallback}
			</a>
		)
	}

	return getAuthorFiledValue(authorDetails, author_field) || fallback
}

export default AuthorPreview
