import $ from 'jquery'

if ($) {
	// https://woocommerce.com/document/composite-products/composite-products-js-api-reference/#using-the-api
	$('.composite_data').on('wc-composite-initializing', (event, composite) => {
		composite.actions.add_action('component_selection_changed', () => {
			setTimeout(() => {
				ctEvents.trigger('blocksy:frontend:init')
			}, 1000)
		})
	})
	;[
		'updated_checkout',
		'wc_fragments_refreshed',
		'found_variation',
		'reset_data',
		'wc_fragments_loaded',
		'prdctfltr-reload',
		'wpf_ajax_success',
	].map((event) => {
		$(document.body).on(event, () =>
			ctEvents.trigger('blocksy:frontend:init')
		)

		$(window).on(event, () => ctEvents.trigger('blocksy:frontend:init'))
	})

	$('.wc-product-table').on('draw.wcpt', () => {
		ctEvents.trigger('blocksy:frontend:init')
	})

	setTimeout(() => {
		if (window.woof_mass_reinit) {
			const prevFn = window.woof_mass_reinit

			window.woof_mass_reinit = () => {
				ctEvents.trigger('blocksy:frontend:init')
				prevFn()
			}
		}
	}, 1000)
}
