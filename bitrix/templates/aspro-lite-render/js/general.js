getRandomInt = function (min, max) {
	return Math.floor(Math.random() * (max - min)) + min;
};

ShowOverlay = function () {
	$('<div class="jqmOverlay waiting"></div>').appendTo("body");
};

function throttle(f, t) {
	let throttled, saveThis, saveArgs;
	const wrapper = function () {
		if (throttled) {
			saveThis = this;
			saveArgs = arguments;
			return;
		}
		throttled = true;
		f.apply(this, arguments);
		setTimeout(function () {
			throttled = false;
			if (saveArgs) {
				wrapper.apply(saveThis, saveArgs);
				saveThis = saveArgs = null;
			}
		}, t);
	};
	return wrapper;
}

function debounce(f, t) {
	return function (args) {
		var previousCall = this.lastCall;
		this.lastCall = Date.now();
		if (previousCall && this.lastCall - previousCall <= t) {
			clearTimeout(this.lastCallTimer);
		}
		this.lastCallTimer = setTimeout(function () {
			f(args);
		}, t);
	};
}

window.addEventListener("popstate", function (event) {
	if (typeof startActions == "function") {
		setTimeout(startActions, 500);
	}
});

$(document).on("change", ".uploader input[type=file]", function () {
	if (!$(this).next().length || !$(this).next().hasClass("resetfile")) {
		$(
			'<span class="resetfile" title="' +
			BX.message("CLEAR_FILE") +
			'"><svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 1.00161L1 9M9 9L1 1" stroke="#999999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>'
		).insertAfter($(this));
	}
});

$(document).on("click", ".uploader .resetfile", function () {
	var $uploader = $(this).closest(".uploader");
	$uploader.removeClass("error files_add");
	$uploader.find(".error").hide();

	if ($uploader.next().hasClass('error')) {
		$uploader.next().hide();
	}

	var $input = $uploader.find("input[type=file]");
	$input.val("");
	$.uniform.update($input);

	$(this).remove();
});

$(document).on("click", ".region-close", function () {
	$(".confirm_region").fadeOut();
});

headerInit = function (targetBlock) {
	var header = $("header");
	if (header.length) {
		setTimeout(function () {
			CheckTopMenuDotted();
			CheckTopMenuDotted();
			if (typeof window.lvtRenderCityControl === "function") {
				window.lvtRenderCityControl();
			}
		}, 1);
	}
};

typeofExt = function (item) {
	const _toString = Object.prototype.toString;
	return _toString.call(item).slice(8, -1).toLowerCase();
};

HideOverlay = function () {
	$(".jqmOverlay").detach();
	CloseMobileSearch();
	CloseMobilePhone();
};

if (!funcDefined("pauseYmObserver")) {
	// pause ya metrika webvisor MutationObserver callback (DOM indexer)
	// use before insert html with some animation
	pauseYmObserver = function () {
		if (
			typeof MutationObserver === "function" &&
			typeof MutationObserver.observers === "object" &&
			typeof MutationObserver.observers.ym === "object"
		) {
			if (typeof pauseYmObserver.cnt === "undefined") {
				pauseYmObserver.cnt = 0;
			}

			++pauseYmObserver.cnt;

			if (!MutationObserver.observers.ym.paused) {
				MutationObserver.observers.ym.pause();
			}
		}
	};
}

if (!funcDefined("resumeYmObserver")) {
	// resume ya metrika webvisor MutationObserver callback
	// use when animation finished
	resumeYmObserver = function () {
		if (
			typeof MutationObserver === "function" &&
			typeof MutationObserver.observers === "object" &&
			typeof MutationObserver.observers.ym === "object"
		) {
			if (typeof pauseYmObserver.cnt === "undefined") {
				pauseYmObserver.cnt = 1;
			}

			pauseYmObserver.cnt -= pauseYmObserver.cnt > 0 ? 1 : 0;

			if (!pauseYmObserver.cnt && MutationObserver.observers.ym.paused) {
				MutationObserver.observers.ym.resume();
			}
		}
	};
}

if (!funcDefined("declOfNum")) {
	declOfNum = function (number, titles) {
		var cases = [2, 0, 1, 1, 1, 2];
		return number + " " + titles[number % 100 > 4 && number % 100 < 20 ? 2 : cases[Math.min(number % 10, 5)]];
	};
}

if (!funcDefined("togglePropBlock")) {
	togglePropBlock = function (className) {
		var all_props_block = className.closest(".bx_filter_parameters_box_container").find(".hidden_values");
		if (all_props_block.length && (className.hasClass("inner_text") || className.hasClass("expand_block"))) {
			if (all_props_block.is(":visible")) {
				className.text(BX.message("FILTER_EXPAND_VALUES"));
				all_props_block.hide();
			} else {
				className.text(BX.message("FILTER_HIDE_VALUES"));
				all_props_block.show();
			}
		}
	};
}

$(document).on("click", ".map-mobile-opener", function () {
	if ($(this).hasClass("closer")) {
		closeFullscreenMap();
	} else {
		openFullsreenMap(this);
	}
});

function openFullsreenMap(element) {
	var $this = $(element);
	if ($this.hasClass("closer")) return;

	var currentMap = $this.parents(".bx-map-view-layout");
	var mapId = currentMap.find(".bx-yandex-map").attr("id");
	window.openedYandexMapFrame = mapId;
	var mapContainer = $('<div data-mapId="' + mapId + '"></div>');

	if (!$("div[data-mapId=" + mapId + "]").length) {
		currentMap.after(mapContainer);
	}

	var addClass = "";
	if ($this.parents(".contacts-page-map-top").length) {
		addClass += " contacts-map-top-frame ";
	}

	var yandexMapFrame = $('<div class="yandex-map__frame filter-none' + addClass + '"></div>');
	$("body > .body").append(yandexMapFrame);
	currentMap.appendTo(yandexMapFrame);
	currentMap.find(".map-mobile-opener").addClass("closer");

	if (typeof map === "object" && map !== null && "container" in map) {
		window.map.container.fitToViewport();
	}
}

function closeFullscreenMap() {
	var yandexMapFrame = $(".yandex-map__frame");
	if (yandexMapFrame.length) {
		var currentMap = yandexMapFrame.find(".bx-map-view-layout");
		var yandexMapContainer = $("div[data-mapId=" + window.openedYandexMapFrame + "]");

		currentMap.appendTo(yandexMapContainer);
		yandexMapFrame.remove();
		currentMap.find(".map-mobile-opener").removeClass("closer");

		if (typeof map === "object" && map !== null && "container" in map) {
			window.map.container.fitToViewport();
		}
	}
}

$.fn.iAppear = function (callback, options) {
	if (typeof $.fn.iAppear.useObserver === "undefined") {
		$.fn.iAppear.useObserver = typeof window["IntersectionObserver"] === "function";

		if (!$.fn.iAppear.useObserver && typeof $.fn.appear !== "function") {
			$.fn.iAppear.queue = [];
			BX.loadScript(arAsproOptions.SITE_TEMPLATE_PATH + "/vendor/js/jquery.appear.js", function () {
				if (typeof $.fn.iAppear.queue === "object") {
					$.fn.iAppear.queue.forEach(function (queueItem) {
						$(queueItem.items).iAppear(queueItem.callback, queueItem.options);
					});
				}
			});

			return;
		}
	}

	if ($.fn.iAppear.useObserver) {
		var options = $.extend(
			{
				root: null,
				rootMargin: "150px 0px 150px 0px",
				threshold: 0.0,
			},
			options
		);

		$(this).each(function (i, appearBlock) {
			var observer = new IntersectionObserver(function (entries, observer) {
				entries.forEach(function (element) {
					if (element.intersectionRatio > 0 && !element.target.dataset.iAppeared) {
						element.target.dataset.iAppeared = true;

						if (typeof callback === "function") {
							callback.call(element.target);
						}
					}
				});
			}, options);

			observer.observe(appearBlock);
		});
	} else {
		if (typeof $.fn.appear !== "function") {
			if ($(this).length) {
				$.fn.iAppear.queue.push({
					items: this,
					callback: callback,
					options: options,
				});
			}

			return;
		} else {
			if ($(this).length) {
				$(this).each(function (i, appearBlock) {
					$(appearBlock).appear(function () {
						if (typeof callback === "function") {
							callback.call(appearBlock);
						}
					}, options);
				});
			}
		}
	}
};

$.fn.mCustomScrollbarDeferred = function (config) {
	$(this).addClass("scroll-init");
	$(this).data("plugin-options", config);

	if ($(this).hasClass("destroyed")) {
		return;
	}

	$(this).hover(
		function (e) {
			var $this = $(this);
			if (!$this.hasClass("mCustomScrollbar") && !$(this).hasClass("destroyed")) {
				$this.data(
					"scrollTimer",
					setTimeout(function () {
						$this.mCustomScrollbar(config);
						$this.off("touchstart touchmove touchend mousewheel mouseenter mouseleave");
					}, 200)
				);
			}
		},
		function (e) {
			clearTimeout($(this).data("scrollTimer"));
		}
	);

	$(this).on("touchstart touchmove", function (e) {
		var $this = $(this);
		if (!$this.hasClass("mCustomScrollbar") && !$(this).hasClass("destroyed")) {
			$this.mCustomScrollbar(config);
		}

		var $scrollContainer = $this.find(">.mCustomScrollBox>.mCSB_container");
		if ($scrollContainer.length) {
			var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];

			var newTouch = new Touch({
				identifier: 42,
				target: $scrollContainer[0],
				clientX: touch.clientX,
				clientY: touch.clientY,
				screenX: touch.screenX,
				screenY: touch.screenY,
				pageX: touch.pageX,
				pageY: touch.pageY,
				radiusX: 1,
				radiusY: 1,
			});

			var newEvent = new TouchEvent(e.type, {
				cancelable: true,
				bubbles: false,
				composed: true,
				touches: [newTouch],
				targetTouches: [newTouch],
				changedTouches: [newTouch],
			});

			$scrollContainer[0].dispatchEvent(newEvent);
		}
	});

	$(this).on("touchend", function (e) {
		$(this).off("touchstart touchmove touchend mousewheel mouseenter mouseleave");
	});

	if ($.event.special.mousewheel) {
		$(this).on("mousewheel", function (e) {
			var $this = $(this);
			if (!$this.hasClass("mCustomScrollbar") && !$(this).hasClass("destroyed")) {
				$this.mCustomScrollbar(config);
				$this.off("touchstart touchmove touchend mousewheel mouseenter mouseleave");
			}
		});
	}
};

InitScrollBar = function (el, initOptions) {
	var block;
	if (typeof el === "undefined") {
		block = $(".srollbar-custom:not(.mobile-scroll):not(.scroll-init)");
	} else {
		block = el.filter(":not(.scroll-init)");
	}
	var blockParent = $(".scrollbar-parent");

	if (
		(block.length || blockParent.length) &&
		!isMobile
		// (!isMobile || window.matchMedia('(min-width:992px)').matches)
	) {
		block.addClass("scroll-init");

		var options,
			defaults = {
				mouseWheel: {
					scrollAmount: 150,
					preventDefault: true,
				},
			};
		if (block.length) {
			var config = $.extend({}, defaults, options, block.data("plugin-options"), initOptions);
		} else if (blockParent.length) {
			var config = $.extend({}, defaults, options, blockParent.data("plugin-options"), initOptions);
		}

		config.callbacks = {
			onScroll: function () {
				if ($(this).find(".mCSB_buttonLeft").hasClass("disabled"))
					$(this).find(".mCSB_buttonLeft").removeClass("disabled");
				if ($(this).find(".mCSB_buttonRight").hasClass("disabled"))
					$(this).find(".mCSB_buttonRight").removeClass("disabled");
			},
			onTotalScrollBack: function () {
				$(this).find(".mCSB_buttonLeft").addClass("disabled");
			},
			onTotalScroll: function () {
				$(this).find(".mCSB_buttonRight").addClass("disabled");
			},
			onInit: function () {
				$(this).find(".mCSB_buttonLeft").addClass("disabled");
			},
		};

		// if (block.length) {
		//   block.filter(":not(.scroll-deferred)").mCustomScrollbar(config);
		//   block.filter(".scroll-deferred").mCustomScrollbarDeferred(config);
		// }

		// if (blockParent.length) {
		//   blockParent.find(".scroll-deferred").mCustomScrollbarDeferred(config);
		// }
	}
};

