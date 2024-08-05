import { createElement } from '@wordpress/element'
import { ToolbarDropdownMenu } from '@wordpress/components'
import { __, sprintf } from 'ct-i18n'

import TagNameIcon from './TagNameIcon'

export default function HeadingLevelDropdown({ tagName, onChange }) {
	return (
		<ToolbarDropdownMenu
			popoverProps={{
				className: 'block-library-heading-level-dropdown',
			}}
			icon={<TagNameIcon level={tagName} />}
			label={__('Change heading level', 'blocksy')}
			controls={[
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'p',
				'span',
				'div',
			].map((targetTagName) => {
				{
					const isActive = targetTagName === tagName

					return {
						icon: (
							<TagNameIcon
								level={targetTagName}
								isPressed={isActive}
							/>
						),
						label: targetTagName,
						title: {
							h1: __('Heading 1', 'blocksy'),
							h2: __('Heading 2', 'blocksy'),
							h3: __('Heading 3', 'blocksy'),
							h4: __('Heading 4', 'blocksy'),
							h5: __('Heading 5', 'blocksy'),
							h6: __('Heading 6', 'blocksy'),
							p: __('Paragraph', 'blocksy'),
							span: __('Span', 'blocksy'),
							div: __('Div', 'blocksy'),
						}[targetTagName],
						isActive,
						onClick() {
							onChange(targetTagName)
						},
						role: 'menuitemradio',
					}
				}
			})}
		/>
	)
}
