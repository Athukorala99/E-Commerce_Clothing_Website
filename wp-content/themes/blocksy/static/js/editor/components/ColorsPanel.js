import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'
import {
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from '@wordpress/block-editor'
import {
	PanelBody,
	__experimentalToolsPanel as ToolsPanel,
} from '@wordpress/components'

const ColorsPanel = ({ label, resetAll, panelId, settings }) => {
	const colorGradientSettings = useMultipleOriginColorsAndGradients()

	return (
		<ToolsPanel
			label={label}
			resetAll={resetAll}
			panelId={panelId}
			hasInnerWrapper
			className="color-block-support-panel"
			__experimentalFirstVisibleItemClass="first"
			__experimentalLastVisibleItemClass="last">
			<div className="color-block-support-panel__inner-wrapper">
				<ColorGradientSettingsDropdown
					__experimentalIsRenderedInSidebar
					__experimentalHasMultipleOrigins
					__experimentalGroup="bg"
					settings={settings}
					panelId={panelId}
					{...colorGradientSettings}
					gradients={[]}
					disableCustomGradients={true}
				/>
			</div>
		</ToolsPanel>
	)
}

export default ColorsPanel
