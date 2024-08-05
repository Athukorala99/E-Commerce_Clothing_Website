import { createElement, Component, useContext } from '@wordpress/element'
import DashboardContext from './context'
import { sprintf, __ } from 'ct-i18n'
import ctEvents from 'ct-events'

const Heading = () => {
	const { theme_name, theme_custom_description, dashboard_has_heading } =
		useContext(DashboardContext)
	let afterContent = { content: null }
	ctEvents.trigger('ct:dashboard:heading:after', afterContent)

	return (
		<div>
			<h2
				onClick={(e) =>
					e.shiftKey &&
					ctEvents.trigger('ct:dashboard:heading:advanced-click')
				}>
				{dashboard_has_heading === 'yes' && (
					<svg
						width="35"
						height="35"
						viewBox="0 0 35 35">
						<path fill="#1e1e1e" d="M35,17.5C35,7.8,27.2,0,17.5,0C7.8,0,0,7.8,0,17.5C0,27.2,7.8,35,17.5,35C27.2,35,35,27.2,35,17.5z"/>
						<path fill="#ffffff" d="M16.3,13.7h3.9c0.5,0,0.9,0.4,0.9,1c0,0.5-0.4,1-1,1h-3.1L16.3,13.7z M24.2,17.6c0.6-0.8,1-1.9,1-3c0-1.1-0.4-2.1-1-2.9c-0.9-1.2-2.3-2-3.9-2.1c0,0-0.1,0-0.1,0v0h-9.4c-0.2,0-0.4,0.3-0.3,0.5l2.3,5.5h-1.9c-0.2,0-0.4,0.3-0.3,0.5l3.9,9.5h5.8c2.7,0,5-2.2,5-5C25.2,19.5,24.8,18.5,24.2,17.6C24.2,17.6,24.2,17.6,24.2,17.6zM16.3,19.6h3.9c0.5,0,0.9,0.4,0.9,1c0,0.5-0.4,1-1,1h-3.1L16.3,19.6z"/>
						/>
					</svg>
				)}

				{theme_name}
				{dashboard_has_heading === 'yes' && afterContent.content}
			</h2>
			<p>
				{theme_custom_description ||
					__(
						'The most innovative, intuitive and lightning fast WordPress theme. Build your next web project visually, in no time.',
						'blocksy'
					)}
			</p>
		</div>
	)
}

export default Heading
