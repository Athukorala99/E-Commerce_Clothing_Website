import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'
import { InspectorControls } from '@wordpress/block-editor'
import { PanelBody } from '@wordpress/components'

import { OptionsPanel } from 'blocksy-options'

const BasicEdit = ({ attributes, setAttributes, options }) => (
	<InspectorControls>
		<PanelBody>
			<OptionsPanel
				purpose="gutenberg"
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
)

export default BasicEdit
