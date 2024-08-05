import { useState, createElement, useRef, Fragment } from '@wordpress/element'
import OptionsPanel from '../../OptionsPanel'
import { __, sprintf } from 'ct-i18n'
import classnames from 'classnames'

import { CopyToClipboard } from 'react-copy-to-clipboard'

import EditVariableName from './EditVariableName'

const PalettePreview = ({
	renderBefore = () => null,
	onChange,
	currentPalette,
	option,
	className,
	onClick,

	isActive,

	hasColorRemove = true,
}) => {
	const [copied, setCopied] = useState(false)

	return (
		<div
			className={classnames('ct-color-palette-preview', className, {
				active: isActive,
			})}
			onClick={() => {
				if (onClick) {
					onClick()
				}
			}}>
			<div className="ct-single-palette">
				{renderBefore()}
				<OptionsPanel
					hasRevertButton={false}
					onChange={(optionId, optionValue) => {
						if (optionId !== 'color') {
							return
						}

						onChange(
							optionId,
							Object.keys(optionValue).reduce(
								(finalValue, currentId) => ({
									...finalValue,
									...(currentId.indexOf('color') === 0
										? {
												[currentId]:
													optionValue[currentId],
										  }
										: {
												[currentId]:
													optionValue[currentId],
										  }),
								}),

								{}
							)
						)
					}}
					value={{ color: currentPalette }}
					options={{
						color: {
							type: 'ct-color-picker',
							predefined: true,
							design: 'none',
							label: false,
							modalClassName: 'ct-color-palette-modal',
							value: currentPalette,

							afterPill: ({ picker }) => {
								if (!option.palettes) {
									return null
								}

								const { id, ...colors } = option.palettes[0]

								if (
									parseFloat(
										picker.id.replace('color', '')
									) <= Object.keys(colors).length
								) {
									return null
								}

								if (!hasColorRemove) {
									return null
								}

								return (
									<i
										className="ct-remove-color"
										onClick={() => {
											const {
												[picker.id]: removed,
												...cleanedUpValue
											} = currentPalette

											onChange('color', cleanedUpValue)

											document.documentElement.style.removeProperty(
												`--theme-palette-color-{picker.id.replace(
													'color',
													''
												)}`
											)
										}}>
										<svg
											fill="currentColor"
											viewBox="0 0 35 35">
											<polygon points="34.5,30.2 21.7,17.5 34.5,4.8 30.2,0.5 17.5,13.3 4.8,0.5 0.5,4.8 13.3,17.5 0.5,30.2 4.8,34.5 17.5,21.7 30.2,34.5 "></polygon>
										</svg>
									</i>
								)
							},

							colorVariableName: ({ picker }) => {
								return (
									<div className="ct-option-color-variable">
										<EditVariableName
											currentPalette={currentPalette}
											picker={picker}
											onChange={onChange}
										/>

										<CopyToClipboard
											text={`--${picker.variableName}`}
											onCopy={() => {
												setCopied(picker.variableName)

												setTimeout(() => {
													setCopied(null)
												}, 3000)
											}}>
											<span
												className={classnames(
													'ct-copy-color',
													{
														copied:
															copied ===
															picker.variableName,
													}
												)}
												data-tooltip="top">
												<svg
													width="12"
													height="12"
													fill="currentColor"
													viewBox="0 0 24 24">
													<path d="M20.7 7.6h-9.8c-1.8 0-3.3 1.5-3.3 3.3v9.8c0 1.8 1.5 3.3 3.3 3.3h9.8c1.8 0 3.3-1.5 3.3-3.3v-9.8c0-1.8-1.5-3.3-3.3-3.3zm1.1 13.1c0 .6-.5 1.1-1.1 1.1h-9.8c-.6 0-1.1-.5-1.1-1.1v-9.8c0-.6.5-1.1 1.1-1.1h9.8c.6 0 1.1.5 1.1 1.1v9.8zM5.5 15.3c0 .6-.5 1.1-1.1 1.1H3.3c-1.8 0-3.3-1.5-3.3-3.3V3.3C0 1.5 1.5 0 3.3 0h9.8c1.8 0 3.3 1.5 3.3 3.3v1.1c0 .6-.5 1.1-1.1 1.1-.6 0-1.1-.5-1.1-1.1V3.3c0-.6-.5-1.1-1.1-1.1H3.3c-.6 0-1.1.5-1.1 1.1v9.8c0 .6.5 1.1 1.1 1.1h1.1c.6 0 1.1.5 1.1 1.1z" />
												</svg>
												<i className="ct-tooltip">
													{copied ===
													picker.variableName
														? __(
																'Copied',
																'blocksy'
														  )
														: __('Copy', 'blocksy')}
												</i>
											</span>
										</CopyToClipboard>
									</div>
								)
							},

							...(onChange ? {} : { skipModal: true }),

							pickers: Object.keys(currentPalette)
								.filter((k) => k.indexOf('color') === 0)
								.map((key, index) => ({
									title: sprintf(
										__('Color %s', 'blocksy'),
										key.replace('color', '')
									),
									variableName:
										'theme-palette-color-' +
										key.replace('color', ''),
									id: key,
								})),
						},
					}}
				/>
			</div>
		</div>
	)
}

export default PalettePreview
