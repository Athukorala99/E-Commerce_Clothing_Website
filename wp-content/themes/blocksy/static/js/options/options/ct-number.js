import { createElement } from '@wordpress/element'

import BlocksyNumberOption from './ct-number/BlocksyNumber'
import GutenbergNumberOption from './ct-number/GutenbergNumber'

const NumberOption = (props) => {
	const { purpose } = props

	if (purpose === 'gutenberg') {
		return <GutenbergNumberOption {...props} />
	}

	return <BlocksyNumberOption {...props} />
}

NumberOption.supportedPurposes = ['default', 'gutenberg']

export default NumberOption
