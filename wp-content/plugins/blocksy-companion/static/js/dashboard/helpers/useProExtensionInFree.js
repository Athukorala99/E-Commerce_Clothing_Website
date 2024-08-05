import { useContext, createElement, useState } from '@wordpress/element'
import { __, sprintf } from 'ct-i18n'

import DashboardContext from '../DashboardContext'

import useUpsellModal from './useUpsellModal'

const getLowestPlan = () => {}

const useProExtensionInFree = (extension, args = {}) => {
	args = {
		// pro-ext | pro
		strategy: 'pro-ext',
		modalTitle: __('This is a Pro extension', 'blocksy-companion'),
		...args,
	}

	const [isDisplayed, setIsDisplayed] = useState(false)
	const isPro = ctDashboardLocalizations.plugin_data.is_pro

	const { history } = useContext(DashboardContext)

	const isProInFree = !isPro && extension.config.pro

	const currentPlan = ctDashboardLocalizations.plugin_data.current_plan

	let requiredPlan = 'personal'

	if (extension.config.plans) {
		if (extension.config.plans.includes('agency_v2')) {
			requiredPlan = 'agency'
		}

		if (extension.config.plans.includes('professional_v2')) {
			requiredPlan = 'professional'
		}
	}

	const { content, showNotice } = useUpsellModal({
		currentPlan,
		requiredPlan,
		...args,
	})

	return {
		isPro,
		isProInFree:
			(!isPro && extension.config.pro) ||
			(extension.config.plans &&
				extension.config.plans.length &&
				!extension.config.plans.includes(currentPlan)),

		showNotice,

		content:
			(args.strategy === 'pro-ext' && isProInFree) ||
			(args.strategy === 'pro' && !isPro) ||
			(extension.config.plans &&
				extension.config.plans.length &&
				!extension.config.plans.includes(currentPlan))
				? content
				: null,
	}
}

export default useProExtensionInFree
