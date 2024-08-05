import { createElement, useRef, useState } from '@wordpress/element'
import classnames from 'classnames'

import OptionsPanel from '../../options/OptionsPanel'
import { getValueFromInput } from '../../options/helpers/get-value-from-input'
import PanelLevel from '../../options/components/PanelLevel'
import { DeviceManagerProvider } from '../components/useDeviceManager'

import { CustomizerValues } from './customizer-values-context'

const Options = ({ option, renderOptions = null }) => {
	const [values, setValues] = useState(null)

	const containerRef = useRef()

	const onChangeFor = (key, val) => {
		setValues((values) => {
			return {
				...(values ||
					getValueFromInput(option['inner-options'], {}, (id) => ({
						[id]: wp.customize(id) && wp.customize(id)(),
					}))),
				[key]: val,
			}
		})

		wp.customize(key) && wp.customize(key).set(val)
	}

	const computedValues =
		values ||
		getValueFromInput(option['inner-options'], {}, (id) => ({
			[id]: wp.customize(id) && wp.customize(id)(),
		}))

	return (
		<CustomizerValues.Provider
			value={{
				values: computedValues,
				onChange: onChangeFor,
			}}>
			<DeviceManagerProvider>
				<div className="ct-options-container" ref={containerRef}>
					<PanelLevel containerRef={containerRef}>
						<OptionsPanel
							renderOptions={renderOptions}
							purpose="customizer"
							onChange={(key, val) => {
								onChangeFor(key, val)
							}}
							options={option['inner-options']}
							value={computedValues}
						/>
					</PanelLevel>
				</div>
			</DeviceManagerProvider>
		</CustomizerValues.Provider>
	)
}

export default Options
