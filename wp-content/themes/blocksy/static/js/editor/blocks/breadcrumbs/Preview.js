import { createElement, useMemo } from '@wordpress/element'
import { withSelect, withDispatch } from '@wordpress/data'

import { __ } from 'ct-i18n'

const {
	breadcrumb_home_item,
	breadcrumb_home_text,
	breadcrumb_separator,
	breadcrumb_page_title,
} = window.blc_blocks_data

const Preview = () => {
	const homeComponent = useMemo(() => {
		if (breadcrumb_home_item === 'text') {
			return breadcrumb_home_text
		}

		return (
			<svg class="ct-home-icon" width="15" viewBox="0 0 24 20">
				<path d="M12,0L0.4,10.5h3.2V20h6.3v-6.3h4.2V20h6.3v-9.5h3.2L12,0z"></path>
			</svg>
		)
	}, [])

	const separator = useMemo(() => {
		if (breadcrumb_separator === 'type-2') {
			return (
				<svg class="separator" width="8" height="8" viewBox="0 0 8 8">
					<polygon points="2.5,0 6.9,4 2.5,8 "></polygon>
				</svg>
			)
		}

		if (breadcrumb_separator === 'type-3') {
			return <span className="separator">/</span>
		}
		return (
			<svg class="separator" width="8" height="8" viewBox="0 0 8 8">
				<path d="M2,6.9L4.8,4L2,1.1L2.6,0l4,4l-4,4L2,6.9z"></path>
			</svg>
		)
	}, [])

	const GetTitle = (props) => (
		<span>{props?.title || __('Page', 'blocksy')}</span>
	)

	const selectTitle = withSelect((select) => ({
		title: select('core/editor')?.getEditedPostAttribute('title') || '',
	}))

	const PostTitle = selectTitle(GetTitle)

	return (
		<>
			<span>
				<span>{homeComponent}</span>
				{separator}
			</span>

			<span>
				<span>{__('Subpage', 'blocksy')}</span>
				{breadcrumb_page_title && separator}
			</span>

			{breadcrumb_page_title && <PostTitle />}
		</>
	)
}

export default Preview
