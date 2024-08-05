import { createElement, RawHTML } from '@wordpress/element'
import { __, sprintf } from 'ct-i18n'

const RatingPreview = ({ product }) => {
	const width = (parseFloat(product?.average_rating) / 5) * 100 + '%'
	return (
		<div
			className="star-rating"
			role="img"
			aria-label="Rated 2.15 out of 5">
			<span style={{ width }}>
				{sprintf(
					__('Rated %s out of 5', 'blocksy-companion'),
					product?.average_rating
				)}
			</span>
		</div>
	)
}

export default RatingPreview
