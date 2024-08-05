import { createElement } from '@wordpress/element'
import { ToggleControl } from '@wordpress/components'

const isActive = ({ option: { behavior = 'words' }, value }) =>
	behavior === 'words' ? value === 'yes' : !!value

const alternateValueFor = ({
	option = {},
	option: { behavior = 'words' },
	value,
}) =>
	isActive({ option, value })
		? behavior === 'words'
			? 'no'
			: false
		: behavior === 'words'
		? 'yes'
		: true

const GutenbergSwitch = ({
	value,
	option = {},
	onChange,
	onClick,
	maybeLabel,
}) => {
	return (
		<ToggleControl
			label={maybeLabel}
			checked={isActive({ option, value })}
			onChange={() => onChange(alternateValueFor({ option, value }))}
			onClick={(e) => {
				onClick && onClick(e)
				onChange(alternateValueFor({ option, value }))
			}}
		/>
	)
}

export default GutenbergSwitch
