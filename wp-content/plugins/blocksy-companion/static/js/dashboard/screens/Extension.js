import {
	useEffect,
	createElement,
	Fragment,
	useContext,
} from '@wordpress/element'
import { Transition, animated } from 'blocksy-options'

import { __ } from 'ct-i18n'

import useExtsStatus from './Extensions/useExtsStatus'

import Sidebar from './Extensions/Sidebar'
import CurrentExtension from './Extensions/CurrentExtension'
import SubmitSupport from '../helpers/SubmitSupport'

import DashboardContext from '../DashboardContext'

const Extension = (props) => {
	const { history } = useContext(DashboardContext)

	const { navigate } = props
	const { exts_status, syncExts, isLoading, setExtsStatus } = useExtsStatus()

	let currentExtension = null

	if (exts_status[props.extension]) {
		currentExtension = {
			...exts_status[props.extension],
			name: props.extension,
		}
	}

	return (
		<div className="ct-extensions-container">
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
									'Loading Extensions Status...',
									'blocksy-companion'
								)}
							</animated.p>
						)
					}

					return (props) => (
						<animated.div style={props}>
							<section className="ct-extensions-list">
								<Sidebar
									currentExtension={currentExtension}
									exts_status={exts_status}
									navigate={navigate}
								/>
								<CurrentExtension
									navigate={navigate}
									currentExtension={currentExtension}
									setExtsStatus={setExtsStatus}
									onExtsSync={(payload = {}) => {
										return syncExts({
											...payload,
											extension: currentExtension.name,
										})
									}}
								/>
							</section>

							<Fragment>
								<SubmitSupport />
							</Fragment>
						</animated.div>
					)
				}}
			</Transition>
		</div>
	)
}

export default Extension
