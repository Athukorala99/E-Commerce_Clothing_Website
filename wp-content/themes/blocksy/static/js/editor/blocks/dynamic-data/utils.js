import { __ } from 'ct-i18n'

export const getLabelForProvider = (provider) => {
	return (
		{
			wp: 'WordPress',
			woo: 'WooCommerce',
			acf: 'ACF',
			metabox: 'MetaBox',
			custom: __('Custom', 'blocksy'),
			toolset: 'Toolset',
			jetengine: 'Jet Engine',
			pods: 'Pods',
		}[provider] || __('Unknown', 'blocksy')
	)
}

export const fieldIsImageLike = (fieldDescriptor) => {
	if (fieldDescriptor.provider === 'wp') {
		return (
			fieldDescriptor.id === 'featured_image' ||
			fieldDescriptor.id === 'author_avatar'
		)
	}

	return fieldDescriptor.type === 'image'
}
