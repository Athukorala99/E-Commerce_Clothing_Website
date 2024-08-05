import { createElement } from '@wordpress/element'

import BlocksyText from './text/BlocksyText'
import GutenbergText from './text/GutenbergText'

const Text = (props) => {
	const { purpose } = props

	if (purpose === 'gutenberg') {
		return <GutenbergText {...props} />
	}

	return <BlocksyText {...props} />
}

Text.supportedPurposes = ['default', 'gutenberg']

export default Text
