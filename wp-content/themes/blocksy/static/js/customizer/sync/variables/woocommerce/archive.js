import { withKeys } from '../../helpers'
import { typographyOption } from '../typography'
import { handleBackgroundOptionFor } from '../background'

export const getWooArchiveVariablesFor = () => ({
	woo_card_layout: (v) => {
		let variables = []

		v.map((layer) => {
			let selectorsMap = {
				product_image: '[data-products] .product figure',
				product_title: '[data-products] .product .woocommerce-loop-product__title',
				product_price: '[data-products] .product .price',
				product_rating: '[data-products] .product .star-rating',
				product_meta: '[data-products] .product .entry-meta',
				product_desc: '[data-products] .product .entry-excerpt',
				product_add_to_cart: '[data-products] .product .ct-woo-card-actions',
				product_add_to_cart_and_price: '[data-products] .product .ct-woo-card-actions',

				// companion
				product_brands: '[data-products] .product .ct-product-brands',
				product_swatches: '[data-products] .product .ct-card-variation-swatches',
				product_sku: '[data-products] .product .ct-product-sku',
			}

			if (selectorsMap[layer.id]) {
				variables = [
					...variables,
					{
						selector: selectorsMap[layer.id],
						variable: 'product-element-spacing',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							let defaultValue = 10

							if (
								layer.id === 'product_image' ||
								layer.id === 'product_desc'
							) {
								defaultValue = 25
							}

							return layer.spacing || defaultValue
						},
					},
				]
			}

			if (layer.id === 'product_meta') {
				let maybeMetaBox = document.querySelectorAll(
					'[data-products] .product .entry-meta .meta-categories'
				)

				if (maybeMetaBox) {
					maybeMetaBox.forEach(
						(metabox) =>
							(metabox.dataset.type = layer?.style || 'simple')
					)
				}
			}

			if (layer.id === 'product_brands') {
				variables = [
					...variables,

					{
						selector: selectorsMap[layer.id],
						variable: 'product-brand-logo-size',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							return layer.brand_logo_size || 100
						},
					},

					{
						selector: selectorsMap[layer.id],
						variable: 'product-brands-gap',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							return layer.brand_logo_gap || 10
						},
					},
				]
			}

			if (layer.id === 'content-block') {
				variables = [
					...variables,
					{
						selector: `[data-products] .product .ct-product-content-block[data-id="${
							layer?.__id || 'default'
						}"]`,
						variable: 'product-element-spacing',
						responsive: true,
						unit: 'px',
						extractValue: () => {
							return layer.spacing || 10
						},
					},
				]
			}
		})

		return variables
	},

	// archive columns
	...withKeys(['woocommerce_catalog_columns', 'blocksy_woo_columns'], {
		selector: '[data-products]',
		variable: 'shop-columns',
		responsive: true,
		unit: '',
		extractValue: () => {
			const value = wp.customize('blocksy_woo_columns')()

			return {
				desktop: `CT_CSS_SKIP_RULE`,
				tablet: `repeat(${value.tablet}, minmax(0, 1fr))`,
				mobile: `repeat(${value.mobile}, minmax(0, 1fr))`,
			}
		},
	}),

	// archive columns & rows gap
	shopCardsGap: {
		selector: '[data-products]',
		variable: 'grid-columns-gap',
		responsive: true,
		unit: '',
	},

	shopCardsRowGap: {
		selector: '[data-products]',
		variable: 'grid-rows-gap',
		responsive: true,
		unit: '',
	},

	// border radius
	cardProductRadius: {
		selector: '[data-products] .product',
		type: 'spacing',
		variable: 'theme-border-radius',
		responsive: true,
	},

	// product title
	...typographyOption({
		id: 'cardProductTitleFont',
		selector:
			'[data-products] .woocommerce-loop-product__title, [data-products] .woocommerce-loop-category__title',
	}),

	cardProductTitleColor: [
		{
			selector:
				'[data-products] .woocommerce-loop-product__title, [data-products] .woocommerce-loop-category__title',
			variable: 'theme-heading-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector:
				'[data-products] .woocommerce-loop-product__title, [data-products] .woocommerce-loop-category__title',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	// product excerpt
	...typographyOption({
		id: 'cardProductExcerptFont',
		selector: '[data-products] .entry-excerpt',
	}),

	cardProductExcerptColor: {
		selector: '[data-products] .entry-excerpt',
		variable: 'theme-text-color',
		type: 'color',
		responsive: true,
	},

	// product price
	...typographyOption({
		id: 'cardProductPriceFont',
		selector: '[data-products] .product .price',
	}),

	cardProductPriceColor: {
		selector: '[data-products] .product .price',
		variable: 'theme-text-color',
		type: 'color',
		responsive: true,
	},

	// product SKU
	...typographyOption({
		id: 'cardProductSkuFont',
		selector: '[data-products] .ct-product-sku',
	}),

	cardProductSkuColor: {
		selector: '[data-products] .ct-product-sku',
		variable: 'theme-text-color',
		type: 'color',
		responsive: true,
	},

	// star rating
	starRatingColor: [
		{
			selector: ':root',
			variable: 'star-rating-initial-color',
			type: 'color:default',
		},

		{
			selector: ':root',
			variable: 'star-rating-inactive-color',
			type: 'color:inactive',
		},
	],

	// categories/meta
	...typographyOption({
		id: 'card_product_categories_font',
		selector: '[data-products] .entry-meta',
	}),

	cardProductCategoriesColor: [
		{
			selector: '[data-products] .entry-meta',
			variable: 'theme-link-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: '[data-products] .entry-meta',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	card_product_categories_button_type_font_colors: [
		{
			selector: '[data-products] [data-type="pill"]',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: '[data-products] [data-type="pill"]',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	card_product_categories_button_type_background_colors: [
		{
			selector: '[data-products] [data-type="pill"]',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: '[data-products] [data-type="pill"]',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	// archive background
	...handleBackgroundOptionFor({
		id: 'shop_archive_background',
		selector: '[data-prefix="woo_categories"]',
		responsive: true,
	}),

	// cards type 1
	cardProductButton1Text: [
		{
			selector: '[data-products="type-1"]',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: '[data-products="type-1"]',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	cardProductButtonBackground: [
		{
			selector: '[data-products="type-1"]',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: '[data-products="type-1"]',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	// cards type 2
	cardProductButton2Text: [
		{
			selector: '[data-products="type-2"] .ct-woo-card-actions',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: '[data-products="type-2"] .ct-woo-card-actions',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
			responsive: true,
		},
	],

	cardProductBackground: {
		selector: '[data-products="type-2"]',
		variable: 'backgroundColor',
		type: 'color',
		responsive: true,
	},

	cardProductShadow: {
		selector: '[data-products="type-2"]',
		type: 'box-shadow',
		variable: 'theme-box-shadow',
		responsive: true,
	},

	// csrds type 1 and type 3
	shop_cards_alignment: [
		{
			selector: '[data-products] .product',
			variable: 'horizontal-alignment',
			responsive: true,
			unit: '',
		},

		{
			selector: '[data-products] .product',
			variable: 'text-horizontal-alignment',
			responsive: true,
			unit: '',
			extractValue: (value) => {
				if (!value.desktop) {
					return value
				}

				if (value.desktop === 'flex-start') {
					value.desktop = 'left'
				}

				if (value.desktop === 'flex-end') {
					value.desktop = 'right'
				}

				if (value.tablet === 'flex-start') {
					value.tablet = 'left'
				}

				if (value.tablet === 'flex-end') {
					value.tablet = 'right'
				}

				if (value.mobile === 'flex-start') {
					value.mobile = 'left'
				}

				if (value.mobile === 'flex-end') {
					value.mobile = 'right'
				}

				return value
			},
		},
	],
})
