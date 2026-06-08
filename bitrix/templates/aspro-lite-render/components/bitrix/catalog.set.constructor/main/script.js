BX.namespace("BX.Catalog.SetConstructor");

BX.Catalog.SetConstructor = (function () {
  var SetConstructor = function (params) {
    this.numSliderItems = params.numSliderItems || 0;
    this.numSetItems = params.numSetItems || 0;
    this.jsId = params.jsId || "";
    this.ajaxPath = params.ajaxPath || "";
    this.currency = params.currency || "";
    this.lid = params.lid || "";
    this.iblockId = params.iblockId || "";
    this.basketUrl = params.basketUrl || "";
    this.setIds = params.setIds || null;
    this.offersCartProps = params.offersCartProps || null;
    this.itemsRatio = params.itemsRatio || null;
    this.noFotoSrc = params.noFotoSrc || "";
    this.messages = params.messages;

    this.canBuy = params.canBuy;
    this.mainElementPrice = params.mainElementPrice || 0;
    this.mainElementOldPrice = params.mainElementOldPrice || 0;
    this.mainElementDiffPrice = params.mainElementDiffPrice || 0;
    this.mainElementBasketQuantity = params.mainElementBasketQuantity || 1;

    this.parentCont = BX(params.parentContId) || null;
    this.sliderParentCont = this.parentCont.querySelector("[data-role='slider-parent-container']");
    this.setItemsCont = this.parentCont.querySelector("[data-role='set-items']");

    this.setPriceCont = this.parentCont.querySelector("[data-role='set-price']");
    this.setOldPriceCont = this.parentCont.querySelector("[data-role='set-old-price']");

    BX.bindDelegate(this.setItemsCont, "click", { "data-role" : "set-delete-btn" }, BX.proxy(this.deleteFromSet, this));
    BX.bindDelegate(this.setItemsCont, "click", { "data-role" : "set-add-btn" }, BX.proxy(this.addToSet, this));

    const buyButton = this.parentCont.querySelector("[data-role='set-buy-btn']");

    if (this.canBuy) {
      BX.show(buyButton);
      BX.bind(buyButton, "click", BX.proxy(this.addToBasket, this));
    } else {
      BX.hide(buyButton);
    }
  };
  
  // delete item
  SetConstructor.prototype.deleteFromSet = function () {
    const $target = BX.proxy_context.closest('[data-role="set-delete-btn"]');

    if ($target) {
      const $item = $target.closest('.set-constructor__item');
      const $itemButtonContainer = $item.querySelector('.bx-added-item-table-cell-action');
      const itemId = $item.getAttribute("data-id");
      const $newActionNode = BX.create('div', {
        attrs: {
          'data-role': 'set-add-btn',
          className: 'pointer',
        },
        html: '<i class="svg inline fill-dark-light" aria-hidden="true"><svg width="13" height="13"><use xlink:href="/bitrix/templates/aspro-lite/images/svg/catalog/item_action_icons.svg#plus-12-12"></use></svg></i>'
      });

      this.numSliderItems++;
      this.numSetItems--;
      BX.cleanNode($itemButtonContainer);
      BX.adjust($itemButtonContainer, {
        children: [$newActionNode]
      });
      $item.dataset.active = false;

      for (let i = 0, l = this.setIds.length; i < l; i++) {
        if (this.setIds[i] == itemId) this.setIds.splice(i, 1);
      }

      this.recountPrice();

      if (this.numSliderItems > 0 && this.sliderParentCont) {
        this.sliderParentCont.style.display = "";
      }
    }
  };

  // add item
  SetConstructor.prototype.addToSet = function () {
    const $target = BX.proxy_context.closest('[data-role="set-add-btn"]');

    if ($target) {
      const $item = $target.closest('.set-constructor__item');
      const $itemButtonContainer = $item.querySelector('.bx-added-item-table-cell-action');
      const itemId = $item.getAttribute("data-id");
      const $newActionNode = BX.create('div', {
        attrs: {
          'data-role': 'set-delete-btn',
          className: 'pointer',
        },
        html: '<i class="svg inline fill-use-999" aria-hidden="true"><svg width="13" height="13"><use xlink:href="/bitrix/templates/aspro-lite/images/svg/header_icons.svg#close-16-16"></use></svg></i>'
      });

      this.numSliderItems--;
      this.numSetItems++;
      BX.cleanNode($itemButtonContainer);
      BX.adjust($itemButtonContainer, {
        children: [$newActionNode]
      });
      $item.dataset.active = true;
      this.setIds.push(itemId);
      this.recountPrice();

      if (this.numSliderItems <= 0 && this.sliderParentCont) {
        this.sliderParentCont.style.display = "none";
      }
    }
  };

  SetConstructor.prototype.recountPrice = function () {
    const setItems = this.setItemsCont.querySelectorAll('.set-constructor__item[data-active="true"]');
    let sumPrice = this.mainElementPrice * this.mainElementBasketQuantity,
        sumOldPrice = this.mainElementOldPrice * this.mainElementBasketQuantity,
        sumDiffDiscountPrice = this.mainElementDiffPrice * this.mainElementBasketQuantity;
        

    if (setItems) {
      for (let i = 0, l = setItems.length; i < l; i++) {
        const ratio = Number(setItems[i].getAttribute("data-quantity")) || 1;
        
        sumPrice += Number(setItems[i].getAttribute("data-price")) * ratio;
        sumOldPrice += Number(setItems[i].getAttribute("data-old-price")) * ratio;
        sumDiffDiscountPrice += Number(setItems[i].getAttribute("data-diff-price")) * ratio;
      }
    }

    this.setPriceCont.innerHTML = BX.Currency.currencyFormat(sumPrice, this.currency, true);
    
    if (Math.floor(sumDiffDiscountPrice * 100) > 0) {
      this.setOldPriceCont.innerHTML = BX.Currency.currencyFormat(sumOldPrice, this.currency, true);
      BX.removeClass(this.setOldPriceCont, 'hidden');
    } else {
      this.setOldPriceCont.innerHTML = "";
      BX.addClass(this.setOldPriceCont, 'hidden');
    }
  };

  SetConstructor.prototype.addToBasket = function () {
    const target = BX.proxy_context;

    BX.showWait(target.parentNode);

    BX.ajax.post(
      this.ajaxPath,
      {
        sessid: BX.bitrix_sessid(),
        action: "catalogSetAdd2Basket",
        set_ids: this.setIds,
        lid: this.lid,
        iblockId: this.iblockId,
        setOffersCartProps: this.offersCartProps,
        itemsRatio: this.itemsRatio,
      },
      BX.proxy(function (result) {
        BX.closeWait();
        document.location.href = this.basketUrl;
      }, this)
    );
  };

  return SetConstructor;
})();
