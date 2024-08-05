class DomEvents {
	_callbacksMap = {}

	on(topic, callback) {
		const cb = (e) => {
			callback(e.detail)
		}

		this._callbacksMap[callback] = cb

		document.addEventListener(topic, cb)
	}

	once(topic, callback) {
		const cb = (e) => {
			callback(e.detail)
		}

		document.addEventListener(topic, cb, { once: true })
	}

	off(topic, callback) {
		document.removeEventListener(topic, this._callbacksMap[callback])
	}

	trigger(topic, data) {
		document.dispatchEvent(new CustomEvent(topic, { detail: data }))
	}
}

const events = new DomEvents()

window.ctEvents = events

export default events
