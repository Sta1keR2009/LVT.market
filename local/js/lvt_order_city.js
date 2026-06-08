(function () {
  'use strict';

  function readCookie(name) {
    var p = name + '=';
    var parts = document.cookie.split(';');
    for (var i = 0; i < parts.length; i++) {
      var c = parts[i].trim();
      if (c.indexOf(p) === 0) {
        return c.substring(p.length);
      }
    }
    return '';
  }

  function decodeCityValue(raw) {
    var v = String(raw || '');
    for (var i = 0; i < 3; i++) {
      try {
        var next = decodeURIComponent(v.replace(/\+/g, ' '));
        if (next === v) break;
        v = next;
      } catch (e) {
        break;
      }
    }
    return v.trim();
  }

  function extractCityName(label) {
    var v = String(label || '').trim();
    if (!v) return '';

    var parts = v.split(',').map(function (p) {
      return p.trim();
    }).filter(function (p) {
      if (!p) return false;
      var lower = p.toLowerCase();
      return lower !== 'россия' && lower !== 'russia';
    });

    if (!parts.length) return v;

    if (parts.length === 1) return parts[0];

    var last = parts[parts.length - 1];
    var first = parts[0];
    if (/край$|область$|обл\.|респ\.|республика$/i.test(last)) {
      return first;
    }
    if (/край$|область$|обл\.|респ\.|республика$/i.test(first)) {
      return last;
    }
    return first;
  }

  function readLocationInputRaw() {
    var root = document.getElementById('bx-soa-delivery') || document.getElementById('bx-soa-order-main');
    if (!root) return '';

    var searchInput = root.querySelector('input.bx-ui-sls-fake[type="text"]');
    if (searchInput && searchInput.value.trim()) {
      return searchInput.value.trim();
    }

    var routeInput = root.querySelector('.bx-ui-sls-route');
    if (routeInput && routeInput.value.trim()) {
      return routeInput.value.trim();
    }

    var steps = root.querySelectorAll('.bx-ui-combobox-fake.bx-combobox-fake-as-input');
    var chunks = [];
    for (var i = 0; i < steps.length; i++) {
      var text = String(steps[i].textContent || '').trim();
      if (!text || text.indexOf('...') >= 0 || text.indexOf('---') >= 0) continue;
      chunks.push(text);
    }
    if (chunks.length) return chunks.join(', ');

    var alt = root.querySelector('#altProperty');
    if (alt && alt.value.trim()) return alt.value.trim();

    return '';
  }

  function resolveCityName() {
    var fromInput = extractCityName(readLocationInputRaw());
    if (fromInput) return fromInput;

    var fromCookie = decodeCityValue(readCookie('lvt_display_city'));
    if (fromCookie) return extractCityName(fromCookie);

    return '';
  }

  function syncDestinationValue() {
    var city = resolveCityName();
    if (!city) return;

    document.querySelectorAll('.destination_value').forEach(function (node) {
      if (node.textContent !== city) {
        node.textContent = city;
      }
    });
  }

  function bindLocationInputs() {
    var root = document.getElementById('bx-soa-order-main');
    if (!root || root.dataset.lvtCityBound === '1') return;
    root.dataset.lvtCityBound = '1';

    var timer = null;
    function scheduleSync() {
      clearTimeout(timer);
      timer = setTimeout(syncDestinationValue, 80);
    }

    root.addEventListener('input', function (e) {
      var t = e.target;
      if (!t) return;
      if (
        t.matches('input.bx-ui-sls-fake[type="text"]') ||
        t.matches('.bx-ui-sls-route') ||
        t.id === 'altProperty' ||
        t.classList.contains('lvt-city-input')
      ) {
        scheduleSync();
      }
    }, true);

    root.addEventListener('change', function (e) {
      var t = e.target;
      if (!t) return;
      if (
        t.matches('input.bx-ui-sls-fake[type="text"]') ||
        t.matches('.bx-ui-sls-route') ||
        t.id === 'altProperty'
      ) {
        scheduleSync();
      }
    }, true);

    root.addEventListener('click', function (e) {
      if (e.target.closest('.dropdown-item, .bx-ui-slst-pool-item, .quick-location-tag')) {
        scheduleSync();
      }
    }, true);
  }

  function hookOrderAjax() {
    if (!window.BX || !BX.Sale || !BX.Sale.OrderAjaxComponent) return false;
    var cmp = BX.Sale.OrderAjaxComponent;
    if (cmp.__lvtCityHooked) return true;
    cmp.__lvtCityHooked = true;

    var origLocations = cmp.locationsCompletion;
    if (typeof origLocations === 'function') {
      cmp.locationsCompletion = function () {
        var result = origLocations.apply(this, arguments);
        syncDestinationValue();
        bindLocationInputs();
        return result;
      };
    }

    var origEditOrder = cmp.editOrder;
    if (typeof origEditOrder === 'function') {
      cmp.editOrder = function () {
        var result = origEditOrder.apply(this, arguments);
        syncDestinationValue();
        bindLocationInputs();
        return result;
      };
    }

    syncDestinationValue();
    bindLocationInputs();
    return true;
  }

  function boot() {
    bindLocationInputs();
    syncDestinationValue();

    var tries = 0;
    (function waitHook() {
      if (hookOrderAjax()) return;
      tries += 1;
      if (tries < 120) setTimeout(waitHook, 100);
    })();

    if (window.MutationObserver) {
      var moTimer = null;
      var mo = new MutationObserver(function () {
        clearTimeout(moTimer);
        moTimer = setTimeout(function () {
          syncDestinationValue();
          bindLocationInputs();
          hookOrderAjax();
        }, 120);
      });
      var target = document.getElementById('bx-soa-order-main') || document.body;
      mo.observe(target, { childList: true, subtree: true });
    }
  }

  window.lvtSyncOrderDestinationCity = syncDestinationValue;

  if (typeof BX !== 'undefined' && BX.ready) {
    BX.ready(boot);
  } else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
