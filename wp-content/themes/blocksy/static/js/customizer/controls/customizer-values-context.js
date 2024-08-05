import { useContext, createContext } from '@wordpress/element'

export const CustomizerValues = createContext({
	onChange: (key, value) => {},
	values: {},
})

export const useCustomizerValues = () => {
	const { onChange, values } = useContext(CustomizerValues)
	return [values, onChange]
}
