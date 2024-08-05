import { createElement, useRef } from '@wordpress/element'
import { useSelect } from '@wordpress/data'
import {
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
} from '@wordpress/block-editor'
import { Spinner } from '@wordpress/components'

import classnames from 'classnames'

import ExcerptPreview from './wp/ExcerptPreview'
import DatePreview from './wp/DatePreview'
import CommentsPreview from './wp/CommentsPreview'
import AuthorPreview from './wp/AuthorPreview'
import TaxonomyPreview from './wp/TaxonomyPreview'
import PricePreview from './woo/PricePreview'
import StockPreview from './woo/StockPreview'
import RatingPreview from './woo/RatingPreview'
import SkuPreview from './woo/SkuPreview'

const TextField = ({
	fieldDescriptor,
	attributes,
	attributes: { align, tagName: TagName, before, after, fallback },
	postId,
	postType,
}) => {
	const { product, isLoading, ...da } =
		wc.wcBlocksSharedContext.useProductDataContext()

	const blockProps = useBlockProps({
		className: classnames('ct-dynamic-data', {
			[`has-text-align-${align}`]: align,
		}),
	})

	const borderProps = useBorderProps(attributes)

	if (isLoading) {
		return <Spinner />
	}

	let Component = null

	if (fieldDescriptor.id === 'price') {
		Component = PricePreview
	}

	if (fieldDescriptor.id === 'stock_status') {
		Component = StockPreview
	}

	if (fieldDescriptor.id === 'rating') {
		Component = RatingPreview
	}

	if (fieldDescriptor.id === 'sku') {
		Component = SkuPreview
	}

	if (Component) {
		return (
			<TagName
				{...blockProps}
				{...borderProps}
				style={{
					...(blockProps.style || {}),
					...(borderProps.style || {}),
				}}
				className={classnames(
					blockProps.className,
					borderProps.className
				)}>
				{before}

				<Component
					attributes={attributes}
					postId={postId}
					postType={postType}
					fallback={fallback}
					product={product}
				/>

				{after}
			</TagName>
		)
	}

	return null
}

const WooFieldPreview = (props) => {
	const { fieldDescriptor, postId } = props

	const { withProductDataContext } = wc.wcBlocksSharedHocs

	return withProductDataContext(TextField)({
		...props,
		productId: postId,
	})
}

export default WooFieldPreview
