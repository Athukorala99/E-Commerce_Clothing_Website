import { useEffect, useState } from '@wordpress/element'

import md5 from 'md5'

// Stable JSON serialization
// Props to: https://github.com/fraunhoferfokus/JSum
function serialize(obj) {
	if (Array.isArray(obj)) {
		return `[${obj.map((el) => serialize(el)).join(',')}]`
	} else if (typeof obj === 'object' && obj !== null) {
		let acc = ''
		const keys = Object.keys(obj).sort()
		acc += `{${JSON.stringify(keys)}`
		for (let i = 0; i < keys.length; i++) {
			acc += `${serialize(obj[keys[i]])},`
		}
		return `${acc}}`
	}

	return `${JSON.stringify(obj)}`
}

function getJsonFromUrl(url) {
	if (!url) url = location.search
	var query = url.substr(1)
	var result = {}
	query.split('&').forEach(function (part) {
		var item = part.split('=')
		result[item[0]] = decodeURIComponent(item[1])
	})
	return result
}

const cache = {}

export const usePostsBlockData = ({ attributes, previewedPostId }) => {
	const [blockData, setBlockData] = useState(null)

	let [{ controller }, setAbortState] = useState({
		controller: null,
	})

	useEffect(() => {
		const input = {
			attributes,
			previewedPostId,
		}

		const key = md5(serialize(input))

		if (cache[key]) {
			setBlockData(cache[key])
		} else {
			if (controller) {
				controller.abort()
			}

			if ('AbortController' in window) {
				controller = new AbortController()

				setAbortState({
					controller,
				})
			}

			let qs = getJsonFromUrl(location.search)

			fetch(
				`${wp.ajax.settings.url}?action=blocksy_get_posts_block_data${
					qs.lang ? '&lang=' + qs.lang : ''
				}`,
				{
					headers: {
						Accept: 'application/json',
						'Content-Type': 'application/json',
					},
					method: 'POST',
					signal: controller.signal,
					body: JSON.stringify({
						attributes,
						previewedPostId,
					}),
				}
			)
				.then((res) => res.json())
				.then(({ success, data }) => {
					if (!success) {
						return
					}

					cache[key] = data

					setAbortState({
						controller: null,
					})

					setBlockData(data)
				})
		}
	}, [attributes, previewedPostId])

	return {
		blockData,
	}
}
