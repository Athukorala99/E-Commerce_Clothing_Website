import { createElement, render } from '@wordpress/element'
import Dashboard from './Dashboard'

document.addEventListener('DOMContentLoaded', () => {
	if (!ctDashboardLocalizations.plugin_data) {
		return
	}

	if (document.getElementById('ct-dashboard')) {
		render(<Dashboard />, document.getElementById('ct-dashboard'))
	}
})
