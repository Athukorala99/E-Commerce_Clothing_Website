import { store as blockEditorStore } from '@wordpress/block-editor'
import { store as coreStore, useEntityProp } from '@wordpress/core-data'
import { __, sprintf } from '@wordpress/i18n'
import { useSelect } from '@wordpress/data'

function useDefaultAvatar() {
	const { avatarURL: defaultAvatarUrl } = useSelect((select) => {
		const { getSettings } = select(blockEditorStore)
		const { __experimentalDiscussionSettings } = getSettings()
		return __experimentalDiscussionSettings
	})

	return defaultAvatarUrl
}

export function useUserAvatar({ postId, postType }) {
	const { authorDetails } = useSelect(
		(select) => {
			const { getEditedEntityRecord, getUser } = select(coreStore)

			const _authorId = getEditedEntityRecord(
				'postType',
				postType,
				postId
			)?.author

			return {
				authorDetails: _authorId ? getUser(_authorId) : null,
			}
		},
		[postType, postId]
	)

	const avatarUrls = authorDetails?.avatar_urls
		? Object.values(authorDetails.avatar_urls)
		: null

	const sizes = authorDetails?.avatar_urls
		? Object.keys(authorDetails.avatar_urls)
		: null

	const defaultAvatar = useDefaultAvatar()

	return {
		src: avatarUrls ? avatarUrls[avatarUrls.length - 1] : defaultAvatar,
	}
}
