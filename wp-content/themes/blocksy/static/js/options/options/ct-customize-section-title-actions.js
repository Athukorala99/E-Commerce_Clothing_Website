import {
	useEffect,
	useState,
	Fragment,
	createElement,
	createPortal,
} from '@wordpress/element'

import useForceUpdate from '../containers/use-force-update'

import Overlay from '../../customizer/components/Overlay'
import { __, sprintf } from 'ct-i18n'
import { getFirstLevelOptions } from '../helpers/get-value-from-input'

import Select from './ct-select'

import classnames from 'classnames'

const OptionsActions = ({ option, option: { areas, prefix }, onChangeFor }) => {
	const [isOverlayOpen, setIsOverlayOpen] = useState(false)
	const [currentPrefix, setCurrentPrefix] = useState(false)
	const [currentOptionsArea, setCurrentOptionsArea] = useState(false)

	const forceUpdate = useForceUpdate()

	const maybePostsListing = areas.find((area) => area.id === 'posts_listing')
	const maybePageStructure = areas.find(
		(area) => area.id === 'page_structure'
	)

	const allAreas = [
		{
			title: __('All Options', 'blocksy'),
			sources: maybePageStructure
				? maybePageStructure.sources
				: maybePostsListing
				? maybePostsListing.sources
				: null,
		},

		...areas,
	]

	const sources =
		currentOptionsArea &&
		allAreas.find(({ title }) => title === currentOptionsArea) &&
		allAreas.find(({ title }) => title === currentOptionsArea).sources
			? allAreas.find(({ title }) => title === currentOptionsArea).sources
			: areas.reduce(
					(acc, { sources }) => [
						...acc,

						...sources.filter(
							({ title }) =>
								!acc.find(({ title: t }) => t === title)
						),
					],
					[]
			  )

	useEffect(() => {
		const wrapper = document
			.querySelector(
				'.customize-control-ct-options .ct-options-container'
			)
			.closest('ul')
			.firstElementChild.firstElementChild.insertAdjacentHTML(
				'beforeend',
				'<div class="ct-customize-section-title-actions"></div>'
			)
	}, [])

	if (
		!document.querySelector(
			'.customize-control-ct-options .ct-options-container'
		)
	) {
		setTimeout(() => {
			forceUpdate()
		}, 100)
		return null
	}

	const maybeTarget = document
		.querySelector('.customize-control-ct-options .ct-options-container')
		.closest('ul')
		.querySelector('.ct-customize-section-title-actions')

	if (!maybeTarget) {
		setTimeout(() => {
			forceUpdate()
		}, 100)
		return null
	}

	return createPortal(
		<Fragment>
			<span className="ct-more-options-trigger" data-tooltip="left">
				<button
					className="components-button components-dropdown-menu__toggle is-small has-icon"
					onClick={(e) => {
						e.preventDefault()
						setIsOverlayOpen(true)
					}}>
					<svg
						viewBox="0 0 24 24"
						width="24"
						height="24"
						fill="currentColor">
						<path d="M13 19h-2v-2h2v2zm0-6h-2v-2h2v2zm0-6h-2V5h2v2z"></path>
					</svg>
				</button>

				<i className="ct-tooltip">{__('Advanced', 'blocksy')}</i>
			</span>

			<Overlay
				items={isOverlayOpen}
				className="ct-admin-modal ct-copy-options-modal"
				onDismiss={() => {
					setIsOverlayOpen(false)
				}}
				render={() => (
					<div className="ct-modal-content">
						<h2 className="ct-modal-title">
							{sprintf(__('Copy Options', 'blocksy'))}
						</h2>

						<p>
							{__(
								'1. Choose what set of options you want to copy:',
								'blocksy'
							)}
						</p>

						<div
							className="ct-checkboxes-container"
							data-type="grid:bordered">
							{allAreas.map(({ title, options }) => (
								<div
									className="ct-checkbox-container"
									onClick={() => {
										setCurrentOptionsArea(title)
									}}
									key={title}>
									<span
										className={classnames('ct-checkbox', {
											active:
												currentOptionsArea === title,
										})}>
										<svg
											width="10"
											height="8"
											viewBox="0 0 11.2 9.1">
											<polyline
												className="check"
												points="1.2,4.8 4.4,7.9 9.9,1.2 "></polyline>
										</svg>
									</span>
									{title}
								</div>
							))}
						</div>

						<p>{__('2. Choose options source:', 'blocksy')}</p>

						<Select
							option={{
								disabled:
									!currentOptionsArea || !sources.length,
								choices: sources
									.filter(({ key }) => key !== prefix)
									.map(({ key, label, group }) => ({
										key,
										value: label,
										group,
									})),
								search: true,
								defaultToFirstItem: false,
							}}
							value={currentPrefix}
							onChange={(value) => {
								setCurrentPrefix(value)
							}}
						/>

						<div
							className="ct-modal-actions has-divider"
							data-buttons="2">
							<button
								className="button"
								onClick={(e) => {
									setIsOverlayOpen(false)
									e.preventDefault()
									setCurrentPrefix(false)
								}}>
								{__('Cancel', 'blocksy')}
							</button>
							<button
								className={classnames('button button-primary', {
									disabled:
										!currentPrefix || !currentOptionsArea,
								})}
								onClick={() => {
									const area = allAreas.find(
										({ title }) =>
											title === currentOptionsArea
									)

									if (!area.options) {
										const sourceKeys = Object.keys(
											wp.customize._value
										).filter(
											(key) =>
												key.indexOf(currentPrefix) === 0
										)

										sourceKeys.map((key) => {
											const newKey = key.replace(
												currentPrefix,
												prefix
											)

											if (wp.customize(key)) {
												onChangeFor(
													newKey,
													wp.customize(key)()
												)
											}
										})
									}

									if (area.options) {
										const destinationKeys = Object.keys(
											getFirstLevelOptions(area.options)
										)

										destinationKeys.map((key) => {
											const newKey = key.replace(
												prefix,
												currentPrefix
											)

											if (wp.customize(newKey)) {
												onChangeFor(
													key,
													wp.customize(newKey)()
												)
											}
										})
									}

									/*
									if (
										sync &&
										(Object.keys(sync).length > 0 ||
											Array.isArray(sync)) &&
										wp.customize &&
										wp.customize.previewer
									) {
										wp.customize.previewer.send(
											'ct:sync:refresh_partial',
											{
												id: sync.id || id,
												shouldSkip: !!sync.shouldSkip,
											}
										)
									}
*/

									if (
										wp.customize &&
										wp.customize.previewer
									) {
										wp.customize.previewer.refresh()
									}

									setIsOverlayOpen(false)
									setCurrentPrefix(false)
									setCurrentOptionsArea(false)
								}}>
								{__('Copy', 'blocksy')}
							</button>
						</div>
					</div>
				)}
			/>
		</Fragment>,
		maybeTarget
	)
}

OptionsActions.renderingConfig = { design: 'none' }

export default OptionsActions
