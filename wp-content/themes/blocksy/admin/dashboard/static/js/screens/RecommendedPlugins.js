import {
	createElement,
	Component,
	useEffect,
	Fragment,
	useState,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import classnames from 'classnames'
import SinglePremiumPlugin from '../components/SinglePremiumPlugin'
import { Transition, animated } from 'react-spring/renderprops'
import SubmitSupport from '../components/SubmitSupport'

let plugins_status_cache = null

let staticSource = ctDashboardLocalizations.clean_install_plugins

export const pluginsWithNames = () =>
	Object.values(staticSource).map((plugin, index) => {
		plugin['name'] = Object.keys(staticSource)[index]
		return plugin
	})

const RecommendedPlugins = () => {
	const [isLoading, setIsLoading] = useState(!plugins_status_cache)
	const [plugins_status, setPluginStatus] = useState(
		plugins_status_cache || []
	)

	const plugins = pluginsWithNames()

	const syncPlugins = async (verbose = false) => {
		if (verbose) {
			setIsLoading(true)
		}

		const body = new FormData()
		body.append('action', 'get_premium_plugins_status')

		try {
			const response = await fetch(ctDashboardLocalizations.ajax_url, {
				method: 'POST',
				body,
			})

			if (response.status === 200) {
				const { success, data } = await response.json()
				if (success) {
					setPluginStatus(data)
					plugins_status_cache = data
				}
			}
		} catch (e) {}

		setIsLoading(false)
	}

	useEffect(() => {
		syncPlugins(!plugins_status_cache)
	}, [])

	return (
		<div>
			<Transition
				items={isLoading}
				from={{ opacity: 0 }}
				enter={[{ opacity: 1 }]}
				leave={[{ opacity: 0 }]}
				initial={null}
				config={(key, phase) => {
					return phase === 'leave'
						? {
								duration: 300,
						  }
						: {
								delay: 300,
								duration: 300,
						  }
				}}>
				{(isLoading) => {
					if (isLoading) {
						return (props) => (
							<animated.p
								style={props}
								className="ct-loading-text">
								
								<svg width="16" height="16" viewBox="0 0 100 100">

									<g transform="translate(50,50)">
										<g transform="scale(1)">
											<circle cx="0" cy="0" r="50" fill="currentColor"></circle>
											<circle cx="0" cy="-26" r="12" fill="#ffffff" transform="rotate(161.634)">
												<animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 0 0;360 0 0" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform>
											</circle>
										</g>
									</g>
								</svg>

								{__('Loading Plugins Status...', 'blocksy')}
							</animated.p>
						)
					}

					return (props) => (
						<animated.div style={props}>
							{plugins.length > 0 && (
								<Fragment>
									<ul className="ct-recommended-plugins-list">
										{plugins.map((plugin) => (
											<SinglePremiumPlugin
												plugin={plugin}
												key={plugin.name}
												onPluginsSync={() =>
													syncPlugins()
												}
												status={
													(
														plugins_status.find(
															({ name }) =>
																name ===
																plugin.name
														) || {}
													).status
												}
											/>
										))}
									</ul>

									<SubmitSupport />
								</Fragment>
							)}
						</animated.div>
					)
				}}
			</Transition>
		</div>
	)
}

export default RecommendedPlugins
