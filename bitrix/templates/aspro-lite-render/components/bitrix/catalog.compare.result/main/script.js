BX.namespace("BX.Iblock.Catalog");

BX.Iblock.Catalog.CompareClass = (function () {
  var CompareClass = function (wrapObjId) {
    this.wrapObjId = wrapObjId;
  };

  CompareClass.prototype.MakeAjaxAction = function (url, refresh) {
    BX.showWait(BX(this.wrapObjId));
    BX.ajax.post(
      url,
      {
        ajax_action: "Y",
      },
      BX.proxy(function (result) {
        url = new URL(window.location.origin + url);
        const action = url.searchParams.get('action');
        const id = url.searchParams.get('ID');

        if (action === 'DELETE_FROM_COMPARE_RESULT' && typeof JNoticeSurface !== 'undefined' && id) {
          const $block = document.querySelector('[data-id="' + id + '"][data-item]');
          JNoticeSurface.get().onAdd2Compare([$block], false);
        }

        BX(this.wrapObjId).innerHTML = result;
        if (typeof refresh !== undefined) {
          const $compareItems = document.querySelector(".catalog-compare");

          if (!$compareItems) {
            arAsproOptions["COMPARE_ITEMS"] = [];
            $(".js-compare-block .count").text(arAsproOptions["COMPARE_ITEMS"].length);

            document.querySelectorAll(".js-compare-block.icon-block-with-counter--count").forEach(function ($el) {
              $el.classList.remove("icon-block-with-counter--count");
            });
          }
        }
        BX.closeWait();
      }, this)
    );
  };

  return CompareClass;
})();

$(document).on("change", ".catalog-compare__switch #compare_diff", function () {
  var linksDiff = $(this).closest(".catalog-compare__top").find(".tabs-head"),
    url = "";

  if ($(this).is(":checked")) {
    url = linksDiff.find("li:eq(1) a").data("href");
  } else {
    url = linksDiff.find("li:eq(0) a").data("href");
  }

  BX.showWait(BX("bx_catalog_compare_block"));
  $.ajax({
    url: url,
    data: { ajax_action: "Y" },
    success: function (html) {
      history.pushState(null, null, url);
      $("#bx_catalog_compare_block").html(html);
      BX.closeWait();
    },
  });
});

function tableEqualHeight($sliderProps, $sliderPropsItems) {
  var arHeights = [];

  $sliderProps.find(".catalog-compare__prop-line").removeAttr("style");

  for (var i = 0; i < $sliderProps.find(".owl-item:first-child .catalog-compare__prop-line").length; i++) {
    arHeights[i] = 0;
  }

  //get max height
  $sliderPropsItems.each(function (i, elementI) {
    $(this)
      .find(".catalog-compare__prop-line")
      .each(function (j, elementJ) {
        if ($(this).outerHeight() > arHeights[j]) arHeights[j] = $(this).outerHeight(true);
      });
  });

  // set height
  $sliderPropsItems.each(function (i, elementI) {
    $(this)
      .find(".catalog-compare__prop-line")
      .each(function (j, elementJ) {
        $(this).css("height", arHeights[j]);
      });
  });
}

BX.addCustomEvent("onSliderInitialized", function (eventdata) {
  if (eventdata) {
    var slider = eventdata.slider;
    if (slider) {
      $(".catalog-compare__inner").removeClass("loading");
    }
  }
});

function stickyCompareItems() {
  if (window.matchMedia("(min-width:768px)").matches) {
    let propSlider = $(".compare-sections__item.active .catalog-compare__props-slider:visible");
    let headerSelector = window.matchMedia("(min-width:992px)").matches
      ? "#headerfixed"
      : ".mfixed_y.mfixed_view_always #mobileheader, .mfixed_y.mfixed_view_scroll_top #mobileheader.fixed";
    let headerHeight = $(headerSelector).length ? $(headerSelector).height() : 0;
    let comparePosition = propSlider.length > 0 ? propSlider.offset().top : 0;
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    let stickyItems = propSlider.find(".catalog-small__item");
    let topPos = 0;
    if (stickyItems.length) {
      topPos = scrollTop - comparePosition + headerHeight;
      stickyItems.css("top", topPos - 1 + "px");
    }
    if (headerHeight + scrollTop > comparePosition) {
      propSlider.addClass("show-sticky-items");
    } else {
      propSlider.removeClass("show-sticky-items");
    }
  }
}

if ($("html.bx-touch").length) {
  $(document).scroll(debounce(stickyCompareItems, 100));
} else {
  $(document).scroll(stickyCompareItems);
}
//$(document).scroll(throttle(stickyCompareItems, 50));
//$(document).scroll(debounce(stickyCompareItems, 200));

$(document).on("click", ".compare-sections__tab-item", function () {
  let th = $(this);
  if (!th.hasClass("active")) {
    let curSectionId = th.find("[data-section-id]").attr("data-section-id");
    $(".compare-sections__tab-item").removeClass("active");
    $(".compare-sections__item").removeClass("active");
    th.addClass("active");
    if (curSectionId) {
      $(".compare-sections__item" + "[data-section-id=" + curSectionId + "]").addClass("active");
      $.cookie("compare_section", curSectionId);
    }
  }
});
