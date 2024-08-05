import {
	createElement,
	Component,
	useEffect,
	useState,
	useMemo,
	createContext,
	Fragment,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import classnames from 'classnames'
import useActivationAction from '../helpers/useActivationAction'
import { Transition, animated } from 'blocksy-options'
import DemosList from './DemoInstall/DemosList'
import DemoToInstall from './DemoInstall/DemoToInstall'

import SiteExport from './SiteExport'

export const DemosContext = createContext({
	demos: [],
})

import SubmitSupport from '../helpers/SubmitSupport'

let demos_cache = null
let plugins_cache = null
let currently_installed_demo_cache = null
let demos_error_cache = null

const DemoInstall = ({ children, path, location }) => {
	const [isLoading, setIsLoading] = useState(!demos_cache)
	const [demos_list, setDemosList] = useState(demos_cache || [])
	const [pluginsStatus, setPluginsStatus] = useState(plugins_cache || {})
	const [currentDemo, setCurrentDemo] = useState(null)
	const [currentlyInstalledDemo, setCurrentlyInstalledDemo] = useState(
		currently_installed_demo_cache
	)

	const [demo_error, setDemoError] = useState(false)

	const [demoConfiguration, setDemoConfiguration] = useState({
		builder: '',
	})

	const [installerBlockingReleased, setInstallerBlockingReleased] =
		useState(false)

	const syncDemos = async (verbose = false) => {
		if (verbose) {
			setIsLoading(true)
		}

		const body = new FormData()
		body.append('action', 'blocksy_demo_list')

		try {
			const response = await fetch(ctDashboardLocalizations.ajax_url, {
				method: 'POST',
				body,
			})

			if (response.status === 200) {
				const { success, data } = await response.json()

				if (success) {
					setDemosList(data.demos)
					setPluginsStatus(data.active_plugins)
					setCurrentlyInstalledDemo(data.current_installed_demo)
					setDemoError(data.demo_error)
					plugins_cache = data.active_plugins
					demos_cache = data.demos
					demos_error_cache = data.demo_error
				}
			}
		} catch (e) {}

		setIsLoading(false)
	}

	useEffect(() => {
		syncDemos(!demos_cache)
	}, [])

	return (
		<div className="ct-demos-list-container">
			{demo_error && (
				<div
					className="ct-demo-notification"
					dangerouslySetInnerHTML={{
						__html: demo_error,
					}}
				/>
			)}
			<Transition
				items={isLoading}
				from={{ opacity: 0 }}
				enter={[{ opacity: 1 }]}
				leave={[{ opacity: 0 }]}
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
								<svg
									width="16"
									height="16"
									viewBox="0 0 100 100">
									<g transform="translate(50,50)">
										<g transform="scale(1)">
											<circle
												cx="0"
												cy="0"
												r="50"
												fill="currentColor"></circle>
											<circle
												cx="0"
												cy="-26"
												r="12"
												fill="#ffffff"
												transform="rotate(161.634)">
												<animateTransform
													attributeName="transform"
													type="rotate"
													calcMode="linear"
													values="0 0 0;360 0 0"
													keyTimes="0;1"
													dur="1s"
													begin="0s"
													repeatCount="indefinite"></animateTransform>
											</circle>
										</g>
									</g>
								</svg>

								{__(
									'Loading Starter Sites...',
									'blocksy-companion'
								)}
							</animated.p>
						)
					}

					if (demos_list.length === 0) {
						return (props) => (
							<animated.div style={props}>
								<div
									className="ct-demo-notification"
									dangerouslySetInnerHTML={{
										__html: __(
											"The connection to our <b>demo.creativethemes.com</b> server didn't worked. This connection is required for importing the starter sites from our demo content server. All you have to do is to contact your hosting provider and ask them to white list our demo server address.",
											'blocksy-companion'
										),
									}}
								/>
								<SubmitSupport />
							</animated.div>
						)
					}

					return (props) => (
						<animated.div style={props}>
							<Fragment>
								<DemosContext.Provider
									value={{
										demo_error,
										demos_list: demos_list.filter(
											(ext) =>
												!ext.dev_v2 ||
												ct_localizations.is_dev_mode
										),
										currentDemo,
										pluginsStatus,
										installerBlockingReleased,
										setInstallerBlockingReleased,
										setCurrentDemo,
										currentlyInstalledDemo,
										setCurrentlyInstalledDemo,
									}}>
									<DemosList />
									<DemoToInstall />
									<SiteExport />
								</DemosContext.Provider>
								<SubmitSupport />
							</Fragment>
						</animated.div>
					)
				}}
			</Transition>
		</div>
	)
}

export default DemoInstall
