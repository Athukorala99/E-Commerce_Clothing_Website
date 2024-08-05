import ctEvents from 'ct-events'
import {
	watchOptionsWithPrefix,
	getPrefixFor,
	setRatioFor,
	disableTransitionsStart,
	disableTransitionsEnd,
	getOptionFor,
	withKeys,
} from '../helpers'
import { typographyOption } from '../variables/typography'
import { handleBackgroundOptionFor } from '../variables/background'
import { renderSingleEntryMeta } from '../helpers/entry-meta'
import { replaceFirstTextNode, applyPrefixFor } from '../helpers'

import { maybePromoteScalarValueIntoResponsive } from 'customizer-sync-helpers/dist/promote-into-responsive'

const prefix = getPrefixFor()

watchOptionsWithPrefix({
	getPrefix: () => prefix,
	getOptionsForPrefix: ({ prefix }) => [`${prefix}_archive_order`],

	render: ({ id }) => {
		if (id === `${prefix}_archive_order` || id === `${prefix}_card_type`) {
			disableTransitionsStart(document.querySelectorAll('.entries'))

			disableTransitionsEnd(document.querySelectorAll('.entries'))

			let archiveOrder = getOptionFor('archive_order', prefix)
			disableTransitionsStart(document.querySelectorAll('.entries'))

			let allItemsToOutput = archiveOrder.filter(
				({ enabled }) => !!enabled
			)

			allItemsToOutput.map((component, index) => {
				;[...document.querySelectorAll('.entries > article')].map(
					(article) => {
						let image = article.querySelector('.ct-media-container')
						let button = article.querySelector('.entry-button')

						if (component.id === 'featured_image' && image) {
							setRatioFor(component.thumb_ratio, image)

							image.classList.remove('boundless-image')

							if (
								(component.is_boundless || 'yes') === 'yes' &&
								getOptionFor('card_type', prefix) === 'boxed' &&
								getOptionFor('structure', prefix) !==
									'gutenberg'
							) {
								image.classList.add('boundless-image')
							}
						}

						if (component.id === 'read_more' && button) {
							button.dataset.type =
								component.button_type || 'simple'

							button.classList.remove(
								'ct-button',
								'ct-button-ghost'
							)

							if (
								(component.button_type || 'simple') ===
								'background'
							) {
								button.classList.add('ct-button')
							}

							if (
								(component.button_type || 'simple') ===
								'outline'
							) {
								button.classList.add('ct-button-ghost')
							}

							replaceFirstTextNode(
								button,
								component.read_more_text || 'Read More'
							)
						}

						if (component.id === 'post_meta') {
							let moreDefaults = {}
							let el = article.querySelectorAll('.entry-meta')

							if (
								archiveOrder.filter(
									({ id }) => id === 'post_meta'
								).length > 1
							) {
								if (
									archiveOrder
										.filter(({ id }) => id === 'post_meta')
										.map(({ __id }) => __id)
										.indexOf(component.__id) === 0
								) {
									moreDefaults = {
										meta_elements: [
											{
												id: 'categories',
												enabled: true,
											},
										],
									}

									el = el[0]
								}

								if (
									archiveOrder
										.filter(({ id }) => id === 'post_meta')
										.map(({ __id }) => __id)
										.indexOf(component.__id) === 1
								) {
									moreDefaults = {
										meta_elements: [
											{
												id: 'author',
												enabled: true,
											},

											{
												id: 'post_date',
												enabled: true,
											},

											{
												id: 'comments',
												enabled: true,
											},
										],
									}

									if (el.length > 1) {
										el = el[1]
									}
								}
							}

							renderSingleEntryMeta({
								el,
								...moreDefaults,
								...component,
							})
						}
					}
				)
			})

			disableTransitionsEnd(document.querySelectorAll('.entries'))
		}
	},
})

