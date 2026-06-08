function basketAction() {
  checkMinPrice();

  //remove4Cart
  if (typeof BX.Sale !== "undefined" && typeof BX.Sale === "object") {
    if (typeof BX.Sale.BasketComponent !== "undefined" && typeof BX.Sale.BasketComponent === "object") {
      $(document).on("click", ".basket-item-actions-remove", function () {
        var basketID = $(this).closest(".basket-items-list-item-container").data("id");
        if (!basketID) {
          basketID = $(this).closest(".basket-items-list-item-wrapper").data("id");
        }

        if (basketID && BX.Sale.BasketComponent.items && BX.Sale.BasketComponent.items[basketID]) {
          BX.onCustomEvent("onCounterGoals", [
            {
              goal: JItemActionBasket.prototype.getStateGoalCode(false),
              params: {
                id: BX.Sale.BasketComponent.items[basketID].PRODUCT_ID,
              },
            },
          ]);
        }
      });
    }
  }

  if (location.hash) {
    var hash = location.hash.substring(1);
    if ($("#basket_toolbar_button_" + hash).length) $("#basket_toolbar_button_" + hash).trigger("click");

    if ($('.basket-items-list-header-filter a[data-filter="' + hash + '"]').length)
      $('.basket-items-list-header-filter a[data-filter="' + hash + '"]')[0].click();
  }
  var svg_cross =
    '<svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 8 8"><path id="Rounded_Rectangle_568_copy_13" data-name="Rounded Rectangle 568 copy 13" class="cls-1" d="M1615.4,589l2.32,2.315a0.987,0.987,0,0,1,0,1.4,1,1,0,0,1-1.41,0L1614,590.4l-2.31,2.315a1,1,0,0,1-1.41,0,0.987,0.987,0,0,1,0-1.4L1612.6,589l-2.32-2.314a0.989,0.989,0,0,1,0-1.4,1,1,0,0,1,1.41,0l2.31,2.315,2.31-2.315a1,1,0,0,1,1.41,0,0.989,0.989,0,0,1,0,1.4Z" transform="translate(-1610 -585)"/></svg>';

  $(".bx_sort_container").append(
    '<div class="top_control basket_action"><span style="opacity:0;" class="delete_all colored_theme_hover_text remove_all_basket">' +
      svg_cross +
      BX.message("BASKET_CLEAR_ALL_BUTTON") +
      "</span></div>"
  );
  if ($(".basket-items-list-header-filter:not(.no-clear-button)").length) {
    $(".basket-items-list-header-filter").append(
      '<div class="top_control basket_action"><span style="opacity:1;" class="delete_all colored_theme_hover_text remove_all_basket">' +
        svg_cross +
        BX.message("BASKET_CLEAR_ALL_BUTTON") +
        "</span></div>"
    );

    var cur_index = $(".basket-items-list-header-filter > a.active").index();
    //fix delayed
    if (cur_index == 3) cur_index = 2;

    if ($(".basket-items-list-header-filter > a.active").data("filter") == "all") cur_index = "all";

    $(".basket-items-list-header-filter .top_control .delete_all").data("type", cur_index);

    $(".basket-items-list-header-filter > a").on("click", function () {
      var index = $(this).index();

      //fix delayed
      if (index == 3) index = 2;

      if ($(this).data("filter") == "all") index = "all";

      $(".basket-items-list-header-filter .top_control .delete_all").data("type", index);
    });
  } else {
    var cur_index = $(".bx_sort_container a.current").index();
    $(".bx_sort_container .top_control .delete_all").data("type", cur_index);
    if ($(".bx_ordercart > div:eq(" + cur_index + ") table tbody tr td.item").length)
      $(".bx_sort_container .top_control .delete_all").css("opacity", 1);

    $(".bx_ordercart .bx_ordercart_coupon #coupon").wrap('<div class="input"></div>');

    $(".bx_sort_container > a").on("click", function () {
      var index = $(this).index();
      $(".bx_sort_container .top_control .delete_all").data("type", index);

      if ($(".bx_ordercart > div:eq(" + index + ") table tbody tr td.item").length)
        $(".bx_sort_container .top_control .delete_all").css("opacity", 1);
      else $(".bx_sort_container .top_control .delete_all").css("opacity", 0);
    });
  }

  $(".basket_print").on("click", function () {
    // window.open(location.pathname+"?print=Y",'_blank');
    window.print();
  });

  $(document).on("click", ".delete_all", function () {
    if (arAsproOptions["COUNTERS"]["USE_BASKET_GOALS"] !== "N") {
      var eventdata = { goal: "goal_basket_clear", params: { type: $(this).data("type") } };
      BX.onCustomEvent("onCounterGoals", [eventdata]);
    }
    $.post(
      arAsproOptions["SITE_DIR"] + "ajax/item.php",
      "TYPE=" + $(this).data("type") + "&CLEAR_ALL=Y&action=basket_clear&sessid=" + BX.bitrix_sessid(),
      $.proxy(function (data) {
        location.reload();
      })
    );
  });

  if ($.fn.sliceHeight) {
    $(".bx_item_list_section .bx_catalog_item").sliceHeight({ row: ".bx_item_list_slide", item: ".bx_catalog_item" });
  }

  BX.addCustomEvent("onAjaxSuccess", function (e) {
    checkMinPrice();

    var errorText = $.trim($("#warning_message").text());
    $("#basket_items_list .error_text").detach();
    if (errorText != "") {
      $("#warning_message").hide().text("");
      $("#basket_items_list").prepend('<div class="error_text">' + errorText + "</div>");
    }

    if (typeof e === "object" && e && "BASKET_DATA" in e) {
      if ($("#ajax_basket").length) {
        reloadTopBasket("add", $("#ajax_basket"), 200, 5000, "Y");
      }
      if ($("#basket_line .basket_fly").length) {
        basketFly("open", "N");
      }
    }
    if (checkCounters("google")) {
      BX.unbindAll(
        BX.Sale.BasketComponent.getEntity(
          BX.Sale.BasketComponent.getCacheNode(BX.Sale.BasketComponent.ids.basketRoot),
          "basket-checkout-button"
        )
      );
    }
  });
  if (checkCounters("google")) {
    BX.unbindAll(
      BX.Sale.BasketComponent.getEntity(
        BX.Sale.BasketComponent.getCacheNode(BX.Sale.BasketComponent.ids.basketRoot),
        "basket-checkout-button"
      )
    );
  }
  $(document).on(
    "click",
    ".bx_ordercart_order_pay_center .checkout, .basket-checkout-section-inner .basket-btn-checkout",
    function () {
      if (checkCounters("google")) {
        const gotoOrder = function () {
          BX.Sale.BasketComponent.checkOutAction();
        };
        checkoutCounter(1, "start order", gotoOrder);
      }
    }
  );
}

