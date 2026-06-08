BX.addCustomEvent("onInitSlider", eventdata => {
	if ("slider" in eventdata && eventdata.slider) {
		const slider = eventdata.slider;
		
		if (slider && slider.params && "type" in slider.params) {
			if (slider.params.type === "detail_gallery_main") {
				const $sliderContainer = slider.el.parentElement;
				const $navItems = $sliderContainer.querySelectorAll('.slider-nav');

				for (let i = 0; i < $navItems.length; i++) {
					$navItems[i].removeAttribute('style');
				}

				const pauseInactiveGalleryVideos = function(activeSlide) {
					slider.el.querySelectorAll('video').forEach(function(video) {
						const slide = video.closest('.swiper-slide');
						if (slide && slide === activeSlide) {
							return;
						}
						try {
							video.pause();
						} catch (e) {}
						if (slide) {
							slide.classList.remove('is-playing');
						}
					});
				};

				pauseInactiveGalleryVideos(slider.slides[slider.activeIndex]);
				slider.on('slideChange', function() {
					pauseInactiveGalleryVideos(this.slides[this.activeIndex]);
				});
			}

			if (slider.params.type === "detail_gallery_thumb") {
				const S_HIDE_NAVIGATION_CLASS = 'gallery-slider-thumb__container--hide-navigation';
				const S_HIDE_THUMBS_CLASS = 'gallery-wrapper--hide-thumbs';

				const $sliderContainer = slider.el.parentElement;
				const $galleryContainer = $sliderContainer.closest('.detail-gallery-big');
				const $navItems = $sliderContainer.querySelectorAll('.slider-nav');


				UpdateSliderArrows(slider, $sliderContainer, S_HIDE_NAVIGATION_CLASS);
				UpdateThumbsBlock(slider.slides.length, $galleryContainer, S_HIDE_THUMBS_CLASS);

				for (let i = 0; i < $navItems.length; i++) {
					$navItems[i].removeAttribute('style');
				}

				slider.on('update', function() {
					const slidesLength = this.wrapperEl.children.length;

					UpdateSliderArrows(this, $sliderContainer, S_HIDE_NAVIGATION_CLASS);
					UpdateThumbsBlock(slidesLength, $galleryContainer, S_HIDE_THUMBS_CLASS);
				});
			}
		}
	}
});

const UpdateSliderArrows = function(slider, $sliderContainer, classList) {
	(slider.isBeginning && slider.isEnd || !slider.slides.length)
		? $sliderContainer.classList.add(classList)
		: $sliderContainer.classList.remove(classList);
};

const UpdateThumbsBlock = function(slidesLength, $container, classList) {
	if (typeof $container !== 'undefined') {
		slidesLength
			? $container.classList.remove(classList)
			: $container.classList.add(classList);
	}
}