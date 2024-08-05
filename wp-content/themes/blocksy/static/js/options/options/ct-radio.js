import { createElement } from '@wordpress/element'

import BlocksyRadio from './ct-radio/BlocksyRadio'
import GutenbergRadio from './ct-radio/GutenbergRadio'

const Radio = (props) => {
	const { purpose } = props

	if (purpose === 'gutenberg') {
		return <GutenbergRadio {...props} />
	}

	return <BlocksyRadio {...props} />
}

Radio.supportedPurposes = ['default', 'gutenberg']

export default Radio
