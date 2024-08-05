import { __ } from 'ct-i18n'
import { ToggleControl } from '@wordpress/components'
import { FormTokenField } from '@wordpress/components'

import { useDebounce } from '@wordpress/compose'

import { useState, createElement, useRef, useEffect } from '@wordpress/element'
import { useSelect } from '@wordpress/data'
import { store as coreStore } from '@wordpress/core-data'

const BASE_QUERY = {
	order: 'asc',
	_fields: 'id,title',
	context: 'view',
}
const EMPTY_ARRAY = []

const getPostIdByPostValue = (posts, postValue) => {
	const postId =
		postValue?.id ||
		posts?.find((post) => post.title.rendered === postValue)?.id

	if (postId) {
		return postId
	}

	const postValueLower = postValue.toLocaleLowerCase()

	return posts?.find(
		(post) => post.title.rendered.toLocaleLowerCase() === postValueLower
	)?.id
}

function PostItem({ label, post_type, postIds, onChange, hasLabel = false }) {
	const [search, setSearch] = useState('')
	const [value, setValue] = useState(EMPTY_ARRAY)
	const [suggestions, setSuggestions] = useState(EMPTY_ARRAY)
	const debouncedSearch = useDebounce(setSearch, 250)

	const { searchResults, searchHasResolved } = useSelect(
		(select) => {
			if (!search) {
				return { searchResults: EMPTY_ARRAY, searchHasResolved: true }
			}

			const { getEntityRecords, hasFinishedResolution } =
				select(coreStore)

			const selectorArgs = [
				'postType',
				post_type,
				{
					...BASE_QUERY,
					search,
					orderby: 'title',
					exclude: postIds,
					per_page: 20,
				},
			]

			return {
				searchResults: getEntityRecords(...selectorArgs),
				searchHasResolved: hasFinishedResolution(
					'getEntityRecords',
					selectorArgs
				),
			}
		},
		[search, postIds, post_type]
	)

	const existingPosts = useSelect(
		(select) => {
			if (!postIds?.length) return EMPTY_ARRAY

			const { getEntityRecords } = select(coreStore)

			return getEntityRecords('postType', post_type, {
				...BASE_QUERY,
				include: postIds,
				per_page: postIds.length,
			})
		},
		[postIds]
	)

	useEffect(() => {
		if (!postIds?.length) {
			setValue(EMPTY_ARRAY)
		}

		if (!existingPosts?.length) return

		// Returns only the existing entity ids. This prevents the component
		// from crashing in the editor, when non existing ids are provided.
		const sanitizedValue = postIds.reduce((accumulator, id) => {
			const entity = existingPosts.find((post) => post.id === id)
			if (entity) {
				accumulator.push({
					id,
					value: entity.title.rendered,
				})
			}
			return accumulator
		}, [])

		setValue(sanitizedValue)
	}, [postIds, existingPosts])

	// Update suggestions only when the query has resolved.
	useEffect(() => {
		if (!searchHasResolved) return
		setSuggestions(searchResults.map((result) => result.title.rendered))
	}, [searchResults, searchHasResolved])

	const onPostsChange = (newPostValues) => {
		const newPostIds = new Set()

		for (const postValue of newPostValues) {
			const postId = getPostIdByPostValue(searchResults, postValue)

			if (postId) {
				newPostIds.add(postId)
			}
		}

		setSuggestions(EMPTY_ARRAY)

		onChange(Array.from(newPostIds))
	}

	return (
		<FormTokenField
			label={label}
			value={value}
			onInputChange={debouncedSearch}
			suggestions={suggestions}
			onChange={onPostsChange}
			__experimentalShowHowTo={false}
			placeholder={__('Search for a post', 'blocksy')}
		/>
	)
}

const ManagePosts = ({
	attributes,
	setAttributes,

	fieldId = 'exclude_post_ids',

	label,
	excludeLabel,

	previewedPostMatchesType,
}) => {
	return (
		<>
			<PostItem
				post_type={attributes.post_type}
				postIds={attributes[fieldId] ? attributes[fieldId].ids : []}
				hasLabel
				onChange={(newPostIds) => {
					setAttributes({
						[fieldId]: {
							...attributes[fieldId],
							ids: newPostIds,
						},
					})
				}}
				label={label}
			/>

			{previewedPostMatchesType && (
				<ToggleControl
					label={excludeLabel}
					checked={attributes[fieldId].current_post}
					onChange={() =>
						setAttributes({
							[fieldId]: {
								...attributes[fieldId],
								current_post: !attributes[fieldId].current_post,
							},
						})
					}
				/>
			)}
		</>
	)
}

export default ManagePosts
