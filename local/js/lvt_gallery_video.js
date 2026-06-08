(function (global) {
    'use strict';

    function initGalleryVideos() {
        global.document.querySelectorAll('.detail-gallery-big').forEach(function (gallery) {
            if (gallery.dataset.lvtVideoBound === '1') {
                return;
            }
            gallery.dataset.lvtVideoBound = '1';

            gallery.addEventListener('click', function (event) {
                var playBtn = event.target.closest('.detail-gallery-big__video-play');
                var wrap = event.target.closest('.detail-gallery-big__link--video');
                if (!playBtn && !wrap) {
                    return;
                }
                var slide = event.target.closest('.detail-gallery-big__item--video');
                if (!slide) {
                    return;
                }
                var video = slide.querySelector('video');
                if (!video) {
                    return;
                }
                event.preventDefault();
                event.stopPropagation();
                if (typeof event.stopImmediatePropagation === 'function') {
                    event.stopImmediatePropagation();
                }
                if (video.paused) {
                    var playPromise = video.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {});
                    }
                    slide.classList.add('is-playing');
                } else {
                    video.pause();
                    slide.classList.remove('is-playing');
                }
            }, true);

            gallery.addEventListener('pointerdown', function (event) {
                if (event.target.closest('.detail-gallery-big__item--video')) {
                    event.stopPropagation();
                }
            }, true);

            gallery.querySelectorAll('video.detail-gallery-big__video').forEach(function (video) {
                video.addEventListener('play', function () {
                    var slide = video.closest('.detail-gallery-big__item--video');
                    if (slide) {
                        slide.classList.add('is-playing');
                    }
                });
                video.addEventListener('pause', function () {
                    var slide = video.closest('.detail-gallery-big__item--video');
                    if (slide) {
                        slide.classList.remove('is-playing');
                    }
                });
            });
        });
    }

    if (global.document.readyState === 'loading') {
        global.document.addEventListener('DOMContentLoaded', initGalleryVideos);
    } else {
        initGalleryVideos();
    }

    global.document.addEventListener('onInitSlider', function (event) {
        if (!event || !event.slider || !event.slider.params || event.slider.params.type !== 'detail_gallery_main') {
            return;
        }
        initGalleryVideos();
    });
})(window);
