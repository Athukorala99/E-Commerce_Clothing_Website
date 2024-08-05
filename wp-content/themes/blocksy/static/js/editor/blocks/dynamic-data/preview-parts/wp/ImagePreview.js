import { createElement, useState } from '@wordpress/element'
import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

import { useSelect } from '@wordpress/data'
import { useEntityProp, store as coreStore } from '@wordpress/core-data'

function getMediaSourceUrlBySizeSlug(media, slug) {
	return media?.media_details?.sizes?.[slug]?.source_url || media?.source_url
}

const VideoIndicator = () => (
	<span class="ct-video-indicator">
		<svg width="40" height="40" viewBox="0 0 40 40" fill="#fff">
			<path
				class="ct-play-path"
				d="M20,0C8.9,0,0,8.9,0,20s8.9,20,20,20s20-9,20-20S31,0,20,0z M16,29V11l12,9L16,29z"></path>
		</svg>
	</span>
)

const ImagePreview = ({
	postType,
	postId,

	attributes,
	attributes: {
		aspectRatio,
		width,
		height,
		imageAlign,
		has_field_link,
		sizeSlug,
		image_hover_effect,
		videoThumbnail,
	},
}) => {
	const [isLoaded, setIsLoaded] = useState(false)

	const borderProps = useBorderProps(attributes)

	const blockProps = useBlockProps({
		className: classnames(
			'ct-dynamic-media wp-block-image',
			{
				[`align${imageAlign}`]: imageAlign,
			},
			borderProps.className
		),

		style: {
			...borderProps.style,
			aspectRatio,
			width,
			height,
		},

		...(image_hover_effect !== 'none'
			? { 'data-hover': image_hover_effect }
			: {}),
	})

	const [featuredImage, setFeaturedImage] = useEntityProp(
		'postType',
		postType,
		'featured_media',
		postId
	)

	const { media } = useSelect(
		(select) => {
			const { getMedia } = select(coreStore)

			return {
				media:
					featuredImage &&
					getMedia(featuredImage, {
						context: 'view',
					}),
			}
		},
		[featuredImage]
	)

	const maybeUrl = getMediaSourceUrlBySizeSlug(media, sizeSlug)

	const imageStyles = {
		height: aspectRatio ? '100%' : height,
		width: !!aspectRatio && '100%',
		objectFit: !!(height || aspectRatio) && 'cover',
	}

	if (!maybeUrl) {
		return (
			<figure {...blockProps}>
				<div
					className="ct-dynamic-data-placeholder"
					style={{
						...imageStyles,
					}}>
					<svg
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 60 60"
						preserveAspectRatio="none"
						class="components-placeholder__illustration"
						aria-hidden="true"
						focusable="false"
						style={{
							'min-height': '200px',
							height: !!aspectRatio && '100%',
							width: !!aspectRatio && '100%',
						}}>
						<path
							vector-effect="non-scaling-stroke"
							d="M60 60 0 0"></path>
					</svg>
				</div>
			</figure>
		)
	}

	let content = (
		<img
			style={{
				...imageStyles,
			}}
			src={maybeUrl}
			onLoad={() => setIsLoaded(true)}
			loading="lazy"
		/>
	)

	if (
		has_field_link &&
		!media.has_video &&
		videoThumbnail !== 'yes' &&
		!isLoaded
	) {
		content = <a href="#">{content}</a>
	}

	return (
		<figure {...blockProps}>
			{content}

			{media.has_video && videoThumbnail === 'yes' ? (
				<VideoIndicator />
			) : null}
		</figure>
	)
}

export default ImagePreview
