import { responsiveClassesFor, updateAndSaveEl } from '../../../sync'
import { typographyOption } from '../typography'

const collectVariablesForLayers = (v) => {
	let variables = []
	v.map((layer) => {
		let selectorsMap = {
			product_title: '.entry-summary-items > .entry-title',
			product_rating:
				'.entry-summary-items > .woocommerce-product-rating',
			product_price: '.entry-summary-items > .price',
			product_desc:
				'.entry-summary-items > .woocommerce-product-details__short-description',
			product_add_to_cart:
				'.entry-summary-items > .ct-product-add-to-cart',
			product_meta: '.entry-summary-items > .product_meta',
			product_payment_methods:
				'.entry-summary-items > .ct-payment-methods',
			additional_info:
				'.entry-summary-items > .ct-product-additional-info',
			product_tabs: '.entry-summary-items > .woocommerce-tabs',

			// companion
			product_brands: '.entry-summary-items > .ct-product-brands-single',
			product_sharebox: '.entry-summary-items > .ct-share-box',
			free_shipping:
				'.entry-summary-items > .ct-shipping-progress-single',
			product_actions:
				'.entry-summary-items > .ct-product-additional-actions',
			product_countdown:
				'.entry-summary-items > .ct-product-sale-countdown',
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

						switch (layer.id) {
							case 'product_title':
								defaultValue = 10
								break
							case 'product_rating':
								defaultValue = 10
								break
							case 'product_price':
								defaultValue = 35
								break
							case 'product_desc':
								defaultValue = 35
								break
							case 'product_add_to_cart':
								defaultValue = 35
								break
							case 'product_meta':
								defaultValue = 10
								break
							case 'product_payment_methods':
								defaultValue = 10
								break
							case 'additional_info':
								defaultValue = 10
								break
							case 'product_actions':
								defaultValue = 35
								break
							case 'product_countdown':
								defaultValue = 35
							default:
								break
						}

						return layer.spacing || defaultValue
					},
				},
			]
		}

		if (layer.id === 'additional_info') {
			if (layer?.additional_info_items?.length) {
				let maybeAdditionalInfo = document.querySelector(
					'.ct-product-additional-info ul'
				)

				if (maybeAdditionalInfo) {
					layer.additional_info_items.map((item, index) => {
						const additionalInfoItem =
							maybeAdditionalInfo.children[index]

						if (additionalInfoItem) {
							let maybeLabel =
								additionalInfoItem.querySelector('.ct-label')

							if (maybeLabel) {
								maybeLabel.innerHTML = item.item_title
							}
						}
					})
				}
			}
		}

		if (layer.id === 'product_sharebox') {
			const titleEl = document.querySelector(
				`${selectorsMap[layer.id]} .ct-module-title`
			)

			if (titleEl && typeof layer?.share_box_title !== 'undefined') {
				titleEl.innerHTML = layer.share_box_title
			}

			variables = [
				...variables,

				{
					selector: selectorsMap[layer.id],
					variable: 'theme-icon-size',
					responsive: true,
					unit: '',
					extractValue: () => {
						return layer.share_box_icon_size || '15px'
					},
				},

				{
					selector: selectorsMap[layer.id],
					variable: 'items-spacing',
					responsive: true,
					unit: '',
					extractValue: () => {
						return layer.share_box_icons_spacing || '15px'
					},
				},
			]
		}

		if (layer.id === 'additional_info') {
			const titleEl = document.querySelector(
				`${selectorsMap[layer.id]} span`
			)

			if (
				titleEl &&
				typeof layer?.product_additional_info_title !== 'undefined'
			) {
				titleEl.innerHTML = layer.product_additional_info_title
			}
		}

		if (layer.id === 'product_payment_methods') {
			const legendEl = document.querySelector(
				`${selectorsMap[layer.id]} legend`
			)

			if (
				legendEl &&
				typeof layer?.payment_methods_title !== 'undefined'
			) {
				legendEl.innerHTML = layer.payment_methods_title
			}

			variables = [
				...variables,

				{
					selector: selectorsMap[layer.id],
					variable: 'theme-icon-size',
					responsive: true,
					unit: 'px',
					extractValue: () => {
						return layer.payment_icons_size || 40
					},
				},
			]
		}

		if (layer.id === 'product_add_to_cart') {
			const labelEl = document.querySelector(
				`${selectorsMap[layer.id]} .ct-module-title`
			)

			if (
				labelEl &&
				typeof layer?.add_to_cart_layer_title !== 'undefined'
			) {
				labelEl.innerHTML = layer.add_to_cart_layer_title
			}

			variables = [
				...variables,

				{
					selector: `${selectorsMap[layer.id]} > .cart`,
					variable: 'theme-button-max-width',
					responsive: true,
					unit: '',
					extractValue: () => {
						return layer.add_to_cart_button_width
					},
				},
			]
		}

		if (layer.id === 'product_brands') {
			const labelEl = document.querySelector(
				`${selectorsMap[layer.id]} .ct-module-title`
			)

			if (labelEl && typeof layer?.brand_layer_title !== 'undefined') {
				labelEl.innerHTML = layer.brand_layer_title
			}

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

		if (layer.id === 'divider') {
			variables = [
				...variables,
				{
					selector: `.entry-summary-items > .ct-product-divider[data-id="${
						layer?.__id || 'default'
					}"]`,
					variable: 'product-element-spacing',
					responsive: true,
					unit: 'px',
					extractValue: () => {
						return layer.spacing || 35
					},
				},
			]
		}

		if (layer.id === 'content-block') {
			variables = [
				...variables,
				{
					selector: `.entry-summary-items > .ct-product-content-block[data-id="${
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

		if (layer.id === 'product_actions') {
			const actionButtons = document.querySelectorAll(
				'.entry-summary-items > .ct-product-additional-actions a'
			)

			const container = document.querySelector(selectorsMap[layer.id])

			if (container) {
				container.dataset.type = layer.actions_type || 'link'
			}

			if (actionButtons.length) {
				actionButtons.forEach((button, index) => {
					;[...button.querySelectorAll('.ct-label')].map((label) => {
						responsiveClassesFor(layer.label_visibility, label)

						if (layer?.woo_actions_layout?.[index]?.label) {
							label.innerHTML =
								layer.woo_actions_layout[index].label
						}
					})
				})
			}

			variables = [...variables]
		}

		if (layer.id === 'product_countdown') {
			const titleEl = document.querySelector(
				`${selectorsMap[layer.id]} span`
			)

			if (
				titleEl &&
				typeof layer?.product_countdown_title !== 'undefined'
			) {
				titleEl.innerHTML = layer.product_countdown_title
			}
		}
	})

	return variables
}

export const getWooSingleLayersVariablesFor = () => ({
	woo_single_layout: collectVariablesForLayers,
	woo_single_split_layout: (v) => {
		return [
			...collectVariablesForLayers(v.left),
			...collectVariablesForLayers(v.right),
		]
	},

	// product title
	...typographyOption({
		id: 'singleProductTitleFont',
		selector: '.entry-summary .entry-title',
	}),

	singleProductTitleColor: {
		selector: '.entry-summary .entry-title',
		variable: 'theme-heading-color',
		type: 'color',
	},

	// product price
	...typographyOption({
		id: 'singleProductPriceFont',
		selector: '.entry-summary .price',
	}),

	singleProductPriceColor: {
		selector: '.entry-summary .price',
		variable: 'theme-text-color',
		type: 'color',
	},

	// quantity input
	quantity_color: [
		{
			selector: '.entry-summary .quantity',
			variable: 'quantity-initial-color',
			type: 'color:default',
		},

		{
			selector: '.entry-summary .quantity',
			variable: 'quantity-hover-color',
			type: 'color:hover',
		},
	],

	quantity_arrows: [
		{
			selector: '.entry-summary .quantity[data-type="type-1"]',
			variable: 'quantity-arrows-initial-color',
			type: 'color:default',
		},

		{
			selector: '.entry-summary .quantity[data-type="type-2"]',
			variable: 'quantity-arrows-initial-color',
			type: 'color:default_type_2',
		},

		{
			selector: '.entry-summary .quantity',
			variable: 'quantity-arrows-hover-color',
			type: 'color:hover',
		},
	],

	// add to cart & view cart buttons
	add_to_cart_text: [
		{
			selector: '.entry-summary .single_add_to_cart_button',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: '.entry-summary .single_add_to_cart_button',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	add_to_cart_background: [
		{
			selector: '.entry-summary .single_add_to_cart_button',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: '.entry-summary .single_add_to_cart_button',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	view_cart_button_text: [
		{
			selector: '.entry-summary .ct-cart-actions .added_to_cart',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: '.entry-summary .ct-cart-actions .added_to_cart',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	view_cart_button_background: [
		{
			selector: '.entry-summary .ct-cart-actions .added_to_cart',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: '.entry-summary .ct-cart-actions .added_to_cart',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	// divider
	woo_single_layers_divider: {
		selector: '.entry-summary .ct-product-divider',
		variable: 'single-product-layer-divider',
		type: 'border',
	},

	// payment methods
	payment_method_icons_color: {
		selector: '.entry-summary .ct-payment-methods[data-color="custom"]',
		variable: 'theme-icon-color',
		type: 'color',
	},

	// product tabs
	...typographyOption({
		id: 'woo_tabs_font',
		selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
	}),

	woo_tabs_font_color: [
		{
			selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},

		{
			selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			variable: 'theme-link-active-color',
			type: 'color:active',
		},
	],

	woo_tabs_border_color: {
		selector: '.woocommerce-tabs[data-type] .tabs, .woocommerce-tabs .ct-accordion-heading',
		variable: 'tab-border-color',
		type: 'color',
	},

	woo_actibe_tab_border: {
		selector: '.woocommerce-tabs[data-type] .tabs',
		variable: 'tab-background',
		type: 'color',
	},

	woo_actibe_tab_background: [
		{
			selector: '.woocommerce-tabs[data-type*="type-2"] .tabs',
			variable: 'tab-background',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-tabs[data-type*="type-2"] .tabs li.active',
			variable: 'tab-border-color',
			type: 'color:border',
		},
	],

	woo_separated_tabs_spacing: {
		selector: '.woocommerce-tabs[data-type*="type-4"]',
		variable: 'woo-separated-tabs-spacing',
		responsive: true,
		unit: 'px',
	},
})
