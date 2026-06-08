$(document).ready(function () {
  // dropdown-select
  $(document).on("click", ".dropdown-select--with-dropdown .dropdown-select__title", function (event) {
		if (event.target.closest('.delete_filter')) return;

    var _this = $(this),
      menu = _this.parent().find("> .dropdown-select__list");
    if (!_this.hasClass("clicked") && menu.length) {
      _this.addClass("clicked");

      if (_this.attr('data-expanded-prop')) {
        _this.removeAttr('data-expanded-prop');
      }

      _this.toggleClass("opened", menu.is(':hidden'));

      menu.stop().slideToggle(100, function () {
        _this.removeClass("clicked");
      });
    }
  });

  // dropdown-select change item
  $(document).on("click", ".dropdown-select__list .mixitup-item", function () {
    var $select = $(this).closest('.dropdown-select');
    $select.find('.dropdown-select__title span').text($(this).text());
    $select.find('.dropdown-select__title.opened').click();
  });

  // close select
  $("html, body").on("mousedown", function (e) {
    if (typeof e.target.className == "string" && e.target.className.indexOf("adm") < 0) {
      e.stopPropagation();
      $(".dropdown-select .dropdown-select__title.opened").each(function(){
        if ($(this).closest('#mobilefilter').length) return
        var $select = $(this).closest('.dropdown-select');
        if ($select.data('visible_by_class') && $($select.data('visible_by_class')).is(':visible')) return;
        if(!$(e.target).closest($select).length) {
          $(this).click();
        }
      });
    }
  });

  // mixitUp
  /*
  const containerEl = document.querySelector(".mixitup-container");
  if (containerEl) {
    var config = {
      selectors: {
        target: '[data-ref="mixitup-target"]',
      },
      animation: {
        effects: "", // Set a 'stagger' effect for the loading animation
      },
      load: {
        filter: "none", // Ensure all targets start from hidden (i.e. display: none;)
      },
      animation: {
        duration: 350,
      },
      controls: {
        scope: "local",
      },
      callbacks: {
        onMixStart: function (state) {
          // console.log(state);
        },
        onMixClick: function (state, event) {
          let $item = event.target.children.length ? event.target : event.target.parentElement;
          $($item).siblings().removeClass("color-theme head-block__item--active active");
          $($item).addClass("color-theme head-block__item--active active");
        },
        onMixEnd: function () {
          // InitLazyLoad();
        },
      },
    };
    var mixer = mixitup(containerEl, config);

    containerEl.classList.add("mixitup-ready");

    mixer.show().then(function () {
      // Remove the stagger effect for any subsequent operations
      mixer.configure({
        animation: {
          effects: "fade scale",
        },
      });
    });
  }*/

  /*show password*/
  $(".form-group:not(.eye-password-ignore) [type=password]").each(function (item) {
    let inputBlock = $(this).closest(".input");
    if (inputBlock.length) {
      inputBlock.addClass("eye-password");
    } else {
      let passBlock = $(this).closest(".form-group");
      let labelBlock = passBlock.find(".label_block");
      if (labelBlock.length) {
        labelBlock.addClass("eye-password");
      } else {
        passBlock.addClass("eye-password");
      }
    }
  });

  $(document).on("click", ".eye-password:not(.eye-password-ignore)", function (event) {
    let input = this.querySelector("input");
    let eyeWidth = 56;
    if (this.clientWidth - eyeWidth < event.offsetX) {
      if (input.type == "password") {
        input.type = "text";
        this.classList.add("password-show");
      } else if (input.type == "text") {
        input.type = "password";
        this.classList.remove("password-show");
      }
      event.stopPropagation();
    }
  });
  /**/
});

// gallery switch
$(document).on("click", ".gallery-view_switch__icons:not(.active)", function () {
  var $this = $(this);
  var animationTime = 200;
  var bSmall = $this.hasClass("gallery-view_switch__icons--small");
  var $switchGallery = $this.closest(".gallery-view_switch");
  var $bigGallery = ($this.closest(".big_gallery").length
    ? $this.closest(".big_gallery")
    : $this.closest(".gallery")
  ).find(".gallery-big");
  var $bigGalleryCounter = $switchGallery.find(".gallery-view_switch__count-wrapper--big");
  var $smallGallery = ($this.closest(".big_gallery").length
    ? $this.closest(".big_gallery")
    : $this.closest(".gallery")
  ).find(".gallery-small");
  var $smallGalleryCounter = $switchGallery.find(".gallery-view_switch__count-wrapper--small");
  var $toHideGallery = bSmall ? $bigGallery : $smallGallery;
  var $toHideGalleryCounter = bSmall ? $bigGalleryCounter : $smallGalleryCounter;
  var $toShowGallery = bSmall ? $smallGallery : $bigGallery;
  var $toShowGalleryCounter = bSmall ? $smallGalleryCounter : $bigGalleryCounter;

  $this.addClass("active");
  $this.siblings(".active").removeClass("active");

  $toHideGalleryCounter.fadeOut(animationTime, function () {
    $toShowGalleryCounter.fadeIn(animationTime);
  });

  $toHideGallery.fadeOut(animationTime, function () {
    $toShowGallery.fadeIn(animationTime);
  });
});

// reload captcha
$(document).on("click", ".refresh", function (e) {
  var captcha = $(this).parents(".captcha-row");
  e.preventDefault();
  $.ajax({
    url: arAsproOptions["SITE_DIR"] + "ajax/captcha.php",
  }).done(function (text) {
    captcha.find("input[name=captcha_sid],input[name=captcha_code]").val(text);
    captcha.find("img").attr("src", "/bitrix/tools/captcha.php?captcha_sid=" + text);
    captcha.find("input[name=captcha_word]").val("").removeClass("error");
    captcha.find(".captcha_input").removeClass("error").find(".error").remove();
  });
});
