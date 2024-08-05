import { createElement, RawHTML } from '@wordpress/element'
import { __ } from 'ct-i18n'

const SkuPreview = ({ product }) => product?.sku || ''

export default SkuPreview
