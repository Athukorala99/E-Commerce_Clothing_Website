import { createElement, useRef, useCallback, RawHTML } from '@wordpress/element'
import { RichText } from '@wordpress/block-editor'
import { __ } from 'ct-i18n'

import { Spinner } from '@wordpress/components'
import useDynamicPreview from '../../hooks/useDynamicPreview'
import { colors } from './colors'

const OVERWRITE_ATTRIBUTES = {
	about_alignment: 'center',
	avatar_shape: 'round',
	about_avatar_size: 'small',
	post_widget_thumb_size: 'default',
	about_social_type: 'rounded',
	about_social_icons_fill: 'outline',
	about_social_icons_color: 'official',
	about_text:
		'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua tincidunt tortor aliquam.',
	about_name: 'John Doe',
	about_items_spacing: '',
	about_social_icons_size: '',

	...colors,
}

const Preview = ({ attributes, setAttributes }) => {
	const maybeParts = useRef({
		image: '',
		socials: '',
	})

	const {
		about_name,
		about_text,
		about_source,
		about_alignment,
		avatar_shape,
		about_avatar_size,
		about_social_type,
		about_social_icons_fill,
		about_items_spacing,
		about_social_icons_size,
		about_social_icons_color,
	} = attributes

	const formatContent = useCallback(
		(content) => {
			const virtualContainer = document.createElement('div')
			virtualContainer.innerHTML = content

			const contentContainer =
				virtualContainer.querySelector('.ct-about-me-block')

			if (contentContainer) {
				contentContainer.querySelector('div').dataset.alignment =
					about_alignment

				const aboutImage = contentContainer.querySelector(
					'.ct-media-container'
				)

				if (aboutImage) {
					aboutImage.dataset.shape = avatar_shape
					aboutImage.dataset.size = about_avatar_size
				}

				const socialIcons =
					contentContainer.querySelector('.ct-social-box')

				if (socialIcons) {
					socialIcons.dataset.iconsType =
						about_social_type === 'simple'
							? about_social_type
							: `${about_social_type}:${about_social_icons_fill}`

					socialIcons.dataset.color = about_social_icons_color
				}

				if (about_source === 'custom') {
					maybeParts.current = {
						image:
							contentContainer.querySelector(
								'.ct-media-container'
							)?.outerHTML || '',
						socials: socialIcons?.outerHTML || '',
					}
				}
			}

			return contentContainer.innerHTML
		},
		[
			about_alignment,
			avatar_shape,
			about_source,
			about_avatar_size,
			about_social_type,
			about_social_icons_fill,
			about_social_icons_size,
			about_items_spacing,
			about_social_icons_color,
		]
	)

	const { isLoading, preview } = useDynamicPreview(
		'about-me',
		{
			...attributes,
			...OVERWRITE_ATTRIBUTES,
		},
		formatContent
	)

	if (isLoading) {
		return <Spinner />
	}

	if (about_source === 'from_wp') {
		return <RawHTML>{preview}</RawHTML>
	}

	return (
		<div data-alignment={about_alignment}>
			<RawHTML>{maybeParts.current.image}</RawHTML>
			<div className="ct-about-me-name">
				<RichText
					tagName="span"
					value={about_name}
					placeholder="User Name"
					onChange={(content) =>
						setAttributes({ about_name: content })
					}
				/>
			</div>
			<RichText
				tagName="div"
				className="ct-about-me-text"
				value={about_text}
				placeholder="User Description"
				onChange={(content) => setAttributes({ about_text: content })}
			/>
			<RawHTML>{maybeParts.current.socials}</RawHTML>
		</div>
	)
}

export default Preview
