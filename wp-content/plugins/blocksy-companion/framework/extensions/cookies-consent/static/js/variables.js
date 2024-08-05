import ctEvents from 'ct-events'

ctEvents.on(
	'ct:customizer:sync:collect-variable-descriptors',
	(allVariables) => {
		allVariables.result = {
			...allVariables.result,
			cookieContentColor: [
				{
					selector: '.cookie-notification',
					variable: 'theme-text-color',
					type: 'color:default',
				},

				{
					selector: '.cookie-notification',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
				},
			],

			cookieBackground: {
				selector: '.cookie-notification',
				variable: 'backgroundColor',
				type: 'color',
			},

			cookieButtonText: [
				{
					selector: '.cookie-notification .ct-cookies-accept-button',
					variable: 'theme-button-text-initial-color',
					type: 'color:default',
				},

				{
					selector: '.cookie-notification .ct-cookies-accept-button',
					variable: 'theme-button-text-hover-color',
					type: 'color:hover',
				},
			],

			cookieButtonBackground: [
				{
					selector: '.cookie-notification .ct-cookies-accept-button',
					variable: 'theme-button-background-initial-color',
					type: 'color:default',
				},

				{
					selector: '.cookie-notification .ct-cookies-accept-button',
					variable: 'theme-button-background-hover-color',
					type: 'color:hover',
				},
			],

			cookieDeclineButtonText: [
				{
					selector: '.cookie-notification .ct-cookies-decline-button',
					variable: 'theme-button-text-initial-color',
					type: 'color:default',
				},

				{
					selector: '.cookie-notification .ct-cookies-decline-button',
					variable: 'theme-button-text-hover-color',
					type: 'color:hover',
				},
			],

			cookieDeclineButtonBackground: [
				{
					selector: '.cookie-notification .ct-cookies-decline-button',
					variable: 'theme-button-background-initial-color',
					type: 'color:default',
				},

				{
					selector: '.cookie-notification .ct-cookies-decline-button',
					variable: 'theme-button-background-hover-color',
					type: 'color:hover',
				},
			],

			cookieMaxWidth: {
				selector: '.cookie-notification',
				variable: 'maxWidth',
				unit: 'px',
			},
		}
	}
)
