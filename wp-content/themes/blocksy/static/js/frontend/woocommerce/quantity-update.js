import $ from 'jquery'

let request = null

export const mount = (el) => {
	var item_hash = $(el)
		.attr('name')
		.replace(/cart\[([\w]+)\]\[qty\]/g, '$1')

	var item_quantity = $(el).val()
	var currentVal = parseFloat(item_quantity)

	if (request) {
		request.abort()
		request = null
	}

	const maybeMiniCartItem = el.closest(
		'.woocommerce-mini-cart-item'
	)

	if (maybeMiniCartItem) {
		maybeMiniCartItem.classList.add('processing')
	}

	request = $.ajax({
		type: 'POST',
		url: ct_localizations.ajax_url,
		data: {
			action: 'blocksy_update_qty_cart',
			hash: item_hash,
			quantity: currentVal,
		},
		success: (data) => {
			jQuery('body').trigger('updated_wc_div')
			ctEvents.trigger('ct:header:update')
		},
	})
}
