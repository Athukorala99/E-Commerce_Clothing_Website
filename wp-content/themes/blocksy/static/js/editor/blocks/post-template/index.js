import { createElement, useMemo } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'
import Edit from './Edit'
import Save from './Save'

import metadata from './block.json'

registerBlockType('blocksy/post-template', {
	...metadata,
	title: __('Post Template', 'blocksy'),
	description: __('Post Template', 'blocksy'),
	icon: {
		src: (
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" context="list-view" aria-hidden="true" focusable="false"><path d="M18 5.5H6a.5.5 0 00-.5.5v3h13V6a.5.5 0 00-.5-.5zm.5 5H10v8h8a.5.5 0 00.5-.5v-7.5zm-10 0h-3V18a.5.5 0 00.5.5h2.5v-8zM6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
			</svg>
		),
	},
	edit: (props) => <Edit {...props} />,
	save: Save,
})
