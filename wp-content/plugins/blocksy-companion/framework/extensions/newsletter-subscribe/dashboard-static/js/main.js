import { createElement } from '@wordpress/element'
import { addFilter } from '@wordpress/hooks'

import NewsletterSubscribe from './NewsletterSubscribe'

addFilter(
	'blocksy.extensions.current_extension_content',
	'blocksy',
	(contentDescriptor, { extension, onExtsSync }) => {
		if (extension.name !== 'newsletter-subscribe') return contentDescriptor

		return {
			...contentDescriptor,
			...(extension.data.api_key
				? {}
				: {
						activationStrategy: 'from-custom-content',
				  }),
			content: (
				<NewsletterSubscribe
					extension={extension}
					onExtsSync={onExtsSync}
				/>
			),
		}
	}
)
