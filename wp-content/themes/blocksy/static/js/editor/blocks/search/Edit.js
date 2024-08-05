import { createElement, useRef } from '@wordpress/element'
import { __ } from 'ct-i18n'
import {
	InspectorControls,
	BlockControls,
	useBlockProps,
	withColors,
} from '@wordpress/block-editor'
import Preview from './Preview'
import { options } from '.'
import { colors } from './colors'
import BasicEdit from '../../components/BasicEdit'
import ColorsPanel from '../../components/ColorsPanel'

import { ToolbarGroup, ToolbarButton } from '@wordpress/components'

import { buttonOutside, buttonWithIcon } from './icons'

const Edit = ({
	attributes,
	setAttributes,
	clientId,
	className,

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

	dropdownTextInitialColor,
	setDropdownTextInitialColor,
	dropdownTextHoverColor,
	setDropdownTextHoverColor,

	dropdownBackgroundColor,
	setDropdownBackgroundColor,

	shadowColor,
	setShadowColor,
}) => {
	const {
		buttonUseText = 'no',
		buttonPosition,
		enable_live_results,
	} = attributes

	const radius = attributes?.style?.border?.radius

	const navRef = useRef()

	const blockProps = useBlockProps({
		ref: navRef,
		className: {
			'ct-search-box': true,
			className,
		},
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

			...(radius
				? {
						'--theme-form-field-border-radius': `${
							typeof radius === 'string'
								? radius
								: `${radius.topLeft} ${radius.topRight} ${radius.bottomLeft} ${radius.bottomRight}`
						}`,
				  }
				: {}),

			...(attributes?.searchBoxHeight
				? {
						'--theme-form-field-height': `${attributes.searchBoxHeight}px`,
				  }
				: {}),
			...(enable_live_results === 'yes'
				? {
						'--theme-link-initial-color':
							dropdownTextInitialColor?.color,
						'--theme-link-hover-color':
							dropdownTextHoverColor?.color,
						'--search-dropdown-background':
							dropdownBackgroundColor?.color,
						'--search-dropdown-box-shadow-color':
							shadowColor?.color,
				  }
				: {}),
		},
	})

	return (
		<div {...blockProps}>
			<Preview
				blockProps={blockProps}
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

					...(buttonPosition === 'outside'
						? {
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
						  }
						: {}),
				}}
			/>
			<BasicEdit
				attributes={attributes}
				setAttributes={setAttributes}
				options={options}
			/>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						title={__('Button Outside')}
						icon={buttonOutside}
						onClick={() => {
							setAttributes({
								buttonPosition:
									buttonPosition === 'outside'
										? 'inside'
										: 'outside',
							})
						}}
						className={
							buttonPosition === 'outside'
								? 'is-pressed'
								: undefined
						}
					/>
					<ToolbarButton
						title={__('Use button with text')}
						icon={buttonWithIcon}
						onClick={() => {
							setAttributes({
								buttonUseText:
									buttonUseText === 'no' ? 'yes' : 'no',
							})
						}}
						className={
							buttonUseText === 'yes' ? 'is-pressed' : undefined
						}
					/>
				</ToolbarGroup>
			</BlockControls>
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
							onColorChange: (value) =>
								setInputFontColor(
									value || colors.inputFontColor
								),
						},
						{
							colorValue: inputFontColorFocus.color,
							label: __('Focus', 'blocksy'),
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
							onColorChange: (value) =>
								setInputBorderColor(
									value || colors.inputBorderColor
								),
						},
						{
							colorValue: inputBorderColorFocus.color,
							label: __('Focus', 'blocksy'),
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
							onColorChange: (value) =>
								setInputBackgroundColor(
									value || colors.inputBackgroundColor
								),
						},
						{
							colorValue: inputBackgroundColorFocus.color,
							label: __('Focus', 'blocksy'),
							onColorChange: (value) =>
								setInputBackgroundColorFocus(
									value || colors.inputBackgroundColorFocus
								),
						},
					]}
				/>

				<ColorsPanel
					label={
						buttonUseText === 'yes'
							? __('Button Text Color', 'blocksy')
							: __('Button Icon Color', 'blocksy')
					}
					resetAll={() => {
						setInputIconColor(colors.inputIconColor)
						setInputIconColorFocus(colors.inputIconColorFocus)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: inputIconColor.color,
							label: __('Initial', 'blocksy'),
							onColorChange: (value) =>
								setInputIconColor(
									value || colors.inputIconColor
								),
						},
						{
							colorValue: inputIconColorFocus.color,
							label: __('Hover', 'blocksy'),
							onColorChange: (value) =>
								setInputIconColorFocus(
									value || colors.inputIconColorFocus
								),
						},
					]}
				/>

				{buttonPosition === 'outside' ? (
					<ColorsPanel
						label={__('Button Background Color', 'blocksy')}
						resetAll={() => {
							setButtonBackgroundColor(
								colors.buttonBackgroundColor
							)
							setButtonBackgroundColorHover(
								colors.buttonBackgroundColorHover
							)
						}}
						panelId={clientId}
						settings={[
							{
								colorValue: buttonBackgroundColor.color,
								label: __('Initial', 'blocksy'),
								onColorChange: (value) =>
									setButtonBackgroundColor(
										value || colors.buttonBackgroundColor
									),
							},
							{
								colorValue: buttonBackgroundColorHover.color,
								label: __('Hover', 'blocksy'),
								onColorChange: (value) =>
									setButtonBackgroundColorHover(
										value ||
											colors.buttonBackgroundColorHover
									),
							},
						]}
					/>
				) : null}

				{enable_live_results === 'yes' ? (
					<>
						<ColorsPanel
							label={__('Dropdown Text Color', 'blocksy')}
							resetAll={() => {
								setDropdownTextInitialColor(
									colors.dropdownTextInitialColor
								)
								setDropdownTextHoverColor(
									colors.dropdownTextHoverColor
								)
							}}
							panelId={clientId}
							settings={[
								{
									colorValue: dropdownTextInitialColor.color,
									label: __('Initial', 'blocksy'),
									onColorChange: (value) =>
										setDropdownTextInitialColor(
											value ||
												colors.dropdownTextInitialColor
										),
								},
								{
									colorValue: dropdownTextHoverColor.color,
									label: __('Hover', 'blocksy'),
									onColorChange: (value) =>
										setDropdownTextHoverColor(
											value ||
												colors.dropdownTextHoverColor
										),
								},
							]}
						/>

						<ColorsPanel
							label={__('Dropdown Background Color', 'blocksy')}
							resetAll={() => {
								setDropdownBackgroundColor(
									colors.dropdownBackgroundColor
								)
							}}
							panelId={clientId}
							settings={[
								{
									colorValue: dropdownBackgroundColor.color,
									label: __('Initial', 'blocksy'),
									onColorChange: (value) =>
										setDropdownBackgroundColor(
											value ||
												colors.dropdownBackgroundColor
										),
								},
							]}
						/>

						<ColorsPanel
							label={__('Dropdown Shadow Color', 'blocksy')}
							resetAll={() => {
								setShadowColor(colors.shadowColor)
							}}
							panelId={clientId}
							settings={[
								{
									colorValue: shadowColor.color,
									enableAlpha: true,
									label: __('Initial', 'blocksy'),
									onColorChange: (value) =>
										setShadowColor(
											value || colors.shadowColor
										),
								},
							]}
						/>
					</>
				) : null}
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
	{ buttonBackgroundColorHover: 'color' },
	{ dropdownTextInitialColor: 'color' },
	{ dropdownTextHoverColor: 'color' },
	{ dropdownBackgroundColor: 'color' },
	{ shadowColor: 'color' }
)(Edit)
