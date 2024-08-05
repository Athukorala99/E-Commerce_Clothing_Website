import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

import { InspectorControls } from '@wordpress/block-editor'

import OptionsPanel from '../../../../options/OptionsPanel'

import BlocksyToolsPanel from '../../../components/ToolsPanel'

import { useTaxonomiesLayers } from './layers/useTaxonomiesLayers'

const PostsInspectorControls = ({
	context,
	attributes,
	attributes: { post_type },
	setAttributes,
}) => {
	const { taxonomiesGroup } = useTaxonomiesLayers({
		attributes,
		setAttributes,
		previewedPostMatchesType: attributes.post_type === context.postType,
	})

	return (
		<InspectorControls>
			<BlocksyToolsPanel
				className="ct-query-parameters-component"
				attributes={attributes}
				setAttributes={setAttributes}
				resetAll={() => {
					setAttributes({
						offset: 0,
						sticky_posts: 'include',
						orderby: 'post_date',
						order: 'desc',

						include_term_ids: {},
						exclude_term_ids: {},
					})
				}}
				items={[
					{
						label: __('General', 'blocksy'),
						items: [
							{
								label: __('Offset', 'blocksy'),

								hasValue: () => {
									return attributes.offset !== 0
								},

								reset: () => {
									setAttributes({
										offset: 0,
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												offset: {
													type: 'ct-number',
													label: __(
														'Offset',
														'blocksy'
													),
													value: '',
													min: 0,
													max: 500,
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},

							{
								label: __('Order by', 'blocksy'),

								hasValue: () => {
									return attributes.orderby !== 'post_date'
								},

								reset: () => {
									setAttributes({
										orderby: 'post_date',
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												orderby: {
													type: 'ct-select',
													label: __(
														'Order by',
														'blocksy'
													),
													value: '',
													choices: [
														{
															key: 'title',
															value: __(
																'Title',
																'blocksy'
															),
														},

														{
															key: 'post_date',
															value: __(
																'Publish Date',
																'blocksy'
															),
														},

														{
															key: 'modified_date',
															value: __(
																'Modified Date',
																'blocksy'
															),
														},

														{
															key: 'comment_count',
															value: __(
																'Most commented',
																'blocksy'
															),
														},

														{
															key: 'author',
															value: __(
																'Author',
																'blocksy'
															),
														},

														{
															key: 'rand',
															value: __(
																'Random',
																'blocksy'
															),
														},

														{
															key: 'menu_order',
															value: __(
																'Menu Order',
																'blocksy'
															),
														},
													],
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},

							{
								label: __('Order', 'blocksy'),

								hasValue: () => {
									return attributes.order !== 'desc'
								},

								reset: () => {
									setAttributes({
										order: 'desc',
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												order: {
													type: 'ct-select',
													label: __(
														'Order',
														'blocksy'
													),
													value: '',
													choices: [
														{
															key: 'DESC',
															value: __(
																'Descending',
																'blocksy'
															),
														},

														{
															key: 'ASC',
															value: __(
																'Ascending',
																'blocksy'
															),
														},
													],
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},

							{
								label: __('Sticky Posts', 'blocksy'),

								hasValue: () => {
									return attributes.sticky_posts !== 'include'
								},

								reset: () => {
									setAttributes({
										sticky_posts: 'include',
									})
								},

								render: () => {
									return (
										<OptionsPanel
											purpose="gutenberg"
											onChange={(
												optionId,
												optionValue
											) => {
												setAttributes({
													[optionId]: optionValue,
												})
											}}
											options={{
												sticky_posts: {
													type: 'ct-select',
													label: __(
														'Sticky Posts',
														'blocksy'
													),
													value: 'include',
													choices: [
														{
															key: 'include',
															value: __(
																'Include',
																'blocksy'
															),
														},

														{
															key: 'exclude',
															value: __(
																'Exclude',
																'blocksy'
															),
														},

														{
															key: 'only',
															value: __(
																'Only',
																'blocksy'
															),
														},
													],
												},
											}}
											value={attributes}
											hasRevertButton={false}
										/>
									)
								},
							},
						],
					},

					...(taxonomiesGroup ? [taxonomiesGroup] : []),
				]}
				label={__('Parameters', 'blocksy')}
			/>
		</InspectorControls>
	)
}

export default PostsInspectorControls
