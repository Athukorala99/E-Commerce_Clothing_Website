import { createElement, RawHTML } from '@wordpress/element'
import { useSelect } from '@wordpress/data'
import { __ } from 'ct-i18n'

import classnames from 'classnames'

import { useTaxonomies } from '../../../query/edit/utils/utils'

const TaxonomyPreview = ({
	postId,
	postType,

	fallback,

	attributes,
	attributes: { has_field_link, taxonomy: req_taxonomy, separator },
}) => {
	const taxonomies = useTaxonomies(postType)

	const { terms } = useSelect((select) => {
		return {
			terms:
				select('core').getEntityRecords(
					'taxonomy',
					req_taxonomy
						? req_taxonomy
						: taxonomies && taxonomies.length > 0
						? taxonomies[0].slug
						: '',
					{
						per_page: -1,
						post: postId,
					}
				) || [],
		}
	})

	if (terms.length === 0) {
		return fallback
	}

	let TagName = 'span'

	let attrs = {}

	if (has_field_link === 'yes') {
		TagName = 'a'

		attrs.href = '#'
		attrs.rel = 'noopener noreferrer'
	}

	return terms.map((t, index) => (
		<>
			<TagName
				{...attrs}
				className={classnames(
					{
						[`ct-term-${t.id}`]:
							attributes.termAccentColor === 'yes',
					},
					attributes.termClass
				)}
				dangerouslySetInnerHTML={{ __html: t.name }}
			/>
			{index !== terms.length - 1
				? separator.replace(/ /g, '\u00A0')
				: ''}
		</>
	))
}

export default TaxonomyPreview
