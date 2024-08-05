import {
	useState,
	useEffect,
	createElement,
	useMemo,
	RawHTML,
} from '@wordpress/element'

import { withSelect, withDispatch } from '@wordpress/data'
import { Spinner } from '@wordpress/components'

import { __ } from 'ct-i18n'

import { usePostsBlockData } from './hooks/use-posts-block-data'

const Preview = ({ attributes, postId, uniqueId }) => {
	const { blockData } = usePostsBlockData({
		attributes,
		previewedPostId: postId,
	})

	if (!blockData || !blockData.block) {
		return <Spinner />
	}

	return (
		<>
			<RawHTML>{blockData.block}</RawHTML>
			{blockData && blockData.dynamic_styles && (
				<style>{blockData.dynamic_styles}</style>
			)}
		</>
	)
}

export default Preview
