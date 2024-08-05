import $ from 'jquery'
import ctEvents from 'ct-events'

let mounted = false
let timeoutId = null
let focusedEl = null

const listenToClicks = () =>
	[...document.querySelectorAll('.quantity')].map((singleQuantity) => {
		;[...singleQuantity.querySelectorAll('input')].map((input) => {
			if (input.hasInputListener) {
				return
			}
			input.hasInputListener = true

			input.addEventListener('input', (e) => {
				if (input.closest('tr')) {
					;[
						...input
							.closest('tr')
							.querySelectorAll('.quantity input'),
					]
						.filter((i) => i !== input)
						.map((input) => (input.value = e.target.value))
				}

				if (document.activeElement === input) {
					focusedEl = input.name
				}

				if (input.closest('.ct-cart-auto-update')) {
					if (timeoutId) {
						clearTimeout(timeoutId)
					}

					timeoutId = setTimeout(function () {
						$("[name='update_cart']").trigger('click')
					}, 300)
				}
			})
		})
	})

$(document.body).on('updated_cart_totals', () => {
	setTimeout(() => {
		;[...document.querySelectorAll(`[name="${focusedEl}"]`)].map((el) => {
			el.focus()
		})
	}, 500)

	ctEvents.trigger('blocksy:frontend:init')
})

export const mount = (el, { event }) => {
	if ($ && !mounted) {
		mounted = true
		$(document.body).on('updated_cart_totals', listenToClicks)
		listenToClicks()
	}

	if (
		!el.classList.contains('ct-increase') &&
		!el.classList.contains('ct-decrease')
	) {
		return
	}

	const singleQuantity = el.parentNode
	const input = singleQuantity.querySelector('input')
	const properValue = parseFloat(input.value, 10) || 0

	if (el.classList.contains('ct-increase')) {
		const max = input.getAttribute('max')
			? parseFloat(input.getAttribute('max'), 0) || Infinity
			: Infinity

		input.value =
			properValue < max
				? Math.round(
						(properValue + parseFloat(input.step || '1')) * 100
				  ) / 100
				: max
	}

	if (el.classList.contains('ct-decrease')) {
		const min = input.getAttribute('min')
			? Math.round(parseFloat(input.getAttribute('min'), 0) * 100) / 100
			: 0

		input.value =
			properValue > min
				? Math.round(
						(properValue - parseFloat(input.step || '1')) * 100
				  ) / 100
				: min
	}

	$(input).trigger('change')
	$(input).trigger('input')

	input.dispatchEvent(new Event('input', { bubbles: true }))

	if (input.closest('tr')) {
		;[...input.closest('tr').querySelectorAll('.quantity input')]
			.filter((i) => i !== input)
			.map((i) => (i.value = input.value))
	}
}
