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
	textInitialColor,
	setTextInitialColor,
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
		contacts_items_direction = 'column',
		contacts_icons_size = 20,
		contacts_items_spacing = '',
		contacts_icon_shape = 'rounded',
		contacts_icon_fill_type = 'outline',
	} = attributes

	const navRef = useRef()

	const blockProps = useBlockProps({
		ref: navRef,
		className: {
			'ct-contact-info-block': true,
			className,
		},
		style: {
			'--theme-block-text-color': textColor?.color,
			'--theme-link-initial-color': textInitialColor?.color,
			'--theme-link-hover-color': textHoverColor?.color,
			'--theme-icon-color': iconsColor?.color,
			'--theme-icon-hover-color': iconsHoverColor?.color,
			'--background-color':
				contacts_icon_fill_type === 'solid'
					? backgroundColor?.color
					: borderColor?.color,
			'--background-hover-color':
				contacts_icon_fill_type === 'solid'
					? backgroundHoverColor?.color
					: borderHoverColor?.color,
			...(contacts_icons_size
				? {
						'--theme-icon-size': `${contacts_icons_size}px`,
				  }
				: {}),
			...(contacts_items_spacing
				? {
						'--items-spacing': `${contacts_items_spacing}px`,
				  }
				: {}),
			...(contacts_items_direction === 'column'
				? {
						'--items-direction': contacts_items_direction,
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
						setTextInitialColor(colors.textInitialColor)
						setTextHoverColor(colors.textHoverColor)
					}}
					panelId={clientId}
					settings={[
						{
							colorValue: textColor.color,
							enableAlpha: true,
							label: __('Text', 'blocksy'),
							onColorChange: (value) =>
								setTextColor(value || colors.textColor),
						},
						{
							colorValue: textInitialColor.color,
							enableAlpha: true,
							label: __('Link Initial', 'blocksy'),
							onColorChange: (value) =>
								setTextInitialColor(
									value || colors.textInitialColor
								),
						},
						{
							colorValue: textHoverColor.color,
							enableAlpha: true,
							label: __('Link Hover', 'blocksy'),
							onColorChange: (value) =>
								setTextHoverColor(
									value || colors.textHoverColor
								),
						},
					]}
				/>

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

				{contacts_icon_shape !== 'simple' &&
					(contacts_icon_fill_type === 'solid' ? (
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
	{ textInitialColor: 'color' },
	{ textHoverColor: 'color' },
	{ iconsColor: 'color' },
	{ iconsHoverColor: 'color' },
	{ backgroundColor: 'color' },
	{ backgroundHoverColor: 'color' },
	{ borderColor: 'color' },
	{ borderHoverColor: 'color' }
)(Edit)
