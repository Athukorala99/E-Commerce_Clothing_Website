import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { InspectorControls } from '@wordpress/block-editor'
import {
	RangeControl,
	PanelBody,
	TextareaControl,
	ExternalLink,
	TextControl,
} from '@wordpress/components'
import { OptionsPanel } from 'blocksy-options'

import { fieldIsImageLike } from '../utils'
import DimensionControls from './Dimensions'

const DynamicDataInspectorControls = ({
	fieldDescriptor,
	fieldsDescriptor,

	attributes,
	setAttributes,

	options,
	fieldsChoices,

	clientId,

	taxonomies,
}) => {
	return (
		<>
			<InspectorControls>
				<PanelBody>
					<OptionsPanel
						purpose="gutenberg"
						onChange={(optionId, optionValue) => {
							setAttributes({
								[optionId]: optionValue,
							})
						}}
						options={{
							field: {
								type: 'ct-select',
								label: __('Content Source', 'blocksy'),
								value: '',
								defaultToFirstItem: false,
								choices: fieldsChoices,
								purpose: 'default',
							},

							...(attributes.field === 'wp:terms' &&
							taxonomies &&
							taxonomies.length > 0
								? {
										taxonomy: {
											type: 'ct-select',
											label: __('Taxonomy', 'blocksy'),
											value: '',
											design: 'inline',
											purpose: 'default',
											choices: taxonomies.map(
												({ name, slug }) => ({
													key: slug,
													value: name,
												})
											),
										},
								  }
								: {}),

							...options,
						}}
						value={{
							...attributes,
							...(fieldsDescriptor &&
							fieldsDescriptor.has_taxonomies_customization
								? { has_taxonomies_customization: 'yes' }
								: {}),
						}}
						hasRevertButton={false}
					/>

					{fieldIsImageLike(fieldDescriptor) &&
						attributes.field !== 'wp:author_avatar' && (
							<OptionsPanel
								purpose="gutenberg"
								onChange={(optionId, optionValue) => {
									setAttributes({
										[optionId]: optionValue,
									})
								}}
								options={{
									lightbox: {
										type: 'ct-switch',
										label: __('Expand on click', 'blocksy'),
										value: 'no',
									},

									...(attributes.field === 'wp:featured_image'
										? {
												videoThumbnail: {
													type: 'ct-switch',
													label: __(
														'Video Thumbnail',
														'blocksy'
													),
													value: 'no',
												},
										  }
										: {}),

									image_hover_effect: {
										label: __('Hover Effect', 'blocksy'),
										type: 'ct-select',
										value: 'none',
										view: 'text',
										design: 'inline',
										choices: {
											none: __('None', 'blocksy'),
											'zoom-in': __('Zoom In', 'blocksy'),
											'zoom-out': __(
												'Zoom Out',
												'blocksy'
											),
										},
									},
								}}
								value={attributes}
								hasRevertButton={false}
							/>
						)}
				</PanelBody>

				{fieldIsImageLike(fieldDescriptor) &&
					attributes.field !== 'wp:author_avatar' && (
						<>
							<DimensionControls
								clientId={clientId}
								attributes={attributes}
								setAttributes={setAttributes}
							/>

							<PanelBody>
								<TextareaControl
									label={__('Alternative Text', 'blocksy')}
									value={attributes.alt_text || ''}
									onChange={(value) => {
										setAttributes({
											alt_text: value,
										})
									}}
									help={
										<>
											<ExternalLink href="https://www.w3.org/WAI/tutorials/images/decision-tree">
												{__(
													'Describe the purpose of the image.',
													'blocksy'
												)}
											</ExternalLink>
											<br />
											{__(
												'Leave empty if decorative.',
												'blocksy'
											)}
										</>
									}
									__nextHasNoMarginBottom
								/>
							</PanelBody>
						</>
					)}

				{attributes.field === 'wp:author_avatar' && (
					<PanelBody>
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={__('Image size', 'blocksy')}
							onChange={(newSize) =>
								setAttributes({
									avatar_size: newSize,
								})
							}
							min={5}
							max={500}
							initialPosition={attributes?.avatar_size}
							value={attributes?.avatar_size}
						/>
					</PanelBody>
				)}

				{!fieldIsImageLike(fieldDescriptor) && (
					<PanelBody>
						<OptionsPanel
							purpose="gutenberg"
							onChange={(optionId, optionValue) => {
								setAttributes({
									[optionId]: optionValue,
								})
							}}
							options={{
								before: {
									type: 'text',
									label: __('Before', 'blocksy'),
									value: '',
								},

								after: {
									type: 'text',
									label: __('After', 'blocksy'),
									value: '',
								},

								...(fieldDescriptor.provider !== 'wp' ||
								(fieldDescriptor.provider === 'wp' &&
									(fieldDescriptor.id === 'excerpt' ||
										fieldDescriptor.id === 'terms' ||
										fieldDescriptor.id === 'author'))
									? {
											fallback: {
												type: 'text',
												label: __(
													'Fallback',
													'blocksy'
												),
												value: __(
													'Custom field fallback',
													'blocksy'
												),
											},
									  }
									: {}),
							}}
							value={attributes}
							hasRevertButton={false}
						/>
					</PanelBody>
				)}
			</InspectorControls>

			{attributes.field === 'wp:terms' && (
				<InspectorControls group="advanced">
					<TextControl
						__nextHasNoMarginBottom
						autoComplete="off"
						label={__('Term additional class', 'blocksy')}
						value={attributes.termClass}
						onChange={(nextValue) => {
							setAttributes({
								termClass: nextValue,
							})
						}}
						help={__(
							'Additional class for term items. Useful for styling.',
							'blocksy'
						)}
					/>
				</InspectorControls>
			)}
		</>
	)
}

export default DynamicDataInspectorControls
