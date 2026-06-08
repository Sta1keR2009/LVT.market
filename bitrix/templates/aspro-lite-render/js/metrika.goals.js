function checkCounters(name) {
  if (typeof name !== "undefined") {
    if (
      name == "google" &&
      arAsproOptions['COUNTERS']["GOOGLE_ECOMERCE"] == "Y" &&
      arAsproOptions['COUNTERS']["GOOGLE_COUNTER"] > 0
    ) {
      return true;
    } else if (
      name == "yandex" &&
      arAsproOptions['COUNTERS']["YANDEX_ECOMERCE"] == "Y" &&
      arAsproOptions['COUNTERS']["YANDEX_COUNTER"] > 0
    ) {
      return true;
    } else {
      return false;
    }
  } else if (
    (arAsproOptions['COUNTERS']["YANDEX_ECOMERCE"] == "Y" && arAsproOptions['COUNTERS']["YANDEX_COUNTER"] > 0) ||
    (arAsproOptions['COUNTERS']["GOOGLE_ECOMERCE"] == "Y" && arAsproOptions['COUNTERS']["GOOGLE_COUNTER"] > 0)
  ) {
    return true;
  } else {
    return false;
  }
}

function waitLayer(delay, callback) {
  if (typeof dataLayer !== "undefined" && typeof callback === "function") {
    callback();
  } else {
    setTimeout(function () {
      waitLayer(delay, callback);
    }, delay);
  }
}

function addBasketCounter(id, callback) {
  if (checkCounters()) {
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
      dataType: "json",
      type: "POST",
      data: { ID: id },
      success: function (item) {
        if (!!item && !!item.ID) {
          let ecommerce = {
            items: [
              {
                item_name: item.NAME, // Name or ID is required.
                item_id: item.ID,
                price: parseFloat(item.PRICE),
                item_brand: item.BRAND,
                item_category: item.CATEGORY,
                item_list_name: "List Results",
                item_list_id: item.IBLOCK_SECTION_ID,
                affiliation: item.SHOP_NAME,
                index: 1,
                quantity: parseFloat(item.QUANTITY),
              },
            ],
          };
          if (arAsproOptions['COUNTERS']["GA_VERSION"] === "v3") {
            ecommerce = {
              currencyCode: item.CURRENCY,
              add: {
                products: [
                  {
                    id: item.ID,
                    name: item.NAME,
                    price: parseFloat(item.PRICE),
                    brand: item.BRAND,
                    category: item.CATEGORY,
                    quantity: parseFloat(item.QUANTITY),
                  },
                ],
              },
            };
          }
          waitLayer(100, function () {
            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
            dataLayer.push({
              // event: 'add_to_cart',
              event: arAsproOptions['COUNTERS']["GOOGLE_EVENTS"]["ADD2BASKET"],
              currency: item.CURRENCY,
              value: parseFloat(item.PRICE),
              ecommerce: ecommerce,
            });
            if (typeof callback == "function") {
              callback();
            }
          });
        }
      },
    });
  }
}

function delFromBasketCounter(id, callback) {
  if (checkCounters()) {
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
      dataType: "json",
      type: "POST",
      data: { ID: id },
      success: function (item) {
        if (item.ID) {
          let ecommerce = {
            items: [
              {
                item_name: item.NAME, // Name or ID is required.
                item_id: item.ID,
                price: parseFloat(item.PRICE),
                item_brand: item.BRAND,
                item_category: item.CATEGORY,
                affiliation: item.SHOP_NAME,
                item_list_name: "List Results",
              },
            ],
          };
          if (arAsproOptions['COUNTERS']["GA_VERSION"] === "v3") {
            ecommerce = {
              remove: {
                products: [
                  {
                    id: item.ID,
                    name: item.NAME,
                    category: item.CATEGORY,
                  },
                ],
              },
            };
          }
          waitLayer(100, function () {
            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
            dataLayer.push({
              event: arAsproOptions['COUNTERS']["GOOGLE_EVENTS"]["REMOVE_BASKET"],
              currency: item.CURRENCY,
              value: parseFloat(item.PRICE),
              ecommerce: ecommerce,
            });
            if (typeof callback == "function") {
              callback();
            }
          });
        }
      },
    });
  }
}