function checkMinPrice() {
  if (arAsproOptions["PAGES"]["BASKET_PAGE"]) {
    var summ_raw = 0,
      summ = 0;
    if ($("#allSum_FORMATED").length) {
      summ_raw = $("#allSum_FORMATED")
        .text()
        .replace(/[^0-9\.,]/g, "");
      summ = parseFloat(summ_raw);
      if ($("#basket_items").length) {
        var summ = 0;
        $("#basket_items tr").each(function () {
          if (typeof $(this).data("item-price") !== "undefined" && $(this).data("item-price"))
            summ +=
              $(this).data("item-price") *
              $(this)
                .find("#QUANTITY_INPUT_" + $(this).attr("id"))
                .val();
        });
      }
      if (!$(".catalog_back").length)
        $(".bx_ordercart_order_pay_center").prepend(
          '<a href="' +
            arAsproOptions["PAGES"]["CATALOG_PAGE_URL"] +
            '" class="catalog_back btn btn-default btn-lg white grey">' +
            BX.message("BASKET_CONTINUE_BUTTON") +
            "</a>"
        );
    }

    if (arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y")
      $(".basket-coupon-section").addClass("smallest");

    if (typeof BX.Sale !== "undefined") {
      if (typeof BX.Sale.BasketComponent !== "undefined" && typeof BX.Sale.BasketComponent.result !== "undefined")
        summ = BX.Sale.BasketComponent.result.allSum;
    }

    if (arAsproOptions["PRICES"]["MIN_PRICE"]) {
      if (arAsproOptions["PRICES"]["MIN_PRICE"] > summ) {
        var svgMinPrice =
          '<i class="svg  svg-inline-price colored_theme_svg fill-theme-svg" aria-hidden="true"><svg id="Group_278_copy" data-name="Group 278 copy" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"><path id="Ellipse_305_copy_2" data-name="Ellipse 305 copy 2" class="clswm-1" d="M1851,561a19,19,0,1,1,19-19A19,19,0,0,1,1851,561Zm0-36a17,17,0,1,0,17,17A17,17,0,0,0,1851,525Zm3.97,10.375-0.03.266c-0.01.062-.02,0.127-0.03,0.188l-0.94,7.515h0a2.988,2.988,0,0,1-5.94,0H1848l-0.91-7.525c-0.01-.041-0.01-0.086-0.02-0.128l-0.04-.316h0.01c-0.01-.125-0.04-0.246-0.04-0.375a4,4,0,0,1,8,0c0,0.129-.03.25-0.04,0.375h0.01ZM1851,533a2,2,0,0,0-2,2,1.723,1.723,0,0,0,.06.456L1850,543a1,1,0,0,0,2,0l0.94-7.544A1.723,1.723,0,0,0,1853,535,2,2,0,0,0,1851,533Zm0,14a3,3,0,1,1-3,3A3,3,0,0,1,1851,547Zm0,4a1,1,0,1,0-1-1A1,1,0,0,0,1851,551Z" transform="translate(-1832 -523)"></path>  <path class="clswm-2 op-cls" style="opacity: 0.1" d="M1853,543l-1,1h-2l-1-1-1-8,1-2,1-1h2l1,1,1,2Zm-1,5,1,1v2l-1,1h-2l-1-1v-2l1-1h2Z" transform="translate(-1832 -523)"></path></svg></i>';
        if ($(".oneclickbuy.fast_order").length) $(".oneclickbuy.fast_order").remove();

        if ($(".basket-checkout-container").length) {
          if (!$(".icon_error_wrapper").length) {
            $(".basket-checkout-block.basket-checkout-block-btn").html(
              '<div class="icon_error_wrapper"><div class="icon_error_block">' +
                svgMinPrice +
                BX.message("MIN_ORDER_PRICE_TEXT").replace(
                  "#PRICE#",
                  jsPriceFormat(arAsproOptions["PRICES"]["MIN_PRICE"])
                ) +
                "</div></div>"
            );
          }
        } else {
          if (!$(".icon_error_wrapper").length && typeof jsPriceFormat !== "undefined") {
            $(".bx_ordercart_order_pay_center").prepend(
              '<div class="icon_error_wrapper"><div class="icon_error_block">' +
                svgMinPrice +
                BX.message("MIN_ORDER_PRICE_TEXT").replace(
                  "#PRICE#",
                  jsPriceFormat(arAsproOptions["PRICES"]["MIN_PRICE"])
                ) +
                "</div></div>"
            );
          }
          if ($(".bx_ordercart_order_pay .checkout").length) $(".bx_ordercart_order_pay .checkout").remove();
        }
      } else {
        if ($(".icon_error_wrapper").length) $(".icon_error_wrapper").remove();

        if ($(".basket-checkout-container").length) {
          if (
            !$(".oneclickbuy.fast_order").length &&
            arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y" &&
            !$(".basket-btn-checkout.disabled").length
          )
            $(".basket-checkout-section-inner").append(
              '<div class="fastorder"><span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
                BX.message("BASKET_QUICK_ORDER_BUTTON") +
                "</span></div>"
            );
        } else {
          if ($(".bx_ordercart_order_pay .checkout").length)
            $(".bx_ordercart .bx_ordercart_order_pay .checkout").css("opacity", "1");
          else
            $(".bx_ordercart_order_pay_center").append(
              '<a href="javascript:void(0)" onclick="checkOut();" class="checkout" style="opacity: 1;">' +
                BX.message("BASKET_ORDER_BUTTON") +
                "</a>"
            );
          if (!$(".oneclickbuy.fast_order").length && arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y")
            $(".bx_ordercart_order_pay_center").append(
              '<span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
                BX.message("BASKET_QUICK_ORDER_BUTTON") +
                "</span>"
            );
        }
      }
    } else {
      if ($(".basket-checkout-container").length) {
        if (
          !$(".oneclickbuy.fast_order").length &&
          arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y" &&
          !$(".basket-btn-checkout.disabled").length
        )
          $(".basket-checkout-section-inner").append(
            '<div class="fastorder"><span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
              BX.message("BASKET_QUICK_ORDER_BUTTON") +
              "</span></div>"
          );
      } else {
        $(".bx_ordercart .bx_ordercart_order_pay .checkout").css("opacity", "1");
        if (!$(".oneclickbuy.fast_order").length && arAsproOptions["THEME"]["SHOW_ONECLICKBUY_ON_BASKET_PAGE"] == "Y")
          $(".bx_ordercart_order_pay_center").append(
            '<span class="oneclickbuy btn btn-lg fast_order btn-transparent-border-color" onclick="oneClickBuyBasket()">' +
              BX.message("BASKET_QUICK_ORDER_BUTTON") +
              "</span>"
          );
      }
    }

    showBasketHeadingBtn();

    $("#basket-root .basket-checkout-container .basket-checkout-section .basket-checkout-block .basket-btn-checkout");
    $("#basket-root .basket-checkout-container").addClass("visible");
  }
}

function showBasketHeadingBtn() {
  if (
    BX.Sale &&
    BX.Sale.BasketComponent &&
    "items" in BX.Sale.BasketComponent &&
    "result" in BX.Sale.BasketComponent &&
    BX.Sale.BasketComponent.result.BASKET_ITEMS_COUNT
  ) {
    if (document.querySelector(".page-top h1")) {
      var topicHeading = document.querySelector(".page-top .topic__heading");

      if (topicHeading) {
        BX.addClass(topicHeading, "flexbox--wrap-nowrap");

        if (arAsproOptions["THEME"]["SHOW_DOWNLOAD_BASKET"] === "Y") {
          if (!document.querySelector(".btn_basket_heading--download")) {
            var btnDownloadBasket = BX.create({
              tag: "div",
              attrs: {
                class: "heading-icons",
              },
              events: {
                click: BX.proxy(function (e) {
                  if (!e) {
                    e = window.event;
                  }

                  BX.PreventDefault(e);

                  var button = e.target.querySelector(".btn_basket_heading");
                  if (button) {
                    if (BX.hasClass(button, "loadings")) {
                      return;
                    }

                    BX.addClass(button, "loadings");
                    setTimeout(function () {
                      BX.removeClass(button, "loadings");
                    }, 2000);
                  }

                  location.href =
                    arAsproOptions["SITE_DIR"] + "ajax/download_basket.php?params[type]=" +
                    arAsproOptions["THEME"]["BASKET_FILE_DOWNLOAD_TEMPLATE"];
                }, this),
              },
              html:
                '<div class="item-action item-action--horizontal fill-theme-hover hover-block"><div class="hover-block__item"><div class="btn-heading btn_basket_heading btn_basket_heading--download colored_theme_hover_bg-block btn_heading--with_title" title="' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_DOWNLOAD_BASKET"] +
                '" data-event="jqm" data-param-form_id="share_basket" data-name="share_basket"><i class="svg colored_theme_hover_bg-el-svg"><svg class="svg-file-download" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6 2C5.44772 2 5 2.44772 5 3V6H7C8.10457 6 9 6.89543 9 8V10C9 11.1046 8.10457 12 7 12H5V13C5 13.5523 5.44772 14 6 14H13C13.5523 14 14 13.5523 14 13V6H12C10.8954 6 10 5.10457 10 4V2H6ZM12 2.41421V4H13.5858L12 2.41421ZM3 3V6H2C0.895431 6 0 6.89543 0 8V10C0 11.1046 0.89543 12 2 12H3V13C3 14.6569 4.34315 16 6 16H13C14.6569 16 16 14.6569 16 13V4.82843C16 4.03278 15.6839 3.26972 15.1213 2.70711L13.2929 0.87868C12.7303 0.31607 11.9672 0 11.1716 0H6C4.34315 0 3 1.34315 3 3ZM7 8H2V10H7V8Z" fill="#B8B8B8"></path></svg></i><span class="title">' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_DOWNLOAD_BASKET"] +
                "</span></div></div></div>",
            });
            BX.append(btnDownloadBasket, topicHeading);
          }
        }

        if (arAsproOptions["THEME"]["SHOW_BASKET_PRINT"] === "Y") {
          if (!document.querySelector(".btn_basket_heading--print")) {
            var btnPrintBasket = BX.create({
              tag: "div",
              attrs: {
                class: "heading-icons",
              },
              html:
                '<div class="item-action item-action--horizontal fill-theme-hover hover-block"><div class="hover-block__item"><div class="print-link btn-heading btn_basket_heading btn_heading--with_title btn_basket_heading--print" title="' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_PRINT_PAGE"] +
                '"><i class="svg colored_theme_hover_bg-el-svg"><svg class="svg-print" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6 4H10V2H6V4ZM12 4V2C12 0.895431 11.1046 0 10 0H6C4.89543 0 4 0.895431 4 2V4H3C1.34315 4 0 5.34315 0 7V10C0 11.6569 1.34315 13 3 13H4V14C4 15.1046 4.89543 16 6 16H10C11.1046 16 12 15.1046 12 14V13H13C14.6569 13 16 11.6569 16 10V7C16 5.34315 14.6569 4 13 4H12ZM11 6H5H3C2.44772 6 2 6.44772 2 7V10C2 10.5523 2.44772 11 3 11H4V9C4 8.44772 4.44772 8 5 8H11C11.5523 8 12 8.44772 12 9V11H13C13.5523 11 14 10.5523 14 10V7C14 6.44772 13.5523 6 13 6H11ZM10 14H6V10H10V14Z" fill="#b8b8b8"></path></svg></i><span class="title">' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_PRINT_PAGE"] +
                "</span></div></div></div>",
            });
            BX.append(btnPrintBasket, topicHeading);
          }
        }

        if (arAsproOptions["THEME"]["SHOW_SHARE_BASKET"] === "Y") {
          if (!document.querySelector(".btn_basket_heading--share")) {
            var btnShareBasket = BX.create({
              tag: "div",
              attrs: {
                class: "heading-icons",
              },
              html:
                '<div class="item-action item-action--horizontal fill-theme-hover hover-block"><div class="hover-block__item"><div class="btn-heading btn_basket_heading basket-checkout-block btn_basket_heading--share animate-load colored_theme_hover_bg-block btn_heading--with_title" title="' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_SHARE_BASKET"] +
                '" data-event="jqm" data-param-form_id="share_basket" data-name="share_basket"><i class="svg colored_theme_hover_bg-el-svg"><svg class=" svg-share" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.70711 0.292894C8.31658 -0.0976312 7.68342 -0.0976312 7.29289 0.292894L4.29289 3.29289C3.90237 3.68342 3.90237 4.31658 4.29289 4.70711C4.68342 5.09763 5.31658 5.09763 5.70711 4.70711L7 3.41421V10C7 10.5523 7.44772 11 8 11C8.55228 11 9 10.5523 9 10V3.41421L10.2929 4.70711C10.6834 5.09763 11.3166 5.09763 11.7071 4.70711C12.0976 4.31658 12.0976 3.68342 11.7071 3.29289L8.70711 0.292894Z" fill="#222222"></path><path d="M2 10C2 9.44772 2.44771 9 3 9C3.55228 9 4 8.55228 4 8C4 7.44772 3.55228 7 3 7C1.34315 7 0 8.34315 0 10V13C0 14.6569 1.34315 16 3 16H13C14.6569 16 16 14.6569 16 13V10C16 8.34315 14.6569 7 13 7C12.4477 7 12 7.44772 12 8C12 8.55228 12.4477 9 13 9C13.5523 9 14 9.44772 14 10V13C14 13.5523 13.5523 14 13 14H3C2.44772 14 2 13.5523 2 13V10Z" fill="#B8B8B8"></path></svg></i><span class="title">' +
                arAsproOptions["THEME"]["EXPRESSION_FOR_SHARE_BASKET"] +
                "</span></div></div></div>",
            });

            BX.append(btnShareBasket, topicHeading);
          }
        }

        var eventdata = { parent: topicHeading };
        BX.onCustomEvent("onShowBasketHeadingBtn", [eventdata]);
      }
    }
  } else {
    BX.remove(document.querySelector(".btn_basket_heading--download"));
    BX.remove(document.querySelector(".btn_basket_heading--print"));
    BX.remove(document.querySelector(".btn_basket_heading--share"));
  }
}

readyDOM(function () {
  basketAction();
});
