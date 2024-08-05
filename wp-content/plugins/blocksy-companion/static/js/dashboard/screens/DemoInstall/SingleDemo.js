import {
	createElement,
	Component,
	useEffect,
	useState,
	createContext,
	useContext,
	Fragment,
} from '@wordpress/element'

import classnames from 'classnames'
import { __ } from 'ct-i18n'

import { getNameForPlugin } from './Wizzard/Plugins'

import { DemosContext } from '../DemoInstall'
import useProExtensionInFree from '../../helpers/useProExtensionInFree'

const SingleDemo = ({ demo }) => {
	const {
		currentlyInstalledDemo,
		demos_list,
		setCurrentDemo,
		demo_error,
		setInstallerBlockingReleased,
	} = useContext(DemosContext)

	const { isProInFree, showNotice, content } = useProExtensionInFree(
		{
			config: {
				pro: !!demo.is_pro,

				...(demo.plans
					? {
							plans: demo.plans,
					  }
					: {}),
			},
		},

		{
			strategy: 'pro',

			personal: {
				title: __('This is a Pro starter site', 'blocksy-companion'),
				description: __(
					'Upgrade to any pro plan and get instant access to this starter site and many other features.',
					'blocksy-companion'
				),
			},

			professional: {
				description: __(
					'Upgrade to the professional or agency plan and get instant access to this starter site and many other features.',
					'blocksy-companion'
				),
			},

			agency: {
				description: __(
					'Upgrade to the agency plan and get instant access to this starter site and many other features.',
					'blocksy-companion'
				),
			},
		}
	)

	return (
		<Fragment>
			<li
				className={classnames('ct-single-demo', {
					'ct-is-pro': demo.is_pro,
				})}>
				<figure>
					<img src={demo.screenshot} />

					<section>
						<h3>{__('Available for', 'blocksy-companion')}</h3>
						<div>
							{demos_list
								.filter(({ name }) => name === demo.name || '')

								.sort((a, b) => {
									if (a.builder < b.builder) {
										return -1
									}

									if (a.builder > b.builder) {
										return 1
									}

									return 0
								})
								.map(({ builder }) => (
									<span key={builder}>
										{getNameForPlugin(builder) ||
											'Gutenberg'}
									</span>
								))}
						</div>
					</section>

					{demo.is_pro && (
						<a onClick={(e) => e.preventDefault()} href="#">
							PRO
						</a>
					)}
				</figure>

				<div className="ct-demo-actions">
					<h4>{demo.name}</h4>

					<div>
						<a
							className="ct-button"
							target="_blank"
							href={demo.url}>
							{__('Preview', 'blocksy-companion')}
						</a>
						<button
							className="ct-button-primary"
							onClick={() => {
								if (isProInFree) {
									showNotice()
									return
								} else {
									setInstallerBlockingReleased(false)
									setCurrentDemo(demo.name)
								}
							}}
							disabled={!!demo_error}>
							{currentlyInstalledDemo &&
							currentlyInstalledDemo.demo.indexOf(demo.name) > -1
								? __('Modify', 'blocksy-companion')
								: __('Import', 'blocksy-companion')}
						</button>
					</div>
				</div>
			</li>

			{content}
		</Fragment>
	)
}

export default SingleDemo
