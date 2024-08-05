<?php

function blc_exts_get_preliminary_config($ext = null) {
	$data = [
		'adobe-typekit' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Adobe Fonts', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Connect your Adobe Fonts project and use the selected fonts throughout Blocksy and your favorite page builder.', 'blocksy-companion'),
			'pro' => true,

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/adobe-typekit-fonts/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/shorts/adobe-fonts-quickie/',
			'icon' => '<svg width="15" height="15" viewBox="0 0 16 16"><path d="M9.3 6c-.6 2-1.1 4-1.7 5.9-.3 1-.7 1.9-1.2 2.7-.6.8-1.6 1.4-2.6 1.4-.8 0-1.6-.4-1.6-1.3 0-.5.5-1 1-1 .2 0 .4.1.5.3.4.7.8 1.2 1 1.2s.3-.2.6-1.3l2-7.9H5.8c-.2-.3-.1-.7.2-.9h1.5c.3-1 .7-1.9 1.1-2.8.6-1.4 2-2.3 3.5-2.4 1.2 0 1.7.6 1.7 1.3 0 .6-.4 1.1-1 1.2-.3 0-.4-.2-.5-.5-.3-1-.6-1.3-.8-1.3s-.5.5-.9 1.3c-.4 1.1-.8 2.1-1 3.2h1.9c.1.3 0 .6-.3.8L9.3 6z"/></svg>',
		],

		'code-snippets' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Custom Code Snippets', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Inject custom code snippets throughout your website. The extension works globally or on a per post/page basis.', 'blocksy-companion'),

			'pro' => true,

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/custom-code-snippets/',
			// 'video' => 'https://www.youtube.com/watch?v=6ZQY9Z9ZQZQ',
			'customize' => admin_url('customize.php?ct_autofocus=header_footer_scripts'),
			'icon' => '<svg width="16" height="16" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM5.6 10.4c-.3.2-.7.2-.9-.1L3.1 8.4c-.1-.2-.1-.6 0-.8l1.6-1.9c.2-.3.6-.3.9-.1.3.2.3.6.1.9L4.4 8l1.2 1.5c.3.2.3.6 0 .9zm2.4.8c-.1.3-.3.5-.6.5h-.1c-.3-.1-.6-.4-.5-.7L8 4.8c.1-.3.4-.6.7-.5.4 0 .6.4.5.7L8 11.2zm4.9-2.8-1.6 1.9c-.2.3-.6.3-.9.1-.3-.2-.3-.6-.1-.9L11.6 8l-1.2-1.5c-.2-.3-.2-.7.1-.9.3-.2.7-.2.9.1L13 7.6c0 .2 0 .6-.1.8z"/></svg>',
		],

		'color-mode-switch' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Color Mode Switch', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Add a dark colour scheme and switch to your website, which will make it pleasant to look at in low light environments.', 'blocksy-companion'),

			'pro' => true,

			'documentation' => '#',
			// 'video' => 'https://www.youtube.com/watch?v=6ZQY9Z9ZQZQ',
			'customize' => admin_url('customize.php?ct_autofocus=header'),
			'icon' => '<svg with="16" height="16" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14.5v-13c3.6 0 6.5 2.9 6.5 6.5s-2.9 6.5-6.5 6.5z"/></svg>'
		],

		'custom-fonts' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Custom Fonts', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Upload an unlimited number of custom fonts or variable fonts and use them throughout Blocksy and your favorite page builder.', 'blocksy-companion'),

			'pro' => true,

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/custom-fonts/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/exploring-the-custom-fonts-extension/',
			'icon' => '<svg width="16" height="16" viewBox="0 0 16 16"><path d="M15.2 13.5h-.3L9.6 1.4c-.1-.3-.4-.5-.7-.5H7.3c-.3 0-.6.2-.7.5L2 13.5h-.2c-.4 0-.8.4-.8.8s.4.8.8.8h2.4c.4 0 .8-.4.8-.8s-.4-.8-.8-.8h-.5l.9-2.4h4.5l1 2.4h-.4c-.4 0-.8.4-.8.8s.4.8.8.8h5.5c.4 0 .8-.4.8-.8s-.4-.8-.8-.8zm-10-3.9 1.5-4 1.7 4H5.2z"/></svg>'
		],

		'local-google-fonts' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Local Google Fonts', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __(
				'Serve your chosen Google Fonts from your own web server. This will increase the loading speed and makes sure your website complies with the privacy regulations.',
				'blocksy-companion'
			),

			'pro' => true,

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/local-google-fonts/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/local-google-fonts/',
			'icon' => '<svg width="16" height="16" viewBox="0 0 16 16"><path d="M16 10.7c0 1.9-1.6 3.5-3.5 3.5v-7c1.9 0 3.5 1.6 3.5 3.5zm-.8-6.2c0-1.5-1.2-2.7-2.7-2.7v5.3c1.5.1 2.7-1.1 2.7-2.6zm-6.1 6.2c0-1.9 1.6-3.5 3.5-3.5-.7 0-1.3-.3-1.8-.7l-.1-.1c-.1-.1-.1-.2-.2-.3l-.1-.1c-.1-.1-.1-.2-.2-.3v-.1c-.2-.2-.2-.4-.3-.5 0-.2-.1-.3-.1-.5 0-1.5 1.2-2.7 2.7-2.7H7.7L0 14.1h5.9l1.9-3v3h4.8c-2 0-3.5-1.5-3.5-3.4zm-6.4-4c1.3 0 2.4-1.1 2.4-2.4S4 1.9 2.7 1.9.3 2.9.3 4.3s1 2.4 2.4 2.4z"/></svg>',
		],

		'mega-menu' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Advanced Menu', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Create beautiful personalised menus that set your website apart from the others. Add icons and badges to your entries and even add Content Blocks inside your dropdowns.', 'blocksy-companion'),

			'pro' => true,
			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/advanced-menu/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/the-advanced-menus-extension/',
			'customize' => admin_url('nav-menus.php'),
			'icon' => '<svg with="15" height="15" viewBox="0 0 16 16"><path d="M.5 2.6c0-.5.4-.9.9-.9h2.1c.5 0 1 .3 1 .9-.1.6-.5 1-.9 1H1.4c-.4 0-.9-.4-.9-1zm6.4 1H9c.5 0 .9-.4.9-.9s-.4-1-.8-1H6.9c-.5 0-.9.5-.9.9 0 .6.5 1 .9 1zm5.5 0h2.1c.5 0 .9-.4.9-.9s-.4-1-.8-1h-2.1c-.6 0-.9.5-.9.9-.1.6.4 1 .8 1zM.5 6h15v7.5c0 .8-.7 1.5-1.5 1.5H2c-.8 0-1.5-.7-1.5-1.5V6zm9.5 4.2c0 .9.7 1.6 1.6 1.6s1.6-.7 1.6-1.6-.7-1.6-1.6-1.6-1.6.7-1.6 1.6zM2.8 9c0 .3.2.5.5.5h3c.4 0 .6-.2.5-.5 0-.3-.2-.5-.5-.5h-3c-.4 0-.5.2-.5.5zm0 2.3c0 .3.2.5.5.5h3c.4 0 .5-.2.5-.5s-.2-.5-.5-.5h-3c-.4.1-.5.3-.5.5z"/></svg>',
		],

		'post-types-extra' => [

			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Post Types Extra', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Enables support for Custom Fields inside archive cards and single page post title, adds a reading progress bar for your posts and lets you set featured images and colors for your categories archives.', 'blocksy-companion'),

			'features' => [
				[
					'id' => 'read-time',
					'title' => __('Read Time', 'blocksy-companion'),
					'description' => __('Display the approximate reading time of an article, so that visitors know what to expect when starting to read the content.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/post-types-extra/',
				],

				[
					'id' => 'dynamic-data',
					'title' => __('Dynamic Data', 'blocksy-companion'),
					'description' => __('Integrates custom fields solutions in the meta layers of a post and presents additional information.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/post-types-extra/',
				],

				[
					'id' => 'filtering',
					'title' => __('Posts Filter', 'blocksy-companion'),
					'description' => __('Let your guests easily filter the posts by their category or tags taxonomy terms, instantly drilling down the listings.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/post-types-extra/',
				],

				[
					'id' => 'taxonomies-customization',
					'title' => __('Taxonomy Customisations', 'blocksy-companion'),
					'description' => __('Additional customisation options for your taxonomies such as hero backgrounds and custom colour labels.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/post-types-extra/',
				]
			],

			'pro' => true,
			'plans' => blc_get_capabilities()->get_features()['post_types_extra'],

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/post-types-extra/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/post-types-extra-extension-walkthrough/',
			'icon' => '<svg width="15" height="15" viewBox="0 0 16 16"><path d="M14 0H2C.9 0 0 .9 0 2v11c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2zM2.7 3.3c0-.3.3-.6.6-.6h3.6c.3 0 .6.3.6.6v5c0 .3-.3.6-.6.6H3.3c-.3 0-.6-.3-.6-.6v-5zm10.6 8.4c0 .3-.3.6-.6.6H3.3c-.3 0-.6-.3-.6-.6v-.4c0-.3.3-.6.6-.6h9.4c.3 0 .6.3.6.6v.4zm0-4.1c0 .3-.3.6-.6.6h-2.5c-.3 0-.6-.3-.6-.6v-.4c0-.3.3-.6.6-.6h2.5c.3 0 .6.3.6.6v.4zm0-3.2c0 .3-.3.6-.6.6h-2.5c-.3 0-.6-.3-.6-.6V4c0-.3.3-.6.6-.6h2.5c.3 0 .6.3.6.6v.4z"/></svg>',
		],

		'shortcuts' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Shortcuts Bar', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __(
				'Easily turn your websites into mobile first experiences. You can easily add the most important actions at the bottom of the screen for easy access.',
				'blocksy-companion'
			),

			'pro' => true,
			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/shortcuts-bar/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/shortcuts-bar-extension-showcase/',
			'customize' => admin_url('customize.php?ct_autofocus=shortcuts_ext'),
			'icon' => '<svg with="15" height="15" viewBox="0 0 16 16"><path d="M14 0H2C.9 0 0 .8 0 1.9v11.2c0 1 .9 1.9 2 1.9h12c1.1 0 2-.8 2-1.9V1.9C16 .8 15.1 0 14 0zM3.3 12.6c-.7 0-1.3-.6-1.3-1.4s.6-1.4 1.4-1.4c.7 0 1.3.6 1.3 1.4s-.6 1.4-1.4 1.4zm4.7 0c-.7 0-1.3-.6-1.3-1.3S7.3 9.9 8 9.9s1.3.6 1.3 1.4-.6 1.3-1.3 1.3zm4.7 0c-.7 0-1.3-.6-1.3-1.3s.6-1.4 1.3-1.4c.7 0 1.4.6 1.4 1.4s-.7 1.3-1.4 1.3z"/></svg>',
		],

		'sidebars' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Multiple Sidebars', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Create unlimited personalized sets of widget areas and display them on any page or post using our conditional logic functionality.', 'blocksy-companion'),

			'buttons' => [
				[
					'text' => __('Create New Sidebar', 'blocksy-companion'),
					'url' => admin_url('widgets.php')
				]
			],

			'pro' => true,

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/multiple-sidebars/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/exploring-sidebars-dynamic-sidebars/',
			'customize' => admin_url('widgets.php'),
			'icon' => '<svg with="15" height="15" viewBox="0 0 16 16"><path d="M14 0H2C.9 0 0 .9 0 2v11c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V2c0-1.1-.9-2-2-2zM9.7 13.6H2.5c-.7 0-1.2-.5-1.2-1.2V2.6c0-.7.5-1.2 1.2-1.2h7.2v12.2z"/></svg>',
		],

		'white-label' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('White Label', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Replace Blocksy\'s branding with your own. Easily hide licensing info and other sections of the theme and companion plugin from your clients and make your final product look more professional.', 'blocksy-companion'),

			'pro' => true,
			'plans' => blc_get_capabilities()->get_features()['white_label'],

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/white-label/',
			'video' => 'https://creativethemes.com/blocksy/video-tutorials/blocksy-premium/the-white-label-extension/',
			'icon' => '<svg with="15" height="15" viewBox="0 0 16 16"><path d="M3.4 4.6C3.4 2 5.5 0 8 0s4.6 2 4.6 4.6-2 4.6-4.6 4.6-4.6-2.1-4.6-4.6zM8 11.1c-3.6 0-5.7 1.6-5.7 3.3 0 .8 2.1 1.6 5.7 1.6 3.4 0 5.7-.8 5.7-1.6 0-1.7-2.2-3.3-5.7-3.3z"/></svg>',
		],

		'woocommerce-extra' => [
			//  translators: This is a brand name. Preferably to not be translated
			'name' => _x('Shop Extra', 'Extension Brand Name', 'blocksy-companion'),
			'description' => __('Make the shopping experience better for your visitors! Add features such as Product Quick View, Wishlist functionality and a Floating Add to Cart button. Customize the single product gallery/slider and the layout.', 'blocksy-companion'),

			'features' => [
				[
					'id' => 'floating-cart',
					'title' => __('Floating Cart', 'blocksy-companion'),
					'description' => __('Adds the “add to cart” actions to the product page as a floating bar if the product summary has disappeared from view.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_single:has_floating_bar'),
				],

				[
					'id' => 'quick-view',
					'title' => __('Quick View', 'blocksy-companion'),
					'description' => __('Preview the available products and let your users make quick and informative decisions about their purchase.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_quick_view_panel'),
				],

				[
					'id' => 'filters',
					'title' => __('Filters', 'blocksy-companion'),
					'description' => __('Drill down the product list with new filtering widgets, an off canvas area for them and showing the active filters on the page.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_posts_archives:has_woo_offcanvas_filter'),
				],

				[
					'id' => 'wishlist',
					'title' => __('Wishlist', 'blocksy-companion'),
					'description' => __('A set of features that lets you create easily your dream products wishlists and share them with friends and family.', 'blocksy-companion'),

					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_wishlist_panel'),
				],

				[
					'id' => 'compareview',
					'title' => __('Compare View', 'blocksy-companion'),
					'description' => __('Compare products with a clear and concise table system that givis your users a way to make a quick decision.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_compare_panel'),
				],

				[
					'id' => 'single-product-share-box',
					'title' => __('Product Share Box', 'blocksy-companion'),
					'description' => __('Enable social sharing abilities for products available on the site, letting even more users discover your great shop selection.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_single:woo_product_elements'),
				],

				[
					'id' => 'advanced-gallery',
					'title' => __('Advanced Gallery', 'blocksy-companion'),
					'description' => __('Replace the standard product gallery with additional layouts which can showcase the photos as a grid or even a slider.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_single:woo_product_gallery'),
				],

				[
					'id' => 'search-by-sku',
					'title' => __('Search by SKU', 'blocksy-companion'),
					'description' => __('Advanced searching for products by their SKU classification can be useful in cases of vast product catalogues.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
				],

				[
					'id' => 'free-shipping',
					'title' => __('Free Shipping Bar', 'blocksy-companion'),
					'description' => __('Add a visual cue that tells your visitors how much the cart total must be to be able to benefit of free shipping.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_free_shipping_panel'),
				],

				[
					'id' => 'variation-swatches',
					'title' => __('Variation Swatches', 'blocksy-companion'),
					'description' => __('Catch the attention of your clients by showcasing your product variations as colour, image or button swatches.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_variation_swatches_panel'),
				],

				[
					'id' => 'product-brands',
					'title' => __('Product Brands', 'blocksy-companion'),
					'description' => __('Categorise products by brands and show their logo in archive or single page so users could discover more about their makers.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'manage' => admin_url('edit-tags.php?taxonomy=product_brands&post_type=product'),
					'require_refresh' => true,
				],

				[
					'id' => 'product-affiliates',
					'title' => __('Affiliate Product Links', 'blocksy-companion'),
					'description' => __('Better management for affiliate products with a few simple options that strengthen the external integration with these.', 'blocksy-companion'),
					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_product_affiliates_panel'),
				],

				[
					'id' => 'product-custom-tabs',
					'title' => __('Custom Tabs', 'blocksy-companion'),
					'description' => __('Present additional information about your products by adding new custom tabs to the product information section.', 'blocksy-companion'),
					'require_refresh' => true,

					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'manage' => admin_url('edit.php?post_type=ct_product_tab'),
				],

				[
					'id' => 'product-size-guide',
					'title' => __('Size Guide', 'blocksy-companion'),
					'description' => __('Show a size chart guide so that your visitors can pick the right size for them when ordering a product.', 'blocksy-companion'),

					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_size_guide_panel'),
					'require_refresh' => true,
				],

				[
					'id' => 'product-custom-thank-you-page',
					'title' => __('Custom Thank You Pages', 'blocksy-companion'),
					'description' => __('Create a customized order “Thank You” page for your customers, giving them a personalized experience.', 'blocksy-companion'),

					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'manage' => admin_url('edit.php?post_type=ct_thank_you_page'),
					'require_refresh' => true,
				],

				[
					'id' => 'product-advanced-reviews',
					'title' => __('Advanced Reviews', 'blocksy-companion'),
					'description' => __('Enhance your WooCommerce reviews with rich content, images and a thumbs up system that help your shoppers find the perfect product.', 'blocksy-companion'),

					'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
					'customize' => admin_url('customize.php?ct_autofocus=woocommerce_general:has_product_advanced_reviews_panel'),
				]
			],

			'pro' => true,

			'plans' => blc_get_capabilities()->get_features()['shop_extra'],

			'documentation' => 'https://creativethemes.com/blocksy/docs/extensions/woocommerce-extra/',
			// 'video' => 'https://www.youtube.com/watch?v=Je18wF6xfWo',
			'icon' => '<svg with="16" height="16" viewBox="0 0 16 16"><path d="M15 .9H1L0 5.1v2.1h1.1V15h13.8V7.1H16v-2L15 .9zM8.7 2.3h1.9l.4 2.9v.7H8.7V2.3zM5.1 5.1l.4-2.9h1.9v3.5H5.1v-.6zm9.6.7h-2.4V5l-.4-2.7H14l.7 2.9v.6z"/></svg>'
		],

	];

	if (! $ext || ! isset($data[$ext])) {
		return $data;
	}

	return $data[$ext];
}