function viewItemCounter(id, price_id) {
  if (checkCounters()) {
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
      dataType: "json",
      type: "POST",
      data: { PRODUCT_ID: id, PRICE_ID: price_id },
      success: function (item) {
        if (item.ID) {
          let ecommerce = {
            items: [
              {
                item_name: item.NAME, // Name or ID is required.
                item_id: item.ID,
                price: parseFloat(item.PRICE),
                item_brand: item.BRAND,
                item_category: item.CATEGORY,
                item_list_name: "List Results",
                item_list_id: item.IBLOCK_SECTION_ID,
                affiliation: item.SHOP_NAME,
                index: 1,
                quantity: parseFloat(item.QUANTITY),
              },
            ],
          };
          if (arAsproOptions['COUNTERS']["GA_VERSION"] === "v3") {
            ecommerce = {
              detail: {
                products: [
                  {
                    id: item.ID,
                    name: item.NAME,
                    price: parseFloat(item.PRICE),
                    brand: item.BRAND,
                    category: item.CATEGORY,
                  },
                ],
              },
            };
          }
          waitLayer(100, function () {
            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
            dataLayer.push({
              event: "view_item",
              currency: item.CURRENCY,
              value: parseFloat(item.PRICE),
              ecommerce: ecommerce,
            });
          });
        }
      },
    });
  }
}

function purchaseCounter(order_id, type, callback) {
  if (checkCounters()) {
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
      dataType: "json",
      type: "POST",
      data: { ORDER_ID: order_id, TYPE: type },
      success: function (order) {
        var products = [];
        const items = [];
        if (order.ITEMS) {
          for (var i in order.ITEMS) {
            products.push({
              id: order.ITEMS[i].ID,
              sku: order.ITEMS[i].ID,
              name: order.ITEMS[i].NAME,
              price: order.ITEMS[i].PRICE,
              brand: order.ITEMS[i].BRAND,
              category: order.ITEMS[i].CATEGORY,
              quantity: order.ITEMS[i].QUANTITY,
            });
            items.push({
              item_id: order.ITEMS[i].ID,
              item_name: order.ITEMS[i].NAME,
              price: parseFloat(order.ITEMS[i].PRICE),
              item_brand: order.ITEMS[i].BRAND,
              item_category: order.ITEMS[i].CATEGORY,
              affiliation: order.SHOP_NAME,
              quantity: parseFloat(order.ITEMS[i].QUANTITY),
            });
          }
        }
        if (order.ID) {
          let ecommerce = {
            transaction_id: order.ACCOUNT_NUMBER,
            affiliation: order.SHOP_NAME,
            value: order.PRICE,
            tax: order.TAX_VALUE,
            shipping: order.PRICE_DELIVERY,
            currency: order.CURRENCY,
            items: items,
          };
          if (arAsproOptions['COUNTERS']["GA_VERSION"] === "v3") {
            ecommerce = {
              purchase: {
                actionField: {
                  id: order.ACCOUNT_NUMBER,
                  shipping: order.PRICE_DELIVERY,
                  tax: order.TAX_VALUE,
                  list: type,
                  revenue: order.PRICE,
                },
                products: products,
              },
            };
          }
          waitLayer(100, function () {
            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
            dataLayer.push({
              event: arAsproOptions['COUNTERS']["GOOGLE_EVENTS"]["PURCHASE"],
              ecommerce: ecommerce,
            });

            if (typeof callback !== "undefined") {
              callback(ecommerce);
            }
          });
        } else {
          if (typeof callback !== "undefined") {
            callback();
          }
        }
      },
      error: function () {
        if (typeof callback !== "undefined") {
          callback();
        }
      },
    });
  }
}

