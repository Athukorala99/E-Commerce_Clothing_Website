import { useTaxonomies } from '../utils/utils'
import { __, sprintf } from 'ct-i18n'

import { createElement } from '@wordpress/element'

import { TaxonomyItem } from '../TaxonomyControls'

import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components'

const getIncludeLayer = ({
	taxonomy,
	attributes,
	setAttributes,
	previewedPostMatchesType,
}) => {
	return {
		label: sprintf(__('Include %s', 'blocksy'), taxonomy.name),

		hasValue: () => {
			return attributes.include_term_ids[taxonomy.slug]
		},

		reset: () => {
			const { [taxonomy.slug]: taxValue, ...restTaxonomies } =
				attributes.include_term_ids || {}

			setAttributes({
				include_term_ids: restTaxonomies,
			})
		},

		render: () => {
			const taxonomyDescriptor = attributes.include_term_ids[
				taxonomy.slug
			] || {
				strategy: 'all',
				terms: [],
			}

			return (
				<div>
					<ToggleGroupControl
						label={sprintf(
							__('Include %s', 'blocksy'),
							taxonomy.name
						)}
						value={taxonomyDescriptor.strategy}
						isBlock
						onChange={(newValue) => {
							setAttributes({
								include_term_ids: {
									...attributes.include_term_ids,
									[taxonomy.slug]: {
										...taxonomyDescriptor,
										strategy: newValue,
									},
								},
							})
						}}>
						<ToggleGroupControlOption
							value={'all'}
							label={__('All', 'blocksy')}
						/>
						<ToggleGroupControlOption
							value={'specific'}
							label={__('Specific', 'blocksy')}
						/>

						{previewedPostMatchesType && (
							<ToggleGroupControlOption
								value={'related'}
								label={__('Related', 'blocksy')}
							/>
						)}
					</ToggleGroupControl>
					{taxonomyDescriptor.strategy === 'specific' && (
						<TaxonomyItem
							taxonomy={taxonomy}
							termIds={taxonomyDescriptor.terms}
							onChange={(newTermIds) => {
								setAttributes({
									include_term_ids: {
										...attributes.include_term_ids,
										[taxonomy.slug]: {
											...taxonomyDescriptor,
											terms: newTermIds,
										},
									},
								})
							}}
						/>
					)}
				</div>
			)
		},
	}
}

const getExcludeLayer = ({ taxonomy, attributes, setAttributes }) => {
	return {
		label: sprintf(__('Exclude %s', 'blocksy'), taxonomy.name),

		hasValue: () => {
			return attributes.exclude_term_ids[taxonomy.slug]
		},

		reset: () => {
			const { [taxonomy.slug]: taxValue, ...restTaxonomies } =
				attributes.exclude_term_ids || {}

			setAttributes({
				exclude_term_ids: restTaxonomies,
			})
		},

		render: () => {
			const taxonomyDescriptor = attributes.exclude_term_ids[
				taxonomy.slug
			] || {
				strategy: 'specific',
				terms: [],
			}

			return (
				<TaxonomyItem
					label={sprintf(__('Exclude %s', 'blocksy'), taxonomy.name)}
					taxonomy={taxonomy}
					termIds={taxonomyDescriptor.terms}
					onChange={(newTermIds) => {
						setAttributes({
							exclude_term_ids: {
								...attributes.exclude_term_ids,
								[taxonomy.slug]: {
									...taxonomyDescriptor,
									terms: newTermIds,
								},
							},
						})
					}}
				/>
			)
		},
	}
}

export const useTaxonomiesLayers = ({
	attributes,
	attributes: { post_type },

	previewedPostMatchesType,

	setAttributes,
}) => {
	const taxonomies = useTaxonomies(post_type)

	let layers = []

	if (taxonomies && taxonomies.length > 0) {
		layers = [
			...taxonomies.map((taxonomy) => {
				return getIncludeLayer({
					taxonomy,
					attributes,
					setAttributes,
					previewedPostMatchesType,
				})
			}),

			...taxonomies.map((taxonomy) => {
				return getExcludeLayer({
					taxonomy,
					attributes,
					setAttributes,
				})
			}),
		]
	}

	return {
		taxonomiesGroup:
			layers.length > 0
				? {
						label: __('Taxonomies', 'blocksy'),
						items: layers,
				  }
				: null,
	}
}
