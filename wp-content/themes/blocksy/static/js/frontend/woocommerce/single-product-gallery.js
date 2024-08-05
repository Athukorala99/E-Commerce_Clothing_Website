import $ from 'jquery'
import ctEvents from 'ct-events'

import { isTouchDevice } from '../helpers/is-touch-device'

import { pauseVideo } from '../helpers/video'

export const mount = (el, { event: mountEvent }) => {
	const isGalleryEnabled =
		window.PhotoSwipe && !!ct_localizations.has_product_single_lightbox

	const openPhotoswipeFor = (el, index = null) => {
		if (el.closest('.elementor-section-wrap')) {
			return
		}

		const pswpElement = $('.pswp')[0]
		const clicked = $(el)

		let items = [
			...el
				.closest('.woocommerce-product-gallery')
				.querySelectorAll('.ct-media-container'),
		].filter((el) => !el.closest('.flexy-pills'))

		items = items.map((mediaContainer) => {
			if (mediaContainer.matches('[data-media-id]')) {
				return {
					mediaContainer,
					html: `<div class="ct-lightbox-video-container" data-media-id="${mediaContainer.dataset.mediaId}"></div>`,
				}
			}

			const videoOrIframe = mediaContainer.querySelector('video,iframe')

			if (videoOrIframe) {
				return {
					mediaContainer,
					html: `<div class="ct-lightbox-video-container">${videoOrIframe.outerHTML}</div>`,
				}
			}

			const img = mediaContainer.querySelector('img:not(.zoomImg)')

			return {
				mediaContainer,
				img,
				src: img.closest('[data-src]')
					? img.closest('[data-src]').dataset.src ||
					  img.closest('[data-src]').href ||
					  img.src
					: img.src,
				w:
					(img.closest('[data-width]')
						? img.closest('[data-width]').dataset.width
						: img.width) || img.width,
				h:
					(img.closest('[data-height]')
						? img.closest('[data-height]').dataset.height
						: img.width) || img.width,
				title: img.getAttribute('data-caption'),
			}
		})

		if (
			items.length === 1 &&
			items[0].img &&
			items[0].img.closest('a') &&
			!items[0].img.closest('a').getAttribute('data-src') &&
			items[0].img.title === 'woocommerce-placeholder'
		) {
			return
		}

		var options = $.extend(
			{
				index: index === 0 ? 0 : index || $(clicked).index(),
				addCaptionHTMLFn: function (item, captionEl) {
					if (!item.title) {
						captionEl.children[0].textContent = ''
						return false
					}
					captionEl.children[0].textContent = item.title
					return true
				},
			},
			{
				shareEl: false,
				fullscreenEl: true,
				closeOnScroll: false,
				history: false,
				showHideOpacity: false,
				hideAnimationDuration: 0,
				showAnimationDuration: 0,
			}
		)

		// Initializes and opens PhotoSwipe.
		var photoswipe = new PhotoSwipe(
			pswpElement,
			PhotoSwipeUI_Default,
			items,
			options
		)

		photoswipe.init()

		const pauseAllVideos = () => {
			photoswipe.currItem.container
				.closest('.pswp')
				.querySelectorAll('video,iframe')
				.forEach((videoOrIframe) => pauseVideo(videoOrIframe))
		}

		photoswipe.listen('close', () => pauseAllVideos())

		const loadVideoForCurrentSlide = () => {
			const videoContainer =
				photoswipe.currItem.container.querySelector('[data-media-id]')

			if (
				!videoContainer ||
				videoContainer.querySelector('video,iframe')
			) {
				return
			}

			const preloader = videoContainer
				.closest('.pswp')
				.querySelector('.pswp__preloader')

			if (preloader) {
				preloader.classList.add('pswp__preloader--active')
			}

			import('../lazy/video-on-click').then(({ fetchVideoBy }) => {
				fetchVideoBy(videoContainer.dataset.mediaId, {
					ignoreVideoOptions: true,
				}).then((data) => {
					videoContainer.innerHTML = data.html

					if (preloader) {
						preloader.classList.remove('pswp__preloader--active')
					}
				})
			})
		}

		setTimeout(() => {
			loadVideoForCurrentSlide()
		}, 300)

		photoswipe.listen('afterChange', () => {
			pauseAllVideos()
			loadVideoForCurrentSlide()
		})
	}

	const renderPhotoswipe = ({ onlyZoom = false } = {}) => {
		let maybeTrigger = [
			...document.querySelectorAll(
				'.woocommerce-product-gallery .woocommerce-product-gallery__trigger'
			),
		]

		;[
			...document.querySelectorAll(
				'.woocommerce-product-gallery .ct-media-container'
			),
		]
			.filter((el) => !el.closest('.flexy-pills'))
			.map((el) => {
				if (
					((window.wp &&
						wp.customize &&
						wp.customize('has_product_single_lightbox') &&
						wp.customize('has_product_single_lightbox')() ===
							'yes') ||
						!window.wp ||
						!window.wp.customize) &&
					!onlyZoom &&
					!el.matches('[data-media-id]')
				) {
					if (!el.hasPhotoswipeListener) {
						el.hasPhotoswipeListener = true
						el.addEventListener('click', (e) => {
							if (!isGalleryEnabled) {
								return
							}

							if (maybeTrigger.length > 0) {
								return
							}

							e.preventDefault()

							let activeIndex = 0

							activeIndex = [
								...el.parentNode.querySelectorAll(
									'.ct-media-container'
								),
							].indexOf(el)

							if (el.closest('.flexy-items')) {
								activeIndex = [
									...el.closest('.flexy-items').children,
								].indexOf(el.parentNode)
							}

							isGalleryEnabled &&
								openPhotoswipeFor(el, activeIndex)
						})
					}
				}

				if ($.fn.zoom) {
					if (
						(window.wp &&
							wp.customize &&
							wp.customize('has_product_single_zoom') &&
							wp.customize('has_product_single_zoom')() ===
								'yes') ||
						!window.wp ||
						!window.wp.customize
					) {
						const rect = el.getBoundingClientRect()

						if (el.closest('.elementor-section-wrap')) {
							return
						}

						if (el.closest('.ct-quick-view-card')) {
							return
						}

						if (el.querySelector('iframe')) {
							return
						}

						if (el.querySelector('video')) {
							return
						}

						if (
							parseFloat(el.getAttribute('data-width')) >
							el
								.closest('.woocommerce-product-gallery')
								.getBoundingClientRect().width
						) {
							$(el).zoom({
								url: el.dataset.src,
								touch: false,
								duration: 50,

								...(rect.width > parseFloat(el.dataset.width) ||
								rect.height > parseFloat(el.dataset.height)
									? {
											magnify: 2,
									  }
									: {}),

								...(isTouchDevice()
									? {
											on: 'toggle',
									  }
									: {}),
							})
						}
					}
				}
			})

		if ($.fn.zoom) {
			if (
				(window.wp &&
					wp.customize &&
					wp.customize('has_product_single_zoom') &&
					wp.customize('has_product_single_zoom')() === 'yes') ||
				!window.wp ||
				!window.wp.customize
			) {
				setTimeout(() => {
					if (!mountEvent) {
						return
					}

					if (mountEvent.target.closest('.elementor-section-wrap')) {
						return
					}

					if (
						mountEvent.target.closest('.flexy-items') ||
						(mountEvent.target.closest('.ct-media-container') &&
							mountEvent.target
								.closest('.ct-media-container')
								.parentNode.classList.contains(
									'ct-stacked-gallery-container'
								))
					) {
						$(
							mountEvent.target.closest('.ct-media-container')
						).trigger(
							isTouchDevice() ? 'click.zoom' : 'mouseenter.zoom'
						)
					}
				}, 150)
			}
		}

		maybeTrigger.map((maybeTrigger) => {
			if (maybeTrigger.hasPhotoswipeListener) {
				return
			}

			maybeTrigger.hasPhotoswipeListener = true

			maybeTrigger.addEventListener('click', (e) => {
				e.preventDefault()
				e.stopPropagation()

				const galleryWrapper = maybeTrigger.closest(
					'.woocommerce-product-gallery'
				)

				if (
					galleryWrapper.querySelector('.ct-media-container') &&
					!galleryWrapper.querySelector('.flexy-items')
				) {
					isGalleryEnabled &&
						openPhotoswipeFor(
							galleryWrapper.querySelector('.ct-media-container')
						)

					return
				}

				if (
					maybeTrigger.closest('.ct-media-container') &&
					maybeTrigger.closest('.flexy-items') &&
					maybeTrigger.closest('.ct-columns-top-gallery')
				) {
					isGalleryEnabled &&
						openPhotoswipeFor(
							maybeTrigger.closest('.ct-media-container'),

							[
								...maybeTrigger.closest('.ct-media-container')
									.parentNode.parentNode.children,
							].indexOf(
								maybeTrigger.closest('.ct-media-container')
									.parentNode
							)
						)

					return
				}

				if (
					document.querySelector(
						'.single-product .ct-stacked-gallery-container > .ct-media-container'
					)
				) {
					isGalleryEnabled &&
						openPhotoswipeFor(
							document.querySelector(
								'.single-product .ct-stacked-gallery-container > .ct-media-container'
							)
						)
				}

				if (
					document.querySelector(
						'.single-product .flexy-items .ct-media-container'
					)
				) {
					let pills = document.querySelector(
						'.single-product .flexy-pills'
					)

					let activeIndex = Array.from(
						pills.querySelector('.active').parentNode.children
					).indexOf(
						pills.querySelector('.active') ||
							pills.firstElementChild
					)

					isGalleryEnabled &&
						openPhotoswipeFor(
							document.querySelector(
								'.single-product .flexy-items'
							).children[activeIndex].firstElementChild,

							activeIndex
						)
				}
			})
		})
	}

	if (mountEvent) {
		if (isTouchDevice() && mountEvent.type === 'click') {
			setTimeout(() => {
				if (mountEvent.target && mountEvent.target.click) {
					mountEvent.target.click()
				}
			})
		}
	}

	renderPhotoswipe()
}
