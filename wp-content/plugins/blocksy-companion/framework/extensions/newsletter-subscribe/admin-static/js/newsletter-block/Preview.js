import { createElement } from '@wordpress/element'
import { RichText } from '@wordpress/block-editor'
import { __ } from 'ct-i18n'

const { has_cookies_checkbox } = window.blc_newsletter_data

const cookies_checkbox_enabled = !!parseInt(has_cookies_checkbox)

const Preview = ({ attributes, buttonStyles, setAttributes }) => {
	const {
		newsletter_subscribe_view_type,
		newsletter_subscribe_name_label,
		newsletter_subscribe_button_text,
		has_newsletter_subscribe_name,
		newsletter_subscribe_mail_label,
	} = attributes

	return (
		<div className="ct-newsletter-subscribe-block">
			<form
				action="#"
				method="post"
				target="_blank"
				className="ct-newsletter-subscribe-form"
				{...(newsletter_subscribe_view_type !== 'inline'
					? {}
					: {
							'data-columns':
								has_newsletter_subscribe_name === 'yes' ? 3 : 2,
					  })}
				data-provider="convertkit">
				{has_newsletter_subscribe_name === 'yes' ? (
					<input
						type="text"
						name="FNAME"
						title="Name"
						value={newsletter_subscribe_name_label}
						onChange={(e) => {
							setAttributes({
								newsletter_subscribe_name_label: e.target.value,
							})
						}}
					/>
				) : null}
				<input
					type="email"
					name="EMAIL"
					title="Email"
					required=""
					value={newsletter_subscribe_mail_label}
					onChange={(e) => {
						setAttributes({
							newsletter_subscribe_mail_label: e.target.value,
						})
					}}
				/>
				<RichText
					className="wp-element-button"
					style={{ ...buttonStyles }}
					tagName="span"
					value={newsletter_subscribe_button_text}
					placeholder="Search"
					allowedFormats={[]}
					onChange={(content) =>
						setAttributes({
							newsletter_subscribe_button_text: content,
						})
					}
				/>

				{cookies_checkbox_enabled ? (
					<p className="gdpr-confirm-policy">
						<input
							name="ct_has_gdprconfirm"
							type="hidden"
							value="yes"
						/>
						<input
							id="gdprconfirm_newsletter-subscribe"
							className="ct-checkbox"
							name="gdprconfirm"
							type="checkbox"
							required=""
						/>
						<label for="gdprconfirm_newsletter-subscribe">
							I accept the{' '}
							<a href="/privacy-policy">Privacy Policy</a>
						</label>
					</p>
				) : null}

				<div className="ct-newsletter-subscribe-message"></div>
			</form>
		</div>
	)
}

export default Preview
