import { createElement, useRef } from '@wordpress/element'
import { __ } from 'ct-i18n'
import {
	InspectorControls,
	useBlockProps,
	withColors,
} from '@wordpress/block-editor'
import Preview from './Preview'
import ColorsPanel from '../../components/ColorsPanel'
import BasicEdit from '../../components/BasicEdit'
import { options } from '.'
import { colors } from './colors'

const Edit = ({
	attributes,
	setAttributes,
	clientId,
	className,
	textColor,
	setTextColor,
	textHoverColor,
	setTextHoverColor,
	iconsColor,
	setIconsColor,
	iconsHoverColor,
	setIconsHoverColor,
	backgroundColor,
	setBackgroundColor,
	backgroundHoverColor,
	setBackgroundHoverColor,
	borderColor,
	setBorderColor,
	borderHoverColor,
	setBorderHoverColor,
}) => {
	const {
		about_alignment = 'center',
		about_items_spacing = '',
		about_social_icons_size = '',
		about_social_type = 'simple',
		about_social_icons_fill = 'outline',
		about_social_icons_color = 'default',
	} = attributes
	const navRef = useRef()

	const blockProps = useBlockProps({
		ref: navRef,
		className: {
			'ct-about-me-block': true,
			className,
		},
		'data-alignment': about_alignment,
		style: {
			'--theme-block-text-color': textColor?.color,
			'--theme-link-hover-color': textColor?.color,
			'--theme-icon-color': iconsColor?.color,
			'--theme-icon-hover-color': iconsHoverColor?.color,
			'--background-color':
				about_social_icons_fill === 'solid'
					? backgroundColor?.color
					: borderColor?.color,
			'--background-hover-color':
				about_social_icons_fill === 'solid'
					? backgroundHoverColor?.color
					: borderHoverColor?.color,
			...(about_social_icons_size
				? {
						'--theme-icon-size': `${about_social_icons_size}px`,
				  }
				: {}),
			...(about_items_spacing
				? {
						'--items-spacing': `${about_items_spacing}px`,
				  }
				: {}),
		},
	})

	return (
		<div {...blockProps}>
			<Preview attributes={attributes} setAttributes={setAttributes} />
			<BasicEdit
				attributes={attributes}
				setAttributes={setAttributes}
				options={options}
			/>
			<InspectorControls group="styles">
				<ColorsPanel
					label={__('Text Color', 'blocksy')}
					resetAll={() => {
						setTextColor(colors.textColor)
						setTextHoverColor(colors.textHoverColor)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: textColor.color,
							enableAlpha: true,
							label: __('Initial', 'blocksy'),
							onColorChange: (value) =>
								setTextColor(value || colors.textColor),
						},
						{
							colorValue: textHoverColor.color,
							enableAlpha: true,
							label: __('Hover', 'blocksy'),
							onColorChange: (value) =>
								setTextHoverColor(
									value || colors.textHoverColor
								),
						},
					]}
				/>

				{about_social_icons_color === 'default' ? (
					<ColorsPanel
						label={__('Icons Color', 'blocksy')}
						resetAll={() => {
							setIconsColor(colors.iconsColor)
							setIconsHoverColor(colors.iconsHoverColor)
						}}
						panelId={clientId}
						settings={[
							{
								colorValue: iconsColor.color,
								enableAlpha: true,
								label: __('Initial', 'blocksy'),
								onColorChange: (value) =>
									setIconsColor(value || colors.iconsColor),
							},
							{
								colorValue: iconsHoverColor.color,
								enableAlpha: true,
								label: __('Hover', 'blocksy'),
								onColorChange: (value) =>
									setIconsHoverColor(
										value || colors.iconsHoverColor
									),
							},
						]}
					/>
				) : null}

				{about_social_type !== 'simple' &&
					about_social_icons_color === 'default' &&
					(about_social_icons_fill === 'solid' ? (
						<ColorsPanel
							label={__('Icons Background Color', 'blocksy')}
							resetAll={() => {
								setBackgroundColor(colors.backgroundColor)
								setBackgroundHoverColor(
									colors.backgroundHoverColor
								)
							}}
							panelId={clientId}
							settings={[
								{
									colorValue: backgroundColor.color,
									enableAlpha: true,
									label: __('Initial', 'blocksy'),
									onColorChange: (value) =>
										setBackgroundColor(
											value || colors.backgroundColor
										),
								},
								{
									colorValue: backgroundHoverColor.color,
									enableAlpha: true,
									label: __('Hover', 'blocksy'),
									onColorChange: (value) =>
										setBackgroundHoverColor(
											value || colors.backgroundHoverColor
										),
								},
							]}
						/>
					) : (
						<ColorsPanel
							label={__('Icons Border Color', 'blocksy')}
							resetAll={() => {
								setBorderColor(colors.borderColor)
								setBorderHoverColor(colors.borderHoverColor)
							}}
							panelId={clientId}
							settings={[
								{
									colorValue: borderColor.color,
									enableAlpha: true,
									label: __('Initial', 'blocksy'),
									onColorChange: (value) =>
										setBorderColor(
											value || colors.borderColor
										),
								},
								{
									colorValue: borderHoverColor.color,
									enableAlpha: true,
									label: __('Hover', 'blocksy'),
									onColorChange: (value) =>
										setBorderHoverColor(
											value || colors.borderHoverColor
										),
								},
							]}
						/>
					))}
			</InspectorControls>
		</div>
	)
}

export default withColors(
	{ textColor: 'color' },
	{ textHoverColor: 'color' },
	{ iconsColor: 'color' },
	{ iconsHoverColor: 'color' },
	{ backgroundColor: 'color' },
	{ backgroundHoverColor: 'color' },
	{ borderColor: 'color' },
	{ borderHoverColor: 'color' }
)(Edit)
