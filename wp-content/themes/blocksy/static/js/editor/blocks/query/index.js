import { createElement, useMemo } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'
import Edit from './Edit'
import Save from './Save'

import metadata from './block.json'

registerBlockType('blocksy/query', {
	...metadata,
	title: __('Advanced Posts', 'blocksy'),
	description: __('Advanced Posts', 'blocksy'),
	icon: {
		src: (
			<svg
				viewBox="0 0 24 24"
				xmlns="http://www.w3.org/2000/svg"
				width="24"
				height="24"
				context="list-view"
				aria-hidden="true"
				focusable="false">
				<path d="M5.5 18v-1c0-.3.2-.5.5-.5h3c.3 0 .5.2.5.5v1c0 .3-.2.5-.5.5H6c-.3 0-.5-.2-.5-.5zm.5 2c-1.1 0-2-.9-2-2v-1c0-1.1.9-2 2-2h3c1.1 0 2 .9 2 2v1c0 1.1-.9 2-2 2H6zm-.5-9V6c0-.3.2-.5.5-.5h3c.3 0 .5.2.5.5v5c0 .3-.2.5-.5.5H6c-.3 0-.5-.2-.5-.5zm.5 2c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h3c1.1 0 2 .9 2 2v5c0 1.1-.9 2-2 2H6zm8.5 0v5c0 .3.2.5.5.5h3c.3 0 .5-.2.5-.5v-5c0-.3-.2-.5-.5-.5h-3c-.3 0-.5.2-.5.5zM13 18c0 1.1.9 2 2 2h3c1.1 0 2-.9 2-2v-5c0-1.1-.9-2-2-2h-3c-1.1 0-2 .9-2 2v5zm1.5-11V6c0-.3.2-.5.5-.5h3c.3 0 .5.2.5.5v1c0 .3-.2.5-.5.5h-3c-.3 0-.5-.2-.5-.5zm.5 2c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h3c1.1 0 2 .9 2 2v1c0 1.1-.9 2-2 2h-3z" fill-rule="evenodd"/>
			</svg>
		),
	},
	edit: (props) => <Edit {...props} />,
	save: () => <Save />,
})
