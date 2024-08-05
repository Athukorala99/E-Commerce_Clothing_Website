import { createElement, Component } from '@wordpress/element'
import * as check from '@wordpress/element'
import ctEvents from 'ct-events'

import { __ } from 'ct-i18n'

import Extensions from './dashboard/screens/Extensions'

import Extension from './dashboard/screens/Extension'

import DemoInstall from './dashboard/screens/DemoInstall'
import SiteExport from './dashboard/screens/SiteExport'
import DemoToInstall from './dashboard/screens/DemoInstall/DemoToInstall'

import { getRawExtsStatus } from './dashboard/screens/Extensions/useExtsStatus'

ctEvents.on('ct:dashboard:routes', (r) => {
	r.push({
		Component: (props) => <Extensions {...props} />,
		path: '/extensions',
	})

	r.push({
		Component: (props) => <Extension {...props} />,
		path: '/extensions/:extension',
	})

	if (ctDashboardLocalizations.plugin_data.has_demo_install === 'yes') {
		r.push({
			Component: (props) => <DemoInstall {...props} />,
			path: '/demos',
		})
	}
})

ctEvents.on('ct:dashboard:navigation-links', (r) => {
	if (ctDashboardLocalizations.plugin_data.has_demo_install === 'yes') {
		r.push({
			text: __('Starter Sites', 'blocksy-companion'),
			path: 'demos',
			getProps: ({ isPartiallyCurrent, isCurrent }) =>
				isPartiallyCurrent
					? {
							'aria-current': 'page',
					  }
					: {},
		})
	}

	r.push({
		text: __('Extensions', 'blocksy-companion'),
		path: '/extensions',

		onClick: (e) => {
			if (location.hash.indexOf('extensions') > -1) {
				e.preventDefault()
			}
		},

		getProps: ({ isPartiallyCurrent, isCurrent }) => {
			return {
				...(isPartiallyCurrent || isCurrent
					? {
							'aria-current': 'page',
					  }
					: {}),
			}
		},
	})
})

ctEvents.on('ct:dashboard:heading:after', (r) => {
	if (!ctDashboardLocalizations.plugin_data.is_pro) {
		return
	}

	r.content = <span>PRO</span>
})