/*InitCustomScrollBar = function (el) {
	var block;
	if (typeof el === "undefined") {
		block = $(".scrollbar:not(.mobile-scroll):not(.scroll-init)");
	} else {
		block = el.filter(":not(.scroll-init)");
	}

	if (block.length) {
		block.addClass("scroll-init");

		var options,
			defaults = {
				effect: "fadeIn",
				effectTime: 300,
				threshold: 0,
				// threshold: 30
			};

		var config = $.extend({}, defaults, options, block.data("plugin-options"));

		block.filter(":not(.scroll-deferred)").mCustomScrollbar(config);
		block.filter(".scroll-deferred").mCustomScrollbarDeferred(config);
	}
};
*/
/* slider gallery for fancybox */
const InitFancyboxThumbnailsGallery = function (instance) {
	const bUseThumbs = instance.group[0].opts.$orig[0].classList.contains('fancy-thumbs') && instance.group.length > 1;

	if (bUseThumbs)
		instance.Thumbs.show();
	else
		return;

	if (
		bUseThumbs &&
		$(".fancybox-thumbs") &&
		$(".fancybox-thumbs__list a").length > 1 &&
		!$(".fancybox-thumbs .swiper").length &&
		typeof initSwiperSlider === "function"
	) {
		instance.Thumbs.$grid.find(".fancybox-thumbs__list a").each(function (i, el) {
			$(el).css("background-image", "url(\"" + location.origin + encodeURI(instance.group[i].src) + "\")");
		});
		const SLIDER_RESIZE_BREAKPOINT = 600;
		const $thumbsContainer = $(".fancybox-thumbs");
		const $swiperSlides = $(".fancybox-thumbs__list a");
		const $swiperWrapper = $(".fancybox-thumbs__list");
		const sliderDirection = (window.innerWidth >= SLIDER_RESIZE_BREAKPOINT ? 'vertical' : 'horizontal');

		const options = {
			centerInsufficientSlides: true,
			direction: sliderDirection,
			mousewheelControl: true,
			slidesPerView: 'auto',
			spaceBetween: 16,
			watchSlidesProgress: true,
		};

		options.navigation = {
			nextEl: ".fancybox-thumbs__wrapper .swiper-button-next",
			prevEl: ".fancybox-thumbs__wrapper .swiper-button-prev",
		};

		$('.fancybox-container').addClass('detail-gallery-big--vertical with-slider');
		$('.fancybox-inner').addClass('with-slider');
		$thumbsContainer.addClass('detail-gallery-big-slider-thumbs with-slider');
		$thumbsContainer.wrapInner(
			`<div class='fancybox-thumbs__wrapper gallery-slider-thumb__container'>
        <div class='swiper slider-solution' data-plugin-options='`+ JSON.stringify(options) + `'/>
      </div>`
		);
		const $swiperContainerWrapper = $('.fancybox-thumbs__wrapper');

		$swiperContainerWrapper.append(
			`<div class='gallery-slider-thumb-button gallery-slider-thumb-button--next slider-nav slider-nav--no-auto-hide swiper-button-next'>
        <i class='svg inline stroke-dark-light'><svg width='7' height='12'><use xlink:href='`+ arAsproOptions.SITE_TEMPLATE_PATH + `/images/svg/arrows.svg#right-7-12'></use></svg></i>
      </div>`
		);
		$swiperContainerWrapper.prepend(
			`<div class='gallery-slider-thumb-button gallery-slider-thumb-button--prev slider-nav slider-nav--no-auto-hide swiper-button-prev'>
        <i class='svg inline stroke-dark-light'><svg width='7' height='12'><use xlink:href='`+ arAsproOptions.SITE_TEMPLATE_PATH + `/images/svg/arrows.svg#left-7-12'></use></svg></i>
      </div>`
		);

		$swiperWrapper.addClass('fancybox-thumb__list--slider-wrapper swiper-wrapper gallery__thumb-wrapper');
		$swiperSlides.addClass('swiper-slide gallery__item gallery__item--thumb rounded-x pointer');
		$swiperSlides.each(function () {
			const background = $(this).css('background-image').replace(/url\(|\"|\'|\)/g, '');
			$(this).css('background-image', '');
			$(this).wrapInner("<img src='" + background + "' class='rounded-x' alt='' title='' />");
		})

		initSwiperSlider();

		const swiper = $thumbsContainer.find('.swiper').data('swiper');
		swiper.slideTo(instance.curPos, 10);
		swiper.on('resize', function () {
			if (window.innerWidth >= SLIDER_RESIZE_BREAKPOINT && options.direction === 'horizontal') {
				options.direction = 'vertical';
				swiper.changeDirection(options.direction, false);
				swiper.update();
			} else if (window.innerWidth < SLIDER_RESIZE_BREAKPOINT && options.direction === 'vertical') {
				options.direction = 'horizontal';
				swiper.changeDirection(options.direction, false);
				swiper.update();
			}
		})
	}
}
/** */
InitFancyBox = function () {
	if (typeof $.fn.fancybox !== "function") {
		return;
	}

	if (!$(".fancy").length) {
		return;
	}

	$(".fancy").fancybox({
		padding: [40, 40, 64, 40],
		openEffect: "fade",
		closeEffect: "fade",
		nextEffect: "fade",
		prevEffect: "fade",
		opacity: true,
		tpl: {
			closeBtn:
				'<span title="' +
				BX.message("FANCY_CLOSE") +
				'" class="fancybox-item fancybox-close inline svg"><svg class="svg svg-close" width="14" height="14" viewBox="0 0 14 14"><path data-name="Rounded Rectangle 568 copy 16" d="M1009.4,953l5.32,5.315a0.987,0.987,0,0,1,0,1.4,1,1,0,0,1-1.41,0L1008,954.4l-5.32,5.315a0.991,0.991,0,0,1-1.4-1.4L1006.6,953l-5.32-5.315a0.991,0.991,0,0,1,1.4-1.4l5.32,5.315,5.31-5.315a1,1,0,0,1,1.41,0,0.987,0.987,0,0,1,0,1.4Z" transform="translate(-1001 -946)"></path></svg></span>',
			next:
				'<a title="' +
				BX.message("FANCY_NEXT") +
				'" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
			prev:
				'<a title="' +
				BX.message("FANCY_PREV") +
				'" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>',
		},
		touch: "enabled",
		buttons: [
			//"zoom",
			//"share",
			// "slideShow",
			//"fullScreen",
			//"download",
			//"thumbs",
			"close",
		],
		backFocus: false,
		onActivate: function (instance) {
			InitFancyboxThumbnailsGallery(instance);
		},
		beforeShow: function (event) {
			if ($(".fancybox-thumbs .swiper").length) {
				const swiper = $(".fancybox-thumbs .swiper").data("swiper");

				if (typeof event.current !== 'undefined') {
					swiper.slideTo(event.current.index);
				}
			}
			var bCurrentSrc =
				typeof event.current !== "undefined" && event.current.contentType == "html" && $(event.current.src).length;
			var video_block = [];

			if (bCurrentSrc) {
				video_block = $(event.current.src).find("source.video-content");
			}
			// else {
			//   video_block = $(".company-item source.video-content");
			// }
			if (video_block.length) {
				if (video_block.attr("src") == "#") {
					var video_block_wrapper = video_block.closest("video");
					var video_block_clone = video_block_wrapper.clone();

					video_block_clone.find("source").attr("src", video_block_clone.find("source").data("src"));
					video_block.attr("src", video_block.data("src"));
					video_block_clone.insertAfter(video_block_wrapper);
					video_block_clone.siblings("video").remove();
				}
			}
			var video_block_frame = [];
			if (bCurrentSrc) {
				video_block_frame = $(event.current.src).find(".company-item__video-iframe");
			}
			// else {
			//   video_block_frame = $(".company-item .company-item__video-iframe");
			// }
			if (video_block_frame.length) {
				var data_src_iframe = video_block_frame.attr("data-src");
				video_block_frame.attr("src", data_src_iframe);
				video_block_frame.attr("allow", "autoplay");
			}
		},
		afterShow: function (event) {
			if ($(".fancybox-overlay").css("opacity") == 0) {
				setTimeout(function () {
					$(".fancybox-overlay").css("opacity", 1);
					$("html").addClass("overflow_html");
				}, 200);
			}

			$(".fancybox-nav").css("opacity", 0);
			setTimeout(function () {
				$(".fancybox-nav").css("opacity", 1);
			}, 150);

			var bCurrentSrc =
				typeof event.current !== "undefined" && event.current.contentType == "html" && $(event.current.src).length;
			var companyVideo = [];
			if (bCurrentSrc) {
				companyVideo = event.current.src[0].getElementsByClassName("company-item__video");
			}
			// else {
			//   companyVideo = $(".fancybox-inner .company-item__video");
			// }
			if (companyVideo.length) {
				setTimeout(function () {
					$(".fancybox-wrap video").resize();
					setTimeout(function () {
						$(".fancybox-wrap").addClass("show_video");
						if (companyVideo[0].currentTime === 0 || companyVideo[0].paused) {
							companyVideo[0].currentTime = 0;
							companyVideo[0].play();
						}
					}, 300);
				}, 150);
			} else if ($(".fancybox-wrap iframe").length) {
				$(".fancybox-inner").height("100%");
			}
		},
		beforeClose: function (event) {
			// closeModal();
			$(".fancybox-overlay").fadeOut();
			var bCurrentSrc =
				typeof event.current !== "undefined" && event.current.contentType == "html" && $(event.current.src).length;
			var companyVideo = [];
			if (bCurrentSrc) {
				companyVideo = event.current.src[0].getElementsByClassName("company-item__video");
			}
			// else {
			//   companyVideo = $(".fancybox-inner .company-item__video");
			// }
			if (companyVideo.length) {
				companyVideo[0].currentTime = 0;
			}

			$("html").removeClass("overflow_html");
			var video_block_frame = [];
			if (bCurrentSrc) {
				video_block_frame = $(event.current.src).find(".company-item__video-iframe");
			}
			// else {
			//   video_block_frame = $(".company-item .company-item__video-iframe");
			// }
			if (video_block_frame.length) {
				video_block_frame.attr("src", "");
			}
		},
		onClosed: function (event) {
			var companyVideo = [];
			if (bCurrentSrc) {
				companyVideo = event.current.src[0].getElementsByClassName("company-item__video");
			}
			// else {
			//   companyVideo = $(".fancybox-wrap .company-item__video");
			// }
			if (companyVideo.length) {
				companyVideo[0].pause();
			}
		},
	});
};

InitFancyBoxVideo = function () {
	if (typeof $.fn.fancybox !== "function") {
		return;
	}

	if (!$(".video_link").length) {
		return;
	}

	$(".video_link").fancybox({
		type: "iframe",
		iframe: {
			preload: false
		},
		maxWidth: 800,
		maxHeight: 600,
		fitToView: false,
		width: "70%",
		height: "70%",
		autoSize: false,
		closeClick: false,
		opacity: true,
		tpl: {
			closeBtn:
				'<span title="' +
				BX.message("FANCY_CLOSE") +
				'" class="fancybox-item fancybox-close inline svg"><svg class="svg svg-close" width="14" height="14" viewBox="0 0 14 14"><path data-name="Rounded Rectangle 568 copy 16" d="M1009.4,953l5.32,5.315a0.987,0.987,0,0,1,0,1.4,1,1,0,0,1-1.41,0L1008,954.4l-5.32,5.315a0.991,0.991,0,0,1-1.4-1.4L1006.6,953l-5.32-5.315a0.991,0.991,0,0,1,1.4-1.4l5.32,5.315,5.31-5.315a1,1,0,0,1,1.41,0,0.987,0.987,0,0,1,0,1.4Z" transform="translate(-1001 -946)"></path></svg></span>',
			next:
				'<a title="' +
				BX.message("FANCY_NEXT") +
				'" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
			prev:
				'<a title="' +
				BX.message("FANCY_PREV") +
				'" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>',
		},
		beforeShow: function (event) {
			/*if(!$('.cd-modal-bg').hasClass('is-visible')){
				var scaleValue = retrieveScale($('.cd-modal-bg'));

				$('.cd-modal-bg').show().addClass('is-visible').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
					animateLayer($('.cd-modal-bg'), scaleValue, true);
				});
			}*/
		},
		afterShow: function () {
			if ($(".fancybox-overlay").css("opacity") == 0) {
				setTimeout(function () {
					$(".fancybox-overlay").css("opacity", 1);
					$("html").addClass("overflow_html");
				}, 200);
			}

			$(".fancybox-nav").css("opacity", 0);
			setTimeout(function () {
				$(".fancybox-nav").css("opacity", 1);
			}, 150);
			if ($(".fancybox-wrap iframe").length) {
				$(".fancybox-inner").height("100%");
			}
		},
		beforeClose: function () {
			// closeModal();
			$(".fancybox-overlay").fadeOut();
			$("html").removeClass("overflow_html");
		},
	});
};


CheckTopVisibleMenu = function (that) {
	var dropdownMenu = $(that).find(">.header-menu__dropdown-menu");

	if (!dropdownMenu.length) {
		dropdownMenu = $(that).find(">.header-menu__wide-item-wrapper>.header-menu__wide-submenu");
	}
	if (!dropdownMenu.length) {
		dropdownMenu = $(that).find(">.header-menu__wide-submenu-item-inner>.submenu-wrapper>.header-menu__wide-submenu");
	}


	if (!dropdownMenu.length) {
		return;
	}

	// if( that !== undefined ) {
	// 	dropdownMenu.push($(that));
	// }

	if (dropdownMenu.length) {
		dropdownMenu.each(function (i, el) {
			var dropdownMenuCurrent = $(el);
			dropdownMenuCurrent.find("a").css("white-space", "");
			dropdownMenuCurrent.css("left", "");
			dropdownMenuCurrent.css("right", "");
			dropdownMenuCurrent.removeClass("toright");

			var dropdownMenuCurrent_left = dropdownMenuCurrent.offset().left;

			if (typeof dropdownMenuCurrent_left != "undefined") {
				var menu = dropdownMenuCurrent.parents("header");
				if (!menu.length) menu = dropdownMenuCurrent.closest(".header__top-inner");
				var menu_width = menu.outerWidth();
				var menu_left = menu.offset().left;
				var menu_right = menu_left + menu_width;
				var isToRight = dropdownMenuCurrent.parents(".toright").length > 0;
				var parentsdropdownMenuCurrents = dropdownMenuCurrent.parents(".header-menu__dropdown-menu");
				var isHasParentdropdownMenuCurrent = parentsdropdownMenuCurrents.length > 0;
				if (isHasParentdropdownMenuCurrent) {
					var parentdropdownMenuCurrent_width = parentsdropdownMenuCurrents.first().outerWidth()
						? parentdropdownMenuCurrent_width
						: 240;
					var parentdropdownMenuCurrent_left = parentsdropdownMenuCurrents.first().offset().left;
					var parentdropdownMenuCurrent_right = parentdropdownMenuCurrent_width + parentdropdownMenuCurrent_left;
				}

				var emptyCurrentWidth = dropdownMenuCurrent.outerWidth() == 0;

				var dropdownMenuCurrent_width = !emptyCurrentWidth ? dropdownMenuCurrent.outerWidth() : 240;

				if (parentdropdownMenuCurrent_right + dropdownMenuCurrent_width > menu_right) {
					dropdownMenuCurrent.find("a").css("white-space", "normal");
				}

				var dropdownMenuCurrent_right = dropdownMenuCurrent_left + dropdownMenuCurrent_width;

				if (dropdownMenuCurrent_right > menu_right || isToRight) {
					var addleft = 0;
					addleft = menu_right - dropdownMenuCurrent_right;
					if (isHasParentdropdownMenuCurrent || isToRight) {
						dropdownMenuCurrent.css("left", "auto");
						dropdownMenuCurrent.css("right", "calc(100% + 8px)");
						dropdownMenuCurrent.addClass("toright");
					} else {
						var dropdownMenuCurrent_curLeft = parseInt(dropdownMenuCurrent.css("left"));
						dropdownMenuCurrent.css("left", dropdownMenuCurrent_curLeft + addleft + "px");
					}
				}
				var firstMenuItem = dropdownMenuCurrent.parent().hasClass("header-menu__item--first");
				if (!firstMenuItem) {
					if (dropdownMenuCurrent_right < 240 + menu_left) {
						dropdownMenuCurrent.css("left", "calc(100% + 8px)");
						dropdownMenuCurrent.css("right", "0");
						dropdownMenuCurrent.removeClass("toright");
					}
					/*
					var childDropDown = dropdownMenuCurrent.find(">.dropdown");
					if (childDropDown.length) {
						childDropDown.css("left", "");
						childDropDown.css("right", "");
					}
					if (emptyCurrentWidth && childDropDown.length && dropdownMenuCurrent_right < 480 + menu_left) {
						dropdownMenuCurrent.css("left", "calc(100% + 8px)");
						dropdownMenuCurrent.css("right", "0");
						dropdownMenuCurrent.removeClass("toright");
						childDropDown.css("left", "calc(100% + 8px)");
						childDropDown.css("right", "0");
					}
					*/
				}
			}
		});
	}
};

MegaMenuFixed = function () {
	var animationTime = 150;
	$megaFixedNlo = $(".mega-fixed-menu").find("[data-nlo]");

	$(document).on("click", ".header .burger", function () {
		if ($megaFixedNlo.length) {
			if (!$megaFixedNlo.hasClass("nlo-loadings")) {
				$megaFixedNlo.addClass("nlo-loadings");
				setTimeout(function () {
					$.ajax({
						data: { nlo: $megaFixedNlo.attr("data-nlo") },
						error: function () {
							$megaFixedNlo.removeClass("nlo-loadings");
						},
						complete: function (jqXHR, textStatus) {
							if (textStatus === "success" || jqXHR.status == 404) {
								var ob = BX.processHTML($.trim(jqXHR.responseText));
								BX.ajax.processScripts(ob.SCRIPT);
								$megaFixedNlo.replaceWith(ob.HTML);
							}
						},
					});
				}, 300);
			}
		}

		$("body").css("overflow", "hidden");
		$(".mega-fixed-menu").fadeIn(animationTime);
		$(".mega-fixed-menu").css("display", "flex");
	});

	$(".mega-fixed-menu__close").on("click", function () {
		$(this).closest(".mega-fixed-menu").fadeOut(animationTime);
		//$("body").css("overflow", "auto");
		$("body").css("overflow", "");
	});

	$(".mega-fixed-menu .dropdown-menu .arrow").on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).closest(".dropdown-submenu").find(".dropdown-menu").slideToggle(animationTime);
		$(this).closest(".dropdown-submenu").addClass("opened");
	});
};

CheckMainBannerSliderVText = function (slider) {
	/*if(slider.parents('.banners-big').length){
		var sh = slider.height();
		slider.find('.item').each(function() {
			var curSlideTextInner = $(this).find('.text .inner');
			if(curSlideTextInner.length){
				var ith = curSlideTextInner.actual('height');
				var p = (ith >= sh ? 0 : Math.floor((sh - ith) / 2));
				curSlideTextInner.css('padding-top', p + 'px');
			}
		});
	}*/
};

footerInit = function (block) {
	if (block.hasClass("check-visible")) {
		const blockJsPadding = $(".js-check-padding");
		const blockJsHide = $(".js-check-hide");

		if (blockJsPadding.length && blockJsHide.length && block.data("ajax-check-visible")) {
			const arCheckBlock = block.data("ajax-check-visible").split(",");
			let bHiddenBlocks = arCheckBlock.every(function (i) {
				return $('[data-ajax-load-block="' + i + '"]').hasClass("hidden") === true;
			});

			if (bHiddenBlocks) {
				blockJsHide.addClass("hidden");
				blockJsPadding.addClass("js-padding");
			} else {
				blockJsHide.removeClass("hidden");
				blockJsPadding.removeClass("js-padding");
			}
		}
	}
};

verticalAlign = function (class_name) {
	if (typeof class_name == "undefined") class_name = "auto_align";
	if ($("." + class_name).length) {
		maxHeight = 0;
		$("." + class_name).each(function () {
			if ($(this).height() > maxHeight) {
				maxHeight = $(this).height();
			}
		});
		$("." + class_name).each(function () {
			delta = Math.round((maxHeight - $(this).height()) / 2);
			$(this).css({ "padding-top": delta + "px", "padding-bottom": delta + "px" });
		});
	}
};

getGridSize = function (counts, custom_counts) {
	var z = parseInt($(".body_media").css("top"));
	if (typeof custom_counts != "undefined") {
		if (window.matchMedia("(max-width: 700px)").matches) return counts[3] ? counts[3] : counts[2];
		else if (window.matchMedia("(max-width: 850px)").matches) return counts[2];
		else if (window.matchMedia("(max-width: 1100px)").matches) return counts[1];
		else return counts[0];
	} else {
		if (window.matchMedia("(max-width: 600px)").matches) {
			return counts[3] ? counts[3] : counts[2];
		} else return z == 2 ? counts[0] : z == 1 ? counts[1] : counts[2];
	}
};

CheckFlexSlider = function () {
	$(".flexslider:not(.thmb):visible").each(function () {
		var slider = $(this);
		slider.resize();
		var counts = slider.data("flexslider").vars.counts,
			slide_counts = slider.data("flexslider").vars.slide_counts;
		if (typeof counts != "undefined") {
			var cnt = getGridSize(counts, slider.data("flexslider").vars.customGrid);
			var to0 =
				cnt != slider.data("flexslider").vars.minItems ||
				cnt != slider.data("flexslider").vars.maxItems ||
				cnt != slider.data("flexslider").vars.move;
			if (to0) {
				slider.data("flexslider").vars.minItems = cnt;
				slider.data("flexslider").vars.maxItems = cnt;
				if (typeof slide_counts != "undefined") slider.data("flexslider").vars.move = slide_counts;
				else slider.data("flexslider").vars.move = cnt;

				slider.flexslider(0);
				slider.resize();
				slider.resize(); // twise!
			}
		}
	});
};

CheckHeaderFixed = function () {
	var header = $("header .header__inner").first(),
		header_fixed = $("#headerfixed, body.simple_basket_mode .header_simple"),
		header_simple = $("body.simple_basket_mode .header_simple");

	if (header_fixed.length) {
		if (header.length) {
			var isHeaderFixed = false,
				headerCanFix = true,
				headerFixedHeight = header_fixed.actual("outerHeight"),
				headerNormalHeight = header.actual("outerHeight"),
				headerDiffHeight = headerNormalHeight - headerFixedHeight,
				mobileBtnMenu = $(".btn.btn-responsive-nav"),
				headerTop = $("#panel:visible").actual("outerHeight"),
				topBlock = $(".TOP_HEADER").first(),
				$headerFixedNlo = header_fixed.find("[data-nlo]"),
				isNloLoaded = !$headerFixedNlo.length,
				OnHeaderFixedScrollHandler;

			if (headerDiffHeight <= 0) headerDiffHeight = 0;

			if (topBlock.length) headerTop += topBlock.actual("outerHeight");

			$(window).scroll(
				(OnHeaderFixedScrollHandler = function () {
					var scrollTop = $(window).scrollTop();
					headerCanFix = !mobileBtnMenu.is(":visible");

					if (!isHeaderFixed) {
						if (headerCanFix && scrollTop > headerNormalHeight + headerTop) {
							isHeaderFixed = true;

							header_fixed.css("top", "-" + headerNormalHeight + "px");
							header_fixed.addClass("fixed");

							header_fixed.animate({ top: "0" }, { duration: 300, complete: function () { } });

							if (header_simple.length) {
								headerSimpleHeight = header_simple.actual("outerHeight");
								header_simple.parent().css({ "margin-top": headerSimpleHeight });
							}

							//CheckTopMenuDotted();
						}
					}

					if (isHeaderFixed || !headerCanFix) {
						if (!headerCanFix || scrollTop <= headerDiffHeight + headerTop) {
							isHeaderFixed = false;
							header_fixed.removeClass("fixed");

							if (header_simple.length) {
								header_simple.parent().css({ "margin-top": "" });
							}
						}
					}
				})
			);
		}
	}

	// mobile fixed
	var mfixed = $("body.mfixed_y.mfixed_view_scroll_top .headers-container");
	if (mfixed.length) {
		var mheaderFixed = false,
			mheaderFixedHeight = mfixed.actual("outerHeight"),
			mheaderTop = $("#panel:visible").actual("outerHeight"),
			mheaderStartScroll = 0;

		$(window).scroll(function () {
			if (window.matchMedia("(max-width:991px)").matches) {
				var scrollTop = $(window).scrollTop();
				if (!mheaderFixed) {
					if (scrollTop > mheaderTop + mheaderFixedHeight && scrollTop < mheaderStartScroll) {
						mfixed.addClass("fixed");
						mheaderFixed = true;
					}
				} else {
					if (scrollTop <= mheaderTop || scrollTop > mheaderStartScroll) {
						mfixed.removeClass("fixed");
						mheaderFixed = false;
					}
				}

				mheaderStartScroll = scrollTop;
			} else {
				mfixed.removeClass("fixed");
			}
		});
	}
};

CheckObjectsSizes = function () {
	$(".container iframe,.container object,.container video").each(function () {
		var height_attr = $(this).attr("height");
		var width_attr = $(this).attr("width");

		if (height_attr && width_attr) {
			$(this).css("height", ($(this).outerWidth() * height_attr) / width_attr);
		}
	});
};

var isMobile = jQuery.browser.mobile;
var players = {};

if (isMobile) {
	document.documentElement.className += " mobile";
}

var timerScroll = false,
	ignoreScroll = [],
	documentScrollTopLast = $(document).scrollTop(),
	startScroll = 0;

$.fn.equalizeHeights = function (outer, classNull, minHeight, autoHeightBlock) {
	var maxHeight = this.map(function (i, e) {
		var minus_height = 0,
			calc_height = 0;
		if (classNull !== false && $(e).find(classNull).is(":visible"))
			minus_height = parseInt($(e).find(classNull).actual("outerHeight"));
		if (minus_height) minus_height += 15;
		$(e).css("height", "");
		if (autoHeightBlock !== false) {
			var height_tmp = $(e).find(autoHeightBlock).css("height");
			$(e).find(autoHeightBlock).css("height", "");
		}
		if (outer === true) {
			calc_height = $(e).actual("outerHeight") - minus_height;
		} else {
			calc_height = $(e).actual("height") - minus_height;
		}

		if (autoHeightBlock !== false) {
			$(e).find(autoHeightBlock).css("height", height_tmp);
		}
		if (minHeight !== false) {
			if (calc_height < minHeight) {
				calc_height += minHeight - calc_height;
			}
			if (window.matchMedia("(max-width: 520px)").matches) {
				calc_height = 300;
			}
			if (window.matchMedia("(max-width: 400px)").matches) {
				calc_height = 200;
			}
		}
		return calc_height;
	}).get();

	for (var i = 0, c = maxHeight.length; i < c; ++i) {
		if (maxHeight[i] % 2) {
			--maxHeight[i];
		}
	}

	return this.height(Math.max.apply(this, maxHeight));
};

$.fn.getFloatWidth = function () {
	var width = 0;

	if ($(this).length) {
		var rect = $(this)[0].getBoundingClientRect();
		if (!(width = rect.width)) {
			width = rect.right - rect.left;
		}
	}

	return width;
};

$.fn.sliceHeight2 = function (options) {
	function _slice(el) {
		el.each(function () {
			$(this).css("line-height", "");
			$(this).css("height", "");
		});
		if (typeof options.autoslicecount == "undefined" || options.autoslicecount !== false) {
			var elsw =
				typeof options.row !== "undefined" && options.row.length
					? el.first().parents(options.row).getFloatWidth()
					: el.first().parents(".grid-list").getFloatWidth(),
				elw =
					typeof options.item !== "undefined" && options.item.length
						? $(options.item).first().getFloatWidth()
						: el.first().hasClass("grid-list__item")
							? el.first().getFloatWidth()
							: el.first().parents(".grid-list__item").getFloatWidth();

			if (!elsw) {
				elsw = el.first().parents(".grid-list").getFloatWidth();
			}
			if (elw && options.fixWidth) elw -= options.fixWidth;

			if (elsw && elw) {
				options.slice = Math.floor(elsw / elw);
			}
		}
		if (typeof options.typeResize == "undefined" || options.typeResize === false) {
			if (options.sliceEqualLength && el.closest(".flexslider").length) options.slice = el.length;
			if (options.slice >= 1) {
				for (var i = 0; i < el.length; i += options.slice) {
					$(el.slice(i, i + options.slice)).equalizeHeights(
						options.outer,
						options.classNull,
						options.minHeight,
						options.autoHeightBlock
					);
				}
			}
			if (options.lineheight) {
				var lineheightAdd = parseInt(options.lineheight);
				if (isNaN(lineheightAdd)) {
					lineheightAdd = 0;
				}
				el.each(function () {
					$(this).css("line-height", $(this).actual("height") + lineheightAdd + "px");
				});
			}
		}

		if (typeof options.callback == "function") options.callback(el);
	}
	var options = $.extend(
		{
			slice: null,
			outer: false,
			lineheight: false,
			autoslicecount: true,
			classNull: false,
			minHeight: false,
			row: false,
			item: false,
			typeResize: false,
			resize: true,
			typeValue: false,
			sliceEqualLength: false,
			fixWidth: 0,
			callback: false,
			autoHeightBlock: false,
		},
		options
	);

	var el = $(this);
	ignoreResize.push(true);
	_slice(el);
	ignoreResize.pop();

	if (options.resize !== false) {
		BX.addCustomEvent("onWindowResize", function (eventdata) {
			ignoreResize.push(true);
			_slice(el);
			ignoreResize.pop();
		});
	}
};

