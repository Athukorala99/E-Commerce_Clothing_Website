import { createElement } from '@wordpress/element'
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor'

const Save = (props) => {
	const blockProps = useBlockProps.save()
	const innerBlocksProps = useInnerBlocksProps.save(blockProps)

	return <div {...innerBlocksProps} />
}

export default Save
