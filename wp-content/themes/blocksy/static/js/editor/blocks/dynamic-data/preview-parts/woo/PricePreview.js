import { createElement, RawHTML } from '@wordpress/element'
import { __ } from 'ct-i18n'

const PricePreview = ({ product }) => {
	return <RawHTML>{product?.price_html}</RawHTML>
}

export default PricePreview
