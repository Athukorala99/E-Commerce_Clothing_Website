import { createElement, Component } from '@wordpress/element'
import classnames from 'classnames'

import BlocksySwitch from './ct-switch/BlocksySwitch'
import GutenbergSwitch from './ct-switch/GutenbergSwitch'

const Switch = (props) => {
	const { purpose } = props

	if (purpose === 'gutenberg') {
		return <GutenbergSwitch {...props} />
	}

	return <BlocksySwitch {...props} />
}

Switch.renderingConfig = {
	design: 'inline',
}

Switch.supportedPurposes = ['default', 'gutenberg']

export default Switch