function extendDepthObject(target, FromObj) {
	var to = Object.assign({}, target);
	for (var key in FromObj) {
		if (typeof FromObj[key] == "object") {
			to[key] = extendDepthObject(to[key], FromObj[key]);
		} else {
			to[key] = FromObj[key];
		}
	}

	return to;
}

$.fn.sliceHeight = function (options) {
	function _slice(el) {
		var arBreakpoints = Object.keys(options.breakpoint);
		var resizeOptionsTmp = {};
		if (arBreakpoints.length) {
			for (var key in arBreakpoints) {
				if (window.matchMedia(arBreakpoints[key].toString()).matches) {
					resizeOptionsTmp = options.breakpoint[arBreakpoints[key]];
				}
			}
		}

		var resizeOptions = extendDepthObject(options, resizeOptionsTmp);

		var blockNullClass =
			resizeOptions.blockNull !== undefined && resizeOptions.blockNull.class !== undefined
				? resizeOptions.blockNull.class
				: false;
		var parent =
			typeof resizeOptions.row !== "undefined" && resizeOptions.row.length
				? el.first().parents(resizeOptions.row)
				: el.first().parents(".items");
		var item = "";

		if (typeof resizeOptions.item !== "undefined" && resizeOptions.item.length) {
			if (blockNullClass) {
				$(resizeOptions.item).each(function (i, element) {
					_element = $(element);
					if (!_element.hasClass(blockNullClass)) {
						item = _element;
					}
				});
				if (!item)
					// if only ignored blocks
					return false;
			} else {
				item = $(resizeOptions.item).first();
			}
		} else {
			if (el.first().hasClass("item")) {
				item = el.first();
			} else {
				item = el.first().parents(".item");
			}
		}

		if (typeof resizeOptions.autoslicecount == "undefined" || resizeOptions.autoslicecount !== false) {
			var elsw = parent.getFloatWidth(),
				elw = item.getFloatWidth();
			if (!elsw) {
				elsw = el.first().parents(".row").getFloatWidth();
			}
			if (!elw) {
				if (typeof resizeOptions.item !== "undefined" && resizeOptions.item.length)
					elw = $(resizeOptions.item + ":eq(1)").getFloatWidth()
						? $(resizeOptions.item + ":eq(1)").getFloatWidth()
						: $(resizeOptions.item + ":eq(2)").getFloatWidth();
				else elw = $(el[1]).getFloatWidth() ? $(el[1]).getFloatWidth() : $(el[2]).getFloatWidth();
			}
			if (elw && resizeOptions.fixWidth) elw -= resizeOptions.fixWidth;

			elw = parseInt(elw * 100) / 100;

			if (elsw && elw) {
				resizeOptions.slice = Math.floor(elsw / elw);
			}
		}

		if (resizeOptions.customSlice) {
			//manual slice count
			var bSliceNext = false;
			if (resizeOptions.length) {
				elw =
					typeof resizeOptions.item !== "undefined" && resizeOptions.item.length
						? $(resizeOptions.item).last().getFloatWidth()
						: el.last().hasClass("item")
							? el.last().getFloatWidth()
							: el.last().parents(".item").getFloatWidth();
				if (elw) resizeOptions.sliceNext = Math.floor(elsw / elw);

				bSliceNext = true;
			}
		}

		elements = [];
		if (resizeOptions.classes !== undefined && resizeOptions.classes.length) {
			for (var i = 0; i < resizeOptions.classes.length; i++) {
				var items = $(resizeOptions.item).find(resizeOptions.classes[i]);
				elements.push(items);
			}
		}
		var elIndex = elements.push(el) - 1;

		if (resizeOptions.mobile === true) {
			if (window.matchMedia("(max-width: 550px)").matches) {
				for (var block = 0; block < elements.length; block++) {
					var items = $(elements[block]);
					items.css({
						"line-height": "",
						height: "",
					});
				}
				return;
			}
		}

		if (typeof resizeOptions.typeResize == "undefined" || resizeOptions.typeResize === false) {
			if (resizeOptions.slice) {
				for (var block = 0; block < elements.length; block++) {
					if (resizeOptions.currentRow) {
						var itemNumber = el.index(resizeOptions.currentItem);
						var rowCount = resizeOptions.slice;
						var rowNumber = Math.floor(itemNumber / rowCount);

						var items = $(elements[block].slice(rowNumber * rowCount, rowNumber * rowCount + rowCount));
						var classNull = block == elIndex ? resizeOptions.classNull : false;
						items.css({
							"line-height": "",
							height: "",
						});
						items.equalizeHeights(resizeOptions.outer, classNull, resizeOptions);
					} else {
						for (var i = 0; i < elements[block].length;) {
							if (resizeOptions.customSlice && resizeOptions.sliceNext && bSliceNext && i)
								//manual slice count
								resizeOptions.slice = resizeOptions.sliceNext;

							var slice = resizeOptions.slice;
							var items = $(elements[block].slice(i, i + slice));
							if (resizeOptions.blockNull !== undefined) {
								if (resizeOptions.blockNull.class !== undefined) {
									var counts = 0;
									items.each(function (i, item) {
										var _item = $(item);
										if (
											_item.hasClass(resizeOptions.blockNull.class) ||
											_item.closest("." + resizeOptions.blockNull.class).length
										) {
											counts++;
										}
									});
								}
							}
							if (counts) {
								slice -= (resizeOptions.blockNull.width - 1) * counts;
							}

							items = $(elements[block].slice(i, i + slice));
							if (items) {
								var classNull = block == elIndex ? resizeOptions.classNull : false;

								items.css({
									"line-height": "",
									height: "",
								});
								items.equalizeHeights(resizeOptions.outer, classNull, resizeOptions);
							}
							i += slice || 1;
						}
					}
				}
			}
			if (resizeOptions.lineheight) {
				var lineheightAdd = parseInt(resizeOptions.lineheight);
				if (isNaN(lineheightAdd)) {
					lineheightAdd = 0;
				}
				el.each(function () {
					$(this).css("line-height", $(this).actual("height") + lineheightAdd + "px");
				});
			}
		}
	}
	var options = $.extend(
		{
			slice: null,
			sliceNext: null,
			outer: false,
			lineheight: false,
			autoslicecount: true,
			classNull: false,
			minHeight: false,
			row: false,
			item: false,
			typeResize: false,
			typeValue: false,
			fixWidth: 0,
			resize: true,
			mobile: false,
			customSlice: false,
			breakpoint: {},
			classes: [],
		},
		options
	);

	var el = $(this);

	ignoreResize.push(true);
	_slice(el);
	ignoreResize.pop();

	if (options.resize) {
		BX.addCustomEvent("onWindowResize", function (eventdata) {
			try {
				ignoreResize.push(true);
				_slice(el);
			} catch (e) {
			} finally {
				ignoreResize.pop();
			}
		});
	} else {
		if (!ignoreResize.length) {
			// ignoreResize.push(true);
			_slice(el);
			// ignoreResize.pop();
		}
	}
};

waitingExists = function (selector, delay, callback) {
	if (typeof callback !== "undefined" && selector.length && delay > 0) {
		delay = parseInt(delay);
		delay = delay < 0 ? 0 : delay;

		if (!$(selector).length) {
			setTimeout(function () {
				waitingExists(selector, delay, callback);
			}, delay);
		} else {
			callback();
		}
	}
};

waitingNotExists = function (selectorExists, selectorNotExists, delay, callback) {
	if (
		typeof callback !== "undefined" &&
		selectorExists.length &&
		$(selectorExists).length &&
		selectorNotExists.length &&
		!$(selectorNotExists).length &&
		delay > 0
	) {
		delay = parseInt(delay);
		delay = delay < 0 ? 0 : delay;

		setTimeout(function () {
			if (selectorExists.length && !$(selectorNotExists).length) {
				callback();
			}
		}, delay);
	}
};

function onLoadjqm(name, hash, _this) {
	if (funcDefined("onlyCatalogMenuClose")) {
		onlyCatalogMenuClose();
	}

	if (hash.c.noOverlay === undefined || (hash.c.noOverlay !== undefined && !hash.c.noOverlay)) {
		$("body").addClass("jqm-initied");
	}

	$("body").addClass("swipeignore");

	if (typeof $(hash.t).data("ls") !== " undefined" && $(hash.t).data("ls")) {
		var ls = $(hash.t).data("ls"),
			ls_timeout = 0,
			v = "";

		if ($(hash.t).data("ls_timeout")) ls_timeout = $(hash.t).data("ls_timeout");

		ls_timeout = ls_timeout ? Date.now() + ls_timeout * 1000 : "";

		if (typeof localStorage !== "undefined") {
			var val = localStorage.getItem(ls);
			try {
				v = JSON.parse(val);
			} catch (e) {
				v = val;
			}
			if (v != null) {
				localStorage.removeItem(ls);
			}
			v = {};
			v["VALUE"] = "Y";
			v["TIMESTAMP"] = ls_timeout; // default: seconds for 1 day

			localStorage.setItem(ls, JSON.stringify(v));
		} else {
			var val = $.cookie(ls);
			if (!val) $.cookie(ls, "Y", { expires: ls_timeout }); // default: seconds for 1 day
		}

		var dopClasses = hash.w.find(".marketing-popup").data("classes");
		if (dopClasses) {
			hash.w.addClass(dopClasses);
		}
	}

	//update show password
	//show password eye
	if (hash.w.hasClass("auth_frame")) {
		hash.w.find(".form-group:not(.eye-password-ignore) [type=password]").each(function (item) {
			let inputBlock = $(this).closest(".input");
			if (inputBlock.length) {
				inputBlock.addClass("eye-password");
			} else {
				let labelBlock = passBlock.find(".label_block");
				let passBlock = $(this).closest(".form-group");
				if (labelBlock.length) {
					labelBlock.addClass("eye-password");
				} else {
					passBlock.addClass("eye-password");
				}
			}
		});
	}

	$.each($(hash.t).get(0).attributes, function (index, attr) {
		if (/^data\-autoload\-(.+)$/.test(attr.nodeName)) {
			var key = attr.nodeName.match(/^data\-autoload\-(.+)$/)[1];
			var el = $('input[name="' + key.toUpperCase() + '"]');
			if (!el.length) {
				//is form block
				el = $('input[data-sid="' + key.toUpperCase() + '"]');
			}

			var value = $(hash.t).data("autoload-" + key);
			value = value.toString().replace(/%99/g, "\\"); // replace symbol \

			el.val(BX.util.htmlspecialcharsback(value)).attr("readonly", "readonly");
			el.closest(".form-group").addClass("input-filed");
			el.attr("title", el.val());
		}
	});

	//show gift block
	if (hash.w.hasClass("send_gift_frame")) {
		let imgHtml, priceHtml, propsHtml;
		imgHtml = priceHtml = propsHtml = '';

		if ($('.detail-gallery-big link[itemprop="image"]').length) {
			imgHtml = '<img src="' + $('.detail-gallery-big link[itemprop="image"]').attr('href') + '" alt="" title="" />';
		}

		if ($('.catalog-detail__buy-block .price__new').length) {
			priceHtml = '<div class="item-block-info__price color_222">' + $('.catalog-detail__buy-block .price__new-val').text() + '</div>';
		}

		if ($('.catalog-block__offers .sku-props__item').length) {
			propsHtml = '<div class="item-block-info__props font_13">';
			$('.catalog-block__offers .sku-props__item').each(function () {
				propsHtml += '<div class="item-block-info__props-item">' + $(this).find('.sku-props__title').text() + '</div>';
			})
			propsHtml += '</div>';
		}

		$(
			'<div class="popup__item-block">' +
			'<div class="popup__item-block-title">' + BX.message("POPUP_GIFT_TEXT") + '</div>' +
			'<div class="popup__item-block-info bordered rounded-x grid-list grid-list--gap-20">' +
			'<div class="item-block-info__image grid-list__item">' + imgHtml + '</div>' +
			'<div class="item-block-info__text grid-list__item">' +
			'<div class="name">' + $("h1").text() + '</div>' +
			priceHtml +
			propsHtml +
			'</div>' +
			'</div>' +
			'</div>'
		).prependTo(hash.w.find(".form-body"));
	}

	if (hash.c.noOverlay === undefined || (hash.c.noOverlay !== undefined && !hash.c.noOverlay)) {
		let diffWidth;
		if ((diffWidth = window.innerWidth - document.documentElement.clientWidth)) {
			$("body").css({ "padding-right": diffWidth + "px" });
		}

		$("body").css({ overflow: "hidden", height: "100vh" });
		hash.w.closest("#popup_iframe_wrapper").css({ "z-index": 3000, display: "flex" });
	}

	var eventdata = { action: "loadForm" };
	BX.onCustomEvent("onCompleteAction", [eventdata, $(hash.t)[0]]);

	if ($(hash.t).data("autohide")) {
		$(hash.w).data("autohide", $(hash.t).data("autohide"));
	}

	if ($(hash.t).data("autoshow")) {
		eval($(hash.t).data("autoshow"));
	}

	if (name == "order_product") {
		if ($(hash.t).data("product")) {
			$('input[name="PRODUCT"]').closest(".form-group").addClass("input-filed");
			$('input[name="PRODUCT"]')
				.val($(hash.t).data("product"))
				.attr("readonly", "readonly")
				.attr("title", $('input[name="PRODUCT"]').val());
		}
	}
	if (name == "question") {
		if ($(hash.t).data("product")) {
			$('input[name="NEED_PRODUCT"]').closest(".form-group").addClass("input-filed");
			$('input[name="NEED_PRODUCT"]')
				.val($(hash.t).data("product"))
				.attr("readonly", "readonly")
				.attr("title", $('input[name="NEED_PRODUCT"]').val());
		}
	}

	//show one_click_buy block
	if (name === "ocb" && !$(hash.t).data('paramBuy_all_basket')) {
		const parent = hash.w.find(".flexbox").first();
		const item = _this.closest(".js-popup-block");

		var data = item.find("[data-item]").data("item");
		if (typeof data === "undefined" || !data) {
			data = {};
		}

		parent.addClass("flexbox--direction-row-reverse");

		parent.find(".form.popup").addClass("flex-grow-1");

		let goodsNode = '<div class="goods-popup"><div class="goods-popup__sticky">';

		//image block
		let imageNode = '<div class="goods-popup__image image-list-wrapper">';
		//stiker
		if (item.find(".sticker").length) {
			imageNode += '<div class="sticker sticker--upper">';
			imageNode += item.find(".sticker").html();
			imageNode += "</div>";
		}
		//image
		imageNode += '<div class="image">';
		imageNode +=
			'<img class="img-responsive" src="' +
			(item.find(".image-list__link link[itemprop=image]").length
				? item.find(".image-list__link link[itemprop=image]:first").attr("href")
				: item.find(".image-list-wrapper img:first").length
					? item.find(".image-list-wrapper img:first").attr("src")
					: arAsproOptions.SITE_TEMPLATE_PATH + "/images/svg/noimage_product.svg") +
			'" />';
		imageNode += "</div>";
		imageNode += "</div>";
		goodsNode += imageNode;

		//info block
		let infoNode = '<div class="goods-popup__info">';
		//link
		if (item.find(".js-popup-title").attr("href")) {
			infoNode +=
				'<a class="dark_link switcher-title goods-popup__info-link font_15" href="' +
				item.find(".js-popup-title").attr("href") +
				'">';
			infoNode += data ? data.NAME : item.find(".js-popup-title").html();
			infoNode += "</a>";
		} else {
			infoNode += '<div class="color_222 switcher-title goods-popup__info-link font_15">';
			infoNode += data ? data.NAME : item.find(".js-popup-title").html();
			infoNode += "</div>";
		}

		// status
		let status = '';
		if (item.find(".status-container").length) {
			status += '<div class="line-block__item font_14">';
			status += item.find(".status-container").wrap('<div></div>').parent().html();
			status += '</div>';
		}

		//article
		let article = '';
		if (item.find('.js-popup-info .article').length) {
			article += '<div class="line-block__item font_13 color_999">';
			article += item.find('.js-popup-info .article').closest('.line-block__item').html();
			article += '</div>';
		}

		if (article || status) {
			infoNode += '<div class="line-block line-block--20 flexbox--wrap goods-popup__info-more">' + status + article + '</div>';
		}

		infoNode += "</div>";
		goodsNode += infoNode;

		//price block
		if (item.find(".js-popup-price .price").length) {
			let priceNode = '<div class="goods-popup__price">';
			priceNode += item.find(".js-popup-price").html();
			priceNode += "</div>";
			goodsNode += priceNode;

			let $price = item.find(".js-popup-price .price__new-val");
			if ($price.length) {
				parent.find('input[name=PRODUCT_PRICE],input[data-sid=PRODUCT_PRICE]').val($price.text().trim());
			}
		}

		goodsNode += "</div></div>";

		$(goodsNode).appendTo(parent);
	}

	if (name == "fast_view" && $(".smart-filter-filter").length) {
		var navButtons =
			'<div class="navigation-wrapper-fast-view">' +
			'<div class="fast-view-nav prev bg-theme-hover" data-fast-nav="prev">' +
			'<i class="svg left">' +
			'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6.969" viewBox="0 0 12 6.969"><path id="Rounded_Rectangle_702_copy_24" data-name="Rounded Rectangle 702 copy 24" class="cls-1" d="M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z" transform="translate(-350 -395.031)"/></svg>' +
			"</i>" +
			"</div>" +
			'<div class="fast-view-nav next bg-theme-hover" data-fast-nav="next">' +
			'<i class="svg right">' +
			'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6.969" viewBox="0 0 12 6.969"><path id="Rounded_Rectangle_702_copy_24" data-name="Rounded Rectangle 702 copy 24" class="cls-1" d="M361.691,401.707a1,1,0,0,1-1.414,0L356,397.416l-4.306,4.291a1,1,0,0,1-1.414,0,0.991,0.991,0,0,1,0-1.406l5.016-5a1.006,1.006,0,0,1,1.415,0l4.984,5A0.989,0.989,0,0,1,361.691,401.707Z" transform="translate(-350 -395.031)"/></svg>' +
			"</i>" +
			"</div>" +
			"</div>";

		// hash.w.addClass("no_custom_scroll");
		hash.w.closest("#popup_iframe_wrapper").append(navButtons);
	}

	var needScrollbar = true;

	if (
		arAsproOptions["THEME"]["REGIONALITY_SEARCH_ROW"] == "Y" &&
		(hash.w.hasClass("city_chooser_frame") || hash.w.hasClass("city_chooser_small_frame"))
	) {
		hash.w.addClass("small_popup_regions");
		hash.w.addClass("jqmWindow--overflow-visible");
		needScrollbar = false;
	}

	/*if (!hash.w.hasClass("no_custom_scroll")) {
		hash.w.addClass("show srollbar-custom").css({ opacity: 1 });
		InitScrollBar();
	}*/
	hash.w.addClass("show").css({ opacity: 1 });
	if (needScrollbar)
		hash.w.find(">div").addClass("scrollbar");
}

function onHidejqm(name, hash) {
	if (
		name === "ocb"
		&& hash.w.hasClass('ocb_basket')
		&& hash.w.find("#one_click_buy_result").is(":visible")
	) {
		window.location.href = window.location.origin + arAsproOptions.SITE_DIR;
	}

	if ($(hash.w).data("autohide")) {
		eval($(hash.w).data("autohide"));
	}

	// hash.w.css('opacity', 0).hide();
	hash.w.animate({ opacity: 0 }, 200, function () {
		// hash.w.removeClass("scroll-init srollbar-custom").mCustomScrollbar("destroy");
		hash.w.hide();
		hash.w.empty();
		hash.o.remove();
		hash.w.removeClass("show");
		hash.w.removeClass("success");

		if (!hash.w.closest("#popup_iframe_wrapper").find(".jqmOverlay").length) {
			$("body").css({ overflow: "", height: "", "padding-right": "" });
			hash.w.closest("#popup_iframe_wrapper").css({ "z-index": "", display: "" });
		}

		if (window.matchMedia("(max-width: 991px)").matches) {
			$("body").removeClass("all_viewed");
		}
		if (!$(".jqmOverlay:not(.mobp)").length || $(".jqmOverlay.waiting").length) {
			$("body").removeClass("jqm-initied");
		}

		$("body").removeClass("swipeignore");
		$("body").removeClass("overflow-block");

		if (name == "fast_view") {
			$(".fast_view_popup").remove();

			var navButtons = hash.w.closest("#popup_iframe_wrapper").find(".navigation-wrapper-fast-view");
			navButtons.remove();
		}

		if (name == 'ocb' && !hash.w.hasClass('ocb_basket')) {
			hash.w.removeClass('compact');
		}

		if (name == "stores") {
			window.GLOBAL_arMapObjects = {};
		}
	});

	window.b24form = false;
}

function parseUrlQuery() {
	var data = {};
	if (location.search) {
		var pair = location.search.substr(1).split("&");
		for (var i = 0; i < pair.length; i++) {
			var param = pair[i].split("=");
			data[param[0]] = param[1];
		}
	}
	return data;
}

