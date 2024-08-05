import { createElement, useState, Fragment } from '@wordpress/element'
import { __ } from 'ct-i18n'
import classnames from 'classnames'

import EditCredentials from './EditCredentials'

const NewsletterSubscribe = ({ extension, onExtsSync }) => {
	const toggleActivationState = async () => {
		const body = new FormData()

		body.append('ext', extension.name)
		body.append(
			'action',
			extension.__object
				? 'blocksy_extension_deactivate'
				: 'blocksy_extension_activate'
		)

		try {
			await fetch(ctDashboardLocalizations.ajax_url, {
				method: 'POST',
				body,
			})

			onExtsSync()
		} catch (e) {}
	}

	return (
		<EditCredentials
			extension={extension}
			onCredentialsValidated={() => {
				if (extension.__object) {
					return
				}

				toggleActivationState()
			}}
		/>
	)
}

export default NewsletterSubscribe
