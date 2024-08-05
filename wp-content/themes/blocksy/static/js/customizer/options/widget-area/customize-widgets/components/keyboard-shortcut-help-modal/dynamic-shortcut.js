/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data'
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts'
import { createElement } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Shortcut from './shortcut'

function DynamicShortcut({ name }) {
	const { keyCombination, description, aliases } = useSelect(
		(select) => {
			const {
				getShortcutKeyCombination,
				getShortcutDescription,
				getShortcutAliases,
			} = select(keyboardShortcutsStore)

			return {
				keyCombination: getShortcutKeyCombination(name),
				aliases: getShortcutAliases(name),
				description: getShortcutDescription(name),
			}
		},
		[name]
	)

	if (!keyCombination) {
		return null
	}

	return (
		<Shortcut
			keyCombination={keyCombination}
			description={description}
			aliases={aliases}
		/>
	)
}

export default DynamicShortcut