function scroll_block(block) {
	if (block.length) {
		var topPos = block.offset().top,
			headerH = $("header").outerHeight(true, true);
		if ($(".stores_tab").length) {
			$(".stores_tab").addClass("active").siblings().removeClass("active");
		} else {
			$(".prices_tab").addClass("active").siblings().removeClass("active");
			if ($(".prices_tab .opener").length && !$(".prices_tab .opener .opened").length) {
				var item = $(".prices_tab .opener").first();
				item.find(".opener_icon").addClass("opened");
				item.parents("tr").addClass("nb");
				item.parents("tr").next(".offer_stores").find(".stores_block_wrap").slideDown(200);
			}
		}
		$("html,body").animate({ scrollTop: topPos - 80 }, 150);
	}
}

$.fn.jqmEx = function (onLoad, onHide) {
	$(this).each(function () {
		let $this = $(this);
		let name = $this.data("name");
		name = typeof name === "undefined" || !name.length ? "noname" : name;

		let paramsStr = "";
		let trigger = $this;

		// trigger attrs and params
		$.each($this.get(0).attributes, function (index, attr) {
			var attrName = attr.nodeName;
			var attrValue = $this.attr(attrName);
			if (/^data\-param\-(.+)$/.test(attrName)) {
				var key = attrName.match(/^data\-param\-(.+)$/)[1];
				paramsStr += key + "=" + attrValue.replace(/ /g, '+') + "&";
			}
		});

		// one click buy with sale
		if (
			name == "ocb" &&
			arAsproOptions.MODULES.sale
		) {
			let $buyAction = $this.closest('.buy_block').find('.js-item-action[data-action="basket"]');
			if ($buyAction.length) {
				let action = JItemAction.factory($buyAction[0]);
				if (action.valid) {
					let dataItem = action.data;
					if (dataItem) {
						paramsStr += 'ELEMENT_ID=' + dataItem.ID + '&';
						paramsStr += 'IBLOCK_ID=' + dataItem.IBLOCK_ID + '&';
						paramsStr += 'ELEMENT_QUANTITY=' + action.quantity + '&';
					}

					let props = action.node.getAttribute('data-props') || '';
					try {
						props = props.length ? props.split(';') : [];
					}
					catch (e) {
						props = [];
					}

					paramsStr += 'OFFER_PROPS=' + JSON.stringify(props) + '&';
				}
			}
		}

		// popup url
		let script = arAsproOptions["SITE_DIR"] + "ajax/form.php";
		if (name == "auth") {
			script += "?" + paramsStr + "auth=Y";
		} else {
			script += "?" + paramsStr;
		}

		// ext frame class
		let extClass = "";
		if ($this.closest("#fast_view_item").length) {
			extClass = "fast_view_popup";
		}

		if (name == "ocb" && $this.data('paramBuy_all_basket')) {
			extClass = 'compact ocb_basket';
		}

		// use overlay?
		var noOverlay = $this.data("noOverlay") == "Y";

		// call counter
		if (typeof $.fn.jqmEx.counter === "undefined") {
			$.fn.jqmEx.counter = 0;
		} else {
			++$.fn.jqmEx.counter;
		}

		// unique frame to each trigger
		if (noOverlay) {
			var frame = $(
				'<div class="' +
				name +
				"_frame " +
				extClass +
				' jqmWindow popup" data-popup="' +
				$.fn.jqmEx.counter +
				'"></div>'
			).appendTo("body");
		} else {
			var frame = $(
				'<div class="' +
				name +
				"_frame " +
				extClass +
				' jqmWindow popup" data-popup="' +
				$.fn.jqmEx.counter +
				'"></div>'
			).appendTo("#popup_iframe_wrapper");
		}

		frame.jqm({
			ajax: script,
			trigger: trigger,
			noOverlay: noOverlay,
			onLoad: function (hash) {
				onLoadjqm(name, hash, $this);

				if (typeof onLoad === 'function') {
					onLoad(name, hash, $this);
				}
			},
			onHide: function (hash) {
				onHidejqm(name, hash, $this);

				if (typeof onHide === 'function') {
					onHide(name, hash, $this);
				}
			},
		});
	});
};

InitOwlSlider = function () {
	$(".owl-carousel:not(.owl-loaded):not(.appear-block)").each(function () {
		var slider = $(this);
		var options;
		var svg =
			'<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 1L1 6L6 11" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		var defaults = {
			navText: [
				'<i class="owl-carousel__button owl-carousel__button--left colored_theme_bg_hover">' + svg + "</i>",
				'<i class="owl-carousel__button owl-carousel__button--right colored_theme_bg_hover">' + svg + "</i>",
			],
			dotsContainer: slider.siblings(".owl-carousel__dots"),
		};
		var config = $.extend({}, defaults, options, slider.data("plugin-options"));

		slider.siblings(".owl-carousel__dots").on("click", ".owl-carousel__dot", function () {
			var _this = $(this);
			var sliderData = slider.data("owl.carousel");

			slider.trigger("to.owl.carousel", [_this.index(), 300]);
			if (sliderData.settings.autoplayHoverPause) {
				slider.trigger("mouseover.owl.autoplay");
			}
		});

		if (slider.hasClass("destroyed")) {
			slider.owlCarousel(config);
			slider.removeClass("destroyed");
			return;
		}

		slider.on("initialized.owl.carousel", function (event) {
			var eventdata = { slider: $(event.target), data: event };

			BX.onCustomEvent("onSliderInitialized", [eventdata]);
			BX.onCustomEvent("onSlide", [eventdata]);

			$(event.target).removeClass("loading-state");
			$(event.target).find(".owl-item:first").addClass("current");

			if (typeof sliceItemBlockSlide === "function") {
				sliceItemBlockSlide();
			}
		});

		// if slider was inited on animated scrolling to block, than need rescroll
		if ($("body.scrolling-state").length) {
			slider.on("initialized.owl.carousel", function (event) {
				setTimeout(function () {
					scrollToBlock.rescroll();
				}, 100);
			});
		}

		slider.on("change.owl.carousel", function (event) {
			// var eventdata = {slider: $(event.target)};
			// BX.onCustomEvent('onSlide', [eventdata]);
		});

		slider.owlCarousel(config);

		slider.on("resized.owl.carousel", function (event) {
			if (typeof sliceItemBlockSlide === "function") {
				sliceItemBlockSlide({ resize: false });
			}
		});

		slider.on("mouseover.owl.autoplay", function (event) {
			var slider = $(event.target).closest(".owl-carousel");
			var sliderData = slider.data("owl.carousel");
			var dots = slider.siblings(".owl-carousel__dots--autoplay");
			if (typeof sliderData !== "undefined") {
				var bNeedStopDotsAnimation =
					sliderData.settings.autoplay && sliderData.settings.autoplayHoverPause && dots.length;
				if (bNeedStopDotsAnimation) {
					var animationBlock = dots.find(".owl-carousel__dot .owl-carousel__dot-pie");
					if (animationBlock.length) {
						animationBlock.css("animation-play-state", "paused");
					}
				}
			}
		});

		slider.on("mouseleave.owl.autoplay", function (event) {
			var slider = $(event.target).closest(".owl-carousel");
			var sliderData = slider.data("owl.carousel");
			var dots = slider.siblings(".owl-carousel__dots--autoplay");
			if (typeof sliderData !== "undefined") {
				var bNeedStopDotsAnimation =
					sliderData.settings.autoplay && sliderData.settings.autoplayHoverPause && dots.length;
				var rotating = sliderData._states.current.rotating;
				if (bNeedStopDotsAnimation && rotating) {
					var animationBlock = dots.find(".owl-carousel__dot .owl-carousel__dot-pie");
					if (animationBlock.length) {
						animationBlock.css("animation-play-state", "");
					}
				}
			}
		});

		slider.on("stop.owl.autoplay", function (event) {
			var slider = $(event.target);
			var sliderData = slider.data("owl.carousel");
			var dots = slider.siblings(".owl-carousel__dots--autoplay");
			if (typeof sliderData !== "undefined") {
				var bNeedStopDotsAnimation =
					sliderData.settings.autoplay && sliderData.settings.autoplayHoverPause && dots.length;
				if (bNeedStopDotsAnimation) {
					var animationBlock = dots.find(".owl-carousel__dot .owl-carousel__dot-pie");
					if (animationBlock.length) {
						dots.find(".owl-carousel__dot.active").addClass("reset-animation");
						animationBlock.css("animation-play-state", "paused");
					}
				}
			}
		});

		slider.on("play.owl.autoplay", function (event) {
			var slider = $(event.target);
			var sliderData = slider.data("owl.carousel");
			var dots = slider.siblings(".owl-carousel__dots--autoplay");
			if (typeof sliderData !== "undefined") {
				var bNeedStopDotsAnimation =
					sliderData.settings.autoplay && sliderData.settings.autoplayHoverPause && dots.length;
				dots.find(".owl-carousel__dot.reset-animation").removeClass("reset-animation");
				if (bNeedStopDotsAnimation && !slider.is(":hover") && !dots.is(":hover")) {
					var animationBlock = dots.find(".owl-carousel__dot .owl-carousel__dot-pie");
					if (animationBlock.length) {
						animationBlock.css("animation-play-state", "");
					}
				}
			}
		});

		slider.on("touchstart.owl.core", function (event) {
			slider.trigger("stop.owl.autoplay");
		});

		slider.siblings(".owl-carousel__dots--autoplay").on("mouseover", function (event) {
			var sliderData = slider.data("owl.carousel");
			if (sliderData.settings.autoplayHoverPause) {
				slider.trigger("mouseover.owl.autoplay");
			}
		});

		slider.on("changed.owl.carousel", function (event) {
			var $slider = $(event.target);
			var sliderData = $slider.data("owl.carousel");
			var pluginOptions = $slider.data("pluginOptions");

			var eventdata = { slider: $slider, data: event };
			BX.onCustomEvent("onSlide", [eventdata]);

			if (pluginOptions) {
				// click .ajax_load_btn
				if ($slider.parent().find(".ajax_load_btn").length) {
					if (sliderData._current + 1 + sliderData.settings.items >= sliderData._items.length) {
						$slider.parent().find(".ajax_load_btn").trigger("click");
					}
				}
				if (typeofExt(pluginOptions) === "object") {
					if ("index" in pluginOptions) {
						if ($(".gallery-view_switch").length) {
							$(".gallery-view_switch__count-wrapper--big .gallery-view_switch__count-value").text(
								event.item.index + 1 + "/" + event.item.count
							);
						}
					}

					if ("relatedTo" in pluginOptions) {
						var relatedClass = pluginOptions.relatedTo,
							relatedBlock = $(relatedClass);

						if (relatedBlock.length && sliderData) {
							if (!sliderData.loop) {
								var current = event.item.index;
							} else {
								var count = event.item.count - 1;
								var current = Math.round(event.item.index - event.item.count / 2 - 0.5);

								if (current < 0) current = count;

								if (current > count) current = 0;
							}

							relatedBlock.find(".owl-item").removeClass("current").eq(current).addClass("current");

							var onscreen = relatedBlock.find(".owl-item.active").length - 1;
							var start = relatedBlock.find(".owl-item.active").first().index();
							var end = relatedBlock.find(".owl-item.active").last().index();

							if (current > end) relatedBlock.data("owl.carousel").to(current, 100, true);

							if (current < start) relatedBlock.data("owl.carousel").to(current - onscreen, 100, true);
							// $(".owl-slider-"+id).trigger('to.owl.carousel', [itemCarousel])
						}
					}
				}
			}
		});

		slider.on("translated.owl.carousel", function (event) { });

		if ("clickTo" in config) {
			var relatedClass = config.clickTo,
				magnifier = "magnifier" in config;

			slider.on("click", ".owl-item", function (e) {
				e.preventDefault();
				var _this = $(this),
					number = _this.index();

				if (magnifier) {
					if ($(relatedClass).closest(".product-container").find(".zoom_picture").length) {
						$(relatedClass)
							.closest(".product-container")
							.find(".zoom_picture")
							.attr("data-large", _this.find(".product-detail-gallery__item").data("big"));
						$(relatedClass)
							.closest(".product-container")
							.find(".zoom_picture")
							.attr("xoriginal", _this.find(".product-detail-gallery__item").data("big"));
						$(relatedClass)
							.closest(".product-container")
							.find(".zoom_picture")
							.attr("src", _this.find(".product-detail-gallery__item img").attr("src"));
					}
					_this.siblings("").removeClass("current");
					_this.addClass("current");
				} else {
					$(relatedClass).data("owl.carousel").to(number, 300, true);
				}
			});
		}
	});
};

InitFlexSlider = function () {
	$(".flexslider:not(.thmb):not(.flexslider-init):not(.appear-block):visible").each(function () {
		var slider = $(this);
		var options;
		var defaults = {
			animationLoop: false,
			controlNav: false,
			directionNav: true,
			animation: "slide",
		};
		var config = $.extend({}, defaults, options, slider.data("plugin-options"));
		if (typeof config.counts != "undefined" && config.direction !== "vertical") {
			var slide_counts = "";
			if (typeof slider.data("plugin-options") != "undefined") {
				if ("slide_counts" in slider.data("plugin-options")) slide_counts = slider.data("plugin-options").slide_counts;
			}
			config.maxItems = getGridSize(config.counts);
			config.minItems = getGridSize(config.counts);

			if (slide_counts) config.move = slide_counts;
			else config.move = getGridSize(config.counts);

			config.itemWidth = 200;
		}

		// custom direction nav
		if (typeof config.customDirection != "undefined") config.customDirectionNav = $(config.customDirection);

		(config.prevText = BX.message("FANCY_PREV")), //String: Set the text for the "previous" directionNav item
			(config.nextText = BX.message("FANCY_NEXT")),
			(config.after = config.start =
				function (slider) {
					var eventdata = { slider: slider };
					BX.onCustomEvent("onSlide", [eventdata]);
				});

		config.before = function (slider) {
			var eventdata = { slider: slider };
			BX.onCustomEvent("onSlideBefore", [eventdata]);
		};

		config.end = function (slider) {
			var eventdata = { slider: slider };
			BX.onCustomEvent("onSlideEnd", [eventdata]);
		};

		/*if(typeof(config.nav) == 'undefined')
			slider.addClass('dark-nav');*/
		slider.flexslider(config).addClass("flexslider-init");
		if (config.controlNav) slider.addClass("flexslider-control-nav");
		if (config.directionNav) slider.addClass("flexslider-direction-nav");
	});
};

InitFlexSliderClass = function (class_name) {
	//$('.flexslider:not(.thmb):not(.flexslider-init)').each(function(){

	var slider = $(class_name);
	var options;
	var defaults = {
		animationLoop: false,
		controlNav: false,
		directionNav: true,
		animation: "slide",
	};
	var config = $.extend({}, defaults, options, slider.data("plugin-options"));

	var slide_counts = "";
	if (typeof slider.data("plugin-options") != "undefined") {
		if ("slide_counts" in slider.data("plugin-options")) slide_counts = slider.data("plugin-options").slide_counts;
	}

	if (typeof config.counts != "undefined" && config.direction !== "vertical") {
		config.maxItems = getGridSize(config.counts);
		config.minItems = getGridSize(config.counts);
		config.move = getGridSize(config.counts);

		config.itemWidth = 200;
	}
	if (slide_counts) config.move = slide_counts;

	// custom direction nav
	if (typeof config.customDirection != "undefined") config.customDirectionNav = $(config.customDirection);

	(config.prevText = BX.message("FANCY_PREV")), //String: Set the text for the "previous" directionNav item
		(config.nextText = BX.message("FANCY_NEXT")),
		(config.after = config.start =
			function (slider) {
				var eventdata = { slider: slider };
				BX.onCustomEvent("onSlide", [eventdata]);
			});

	config.before = function (slider) {
		var eventdata = { slider: slider };
		BX.onCustomEvent("onSlideBefore", [eventdata]);
	};

	config.end = function (slider) {
		var eventdata = { slider: slider };
		BX.onCustomEvent("onSlideEnd", [eventdata]);
	};

	slider.flexslider(config).addClass("flexslider-init");
	if (config.controlNav) slider.addClass("flexslider-control-nav");
	if (config.directionNav) slider.addClass("flexslider-direction-nav");
	//});
};

InitFlexSliderMin = function () {
	$(".bxSlider.top-small").on("mousedown", ".slides li", function () {
		var index = $(this).data("slide_key");
		$(this).siblings().removeClass("flex-active-slide");
		$(this).addClass("flex-active-slide");
		$(this)
			.siblings('[data-slide_key="' + index + '"]')
			.addClass("flex-active-slide");
		$(".top-bigs").data("flexslider").flexAnimate(index);
	});
};

InitAppear = function () {
	// progress bar
	$("[data-appear-progress-animation]").iAppear(
		function () {
			var $this = $(this);

			var delay = $this.attr("data-appear-animation-delay") ? $this.attr("data-appear-animation-delay") : 1;
			if (delay > 1) $this.css("animation-delay", delay + "ms");
			$this.addClass($this.attr("data-appear-animation"));

			setTimeout(function () {
				$this.animate(
					{
						width: $this.attr("data-appear-progress-animation"),
					},
					1500,
					"easeOutQuad",
					function () {
						$this.find(".progress-bar-tooltip").animate(
							{
								opacity: 1,
							},
							500,
							"easeOutQuad"
						);
					}
				);
			}, delay);
		},
		{
			rootMargin: "-50px 0px -50px 0px",
			accX: 0,
			accY: -50,
		}
	);

	// portfolio item
	$(".item.animated-block").iAppear(function () {
		$(this).addClass($(this).data("animation")).addClass("visible");
	});

	// flexslider appear
	$(".appear-block").iAppear(function () {
		$(this).removeClass("appear-block");
		const runAppearSliders = function () {
			CheckFlexSlider();
			InitFlexSlider();
			InitOwlSlider();
			if (typeof initSwiperSlider === "function") {
				initSwiperSlider();
			}
		};

		if (typeof Swiper === "function") {
			runAppearSliders();
		} else if (typeof BX !== "undefined" && typeof BX.loadExt === "function") {
			BX.loadExt(["aspro_swiper"]).then(runAppearSliders).catch(runAppearSliders);
		} else {
			runAppearSliders();
		}
	});
};

SliceHeightBlocks = function () {
	$('*[data-slice="Y"]').each(function () {
		var slice_els = $(this).find('*[data-slice-block="Y"]');
		if (slice_els.length) {
			var slice_params = {};
			if (slice_els.data("slice-params")) slice_params = slice_els.data("slice-params");
			slice_els.sliceHeight(slice_params);
		}
	});
};

createTableCompare = function (originalTable, appendDiv, cloneTable) {
	try {
		if ($(".tarifs .head-block:visible").length) {
			var clone = originalTable.clone().addClass("clone");
			if (cloneTable.length) {
				cloneTable.remove();
				appendDiv.html("");
				appendDiv.html(clone);
			} else {
				appendDiv.append(clone);
			}
		}
	} catch (e) {
	} finally {
	}
};

InitTopestMenuGummi = function () {
	if (!isOnceInited) {
		function _init() {
			var arItems = $menuTopest.find(".menu-topest__item");
			var cntItems = arItems.length;
			if (cntItems) {
				var itemsWidth = 0;
				for (var i = 0; i < cntItems; ++i) {
					var item = arItems.eq(i);
					var itemWidth = item.actual("outerWidth", { includeMargin: true });

					arItemsHideWidth[i] = (itemsWidth += itemWidth) + (i == cntItems - 1 ? 0 : moreWidth);
				}
			}
		}

		function _gummi() {
			var rowWidth = $menuTopest.actual("innerWidth");
			var arItems = $menuTopest.find(".menu-topest__item");
			var cntItems = arItems.length;
			var arItemsToRemove = [];

			if (cntItems) {
				var bMore = false;
				var moreItems = [];
				for (var i = cntItems - 1; i >= 0; --i) {
					var item = arItems.eq(i);
					var bInMore = item.parents(".menu-topest__more").length > 0;
					if (!bInMore) {
						if (arItemsHideWidth[i] > rowWidth + 1) {
							if (!bMore) {
								bMore = true;
								more.removeClass("hidden");
							}
							var clone = item.clone();
							clone.find(">a").addClass("dark_font");
							moreItems.push(clone[0]);
							arItemsToRemove.push(i);
						} else {
							break;
						}
					}
				}
				$(moreItems).prependTo(moreDropdown);

				if (!bMore) {
					for (var i = 0; i < cntItems; ++i) {
						var item = arItems.eq(i);
						var bInMore = item.parents(".menu-topest__more li").length > 0;
						if (bInMore) {
							if (arItemsHideWidth[i] <= rowWidth) {
								if (i === cntItems - 1) {
									bMore = false;
									more.addClass("hidden");
								}
								var clone = item.clone();
								clone.find(">a").removeClass("dark_font");
								clone.insertBefore(more);
								item.addClass("cloned");
							}
						}
					}
				}

				if (arItemsToRemove.length) {
					arItemsToRemove.map(function (elIndex) {
						$(arItems[elIndex]).remove();
					});
				}

				$menuTopest.find(".menu-topest__item.cloned").remove();
				$menuTopest.closest(".menu-topest__wrapper").addClass("dotted-complete");
				var wrapper = $menuTopest.closest(".hide-dotted");
				if (wrapper.length) {
					wrapper.addClass("dotted-complete");
				}
			}
		}

		var $menuTopest = $(".menu-topest");
		if ($menuTopest.length) {
			var more = $menuTopest.find(".menu-topest__more");
			var moreDropdown = more.find(">.dropdown");
			var moreWidth = more.actual("outerWidth", { includeMargin: true });
			var arItemsHideWidth = [];

			ignoreResize.push(true);
			_init();
			_gummi();
			ignoreResize.pop();

			BX.addCustomEvent("onWindowResize", function (eventdata) {
				try {
					ignoreResize.push(true);
					_gummi();
				} catch (e) {
				} finally {
					ignoreResize.pop();
				}
			});
		}
	}
};

