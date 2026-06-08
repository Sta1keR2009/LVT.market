(function () {
  'use strict';

  var WIDGET_HOST_ID = 'lvt-catapulto-pvz-widget';
  var V3_CONTAINER_ID = 'catapulto-widget';

  var OPERATOR_NAMES = {
    cdek: 'СДЭК',
    cse: 'КСЭ',
    dostavista: 'Dostavista',
    yandex_dostavista: 'Яндекс Доставка',
    yandex_dostavka: 'Яндекс Доставка',
    dpd: 'DPD',
    boxberry: 'Boxberry',
    pochta: 'Почта России',
    pek: 'ПЭК',
    dl: 'Деловые Линии',
    ems: 'EMS',
    iml: 'IML',
    hermes: 'Hermes',
  };

  var widgetController = null;
  var widgetLoading = false;
  var pendingOnSelected = null;
  var loadedScriptUrl = '';
  var loadedEngine = '';

  window.__lvtCatapultoPvzOnSelect = null;
  window.__lvtCatapultoPvzOnClose = null;

  function siteDir() {
    if (typeof arLiteOptions !== 'undefined' && arLiteOptions.SITE_DIR) {
      return String(arLiteOptions.SITE_DIR).replace(/\/?$/, '/');
    }
    if (typeof arAsproOptions !== 'undefined' && arAsproOptions.SITE_DIR) {
      return String(arAsproOptions.SITE_DIR).replace(/\/?$/, '/');
    }
    return '/';
  }

  function ensureWidgetHost() {
    var node = document.getElementById(WIDGET_HOST_ID);
    if (!node) {
      node = document.createElement('div');
      node.id = WIDGET_HOST_ID;
      node.className = 'lvt-catapulto-pvz-widget-host';
      document.body.appendChild(node);
    }
    return node;
  }

  function resetWidgetGlobals() {
    loadedScriptUrl = '';
    loadedEngine = '';
    widgetController = null;

    if (window.__catapultoWidgetMounted) {
      window.__catapultoWidgetMounted = false;
    }

    var v3Node = document.getElementById(V3_CONTAINER_ID);
    if (v3Node && v3Node.parentNode) {
      window.__catapultoWidgetDestroyed = v3Node;
      v3Node.parentNode.removeChild(v3Node);
    }
  }

  function loadScript(url, isModule, engine) {
    return new Promise(function (resolve, reject) {
      if (!url) {
        reject(new Error('empty_script'));
        return;
      }

      if (loadedScriptUrl === url && loadedEngine === engine) {
        waitForWidgetClass(engine === 'legacy').then(resolve).catch(reject);
        return;
      }

      resetWidgetGlobals();

      var existing = document.querySelector('script[data-lvt-ctpt-widget="1"]');
      if (existing && existing.parentNode) {
        existing.parentNode.removeChild(existing);
      }

      if (isModule) {
        var absUrl = url.indexOf('http') === 0 ? url : (window.location.origin + (url.indexOf('/') === 0 ? url : '/' + url));
        var mod = document.createElement('script');
        mod.type = 'module';
        mod.setAttribute('data-lvt-ctpt-widget', '1');
        mod.textContent = 'import "' + absUrl.replace(/"/g, '') + '";';
        mod.onload = function () {
          loadedScriptUrl = url;
          loadedEngine = engine;
          waitForWidgetClass(false).then(resolve).catch(reject);
        };
        mod.onerror = reject;
        document.head.appendChild(mod);
        return;
      }

      var s = document.createElement('script');
      s.src = url;
      s.async = true;
      s.setAttribute('data-lvt-ctpt-widget', '1');
      s.onload = function () {
        loadedScriptUrl = url;
        loadedEngine = engine;
        waitForWidgetClass(engine === 'legacy').then(resolve).catch(reject);
      };
      s.onerror = reject;
      document.head.appendChild(s);
    });
  }

  function waitForWidgetClass(preferLegacy) {
    return new Promise(function (resolve, reject) {
      var tries = 0;
      (function tick() {
        var legacy = window.CatapultoWidget;
        var v3 = window.CatapultoWidget2;
        if ((preferLegacy && legacy && !v3) || (!preferLegacy && v3) || legacy || v3) {
          resolve();
          return;
        }
        tries += 1;
        if (tries > 120) {
          reject(new Error('widget_class_missing'));
          return;
        }
        setTimeout(tick, 50);
      })();
    });
  }

  function getWidgetClass(preferLegacy) {
    if (preferLegacy && window.CatapultoWidget && !window.CatapultoWidget2) {
      return window.CatapultoWidget;
    }
    return window.CatapultoWidget2 || window.CatapultoWidget || null;
  }

  function fetchWidgetConfig(city) {
    var url = siteDir() + 'local/api/catapulto_order_widget_config.php?city=' + encodeURIComponent(city);
    return fetch(url, { credentials: 'same-origin', cache: 'no-store' }).then(function (r) {
      return r.json();
    });
  }

  function unwrapItemData(item) {
    if (!item) return null;
    if (typeof item.getData === 'function') {
      return item.getData();
    }
    return item;
  }

  function normalizePvzSelection(item) {
    var data = unwrapItemData(item);
    if (!data) return null;

    var variant = data.variant || data.Variant || {};
    var terminal = data.Terminal || data.terminal || {};
    var operator = String(
      variant.operator || variant.operator_id || terminal.operator || ''
    ).trim();

    if (!operator) return null;

    var price = Math.round(Number(variant.price || variant.price_orig || 0));
    var rateId = String(variant.id || variant.rate_id || variant.key || variant.rate || '').trim();
    var rateKey = 'pvz-map-' + operator + '-' + (rateId || terminal.id || 'default');
    var address = String(terminal.address || '').trim();
    var code = String(terminal.code || '').trim();
    var pvzAddress = address + (code ? ' #' + code : '');

    var periodText = '';
    if (variant.delivery_day) {
      periodText = String(variant.delivery_day);
    } else if (variant.transit_days) {
      periodText = 'от ' + String(variant.transit_days) + ' раб. дней';
    } else if (variant.periodText) {
      periodText = String(variant.periodText);
    }

    return {
      rateKey: rateKey,
      operator: operator,
      operatorName: OPERATOR_NAMES[operator] || operator,
      rateName: String(variant.rate || variant.name || 'Доставка в ПВЗ'),
      shippingType: String(variant.shipping_type || variant.shippingType || 'w2w'),
      deliveryMode: 'pvz',
      price: price,
      priceFormatted: price > 0 ? price.toLocaleString('ru-RU') + ' ₽' : 'Бесплатно',
      periodText: periodText,
      pvzAddress: pvzAddress,
      pvzId: String(terminal.id || ''),
      pvzCode: code,
    };
  }

  function hideWidget() {
    document.body.style.overflow = '';
    document.body.classList.remove('lvt-catapulto-pvz-open');
    window.__lvtCatapultoPvzOnSelect = null;
    window.__lvtCatapultoPvzOnClose = null;

    if (widgetController && typeof widgetController.hide === 'function') {
      widgetController.hide();
    }

    var host = document.getElementById(WIDGET_HOST_ID);
    if (host) {
      host.style.display = 'none';
    }
  }

  function handlePvzSelected(item) {
    var rate = normalizePvzSelection(item);
    hideWidget();
    if (!rate) {
      console.warn('LVT Catapulto PVZ: unable to parse selected item', item);
      alert('Не удалось сохранить выбранный пункт выдачи. Попробуйте ещё раз.');
      return;
    }
    if (typeof pendingOnSelected === 'function') {
      pendingOnSelected(rate);
    }
  }

  function waitForLegacyReady(widget, timeoutMs) {
    return new Promise(function (resolve, reject) {
      var started = Date.now();
      (function tick() {
        try {
          var el = widget.getStructure().getContainer().getDomElement();
          if (el && el.querySelector('.ctpt-widget__search-wrap')) {
            resolve();
            return;
          }
        } catch (e) {
          /* widget still initializing */
        }
        if (Date.now() - started > (timeoutMs || 20000)) {
          reject(new Error('widget_ready_timeout'));
          return;
        }
        setTimeout(tick, 100);
      })();
    });
  }

  function buildWidgetParams(cfg, isLegacy) {
    if (isLegacy) {
      ensureWidgetHost().style.display = 'block';
    }

    window.__lvtCatapultoPvzOnSelect = function (item) {
      handlePvzSelected(item);
    };
    window.__lvtCatapultoPvzOnClose = function () {
      hideWidget();
    };

    var params = Object.assign({}, cfg.widgetParams, {
      link: isLegacy ? (cfg.widgetParams.link || WIDGET_HOST_ID) : V3_CONTAINER_ID,
      popup_mode: true,
      only_delivery_type: 'Pvz',
      onPopupClose: function () {
        if (typeof window.__lvtCatapultoPvzOnClose === 'function') {
          window.__lvtCatapultoPvzOnClose();
        }
      },
      onSelectPvzItem: function (item) {
        if (typeof window.__lvtCatapultoPvzOnSelect === 'function') {
          window.__lvtCatapultoPvzOnSelect(item);
        }
      },
    });

    return params;
  }

  function showWidgetController(isLegacy) {
    document.body.style.overflow = 'hidden';
    document.body.classList.add('lvt-catapulto-pvz-open');

    if (isLegacy) {
      ensureWidgetHost().style.display = 'block';
    }

    if (typeof widgetController.show === 'function') {
      widgetController.show();
    }
  }

  function openPvzMap(city, onSelected) {
    if (widgetLoading) return;
    if (!city) {
      alert('Укажите город доставки');
      return;
    }

    pendingOnSelected = onSelected;
    widgetLoading = true;

    fetchWidgetConfig(city)
      .then(function (cfg) {
        if (!cfg || !cfg.ok) {
          throw new Error((cfg && cfg.error) || 'widget_config_failed');
        }
        if (!cfg.hasYandexKey) {
          throw new Error('yandex_key_missing');
        }

        var isLegacy = cfg.widgetEngine === 'legacy';
        return loadScript(cfg.widgetScript, !!cfg.widgetScriptIsModule, cfg.widgetEngine || 'v3').then(function () {
          return { cfg: cfg, isLegacy: isLegacy };
        });
      })
      .then(function (payload) {
        var cfg = payload.cfg;
        var isLegacy = payload.isLegacy;
        var WidgetClass = getWidgetClass(isLegacy);
        if (!WidgetClass) {
          throw new Error('widget_class_missing');
        }

        var params = buildWidgetParams(cfg, isLegacy);

        if (widgetController && typeof widgetController.reinitialize === 'function') {
          widgetController.reinitialize(params);
        } else {
          widgetController = new WidgetClass(params);
        }

        if (isLegacy) {
          return waitForLegacyReady(widgetController).then(function () {
            showWidgetController(true);
          });
        }

        showWidgetController(false);
      })
      .catch(function (err) {
        console.error('LVT Catapulto PVZ map:', err);
        hideWidget();
        var msg = 'Не удалось открыть карту пунктов выдачи.';
        if (err && err.message === 'yandex_key_missing') {
          msg = 'Не настроен ключ Яндекс.Карт в модуле Catapulto.';
        } else if (err && err.message === 'city_required') {
          msg = 'Укажите город доставки.';
        } else if (err && err.message === 'widget_ready_timeout') {
          msg = 'Виджет Catapulto долго загружается. Попробуйте ещё раз.';
        }
        alert(msg);
      })
      .finally(function () {
        widgetLoading = false;
      });
  }

  window.lvtOrderCatapultoPvz = {
    open: openPvzMap,
    normalize: normalizePvzSelection,
  };
})();
