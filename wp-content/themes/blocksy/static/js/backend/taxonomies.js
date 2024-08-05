import { __ } from 'ct-i18n'
import {
	useRef,
	useState,
	Fragment,
	createElement,
	createPortal,
	useEffect,
	render,
} from '@wordpress/element'

import $ from 'jquery'

import OptionsPanel from '../options/OptionsPanel'

import { getValueFromInput } from '../options/helpers/get-value-from-input'

const TaxonomyRoot = ({ options, input_name, value, tbody, purpose }) => {
	const [internalValue, setInternalValue] = useState(value)
	const input = useRef()

	useEffect(() => {
		const cb = () => {
			setInternalValue(value)
		}

		$(document).on('ajaxComplete', cb)

		return () => {
			$(document).off('ajaxComplete', cb)
		}
	}, [])

	return (
		<Fragment>
			<input
				value={JSON.stringify(
					Array.isArray(internalValue) ? {} : internalValue
				)}
				onChange={() => {}}
				name={input_name}
				type="hidden"
				ref={input}
			/>

			{createPortal(
				<OptionsPanel
					value={internalValue}
					options={options}
					onChange={(key, newValue) => {
						setInternalValue((internalValue) => ({
							...internalValue,
							[key]: newValue,
						}))
						$(input.current).change()
					}}
					purpose={purpose}
				/>,
				tbody
			)}
		</Fragment>
	)
}

export const initTaxonomies = () => {
	const maybeTaxonomyField = document.querySelector(
		'[name*="blocksy_taxonomy_meta_options"]'
	)

	if (!maybeTaxonomyField) {
		return
	}
	if (!maybeTaxonomyField.dataset.options) {
		return
	}

	let options = JSON.parse(maybeTaxonomyField.dataset.options)

	const maybeTable = document.querySelector('.form-table')

	let tbody = null
	let purpose = 'taxonomy'

	if (!maybeTable) {
		const rootEl = document.querySelector('#addtag')

		tbody = document.createElement('div')
		tbody.classList.add('form-field', 'ct-term-screen-create')
		tbody.textContent = ''

		rootEl.insertBefore(tbody, rootEl.querySelector('.submit'))
		purpose = 'default'
	} else {
		tbody = maybeTable.querySelector('tbody')
	}

	render(
		<TaxonomyRoot
			input_name={maybeTaxonomyField.name}
			options={options}
			tbody={tbody}
			value={getValueFromInput(
				options,
				JSON.parse(maybeTaxonomyField.value),
				null,
				false
			)}
			purpose={purpose}
		/>,
		maybeTaxonomyField.parentNode
	)
}