function checkoutCounter(step, option, callback) {
  if (checkCounters("google")) {
    $.ajax({
      url: arAsproOptions["SITE_DIR"] + "ajax/goals.php",
      dataType: "json",
      type: "POST",
      data: { BASKET: "Y" },
      success: function (basket) {
        var products = [];
        const items = [];
        let summ = 0;
        let currency = "RUB";
        if (basket.ITEMS) {
          for (var i in basket.ITEMS) {
            products.push({
              id: basket.ITEMS[i].ID,
              name: basket.ITEMS[i].NAME,
              price: basket.ITEMS[i].PRICE,
              brand: basket.ITEMS[i].BRAND,
              category: basket.ITEMS[i].CATEGORY,
              quantity: basket.ITEMS[i].QUANTITY,
            });
            items.push({
              item_id: basket.ITEMS[i].ID,
              item_name: basket.ITEMS[i].NAME,
              price: parseFloat(basket.ITEMS[i].PRICE),
              item_brand: basket.ITEMS[i].BRAND,
              item_category: basket.ITEMS[i].CATEGORY,
              affiliation: basket.SHOP_NAME,
              quantity: parseFloat(basket.ITEMS[i].QUANTITY),
            });
            summ += basket.ITEMS[i].PRICE;
            currency = basket.ITEMS[i].CURRENCY;
          }
        }
        if (products) {
          let ecommerce = {
            items: items,
          };
          if (arAsproOptions['COUNTERS']["GA_VERSION"] === "v3") {
            ecommerce = {
              checkout: {
                actionField: {
                  step: step,
                  option: option,
                },
                products: products,
              },
            };
          }
          waitLayer(100, function () {
            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
            dataLayer.push({
              event: arAsproOptions['COUNTERS']["GOOGLE_EVENTS"]["CHECKOUT_ORDER"],
              currency: currency,
              value: parseFloat(summ),
              ecommerce: ecommerce,
              /*"eventCallback": function() {
                  if((typeof callback !== 'undefined') && (typeof callback === 'function')){
                    callback();
                  }
               }*/
            });
            if (typeof callback !== "undefined" && typeof callback === "function") {
              callback();
            }
          });
        }
      },
    });
  }
}

function waitCounter(idCounter, delay, callback) {
  var obCounter = window['yaCounter' + idCounter];
  if (typeof obCounter === 'object') {
    if (typeof callback === 'function') {
      callback();
    }
  } else {
    setTimeout(function () {
      waitCounter(idCounter, delay, callback);
    }, delay);
  }
};

BX.addCustomEvent('onCounterGoals', function (eventdata) {
  if (typeof eventdata !== 'object') {
    eventdata = {
      goal: 'undefined',
    };
  }

  if (typeof eventdata.goal !== 'string') {
    eventdata.goal = 'undefined';
  }
  
  if (eventdata.goal === 'goal_basket_add') {
    if (
      typeof eventdata.params === 'object' &&
      eventdata.params &&
      eventdata.params.id
    ) {
      addBasketCounter(eventdata.params.id);
    }
  } else if (eventdata.goal === 'goal_basket_remove') {
    if (
      typeof eventdata.params === 'object' &&
      eventdata.params &&
      eventdata.params.id
    ) {
      delFromBasketCounter(eventdata.params.id);
    }
  }

  if (
    arAsproOptions['THEME']['YA_GOALS'] === 'Y' &&
    arAsproOptions['THEME']['YA_COUNTER_ID']
  ) {
    var idCounter = arAsproOptions['THEME']['YA_COUNTER_ID'];
    idCounter = parseInt(idCounter);

    if (idCounter) {
      try {
        waitCounter(idCounter, 50, function () {
          var obCounter = window['yaCounter' + idCounter];
          if (typeof obCounter === 'object') {
            obCounter.reachGoal(eventdata.goal);
          }
        });
      } catch (e) {
        console.error(e);
      }
    } else {
      console.info('Bad counter id!', idCounter);
    }
  }
});

readyDOM(function(){
  if (arAsproOptions['THEME']['USE_DEBUG_GOALS'] === 'Y') {
    $.cookie('_ym_debug', '1');
  } else {
    $.cookie('_ym_debug', null);
  }
});