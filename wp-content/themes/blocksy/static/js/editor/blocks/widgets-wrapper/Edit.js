import { createElement } from '@wordpress/element'
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor'

const Edit = ({ attributes }) => {
	const isBlockLocation =
		!window.location.pathname.includes('widgets.php') &&
		!window.location.pathname.includes('customize.php')

	const template = [
		[
			'core/heading',
			{
				level: 3,
				content: attributes.heading || '',
				fontSize: 'medium',
				className: isBlockLocation ? '' : 'widget-title',
			},
		],
	]

	let allowedBlocks = ['core/heading']

	if (attributes.hasDescription) {
		template.push([
			'core/paragraph',
			{
				content: attributes.description,
				placeholder: 'Description',
			},
		])

		allowedBlocks = ['core/heading', 'core/paragraph']
	}

	template.push([
		attributes.block,
		{
			lock: {
				remove: true,
			},
			...attributes.blockAttrs,
		},
	])

	return (
		<div {...useBlockProps()}>
			<InnerBlocks allowedBlocks={allowedBlocks} template={template} />
		</div>
	)
}

export default Edit
