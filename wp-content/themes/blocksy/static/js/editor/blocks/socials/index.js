import { createElement } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'

import { getAttributesFromOptions, getOptionsForBlock } from '../../utils'
import Edit from './Edit'
import { colorsDefaults } from './colors'

export const options = getOptionsForBlock('socials')
export const defaultAttributes = getAttributesFromOptions(options)

registerBlockType('blocksy/socials', {
	apiVersion: 3,
	title: __('Socials Controls', 'blocksy'),
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path d="M16.4 14.2c-.8 0-1.5.3-2.1.9l-3.9-2.3c.1-.3.1-.5.1-.8 0-.3-.1-.5-.1-.8L14.3 9c.5.5 1.3.9 2.1.9 1.6 0 2.9-1.3 2.9-2.9S18 4 16.4 4s-2.9 1.3-2.9 2.9c0 .3.1.5.1.8L9.7 10c-.5-.6-1.3-.9-2.1-.9-1.6 0-2.9 1.3-2.9 2.9 0 1.6 1.3 2.9 2.9 2.9.8 0 1.5-.3 2.1-.9l3.9 2.3c-.1.3-.1.5-.1.8 0 1.6 1.3 2.9 2.9 2.9s2.9-1.3 2.9-2.9c0-1.6-1.3-2.9-2.9-2.9zm0-8.7c.8 0 1.5.7 1.5 1.5s-.7 1.5-1.5 1.5-1.5-.7-1.5-1.5.7-1.5 1.5-1.5zm-8.8 8c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5zm8.8 5c-.8 0-1.5-.7-1.5-1.5 0-.3.1-.5.2-.7.3-.4.7-.7 1.2-.7.8 0 1.5.7 1.5 1.5s-.6 1.4-1.4 1.4z"/>
			</svg>
		),
	},
	supports: {
		html: false,
		multiple: false,
		inserter: false,
		lock: false,
	},
	parent: ['blocksy/widgets-wrapper'],
	attributes: {
		...defaultAttributes,
		...colorsDefaults,
	},
	edit: (props) => <Edit {...props} />,
	save: function () {
		return <div>Blocksy: Socials</div>
	},
})

wp.blocks.registerBlockVariation('blocksy/widgets-wrapper', {
	name: 'blocksy-socials',
	title: __('Socials', 'blocksy'),
	attributes: {
		heading: __('Socials', 'blocksy'),
		block: 'blocksy/socials',
	},
	isActive: (attributes) => attributes.block === 'blocksy/socials',
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path d="M16.4 14.2c-.8 0-1.5.3-2.1.9l-3.9-2.3c.1-.3.1-.5.1-.8 0-.3-.1-.5-.1-.8L14.3 9c.5.5 1.3.9 2.1.9 1.6 0 2.9-1.3 2.9-2.9S18 4 16.4 4s-2.9 1.3-2.9 2.9c0 .3.1.5.1.8L9.7 10c-.5-.6-1.3-.9-2.1-.9-1.6 0-2.9 1.3-2.9 2.9 0 1.6 1.3 2.9 2.9 2.9.8 0 1.5-.3 2.1-.9l3.9 2.3c-.1.3-.1.5-.1.8 0 1.6 1.3 2.9 2.9 2.9s2.9-1.3 2.9-2.9c0-1.6-1.3-2.9-2.9-2.9zm0-8.7c.8 0 1.5.7 1.5 1.5s-.7 1.5-1.5 1.5-1.5-.7-1.5-1.5.7-1.5 1.5-1.5zm-8.8 8c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5zm8.8 5c-.8 0-1.5-.7-1.5-1.5 0-.3.1-.5.2-.7.3-.4.7-.7 1.2-.7.8 0 1.5.7 1.5 1.5s-.6 1.4-1.4 1.4z"/>
			</svg>
		),
	},
})
