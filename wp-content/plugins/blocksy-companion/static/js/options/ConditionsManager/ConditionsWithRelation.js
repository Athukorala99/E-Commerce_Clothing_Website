import { Fragment, createElement, useState } from '@wordpress/element'
import SingleCondition from './SingleCondition'
import useConditionsData from './useConditionsData'
import { __, sprintf } from 'ct-i18n'

import cls from 'classnames'

const ConditionsWithRelation = ({
	conditionsListDescriptor,
	onChange,
	extractRulesIntoRoot = null,
	className = '',
}) => {
	const [hoveredRelation, setHoveredRelation] = useState(null)
	const { rulesToUse, isAdvancedMode } = useConditionsData()

	if (conditionsListDescriptor.conditions.length === 0) {
		return null
	}

	const output = conditionsListDescriptor.conditions.map(
		(conditionOrRelation, index) => {
			let content = null
			let hoveredClassName = ''

			if (hoveredRelation === index || hoveredRelation - 1 === index) {
				hoveredClassName = 'ct-hovered'
			}

			if (conditionOrRelation.relation) {
				content = (
					<ConditionsWithRelation
						className={hoveredClassName}
						key={index}
						conditionsListDescriptor={conditionOrRelation}
						extractRulesIntoRoot={(rulesIndex) => {
							const newConditions = [
								...conditionOrRelation.conditions.slice(
									0,
									rulesIndex - 1
								),

								...conditionOrRelation.conditions.slice(
									rulesIndex + 1
								),
							]

							onChange([
								...conditionsListDescriptor.conditions.slice(
									0,
									index
								),

								...conditionOrRelation.conditions.slice(
									rulesIndex - 1,
									rulesIndex + 1
								),

								...(newConditions.length > 0
									? [
											{
												...conditionOrRelation,
												conditions: newConditions,
											},
									  ]
									: []),

								...conditionsListDescriptor.conditions.slice(
									index + 1
								),
							])
						}}
						onChange={(c) => {
							if (c.length === 0) {
								onChange([
									...conditionsListDescriptor.conditions.slice(
										0,
										index
									),
									...conditionsListDescriptor.conditions.slice(
										index + 1
									),
								])
							} else {
								onChange(
									conditionsListDescriptor.conditions.map(
										(r, i) => ({
											...(i === index
												? {
														...r,
														conditions: c,
												  }
												: r),
										})
									)
								)
							}
						}}
					/>
				)
			}

			if (!conditionOrRelation.relation) {
				content = (
					<SingleCondition
						className={hoveredClassName}
						condition={conditionOrRelation}
						onChange={(newCondition) => {
							onChange(
								conditionsListDescriptor.conditions.map(
									(r, i) => ({
										...(i === index ? newCondition : r),
									})
								)
							)
						}}
						onAdd={() => {
							if (conditionsListDescriptor.relation === 'OR') {
								onChange(
									conditionsListDescriptor.conditions.map(
										(r, i) => ({
											...(i === index
												? {
														relation: 'AND',

														conditions: [
															r,

															{
																type: 'include',
																rule: rulesToUse[0]
																	.rules[0]
																	.id,
																payload: {},
															},
														],
												  }
												: r),
										})
									)
								)

								return
							}

							onChange([
								...conditionsListDescriptor.conditions,
								{
									type: 'include',
									rule: rulesToUse[0].rules[0].id,
									payload: {},
								},
							])
						}}
						onRemove={() => {
							onChange([
								...conditionsListDescriptor.conditions.slice(
									0,
									index
								),
								...conditionsListDescriptor.conditions.slice(
									index + 1
								),
							])
						}}
					/>
				)
			}

			return (
				<Fragment key={index}>
					{isAdvancedMode && index > 0 && (
						<div
							className={cls('ct-condition-relation', {
								'ct-hovered': hoveredRelation === index,
							})}>
							<ul
								onMouseEnter={() => {
									setHoveredRelation(index)
								}}
								onMouseLeave={() => {
									setHoveredRelation(null)
								}}>
								{conditionsListDescriptor.relation ===
									'AND' && (
									<Fragment>
										<li
											onClick={() => {
												if (extractRulesIntoRoot) {
													setHoveredRelation(null)
													extractRulesIntoRoot(index)
												}
											}}>
											<span>OR</span>
										</li>
										<li className="active">
											<span>AND</span>
										</li>
									</Fragment>
								)}

								{conditionsListDescriptor.relation === 'OR' && (
									<Fragment>
										<li className="active">
											<span>OR</span>
										</li>

										<li
											onClick={() => {
												setHoveredRelation(null)
												onChange([
													...conditionsListDescriptor.conditions.slice(
														0,
														index - 1
													),
													{
														relation: 'AND',
														conditions:
															conditionsListDescriptor.conditions
																.slice(
																	index - 1,
																	index + 1
																)

																.reduce(
																	(
																		acc,
																		curr
																	) => [
																		...acc,
																		...(curr.conditions
																			? curr.conditions
																			: [
																					curr,
																			  ]),
																	],
																	[]
																),
													},
													...conditionsListDescriptor.conditions.slice(
														index + 1,
														conditionsListDescriptor
															.conditions.length
													),
												])
											}}>
											<span>AND</span>
										</li>
									</Fragment>
								)}
							</ul>
						</div>
					)}
					{content}
				</Fragment>
			)
		}
	)

	if (
		(conditionsListDescriptor.relation === 'OR' &&
			conditionsListDescriptor.conditions.length === 1) ||
		!isAdvancedMode
	) {
		return output
	}

	return (
		<div
			className={cls('ct-relation-group', className)}
			data-relation={conditionsListDescriptor.relation}>
			{output}
			{conditionsListDescriptor.relation === 'AND' && (
				<div className="ct-condition-relation ct-add-and-relation">
					<span
						onClick={() => {
							onChange([
								...conditionsListDescriptor.conditions,
								{
									type: 'include',
									rule: rulesToUse[0].rules[0].id,
									payload: {},
								},
							])
						}}>
						<svg
							width="8"
							height="8"
							fill="currentColor"
							viewBox="0 0 20 20">
							<path d="M20,11h-9v9H9v-9H0V9h9V0h2v9h9V11z" />
						</svg>
					</span>
				</div>
			)}
		</div>
	)
}

export default ConditionsWithRelation
