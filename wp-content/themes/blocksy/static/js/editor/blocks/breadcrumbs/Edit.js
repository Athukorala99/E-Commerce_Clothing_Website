import { createElement, useRef } from '@wordpress/element'
import { __ } from 'ct-i18n'

import {
	InspectorControls,
	useBlockProps,
	withColors,
} from '@wordpress/block-editor'
import Preview from './Preview'
import ColorsPanel from '../../components/ColorsPanel'

const Edit = ({
	clientId,
	textColor,
	setTextColor,
	linkColor,
	setLinkColor,
	linkHoverColor,
	setLinkHoverColor,
	className,
}) => {
	const navRef = useRef()

	const blockProps = useBlockProps({
		ref: navRef,
		className: {
			'ct-breadcrumbs': true,
			className,
		},
		style: {
			color: textColor?.color,
			'--theme-link-initial-color': linkColor?.color,
			'--theme-link-hover-color': linkHoverColor?.color,
		},
	})

	return (
		<>
			<div {...blockProps}>
				<Preview />
				<InspectorControls group="styles">
					<ColorsPanel
						label={__('Text Color', 'blocksy')}
						resetAll={() => {
							setTextColor('')
							setLinkColor('')
							setLinkHoverColor('')
						}}
						panelId={clientId}
						settings={[
							{
								colorValue: textColor.color,
								enableAlpha: true,
								label: __('Text', 'blocksy'),
								onColorChange: setTextColor,
							},
							{
								colorValue: linkColor.color,
								enableAlpha: true,
								label: __('Link Initial', 'blocksy'),
								onColorChange: setLinkColor,
							},
							{
								colorValue: linkHoverColor.color,
								enableAlpha: true,
								label: __('Link Hover', 'blocksy'),
								onColorChange: setLinkHoverColor,
							},
						]}
					/>
				</InspectorControls>
			</div>
		</>
	)
}

export default withColors(
	{ textColor: 'color' },
	{ linkColor: 'color' },
	{ linkHoverColor: 'color' }
)(Edit)
