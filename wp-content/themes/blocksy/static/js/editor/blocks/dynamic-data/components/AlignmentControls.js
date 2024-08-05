import { createElement } from '@wordpress/element'
import {
	BlockControls,
	BlockAlignmentControl,
	AlignmentControl,
} from '@wordpress/block-editor'
import TagNameDropdown from './TagNameDropdown'

import { fieldIsImageLike } from '../utils'

const AlignmentControls = ({
	fieldDescriptor,
	attributes,
	attributes: { align, imageAlign },
	setAttributes,
}) => {
	return (
		<BlockControls group="block">
			{!fieldIsImageLike(fieldDescriptor) ? (
				<>
					<AlignmentControl
						value={align}
						onChange={(newAlign) =>
							setAttributes({
								align: newAlign,
							})
						}
					/>
					<TagNameDropdown
						tagName={attributes.tagName}
						onChange={(tagName) => setAttributes({ tagName })}
					/>
				</>
			) : (
				<BlockAlignmentControl
					{...(fieldDescriptor.provider === 'wp' &&
					fieldDescriptor.id === 'author_avatar'
						? {
								controls: ['none', 'left', 'center', 'right'],
						  }
						: {})}
					value={imageAlign}
					onChange={(newImageAlign) =>
						setAttributes({
							imageAlign: newImageAlign,
						})
					}
				/>
			)}
		</BlockControls>
	)
}

export default AlignmentControls
