/**
 * WordPress dependencies
 */
import { createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'
import {
	SelectControl,
	__experimentalUnitControl as UnitControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalUseCustomUnits as useCustomUnits,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components'
import { useSetting } from '@wordpress/block-editor'
import { useSelect } from '@wordpress/data'

import { store as blockEditorStore } from '@wordpress/block-editor'

const DEFAULT_SIZE = 'full'

const DimensionControls = ({
	clientId,
	attributes: { aspectRatio, width, height, sizeSlug },
	setAttributes,
}) => {
	const imageSizes = useSelect(
		(select) => select(blockEditorStore).getSettings().imageSizes,
		[]
	)
	const imageSizeOptions = imageSizes.map(({ name, slug }) => ({
		value: slug,
		label: name,
	}))

	const defaultUnits = ['px', '%', 'vw', 'em', 'rem']
	const units = useCustomUnits({
		availableUnits: useSetting('spacing.units') || defaultUnits,
	})

	const onDimensionChange = (dimension, nextValue) => {
		const parsedValue = parseFloat(nextValue)
		/**
		 * If we have no value set and we change the unit,
		 * we don't want to set the attribute, as it would
		 * end up having the unit as value without any number.
		 */
		if (isNaN(parsedValue) && nextValue) return

		setAttributes({
			[dimension]: parsedValue < 0 ? '0' : nextValue,
		})
	}

	return (
		<ToolsPanel
			label={__('Image Settings', 'blocksy')}
			resetAll={() => {
				setAttributes({
					aspectRatio: 'auto',
					width: undefined,
					height: undefined,
					sizeSlug: undefined,
				})
			}}>
			<ToolsPanelItem
				hasValue={() => !!aspectRatio}
				label={__('Aspect Ratio', 'blocksy')}
				onDeselect={() => setAttributes({ aspectRatio: undefined })}
				resetAllFilter={() => ({
					aspectRatio: 'auto',
				})}
				isShownByDefault
				key={clientId}>
				<SelectControl
					__nextHasNoMarginBottom
					label={__('Aspect Ratio', 'blocksy')}
					value={aspectRatio}
					options={[
						// These should use the same values as AspectRatioDropdown in @wordpress/block-editor
						{
							label: __('Original', 'blocksy'),
							value: 'auto',
						},
						{
							label: __('Square', 'blocksy'),
							value: '1',
						},
						{
							label: __('16:9', 'blocksy'),
							value: '16/9',
						},
						{
							label: __('4:3', 'blocksy'),
							value: '4/3',
						},
						{
							label: __('3:2', 'blocksy'),
							value: '3/2',
						},
						{
							label: __('9:16', 'blocksy'),
							value: '9/16',
						},
						{
							label: __('3:4', 'blocksy'),
							value: '3/4',
						},
						{
							label: __('2:3', 'blocksy'),
							value: '2/3',
						},
					]}
					onChange={(nextAspectRatio) =>
						setAttributes({ aspectRatio: nextAspectRatio })
					}
				/>
			</ToolsPanelItem>
			<ToolsPanelItem
				style={{
					'grid-column': 'span 1 / auto',
				}}
				hasValue={() => !!width}
				label={__('Width', 'blocksy')}
				onDeselect={() => setAttributes({ width: undefined })}
				resetAllFilter={() => ({
					width: undefined,
				})}
				isShownByDefault
				key={clientId}>
				<UnitControl
					label={__('Width', 'blocksy')}
					labelPosition="top"
					value={width || ''}
					min={0}
					onChange={(nextWidth) =>
						onDimensionChange('width', nextWidth)
					}
					units={units}
				/>
			</ToolsPanelItem>
			<ToolsPanelItem
				style={{
					'grid-column': 'span 1 / auto',
				}}
				hasValue={() => !!height}
				label={__('Height', 'blocksy')}
				onDeselect={() => setAttributes({ height: undefined })}
				resetAllFilter={() => ({
					height: undefined,
				})}
				isShownByDefault
				key={clientId}>
				<UnitControl
					label={__('Height', 'blocksy')}
					labelPosition="top"
					value={height || ''}
					min={0}
					onChange={(nextHeight) =>
						onDimensionChange('height', nextHeight)
					}
					units={units}
				/>
			</ToolsPanelItem>
			{!!imageSizeOptions.length && (
				<ToolsPanelItem
					hasValue={() => !!sizeSlug}
					label={__('Resolution', 'blocksy')}
					onDeselect={() => setAttributes({ sizeSlug: undefined })}
					resetAllFilter={() => ({
						sizeSlug: undefined,
					})}
					isShownByDefault={false}
					key={clientId}>
					<SelectControl
						__nextHasNoMarginBottom
						label={__('Resolution', 'blocksy')}
						value={sizeSlug || DEFAULT_SIZE}
						options={imageSizeOptions}
						onChange={(nextSizeSlug) =>
							setAttributes({ sizeSlug: nextSizeSlug })
						}
						help={__(
							'Select the size of the source image.',
							'blocksy'
						)}
					/>
				</ToolsPanelItem>
			)}
		</ToolsPanel>
	)
}

export default DimensionControls
