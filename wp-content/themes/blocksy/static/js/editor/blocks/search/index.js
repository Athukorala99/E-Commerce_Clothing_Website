import { createElement } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'

import { getAttributesFromOptions, getOptionsForBlock } from '../../utils'

export const options = getOptionsForBlock('search')
export const defaultAttributes = getAttributesFromOptions(options)

import { colorsDefaults } from './colors'
import Edit from './Edit'

registerBlockType('blocksy/search', {
	apiVersion: 3,
	title: __('Advanced Search', 'blocksy'),
	description: __('Insert a search block anywhere on the site.', 'blocksy'),
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path d="m19.7 18.5-3-2.9c1-1.3 1.6-2.8 1.6-4.5 0-3.9-3.2-7.2-7.2-7.2S4 7.2 4 11.2s3.2 7.2 7.2 7.2c1.7 0 3.3-.6 4.5-1.6l3 3c.1.1.3.2.5.2s.4-.1.5-.2c.3-.4.3-1 0-1.3zM5.6 11.2c0-3.1 2.5-5.5 5.5-5.5s5.6 2.4 5.6 5.5-2.5 5.5-5.5 5.5-5.6-2.5-5.6-5.5z" />
			</svg>
		),
	},
	category: 'blocksy-blocks',
	attributes: {
		...defaultAttributes,
		...colorsDefaults,
	},
	supports: {
		spacing: {
			margin: true,
			__experimentalDefaultControls: {
				margin: true,
			},
		},
		__experimentalBorder: {
			color: false,
			radius: true,
			width: false,
			__experimentalSkipSerialization: true,
			__experimentalDefaultControls: {
				color: false,
				radius: true,
				width: false,
			},
		},
	},
	edit: (props) => <Edit {...props} />,
	save: function () {
		return <div>Blocksy: Search Block</div>
	},
})