CheckHeaderFixedMenu = function () {
	if (
		arAsproOptions["THEME"]["HEADER_FIXED"] == 2 &&
		$("#headerfixed .js-nav").length &&
		window.matchMedia("(min-width: 992px)").matches
	) {
		$("#headerfixed .js-nav").css("width", "0");
		var all_width = 0,
			cont_width = $("#headerfixed .maxwidth-theme").actual("width"),
			padding_menu =
				$("#headerfixed .logo-row.v2 .menu-block").actual("outerWidth") -
				$("#headerfixed .logo-row.v2 .menu-block").actual("width");
		$("#headerfixed .logo-row.v2 > .inner-table-block").each(function () {
			if (!$(this).hasClass("menu-block")) all_width += $(this).actual("outerWidth");
		});
		$("#headerfixed .js-nav").width(cont_width - all_width - padding_menu);
	}
};

/*function CheckTopMenuPadding() {
	if ($(".logo_and_menu-row .right-icons .wrap_icon").length && $(".logo_and_menu-row .menu-row").length) {
		var menuPosition = $(".menu-row .menu-only").position().left,
			maxWidth = $(".logo_and_menu-row .maxwidth-theme").width() - 32,
			leftPadding = 0,
			rightPadding = 0;

		$(".logo_and_menu-row .menu-row>div")
			.each(function (indx) {
				if (!$(this).hasClass("menu-only")) {
					var elementPosition = $(this).position().left,
						elementWidth = $(this).outerWidth();

					if (elementPosition > menuPosition) {
						rightPadding += elementWidth;
					} else {
						leftPadding += elementWidth;
					}
				}
			})
			.promise()
			.done(function () {
				$(".logo_and_menu-row .menu-only").css({ "padding-left": leftPadding, "padding-right": rightPadding + 1 });
			});
	}
};*/

/*function CheckTopMenuOncePadding() {
	if ($(".menu-row.sliced .right-icons .wrap_icon").length) {
		var menuPosition = $(".menu-row .menu-only").position().left,
			maxWidth = $(".logo_and_menu-row .maxwidth-theme").width() - 32,
			leftPadding = 0,
			rightPadding = 0;

		$(".menu-row.sliced .maxwidth-theme>div>div")
			.each(function (indx) {
				if (!$(this).hasClass("menu-only")) {
					var elementPosition = $(this).position().left,
						elementWidth = $(this).outerWidth();

					if (elementPosition > menuPosition) {
						rightPadding += elementWidth;
					} else {
						leftPadding += elementWidth;
					}
				}
			})
			.promise()
			.done(function () {
				$(".menu-row.sliced .menu-only").css({ "padding-left": leftPadding, "padding-right": rightPadding + 1 });
			});
	}
};
*/

CheckSearchWidth = function () {
	if ($(".logo_and_menu-row .search_wrap").length) {
		var searchPosition = $(".logo_and_menu-row .search_wrap").position().left,
			maxWidth = $(".logo_and_menu-row .maxwidth-theme").width() - 32;
		width = 0;

		$(".logo_and_menu-row .maxwidth-theme>div")
			.each(function () {
				if (!$(this).hasClass("search_wrap")) {
					var elementWidth = $(this).outerWidth();

					width = width ? width - elementWidth : maxWidth - elementWidth;
				}
			})
			.promise()
			.done(function () {
				$(".logo_and_menu-row .search_wrap").outerWidth(width).css({ opacity: 1, visibility: "visible" });
			});
	}
};

waitYTPlayer = function (delay, callback) {
	if (typeof YT !== "undefined" && YT && YT.Player) {
		if (typeof callback == "function") callback();
	} else {
		setTimeout(function () {
			waitYTPlayer(delay, callback);
		}, delay);
	}
};

var scrollToTopAnimateClassIn = false;
var scrollToTopAnimateClassOut = false;

var $body = {};
var isOnceInited = false;

if (navigator.userAgent.indexOf("Edge") != -1) document.documentElement.className += " edge";

initFull = function () {
	checkMobileRegion();
};

var isFrameDataReceived = false;
if (typeof window.frameCacheVars !== "undefined") {
	BX.addCustomEvent(window, "onFrameDataRequestFail", function (response) {
		// console.log(response);
	});

	BX.addCustomEvent("onFrameDataReceivedBefore", function () {
		// stop ya metrika webvisor DOM indexer
		pauseYmObserver();
	});

	BX.addCustomEvent("onFrameDataReceived", function (json) {
		initFull();

		// resume ya metrika webvisor
		// (100ms sliceHeight) + (200ms mCustomScroll) + (100ms lazyload)
		setTimeout(resumeYmObserver, 400);

		isFrameDataReceived = true;
	});
} else {
	$(document).ready(initFull);
}

scrollPreviewBlock = function () {
	scrollToBlock($.cookie("scroll_block"));
	$.cookie("scroll_block", null);
};

checkMobileRegion = function () {
	if ($(".confirm_region").length) {
		if (!$(".top_mobile_region").length)
			$('<div class="top_mobile_region"><div class="confirm_region"></div></div>').insertBefore($(".body"));
		$(".top_mobile_region .confirm_region").html($(".regions .confirm_region").html());
	}
};

$(document).on("click", ".map-items__item", function () {
	var _this = $(this),
		itemID = _this.data("id"),
		animationTime = 200;

	_this.closest(".map-wrapper").find(".index-block__subtitle").addClass("hidden");
	_this.closest(".map-wrapper").find(".index-block__title-wrapper").addClass("hidden");
	_this.closest(".map-wrapper").find(".index-block__preview").addClass("hidden");

	_this.closest(".map-container").find(" > .map-detail-items__item-svg").fadeIn(animationTime);

	_this.closest(".map-items").fadeOut(animationTime, function () {
		_this.closest(".map-container").find(".map-detail-items").fadeIn(animationTime);
		_this
			.closest(".map-container")
			.find(".map-detail-items .map-detail-items__item[data-id=" + itemID + "]")
			.fadeIn(animationTime);

		var arCoordinates = _this.data("coordinates").split(",");

		if (typeof map === "object" && map !== null && "setCenter" in map) {
			if ($(".bx-google-map").length) {
				map.setCenter({ lat: +arCoordinates[0], lng: +arCoordinates[1] });
				map.setZoom(17);
			} else {
				map.setCenter([arCoordinates[0], arCoordinates[1]], 15);
			}
		}
	});
});

$(document).on("click", ".map-detail-items__item-svg", function () {
	var _this = $(this).closest(".map-container").find(".map-detail-items__item:visible"),
		animationTime = 200;

	_this.fadeOut(animationTime);

	_this.closest(".map-container").find(" > .map-detail-items__item-svg").fadeOut(10);

	_this
		.closest(".map-container")
		.find(".map-detail-items")
		.fadeOut(animationTime, function () {
			_this.closest(".map-container").find(".map-items").fadeIn(animationTime);

			_this.closest(".map-wrapper").find(".index-block__subtitle").removeClass("hidden");
			_this.closest(".map-wrapper").find(".index-block__title-wrapper").removeClass("hidden");
			_this.closest(".map-wrapper").find(".index-block__preview").removeClass("hidden");

			if (typeof map === "object" && map !== null) {
				if (typeof clusterer === "object" && clusterer !== null && "setBounds" in map && "getBounds" in clusterer) {
					map.setBounds(clusterer.getBounds(), {
						zoomMargin: 40,
						// checkZoomRange: true
					});
				} else if (typeof bounds === "object" && bounds !== null && "fitBounds" in map && "getCenter" in bounds) {
					map.fitBounds(bounds);
				}
			}
		});
});

const CloseMobileSearch = function () {
	const $body = document.querySelector('body');
	const $header = document.querySelector('[data-ajax-block="HEADER"]')
	const $mobileHeader = document.querySelector("#mobileheader");

	$body.classList.remove('header--mobile-search-active', 'jqm-initied', 'overflow-block');
	$header.classList.remove("filter-none");
	$mobileHeader.classList.remove("filter-none");
}

