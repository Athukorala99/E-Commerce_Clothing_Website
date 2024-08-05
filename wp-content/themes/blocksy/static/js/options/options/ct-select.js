import {
	createPortal,
	useState,
	useEffect,
	useRef,
	createElement,
	Fragment,
} from '@wordpress/element'
import { maybeTransformUnorderedChoices } from '../helpers/parse-choices'
import Downshift from 'downshift'
import classnames from 'classnames'
import { __ } from 'ct-i18n'

import usePopoverMaker from '../helpers/usePopoverMaker'

import BlocksySelect from './ct-select/BlocksySelect'
import GutenbergSelect from './ct-select/GutenbergSelect'

const Select = (props) => {
	const { purpose } = props

	if (purpose === 'gutenberg') {
		return <GutenbergSelect {...props} />
	}

	return <BlocksySelect {...props} />
}

Select.supportedPurposes = ['default', 'gutenberg']

export default Select
