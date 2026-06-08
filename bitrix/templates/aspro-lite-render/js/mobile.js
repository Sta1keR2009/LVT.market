$(document).ready(function () {
  /*  --- Bind mobile filter  --- */
  var $mobilefilter = $("#mobilefilter");
  var $mobiledMenu = $("#mobilemenu");
  if ($mobilefilter.length) {
    $mobilefilter.isOpen = $mobiledMenu.hasClass("show");
    $mobilefilter.isAppendLeft = false;
    $mobilefilter.isWrapFilter = false;
    $mobilefilter.isHorizontalOrCompact = $(".filter_horizontal").length || $(".bx_filter_vertical.compact").length;
    $mobilefilter.close = '<i class="svg svg-close close-icons"></i>';

    $(document).on("click", ".bx-filter-title", function () {
      OpenMobileFilter();
    });

    $(document).on("click", "#mobilefilter .svg-close.close-icons", function () {
      CloseMobileFilter();
    });

    $(document).on("click", ".bx_filter_select_block", function (e) {
      var bx_filter_select_container = $(e.target).parents(".bx_filter_select_container");
      if (bx_filter_select_container.length) {
        var prop_id = bx_filter_select_container.closest(".bx_filter_parameters_box").attr("data-property_id");
        if ($("#smartFilterDropDown" + prop_id).length) {
          $("#smartFilterDropDown" + prop_id).css({
            "max-width": bx_filter_select_container.width(),
            "z-index": "3020",
          });
        }
      }
    });

    $(document).on("click", ".bx_filter_search_button", function (e) {
      // if ($(e.target).hasClass("bx_filter_search_button")) {
        CloseMobileFilter();
      // }
    });

    $(document).on("mouseup", ".bx_filter_parameters_box_title", function (e) {
      $("[id^='smartFilterDropDown']").hide();
      if ($(e.target).hasClass("close-icons")) {
        CloseMobileFilter();
      }
    });

    $mobilefilter.parent().append('<div id="mobilefilter-overlay"></div>');
    var $mobilefilterOverlay = $("#mobilefilter-overlay");

    $mobilefilterOverlay.click(function () {
      if ($mobilefilter.isOpen) {
        CloseMobileFilter();
        //e.stopPropagation();
      }
    });

    mobileFilterNum = function (num, def) {
      if (def) {
        $(".bx_filter_search_button").val(num);
      } else {
        var str = "";
        var $prosLeng = $(".bx_filter_parameters_box > span");

        str +=
          $prosLeng.data("f") +
          " " +
          num
          // " " +
          // declOfNumFilter(num, [$prosLeng.data("fi"), $prosLeng.data("fr"), $prosLeng.data("frm")]);
        $(".bx_filter_search_button").val(str);
      }
    };

    declOfNumFilter = function (number, titles) {
      cases = [2, 0, 1, 1, 1, 2];
      return titles[number % 100 > 4 && number % 100 < 20 ? 2 : cases[number % 10 < 5 ? number % 10 : 5]];
    };

    OpenMobileFilter = function () {
      if (!$mobilefilter.isOpen) {
        $("body").addClass("jqm-initied wf");

        $(".bx_filter_vertical .slide-block__head.filter_title").removeClass("closed");

        $(".bx_filter_vertical .slide-block__head.filter_title + .slide-block__body").show();

        if (!$mobilefilter.isAppendLeft) {
          if (!$mobilefilter.isWrapFilter) {
            $(".bx_filter").wrap("<div id='wrapInlineFilter'></div>");
            $mobilefilter.isWrapFilter = true;
          }
          $(".bx_filter").appendTo($("#mobilefilter"));
          var helper = $("#filter-helper");
          if (helper.length) {
            helper.prependTo($("#mobilefilter .bx_filter_parameters"));
          }
          $mobilefilter.isAppendLeft = true;
        }
        if (typeof checkFilterLandgings === "function") {
          checkFilterLandgings();
        }

        $("#mobilefilter .bx_filter_parameters").addClass("mobile-scroll scrollbar");

        $(".bx_filter_button_box.ajax-btns").addClass("colored_theme_bg");
        $(".bx_filter_button_box.ajax-btns .filter-bnt-wrapper").removeClass("hidden");

        // show overlay
        setTimeout(function () {
          $mobilefilterOverlay.fadeIn("fast");
        }, 100);

        // fix body
        $("body").css({ overflow: "hidden", height: "100vh" });

        // show mobile filter
        $mobilefilter.addClass("show");
        $mobilefilter.find(".bx_filter").css({ display: "block" });
        $mobilefilter.isOpen = true;

        $("#mobilefilter .bx_filter_button_box.btns.ajax-btns").removeClass("hidden");

        var init = $mobilefilter.data("init");
        if (typeof init === "undefined") {
          $mobilefilter.scroll(function () {
            $(".bx_filter_section .bx_filter_select_container").each(function () {
              var prop_id = $(this).closest(".bx_filter_parameters_box").attr("data-property_id");
              if ($("#smartFilterDropDown" + prop_id).length) {
                $("#smartFilterDropDown" + prop_id).hide();
              }
            });
          });

          $mobilefilter.data("init", "Y");
        }
      }
    };

    CloseMobileFilter = function (append) {
      $mobilefilter.find(".bx_filter_parameters").removeClass("scrollbar");

      $("body").removeClass("jqm-initied wf");

      $(".bx_filter:not(.n-ajax) .bx_filter_button_box.ajax-btns .filter-bnt-wrapper").addClass("hidden");

      if ($mobilefilter.isOpen) {
        // scroll to top
        $mobilefilter.find(".bx_filter_parameters").scrollTop(0);

        // unfix body
        $("body").css({ overflow: "", height: "" });

        // hide overlay
        setTimeout(function () {
          $mobilefilterOverlay.fadeOut("fast");
        }, 100);

        // hide mobile filter
        $mobilefilter.removeClass("show");
        $mobilefilter.isOpen = false;
      }

      if (append && $mobilefilter.isAppendLeft) {
        $("#mobilefilter .bx_filter_parameters_box_title").removeClass("opened");
        $("#mobilefilter .bx_filter_block.dropdown-menu-wrapper").hide();

        $(".bx_filter").appendTo($("#wrapInlineFilter")).show();
        var helper = $("#filter-helper");
        if (helper.length) {
          helper.appendTo($("#filter-helper-wrapper"));
        }
        $mobilefilter.isAppendLeft = false;
        $mobilefilter.removeData("init");
        // mobileFilterNum($("#modef_num_mobile"), true);
      }
    };

    checkMobileFilter = function () {
      if (
        (!window.matchMedia("(max-width: 991px)").matches && !$mobilefilter.isHorizontalOrCompact) ||
        (!window.matchMedia("(max-width: 767px)").matches && $mobilefilter.isHorizontalOrCompact)
      ) {
        CloseMobileFilter(true);
      }
    };
  }
  /*  --- END Bind mobile filter  --- */
});
