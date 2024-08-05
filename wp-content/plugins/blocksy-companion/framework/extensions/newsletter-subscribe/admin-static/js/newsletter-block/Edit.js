import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'
import { colors } from './colors'

import {
	InspectorControls,
	useBlockProps,
	withColors,
} from '@wordpress/block-editor'
import { PanelBody } from '@wordpress/components'

import { OptionsPanel, ColorsPanel } from 'blocksy-options'
import Preview from './Preview'
import { options } from '.'

const Edit = ({
	attributes,
	setAttributes,
	clientId,

	inputFontColor,
	setInputFontColor,
	inputFontColorFocus,
	setInputFontColorFocus,
	inputIconColor,
	setInputIconColor,
	inputIconColorFocus,
	setInputIconColorFocus,
	inputBorderColor,
	setInputBorderColor,
	inputBorderColorFocus,
	setInputBorderColorFocus,
	inputBackgroundColor,
	setInputBackgroundColor,
	inputBackgroundColorFocus,
	setInputBackgroundColorFocus,

	buttonBackgroundColor,
	setButtonBackgroundColor,
	buttonBackgroundColorHover,
	setButtonBackgroundColorHover,
}) => {
	const radius = attributes?.style?.border?.radius

	const blockProps = useBlockProps({
		style: {
			'--theme-form-text-initial-color': inputFontColor?.color,
			'--theme-form-text-focus-color': inputFontColorFocus?.color,

			'--theme-form-field-border-initial-color': inputBorderColor?.color,
			'--theme-form-field-border-focus-color':
				inputBorderColorFocus?.color,

			'--theme-form-field-background-initial-color':
				inputBackgroundColor?.color,
			'--theme-form-field-background-focus-color':
				inputBackgroundColorFocus?.color,
			...(attributes?.newsletter_subscribe_height
				? {
						'--theme-form-field-height': `${attributes.newsletter_subscribe_height}px`,
				  }
				: {}),
			...(radius
				? {
						'--theme-form-field-border-radius': `${
							typeof radius === 'string'
								? radius
								: `${radius.topLeft} ${radius.topRight} ${radius.bottomLeft} ${radius.bottomRight}`
						}`,
				  }
				: {}),
			...(attributes?.newsletter_subscribe_gap
				? {
						'--theme-form-field-gap': `${attributes.newsletter_subscribe_gap}px`,
				  }
				: {}),
		},
	})

	return (
		<div {...blockProps}>
			<Preview
				attributes={attributes}
				setAttributes={setAttributes}
				buttonStyles={{
					...(inputIconColor?.color
						? {
								'--theme-button-text-initial-color':
									inputIconColor.color,
						  }
						: {}),
					...(inputIconColorFocus?.color
						? {
								'--theme-button-text-hover-color':
									inputIconColorFocus.color,
						  }
						: {}),
					...(buttonBackgroundColor?.color
						? {
								'--theme-button-background-initial-color':
									buttonBackgroundColor.color,
						  }
						: {}),
					...(buttonBackgroundColorHover?.color
						? {
								'--theme-button-background-hover-color':
									buttonBackgroundColorHover.color,
						  }
						: {}),
				}}
			/>
			<InspectorControls>
				<PanelBody>
					<OptionsPanel
						purpose={'gutenberg'}
						onChange={(optionId, optionValue) => {
							setAttributes({
								[optionId]: optionValue,
							})
						}}
						options={options}
						value={attributes}
						hasRevertButton={false}
					/>
				</PanelBody>
			</InspectorControls>

			<InspectorControls group="styles">
				<ColorsPanel
					label={__('Input Font Color', 'blocksy')}
					resetAll={() => {
						setInputFontColor(colors.inputFontColor)
						setInputFontColorFocus(colors.inputFontColorFocus)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: inputFontColor.color,
							label: __('Initial', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputFontColor(
									value || colors.inputFontColor
								),
						},
						{
							colorValue: inputFontColorFocus.color,
							label: __('Focus', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputFontColorFocus(
									value || colors.inputFontColorFocus
								),
						},
					]}
				/>

				<ColorsPanel
					label={__('Input Border Color', 'blocksy')}
					resetAll={() => {
						setInputBorderColor(colors.inputBorderColor)
						setInputBorderColorFocus(colors.inputBorderColorFocus)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: inputBorderColor.color,
							label: __('Initial', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputBorderColor(
									value || colors.inputBorderColor
								),
						},
						{
							colorValue: inputBorderColorFocus.color,
							label: __('Focus', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputBorderColorFocus(
									value || colors.inputBorderColorFocus
								),
						},
					]}
				/>

				<ColorsPanel
					label={__('Input Background Color', 'blocksy')}
					resetAll={() => {
						setInputBackgroundColor(colors.inputBackgroundColor)
						setInputBackgroundColorFocus(
							colors.inputBackgroundColorFocus
						)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: inputBackgroundColor.color,
							label: __('Initial', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputBackgroundColor(
									value || colors.inputBackgroundColor
								),
						},
						{
							colorValue: inputBackgroundColorFocus.color,
							label: __('Focus', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputBackgroundColorFocus(
									value || colors.inputBackgroundColorFocus
								),
						},
					]}
				/>

				<ColorsPanel
					label={__('Button Text Color', 'blocksy')}
					resetAll={() => {
						setInputIconColor(colors.inputIconColor)
						setInputIconColorFocus(colors.inputIconColorFocus)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: inputIconColor.color,
							label: __('Initial', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputIconColor(
									value || colors.inputIconColor
								),
						},
						{
							colorValue: inputIconColorFocus.color,
							label: __('Hover', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setInputIconColorFocus(
									value || colors.inputIconColorFocus
								),
						},
					]}
				/>

				<ColorsPanel
					label={__('Button Background Color', 'blocksy')}
					resetAll={() => {
						setButtonBackgroundColor(colors.buttonBackgroundColor)
						setButtonBackgroundColorHover(
							colors.buttonBackgroundColorHover
						)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: buttonBackgroundColor.color,
							label: __('Initial', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setButtonBackgroundColor(
									value || colors.buttonBackgroundColor
								),
						},
						{
							colorValue: buttonBackgroundColorHover.color,
							label: __('Hover', 'blocksy'),
							enableAlpha: true,
							onColorChange: (value) =>
								setButtonBackgroundColorHover(
									value || colors.buttonBackgroundColorHover
								),
						},
					]}
				/>
			</InspectorControls>
		</div>
	)
}

export default withColors(
	{ textColor: 'color' },
	{ inputFontColor: 'color' },
	{ inputFontColorFocus: 'color' },
	{ inputIconColor: 'color' },
	{ inputIconColorFocus: 'color' },
	{ inputBorderColor: 'color' },
	{ inputBorderColorFocus: 'color' },
	{ inputBackgroundColor: 'color' },
	{ inputBackgroundColorFocus: 'color' },
	{ buttonBackgroundColor: 'color' },
	{ buttonBackgroundColorHover: 'color' }
)(Edit)
