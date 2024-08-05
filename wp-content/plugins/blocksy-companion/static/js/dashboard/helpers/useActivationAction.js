import {
	createElement,
	Component,
	useEffect,
	useState,
	Fragment,
} from '@wordpress/element'

import useProExtensionInFree from './useProExtensionInFree'
import { flushPermalinks } from '../../flushPermalinks'

const useActivationAction = (extension, doneCb = () => {}) => {
	const [isLoading, setIsLoading] = useState(false)

	const { isProInFree, showNotice, content } =
		useProExtensionInFree(extension)

	const makeAction = async (cb = () => {}, useFeatures = true) => {
		if (isProInFree) {
			if (useFeatures && extension.config.features) {
				cb()
				return
			}

			showNotice()
			return
		}

		cb()

		if (useFeatures) {
			const body = new FormData()

			body.append('ext', extension.name)
			body.append(
				'action',
				extension.__object
					? 'blocksy_extension_deactivate'
					: 'blocksy_extension_activate'
			)

			setIsLoading(true)

			try {
				await fetch(ctDashboardLocalizations.ajax_url, {
					method: 'POST',
					body,
				})

				if (extension.config.require_refresh) {
					flushPermalinks()
				}

				doneCb()
			} catch (e) {}

			// await new Promise(r => setTimeout(() => r(), 1000))

			setIsLoading(false)
		}
	}

	return [isLoading, makeAction, content]
}

export default useActivationAction
