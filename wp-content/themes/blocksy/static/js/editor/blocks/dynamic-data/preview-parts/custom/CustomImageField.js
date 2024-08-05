import { createElement } from '@wordpress/element'

import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

const CustomImageField = ({
	fieldDescriptor,
	fieldData,

	attributes,
	attributes: {
		aspectRatio,
		width,
		height,
		imageAlign,
		has_field_link,
		image_hover_effect,
		sizeSlug,
	},
}) => {
	const blockProps = useBlockProps({
		className: classnames('ct-dynamic-media wp-block-image', {
			[`align${imageAlign}`]: imageAlign,
		}),

		style: {
			aspectRatio,
			width,
			height,
		},

		...(image_hover_effect !== 'none'
			? { 'data-hover': image_hover_effect }
			: {}),
	})

	const borderProps = useBorderProps(attributes)

	let maybeUrl = fieldData.value.value.url

	if (fieldData.value.value.sizes[sizeSlug]) {
		maybeUrl = fieldData.value.value.sizes[sizeSlug].url
	}

	const imageStyles = {
		...borderProps.style,

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
			className={borderProps.className}
		/>
	)

	return <figure {...blockProps}>{content}</figure>
}

export default CustomImageField
