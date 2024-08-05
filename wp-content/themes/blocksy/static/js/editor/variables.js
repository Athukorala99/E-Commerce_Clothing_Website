import { handleBackgroundOptionFor } from '../customizer/sync/variables/background'
import { withKeys } from '../customizer/sync/helpers'
import { maybePromoteScalarValueIntoResponsive } from 'customizer-sync-helpers/dist/promote-into-responsive'

const isContentBlock = document.body.classList.contains(
	'post-type-ct_content_block'
)

export const gutenbergVariables = {
	background: ['desktop', 'tablet', 'mobile'].reduce(
		(result, breakpoint) => [
			...result,
			...handleBackgroundOptionFor({
				id: 'background',
				selector: `.edit-post-visual-editor__content-area > .is-${breakpoint}-preview`,
				responsive: false,
				addToDescriptors: {
					fullValue: true,
					important: true,
				},
				valueExtractor: ({ background }) => {
					let valueToUse = background

					if (
						!background.desktop &&
						!isContentBlock &&
						background.background_type === 'color' &&
						background.backgroundColor.default.color &&
						background.backgroundColor.default.color.indexOf(
							'CT_CSS_SKIP_RULE'
						) > -1
					) {
						valueToUse = ct_editor_localizations.default_background
					}

					return maybePromoteScalarValueIntoResponsive(valueToUse)[
						breakpoint
					]
				},
			}).background,
		],
		[]
	),

	...handleBackgroundOptionFor({
		id: 'popup_background',
		selector: '.edit-post-visual-editor__content-area > div',
		responsive: true,
		addToDescriptors: {
			important: true,
		},
	}),

	...withKeys(
		[
			'content_style_source',
			'content_style',
			'content_background',
			'content_boxed_shadow',
			'boxed_content_spacing',
			'content_boxed_radius',
			'content_boxed_border',
			'page_structure_type',

			...(isContentBlock
				? [
						'has_content_block_structure',
						'content_block_structure',
						'template_subtype',
						'template_editor_width_source',
						'template_editor_width',
				  ]
				: []),
		],
		[
			{
				selector: `:root`,
				variable: 'theme-block-max-width',
				extractValue: ({
					template_subtype,
					template_editor_width_source = 'small',
					template_editor_width = 1290,

					has_content_block_structure,
					content_block_structure,

					page_structure_type,
				}) => {
					if (template_subtype && template_subtype === 'card') {
						if (template_editor_width_source === 'small') {
							return '500px'
						}

						if (template_editor_width_source === 'medium') {
							return '900px'
						}

						return `${template_editor_width}px`
					}

					if (page_structure_type === 'default') {
						page_structure_type =
							ct_editor_localizations.default_page_structure
					}

					if (isContentBlock) {
						page_structure_type = content_block_structure

						if (
							(has_content_block_structure !== 'yes' ||
								template_subtype === 'card' ||
								template_subtype === 'content') &&
							template_subtype !== 'canvas'
						) {
							page_structure_type = 'type-4'
						}
					}

					if (page_structure_type === 'type-4') {
						return 'var(--theme-normal-container-max-width)'
					}

					return 'var(--theme-narrow-container-max-width)'
				},
				fullValue: true,
				unit: '',
			},

			{
				selector: `:root`,
				variable: 'theme-block-wide-max-width',
				extractValue: ({
					template_subtype,

					has_content_block_structure,
					content_block_structure,

					page_structure_type,
				}) => {
					if (template_subtype && template_subtype === 'card') {
						return 'CT_CSS_SKIP_RULE'
					}

					if (isContentBlock) {
						page_structure_type = content_block_structure

						if (has_content_block_structure !== 'yes') {
							page_structure_type = 'type-4'
						}
					}

					if (page_structure_type === 'default') {
						page_structure_type =
							ct_editor_localizations.default_page_structure
					}

					if (page_structure_type === 'type-4') {
						return 'calc(var(--theme-normal-container-max-width) + var(--theme-wide-offset) * 2)'
					}

					return 'calc(var(--theme-narrow-container-max-width) + var(--theme-wide-offset) * 2)'
				},
				fullValue: true,
				unit: '',
			},

			{
				selector: `:root`,
				variable: 'has-boxed',
				responsive: true,
				extractValue: ({
					template_subtype,
					content_style_source = 'inherit',
					has_content_block_structure = 'yes',
					content_style = 'wide',
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						content_style =
							ct_editor_localizations.default_content_style
					}

					content_style =
						maybePromoteScalarValueIntoResponsive(content_style)

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						content_style = {
							desktop: 'wide',
							tablet: 'wide',
							mobile: 'wide',
						}
					}

					return {
						desktop:
							content_style.desktop === 'boxed'
								? 'var(--true)'
								: 'var(--false)',

						tablet:
							content_style.tablet === 'boxed'
								? 'var(--true)'
								: 'var(--false)',

						mobile:
							content_style.mobile === 'boxed'
								? 'var(--true)'
								: 'var(--false)',
					}
				},
				fullValue: true,
				unit: '',
			},

			{
				selector: `:root`,
				variable: 'has-wide',
				responsive: true,
				extractValue: ({
					template_subtype,
					has_content_block_structure = 'yes',
					content_style_source = 'inherit',
					content_style = 'wide',
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						content_style =
							ct_editor_localizations.default_content_style
					}

					content_style =
						maybePromoteScalarValueIntoResponsive(content_style)

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						content_style = {
							desktop: 'wide',
							tablet: 'wide',
							mobile: 'wide',
						}
					}

					return {
						desktop:
							content_style.desktop === 'wide'
								? 'var(--true)'
								: 'var(--false)',

						tablet:
							content_style.tablet === 'wide'
								? 'var(--true)'
								: 'var(--false)',

						mobile:
							content_style.mobile === 'wide'
								? 'var(--true)'
								: 'var(--false)',
					}
				},
				fullValue: true,
				unit: '',
			},

			...handleBackgroundOptionFor({
				id: 'background',
				selector: ':root',
				responsive: true,
				conditional_var: '--has-boxed',
				addToDescriptors: {
					fullValue: true,
				},
				valueExtractor: ({
					template_subtype,
					has_content_block_structure = 'yes',
					content_style_source = 'inherit',
					content_background,
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						content_background =
							ct_editor_localizations.default_content_background
					}

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						content_background = JSON.parse(
							JSON.stringify(
								maybePromoteScalarValueIntoResponsive(
									content_background
								)
							)
						)

						content_background.desktop.background_type = 'color'
						content_background.desktop.backgroundColor.default.color =
							'transparent'

						content_background.tablet.background_type = 'color'
						content_background.tablet.backgroundColor.default.color =
							'transparent'

						content_background.mobile.background_type = 'color'
						content_background.mobile.backgroundColor.default.color =
							'transparent'
					}

					return content_background
				},
			}).background,

			{
				selector: ':root',
				type: 'spacing',
				variable: 'theme-boxed-content-spacing',
				responsive: true,
				unit: '',
				fullValue: true,
				extractValue: ({
					template_subtype,
					content_style_source = 'inherit',
					boxed_content_spacing,
					has_content_block_structure = 'yes',
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						boxed_content_spacing =
							ct_editor_localizations.default_boxed_content_spacing
					}

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						return 'CT_CSS_SKIP_RULE'
					}

					return boxed_content_spacing
				},
			},

			{
				selector: ':root',
				type: 'spacing',
				variable: 'theme-boxed-content-border-radius',
				responsive: true,

				fullValue: true,
				extractValue: ({
					template_subtype,
					content_style_source = 'inherit',
					content_boxed_radius,
					has_content_block_structure = 'yes',
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						content_boxed_radius =
							ct_editor_localizations.default_content_boxed_radius
					}

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						return 'CT_CSS_SKIP_RULE'
					}

					return content_boxed_radius
				},
			},

			{
				selector: ':root',
				type: 'border',
				variable: 'theme-boxed-content-border',
				responsive: true,

				fullValue: true,
				extractValue: ({
					template_subtype,
					content_style_source = 'inherit',
					content_boxed_border,
					has_content_block_structure = 'yes',
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						content_boxed_border =
							ct_editor_localizations.default_content_boxed_border
					}

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						content_boxed_border = null
					}

					return content_boxed_border
				},
			},

			{
				selector: ':root',
				type: 'box-shadow',
				variable: 'theme-boxed-content-box-shadow',
				responsive: true,
				fullValue: true,
				extractValue: ({
					template_subtype,
					content_style_source = 'inherit',
					content_boxed_shadow,
					has_content_block_structure = 'yes',
				}) => {
					if (!isContentBlock && content_style_source === 'inherit') {
						content_boxed_shadow =
							ct_editor_localizations.default_content_boxed_shadow
					}

					if (
						isContentBlock &&
						(has_content_block_structure !== 'yes' ||
							template_subtype === 'card' ||
							template_subtype === 'content')
					) {
						return 'CT_CSS_SKIP_RULE'
					}

					return content_boxed_shadow
				},
			},
		]
	),
}
