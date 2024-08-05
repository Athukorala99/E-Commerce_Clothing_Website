/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data'
import { useMemo } from '@wordpress/element'
import { store as coreStore } from '@wordpress/core-data'
import { store as blockEditorStore } from '@wordpress/block-editor'
import { decodeEntities } from '@wordpress/html-entities'
import { cloneBlock, store as blocksStore } from '@wordpress/blocks'

/**
 * Clones a pattern's blocks and then recurses over that list of blocks,
 * transforming them to retain some `query` attribute properties.
 * For now we retain the `postType` and `inherit` properties as they are
 * fundamental for the expected functionality of the block and don't affect
 * its design and presentation.
 *
 * Returns the cloned/transformed blocks and array of existing Query Loop
 * client ids for further manipulation, in order to avoid multiple recursions.
 *
 * @param {WPBlock[]}        blocks               The list of blocks to look through and transform(mutate).
 * @param {Record<string,*>} queryBlockAttributes The existing Query Loop's attributes.
 * @return {{ newBlocks: WPBlock[], queryClientIds: string[] }} An object with the cloned/transformed blocks and all the Query Loop clients from these blocks.
 */
export const getTransformedBlocksFromPattern = (
	blocks,
	queryBlockAttributes
) => {
	const clonedBlocks = blocks.map((block) => cloneBlock(block))
	const queryClientIds = []
	const blocksQueue = [...clonedBlocks]

	while (blocksQueue.length > 0) {
		const block = blocksQueue.shift()

		if (block.name === 'blocksy/query') {
			block.attributes.uniqueId = ''

			queryClientIds.push(block.clientId)
		}

		block.innerBlocks?.forEach((innerBlock) => {
			blocksQueue.push(innerBlock)
		})
	}

	return { newBlocks: clonedBlocks, queryClientIds }
}

export function useBlockNameForPatterns(clientId, attributes) {
	return 'blocksy/query'
}

/**
 * Hook that returns the block patterns for a specific block type.
 *
 * @param {string} clientId The block's client ID.
 * @param {string} name     The block type name.
 * @return {Object[]} An array of valid block patterns.
 */
export const usePatterns = (clientId, name) => {
	return useSelect(
		(select) => {
			const { getBlockRootClientId, getPatternsByBlockTypes } =
				select(blockEditorStore)

			const rootClientId = getBlockRootClientId(clientId)

			return getPatternsByBlockTypes(name, rootClientId)
		},
		[name, clientId]
	)
}

/**
 * Returns a helper object that contains:
 * 1. An `options` object from the available post types, to be passed to a `SelectControl`.
 * 2. A helper map with available taxonomies per post type.
 *
 * @return {Object} The helper object related to post types.
 */
export const usePostTypes = (args = {}) => {
	let { hasPages = false } = args

	const postTypes = useSelect(
		(select) => {
			const { getPostTypes } = select(coreStore)

			const excludedPostTypes = [
				'attachment',
				'product',
				...(hasPages ? [] : ['page']),
			]

			const filteredPostTypes = getPostTypes({ per_page: -1 })?.filter(
				({ viewable, slug }) =>
					viewable &&
					!excludedPostTypes.includes(slug) &&
					!slug.includes('ct_') &&
					!slug.includes('blc-')
			)

			return filteredPostTypes
		},
		[hasPages]
	)

	const postTypesTaxonomiesMap = useMemo(() => {
		if (!postTypes?.length) return

		return postTypes.reduce((accumulator, type) => {
			accumulator[type.slug] = type.taxonomies
			return accumulator
		}, {})
	}, [postTypes])

	const postTypesSelectOptions = useMemo(
		() =>
			(postTypes || []).reduce((accumulator, type) => {
				return {
					...accumulator,
					[type.slug]: type.labels.singular_name,
				}
			}, {}),
		[postTypes]
	)

	return { postTypesTaxonomiesMap, postTypesSelectOptions }
}

export const useTaxonomies = (postType) => {
	const taxonomies = useSelect(
		(select) => {
			const { getTaxonomies } = select(coreStore)

			const filteredTaxonomies = getTaxonomies({
				type: postType,
				per_page: -1,
				context: 'view',
			})

			return filteredTaxonomies
		},
		[postType]
	)

	return taxonomies
}