const imageBorderVariables = [
	{
		selector: applyPrefixFor('.entry-card', prefix),
		type: 'spacing',
		variable: 'theme-image-border-radius',
		responsive: true,

		extractValue: () => getOptionFor('cardRadius', prefix),

		transformSpacingValue: (value, valueAsArray, device) => {
			const card_type = getOptionFor('card_type', prefix)
			const card_spacing = maybePromoteScalarValueIntoResponsive(
				getOptionFor('card_spacing', prefix)
			)
			const cardBorder = maybePromoteScalarValueIntoResponsive(
				getOptionFor('cardBorder', prefix)
			)

			const archive_order = getOptionFor('archive_order', prefix)

			let didChange = false

			const featured_image_settings = getOptionFor(
				'archive_order',
				prefix
			).find(({ id }) => id === 'featured_image')

			const is_boundles = featured_image_settings.is_boundless || 'yes'

			let maybeWidth = 0

			if (card_type === 'boxed' || card_type === 'cover') {
				if (
					cardBorder[device] &&
					cardBorder[device]['style'] !== 'none' &&
					cardBorder[device]['width'] > 0
				) {
					maybeWidth = `${cardBorder[device].width}px`
				}

				if (card_type === 'boxed' && is_boundles !== 'yes') {
					maybeWidth = card_spacing[device]
				}
			}

			if (maybeWidth !== 0) {
				return valueAsArray
					.map((value) => `calc(${value} - ${maybeWidth})`)
					.join(' ')
			}

			return value
		},
	},

	{
		selector: applyPrefixFor('[data-cards] .entry-card', prefix),
		variable: 'card-inner-spacing',
		responsive: true,
		unit: '',
		extractValue: () => getOptionFor('card_spacing', prefix),
	},

	{
		selector: applyPrefixFor('.entry-card', prefix),
		type: 'spacing',
		variable: 'theme-border-radius',
		responsive: true,
		extractValue: () => {
			const cardRadius = getOptionFor('cardRadius', prefix)

			return cardRadius
		},
	},
	{
		selector: applyPrefixFor('.entry-card', prefix),
		variable: 'card-border',
		type: 'border',
		responsive: true,
		skip_none: true,
		extractValue: () => getOptionFor('cardBorder', prefix),
	},
]

