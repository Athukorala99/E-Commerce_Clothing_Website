import { useEffect } from '@wordpress/element'
import { select } from '@wordpress/data'

export const getAttributes = () => {
	return {
		uniqueId: {
			type: 'string',
			default: '',
		},
	}
}

const shortenId = (clientId) => clientId.split('-')[0]

export function getBlockDocumentRoot(props) {
	const iframes = document.querySelectorAll(
		'.edit-site-visual-editor__editor-canvas'
	)
	let _document = document

	// check for block editor iframes
	for (let i = 0; i < iframes.length; i++) {
		let block = iframes[i].contentDocument.getElementById(
			'block-' + props.clientId
		)

		if (block !== null) {
			_document = iframes[i].contentDocument
			break
		}
	}

	return _document
}

function isDuplicate(props) {
	let output = false

	const _document = getBlockDocumentRoot(props)
	const elements = _document.querySelectorAll(
		`[data-id="${props.attributes.uniqueId}"]`
	)

	if (elements.length > 1) {
		output = true
	}

	return output
}

// Add support for this, otherwise IDs will be duplicated on clone
// https://github.com/WordPress/gutenberg/pull/38643
export const useUniqueId = ({ attributes, clientId, setAttributes }) => {
	useEffect(() => {
		if (!attributes.uniqueId || isDuplicate({ attributes, clientId })) {
			setAttributes({
				uniqueId: shortenId(clientId),
			})
		}
	}, [clientId])

	let uniqueId = attributes.uniqueId || shortenId(clientId)

	return {
		uniqueId,
		props: {
			'data-id': uniqueId,
		},
	}
}

export const useSaveUniqueId = (props) => {
	const { attributes, clientId } = props

	return {
		uniqueId: attributes.uniqueId || clientId || '',

		props: attributes.uniqueId
			? {
					'data-id': attributes.uniqueId,
			  }
			: {},
	}
}
