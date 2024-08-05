import {
	createElement,
	Component,
	useEffect,
	useMemo,
	useState,
	Fragment,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import { Transition, animated } from 'blocksy-options'

import useExtsStatus from './Extensions/useExtsStatus'

const Extensions = ({ navigate }) => {
	const { exts_status } = useExtsStatus()

	useEffect(() => {
		if (Object.keys(exts_status).length > 0) {
			navigate(`/extensions/${Object.keys(exts_status)[0]}`)
		}
	}, [exts_status])

	return (
		<div className="ct-extensions-container">
			<Transition
				items={true}
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

								{__(
									'Loading Extensions Status...',
									'blocksy-companion'
								)}
							</animated.p>
						)
					}

					return null
				}}
			</Transition>
		</div>
	)
}

export default Extensions
