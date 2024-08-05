import { createElement, RawHTML } from '@wordpress/element'
import { __ } from 'ct-i18n'

const StockPreview = ({ product }) => {
	return product?.is_in_stock
		? __('In Stock', 'blocksy-companion')
		: __('Out of Stock', 'blocksy-companion')
}

export default StockPreview
