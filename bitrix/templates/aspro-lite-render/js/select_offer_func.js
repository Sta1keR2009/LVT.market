window.appAspro = window.appAspro || {};

if (!window.appAspro.sku) {
  window.appAspro.sku = {
    xhr: {},
    obOffers: {},
    init: function (options) {
      const optionDefault = {
        containerClass: ".sku-props.js-selected",
        depth: 0,
        strPropValue: "",
        selectedValues: "",
        showAbsent: true,
        showSkuDescription: false,
      };
      this.options = Object.assign({}, optionDefault, options);
    },
    GetRowValues: function (arFilter, index, offersTree) {
      var i = 0,
        j,
        arValues = [],
        boolSearch = false,
        boolOneSearch = true;

      if (0 === arFilter.length) {
        for (i = 0; i < offersTree.length; i++) {
          if (!BX.util.in_array(offersTree[i].TREE[index], arValues))
            arValues[arValues.length] = offersTree[i].TREE[index].toString();
        }
        boolSearch = true;
      } else {
        for (i = 0; i < offersTree.length; i++) {
          boolOneSearch = true;
          for (j in arFilter) {
            //if (arFilter[j]) {
            if (arFilter[j].toString() !== offersTree[i].TREE[j].toString()) {
              boolOneSearch = false;
              break;
            }
            //}
          }

          if (boolOneSearch) {
            if (!BX.util.in_array(offersTree[i].TREE[index], arValues))
              arValues[arValues.length] = offersTree[i].TREE[index].toString();
            boolSearch = true;
          }
        }
      }
      return boolSearch ? arValues : false;
    },

    GetCanBuy: function (arFilter, offersTree) {
      var i = 0,
        j,
        boolSearch = false,
        boolOneSearch = true;

      for (i = 0; i < offersTree.length; i++) {
        boolOneSearch = true;
        for (j in arFilter) {
          if (arFilter[j].toString() !== offersTree[i].TREE[j].toString()) {
            boolOneSearch = false;
            break;
          }
        }
        if (boolOneSearch) {
          if (offersTree[i].CAN_BUY) {
            boolSearch = true;
            break;
          }
        }
      }
      return boolSearch;
    },

    GetCurrentOfferIndex: function (offersTree) {
      let i = 0,
        j,
        index = -1,
        boolOneSearch = true;

      if ($(this.options.containerClass).data("selected")) {
        this.options.selectedValues = $(this.options.containerClass).data("selected");
      }

      for (i = 0; i < offersTree.length; i++) {
        boolOneSearch = true;
        for (j in this.options.selectedValues) {
          if (this.options.selectedValues[j]) {
            if (this.options.selectedValues[j].toString() !== offersTree[i].TREE[j].toString()) {
              boolOneSearch = false;
              break;
            }
          }
        }
        if (boolOneSearch) {
          index = i;
          break;
        }
      }

      return index;
    },

    ChangeInfo: function (wrapper, offersTree, currentOfferId = 0) {
      let index = -1;

      if (!currentOfferId) {
        // old code never run
        index = this.GetCurrentOfferIndex(offersTree);
        currentOfferId = offersTree[index]["ID"];
      }

      if (currentOfferId) {
        this.UpdateStatus(currentOfferId, ".js-replace-status:first", wrapper);
        this.UpdateStatus(currentOfferId, ".js-popup-block-adaptive .js-replace-status:first", wrapper);
        this.UpdateArticle(currentOfferId, ".js-replace-article:first", wrapper);
        this.UpdateArticle(currentOfferId, ".js-popup-block-adaptive .js-replace-article:first", wrapper);
        this.UpdatePrice(currentOfferId, ".js-popup-price:first", wrapper);
        this.UpdatePrice(currentOfferId, ".js-popup-block-adaptive .js-popup-price:first", wrapper);
        this.UpdateItemInfoForBasket(currentOfferId, "[data-item]:first", wrapper);
        this.UpdateItemInfoForBasket(currentOfferId, ".js-popup-block-adaptive [data-item]:first", wrapper);
        this.UpdateBtnBasket(currentOfferId, ".js-replace-btns:first", wrapper);
        this.UpdateBtnBasket(currentOfferId, ".js-popup-block-adaptive .js-replace-btns:first", wrapper);
        this.UpdateOneClickBuy(currentOfferId, ".js-replace-ocb:first", wrapper);
        this.UpdateProps(currentOfferId, wrapper);
        this.UpdateGroupPropsSKU(currentOfferId, wrapper);
        this.UpdateImages(currentOfferId, wrapper);
        this.UpdateSideIcons(currentOfferId, wrapper);
        this.UpdateLink(currentOfferId, wrapper);
        this.UpdateName(currentOfferId, ".switcher-title:first", wrapper);
        this.UpdateDescription(currentOfferId, wrapper);
        this.UpdateSKUBlocks(currentOfferId);
        this.Add2Viewed(currentOfferId);
        JItemActionBasket.markItems();
        JItemActionCompare.markItems();
        JItemActionFavorite.markItems();
        JItemActionSubscribe.markItems();
      }
    },

    Add2Viewed: function (index) {
      let viewedParams = this.obOffers[index]["VIEWED_PARAMS"];
      if (typeof viewedParams === "object" && viewedParams) {
        if (typeof JViewed === "function") {
          JViewed.get().addProduct(viewedParams.ID, viewedParams);
        }
      }
    },

    UpdateDescription: function setDescriptionSKU(index, wrapper) {
      if (!this.options.showSkuDescription) {
        return;
      }

      const detailText = this.obOffers[index]["DETAIL_TEXT"] ? this.obOffers[index]["DETAIL_TEXT"] : "";
      const previewText = this.obOffers[index]["PREVIEW_TEXT"] ? this.obOffers[index]["PREVIEW_TEXT"] : "";

      const $detailDescription = wrapper.find(".js-detail-description");
      if ($detailDescription.length) {
        $detailDescription.html(detailText);
        $detailDescription.parent().toggleClass("hidden", !detailText);

        const $tab = wrapper.find('.tabs-history a[href="#desc"]').parent();
        if ($tab.length) {
          $tab.toggleClass("hidden", !detailText);

          if ($tab.hasClass("active") && !detailText) {
            const $availableTab = wrapper.find(".tabs-history li:not(.hidden) a");
            if ($availableTab.length) {
              $availableTab[0].click();
            }
          }
        }
      }

      const $previewDescription = wrapper.find(".js-preview-description");
      if ($previewDescription) {
        $previewDescription.html(previewText);
        $previewDescription.parent().toggleClass("hidden", !previewText);

        const $detailDescriptionAnchor = wrapper.find(".js-detail-description-anchor");
        if ($detailDescriptionAnchor) {
          $detailDescriptionAnchor.toggleClass("hidden", !detailText);
        }
      }
    },

    UpdateName: function (index, selector, wrapper) {
      let isDetail = wrapper.hasClass("catalog-detail");
      if (
        (!isDetail && arLiteOptions["THEME"]["CHANGE_TITLE_ITEM_LIST"] === "Y") ||
        (isDetail && arLiteOptions["THEME"]["CHANGE_TITLE_ITEM_DETAIL"] === "Y")
      ) {
        const $node = wrapper.find(selector);
        if ($node.length && this.CheckWrapper($node, wrapper)) {
          $node.html(this.obOffers[index]["NAME"]);
        }
      }
      wrapper.find(".catalog-block__info").css("height", "");
    },
    UpdateLink: function (index, wrapper) {
      const $titleLink = wrapper.find(".js-popup-title:first");
      if ($titleLink.length && this.CheckWrapper($titleLink, wrapper)) {
        let url = $titleLink.attr("href");
        const $detailBlock = wrapper.find(".detail-block");
        if (arAsproOptions["OID"]) {
          const re = new RegExp("(" + arAsproOptions["OID"] + "=)" + "(\\d+)");
          if (url && !$detailBlock.length) {
            const matches = url.match(re);
            if (matches && matches.length === 3) {
              url = url.replace(matches[2], this.obOffers[index]["ID"]);
              $titleLink.attr("href", url);
              if (wrapper.find(".image-list__link").length) {
                wrapper.find(".image-list__link").attr("href", url);
              }
            }

            const $moreLink = wrapper.find(".js-replace-more");
            if ($moreLink.length) {
              $moreLink.attr("href", url);
            }
            const data = wrapper.find("[data-item]:first").data("item");
            if (data) {
              data["DETAIL_PAGE_URL"] = url;
              wrapper.find("[data-item]:first").data("item", data);
            }
          } else {
            let href = window.location.href;
            let matches = href.match(re);
            if (matches && matches.length === 3) {
              href = href.replace(matches[2], this.obOffers[index]["ID"]);
            } else {
              let queryString = location.search;
              if (queryString) {
                matches = queryString.match(re);
                if (!matches) {
                  queryString += "&" + arAsproOptions["OID"] + "=" + this.obOffers[index]["ID"];
                }
              } else {
                queryString = "?" + arAsproOptions["OID"] + "=" + this.obOffers[index]["ID"];
              }
              href = location.pathname + queryString;
            }
            history.replaceState(null, null, href);
          }
        }
      }
    },
    UpdateStatus: function (index, selector, wrapper) {
      const $status = wrapper.find(selector);
      if ($status.length && this.CheckWrapper($status, wrapper)) {
        const $parent = $status.closest("[data-state]");
        let value = "";
        let state = $parent.data("state");
        let $iconContainer = $status.closest(".status-container").find(".status__svg-icon");
        let $icon = $iconContainer.find("use");
        let xlink = "";

        $status.removeClass(state);
        $parent.removeClass(state);

        if (this.obOffers[index]["STATUS"]["NAME"]) {
          const prevState = state;

          value = this.obOffers[index]["STATUS"]["NAME"];
          state = this.obOffers[index]["STATUS"]["CODE"];

          if ($icon.length) {
            $iconContainer.removeClass(prevState);
            xlink = $icon.attr("xlink:href").replace(prevState, state);
          }
        }

        if (value) {
          $status.html(value);
        }
        $parent.data("state", state);

        $status.addClass(state);
        $parent.addClass(state);

        if ($icon.length) {
          $icon.attr("xlink:href", xlink);
          $iconContainer.addClass(state);
        }

        if (funcDefined("showItemStoresAmount")) {
          showItemStoresAmount();
        }
      }
    },
    UpdateArticle: function (index, selector, wrapper) {
      const $article = wrapper.find(selector);
      if (
        $article.length &&
        this.CheckWrapper($article, wrapper) &&
        this.obOffers[index]["DISPLAY_PROPERTIES"] &&
        this.obOffers[index]["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]
      ) {
        let value = $article.data("value");
        if (this.obOffers[index]["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"]) {
          value = this.obOffers[index]["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"];
        }
        $article.text(value);
      }
    },
    UpdatePrice: function (index, selector, wrapper) {
      const $price = wrapper.find(selector);
      if ($price.length && this.CheckWrapper($price, wrapper)) {
        if (this.obOffers[index]["PRICES_HTML"]) {
          $price.parent(".line-block").removeClass("hidden");
        } else {
          if (!$price.next().length) {
            $price.parent(".line-block").addClass("hidden");
          }
        }

        wrapper.find(".js-image-block").next('.price-economy').remove();
        if (this.obOffers[index]["DISCOUNT_PRICES_HTML"]) {
          wrapper.find(".js-image-block").parent().append(this.obOffers[index]["DISCOUNT_PRICES_HTML"]);
        }

        var eventdata = {
          offer: this.obOffers[index],
          product: this.obOffers[index].item,
          obPrices: this.obOffers[index].PRICES,
          measure: this.obOffers[index].PRICES_HTML
            ? $(this.obOffers[index].PRICES_HTML).find(".price__new-val span").text()
            : "",
        };
        BX.onCustomEvent("onAsproSkuSetPrice", [eventdata]);

        $price.html(this.obOffers[index]["PRICES_HTML"]);
      }
    },
    UpdateItemInfoForBasket: function (index, selector, wrapper) {
      const $infoBasket = wrapper.find(selector);
      if ($infoBasket.length && this.CheckWrapper($infoBasket, wrapper)) {
        $infoBasket.data("id", this.obOffers[index]["ID"]).data("item", this.obOffers[index]["BASKET_JSON"]);
      }
    },
    UpdateBtnBasket: function (index, selector, wrapper) {
      const $btn = wrapper.find(selector);
      if ($btn.length && this.CheckWrapper($btn, wrapper)) {
        if (this.obOffers[index]["BASKET_HTML"]) {
          $btn.closest(".js-btn-state-wrapper").removeClass("hidden");
          $btn.closest(".catalog-detail__cart").removeClass("hidden");
        } else {
          $btn.closest(".js-btn-state-wrapper").addClass("hidden");
          $btn.closest(".catalog-detail__cart").addClass("hidden");
        }

        $btn.html(this.obOffers[index]["BASKET_HTML"]);

        if ($btn.closest(".catalog-detail__cell-block")) {
          if ($btn.closest(".catalog-detail__cell-block").find(">*:not(.hidden)").length) {
            $btn.closest(".catalog-detail__cell-block").closest(".grid-list__item").removeClass("hidden");
          } else {
            $btn.closest(".catalog-detail__cell-block").closest(".grid-list__item").addClass("hidden");
          }
        }
      }
    },
    UpdateOneClickBuy: function (index, selector, wrapper) {
      const $btn = wrapper.find(selector);
      if ($btn.length && this.CheckWrapper($btn, wrapper)) {
        $btn[0].outerHTML = this.obOffers[index]["ONE_CLICK_BUY_HTML"];
      }
    },
    UpdateProps: function (index, wrapper) {
      const $props = wrapper.find(".js-offers-prop:first");
      if ($props.length && this.CheckWrapper($props, wrapper)) {
        wrapper.find(".js-prop").remove();
        if (this.obOffers[index]["OFFER_PROP"]) {
          const maxVisibleProps = $props.data("visible_prop_count") || Infinity;
          let propIterator = wrapper.find(".js-prop-replace").length;
          if (!Object.keys(this.obOffers[index]["OFFER_PROP"]).length) {
            this.checkPropsBlockVisibility(wrapper[0], propIterator, maxVisibleProps);
            return;
          }

          if (!window["propTemplate"]) {
            window["propTemplate"] = wrapper
              .closest(".catalog-items, .container, .fast_view_frame")
              .find(".props-template")
              .html();
            if (!window["propTemplate"]) {
              console.error("Unable to find property <template> tag");
              return;
            }
          }

          let html = "";
          for (let key in this.obOffers[index]["OFFER_PROP"]) {
            if (propIterator++ >= maxVisibleProps) continue;

            let title = this.obOffers[index]["OFFER_PROP"][key]["NAME"];
            let value = this.obOffers[index]["OFFER_PROP"][key]["DISPLAY_VALUE"];

            if (this.obOffers[index]["OFFER_PROP"][key]["HINT"]) {
              title +=
                '<div class="hint hint--down">' +
                '<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>' +
                '<div class="tooltip">' +
                this.obOffers[index]["OFFER_PROP"][key]["HINT"] +
                "</div>" +
                "</div>";
            }

            let str = window["propTemplate"].replace("#PROP_TITLE#", title).replace("#PROP_VALUE#", value);

            html += str;
          }

          this.checkPropsBlockVisibility(wrapper[0], propIterator, maxVisibleProps);
          if (html) {
            $props[0].insertAdjacentHTML("beforeend", html);
          }
        }
      }
    },
    UpdateGroupPropsSKU(index, wrapper) {
      let container = wrapper.closest(".product-container");
      const $props = container.find(".js-offers-group-wrap:first");
      if ($props.length) {
        container.find(".js-offers-group").remove();
        container.find(".js-offers-group__item").remove();
        if (this.obOffers[index]["OFFER_PROP"] && this.obOffers[index]["PROPS_GROUP_HTML"]) {
          if (!Object.keys(this.obOffers[index]["OFFER_PROP"]).length) {
            return;
          }

          let tmpDiv = document.createElement("div");
          tmpDiv.innerHTML = this.obOffers[index]["PROPS_GROUP_HTML"];
          let offerPropGroups = tmpDiv.querySelectorAll(".js-offers-group");
          if (offerPropGroups.length) {
            let groupCode, elementPropGroup;
            for (let keyGroup = 0; keyGroup < offerPropGroups.length; keyGroup++) {
              groupCode = offerPropGroups[keyGroup].getAttribute("data-group-code");
              elementPropGroup = container.find("[data-group-code='" + groupCode + "'] .js-offers-group__items-wrap");
              if (elementPropGroup.length) {
                elementPropGroup.append(offerPropGroups[keyGroup].querySelectorAll(".js-offers-group__item"));
              } else {
                let noGroupContainer = container.find("[data-group-code='no-group']");
                if (noGroupContainer.length) {
                  noGroupContainer.before(offerPropGroups[keyGroup]);
                } else {
                  $props.append(offerPropGroups[keyGroup]);
                }
              }
            }
          }
        }
      }
    },
    UpdateImages: function (index, wrapper) {
      if (wrapper.find(".js-detail-img").length) {
        this.UpdateDetailImages(index, wrapper);
      } else {
        this.UpdateListImages(index, wrapper);
      }
    },
    UpdateListImages: function (index, wrapper) {
      let $img = wrapper.find(".js-replace-img");
      let $gallery = wrapper.find(".js-image-block");
      if ($gallery.length && this.CheckWrapper($gallery, wrapper) && this.obOffers[index]["GALLERY_HTML"]) {
        let sticker = $gallery.find(".sticker");
        if (sticker.length) {
          sticker.appendTo(wrapper.find(".js-config-img"));
        }
        $gallery.html($(this.obOffers[index]["GALLERY_HTML"]).find(".js-image-block").html());
        $gallery.prepend(sticker);
      } else if ($img.length && this.CheckWrapper($img, wrapper)) {
        let src = $img.data("js") ? $img.data("js") : $img.attr("src");
        if (this.obOffers[index]["PICTURE_SRC"]) {
          src = this.obOffers[index]["PICTURE_SRC"];
        }
        $img.prop("src", src);

        //fast_view
        if (this.obOffers[index]["FAST_VIEW_HTML"]) {
          wrapper.find(".btn-fast-view").html(this.obOffers[index]["FAST_VIEW_HTML"]);
        }
      }
    },
    UpdateDetailImages: function (index, wrapper) {
      let $gallery = wrapper.find(".js-detail-img");
      let $galleryThumb = wrapper.find(".js-detail-img-thumb");
      if ($gallery.length && this.CheckWrapper($gallery, wrapper) && this.obOffers[index]["GALLERY"]) {
        const mainSlider = $gallery.data("swiper");
        const countPhoto = this.obOffers[index]["GALLERY"].length;
        const mainSlideClassList = $gallery.data("slideClassList");
        const slideHtml = [];
        const thumbsSlider = $galleryThumb.data("swiper");
        const thumbsSlideClassList = $galleryThumb.data("slideClassList");
        const slideThmbHtml = [];

        if (countPhoto > 0) {
          for (let i in this.obOffers[index]["GALLERY"]) {
            const image = this.obOffers[index]["GALLERY"][i];
            const title = image["TITLE"] ? image["TITLE"] : this.obOffers[index]["NAME"];
            const alt = image["ALT"] ? image["ALT"] : this.obOffers[index]["NAME"];
            if (typeof image === "object") {
              slideHtml.push(
                '<div id="photo-' +
                  i +
                  '" class="' +
                  mainSlideClassList +
                  '">' +
                  '<a href="' +
                  image["SRC"] +
                  '" data-fancybox="gallery" class="catalog-detail__gallery__link popup_link fancy fancy-thumbs" title="' +
                  title +
                  '">' +
                  '<img class="catalog-detail__gallery__picture" src="' +
                  image["SRC"] +
                  '" alt="' +
                  alt +
                  '" title="' +
                  title +
                  '" />' +
                  "</a>" +
                  "</div>"
              );
            }
          }

          if ($galleryThumb.length) {
            if (countPhoto > 1) {
              for (let i in this.obOffers[index]["GALLERY"]) {
                const image = this.obOffers[index]["GALLERY"][i];
                const title = image["TITLE"] ? image["TITLE"] : this.obOffers[index]["NAME"];
                const alt = image["ALT"] ? image["ALT"] : this.obOffers[index]["NAME"];

                if (typeof image === "object") {
                  slideThmbHtml.push(
                    '<div id="thumb-photo-' +
                      i +
                      '" class="' +
                      (i == 0 ? "swiper-slide-thumb-active " : "") +
                      "" +
                      thumbsSlideClassList +
                      '">' +
                      '<img class="gallery__picture rounded-x" src="' +
                      image["SRC"] +
                      '" alt="' +
                      alt +
                      '" title="' +
                      title +
                      '" />' +
                      "</div>"
                  );
                }
              }
            }
            $galleryThumb.attr("data-size", countPhoto);
          }

          wrapper.find('[itemprop="image"]').attr("href", this.obOffers[index]["GALLERY"][0]["SRC"]);
        } else {
          slideHtml.push(
            '<div class="detail-gallery-big__item detail-gallery-big__item--big detail-gallery-big__item--no-image swiper-slide">' +
              '<span data-fancybox="gallery" class="detail-gallery-big__link" >' +
              '<img class="catalog-detail__gallery__picture" src="' +
              arAsproOptions["SITE_TEMPLATE_PATH"] +
              "/images/svg/noimage_product.svg" +
              '" />' +
              "</span>" +
              "</div>"
          );
        }

        if (mainSlider !== undefined) {
          mainSlider.removeAllSlides();
          mainSlider.appendSlide(slideHtml);
          mainSlider.update();
        }

        if (thumbsSlider !== undefined) {
          thumbsSlider.removeAllSlides();
          thumbsSlider.appendSlide(slideThmbHtml);
          thumbsSlider.update();
        }

        InitFancyBox();
      }
    },
    UpdateSideIcons: function (index, wrapper) {
      let $icons = wrapper.find(".js-replace-icons:first");
      if ($icons.length && this.CheckWrapper($icons, wrapper) && this.obOffers[index]["ICONS_HTML"]) {
        $icons.html($(this.obOffers[index]["ICONS_HTML"]));
      }
    },
    UpdateRow: function (intNumber, activeID, showID, canBuyID) {
      var i = 0,
        showI = 0,
        countShow = 0,
        obData = {},
        obDataCont = {},
        isCurrent = false,
        selectIndex = 0,
        RowItems = null,
        $titleNode;

      if (-1 < intNumber && intNumber < $(this.options.containerClass + " .sku-props__inner").length) {
        const activeClass = "sku-props__value--active";
        const missingClass = "sku-props__value--missing";

        RowItems = BX.findChildren(
          $(this.options.containerClass + " .sku-props__inner:eq(" + intNumber + ") .sku-props__values")[0],
          { tag: "div" },
          false
        );

        if (!!RowItems && 0 < RowItems.length) {
          countShow = showID.length;
          obData = {
            style: {},
            props: {
              disabled: "",
              selected: "",
            },
          };
          obDataCont = {
            style: {},
          };

          $titleNode = $(this.options.containerClass + " .sku-props__inner:eq(" + intNumber + ") .sku-props__js-size");

          for (i = 0; i < RowItems.length; i++) {
            let $item = RowItems[i].querySelector(".sku-props__value");
            $item.classList.remove(activeClass);

            let value = $item.getAttribute("data-onevalue");
            let title = $item.getAttribute("data-title");

            isCurrent = value === activeID;

            if (isCurrent) {
              $item.classList.add(activeClass);
              $titleNode.text(title);
            }

            $item.classList.toggle(missingClass, !BX.util.in_array(value, canBuyID));

            obData.style.display = "none";

            if (BX.util.in_array(value, showID)) {
              obData.style.display = "";

              if (isCurrent) {
                selectIndex = showI;
              }

              if (value != 0) showI++;
            }

            BX.adjust(RowItems[i], obData);
          }
          // activeID is string, and can be 0 or empty
          if (!showI) {
            obDataCont.style.display = "none";
          } else {
            obDataCont.style.display = "";
          }
          BX.adjust($(this.options.containerClass + " .sku-props__inner:eq(" + intNumber + ")")[0], obDataCont);
        }
      }
    },

    CheckWrapper: function (replaceBlock, wrapper) {
      return replaceBlock.closest(".js-popup-block")[0] === wrapper[0];
    },

    UpdateSKUInfoByProps: function (offersTree) {
      let arCanBuyValues,
        arShowValues = false;

      (strName = ""), (arFilter = {});
      for (i = 0; i < this.options.depth; i++) {
        strName = "PROP_" + $(this.options.containerClass + " .sku-props__inner:eq(" + i + ")").data("id");
        if (this.options.selectedValues[strName]) {
          arFilter[strName] = this.options.selectedValues[strName].toString();
        }
      }
      strName =
        "PROP_" + $(this.options.containerClass + " .sku-props__inner:eq(" + this.options.depth + ")").data("id");

      arShowValues = this.GetRowValues(arFilter, strName, offersTree);

      if (arShowValues && BX.util.in_array(this.options.strPropValue, arShowValues)) {
        if ($(this.options.containerClass).data("selected")) {
          this.options.selectedValues = $(this.options.containerClass).data("selected");
        }

        arFilter[strName] = this.options.strPropValue.toString();

        for (i = ++this.options.depth; i < $(this.options.containerClass + " .sku-props__inner").length; i++) {
          strName = "PROP_" + $(this.options.containerClass + " .sku-props__inner:eq(" + i + ")").data("id");
          arShowValues = this.GetRowValues(arFilter, strName, offersTree);

          if (!arShowValues) {
            break;
          }

          allValues = [];
          if (this.options.showAbsent) {
            arCanBuyValues = [];
            tmpFilter = [];
            tmpFilter = arFilter;

            for (j = 0; j < arShowValues.length; j++) {
              tmpFilter[strName] = arShowValues[j];
              allValues[allValues.length] = arShowValues[j];
              if (this.GetCanBuy(tmpFilter, offersTree)) {
                arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
              }
            }
          } else {
            arCanBuyValues = arShowValues;
          }

          if (
            this.options.selectedValues[strName] &&
            BX.util.in_array(this.options.selectedValues[strName], arCanBuyValues)
          ) {
            arFilter[strName] = this.options.selectedValues[strName].toString();
          } else {
            if (this.options.showAbsent) {
              arFilter[strName] = arCanBuyValues.length ? arCanBuyValues[0] : allValues[0];
            } else {
              arFilter[strName] = arCanBuyValues[0];
            }
          }

          this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
        }
        $(this.options.containerClass).data("selected", arFilter);
      }
    },

    UpdateSKUBlocks: function (index) {
      const blockID = this.obOffers[index]["ID"];
      const $blocks = document.querySelectorAll("[data-sku_block_id]");

      if ($blocks.length) {
        for (let i = 0; i < $blocks.length; i++) {
          $blocks[i].dataset.sku_block_id == blockID
            ? $blocks[i].classList.remove("hidden")
            : $blocks[i].classList.add("hidden");
        }
      }
    },

    checkPropsBlockVisibility: function (wrapper, totalProps, maxVisibleProps) {
      const nodePropsContainer = wrapper.querySelector(".properties__container");
      if (nodePropsContainer) {
        nodePropsContainer.classList.toggle("hidden", !totalProps);
      }

      const isNeedShowMore = totalProps > maxVisibleProps;
      const nodeShowMore = wrapper.querySelector(".properties__show-more");
      if (nodeShowMore) {
        nodeShowMore.classList.toggle("hidden", !isNeedShowMore);

        const nodePropsTab = wrapper.querySelector('.tabs-history a[href="#char"]');
        if (nodePropsTab) {
          const nodePropsTabContent = document.getElementById("char");
          nodePropsTab.parentElement.classList.toggle("hidden", !isNeedShowMore);
          nodePropsTabContent.classList.toggle("hidden", !isNeedShowMore);

          if (!isNeedShowMore) {
            nodePropsTab.parentElement.classList.remove("active");
            nodePropsTabContent.classList.remove("active");

            const nodePropsTabFirstAvailable = wrapper.querySelector(".tabs-history li:not(.hidden) a");
            if (nodePropsTabFirstAvailable && window.location.hash === "#char") {
              nodePropsTabFirstAvailable.click();
            }
          }

          if (typeof ResizeScrollTabs === "function") {
            ResizeScrollTabs();
          }
        }

        const nodePropsBlock = wrapper.querySelector(".detail-block.char");
        if (nodePropsBlock) {
          /* Не скрывать блок, если в нём форма фильтра «Подобрать похожие» */
          const hasAnalogFilter = nodePropsBlock.querySelector(".analog-filter-form");
          if (!hasAnalogFilter) {
            nodePropsBlock.classList.toggle("hidden", !isNeedShowMore);
          } else {
            nodePropsBlock.classList.remove("hidden");
          }
        }
      }
    },
  };
}
