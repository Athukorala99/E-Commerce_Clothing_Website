import { useMemo, useState, useEffect } from '@wordpress/element'

const cache = {}

const useCustomFieldData = ({ postId, fieldDescriptor }) => {
	const [fieldData, setFieldData] = useState(cache)

	const cacheKey = useMemo(() => {
		return `${postId}-${fieldDescriptor.provider}-${fieldDescriptor.id}`
	}, [postId, fieldDescriptor.provider, fieldDescriptor.id])

	useEffect(() => {
		if (!cache[cacheKey]) {
			fetch(
				`${wp.ajax.settings.url}?action=blocksy_dynamic_data_block_custom_field_data`,
				{
					headers: {
						Accept: 'application/json',
						'Content-Type': 'application/json',
					},
					method: 'POST',
					body: JSON.stringify({
						post_id: postId,
						field_provider: fieldDescriptor.provider,
						field_id: fieldDescriptor.id,
					}),
				}
			)
				.then((res) => res.json())
				.then(({ success, data }) => {
					if (!success) {
						return
					}

					cache[cacheKey] = {
						value: data.field_data,
					}

					setFieldData({
						...fieldData,
						[cacheKey]: {
							value: data.field_data,
						},
					})
				})
		}
	}, [
		postId,
		fieldDescriptor.provider,
		fieldDescriptor.id,
		cacheKey,
		fieldData,
	])

	return {
		fieldData: fieldData[cacheKey] ? fieldData[cacheKey] : null,
	}
}

export default useCustomFieldData
