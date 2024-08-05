import { useContext } from '@wordpress/element'
import { ConditionsDataContext } from '../ConditionsManager'

const useConditionsData = (condition = null) => {
	const { isAdvancedMode, filter, allTaxonomies, allLanguages, allUsers } =
		useContext(ConditionsDataContext)

	let rulesToUse = blocksy_admin.all_condition_rules

	if (filter === 'singular') {
		rulesToUse = blocksy_admin.singular_condition_rules
	}

	if (filter === 'archive') {
		rulesToUse = blocksy_admin.archive_condition_rules
	}

	if (filter === 'product_tabs') {
		rulesToUse = blocksy_admin.product_tabs_rules
	}

	if (filter === 'maintenance-mode') {
		rulesToUse = blocksy_admin.maintenance_mode_rules
	}

	const allRules = rulesToUse
		.reduce(
			(current, { rules, title }) => [
				...current,
				...rules.map((r) => ({
					...r,
					group: title,
				})),
			],
			[]
		)
		.reduce(
			(current, { title, id, sub_ids = [], ...rest }) => [
				...current,
				{
					key:
						condition &&
						sub_ids.length > 0 &&
						sub_ids.find((i) => i.id === condition.rule)
							? condition.rule
							: id,
					value: title,
					sub_ids,
					...rest,
				},
			],
			[]
		)

	return {
		isAdvancedMode,
		allRules,
		rulesToUse,
		allTaxonomies,
		allLanguages,
		allUsers,
	}
}

export default useConditionsData
