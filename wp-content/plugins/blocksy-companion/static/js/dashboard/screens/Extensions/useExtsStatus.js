import {
	createElement,
	Component,
	useEffect,
	useMemo,
	useState,
	Fragment,
} from '@wordpress/element'

import ctEvents from 'ct-events'
import { flushPermalinks } from '../../../flushPermalinks'

let exts_status_cache = null

export const getRawExtsStatus = () => exts_status_cache || []

const useExtsStatus = () => {
	const [isLoading, setIsLoading] = useState(!exts_status_cache)
	const [exts_status, setExtsStatus] = useState(exts_status_cache || [])

	let [{ controller }, setAbortState] = useState({
		controller: null,
	})

	const syncExts = async (args = {}) => {
		let { verbose, extension, extAction } = {
			verbose: false,
			extension: null,
			extAction: null,
			...args,
		}

		if (verbose) {
			setIsLoading(true)
		}

		if (controller) {
			controller.abort()
		}

		if ('AbortController' in window) {
			controller = new AbortController()

			setAbortState({
				controller,
			})
		}

		const response = await fetch(
			`${wp.ajax.settings.url}?action=blocksy_extensions_status`,

			{
				method: 'POST',
				signal: controller.signal,
				...(extension && extAction
					? {
							body: JSON.stringify({
								extension,
								extAction,
							}),
					  }
					: {}),
			}
		)

		if (response.status !== 200) {
			return
		}

		const { success, data } = await response.json()

		if (!success) {
			return
		}

		if (!!extAction?.require_refresh) {
			flushPermalinks()
		}

		setIsLoading(false)

		setExtsStatus(data)
		exts_status_cache = data

		if (extension) {
			return data[extension]
		}

		return data
	}

	useEffect(() => {
		syncExts({ verbose: !exts_status_cache })

		const cb = () => {
			syncExts()
		}

		ctEvents.on('blocksy_exts_sync_exts', cb)

		return () => {
			ctEvents.off('blocksy_exts_sync_exts', cb)
		}
	}, [])

	return {
		syncExts,
		isLoading,
		exts_status,

		setExtsStatus: (cb) => {
			const data = cb(exts_status)
			exts_status_cache = data
			setExtsStatus(cb)
		},
	}
}

export default useExtsStatus
