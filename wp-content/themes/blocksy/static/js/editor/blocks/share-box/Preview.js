import { createElement, useCallback, RawHTML } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { Spinner } from '@wordpress/components'
import useDynamicPreview from '../../hooks/useDynamicPreview'
import { colors } from './colors'

const OVERWRITE_ATTRIBUTES = {
	link_nofollow: 'no',
	share_icons_size: '',
	items_spacing: '',

	...colors,
}

const Preview = ({ attributes }) => {
	const {
		share_icons_color,
		share_icons_size,
		share_type,
		share_icons_fill,
		items_spacing,
	} = attributes

	const formatContent = useCallback(
		(content) => {
			const virtualContainer = document.createElement('div')
			virtualContainer.innerHTML = content

			return virtualContainer.querySelector('.ct-share-box').innerHTML
		},
		[
			share_icons_color,
			share_icons_size,
			share_type,
			share_icons_fill,
			items_spacing,
		]
	)

	const { isLoading, preview } = useDynamicPreview(
		'share-box',
		{
			...attributes,
			...OVERWRITE_ATTRIBUTES,
		},
		formatContent
	)

	return isLoading ? (
		<Spinner />
	) : (
		<RawHTML className="ct-share-box" data-type="type-3">
			{preview}
		</RawHTML>
	)
}

export default Preview
