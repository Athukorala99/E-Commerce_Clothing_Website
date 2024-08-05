wp.customize('account_page_avatar_size', (val) =>
	val.bind((to) => {
		Array.from(document.querySelectorAll('.ct-account-welcome img')).map(
			(el) => {
				el.height = to || '25'
				el.width = to || '25'
				el.style.height = `${to || 25}px`
			}
		)
	})
)

wp.customize('sale_badge_shape', (val) =>
	val.bind((to) => {
		Array.from(
			document.querySelectorAll(
				'.onsale,.out-of-stock-badge,.ct-woo-badge-new,.ct-woo-badge-featured'
			)
		).map((el) => {
			el.dataset.shape = to
		})
	})
)

wp.customize('sale_badge_default_value', (val) =>
	val.bind((to) => {
		Array.from(document.querySelectorAll('.onsale')).map((el) => {
			el.textContent = to
		})
	})
)

wp.customize('stock_badge_value', (val) =>
	val.bind((to) => {
		Array.from(document.querySelectorAll('.out-of-stock-badge')).map(
			(el) => {
				el.textContent = to
			}
		)
	})
)

wp.customize('store_notice_position', (val) =>
	val.bind((to) => {
		if (!document.querySelector('.woocommerce-store-notice')) {
			return
		}

		document.querySelector('.woocommerce-store-notice').dataset.position =
			to
	})
)
