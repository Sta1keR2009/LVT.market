(function () {
  'use strict';

  var SELECT_URL = '/local/api/catapulto_order_select_rate.php';
  var MARKER = 'lvt-catapulto-delivery';
  var PICKUP_RATE_KEY = 'lvt-pickup-lytkarino';

  var PICKUP_RATE = {
    rateKey: PICKUP_RATE_KEY,
    operator: 'pickup',
    operatorName: 'Самовывоз',
    rateName: 'Склад Лыткарино',
    shippingType: 'pickup',
    deliveryMode: 'pickup',
    price: 0,
    priceFormatted: 'Бесплатно',
    periodText: 'По готовности заказа',
  };

  var state = {
    mode: 'courier',
    loading: false,
    data: null,
    selectedKey: '',
    pvzAddress: '',
    deliveryAddress: '',
    boundDeliveryId: 0,
    lastFetchKey: '',
    isLytkarino: false,
  };

  var clientCache = {};
  var inflightController = null;
  var fetchTimer = null;

  function siteDir() {
    if (typeof arLiteOptions !== 'undefined' && arLiteOptions.SITE_DIR) {
      return String(arLiteOptions.SITE_DIR).replace(/\/?$/, '/');
    }
    if (typeof arAsproOptions !== 'undefined' && arAsproOptions.SITE_DIR) {
      return String(arAsproOptions.SITE_DIR).replace(/\/?$/, '/');
    }
    return '/';
  }

  function esc(text) {
    if (!text) return '';
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(text));
    return d.innerHTML;
  }

  function normalizeCityKey(city) {
    var v = String(city || '').trim().toLowerCase();
    if (!v) return '';

    var parts = v.split(',').map(function (p) { return p.trim(); }).filter(function (p) {
      if (!p) return false;
      return p !== 'россия' && p !== 'russia';
    });
    if (!parts.length) return v;
    if (parts.length === 1) return parts[0];

    var last = parts[parts.length - 1];
    var first = parts[0];
    if (/край$|область$|обл\.|респ\.|республика$/i.test(last)) return first;
    if (/край$|область$|обл\.|респ\.|республика$/i.test(first)) return last;
    return first;
  }

  function buildFetchKey(city, deliveryId) {
    var addrKey = '';
    if (window.lvtOrderAddress && typeof window.lvtOrderAddress.getQuoteKey === 'function') {
      addrKey = window.lvtOrderAddress.getQuoteKey();
    }
    return String(deliveryId || 0) + '|' + normalizeCityKey(city) + '|' + addrKey;
  }

  function isCourierAddressReady() {
    return !!(window.lvtOrderAddress && typeof window.lvtOrderAddress.isComplete === 'function' && window.lvtOrderAddress.isComplete());
  }

  function readCourierDestination() {
    if (window.lvtOrderAddress && typeof window.lvtOrderAddress.buildDestination === 'function') {
      var dest = window.lvtOrderAddress.buildDestination();
      if (dest) return dest;
    }
    return readCityFromOrder();
  }

  function readDeliveryAddressLine() {
    if (window.lvtOrderAddress && typeof window.lvtOrderAddress.readLine === 'function') {
      return window.lvtOrderAddress.readLine();
    }
    return '';
  }

  function isLytkarinoCity(city) {
    return normalizeCityKey(city) === 'лыткарино';
  }

  function isCatapultoDelivery(item) {
    if (!item) return false;
    var chunks = [item.DESCRIPTION, item.CALCULATE_DESCRIPTION, item.OWN_NAME, item.NAME];
    for (var i = 0; i < chunks.length; i++) {
      var text = String(chunks[i] || '').toLowerCase();
      if (!text) continue;
      if (text.indexOf(MARKER) !== -1 || text.indexOf('catapulto') !== -1) {
        return true;
      }
    }
    if (String(item.NAME || '').toLowerCase() === 'доставка' && String(item.OWN_NAME || '').toLowerCase() === 'доставка') {
      return true;
    }
    return false;
  }

  function getCatapultoDeliveryMeta() {
    if (!window.BX || !BX.Sale || !BX.Sale.OrderAjaxComponent || !BX.Sale.OrderAjaxComponent.result) {
      return null;
    }
    var list = BX.Sale.OrderAjaxComponent.result.DELIVERY || [];
    for (var i = 0; i < list.length; i++) {
      if (isCatapultoDelivery(list[i])) return list[i];
    }
    return null;
  }

  function ensureDeliverySelected(deliveryId) {
    if (!deliveryId) return;
    var radio = document.getElementById('ID_DELIVERY_ID_' + deliveryId);
    if (!radio || radio.checked) return;
    radio.click();
  }

  function decoratePickupDeliveryCard(node) {
    if (!node) return;
    var label = node.querySelector('.bx-soa-pp-company-label > span');
    if (label) label.textContent = 'Самовывоз';

    var costValue = node.querySelector('.bx-soa-pp-delivery-cost .bx-soa-pp-list-description');
    if (costValue) costValue.textContent = 'бесплатно';

    var periodValue = node.querySelector('.bx-soa-pp-delivery-period .bx-soa-pp-list-description');
    if (periodValue) periodValue.textContent = PICKUP_RATE.periodText;

    var calc = node.querySelector('.bx-soa-pp-delivery-calculate');
    if (calc) calc.style.display = 'none';

    var desc = node.querySelector('.bx-soa-pp-company-description');
    if (desc) {
      desc.classList.remove('hidden');
      desc.textContent = 'Самовывоз со склада: г. Лыткарино, Московская область';
    }
  }

  function syncDeliveryCardVisibility(city) {
    city = city || readCityFromOrder();
    var lytkarino = isLytkarinoCity(city);
    state.isLytkarino = lytkarino;

    var meta = getCatapultoDeliveryMeta();
    var deliveryId = meta ? parseInt(meta.ID, 10) : 0;
    var items = document.querySelectorAll('#bx-soa-delivery .bx-soa-pp-company-item[data-id]');

    items.forEach(function (node) {
      var id = parseInt(node.getAttribute('data-id'), 10);
      if (!deliveryId || id !== deliveryId) return;

      node.setAttribute('data-lvt-catapulto', '1');

      if (lytkarino) {
        node.classList.remove('lvt-catapulto-delivery-hidden');
        node.classList.add('lvt-catapulto-delivery-pickup-mode');
        decoratePickupDeliveryCard(node);
        ensureDeliverySelected(deliveryId);
      } else {
        node.classList.add('lvt-catapulto-delivery-hidden');
        node.classList.remove('lvt-catapulto-delivery-pickup-mode');
        ensureDeliverySelected(deliveryId);
      }
    });

    return lytkarino;
  }

  function clearSelectedRateRemote() {
    if (typeof BX === 'undefined' || typeof BX.bitrix_sessid !== 'function') {
      return Promise.resolve(false);
    }
    var fd = new FormData();
    fd.append('sessid', BX.bitrix_sessid());
    fd.append('action', 'clear');
    return fetch(siteDir() + 'local/api/catapulto_order_select_rate.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      cache: 'no-store',
    })
      .then(function (r) { return r.json(); })
      .then(function (data) { return !!(data && data.ok); })
      .catch(function () { return false; });
  }

  function handleLytkarinoMode() {
    hideContainer();
    syncDeliveryCardVisibility();
    state.selectedKey = PICKUP_RATE_KEY;
    state.lastFetchKey = 'pickup|lytkarino';

    return saveRate(PICKUP_RATE, true).then(function (ok) {
      if (ok) refreshOrder();
      return ok;
    });
  }

  function getSelectedDelivery() {
    if (!window.BX || !BX.Sale || !BX.Sale.OrderAjaxComponent) return null;
    return BX.Sale.OrderAjaxComponent.getSelectedDelivery
      ? BX.Sale.OrderAjaxComponent.getSelectedDelivery()
      : null;
  }

  function readCityFromOrder() {
    if (typeof window.lvtSyncOrderDestinationCity === 'function') {
      window.lvtSyncOrderDestinationCity();
    }
    var node = document.querySelector('#bx-soa-delivery .destination_value');
    if (node && node.textContent.trim()) return node.textContent.trim();

    var input = document.querySelector('#bx-soa-delivery input.bx-ui-sls-fake[type="text"]');
    if (input && input.value.trim()) return input.value.trim();

    return '';
  }

  function ensureContainer() {
    var deliveryBlock = document.getElementById('bx-soa-delivery');
    if (!deliveryBlock) return null;

    var content = deliveryBlock.querySelector('.bx-soa-section-content');
    if (!content) return null;

    var node = content.querySelector('.lvt-order-catapulto');
    if (!node) {
      node = document.createElement('div');
      node.className = 'lvt-order-catapulto';
      node.style.display = 'none';
      content.insertBefore(node, content.firstChild);
    }
    return node;
  }

  function hideContainer() {
    var node = document.querySelector('.lvt-order-catapulto');
    if (node) node.style.display = 'none';
  }

  function showLoader(container, soft) {
    container.style.display = 'block';
    if (soft && state.data && state.data.ok) {
      return;
    }
    container.innerHTML =
      '<div class="lvt-order-catapulto__title">Транспортная компания и способ доставки</div>' +
      '<div class="lvt-order-catapulto__loader">Загружаем варианты доставки Catapulto...</div>';
  }

  function filteredDeliveries() {
    var list = (state.data && state.data.deliveries) ? state.data.deliveries.slice() : [];
    var filtered = list.filter(function (item) {
      return state.mode === 'pvz' ? item.deliveryMode === 'pvz' : item.deliveryMode !== 'pvz';
    });
    if (state.mode === 'courier' && !isCourierAddressReady()) {
      return [];
    }
    return filtered;
  }

  function render(container) {
    var deliveries = filteredDeliveries();
    var selected = state.selectedKey;
    var courierNeedsAddress = state.mode === 'courier' && !isCourierAddressReady();
    var addressLine = readDeliveryAddressLine();

    var html = '<div class="lvt-order-catapulto__title">Транспортная компания и способ доставки</div>';
    if (state.loading && state.data && state.data.ok) {
      html += '<div class="lvt-order-catapulto__hint">Обновляем тарифы...</div>';
    }
    html += '<div class="lvt-order-catapulto__tabs">';
    html += '<button type="button" class="lvt-order-catapulto__tab' + (state.mode === 'courier' ? ' is-active' : '') + '" data-mode="courier">Курьером</button>';
    html += '<button type="button" class="lvt-order-catapulto__tab' + (state.mode === 'pvz' ? ' is-active' : '') + '" data-mode="pvz">В пункт выдачи</button>';
    html += '</div>';

    if (state.mode === 'courier') {
      html += '<div class="lvt-order-catapulto__address-field">';
      html += '<div class="lvt-order-catapulto__address-title">Адрес доставки до двери</div>';
      if (courierNeedsAddress) {
        html += '<div class="lvt-order-catapulto__empty">Укажите полный адрес (улица, дом, квартира) в поле «Адрес доставки» в блоке доставки ниже — это свойство заказа Bitrix.</div>';
        html += '<button type="button" class="btn btn-default btn-sm lvt-order-catapulto__address-scroll-btn">Перейти к полю адреса</button>';
      } else {
        html += '<div class="lvt-order-catapulto__address-selected">' + esc(addressLine) + '</div>';
        html += '<div class="lvt-order-catapulto__hint">Адрес берётся из свойства заказа Bitrix. Чтобы изменить — отредактируйте поле «Адрес доставки» ниже.</div>';
        html += '<button type="button" class="btn btn-default btn-sm lvt-order-catapulto__address-scroll-btn">Изменить адрес</button>';
      }
      html += '</div>';
    }

    if (!deliveries.length) {
      if (!courierNeedsAddress) {
        html += '<div class="lvt-order-catapulto__empty">Для выбранного города варианты не найдены. Попробуйте изменить город или адрес.</div>';
      }
    } else {
      html += '<div class="lvt-order-catapulto__list">';
      for (var i = 0; i < deliveries.length; i++) {
        var d = deliveries[i];
        var key = String(d.rateKey || '');
        html += '<button type="button" class="lvt-order-catapulto__item' + (selected === key ? ' is-selected' : '') + '" data-rate-key="' + esc(key) + '">';
        html += '<div class="lvt-order-catapulto__item-main">';
        html += '<div class="lvt-order-catapulto__operator">' + esc(d.operatorName || d.operator || 'ТК') + '</div>';
        html += '<div class="lvt-order-catapulto__rate">' + esc(d.rateName || '') + '</div>';
        html += '<div class="lvt-order-catapulto__meta">' + esc(d.periodText || d.deliveryDay || d.deliveryModeLabel || '') + '</div>';
        html += '</div>';
        html += '<div class="lvt-order-catapulto__price">' + esc(d.priceFormatted || '') + '</div>';
        html += '</button>';
      }
      html += '</div>';
    }

    if (state.mode === 'pvz') {
      html += '<div class="lvt-order-catapulto__pvz-field">';
      html += '<div class="lvt-order-catapulto__pvz-map-row">';
      html += '<button type="button" class="btn btn-default btn-sm lvt-order-catapulto__pvz-map-btn">Выбрать ПВЗ на карте</button>';
      html += '</div>';
      if (state.pvzAddress) {
        html += '<div class="lvt-order-catapulto__pvz-selected">' + esc(state.pvzAddress) + '</div>';
      }
      html += '<label for="lvt-order-pvz-address">Адрес пункта выдачи</label>';
      html += '<input id="lvt-order-pvz-address" class="form-control" type="text" placeholder="Или укажите адрес вручную" value="' + esc(state.pvzAddress) + '">';
      html += '<div class="lvt-order-catapulto__hint">Откройте карту для выбора пункта выдачи в городе «' + esc(readCityFromOrder() || '—') + '» или введите адрес вручную.</div>';
      html += '</div>';
    }

    container.style.display = 'block';
    container.innerHTML = html;

    container.querySelectorAll('.lvt-order-catapulto__tab').forEach(function (btn) {
      btn.addEventListener('click', function () {
        state.mode = btn.getAttribute('data-mode') || 'courier';
        if (state.mode === 'courier' && isCourierAddressReady()) {
          state.lastFetchKey = '';
          fetchQuotes(true);
        }
        render(container);
      });
    });

    container.querySelectorAll('.lvt-order-catapulto__item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (state.mode === 'courier' && !isCourierAddressReady()) {
          alert('Для курьерской доставки укажите полный адрес в поле «Адрес доставки».');
          if (window.lvtOrderAddress && typeof window.lvtOrderAddress.scrollToField === 'function') {
            window.lvtOrderAddress.scrollToField();
          }
          return;
        }
        var key = btn.getAttribute('data-rate-key') || '';
        var item = findDeliveryByKey(key);
        if (!item) return;
        if (state.selectedKey === key) return;

        state.selectedKey = key;
        state.deliveryAddress = readDeliveryAddressLine();
        saveRate(item).then(function (ok) {
          if (!ok) return;
          render(container);
          refreshOrder();
        });
      });
    });

    var addressScrollBtn = container.querySelector('.lvt-order-catapulto__address-scroll-btn');
    if (addressScrollBtn) {
      addressScrollBtn.addEventListener('click', function () {
        if (window.lvtOrderAddress && typeof window.lvtOrderAddress.scrollToField === 'function') {
          window.lvtOrderAddress.scrollToField();
        }
      });
    }

    var pvzInput = container.querySelector('#lvt-order-pvz-address');
    if (pvzInput) {
      pvzInput.addEventListener('change', function () {
        state.pvzAddress = pvzInput.value.trim();
        var current = findDeliveryByKey(state.selectedKey);
        if (current) {
          saveRate(Object.assign({}, current, { pvzAddress: state.pvzAddress, pvzId: '', pvzCode: '' }));
        }
      });
    }

    var pvzMapBtn = container.querySelector('.lvt-order-catapulto__pvz-map-btn');
    if (pvzMapBtn) {
      pvzMapBtn.addEventListener('click', function () {
        var city = readCityFromOrder();
        if (!city) {
          alert('Сначала укажите город доставки.');
          return;
        }
        if (!window.lvtOrderCatapultoPvz || typeof window.lvtOrderCatapultoPvz.open !== 'function') {
          alert('Карта ПВЗ временно недоступна. Обновите страницу.');
          return;
        }
        window.lvtOrderCatapultoPvz.open(city, function (rate) {
          if (!rate) return;
          state.pvzAddress = rate.pvzAddress || '';
          state.selectedKey = rate.rateKey || state.selectedKey;
          state.mode = 'pvz';
          saveRate(rate).then(function (ok) {
            if (!ok) return;
            render(container);
            refreshOrder();
          });
        });
      });
    }
  }

  function findDeliveryByKey(key) {
    var all = (state.data && state.data.deliveries) ? state.data.deliveries : [];
    for (var i = 0; i < all.length; i++) {
      if (String(all[i].rateKey || '') === key) return all[i];
    }
    return null;
  }

  function saveRate(item, silent) {
    if (!item || typeof BX === 'undefined' || typeof BX.bitrix_sessid !== 'function') {
      return Promise.resolve(false);
    }

    var fd = new FormData();
    fd.append('sessid', BX.bitrix_sessid());
    fd.append('rateKey', item.rateKey || '');
    fd.append('operator', item.operator || '');
    fd.append('operatorName', item.operatorName || '');
    fd.append('rateName', item.rateName || '');
    fd.append('shippingType', item.shippingType || '');
    fd.append('deliveryMode', item.deliveryMode || '');
    fd.append('price', String(item.price || 0));
    fd.append('periodText', item.periodText || item.deliveryDay || '');
    fd.append('pvzAddress', item.pvzAddress || state.pvzAddress || '');
    fd.append('pvzId', item.pvzId || '');
    fd.append('pvzCode', item.pvzCode || '');
    fd.append('deliveryAddress', item.deliveryAddress || state.deliveryAddress || readDeliveryAddressLine() || '');

    return fetch(siteDir() + 'local/api/catapulto_order_select_rate.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      cache: 'no-store',
    })
      .then(function (r) { return r.json(); })
      .then(function (data) { return !!(data && data.ok); })
      .catch(function () { return false; });
  }

  function refreshOrder() {
    if (window.BX && BX.Sale && BX.Sale.OrderAjaxComponent && typeof BX.Sale.OrderAjaxComponent.sendRequest === 'function') {
      BX.Sale.OrderAjaxComponent.sendRequest();
    }
  }

  function applyQuoteData(data, delivery, fetchKey) {
    state.data = data;
    state.boundDeliveryId = parseInt(delivery.ID, 10) || 0;
    state.lastFetchKey = fetchKey;
    clientCache[fetchKey] = data;

    var selected = data.selectedRate || null;
    if (selected && selected.rateKey) {
      state.selectedKey = String(selected.rateKey);
      state.pvzAddress = String(selected.pvzAddress || '');
      state.deliveryAddress = String(selected.deliveryAddress || '');
      state.mode = selected.deliveryMode === 'pvz' ? 'pvz' : 'courier';
    } else if (data.deliveries && data.deliveries.length) {
      state.selectedKey = String(data.deliveries[0].rateKey || '');
      saveRate(data.deliveries[0], true);
    }
  }

  function doFetchQuotes(force) {
    var container = ensureContainer();
    if (!container) return;

    var city = readCityFromOrder();
    var wasLytkarino = state.isLytkarino;
    var lytkarino = syncDeliveryCardVisibility(city);

    if (lytkarino) {
      if (state.lastFetchKey === 'pickup|lytkarino' && state.selectedKey === PICKUP_RATE_KEY) {
        hideContainer();
        syncDeliveryCardVisibility(city);
        return;
      }
      handleLytkarinoMode();
      return;
    }

    if (wasLytkarino && !lytkarino) {
      clearSelectedRateRemote();
      state.selectedKey = '';
      state.data = null;
      state.lastFetchKey = '';
    }

    var delivery = getSelectedDelivery();
    if (!delivery || !isCatapultoDelivery(delivery)) {
      var meta = getCatapultoDeliveryMeta();
      if (meta) {
        ensureDeliverySelected(parseInt(meta.ID, 10));
        delivery = getSelectedDelivery();
      }
    }

    if (!delivery || !isCatapultoDelivery(delivery)) {
      hideContainer();
      state.boundDeliveryId = 0;
      state.lastFetchKey = '';
      return;
    }
    var fetchKey = buildFetchKey(city, delivery.ID);

    if (!force && fetchKey === state.lastFetchKey && state.data && state.data.ok) {
      render(container);
      return;
    }

    if (!force && clientCache[fetchKey]) {
      applyQuoteData(clientCache[fetchKey], delivery, fetchKey);
      render(container);
      return;
    }

    if (state.loading && !force) return;

    if (inflightController) {
      try { inflightController.abort(); } catch (e) {}
    }
    inflightController = typeof AbortController !== 'undefined' ? new AbortController() : null;

    state.loading = true;
    showLoader(container, !!(state.data && state.data.ok));
    if (state.data && state.data.ok) render(container);

    var url = siteDir() + 'local/api/catapulto_order_delivery_quote.php?include_pvz=1';
    if (city) url += '&city=' + encodeURIComponent(city);
    if (isCourierAddressReady()) {
      var destination = readCourierDestination();
      if (destination) url += '&address=' + encodeURIComponent(destination);
    }

    var fetchOpts = { credentials: 'same-origin', cache: 'default' };
    if (inflightController) fetchOpts.signal = inflightController.signal;

    fetch(url, fetchOpts)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        state.loading = false;
        inflightController = null;

        if (!data || !data.ok) {
          if (state.data && state.data.ok) {
            render(container);
            return;
          }
          container.innerHTML =
            '<div class="lvt-order-catapulto__title">Транспортная компания и способ доставки</div>' +
            '<div class="lvt-order-catapulto__empty">' + esc((data && data.error) || 'Не удалось получить тарифы Catapulto') + '</div>';
          container.style.display = 'block';
          return;
        }

        applyQuoteData(data, delivery, fetchKey);
        render(container);
      })
      .catch(function (err) {
        if (err && err.name === 'AbortError') return;
        state.loading = false;
        inflightController = null;
        if (state.data && state.data.ok) {
          render(container);
          return;
        }
        container.innerHTML =
          '<div class="lvt-order-catapulto__title">Транспортная компания и способ доставки</div>' +
          '<div class="lvt-order-catapulto__empty">Ошибка загрузки тарифов Catapulto</div>';
        container.style.display = 'block';
      });
  }

  function fetchQuotes(force) {
    clearTimeout(fetchTimer);
    fetchTimer = setTimeout(function () {
      doFetchQuotes(!!force);
    }, force ? 0 : 280);
  }

  function hookOrderAjax() {
    if (!window.BX || !BX.Sale || !BX.Sale.OrderAjaxComponent) return false;
    var cmp = BX.Sale.OrderAjaxComponent;
    if (cmp.__lvtCatapultoHooked) return true;
    cmp.__lvtCatapultoHooked = true;

    var origEditDelivery = cmp.editActiveDeliveryBlock;
    if (typeof origEditDelivery === 'function') {
      cmp.editActiveDeliveryBlock = function () {
        var result = origEditDelivery.apply(this, arguments);
        syncDeliveryCardVisibility();
        fetchQuotes(false);
        return result;
      };
    }

    var origLocations = cmp.locationsCompletion;
    if (typeof origLocations === 'function') {
      cmp.locationsCompletion = function () {
        var result = origLocations.apply(this, arguments);
        syncDeliveryCardVisibility();
        fetchQuotes(true);
        return result;
      };
    }

    var origSelectDelivery = cmp.selectDelivery;
    if (typeof origSelectDelivery === 'function') {
      cmp.selectDelivery = function () {
        var result = origSelectDelivery.apply(this, arguments);
        fetchQuotes(true);
        return result;
      };
    }

    setTimeout(function () { fetchQuotes(false); }, 200);
    return true;
  }

  function boot() {
    if (window.lvtOrderAddress && typeof window.lvtOrderAddress.bind === 'function') {
      window.lvtOrderAddress.bind(function () {
        state.deliveryAddress = readDeliveryAddressLine();
        state.lastFetchKey = '';
        fetchQuotes(true);
      });
    }

    var tries = 0;
    (function wait() {
      if (hookOrderAjax()) return;
      tries += 1;
      if (tries < 120) setTimeout(wait, 100);
    })();

    document.addEventListener('click', function (e) {
      if (e.target.closest('.change_city, .lvt-city-item, .lvt-city-modal-item, .quick-location-tag, .dropdown-item')) {
        state.lastFetchKey = '';
        fetchQuotes(true);
      }
    }, true);
  }

  if (typeof BX !== 'undefined' && BX.ready) {
    BX.ready(boot);
  } else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
