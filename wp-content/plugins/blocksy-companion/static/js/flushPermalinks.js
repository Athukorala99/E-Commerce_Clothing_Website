export const flushPermalinks = async () => {
	const response = await fetch(
		`${wp.ajax.settings.url}?action=blocksy_flush_permalinks`,

		{
			method: 'POST',
		}
	)

	if (response.status !== 200) {
		return
	}

	const { success } = await response.json()

	if (!success) {
		return
	}
}
