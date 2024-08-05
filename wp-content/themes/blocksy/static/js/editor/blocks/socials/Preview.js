import { createElement, useCallback, RawHTML } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { Spinner } from '@wordpress/components'
import useDynamicPreview from '../../hooks/useDynamicPreview'
import { colors } from './colors'

const OVERWRITE_ATTRIBUTES = {
	link_nofollow: 'no',
	link_target: 'no',
	social_icons_color: 'official',
	social_icons_fill: 'outline',
	social_icons_size: '',
	items_spacing: '',
	social_type: 'simple',

	...colors,
}

const Preview = ({ attributes }) => {
	const {
		social_icons_color,
		social_icons_size,
		social_type,
		social_icons_fill,
		items_spacing,
	} = attributes

	const formatContent = useCallback(
		(content) => {
			const virtualContainer = document.createElement('div')
			virtualContainer.innerHTML = content

			return virtualContainer.querySelector('.ct-social-box').innerHTML
		},
		[
			social_icons_color,
			social_icons_size,
			social_type,
			social_icons_fill,
			items_spacing,
		]
	)

	const { isLoading, preview } = useDynamicPreview(
		'socials',
		{
			...attributes,
			...OVERWRITE_ATTRIBUTES,
		},
		formatContent
	)

	return isLoading ? (
		<Spinner />
	) : (
		<RawHTML
			className="ct-social-box"
			data-icons-type={
				social_type === 'simple'
					? social_type
					: `${social_type}:${social_icons_fill}`
			}
			data-color={social_icons_color}>
			{preview}
		</RawHTML>
	)
}

export default Preview
