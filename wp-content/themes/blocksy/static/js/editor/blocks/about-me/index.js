import { createElement } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'

import { getAttributesFromOptions, getOptionsForBlock } from '../../utils'
import Edit from './Edit'
import { colorsDefaults } from './colors'

export const options = getOptionsForBlock('about_me')
export const defaultAttributes = getAttributesFromOptions(options)

registerBlockType('blocksy/about-me', {
	apiVersion: 3,
	title: __('About Me Controls', 'blocksy'),
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path d="M5.8 13H4.2v-1c0-1.5 1.2-2.8 2.8-2.8h4c1.5 0 2.8 1.2 2.8 2.8v1h-1.5v-1c0-.7-.6-1.2-1.2-1.2H7c-.7 0-1.2.6-1.2 1.2v1zM4 21h9v-1.5H4V21zm0-5.5V17h16v-1.5H4zm2.5-10C6.5 4.1 7.6 3 9 3s2.5 1.1 2.5 2.5S10.4 8 9 8 6.5 6.9 6.5 5.5zm1.5 0c0 .6.4 1 1 1s1-.4 1-1-.4-1-1-1-1 .4-1 1z" />
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
	save: () => <div>Blocksy: About Me</div>,
})

wp.blocks.registerBlockVariation('blocksy/widgets-wrapper', {
	name: 'blocksy-about-me',
	title: __('About Me', 'blocksy'),
	attributes: {
		heading: __('About Me', 'blocksy'),
		block: 'blocksy/about-me',
	},
	isDefault: true,
	isActive: (attributes) => attributes.block === 'blocksy/about-me',
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path d="M5.8 13H4.2v-1c0-1.5 1.2-2.8 2.8-2.8h4c1.5 0 2.8 1.2 2.8 2.8v1h-1.5v-1c0-.7-.6-1.2-1.2-1.2H7c-.7 0-1.2.6-1.2 1.2v1zM4 21h9v-1.5H4V21zm0-5.5V17h16v-1.5H4zm2.5-10C6.5 4.1 7.6 3 9 3s2.5 1.1 2.5 2.5S10.4 8 9 8 6.5 6.9 6.5 5.5zm1.5 0c0 .6.4 1 1 1s1-.4 1-1-.4-1-1-1-1 .4-1 1z" />
			</svg>
		),
	},
})
