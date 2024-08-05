import { handleBackgroundOptionFor } from 'blocksy-customizer-sync'
import ctEvents from 'ct-events'

ctEvents.on(
	'ct:customizer:sync:collect-variable-descriptors',
	(allVariables) => {
		allVariables.result = {
			...allVariables.result,
			newsletter_subscribe_title_color: {
				selector: '.ct-newsletter-subscribe-container',
				variable: 'theme-heading-color',
				type: 'color:default',
				responsive: true,
			},

			newsletter_subscribe_content: [
				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'text-color',
					type: 'color:default',
				},

				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
				},
			],

			newsletter_subscribe_button: [
				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-button-background-initial-color',
					type: 'color:default',
				},

				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-button-background-hover-color',
					type: 'color:hover',
				},
			],

			newsletter_subscribe_input_font_color: [
				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-form-text-initial-color',
					type: 'color:default',
				},

				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-form-text-focus-color',
					type: 'color:focus',
				},
			],

			newsletter_subscribe_border_color: [
				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-form-field-border-initial-color',
					type: 'color:default',
				},

				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-form-field-border-focus-color',
					type: 'color:focus',
				},
			],

			newsletter_subscribe_input_background: [
				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-form-field-background-initial-color',
					type: 'color:default',
				},

				{
					selector: '.ct-newsletter-subscribe-container',
					variable: 'theme-form-field-background-focus-color',
					type: 'color:focus',
				},
			],

			...handleBackgroundOptionFor({
				id: 'newsletter_subscribe_container_background',
				selector: '.ct-newsletter-subscribe-container',
				responsive: true,
			}),

			newsletter_subscribe_container_border: {
				selector: '.ct-newsletter-subscribe-container',
				variable: 'newsletter-container-border',
				type: 'border',
				responsive: true,
				skip_none: true,
			},

			newsletter_subscribe_shadow: {
				selector: '.ct-newsletter-subscribe-container',
				type: 'box-shadow',
				variable: 'theme-box-shadow',
				responsive: true,
			},

			newsletter_subscribe_container_spacing: {
				selector: '.ct-newsletter-subscribe-container',
				type: 'spacing',
				variable: 'padding',
				responsive: true,
			},

			newsletter_subscribe_container_border_radius: {
				selector: '.ct-newsletter-subscribe-container',
				type: 'spacing',
				variable: 'theme-border-radius',
				responsive: true,
			},
		}
	}
)
