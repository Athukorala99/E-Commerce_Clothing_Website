import { useEffect, useMemo, createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import Preview from './Preview'

import AlignmentControls from './components/AlignmentControls'

import useDynamicDataDescriptor from './hooks/use-dynamic-data-descriptor'

import DynamicDataInspectorControls from './components/InspectorControls'

import { useTaxonomies } from '../query/edit/utils/utils'

const Edit = ({
	clientId,

	attributes,
	setAttributes,

	context,
}) => {
	const { postId, postType } = context

	const { fieldsDescriptor, options, fieldsChoices } =
		useDynamicDataDescriptor({
			postId,
			postType,
		})

	const taxonomies = useTaxonomies(postType)

	const fieldDescriptor = useMemo(() => {
		if (!attributes.field || !fieldsDescriptor) {
			return null
		}

		const [provider, field] = attributes.field.split(':')

		const providerFields = fieldsDescriptor.fields.find(
			({ provider: p }) => p === provider
		)

		if (!providerFields) {
			return null
		}

		const maybeFieldDescriptor = providerFields.fields.find(
			({ id }) => id === field
		)

		if (!maybeFieldDescriptor) {
			return null
		}

		return {
			...maybeFieldDescriptor,
			provider: providerFields.provider,
		}
	}, [attributes.field, fieldsDescriptor])

	useEffect(() => {
		if (
			attributes.field === 'wp:terms' &&
			taxonomies &&
			taxonomies.length === 0
		) {
			setAttributes({
				field: `wp:title`,
			})
		}
	}, [taxonomies, attributes.field])

	if (!fieldDescriptor) {
		return null
	}

	return (
		<>
			<AlignmentControls
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
				setAttributes={setAttributes}
			/>

			<Preview
				attributes={attributes}
				postId={postId}
				postType={postType}
				fieldsDescriptor={fieldsDescriptor}
				fieldDescriptor={fieldDescriptor}
			/>

			<DynamicDataInspectorControls
				options={options}
				fieldDescriptor={fieldDescriptor}
				attributes={attributes}
				setAttributes={setAttributes}
				fieldsChoices={fieldsChoices}
				clientId={clientId}
				fieldsDescriptor={fieldsDescriptor}
				taxonomies={taxonomies}
			/>
		</>
	)
}

export default Edit
