import { responsiveClassesFor } from '../../helpers'

export const getWooWishlistVariablesFor = () => ({
	wish_list_share_box_title: (v) => {
		let variables = []

		const titleEl = document.querySelector(
			'.ct-woo-account .ct-share-box .ct-module-title'
		)

		if (titleEl) {
			titleEl.innerHTML = v
		}

		return variables
	},
	wish_list_share_box_visibility: (v) => {
		let variables = []

		const el = document.querySelector('.ct-woo-account .ct-share-box')

		if (el) {
			responsiveClassesFor(v, el)
		}

		return variables
	},
})