$(document).ready(function () {
	(typeof scrollToTop === 'function') && scrollToTop();

	InitScrollBar();

	// ripple effect for buttons
	$.ripple(".btn:not(.btn--no-rippple)", {
		debug: false, // Turn Ripple.js logging on/off
		on: "mouseenter", // The event to trigger a ripple effect
		opacity: 0.4, // The opacity of the ripple
		color: "auto", // Set the background color. If set to "auto", it will use the text color
		multi: true, // Allow multiple ripples per element
		duration: 0.6, // The duration of the ripple
		easing: "linear", // The CSS3 easing function of the ripple
	});

	if (!jQuery.browser.safari) {
		CheckTopMenuPadding();
		CheckTopMenuOncePadding();
		CheckHeaderFixed();
		CheckTopMenuDotted();
		MegaMenuFixed();
		CheckSearchWidth();
		InitTopestMenuGummi();
		isOnceInited = true;
		/*
		setTimeout(function () {
			$(window).resize(); // need to check resize flexslider & menu
		}, 350);
		*/

		setTimeout(function () {
			$(window).scroll();
		}, 250); // need to check position fixed ask block
	} else {
		setTimeout(function () {
			$(window).resize(); // need to check resize flexslider & menu

			setTimeout(function () {
				CheckTopMenuPadding();
				CheckTopMenuOncePadding();
				CheckHeaderFixed();
				CheckTopMenuDotted();
				MegaMenuFixed();
				CheckSearchWidth();
				InitTopestMenuGummi();
				isOnceInited = true;

				setTimeout(function () {
					$(window).scroll();
				}, 50);
			}, 50);
		}, 350);
	}

	//  --- Bind mobile menu  ---
	var $mobileMenu = $("#mobilemenu");
	if ($mobileMenu.length) {
		$mobileMenu.isOpen = $mobileMenu.hasClass("show");
		$mobileMenu.isLeftSide = $("body").hasClass("mmenu_leftside");
		$mobileMenuNlo = $mobileMenu.find("[data-nlo]");

		$(document).on("click", "#mobileheader .burger", function () {
			SwipeMobileMenu();
			CloseMobilePhone();
		});

		$(document).on("click", ".mobilemenu__close", function () {
			CloseMobileMenu();
			$(".mobilemenu__menu-item").removeClass("mobilemenu__menu-item--expanded");
		});

		$(document).on("click", ".mobilemenu__menu .toggle_block", function (e) {
			e.stopPropagation();

			var $this = $(this);
			var $item = $this.closest(".mobilemenu__menu-item");

			if ($item.hasClass("mobilemenu__menu-item--parent")) {
				e.preventDefault();

				$item.addClass("mobilemenu__menu-item--expanded");
				MoveMobileMenuWrapNext();
			}
		});

		$(document).on("click", ".mobilemenu__menu a", function (e) {
			var $this = $(this);
			var $item = $this.closest(".mobilemenu__menu-item");

			if ($item.hasClass("mobilemenu__menu-item--back")) {
				e.preventDefault();

				MoveMobileMenuWrapPrev();
				setTimeout(function () {
					$item.closest(".mobilemenu__menu-item--expanded").removeClass("mobilemenu__menu-item--expanded");
				}, 400);
			} else {
				var href = $this.attr("href");
				if (typeof href !== "undefined") {
					if (href.length) {
						window.location.href = href;
						//window.location.reload();
					} else {
						if ($item.hasClass("mobilemenu__menu-item--parent")) {
							e.preventDefault();

							$item.addClass("mobilemenu__menu-item--expanded");
							MoveMobileMenuWrapNext();

							return;
						} else if ($item.hasClass("mobilemenu__menu-item--title")) {
							e.preventDefault();

							return;
						}
					}
				}

				CloseMobileMenu();
			}
		});

		if ($mobileMenu.isLeftSide) {
			$mobileMenu.parent().append('<div class="mobilemenu__overlay"></div>');
			var $mobileMenuOverlay = $(".mobilemenu__overlay");

			$mobileMenuOverlay.click(function () {
				if ($mobileMenu.isOpen) {
					CloseMobileMenu();
				}
			});

			/* $(document).swiperight(function (e) {
				 if (
					 !$(e.target).closest(".owl-carousel").length &&
					 !$(e.target).closest(".swipeignore").length &&
					 !$(e.target).closest(".mobile-scrolled").length
				 ) {
					 var partWindowWidth = document.documentElement.clientWidth / 3;
					 var swipeStart = e.swipestart.coords[0];
					 if (swipeStart <= partWindowWidth) {
						 OpenMobileMenu();
					 }
				 }
			 });

			 $(document).swipeleft(function (e) {
				 if (
					 !$(e.target).closest(".owl-carousel").length &&
					 !$(e.target).closest(".swipeignore").length &&
					 !$(e.target).closest(".mobile-scrolled").length
				 ) {
					 CloseMobileMenu();
				 }
			 });*/
		} else {
			$(document).on("click", "#mobileheader", function (e) {
				if (
					!$(e.target).closest("#mobilemenu").length &&
					!$(e.target).closest(".burger").length &&
					$mobileMenu.isOpen
				) {
					CloseMobileMenu();
				}
			});
		}

		OpenMobileMenu = function () {
			if (!$mobileMenu.isOpen && window.matchMedia("(max-width: 991px)").matches) {
				// hide styleswitcher
				if ($(".style-switcher").hasClass("active")) {
					$(".style-switcher .switch").trigger("click");
				}
				$(".style-switcher .switch").hide();
				$(".style-switcher .switch_presets").hide();

				if ($mobileMenu.isLeftSide) {
					setTimeout(function () {
						// hide body scroll
						$("body").addClass("overflow-block");

						// blur body
						$("#mobileheader").addClass("filter-none");
						$("#mobilemenu").addClass("filter-none");
						$("body").addClass("jqm-initied");

						// show overlay
						$mobileMenuOverlay.fadeIn("fast");
					}, 100);
				} else {
					// hide body scroll
					$("body").addClass("overflow-block");

					// set menu top = bottom of header
					$mobileMenu.css({
						top: +($("#mobileheader")[0].getBoundingClientRect().top + $("#mobileheader").height()) + "px",
					});

					// change burger icon
					// $('#mobileheader .burger').addClass('c');
				}

				// show menu
				$mobileMenu.addClass("show");
				$mobileMenu.isOpen = true;

				var $inner = $mobileMenu.find(".mobilemenu__inner");
				var params = $inner.data("params");
				if (typeof params === "undefined") {
					params = {
						depth: 0,
						scroll: {},
						height: {},
					};
				}
				$inner.data("params", params);

				if ($mobileMenuNlo.length) {
					if (!$mobileMenuNlo.hasClass("nlo-loadings")) {
						$mobileMenuNlo.addClass("nlo-loadings");
						setTimeout(function () {
							$.ajax({
								data: { nlo: $mobileMenuNlo.attr("data-nlo") },
								error: function () {
									$mobileMenuNlo.removeClass("nlo-loadings");
								},
								complete: function (jqXHR, textStatus) {
									if (textStatus === "success" || jqXHR.status == 404) {
										var ob = BX.processHTML($.trim(jqXHR.responseText));
										BX.ajax.processScripts(ob.SCRIPT);
										$mobileMenuNlo.replaceWith(ob.HTML);
									}
								},
							});
						}, 300);
					}
				}
			}
		};

		CloseMobileMenu = function () {
			if ($mobileMenu.isOpen) {
				// hide menu
				$mobileMenu.removeClass("show");
				$mobileMenu.isOpen = false;

				$mobileMenu.find(".mobilemenu__menu-item--expanded").removeClass("mobilemenu__menu-item--expanded");

				// show styleswitcher
				$(".style-switcher .switch").show();
				$(".style-switcher .switch_presets").show();

				if ($mobileMenu.isLeftSide) {
					setTimeout(function () {
						// hide overlay
						$mobileMenuOverlay.fadeOut("fast");

						// show body scroll
						$("body").removeClass("overflow-block");

						// unblur body
						$("body").removeClass("jqm-initied");
						$("#mobilemenu").removeClass("filter-none");
						$("#mobileheader").removeClass("filter-none");
					}, 100);
				} else {
					// change burger icon
					// $('#mobileheader .burger').removeClass('c');

					// show body scroll
					$("body").removeClass("overflow-block");
				}

				setTimeout(function () {
					var $scroller = $mobileMenu.find(".mobilemenu");
					var $inner = $mobileMenu.find(".mobilemenu__inner");
					var params = $inner.data("params");
					params.depth = 0;
					$inner.data("params", params).attr("style", "");
					$mobileMenu.scrollTop(0);
					$scroller.css("height", "");
				}, 400);
			}
		};

		SwipeMobileMenu = function () {
			if ($mobileMenu.isOpen) {
				CloseMobileMenu();
			} else {
				OpenMobileMenu();
			}
		};

		CheckMobileMenu = function () {
			if (!window.matchMedia("(max-width: 991px)").matches) {
				CloseMobileMenu();
			}
		};

		function MoveMobileMenuWrapNext() {
			var $scroller = $mobileMenu.find(".mobilemenu");
			var $inner = $mobileMenu.find(".mobilemenu__inner");
			if ($inner.length) {
				var params = $inner.data("params");
				var $dropdownNext = $mobileMenu
					.find(".mobilemenu__menu-item--expanded>.mobilemenu__menu-dropdown")
					.eq(params.depth);
				if ($dropdownNext.length) {
					// save scroll position
					params.scroll[params.depth] = parseInt($mobileMenu.scrollTop());

					// height while move animating
					params.height[params.depth + 1] = Math.max(
						$dropdownNext.height(),
						!params.depth
							? $inner.height()
							: $mobileMenu
								.find(".mobilemenu__menu-item--expanded>.mobilemenu__menu-dropdown")
								.eq(params.depth - 1)
								.height()
					);
					$scroller.css("height", params.height[params.depth + 1] + "px");

					// inc depth
					++params.depth;

					// translateX for move
					$inner.css("transform", "translateX(" + -100 * params.depth + "%)");

					// scroll to top
					setTimeout(function () {
						$mobileMenu.animate({ scrollTop: 0 }, 200);
					}, 100);

          let e = {
            menu: $mobileMenu,
            dropdownNext: $dropdownNext,
          };

          BX.onCustomEvent('onBefore.moveMobileMenuWrapNext.updateScrollerHeight', [e]);

					// height on enimating end
					var h =
						$dropdownNext.outerHeight();
					setTimeout(function () {
						if (h) {
							$scroller.css("height", h + "px");
						} else {
							$scroller.css("height", "");
						}

            BX.onCustomEvent('onAfter.moveMobileMenuWrapNext.updateScrollerHeight', [e]);
					}, 200);
				}

				$inner.data("params", params);
			}
		}

		function MoveMobileMenuWrapPrev() {
			var $scroller = $mobileMenu.find(".mobilemenu");
			var $inner = $mobileMenu.find(".mobilemenu__inner");
			if ($inner.length) {
				var params = $inner.data("params");
				if (params.depth > 0) {
					var $dropdown = $mobileMenu
						.find(".mobilemenu__menu-item--expanded>.mobilemenu__menu-dropdown")
						.eq(params.depth - 1);
					if ($dropdown.length) {
						// height while move animating
						$scroller.css("height", params.height[params.depth] + "px");

						// dec depth
						--params.depth;

						// translateX for move
						$inner.css("transform", "translateX(" + -100 * params.depth + "%)");

						// restore scroll position
						setTimeout(function () {
							$mobileMenu.animate({ scrollTop: params.scroll[params.depth] }, 200);
						}, 100);

						// height on enimating end
						var h = !params.depth
							? false
							: $mobileMenu
								.find(".mobilemenu__menu-item--expanded>.mobilemenu__menu-dropdown")
								.eq(params.depth - 1)
								.outerHeight();
						setTimeout(function () {
							if (h) {
								$scroller.css("height", h + "px");
							} else {
								$scroller.css("height", "");
							}
						}, 200);
					}
				}

				$inner.data("params", params);
			}
		}
	}
	//  --- END Bind mobile menu  ---

	//  --- Bind mobile phone ---
	if ($("#mobilephones").length) {
		$(document).on("click", ".mobileheader .phones__inner .icon-block__only-icon", function (e) {
			e.stopPropagation();
			SwipeMobilePhone();
		});

		$(document).on("click", ".mobilephones__menu-item a,.mobilephones__close", function (e) {
			e.stopPropagation();
			CloseMobilePhone();
		});
	}

	SwipeMobilePhone = function () {
		if ($("#mobilephones.show").length) {
			CloseMobilePhone();
		} else {
			OpenMobilePhone();
		}
	};

	OpenMobilePhone = function () {
		if (!$("#mobilephones.show").length) {
			CloseMobileMenu();

			// show overlay
			$('<div class="jqmOverlay mobp"></div>').appendTo("body");

			// blur body
			$("#mobileheader").addClass("filter-none");
			$("body").addClass("jqm-initied");

			// toggle phones
			setTimeout(function () {
				$("#mobilephones").slideDown("fast", function () {
					$("#mobilephones").addClass("show");
				});
			}, 100);
		}
	};

	CloseMobilePhone = function () {
		if ($("#mobilephones.show").length) {
			// toggle phones
			setTimeout(function () {
				$("#mobilephones").slideUp("fast", function () {
					$("#mobilephones").removeClass("show");

					// unblur body
					$("body").removeClass("jqm-initied");
					$("#mobileheader").removeClass("filter-none");

					// hide overlay
					$(".jqmOverlay.mobp").remove();
				});
			}, 100);
		}
	};

	checkMobilePhone = function () {
		if (!window.matchMedia("(max-width: 991px)").matches) {
			CloseMobilePhone();
		}
	};
	//  --- END Bind mobile phone ---

	// change type2 menu for fixed
	if ($("#headerfixed .js-nav").length) {
		if (arAsproOptions["THEME"]["HEADER_FIXED"] == 2) CheckHeaderFixedMenu();

		setTimeout(function () {
			$("#headerfixed .js-nav").addClass("opacity1");
		}, 350);
	}

	// close search block
	$("html, body").on("mousedown", function (e) {
		if (typeof e.target.className == "string" && e.target.className.indexOf("adm") < 0) {
			e.stopPropagation();
			var search_target = $(e.target).closest(".searche-result");
			if (
				!$(e.target).closest("#mobileheader").length &&
				!$(e.target).closest("#mobilefilter").length &&
				!$(e.target).closest(".popup").length &&
				!$(e.target).hasClass("inline-search-block") &&
				!$(e.target).hasClass("svg") &&
				!$(e.target).closest(".searche-result__all").length &&
				!$(e.target).closest('[data-ajax-block="HEADER"]') &&
				!search_target.length
			) {
				$(".inline-search-block").removeClass("show");
				$(".title-search-result").hide();

				$("body").removeClass("overflow-block");
			}

			if (isMobile) {
				if (search_target.length) location.href = search_target.attr("href");
			}

			var class_name = $(e.target).attr("class");
			if (typeof class_name == "undefined" || class_name.indexOf("tooltip") < 0)
				//tooltip link
				$(".tooltip-link").tooltip("hide");
		}
	});
	$(".inline-search-block")
		.find("*")
		.on("mousedown", function (e) {
			e.stopPropagation();
		});

	$(".filter-action").on("click", function () {
		$(this).toggleClass("active");
		$(this).find(".svg").toggleClass("white");
		if ($(".introtext").length) {
			var top_pos = $(".filters-wrap").position();
			$(".bx_filter").css({ top: top_pos.top + 40 });
		}
		$(".bx_filter").slideToggle();
	});

	// -- scroll after apply option
	scrollPreviewBlock();

	// expand/hide filter values
	$(document).on("click", ".expand_block", function () {
		togglePropBlock($(this));
	});

	// popup img list
	$(document).on("click", ".fancy-js", function () {
		const parent = $(this).closest(".image-list");
		let arItems = [];
		let index = 0;
		parent.find("[data-big]").each(function () {
			let _this = $(this);
			let obItem = {
				src: _this.data("big"),
				opts: {
					caption: _this.attr("alt"),
				},
			};
			arItems.push(obItem);
		});
		index = parent.find(".active").index();
		if (arItems.length) {
			if (typeof $.fn.fancybox === "function") {
				$.fancybox.open(arItems, { loop: false }, index);
			}
		}
	});

	InitOwlSlider();
	InitFlexSlider();
	InitFlexSliderMin();

	InitFancyBox();
	InitFancyBoxVideo();

	// for check flexslider bug in composite mode
	waitingNotExists(".detail .galery #slider", ".detail .galery #slider .flex-viewport", 1000, function () {
		InitOwlSlider();
		InitFlexSlider();
		setTimeout(function () {
			$(window).resize();
		}, 350);
	});

	// change view type catalog
	$(".view-button").on("click", function () {
		$(this).siblings().removeClass("cur");
		$(this).addClass("cur");
	});

	// -- escape close popup form
	$(document).on("keydown", function (e) {
		if (e.keyCode == 27) {
			if ($(".jqmWindow.show").length) $(".jqmWindow.show:last").jqmHide();
			if ($(".inline-search-block.fixed.show").length) {
				$(".inline-search-block.fixed .inline-search-hide").click();
				setTimeout(function () {
					$(".title-search-result").hide();
				}, 0);
			}
			if ($(".mega-fixed-menu").is(":visible")) {
				$(".mega-fixed-menu .mega-fixed-menu__close").click();
			}
		}
	});

	$(document).on("click", '*[data-event="jqm"]', function (e) {
		e.preventDefault();
		e.stopPropagation();
		if (!$(this).hasClass("clicked")) {
			$(this).addClass("clicked");
			$(this).jqmEx();
			$(this).trigger("click");
		}
	});

	$(".animate-load").on("click", function (e) {
		$(this).parent().addClass("loadings");
	});

	BX.addCustomEvent("onCompleteAction", function (eventdata, _this) {
		try {
			if (eventdata.action === "loadForm") {
				$(_this).parent().removeClass("loadings");
				$(_this).removeClass("clicked");
			} else if (eventdata.action === "loadBasket") {
			} else if (eventdata.action === "loadRSS") {
			} else if (eventdata.action === "jsLoadBlock") {
				InitScrollBar();
			} else if (eventdata.action === "ajaxContentLoaded") {
			}
		} catch (e) {
			console.error(e);
		}
	});

	BX.addCustomEvent(window, "onAjaxSuccess", function (e) {
		if (e != "OK") {
			InitOwlSlider();
			InitFlexSlider();
			InitFlexSliderMin();
		}
	});

	// show print
	if (arAsproOptions["THEME"]["PRINT_BUTTON"] == "Y") {
		setTimeout(function () {
			if ($(".page-top .rss-block.top").length) {
				$('<div class="print-link"><i class="svg svg-print"></i></div>').insertBefore(
					$(".page-top .rss-block.top .shares-block")
				);
			} else if ($(".page-top .rss").length) {
				$('<div class="print-link"><i class="svg svg-print"></i></div>').insertAfter($(".page-top .rss"));
			} else if ($(".page-top h1").length)
				$('<div class="print-link"><i class="svg svg-print"></i></div>').insertBefore($(".page-top h1"));
			// else
			// $('footer .print-block').html('<div class="print-link"><i class="svg svg-print"><svg id="Print.svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path class="cls-1" d="M1553,287h-2v3h-8v-3h-2a2,2,0,0,1-2-2v-5a2,2,0,0,1,2-2h2v-4h8v4h2a2,2,0,0,1,2,2v5A2,2,0,0,1,1553,287Zm-8,1h4v-4h-4v4Zm4-12h-4v2h4v-2Zm4,4h-12v5h2v-3h8v3h2v-5Z" transform="translate(-1539 -274)"/></svg></i></div>');
		}, 150);
	}

	$(document).on("click", ".print-link", function () {
		window.print();
	});

	$(".head-block .item-link").on("click", function () {
		var _this = $(this);
		_this.siblings().removeClass("active");
		_this.addClass("active");
	});

	//alert(navigator.userAgent);
	$("table.table").each(function () {
		var _this = $(this),
			first_td = _this.find("thead tr th");
		if (!first_td.length) first_td = _this.find("thead tr td");
		if (first_td.length) {
			_this.find("tbody tr:not(.nomobile)").each(function (i) {
				$(this)
					.find("td")
					.each(function (i) {
						if (typeof first_td[i] !== "undefined")
							$('<div class="th-mobile">' + first_td[i].textContent + "</div>").appendTo($(this));
					});
			});
		}
	});

	if ($("a.fancybox").length) {
		if (typeof $.fn.fancybox == "function") {
			$("a.fancybox").fancybox();
		}
	}

	// flex pagination
	$(document).on("mouseenter", ".flex-viewport .item", function () {
		$(this).closest(".flexslider").find(".flex-control-nav").toggleClass("noz");
		$(this).closest(".flexslider").find(".flex-control-nav").css("z-index", "0");
	});
	$(document).on("mouseleave", ".flex-viewport .item", function () {
		$(this).closest(".flexslider").find(".flex-control-nav").toggleClass("noz");
		$(this).closest(".flexslider").find(".flex-control-nav").css("z-index", "2");
	});

	// ajax more items
	$(document).on("click", ".ajax_load_btn", function () {
		var url = $(this).closest(".bottom_nav").find(".arrows-pagination__next").attr("href"),
			th = $(this).find(".more_text_ajax"),
			_this = $(this),
			bottom_nav = $(this).closest(".bottom_nav");

		if (!th.hasClass("loadings")) {
			th.addClass("loadings");

			var objUrl = parseUrlQuery(),
				obData = { AJAX_REQUEST: "Y", ajax_get: "Y", bitrix_include_areas: "N" };

			if ("clear_cache" in objUrl && objUrl.clear_cache == "Y") {
				obData.clear_cache = "Y";
			}

			//index page
			if ($(".body.index").length) {
				var class_block = th.closest(".drag-block").data("class").replace("_drag", "");
				class_block = class_block.replace(/\s/g, "");
				obData.BLOCK = class_block;

				// get mainblock action file
				let $dragBlock = th.closest('.drag-block');
				if ($dragBlock.length) {
					$jsParamsBlock = $dragBlock[0].querySelector(".js-request-data");
					if ($jsParamsBlock) {
						if ($jsParamsBlock.dataset.action) {
							let action = new URL(window.location.origin + url);
							url = window.location.origin + $jsParamsBlock.dataset.action + action.search;
						}
					}
				}
			}

			if (_this.closest(".ajax-pagination-wrapper").length) {
				obData.BLOCK = _this.closest(".ajax-pagination-wrapper").data("class");
			}

			if ($(".banners-small.front").length) {
				obData.MD = $(".banners-small.front").find(".items").data("colmd");
				obData.SM = $(".banners-small.front").find(".items").data("colsm");
			}

			// tabs block
			if (_this.closest(".tab-content-block").length) {
				var filter = th.closest(".tab-content-block").data("filter");
				obData.GLOBAL_FILTER = filter;
			}

			$.ajax({
				url: url,
				data: BX.ajax.prepareData(obData),
				success: function (html) {
					var html = html.trim();
					var mobileBottomClicked = bottom_nav.hasClass("mobile_slider");
					var hasMobileBottomNav = $(html).find(".bottom_nav.mobile_slider");
					var bottomNav = hasMobileBottomNav.length ? hasMobileBottomNav : $(html).find(".bottom_nav");
					var bottomNavHtml = bottomNav.html();
					var bottomNavScrollClass = bottomNav.data("scroll-class");
					var hasBottomNav = $(html).find(".ajax_load_btn").length;

					var eventdata = { action: "ajaxContentLoaded", content: html };

					if ($(".banners-small.front").length) {
						$(".banners-small .items.row").append(html);
						$(".bottom_nav").html($(".banners-small .items.row .bottom_nav").html());
						$(".banners-small .items.row .bottom_nav").remove();
					} else if (bottom_nav.data("append") !== undefined && bottom_nav.data("parent") !== undefined) {
						var $slider = th.closest(bottom_nav.data("parent")).find(".owl-carousel");
						if ($slider.length) {
							var obData = BX.processHTML(html);
							html = obData.HTML;

							var $slides = $("<div>" + html + "</div>").find(">*");
							$slides.each(function () {
								if (!$(this).hasClass("wrap_nav")) {
									$slider.trigger("add.owl.carousel", [$(this).wrap("<div></div>").parent().html()]);
								}
							});
							$slider.trigger("refresh.owl.carousel");

							setTimeout(function () {
								BX.ajax.processScripts(obData.SCRIPT);
							}, 100);
						} else {
							var target = html;
							if (bottom_nav.data("target") !== undefined) {
								target = $(html).find(bottom_nav.data("target"));
							}
							if (mobileBottomClicked || hasMobileBottomNav.length) {
								var mobileSliderNav = th.closest(bottom_nav.data("parent")).find(".bottom_nav.mobile_slider");
								if (mobileSliderNav.length) {
									mobileSliderNav.before(target);
								} else {
									bottom_nav.before(target);
								}
							} else {
								th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).append(target);
							}
							th.closest(bottom_nav.data("parent"))
								.find(bottom_nav.data("append"))
								.find(".bottom_nav_wrapper")
								.remove();

							if (hasBottomNav) {
								if (bottomNavScrollClass !== undefined) {
									th.closest(bottom_nav.data("parent")).find(bottomNavScrollClass).addClass("has-bottom-nav");
								}
								th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).addClass("has-bottom-nav");
							} else {
								if (bottomNavScrollClass !== undefined) {
									th.closest(bottom_nav.data("parent")).find(bottomNavScrollClass).removeClass("has-bottom-nav");
								}
								th.closest(bottom_nav.data("parent")).find(bottom_nav.data("append")).removeClass("has-bottom-nav");
							}
						}

						bottom_nav = th.closest(bottom_nav.data("parent")).find(".bottom_nav");
						bottom_nav.html(bottomNavHtml);
						var icon = bottom_nav.find(".svg-inline-bottom_nav-icon");
						icon.css("display", "");

						eventdata.container = th.closest(bottom_nav.data("parent"));
					} else {
						$(html).insertBefore($(".blog .bottom_nav"));
						$(".bottom_nav").html($(".blog .bottom_nav:hidden").html());
						$(".blog .bottom_nav:hidden").remove();
					}

					setTimeout(function () {
						BX.onCustomEvent("onCompleteAction", [eventdata, th[0]]);
						$(".banners-small .item.normal-block").sliceHeight();
						th.removeClass("loading");
					}, 100);
				},
			});
		}
	});

	// bug fix in ff
	$("img").removeAttr("draggable");

	// Responsive Menu Events
	var addActiveClass = false;
	$("#mainMenu li.dropdown > a > i, #mainMenu li.dropdown-submenu > a > i").on("click", function (e) {
		e.preventDefault();
		if ($(window).width() > 979) return;
		addActiveClass = $(this).closest("li").hasClass("resp-active");
		// $('#mainMenu').find('.resp-active').removeClass('resp-active');
		if (!addActiveClass) {
			$(this).closest("li").addClass("resp-active");
		} else {
			$(this).closest("li").removeClass("resp-active");
		}
	});

	// animate increment
	if ($(".spincrement").length) {
		$(".spincrement").counterUp({
			delay: 80,
			time: 1000,
		});
	}

	// form rating
	$(document).on("mouseenter", ".form .votes_block.with-text .item-rating", function () {
		var $this = $(this),
			index = $this.index(),
			ratingMessage = $this.data("message");

		$(this).addClass("rating__star-svg--filled");
		$this.siblings().each(function () {
			if ($(this).index() <= index) $(this).addClass("rating__star-svg--filled");
			else $(this).removeClass("rating__star-svg--filled");
		});
		$this.closest(".votes_block").find(".rating_message").text(ratingMessage);
	});

	$(document).on("mouseleave", ".form .votes_block.with-text", function () {
		var $this = $(this),
			index = $this.data("rating"),
			ratingMessage = $this.closest(".votes_block").find(".rating_message").data("message");

		$this.find(".item-rating").each(function () {
			if ($(this).index() < index && index !== undefined) $(this).addClass("rating__star-svg--filled");
			else $(this).removeClass("rating__star-svg--filled");
		});
		$this.closest(".votes_block").find(".rating_message").text(ratingMessage);
	});

	$(document).on("click", ".form .votes_block.with-text .item-rating", function () {
		var $this = $(this),
			rating = $this.closest(".votes_block").data("rating"),
			index = $this.index() + 1,
			ratingMessage = $this.data("message");

		$this.closest(".votes_block").data("rating", index);
		if ($this.closest(".form-group").find("input[name=RATING]").length) {
			$this.closest(".form-group").find("input[name=RATING]").val(index);
		} else {
			$this.closest(".form-group").find("input[data-sid=RATING]").val(index);
		}
		$this.closest(".votes_block").find(".rating_message").data("message", ratingMessage);
		$this.closest(".form").find(".error").remove();
	});

	$(document).on('paste, change, keyup', '.form.blog-comment-fields input[required]', function () {
		let value = $(this).val();

		if (value.length) {
			$(this).closest('.form-group').find('label.error').remove();
		}
	});

	$(".toggle .more_items").on("click", function () {
		$(this).closest(".toggle").find(".collapsed").fadeToggle();
		$(this).remove();
		if (typeof $(this).data("resize") !== "undefined" && $(this).data("resize")) $(window).resize();
	});
	$(".toggle_menu .more_items").on("click", function () {
		$(this).closest(".toggle_menu").find(".collapsed").addClass("clicked_exp");
		$(this).remove();
	});

	// search sync
	$(document).on("keyup", ".search-input-div input", function (e) {
		var inputValue = $(this).val();
		$(".search-input-div input").val(inputValue);

		if ($(this).closest("#headerfixed").length) {
			if (e.keyCode == 13) $(".search form").submit();
		}
	});
	$(document).on("click", ".search-button-div button", function (e) {
		if ($(this).closest("#headerfixed").length) $(".search form").submit();
	});

	$(document).on("click", ".header-search__mobile, .bottom-icons-panel__content-link--search", function (e) {
		const $body = document.querySelector('body');
		const $header = document.querySelector('[data-ajax-block="HEADER"]')
		const $mobileHeader = document.querySelector("#mobileheader");

		if ($body.classList.contains('jqm-initied')) {
			CloseMobilePhone();
			CloseMobileMenu();

			setTimeout(function () {
				$body.classList.add('header--mobile-search-active', 'jqm-initied', 'overflow-block');
				$header.classList.add("filter-none");
				$mobileHeader.classList.add("filter-none");
				if (!$("body>.jqmOverlay.mobp").length) {
					$('<div class="jqmOverlay mobp"></div>').appendTo("body");
				}
			}, 400);
		} else {
			$body.classList.add('header--mobile-search-active', 'jqm-initied', 'overflow-block');
			$header.classList.add("filter-none");
			$mobileHeader.classList.add("filter-none");
			$('<div class="jqmOverlay mobp"></div>').appendTo("body");
		}

		// document.querySelector('body').classList.add('header--mobile-search-active');
		// setTimeout(function() {
		//   if (!document.querySelector('.jqmOverlay.mobp')) {
		//     $('<div class="jqmOverlay mobp"></div>').appendTo("body");
		//     // $("#mobileheader").addClass("filter-none");
		//     // $("body").addClass("jqm-initied");
		//   }
		// }, 400)
		// const $bannerTopHeader = document.querySelector('.banner.TOP_HEADER');
		// if($bannerTopHeader){
		//   document.querySelector('[data-ajax-block="HEADER"]').style.top = $bannerTopHeader.clientHeight + 'px';
		// }
	});

	$(document).on("click", ".search-input-close", function (e) {
		HideOverlay();
	});

	$(document).on("click", ".jqmOverlay.mobp", function (e) {
		HideOverlay();
	});

	if ($(".styled-block .row > div.col-md-3").length) {
		BX.addCustomEvent("onWindowResize", function (eventdata) {
			try {
				ignoreResize.push(true);
				$(".styled-block .row > div.col-md-3").each(function () {
					$(this).css({ height: "", "line-height": "" });
					var z = parseInt($(".body_media").css("top"));
					if (z > 0) {
						var rowHeight = $(this).parents(".row").first().actual("outerHeight");
						$(this).css({ height: rowHeight + "px", "line-height": rowHeight + "px" });
					}
				});
			} catch (e) {
			} finally {
				ignoreResize.pop();
			}
		});
	}

	if ($(".order-block").length) {
		BX.addCustomEvent("onWindowResize", function (eventdata) {
			try {
				ignoreResize.push(true);
				$(".order-block").each(function () {
					var cols = $(this).find(".row > div");
					if (cols.length) {
						var colFirst = cols.first();
						var colLast = cols.last();
						var colText = colFirst.find(".text");
						var bText = colText.length;
						var bOnlyText = cols.length === 1 && bText;
						var bPrice = colFirst.find(".price").length;
						var z = parseInt($(".body_media").css("top"));

						cols.css({ height: "", "padding-top": "", "padding-bottom": "" });
						colText.css({ height: "", "padding-top": "", "padding-bottom": "" });
						if ((!bPrice && z > 0) || (bPrice && z > 1)) {
							var minHeight = 83;

							if (!bOnlyText) {
								var colLast_height = colLast.outerHeight();
								colLast_height = colLast_height >= minHeight ? colLast_height : minHeight;
							}

							if (bText) {
								var colFirst_height = colFirst.outerHeight();
								colFirst_height = colFirst_height >= minHeight ? colFirst_height : minHeight;
							}

							var colMax_height = bText
								? !bOnlyText
									? colLast_height >= colFirst_height
										? colLast_height
										: colFirst_height
									: colLast_height
								: colFirst_height;

							if (!bOnlyText) {
								var textPadding = 41 + (colMax_height - colFirst.outerHeight()) / 2;
								colLast.find(".btns").css({
									"padding-top": textPadding + "px",
									"padding-bottom": textPadding + "px",
									height: colMax_height + "px",
								});
							}
							if (bText) {
								colLast.css({ height: colMax_height + "px" });
								var textPadding = 41 + (colMax_height - colText.outerHeight()) / 2;
								colText.css({
									"padding-top": textPadding + "px",
									"padding-bottom": textPadding + "px",
									height: colMax_height + "px",
								});
							}
						}
					}
				});
			} catch (e) {
			} finally {
				ignoreResize.pop();
			}
		});
	}

	if ($(".equal-padding").length) {
		BX.addCustomEvent("onWindowResize", function (eventdata) {
			try {
				ignoreResize.push(true);
				$(".equal-padding").each(function () {
					$(this).find(".text").css({ "padding-top": "0px", "padding-bottom": "0px" });
					var equal_block = $(this).siblings(".equals"),
						height = $(this).actual("outerHeight");

					delta = Math.round((equal_block.actual("outerHeight") - height) / 2);
					if (delta)
						$(this)
							.find(".text")
							.css({ "padding-top": delta + "px", "padding-bottom": delta + "px" });
				});
			} catch (e) {
			} finally {
				ignoreResize.pop();
			}
		});
	}

	$(document).on("click", ".mega-menu .dropdown-menu", function (e) {
		e.stopPropagation();
	});

	$(document).on("click", ".mega-menu .dropdown-toggle.more-items", function (e) {
		e.preventDefault();
	});

	// $(".table-menu .dropdown,.table-menu .dropdown-submenu,.table-menu .dropdown-toggle").on("mouseenter", function () {
	//   CheckTopVisibleMenu();
	// });

	$(document).on(
		"mouseenter",
		".header-menu__wrapper .header-menu__item--dropdown:not(.header-menu__item--wide), .header-menu__wrapper .header-menu__item--dropdown:not(.header-menu__item--wide) .header-menu__dropdown-item.header-menu__dropdown-item--with-dropdown, .header-menu__wrapper .header-menu__item--more-items, .header-menu__wrapper .header-menu__item--more-items .header-menu__dropdown-item--with-dropdown, .header-menu__wrapper .header-menu__item--more-items .header-menu__item--dropdown.header-menu__item--wide, .header-menu__wrapper .header-menu__item--more-items .header-menu__item--dropdown.header-menu__item--wide .header-menu__dropdown-item--with-dropdown, .header-menu__wrapper .header-menu__item--more-items .header-menu__item--dropdown.header-menu__item--wide .header-menu__dropdown-item--with-dropdown .header-menu__wide-submenu-item--with-dropdown",
		function (e) {
			CheckTopVisibleMenu($(e.currentTarget));
		}
	);

	$(document).on(
		"mouseenter",
		".header-menu__item--wide, .header-menu__item--wide .header-menu__dropdown-menu",
		function () {
			var bannerMenu = $(this).find(".side_banners .owl-carousel-hover");
			if (bannerMenu.length) {
				bannerMenu.removeClass("owl-carousel-hover").addClass("owl-carousel");
				setTimeout(function () {
					InitOwlSlider();
					bannerMenu.removeClass("loading-state");
				}, 1);
			}
		}
	);

	$(document).on("mouseenter", ".menu-side-column__item--wide", function () {
		var bannerMenu = $(".bottom-banners .owl-carousel-hover");
		if (bannerMenu.length) {
			bannerMenu.removeClass("owl-carousel-hover").addClass("owl-carousel");
			setTimeout(function () {
				InitOwlSlider();
				bannerMenu.removeClass("loading-state");
			}, 1);
		}
	});
	$(".mega-menu .search-item .search-icon, .menu-row #title-search .fa-close").on("click", function (e) {
		e.preventDefault();
		$(".menu-row #title-search").toggleClass("hide");
	});

	$(".mega-menu ul.nav .search input").on("keyup", function (e) {
		var inputValue = $(this).val();
		$(".menu-row > .search input").val(inputValue);
		if (e.keyCode == 13) {
			$(".menu-row > .search form").submit();
		}
	});

	$(".menu-row > .search input").on("keyup", function (e) {
		var inputValue = $(this).val();
		$(".mega-menu ul.nav .search input").val(inputValue);
		if (e.keyCode == 13) {
			$(".menu-row > .search form").submit();
		}
	});

	$(".mega-menu ul.nav .search button").on("click", function (e) {
		e.preventDefault();
		var inputValue = $(this).parents(".search").find("input").val();
		$(".menu-and-search .search input").val(inputValue);
		$(".menu-row > .search form").submit();
	});

	$(".filter .calendar").on("click", function () {
		var button = $(this).next();
		if (button.hasClass("calendar-icon")) {
			button.trigger("click");
		}
	});

	// sliceheights
	if ($(".banners-small .item.normal-block").length) $(".banners-small .item.normal-block").sliceHeight();
	if ($(".teasers .item").length) $(".teasers .item").sliceHeight();
	if ($(".wrap-portfolio-front .row.items > div").length)
		$(".wrap-portfolio-front .row.items > div").sliceHeight({ row: ".row.items", item: ".item1" });

	SliceHeightBlocks();

	// toggle
	var $this = this,
		previewParClosedHeight = 25;

	$("section.toggle > label").prepend($("<i />").addClass("fa fa-plus"));
	$("section.toggle > label").prepend($("<i />").addClass("fa fa-minus"));
	$("section.toggle.active > p").addClass("preview-active");
	$("section.toggle.active > div.toggle-content").slideDown(350, function () { });

	$("section.toggle > label").click(function (e) {
		var parentSection = $(this).parent(),
			parentWrapper = $(this).parents("div.toogle"),
			previewPar = false,
			isAccordion = parentWrapper.hasClass("toogle-accordion");

		if (isAccordion && typeof e.originalEvent != "undefined") {
			parentWrapper.find("section.toggle.active > label").trigger("click");
		}

		parentSection.toggleClass("active");

		// Preview Paragraph
		if (parentSection.find("> p").get(0)) {
			previewPar = parentSection.find("> p");
			var previewParCurrentHeight = previewPar.css("height");
			previewPar.css("height", "auto");
			var previewParAnimateHeight = previewPar.css("height");
			previewPar.css("height", previewParCurrentHeight);
		}

		// Content
		var toggleContent = parentSection.find("> div.toggle-content");

		if (parentSection.hasClass("active")) {
			$(previewPar).animate(
				{
					height: previewParAnimateHeight,
				},
				350,
				function () {
					$(this).addClass("preview-active");
				}
			);
			toggleContent.slideDown(350, function () { });
		} else {
			$(previewPar).animate(
				{
					height: previewParClosedHeight,
				},
				350,
				function () {
					$(this).removeClass("preview-active");
				}
			);
			toggleContent.slideUp(350, function () { });
		}
	});

	// accordion
	$(".accordion-head").on("click", function (e) {
		e.preventDefault();
		if (!$(this).next().hasClass("collapsing")) {
			$(this).toggleClass("accordion-open");
			$(this).toggleClass("accordion-close");
		}
	});

	InitAppear();

	// js-load-block appear
	if ($(".js-load-block").length) {
		var objUrl = parseUrlQuery();
		var bClearCache = false;
		if ("clear_cache" in objUrl) {
			if (objUrl.clear_cache == "Y") {
				bClearCache = true;
			}
		}

		var items = [];
		var bIdle = true;
		var insertNextBlockContent = function () {
			if (bIdle) {
				if (items.length) {
					bIdle = false;
					var item = items.pop();

					item.content = $.trim(item.content);

					// remove /bitrix/js/main/core/core_window.js if it was loaded already
					if (item.content.indexOf("/bitrix/js/main/core/core_window.") !== -1 && BX.WindowManager) {
						item.content = item.content.replace(
							/<script src="\/bitrix\/js\/main\/core\/core_window\.[^>]*><\/script>/gm,
							""
						);
					}

					// remove /bitrix/js/currency/core_currency.js if it was loaded already
					if (
						item.content.indexOf("/bitrix/js/currency/core_currency.") !== -1 &&
						typeof BX.Currency === "object" &&
						BX.Currency.defaultFormat
					) {
						item.content = item.content.replace(
							/<script src="\/bitrix\/js\/currency\/core_currency\.[^>]*><\/script>/gm,
							""
						);
					}

					// remove /bitrix/js/main/pageobject/pageobject.js if it was loaded already
					if (item.content.indexOf("/bitrix/js/main/pageobject/pageobject.") !== -1 && BX.PageObject) {
						item.content = item.content.replace(
							/<script src="\/bitrix\/js\/main\/pageobject\/pageobject\.[^>]*><\/script>/gm,
							""
						);
					}

					// remove /bitrix/js/main/polyfill/promise/js/promise.js if it not need
					if (
						item.content.indexOf("/bitrix/js/main/polyfill/promise/js/promise.") !== -1 &&
						typeof window.Promise !== "undefined" &&
						window.Promise.toString().indexOf("[native code]") !== -1
					) {
						item.content = item.content.replace(
							/<script src="\/bitrix\/js\/main\/polyfill\/promise\/js\/promise\.[^>]*><\/script>/gm,
							""
						);
					}

					var ob = BX.processHTML(item.content);

					// stop ya metrika webvisor DOM indexer
					pauseYmObserver();

					item.block.removeAttr("data-file").removeClass("loader_circle");

					if (item.block.data("appendTo")) {
						item.block.find(item.block.data("appendTo"))[0].innerHTML = ob.HTML;
					} else {
						if (item.block.find('> div[id*="bx_incl_"]').length) {
							item.block.find('> div[id*="bx_incl_"]')[0].innerHTML = ob.HTML;
						} else {
							item.block[0].innerHTML = ob.HTML;
						}
					}

					BX.ajax.processScripts(ob.SCRIPT);

					var eventdata = { action: "jsLoadBlock" };
					BX.onCustomEvent("onCompleteAction", [eventdata, item.block]);

					// resume ya metrika webvisor
					// 500ms
					setTimeout(resumeYmObserver, 500);

					bIdle = true;
					insertNextBlockContent();
				}
			}
		};

		$(".js-load-block").iAppear(
			function () {
				var $this = $(this);

				if ($this.data("file")) {
					var add_url = bClearCache ? "?clear_cache=Y" : "";
					if ($this.data("block")) {
						add_url += (bClearCache ? "&" : "?") + "BLOCK=" + $this.data("block");
					}

					// get content
					$.get($this.data("file") + add_url).done(function (html) {
						items.push({
							block: $this,
							content: html,
						});

						if (items.length == 1) {
							setTimeout(insertNextBlockContent, 100);
						}
					});
				}
			},
			{
				rootMargin: isMobile ? "300px 0px 300px 0px" : "150px 0px 150px 0px",
				accX: 0,
				accY: isMobile ? 300 : 150,
			}
		);
	}

	$("a[rel=tooltip]").tooltip();
	$("span[data-toggle=tooltip]").tooltip();

	$("select.sort").on("change", function () {
		location.href = $(this).val();
	});

	setTimeout(function (th) {
		$(".catalog.group.list .item").each(function () {
			var th = $(this);
			if ((tmp = th.find(".image").outerHeight() - th.find(".text_info").outerHeight()) > 0) {
				th.find(".text_info .titles").height(th.find(".text_info .titles").outerHeight() + tmp);
			}
		});
	}, 50);

	// ajax tabs

	// tabs on front in mobile
	$(".items-link-mobile").each(function () {
		var _this = $(this),
			selectOption = _this.find("option"),
			selectOptionLength = selectOption.length,
			selectedOption = selectOption.filter(":selected"),
			duration = 200; //

		_this.hide();
		_this.wrap('<div class="select"></div>');
		$("<div>", {
			class: "new-select",
			text: _this.children("option:disabled").text(),
		}).insertAfter(_this);

		var selectHead = _this.next(".new-select");
		$("<div>", {
			class: "new-select__list",
		}).insertAfter(selectHead);

		var selectList = selectHead.next(".new-select__list");
		for (var i = 1; i < selectOptionLength; i++) {
			$("<div>", {
				class: "new-select__item",
				html: $("<span>", {
					text: selectOption.eq(i).text(),
				}),
			})
				.attr("data-value", selectOption.eq(i).val())
				.appendTo(selectList);
		}

		var selectItem = selectList.find(".new-select__item");
		var selectItemFirst = selectList.find(".new-select__item:first-child");
		selectList.slideUp();
		selectHead.on("click", function () {
			if (!$(this).hasClass("on")) {
				$(this).addClass("on");
				selectList.slideDown(duration);

				selectItemCur = selectList.find(".new-select__item.active");

				if (!selectItem.hasClass("active")) {
					selectItemFirst.addClass("active");
					selectItemCur = selectItemFirst;
				}

				selectItem.on("click", function () {
					selectItemCur.removeClass("active");
					var chooseItem = $(this).data("value");

					$("select").val(chooseItem).attr("selected", "selected");
					selectHead.text($(this).find("span").text());
					selectHead.attr("data-value", $(this).attr("data-value"));
					$(this).addClass("active");
					var _this = $(this);
					selectList.slideUp(150, function () {
						selectHead.removeClass("on");

						var index = _this.index();
						var curHeadBlock = _this.closest(".head-block");
						var indexTab = curHeadBlock.find(".items-link .item-link.active");
						var indexActiveTab = indexTab.index();

						if (indexActiveTab != index) {
							curHeadBlock.find(".items-link .item-link").eq(index).trigger("click");
						}
					});
				});
			} else {
				$(this).removeClass("on");
				selectList.slideUp(duration);
			}
		});
	});

	// gallery in list
	$(document).on("mouseenter", ".section-gallery-wrapper .section-gallery-wrapper__item", function () {
		const _this = $(this);
		const index = _this.index();
		const $nav_items = _this.closest(".image-list-wrapper").find(".section-gallery-nav .section-gallery-nav__item");

		_this.siblings().removeClass("active");
		_this.addClass("active");

		$nav_items.removeClass("active");
		$nav_items.filter(":eq(" + index + ")").addClass("active");
	});

	// return to the first image
	$(document).on("mouseleave", ".section-gallery-wrapper", function () {
		const _this = $(this);
		const $elements = $(this).find('.section-gallery-wrapper__item');
		const $nav_items = _this.closest(".image-list-wrapper").find(".section-gallery-nav .section-gallery-nav__item");

		if ($elements.length) {
			$elements.removeClass('active');
			$($elements[0]).addClass('active');
			$nav_items.removeClass('active');
			$($nav_items[0]).addClass('active');
		}
	});

	$(document).on("click", ".section-gallery-nav .section-gallery-nav__item", function () {
		const _this = $(this);
		const index = _this.index();
		const items = _this.closest(".image-list-wrapper").find(".section-gallery-wrapper .section-gallery-wrapper__item");

		_this.siblings().removeClass("active");
		_this.addClass("active");

		items.removeClass("active");
		items.filter(":eq(" + index + ")").addClass("active");
	});

	// item galery
	$(".thumbs .item a").on("click", function (e) {
		e.preventDefault();
		$(".thumbs .item").removeClass("current");
		$(this).closest(".item").toggleClass("current");
		$(".slides li" + $(this).attr("href"))
			.addClass("current")
			.siblings()
			.removeClass("current");
	});

	$("header.fixed .btn-responsive-nav").on("click", function () {
		$("html, body").animate({ scrollTop: 0 }, 400);
	});

	$("body").on("click", ".form .refresh-page", function () {
		location.href = location.href;
	});

	// show props
	$(document).on("click", ".show_props", function () {
		$(this).prev(".props_list").stop().slideToggle(333);
		$(this).find(".char_title").toggleClass("opened");
	});

	// animated labels
	$(document)
		.on("focus", ".fill-animate input,.fill-animate textarea", function () {
			$(this).addClass("input-filed");
		})
		.on("focusout", ".fill-animate input,.fill-animate textarea", function () {
			if ("" != $(this).val()) {
				$(this).addClass("input-filed");
			} else {
				$(this).removeClass("input-filed");
			}
		});

	// accordion action
	$(".panel-collapse").on("hidden.bs.collapse", function () {
		$(this).parent().toggleClass("opened");
	});
	$(".panel-collapse").on("show.bs.collapse", function () {
		$(this).parent().toggleClass("opened");
	});

	$(document).on("click", ".reload-page", function () {
		location.reload(true);
	});

	$("select.region").on("change", function () {
		var val = parseInt($(this).val());
		if ($("select.city").length) {
			if (val) {
				$("select.city").removeAttr("disabled");
				$("select.city option").hide();
				$("select.city option[data-parent_section=" + val + "]").show();
			} else $("select.city").attr("disabled", "disabled");
		}
	});

	$("select.city, select.region").on("change", function () {
		var _this = $(this),
			val = parseInt(_this.val());
		if (_this.hasClass("region")) {
			$("select.city option:eq(0)").show();
			$("select.city").val(0);
		}

		if ((_this.hasClass("region") && !val) || _this.hasClass("city")) {
			$.ajax({
				type: "POST",
				data: { ID: val },
			}).done(function (html) {
				var ob = BX.processHTML(html);
				$(".ajax_items")[0].innerHTML = ob.HTML;
				BX.ajax.processScripts(ob.SCRIPT);
			});
		}
	});

	$(document).on("click", ".print", function () {
		window.print();
	});

	$(".choise").on("click", function () {
		var $this = $(this);
		if (typeof $this.data("block") !== "undefined") {
			var block = $this.attr("data-block");

			try {
				var $block = BX(block) ? $("#" + block) : $(".detail-block." + block);
			} catch (e) {
				var $block = $(block);
			}

			if ($block.length) {
				if ($block.closest(".tab-pane").length) {
					var offset = -206;
					if (typeof arAsproOptions !== "undefined") {
						offset = window.matchMedia("(max-width:991px)").matches
							? arAsproOptions.THEME.HEADER_MOBILE_FIXED !== "Y" || arAsproOptions.THEME.HEADER_MOBILE_SHOW !== "ALWAYS"
								? -187 + 62
								: -187
							: arAsproOptions.THEME.TOP_MENU_FIXED !== "Y"
								? -206 + 81
								: -206;
					}

					$block.data("offset", offset);
					$('.ordered-block a[href="#' + $block.closest(".tab-pane").attr("id") + '"]').click();
				}

				scrollToBlock($block);
			}
		}
	});

	// touch event
	document.addEventListener(
		"touchend",
		function (event) {
			if (!$(event.target).closest(".menu-item").length && !$(event.target).hasClass("menu-item")) {
				$(".mega-menu .dropdown-menu").css({ display: "none", opacity: 0 });
				$(".menu-item").removeClass("hover");
			}
			if (!$(event.target).closest(".menu.topest").length) {
				$(".menu.topest").css({ overflow: "hidden" });
				$(".menu.topest > li").removeClass("hover");
			}
			if (!$(event.target).closest(".full.has-child").length) {
				$(".menu_top_block.catalog_block li").removeClass("hover");
			}
			// if(!$(event.target).closest('.basket_block').length){
			//   $('.basket_block .link').removeClass('hover');
			//   $('.basket_block .basket_popup_wrapp').slideUp();
			// }
			// if(!$(event.target).closest('.catalog_item').length){
			//   var tabsContentUnhoverHover = $('.tab:visible').attr('data-unhover') * 1;
			//   $('.tab:visible').stop().animate({'height': tabsContentUnhoverHover}, 100);
			//   $('.tab:visible').find('.catalog_item').removeClass('hover');
			//   $('.tab:visible').find('.catalog_item .buttons_block').stop().fadeOut(233);
			//   if($('.catalog_block').length){
			//     $('.catalog_block').find('.catalog_item_wrapp').removeClass('hover');
			//     $('.catalog_block').find('.catalog_item').removeClass('hover');
			//   }
			// }
		},
		false
	);

	touchMenu(".mega-menu .menu-item");
	touchTopMenu(".menu.topest li");
});

