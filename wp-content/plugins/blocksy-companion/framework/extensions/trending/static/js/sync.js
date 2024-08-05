import {
	handleBackgroundOptionFor,
	responsiveClassesFor,
	typographyOption,
} from 'blocksy-customizer-sync'
import ctEvents from 'ct-events'

ctEvents.on(
	'ct:customizer:sync:collect-variable-descriptors',
	(allVariables) => {
		allVariables.result = {

			...typographyOption({
				id: 'trendingBlockHeadingFont',
				selector: '.ct-trending-block .ct-block-title',
			}),

			trendingBlockHeadingFontColor: {
				selector: '.ct-trending-block .ct-block-title',
				variable: 'theme-heading-color',
				type: 'color',
				responsive: true,
			},

			...typographyOption({
				id: 'trendingBlockPostsFont',
				selector: '.ct-trending-block .ct-post-title',
			}),

			trendingBlockFontColor: [
				{
					selector: '.ct-trending-block a',
					variable: 'theme-text-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block a',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			trendingBlockArrowsColor: [
				{
					selector: '.ct-trending-block [class*="ct-arrow"]',
					variable: 'theme-text-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-trending-block [class*="ct-arrow"]',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
					responsive: true,
				},
			],

			...handleBackgroundOptionFor({
				id: 'trending_block_background',
				selector: '.ct-trending-block',
				responsive: true,
			}),

			...allVariables.result,
			trendingBlockContainerSpacing: {
				selector: '.ct-trending-block',
				variable: 'padding',
				responsive: true,
				unit: '',
			},
		}
	}
)

wp.customize('trending_block_visibility', (value) =>
	value.bind((to) =>
		responsiveClassesFor(
			'trending_block_visibility',
			document.querySelector('.ct-trending-block')
		)
	)
)

wp.customize('trending_block_label', (value) =>
	value.bind((to) => {
		const title = document.querySelector(
			'.ct-trending-block .ct-block-title'
		)

		if (title) {
			const components = title.innerHTML.split('<svg')
			components[0] = to
			title.innerHTML = components.join('<svg')
		}
	})
)
