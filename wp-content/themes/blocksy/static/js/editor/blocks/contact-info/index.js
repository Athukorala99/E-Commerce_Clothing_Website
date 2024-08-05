import { createElement } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'

import { getAttributesFromOptions, getOptionsForBlock } from '../../utils'
import Edit from './Edit'
import { colorsDefaults } from './colors'

export const options = getOptionsForBlock('contact_info')
export const defaultAttributes = getAttributesFromOptions(options)

registerBlockType('blocksy/contact-info', {
	apiVersion: 3,
	title: __('Contact Info Controls', 'blocksy'),
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path
					fill-rule="evenodd"
					d="M7.25 16.437a6.5 6.5 0 1 1 9.5 0V16A2.75 2.75 0 0 0 14 13.25h-4A2.75 2.75 0 0 0 7.25 16v.437Zm1.5 1.193a6.47 6.47 0 0 0 3.25.87 6.47 6.47 0 0 0 3.25-.87V16c0-.69-.56-1.25-1.25-1.25h-4c-.69 0-1.25.56-1.25 1.25v1.63ZM4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm10-2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"
					clip-rule="evenodd"
				/>
			</svg>
		),
	},
	supports: {
		html: false,
		multiple: false,
		inserter: false,
		lock: false,
		typography: {
			fontSize: true,
		},
	},
	parent: ['blocksy/widgets-wrapper'],
	attributes: {
		...defaultAttributes,
		...colorsDefaults,
	},
	edit: (props) => <Edit {...props} />,
	save: function () {
		return <div>Blocksy: Contact Info</div>
	},
})

wp.blocks.registerBlockVariation('blocksy/widgets-wrapper', {
	name: 'blocksy-contact-info',
	title: __('Contact Info', 'blocksy'),
	attributes: {
		heading: __('Contact Info', 'blocksy'),
		block: 'blocksy/contact-info',
		hasDescription: true,
		description: defaultAttributes?.contact_text?.default || '',
	},
	isActive: (attributes) => attributes.block === 'blocksy/contact-info',
	icon: {
		src: (
			<svg
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				className="wc-block-editor-components-block-icon">
				<path
					fill-rule="evenodd"
					d="M7.25 16.437a6.5 6.5 0 1 1 9.5 0V16A2.75 2.75 0 0 0 14 13.25h-4A2.75 2.75 0 0 0 7.25 16v.437Zm1.5 1.193a6.47 6.47 0 0 0 3.25.87 6.47 6.47 0 0 0 3.25-.87V16c0-.69-.56-1.25-1.25-1.25h-4c-.69 0-1.25.56-1.25 1.25v1.63ZM4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm10-2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"
					clip-rule="evenodd"
				/>
			</svg>
		),
	},
})
