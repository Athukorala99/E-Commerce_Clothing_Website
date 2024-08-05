import WebFontLoader from 'webfontloader'

const withPrefix = (value, prefix = '') => {
	if (prefix.trim() === '') {
		return value
	}

	return value.replace('theme-', `theme-${prefix}-`)
}

const getWeightFor = ({ variation }) => {
	if (variation === 'Default') {
		return 'CT_CSS_SKIP_RULE'
	}

	return parseInt(variation[1], 10) * 100
}
const getStyleFor = ({ variation }) => {
	if (variation === 'Default') {
		return 'CT_CSS_SKIP_RULE'
	}

	return variation[0] === 'i' ? 'italic' : 'normal'
}

let loadedFonts = {}

const systemFonts = [
	'System Default',
	'Arial',
	'Verdana',
	'Trebuchet',
	'Georgia',
	'Times New Roman',
	'Palatino',
	'Helvetica',
	'Myriad Pro',
	'Lucida',
	'Gill Sans',
	'Impact',
	'Serif',
	'monospace',
]

const loadGoogleFonts = (font_family, variation) => {
	if (systemFonts.indexOf(font_family) > -1) {
		return
	}

	if (font_family.indexOf('ct_font_') === 0) {
		return
	}

	if (font_family.indexOf('ct_typekit_') === 0) {
		return
	}

	if (font_family === 'CT_CSS_SKIP_RULE') {
		return
	}

	if (font_family === 'Default') {
		return
	}

	if (font_family.indexOf('apple-system') > -1) {
		return
	}

	if (loadedFonts[font_family]) {
		if (loadedFonts[font_family].indexOf(variation) > -1) return
		loadedFonts[font_family] = [...loadedFonts[font_family], variation]
	} else {
		loadedFonts[font_family] = [variation]
	}

	WebFontLoader.load({
		google: {
			api: 'https://fonts.googleapis.com/css2',
			families: [font_family],
		},
		classes: false,
		text: 'abcdefghijklmnopqrstuvwxyz',
	})
}

export const typographyOption = ({
	id,
	selector,
	prefix = '',
	extractValue = (v) => v,
}) => ({
	[id]: [
		{
			variable: withPrefix('theme-font-family', prefix),
			selector,
			extractValue: (value) => {
				value = extractValue(value)

				if (value.family === 'Default') {
					return 'CT_CSS_SKIP_RULE'
				}

				if (value.family === 'System Default') {
					return "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'"
				}

				if (systemFonts.indexOf(value.family) > -1) {
					return value.family
				}

				if (value.family.indexOf(' ') > -1) {
					return `'${value.family}'`.replace('ct_typekit_', '')
				}

				return value.family.replace('ct_typekit_', '')
			},
			whenDone: (extractedValue, value) => {
				if (!extractedValue) {
					return
				}

				let { variation } = extractValue(value)

				loadGoogleFonts(extractedValue, variation)
			},
		},

		{
			variable: withPrefix('theme-font-weight', prefix),
			selector,
			extractValue: (value) => {
				value = extractValue(value)

				return getWeightFor(value)
			},
			whenDone: (extractedValue, value) => {
				let { family, variation } = extractValue(value)

				loadGoogleFonts(family, variation)
			},
		},

		{
			variable: withPrefix('theme-font-style', prefix),
			selector,
			extractValue: (value) => {
				value = extractValue(value)

				return getStyleFor(value)
			},

			whenDone: (extractedValue, value) => {
				let { family, variation } = extractValue(value)

				loadGoogleFonts(family, variation)
			},
		},

		{
			variable: withPrefix('theme-text-transform', prefix),
			selector,
			extractValue: (value) => {
				value = extractValue(value)
				return value['text-transform']
			},
		},

		{
			variable: withPrefix('theme-text-decoration', prefix),
			selector,
			extractValue: (value) => {
				value = extractValue(value)
				return value['text-decoration']
			},
		},

		{
			variable: withPrefix('theme-font-size', prefix),
			selector,
			unit: '',
			responsive: true,
			extractValue: (value) => {
				value = extractValue(value)
				return value.size
			},
		},

		{
			variable: withPrefix('theme-line-height', prefix),
			selector,
			unit: '',
			responsive: true,
			extractValue: (value) => {
				value = extractValue(value)
				return value['line-height']
			},
		},

		{
			variable: withPrefix('theme-letter-spacing', prefix),
			selector,
			unit: '',
			responsive: true,
			extractValue: (value) => {
				value = extractValue(value)
				return value['letter-spacing']
			},
		},
	],
})

export const getTypographyVariablesFor = () => ({
	...typographyOption({
		id: 'rootTypography',
		selector: ':root',
	}),

	...typographyOption({
		id: 'h1Typography',
		selector: 'h1',
	}),

	...typographyOption({
		id: 'h2Typography',
		selector: 'h2',
	}),

	...typographyOption({
		id: 'h3Typography',
		selector: 'h3',
	}),

	...typographyOption({
		id: 'h4Typography',
		selector: 'h4',
	}),

	...typographyOption({
		id: 'h5Typography',
		selector: 'h5',
	}),

	...typographyOption({
		id: 'h6Typography',
		selector: 'h6',
	}),

	...typographyOption({
		id: 'buttons',
		selector: ':root',
		prefix: 'button',
	}),

	...typographyOption({
		id: 'quote',
		selector: '.wp-block-quote',
	}),

	...typographyOption({
		id: 'pullquote',
		selector: '.wp-block-pullquote',
	}),

	...typographyOption({
		id: 'pre',
		selector: 'code, kbd, samp, pre',
	}),

	...typographyOption({
		id: 'figcaption',
		selector: '.entry-content figcaption',
	}),

	...typographyOption({
		id: 'sidebarWidgetsTitleFont',
		selector: '.ct-sidebar .widget-title',
	}),

	...typographyOption({
		id: 'sidebarWidgetsFont',
		selector: '.ct-sidebar .ct-widget > *:not(.widget-title)',
	}),

	...typographyOption({
		id: 'quickViewProductTitleFont',
		selector: '.ct-quick-view-card .product_title',
	}),

	...typographyOption({
		id: 'quickViewProductPriceFont',
		selector: '.ct-quick-view-card .entry-summary .price',
	}),

	...typographyOption({
		id: 'breadcrumbsFont',
		selector: '.ct-breadcrumbs',
	}),
})
