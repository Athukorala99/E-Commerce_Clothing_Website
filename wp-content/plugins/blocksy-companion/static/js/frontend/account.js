import ctEvents from 'ct-events'
import { formPreSubmitHook } from './account/hooks'
import { resetCaptchaFor, reCreateCaptchaFor } from './account/captcha'
import { mountPasswordStrength } from './account/password-strength'

import { maybeHandleLoginForm } from './account/handlers/login'

export const maybeAddLoadingState = (form) => {
	const maybeButton = form.querySelector('[name*="submit"]')

	if (!maybeButton) {
		return
	}

	maybeButton.classList.add('ct-loading')
}

export const maybeCleanupLoadingState = (form) => {
	const maybeButton = form.querySelector('[name*="submit"]')

	if (!maybeButton) {
		return
	}

	maybeButton.classList.remove('ct-loading')
}

export const activateScreen = (
	el,
	{
		// login | register | forgot
		screen = 'login',
	}
) => {
	if (!el.querySelector(`.ct-${screen}-form`)) {
		screen = 'login'
	}

	if (el.querySelector('ul') && el.querySelector(`ul .ct-${screen}`)) {
		el.querySelector('ul .active').classList.remove('active')
		el.querySelector(`ul .ct-${screen}`).classList.add('active')
	}

	el.querySelector('[class*="-form"].active').classList.remove('active')
	el.querySelector(`.ct-${screen}-form`).classList.add('active')

	if (el.querySelector(`.ct-${screen}-form form`)) {
		el.querySelector(`.ct-${screen}-form form`).reset()
	}

	el.querySelector('.ct-account-modal').classList.remove('ct-error')

	let maybeMessageContainer = el
		.querySelector(`.ct-${screen}-form`)
		.querySelector('.ct-form-notification')

	if (maybeMessageContainer) {
		maybeMessageContainer.remove()
	}

	let maybeErrorContainer = el
		.querySelector(`.ct-${screen}-form`)
		.querySelector('.ct-form-notification-error')

	if (maybeErrorContainer) {
		maybeErrorContainer.remove()
	}

	reCreateCaptchaFor(el)
}

export let actuallyInsertError = (container, errorHtml) => {
	let maybeErrorContainer = container.querySelector(
		'.ct-form-notification-error'
	)

	if (maybeErrorContainer) {
		maybeErrorContainer.remove()
	}

	container.closest('.ct-account-modal').classList.remove('ct-error')

	if (errorHtml) {
		container.insertAdjacentHTML(
			'afterbegin',
			`<div class="ct-form-notification-error">${errorHtml}</div>`
		)

		requestAnimationFrame(() => {
			container.closest('.ct-account-modal').classList.add('ct-error')
		})
	}
}

export const maybeAddErrors = (container, html) => {
	let parser = new DOMParser()
	let doc = parser.parseFromString(html, 'text/html')

	let maybeLoginError = doc.querySelector('#login_error')

	let errorHtml = ''

	if (maybeLoginError) {
		errorHtml = maybeLoginError.innerHTML
	}

	actuallyInsertError(container, errorHtml)

	return {
		hasError: !!maybeLoginError,
		doc,
	}
}

export const actuallyInsertMessage = (container, html) => {
	let maybeMessageContainer = container.querySelector('.ct-form-notification')

	if (maybeMessageContainer) {
		maybeMessageContainer.remove()
	}

	container.closest('.ct-account-modal').classList.remove('ct-error')

	if (html) {
		container.insertAdjacentHTML(
			'afterbegin',
			`<div class="ct-form-notification">${html}</div>`
		)
	}
}

const maybeAddMessage = (container, html) => {
	let parser = new DOMParser()
	let doc = parser.parseFromString(html, 'text/html')

	let maybeMessage = doc.querySelector('.message')

	let messageHtml = ''

	if (maybeMessage) {
		messageHtml = maybeMessage.innerHTML
	}

	actuallyInsertMessage(container, messageHtml)

	return { doc }
}

