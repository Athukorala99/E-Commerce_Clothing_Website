import {
	createElement,
	Component,
	useEffect,
	useState,
	Fragment,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import cn from 'classnames'

const Checkbox = ({
	children,
	activated,
	checked,
	onChange,

	className,

	// start | end
	position = 'end',
}) => {
	const items = [
		...(position === 'start' ? [children] : []),
		<span className={cn('ct-checkbox', { active: checked })} key={'icon'}>
			<svg width="10" height="8" viewBox="0 0 11.2 9.1">
				<polyline className="check" points="1.2,4.8 4.4,7.9 9.9,1.2 " />
			</svg>
		</span>,
		...(position === 'end' ? [children] : []),
	]

	return (
		<div
			onClick={() => onChange()}
			className={cn('ct-checkbox-container', className, {
				activated,
			})}>
			{items}
		</div>
	)
}

export default Checkbox
