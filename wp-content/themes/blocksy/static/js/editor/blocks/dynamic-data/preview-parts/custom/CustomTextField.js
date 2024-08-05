import { createElement } from '@wordpress/element'

import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'

import classnames from 'classnames'

const CustomTextField = ({
	fieldDescriptor,
	attributes,
	attributes: { align, tagName: TagName, before, after, fallback },
	fieldData,
}) => {
	const blockProps = useBlockProps({
		className: classnames('ct-dynamic-data', {
			[`has-text-align-${align}`]: align,
		}),
	})

	const borderProps = useBorderProps(attributes)

	let isFallback = false

	let valueToRender = fieldData.value || ''

	if (!valueToRender) {
		isFallback = true
		valueToRender = fallback || ''
	}

	if (!isFallback && valueToRender && typeof valueToRender === 'string') {
		valueToRender = before + valueToRender + after
	}

	return (
		<TagName
			{...blockProps}
			{...borderProps}
			style={{
				...(blockProps.style || {}),
				...(borderProps.style || {}),
			}}
			className={classnames(blockProps.className, borderProps.className)}>
			{valueToRender}
		</TagName>
	)
}

export default CustomTextField
