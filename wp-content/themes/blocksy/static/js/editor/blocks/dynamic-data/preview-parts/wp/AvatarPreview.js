import { createElement } from '@wordpress/element'
import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

import { addQueryArgs, removeQueryArgs } from '@wordpress/url'
import { useUserAvatar } from '../../hooks/use-user-avatar'

const ImagePreview = ({
	postId,
	postType,

	attributes,
	attributes: { avatar_size, imageAlign, has_field_link },
}) => {
	const blockProps = useBlockProps({
		className: classnames('ct-dynamic-media wp-block-image', {
			[`align${imageAlign}`]: imageAlign,
		}),

		style: {},
	})

	const borderProps = useBorderProps(attributes)

	const avatar = useUserAvatar({
		postId,
		postType,
	})

	let maybeUrl = '#'

	const doubledSizedSrc = addQueryArgs(removeQueryArgs(avatar.src, ['s']), {
		s: avatar_size * 2,
	})

	const imageStyles = {
		...borderProps.style,
	}

	let content = (
		<img
			style={{
				...imageStyles,
				width: `${avatar_size}px`,
				height: `${avatar_size}px`,
			}}
			src={doubledSizedSrc}
			className={classnames(
				'avatar',
				'avatar-' + avatar_size,
				'photo',
				borderProps.className
			)}
		/>
	)

	if (has_field_link) {
		content = <a href="#">{content}</a>
	}

	return <figure {...blockProps}>{content}</figure>
}

export default ImagePreview
