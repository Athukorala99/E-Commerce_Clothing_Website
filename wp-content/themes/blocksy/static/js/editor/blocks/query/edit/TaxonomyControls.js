import {
	createElement,
	useState,
	useEffect,
	Fragment,
} from '@wordpress/element'

import { FormTokenField } from '@wordpress/components'
import { useSelect } from '@wordpress/data'
import { store as coreStore } from '@wordpress/core-data'
import { useDebounce } from '@wordpress/compose'
import { ToggleControl } from '@wordpress/components'

import OptionsPanel from '../../../../options/OptionsPanel'

import { useTaxonomies } from './utils/utils'

import { __, sprintf } from 'ct-i18n'

import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components'

const EMPTY_ARRAY = []
const BASE_QUERY = {
	order: 'asc',
	_fields: 'id,name,slug',
	context: 'view',
}

// Helper function to get the term id based on user input in terms `FormTokenField`.
const getTermIdByTermValue = (terms, termValue) => {
	// First we check for exact match by `term.id` or case sensitive `term.name` match.
	const termId =
		termValue?.slug || terms?.find((term) => term.name === termValue)?.slug
	if (termId) {
		return termId
	}

	/**
	 * Here we make an extra check for entered terms in a non case sensitive way,
	 * to match user expectations, due to `FormTokenField` behaviour that shows
	 * suggestions which are case insensitive.
	 *
	 * Although WP tries to discourage users to add terms with the same name (case insensitive),
	 * it's still possible if you manually change the name, as long as the terms have different slugs.
	 * In this edge case we always apply the first match from the terms list.
	 */
	const termValueLower = termValue.toLocaleLowerCase()

	return terms?.find(
		(term) => term.name.toLocaleLowerCase() === termValueLower
	)?.slug
}

export function TaxonomyItem({ taxonomy, termIds, onChange, label = false }) {
	const [search, setSearch] = useState('')
	const [value, setValue] = useState(EMPTY_ARRAY)
	const [suggestions, setSuggestions] = useState(EMPTY_ARRAY)
	const debouncedSearch = useDebounce(setSearch, 250)

	const { searchResults, searchHasResolved } = useSelect(
		(select) => {
			if (!search) {
				return { searchResults: EMPTY_ARRAY, searchHasResolved: true }
			}
			const { getEntityRecords, hasFinishedResolution } =
				select(coreStore)
			const selectorArgs = [
				'taxonomy',
				taxonomy.slug,
				{
					...BASE_QUERY,
					search,
					orderby: 'name',
					// exclude: termIds,
					per_page: 20,
				},
			]
			return {
				searchResults: getEntityRecords(...selectorArgs),
				searchHasResolved: hasFinishedResolution(
					'getEntityRecords',
					selectorArgs
				),
			}
		},
		[search, termIds]
	)

	// `existingTerms` are the ones fetched from the API and their type is `{ id: number; name: string }`.
	// They are used to extract the terms' names to populate the `FormTokenField` properly
	// and to sanitize the provided `termIds`, by setting only the ones that exist.
	const existingTerms = useSelect(
		(select) => {
			if (!termIds?.length) return EMPTY_ARRAY
			const { getEntityRecords } = select(coreStore)
			return getEntityRecords('taxonomy', taxonomy.slug, {
				...BASE_QUERY,
				slug: termIds,
				per_page: termIds.length,
			})
		},
		[termIds]
	)

	// Update the `value` state only after the selectors are resolved
	// to avoid emptying the input when we're changing terms.
	useEffect(() => {
		if (!termIds?.length) {
			setValue(EMPTY_ARRAY)
		}

		if (!existingTerms?.length) return

		// Returns only the existing entity ids. This prevents the component
		// from crashing in the editor, when non existing ids are provided.
		const sanitizedValue = termIds.reduce((accumulator, id) => {
			const entity = existingTerms.find((term) => term.slug === id)

			if (entity) {
				accumulator.push({
					id,
					slug: id,
					value: entity.name,
				})
			}

			return accumulator
		}, [])

		setValue(sanitizedValue)
	}, [termIds, existingTerms])

	// Update suggestions only when the query has resolved.
	useEffect(() => {
		if (!searchHasResolved) return
		setSuggestions(searchResults.map((result) => result.name))
	}, [searchResults, searchHasResolved])

	const onTermsChange = (newTermValues) => {
		const newTermIds = new Set()

		for (const termValue of newTermValues) {
			const termId = getTermIdByTermValue(searchResults, termValue)

			if (termId) {
				newTermIds.add(termId)
			}
		}

		setSuggestions(EMPTY_ARRAY)

		onChange(Array.from(newTermIds))
	}

	return (
		<FormTokenField
			label={label}
			value={value}
			onInputChange={debouncedSearch}
			suggestions={suggestions}
			onChange={onTermsChange}
			__experimentalShowHowTo={false}
			__nextHasNoMarginBottom
			placeholder={__('Search for a term', 'blocksy')}
		/>
	)
}
