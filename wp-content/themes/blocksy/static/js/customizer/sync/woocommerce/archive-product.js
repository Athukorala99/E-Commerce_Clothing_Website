import { getOptionFor, setRatioFor, watchOptionsWithPrefix } from '../helpers'
import ctEvents from 'ct-events'

export const replaceCards = () => {
	if (!document.querySelector('[data-products]')) {
		return
	}

	;[...document.querySelectorAll('[data-products]')].map((el) => {
		el.classList.add('ct-disable-transitions')
	})
	;[...document.querySelectorAll('[data-products] > *')].map((product) => {
		const woo_card_layout = wp.customize('woo_card_layout')()

		const maybeProductImage = woo_card_layout.find(
			({ id, enabled }) => enabled && id === 'product_image'
		)

		if (maybeProductImage) {
			const ratio =
				maybeProductImage.blocksy_woocommerce_archive_thumbnail_cropping ||
				'predefined'

			if (product.querySelector('.ct-media-container')) {
				setRatioFor(
					ratio === 'uncropped'
						? 'original'
						: ratio === 'custom' || ratio === 'predefined'
						? `${wp.customize(
								'woocommerce_archive_thumbnail_cropping_custom_width'
						  )()}/${wp.customize(
								'woocommerce_archive_thumbnail_cropping_custom_height'
						  )()}`
						: '1/1',
					product.querySelector('.ct-media-container')
				)
			}
		}
	})
	;[...document.querySelectorAll('[data-products]')].map((el) => {
		if (el.closest('.related') || el.closest('.upsells')) {
			return
		}

		el.classList.remove('columns-2', 'columns-3', 'columns-4', 'columns-5')

		el.classList.add(
			`columns-${getOptionFor('woocommerce_catalog_columns')}`
		)
	})

	setTimeout(() => {
		;[...document.querySelectorAll('[data-products]')].map((el) => {
			el.classList.remove('ct-disable-transitions')
		})
	})
}

watchOptionsWithPrefix({
	getOptionsForPrefix: () => [
		'woocommerce_catalog_columns',
		'woo_card_layout',
		'woocommerce_archive_thumbnail_cropping_custom_width',
		'woocommerce_archive_thumbnail_cropping_custom_height',
	],

	render: () => replaceCards(),
})
