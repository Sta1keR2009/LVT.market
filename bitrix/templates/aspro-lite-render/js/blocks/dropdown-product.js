/*hover top products*/
$(document).on("mouseenter", ".header-favorite, .header-compare", function () {
  if (window.matchMedia("(min-width: 992px)").matches) {
    let _this = $(this);
    let hover_block = _this.find(".product-dropdown-hover-block");
    let type = _this.hasClass('header-compare') ? 'compare' : 'favorite';
    let obParams = {
      'type': type
    }
    if (!hover_block.hasClass("loaded")) {
      BX.ajax.runAction('aspro:lite.DropdownProducts.show', {
        data: {
          params: obParams
        },
      }).then(
        response => {
          let dropdown = $(`.${type}-dropdown`);
          dropdown.addClass('loaded');
          dropdown.html(response.data.html);
        },
        response => {
          console.log('error');
        }
      );
    }
  }
});

$(document).on("click", ".product-dropdown-hover-block .dropdown-product-action.remove", function () {
  let _this = $(this);
  let itemAction = JItemAction.factory(this);
  itemAction.state = false;

  _this.closest(".dropdown-product__item").fadeOut(400, () => { 
    let parentWrap = _this.closest(".product-dropdown-hover-block");
    let visibleItems = parentWrap.find(".dropdown-product__item:visible");
    if (!visibleItems.length) { 
      parentWrap.html('');
    }
  });

  itemAction.updateState();
});
/**/