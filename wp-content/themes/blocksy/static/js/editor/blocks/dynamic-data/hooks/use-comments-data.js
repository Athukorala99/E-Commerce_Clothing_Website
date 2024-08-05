import { useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'
import { addQueryArgs } from '@wordpress/url'

const cache = {}

const useCommentsData = ({ postId }) => {
	const [commentsData, setCommentsData] = useState(null)

	useEffect(() => {
		if (cache[postId]) {
			setCommentsData(cache[postId])
		}

		if (!cache[postId]) {
			apiFetch({
				path: addQueryArgs('/wp/v2/comments', {
					post: postId,
					_fields: 'id',
				}),
				method: 'HEAD',
				parse: false,
			}).then((res) => {
				const data = {
					total: parseInt(res.headers.get('X-WP-Total')),
				}

				setCommentsData(data)
				cache[postId] = data
			})
		}
	}, [postId])

	return {
		commentsData,
	}
}

export default useCommentsData
