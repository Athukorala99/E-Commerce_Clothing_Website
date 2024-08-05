import {
	createElement,
	useState,
	useEffect,
	useCallback,
} from '@wordpress/element'

const cachedFetch = {}

const useDynamicPreview = (block, attributes, formatContent) => {
	const [preview, setPreview] = useState([])
	const [isLoading, setIsLoading] = useState([])

	const fetchAttributes = useCallback(async () => {
		const body = new FormData()

		const data = JSON.stringify(attributes)

		body.append('action', 'blocksy_get_dynamic_block_view')
		body.append('block', block)
		body.append('attributes', data)

		if (cachedFetch[data]) {
			setPreview(formatContent(cachedFetch[data]))
			setIsLoading(false)
		} else {
			if (!preview) {
				setIsLoading(true)
			}

			fetch(
				(window.ct_localizations || ct_customizer_localizations)
					?.ajax_url || wp.ajax.settings.url,
				{
					method: 'POST',
					body,
				}
			)
				.then((res) => res.json())
				.then(({ data: { content } }) => {
					setPreview(formatContent(content))
					setIsLoading(false)
					cachedFetch[data] = content
				})
		}
	}, [attributes, preview])

	useEffect(() => {
		fetchAttributes()
	}, [attributes])

	return {
		isLoading,
		preview,
	}
}

export default useDynamicPreview
