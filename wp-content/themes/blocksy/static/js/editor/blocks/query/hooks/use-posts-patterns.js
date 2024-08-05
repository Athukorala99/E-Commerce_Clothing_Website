import { parse } from '@wordpress/blocks'

let cache = null

let controller = new AbortController()

export const getPostsPatterns = () => {
	return new Promise((resolve) => {
		if (cache) {
			resolve(cache)
			return
		}

		fetch(
			`${wp.ajax.settings.url}?action=blocksy_get_posts_block_patterns`,
			{
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				method: 'POST',
				body: JSON.stringify({}),
				signal: controller.signal,
			}
		)
			.then((res) => res.json())
			.then(({ success, data }) => {
				if (!success) {
					return
				}

				cache = data.patterns.map((pattern) => {
					return {
						...pattern,

						blocks: parse(pattern.content, {
							__unstableSkipMigrationLogs: true,
						}),
					}
				})

				resolve(cache)
			})
	})
}
