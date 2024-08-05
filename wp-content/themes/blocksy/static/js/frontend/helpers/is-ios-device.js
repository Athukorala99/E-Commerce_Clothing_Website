export const isIosDevice = () =>
	typeof window !== 'undefined' &&
	window.navigator &&
	window.navigator.platform &&
	(/iP(ad|hone|od)/.test(window.navigator.platform) ||
		(window.navigator.platform === 'MacIntel' &&
			window.navigator.maxTouchPoints > 1))
