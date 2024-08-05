import { createElement, Component } from '@wordpress/element'
import classnames from 'classnames'

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

const BlocksySwitch = ({ value, option = {}, onChange, onClick }) => {
	return (
		<div
			className={classnames({
				[`ct-option-switch`]: true,
				[`ct-active`]: isActive({ option, value }),
			})}
			onClick={(e) => {
				onClick && onClick(e)
				onChange(alternateValueFor({ option, value }))
			}}>
			<span />
		</div>
	)
}

export default BlocksySwitch
