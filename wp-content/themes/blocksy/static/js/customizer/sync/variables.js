import { getHeroVariables } from './hero-section'
import { getPostListingVariables } from './template-parts/content-loop'
import { getTypographyVariablesFor } from './variables/typography'
import { getBackgroundVariablesFor } from './variables/background'
import { getWooVariablesFor } from './variables/woocommerce'
import { getFormsVariablesFor } from './variables/forms'
import { getPaginationVariables } from './pagination'
import { getCommentsVariables } from './comments'

import { getSingleContentVariablesFor } from './single/structure'

import { getSingleElementsVariables } from './variables/single/related-posts'

import { updateVariableInStyleTags } from 'customizer-sync-helpers'
import { makeVariablesWithCondition } from './helpers/variables-with-conditions'

import { isFunction } from './builder'

import ctEvents from 'ct-events'

let variablesCache = null

const getAllVariables = () => {
	if (variablesCache) {
		return variablesCache
	}

	let allVariables = {
		result: {
			colorPalette: (value) =>
				Object.keys(value).reduce(
					(acc, key) => [
						...acc,
						{
							variable: value[key].variable
								? value[key].variable
								: `theme-palette-color-${key.replace(
										'color',
										''
								  )}`,
							type: `color:${key}`,
						},
					],
					[]
				),

			background_pattern: [
				{
					variable: 'backgroundPattern',
				},
			],

			...getSingleContentVariablesFor(),

			// Page Hero
			...getHeroVariables(),

			...getPostListingVariables(),
			...getPaginationVariables(),

			...getTypographyVariablesFor(),
			...getBackgroundVariablesFor(),
			...getFormsVariablesFor(),
			...getCommentsVariables(),
			...getWooVariablesFor(),

			// Single
			...getSingleElementsVariables(),

			// Colors
			fontColor: {
				selector: ':root',
				variable: 'theme-text-color',
				type: 'color',
			},

			linkColor: [
				{
					selector: ':root',
					variable: 'theme-link-initial-color',
					type: 'color:default',
				},

				{
					selector: ':root',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
				},
			],

			selectionColor: [
				{
					selector: ':root',
					variable: 'theme-selection-text-color',
					type: 'color:default',
				},

				{
					selector: ':root',
					variable: 'theme-selection-background-color',
					type: 'color:hover',
				},
			],

			border_color: {
				variable: 'theme-border-color',
				type: 'color',
				selector: ':root',
			},

			// Headings
			headingColor: {
				variable: 'theme-headings-color',
				type: 'color',
				selector: ':root',
			},

			heading_1_color: {
				variable: 'theme-heading-1-color',
				type: 'color',
				selector: ':root',
			},

			heading_2_color: {
				variable: 'theme-heading-2-color',
				type: 'color',
				selector: ':root',
			},

			heading_3_color: {
				variable: 'theme-heading-3-color',
				type: 'color',
				selector: ':root',
			},

			heading_4_color: {
				variable: 'theme-heading-4-color',
				type: 'color',
				selector: ':root',
			},

			heading_5_color: {
				variable: 'theme-heading-5-color',
				type: 'color',
				selector: ':root',
			},

			heading_6_color: {
				variable: 'theme-heading-6-color',
				type: 'color',
				selector: ':root',
			},

			// Content spacing
			contentSpacing: [
				{
					selector: ':root',
					variable: 'theme-content-spacing',
					extractValue: (value) =>
						({
							none: '0px',
							compact: '0.8em',
							comfortable: '1.5em',
							spacious: '2em',
						}[value]),
				},

				{
					selector: ':root',
					variable: 'has-theme-content-spacing',
					extractValue: (value) => {
						return value === 'none' ? '0' : '1'
					},
				},
			],

			// Buttons
			buttonMinHeight: {
				selector: ':root',
				variable: 'theme-button-min-height',
				responsive: true,
				unit: 'px',
			},

			buttonHoverEffect: [
				{
					selector: ':root',
					variable: 'theme-button-shadow',
					extractValue: (value) =>
						value === 'yes' ? 'CT_CSS_SKIP_RULE' : 'none',
				},

				{
					selector: ':root',
					variable: 'theme-button-transform',
					extractValue: (value) =>
						value === 'yes' ? 'CT_CSS_SKIP_RULE' : 'none',
				},
			],

			buttonTextColor: [
				{
					selector: ':root',
					variable: 'theme-button-text-initial-color',
					type: 'color:default',
				},

				{
					selector: ':root',
					variable: 'theme-button-text-hover-color',
					type: 'color:hover',
				},
			],

			buttonColor: [
				{
					selector: ':root',
					variable: 'theme-button-background-initial-color',
					type: 'color:default',
				},

				{
					selector: ':root',
					variable: 'theme-button-background-hover-color',
					type: 'color:hover',
				},
			],

			buttonBorder: [
				{
					selector: ':root',
					variable: 'theme-button-border',
					type: 'border',
				},

				{
					selector: ':root',
					variable: 'theme-button-border-hover-color',
					type: 'color:default',
					extractValue: ({ style, secondColor }) => ({
						default: {
							...secondColor,
							...(style === 'none'
								? {
										color: 'CT_CSS_SKIP_RULE',
								  }
								: {}),
						},
					}),
				},
			],

			buttonRadius: {
				selector: ':root',
				type: 'spacing',
				variable: 'theme-button-border-radius',
				responsive: true,
			},

			buttonPadding: {
				selector: ':root',
				type: 'spacing',
				variable: 'theme-button-padding',
				responsive: true,
			},

			// Layout
			maxSiteWidth: {
				selector: ':root',
				variable: 'theme-normal-container-max-width',
				unit: 'px',
			},

			contentAreaSpacing: {
				selector: ':root',
				variable: 'theme-content-vertical-spacing',
				responsive: true,
				unit: '',
			},

			contentEdgeSpacing: {
				selector: ':root',
				variable: 'theme-container-edge-spacing',
				responsive: true,
				unit: 'vw',
				extractValue: (value) => {
					return {
						desktop: 100 - parseFloat(value.desktop) * 2,
						tablet: 100 - parseFloat(value.tablet) * 2,
						mobile: 100 - parseFloat(value.mobile) * 2,
					}
				},
			},

			narrowContainerWidth: {
				selector: ':root',
				variable: 'theme-narrow-container-max-width',
				unit: 'px',
			},

			wideOffset: {
				selector: ':root',
				variable: 'theme-wide-offset',
				unit: 'px',
			},

			// Sidebar
			sidebarWidth: [
				{
					selector: '[data-sidebar]',
					variable: 'sidebar-width',
					unit: '%',
				},
				{
					selector: '[data-sidebar]',
					variable: 'sidebar-width-no-unit',
					unit: '',
				},
			],

			sidebarGap: {
				selector: '[data-sidebar]',
				variable: 'sidebar-gap',
				unit: '',
			},

			sidebarOffset: {
				selector: '[data-sidebar]',
				variable: 'sidebar-offset',
				unit: 'px',
			},

			mobile_sidebar_position: [
				{
					selector: ':root',
					variable: 'sidebar-order',
					responsive: true,
					extractValue: (value) => ({
						desktop: 'CT_CSS_SKIP_RULE',
						tablet: value === 'top' ? '-1' : 'CT_CSS_SKIP_RULE',
						mobile: value === 'top' ? '-1' : 'CT_CSS_SKIP_RULE',
					}),
				},
			],

			sidebarWidgetsTitleColor: {
				selector: '.ct-sidebar .widget-title',
				variable: 'theme-heading-color',
				type: 'color',
				responsive: true,
			},

			sidebarWidgetsFontColor: [
				{
					selector: '.ct-sidebar > *',
					variable: 'theme-text-color',
					type: 'color:default',
					responsive: true,
				},

				{
					selector: '.ct-sidebar',
					variable: 'theme-link-initial-color',
					type: 'color:link_initial',
					responsive: true,
				},

				{
					selector: '.ct-sidebar',
					variable: 'theme-link-hover-color',
					type: 'color:link_hover',
					responsive: true,
				},
			],

			sidebarBackgroundColor: {
				selector: '[data-sidebar] > aside',
				variable: 'sidebar-background-color',
				type: 'color',
				responsive: true,
			},

			sidebarBorder: {
				selector: 'aside[data-type="type-2"]',
				variable: 'theme-border',
				type: 'border',
				responsive: true,
			},

			sidebarDivider: {
				selector: 'aside[data-type="type-3"]',
				variable: 'theme-border',
				type: 'border',
				responsive: true,
			},

			sidebarWidgetsSpacing: {
				selector: '.ct-sidebar',
				variable: 'sidebar-widgets-spacing',
				responsive: true,
				unit: 'px',
			},

			sidebarInnerSpacing: {
				selector: '[data-sidebar] > aside',
				variable: 'sidebar-inner-spacing',
				responsive: true,
				unit: 'px',
			},

			sidebarRadius: {
				selector: 'aside[data-type="type-2"]',
				type: 'spacing',
				variable: 'theme-border-radius',
				responsive: true,
			},

			sidebarShadow: {
				selector: 'aside[data-type="type-2"]',
				type: 'box-shadow',
				variable: 'theme-box-shadow',
				responsive: true,
			},

			// To top button
			topButtonSize: {
				selector: '.ct-back-to-top .ct-icon',
				variable: 'theme-icon-size',
				responsive: true,
				unit: 'px',
			},

			topButtonOffset: {
				selector: '.ct-back-to-top',
				variable: 'back-top-bottom-offset',
				responsive: true,
				unit: 'px',
			},

			sideButtonOffset: {
				selector: '.ct-back-to-top',
				variable: 'back-top-side-offset',
				responsive: true,
				unit: 'px',
			},

			topButtonIconColor: [
				{
					selector: '.ct-back-to-top',
					variable: 'theme-icon-color',
					type: 'color:default',
				},

				{
					selector: '.ct-back-to-top',
					variable: 'theme-icon-hover-color',
					type: 'color:hover',
				},
			],

			topButtonShapeBackground: [
				{
					selector: '.ct-back-to-top',
					variable: 'top-button-background-color',
					type: 'color:default',
				},

				{
					selector: '.ct-back-to-top',
					variable: 'top-button-background-hover-color',
					type: 'color:hover',
				},
			],

			topButtonRadius: {
				selector: '.ct-back-to-top',
				type: 'spacing',
				variable: 'theme-border-radius',
				// responsive: true,
			},

			topButtonShadow: {
				selector: '.ct-back-to-top',
				type: 'box-shadow',
				variable: 'theme-box-shadow',
				responsive: true,
			},

			// Passepartout
			...makeVariablesWithCondition('has_passepartout', {
				passepartoutSize: {
					selector: ':root',
					variable: 'theme-frame-size',
					responsive: true,
					unit: 'px',
				},

				passepartoutColor: {
					selector: ':root',
					variable: 'theme-frame-color',
					type: 'color',
				},
			}),

			// Breadcrumbs
			breadcrumbsFontColor: [
				{
					selector: '.ct-breadcrumbs',
					variable: 'theme-text-color',
					type: 'color:default',
				},

				{
					selector: '.ct-breadcrumbs',
					variable: 'theme-link-initial-color',
					type: 'color:initial',
				},

				{
					selector: '.ct-breadcrumbs',
					variable: 'theme-link-hover-color',
					type: 'color:hover',
				},
			],
		},
	}

	ctEvents.trigger(
		'ct:customizer:sync:collect-variable-descriptors',
		allVariables
	)

	variablesCache = allVariables.result

	return variablesCache
}

wp.customize.bind('change', (e) => {
	let allVariables = getAllVariables()

	if (!allVariables[e.id]) {
		return
	}

	updateVariableInStyleTags({
		variableDescriptor: allVariables[e.id],
		value: e(),
	})
})
