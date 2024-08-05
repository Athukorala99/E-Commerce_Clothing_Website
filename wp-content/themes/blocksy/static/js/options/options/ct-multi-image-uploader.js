import { createElement, Component, Fragment } from '@wordpress/element'
import classnames from 'classnames'
import { __ } from 'ct-i18n'
import _ from 'underscore'

import { applyFilters } from '@wordpress/hooks'

const ALLOWED_MEDIA_TYPES = ['image']

export default class MultiImageUploader extends Component {
	params = {
		height: 250,
		width: 250,
		flex_width: true,
		flex_height: true,
	}

	state = {
		attachment_info: [],
	}

	getUrlFor = (attachmentInfo) =>
		attachmentInfo
			? (attachmentInfo.width < 700
					? attachmentInfo.sizes.full
					: _.max(
							_.values(
								_.keys(attachmentInfo.sizes).length === 1
									? attachmentInfo.sizes
									: _.omit(attachmentInfo.sizes, 'full')
							),
							({ width }) => width
					  )
			  ).url || attachmentInfo.url
			: null

	render() {
		const actions = applyFilters(
			'blocksy.options.ct-multi-image-uploader.actions',
			[
				({ props, attachment: { attachment_id } }) => (
					<button
						title="Remove"
						type="button"
						className="button remove-button"
						onClick={(e) => {
							e.stopPropagation()

							props.onChange(
								props.value.filter(
									(a) => a.attachment_id !== attachment_id
								)
							)
						}}></button>
				),
			]
		)

		return (
			<div
				className={classnames('ct-attachment-multi', {})}
				{...(this.props.option.attr || {})}>
				<wp.mediaUtils.MediaUpload
					onSelect={(media) => {
						const result = media.map((attachment) => ({
							url: this.getUrlFor(attachment),
							attachment_id: attachment.id,
						}))

						this.props.onChange(result)
					}}
					gallery={true}
					allowedTypes={ALLOWED_MEDIA_TYPES}
					value={this.props.value.map(
						({ attachment_id }) => attachment_id
					)}
					multiple={true}
					render={({ open }) => (
						<Fragment>
							{Array.isArray(this.props.value) &&
								this.props.value.length > 0 && (
									<div className="ct-thumbnails-list">
										{this.props.value.map(
											({
												url,
												attachment_id,
												...attachment
											}) => (
												<div
													key={attachment_id}
													className="thumbnail thumbnail-image"
													onClick={() => {
														open()
													}}>
													<img
														className="attachment-thumb"
														src={url}
														draggable="false"
														alt=""
													/>

													<ul className="actions">
														{actions.map(
															(a, index) => (
																<li key={index}>
																	{a({
																		props: this
																			.props,
																		attachment:
																			{
																				...attachment,
																				url,
																				attachment_id,
																			},
																	})}
																</li>
															)
														)}
													</ul>
												</div>
											)
										)}
									</div>
								)}
							<button
								type="button"
								className="button edit-button control-focus"
								title="Edit"
								onClick={() => open()}>
								{__('Add/Edit Gallery', 'blocksy')}
							</button>
						</Fragment>
					)}
				/>
			</div>
		)
	}
}
