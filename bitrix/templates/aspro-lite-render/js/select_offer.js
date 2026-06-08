/*sku change props*/
if (!("SelectOfferProp" in window) || typeof window.SelectOfferProp != "function") {
  SelectOfferProp = function () {
    let _this = $(this),
      obParams = {},
      obSelect = {},
      objUrl = parseUrlQuery(),
      add_url = "?site_id=" + arAsproOptions["SITE_ID"] + "&site_dir=" + arAsproOptions["SITE_DIR"],
      container = _this.closest(".sku-props"),
      item = _this.closest(".js-popup-block"),
      depthCount = _this.closest(".sku-props__inner").siblings().length,
      offersTreeDocFragment = item.find(".offers-template-json:first").prop("content"),
      offersTree = $(offersTreeDocFragment).find("div").data("json"),
      bDetail = _this.closest(".catalog-detail__top-info").length;

    /* request params */
    obParams = {
      PARAMS: (
        bDetail
          ? _this.closest(".catalog-detail").find(".js-sku-config:first").data("value")
          : _this.closest(".catalog-items").find(".js-sku-config").data("value")
      ),
      BASKET_PARAMS: item.find(".js-config-btns").data("btn-config"),
      ONE_CLICK_PARAMS: item.find(".js-config-ocb").data("ocb-config"),
      IMG_PARAMS: item.find(".js-config-img").data("img-config"),
      PRICE_PARAMS: item.find(".js-popup-price").data("price-config"),
      ID: container.data("item-id"),
      OFFERS_ID: container.data("offers-id"),
      OFFER_ID: container.data("offer-id"),
      SITE_ID: container.data("site-id"),
      IBLOCK_ID: container.data("iblockid"),
      SKU_IBLOCK_ID: container.data("offer-iblockid"),
      DEPTH: _this.closest(".sku-props__inner").index(),
      VALUE: _this.data("onevalue"),
      SHOW_GALLERY: arAsproOptions["THEME"]["SHOW_CATALOG_GALLERY_IN_LIST"],
      MAX_GALLERY_ITEMS: arAsproOptions["THEME"]["MAX_GALLERY_ITEMS"],
      OID: arAsproOptions["THEME"]["CATALOG_OID"],
      IS_DETAIL: bDetail ? "Y" : "N",
    };
    /**/

    if ("clear_cache" in objUrl) {
      if (objUrl.clear_cache == "Y") {
        add_url += "&clear_cache=Y";
      }
    }

    // let isActiveContainer = container.hasClass("js-selected");

    // set active
    $(".sku-props").removeClass("js-selected");
    container.addClass("js-selected");
    _this.closest(".sku-props__values").find(".sku-props__value").removeClass("sku-props__value--active");
    _this.addClass("sku-props__value--active");
    _this.closest(".sku-props__item").find(".sku-props__js-size").text(_this.data("title"));

    /* save selected values */
    for (i = 0; i < depthCount + 1; i++) {
      strName = "PROP_" + container.find(".sku-props__inner:eq(" + i + ")").data("id");
      obSelect[strName] = container.find(".sku-props__inner:eq(" + i + ") .sku-props__value--active").data("onevalue");
      obParams[strName] = container.find(".sku-props__inner:eq(" + i + ") .sku-props__value--active").data("onevalue");
    }
    obParams.SELECTED = JSON.stringify(obSelect);
    /**/

    appAspro.sku.init({
      selectedValues: obSelect,
      strPropValue: obParams["VALUE"],
      depth: obParams["DEPTH"],
      showSkuDescription: obParams["PARAMS"]["SHOW_SKU_DESCRIPTION"] === "Y",
    });

    /* get sku */
    if (offersTree && typeofExt(offersTree) === "array") {
      appAspro.sku.UpdateSKUInfoByProps(offersTree);
      obParams["SELECTED_OFFER_INDEX"] = appAspro.sku.GetCurrentOfferIndex(offersTree);
      obParams["SELECTED_OFFER_ID"] = offersTree[obParams["SELECTED_OFFER_INDEX"]]["ID"];
      if (
        appAspro.sku.obOffers[obParams["SELECTED_OFFER_ID"]] &&
        item.is(appAspro.sku.obOffers[obParams["SELECTED_OFFER_ID"]]["item"])
      ) {
        appAspro.sku.ChangeInfo(item, offersTree, obParams["SELECTED_OFFER_ID"]);
        finalActionSKUInfo(appAspro.sku.obOffers[obParams["SELECTED_OFFER_ID"]], item);
      } else {
        if (appAspro.sku.xhr[obParams["ID"]]) {
          appAspro.sku.xhr[obParams["ID"]].abort();
        }
        appAspro.sku.xhr[obParams["ID"]] = $.ajax({
          url: arAsproOptions["SITE_TEMPLATE_PATH"] + "/ajax/js_item_detail.php" + add_url,
          type: "POST",
          dataType: "json",
          contentType: "application/json",
          data: JSON.stringify(obParams),
        }).done(function (jsonData) {
          appAspro.sku.obOffers[obParams["SELECTED_OFFER_ID"]] = jsonData;
          appAspro.sku.obOffers[obParams["SELECTED_OFFER_ID"]]["item"] = item;
          appAspro.sku.ChangeInfo(item, offersTree, obParams["SELECTED_OFFER_ID"]);
          delete appAspro.sku.xhr[obParams["ID"]];
          finalActionSKUInfo(appAspro.sku.obOffers[obParams["SELECTED_OFFER_ID"]], item);
        });
      }
    }
  };
  $(document).on("click", ".sku-props__item .sku-props__value:not(.sku-props__value--active)", SelectOfferProp);

  function finalActionSKUInfo(currentOffer, wrapper) {
    const eventdata = {
      offer: currentOffer,
      wrapper: wrapper,
    };

    BX.onCustomEvent("onFinalActionSKUInfo", [eventdata]);
  }
}