function touchMenu(selector) {
	if (isMobile) {
		if ($(selector).length) {
			$(selector).each(function () {
				var th = $(this);
				th.on("touchend", function (e) {
					var _th = $(e.target).closest(".menu-item");

					$(".menu.topest > li").removeClass("hover");
					$(".menu_top_block.catalog_block li").removeClass("hover");

					if (_th.find(".dropdown-menu").length && !_th.hasClass("hover")) {
						e.preventDefault();
						e.stopPropagation();
						_th.siblings().removeClass("hover");
						_th.addClass("hover");
						$(".menu-row .dropdown-menu").css({ display: "none", opacity: 0 });
						if (_th.hasClass("menu-item")) {
							_th.closest(".dropdown-menu").css({ display: "block", opacity: 1 });
						}
						if (_th.find("> .wrap > .dropdown-menu")) {
							_th.find("> .wrap > .dropdown-menu").css({ display: "block", opacity: 1 });
						} else if (_th.find("> .dropdown-menu")) {
							_th.find("> .dropdown-menu").css({ display: "block", opacity: 1 });
						}
						CheckTopVisibleMenu();
					} else {
						var href = $(e.target).attr("href") ? $(e.target).attr("href") : $(e.target).closest("a").attr("href");
						if (href && href !== "undefined") location.href = href;
					}
				});
			});
		}
	} else {
		$(selector).off();
	}
}

