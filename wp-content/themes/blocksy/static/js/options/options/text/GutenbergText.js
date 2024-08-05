import { createElement } from '@wordpress/element'
import { TextControl } from '@wordpress/components'

const GutenbergText = ({ value, option, onChange }) => (
	<TextControl
		label={option.label}
		value={value}
		{...{
			...(option.field_attr ? option.field_attr : {}),
			...(option.attr && option.attr.placeholder
				? {
						placeholder: option.attr.placeholder,
				  }
				: {}),
		}}
		onChange={(value) => onChange(value)}
		{...(option.select_on_focus
			? { onFocus: ({ target }) => target.select() }
			: {})}
	/>
)

export default GutenbergText
