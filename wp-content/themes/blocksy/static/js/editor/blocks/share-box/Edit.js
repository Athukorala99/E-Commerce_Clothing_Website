import { createElement, useRef } from '@wordpress/element'
import { __ } from 'ct-i18n'
import {
	InspectorControls,
	useBlockProps,
	withColors,
} from '@wordpress/block-editor'
import { getOptionsForBlock } from '../../utils'
import Preview from './Preview'
import BasicEdit from '../../components/BasicEdit'
import ColorsPanel from '../../components/ColorsPanel'
import { colors } from './colors'

export const options = getOptionsForBlock('share_box')

const Edit = ({
	attributes,
	setAttributes,
	clientId,
	className,
	initialColor,
	setInitialColor,
	hoverColor,
	setHoverColor,
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
		items_spacing = '',
		share_icons_size = '',
		share_type = 'simple',
		share_icons_fill = 'outline',
		share_icons_color = 'default',
	} = attributes
	const navRef = useRef()

	const blockProps = useBlockProps({
		ref: navRef,
		className: {
			'ct-shares-block': true,
			className,
		},
		style: {
			'--theme-icon-color': initialColor?.color,
			'--theme-icon-hover-color': hoverColor?.color,
			'--background-color':
				share_icons_fill === 'solid'
					? backgroundColor?.color
					: borderColor?.color,
			'--background-hover-color':
				share_icons_fill === 'solid'
					? backgroundHoverColor?.color
					: borderHoverColor?.color,

			...(share_icons_size
				? { '--theme-icon-size': `${share_icons_size}px` }
				: {}),
			...(items_spacing
				? { '--items-spacing': `${items_spacing}px` }
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
			{share_icons_color === 'default' ? (
				<InspectorControls group="styles">
					<ColorsPanel
						label={__('Icon Color', 'blocksy')}
						resetAll={() => {
							setInitialColor(colors.initialColor)
							setHoverColor(colors.hoverColor)
						}}
						panelId={clientId}
						settings={[
							{
								colorValue: initialColor.color,
								enableAlpha: true,
								label: __('Initial', 'blocksy'),
								onColorChange: (value) =>
									setInitialColor(
										value || colors.initialColor
									),
							},
							{
								colorValue: hoverColor.color,
								enableAlpha: true,
								label: __('Hover', 'blocksy'),
								onColorChange: (value) =>
									setHoverColor(value || colors.hoverColor),
							},
						]}
					/>

					{share_type !== 'simple' &&
						(share_icons_fill === 'solid' ? (
							<ColorsPanel
								label={__('Icons Background Colors', 'blocksy')}
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
												value ||
													colors.backgroundHoverColor
											),
									},
								]}
							/>
						) : (
							<ColorsPanel
								label={__('Icons Border Colors', 'blocksy')}
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
			) : null}
		</div>
	)
}

export default withColors(
	{ initialColor: 'color' },
	{ hoverColor: 'color' },
	{ backgroundColor: 'color' },
	{ backgroundHoverColor: 'color' },
	{ borderColor: 'color' },
	{ borderHoverColor: 'color' }
)(Edit)
