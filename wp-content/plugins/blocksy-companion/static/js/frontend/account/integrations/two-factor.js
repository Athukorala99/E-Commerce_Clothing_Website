import { maybeCleanupLoadingState, maybeAddErrors } from '../../account'

export const maybeMountTwoFactorForm = (form, doc) => {
	const maybeTwoFactorForm = doc.querySelector('[name="validate_2fa_form"]')

	if (!maybeTwoFactorForm) {
		return false
	}

	let currentCode = ''

	if (form.parentNode.querySelector('[name="validate_2fa_form"]')) {
		currentCode = form.parentNode.querySelector('[id="authcode"]').value
		form.parentNode.querySelector('[name="validate_2fa_form"]').remove()
	}

	form.insertAdjacentElement('beforebegin', maybeTwoFactorForm)

	maybeTwoFactorForm.querySelector('[id="authcode"]').value = currentCode

	// Enforce numeric-only input for numeric inputmode elements.
	let inputEl = maybeTwoFactorForm.querySelector(
		'input.authcode[inputmode="numeric"]'
	)
	let expectedLength = inputEl?.dataset.digits || 0

	if (inputEl) {
		let spaceInserted = false

		inputEl.addEventListener('input', function () {
			let value = this.value.replace(/[^0-9 ]/g, '').trimStart()

			if (
				!spaceInserted &&
				expectedLength &&
				value.length === Math.floor(expectedLength / 2)
			) {
				value += ' '
				spaceInserted = true
			} else if (spaceInserted && !this.value) {
				spaceInserted = false
			}

			this.value = value

			// Auto-submit if it's the expected length.
			if (
				expectedLength &&
				value.replace(/ /g, '').length == expectedLength
			) {
				if (undefined !== maybeTwoFactorForm.requestSubmit) {
					maybeTwoFactorForm.requestSubmit()
					maybeTwoFactorForm.submit.disabled = 'disabled'
				}
			}
		})
	}

	maybeTwoFactorForm.addEventListener('submit', (e) => {
		e.preventDefault()

		let formData = new FormData(maybeTwoFactorForm)

		fetch(maybeTwoFactorForm.action, {
			method: maybeTwoFactorForm.method,
			body: formData,
		})
			.then((response) => response.text())
			.then((html) => {
				maybeCleanupLoadingState(form)

				const { doc, hasError } = maybeAddErrors(
					form.closest('.ct-login-form'),
					html
				)

				if (hasError) {
					maybeMountTwoFactorForm(form, doc)
				}

				if (!hasError) {
					setTimeout(() => {
						location = form.querySelector(
							'[name="redirect_to"]'
						).value
					}, 2000)
				}
			})
	})

	return true
}
