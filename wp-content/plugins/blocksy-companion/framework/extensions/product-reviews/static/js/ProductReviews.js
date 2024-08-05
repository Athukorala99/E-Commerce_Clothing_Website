import {
	createElement,
	Component,
	useEffect,
	useState,
	Fragment,
} from '@wordpress/element'
import ctEvents from 'ct-events'

import { OptionsPanel } from 'blocksy-options'
import nanoid from 'nanoid'

import classnames from 'classnames'
import { __, sprintf } from 'ct-i18n'
import Overlay from '../../../../../static/js/helpers/Overlay'

const ProductReviews = ({ setExtsStatus, extension, onExtsSync }) => {
	const [settings, setSettings] = useState(null)

	return (
		<div className={classnames('ct-extension-options ct-product-reviews-options')}>
			<h4>{__('Product Reviews Settings', 'blocksy-companion')}</h4>

			<p className="ct-modal-description">
				{__(
					'Configure the slugs for single and category pages of the product review custom post type.',
					'blocksy-companion'
				)}
			</p>

			<form>
				<OptionsPanel
					onChange={(optionId, optionValue) =>
						setSettings((settings) => ({
							...settings,
							[optionId]: optionValue,
						}))
					}
					options={{
						single_slug: {
							type: 'text',
							value: '',
							label: __('Single Slug', 'blocksy-companion'),
						},

						category_slug: {
							type: 'text',
							value: '',
							label: __('Category Slug', 'blocksy-companion'),
						},
					}}
					value={{
						...extension.data.settings,
						...(settings || {}),
					}}
					hasRevertButton={false}
				/>

				<button
					className="ct-button-primary"
					disabled={!settings}
					onClick={(e) => {
						e.preventDefault()

						if (!settings) {
							return
						}

						const newSettings = {
							...extension.data.settings,
							...settings,
						}

						setExtsStatus((extStatus) => ({
							...extStatus,
							[extension.name]: {
								...extStatus[extension.name],
								data: {
									...extStatus[extension.name].data,
									settings: newSettings,
								},
							},
						}))

						onExtsSync({
							extAction: {
								type: 'persist',
								settings: newSettings,
							},
						})

						setSettings(null)
					}}>
					{__('Save Settings', 'blocksy-companion')}
				</button>
			</form>
		</div>
	)
}

export default ProductReviews
