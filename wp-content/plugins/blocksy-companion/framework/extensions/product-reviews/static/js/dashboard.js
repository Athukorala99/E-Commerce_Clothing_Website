import { createElement } from '@wordpress/element'
import { addFilter } from '@wordpress/hooks'

import ProductReviews from './ProductReviews'

import ctEvents from 'ct-events'

addFilter(
	'blocksy.extensions.current_extension_content',
	'blocksy',
	(contentDescriptor, { extension, onExtsSync, setExtsStatus }) => {
		if (extension.name !== 'product-reviews') return contentDescriptor

		return {
			...contentDescriptor,
			content: (
				<ProductReviews
					setExtsStatus={setExtsStatus}
					extension={extension}
					onExtsSync={onExtsSync}
				/>
			),
		}
	}
)