export const handleAccountModal = (el) => {
	if (!el) {
		return
	}

	if (el.hasListeners) {
		return
	}

	el.hasListeners = true

	maybeHandleLoginForm(el)

	let maybeLogin = el.querySelector('[name="loginform"]')
	let maybeRegister = el.querySelector('[name="registerform"]')
	let maybeLostPassword = el.querySelector('[name="lostpasswordform"]')

	el.addEventListener(
		'click',
		(e) => {
			if (e.target.href && e.target.href.indexOf('lostpassword') > -1) {
				activateScreen(el, { screen: 'forgot-password' })
				e.preventDefault()
			}

			if (e.target.href && e.target.classList.contains('showlogin')) {
				activateScreen(el, { screen: 'login' })
				e.preventDefault()
			}

			if (
				e.target.href &&
				(e.target.href.indexOf('wp-login') > -1 ||
					(maybeLogin && e.target.href === maybeLogin.action) ||
					e.target.href.indexOf('login') > -1 ||
					e.target.dataset.login === 'yes') &&
				e.target.href.indexOf('lostpassword') === -1
			) {
				activateScreen(el, { screen: 'login' })
				e.preventDefault()
			}
		},
		true
	)
	if (el.querySelectorAll('.show-password-input + .show-password-input')) {
		el.querySelectorAll(
			'.show-password-input + .show-password-input'
		).forEach((el) => {
			el.remove()
		})
	}

	;[...el.querySelectorAll('.show-password-input')].map((eye) => {
		eye.addEventListener('click', (e) => {
			eye.previousElementSibling.type =
				eye.previousElementSibling.type === 'password'
					? 'text'
					: 'password'
		})
	})

	if (maybeRegister) {
		maybeRegister.addEventListener('submit', (e) => {
			e.preventDefault()

			if (window.ct_customizer_localizations) {
				return
			}

			maybeAddLoadingState(maybeRegister)

			formPreSubmitHook(maybeRegister).then(() =>
				fetch(
					// maybeRegister.action,
					`${ct_localizations.ajax_url}?action=blc_implement_user_registration`,

					{
						method: maybeRegister.method,
						body: new FormData(maybeRegister),
					}
				)
					.then((response) => response.text())
					.then((html) => {
						const { doc, hasError } = maybeAddErrors(
							maybeRegister.closest('.ct-register-form'),
							html
						)

						maybeCleanupLoadingState(maybeRegister)

						if (!hasError) {
							maybeAddMessage(
								maybeRegister.closest('.ct-register-form'),
								html
							)
						}

						ctEvents.trigger(
							`blocksy:account:register:${
								hasError ? 'error' : 'success'
							}`
						)

						if (!hasError) {
							if (
								maybeRegister.querySelector(
									'[name="redirect_to"]'
								) &&
								maybeRegister.querySelector(
									'[name="role"][value="seller"]:checked'
								)
							) {
								location = maybeRegister.querySelector(
									'[name="redirect_to"]'
								).value
							}
						}

						if (
							!hasError ||
							(hasError &&
								maybeRegister
									.closest('.ct-register-form')
									.querySelector(
										'.ct-form-notification-error'
									)
									.innerHTML.indexOf('Captcha') === -1)
						) {
							resetCaptchaFor(
								maybeRegister.closest('.ct-register-form')
							)
						}
					})
			)
		})

		let maybePassField = maybeRegister.querySelector('#user_pass_register')

		if (maybePassField) {
			mountPasswordStrength(maybePassField)
		}
	}

	if (maybeLostPassword) {
		maybeLostPassword.addEventListener('submit', (e) => {
			e.preventDefault()

			if (window.ct_customizer_localizations) {
				return
			}

			maybeAddLoadingState(maybeLostPassword)

			fetch(
				// maybeLostPassword.action,
				`${ct_localizations.ajax_url}?action=blc_implement_user_lostpassword`,

				{
					method: maybeLostPassword.method,
					body: new FormData(maybeLostPassword),
				}
			)
				.then((response) => response.text())
				.then((html) => {
					const { doc, hasError } = maybeAddErrors(
						maybeLostPassword.closest('.ct-forgot-password-form'),
						html
					)

					maybeCleanupLoadingState(maybeLostPassword)

					if (!hasError) {
						maybeAddMessage(
							maybeLostPassword.closest(
								'.ct-forgot-password-form'
							),
							html
						)
					}

					if (
						!hasError ||
						(hasError &&
							maybeLostPassword
								.closest('.ct-forgot-password-form')
								.querySelector('.ct-form-notification-error')
								.innerHTML.indexOf('Captcha') === -1)
					) {
						resetCaptchaFor(
							maybeLostPassword.closest(
								'.ct-forgot-password-form'
							)
						)
					}
				})
		})
	}

	;['login', 'register', 'forgot-password'].map((screen) => {
		Array.from(el.querySelectorAll(`.ct-${screen}`)).map((itemEl) => {
			itemEl.addEventListener('click', (e) => {
				e.preventDefault()
				activateScreen(el, { screen })
			})

			itemEl.addEventListener('keyup', (e) => {
				if (e.keyCode !== 13) {
					return
				}

				e.preventDefault()
				activateScreen(el, { screen })
			})
		})
	})
}
