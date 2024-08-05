import {
	createElement,
	useRef,
	useEffect,
	useState,
	useCallback,
	RawHTML,
} from '@wordpress/element'
import { RichText } from '@wordpress/block-editor'
import { __ } from 'ct-i18n'

import { Spinner } from '@wordpress/components'
import useDynamicPreview from '../../hooks/useDynamicPreview'

import { colors } from './colors'

const OVERWRITE_ATTRIBUTES = {
	enable_live_results: 'no',
	live_results_images: 'yes',
	searchBoxHeight: '',
	searchProductPrice: 'no',
	searchProductStatus: 'no',
	search_box_placeholder: __('Search', 'blocksy'),
	taxonomy_filter_label: __('Select category', 'blocksy'),
	search_through: { post: true, page: true, product: true, custom: true },
	taxonomy_filter_visibility: { desktop: true, tablet: true, mobile: false },

	...colors,
}

const Preview = ({ attributes, setAttributes, buttonStyles }) => {
	const [isChanging, setIsChanging] = useState(false)

	const {
		search_box_button_text,
		search_box_placeholder,
		taxonomy_filter_label,
		buttonPosition,
		has_taxonomy_filter,
		buttonUseText,
	} = attributes

	const maybeParts = useRef({
		taxonomy: '',
		icon: '',
	})

	const formatContent = useCallback(
		(content) => {
			const virtualContainer = document.createElement('div')
			virtualContainer.innerHTML = content

			const input = virtualContainer.querySelector('[type="search"]')

			if (input) {
				input.setAttribute('placeholder', search_box_placeholder)
			}

			const searchBox = virtualContainer.querySelector('.ct-search-box')

			searchBox.style = ''

			if (virtualContainer.querySelector('.ct-select-taxonomy')) {
				maybeParts.current = {
					...maybeParts.current,
					taxonomy: virtualContainer.querySelector(
						'.ct-select-taxonomy'
					).outerHTML,
				}
			}

			if (virtualContainer.querySelector('.ct-icon')) {
				maybeParts.current = {
					...maybeParts.current,
					icon: virtualContainer.querySelector('.ct-icon').outerHTML,
				}
			}

			return virtualContainer.innerHTML
		},
		[search_box_placeholder, buttonPosition, buttonStyles]
	)

	const { isLoading } = useDynamicPreview(
		'search',
		{
			...attributes,
			...OVERWRITE_ATTRIBUTES,
		},
		formatContent
	)

	useEffect(() => {
		setIsChanging(true)

		setTimeout(() => {
			setIsChanging(false)
		}, 100)
	}, [attributes])

	return isLoading ? (
		<Spinner />
	) : (
		<form
			role="search"
			method="get"
			className="ct-search-form"
			data-form-controls={buttonPosition}
			data-taxonomy-filter={
				has_taxonomy_filter === 'yes' ? 'true' : 'false'
			}
			data-submit-button={buttonUseText === 'yes' ? 'text' : 'icon'}
			data-updating={isChanging ? 'yes' : 'no'}>
			<input
				type="search"
				value={search_box_placeholder}
				onChange={(e) => {
					setAttributes({
						search_box_placeholder: e.target.value,
					})
				}}
				placeholder="Search"
				name="s"
				autocomplete="off"
				title="Search for..."
				aria-label="Search for..."
			/>

			<div className="ct-search-form-controls">
				{has_taxonomy_filter === 'yes' ? (
					<span className="ct-fake-select-container">
						<select className="ct-select-taxonomy" />
						<RichText
							tagName="span"
							className="ct-fake-select"
							value={taxonomy_filter_label}
							placeholder="Select Category"
							allowedFormats={[]}
							onChange={(content) =>
								setAttributes({
									taxonomy_filter_label: content,
								})
							}
						/>
					</span>
				) : null}
				<div
					className="wp-element-button"
					data-button={`${buttonPosition}:${
						buttonUseText === 'yes' ? 'text' : 'icon'
					}`}
					aria-label="Search button"
					style={buttonStyles}>
					{buttonUseText === 'yes' ? (
						<RichText
							tagName="span"
							value={search_box_button_text}
							placeholder="Search"
							allowedFormats={[]}
							onChange={(content) =>
								setAttributes({
									search_box_button_text: content,
								})
							}
						/>
					) : (
						<RawHTML>{maybeParts.current.icon}</RawHTML>
					)}
					<span className="ct-ajax-loader">
						<svg viewBox="0 0 24 24">
							<circle
								cx="12"
								cy="12"
								r="10"
								opacity="0.2"
								fill="none"
								stroke="currentColor"
								stroke-miterlimit="10"
								stroke-width="2"></circle>

							<path
								d="m12,2c5.52,0,10,4.48,10,10"
								fill="none"
								stroke="currentColor"
								stroke-linecap="round"
								stroke-miterlimit="10"
								stroke-width="2">
								<animateTransform
									attributeName="transform"
									attributeType="XML"
									type="rotate"
									dur="0.6s"
									from="0 12 12"
									to="360 12 12"
									repeatCount="indefinite"></animateTransform>
							</path>
						</svg>
					</span>
				</div>
			</div>
		</form>
	)
}

export default Preview
