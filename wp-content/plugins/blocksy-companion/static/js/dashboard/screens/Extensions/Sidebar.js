import {
	useContext,
	createElement,
	useMemo,
	useState,
	Fragment,
} from '@wordpress/element'
import classnames from 'classnames'
import { __ } from 'ct-i18n'

const Sidebar = ({ navigate, currentExtension, exts_status }) => {
	const hasProExt = useMemo(
		() =>
			Object.values(exts_status)
				.map((ext, index) => ({
					...ext,
					name: Object.keys(exts_status)[index],
				}))
				.find(({ config }) => config.pro),
		[exts_status]
	)

	const freeExts = useMemo(
		() =>
			Object.values(exts_status)
				.map((ext, index) => ({
					...ext,
					name: Object.keys(exts_status)[index],
				}))
				.filter(({ config }) => !config.pro),
		[exts_status]
	)

	const proExts = useMemo(
		() =>
			Object.values(exts_status)
				.map((ext, index) => ({
					...ext,
					name: Object.keys(exts_status)[index],
				}))
				.filter(({ config }) => config.pro)
				.filter((ext) => {
					if (
						ext.name === 'white-label' &&
						ext.data &&
						ext.data.locked
					) {
						return false
					}

					return true
				}),
		[exts_status]
	)

	return (
		<div className="ct-extensions-menu">
			{[
				{
					label: __('Free Extensions', 'blocksy-companion'),
					exts: freeExts,
					order: [],
				},

				...(hasProExt
					? [
							{
								label: __(
									'Pro Extensions',
									'blocksy-companion'
								),
								exts: proExts,
								order: [
									'woocommerce-extra',
									'post-types-extra',
									'local-google-fonts',
									'custom-fonts',
									'adobe-typekit',
								],
							},
					  ]
					: []),
			].map(({ label, exts, order }) => {
				const sortedExts = [
					...order.map((name) =>
						exts.find((ext) => ext.name === name)
					),
					...exts
						.filter(({ name }) => !order.includes(name))
						.sort((a, b) => {
							return a.config.name.localeCompare(b.config.name)
						}),
				].filter((ext) => !!ext)

				return (
					<Fragment key={label}>
						<h4>{label}</h4>

						<ul>
							{sortedExts.map(
								({ name, config, status, __object }) => (
									<li
										key={name}
										className={classnames({
											selected:
												currentExtension &&
												currentExtension.name === name,
											active: !!__object,
										})}
										onClick={() => {
											navigate(`/extensions/${name}`, {
												state: {
													hasNoChange: true,
												},
											})
										}}>
										{config.name}
										<span data-tooltip="top">
											<i className="ct-tooltip">
												{__(
													'Active',
													'blocksy-companion'
												)}
											</i>
										</span>
									</li>
								)
							)}
						</ul>
					</Fragment>
				)
			})}
		</div>
	)
}

export default Sidebar
