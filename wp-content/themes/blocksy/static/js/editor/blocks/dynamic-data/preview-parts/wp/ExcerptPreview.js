import { createElement, RawHTML } from '@wordpress/element'
import { withSelect } from '@wordpress/data'
import { __ } from 'ct-i18n'

import { useEntityProp } from '@wordpress/core-data'

const strippedRenderedExcerpt = (content) => {
	if (!content) return ''

	const document = new window.DOMParser().parseFromString(
		content,
		'text/html'
	)

	return document.body.textContent || document.body.innerText || ''
}

const ExcerptPreview = ({
	attributes: { excerpt_length },
	postId,
	postType,
	fallback,
}) => {
	const [
		rawExcerpt,
		setExcerpt,
		{ rendered: renderedExcerpt, protected: isProtected } = {},
	] = useEntityProp('postType', postType, 'excerpt', postId)

	const [rawContent, setContent, { rendered: renderedContent } = {}] =
		useEntityProp('postType', postType, 'content', postId)

	const rawOrRenderedExcerpt = (
		rawExcerpt ||
		strippedRenderedExcerpt(renderedExcerpt) ||
		strippedRenderedExcerpt(renderedContent)
	).trim()

	let trimmedExcerpt = rawOrRenderedExcerpt
		.split(' ', excerpt_length)
		.join(' ')

	const maybeMore = trimmedExcerpt !== rawOrRenderedExcerpt ? '...' : ''

	if (!trimmedExcerpt) {
		return fallback
	}

	return (
		<RawHTML>
			{trimmedExcerpt}
			{maybeMore}
		</RawHTML>
	)
}

export default ExcerptPreview
