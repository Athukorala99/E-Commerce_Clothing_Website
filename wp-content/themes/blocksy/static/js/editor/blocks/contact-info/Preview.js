import { createElement, useCallback, RawHTML } from '@wordpress/element'

import { __ } from 'ct-i18n'

import { Spinner } from '@wordpress/components'
import useDynamicPreview from '../../hooks/useDynamicPreview'
import { colors } from './colors'

const OVERWRITE_ATTRIBUTES = {
	contacts_icons_size: 20,
	contacts_items_spacing: '',
	contacts_icon_shape: 'rounded',
	contacts_icon_fill_type: 'outline',
	contact_link_target: 'no',
	contact_text: '',
	contacts_items_direction: 'column',
	link_icons: 'no',

	...colors,
}

const Preview = ({ attributes }) => {
	const {
		contacts_icons_size = 20,
		contacts_items_spacing = '',
		contacts_icon_shape = 'rounded',
		contacts_icon_fill_type = 'outline',
		contact_link_target = 'no',
		contacts_items_direction = 'column',
	} = attributes

	const formatContent = useCallback(
		(content) => {
			const virtualContainer = document.createElement('div')
			virtualContainer.innerHTML = content

			const socialIcons = virtualContainer.querySelector(
				'.ct-contact-info-block ul'
			)

			const links = virtualContainer.querySelectorAll('a')

			if (links) {
				links.forEach((link) => {
					link.target =
						contact_link_target === 'yes' ? '_blank' : '_self'
				})
			}

			return socialIcons.innerHTML
		},
		[
			contacts_icons_size,
			contacts_icon_shape,
			contacts_icon_fill_type,
			contact_link_target,
			contacts_items_direction,
			contacts_items_spacing,
		]
	)

	const { isLoading, preview } = useDynamicPreview(
		'contact-info',
		{
			...attributes,
			...OVERWRITE_ATTRIBUTES,
		},
		formatContent
	)

	if (isLoading) {
		return <Spinner />
	}

	return (
		<ul
			data-icons-type={
				contacts_icon_shape === 'simple'
					? contacts_icon_shape
					: `${contacts_icon_shape}:${contacts_icon_fill_type}`
			}
			dangerouslySetInnerHTML={{
				__html: preview,
			}}
		/>
	)
}

export default Preview
