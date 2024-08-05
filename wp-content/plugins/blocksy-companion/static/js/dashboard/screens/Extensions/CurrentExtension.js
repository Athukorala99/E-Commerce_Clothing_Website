import {
	useEffect,
	useState,
	createElement,
	Fragment,
} from '@wordpress/element'
import classnames from 'classnames'

import { __ } from 'ct-i18n'

import useActivationAction from '../../helpers/useActivationAction'

import { applyFilters } from '@wordpress/hooks'

const CurrentExtension = ({
	navigate,
	currentExtension,
	onExtsSync,
	setExtsStatus,
}) => {
	const [hasCustomContent, setHasCustomContent] = useState(false)

	const [isLoading, activationAction, activationContent] =
		useActivationAction(currentExtension)

	let defaultContentDescriptor = {
		content: null,
		showExtension: true,

		// default | from-custom-content
		activationStrategy: 'default',
	}

	const customContent = currentExtension
		? applyFilters(
				'blocksy.extensions.current_extension_content',
				defaultContentDescriptor,
				{
					extension: currentExtension,
					onExtsSync,
					setExtsStatus,
				}
		  )
		: defaultContentDescriptor

	let shouldDisplayCustomContent =
		!!currentExtension && currentExtension.__object

	if (customContent.activationStrategy === 'from-custom-content') {
		shouldDisplayCustomContent = hasCustomContent
	}

	const actions = [
		...(currentExtension &&
		currentExtension.config.documentation &&
		!ctDashboardLocalizations.plugin_data.hide_docs_section
			? [
					<a
						target="_blank"
						href={currentExtension.config.documentation}>
						<svg
							width="15px"
							height="15px"
							viewBox="0 0 24 24"
							fill="currentColor">
							<path d="M22.9,1.1h-6.5C14.6,1.1,13,2,12,3.3C11,2,9.4,1.1,7.6,1.1H1.1C0.5,1.1,0,1.6,0,2.2v16.4c0,0.6,0.5,1.1,1.1,1.1h7.6c1.2,0,2.2,1,2.2,2.2c0,0.6,0.5,1.1,1.1,1.1c0.6,0,1.1-0.5,1.1-1.1c0-1.2,1-2.2,2.2-2.2h7.6c0.6,0,1.1-0.5,1.1-1.1V2.2C24,1.6,23.5,1.1,22.9,1.1z M10.9,18c-0.6-0.4-1.4-0.6-2.2-0.6H2.2V3.3h5.5c1.8,0,3.3,1.5,3.3,3.3V18z M21.8,17.5h-6.5c-0.8,0-1.5,0.2-2.2,0.6V6.5c0-1.8,1.5-3.3,3.3-3.3h5.5V17.5z" />
						</svg>
						{__('Documentation', 'blocksy-companion')}
					</a>,
			  ]
			: []),

		...(currentExtension &&
		currentExtension.config.video &&
		!ctDashboardLocalizations.plugin_data.hide_video_section
			? [
					<a target="_blank" href={currentExtension.config.video}>
						<svg
							width="15px"
							height="15px"
							viewBox="0 0 24 24"
							fill="currentColor">
							<path d="M12,0C5.4,0,0,5.4,0,12s5.4,12,12,12s12-5.4,12-12S18.6,0,12,0z M12,21.8c-5.4,0-9.8-4.4-9.8-9.8c0-5.4,4.4-9.8,9.8-9.8c5.4,0,9.8,4.4,9.8,9.8C21.8,17.4,17.4,21.8,12,21.8z M16.1,11.3c0.2,0.2,0.4,0.4,0.4,0.7c0,0.3-0.1,0.6-0.4,0.7L11,16.1c-0.1,0.1-0.3,0.1-0.5,0.1c-0.1,0-0.3,0-0.4-0.1c-0.3-0.1-0.5-0.4-0.5-0.8V8.6c0-0.3,0.2-0.6,0.5-0.8c0.3-0.1,0.6-0.1,0.9,0L16.1,11.3z" />
						</svg>
						{__('Video Tutorial', 'blocksy-companion')}
					</a>,
			  ]
			: []),

		...(currentExtension &&
		currentExtension.config.customize &&
		shouldDisplayCustomContent
			? [
					<a
						href={currentExtension.config.customize}
						target="_blank"
						className="ct-button">
						<svg
							width="15px"
							height="15px"
							viewBox="0 0 24 24"
							fill="currentColor">
							<path d="M4 11c.6 0 1-.4 1-1V3c0-.6-.4-1-1-1s-1 .4-1 1v7c0 .6.4 1 1 1zM12 11c-.6 0-1 .4-1 1v9c0 .6.4 1 1 1s1-.4 1-1v-9c0-.6-.4-1-1-1zM20 13c.6 0 1-.4 1-1V3c0-.6-.4-1-1-1s-1 .4-1 1v9c0 .6.4 1 1 1zM7 13H1c-.6 0-1 .4-1 1s.4 1 1 1h2v6c0 .6.4 1 1 1s1-.4 1-1v-6h2c.6 0 1-.4 1-1s-.4-1-1-1zM15 7h-2V3c0-.6-.4-1-1-1s-1 .4-1 1v4H9c-.6 0-1 .4-1 1s.4 1 1 1h6c.6 0 1-.4 1-1s-.4-1-1-1zM23 15h-6c-.6 0-1 .4-1 1s.4 1 1 1h2v4c0 .6.4 1 1 1s1-.4 1-1v-4h2c.6 0 1-.4 1-1s-.4-1-1-1z" />
						</svg>
						{__('Customize', 'blocksy-companion')}
					</a>,
			  ]
			: []),
	]

	useEffect(() => {
		if (!currentExtension || !customContent.showExtension) {
			navigate('/extensions')
		}
	}, [])

	if (!currentExtension) {
		return null
	}

	return (
		<div className="ct-extension-container">
			<div className="ct-extension-info">
				<h4>
					<span
						className="ct-extension-icon"
						dangerouslySetInnerHTML={{
							__html:
								(currentExtension &&
									currentExtension.config.icon) ||
								`<svg width="16" height="16" viewBox="0 0 24 24">
							<path
								fillRule="evenodd"
								d="M22.3,10.7H24V7.6l-1.5-6.2h-21L0,7.6v3.1h1.7v11.7h20.7V10.7z M20.3,10.7H3.7v9.7h5.7v-7h5.3v7h5.7V10.7zM13,3.4h2.8l0.6,4.3l0,1H13V3.4z M8.2,3.4H11v5.3H7.6l0-1L8.2,3.4z M18.4,7.5l-0.6-4.1h3.1L22,7.8v0.9h-3.6L18.4,7.5z M6.2,3.4H3.1L2,7.8v0.9h3.6l0-1.2L6.2,3.4z"
							/>
						</svg>`,
						}}
					/>
					{currentExtension && currentExtension.config.name}
					{currentExtension &&
						(!currentExtension.config.requirement ||
							(currentExtension.config.requirement &&
								currentExtension.config.requirement.check)) && (
							<div
								className={classnames('ct-option-switch', {
									'ct-active': shouldDisplayCustomContent,
								})}
								onClick={() => {
									if (
										customContent.activationStrategy ===
										'from-custom-content'
									) {
										setHasCustomContent(!hasCustomContent)
										return
									}

									activationAction(() => {
										setExtsStatus((extStatus) => ({
											...extStatus,
											[currentExtension.name]: {
												...extStatus[
													currentExtension.name
												],
												__object: extStatus[
													currentExtension.name
												].__object
													? null
													: {},
											},
										}))
									})
								}}>
								<span></span>
							</div>
						)}
				</h4>

				{currentExtension && currentExtension.config.description && (
					<p>{currentExtension.config.description}</p>
				)}

				{actions.length > 0 && (
					<div className="ct-extension-actions">
						{actions.map((action, index) => (
							<Fragment key={index}>{action}</Fragment>
						))}
					</div>
				)}
			</div>

			{shouldDisplayCustomContent &&
				currentExtension.config.features &&
				(!currentExtension.config.requirement ||
					(currentExtension.config.requirement &&
						currentExtension.config.requirement.check)) && (
					<div className="ct-extension-modules">
						{currentExtension.config.features.map((feature) => (
							<div
								className="ct-extension-module"
								key={feature.id}>
								<h5>
									{feature.title}
									<div
										className={classnames(
											'ct-option-switch',
											{
												'ct-active':
													currentExtension.data &&
													currentExtension.data
														.settings.features[
														feature.id
													],
											}
										)}
										onClick={() => {
											activationAction(() => {
												onExtsSync({
													extAction: {
														type: 'update-features',
														require_refresh:
															!!feature?.require_refresh,
														settings: {
															features: {
																...currentExtension
																	.data
																	.settings
																	.features,
																[feature.id]:
																	!currentExtension
																		.data
																		.settings
																		.features[
																		feature
																			.id
																	],
															},
														},
													},
												})

												setExtsStatus((extStatus) => ({
													...extStatus,
													[currentExtension.name]: {
														...extStatus[
															currentExtension
																.name
														],

														data: {
															...extStatus[
																currentExtension
																	.name
															].data,

															settings: {
																...extStatus[
																	currentExtension
																		.name
																].data.settings,

																features: {
																	...extStatus[
																		currentExtension
																			.name
																	].data
																		.settings
																		.features,

																	[feature.id]:
																		!extStatus[
																			currentExtension
																				.name
																		].data
																			.settings
																			.features[
																			feature
																				.id
																		],
																},
															},
														},
													},
												}))
											}, false)
										}}>
										<span></span>
									</div>
								</h5>

								<p>{feature.description}</p>

								{(feature.documentation &&
									!ctDashboardLocalizations.plugin_data
										.hide_docs_section) ||
								((feature.customize || feature.manage) &&
									currentExtension.data &&
									currentExtension.data.settings.features[
										feature.id
									]) ? (
									<div className="ct-extension-module-actions">
										{feature.documentation &&
											!ctDashboardLocalizations
												.plugin_data
												.hide_docs_section && (
												<a
													href={feature.documentation}
													target="_blank">
													<svg
														width="14px"
														height="14px"
														viewBox="0 0 24 24"
														fill="currentColor">
														<path d="M23 2.1h-6.6c-1.8 0-3.4.9-4.4 2.3C11 3 9.4 2.1 7.6 2.1H1c-.6 0-1 .4-1 1v16.5c0 .6.4 1 1 1h7.7c1.3 0 2.3 1 2.3 2.3 0 .6.4 1 1 1s1-.4 1-1c0-1.3 1-2.3 2.3-2.3H23c.6 0 1-.4 1-1V3.1c0-.6-.4-1-1-1zM11 19.3c-.7-.4-1.5-.7-2.3-.7H2V4.1h5.6c1.9 0 3.4 1.5 3.4 3.4v11.8zm11-.7h-6.7c-.8 0-1.6.2-2.3.7V7.5c0-1.9 1.5-3.4 3.4-3.4H22v14.5z" />
													</svg>
													{__(
														'Documentation',
														'blocksy-companion'
													)}
												</a>
											)}

										{(feature.customize ||
											feature.manage) &&
											currentExtension.data &&
											currentExtension.data.settings
												.features[feature.id] && (
												<a
													href={
														feature.customize ||
														feature.manage
													}
													target="_blank">
													<svg
														width="14px"
														height="14px"
														viewBox="0 0 24 24"
														fill="currentColor">
														<path d="M4 11c.6 0 1-.4 1-1V3c0-.6-.4-1-1-1s-1 .4-1 1v7c0 .6.4 1 1 1zM12 11c-.6 0-1 .4-1 1v9c0 .6.4 1 1 1s1-.4 1-1v-9c0-.6-.4-1-1-1zM20 13c.6 0 1-.4 1-1V3c0-.6-.4-1-1-1s-1 .4-1 1v9c0 .6.4 1 1 1zM7 13H1c-.6 0-1 .4-1 1s.4 1 1 1h2v6c0 .6.4 1 1 1s1-.4 1-1v-6h2c.6 0 1-.4 1-1s-.4-1-1-1zM15 7h-2V3c0-.6-.4-1-1-1s-1 .4-1 1v4H9c-.6 0-1 .4-1 1s.4 1 1 1h6c.6 0 1-.4 1-1s-.4-1-1-1zM23 15h-6c-.6 0-1 .4-1 1s.4 1 1 1h2v4c0 .6.4 1 1 1s1-.4 1-1v-4h2c.6 0 1-.4 1-1s-.4-1-1-1z" />
													</svg>
													{feature.customize
														? __(
																'Customize',
																'blocksy-companion'
														  )
														: __(
																'Manage',
																'blocksy-companion'
														  )}
												</a>
											)}
									</div>
								) : null}
							</div>
						))}
					</div>
				)}

			{currentExtension &&
				currentExtension.config.requirement &&
				!currentExtension.config.requirement.check &&
				currentExtension.config.requirement.message && (
					<div className="ct-extension-options ct-newsletter-subscribe-options">
						<div className="ct-extension-requirement">
							<span>
								<svg
									width="16"
									height="16"
									fill="#ffffff"
									viewBox="0 0 24 24">
									<path d="M12,23.6c-1.4,0-2.6-1-2.8-2.3L8.9,20h6.2l-0.3,1.3C14.6,22.6,13.4,23.6,12,23.6z M24,17.8H0l3.1-2c0.5-0.3,0.9-0.7,1.1-1.3c0.5-1,0.5-2.2,0.5-3.2V7.6c0-4.1,3.2-7.3,7.3-7.3s7.3,3.2,7.3,7.3v3.6c0,1.1,0.1,2.3,0.5,3.2c0.3,0.5,0.6,1,1.1,1.3L24,17.8zM6.1,15.6h11.8c0,0-0.1-0.1-0.1-0.2c-0.7-1.3-0.7-2.9-0.7-4.2V7.6c0-2.8-2.2-5.1-5.1-5.1c-2.8,0-5.1,2.2-5.1,5.1v3.6c0,1.3-0.1,2.9-0.7,4.2C6.1,15.5,6.1,15.6,6.1,15.6z"></path>
								</svg>
							</span>
							{currentExtension.config.requirement.message}
						</div>
					</div>
				)}
			{shouldDisplayCustomContent && customContent.content}
			{activationContent}
		</div>
	)
}

export default CurrentExtension