function touchTopMenu(selector) {
	if (isMobile) {
		if ($(selector).length) {
			$(selector).each(function () {
				var th = $(this);
				th.on("touchend", function (e) {
					var _th = $(e.target).closest("li");

					$(".menu-item").removeClass("hover");
					$(".menu-item .dropdown-menu").css({ display: "none", opacity: 0 });
					$(".menu_top_block.catalog_block li").removeClass("hover");

					if (_th.hasClass("more") && !_th.hasClass("hover")) {
						e.preventDefault();
						e.stopPropagation();
						_th.siblings().removeClass("hover");
						_th.addClass("hover");
						$(".menu.topest").css({ overflow: "visible" });
					} else {
						var href = $(e.target).attr("href") ? $(e.target).attr("href") : $(e.target).closest("a").attr("href");
						if (href && href !== "undefined") location.href = href;
					}
				});
			});
		}
	} else {
		$(selector).off();
	}
}

scrollToBlock = function (block) {
	if ($(block).length) {
		scrollToBlock.last = block;

		if (typeof $(block).data("toggle") !== "undefined") {
			$(block).click();
		}

		var offset = -81 - 64;
		if (typeof $(block).data("offset") !== "undefined") {
			offset = $(block).data("offset");
		} else {
			if (typeof arAsproOptions !== "undefined") {
				offset = window.matchMedia("(max-width:991px)").matches
					? arAsproOptions.THEME.HEADER_MOBILE_FIXED !== "Y" || arAsproOptions.THEME.HEADER_MOBILE_SHOW !== "ALWAYS"
						? -43
						: -62 - 43
					: arAsproOptions.THEME.TOP_MENU_FIXED !== "Y"
						? -43
						: -81 - 43;

				if ($(block).hasClass("drag-block")) {
					offset += 43;
				}
			}
		}

		offset = $(block).offset().top + offset;

		$("body").addClass("scrolling-state");
		$("body, html").animate({ scrollTop: offset }, 500);
		setTimeout(function () {
			$("body").removeClass("scrolling-state");
		}, 500);
	}
};

scrollToBlock.rescroll = function () {
	if (typeof scrollToBlock.last !== "undefined") {
		scrollToBlock(scrollToBlock.last);
	}
};

$(document).on("click", ".counter__action--plus", function () {
	//for corporate basket
	if ($(this).closest(".counter--basket").length) return;

	let $input = $(this).closest(".counter").find(".counter__count");
	let currentValue = $input.val();

	let $buyBlock = $(this).closest(".buy_block");
	let $toCart = $buyBlock.find(".item-action .to_cart");

	let isFloatRatio = $toCart.data("float_ratio");
	let ratio = $toCart.data("ratio");
	ratio = isFloatRatio ? parseFloat(ratio) : parseInt(ratio, 10);
	if (isFloatRatio) {
		ratio = Math.round(ratio * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;
	}

	let maxValue = parseFloat($toCart.data("max") ? $toCart.data("max") : ($(this).data("max") ? $(this).data("max") : 0));

	let newValue = isFloatRatio ? parseFloat(currentValue) : parseInt(currentValue, 10);
	newValue += ratio;
	if (isFloatRatio) {
		newValue = Math.round(newValue * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;
	}

	if (
		parseFloat(maxValue) > 0 &&
		newValue > maxValue
	) {
		newValue = maxValue;
	}

	$input.val(newValue);
	$input.change();
});

$(document).on("click", ".counter__action--minus", function () {
	//for corporate basket
	if ($(this).closest(".counter--basket").length) return;

	let $input = $(this).closest(".counter").find(".counter__count");
	let currentValue = $input.val();

	let $buyBlock = $(this).closest(".buy_block");
	let $toCart = $buyBlock.find(".item-action .to_cart");

	let isFloatRatio = $toCart.data("float_ratio");
	let ratio = $toCart.data("ratio");
	ratio = isFloatRatio ? parseFloat(ratio) : parseInt(ratio, 10);
	if (isFloatRatio) {
		ratio = Math.round(ratio * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;
	}
	if (ratio <= 0) {
		ratio = 1;
	}

	let minValue = parseFloat($toCart.data("min") ? $toCart.data("min") : ($(this).data("min") ? $(this).data("min") : 0));
	if (minValue < ratio) {
		minValue = ratio;
	}

	let newValue = isFloatRatio ? parseFloat(currentValue) : parseInt(currentValue, 10);
	newValue -= ratio;
	if (isFloatRatio) {
		newValue = Math.round(newValue * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;
	}

	if (
		parseFloat(minValue) > 0 &&
		newValue < minValue
	) {
		// remove from basket
		newValue = 0;
	}

	$input.val(newValue);
	$input.change();
});

$(document).on("focus", ".counter__count", function (e) {
	$(this).addClass("focus");
});

$(document).on("blur", ".counter__count", function (e) {
	$(this).removeClass("focus");
});

var timerChangeCounterValue = false;
$(document).on("change", ".counter__count", function (e) {
	//for corporate basket
	if ($(this).closest(".counter--basket").length) return;

	let $buyBlock = $(this).closest(".buy_block");
	let $toCart = $buyBlock.find(".item-action .to_cart");
	let itemAction = JItemAction.factory($toCart[0]);

	let currentValue = $(this).val();
	// remove from cart
	if (parseFloat(currentValue) === 0) {
		itemAction.state = false;
		itemAction.resetQuantity();

		BX.onCustomEvent('onCounterGoals', [{
			goal: itemAction.getStateGoalCode(false),
			params: {
				id: itemAction.node.getAttribute('data-id'),
			}
		}]);

		// remove notice
		if (typeof JNoticeSurface === 'function') {
			JNoticeSurface.get().node.remove()
		}

		return itemAction.updateState();
	}

	let isFloatRatio = $toCart.data("float_ratio");
	let ratio = $toCart.data("ratio");
	ratio = isFloatRatio ? parseFloat(ratio) : parseInt(ratio, 10);

	let diff = currentValue % ratio;

	if (isFloatRatio) {
		ratio = Math.round(ratio * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;

		if (Math.round(diff * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor == ratio) {
			diff = 0;
		}
	}

	if ($(this).hasClass("focus")) {
		intCount = Math.round(Math.round((currentValue * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / ratio) / arAsproOptions.JS_ITEM_CLICK.precisionFactor) || 1;
		if (parseFloat(currentValue) !== 0) {
			currentValue = intCount <= 1 ? ratio : intCount * ratio;
		}
		currentValue = Math.round(currentValue * arAsproOptions.JS_ITEM_CLICK.precisionFactor) / arAsproOptions.JS_ITEM_CLICK.precisionFactor;
	}

	let maxValue = $toCart.data("max") ? $toCart.data("max") : $(this).closest(".counter").find(".counter__action--plus").data("max");
	if (maxValue && parseFloat(maxValue) > 0) {
		if (currentValue > parseFloat(maxValue)) {
			currentValue = parseFloat(maxValue);
		}
	}

	let minValue = (typeof $toCart.data("min") !== 'undefined') ? $toCart.data("min") : $(this).closest(".counter").find(".counter__action--minus").data("min");
	if (minValue && parseFloat(minValue) > 0) {
		if (currentValue < parseFloat(minValue)) {
			currentValue = parseFloat(minValue);
		}
	}

	if (timerChangeCounterValue) {
		clearTimeout(timerChangeCounterValue);
	}

	if (currentValue < ratio) {
		currentValue = ratio;
	} else if (!parseFloat(currentValue)) {
		currentValue = 1;
	}

	$toCart.attr("data-quantity", currentValue);

	$(this).val(currentValue);

	itemAction.abortPrevRequest();
	timerChangeCounterValue = setTimeout(function () {
		itemAction.updateState();
		timerChangeCounterValue = false;
	}, 700);

	BX.onCustomEvent(
		"onCounterProductAction",
		[
			{
				type: "change",
				params: {
					id: $(this),
					value: currentValue,
				}
			}
		]
	);

	if (
		$(this).closest(".complect-block").length &&
		typeof setNewPriceComplect === "function"
	) {
		setNewPriceComplect();
	}
});

/*hover basket*/
if (!funcDefined("basketTop")) {
	var basketTop = function basketTop(action, hoverBlock) {

		hoverBlock = $("#headerfixed .basket-dropdown, header .basket-dropdown");

		hoverBlock.removeClass("loaded");
		var firstTime = hoverBlock.find("div").length ? "false" : "true";
		var params = $("#basket_form").find("input#fly_basket_params").val();
		var postData = {
			firstTime: firstTime,
		};
		if (params !== undefined) {
			postData.PARAMS = params;
		}

		$.post(
			arAsproOptions["SITE_DIR"] + "ajax/showBasketHover.php",
			postData,
			$.proxy(function (data) {
				var ob = BX.processHTML(data);

				// inject
				hoverBlock.html(ob.HTML);
				BX.ajax.processScripts(ob.SCRIPT);

				if (window.matchMedia("(min-width: 992px)").matches) {
					hoverBlock.addClass("loaded");
				}
			})
		);
	};
}

$(document).on("mouseenter", ".header-cart .top.basket", function () {
	var _this = $(this);
	var parent = _this.closest("header, #headerfixed");
	var hover_block = parent.find(".basket-dropdown");

	if (!hover_block.hasClass("loaded")) {
		basketTop("", hover_block);
	}
});
/*end hover basket*/

CheckTabActive = function () { };

/* parallax bg */
ParallaxBg = function () {
	if ($("*[data-type=parallax-bg]").length) {
		var x = $(window).scrollTop() / $(document).height();
		x = parseInt(-x * 280);
		$("*[data-type=parallax-bg]")
			.stop()
			.animate({ "background-position-y": x + "px" }, 400, "swing");
	}
};

// Events
var timerScroll = false,
	ignoreScroll = [],
	documentScrollTopLast = $(document).scrollTop(),
	documentScrollTop = documentScrollTopLast;
$(window).scroll(function () {
	documentScrollTop = $(document).scrollTop();

	if (!ignoreScroll.length) {
		if (timerScroll) {
			clearTimeout(timerScroll);
			timerScroll = false;
		}
		timerScroll = setTimeout(function () {
			BX.onCustomEvent("onWindowScroll", false);
		}, 100);
	}

	documentScrollTopLast = $(document).scrollTop();
});

var timerResize = false,
	ignoreResize = [];

$(window).resize(function () {
	documentScrollTop = $(document).scrollTop();

	CheckTopMenuPadding();
	CheckTopMenuOncePadding();
	CheckSearchWidth();

	if (!ignoreResize.length) {
		if (timerResize) {
			clearTimeout(timerResize);
			timerResize = false;
		}
		timerResize = setTimeout(function () {
			BX.onCustomEvent("onWindowResize", false);
		}, 100);
	}

	documentScrollTopLast = $(document).scrollTop();
});

BX.addCustomEvent("onWindowScroll", function (eventdata) {
	try {
		ignoreScroll.push(true);
		ParallaxBg();

		//CheckTopMenuDotted();
	} catch (e) {
	} finally {
		ignoreScroll.pop();
	}
});

BX.addCustomEvent("onWindowResize", function (eventdata) {
	try {
		ignoreResize.push(true);

		CheckHeaderFixedMenu();
		CheckTopMenuDotted();
		CheckTopVisibleMenu();
		CheckFlexSlider();
		CheckMainBannerSliderVText($(".banners-big .flexslider"));
		CheckObjectsSizes();

		if (funcDefined("CoverPlayer")) {
			CoverPlayer();
		}

		verticalAlign();
		CheckTabActive();
		setTimeout(function () {
			createTableCompare(
				$(".main-block .items .title-block:not(.clone) .item"),
				$(".prop_title_table"),
				$(".main-block .prop_title_table .item.clone")
			);
		}, 100);
		SliceHeightBlocks();

		if (funcDefined("checkMobileFilter")) {
			checkMobileFilter();
		}

		if (window.matchMedia("(min-width: 992px)").matches) {
			CloseMobileMenu();
			CloseMobilePhone();
		}

		if (window.matchMedia("(min-width:768px)").matches) {
			closeFullscreenMap();
		}
	} catch (e) {
	} finally {
		ignoreResize.pop();
	}
});

BX.addCustomEvent("onSlide", function (eventdata) {
	try {
		ignoreResize.push(true);

		if (eventdata) {
			var slider = eventdata.slider;
			var slideClass = ".banners-big__item";
			var jsDots = $(slider).siblings(".owl-carousel__dots--line");
			if (jsDots.length) {
				if (eventdata.data) {
					if (eventdata.data.relatedTarget) {
						var dotScroll = jsDots.find(".owl-carousel__dot-scroll");
						var dotsWidth = jsDots.width();
						if (dotScroll.length) {
							var owlOptions = eventdata.data.relatedTarget;

							if (owlOptions) {
								var itemsCount = owlOptions._items.length;
								var itemsInRow = owlOptions.settings.items;
								var currentItem = owlOptions._current; // + 1;

								//orig old
								//currentItem = currentItem < itemsInRow  ? itemsCount + itemsInRow - 1 : currentItem;
								//var offset = ((currentItem - itemsInRow) / itemsCount) * dotsWidth;

								//new by use count clones in slider
								var clonesInStart = Math.ceil(itemsCount / 2);
								currentItem = currentItem < clonesInStart ? itemsCount + clonesInStart - 1 : currentItem;
								currentItem = currentItem === itemsCount + clonesInStart ? clonesInStart : currentItem;
								var offset = ((currentItem - clonesInStart) / itemsCount) * dotsWidth;

								var styles = {
									width: dotsWidth / itemsCount + "px",
									display: "block",
									left: offset,
								};

								dotScroll.css(styles);
							}
						}
					}
				}
			}
			if (slider) {
				// add classes .curent & .shown to slide
				slider.find(slideClass).removeClass("current");
				var curSlideIndex = eventdata.data.item.index;
				var curSlide = slider.find(".owl-item:eq(" + curSlideIndex + ")" + " " + slideClass);

				curSlide.addClass("current");
				curSlide.addClass("shown");
				slider.resize();

				if (slider.closest(".blocks").length)
					slider.closest(".blocks").find(".flex-direction-nav li").addClass("initied");

				if (slider.closest(".wraps").length)
					slider.closest(".wraps").find(".flex-direction-nav li").addClass("initied");

				if (!slider.find(".flex-control-nav li").length && slider.hasClass("normal")) {
					slider.find(".flex-direction-nav li a").addClass("flex-disabled");
				}

				if (slider.closest(".banners-big").length) {
					//nav flex color
					slider.find(".flex-control-nav li").removeClass();

					// header color
					$("header").removeClass("light dark");
					if (typeof curSlide.data("color") != "undefined") {
						$("header").addClass(curSlide.data("color"));
					}

					// logo color
					if (typeof window.headerLogo !== 'undefined') {
						window.headerLogo.setColorOfBanner(curSlide[0]);
					}
				}

				if (
					!slider.hasClass("flexslider-init-slice") &&
					slider.hasClass("nav-title") &&
					$(".gallery-block").closest(".tab-pane").hasClass("active")
				) {
					slider.find(".item").sliceHeight({ lineheight: -3 });
					slider.addClass("flexslider-init-slice");
				}

				if (slider.find(".flex-direction-nav").length) {
					if (slider.find(".flex-direction-nav").find("a.flex-disabled").length)
						slider.find(".flex-direction-nav").removeClass("opacity1").addClass("opacity0");
					else slider.find(".flex-direction-nav").removeClass("opacity0").addClass("opacity1");
				}
			}
		}
	} catch (e) {
	} finally {
		ignoreResize.pop();
	}
});

BX.addCustomEvent("onSlideBefore", function (eventdata) {
	try {
		ignoreResize.push(true);
		if (eventdata) {
			var slider = eventdata.slider;
			if (slider) {
				if (slider.closest(".banners-big").length) {
					setTimeout(function () {
						var curSlide = slider.find(".banners-big__item.current");

						//nav flex color
						slider.find(".flex-control-nav li").removeClass();

						// header color
						$("header").removeClass("light dark");
						if (typeof curSlide.data("color") != "undefined") {
							slider.find(".flex-control-nav li").addClass(curSlide.data("color"));
							$("header").addClass(curSlide.data("color"));
						}

						// logo color
						if (typeof window.headerLogo !== 'undefined') {
							window.headerLogo.setColorOfBanner(curSlide[0]);
						}
					}, 100);
				} else if (slider.hasClass("top-bigs")) {
					setTimeout(function () {
						var index = slider.find(".item.flex-active-slide").data("slide_key");
						$(".bxSlider.top-small .slides").data("bxSlider").goToSlide(index);
						$(".bxSlider.top-small .slides li").removeClass("flex-active-slide");
						$('.bxSlider.top-small .slides li[data-slide_key="' + index + '"]').addClass("flex-active-slide");
					}, 100);
				}
			}
		}
	} catch (e) {
	} finally {
		ignoreResize.pop();
	}
});

BX.addCustomEvent("onSlideEnd", function (eventdata) {
	try {
		ignoreResize.push(true);
		if (eventdata) {
			var slider = eventdata.slider;
		}
	} catch (e) {
	} finally {
		ignoreResize.pop();
	}
});

var onCaptchaVerifyinvisible = function (response) {
	$(".g-recaptcha:last").each(function () {
		var id = $(this).attr("data-widgetid");
		if (typeof id !== "undefined" && response) {
			if (!$(this).closest("form").find(".g-recaptcha-response").val())
				$(this).closest("form").find(".g-recaptcha-response").val(response);
			if ($("iframe[src*=recaptcha]").length) {
				$("iframe[src*=recaptcha]").each(function () {
					var block = $(this).parent().parent();
					if (!block.hasClass("grecaptcha-badge")) block.css("width", "100%");
				});
			}
			if ($(this).closest("form").attr("name") == "form_comment") BX.submit(BX("form_comment"));
			else $(this).closest("form").submit();
		}
	});
};

var onCaptchaVerifynormal = function (response) {
	$(".g-recaptcha").each(function () {
		var id = $(this).attr("data-widgetid");
		if (typeof id !== "undefined") {
			if (grecaptcha.getResponse(id) != "") {
				$(this).closest("form").find(".recaptcha").valid();
			}
		}
	});
};

// on submit form
BX.addCustomEvent('onSubmitForm', function (eventdata) {
	try {
		if (
      typeof eventdata === 'object' &&
      eventdata &&
      eventdata.form
    ) {
      if (!(eventdata.form instanceof Node)) {
        eventdata.form = eventdata.form[0];
      }

      (new Promise((resolve, reject) => {
        if (BX.Aspro?.Captcha) {
          BX.Aspro.Captcha.onSubmit(eventdata).then((result) => {
            resolve(result);
          }).catch((e) => {
            reject(e);
          });

          return;
        }

        resolve(true);
      })).then((result) => {
        if (result) {
          eventdata.form.submit();
          if (eventdata.form.closest('.form')) {
            eventdata.form.closest('.form').classList.add('sending');
          }
        }
      });
		}
	}
	catch (e) {
		console.error(e);
	}
});

const fileInputInit = function (message, reviews = 'N') {
	$("input[type=file]").uniform({
		fileButtonHtml: BX.message("JS_FILE_BUTTON_NAME"),
		fileDefaultHtml: message
	});

	$(document).on("change", "input[type=file]", function () {
		if ($(this).val()) {
			$(this).closest(".uploader").addClass("files_add");
			$(this).valid();
		} else {
			$(this).closest(".uploader").removeClass("files_add");
		}
	});

	$(".form .add_file").on("click", function () {
		const index = $(this).closest(".input").find("input[type=file]").length + 1;

		if (reviews === 'Y') {
			$('<input type="file" class="form-control inputfile" tabindex="3" id="comment_images_n' + index + '" name="comment_images[]" value=""  />').insertBefore(this);
		} else {
			$('<input type="file" id="POPUP_FILE' + index + '" name="FILE_n' + index + '"   class="inputfile" value="" />').inserBefore(this);
		}

		$("input[type=file]").uniform({
			fileButtonHtml: BX.message("JS_FILE_BUTTON_NAME"),
			fileDefaultHtml: message,
		});
	});
}