export const getPostListingVariables = () => ({
	...typographyOption({
		id: `${prefix}_cardTitleFont`,
		selector: applyPrefixFor('.entry-card .entry-title', prefix),
	}),

	[`${prefix}_archive_order`]: (v) => {
		let variables = []

		v.map((layer) => {
			if (layer.typography) {
				variables = [
					...variables,
					...typographyOption({
						id: 'test',
						selector: applyPrefixFor(
							`[data-field*="${layer.__id.substring(0, 6)}"]`,
							prefix
						),
						extractValue: (value) => {
							return layer.typography
						},
					}).test,
				]
			}

			if (layer.color) {
				variables = [
					...variables,

					{
						selector: applyPrefixFor(
							`[data-field*="${layer.__id.substring(0, 6)}"]`,
							prefix
						),
						variable: 'theme-text-color',
						type: 'color:default',
						extractValue: () => {
							return layer.color
						},
					},

					{
						selector: applyPrefixFor(
							`[data-field*="${layer.__id.substring(0, 6)}"]`,
							prefix
						),
						variable: 'theme-link-hover-color',
						type: 'color:hover',
						extractValue: () => {
							return layer.color
						},
					},
				]
			}

			if (layer.id === 'featured_image') {
				variables = [
					...variables,

					{
						selector: applyPrefixFor('.entry-card', prefix),
						variable: 'card-media-max-width',
						unit: '%',
						extractValue: () => {
							return layer.image_width
						},
					},
				]
			}
		})

		return [...variables, ...imageBorderVariables]
	},

	[`${prefix}_columns`]: [
		{
			selector: applyPrefixFor('.entries', prefix),
			variable: 'grid-template-columns',
			responsive: true,
			extractValue: (val) => {
				const responsive = maybePromoteScalarValueIntoResponsive(val)

				return {
					desktop: `repeat(${responsive.desktop}, minmax(0, 1fr))`,
					tablet: `repeat(${responsive.tablet}, minmax(0, 1fr))`,
					mobile: `repeat(${responsive.mobile}, minmax(0, 1fr))`,
				}
			},
		},
	],

	[`${prefix}_cardTitleColor`]: [
		{
			selector: applyPrefixFor('.entry-card .entry-title', prefix),
			variable: 'theme-heading-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-card .entry-title', prefix),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	...typographyOption({
		id: `${prefix}_cardExcerptFont`,
		selector: applyPrefixFor('.entry-excerpt', prefix),
	}),

	[`${prefix}_cardExcerptColor`]: {
		selector: applyPrefixFor('.entry-excerpt', prefix),
		variable: 'theme-text-color',
		type: 'color',
	},

	...typographyOption({
		id: `${prefix}_cardMetaFont`,
		selector: applyPrefixFor('.entry-card .entry-meta', prefix),
	}),

	[`${prefix}_cardMetaColor`]: [
		{
			selector: applyPrefixFor('.entry-card .entry-meta', prefix),
			variable: 'theme-text-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-card .entry-meta', prefix),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_card_meta_button_type_font_colors`]: [
		{
			selector: applyPrefixFor('.entry-card [data-type="pill"]', prefix),
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-card [data-type="pill"]', prefix),
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_card_meta_button_type_background_colors`]: [
		{
			selector: applyPrefixFor('.entry-card [data-type="pill"]', prefix),
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-card [data-type="pill"]', prefix),
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_cardButtonSimpleTextColor`]: [
		{
			selector: applyPrefixFor('.entry-button', prefix),
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-button', prefix),
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_cardButtonBackgroundTextColor`]: [
		{
			selector: applyPrefixFor('.entry-button.ct-button', prefix),
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-button.ct-button', prefix),
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_cardButtonOutlineTextColor`]: [
		{
			selector: applyPrefixFor('.entry-button.ct-button-ghost', prefix),
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-button.ct-button-ghost', prefix),
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	[`${prefix}_cardButtonColor`]: [
		{
			selector: applyPrefixFor('.entry-button', prefix),
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: applyPrefixFor('.entry-button', prefix),
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	...handleBackgroundOptionFor({
		id: `${prefix}_cardBackground`,
		selector: applyPrefixFor('.entry-card', prefix),
		responsive: true,
	}),

	...handleBackgroundOptionFor({
		id: `${prefix}_card_overlay_background`,
		selector: applyPrefixFor(
			'.entry-card .ct-media-container:after',
			prefix
		),
		responsive: true,
	}),

	[`${prefix}_cardDivider`]: {
		selector: applyPrefixFor('[data-cards="simple"] .entry-card', prefix),
		variable: 'card-border',
		type: 'border',
	},

	[`${prefix}_entryDivider`]: {
		selector: applyPrefixFor('.entry-card', prefix),
		variable: 'entry-divider',
		type: 'border',
	},

	...withKeys(
		[`${prefix}_cardThumbRadius`, `${prefix}_card_min_height`],

		[
			{
				selector: applyPrefixFor(
					'.entry-card .ct-media-container',
					prefix
				),
				type: 'spacing',
				variable: 'theme-border-radius',
				responsive: true,
				extractValue: () => {
					return getOptionFor('cardThumbRadius', prefix)
				},
			},

			{
				selector: applyPrefixFor('.entries', prefix),
				variable: 'card-min-height',
				responsive: true,
				unit: 'px',
				extractValue: () => getOptionFor('card_min_height', prefix),
			},
		]
	),

	[`${prefix}_cardsGap`]: {
		selector: applyPrefixFor('.entries', prefix),
		variable: 'grid-columns-gap',
		responsive: true,
		unit: '',
	},

	...withKeys(
		[
			`${prefix}_card_spacing`,
			`${prefix}_cardRadius`,
			`${prefix}_cardBorder`,
		],
		imageBorderVariables
	),

	[`${prefix}_cardShadow`]: {
		selector: applyPrefixFor('.entry-card', prefix),
		type: 'box-shadow',
		variable: 'theme-box-shadow',
		responsive: true,
	},

	[`${prefix}_content_horizontal_alignment`]: [
		{
			selector: applyPrefixFor('.entry-card', prefix),
			variable: 'text-horizontal-alignment',
			responsive: true,
			unit: '',
		},

		{
			selector: applyPrefixFor('.entry-card', prefix),
			variable: 'horizontal-alignment',
			responsive: true,
			unit: '',
			extractValue: (value) => {
				if (!value.desktop) {
					return value
				}

				if (value.desktop === 'left') {
					value.desktop = 'flex-start'
				}

				if (value.desktop === 'right') {
					value.desktop = 'flex-end'
				}

				if (value.tablet === 'left') {
					value.tablet = 'flex-start'
				}

				if (value.tablet === 'right') {
					value.tablet = 'flex-end'
				}

				if (value.mobile === 'left') {
					value.mobile = 'flex-start'
				}

				if (value.mobile === 'right') {
					value.mobile = 'flex-end'
				}

				return value
			},
		},
	],

	[`${prefix}_content_vertical_alignment`]: {
		selector: applyPrefixFor('.entry-card', prefix),
		variable: 'vertical-alignment',
		responsive: true,
		unit: '',
	},

	...(prefix.indexOf('single') === -1
		? {
				...handleBackgroundOptionFor({
					id: `${prefix}_background`,
					selector: `[data-prefix="${prefix}"]`,
					responsive: true,
				}),
		  }
		: {}),
})
