import {
	useState,
	useMemo,
	createElement,
	useRef,
	useEffect,
	RawHTML,
} from '@wordpress/element'

import { useDispatch } from '@wordpress/data'
import { Modal, SearchControl } from '@wordpress/components'
import { useAsyncList } from '@wordpress/compose'
import {
	BlockContextProvider,
	store as blockEditorStore,
	__experimentalBlockPatternsList as BlockPatternsList,
} from '@wordpress/block-editor'
import { __ } from 'ct-i18n'

import { parse } from '@wordpress/blocks'

import { getPostsPatterns } from '../hooks/use-posts-patterns'

/**
 * Internal dependencies
 */
import {
	useBlockNameForPatterns,
	getTransformedBlocksFromPattern,
	usePatterns,
} from './utils/utils'
import { searchPatterns } from './utils/search-patterns'

const PatternSelectionModal = ({
	clientId,
	attributes,
	setIsPatternSelectionModalOpen,
	postType,
}) => {
	const [searchValue, setSearchValue] = useState('')
	const { replaceBlock, selectBlock } = useDispatch(blockEditorStore)

	const [blockPatterns, setBlockPatterns] = useState([])

	useEffect(() => {
		getPostsPatterns().then((patterns) => {
			setBlockPatterns(patterns)
		})
	}, [])

	const onBlockPatternSelect = (pattern, blocks) => {
		const { newBlocks, queryClientIds } = getTransformedBlocksFromPattern(
			blocks,
			attributes
		)

		replaceBlock(clientId, newBlocks)

		if (queryClientIds[0]) {
			selectBlock(queryClientIds[0])
		}
	}

	// When we preview Query Loop blocks we should prefer the current
	// block's postType, which is passed through block context.
	const blockPreviewContext = useMemo(
		() => ({
			previewPostType: postType,
		}),
		[postType]
	)

	const filteredBlockPatterns = useMemo(() => {
		return searchPatterns(blockPatterns, searchValue)
	}, [blockPatterns, searchValue])

	const shownBlockPatterns = useAsyncList(filteredBlockPatterns)

	return (
		<Modal
			overlayClassName="block-library-query-pattern__selection-modal"
			title={__('Choose a pattern', 'blocksy')}
			onRequestClose={() => setIsPatternSelectionModalOpen(false)}
			isFullScreen={true}>
			<div className="block-library-query-pattern__selection-content">
				<div className="block-library-query-pattern__selection-search">
					<SearchControl
						__nextHasNoMarginBottom
						onChange={setSearchValue}
						value={searchValue}
						label={__('Search for patterns', 'blocksy')}
						placeholder={__('Search', 'blocksy')}
					/>
				</div>

				<BlockContextProvider value={blockPreviewContext}>
					<BlockPatternsList
						blockPatterns={filteredBlockPatterns}
						shownPatterns={shownBlockPatterns}
						onClickPattern={onBlockPatternSelect}
					/>
				</BlockContextProvider>
			</div>
		</Modal>
	)
}

export default PatternSelectionModal
