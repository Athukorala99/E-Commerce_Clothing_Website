import { createElement, useMemo } from '@wordpress/element'

import { __ } from 'ct-i18n'
import { registerBlockType } from '@wordpress/blocks'
import Edit from './Edit'

import metadata from './block.json'

registerBlockType('blocksy/breadcrumbs', {
	...metadata,
	title: __('Breadcrumbs', 'blocksy'),
	description: __(
		'Insert the breadcrumbs navigation anywhere you might want.',
		'blocksy'
	),
	icon: {
		src: (
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
				<path d="M4,10.5h6v3H4V10.5z M12,13.5h3v-3h-3V13.5z M17,10.5v3h3v-3H17z" />
			</svg>
		),
	},
	edit: (props) => <Edit {...props} />,
	save: () => null,
})
