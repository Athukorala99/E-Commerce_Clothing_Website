import {
	createElement,
	Component,
	Fragment,
	createContext,
	useRef,
	useContext,
	useState,
} from '@wordpress/element'
import SinglePicker from './color-picker/single-picker'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'

const ColorPicker = ({ option, values, value, onChange, device }) => {
	const [{ isPicking, isTransitioning }, setState] = useState({
		isPicking: null,
		isTransitioning: null,
	})

	const containerRef = useRef()
	const modalRef = useRef()

	return (
		<div ref={containerRef} className="ct-color-picker-container">
			{option.pickers
				.filter(
					(picker) =>
						!picker.condition ||
						matchValuesWithCondition(
							normalizeCondition(picker.condition),
							picker.condition_source === 'global'
								? Object.keys(picker.condition).reduce(
										(current, key) => ({
											...current,
											[key]: wp.customize(key)(),
										}),
										{}
								  )
								: values
						)
				)
				.map((picker) => (
					<SinglePicker
						containerRef={containerRef}
						device={device}
						picker={picker}
						key={picker.id}
						option={option}
						isPicking={isPicking}
						modalRef={modalRef}
						isTransitioning={isTransitioning}
						values={values}
						onOutsideClick={() => {
							setState(({ isPicking }) => ({
								isPicking: null,
								isTransitioning: isPicking,
							}))
						}}
						onPickingChange={(isPicking) =>
							setState({
								isTransitioning: picker.id,
								isPicking,
							})
						}
						stopTransitioning={() =>
							setState((state) => ({
								...state,
								isTransitioning: false,
							}))
						}
						onChange={(newPicker) =>
							onChange({
								...value,
								[picker.id]: newPicker,
							})
						}
						value={value[picker.id] || option.value[picker.id]}
					/>
				))}
		</div>
	)
}

export default ColorPicker
