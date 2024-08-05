import { Fragment, createElement, Component } from '@wordpress/element'

const Title = ({
	option: { label = '', desc = '', attr = {}, variation = 'simple' },
	labelEnd = null,
}) => (
	<Fragment>
		<div
			className="ct-title"
			{...{
				'data-type': variation,
				...(attr || {}),
			}}>
			<h3>
				{label}
				{labelEnd}
			</h3>
			{desc && (
				<div
					className="ct-option-description"
					dangerouslySetInnerHTML={{
						__html: desc,
					}}
				/>
			)}
		</div>
	</Fragment>
)

Title.renderingConfig = { design: 'none' }

export default Title
