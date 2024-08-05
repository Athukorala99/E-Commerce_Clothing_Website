import { createElement } from '@wordpress/element'

import { __experimentalGrid as Grid } from '@wordpress/components'
import {
	DropdownMenu,
	MenuItem,
	MenuGroup,
	__experimentalHStack as HStack,
	__experimentalHeading as Heading,
} from '@wordpress/components'

import { check, moreVertical, plus } from '@wordpress/icons'
import { speak } from '@wordpress/a11y'

import { __ } from 'ct-i18n'

const ToolsPanelHeader = ({
	items,

	selectedItems,

	onItemClick,

	label,
	resetAll,
}) => {
	const canResetAll = selectedItems.length > 0

	return (
		<HStack>
			<Heading
				style={{
					margin: 0,
				}}
				level={2}>
				{label}
			</Heading>

			<DropdownMenu
				icon={selectedItems.length === 0 ? plus : moreVertical}
				label={__('Parameters options', 'blocksy')}
				toggleProps={{
					isSmall: true,
					describedBy:
						selectedItems.length === 0
							? __('All options are currently hidden', 'blocksy')
							: undefined,
				}}>
				{() => (
					<>
						{items.map((item, index) => {
							return (
								<MenuGroup key={index} label={item.label}>
									{item.items.map((item, index) => {
										const isSelected =
											selectedItems.includes(item.label)

										return (
											<MenuItem
												key={index}
												icon={isSelected ? check : null}
												isSelected={isSelected}
												onClick={() =>
													onItemClick(item.label)
												}>
												{item.label}
											</MenuItem>
										)
									})}
								</MenuGroup>
							)
						})}

						<MenuGroup>
							<MenuItem
								aria-disabled={!canResetAll}
								variant={'tertiary'}
								onClick={() => {
									if (canResetAll) {
										resetAll()
										speak(
											__('All options reset', 'blocksy'),
											'assertive'
										)
									}
								}}>
								{__('Reset all', 'blocksy')}
							</MenuItem>
						</MenuGroup>
					</>
				)}
			</DropdownMenu>
		</HStack>
	)
}

export default ToolsPanelHeader
