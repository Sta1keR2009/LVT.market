(function () {
  'use strict';

  function getOrderProps() {
    if (!window.BX || !BX.Sale || !BX.Sale.OrderAjaxComponent) return [];
    var result = BX.Sale.OrderAjaxComponent.result;
    if (!result || !result.ORDER_PROP || !result.ORDER_PROP.properties) return [];
    return result.ORDER_PROP.properties;
  }

  function findPropByCode(code) {
    var props = getOrderProps();
    for (var i = 0; i < props.length; i++) {
      if (String(props[i].CODE || '') === code) return props[i];
    }
    return null;
  }

  function readPropInput(prop) {
    if (!prop || !prop.ID) return '';
    var id = prop.ID;
    var el =
      document.querySelector('[name="ORDER_PROP_' + id + '"]') ||
      document.querySelector('#ORDER_PROP_' + id);
    if (el && typeof el.value === 'string') {
      return el.value.trim();
    }
    return '';
  }

  function readAddressLine() {
    var fromProp = readPropInput(findPropByCode('ADDRESS'));
    if (fromProp) return fromProp;

    var node = document.querySelector(
      '#bx-soa-delivery .ADDRESS textarea, #bx-soa-delivery .ADDRESS input[type="text"]'
    );
    if (node && node.value) return String(node.value).trim();

    var cityText = readPropInput(findPropByCode('CITY'));
    if (cityText) {
      var cityProp = findPropByCode('CITY');
      if (cityProp && cityProp.TYPE !== 'LOCATION') {
        return cityText;
      }
    }

    return '';
  }

  function readZip() {
    var fromProp = readPropInput(findPropByCode('ZIP'));
    if (fromProp) return fromProp;

    var zip = document.querySelector('#zipProperty');
    return zip && zip.value ? String(zip.value).trim() : '';
  }

  function readCityLabel() {
    if (typeof window.lvtSyncOrderDestinationCity === 'function') {
      window.lvtSyncOrderDestinationCity();
    }
    var node = document.querySelector('#bx-soa-delivery .destination_value');
    if (node && node.textContent.trim()) return node.textContent.trim();

    var input = document.querySelector('#bx-soa-delivery input.bx-ui-sls-fake[type="text"]');
    if (input && input.value.trim()) return input.value.trim();

    return '';
  }

  function isDoorAddressComplete(addressLine) {
    addressLine = String(addressLine || '').trim();
    if (addressLine.length < 5) return false;
    return /\d/.test(addressLine) || /(ул\.?|улиц|пр\.?|просп|пер\.?|б-?р|ш\.?|д\.?\s*\d|дом|корп|кв|стр|оф)/i.test(addressLine);
  }

  function buildCourierDestination() {
    var city = readCityLabel();
    var address = readAddressLine();
    var zip = readZip();
    var parts = [];

    if (city) parts.push(city);
    if (address) parts.push(address);
    if (zip) parts.push(zip);

    return parts.join(', ').trim();
  }

  function getQuoteKey() {
    var address = readAddressLine();
    var zip = readZip();
    if (!address && !zip) return '';
    return [address, zip].join('|').toLowerCase();
  }

  function getAddressFieldNode() {
    var prop = findPropByCode('ADDRESS');
    if (prop) {
      var input =
        document.querySelector('[name="ORDER_PROP_' + prop.ID + '"]') ||
        document.querySelector('#ORDER_PROP_' + prop.ID);
      if (input) return input;
    }
    return document.querySelector(
      '#bx-soa-delivery .ADDRESS textarea, #bx-soa-delivery .ADDRESS input[type="text"]'
    );
  }

  function getAddressBlockNode() {
    return document.querySelector('#bx-soa-delivery .ADDRESS');
  }

  function scrollToAddressField() {
    var block = getAddressBlockNode();
    var field = getAddressFieldNode();
    var target = block || field;
    if (!target) return false;

    target.classList.add('lvt-order-address-highlight');
    setTimeout(function () {
      target.classList.remove('lvt-order-address-highlight');
    }, 2400);

    if (typeof target.scrollIntoView === 'function') {
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    if (field && typeof field.focus === 'function') {
      setTimeout(function () {
        field.focus();
      }, 350);
    }
    return true;
  }

  function bindAddressInputs(onChange) {
    var root = document.getElementById('bx-soa-order-main');
    if (!root || root.dataset.lvtAddressBound === '1') return;
    root.dataset.lvtAddressBound = '1';

    var timer = null;
    function schedule() {
      clearTimeout(timer);
      timer = setTimeout(function () {
        if (typeof onChange === 'function') onChange();
      }, 300);
    }

    root.addEventListener(
      'input',
      function (e) {
        var t = e.target;
        if (!t) return;
        if (
          t.closest('.ADDRESS') ||
          t.id === 'zipProperty' ||
          (t.name && t.name.indexOf('ORDER_PROP_') === 0)
        ) {
          schedule();
        }
      },
      true
    );

    root.addEventListener(
      'change',
      function (e) {
        var t = e.target;
        if (!t) return;
        if (
          t.closest('.ADDRESS') ||
          t.id === 'zipProperty' ||
          (t.name && t.name.indexOf('ORDER_PROP_') === 0)
        ) {
          schedule();
        }
      },
      true
    );
  }

  window.lvtOrderAddress = {
    readLine: readAddressLine,
    readZip: readZip,
    readCity: readCityLabel,
    buildDestination: buildCourierDestination,
    isComplete: function () {
      return isDoorAddressComplete(readAddressLine());
    },
    getQuoteKey: getQuoteKey,
    scrollToField: scrollToAddressField,
    bind: bindAddressInputs,
  };
})();
