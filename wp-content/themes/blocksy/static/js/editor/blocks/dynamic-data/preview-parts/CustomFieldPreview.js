import { createElement } from '@wordpress/element'
import useCustomFieldData from '../hooks/use-custom-field-data'

import CustomTextField from './custom/CustomTextField'
import CustomImageField from './custom/CustomImageField'

const CustomFieldPreview = ({
	fieldDescriptor,
	postId,
	postType,
	attributes,
}) => {
	const { fieldData } = useCustomFieldData({ postId, fieldDescriptor })

	if (!fieldData) {
		return null
	}

	if (fieldDescriptor.type === 'image') {
		return (
			<CustomImageField
				fieldData={fieldData}
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
			/>
		)
	}

	return (
		<CustomTextField
			fieldData={fieldData}
			fieldDescriptor={fieldDescriptor}
			attributes={attributes}
		/>
	)
}

export default CustomFieldPreview
